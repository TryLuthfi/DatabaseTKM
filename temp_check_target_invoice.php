<?php
$env = parse_ini_file('d:/XAMPP/htdocs/DatabaseTKM/.env');
$mysqli = @new mysqli($env['HOSTNAME'], $env['USERNAME'], $env['PASSWORD'], $env['DATABASE']);
if ($mysqli->connect_errno) {
    fwrite(STDOUT, 'CONNECT_ERR: ' . $mysqli->connect_error . PHP_EOL);
    exit(1);
}

$q1 = "SELECT COUNT(*) AS cnt FROM tb_target_invoice WHERE id_bowheer=1 AND area_target='BANDUNG'";
$r1 = $mysqli->query($q1);
$row1 = $r1 ? $r1->fetch_assoc() : ['cnt' => null];

echo 'COUNT_BANDUNG_BOWHEER1=' . $row1['cnt'] . PHP_EOL;

$q2 = "SELECT id_bowheer, area_target, COUNT(*) AS row_count
       FROM tb_target_invoice
       GROUP BY id_bowheer, area_target
       HAVING COUNT(*) <> 58
       ORDER BY id_bowheer, area_target";
$r2 = $mysqli->query($q2);
if (!$r2) {
    fwrite(STDOUT, 'QUERY_ERR: ' . $mysqli->error . PHP_EOL);
    exit(1);
}
$rows = [];
while ($x = $r2->fetch_assoc()) { $rows[] = $x; }
echo 'TOTAL_NOT_58=' . count($rows) . PHP_EOL;
foreach ($rows as $x) {
    echo $x['id_bowheer'] . "\t" . $x['area_target'] . "\t" . $x['row_count'] . PHP_EOL;
}
$mysqli->close();
?>
