<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Asia/Jakarta');

$rootPath = __DIR__;
$envPath = $rootPath . DIRECTORY_SEPARATOR . '.env';
$resultPath = $rootPath . DIRECTORY_SEPARATOR . 'tmp_myrep_smoke_light_result.json';
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
            return ['status' => 0, 'body' => '', 'error' => 'curl_init failed'];
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => '',
            CURLOPT_COOKIEFILE => '',
            CURLOPT_HTTPHEADER => ['User-Agent: MyRep-SmokeLight/1.0'],
        ]);
        $sessions[$sessionKey] = $ch;
    }

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
    return ['status' => $status, 'body' => $body, 'error' => $error];
}

function expectedPermission(bool $isSuperAdmin, array $roles, array $permissionSet, string $page, string $action): bool
{
    if ($isSuperAdmin) {
        return true;
    }
    foreach ($roles as $role) {
        $k = strtoupper($page) . '|' . strtoupper($action) . '|' . strtoupper($role);
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
    return str_contains($bodyUpper, 'YOU ARE NOT ALLOWED TO ENTER HERE');
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

$columnRows = dbFetchAll($db, "SHOW COLUMNS FROM tb_myrep_pic_mapping_city");
$existingColumns = array_map(static fn(array $r): string => (string) ($r['Field'] ?? ''), $columnRows);
$existingColumnSet = array_fill_keys($existingColumns, true);

$masterUserColumnRows = dbFetchAll($db, "SHOW COLUMNS FROM tb_master_user_new");
$masterUserColumns = array_map(static fn(array $r): string => (string) ($r['Field'] ?? ''), $masterUserColumnRows);
$masterUserColumnSet = array_fill_keys($masterUserColumns, true);

$columnRoleMap = [
    'rpm_area' => 'RPM_AREA',
    'admin_area' => 'ADMIN_AREA',
    'snd_ho' => 'SND_HO',
    'atp_ho' => 'ATP_HO',
    'qa_ho' => 'QA_HO',
];
$availableRoleColumns = [];
foreach ($columnRoleMap as $col => $roleKey) {
    if (isset($existingColumnSet[$col])) {
        $availableRoleColumns[$col] = $roleKey;
    }
}

$mappingSelectCols = array_merge(['city_name'], array_keys($availableRoleColumns));
$mappingRows = dbFetchAll(
    $db,
    "SELECT " . implode(', ', $mappingSelectCols) . " FROM tb_myrep_pic_mapping_city WHERE IFNULL(is_active,1)=1"
);

$testUsers = [];
foreach ($availableRoleColumns as $col => $roleKey) {
    $rows = dbFetchAll(
        $db,
        "SELECT '{$roleKey}' AS seeded_role, m.city_name, u.id, u.nik, u.nama_karyawan, u.username_user, u.password_user
         FROM tb_myrep_pic_mapping_city m
         JOIN tb_master_user_new u ON u.nik = m.{$col}
         WHERE m.{$col} IS NOT NULL
           AND m.{$col} <> ''
           AND IFNULL(m.is_active,1) = 1
           AND UPPER(IFNULL(u.status_user,'')) = 'ACTIVE'
           AND u.username_user IS NOT NULL AND u.username_user <> ''
         LIMIT 1"
    );
    if (!empty($rows)) {
        $testUsers[] = $rows[0];
    }
}

$superAdminSql = '';
if (isset($masterUserColumnSet['level_user'])) {
    $superAdminSql = "
        SELECT 'SUPER_ADMIN' AS seeded_role, '-' AS city_name, u.id, u.nik, u.nama_karyawan, u.username_user, u.password_user
        FROM tb_master_user_new u
        WHERE UPPER(IFNULL(u.status_user,'')) = 'ACTIVE'
          AND CAST(IFNULL(u.level_user, '0') AS CHAR) = '1'
          AND u.username_user IS NOT NULL AND u.username_user <> ''
        LIMIT 1
    ";
} elseif (isset($masterUserColumnSet['nama_level'])) {
    $superAdminSql = "
        SELECT 'SUPER_ADMIN' AS seeded_role, '-' AS city_name, u.id, u.nik, u.nama_karyawan, u.username_user, u.password_user
        FROM tb_master_user_new u
        WHERE UPPER(IFNULL(u.status_user,'')) = 'ACTIVE'
          AND UPPER(IFNULL(u.nama_level, '')) = 'SUPER ADMIN'
          AND u.username_user IS NOT NULL AND u.username_user <> ''
        LIMIT 1
    ";
}

$superAdminRows = [];
if ($superAdminSql !== '') {
    $superAdminRows = dbFetchAll($db, $superAdminSql);
}
if (!empty($superAdminRows)) {
    $testUsers[] = $superAdminRows[0];
}

$seen = [];
$dedup = [];
foreach ($testUsers as $u) {
    $id = (int) ($u['id'] ?? 0);
    if ($id <= 0 || isset($seen[$id])) {
        continue;
    }
    $seen[$id] = true;
    $dedup[] = $u;
}
$testUsers = $dedup;

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

$pages = [
    'MyRepublik_Project',
    'BAK_MyRep',
    'VALSAL_MyRep',
    'PO_MyRep',
    'Checklist_Dokument_MyRep',
];

$suiteStartedAt = date('c');
$results = [];

foreach ($testUsers as $user) {
    $userId = (int) ($user['id'] ?? 0);
    $nik = trim((string) ($user['nik'] ?? ''));
    $username = (string) ($user['username_user'] ?? '');
    $password = (string) ($user['password_user'] ?? '');
    $seedRole = strtoupper((string) ($user['seeded_role'] ?? ''));
    $isSuperAdmin = ($seedRole === 'SUPER_ADMIN');

    $roles = [];
    $citySet = [];
    if ($isSuperAdmin) {
        $roles[] = 'SUPER_ADMIN';
    } else {
        foreach ($mappingRows as $row) {
            $matched = false;
            foreach ($availableRoleColumns as $col => $roleKey) {
                if (trim((string) ($row[$col] ?? '')) === $nik) {
                    $matched = true;
                    $roles[strtoupper($roleKey)] = true;
                }
            }
            if ($matched) {
                $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
                if ($cityName !== '') {
                    $citySet[$cityName] = true;
                }
            }
        }
        $roles = array_keys($roles);
        sort($roles);
    }

    $sessionKey = 'light_' . safeUsername($username) . '_' . $userId;
    httpRequest('GET', $baseUrl . 'Auth', $sessionKey);
    httpRequest('POST', $baseUrl . 'Auth', $sessionKey, ['username' => $username, 'pass' => $password]);
    $dashboardResp = httpRequest('GET', $baseUrl . 'Dashboard', $sessionKey);
    $loginOk = $dashboardResp['status'] === 200 && stripos($dashboardResp['body'], '<title>Dashboard') !== false;

    $pageChecks = [];
    $jsonChecks = [];
    $cityLeakFindings = [];
    $permissionMismatches = [];

    if ($loginOk) {
        foreach ($pages as $page) {
            $resp = httpRequest('GET', $baseUrl . $page, $sessionKey);
            $expectedAllowed = expectedPermission($isSuperAdmin, $roles, $permissionSet, $page, 'VIEW');
            $actualAllowed = !isNoAccessResponse($resp['status'], $resp['body']);
            $pass = ($expectedAllowed === $actualAllowed) && $resp['status'] < 500;
            $pageChecks[] = [
                'page' => $page,
                'status' => $resp['status'],
                'expected_allowed' => $expectedAllowed,
                'actual_allowed' => $actualAllowed,
                'pass' => $pass,
            ];
            if (!$pass) {
                $permissionMismatches[] = ['page' => $page, 'status' => $resp['status'], 'expected' => $expectedAllowed, 'actual' => $actualAllowed];
            }
            if (!$isSuperAdmin && !isset($citySet['BLITAR']) && in_array($page, ['BAK_MyRep', 'VALSAL_MyRep', 'PO_MyRep'], true)) {
                if (str_contains(strtoupper($resp['body']), 'BLITAR')) {
                    $cityLeakFindings[] = ['page' => $page, 'issue' => 'BLITAR appears for non-BLITAR mapped user'];
                }
            }
        }

        $checklistExpected = expectedPermission($isSuperAdmin, $roles, $permissionSet, 'Checklist_Dokument_MyRep', 'VIEW');
        $checklistResp = httpRequest('POST', $baseUrl . 'Checklist_Dokument_MyRep/itemTableData', $sessionKey, [
            'draw' => 1,
            'start' => 0,
            'length' => 5,
            'selected_city' => '',
            'selected_regional' => '',
        ]);
        $checklistJson = json_decode($checklistResp['body'], true);
        $checklistValid = is_array($checklistJson) && array_key_exists('data', $checklistJson);
        $checklistAllowed = !isNoAccessResponse($checklistResp['status'], $checklistResp['body']);
        $jsonChecks[] = [
            'endpoint' => 'Checklist_Dokument_MyRep/itemTableData',
            'status' => $checklistResp['status'],
            'json_valid' => $checklistValid,
            'expected_allowed' => $checklistExpected,
            'actual_allowed' => $checklistAllowed,
            'pass' => $checklistExpected ? ($checklistAllowed && $checklistValid) : (!$checklistAllowed),
        ];

        $cityForDistrict = trim((string) ($user['city_name'] ?? ''));
        if ($cityForDistrict === '' || $cityForDistrict === '-') {
            $cityForDistrict = 'PRABUMULIH';
        }
        $bakExpected = expectedPermission($isSuperAdmin, $roles, $permissionSet, 'BAK_MyRep', 'VIEW');
        $districtResp = httpRequest('GET', $baseUrl . 'BAK_MyRep/getDistrictOptions?city_name=' . rawurlencode($cityForDistrict), $sessionKey);
        $districtJson = json_decode($districtResp['body'], true);
        $districtValid = is_array($districtJson) && array_key_exists('results', $districtJson);
        $districtAllowed = !isNoAccessResponse($districtResp['status'], $districtResp['body']);
        $jsonChecks[] = [
            'endpoint' => 'BAK_MyRep/getDistrictOptions',
            'status' => $districtResp['status'],
            'json_valid' => $districtValid,
            'expected_allowed' => $bakExpected,
            'actual_allowed' => $districtAllowed,
            'pass' => $bakExpected ? ($districtAllowed && $districtValid) : (!$districtAllowed),
        ];
    }

    $results[] = [
        'user' => [
            'id' => $userId,
            'nama_karyawan' => (string) ($user['nama_karyawan'] ?? ''),
            'username_user' => $username,
            'seeded_role' => $seedRole,
            'effective_roles' => $roles,
            'effective_cities' => array_keys($citySet),
            'is_super_admin' => $isSuperAdmin,
        ],
        'login' => [
            'ok' => $loginOk,
            'dashboard_status' => $dashboardResp['status'],
            'dashboard_error' => $dashboardResp['error'],
        ],
        'page_checks' => $pageChecks,
        'json_checks' => $jsonChecks,
        'permission_mismatches' => $permissionMismatches,
        'city_leak_findings' => $cityLeakFindings,
    ];
}

$summary = [
    'suite_started_at' => $suiteStartedAt,
    'suite_finished_at' => date('c'),
    'base_url' => $baseUrl,
    'db_name' => $dbName,
    'tested_users' => count($results),
    'tested_role_columns' => array_keys($availableRoleColumns),
    'missing_role_columns_on_mapping_table' => array_values(array_diff(array_keys($columnRoleMap), array_keys($availableRoleColumns))),
    'page_total' => 0,
    'page_passed' => 0,
    'json_total' => 0,
    'json_passed' => 0,
    'login_ok' => 0,
    'permission_mismatches' => 0,
    'city_leak_findings' => 0,
];

foreach ($results as $row) {
    if (!empty($row['login']['ok'])) {
        $summary['login_ok']++;
    }
    foreach ((array) $row['page_checks'] as $check) {
        $summary['page_total']++;
        if (!empty($check['pass'])) {
            $summary['page_passed']++;
        }
    }
    foreach ((array) $row['json_checks'] as $check) {
        $summary['json_total']++;
        if (!empty($check['pass'])) {
            $summary['json_passed']++;
        }
    }
    $summary['permission_mismatches'] += count((array) ($row['permission_mismatches'] ?? []));
    $summary['city_leak_findings'] += count((array) ($row['city_leak_findings'] ?? []));
}

$output = [
    'summary' => $summary,
    'results' => $results,
];
file_put_contents($resultPath, json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "MyRep smoke light finished\n";
echo "Users login OK: {$summary['login_ok']}/{$summary['tested_users']}\n";
echo "Page pass: {$summary['page_passed']}/{$summary['page_total']}\n";
echo "JSON pass: {$summary['json_passed']}/{$summary['json_total']}\n";
echo "Permission mismatches: {$summary['permission_mismatches']}\n";
echo "City leak findings: {$summary['city_leak_findings']}\n";
echo "Missing role columns: " . (empty($summary['missing_role_columns_on_mapping_table']) ? '-' : implode(', ', $summary['missing_role_columns_on_mapping_table'])) . "\n";
echo "Result file: {$resultPath}\n";
