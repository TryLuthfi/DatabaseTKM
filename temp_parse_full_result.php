<?php
$data = json_decode(file_get_contents('tmp_myrep_full_test_result.json'), true);
foreach (($data['results'] ?? []) as $r) {
  $user = $r['user']['username_user'] ?? '';
  $role = implode(',', $r['user']['effective_roles'] ?? []);
  foreach (($r['action_checks'] ?? []) as $a) {
    if (empty($a['pass'])) {
      echo "ACTION_FAIL USER={$user} ROLE={$role} MODULE={$a['module']} ACTION={$a['action']} STATUS={$a['status']} EXP=" . ($a['expected_allowed'] ? '1' : '0') . " ACT=" . ($a['actual_allowed'] ? '1' : '0') . " PATH={$a['path']}\n";
    }
  }
  foreach (($r['json_checks'] ?? []) as $j) {
    if (empty($j['pass'])) {
      echo "JSON_FAIL USER={$user} ROLE={$role} ENDPOINT={$j['endpoint']} STATUS={$j['status']} EXP=" . ($j['expected_allowed'] ? '1' : '0') . " ACT=" . ($j['actual_allowed'] ? '1' : '0') . " VALID=" . ($j['json_valid'] ? '1' : '0') . "\n";
    }
  }
}
?>
