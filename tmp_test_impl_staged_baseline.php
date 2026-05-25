<?php
declare(strict_types=1);

function parseEnv(string $path): array {
  $out = [];
  foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === ';' || $line[0] === '#') continue;
    $parts = explode('=', $line, 2);
    if (count($parts) !== 2) continue;
    $out[trim($parts[0])] = trim($parts[1]);
  }
  return $out;
}

$env = parseEnv(__DIR__ . '/.env');
$db = new mysqli($env['HOSTNAME'] ?? '127.0.0.1', $env['USERNAME'] ?? 'root', $env['PASSWORD'] ?? '', $env['DATABASE'] ?? '');
if ($db->connect_error) {
  fwrite(STDERR, "DB connect failed: {$db->connect_error}\n");
  exit(1);
}
$db->set_charset('utf8mb4');

$sql = "
SELECT
  c.id_myrep_cluster,
  c.cluster_name,
  c.city_name,
  hc.review_status AS cluster_boq_status,
  hs.review_status AS subfeeder_boq_status,
  CASE WHEN b.id_boq_baseline IS NULL THEN 0 ELSE 1 END AS has_active_baseline
FROM tb_myrep_cluster c
LEFT JOIN tb_myrep_drm_boq hc ON hc.id_myrep_cluster = c.id_myrep_cluster AND hc.scope_type='CLUSTER'
LEFT JOIN tb_myrep_drm_boq hs ON hs.id_myrep_cluster = c.id_myrep_cluster AND hs.scope_type='SUBFEEDER'
LEFT JOIN tb_myrep_boq_baseline b
  ON b.id_myrep_cluster = c.id_myrep_cluster
 AND b.status_baseline='ACTIVE'
 AND (b.scope_type='CLUSTER' OR b.scope_type IS NULL)
WHERE UPPER(COALESCE(hc.review_status,''))='APPROVED'
  AND UPPER(COALESCE(hs.review_status,''))<>'APPROVED'
ORDER BY c.id_myrep_cluster DESC
LIMIT 20
";

$res = $db->query($sql);
if (!$res) {
  fwrite(STDERR, "Query 1 failed: {$db->error}\n");
  exit(1);
}
$candidates = [];
while ($r = $res->fetch_assoc()) { $candidates[] = $r; }

function flowComplete(mysqli $db, int $clusterId, string $flowType): bool {
  $flowTypeEsc = $db->real_escape_string($flowType);
  $q1 = $db->query("SELECT COUNT(i.id_doc_item) AS total_doc FROM md_myrep_flow_doc_group g JOIN md_myrep_flow_doc_item i ON i.id_doc_group=g.id_doc_group AND i.is_active=1 WHERE g.flow_type='{$flowTypeEsc}' AND g.is_active=1");
  if (!$q1) return false;
  $total = (int)($q1->fetch_assoc()['total_doc'] ?? 0);
  if ($total <= 0) return false;

  $q2 = $db->query("SELECT SUM(CASE WHEN f.id_doc_file IS NOT NULL THEN 1 ELSE 0 END) AS uploaded_doc
                    FROM tb_myrep_flow_doc_package p
                    JOIN md_myrep_flow_doc_group g ON g.id_doc_group=p.id_doc_group AND g.flow_type='{$flowTypeEsc}' AND g.is_active=1
                    JOIN md_myrep_flow_doc_item i ON i.id_doc_group=g.id_doc_group AND i.is_active=1
                    LEFT JOIN tb_myrep_flow_doc_file f ON f.id_doc_package=p.id_doc_package AND f.id_doc_item=i.id_doc_item
                    WHERE p.flow_type='{$flowTypeEsc}' AND p.id_myrep_cluster={$clusterId}");
  if (!$q2) return false;
  $uploaded = (int)($q2->fetch_assoc()['uploaded_doc'] ?? 0);
  return $uploaded >= $total;
}

$report = [];
foreach ($candidates as $row) {
  $id = (int)$row['id_myrep_cluster'];
  $hasBaseline = (int)$row['has_active_baseline'] === 1;
  $drmOk = flowComplete($db, $id, 'DRM');
  $subOk = flowComplete($db, $id, 'DRM_SUBFEEDER');
  $eligibleNew = $drmOk && ($subOk || $hasBaseline);
  $eligibleOld = $drmOk && $subOk;
  $report[] = [
    'id_myrep_cluster' => $id,
    'cluster_name' => $row['cluster_name'],
    'city_name' => $row['city_name'],
    'cluster_boq_status' => $row['cluster_boq_status'],
    'subfeeder_boq_status' => $row['subfeeder_boq_status'],
    'has_active_baseline' => $hasBaseline,
    'drm_docs_complete' => $drmOk,
    'drm_subfeeder_docs_complete' => $subOk,
    'eligible_old_logic' => $eligibleOld,
    'eligible_new_logic' => $eligibleNew,
  ];
}

echo json_encode([
  'candidate_count' => count($candidates),
  'report' => $report,
], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE), "\n";
