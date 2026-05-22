<?php
declare(strict_types=1);

/**
 * MyRep full smoke test (safe dry-run).
 * Scope:
 * - Login per representative role user
 * - Validate VIEW access per MyRep page vs tb_myrep_role_permission
 * - Validate action endpoint access (dry-run payload, no destructive write)
 * - Validate JSON endpoints for Checklist and BAK district options
 * - Detect city-mapping leak indicator for BLITAR cluster visibility
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

date_default_timezone_set('Asia/Jakarta');

$rootPath = __DIR__;
$envPath = $rootPath . DIRECTORY_SEPARATOR . '.env';
$resultPath = $rootPath . DIRECTORY_SEPARATOR . 'tmp_myrep_full_test_result.json';
$baseUrl = 'http://localhost/DatabaseTKM/';

function parseEnvFile(string $path): array
{
    $result = [];
    if (!is_file($path)) {
        return $result;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return $result;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);
        if ($value !== '') {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        $result[$key] = $value;
    }

    return $result;
}

function dbFetchAll(mysqli $db, string $sql): array
{
    $rows = [];
    $query = $db->query($sql);
    if ($query === false) {
        throw new RuntimeException('Query failed: ' . $db->error . ' | SQL: ' . $sql);
    }
    while ($row = $query->fetch_assoc()) {
        $rows[] = $row;
    }
    $query->free();
    return $rows;
}

function httpRequest(string $method, string $url, string $sessionKey, array $postFields = []): array
{
    static $sessions = [];

    if (!isset($sessions[$sessionKey])) {
        $ch = curl_init();
        if ($ch === false) {
            return [
                'status' => 0,
                'body' => '',
                'error' => 'curl_init failed',
            ];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => '',
            CURLOPT_COOKIEFILE => '',
            CURLOPT_HTTPHEADER => ['User-Agent: MyRep-SmokeTest/1.0'],
        ]);

        $sessions[$sessionKey] = $ch;
    }

    /** @var resource $ch */
    $ch = $sessions[$sessionKey];
    $method = strtoupper($method);

    curl_setopt($ch, CURLOPT_URL, $url);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_HTTPGET, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    } else {
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
    }

    $body = (string) curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

    return [
        'status' => $status,
        'body' => $body,
        'error' => $error,
    ];
}

function expectedPermission(bool $isSuperAdmin, array $roles, array $permissionSet, string $page, string $action): bool
{
    if ($isSuperAdmin) {
        return true;
    }

    foreach ($roles as $role) {
        $k = $page . '|' . strtoupper($action) . '|' . strtoupper($role);
        if (isset($permissionSet[$k])) {
            return true;
        }
    }

    return false;
}

function isNoAccessResponse(int $status, string $body): bool
{
    if ($status === 403) {
        return true;
    }

    $bodyUpper = strtoupper($body);
    if (str_contains($bodyUpper, 'YOU ARE NOT ALLOWED TO ENTER HERE')) {
        return true;
    }

    return false;
}

function safeUsername(string $username): string
{
    return preg_replace('/[^A-Za-z0-9_\-\.]/', '', $username) ?? 'user';
}

$env = parseEnvFile($envPath);
$dbHost = (string) ($env['HOSTNAME'] ?? '');
$dbUser = (string) ($env['USERNAME'] ?? '');
$dbPass = (string) ($env['PASSWORD'] ?? '');
$dbName = (string) ($env['DATABASE'] ?? '');

if ($dbHost === '' || $dbUser === '' || $dbName === '') {
    throw new RuntimeException('Missing DB config in .env');
}

$db = mysqli_init();
if ($db === false) {
    throw new RuntimeException('mysqli_init failed');
}
$db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 20);
if (!$db->real_connect($dbHost, $dbUser, $dbPass, $dbName)) {
    throw new RuntimeException('DB connect failed: ' . mysqli_connect_error());
}
$db->set_charset('utf8mb4');

// Detect available role columns on mapping table.
$columnRows = dbFetchAll($db, "SHOW COLUMNS FROM tb_myrep_pic_mapping_city");
$existingColumns = array_map(static fn(array $r): string => (string) ($r['Field'] ?? ''), $columnRows);
$existingColumnSet = array_fill_keys($existingColumns, true);

$columnRoleMap = [
    'rpm_area' => 'RPM_AREA',
    'sm_area' => 'SM_AREA',
    'spv_area' => 'SPV_AREA',
    'snd_area' => 'SND_AREA',
    'admin_area' => 'ADMIN_AREA',
    'snd_ho' => 'SND_HO',
    'atp_ho' => 'ATP_HO',
    'rfs_ho' => 'RFS_HO',
    'sitac_ho' => 'SITAC_HO',
    'dc_ho' => 'DC_HO',
    'qa_ho' => 'QA_HO',
];

$availableRoleColumns = [];
foreach ($columnRoleMap as $col => $role) {
    if (isset($existingColumnSet[$col])) {
        $availableRoleColumns[$col] = $role;
    }
}

$mappingSelectCols = array_merge(['city_name'], array_keys($availableRoleColumns));
$mappingRows = dbFetchAll(
    $db,
    "SELECT " . implode(', ', $mappingSelectCols) . " FROM tb_myrep_pic_mapping_city"
);

// Build representative users by role column.
$testUsers = [];
foreach ($availableRoleColumns as $col => $roleKey) {
    $sql = "
        SELECT
            '{$roleKey}' AS seeded_role,
            m.city_name,
            u.id,
            u.nik,
            u.nama_karyawan,
            u.username_user,
            u.password_user
        FROM tb_myrep_pic_mapping_city m
        JOIN tb_master_user_new u ON u.nik = m.{$col}
        WHERE
            m.{$col} IS NOT NULL
            AND m.{$col} <> ''
            AND u.status_user = 'ACTIVE'
            AND u.username_user IS NOT NULL
            AND u.username_user <> ''
        LIMIT 1
    ";
    $rows = dbFetchAll($db, $sql);
    if (!empty($rows)) {
        $testUsers[] = $rows[0];
    }
}

// Add one super admin.
$superAdminRows = dbFetchAll(
    $db,
    "
    SELECT
        'SUPER_ADMIN' AS seeded_role,
        '-' AS city_name,
        u.id,
        u.nik,
        u.nama_karyawan,
        u.username_user,
        u.password_user
    FROM tb_master_user_new u
    JOIN tb_level l ON l.id_level = u.id_level
    WHERE
        l.nama_level = 'Super Admin'
        AND u.status_user = 'ACTIVE'
        AND u.username_user IS NOT NULL
        AND u.username_user <> ''
    LIMIT 1
"
);
if (!empty($superAdminRows)) {
    $testUsers[] = $superAdminRows[0];
}

// Deduplicate users by ID.
$seenUserIds = [];
$dedupUsers = [];
foreach ($testUsers as $u) {
    $id = (int) ($u['id'] ?? 0);
    if ($id <= 0 || isset($seenUserIds[$id])) {
        continue;
    }
    $seenUserIds[$id] = true;
    $dedupUsers[] = $u;
}
$testUsers = $dedupUsers;

// Permission map.
$permissionRows = dbFetchAll(
    $db,
    "SELECT page_key, action_key, role_key
     FROM tb_myrep_role_permission
     WHERE is_allowed = 1 AND is_active = 1"
);
$permissionSet = [];
foreach ($permissionRows as $row) {
    $k = strtoupper((string) $row['page_key']) . '|' . strtoupper((string) $row['action_key']) . '|' . strtoupper((string) $row['role_key']);
    $permissionSet[$k] = true;
}

$myrepPages = [
    'MyRepublik_Project',
    'BAK_MyRep',
    'VALSAL_MyRep',
    'Batch_Approval_MyRep',
    'DRM_MyRep',
    'Implementasi_BOQ_MyRep',
    'PO_MyRep',
    'Monitoring_RFS_MyRep',
    'ATP_MyRep',
    'Checklist_Dokument_MyRep',
];

$clusterBasedPagesForLeakCheck = [
    'BAK_MyRep',
    'VALSAL_MyRep',
    'Batch_Approval_MyRep',
    'DRM_MyRep',
    'Implementasi_BOQ_MyRep',
    'PO_MyRep',
];

$actionTests = [
    ['module' => 'BAK_MyRep', 'action' => 'TAMBAH', 'method' => 'POST', 'path' => 'BAK_MyRep/saveCluster', 'body' => []],
    ['module' => 'BAK_MyRep', 'action' => 'APPROVAL', 'method' => 'POST', 'path' => 'BAK_MyRep/approveDocument', 'body' => []],
    ['module' => 'VALSAL_MyRep', 'action' => 'TAMBAH', 'method' => 'POST', 'path' => 'VALSAL_MyRep/saveValsal', 'body' => ['cluster_id' => 0]],
    ['module' => 'VALSAL_MyRep', 'action' => 'APPROVAL', 'method' => 'POST', 'path' => 'VALSAL_MyRep/approveDocument', 'body' => []],
    ['module' => 'Batch_Approval_MyRep', 'action' => 'TAMBAH', 'method' => 'POST', 'path' => 'Batch_Approval_MyRep/saveBatchApproval', 'body' => ['cluster_id' => 0]],
    ['module' => 'Batch_Approval_MyRep', 'action' => 'APPROVAL', 'method' => 'POST', 'path' => 'Batch_Approval_MyRep/approveDocument', 'body' => []],
    ['module' => 'DRM_MyRep', 'action' => 'TAMBAH', 'method' => 'POST', 'path' => 'DRM_MyRep/saveDrm', 'body' => ['cluster_id' => 0]],
    ['module' => 'DRM_MyRep', 'action' => 'APPROVAL', 'method' => 'POST', 'path' => 'DRM_MyRep/approveDocument', 'body' => []],
    ['module' => 'Implementasi_BOQ_MyRep', 'action' => 'TAMBAH', 'method' => 'POST', 'path' => 'Implementasi_BOQ_MyRep/saveProgress', 'body' => ['cluster_id' => 0]],
    ['module' => 'Implementasi_BOQ_MyRep', 'action' => 'APPROVAL_DAILY', 'method' => 'POST', 'path' => 'Implementasi_BOQ_MyRep/saveDailyActivity', 'body' => ['cluster_id' => 0]],
    ['module' => 'Implementasi_BOQ_MyRep', 'action' => 'APPROVAL_FOTO_COMPLY', 'method' => 'POST', 'path' => 'Implementasi_BOQ_MyRep/approveComplyPhoto', 'body' => ['progress_photo_id' => 0]],
    ['module' => 'PO_MyRep', 'action' => 'TAMBAH', 'method' => 'POST', 'path' => 'PO_MyRep/savePo', 'body' => ['cluster_id' => 0]],
    ['module' => 'Monitoring_RFS_MyRep', 'action' => 'TAMBAH', 'method' => 'POST', 'path' => 'Monitoring_RFS_MyRep/saveCluster', 'body' => []],
    ['module' => 'Monitoring_RFS_MyRep', 'action' => 'APPROVAL', 'method' => 'POST', 'path' => 'Monitoring_RFS_MyRep/updateClaimStatus', 'body' => ['id_claim' => 0]],
    ['module' => 'ATP_MyRep', 'action' => 'EDIT', 'method' => 'POST', 'path' => 'ATP_MyRep/save', 'body' => ['cluster_id' => 0]],
    ['module' => 'Checklist_Dokument_MyRep', 'action' => 'TAMBAH', 'method' => 'POST', 'path' => 'Checklist_Dokument_MyRep/uploadDocument', 'body' => ['cluster_id' => 0, 'id_doc_package' => 0, 'id_doc_item' => 0]],
    ['module' => 'Checklist_Dokument_MyRep', 'action' => 'APPROVAL', 'method' => 'POST', 'path' => 'Checklist_Dokument_MyRep/approveDocument', 'body' => []],
];

$suiteStartedAt = date('c');
$userResults = [];

foreach ($testUsers as $user) {
    $userId = (int) ($user['id'] ?? 0);
    $nik = (string) ($user['nik'] ?? '');
    $username = (string) ($user['username_user'] ?? '');
    $password = (string) ($user['password_user'] ?? '');
    $displayName = (string) ($user['nama_karyawan'] ?? '');
    $seedRole = strtoupper((string) ($user['seeded_role'] ?? ''));
    $isSuperAdmin = ($seedRole === 'SUPER_ADMIN');

    $roles = [];
    $citySet = [];

    if ($isSuperAdmin) {
        $roles[] = 'SUPER_ADMIN';
    } else {
        foreach ($mappingRows as $row) {
            $matchUser = false;
            foreach ($availableRoleColumns as $col => $roleKey) {
                if (isset($row[$col]) && trim((string) $row[$col]) === $nik) {
                    $matchUser = true;
                    $roles[strtoupper($roleKey)] = true;
                }
            }

            if (!$matchUser) {
                continue;
            }

            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($cityName !== '') {
                $citySet[$cityName] = true;
            }
        }

        $roles = array_keys($roles);
        sort($roles);
    }

    $sessionKey = 'sess_' . safeUsername($username) . '_' . $userId;

    $loginResp = httpRequest('GET', $baseUrl . 'Auth', $sessionKey);
    $loginResp = httpRequest('POST', $baseUrl . 'Auth', $sessionKey, [
        'username' => $username,
        'pass' => $password,
    ]);
    $dashboardResp = httpRequest('GET', $baseUrl . 'Dashboard', $sessionKey);

    $loginOk = $dashboardResp['status'] === 200
        && stripos($dashboardResp['body'], '<title>Dashboard') !== false;

    $pageChecks = [];
    $actionChecks = [];
    $jsonChecks = [];
    $permissionMismatches = [];
    $serverErrors = [];
    $cityLeakFindings = [];

    if ($loginOk) {
        foreach ($myrepPages as $page) {
            $resp = httpRequest('GET', $baseUrl . $page, $sessionKey);
            $actualAllowed = !isNoAccessResponse($resp['status'], $resp['body']);
            $expectedAllowed = expectedPermission(
                $isSuperAdmin,
                $roles,
                $permissionSet,
                strtoupper($page),
                'VIEW'
            );

            $pass = ($actualAllowed === $expectedAllowed) && ($resp['status'] < 500);
            $pageChecks[] = [
                'page' => $page,
                'status' => $resp['status'],
                'expected_allowed' => $expectedAllowed,
                'actual_allowed' => $actualAllowed,
                'pass' => $pass,
            ];

            if (!$pass) {
                $permissionMismatches[] = [
                    'type' => 'VIEW',
                    'page' => $page,
                    'expected_allowed' => $expectedAllowed,
                    'actual_allowed' => $actualAllowed,
                    'status' => $resp['status'],
                ];
            }

            if ($resp['status'] >= 500) {
                $serverErrors[] = [
                    'type' => 'VIEW',
                    'page' => $page,
                    'status' => $resp['status'],
                ];
            }

            if (!$isSuperAdmin && in_array($page, $clusterBasedPagesForLeakCheck, true)) {
                $hasBlitarMapping = isset($citySet['BLITAR']);
                $showsBlitar = str_contains(strtoupper($resp['body']), 'BLITAR');
                if (!$hasBlitarMapping && $showsBlitar) {
                    $cityLeakFindings[] = [
                        'page' => $page,
                        'issue' => 'Non-BLITAR user can see BLITAR text in page output',
                    ];
                }
            }
        }

        foreach ($actionTests as $test) {
            $resp = httpRequest($test['method'], $baseUrl . $test['path'], $sessionKey, (array) $test['body']);
            $actualAllowed = !isNoAccessResponse($resp['status'], $resp['body']);
            $expectedAllowed = expectedPermission(
                $isSuperAdmin,
                $roles,
                $permissionSet,
                strtoupper((string) $test['module']),
                strtoupper((string) $test['action'])
            );

            $pass = ($actualAllowed === $expectedAllowed) && ($resp['status'] < 500);
            $actionChecks[] = [
                'module' => $test['module'],
                'action' => $test['action'],
                'path' => $test['path'],
                'status' => $resp['status'],
                'expected_allowed' => $expectedAllowed,
                'actual_allowed' => $actualAllowed,
                'pass' => $pass,
            ];

            if (!$pass) {
                $permissionMismatches[] = [
                    'type' => strtoupper((string) $test['action']),
                    'page' => (string) $test['module'],
                    'path' => (string) $test['path'],
                    'expected_allowed' => $expectedAllowed,
                    'actual_allowed' => $actualAllowed,
                    'status' => $resp['status'],
                ];
            }

            if ($resp['status'] >= 500) {
                $serverErrors[] = [
                    'type' => strtoupper((string) $test['action']),
                    'page' => (string) $test['module'],
                    'path' => (string) $test['path'],
                    'status' => $resp['status'],
                ];
            }
        }

        // JSON check: Checklist item table
        $checklistExpectedView = expectedPermission(
            $isSuperAdmin,
            $roles,
            $permissionSet,
            'CHECKLIST_DOKUMENT_MYREP',
            'VIEW'
        );
        $checklistResp = httpRequest(
            'POST',
            $baseUrl . 'Checklist_Dokument_MyRep/itemTableData',
            $sessionKey,
            [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'selected_city' => '',
                'selected_regional' => '',
            ]
        );
        $checklistJson = json_decode($checklistResp['body'], true);
        $checklistJsonValid = is_array($checklistJson) && array_key_exists('data', $checklistJson) && array_key_exists('recordsTotal', $checklistJson);
        $checklistAllowedActual = !isNoAccessResponse($checklistResp['status'], $checklistResp['body']);
        $jsonChecks[] = [
            'endpoint' => 'Checklist_Dokument_MyRep/itemTableData',
            'status' => $checklistResp['status'],
            'expected_allowed' => $checklistExpectedView,
            'actual_allowed' => $checklistAllowedActual,
            'json_valid' => $checklistJsonValid,
            'pass' => $checklistExpectedView ? ($checklistAllowedActual && $checklistJsonValid) : (!$checklistAllowedActual),
        ];

        // JSON check: BAK district options
        $cityForDistrict = (string) ($user['city_name'] ?? '');
        if ($cityForDistrict === '' || $cityForDistrict === '-') {
            $cityForDistrict = 'PRABUMULIH';
        }
        $bakExpectedView = expectedPermission(
            $isSuperAdmin,
            $roles,
            $permissionSet,
            'BAK_MYREP',
            'VIEW'
        );
        $bakDistrictResp = httpRequest(
            'GET',
            $baseUrl . 'BAK_MyRep/getDistrictOptions?city_name=' . rawurlencode($cityForDistrict),
            $sessionKey
        );
        $bakDistrictJson = json_decode($bakDistrictResp['body'], true);
        $bakDistrictJsonValid = is_array($bakDistrictJson) && array_key_exists('results', $bakDistrictJson);
        $bakDistrictAllowedActual = !isNoAccessResponse($bakDistrictResp['status'], $bakDistrictResp['body']);
        $jsonChecks[] = [
            'endpoint' => 'BAK_MyRep/getDistrictOptions',
            'status' => $bakDistrictResp['status'],
            'expected_allowed' => $bakExpectedView,
            'actual_allowed' => $bakDistrictAllowedActual,
            'json_valid' => $bakDistrictJsonValid,
            'pass' => $bakExpectedView ? ($bakDistrictAllowedActual && $bakDistrictJsonValid) : (!$bakDistrictAllowedActual),
        ];
    }

    $userResults[] = [
        'user' => [
            'id' => $userId,
            'nik' => $nik,
            'nama_karyawan' => $displayName,
            'username_user' => $username,
            'seeded_role' => $seedRole,
            'effective_roles' => $roles,
            'effective_cities' => array_keys($citySet),
            'is_super_admin' => $isSuperAdmin,
        ],
        'login' => [
            'ok' => $loginOk,
            'login_status' => $loginResp['status'],
            'dashboard_status' => $dashboardResp['status'],
            'login_error' => $loginResp['error'],
            'dashboard_error' => $dashboardResp['error'],
        ],
        'page_checks' => $pageChecks,
        'action_checks' => $actionChecks,
        'json_checks' => $jsonChecks,
        'permission_mismatches' => $permissionMismatches,
        'server_errors' => $serverErrors,
        'city_leak_findings' => $cityLeakFindings,
    ];

}

$summary = [
    'suite_started_at' => $suiteStartedAt,
    'suite_finished_at' => date('c'),
    'base_url' => $baseUrl,
    'db_name' => $dbName,
    'total_users_tested' => count($userResults),
    'users_login_ok' => 0,
    'total_page_checks' => 0,
    'page_passed' => 0,
    'total_action_checks' => 0,
    'action_passed' => 0,
    'total_json_checks' => 0,
    'json_passed' => 0,
    'total_permission_mismatches' => 0,
    'total_server_errors' => 0,
    'total_city_leak_findings' => 0,
    'missing_role_columns_on_mapping_table' => [],
];

foreach (['atp_ho', 'qa_ho'] as $mustHaveCol) {
    if (!isset($existingColumnSet[$mustHaveCol])) {
        $summary['missing_role_columns_on_mapping_table'][] = $mustHaveCol;
    }
}

foreach ($userResults as $ur) {
    if (!empty($ur['login']['ok'])) {
        $summary['users_login_ok']++;
    }

    foreach ((array) $ur['page_checks'] as $row) {
        $summary['total_page_checks']++;
        if (!empty($row['pass'])) {
            $summary['page_passed']++;
        }
    }

    foreach ((array) $ur['action_checks'] as $row) {
        $summary['total_action_checks']++;
        if (!empty($row['pass'])) {
            $summary['action_passed']++;
        }
    }

    foreach ((array) $ur['json_checks'] as $row) {
        $summary['total_json_checks']++;
        if (!empty($row['pass'])) {
            $summary['json_passed']++;
        }
    }

    $summary['total_permission_mismatches'] += count((array) $ur['permission_mismatches']);
    $summary['total_server_errors'] += count((array) $ur['server_errors']);
    $summary['total_city_leak_findings'] += count((array) $ur['city_leak_findings']);
}

$payload = [
    'summary' => $summary,
    'available_role_columns' => $availableRoleColumns,
    'results' => $userResults,
];

file_put_contents($resultPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$db->close();

echo "Smoke test completed.\n";
echo "Result file: {$resultPath}\n";
echo "Users tested: {$summary['total_users_tested']} | Login OK: {$summary['users_login_ok']}\n";
echo "Page pass: {$summary['page_passed']}/{$summary['total_page_checks']}\n";
echo "Action pass: {$summary['action_passed']}/{$summary['total_action_checks']}\n";
echo "JSON pass: {$summary['json_passed']}/{$summary['total_json_checks']}\n";
echo "Permission mismatches: {$summary['total_permission_mismatches']}\n";
echo "Server errors: {$summary['total_server_errors']}\n";
echo "City leak findings: {$summary['total_city_leak_findings']}\n";
