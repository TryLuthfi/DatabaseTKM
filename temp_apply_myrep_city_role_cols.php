<?php
declare(strict_types=1);

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

function fetchExists(mysqli $db, string $sql, array $params = []): bool
{
    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        throw new RuntimeException('Prepare failed: ' . $db->error);
    }
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        throw new RuntimeException('Execute failed: ' . $stmt->error);
    }
    $result = $stmt->get_result();
    $exists = false;
    if ($result !== false) {
        $row = $result->fetch_row();
        $exists = $row !== null;
        $result->free();
    }
    $stmt->close();
    return $exists;
}

$env = parseEnvFile(__DIR__ . DIRECTORY_SEPARATOR . '.env');
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

$tableExists = fetchExists(
    $db,
    'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1',
    ['tb_myrep_pic_mapping_city']
);
if (!$tableExists) {
    echo "Table tb_myrep_pic_mapping_city not found\n";
    exit(0);
}

$actions = [];

$hasAtp = fetchExists(
    $db,
    'SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1',
    ['tb_myrep_pic_mapping_city', 'atp_ho']
);
if (!$hasAtp) {
    $db->query('ALTER TABLE `tb_myrep_pic_mapping_city` ADD COLUMN `atp_ho` VARCHAR(30) NULL AFTER `snd_ho`');
    if ($db->error !== '') {
        throw new RuntimeException('Add column atp_ho failed: ' . $db->error);
    }
    $actions[] = 'ADD COLUMN atp_ho';
}

$hasQa = fetchExists(
    $db,
    'SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1',
    ['tb_myrep_pic_mapping_city', 'qa_ho']
);
if (!$hasQa) {
    $db->query('ALTER TABLE `tb_myrep_pic_mapping_city` ADD COLUMN `qa_ho` VARCHAR(30) NULL AFTER `dc_ho`');
    if ($db->error !== '') {
        throw new RuntimeException('Add column qa_ho failed: ' . $db->error);
    }
    $actions[] = 'ADD COLUMN qa_ho';
}

$hasIdxAtp = fetchExists(
    $db,
    'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
    ['tb_myrep_pic_mapping_city', 'idx_myrep_pic_mapping_atp_ho']
);
if (!$hasIdxAtp) {
    $db->query('ALTER TABLE `tb_myrep_pic_mapping_city` ADD INDEX `idx_myrep_pic_mapping_atp_ho` (`atp_ho`)');
    if ($db->error !== '') {
        throw new RuntimeException('Add index atp_ho failed: ' . $db->error);
    }
    $actions[] = 'ADD INDEX idx_myrep_pic_mapping_atp_ho';
}

$hasIdxQa = fetchExists(
    $db,
    'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
    ['tb_myrep_pic_mapping_city', 'idx_myrep_pic_mapping_qa_ho']
);
if (!$hasIdxQa) {
    $db->query('ALTER TABLE `tb_myrep_pic_mapping_city` ADD INDEX `idx_myrep_pic_mapping_qa_ho` (`qa_ho`)');
    if ($db->error !== '') {
        throw new RuntimeException('Add index qa_ho failed: ' . $db->error);
    }
    $actions[] = 'ADD INDEX idx_myrep_pic_mapping_qa_ho';
}

if (empty($actions)) {
    echo "No schema change needed\n";
} else {
    echo "Applied:\n- " . implode("\n- ", $actions) . "\n";
}

