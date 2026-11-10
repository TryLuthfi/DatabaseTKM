<?php
// Run with the local XAMPP PHP and MariaDB. All writes use an isolated fixture schema.
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('ENVIRONMENT', 'testing');
function log_message($level, $message) {}
require BASEPATH . 'core/Common.php';
require BASEPATH . 'core/Model.php';
require BASEPATH . 'database/DB.php';
require APPPATH . 'models/MChecklist_Dokument_MyRep.php';

function &get_instance() { return $GLOBALS['instance']; }
function checkLinkedAstri($condition, $label)
{
    if (!$condition) {
        throw new RuntimeException($label);
    }
    echo "PASS: $label\n";
}

$admin = new mysqli('127.0.0.1', 'root', '');
$schema = 'checklist_astri_test_' . bin2hex(random_bytes(6));
$admin->query('CREATE DATABASE ' . $schema);
try {
    $db = DB([
        'hostname' => '127.0.0.1', 'username' => 'root', 'password' => '', 'database' => $schema,
        'dbdriver' => 'mysqli', 'db_debug' => false, 'pconnect' => false,
        'char_set' => 'utf8mb4', 'dbcollat' => 'utf8mb4_general_ci',
    ], true);
    $GLOBALS['instance'] = (object) [
        'db' => $db,
        'session' => new class {
            public $data = ['id_user' => 1, 'id_level' => 1, 'nama_level' => 'Super Admin'];
            public function userdata($key) { return $this->data[$key] ?? null; }
        },
    ];
    $ddl = [
        'CREATE TABLE tb_rfs_myrep_cluster (id_cluster INT PRIMARY KEY, id_target INT, cluster_name VARCHAR(100), homepass INT, status_rfs VARCHAR(20))',
        'CREATE TABLE tb_myrep_cluster (id_myrep_cluster INT PRIMARY KEY, rfs_cluster_id INT, cluster_code VARCHAR(20))',
        'CREATE TABLE tb_rfs_myrep_monthly_target (id_target INT PRIMARY KEY, year_num INT, month_num INT, city_name VARCHAR(30), regional_name VARCHAR(30), province_name VARCHAR(30), chief VARCHAR(30), id_user_pic_ho INT, rpm VARCHAR(30), sm VARCHAR(30), spv VARCHAR(30))',
        'CREATE TABLE tb_master_user_new (id INT PRIMARY KEY, nama_karyawan VARCHAR(40), telegram_user_id INT)',
        'CREATE TABLE tb_rfs_myrep_claim (cluster_id INT, claim_date DATE, status_claim VARCHAR(20))',
        'CREATE TABLE md_rfs_myrep_doc_group (id_doc_group INT PRIMARY KEY, scope_type VARCHAR(20), sow_type VARCHAR(20), is_active INT)',
        'CREATE TABLE md_rfs_myrep_doc_item (id_doc_item INT PRIMARY KEY, id_doc_group INT, doc_name VARCHAR(100), is_active INT)',
        'CREATE TABLE tb_rfs_myrep_doc_package (id_doc_package INT PRIMARY KEY, id_doc_group INT, cluster_id INT, actual_atp_date DATE)',
        "CREATE TABLE tb_rfs_myrep_doc_file (
            id_doc_file INT AUTO_INCREMENT PRIMARY KEY, id_doc_package INT NOT NULL, id_doc_item INT NOT NULL,
            status_file ENUM('NOT UPLOADED','UPLOADED','APPROVED','REJECTED') NOT NULL DEFAULT 'UPLOADED',
            file_name VARCHAR(100), file_path VARCHAR(255), astri_submitted_date DATE,
            astri_status VARCHAR(30) DEFAULT 'NY', astri_status_updated_at DATETIME, astri_remark TEXT,
            UNIQUE KEY (id_doc_package, id_doc_item))",
        'CREATE TABLE md_myrep_flow_doc_group (id_doc_group INT PRIMARY KEY, group_label VARCHAR(40), is_active INT)',
        'CREATE TABLE md_myrep_flow_doc_item (id_doc_item INT PRIMARY KEY, id_doc_group INT, doc_name VARCHAR(100), is_active INT)',
        'CREATE TABLE tb_myrep_flow_doc_package (id_doc_package INT PRIMARY KEY, id_doc_group INT, id_myrep_cluster INT, flow_type VARCHAR(20))',
        'CREATE TABLE tb_myrep_flow_doc_file (id_doc_file INT PRIMARY KEY, id_doc_package INT, id_doc_item INT, file_name VARCHAR(100), file_path VARCHAR(255), status_file VARCHAR(20))',
        'CREATE TABLE tb_myrep_po_header (id_po_header INT PRIMARY KEY, id_myrep_cluster INT, po_type VARCHAR(20), po_category VARCHAR(20), po_number VARCHAR(20), po_date DATE, status_po VARCHAR(20), created_at DATETIME)',
        'CREATE TABLE tb_myrep_po_termin (id_po_termin INT PRIMARY KEY, id_po_header INT, termin_no INT, termin_percent INT, termin_value INT, status_termin VARCHAR(20), invoice_date DATE, sertifikat_invoice_date VARCHAR(150))',
        "INSERT INTO tb_rfs_myrep_cluster VALUES (1, 1, 'Fixture', 64, 'RFS'), (2, 1, 'Other cluster', 64, 'RFS')",
        "INSERT INTO tb_rfs_myrep_monthly_target (id_target, city_name) VALUES (1, 'BITUNG')",
        "INSERT INTO tb_myrep_cluster VALUES (10, 1, 'TEST-1'), (20, 2, 'TEST-2')",
        "INSERT INTO md_rfs_myrep_doc_group VALUES (1, 'CLUSTER', 'CW ATP', 1), (2, 'SUBFEEDER', 'CW ATP', 1)",
        "INSERT INTO md_rfs_myrep_doc_item VALUES (9, 1, 'Layout SnD Kasar', 1), (10, 1, 'BAK', 0), (11, 2, 'Layout SnD Kasar', 1)",
        'INSERT INTO tb_rfs_myrep_doc_package VALUES (100, 1, 1, NULL), (200, 1, 2, NULL), (300, 2, 1, NULL)',
        "INSERT INTO md_myrep_flow_doc_group VALUES (1, 'VALIDASI SALES', 1)",
        "INSERT INTO md_myrep_flow_doc_item VALUES (1, 1, 'SND Kasar', 1)",
        "INSERT INTO tb_myrep_flow_doc_package VALUES (1, 1, 10, 'VALSAL')",
        "INSERT INTO tb_myrep_flow_doc_file VALUES (42, 1, 1, 'layout.pdf', 'source/layout.pdf', 'UPLOADED')",
        "INSERT INTO tb_myrep_po_header VALUES (1, 10, 'CLUSTER', 'INITIAL', '7400000000', NULL, NULL, NULL)",
        "INSERT INTO tb_myrep_po_termin VALUES (1, 1, 2, 25, 6788625, 'NOT READY', NULL, NULL)",
    ];
    foreach ($ddl as $sql) {
        if (!$db->query($sql)) {
            throw new RuntimeException(json_encode($db->error()));
        }
    }
    for ($itemId = 1; $itemId <= 7; $itemId++) {
        $db->insert('md_rfs_myrep_doc_item', ['id_doc_item' => $itemId, 'id_doc_group' => 1, 'doc_name' => "Item $itemId", 'is_active' => 1]);
        $db->insert('tb_rfs_myrep_doc_file', [
            'id_doc_package' => 100, 'id_doc_item' => $itemId, 'status_file' => 'APPROVED',
            'astri_status' => 'APPROVED', 'astri_submitted_date' => '2026-11-01',
        ]);
    }

    $model = new MChecklist_Dokument_MyRep();
    $approved = ['astri_status' => 'APPROVED', 'astri_submitted_date' => '2026-11-10', 'astri_remark' => 'Fixture'];
    $term = function () use ($model) { return $model->getCertificateTermRows(1, 10)[0]; };
    checkLinkedAstri($term()['astri_approved_docs'] === 7 && !$term()['is_release_ready'], 'Term 2 waits at 7 of 8');
    $target = $model->getAstriUpdateTarget(1, 'linked:100:9');
    checkLinkedAstri($target['status_file'] === 'UPLOADED' && !$model->saveAstriUpdateTarget($target, $approved), 'Unapproved linked source cannot be submitted');
    checkLinkedAstri(!$model->getAstriUpdateTarget(2, 'linked:100:9') && !$model->getAstriUpdateTarget(1, 'linked:300:11')
        && !$model->getAstriUpdateTarget(1, 'linked:100:10') && !$model->getAstriUpdateTarget(2, '1'), 'Foreign cluster, scope, inactive item and ordinary foreign ID denied');

    $db->where('id_doc_file', 42)->update('tb_myrep_flow_doc_file', ['status_file' => 'APPROVED']);
    $source = $db->get_where('tb_myrep_flow_doc_file', ['id_doc_file' => 42])->row_array();
    $target = $model->getAstriUpdateTarget(1, 'linked:100:9');
    $review = array_replace($approved, ['astri_status' => 'ON REVIEW']);
    checkLinkedAstri($model->saveAstriUpdateTarget($target, $review) && $term()['astri_submitted_docs'] === 8
        && !$term()['is_release_ready'], 'First linked save creates Checklist metadata and counts as submitted');
    checkLinkedAstri($model->saveAstriUpdateTarget($target, $approved) && $term()['astri_approved_docs'] === 8
        && $term()['is_release_ready'], 'Approved linked item takes Term 2 to 8 of 8 and enables release');
    $row = $db->get_where('tb_rfs_myrep_doc_file', ['id_doc_package' => 100, 'id_doc_item' => 9])->row_array();
    checkLinkedAstri($row['file_path'] === null && $row['status_file'] === 'NOT UPLOADED'
        && $row['astri_remark'] === 'Fixture' && $row['astri_status_updated_at'] !== null, 'Metadata row has no copy of the source file');
    $reset = ['astri_status' => 'NY', 'astri_submitted_date' => null, 'astri_remark' => ''];
    checkLinkedAstri($model->saveAstriUpdateTarget($target, $reset) && !$term()['is_release_ready'], 'Reset removes certificate readiness');
    $rows = $db->get_where('tb_rfs_myrep_doc_file', ['id_doc_package' => 100, 'id_doc_item' => 9])->result_array();
    checkLinkedAstri(count($rows) === 1 && $rows[0]['id_doc_file'] === $row['id_doc_file']
        && $rows[0]['astri_submitted_date'] === null && $rows[0]['astri_status_updated_at'] === null, 'Repeated writes retain the original row without duplicates');
    $legacyFields = ['file_path' => 'legacy/layout.pdf', 'file_name' => 'legacy.pdf', 'status_file' => 'REJECTED'];
    $db->where('id_doc_file', $row['id_doc_file'])->update('tb_rfs_myrep_doc_file', $legacyFields);
    checkLinkedAstri($model->saveAstriUpdateTarget($target, $approved)
        && array_intersect_key($db->get_where('tb_rfs_myrep_doc_file', ['id_doc_file' => $row['id_doc_file']])->row_array(), $legacyFields) == $legacyFields,
        'Linked update preserves a pre-existing Checklist file and internal status');
    checkLinkedAstri($source === $db->get_where('tb_myrep_flow_doc_file', ['id_doc_file' => 42])->row_array(), 'VALSAL source record preserved');
    $ordinary = $model->getAstriUpdateTarget(1, '1');
    checkLinkedAstri($ordinary && $model->saveAstriUpdateTarget($ordinary, $review)
        && $model->getFileById(1)['astri_status'] === 'ON REVIEW', 'Ordinary numeric Checklist target still updates');
    $GLOBALS['instance']->session->data = ['id_user' => 2, 'id_level' => 3];
    checkLinkedAstri(!$model->getAstriUpdateTarget(1, 'linked:100:9'), 'City access check applies to linked targets');
} finally {
    $admin->query('DROP DATABASE ' . $schema);
}
