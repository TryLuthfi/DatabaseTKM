<?php
/** Independent POCO accounting fixtures. Run: php tests/akselerasi_2026_data_test.php */
define('BASEPATH', __DIR__ . '/../system/');
class CI_Model {}
require __DIR__ . '/../application/models/MAkselerasi_2026.php';
$checks = 0;
$failures = [];
function expect($condition, $message) {
    global $checks, $failures;
    $checks++;
    if (!$condition) { $failures[] = $message; }
}
function amount($actual, $expected, $message) {
    expect(abs((float) $actual - (float) $expected) < 0.005, $message . ': ' . $actual . ' != ' . $expected);
}
function keyed($dashboard) {
    return array_column($dashboard['rows'], null, 'key');
}
function total($rows, $field) {
    return array_sum(array_column($rows, $field));
}
function claim($bowheer, $type, $achievement, $count = 1) {
    return compact('bowheer', 'type', 'achievement') + ['claim_count' => $count];
}

// Fixed source values approved by el; figures are not derived from model definitions.
$expectedPicTargets = [
    'WARDANI' => 25581923355,
    'SLAMET' => 10971019545,
    'DONNY' => 4499397148,
    'HENDRY' => 3365836000,
    'ZAENUL' => 5698830248,
    'WENDY' => 7954240864,
];
$empty = MAkselerasi_2026::buildDashboard([], [], '2026-11-17');
expect(count(MAkselerasi_2026::definitions()) === 16, '16 source target baselines preserved');
expect(count($empty['rows']) === 15, 'Combined LBS/KOMITEL display has 15 rows');
amount(total($empty['rows'], 'regular'), 29526472569, 'Regular baseline');
amount(total($empty['rows'], 'acceleration'), 28544774591, 'Acceleration baseline');
amount(total($empty['rows'], 'total'), 58071247160, 'Total locked baseline');
$picTargets = [];
foreach ($empty['rows'] as $row) {
    $picTargets[$row['pic']] = ($picTargets[$row['pic']] ?? 0) + $row['total'];
}
foreach ($expectedPicTargets as $pic => $expected) {
    amount($picTargets[$pic] ?? null, $expected, 'Frozen target PIC ' . $pic);
}

$aliases = [
    ['PT EMR - NRO', 'CLUSTER - REGIONAL 1 - LANGSA', 'morep_nro'],
    ['PT EMR - DONASI', 'DONASI', 'morep_donasi'],
    ['PT EMR - PU ( NON PPN )', 'PERIJINAN', 'morep_sop'],
    ['PT EMR - PU ( NON PPN )', 'SITAC PU', 'morep_sop'],
    ['PT IFORTE - FIBERIZATION', 'FTTT', 'iforte_fttt'],
    ['PT IFORTE - FTTH IOH', '', 'iforte_ftth'],
    ['PT IFORTE - FTTH XL', '', 'iforte_ftth'],
    ['PT IFORTE - REGULAR & CONN', '', 'iforte_reguler'],
    ['PT IFORTE - LBS RECTIFIKASI', '', 'iforte_lbs'],
    ['PT KOMITEL', 'REKTIF', 'komitel_rektif'],
    ['KOMITEL', 'REKTIF', 'komitel_rektif'],
    ['PT TELKOM AKSES - STAR', '', 'ta_star'],
    ['PT TELKOM AKSES', '', 'ta_osp'],
    ['PT PERSADA SOKKA TAMA', '', 'pst_osp'],
    ['PT XL SMART', '', 'xlsmart_osp'],
    ['PT TBG ( PERMIT )', '', 'tbg_permit'],
    ['PT MORATEL', '', 'mti_osp'],
    ['PT VGREEN ( EVCS )', 'DC20', 'vgreen_evcs'],
    ['PT VGREEN ( BSS )', '12S3P', 'vgreen_bss'],
    ['PT VGREEN', 'EVCS', 'vgreen_evcs'],
    ['PT VGREEN', 'DC30', 'vgreen_evcs'],
    ['PT VGREEN', 'BSS', 'vgreen_bss'],
    ['PT VGREEN', '6S1P', 'vgreen_bss'],
    ['  pt   vgreen  ', ' dc20 ', 'vgreen_evcs'],
];
foreach ($aliases as $alias) {
    expect(MAkselerasi_2026::resolveKey($alias[0], $alias[1]) === $alias[2], 'Source mapping ' . implode(' / ', $alias));
}
expect(MAkselerasi_2026::resolveKey('PT UNKNOWN', 'REKTIF') === null, 'Unknown bowheer cannot borrow LBS target');
expect(MAkselerasi_2026::resolveKey('PT VGREEN', '') === null, 'Generic VGREEN missing type held unmapped');
expect(MAkselerasi_2026::resolveKey('PT VGREEN', 'OTHER') === null, 'Generic VGREEN unknown type held unmapped');

// Production ledger reconciliation independently queried on 17 November 2026.
// NRO positive 1,129,282,487 less two signed corrections gives 1,097,595,364.
$claims = [
    claim('PT EMR - NRO', 'CLUSTER', 1129282487, 86),
    claim('PT EMR - NRO', 'CLUSTER', -8471549),
    claim('PT EMR - NRO', 'CLUSTER', -23215574),
    claim('PT EMR - DONASI', 'DONASI', 159315754, 14),
    claim('PT EMR - PU ( NON PPN )', 'PERIJINAN', 1973995920, 18),
    claim('PT VGREEN ( EVCS )', 'DC20', 490078040, 37),
    claim('PT VGREEN ( BSS )', '12S3P', 163472673, 47),
];
$actual = MAkselerasi_2026::buildDashboard($claims, [], '2026-11-17');
$byKey = keyed($actual);
amount($actual['source_total'], 3884457751, 'Direct ledger net total');
amount(total($actual['rows'], 'achievement'), 3884457751, 'Mapped net achievement');
amount(total($actual['rows'], 'remaining'), 54186789409, 'Remaining target');
amount(total($actual['rows'], 'regular_achievement'), 3884457751, 'All current achievement allocated regular');
amount(total($actual['rows'], 'acceleration_achievement'), 0, 'CO not yet consumed');
amount(total($actual['rows'], 'claim_count'), 204, '202 positive plus 2 negative claim entries counted once');
amount($byKey['morep_nro']['achievement'], 1097595364, 'NRO signed net');
amount($byKey['morep_sop']['achievement'], 1973995920, 'SOP signed net');
amount($byKey['morep_donasi']['achievement'], 159315754, 'DONASI signed net');
amount($byKey['vgreen_evcs']['achievement'], 490078040, 'EVCS signed net');
amount($byKey['vgreen_bss']['achievement'], 163472673, 'BSS signed net');
expect(count($actual['unmapped']) === 0, 'All current-period source categories matched');

// PIC on operational master must not transfer locked target ownership.
$ownerChanged = MAkselerasi_2026::buildDashboard($claims, [
    ['bowheer' => 'PT EMR - NRO', 'pic' => 'PIC baru'],
], '2026-11-17');
expect(keyed($ownerChanged)['morep_nro']['pic'] === 'WARDANI', 'Target owner stays frozen');

// Generic and explicit VGREEN sources share one target, without duplication.
$vgreen = MAkselerasi_2026::buildDashboard([
    claim('PT VGREEN', 'EVCS', 100), claim('PT VGREEN ( EVCS )', 'DC30', 200),
    claim('PT VGREEN', 'BSS', 300), claim('PT VGREEN ( BSS )', '6S1P', 400),
], [], '2026-11-17');
amount(keyed($vgreen)['vgreen_evcs']['achievement'], 300, 'EVCS alias union once');
amount(keyed($vgreen)['vgreen_bss']['achievement'], 700, 'BSS alias union once');

// Regular is consumed first within a bowheer/type, never across target rows.
$overflow = MAkselerasi_2026::buildDashboard([
    claim('PT EMR - DONASI', 'DONASI', 193098189),
    claim('PT TELKOM AKSES - STAR', '', 100),
], [], '2026-11-17');
$overflowRows = keyed($overflow);
amount($overflowRows['morep_donasi']['regular_achievement'], 193098089, 'Regular capped at target');
amount($overflowRows['morep_donasi']['acceleration_achievement'], 100, 'Excess to same CO target');
amount($overflowRows['ta_star']['regular_achievement'], 0, 'CO-only target has no regular consumption');
amount($overflowRows['ta_star']['acceleration_achievement'], 100, 'CO-only achievement');
amount($overflowRows['morep_nro']['achievement'], 0, 'No cross-target borrowing');
$reversed = MAkselerasi_2026::buildDashboard([
    claim('PT EMR - DONASI', 'DONASI', 193098189),
    claim('PT EMR - DONASI', 'DONASI', -200),
], [], '2026-11-17');
amount(keyed($reversed)['morep_donasi']['regular_achievement'], 193097989, 'Reversal restores regular room after CO exhausted');
amount(keyed($reversed)['morep_donasi']['acceleration_achievement'], 0, 'CO reversed first');
$negative = MAkselerasi_2026::buildDashboard([claim('PT EMR - NRO', '', -100)], [], '2026-11-17');
amount(keyed($negative)['morep_nro']['achievement'], -100, 'Negative net retained');
amount(keyed($negative)['morep_nro']['remaining'], 24520772367, 'Negative net increases remaining');

// KOMITEL future separation preserves targets, claims and original source baselines.
$futureClaims = [claim('PT IFORTE - LBS RECTIFIKASI', '', 100), claim('PT KOMITEL', 'REKTIF', 200)];
$combined = MAkselerasi_2026::buildDashboard($futureClaims, [], '2026-11-17');
$split = MAkselerasi_2026::buildDashboard($futureClaims, [['bowheer' => 'PT KOMITEL']], '2026-11-17');
expect(count($split['rows']) === 16 && $split['komitel_separate'], 'Future master splits display');
amount(total($combined['rows'], 'total'), total($split['rows'], 'total'), 'Split conserves total target');
amount(total($combined['rows'], 'achievement'), total($split['rows'], 'achievement'), 'Split conserves achievement');
amount(keyed($combined)['iforte_lbs']['acceleration'], 2181309365, 'Current combined CO');
amount(keyed($combined)['iforte_lbs']['achievement'], 300, 'Combined source attribution');
amount(keyed($split)['iforte_lbs']['regular'], 640594000, 'Original IFORTE regular preserved');
amount(keyed($split)['iforte_lbs']['acceleration'], 490902500, 'Original IFORTE CO preserved');
amount(keyed($split)['komitel_rektif']['regular'], 0, 'Original KOMITEL regular preserved');
amount(keyed($split)['komitel_rektif']['acceleration'], 1690406865, 'Original KOMITEL CO preserved');
amount(keyed($split)['iforte_lbs']['achievement'], 100, 'Separate IFORTE attribution');
amount(keyed($split)['komitel_rektif']['achievement'], 200, 'Separate KOMITEL attribution');

$unknown = MAkselerasi_2026::buildDashboard(array_merge($claims, [claim('PT UNKNOWN', 'OTHER', 123)]), [], '2026-11-17');
expect(count($unknown['unmapped']) === 1, 'Unmapped category visible');
amount($unknown['source_total'], total($unknown['rows'], 'achievement') + total($unknown['unmapped'], 'achievement'), 'Source reconciles mapped plus unmapped');
foreach (['2026-11-15', '2026-02-30', '2026-13-01', '17-11-2026', '', null, [], '2026-11-17 00:00:00'] as $badDate) {
    expect(!MAkselerasi_2026::validEndDate($badDate), 'Invalid cutoff rejected');
}
foreach (['2026-11-16', '2026-11-17', '2026-12-25', '2027-01-01'] as $goodDate) {
    expect(MAkselerasi_2026::validEndDate($goodDate), 'Flexible cutoff accepted: ' . $goodDate);
}
if ($failures) {
    echo 'FAIL ' . count($failures) . ' of ' . $checks . " independent accounting assertions\n";
    foreach ($failures as $failure) { echo '- ' . $failure . "\n"; }
    exit(1);
}
echo 'PASS ' . $checks . " independent accounting assertions\n";
