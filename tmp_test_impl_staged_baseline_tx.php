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

function flowCounts(mysqli $db, int $clusterId, string $flowType): array {
  $ft = $db->real_escape_string($flowType);
  $q1 = $db->query("SELECT COUNT(i.id_doc_item) AS total_doc
                    FROM md_myrep_flow_doc_group g
                    JOIN md_myrep_flow_doc_item i ON i.id_doc_group=g.id_doc_group AND i.is_active=1
                    WHERE g.flow_type='{$ft}' AND g.is_active=1");
  $total = (int)($q1?->fetch_assoc()['total_doc'] ?? 0);

  $q2 = $db->query("SELECT SUM(CASE WHEN f.id_doc_file IS NOT NULL THEN 1 ELSE 0 END) AS uploaded_doc
                    FROM tb_myrep_flow_doc_package p
                    JOIN md_myrep_flow_doc_group g ON g.id_doc_group=p.id_doc_group AND g.flow_type='{$ft}' AND g.is_active=1
                    JOIN md_myrep_flow_doc_item i ON i.id_doc_group=g.id_doc_group AND i.is_active=1
                    LEFT JOIN tb_myrep_flow_doc_file f ON f.id_doc_package=p.id_doc_package AND f.id_doc_item=i.id_doc_item
                    WHERE p.flow_type='{$ft}' AND p.id_myrep_cluster={$clusterId}");
  $uploaded = (int)($q2?->fetch_assoc()['uploaded_doc'] ?? 0);

  return ['total'=>$total,'uploaded'=>$uploaded,'complete'=>($total>0 && $uploaded >= $total)];
}

$env = parseEnv(__DIR__ . '/.env');
$db = new mysqli($env['HOSTNAME'] ?? '127.0.0.1', $env['USERNAME'] ?? 'root', $env['PASSWORD'] ?? '', $env['DATABASE'] ?? '');
if ($db->connect_error) { fwrite(STDERR, $db->connect_error . "\n"); exit(1);} 
$db->set_charset('utf8mb4');

$pickSql = "
SELECT c.id_myrep_cluster, c.cluster_name, c.city_name
FROM tb_myrep_cluster c
JOIN tb_myrep_boq_baseline b ON b.id_myrep_cluster=c.id_myrep_cluster AND b.status_baseline='ACTIVE' AND (b.scope_type='CLUSTER' OR b.scope_type IS NULL)
LIMIT 1
";
$pick = $db->query($pickSql);
$row = $pick ? $pick->fetch_assoc() : null;
if (!$row) {
  echo json_encode(['ok'=>false,'reason'=>'No cluster with ACTIVE baseline found'], JSON_PRETTY_PRINT),"\n";
  exit(0);
}

$clusterId = (int)$row['id_myrep_cluster'];
$beforeDrm = flowCounts($db, $clusterId, 'DRM');
$beforeSub = flowCounts($db, $clusterId, 'DRM_SUBFEEDER');

$db->begin_transaction();

$fileRow = $db->query("SELECT f.id_doc_file
                       FROM tb_myrep_flow_doc_package p
                       JOIN tb_myrep_flow_doc_file f ON f.id_doc_package=p.id_doc_package
                       WHERE p.id_myrep_cluster={$clusterId} AND p.flow_type='DRM_SUBFEEDER'
                       LIMIT 1")->fetch_assoc();

if (!$fileRow) {
  $db->rollback();
  echo json_encode(['ok'=>false,'reason'=>'No DRM_SUBFEEDER file found for sampled cluster','cluster'=>$row], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),"\n";
  exit(0);
}

$idDocFile = (int)$fileRow['id_doc_file'];
$db->query("DELETE FROM tb_myrep_flow_doc_file WHERE id_doc_file={$idDocFile}");

$afterDrm = flowCounts($db, $clusterId, 'DRM');
$afterSub = flowCounts($db, $clusterId, 'DRM_SUBFEEDER');

$hasBaseline = true;
$eligibleOld = $afterDrm['complete'] && $afterSub['complete'];
$eligibleNew = $afterDrm['complete'] && ($afterSub['complete'] || $hasBaseline);

$db->rollback();

echo json_encode([
  'ok' => true,
  'cluster' => $row,
  'simulated_deleted_subfeeder_doc_file_id' => $idDocFile,
  'before' => ['DRM'=>$beforeDrm,'DRM_SUBFEEDER'=>$beforeSub],
  'after_simulation' => ['DRM'=>$afterDrm,'DRM_SUBFEEDER'=>$afterSub],
  'eligible_old_logic' => $eligibleOld,
  'eligible_new_logic' => $eligibleNew,
  'note' => 'All DB changes rolled back via transaction',
], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),"\n";
