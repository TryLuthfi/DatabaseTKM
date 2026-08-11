<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MPO_Monitor extends CI_Model
{
    public function ensureStandaloneSchema()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_bowheer_po` (
            `id_bowheer` int(11) NOT NULL AUTO_INCREMENT,
            `no_urut` int(11) DEFAULT NULL,
            `pic` varchar(100) DEFAULT NULL,
            `bowheer` varchar(150) NOT NULL,
            `bowheer_key` varchar(180) NOT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            PRIMARY KEY (`id_bowheer`),
            UNIQUE KEY `uk_tb_bowheer_po_key` (`bowheer_key`),
            KEY `idx_tb_bowheer_po_pic` (`pic`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_po_import_batch` (
            `id_batch` int(11) NOT NULL AUTO_INCREMENT,
            `source_file` varchar(255) NOT NULL,
            `imported_at` datetime DEFAULT current_timestamp(),
            `imported_by` int(11) DEFAULT NULL,
            `row_count` int(11) DEFAULT 0,
            `total_effective` decimal(18,2) DEFAULT 0.00,
            `total_invoiced` decimal(18,2) DEFAULT 0.00,
            `total_target_2026` decimal(18,2) DEFAULT 0.00,
            `total_carry_2027` decimal(18,2) DEFAULT 0.00,
            `status` varchar(30) DEFAULT 'COMMITTED',
            `notes` text DEFAULT NULL,
            PRIMARY KEY (`id_batch`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_po_term_claim` (
            `id_claim` int(11) NOT NULL AUTO_INCREMENT,
            `id_term` int(11) NOT NULL,
            `id_allocation` int(11) DEFAULT NULL,
            `invoice_date` date DEFAULT NULL,
            `invoice_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
            `claim_source` varchar(30) DEFAULT 'MANUAL',
            `source_raw` varchar(100) DEFAULT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            `created_by` int(11) DEFAULT NULL,
            PRIMARY KEY (`id_claim`),
            KEY `idx_tb_po_term_claim_id_term` (`id_term`),
            KEY `idx_tb_po_term_claim_invoice_date` (`invoice_date`),
            CONSTRAINT `fk_tb_po_term_claim_id_term` FOREIGN KEY (`id_term`) REFERENCES `tb_po_term` (`id_term`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_po_term_allocation` (
            `id_allocation` int(11) NOT NULL AUTO_INCREMENT,
            `id_term` int(11) NOT NULL,
            `no_po_sub` varchar(150) DEFAULT NULL,
            `regional` varchar(150) DEFAULT NULL,
            `kota_po` varchar(150) DEFAULT NULL,
            `detail_po` text NULL,
            `remarks` text NULL,
            `allocation_value` decimal(18,2) DEFAULT 0.00,
            `plan_amount` decimal(18,2) DEFAULT 0.00,
            `submit_raw` varchar(50) DEFAULT NULL,
            `target_year` int(11) DEFAULT NULL,
            `target_week` int(11) DEFAULT NULL,
            `target_week_start` date DEFAULT NULL,
            `target_week_end` date DEFAULT NULL,
            `target_status` varchar(30) DEFAULT 'OPEN',
            `invoice_date` date DEFAULT NULL,
            `outstanding_amount` decimal(18,2) DEFAULT 0.00,
            `source_row_no` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            PRIMARY KEY (`id_allocation`),
            KEY `idx_tb_po_term_allocation_id_term` (`id_term`),
            KEY `idx_tb_po_term_allocation_target` (`target_year`, `target_week`),
            KEY `idx_tb_po_term_allocation_status` (`target_status`),
            KEY `idx_tb_po_term_allocation_sub` (`no_po_sub`),
            CONSTRAINT `fk_tb_po_term_allocation_id_term` FOREIGN KEY (`id_term`) REFERENCES `tb_po_term` (`id_term`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_po_target_pipeline` (
            `id_pipeline` int(11) NOT NULL AUTO_INCREMENT,
            `id_bowheer` int(11) DEFAULT NULL,
            `dashboard_bowheer` varchar(150) NOT NULL,
            `status_po` varchar(30) DEFAULT 'NY PO',
            `regional` varchar(150) DEFAULT NULL,
            `kota_po` varchar(150) DEFAULT NULL,
            `detail_po` text NULL,
            `remarks` text NULL,
            `type_project` varchar(150) DEFAULT NULL,
            `po_date` date DEFAULT NULL,
            `po_term` varchar(50) DEFAULT NULL,
            `term_index` int(11) DEFAULT NULL,
            `plan_amount` decimal(18,2) DEFAULT 0.00,
            `submit_raw` varchar(50) DEFAULT NULL,
            `target_year` int(11) DEFAULT NULL,
            `target_week` int(11) DEFAULT NULL,
            `target_week_start` date DEFAULT NULL,
            `target_week_end` date DEFAULT NULL,
            `target_status` varchar(30) DEFAULT 'OPEN',
            `ny_po_2026_amount` decimal(18,2) DEFAULT 0.00,
            `ny_po_2027_amount` decimal(18,2) DEFAULT 0.00,
            `source_file` varchar(255) DEFAULT NULL,
            `source_row_no` int(11) DEFAULT NULL,
            `import_batch_id` int(11) DEFAULT NULL,
            `source_hash` varchar(64) DEFAULT NULL,
            `linked_id_po` int(11) DEFAULT NULL,
            `linked_po_number` varchar(100) DEFAULT NULL,
            `pipeline_status` varchar(30) DEFAULT 'OPEN',
            `converted_at` datetime DEFAULT NULL,
            `converted_by` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            PRIMARY KEY (`id_pipeline`),
            KEY `idx_tb_po_target_pipeline_bowheer` (`dashboard_bowheer`),
            KEY `idx_tb_po_target_pipeline_target` (`target_year`, `target_week`),
            KEY `idx_tb_po_target_pipeline_status` (`target_status`),
            KEY `idx_tb_po_target_pipeline_batch` (`import_batch_id`),
            KEY `idx_tb_po_target_pipeline_linked` (`linked_id_po`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_po_dashboard_cache` (
            `id_cache` int(11) NOT NULL AUTO_INCREMENT,
            `import_batch_id` int(11) DEFAULT NULL,
            `pic` varchar(100) DEFAULT NULL,
            `bowheer` varchar(150) NOT NULL,
            `sort_order` int(11) DEFAULT 999,
            `all_po` decimal(18,2) DEFAULT 0.00,
            `all_invoice` decimal(18,2) DEFAULT 0.00,
            `done_inv_2026` decimal(18,2) DEFAULT 0.00,
            `outs_2026_on_target` decimal(18,2) DEFAULT 0.00,
            `ny_po_on_target_2026` decimal(18,2) DEFAULT 0.00,
            `grandtotal_target` decimal(18,2) DEFAULT 0.00,
            `ny_po_total` decimal(18,2) DEFAULT 0.00,
            `co_to_2027` decimal(18,2) DEFAULT 0.00,
            `total_outs` decimal(18,2) DEFAULT 0.00,
            `has_data` tinyint(1) DEFAULT 0,
            `updated_at` datetime DEFAULT current_timestamp(),
            PRIMARY KEY (`id_cache`),
            UNIQUE KEY `uk_tb_po_dashboard_cache_bowheer` (`bowheer`),
            KEY `idx_tb_po_dashboard_cache_sort` (`sort_order`),
            KEY `idx_tb_po_dashboard_cache_batch` (`import_batch_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_po_comparison_target_lock` (
            `id_lock` int(11) NOT NULL AUTO_INCREMENT,
            `id_bowheer` int(11) NOT NULL,
            `group_by` varchar(10) NOT NULL DEFAULT 'month',
            `period_key` varchar(20) NOT NULL,
            `locked_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
            `raw_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
            `deviasi_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
            `locked_at` datetime DEFAULT current_timestamp(),
            PRIMARY KEY (`id_lock`),
            UNIQUE KEY `uk_tb_po_comparison_target_lock` (`id_bowheer`, `group_by`, `period_key`),
            KEY `idx_tb_po_comparison_target_lock_period` (`group_by`, `period_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->addColumnIfMissing('tb_po', 'source_file', "ALTER TABLE `tb_po` ADD COLUMN `source_file` varchar(255) DEFAULT NULL");
        $this->addColumnIfMissing('tb_po', 'source_row_no', "ALTER TABLE `tb_po` ADD COLUMN `source_row_no` int(11) DEFAULT NULL");
        $this->addColumnIfMissing('tb_po', 'source_hash', "ALTER TABLE `tb_po` ADD COLUMN `source_hash` varchar(64) DEFAULT NULL");
        $this->addColumnIfMissing('tb_po', 'import_batch_id', "ALTER TABLE `tb_po` ADD COLUMN `import_batch_id` int(11) DEFAULT NULL");
        $this->addColumnIfMissing('tb_po', 'status_po', "ALTER TABLE `tb_po` ADD COLUMN `status_po` varchar(30) DEFAULT 'ON PO'");
        $this->addColumnIfMissing('tb_po', 'dashboard_bowheer', "ALTER TABLE `tb_po` ADD COLUMN `dashboard_bowheer` varchar(150) DEFAULT NULL");
        $this->addColumnIfMissing('tb_po', 'type_project', "ALTER TABLE `tb_po` ADD COLUMN `type_project` varchar(150) DEFAULT NULL");
        $this->addColumnIfMissing('tb_po', 'dashboard_all_invoice', "ALTER TABLE `tb_po` ADD COLUMN `dashboard_all_invoice` decimal(18,2) DEFAULT 0.00");
        $this->addColumnIfMissing('tb_po', 'dashboard_invoice_2026', "ALTER TABLE `tb_po` ADD COLUMN `dashboard_invoice_2026` decimal(18,2) DEFAULT 0.00");
        $this->addColumnIfMissing('tb_po', 'dashboard_outs_2026', "ALTER TABLE `tb_po` ADD COLUMN `dashboard_outs_2026` decimal(18,2) DEFAULT 0.00");
        $this->addColumnIfMissing('tb_po', 'dashboard_co_2027', "ALTER TABLE `tb_po` ADD COLUMN `dashboard_co_2027` decimal(18,2) DEFAULT 0.00");
        $this->addColumnIfMissing('tb_po_term_claim', 'id_allocation', "ALTER TABLE `tb_po_term_claim` ADD COLUMN `id_allocation` int(11) DEFAULT NULL AFTER `id_term`");
        $this->addColumnIfMissing('tb_po_term_claim', 'claim_source', "ALTER TABLE `tb_po_term_claim` ADD COLUMN `claim_source` varchar(30) DEFAULT 'MANUAL'");
        $this->addColumnIfMissing('tb_po_term_allocation', 'regional', "ALTER TABLE `tb_po_term_allocation` ADD COLUMN `regional` varchar(150) DEFAULT NULL AFTER `no_po_sub`");
        $this->addColumnIfMissing('tb_po_term_allocation', 'remarks', "ALTER TABLE `tb_po_term_allocation` ADD COLUMN `remarks` text NULL AFTER `detail_po`");
        $this->addColumnIfMissing('tb_po_term_claim', 'source_raw', "ALTER TABLE `tb_po_term_claim` ADD COLUMN `source_raw` varchar(100) DEFAULT NULL");
        $this->addColumnIfMissing('tb_po_term_claim', 'created_by', "ALTER TABLE `tb_po_term_claim` ADD COLUMN `created_by` int(11) DEFAULT NULL");
        $this->addColumnIfMissing('tb_po_target_pipeline', 'regional', "ALTER TABLE `tb_po_target_pipeline` ADD COLUMN `regional` varchar(150) DEFAULT NULL AFTER `status_po`");
        $this->addColumnIfMissing('tb_po_target_pipeline', 'remarks', "ALTER TABLE `tb_po_target_pipeline` ADD COLUMN `remarks` text NULL AFTER `detail_po`");
        $this->addColumnIfMissing('tb_po_target_pipeline', 'type_project', "ALTER TABLE `tb_po_target_pipeline` ADD COLUMN `type_project` varchar(150) DEFAULT NULL AFTER `remarks`");
        $this->addColumnIfMissing('tb_po_target_pipeline', 'linked_id_po', "ALTER TABLE `tb_po_target_pipeline` ADD COLUMN `linked_id_po` int(11) DEFAULT NULL AFTER `source_hash`");
        $this->addColumnIfMissing('tb_po_target_pipeline', 'linked_po_number', "ALTER TABLE `tb_po_target_pipeline` ADD COLUMN `linked_po_number` varchar(100) DEFAULT NULL AFTER `linked_id_po`");
        $this->addColumnIfMissing('tb_po_target_pipeline', 'pipeline_status', "ALTER TABLE `tb_po_target_pipeline` ADD COLUMN `pipeline_status` varchar(30) DEFAULT 'OPEN' AFTER `linked_po_number`");
        $this->addColumnIfMissing('tb_po_target_pipeline', 'converted_at', "ALTER TABLE `tb_po_target_pipeline` ADD COLUMN `converted_at` datetime DEFAULT NULL AFTER `pipeline_status`");
        $this->addColumnIfMissing('tb_po_target_pipeline', 'converted_by', "ALTER TABLE `tb_po_target_pipeline` ADD COLUMN `converted_by` int(11) DEFAULT NULL AFTER `converted_at`");
        $this->addColumnIfMissing('tb_po_term', 'plan_amount', "ALTER TABLE `tb_po_term` ADD COLUMN `plan_amount` decimal(18,2) DEFAULT 0.00");
        $this->addColumnIfMissing('tb_po_term', 'submit_raw', "ALTER TABLE `tb_po_term` ADD COLUMN `submit_raw` varchar(50) DEFAULT NULL");
        $this->addColumnIfMissing('tb_po_term', 'target_year', "ALTER TABLE `tb_po_term` ADD COLUMN `target_year` int(11) DEFAULT NULL");
        $this->addColumnIfMissing('tb_po_term', 'target_week', "ALTER TABLE `tb_po_term` ADD COLUMN `target_week` int(11) DEFAULT NULL");
        $this->addColumnIfMissing('tb_po_term', 'target_week_start', "ALTER TABLE `tb_po_term` ADD COLUMN `target_week_start` date DEFAULT NULL");
        $this->addColumnIfMissing('tb_po_term', 'target_week_end', "ALTER TABLE `tb_po_term` ADD COLUMN `target_week_end` date DEFAULT NULL");
        $this->addColumnIfMissing('tb_po_term', 'target_status', "ALTER TABLE `tb_po_term` ADD COLUMN `target_status` varchar(30) DEFAULT 'OPEN'");
        $this->addColumnIfMissing('tb_po_term', 'invoice_date', "ALTER TABLE `tb_po_term` ADD COLUMN `invoice_date` date DEFAULT NULL");
        $this->modifyColumnIfDifferent('tb_po_term', 'percent', 'decimal(7,2)', "ALTER TABLE `tb_po_term` MODIFY COLUMN `percent` decimal(7,2) NOT NULL DEFAULT 0.00");

        $this->dropIndexIfExists('tb_po', 'uk_tb_po_po_number');
        $this->addIndexIfMissing('tb_po', 'idx_tb_po_source_hash', "ALTER TABLE `tb_po` ADD KEY `idx_tb_po_source_hash` (`source_hash`)");
        $this->addIndexIfMissing('tb_po', 'idx_tb_po_number_bowheer', "ALTER TABLE `tb_po` ADD KEY `idx_tb_po_number_bowheer` (`po_number`, `id_bowheer`)");
        $this->addIndexIfMissing('tb_po', 'idx_tb_po_status_date', "ALTER TABLE `tb_po` ADD KEY `idx_tb_po_status_date` (`status_po`, `po_date`)");
        $this->addIndexIfMissing('tb_po', 'idx_tb_po_dashboard_bowheer', "ALTER TABLE `tb_po` ADD KEY `idx_tb_po_dashboard_bowheer` (`dashboard_bowheer`)");
        $this->addIndexIfMissing('tb_po_term', 'idx_tb_po_term_po_term', "ALTER TABLE `tb_po_term` ADD KEY `idx_tb_po_term_po_term` (`id_po`, `term_index`)");
        $this->addIndexIfMissing('tb_po_term', 'idx_tb_po_term_target_week', "ALTER TABLE `tb_po_term` ADD KEY `idx_tb_po_term_target_week` (`target_year`, `target_week`)");
        $this->addIndexIfMissing('tb_po_term', 'idx_tb_po_term_status', "ALTER TABLE `tb_po_term` ADD KEY `idx_tb_po_term_status` (`target_status`)");
        $this->addIndexIfMissing('tb_po_term_claim', 'idx_tb_po_term_claim_allocation', "ALTER TABLE `tb_po_term_claim` ADD KEY `idx_tb_po_term_claim_allocation` (`id_allocation`)");
        $this->addIndexIfMissing('tb_po_term_claim', 'idx_tb_po_term_claim_source', "ALTER TABLE `tb_po_term_claim` ADD KEY `idx_tb_po_term_claim_source` (`claim_source`, `source_raw`)");
        $this->addIndexIfMissing('tb_po_term_claim', 'idx_tb_po_term_claim_term_source_amount', "ALTER TABLE `tb_po_term_claim` ADD KEY `idx_tb_po_term_claim_term_source_amount` (`id_term`, `claim_source`, `invoice_amount`)");
        $this->addIndexIfMissing('tb_po_target_pipeline', 'idx_tb_po_target_pipeline_linked', "ALTER TABLE `tb_po_target_pipeline` ADD KEY `idx_tb_po_target_pipeline_linked` (`linked_id_po`)");
        if ($this->db->table_exists('tb_myrep_po_header')) {
            $this->addIndexIfMissing('tb_myrep_po_header', 'idx_myrep_po_header_number', "ALTER TABLE `tb_myrep_po_header` ADD KEY `idx_myrep_po_header_number` (`po_number`)");
        }
        if ($this->db->table_exists('tb_myrep_po_termin')) {
            $this->addIndexIfMissing('tb_myrep_po_termin', 'idx_myrep_po_termin_invoice_status', "ALTER TABLE `tb_myrep_po_termin` ADD KEY `idx_myrep_po_termin_invoice_status` (`invoice_date`, `status_termin`)");
        }
        $this->seedBowheerPo();
    }

    private function addColumnIfMissing($table, $column, $sql)
    {
        if (!$this->db->field_exists($column, $table)) {
            $this->db->query($sql);
        }
    }

    private function modifyColumnIfDifferent($table, $column, $expectedType, $sql)
    {
        $row = $this->db->query("SHOW COLUMNS FROM `$table` WHERE Field = " . $this->db->escape($column))->row_array();
        if (!empty($row) && strtolower((string) $row['Type']) !== strtolower($expectedType)) {
            $this->db->query($sql);
        }
    }

    private function addIndexIfMissing($table, $index, $sql)
    {
        $row = $this->db->query("SHOW INDEX FROM `$table` WHERE Key_name = " . $this->db->escape($index))->row_array();
        if (empty($row)) {
            $this->db->query($sql);
        }
    }

    private function dropIndexIfExists($table, $index)
    {
        $row = $this->db->query("SHOW INDEX FROM `$table` WHERE Key_name = " . $this->db->escape($index))->row_array();
        if (!empty($row)) {
            $this->db->query("ALTER TABLE `$table` DROP INDEX `$index`");
        }
    }

    private function seedBowheerPo()
    {
        $rows = [
            [1, 'Bp Zaenul', 'PT BANGTELINDO'],
            [2, 'Bp Zaenul', 'PT PERSADA SOKKA TAMA'],
            [3, 'Bp Zaenul', 'PT TELKOM AKSES'],
            [27, 'Bp Hendry', 'PT TELKOM AKSES - STAR'],
            [4, 'Bp Zaenul', 'PT MORATEL'],
            [5, 'Bp Zaenul', 'PT TBG ( PERMIT )'],
            [6, 'Bp Zaenul', 'PT XL SMART'],
            [8, 'Bp Wardani', 'PT NFT'],
            [9, 'Bp Wardani', 'PT EMR - NRO'],
            [10, 'Bp Slamet', 'PT EMR - PU ( NON PPN )'],
            [11, 'Bp Slamet', 'PT FS - PU'],
            [12, 'Bp Slamet', 'PT MORATEL - PU'],
            [13, 'Bp Fringga', 'PT EMR - DONASI'],
            [14, 'Bp Donny', 'PT FS - OSP'],
            [15, 'Bp Donny', 'PT FS - DONASI'],
            [16, 'Bp Sumirat', 'PT IFORTE - FIBERIZATION'],
            [17, 'Bp Sumirat', 'PT IFORTE - FTTH XL'],
            [18, 'Bp Sumirat', 'PT IFORTE - FTTH IOH'],
            [19, 'Bp Sumirat', 'PT IFORTE - REGULAR & CONN'],
            [20, 'Bp Hendry', 'PT IFORTE - LBS RECTIFIKASI'],
            [21, 'Bp Wendy', 'PT VGREEN ( EVCS )'],
            [22, 'Bp Wendy', 'PT VGREEN ( BSS )']
        ];

        foreach ($rows as $row) {
            $payload = [
                'id_bowheer' => $row[0],
                'no_urut' => $row[0],
                'pic' => $row[1],
                'bowheer' => $row[2],
                'bowheer_key' => $this->normalizeBowheerKey($row[2])
            ];

            $exists = $this->db->get_where('tb_bowheer_po', ['id_bowheer' => $row[0]])->row_array();
            if ($exists) {
                $this->db->where('id_bowheer', $row[0])->update('tb_bowheer_po', $payload);
            } else {
                $this->db->insert('tb_bowheer_po', $payload);
            }
        }
    }

    public function getPOsSummary()
    {
        $sql = "SELECT
            p.id_po,
            p.po_number,
            p.po_date,
            p.id_bowheer,
            COALESCE(bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer') AS nama_bowheer,
            bp.pic AS pic_bowheer,
            p.total_value,
            COALESCE((SELECT release_value FROM tb_po_amend a WHERE a.id_po = p.id_po ORDER BY a.amend_no DESC LIMIT 1), p.total_value) AS current_release_value,
            COALESCE((SELECT SUM(tc.invoice_amount) FROM tb_po_term_claim tc JOIN tb_po_term t ON tc.id_term = t.id_term WHERE t.id_po = p.id_po), 0) AS total_invoiced,
            COALESCE((SELECT SUM(COALESCE(NULLIF(t.plan_amount, 0), t.value)) FROM tb_po_term t WHERE t.id_po = p.id_po AND CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci), 0) AS total_target_week,
            COALESCE((SELECT SUM(COALESCE(NULLIF(t.plan_amount, 0), t.value)) FROM tb_po_term t WHERE t.id_po = p.id_po AND CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('CARRY_OVER' USING utf8mb4) COLLATE utf8mb4_unicode_ci), 0) AS total_carry_over
        FROM tb_po p
        LEFT JOIN tb_bowheer_po bp ON p.id_bowheer = bp.id_bowheer
        LEFT JOIN tb_master_bowheer_bilco b ON p.id_bowheer = b.id_bowheer
        ORDER BY p.po_date DESC";

        return $this->db->query($sql)->result_array();
    }

    public function getPOSummaryByBowheer()
    {
        $sql = "SELECT
            COALESCE(bp.id_bowheer, b.id_bowheer, 0) AS id_bowheer,
            COALESCE(bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer') AS nama_bowheer,
            COUNT(p.id_po) AS total_po,
            SUM(COALESCE((SELECT release_value FROM tb_po_amend a WHERE a.id_po = p.id_po ORDER BY a.amend_no DESC LIMIT 1), p.total_value)) AS current_release_value,
            SUM(COALESCE((SELECT SUM(tc.invoice_amount) FROM tb_po_term_claim tc JOIN tb_po_term t ON tc.id_term = t.id_term WHERE t.id_po = p.id_po), 0)) AS total_invoiced
        FROM tb_po p
        LEFT JOIN tb_bowheer_po bp ON p.id_bowheer = bp.id_bowheer
        LEFT JOIN tb_master_bowheer_bilco b ON p.id_bowheer = b.id_bowheer
        GROUP BY COALESCE(bp.id_bowheer, b.id_bowheer, 0), COALESCE(bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer')
        ORDER BY current_release_value DESC, nama_bowheer ASC";

        $rows = $this->db->query($sql)->result_array();

        foreach ($rows as &$row) {
            $release = (float) $row['current_release_value'];
            $invoiced = (float) $row['total_invoiced'];
            $remaining = $release - $invoiced;

            $row['remaining'] = $remaining > 0 ? $remaining : 0;
        }
        unset($row);

        return $rows;
    }

    public function getBowheerTermBreakdown()
    {
        $sql = "SELECT
            COALESCE(bp.id_bowheer, b.id_bowheer, 0) AS id_bowheer,
            COALESCE(bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer') AS nama_bowheer,
            t.term_index,
            COUNT(DISTINCT p.id_po) AS total_po,
            SUM(COALESCE(t.value, 0)) AS term_value,
            SUM(COALESCE(tc.invoiced_amount, 0)) AS invoiced_amount
        FROM tb_po p
        LEFT JOIN tb_bowheer_po bp ON p.id_bowheer = bp.id_bowheer
        LEFT JOIN tb_master_bowheer_bilco b ON p.id_bowheer = b.id_bowheer
        LEFT JOIN tb_po_term t ON p.id_po = t.id_po
            AND (
                t.id_amend = (
                    SELECT a.id_amend
                    FROM tb_po_amend a
                    WHERE a.id_po = p.id_po
                    ORDER BY a.amend_no DESC
                    LIMIT 1
                )
                OR (
                    t.id_amend IS NULL
                    AND NOT EXISTS (
                        SELECT 1
                        FROM tb_po_amend a2
                        WHERE a2.id_po = p.id_po
                    )
                )
            )
        LEFT JOIN (
            SELECT id_term, SUM(invoice_amount) AS invoiced_amount
            FROM tb_po_term_claim
            GROUP BY id_term
        ) tc ON t.id_term = tc.id_term
        GROUP BY COALESCE(bp.id_bowheer, b.id_bowheer, 0), COALESCE(bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer'), t.term_index
        ORDER BY nama_bowheer ASC, t.term_index ASC";

        $rows = $this->db->query($sql)->result_array();
        $result = [];

        foreach ($rows as $row) {
            $bowheerKey = (string) $row['id_bowheer'];

            if (!isset($result[$bowheerKey])) {
                $result[$bowheerKey] = [
                    'id_bowheer' => $row['id_bowheer'],
                    'nama_bowheer' => $row['nama_bowheer'],
                    'total_po' => (int) $row['total_po'],
                    'terms' => []
                ];
            }

            if ($row['term_index'] === null) {
                continue;
            }

            $termValue = (float) $row['term_value'];
            $invoiced = (float) $row['invoiced_amount'];
            $remaining = $termValue - $invoiced;

            $result[$bowheerKey]['terms'][] = [
                'term_index' => (int) $row['term_index'],
                'term_value' => $termValue,
                'invoiced_amount' => $invoiced,
                'remaining' => $remaining > 0 ? $remaining : 0
            ];
        }

        return array_values($result);
    }

    public function getNyOutstandingTermBreakdown()
    {
        if (!$this->db->table_exists('tb_po_target_pipeline')) {
            return [];
        }

        $rows = $this->db->query("SELECT
                COALESCE(NULLIF(pl.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer') AS bowheer,
                pl.term_index,
                SUM(COALESCE(NULLIF(pl.ny_po_2026_amount, 0), pl.plan_amount, 0)) AS outstanding_amount
            FROM tb_po_target_pipeline pl
            LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = pl.id_bowheer
            WHERE pl.linked_id_po IS NULL
            GROUP BY COALESCE(NULLIF(pl.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer'), pl.term_index
            HAVING outstanding_amount > 0
            ORDER BY bowheer ASC, pl.term_index ASC")->result_array();

        $result = [];
        foreach ($rows as $row) {
            $bowheer = (string) ($row['bowheer'] ?? 'Tanpa Bowheer');
            if (!isset($result[$bowheer])) {
                $result[$bowheer] = [
                    'bowheer' => $bowheer,
                    'terms' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                    'total' => 0
                ];
            }

            $term = (int) ($row['term_index'] ?? 0);
            if ($term < 1 || $term > 5) {
                continue;
            }

            $amount = (float) ($row['outstanding_amount'] ?? 0);
            $result[$bowheer]['terms'][$term] += $amount;
            $result[$bowheer]['total'] += $amount;
        }

        return array_values($result);
    }

    public function getBatchInvoiceTerminRows()
    {
        return $this->db->query("SELECT *
            FROM (
                SELECT
                    p.id_po,
                    CONVERT(COALESCE(NULLIF(NULLIF(TRIM(a.no_po_sub), ''), '-'), p.po_number) USING utf8mb4) COLLATE utf8mb4_unicode_ci AS po_number,
                    CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci AS parent_po_number,
                    p.po_date,
                    CONVERT(COALESCE(bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS nama_bowheer,
                    t.id_term,
                    a.id_allocation,
                    t.term_index,
                    GREATEST(COALESCE(NULLIF(a.plan_amount, 0), NULLIF(a.allocation_value, 0), NULLIF(t.value, 0), (COALESCE((SELECT release_value FROM tb_po_amend amv WHERE amv.id_po = p.id_po ORDER BY amv.amend_no DESC LIMIT 1), p.total_value, 0) * COALESCE(t.percent, 0) / 100), 0), COALESCE(p.total_value, 0) * COALESCE(t.percent, 0) / 100) AS term_value,
                    COALESCE(tc.invoiced_amount, 0) AS invoiced_amount,
                    GREATEST(GREATEST(COALESCE(NULLIF(a.plan_amount, 0), NULLIF(a.allocation_value, 0), NULLIF(t.value, 0), (COALESCE((SELECT release_value FROM tb_po_amend amv WHERE amv.id_po = p.id_po ORDER BY amv.amend_no DESC LIMIT 1), p.total_value, 0) * COALESCE(t.percent, 0) / 100), 0), COALESCE(p.total_value, 0) * COALESCE(t.percent, 0) / 100) - COALESCE(tc.invoiced_amount, 0), 0) AS remaining,
                    COALESCE(a.invoice_date, t.invoice_date) AS invoice_date
                FROM tb_po p
                LEFT JOIN tb_bowheer_po bp ON p.id_bowheer = bp.id_bowheer
                LEFT JOIN tb_master_bowheer_bilco b ON p.id_bowheer = b.id_bowheer
                JOIN tb_po_term t ON p.id_po = t.id_po
                    AND (
                        t.id_amend = (
                            SELECT am.id_amend
                            FROM tb_po_amend am
                            WHERE am.id_po = p.id_po
                            ORDER BY am.amend_no DESC
                            LIMIT 1
                        )
                        OR (
                            t.id_amend IS NULL
                            AND NOT EXISTS (
                                SELECT 1
                                FROM tb_po_amend am2
                                WHERE am2.id_po = p.id_po
                            )
                        )
                    )
                JOIN tb_po_term_allocation a ON a.id_term = t.id_term
                LEFT JOIN (
                    SELECT id_allocation, SUM(invoice_amount) AS invoiced_amount
                    FROM tb_po_term_claim
                    WHERE id_allocation IS NOT NULL
                    GROUP BY id_allocation
                ) tc ON a.id_allocation = tc.id_allocation
                UNION ALL
                SELECT
                    p.id_po,
                    CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci AS po_number,
                    CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci AS parent_po_number,
                    p.po_date,
                    CONVERT(COALESCE(bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS nama_bowheer,
                    t.id_term,
                    NULL AS id_allocation,
                    t.term_index,
                    GREATEST(COALESCE(NULLIF(t.value, 0), (COALESCE((SELECT release_value FROM tb_po_amend amv WHERE amv.id_po = p.id_po ORDER BY amv.amend_no DESC LIMIT 1), p.total_value, 0) * COALESCE(t.percent, 0) / 100), 0), COALESCE(p.total_value, 0) * COALESCE(t.percent, 0) / 100) AS term_value,
                    COALESCE(tc.invoiced_amount, 0) AS invoiced_amount,
                    GREATEST(GREATEST(COALESCE(NULLIF(t.value, 0), (COALESCE((SELECT release_value FROM tb_po_amend amv WHERE amv.id_po = p.id_po ORDER BY amv.amend_no DESC LIMIT 1), p.total_value, 0) * COALESCE(t.percent, 0) / 100), 0), COALESCE(p.total_value, 0) * COALESCE(t.percent, 0) / 100) - COALESCE(tc.invoiced_amount, 0), 0) AS remaining,
                    t.invoice_date
                FROM tb_po p
                LEFT JOIN tb_bowheer_po bp ON p.id_bowheer = bp.id_bowheer
                LEFT JOIN tb_master_bowheer_bilco b ON p.id_bowheer = b.id_bowheer
                JOIN tb_po_term t ON p.id_po = t.id_po
                    AND (
                        t.id_amend = (
                            SELECT am.id_amend
                            FROM tb_po_amend am
                            WHERE am.id_po = p.id_po
                            ORDER BY am.amend_no DESC
                            LIMIT 1
                        )
                        OR (
                            t.id_amend IS NULL
                            AND NOT EXISTS (
                                SELECT 1
                                FROM tb_po_amend am2
                                WHERE am2.id_po = p.id_po
                            )
                        )
                    )
                LEFT JOIN (
                    SELECT id_term, SUM(invoice_amount) AS invoiced_amount
                    FROM tb_po_term_claim
                    WHERE id_allocation IS NULL
                    GROUP BY id_term
                ) tc ON t.id_term = tc.id_term
                WHERE NOT EXISTS (SELECT 1 FROM tb_po_term_allocation ax WHERE ax.id_term = t.id_term)
            ) x
            ORDER BY po_number ASC, term_index ASC")->result_array();
    }

    public function getBatchInvoiceTerminRowsByPoNumbers(array $poNumbers)
    {
        $cleanPoNumbers = [];
        foreach ($poNumbers as $poNumber) {
            $poNumber = trim((string) $poNumber);
            if ($poNumber !== '') {
                $cleanPoNumbers[strtoupper($poNumber)] = $poNumber;
            }
        }

        if (empty($cleanPoNumbers)) {
            return [];
        }

        $escapedPoNumbers = [];
        foreach (array_values($cleanPoNumbers) as $poNumber) {
            $escapedPoNumbers[] = $this->db->escape($poNumber);
        }
        $poInSql = implode(',', $escapedPoNumbers);

        return $this->db->query("SELECT *
            FROM (
                SELECT
                    p.id_po,
                    CONVERT(COALESCE(NULLIF(NULLIF(TRIM(a.no_po_sub), ''), '-'), p.po_number) USING utf8mb4) COLLATE utf8mb4_unicode_ci AS po_number,
                    CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci AS parent_po_number,
                    p.po_date,
                    CONVERT(COALESCE(bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS nama_bowheer,
                    t.id_term,
                    a.id_allocation,
                    t.term_index,
                    GREATEST(COALESCE(NULLIF(a.plan_amount, 0), NULLIF(a.allocation_value, 0), NULLIF(t.value, 0), (COALESCE((SELECT release_value FROM tb_po_amend amv WHERE amv.id_po = p.id_po ORDER BY amv.amend_no DESC LIMIT 1), p.total_value, 0) * COALESCE(t.percent, 0) / 100), 0), COALESCE(p.total_value, 0) * COALESCE(t.percent, 0) / 100) AS term_value,
                    COALESCE(tc.invoiced_amount, 0) AS invoiced_amount,
                    GREATEST(GREATEST(COALESCE(NULLIF(a.plan_amount, 0), NULLIF(a.allocation_value, 0), NULLIF(t.value, 0), (COALESCE((SELECT release_value FROM tb_po_amend amv WHERE amv.id_po = p.id_po ORDER BY amv.amend_no DESC LIMIT 1), p.total_value, 0) * COALESCE(t.percent, 0) / 100), 0), COALESCE(p.total_value, 0) * COALESCE(t.percent, 0) / 100) - COALESCE(tc.invoiced_amount, 0), 0) AS remaining,
                    COALESCE(a.invoice_date, t.invoice_date) AS invoice_date
                FROM tb_po p
                LEFT JOIN tb_bowheer_po bp ON p.id_bowheer = bp.id_bowheer
                LEFT JOIN tb_master_bowheer_bilco b ON p.id_bowheer = b.id_bowheer
                JOIN tb_po_term t ON p.id_po = t.id_po
                    AND (
                        t.id_amend = (
                            SELECT am.id_amend
                            FROM tb_po_amend am
                            WHERE am.id_po = p.id_po
                            ORDER BY am.amend_no DESC
                            LIMIT 1
                        )
                        OR (
                            t.id_amend IS NULL
                            AND NOT EXISTS (
                                SELECT 1
                                FROM tb_po_amend am2
                                WHERE am2.id_po = p.id_po
                            )
                        )
                    )
                JOIN tb_po_term_allocation a ON a.id_term = t.id_term
                LEFT JOIN (
                    SELECT id_allocation, SUM(invoice_amount) AS invoiced_amount
                    FROM tb_po_term_claim
                    WHERE id_allocation IS NOT NULL
                    GROUP BY id_allocation
                ) tc ON a.id_allocation = tc.id_allocation
                WHERE p.po_number IN ({$poInSql}) OR a.no_po_sub IN ({$poInSql})
                UNION ALL
                SELECT
                    p.id_po,
                    CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci AS po_number,
                    CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci AS parent_po_number,
                    p.po_date,
                    CONVERT(COALESCE(bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS nama_bowheer,
                    t.id_term,
                    NULL AS id_allocation,
                    t.term_index,
                    GREATEST(COALESCE(NULLIF(t.value, 0), (COALESCE((SELECT release_value FROM tb_po_amend amv WHERE amv.id_po = p.id_po ORDER BY amv.amend_no DESC LIMIT 1), p.total_value, 0) * COALESCE(t.percent, 0) / 100), 0), COALESCE(p.total_value, 0) * COALESCE(t.percent, 0) / 100) AS term_value,
                    COALESCE(tc.invoiced_amount, 0) AS invoiced_amount,
                    GREATEST(GREATEST(COALESCE(NULLIF(t.value, 0), (COALESCE((SELECT release_value FROM tb_po_amend amv WHERE amv.id_po = p.id_po ORDER BY amv.amend_no DESC LIMIT 1), p.total_value, 0) * COALESCE(t.percent, 0) / 100), 0), COALESCE(p.total_value, 0) * COALESCE(t.percent, 0) / 100) - COALESCE(tc.invoiced_amount, 0), 0) AS remaining,
                    t.invoice_date
                FROM tb_po p
                LEFT JOIN tb_bowheer_po bp ON p.id_bowheer = bp.id_bowheer
                LEFT JOIN tb_master_bowheer_bilco b ON p.id_bowheer = b.id_bowheer
                JOIN tb_po_term t ON p.id_po = t.id_po
                    AND (
                        t.id_amend = (
                            SELECT am.id_amend
                            FROM tb_po_amend am
                            WHERE am.id_po = p.id_po
                            ORDER BY am.amend_no DESC
                            LIMIT 1
                        )
                        OR (
                            t.id_amend IS NULL
                            AND NOT EXISTS (
                                SELECT 1
                                FROM tb_po_amend am2
                                WHERE am2.id_po = p.id_po
                            )
                        )
                    )
                LEFT JOIN (
                    SELECT id_term, SUM(invoice_amount) AS invoiced_amount
                    FROM tb_po_term_claim
                    WHERE id_allocation IS NULL
                    GROUP BY id_term
                ) tc ON t.id_term = tc.id_term
                WHERE p.po_number IN ({$poInSql})
                    AND NOT EXISTS (SELECT 1 FROM tb_po_term_allocation ax WHERE ax.id_term = t.id_term)
            ) x
            ORDER BY po_number ASC, term_index ASC")->result_array();
    }

    public function getBowheerTermDetail($idBowheer, $metric, $termIndex = 0)
    {
        $idBowheer = (int) $idBowheer;
        $termIndex = (int) $termIndex;
        $metric = in_array($metric, ['total_po', 'term_value', 'term_done', 'term_remaining', 'outstanding_term'], true)
            ? $metric
            : 'outstanding_term';

        $rows = $this->db->query("SELECT
                p.id_po,
                p.po_number,
                p.type_project,
                p.po_date,
                COALESCE((SELECT release_value FROM tb_po_amend a WHERE a.id_po = p.id_po ORDER BY a.amend_no DESC LIMIT 1), p.total_value) AS current_release_value,
                t.id_term,
                t.term_index,
                alloc.no_po_sub,
                alloc.regional,
                alloc.kota_po,
                alloc.detail_po,
                alloc.remarks,
                COALESCE(t.value, 0) AS term_value,
                COALESCE(tc.invoiced_amount, 0) AS invoiced_amount,
                GREATEST(COALESCE(t.value, 0) - COALESCE(tc.invoiced_amount, 0), 0) AS remaining,
                COALESCE(bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer') AS nama_bowheer
            FROM tb_po p
            LEFT JOIN tb_bowheer_po bp ON p.id_bowheer = bp.id_bowheer
            LEFT JOIN tb_master_bowheer_bilco b ON p.id_bowheer = b.id_bowheer
            LEFT JOIN tb_po_term t ON p.id_po = t.id_po
                AND (
                    t.id_amend = (
                        SELECT a.id_amend
                        FROM tb_po_amend a
                        WHERE a.id_po = p.id_po
                        ORDER BY a.amend_no DESC
                        LIMIT 1
                    )
                    OR (
                        t.id_amend IS NULL
                        AND NOT EXISTS (
                            SELECT 1
                            FROM tb_po_amend a2
                            WHERE a2.id_po = p.id_po
                        )
                    )
                )
            LEFT JOIN (
                SELECT id_term, SUM(invoice_amount) AS invoiced_amount
                FROM tb_po_term_claim
                GROUP BY id_term
            ) tc ON t.id_term = tc.id_term
            LEFT JOIN (
                SELECT
                    id_term,
                    GROUP_CONCAT(DISTINCT NULLIF(no_po_sub, '') ORDER BY source_row_no SEPARATOR ', ') AS no_po_sub,
                    GROUP_CONCAT(DISTINCT NULLIF(regional, '') ORDER BY source_row_no SEPARATOR ', ') AS regional,
                    GROUP_CONCAT(DISTINCT NULLIF(kota_po, '') ORDER BY source_row_no SEPARATOR ', ') AS kota_po,
                    GROUP_CONCAT(DISTINCT NULLIF(detail_po, '') ORDER BY source_row_no SEPARATOR ', ') AS detail_po,
                    GROUP_CONCAT(DISTINCT NULLIF(remarks, '') ORDER BY source_row_no SEPARATOR ', ') AS remarks
                FROM tb_po_term_allocation
                GROUP BY id_term
            ) alloc ON t.id_term = alloc.id_term
            WHERE COALESCE(bp.id_bowheer, b.id_bowheer, 0) = ?
            ORDER BY p.po_date ASC, p.po_number ASC, t.term_index ASC", [$idBowheer])->result_array();

        $filtered = array_values(array_filter($rows, function ($row) use ($metric, $termIndex) {
            $rowTermIndex = (int) ($row['term_index'] ?? 0);
            $termValue = (float) ($row['term_value'] ?? 0);
            $invoiced = (float) ($row['invoiced_amount'] ?? 0);
            $remaining = (float) ($row['remaining'] ?? 0);
            $release = (float) ($row['current_release_value'] ?? 0);

            if ($metric !== 'total_po' && $rowTermIndex <= 0) {
                return false;
            }

            if ($termIndex > 0 && $rowTermIndex !== $termIndex) {
                return false;
            }

            if ($metric === 'total_po') {
                return $release > 0;
            }

            if ($metric === 'term_value') {
                return $termValue > 0;
            }

            if ($metric === 'term_done') {
                return $invoiced > 0;
            }

            return $remaining > 0;
        }));

        if ($metric === 'total_po') {
            $uniqueRows = [];
            foreach ($filtered as $row) {
                $poKey = (string) ($row['id_po'] ?? '');
                if ($poKey !== '' && !isset($uniqueRows[$poKey])) {
                    $row['term_index'] = null;
                    $row['term_value'] = 0;
                    $row['invoiced_amount'] = 0;
                    $row['remaining'] = 0;
                    $uniqueRows[$poKey] = $row;
                }
            }

            return array_values($uniqueRows);
        }

        return $filtered;
    }

    public function getPOByNumber($po_number)
    {
        return $this->db->get_where('tb_po', ['po_number' => $po_number])->row_array();
    }

    public function getPOById($id_po)
    {
        return $this->db
            ->select('p.*')
            ->select("COALESCE(NULLIF(p.dashboard_bowheer, ''), bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer') AS nama_bowheer", false)
            ->select("COALESCE(bp.pic, '') AS pic_bowheer", false)
            ->from('tb_po p')
            ->join('tb_bowheer_po bp', 'bp.id_bowheer = p.id_bowheer', 'left')
            ->join('tb_master_bowheer_bilco b', 'b.id_bowheer = p.id_bowheer', 'left')
            ->where('p.id_po', (int) $id_po)
            ->get()
            ->row_array();
    }

    public function getBowheerOptionsForHeaderEdit()
    {
        if (!$this->db->table_exists('tb_bowheer_po')) {
            return [];
        }

        return $this->db
            ->select('id_bowheer, bowheer, pic')
            ->from('tb_bowheer_po')
            ->order_by('no_urut', 'ASC')
            ->order_by('bowheer', 'ASC')
            ->get()
            ->result_array();
    }

    public function getPicOptionsForHeaderEdit()
    {
        if (!$this->db->table_exists('tb_bowheer_po')) {
            return [];
        }

        $rows = $this->db
            ->select('pic')
            ->from('tb_bowheer_po')
            ->where("COALESCE(pic, '') !=", '')
            ->group_by('pic')
            ->order_by('pic', 'ASC')
            ->get()
            ->result_array();

        return array_values(array_filter(array_map(static function ($row) {
            return trim((string) ($row['pic'] ?? ''));
        }, $rows)));
    }

    public function getTermMasterOptionsForHeaderEdit()
    {
        if (!$this->db->table_exists('tb_term_master') || !$this->db->table_exists('tb_term_master_split')) {
            return [];
        }

        $masters = $this->db
            ->select('id_master, name')
            ->from('tb_term_master')
            ->order_by('id_master', 'ASC')
            ->get()
            ->result_array();

        $options = [];
        $seen = [];
        foreach ($masters as $master) {
            $splits = $this->getDistinctTermMasterSplits((int) ($master['id_master'] ?? 0));
            if (empty($splits)) {
                continue;
            }

            $parts = array_map(static function ($split) {
                $percent = (float) ($split['percent'] ?? 0);
                return rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.');
            }, $splits);
            $signature = implode(':', $parts);
            if (isset($seen[$signature])) {
                continue;
            }
            $seen[$signature] = true;

            $options[] = [
                'id_master' => (int) ($master['id_master'] ?? 0),
                'name' => trim((string) ($master['name'] ?? 'PO Term')),
                'label' => trim((string) ($master['name'] ?? 'PO Term')) . ' (' . $signature . ')',
                'signature' => $signature,
            ];
        }

        return $options;
    }

    public function updatePOHeader($idPo, array $payload, $userId = 0)
    {
        $idPo = (int) $idPo;
        if ($idPo <= 0) {
            return ['status' => false, 'message' => 'PO tidak valid.'];
        }

        $existing = $this->getPOById($idPo);
        if (!$existing) {
            return ['status' => false, 'message' => 'PO tidak ditemukan.'];
        }

        $poNumber = trim((string) ($payload['po_number'] ?? ''));
        if ($poNumber === '') {
            return ['status' => false, 'message' => 'Nomor PO wajib diisi.'];
        }

        $duplicate = $this->db
            ->select('id_po')
            ->from('tb_po')
            ->where('po_number', $poNumber)
            ->where('id_po !=', $idPo)
            ->limit(1)
            ->get()
            ->row_array();
        if (!empty($duplicate)) {
            return ['status' => false, 'message' => 'Nomor PO sudah dipakai oleh data lain.'];
        }

        $idBowheer = (int) ($payload['id_bowheer'] ?? 0);
        $bowheerRow = [];
        if ($idBowheer > 0 && $this->db->table_exists('tb_bowheer_po')) {
            $bowheerRow = $this->db
                ->select('id_bowheer, bowheer, pic')
                ->from('tb_bowheer_po')
                ->where('id_bowheer', $idBowheer)
                ->limit(1)
                ->get()
                ->row_array();
        }

        $dashboardBowheer = trim((string) ($payload['dashboard_bowheer'] ?? ''));
        if ($dashboardBowheer === '' && !empty($bowheerRow['bowheer'])) {
            $dashboardBowheer = trim((string) $bowheerRow['bowheer']);
        }
        if ($dashboardBowheer === '') {
            $dashboardBowheer = trim((string) ($existing['dashboard_bowheer'] ?? ''));
        }

        $statusPo = strtoupper(trim((string) ($payload['status_po'] ?? '')));
        if ($statusPo === '') {
            $statusPo = strtoupper(trim((string) ($existing['status_po'] ?? 'ON PO')));
        }

        $poDate = trim((string) ($payload['po_date'] ?? ''));
        $poDate = $poDate !== '' ? date('Y-m-d', strtotime($poDate)) : null;
        $totalValue = (float) ($payload['total_value'] ?? 0);
        if ($totalValue <= 0) {
            $totalValue = (float) ($existing['total_value'] ?? 0);
        }

        $update = [
            'po_number' => $poNumber,
            'po_date' => $poDate,
            'id_bowheer' => $idBowheer > 0 ? $idBowheer : null,
            'total_value' => $totalValue,
            'dashboard_bowheer' => $dashboardBowheer !== '' ? $dashboardBowheer : null,
            'type_project' => trim((string) ($payload['type_project'] ?? '')) ?: null,
            'status_po' => $statusPo,
        ];

        $this->db->trans_begin();
        $this->db->where('id_po', $idPo)->update('tb_po', $update);

        $amend = $this->db
            ->select('id_amend')
            ->from('tb_po_amend')
            ->where('id_po', $idPo)
            ->order_by('amend_no', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();
        if (!empty($amend)) {
            $this->db
                ->where('id_amend', (int) $amend['id_amend'])
                ->update('tb_po_amend', [
                    'release_value' => $totalValue,
                    'release_date' => $poDate
                ]);
        } else {
            $this->db->insert('tb_po_amend', [
                'id_po' => $idPo,
                'amend_no' => 1,
                'release_value' => $totalValue,
                'release_date' => $poDate,
                'notes' => 'Header edit initial release'
            ]);
            $amend = ['id_amend' => (int) $this->db->insert_id()];
        }

        $termMasterId = (int) ($payload['term_master_id'] ?? 0);
        if ($termMasterId > 0) {
            $termResult = $this->applyPoHeaderTermMaster($idPo, $termMasterId, $totalValue, (int) ($amend['id_amend'] ?? 0));
            if (empty($termResult['status'])) {
                $this->db->trans_rollback();
                return $termResult;
            }
        } elseif (abs($totalValue - (float) ($existing['total_value'] ?? 0)) > 0.001) {
            $termResult = $this->applyPoHeaderCurrentTermScale($idPo, $totalValue, (int) ($amend['id_amend'] ?? 0), (float) ($existing['total_value'] ?? 0));
            if (empty($termResult['status'])) {
                $this->db->trans_rollback();
                return $termResult;
            }
        }

        $picBowheer = trim((string) ($payload['pic_bowheer'] ?? ''));
        if ($idBowheer > 0 && $this->db->table_exists('tb_bowheer_po')) {
            $this->db
                ->where('id_bowheer', $idBowheer)
                ->update('tb_bowheer_po', ['pic' => $picBowheer !== '' ? $picBowheer : null]);
        }

        if ((string) ($existing['po_number'] ?? '') !== $poNumber && $this->db->table_exists('tb_pipeline_project')) {
            $this->db
                ->where('linked_id_po', $idPo)
                ->update('tb_pipeline_project', ['linked_po_number' => $poNumber]);
        }

        if ($statusPo === 'CANCELLED') {
            $this->cancelPoTargets($idPo);
        }

        $this->refreshPoDashboardMetrics($idPo);
        $this->rebuildDashboardCache(null);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Header PO gagal diperbarui.'];
        }

        $this->db->trans_commit();
        return ['status' => true, 'message' => 'Header PO berhasil diperbarui.'];
    }

    private function cancelPoTargets($idPo)
    {
        $idPo = (int) $idPo;
        if ($idPo <= 0) {
            return;
        }

        $this->db->query("UPDATE tb_po_term_allocation a
            JOIN tb_po_term t ON t.id_term = a.id_term
            SET a.target_status = 'CANCELLED'
            WHERE t.id_po = ?
                AND CONVERT(a.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci IN (
                    CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
                    CONVERT('CARRY_OVER' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                )", [$idPo]);

        $this->db->query("UPDATE tb_po_term
            SET target_status = 'CANCELLED'
            WHERE id_po = ?
                AND CONVERT(target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci IN (
                    CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
                    CONVERT('CARRY_OVER' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                )", [$idPo]);
    }

    public function deletePO($idPo)
    {
        $idPo = (int) $idPo;
        if ($idPo <= 0) {
            return ['status' => false, 'message' => 'PO tidak valid.'];
        }

        $po = $this->getPOById($idPo);
        if (!$po) {
            return ['status' => false, 'message' => 'PO tidak ditemukan.'];
        }

        $this->db->trans_begin();
        if ($this->db->table_exists('tb_po_target_pipeline')) {
            $this->db
                ->where('linked_id_po', $idPo)
                ->update('tb_po_target_pipeline', [
                    'linked_id_po' => null,
                    'linked_po_number' => null,
                    'pipeline_status' => 'OPEN',
                    'converted_at' => null,
                    'converted_by' => null,
                ]);
        }

        $this->deletePoChildren($idPo);
        $this->db->delete('tb_po', ['id_po' => $idPo]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'PO gagal dihapus.'];
        }

        $this->db->trans_commit();
        $this->rebuildDashboardCache(null);

        return ['status' => true, 'message' => 'PO berhasil dihapus.'];
    }

    private function getDistinctTermMasterSplits($masterId)
    {
        $masterId = (int) $masterId;
        if ($masterId <= 0 || !$this->db->table_exists('tb_term_master_split')) {
            return [];
        }

        $rows = $this->db
            ->select('term_index, percent')
            ->from('tb_term_master_split')
            ->where('id_master', $masterId)
            ->order_by('term_index', 'ASC')
            ->order_by('id_split', 'ASC')
            ->get()
            ->result_array();

        $splits = [];
        foreach ($rows as $row) {
            $termIndex = (int) ($row['term_index'] ?? 0);
            $percent = (float) ($row['percent'] ?? 0);
            if ($termIndex <= 0 || $percent <= 0 || isset($splits[$termIndex])) {
                continue;
            }
            $splits[$termIndex] = ['term_index' => $termIndex, 'percent' => $percent];
        }

        ksort($splits);
        return array_values($splits);
    }

    private function applyPoHeaderTermMaster($idPo, $masterId, $totalValue, $idAmend)
    {
        $idPo = (int) $idPo;
        $totalValue = (float) $totalValue;
        $splits = $this->getDistinctTermMasterSplits($masterId);
        if ($idPo <= 0 || $totalValue <= 0 || empty($splits)) {
            return ['status' => false, 'message' => 'PO term tidak valid.'];
        }

        $claim = $this->db
            ->select('COUNT(*) AS total')
            ->from('tb_po_term_claim tc')
            ->join('tb_po_term t', 't.id_term = tc.id_term')
            ->where('t.id_po', $idPo)
            ->get()
            ->row_array();
        if ((int) ($claim['total'] ?? 0) > 0) {
            return ['status' => false, 'message' => 'PO term tidak bisa diubah karena sudah ada invoice claim.'];
        }

        $existingTerms = $this->db
            ->from('tb_po_term')
            ->where('id_po', $idPo)
            ->order_by('term_index', 'ASC')
            ->get()
            ->result_array();
        $existingByIndex = [];
        foreach ($existingTerms as $term) {
            $existingByIndex[(int) ($term['term_index'] ?? 0)] = $term;
        }

        $firstAllocation = $this->db
            ->from('tb_po_term_allocation a')
            ->join('tb_po_term t', 't.id_term = a.id_term')
            ->where('t.id_po', $idPo)
            ->order_by('a.id_allocation', 'ASC')
            ->limit(1)
            ->get()
            ->row_array();

        $sumPercent = array_sum(array_map(static function ($split) {
            return (float) ($split['percent'] ?? 0);
        }, $splits));
        $remaining = $totalValue;
        $keepTermIds = [];
        $count = count($splits);

        foreach ($splits as $index => $split) {
            $termIndex = (int) ($split['term_index'] ?? ($index + 1));
            $percent = $sumPercent > 0 ? (((float) ($split['percent'] ?? 0) / $sumPercent) * 100) : 0;
            if ($index === $count - 1) {
                $value = $remaining;
            } else {
                $value = round(($totalValue * $percent) / 100, 2);
                $remaining -= $value;
            }

            $payload = [
                'id_amend' => $idAmend > 0 ? $idAmend : null,
                'term_index' => $termIndex,
                'percent' => round($percent, 2),
                'value' => $value,
                'plan_amount' => $value
            ];

            if (!empty($existingByIndex[$termIndex])) {
                $idTerm = (int) $existingByIndex[$termIndex]['id_term'];
                $this->db->where('id_term', $idTerm)->update('tb_po_term', $payload);
            } else {
                $payload += [
                    'id_po' => $idPo,
                    'target_status' => 'OPEN'
                ];
                $this->db->insert('tb_po_term', $payload);
                $idTerm = (int) $this->db->insert_id();
            }
            $keepTermIds[] = $idTerm;

            $allocations = $this->db
                ->from('tb_po_term_allocation')
                ->where('id_term', $idTerm)
                ->order_by('id_allocation', 'ASC')
                ->get()
                ->result_array();

            if (!empty($allocations)) {
                $totalAllocation = 0;
                foreach ($allocations as $allocation) {
                    $totalAllocation += (float) (($allocation['plan_amount'] ?? 0) ?: ($allocation['allocation_value'] ?? 0));
                }
                $allocationRemaining = $value;
                $allocationCount = count($allocations);
                foreach ($allocations as $allocationIndex => $allocation) {
                    $allocationValue = $allocationIndex === $allocationCount - 1
                        ? $allocationRemaining
                        : ($totalAllocation > 0 ? round(($value * (float) (($allocation['plan_amount'] ?? 0) ?: ($allocation['allocation_value'] ?? 0))) / $totalAllocation, 2) : 0);
                    $allocationRemaining -= $allocationValue;
                    $this->db
                        ->where('id_allocation', (int) $allocation['id_allocation'])
                        ->update('tb_po_term_allocation', [
                            'allocation_value' => $allocationValue,
                            'plan_amount' => $allocationValue
                        ]);
                }
            } elseif (!empty($firstAllocation)) {
                $this->db->insert('tb_po_term_allocation', [
                    'id_term' => $idTerm,
                    'no_po_sub' => $firstAllocation['no_po_sub'] ?? null,
                    'regional' => $firstAllocation['regional'] ?? null,
                    'kota_po' => $firstAllocation['kota_po'] ?? null,
                    'detail_po' => $firstAllocation['detail_po'] ?? null,
                    'remarks' => $firstAllocation['remarks'] ?? null,
                    'allocation_value' => $value,
                    'plan_amount' => $value,
                    'target_status' => 'OPEN',
                    'source_row_no' => $firstAllocation['source_row_no'] ?? null
                ]);
            }
        }

        $deleteIds = [];
        foreach ($existingTerms as $term) {
            $idTerm = (int) ($term['id_term'] ?? 0);
            if ($idTerm > 0 && !in_array($idTerm, $keepTermIds, true)) {
                $deleteIds[] = $idTerm;
            }
        }
        if (!empty($deleteIds)) {
            $this->db->where_in('id_term', $deleteIds)->delete('tb_po_term_allocation');
            $this->db->where_in('id_term', $deleteIds)->delete('tb_po_term');
        }

        return ['status' => true];
    }

    private function applyPoHeaderCurrentTermScale($idPo, $totalValue, $idAmend, $oldTotalValue = 0)
    {
        $idPo = (int) $idPo;
        $totalValue = (float) $totalValue;
        $oldTotalValue = (float) $oldTotalValue;
        if ($idPo <= 0 || $totalValue <= 0) {
            return ['status' => false, 'message' => 'PO value tidak valid.'];
        }

        $terms = $this->db
            ->from('tb_po_term')
            ->where('id_po', $idPo)
            ->order_by('term_index', 'ASC')
            ->get()
            ->result_array();
        if (empty($terms)) {
            return ['status' => true];
        }

        $claim = $this->db
            ->select('COUNT(*) AS total')
            ->from('tb_po_term_claim tc')
            ->join('tb_po_term t', 't.id_term = tc.id_term')
            ->where('t.id_po', $idPo)
            ->get()
            ->row_array();
        if ((int) ($claim['total'] ?? 0) > 0) {
            return ['status' => false, 'message' => 'PO value tidak bisa diubah karena sudah ada invoice claim.'];
        }

        $sumPercent = 0;
        foreach ($terms as $term) {
            $sumPercent += (float) ($term['percent'] ?? 0);
        }
        if ($sumPercent <= 0 && $oldTotalValue <= 0) {
            return ['status' => false, 'message' => 'Komposisi term lama tidak valid untuk scaling PO value.'];
        }

        $remaining = $totalValue;
        $count = count($terms);
        foreach ($terms as $index => $term) {
            $idTerm = (int) ($term['id_term'] ?? 0);
            if ($idTerm <= 0) {
                continue;
            }

            $percent = $sumPercent > 0
                ? (((float) ($term['percent'] ?? 0) / $sumPercent) * 100)
                : (((float) (($term['plan_amount'] ?? 0) ?: ($term['value'] ?? 0)) / $oldTotalValue) * 100);
            if ($index === $count - 1) {
                $value = $remaining;
            } else {
                $value = round(($totalValue * $percent) / 100, 2);
                $remaining -= $value;
            }

            $this->db->where('id_term', $idTerm)->update('tb_po_term', [
                'id_amend' => $idAmend > 0 ? $idAmend : null,
                'percent' => round($percent, 2),
                'value' => $value,
                'plan_amount' => $value
            ]);

            $allocations = $this->db
                ->from('tb_po_term_allocation')
                ->where('id_term', $idTerm)
                ->order_by('id_allocation', 'ASC')
                ->get()
                ->result_array();
            if (empty($allocations)) {
                continue;
            }

            $totalAllocation = 0;
            foreach ($allocations as $allocation) {
                $totalAllocation += (float) (($allocation['plan_amount'] ?? 0) ?: ($allocation['allocation_value'] ?? 0));
            }
            $allocationRemaining = $value;
            $allocationCount = count($allocations);
            foreach ($allocations as $allocationIndex => $allocation) {
                $allocationValue = $allocationIndex === $allocationCount - 1
                    ? $allocationRemaining
                    : ($totalAllocation > 0 ? round(($value * (float) (($allocation['plan_amount'] ?? 0) ?: ($allocation['allocation_value'] ?? 0))) / $totalAllocation, 2) : 0);
                $allocationRemaining -= $allocationValue;
                $this->db
                    ->where('id_allocation', (int) $allocation['id_allocation'])
                    ->update('tb_po_term_allocation', [
                        'allocation_value' => $allocationValue,
                        'plan_amount' => $allocationValue
                    ]);
            }
        }

        return ['status' => true];
    }

    public function createManualNyPoTarget(array $payload, $userId = 0)
    {
        $this->ensureStandaloneSchema();

        $idBowheer = (int) ($payload['id_bowheer'] ?? 0);
        $totalValue = (float) ($payload['total_value'] ?? 0);
        if ($idBowheer <= 0) {
            return ['status' => false, 'message' => 'Bowheer wajib dipilih untuk NY PO.'];
        }
        if ($totalValue <= 0) {
            return ['status' => false, 'message' => 'PO Value wajib lebih dari 0.'];
        }

        $bowheer = $this->db
            ->select('bowheer')
            ->from('tb_bowheer_po')
            ->where('id_bowheer', $idBowheer)
            ->limit(1)
            ->get()
            ->row_array();
        $dashboardBowheer = trim((string) ($bowheer['bowheer'] ?? ''));
        if ($dashboardBowheer === '') {
            return ['status' => false, 'message' => 'Bowheer tidak ditemukan.'];
        }

        $masterId = (int) ($payload['master_id'] ?? 0);
        $splits = [];
        if ($masterId > 0) {
            $splits = $this->db
                ->select('term_index, percent')
                ->from('tb_term_master_split')
                ->where('id_master', $masterId)
                ->order_by('term_index', 'ASC')
                ->get()
                ->result_array();
        }
        if (empty($splits)) {
            $splits = [['term_index' => 1, 'percent' => 100.00]];
        }

        $targetWeekRaw = strtoupper(trim((string) ($payload['target_week'] ?? '')));
        $meta = $targetWeekRaw !== '' ? $this->resolveSubmitMeta($targetWeekRaw) : [
            'submit_raw' => '',
            'target_status' => 'TARGET_WEEK',
            'target_year' => 2026,
            'target_week' => null,
            'target_week_start' => null,
            'target_week_end' => null,
            'invoice_date' => null
        ];
        if ($targetWeekRaw !== '' && strtoupper((string) ($meta['target_status'] ?? '')) !== 'TARGET_WEEK') {
            return ['status' => false, 'message' => 'Week target tidak valid. Gunakan format seperti W34.'];
        }

        $sourceRowNo = (int) $this->db
            ->select('COALESCE(MAX(source_row_no), 0) + 1 AS next_row_no', false)
            ->from('tb_po_target_pipeline')
            ->get()
            ->row_array()['next_row_no'];
        if ($sourceRowNo <= 0) {
            $sourceRowNo = time();
        }

        $typeProject = trim((string) ($payload['type_project'] ?? ''));
        $regional = trim((string) ($payload['regional'] ?? ''));
        $kotaPo = trim((string) ($payload['kota_po'] ?? ''));
        $detailPo = trim((string) ($payload['detail_po'] ?? ''));
        $remarks = trim((string) ($payload['remarks'] ?? ''));
        $notes = trim((string) ($payload['notes'] ?? ''));
        if ($remarks === '' && $notes !== '') {
            $remarks = $notes;
        }

        $poTerm = implode(':', array_map(function ($split) {
            $percent = (float) ($split['percent'] ?? 0);
            return rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.');
        }, $splits));

        $this->db->trans_begin();
        $remaining = $totalValue;
        $count = count($splits);
        foreach ($splits as $index => $split) {
            $termIndex = (int) ($split['term_index'] ?? ($index + 1));
            $percent = (float) ($split['percent'] ?? 0);
            if ($index === $count - 1) {
                $planAmount = $remaining;
            } else {
                $planAmount = round($totalValue * ($percent / 100), 2);
                $remaining -= $planAmount;
            }

            $sourceHash = hash('sha256', implode('|', [
                'MANUAL_NY_PO',
                $sourceRowNo,
                $dashboardBowheer,
                $termIndex,
                number_format($planAmount, 2, '.', ''),
                $targetWeekRaw
            ]));

            $this->db->insert('tb_po_target_pipeline', [
                'id_bowheer' => $idBowheer,
                'dashboard_bowheer' => $dashboardBowheer,
                'status_po' => 'NY PO',
                'regional' => $regional !== '' ? $regional : null,
                'kota_po' => $kotaPo !== '' ? $kotaPo : null,
                'detail_po' => $detailPo !== '' ? $detailPo : null,
                'remarks' => $remarks !== '' ? $remarks : null,
                'type_project' => $typeProject !== '' ? $typeProject : null,
                'po_date' => null,
                'po_term' => $poTerm,
                'term_index' => $termIndex,
                'plan_amount' => $planAmount,
                'submit_raw' => $meta['submit_raw'],
                'target_year' => 2026,
                'target_week' => $meta['target_week'],
                'target_week_start' => $meta['target_week_start'],
                'target_week_end' => $meta['target_week_end'],
                'target_status' => 'TARGET_WEEK',
                'ny_po_2026_amount' => $planAmount,
                'ny_po_2027_amount' => 0,
                'source_file' => 'MANUAL_NY_PO',
                'source_row_no' => $sourceRowNo,
                'source_hash' => $sourceHash,
                'pipeline_status' => 'OPEN',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        $this->rebuildDashboardCache(null);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'NY PO gagal dibuat.'];
        }

        $this->db->trans_commit();
        return ['status' => true, 'message' => 'NY PO berhasil dibuat dan masuk NY PO On Target 2026.'];
    }

    public function getPOTerms($id_po)
    {
        $latestAmend = $this->db
            ->select('id_amend')
            ->from('tb_po_amend')
            ->where('id_po', (int) $id_po)
            ->order_by('amend_no', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        $effectiveValueSql = "COALESCE(NULLIF(t.value, 0), (
            COALESCE(
                (SELECT am.release_value FROM tb_po_amend am WHERE am.id_po = t.id_po ORDER BY am.amend_no DESC LIMIT 1),
                (SELECT p.total_value FROM tb_po p WHERE p.id_po = t.id_po),
                0
            ) * COALESCE(t.percent, 0) / 100
        ), 0)";
        $claimLimitSql = "GREATEST(" . $effectiveValueSql . ", (
            COALESCE((SELECT p.total_value FROM tb_po p WHERE p.id_po = t.id_po), 0) * COALESCE(t.percent, 0) / 100
        ))";
        $this->db->select('t.*', false);
        $this->db->select($claimLimitSql . ' AS value', false);
        $this->db->select('COALESCE(SUM(tc.invoice_amount),0) AS invoiced_amount', false);
        $this->db->from('tb_po_term t');
        $this->db->join('tb_po_term_claim tc', 't.id_term = tc.id_term', 'left');
        $this->db->where('t.id_po', (int) $id_po);

        if (!empty($latestAmend['id_amend'])) {
            $this->db->where('t.id_amend', (int) $latestAmend['id_amend']);
        } else {
            $this->db->where('t.id_amend IS NULL', null, false);
        }

        $this->db->group_by('t.id_term');
        $this->db->order_by('t.term_index', 'ASC');

        return $this->db->get()->result_array();
    }

    public function getPOAllocations($id_po)
    {
        $allocationValueSql = "COALESCE(NULLIF(a.plan_amount, 0), NULLIF(a.allocation_value, 0), NULLIF(t.value, 0), (
            COALESCE(
                (SELECT am.release_value FROM tb_po_amend am WHERE am.id_po = t.id_po ORDER BY am.amend_no DESC LIMIT 1),
                (SELECT p.total_value FROM tb_po p WHERE p.id_po = t.id_po),
                0
            ) * COALESCE(t.percent, 0) / 100
        ), 0)";
        $allocationClaimLimitSql = "GREATEST(" . $allocationValueSql . ", (
            COALESCE((SELECT p.total_value FROM tb_po p WHERE p.id_po = t.id_po), 0) * COALESCE(t.percent, 0) / 100
        ))";
        $claimAmountSql = "COALESCE(tc_alloc.invoiced_amount, CASE WHEN ac.allocation_count = 1 THEN tc_term.invoiced_amount ELSE 0 END, 0)";
        $claimDateSql = "COALESCE(tc_alloc.invoice_date, CASE WHEN ac.allocation_count = 1 THEN tc_term.invoice_date ELSE NULL END)";
        $rows = $this->db->select('a.*', false)
            ->select('t.term_index', false)
            ->select($allocationClaimLimitSql . ' AS allocation_value', false)
            ->select('GREATEST(' . $allocationClaimLimitSql . ' - ' . $claimAmountSql . ', 0) AS outstanding_amount', false)
            ->select($claimAmountSql . ' AS invoiced_amount', false)
            ->select($claimDateSql . ' AS invoice_date', false)
            ->from('tb_po_term_allocation a')
            ->join('tb_po_term t', 't.id_term = a.id_term')
            ->join('(SELECT id_term, COUNT(*) AS allocation_count FROM tb_po_term_allocation GROUP BY id_term) ac', 'ac.id_term = a.id_term', 'left')
            ->join('(SELECT id_allocation, SUM(invoice_amount) AS invoiced_amount, MAX(invoice_date) AS invoice_date FROM tb_po_term_claim WHERE id_allocation IS NOT NULL GROUP BY id_allocation) tc_alloc', 'tc_alloc.id_allocation = a.id_allocation', 'left')
            ->join('(SELECT id_term, SUM(invoice_amount) AS invoiced_amount, MAX(invoice_date) AS invoice_date FROM tb_po_term_claim WHERE id_allocation IS NULL GROUP BY id_term) tc_term', 'tc_term.id_term = a.id_term', 'left')
            ->where('t.id_po', (int) $id_po)
            ->group_by('a.id_allocation')
            ->order_by('t.term_index', 'ASC')
            ->order_by('a.source_row_no', 'ASC')
            ->get()
            ->result_array();

        $result = [];
        foreach ($rows as $row) {
            $idTerm = (int) $row['id_term'];
            if (!isset($result[$idTerm])) {
                $result[$idTerm] = [];
            }
            $result[$idTerm][] = $row;
        }

        return $result;
    }

    public function getTargetWeekSummary()
    {
        $rows = $this->db->query("SELECT
                target_year,
                target_week,
                target_week_start,
                target_week_end,
                term_index,
                COUNT(*) AS total_term,
                SUM(amount) AS amount
            FROM (
                SELECT
                    a.target_year,
                    a.target_week,
                    a.target_week_start,
                    a.target_week_end,
                    t.term_index,
                    COALESCE(NULLIF(a.plan_amount, 0), a.allocation_value) AS amount
                FROM tb_po_term_allocation a
                JOIN tb_po_term t ON t.id_term = a.id_term
                WHERE CONVERT(a.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                UNION ALL
                SELECT
                    t.target_year,
                    t.target_week,
                    t.target_week_start,
                    t.target_week_end,
                    t.term_index,
                    COALESCE(NULLIF(t.plan_amount, 0), t.value) AS amount
                FROM tb_po_term t
                WHERE CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                    AND NOT EXISTS (
                        SELECT 1 FROM tb_po_term_allocation a WHERE a.id_term = t.id_term
                    )
                UNION ALL
                SELECT
                    pl.target_year,
                    pl.target_week,
                    pl.target_week_start,
                    pl.target_week_end,
                    pl.term_index,
                    pl.plan_amount AS amount
                FROM tb_po_target_pipeline pl
                WHERE CONVERT(pl.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                    AND pl.linked_id_po IS NULL
            ) x
            GROUP BY target_year, target_week, target_week_start, target_week_end, term_index
            ORDER BY target_year ASC, target_week ASC, term_index ASC")->result_array();

        return $rows;
    }

    public function getDashboardSummary($useCache = false)
    {
        if ($useCache) {
            $cachedRows = $this->db
                ->order_by('sort_order', 'ASC')
                ->order_by('bowheer', 'ASC')
                ->get('tb_po_dashboard_cache')
                ->result_array();

            if (empty($cachedRows)) {
                return $this->calculateDashboardSummary();
            }

            $rows = [];
            $totals = [
                'data_count' => 0,
                'all_po' => 0,
                'all_invoice' => 0,
                'done_inv_2026' => 0,
                'outs_2026_on_target' => 0,
                'ny_po_on_target_2026' => 0,
                'grandtotal_target' => 0,
                'ny_po_total' => 0,
                'co_to_2027' => 0,
                'total_outs' => 0
            ];

            foreach ($cachedRows as $row) {
                $rows[] = [
                    'pic' => $row['pic'],
                    'bowheer' => $row['bowheer'],
                    'all_po' => (float) $row['all_po'],
                    'all_invoice' => (float) $row['all_invoice'],
                    'done_inv_2026' => (float) $row['done_inv_2026'],
                    'outs_2026_on_target' => (float) $row['outs_2026_on_target'],
                    'ny_po_on_target_2026' => (float) $row['ny_po_on_target_2026'],
                    'grandtotal_target' => (float) $row['grandtotal_target'],
                    'ny_po_total' => (float) $row['ny_po_total'],
                    'co_to_2027' => (float) $row['co_to_2027'],
                    'total_outs' => (float) $row['total_outs']
                ];

                if ((int) $row['has_data'] === 1) {
                    $totals['data_count']++;
                }

                foreach (array_keys($totals) as $key) {
                    if ($key !== 'data_count') {
                        $totals[$key] += (float) $row[$key];
                    }
                }
            }

            return ['rows' => $rows, 'totals' => $totals];
        }

        return $this->calculateDashboardSummary();
    }

    private function calculateDashboardSummary()
    {
        $sql = "SELECT
                dashboard_bowheer,
                SUM(all_po) AS all_po,
                SUM(all_invoice) AS all_invoice,
                SUM(done_inv_2026) AS done_inv_2026,
                SUM(outs_2026_on_target) AS outs_2026_on_target,
                SUM(ny_po_on_target_2026) AS ny_po_on_target_2026,
                SUM(ny_po_2027) AS ny_po_2027,
                SUM(co_2027_on_po) AS co_2027_on_po
            FROM (
                SELECT
                    CONVERT(COALESCE(NULLIF(p.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS dashboard_bowheer,
                    CASE WHEN YEAR(p.po_date) = 2026 THEN COALESCE(p.total_value, 0) ELSE 0 END AS all_po,
                    COALESCE(p.dashboard_all_invoice, 0) AS all_invoice,
                    COALESCE(p.dashboard_invoice_2026, 0) AS done_inv_2026,
                    COALESCE(p.dashboard_outs_2026, 0) AS outs_2026_on_target,
                    0 AS ny_po_on_target_2026,
                    0 AS ny_po_2027,
                    COALESCE(p.dashboard_co_2027, 0) AS co_2027_on_po
                FROM tb_po p
                LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = p.id_bowheer
                WHERE COALESCE(p.status_po, 'ON PO') = 'ON PO'
                UNION ALL
                SELECT
                    CONVERT(dashboard_bowheer USING utf8mb4) COLLATE utf8mb4_unicode_ci AS dashboard_bowheer,
                    0 AS all_po,
                    0 AS all_invoice,
                    0 AS done_inv_2026,
                    0 AS outs_2026_on_target,
                    COALESCE(ny_po_2026_amount, 0) AS ny_po_on_target_2026,
                    COALESCE(ny_po_2027_amount, 0) AS ny_po_2027,
                    0 AS co_2027_on_po
                FROM tb_po_target_pipeline
                WHERE linked_id_po IS NULL
            ) x
            GROUP BY dashboard_bowheer";

        $rows = $this->db->query($sql)->result_array();
        $summaryMap = [];
        foreach ($rows as $row) {
            $name = (string) $row['dashboard_bowheer'];
            $ny2026 = (float) $row['ny_po_on_target_2026'];
            $co2027 = (float) $row['co_2027_on_po'] + (float) $row['ny_po_2027'];
            $grandTarget = (float) $row['outs_2026_on_target'] + $ny2026;
            $summaryMap[$name] = [
                'pic' => $this->dashboardPic($name),
                'bowheer' => $name,
                'all_po' => (float) $row['all_po'],
                'all_invoice' => (float) $row['all_invoice'],
                'done_inv_2026' => (float) $row['done_inv_2026'],
                'outs_2026_on_target' => (float) $row['outs_2026_on_target'],
                'ny_po_on_target_2026' => $ny2026,
                'grandtotal_target' => $grandTarget,
                'ny_po_total' => $ny2026 + $co2027,
                'co_to_2027' => $co2027,
                'total_outs' => $grandTarget + $co2027
            ];
        }

        $order = $this->dashboardBowheerOrder();
        foreach ($order as $name => $pic) {
            if (!isset($summaryMap[$name])) {
                $summaryMap[$name] = [
                    'pic' => $pic,
                    'bowheer' => $name,
                    'all_po' => 0,
                    'all_invoice' => 0,
                    'done_inv_2026' => 0,
                    'outs_2026_on_target' => 0,
                    'ny_po_on_target_2026' => 0,
                    'grandtotal_target' => 0,
                    'ny_po_total' => 0,
                    'co_to_2027' => 0,
                    'total_outs' => 0
                ];
            }
        }

        uksort($summaryMap, function ($a, $b) use ($order) {
            $ai = isset($order[$a]) ? array_search($a, array_keys($order), true) : 999;
            $bi = isset($order[$b]) ? array_search($b, array_keys($order), true) : 999;
            if ($ai === $bi) {
                return strcmp($a, $b);
            }
            return $ai <=> $bi;
        });

        $result = array_values($summaryMap);
        $totals = [
            'data_count' => 0,
            'all_po' => 0,
            'all_invoice' => 0,
            'done_inv_2026' => 0,
            'outs_2026_on_target' => 0,
            'ny_po_on_target_2026' => 0,
            'grandtotal_target' => 0,
            'ny_po_total' => 0,
            'co_to_2027' => 0,
            'total_outs' => 0
        ];

        foreach ($result as $row) {
            $hasData = false;
            foreach (['all_po', 'done_inv_2026', 'outs_2026_on_target', 'ny_po_on_target_2026', 'co_to_2027'] as $key) {
                if ((float) $row[$key] != 0.0) {
                    $hasData = true;
                    break;
                }
            }
            if ($hasData) {
                $totals['data_count']++;
            }
            foreach (array_keys($totals) as $key) {
                if ($key !== 'data_count') {
                    $totals[$key] += (float) $row[$key];
                }
            }
        }

        return ['rows' => $result, 'totals' => $totals];
    }

    private function rebuildDashboardCache($batchId = null)
    {
        $summary = $this->calculateDashboardSummary();
        $order = array_keys($this->dashboardBowheerOrder());

        $this->db->empty_table('tb_po_dashboard_cache');
        foreach ($summary['rows'] as $row) {
            $sortOrder = array_search($row['bowheer'], $order, true);
            if ($sortOrder === false) {
                $sortOrder = 999;
            }

            $hasData = 0;
            foreach (['all_po', 'done_inv_2026', 'outs_2026_on_target', 'ny_po_on_target_2026', 'co_to_2027'] as $key) {
                if ((float) $row[$key] != 0.0) {
                    $hasData = 1;
                    break;
                }
            }

            $this->db->insert('tb_po_dashboard_cache', [
                'import_batch_id' => $batchId ?: null,
                'pic' => $row['pic'],
                'bowheer' => $row['bowheer'],
                'sort_order' => $sortOrder + 1,
                'all_po' => $row['all_po'],
                'all_invoice' => $row['all_invoice'],
                'done_inv_2026' => $row['done_inv_2026'],
                'outs_2026_on_target' => $row['outs_2026_on_target'],
                'ny_po_on_target_2026' => $row['ny_po_on_target_2026'],
                'grandtotal_target' => $row['grandtotal_target'],
                'ny_po_total' => $row['ny_po_total'],
                'co_to_2027' => $row['co_to_2027'],
                'total_outs' => $row['total_outs'],
                'has_data' => $hasData,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function rebuildDashboardMetricsFromClaims($batchId = null)
    {
        $this->ensureStandaloneSchema();

        $this->db->query("UPDATE tb_po p
            LEFT JOIN (
                SELECT
                    t.id_po,
                    COALESCE(SUM(tc.invoice_amount), 0) AS all_invoice,
                    COALESCE(SUM(CASE WHEN YEAR(tc.invoice_date) = 2026 THEN tc.invoice_amount ELSE 0 END), 0) AS invoice_2026
                FROM tb_po_term_claim tc
                JOIN tb_po_term t ON t.id_term = tc.id_term
                GROUP BY t.id_po
            ) c ON c.id_po = p.id_po
            LEFT JOIN (
                SELECT
                    y.id_po,
                    COALESCE(SUM(CASE WHEN y.target_status = 'TARGET_WEEK' THEN y.amount ELSE 0 END), 0) AS target_week_amount,
                    COALESCE(SUM(CASE WHEN y.target_status = 'CARRY_OVER' THEN y.amount ELSE 0 END), 0) AS carry_over_amount
                FROM (
                    SELECT
                        t.id_po,
                        CONVERT(COALESCE(NULLIF(a.target_status, ''), t.target_status) USING utf8mb4) COLLATE utf8mb4_unicode_ci AS target_status,
                        COALESCE(NULLIF(a.plan_amount, 0), a.allocation_value, 0) AS amount
                    FROM tb_po_term_allocation a
                    JOIN tb_po_term t ON t.id_term = a.id_term
                    UNION ALL
                    SELECT
                        t.id_po,
                        CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci AS target_status,
                        COALESCE(NULLIF(t.plan_amount, 0), t.value, 0) AS amount
                    FROM tb_po_term t
                    WHERE NOT EXISTS (
                        SELECT 1 FROM tb_po_term_allocation a WHERE a.id_term = t.id_term
                    )
                ) y
                GROUP BY y.id_po
            ) b ON b.id_po = p.id_po
            LEFT JOIN (
                SELECT
                    t.id_po,
                    COALESCE(SUM(CASE
                        WHEN YEAR(tc.invoice_date) = 2026
                            AND CONVERT(COALESCE(NULLIF(a.target_status, ''), t.target_status) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                        THEN tc.invoice_amount ELSE 0 END), 0) AS target_week_invoice_2026,
                    COALESCE(SUM(CASE
                        WHEN YEAR(tc.invoice_date) = 2026
                            AND CONVERT(COALESCE(NULLIF(a.target_status, ''), t.target_status) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('CARRY_OVER' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                        THEN tc.invoice_amount ELSE 0 END), 0) AS carry_over_invoice_2026
                FROM tb_po_term_claim tc
                JOIN tb_po_term t ON t.id_term = tc.id_term
                LEFT JOIN tb_po_term_allocation a ON a.id_allocation = tc.id_allocation
                GROUP BY t.id_po
            ) ci ON ci.id_po = p.id_po
            SET
                p.dashboard_all_invoice = GREATEST(COALESCE(c.all_invoice, 0), 0),
                p.dashboard_invoice_2026 = GREATEST(COALESCE(c.invoice_2026, 0), 0),
                p.dashboard_outs_2026 = GREATEST(COALESCE(b.target_week_amount, 0) - COALESCE(ci.target_week_invoice_2026, 0), 0),
                p.dashboard_co_2027 = GREATEST(COALESCE(b.carry_over_amount, 0) - COALESCE(ci.carry_over_invoice_2026, 0), 0)");

        $this->rebuildDashboardCache($batchId);
    }

    public function getDashboardDatatable($post)
    {
        $columns = ['sort_order', 'pic', 'bowheer', 'all_po', 'done_inv_2026', 'outs_2026_on_target', 'ny_po_on_target_2026', 'grandtotal_target', 'ny_po_total', 'co_to_2027', 'total_outs'];
        $mode = strtolower((string) ($post['dashboard_mode'] ?? 'current')) === 'initial' ? 'initial' : 'current';
        $search = trim((string) ($post['search']['value'] ?? ''));
        $start = max(0, (int) ($post['start'] ?? 0));
        $length = (int) ($post['length'] ?? -1);
        $showAllRows = $length < 0;
        if (!$showAllRows && $length <= 0) {
            $length = 10;
        }

        if ($mode === 'initial') {
            $rows = $this->db
                ->select('*')
                ->order_by('sort_order', 'ASC')
                ->order_by('bowheer', 'ASC')
                ->get('tb_po_dashboard_cache')
                ->result_array();
            $adjustments = $this->getDashboardManualClaimAdjustments();
            $initialPipeline = $this->getDashboardInitialPipelineAmounts();
            foreach ($rows as &$row) {
                $key = (string) $row['bowheer'];
                $manualDone = (float) ($adjustments[$key]['manual_done_2026'] ?? 0);
                $manualTargetWeek = (float) ($adjustments[$key]['manual_target_week_2026'] ?? 0);
                $initialNy2026 = (float) ($initialPipeline[$key]['ny_po_2026'] ?? 0);

                $row['all_invoice'] = (float) $row['all_invoice'] - $manualDone;
                $row['done_inv_2026'] = (float) $row['done_inv_2026'] - $manualDone;
                $row['outs_2026_on_target'] = (float) $row['outs_2026_on_target'] + $manualTargetWeek;
                $row['ny_po_on_target_2026'] = $initialNy2026;
                $row['grandtotal_target'] = (float) $row['outs_2026_on_target'] + (float) $row['ny_po_on_target_2026'];
                $row['ny_po_total'] = (float) $row['ny_po_on_target_2026'] + (float) $row['co_to_2027'];
                $row['total_outs'] = (float) $row['grandtotal_target'] + (float) $row['co_to_2027'];
            }
            unset($row);
        } else {
            $summary = $this->calculateDashboardSummary();
            $order = array_keys($this->dashboardBowheerOrder());
            $rows = [];
            foreach ($summary['rows'] as $row) {
                $sortOrder = array_search($row['bowheer'], $order, true);
                if ($sortOrder === false) {
                    $sortOrder = 999;
                }

                $hasData = 0;
                foreach (['all_po', 'done_inv_2026', 'outs_2026_on_target', 'ny_po_on_target_2026', 'co_to_2027'] as $key) {
                    if ((float) ($row[$key] ?? 0) != 0.0) {
                        $hasData = 1;
                        break;
                    }
                }

                $row['sort_order'] = $sortOrder + 1;
                $row['has_data'] = $hasData;
                $rows[] = $row;
            }
        }

        $recordsTotal = count($rows);

        if ($search !== '') {
            $rows = array_values(array_filter($rows, function ($row) use ($search) {
                return stripos((string) $row['pic'], $search) !== false || stripos((string) $row['bowheer'], $search) !== false;
            }));
        }
        $recordsFiltered = count($rows);

        $filteredTotals = [
            'data_count' => 0,
            'all_po' => 0,
            'done_inv_2026' => 0,
            'outs_2026_on_target' => 0,
            'ny_po_on_target_2026' => 0,
            'grandtotal_target' => 0,
            'ny_po_total' => 0,
            'co_to_2027' => 0,
            'total_outs' => 0
        ];

        foreach ($rows as $totalRow) {
            if ((int) $totalRow['has_data'] === 1) {
                $filteredTotals['data_count']++;
            }
            foreach (array_keys($filteredTotals) as $key) {
                if ($key !== 'data_count') {
                    $filteredTotals[$key] += (float) $totalRow[$key];
                }
            }
        }

        $orderIndex = (int) ($post['order'][0]['column'] ?? 0);
        $orderDir = strtolower((string) ($post['order'][0]['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $orderColumn = $columns[$orderIndex] ?? 'sort_order';

        usort($rows, function ($a, $b) use ($orderColumn, $orderDir) {
            $av = $a[$orderColumn] ?? null;
            $bv = $b[$orderColumn] ?? null;
            $result = is_numeric($av) && is_numeric($bv)
                ? ((float) $av <=> (float) $bv)
                : strcmp((string) $av, (string) $bv);

            if ($result === 0 && $orderColumn !== 'sort_order') {
                $result = ((float) ($a['sort_order'] ?? 999) <=> (float) ($b['sort_order'] ?? 999));
            }

            return $orderDir === 'DESC' ? -$result : $result;
        });

        $pageRows = $showAllRows ? array_slice($rows, $start) : array_slice($rows, $start, $length);

        return [
            'draw' => (int) ($post['draw'] ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $pageRows,
            'filteredTotals' => $filteredTotals
        ];
    }

    private function getDashboardManualClaimAdjustments()
    {
        $rows = $this->db->query("SELECT
                COALESCE(NULLIF(p.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer') AS bowheer,
                SUM(CASE WHEN YEAR(tc.invoice_date) = 2026 THEN tc.invoice_amount ELSE 0 END) AS manual_done_2026,
                SUM(CASE WHEN YEAR(tc.invoice_date) = 2026 AND CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci THEN tc.invoice_amount ELSE 0 END) AS manual_target_week_2026
            FROM tb_po_term_claim tc
            JOIN tb_po_term t ON t.id_term = tc.id_term
            JOIN tb_po p ON p.id_po = t.id_po
            LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = p.id_bowheer
            WHERE tc.claim_source = 'MANUAL'
            GROUP BY COALESCE(NULLIF(p.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer')")->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['bowheer']] = [
                'manual_done_2026' => (float) $row['manual_done_2026'],
                'manual_target_week_2026' => (float) $row['manual_target_week_2026']
            ];
        }

        return $map;
    }

    private function getDashboardInitialPipelineAmounts()
    {
        if (!$this->db->table_exists('tb_po_target_pipeline')) {
            return [];
        }

        $rows = $this->db->query("SELECT
                CONVERT(dashboard_bowheer USING utf8mb4) COLLATE utf8mb4_unicode_ci AS bowheer,
                SUM(COALESCE(ny_po_2026_amount, 0)) AS ny_po_2026,
                SUM(COALESCE(ny_po_2027_amount, 0)) AS ny_po_2027
            FROM tb_po_target_pipeline
            GROUP BY CONVERT(dashboard_bowheer USING utf8mb4) COLLATE utf8mb4_unicode_ci")->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['bowheer']] = [
                'ny_po_2026' => (float) ($row['ny_po_2026'] ?? 0),
                'ny_po_2027' => (float) ($row['ny_po_2027'] ?? 0)
            ];
        }

        return $map;
    }

    public function getDashboardInitialTotals()
    {
        $rows = $this->db
            ->select('bowheer, done_inv_2026, outs_2026_on_target, ny_po_on_target_2026')
            ->get('tb_po_dashboard_cache')
            ->result_array();
        $adjustments = $this->getDashboardManualClaimAdjustments();
        $initialPipeline = $this->getDashboardInitialPipelineAmounts();

        $totals = [
            'done_inv_2026' => 0,
            'outs_2026_on_target' => 0,
            'ny_po_on_target_2026' => 0,
            'done_outs_ny_2026' => 0
        ];

        foreach ($rows as $row) {
            $key = (string) $row['bowheer'];
            $manualDone = (float) ($adjustments[$key]['manual_done_2026'] ?? 0);
            $manualTargetWeek = (float) ($adjustments[$key]['manual_target_week_2026'] ?? 0);

            $done = (float) $row['done_inv_2026'] - $manualDone;
            $outs = (float) $row['outs_2026_on_target'] + $manualTargetWeek;
            $ny = (float) ($initialPipeline[$key]['ny_po_2026'] ?? 0);

            $totals['done_inv_2026'] += $done;
            $totals['outs_2026_on_target'] += $outs;
            $totals['ny_po_on_target_2026'] += $ny;
        }

        $totals['done_outs_ny_2026'] = $totals['done_inv_2026'] + $totals['outs_2026_on_target'] + $totals['ny_po_on_target_2026'];
        return $totals;
    }

    public function getDashboardTargetInvoiceBreakdownRows()
    {
        $rows = $this->db
            ->select('pic, bowheer, all_po, done_inv_2026, outs_2026_on_target, ny_po_on_target_2026, sort_order, has_data')
            ->order_by('sort_order', 'ASC')
            ->order_by('bowheer', 'ASC')
            ->get('tb_po_dashboard_cache')
            ->result_array();

        if (empty($rows)) {
            $summary = $this->calculateDashboardSummary();
            $rows = $summary['rows'] ?? [];
        }

        $result = [];
        foreach ($rows as $row) {
            $project = trim((string) ($row['bowheer'] ?? ''));
            if ($project === '') {
                continue;
            }

            $target = (float) ($row['all_po'] ?? 0)
                + (float) ($row['done_inv_2026'] ?? 0)
                + (float) ($row['outs_2026_on_target'] ?? 0)
                + (float) ($row['ny_po_on_target_2026'] ?? 0);
            $achieved = (float) ($row['done_inv_2026'] ?? 0);

            if ((int) ($row['has_data'] ?? 1) !== 1 && abs($target) < 0.5 && abs($achieved) < 0.5) {
                continue;
            }

            $result[] = [
                'id_bowheer' => 0,
                'project' => $project,
                'pic' => trim((string) ($row['pic'] ?? '')) ?: $this->dashboardPic($project),
                'row_type' => 'DASHBOARD',
                'po_number' => '-',
                'sub_po' => '-',
                'detail_po' => 'Dashboard Target PO',
                'remarks' => 'Target: PO 2026 + Done Inv 2026 + Outs 2026 On Target + NY PO On Target 2026. Achieved: Done Inv 2026.',
                'regional' => '-',
                'area' => '-',
                'month' => '-',
                'month_label' => '-',
                'week' => '-',
                'date' => '',
                'date_label' => '-',
                'target' => $target,
                'achieved' => $achieved
            ];
        }

        return $result;
    }

    private function getDashboardTargetWeekClaimAdjustments()
    {
        $rows = $this->db->query("SELECT
                CONVERT(COALESCE(NULLIF(p.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS bowheer,
                SUM(CASE WHEN YEAR(tc.invoice_date) = 2026 THEN tc.invoice_amount ELSE 0 END) AS target_week_2026
            FROM tb_po_term_claim tc
            JOIN tb_po_term t ON t.id_term = tc.id_term
            JOIN tb_po p ON p.id_po = t.id_po
            LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = p.id_bowheer
            LEFT JOIN tb_po_term_allocation a ON a.id_allocation = tc.id_allocation
            WHERE tc.invoice_date IS NOT NULL
                AND COALESCE(CONVERT(NULLIF(a.target_status, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci, CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci) = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
            GROUP BY CONVERT(COALESCE(NULLIF(p.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci")->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['bowheer']] = [
                'target_week_2026' => (float) ($row['target_week_2026'] ?? 0)
            ];
        }

        return $map;
    }

    public function getPODatatable($post)
    {
        $columns = ['p.id_po', 'p.po_number', 'p.po_date', 'current_release_value', 'total_invoiced', 'remaining', 'nama_bowheer'];
        $search = trim((string) ($post['search']['value'] ?? ''));
        $start = max(0, (int) ($post['start'] ?? 0));
        $length = (int) ($post['length'] ?? 25);
        if ($length <= 0 || $length > 100) {
            $length = 25;
        }

        $baseSelect = "p.id_po, p.po_number, p.po_date, COALESCE(NULLIF(p.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer') AS nama_bowheer,
            COALESCE((SELECT release_value FROM tb_po_amend a WHERE a.id_po = p.id_po ORDER BY a.amend_no DESC LIMIT 1), p.total_value) AS current_release_value,
            COALESCE((SELECT SUM(tc.invoice_amount) FROM tb_po_term_claim tc JOIN tb_po_term t ON tc.id_term = t.id_term WHERE t.id_po = p.id_po), 0) AS total_invoiced";

        $recordsTotal = (int) $this->db->count_all('tb_po');

        $this->db->select($baseSelect, false)
            ->from('tb_po p')
            ->join('tb_bowheer_po bp', 'bp.id_bowheer = p.id_bowheer', 'left');

        if ($search !== '') {
            $this->db->group_start()
                ->like('p.po_number', $search)
                ->or_like('p.dashboard_bowheer', $search)
                ->or_like('bp.bowheer', $search)
                ->group_end();
        }

        $recordsFiltered = (int) $this->db->count_all_results('', false);

        $orderIndex = (int) ($post['order'][0]['column'] ?? 2);
        $orderDir = strtolower((string) ($post['order'][0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $orderColumn = $columns[$orderIndex] ?? 'p.po_date';

        $rows = $this->db
            ->order_by($orderColumn, $orderDir)
            ->limit($length, $start)
            ->get()
            ->result_array();

        foreach ($rows as &$row) {
            $remaining = (float) $row['current_release_value'] - (float) $row['total_invoiced'];
            $row['remaining'] = $remaining > 0 ? $remaining : 0;
            $row['sla'] = 'AMAN';
        }
        unset($row);

        return [
            'draw' => (int) ($post['draw'] ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows
        ];
    }

    public function getProjectWeekSummary()
    {
        return $this->db->query("SELECT
                nama_bowheer,
                target_year,
                target_week,
                target_week_start,
                target_week_end,
                COUNT(*) AS total_term,
                SUM(amount) AS amount
            FROM (
                SELECT
                    CONVERT(COALESCE(bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS nama_bowheer,
                    a.target_year,
                    a.target_week,
                    a.target_week_start,
                    a.target_week_end,
                    COALESCE(NULLIF(a.plan_amount, 0), a.allocation_value) AS amount
                FROM tb_po_term_allocation a
                JOIN tb_po_term t ON t.id_term = a.id_term
                JOIN tb_po p ON p.id_po = t.id_po
                LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = p.id_bowheer
                LEFT JOIN tb_master_bowheer_bilco b ON b.id_bowheer = p.id_bowheer
                WHERE CONVERT(a.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                UNION ALL
                SELECT
                    CONVERT(COALESCE(bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS nama_bowheer,
                    t.target_year,
                    t.target_week,
                    t.target_week_start,
                    t.target_week_end,
                    COALESCE(NULLIF(t.plan_amount, 0), t.value) AS amount
                FROM tb_po_term t
                JOIN tb_po p ON p.id_po = t.id_po
                LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = p.id_bowheer
                LEFT JOIN tb_master_bowheer_bilco b ON b.id_bowheer = p.id_bowheer
                WHERE CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                    AND NOT EXISTS (
                        SELECT 1 FROM tb_po_term_allocation a WHERE a.id_term = t.id_term
                    )
                UNION ALL
                SELECT
                    CONVERT(pl.dashboard_bowheer USING utf8mb4) COLLATE utf8mb4_unicode_ci AS nama_bowheer,
                    pl.target_year,
                    pl.target_week,
                    pl.target_week_start,
                    pl.target_week_end,
                    pl.plan_amount AS amount
                FROM tb_po_target_pipeline pl
                WHERE CONVERT(pl.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                    AND pl.linked_id_po IS NULL
            ) x
            GROUP BY nama_bowheer, target_year, target_week, target_week_start, target_week_end
            ORDER BY target_year ASC, target_week ASC, nama_bowheer ASC")->result_array();
    }

    public function getCarryOverSummary()
    {
        return $this->db->query("SELECT
                nama_bowheer,
                term_index,
                COUNT(*) AS total_term,
                SUM(amount) AS amount
            FROM (
                SELECT
                    CONVERT(COALESCE(bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS nama_bowheer,
                    t.term_index,
                    COALESCE(NULLIF(a.plan_amount, 0), a.allocation_value) AS amount
                FROM tb_po_term_allocation a
                JOIN tb_po_term t ON t.id_term = a.id_term
                JOIN tb_po p ON p.id_po = t.id_po
                LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = p.id_bowheer
                LEFT JOIN tb_master_bowheer_bilco b ON b.id_bowheer = p.id_bowheer
                WHERE CONVERT(a.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('CARRY_OVER' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                UNION ALL
                SELECT
                    CONVERT(COALESCE(bp.bowheer, b.nama_bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS nama_bowheer,
                    t.term_index,
                    COALESCE(NULLIF(t.plan_amount, 0), t.value) AS amount
                FROM tb_po_term t
                JOIN tb_po p ON p.id_po = t.id_po
                LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = p.id_bowheer
                LEFT JOIN tb_master_bowheer_bilco b ON b.id_bowheer = p.id_bowheer
                WHERE CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('CARRY_OVER' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                    AND NOT EXISTS (
                        SELECT 1 FROM tb_po_term_allocation a WHERE a.id_term = t.id_term
                    )
                UNION ALL
                SELECT
                    CONVERT(pl.dashboard_bowheer USING utf8mb4) COLLATE utf8mb4_unicode_ci AS nama_bowheer,
                    pl.term_index,
                    pl.plan_amount AS amount
                FROM tb_po_target_pipeline pl
                WHERE CONVERT(pl.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('CARRY_OVER' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                    AND pl.linked_id_po IS NULL
            ) x
            GROUP BY nama_bowheer, term_index
            ORDER BY nama_bowheer ASC, term_index ASC")->result_array();
    }

    public function getComparisonMatrix($fromMonth = null, $toMonth = null, $groupBy = 'month', $invoiceOnly = false)
    {
        $bounds = $this->resolveComparisonBounds($fromMonth, $toMonth);
        $groupBy = $groupBy === 'week' ? 'week' : 'month';
        $periods = $groupBy === 'week'
            ? $this->buildWeekList($bounds['from'], $bounds['to'])
            : $this->buildMonthList($bounds['from'], $bounds['to']);

        $projects = $this->db
            ->select('id_bowheer, no_urut, pic, bowheer')
            ->order_by('no_urut', 'ASC')
            ->order_by('bowheer', 'ASC')
            ->get('tb_bowheer_po')
            ->result_array();

        $projectMap = [];
        foreach ($projects as $project) {
            $id = (int) $project['id_bowheer'];
            $projectMap[$id] = [
                'id_bowheer' => $id,
                'project' => $project['bowheer'],
                'pic' => $project['pic'],
                'months' => [],
                'period_history' => [
                    'target' => [],
                    'achieved' => [],
                    'target_outstanding' => []
                ],
                'total_target' => 0,
                'total_achieved' => 0,
                'total_target_invoiced' => 0,
                'deviasi_by_po' => 0
            ];

            foreach ($periods as $period) {
                $projectMap[$id]['months'][$period['key']] = [
                    'target' => 0,
                    'achieved' => 0,
                    'cumulative_target' => 0,
                    'cumulative_achieved' => 0,
                    'cumulative' => 0,
                    'cumulative_breakdown' => [],
                    'effective_target' => 0,
                    'cumulative_percent' => 0,
                    'target_invoiced' => 0,
                    'deviasi_by_po' => 0,
                    'deviasi_by_po_percent' => 0
                ];
            }
        }

        $targetRows = $this->db->query("SELECT
                p.id_bowheer,
                a.target_week_start,
                a.target_week_end,
                COALESCE(NULLIF(a.plan_amount, 0), a.allocation_value) AS amount,
                COALESCE(tc_alloc.invoice_amount, tc_term.invoice_amount, 0) AS invoiced_amount,
                COALESCE(tc_alloc.invoice_date, tc_term.invoice_date) AS claim_invoice_date
            FROM tb_po_term_allocation a
            JOIN tb_po_term t ON t.id_term = a.id_term
            JOIN tb_po p ON p.id_po = t.id_po
            LEFT JOIN (
                SELECT id_allocation, SUM(invoice_amount) AS invoice_amount, MAX(invoice_date) AS invoice_date
                FROM tb_po_term_claim
                WHERE id_allocation IS NOT NULL
                GROUP BY id_allocation
            ) tc_alloc ON tc_alloc.id_allocation = a.id_allocation
            LEFT JOIN (
                SELECT id_term, SUM(invoice_amount) AS invoice_amount, MAX(invoice_date) AS invoice_date
                FROM tb_po_term_claim
                GROUP BY id_term
            ) tc_term ON tc_term.id_term = t.id_term
            WHERE CONVERT(a.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                AND a.target_week_start IS NOT NULL
                AND a.target_week_end IS NOT NULL
            UNION ALL
            SELECT
                p.id_bowheer,
                t.target_week_start,
                t.target_week_end,
                COALESCE(NULLIF(t.plan_amount, 0), t.value) AS amount,
                COALESCE(tc.invoice_amount, 0) AS invoiced_amount,
                tc.invoice_date AS claim_invoice_date
            FROM tb_po_term t
            JOIN tb_po p ON p.id_po = t.id_po
            LEFT JOIN (
                SELECT id_term, SUM(invoice_amount) AS invoice_amount, MAX(invoice_date) AS invoice_date
                FROM tb_po_term_claim
                GROUP BY id_term
            ) tc ON tc.id_term = t.id_term
            WHERE CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                AND t.target_week_start IS NOT NULL
                AND t.target_week_end IS NOT NULL
                AND NOT EXISTS (
                    SELECT 1 FROM tb_po_term_allocation a WHERE a.id_term = t.id_term
                )
            UNION ALL
            SELECT
                pl.id_bowheer,
                pl.target_week_start,
                pl.target_week_end,
                pl.plan_amount AS amount,
                0 AS invoiced_amount,
                CAST(NULL AS DATE) AS claim_invoice_date
            FROM tb_po_target_pipeline pl
            WHERE CONVERT(pl.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                AND pl.linked_id_po IS NULL
                AND pl.target_week_start IS NOT NULL
                AND pl.target_week_end IS NOT NULL")->result_array();

        foreach ($targetRows as $row) {
            $id = (int) $row['id_bowheer'];
            if (!isset($projectMap[$id])) {
                continue;
            }

            $periodKey = $groupBy === 'week'
                ? $this->weekKey((int) date('Y', strtotime($row['target_week_start'])), (int) $this->weekNumberFromPeriod($row['target_week_start'], $row['target_week_end']))
                : $this->majorityMonthKey($row['target_week_start'], $row['target_week_end']);

            $amount = (float) $row['amount'];
            $targetInvoiced = (float) ($row['invoiced_amount'] ?? 0);
            $targetOutstanding = $targetInvoiced > 0.000001 ? 0 : $amount;
            if (!isset($projectMap[$id]['period_history']['target'][$periodKey])) {
                $projectMap[$id]['period_history']['target'][$periodKey] = 0;
            }
            $projectMap[$id]['period_history']['target'][$periodKey] += $amount;
            if (!isset($projectMap[$id]['period_history']['target_outstanding'][$periodKey])) {
                $projectMap[$id]['period_history']['target_outstanding'][$periodKey] = 0;
            }
            $projectMap[$id]['period_history']['target_outstanding'][$periodKey] += $targetOutstanding;

            if (!isset($projectMap[$id]['months'][$periodKey])) {
                continue;
            }

            $projectMap[$id]['months'][$periodKey]['target'] += $amount;
            $projectMap[$id]['months'][$periodKey]['target_invoiced'] += $targetInvoiced;
            $projectMap[$id]['months'][$periodKey]['deviasi_by_po'] += $targetOutstanding;
            $projectMap[$id]['total_target'] += $amount;
            $projectMap[$id]['total_target_invoiced'] += $targetInvoiced;
            $projectMap[$id]['deviasi_by_po'] += $targetOutstanding;
        }

        $claimRows = $this->db->query("SELECT
                p.id_bowheer,
                tc.invoice_date,
                tc.invoice_amount
            FROM tb_po_term_claim tc
            JOIN tb_po_term t ON t.id_term = tc.id_term
            JOIN tb_po p ON p.id_po = t.id_po
            WHERE tc.invoice_date IS NOT NULL")->result_array();

        foreach ($claimRows as $row) {
            $id = (int) $row['id_bowheer'];
            if (!isset($projectMap[$id])) {
                continue;
            }

            $periodKey = $groupBy === 'week'
                ? $this->weekKeyFromDate($row['invoice_date'])
                : $this->monthKeyFromInvoiceWeek($row['invoice_date']);

            $amount = (float) $row['invoice_amount'];
            if (!isset($projectMap[$id]['period_history']['achieved'][$periodKey])) {
                $projectMap[$id]['period_history']['achieved'][$periodKey] = 0;
            }
            $projectMap[$id]['period_history']['achieved'][$periodKey] += $amount;

            if (!isset($projectMap[$id]['months'][$periodKey])) {
                continue;
            }

            $projectMap[$id]['months'][$periodKey]['achieved'] += $amount;
            $projectMap[$id]['total_achieved'] += $amount;
        }

        $totals = [
            'months' => [], 
            'total_target' => 0,
            'total_achieved' => 0,
            'deviasi_by_po' => 0
        ];
        foreach ($periods as $period) {
            $totals['months'][$period['key']] = [
                'target' => 0,
                'achieved' => 0,
                'cumulative' => 0,
                'cumulative_breakdown' => [],
                'effective_target' => 0,
                'cumulative_percent' => 0,
                'target_invoiced' => 0,
                'deviasi_by_po' => 0,
                'deviasi_by_po_percent' => 0
            ];
        }

        foreach ($projectMap as &$project) {
            $project['total_target'] = 0;
            $project['deviasi_by_po'] = 0;
            $project['total_effective_deviasi_by_po'] = 0;
            foreach ($periods as $period) {
                $periodKey = (string) $period['key'];
                $rawTarget = (float) $project['months'][$periodKey]['target'];
                $achieved = (float) $project['months'][$periodKey]['achieved'];
                $deviasiByPo = $this->sumComparisonUninvoicedTargetAmount((int) $project['id_bowheer'], $periodKey, $groupBy);
                $cumulativeBreakdown = $this->comparisonCumulativeBreakdown((int) $project['id_bowheer'], $periodKey, $groupBy);
                $cumulative = array_sum(array_map(static function ($item) {
                    return (float) ($item['amount'] ?? 0);
                }, $cumulativeBreakdown));
                $target = $this->sumComparisonLockedTargetAmount((int) $project['id_bowheer'], $periodKey, $groupBy);
                $effectiveTarget = $target + $cumulative;
                $project['months'][$periodKey]['raw_target'] = $rawTarget;
                $project['months'][$periodKey]['target'] = $target;
                $project['months'][$periodKey]['cumulative'] = $cumulative;
                $project['months'][$periodKey]['cumulative_breakdown'] = $cumulativeBreakdown;
                $project['months'][$periodKey]['effective_target'] = $effectiveTarget;
                $project['months'][$periodKey]['deviasi_by_po'] = $deviasiByPo;
                $project['months'][$periodKey]['percent'] = $target > 0 ? ($achieved / $target) * 100 : ($achieved > 0 ? 100 : 0);
                $project['months'][$periodKey]['cumulative_percent'] = $effectiveTarget > 0 ? ($achieved / $effectiveTarget) * 100 : ($achieved > 0 ? 100 : 0);
                $project['months'][$periodKey]['deviasi_by_po_percent'] = $target > 0 ? ($deviasiByPo / $target) * 100 : 0;
                $project['total_target'] += $target;
                $project['deviasi_by_po'] += $deviasiByPo;
                $project['total_effective_deviasi_by_po'] += $cumulative + $deviasiByPo;
                $totals['months'][$period['key']]['target'] += $target;
                $totals['months'][$period['key']]['achieved'] += $achieved;
                $totals['months'][$period['key']]['cumulative'] += $cumulative;
                foreach ($cumulativeBreakdown as $breakdownItem) {
                    $breakdownKey = (string) ($breakdownItem['key'] ?? '');
                    if ($breakdownKey === '') {
                        continue;
                    }
                    if (!isset($totals['months'][$period['key']]['cumulative_breakdown'][$breakdownKey])) {
                        $totals['months'][$period['key']]['cumulative_breakdown'][$breakdownKey] = [
                            'key' => $breakdownKey,
                            'label' => (string) ($breakdownItem['label'] ?? $breakdownKey),
                            'amount' => 0
                        ];
                    }
                    $totals['months'][$period['key']]['cumulative_breakdown'][$breakdownKey]['amount'] += (float) ($breakdownItem['amount'] ?? 0);
                }
                $totals['months'][$period['key']]['effective_target'] += $effectiveTarget;
                $totals['months'][$period['key']]['target_invoiced'] += (float) $project['months'][$periodKey]['target_invoiced'];
                $totals['months'][$period['key']]['deviasi_by_po'] += $deviasiByPo;
            }

            $project['total_effective_target'] = 0;
            foreach ($periods as $period) {
                $project['total_effective_target'] += (float) ($project['months'][$period['key']]['effective_target'] ?? 0);
            }
            $project['deviasi'] = max($project['total_target'] - $project['total_achieved'], 0);
            $project['achieved_percent'] = $project['total_target'] > 0 ? ($project['total_achieved'] / $project['total_target']) * 100 : ($project['total_achieved'] > 0 ? 100 : 0);
            $project['deviasi_percent'] = max(100 - $project['achieved_percent'], 0);
            $project['deviasi_by_po_percent'] = $project['total_target'] > 0 ? ($project['deviasi_by_po'] / $project['total_target']) * 100 : 0;
            $project['cumulative_deviasi'] = max($project['total_effective_target'] - $project['total_achieved'], 0);
            $project['cumulative_achieved_percent'] = $project['total_effective_target'] > 0 ? ($project['total_achieved'] / $project['total_effective_target']) * 100 : ($project['total_achieved'] > 0 ? 100 : 0);
            $project['cumulative_deviasi_percent'] = max(100 - $project['cumulative_achieved_percent'], 0);
            unset($project['period_history']);
        }
        unset($project);

        foreach ($totals['months'] as $monthKey => &$monthTotal) {
            if (!empty($monthTotal['cumulative_breakdown']) && is_array($monthTotal['cumulative_breakdown'])) {
                $monthTotal['cumulative_breakdown'] = array_values(array_filter($monthTotal['cumulative_breakdown'], static function ($item) {
                    return (float) ($item['amount'] ?? 0) > 0.000001;
                }));
            }
            $monthTotal['percent'] = $monthTotal['target'] > 0 ? ($monthTotal['achieved'] / $monthTotal['target']) * 100 : ($monthTotal['achieved'] > 0 ? 100 : 0);
            $monthTotal['cumulative_percent'] = $monthTotal['effective_target'] > 0 ? ($monthTotal['achieved'] / $monthTotal['effective_target']) * 100 : ($monthTotal['achieved'] > 0 ? 100 : 0);
            $monthTotal['deviasi_by_po_percent'] = $monthTotal['target'] > 0 ? ((float) ($monthTotal['deviasi_by_po'] ?? 0) / (float) $monthTotal['target']) * 100 : 0;
            $totals['total_target'] += $monthTotal['target'];
            $totals['total_achieved'] += $monthTotal['achieved'];
            $totals['deviasi_by_po'] += (float) ($monthTotal['deviasi_by_po'] ?? 0);
        }
        unset($monthTotal);

        $totals['deviasi'] = max($totals['total_target'] - $totals['total_achieved'], 0);
        $totals['achieved_percent'] = $totals['total_target'] > 0 ? ($totals['total_achieved'] / $totals['total_target']) * 100 : ($totals['total_achieved'] > 0 ? 100 : 0);
        $totals['deviasi_percent'] = max(100 - $totals['achieved_percent'], 0);
        $totals['deviasi_by_po_percent'] = $totals['total_target'] > 0 ? ($totals['deviasi_by_po'] / $totals['total_target']) * 100 : 0;
        $totals['total_effective_target'] = 0;
        $totals['total_effective_deviasi_by_po'] = 0;
        foreach ($totals['months'] as $monthTotal) {
            $totals['total_effective_target'] += (float) ($monthTotal['effective_target'] ?? 0);
            $totals['total_effective_deviasi_by_po'] += (float) ($monthTotal['cumulative'] ?? 0)
                + (float) ($monthTotal['deviasi_by_po'] ?? 0);
        }
        $totals['cumulative_deviasi'] = max($totals['total_effective_target'] - $totals['total_achieved'], 0);
        $totals['cumulative_achieved_percent'] = $totals['total_effective_target'] > 0 ? ($totals['total_achieved'] / $totals['total_effective_target']) * 100 : ($totals['total_achieved'] > 0 ? 100 : 0);
        $totals['cumulative_deviasi_percent'] = max(100 - $totals['cumulative_achieved_percent'], 0);

        foreach ($periods as &$period) {
            $periodKey = (string) ($period['key'] ?? '');
            $period['cumulative_periods'] = $totals['months'][$periodKey]['cumulative_breakdown'] ?? [];
        }
        unset($period);

        $rows = array_values($projectMap);
        if ($invoiceOnly) {
            $rows = array_values(array_filter($rows, function ($row) {
                return (float) $row['total_achieved'] > 0;
            }));
        }

        return [
            'from' => $bounds['from'],
            'to' => $bounds['to'],
            'group_by' => $groupBy,
            'invoice_only' => $invoiceOnly,
            'months' => $periods,
            'rows' => $rows,
            'totals' => $totals
        ];
    }

    public function syncEmrNroComparisonClaims($fromMonth = null, $toMonth = null, $userId = 0)
    {
        $this->ensureStandaloneSchema();
        $bounds = $this->resolveComparisonBounds($fromMonth, $toMonth);
        $fromTime = strtotime($bounds['from'] . '-01');
        $toTime = strtotime($bounds['to'] . '-01');

        $emr = $this->db
            ->select('id_bowheer')
            ->where("UPPER(TRIM(CONVERT(bowheer USING utf8mb4) COLLATE utf8mb4_unicode_ci)) = CONVERT('PT EMR - NRO' USING utf8mb4) COLLATE utf8mb4_unicode_ci", null, false)
            ->limit(1)
            ->get('tb_bowheer_po')
            ->row_array();

        $idBowheer = (int) ($emr['id_bowheer'] ?? 0);
        if ($idBowheer <= 0) {
            return [
                'status' => false,
                'synced' => 0,
                'po_count' => 0,
                'message' => 'Bowheer PT EMR - NRO tidak ditemukan'
            ];
        }

        $targetRows = $this->db->query("SELECT DISTINCT
                p.po_number,
                COALESCE(a.target_week_start, t.target_week_start) AS target_week_start,
                COALESCE(a.target_week_end, t.target_week_end) AS target_week_end
            FROM tb_po p
            JOIN tb_po_term t ON t.id_po = p.id_po
            LEFT JOIN tb_po_term_allocation a ON a.id_term = t.id_term
            WHERE p.id_bowheer = ?
                AND (
                    (
                        CONVERT(a.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                        AND a.target_week_start IS NOT NULL
                        AND a.target_week_end IS NOT NULL
                    )
                    OR (
                        CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                        AND t.target_week_start IS NOT NULL
                        AND t.target_week_end IS NOT NULL
                        AND NOT EXISTS (
                            SELECT 1 FROM tb_po_term_allocation ax WHERE ax.id_term = t.id_term
                        )
                    )
                )", [$idBowheer])->result_array();

        $poNumbers = [];
        foreach ($targetRows as $row) {
            $periodMonth = $this->majorityMonthKey($row['target_week_start'], $row['target_week_end']);
            $periodTime = strtotime($periodMonth . '-01');
            if (!$periodTime || $periodTime < $fromTime || $periodTime > $toTime) {
                continue;
            }

            $poNumber = trim((string) ($row['po_number'] ?? ''));
            if ($poNumber !== '') {
                $poNumbers[strtoupper($poNumber)] = $poNumber;
            }
        }

        $extendedStart = date('Y-m-d', strtotime($bounds['from'] . '-01 -6 days'));
        $extendedEnd = date('Y-m-d', strtotime(date('Y-m-t', strtotime($bounds['to'] . '-01')) . ' +6 days'));

        if ($this->db->table_exists('tb_myrep_po_termin') && $this->db->table_exists('tb_myrep_po_header')) {
            $invoiceRows = $this->db->query("SELECT DISTINCT
                    h.po_number,
                    t.invoice_date
                FROM tb_myrep_po_termin t
                JOIN tb_myrep_po_header h ON h.id_po_header = t.id_po_header
                JOIN tb_po p ON CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(h.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci
                WHERE p.id_bowheer = ?
                    AND t.invoice_date IS NOT NULL
                    AND DATE(t.invoice_date) BETWEEN ? AND ?", [$idBowheer, $extendedStart, $extendedEnd])->result_array();

            foreach ($invoiceRows as $row) {
                $periodMonth = $this->monthKeyFromInvoiceWeek($row['invoice_date']);
                $periodTime = strtotime($periodMonth . '-01');
                if (!$periodTime || $periodTime < $fromTime || $periodTime > $toTime) {
                    continue;
                }

                $poNumber = trim((string) ($row['po_number'] ?? ''));
                if ($poNumber !== '') {
                    $poNumbers[strtoupper($poNumber)] = $poNumber;
                }
            }
        }

        $claimRows = $this->db->query("SELECT DISTINCT
                p.po_number,
                tc.invoice_date
            FROM tb_po_term_claim tc
            JOIN tb_po_term t ON t.id_term = tc.id_term
            JOIN tb_po p ON p.id_po = t.id_po
            WHERE p.id_bowheer = ?
                AND tc.claim_source = 'MYREP_SYNC'
                AND tc.invoice_date IS NOT NULL
                AND DATE(tc.invoice_date) BETWEEN ? AND ?", [$idBowheer, $extendedStart, $extendedEnd])->result_array();

        foreach ($claimRows as $row) {
            $periodMonth = $this->monthKeyFromInvoiceWeek($row['invoice_date']);
            $periodTime = strtotime($periodMonth . '-01');
            if (!$periodTime || $periodTime < $fromTime || $periodTime > $toTime) {
                continue;
            }

            $poNumber = trim((string) ($row['po_number'] ?? ''));
            if ($poNumber !== '') {
                $poNumbers[strtoupper($poNumber)] = $poNumber;
            }
        }

        $summary = [
            'status' => true,
            'synced' => 0,
            'po_count' => count($poNumbers),
            'matched' => 0,
            'inserted' => 0,
            'updated' => 0,
            'deleted' => 0,
            'skipped' => 0,
            'unchanged' => 0,
            'unmatched' => [],
            'message' => 'Sync comparison PT EMR - NRO selesai'
        ];

        if (empty($poNumbers)) {
            return $summary;
        }

        if (!$this->db->table_exists('tb_myrep_po_termin') || !$this->db->table_exists('tb_myrep_po_header')) {
            $summary['status'] = false;
            $summary['message'] = 'Tabel MyRep tidak ditemukan';
            return $summary;
        }

        $invoiceValueSql = $this->db->field_exists('invoice_value', 'tb_myrep_po_termin')
            ? 'COALESCE(t.invoice_value, t.termin_value, 0)'
            : 'COALESCE(t.termin_value, 0)';

        $myrepRows = [];
        foreach (array_chunk(array_values($poNumbers), 500) as $poChunk) {
            $poInSql = implode(',', array_map(function ($poNumber) {
                return "CONVERT(" . $this->db->escape($poNumber) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci";
            }, $poChunk));

            $rows = $this->db
                ->select("t.id_po_termin,
                    t.id_po_header,
                    t.termin_no,
                    t.termin_value,
                    {$invoiceValueSql} AS invoice_amount,
                    t.status_termin,
                    t.invoice_date,
                    t.invoice_number,
                    p.po_number,
                    p.po_type,
                    p.po_category", false)
                ->from('tb_myrep_po_termin t')
                ->join('tb_myrep_po_header p', 'p.id_po_header = t.id_po_header', 'inner')
                ->where("CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci IN ({$poInSql})", null, false)
                ->order_by('p.po_number', 'ASC')
                ->order_by('t.termin_no', 'ASC')
                ->order_by('t.invoice_date IS NULL', 'ASC', false)
                ->order_by('t.invoice_date', 'DESC')
                ->order_by('t.id_po_termin', 'DESC')
                ->get()
                ->result_array();

            foreach ($rows as $row) {
                $termNo = (int) ($row['termin_no'] ?? 0);
                $poNumber = strtoupper(trim((string) ($row['po_number'] ?? '')));
                $key = $poNumber . '|' . $termNo;
                if ($poNumber !== '' && $termNo > 0 && !isset($myrepRows[$key])) {
                    $myrepRows[$key] = $row;
                }
            }
        }

        foreach ($myrepRows as $row) {
            $result = $this->syncMyRepTerminRowToMonitor($row, $userId, null, false);
            $action = (string) ($result['action'] ?? 'skipped');
            if (!empty($result['status'])) {
                $summary['matched']++;
                $summary['synced']++;
            }
            if (isset($summary[$action])) {
                $summary[$action]++;
            } else {
                $summary['skipped']++;
            }
            if (empty($result['status']) && !empty($result['po_number'])) {
                $summary['unmatched'][] = [
                    'po_number' => $result['po_number'],
                    'term' => $result['term'] ?? null,
                    'message' => $result['message'] ?? 'Unmatched'
                ];
            }
        }

        return $summary;
    }

    public function getBreakdownTargetInvoiceRows($fromMonth = null, $toMonth = null)
    {
        $bounds = $this->resolveComparisonBounds($fromMonth, $toMonth);
        $startDate = $bounds['from'] . '-01';
        $endDate = date('Y-m-t', strtotime($bounds['to'] . '-01'));

        $targetRows = $this->db->query("SELECT *
            FROM (
                SELECT
                    p.id_bowheer,
                    CONVERT(COALESCE(NULLIF(p.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS project,
                    CONVERT(COALESCE(bp.pic, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS pic,
                    CONVERT('TARGET' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS row_type,
                    CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci AS po_number,
                    CONVERT(COALESCE(NULLIF(a.no_po_sub, ''), '-') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS sub_po,
                    CONVERT(COALESCE(NULLIF(a.detail_po, ''), '-') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS detail_po,
                    CONVERT(COALESCE(NULLIF(a.remarks, ''), '-') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS remarks,
                    CONVERT(COALESCE(NULLIF(a.regional, ''), '-') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS regional,
                    CONVERT(COALESCE(NULLIF(a.kota_po, ''), '-') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS area,
                    a.target_week_start AS period_start,
                    a.target_week_end AS period_end,
                    COALESCE(NULLIF(a.plan_amount, 0), a.allocation_value) AS target_amount,
                    0 AS achieved_amount
                FROM tb_po_term_allocation a
                JOIN tb_po_term t ON t.id_term = a.id_term
                JOIN tb_po p ON p.id_po = t.id_po
                LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = p.id_bowheer
                WHERE CONVERT(a.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                    AND a.target_week_start IS NOT NULL
                    AND a.target_week_end IS NOT NULL
                UNION ALL
                SELECT
                    p.id_bowheer,
                    CONVERT(COALESCE(NULLIF(p.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS project,
                    CONVERT(COALESCE(bp.pic, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS pic,
                    CONVERT('TARGET' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS row_type,
                    CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci AS po_number,
                    CONVERT('-' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS sub_po,
                    CONVERT('-' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS detail_po,
                    CONVERT('-' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS remarks,
                    CONVERT('-' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS regional,
                    CONVERT('-' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS area,
                    t.target_week_start AS period_start,
                    t.target_week_end AS period_end,
                    COALESCE(NULLIF(t.plan_amount, 0), t.value) AS target_amount,
                    0 AS achieved_amount
                FROM tb_po_term t
                JOIN tb_po p ON p.id_po = t.id_po
                LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = p.id_bowheer
                WHERE CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                    AND t.target_week_start IS NOT NULL
                    AND t.target_week_end IS NOT NULL
                    AND NOT EXISTS (
                        SELECT 1 FROM tb_po_term_allocation a WHERE a.id_term = t.id_term
                    )
                UNION ALL
                SELECT
                    pl.id_bowheer,
                    CONVERT(COALESCE(NULLIF(pl.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS project,
                    CONVERT(COALESCE(bp.pic, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS pic,
                    CONVERT('TARGET' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS row_type,
                    CONVERT('-' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS po_number,
                    CONVERT('-' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS sub_po,
                    CONVERT(COALESCE(NULLIF(pl.detail_po, ''), '-') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS detail_po,
                    CONVERT(COALESCE(NULLIF(pl.remarks, ''), '-') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS remarks,
                    CONVERT(COALESCE(NULLIF(pl.regional, ''), '-') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS regional,
                    CONVERT(COALESCE(NULLIF(pl.kota_po, ''), '-') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS area,
                    pl.target_week_start AS period_start,
                    pl.target_week_end AS period_end,
                    COALESCE(pl.ny_po_2026_amount, pl.plan_amount, 0) AS target_amount,
                    0 AS achieved_amount
                FROM tb_po_target_pipeline pl
                LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = pl.id_bowheer
                WHERE CONVERT(pl.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                    AND pl.linked_id_po IS NULL
                    AND pl.target_week_start IS NOT NULL
                    AND pl.target_week_end IS NOT NULL
            ) x
            WHERE DATE(period_start) <= ?
                AND DATE(period_end) >= ?", [$endDate, $startDate])->result_array();

        $claimRows = $this->db->query("SELECT
                p.id_bowheer,
                CONVERT(COALESCE(NULLIF(p.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS project,
                CONVERT(COALESCE(bp.pic, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS pic,
                CONVERT('ACHIEVED' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS row_type,
                CONVERT(COALESCE(p.type_project, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS type_project,
                CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci AS po_number,
                CONVERT(COALESCE(NULLIF(a.no_po_sub, ''), '-') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS sub_po,
                CONVERT(COALESCE(NULLIF(a.detail_po, ''), '-') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS detail_po,
                CONVERT(COALESCE(NULLIF(a.remarks, ''), '-') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS remarks,
                CONVERT(COALESCE(NULLIF(a.regional, ''), NULLIF(aa.regional, ''), '-') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS regional,
                CONVERT(COALESCE(NULLIF(a.kota_po, ''), NULLIF(aa.kota_po, ''), '-') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS area,
                tc.invoice_date AS period_start,
                tc.invoice_date AS period_end,
                0 AS target_amount,
                tc.invoice_amount AS achieved_amount
            FROM tb_po_term_claim tc
            JOIN tb_po_term t ON t.id_term = tc.id_term
            JOIN tb_po p ON p.id_po = t.id_po
            LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = p.id_bowheer
            LEFT JOIN tb_po_term_allocation a ON a.id_allocation = tc.id_allocation
            LEFT JOIN (
                SELECT
                    id_term,
                    GROUP_CONCAT(DISTINCT NULLIF(regional, '') ORDER BY source_row_no SEPARATOR ', ') AS regional,
                    GROUP_CONCAT(DISTINCT NULLIF(kota_po, '') ORDER BY source_row_no SEPARATOR ', ') AS kota_po
                FROM tb_po_term_allocation
                GROUP BY id_term
            ) aa ON aa.id_term = t.id_term
            WHERE tc.invoice_date IS NOT NULL
                AND DATE(tc.invoice_date) BETWEEN ? AND ?", [$startDate, $endDate])->result_array();

        $rows = array_merge($targetRows, $claimRows);
        foreach ($rows as &$row) {
            $start = $row['period_start'] ?: $row['period_end'];
            $end = $row['period_end'] ?: $row['period_start'];
            $monthKey = $start ? $this->majorityMonthKey($start, $end) : '';
            $weekKey = $start ? $this->weekKey((int) date('Y', strtotime($start)), (int) $this->weekNumberFromPeriod($start, $end)) : '';

            $row['id_bowheer'] = (int) ($row['id_bowheer'] ?? 0);
            $row['project'] = trim((string) ($row['project'] ?? 'Tanpa Bowheer'));
            $row['pic'] = trim((string) ($row['pic'] ?? '')) ?: $this->dashboardPic($row['project']);
            $row['row_type'] = trim((string) ($row['row_type'] ?? '-')) ?: '-';
            $row['po_number'] = trim((string) ($row['po_number'] ?? '-')) ?: '-';
            $row['sub_po'] = trim((string) ($row['sub_po'] ?? '-')) ?: '-';
            $row['detail_po'] = trim((string) ($row['detail_po'] ?? '-')) ?: '-';
            $row['remarks'] = trim((string) ($row['remarks'] ?? '-')) ?: '-';
            if (strtoupper((string) ($row['row_type'] ?? '')) === 'ACHIEVED') {
                $fallbackLocation = $this->parseRegionalAreaFromTypeProject((string) ($row['type_project'] ?? ''));
                if (trim((string) ($row['regional'] ?? '-')) === '-') {
                    $row['regional'] = $fallbackLocation['regional'];
                }
                if (trim((string) ($row['area'] ?? '-')) === '-') {
                    $row['area'] = $fallbackLocation['area'];
                }
            }
            $row['regional'] = trim((string) ($row['regional'] ?? '-')) ?: '-';
            $row['area'] = trim((string) ($row['area'] ?? '-')) ?: '-';
            $row['month'] = strtoupper($monthKey);
            $row['month_label'] = $monthKey ? strtoupper(date('F', strtotime($monthKey . '-01'))) : '-';
            $row['month_year_label'] = $monthKey ? strtoupper(date('Y - F', strtotime($monthKey . '-01'))) : '-';
            $row['week'] = strtoupper($weekKey);
            $row['date'] = $start ?: '';
            $row['date_label'] = $start && $end && $start !== $end
                ? $this->indonesianDate($start) . ' s/d ' . $this->indonesianDate($end)
                : ($start ? $this->indonesianDate($start) : '-');
            $row['target'] = (float) ($row['target_amount'] ?? 0);
            $row['achieved'] = (float) ($row['achieved_amount'] ?? 0);
            unset($row['target_amount'], $row['achieved_amount']);
        }
        unset($row);

        return $rows;
    }

    public function getBreakdownTargetInvoiceFilterOptions(array $rows)
    {
        $options = [
            'projects' => [],
            'pics' => [],
            'regionals' => [],
            'areas' => [],
            'months' => [],
            'weeks' => []
        ];

        foreach ($rows as $row) {
            $this->appendBreakdownOption($options['projects'], (string) ($row['project'] ?? ''), (string) ($row['project'] ?? ''));
            $this->appendBreakdownOption($options['pics'], (string) ($row['pic'] ?? ''), (string) ($row['pic'] ?? ''));
            $this->appendBreakdownOption($options['regionals'], (string) ($row['regional'] ?? ''), (string) ($row['regional'] ?? ''));
            $this->appendBreakdownOption($options['areas'], (string) ($row['area'] ?? ''), (string) ($row['area'] ?? ''));
            $this->appendBreakdownOption($options['months'], (string) ($row['month'] ?? ''), (string) ($row['month_year_label'] ?? $row['month_label'] ?? $row['month'] ?? ''));
            $this->appendBreakdownOption($options['weeks'], (string) ($row['week'] ?? ''), (string) ($row['week'] ?? ''));
        }

        foreach ($options as &$items) {
            uasort($items, function ($a, $b) {
                return strcmp($a['value'], $b['value']);
            });
            $items = array_values($items);
        }
        unset($items);

        return $options;
    }

    private function appendBreakdownOption(array &$options, $value, $label)
    {
        $value = trim((string) $value);
        $label = trim((string) $label);
        if ($value === '' || $value === '-') {
            return;
        }
        $options[$value] = ['value' => $value, 'label' => $label ?: $value];
    }

    public function getComparisonDetail($idBowheer, $periodKey, $groupBy, $type, $fromMonth = null, $toMonth = null)
    {
        $idBowheer = (int) $idBowheer;
        $groupBy = $groupBy === 'week' ? 'week' : 'month';
        $type = in_array($type, ['achieved', 'cumulative', 'effective_target', 'effective_deviasi_by_po', 'deviasi_by_po', 'actual_target'], true) ? $type : 'target';

        if ($type === 'achieved') {
            return $this->getComparisonAchievedDetail($idBowheer, $periodKey, $groupBy, $fromMonth, $toMonth);
        }
        if ($type === 'actual_target') {
            return $this->getComparisonLockedTargetDetail($idBowheer, $periodKey, $groupBy, $fromMonth, $toMonth);
        }
        if ($type === 'deviasi_by_po') {
            return $this->getComparisonDeviasiByPoDetail($idBowheer, $periodKey, $groupBy, $fromMonth, $toMonth);
        }
        if ($type === 'effective_deviasi_by_po') {
            return $this->getComparisonEffectiveDeviasiByPoDetail($idBowheer, $periodKey, $groupBy, $fromMonth, $toMonth);
        }
        if ($type === 'cumulative') {
            return $this->getComparisonCumulativeDetail($idBowheer, $periodKey, $groupBy, $fromMonth, $toMonth);
        }
        if ($type === 'effective_target') {
            return $this->getComparisonEffectiveTargetDetail($idBowheer, $periodKey, $groupBy, $fromMonth, $toMonth);
        }

        return $this->getComparisonLockedTargetDetail($idBowheer, $periodKey, $groupBy, $fromMonth, $toMonth);
    }

    private function getComparisonDeviasiByPoDetail($idBowheer, $periodKey, $groupBy, $fromMonth = null, $toMonth = null)
    {
        if ((string) $periodKey === '__total__') {
            $rows = [];
            foreach (array_keys($this->comparisonTotalPeriodKeys($periodKey, $groupBy, $fromMonth, $toMonth)) as $key) {
                $rows = array_merge($rows, $this->getComparisonDeviasiByPoDetail($idBowheer, $key, $groupBy, $fromMonth, $toMonth));
            }

            return $rows;
        }

        $rows = $this->getComparisonDeviasiByPoDetailRows($idBowheer, $periodKey, $groupBy, $fromMonth, $toMonth);
        foreach ($rows as &$row) {
            $row['source_label'] = 'Target belum invoice';
        }
        unset($row);

        return $rows;
    }

    private function getComparisonLockedTargetDetail($idBowheer, $periodKey, $groupBy, $fromMonth = null, $toMonth = null)
    {
        $rows = $this->getComparisonTargetDetail($idBowheer, $periodKey, $groupBy, $fromMonth, $toMonth, false);
        $rows = array_values(array_filter($rows, function ($row) use ($periodKey, $groupBy) {
            return (float) ($row['amount'] ?? 0) > 0.000001
                && !$this->comparisonTargetWasInvoicedBeforePeriod($row, $periodKey, $groupBy);
        }));

        return $this->alignComparisonDetailRowsToLockedAmount($rows, $idBowheer, $periodKey, $groupBy);
    }

    private function alignComparisonDetailRowsToLockedAmount(array $rows, $idBowheer, $periodKey, $groupBy)
    {
        if ((string) $periodKey === '__total__') {
            return $rows;
        }

        $lockedAmount = $this->getComparisonTargetLockAmount($idBowheer, $periodKey, $groupBy);
        if ($lockedAmount === null) {
            return $rows;
        }

        $currentAmount = 0;
        foreach ($rows as $row) {
            $currentAmount += (float) ($row['amount'] ?? 0);
        }

        $adjustment = (float) $lockedAmount - $currentAmount;
        if (abs($adjustment) <= 0.5) {
            return $rows;
        }

        $rows[] = [
            'id_bowheer' => (int) $idBowheer,
            'po_number' => '-',
            'type_project' => 'LOCK TARGET',
            'po_date' => null,
            'term_index' => 0,
            'no_po_sub' => '-',
            'regional' => 'ADJUSTMENT LOCK',
            'kota_po' => '-',
            'detail_po' => 'Penyesuaian angka lock target agar total sesuai dashboard',
            'remarks' => 'Locked target ' . number_format((float) $lockedAmount, 0, ',', '.'),
            'target_week' => 0,
            'target_week_start' => null,
            'target_week_end' => null,
            'amount' => $adjustment,
            'invoiced_amount' => 0,
            'claim_invoice_date' => null,
            'source_label' => 'Target Lock Adjustment'
        ];

        return $rows;
    }

    private function getComparisonDeviasiByPoDetailRows($idBowheer, $periodKey, $groupBy, $fromMonth = null, $toMonth = null)
    {
        $rows = $this->getComparisonTargetDetail($idBowheer, $periodKey, $groupBy, $fromMonth, $toMonth, false);
        return array_values(array_filter($rows, function ($row) use ($periodKey, $groupBy) {
            return (float) ($row['amount'] ?? 0) > 0.000001
                && !$this->comparisonTargetWasInvoicedByPeriod($row, $periodKey, $groupBy);
        }));
    }

    private function getComparisonCumulativeDetail($idBowheer, $periodKey, $groupBy, $fromMonth = null, $toMonth = null)
    {
        $cumulativePeriodKey = trim((string) $periodKey);
        if ($cumulativePeriodKey === '' || $cumulativePeriodKey === '__total__') {
            return [];
        }

        $rows = $this->getComparisonTargetDetail($idBowheer, $cumulativePeriodKey, $groupBy, $fromMonth, $toMonth, false);
        foreach ($rows as &$row) {
            $row['source_label'] = 'Kumulatif deviasi periode sebelumnya';
        }
        unset($row);

        $rows = array_values(array_filter($rows, function ($row) use ($cumulativePeriodKey, $groupBy) {
            return (float) ($row['amount'] ?? 0) > 0.000001
                && !$this->comparisonTargetWasInvoicedByPeriod($row, $cumulativePeriodKey, $groupBy);
        }));

        return $this->alignComparisonCumulativeRowsToSummaryAmount($rows, $idBowheer, $cumulativePeriodKey, $groupBy);
    }

    private function alignComparisonCumulativeRowsToSummaryAmount(array $rows, $idBowheer, $periodKey, $groupBy)
    {
        $summaryAmount = $this->sumComparisonUninvoicedTargetAmount((int) $idBowheer, (string) $periodKey, $groupBy);
        $currentAmount = 0;
        foreach ($rows as $row) {
            $currentAmount += (float) ($row['amount'] ?? 0);
        }

        $adjustment = (float) $summaryAmount - $currentAmount;
        if (abs($adjustment) <= 0.5) {
            return $rows;
        }

        $rows[] = [
            'id_bowheer' => (int) $idBowheer,
            'po_number' => '-',
            'type_project' => 'LOCK KUMULATIF',
            'po_date' => null,
            'term_index' => 0,
            'no_po_sub' => '-',
            'regional' => 'ADJUSTMENT KUMULATIF',
            'kota_po' => '-',
            'detail_po' => 'Penyesuaian angka kumulatif agar total sesuai dashboard',
            'remarks' => 'Summary kumulatif ' . number_format((float) $summaryAmount, 0, ',', '.'),
            'target_week' => 0,
            'target_week_start' => null,
            'target_week_end' => null,
            'amount' => $adjustment,
            'invoiced_amount' => 0,
            'claim_invoice_date' => null,
            'source_label' => 'Kumulatif Adjustment'
        ];

        return $rows;
    }

    private function getComparisonEffectiveTargetDetail($idBowheer, $periodKey, $groupBy, $fromMonth = null, $toMonth = null)
    {
        $periodKeys = [];
        if ($periodKey === '__total__') {
            $periodKeys = array_keys($this->comparisonTotalPeriodKeys($periodKey, $groupBy, $fromMonth, $toMonth));
        } else {
            $periodKeys = [$periodKey];
        }

        $rows = [];
        foreach ($periodKeys as $key) {
            foreach ($this->comparisonPreviousPeriodKeysFromCutoff($key, $groupBy) as $cumulativePeriodKey) {
                $rows = array_merge(
                    $rows,
                    $this->getComparisonCumulativeDetail($idBowheer, $cumulativePeriodKey, $groupBy, $fromMonth, $toMonth)
                );
            }

            $rows = array_merge($rows, $this->getComparisonLockedTargetDetail($idBowheer, $key, $groupBy, $fromMonth, $toMonth));
        }

        return $rows;
    }

    private function getComparisonEffectiveDeviasiByPoDetail($idBowheer, $periodKey, $groupBy, $fromMonth = null, $toMonth = null)
    {
        $periodKeys = [];
        if ($periodKey === '__total__') {
            $periodKeys = array_keys($this->comparisonTotalPeriodKeys($periodKey, $groupBy, $fromMonth, $toMonth));
        } else {
            $periodKeys = [$periodKey];
        }

        $rows = [];
        foreach ($periodKeys as $key) {
            foreach ($this->comparisonPreviousPeriodKeysFromCutoff($key, $groupBy) as $cumulativePeriodKey) {
                $rows = array_merge(
                    $rows,
                    $this->getComparisonCumulativeDetail($idBowheer, $cumulativePeriodKey, $groupBy, $fromMonth, $toMonth)
                );
            }

            $rows = array_merge($rows, $this->getComparisonDeviasiByPoDetail($idBowheer, $key, $groupBy, $fromMonth, $toMonth));
        }

        return $rows;
    }

    private function getComparisonTargetDetail($idBowheer, $periodKey, $groupBy, $fromMonth = null, $toMonth = null, $filterInvoicedForActivePeriod = true)
    {
        $rows = $this->db->query("SELECT *
            FROM (
                SELECT
                    p.id_bowheer,
                    CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci AS po_number,
                    CONVERT(p.type_project USING utf8mb4) COLLATE utf8mb4_unicode_ci AS type_project,
                    p.po_date,
                    t.term_index,
                    CONVERT(a.no_po_sub USING utf8mb4) COLLATE utf8mb4_unicode_ci AS no_po_sub,
                    CONVERT(a.regional USING utf8mb4) COLLATE utf8mb4_unicode_ci AS regional,
                    CONVERT(a.kota_po USING utf8mb4) COLLATE utf8mb4_unicode_ci AS kota_po,
                    CONVERT(a.detail_po USING utf8mb4) COLLATE utf8mb4_unicode_ci AS detail_po,
                    CONVERT(a.remarks USING utf8mb4) COLLATE utf8mb4_unicode_ci AS remarks,
                    a.target_week,
                    a.target_week_start,
                    a.target_week_end,
                    COALESCE(NULLIF(a.plan_amount, 0), a.allocation_value) AS amount,
                    COALESCE(tc_alloc.invoice_amount, tc_term.invoice_amount, 0) AS invoiced_amount,
                    COALESCE(tc_alloc.invoice_date, tc_term.invoice_date) AS claim_invoice_date,
                    CONVERT('Target Allocation' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS source_label
                FROM tb_po_term_allocation a
                JOIN tb_po_term t ON t.id_term = a.id_term
                JOIN tb_po p ON p.id_po = t.id_po
                LEFT JOIN (
                    SELECT id_allocation, SUM(invoice_amount) AS invoice_amount, MAX(invoice_date) AS invoice_date
                    FROM tb_po_term_claim
                    WHERE id_allocation IS NOT NULL
                    GROUP BY id_allocation
                ) tc_alloc ON tc_alloc.id_allocation = a.id_allocation
                LEFT JOIN (
                    SELECT id_term, SUM(invoice_amount) AS invoice_amount, MAX(invoice_date) AS invoice_date
                    FROM tb_po_term_claim
                    GROUP BY id_term
                ) tc_term ON tc_term.id_term = t.id_term
                WHERE CONVERT(a.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                    AND p.id_bowheer = ?
                    AND a.target_week_start IS NOT NULL
                    AND a.target_week_end IS NOT NULL
                UNION ALL
                SELECT
                    p.id_bowheer,
                    CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci AS po_number,
                    CONVERT(p.type_project USING utf8mb4) COLLATE utf8mb4_unicode_ci AS type_project,
                    p.po_date,
                    t.term_index,
                    CAST(NULL AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci AS no_po_sub,
                    CONVERT(COALESCE(myrep_cluster.regional_name, myrep_mainfeeder.regional_name) USING utf8mb4) COLLATE utf8mb4_unicode_ci AS regional,
                    CONVERT(COALESCE(myrep_cluster.city_name, myrep_mainfeeder.city_name) USING utf8mb4) COLLATE utf8mb4_unicode_ci AS kota_po,
                    CONVERT(CASE
                        WHEN TRIM(COALESCE(myrep_cluster.cluster_name, '')) <> '' THEN CONCAT(COALESCE(myrep_header.po_type, 'CLUSTER'), ' - ', myrep_cluster.cluster_name)
                        WHEN TRIM(COALESCE(myrep_mainfeeder.mainfeeder_name, '')) <> '' THEN CONCAT(COALESCE(myrep_header.po_type, 'MAINFEEDER'), ' - ', myrep_mainfeeder.mainfeeder_name)
                        ELSE NULL
                    END USING utf8mb4) COLLATE utf8mb4_unicode_ci AS detail_po,
                    CAST(NULL AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci AS remarks,
                    t.target_week,
                    t.target_week_start,
                    t.target_week_end,
                    COALESCE(NULLIF(t.plan_amount, 0), t.value) AS amount,
                    COALESCE(tc.invoice_amount, 0) AS invoiced_amount,
                    tc.invoice_date AS claim_invoice_date,
                    CONVERT('Target Term' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS source_label
                FROM tb_po_term t
                JOIN tb_po p ON p.id_po = t.id_po
                LEFT JOIN tb_myrep_po_header myrep_header ON CONVERT(TRIM(myrep_header.po_number) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(TRIM(p.po_number) USING utf8mb4) COLLATE utf8mb4_unicode_ci
                LEFT JOIN tb_myrep_cluster myrep_cluster ON myrep_cluster.id_myrep_cluster = myrep_header.id_myrep_cluster
                LEFT JOIN tb_rfs_myrep_mainfeeder myrep_mainfeeder ON myrep_mainfeeder.id_mainfeeder = myrep_header.id_mainfeeder
                LEFT JOIN (
                    SELECT id_term, SUM(invoice_amount) AS invoice_amount, MAX(invoice_date) AS invoice_date
                    FROM tb_po_term_claim
                    GROUP BY id_term
                ) tc ON tc.id_term = t.id_term
                WHERE CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                    AND p.id_bowheer = ?
                    AND t.target_week_start IS NOT NULL
                    AND t.target_week_end IS NOT NULL
                    AND NOT EXISTS (
                        SELECT 1 FROM tb_po_term_allocation a WHERE a.id_term = t.id_term
                    )
                UNION ALL
                SELECT
                    pl.id_bowheer,
                    CONVERT('-' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS po_number,
                    CONVERT(pl.type_project USING utf8mb4) COLLATE utf8mb4_unicode_ci AS type_project,
                    pl.po_date,
                    pl.term_index,
                    CAST(NULL AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci AS no_po_sub,
                    CONVERT(pl.regional USING utf8mb4) COLLATE utf8mb4_unicode_ci AS regional,
                    CONVERT(pl.kota_po USING utf8mb4) COLLATE utf8mb4_unicode_ci AS kota_po,
                    CONVERT(pl.detail_po USING utf8mb4) COLLATE utf8mb4_unicode_ci AS detail_po,
                    CONVERT(pl.remarks USING utf8mb4) COLLATE utf8mb4_unicode_ci AS remarks,
                    pl.target_week,
                    pl.target_week_start,
                    pl.target_week_end,
                    pl.plan_amount AS amount,
                    0 AS invoiced_amount,
                    CAST(NULL AS DATE) AS claim_invoice_date,
                    CONVERT('NY PO Target' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS source_label
                FROM tb_po_target_pipeline pl
                WHERE CONVERT(pl.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                    AND pl.linked_id_po IS NULL
                    AND pl.id_bowheer = ?
                    AND pl.target_week_start IS NOT NULL
                    AND pl.target_week_end IS NOT NULL
            ) x
            ORDER BY target_week_start ASC, po_number ASC, term_index ASC", [$idBowheer, $idBowheer, $idBowheer])->result_array();

        $totalPeriodKeys = $this->comparisonTotalPeriodKeys($periodKey, $groupBy, $fromMonth, $toMonth);
        return array_values(array_filter($rows, function ($row) use ($periodKey, $groupBy, $totalPeriodKeys, $filterInvoicedForActivePeriod) {
            $rowPeriod = $groupBy === 'week'
                ? $this->weekKey((int) date('Y', strtotime($row['target_week_start'])), (int) $this->weekNumberFromPeriod($row['target_week_start'], $row['target_week_end']))
                : $this->majorityMonthKey($row['target_week_start'], $row['target_week_end']);

            if (
                $filterInvoicedForActivePeriod
                &&
                !$this->comparisonPeriodIsBeforeCurrentPeriod($rowPeriod, $groupBy)
                && !empty($row['claim_invoice_date'])
            ) {
                return false;
            }

            if (!empty($totalPeriodKeys)) {
                return isset($totalPeriodKeys[$rowPeriod]);
            }

            return $rowPeriod === $periodKey;
        }));
    }

    private function comparisonPeriodIsBeforeCurrentPeriod($periodKey, $groupBy)
    {
        $groupBy = $groupBy === 'week' ? 'week' : 'month';
        $periodSort = $this->comparisonPeriodSortValue($periodKey, $groupBy);
        $currentKey = $groupBy === 'week'
            ? $this->weekKeyFromDate(date('Y-m-d'))
            : date('Y-m');
        $currentSort = $this->comparisonPeriodSortValue($currentKey, $groupBy);

        return $periodSort > 0 && $currentSort > 0 && $periodSort < $currentSort;
    }

    private function comparisonPeriodIsAfterCurrentPeriod($periodKey, $groupBy)
    {
        $groupBy = $groupBy === 'week' ? 'week' : 'month';
        $periodSort = $this->comparisonPeriodSortValue($periodKey, $groupBy);
        $currentKey = $groupBy === 'week'
            ? $this->weekKeyFromDate(date('Y-m-d'))
            : date('Y-m');
        $currentSort = $this->comparisonPeriodSortValue($currentKey, $groupBy);

        return $periodSort > 0 && $currentSort > 0 && $periodSort > $currentSort;
    }

    private function comparisonPeriodIsCurrentPeriod($periodKey, $groupBy)
    {
        $groupBy = $groupBy === 'week' ? 'week' : 'month';
        $periodSort = $this->comparisonPeriodSortValue($periodKey, $groupBy);
        $currentKey = $groupBy === 'week'
            ? $this->weekKeyFromDate(date('Y-m-d'))
            : date('Y-m');
        $currentSort = $this->comparisonPeriodSortValue($currentKey, $groupBy);

        return $periodSort > 0 && $currentSort > 0 && $periodSort === $currentSort;
    }

    private function sumComparisonUninvoicedTargetAmount($idBowheer, $periodKey, $groupBy)
    {
        static $cache = [];
        $cacheKey = (int) $idBowheer . '|' . (string) $periodKey . '|' . (string) $groupBy;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $rows = $this->getComparisonTargetDetail((int) $idBowheer, (string) $periodKey, $groupBy, null, null, false);
        $total = 0;
        foreach ($rows as $row) {
            if (!$this->comparisonTargetWasInvoicedByPeriod($row, $periodKey, $groupBy)) {
                $total += (float) ($row['amount'] ?? 0);
            }
        }

        $cache[$cacheKey] = $total;
        return $total;
    }

    private function sumComparisonLockedTargetAmount($idBowheer, $periodKey, $groupBy)
    {
        static $cache = [];
        $cacheKey = (int) $idBowheer . '|' . (string) $periodKey . '|' . (string) $groupBy;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $locked = $this->getComparisonTargetLockAmount($idBowheer, $periodKey, $groupBy);
        if ($locked !== null) {
            $cache[$cacheKey] = $locked;
            return $locked;
        }

        if ($this->comparisonPeriodIsCurrentPeriod($periodKey, $groupBy)) {
            $rawAmount = $this->sumComparisonRawTargetAmount($idBowheer, $periodKey, $groupBy);
            $deviasiAmount = $this->sumComparisonUninvoicedTargetAmount($idBowheer, $periodKey, $groupBy);
            $this->saveComparisonTargetLockAmount($idBowheer, $periodKey, $groupBy, $deviasiAmount, $rawAmount, $deviasiAmount);
            $cache[$cacheKey] = $deviasiAmount;
            return $deviasiAmount;
        }

        $rows = $this->getComparisonTargetDetail((int) $idBowheer, (string) $periodKey, $groupBy, null, null, false);
        $total = 0;
        foreach ($rows as $row) {
            if (!$this->comparisonTargetWasInvoicedBeforePeriod($row, $periodKey, $groupBy)) {
                $total += (float) ($row['amount'] ?? 0);
            }
        }

        $cache[$cacheKey] = $total;
        return $total;
    }

    private function sumComparisonRawTargetAmount($idBowheer, $periodKey, $groupBy)
    {
        $rows = $this->getComparisonTargetDetail((int) $idBowheer, (string) $periodKey, $groupBy, null, null, false);
        $total = 0;
        foreach ($rows as $row) {
            $total += (float) ($row['amount'] ?? 0);
        }

        return $total;
    }

    private function getComparisonTargetLockAmount($idBowheer, $periodKey, $groupBy)
    {
        if (!$this->db->table_exists('tb_po_comparison_target_lock')) {
            return null;
        }

        $row = $this->db
            ->select('locked_amount')
            ->from('tb_po_comparison_target_lock')
            ->where('id_bowheer', (int) $idBowheer)
            ->where('group_by', $groupBy === 'week' ? 'week' : 'month')
            ->where('period_key', (string) $periodKey)
            ->limit(1)
            ->get()
            ->row_array();

        return $row ? (float) $row['locked_amount'] : null;
    }

    private function saveComparisonTargetLockAmount($idBowheer, $periodKey, $groupBy, $lockedAmount, $rawAmount, $deviasiAmount)
    {
        if (!$this->db->table_exists('tb_po_comparison_target_lock')) {
            return;
        }

        $this->db->query(
            "INSERT IGNORE INTO tb_po_comparison_target_lock
                (id_bowheer, group_by, period_key, locked_amount, raw_amount, deviasi_amount, locked_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                (int) $idBowheer,
                $groupBy === 'week' ? 'week' : 'month',
                (string) $periodKey,
                (float) $lockedAmount,
                (float) $rawAmount,
                (float) $deviasiAmount,
                date('Y-m-d H:i:s')
            ]
        );
    }

    private function comparisonTargetWasInvoicedByPeriod(array $row, $periodKey, $groupBy)
    {
        if (empty($row['claim_invoice_date'])) {
            return false;
        }

        $invoicePeriodKey = $groupBy === 'week'
            ? $this->weekKeyFromDate($row['claim_invoice_date'])
            : $this->monthKeyFromInvoiceWeek($row['claim_invoice_date']);
        $invoiceSort = $this->comparisonPeriodSortValue($invoicePeriodKey, $groupBy);
        $targetSort = $this->comparisonPeriodSortValue($periodKey, $groupBy);

        return $invoiceSort > 0 && $targetSort > 0 && $invoiceSort <= $targetSort;
    }

    private function comparisonTargetWasInvoicedBeforePeriod(array $row, $periodKey, $groupBy)
    {
        if (empty($row['claim_invoice_date'])) {
            return false;
        }

        $invoicePeriodKey = $groupBy === 'week'
            ? $this->weekKeyFromDate($row['claim_invoice_date'])
            : $this->monthKeyFromInvoiceWeek($row['claim_invoice_date']);
        $invoiceSort = $this->comparisonPeriodSortValue($invoicePeriodKey, $groupBy);
        $targetSort = $this->comparisonPeriodSortValue($periodKey, $groupBy);

        return $invoiceSort > 0 && $targetSort > 0 && $invoiceSort < $targetSort;
    }

    private function sumComparisonTargetAmount($idBowheer, $periodKey, $groupBy)
    {
        static $cache = [];
        $cacheKey = (int) $idBowheer . '|' . (string) $periodKey . '|' . (string) $groupBy;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $rows = $this->getComparisonTargetDetail((int) $idBowheer, (string) $periodKey, $groupBy);
        $total = 0;
        foreach ($rows as $row) {
            $total += (float) ($row['amount'] ?? 0);
        }

        $cache[$cacheKey] = $total;
        return $total;
    }

    private function sumComparisonCumulativeDetailAmount($idBowheer, $periodKey, $groupBy)
    {
        static $cache = [];
        $cacheKey = (int) $idBowheer . '|' . (string) $periodKey . '|' . (string) $groupBy;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $rows = $this->getComparisonCumulativeDetail((int) $idBowheer, (string) $periodKey, $groupBy);
        $total = 0;
        foreach ($rows as $row) {
            $total += (float) ($row['amount'] ?? 0);
        }

        $cache[$cacheKey] = $total;
        return $total;
    }

    private function getComparisonAchievedDetail($idBowheer, $periodKey, $groupBy, $fromMonth = null, $toMonth = null)
    {
        $rows = $this->db->query("SELECT
                p.id_bowheer,
                p.po_number,
                p.type_project,
                p.po_date,
                t.term_index,
                COALESCE(NULLIF(a.no_po_sub, ''), aa.no_po_sub) AS no_po_sub,
                COALESCE(NULLIF(a.regional, ''), aa.regional, myrep_cluster.regional_name, myrep_mainfeeder.regional_name) AS regional,
                COALESCE(NULLIF(a.kota_po, ''), aa.kota_po, myrep_cluster.city_name, myrep_mainfeeder.city_name) AS kota_po,
                COALESCE(
                    NULLIF(a.detail_po, ''),
                    aa.detail_po,
                    CASE
                        WHEN TRIM(COALESCE(myrep_cluster.cluster_name, '')) <> '' THEN CONCAT(COALESCE(myrep_header.po_type, 'CLUSTER'), ' - ', myrep_cluster.cluster_name)
                        WHEN TRIM(COALESCE(myrep_mainfeeder.mainfeeder_name, '')) <> '' THEN CONCAT(COALESCE(myrep_header.po_type, 'MAINFEEDER'), ' - ', myrep_mainfeeder.mainfeeder_name)
                        ELSE NULL
                    END
                ) AS detail_po,
                COALESCE(NULLIF(a.remarks, ''), aa.remarks) AS remarks,
                tc.invoice_date,
                tc.invoice_amount AS amount,
                tc.claim_source AS source_label
            FROM tb_po_term_claim tc
            JOIN tb_po_term t ON t.id_term = tc.id_term
            JOIN tb_po p ON p.id_po = t.id_po
            LEFT JOIN tb_po_term_allocation a ON a.id_allocation = tc.id_allocation
            LEFT JOIN (
                SELECT
                    id_term,
                    GROUP_CONCAT(DISTINCT NULLIF(no_po_sub, '') ORDER BY source_row_no SEPARATOR ', ') AS no_po_sub,
                    GROUP_CONCAT(DISTINCT NULLIF(regional, '') ORDER BY source_row_no SEPARATOR ', ') AS regional,
                    GROUP_CONCAT(DISTINCT NULLIF(kota_po, '') ORDER BY source_row_no SEPARATOR ', ') AS kota_po,
                    GROUP_CONCAT(DISTINCT NULLIF(detail_po, '') ORDER BY source_row_no SEPARATOR ', ') AS detail_po,
                    GROUP_CONCAT(DISTINCT NULLIF(remarks, '') ORDER BY source_row_no SEPARATOR ', ') AS remarks
                FROM tb_po_term_allocation
                GROUP BY id_term
            ) aa ON aa.id_term = t.id_term
            LEFT JOIN tb_myrep_po_header myrep_header ON CONVERT(TRIM(myrep_header.po_number) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(TRIM(p.po_number) USING utf8mb4) COLLATE utf8mb4_unicode_ci
            LEFT JOIN tb_myrep_cluster myrep_cluster ON myrep_cluster.id_myrep_cluster = myrep_header.id_myrep_cluster
            LEFT JOIN tb_rfs_myrep_mainfeeder myrep_mainfeeder ON myrep_mainfeeder.id_mainfeeder = myrep_header.id_mainfeeder
            WHERE tc.invoice_date IS NOT NULL
                AND p.id_bowheer = ?
            ORDER BY tc.invoice_date ASC, p.po_number ASC, t.term_index ASC", [$idBowheer])->result_array();

        $totalPeriodKeys = $this->comparisonTotalPeriodKeys($periodKey, $groupBy, $fromMonth, $toMonth);
        $rows = array_values(array_filter($rows, function ($row) use ($periodKey, $groupBy, $totalPeriodKeys) {
            $rowPeriod = $groupBy === 'week'
                ? $this->weekKeyFromDate($row['invoice_date'])
                : $this->monthKeyFromInvoiceWeek($row['invoice_date']);

            if (!empty($totalPeriodKeys)) {
                return isset($totalPeriodKeys[$rowPeriod]);
            }

            return $rowPeriod === $periodKey;
        }));

        foreach ($rows as &$row) {
            $fallbackLocation = $this->parseRegionalAreaFromTypeProject((string) ($row['type_project'] ?? ''));
            if (trim((string) ($row['regional'] ?? '')) === '') {
                $row['regional'] = $fallbackLocation['regional'];
            }
            if (trim((string) ($row['kota_po'] ?? '')) === '') {
                $row['kota_po'] = $fallbackLocation['area'];
            }
        }
        unset($row);

        return $rows;
    }

    private function comparisonTotalPeriodKeys($periodKey, $groupBy, $fromMonth = null, $toMonth = null)
    {
        if ((string) $periodKey !== '__total__') {
            return [];
        }

        $bounds = $this->resolveComparisonBounds($fromMonth, $toMonth);
        $periods = $groupBy === 'week'
            ? $this->buildWeekList($bounds['from'], $bounds['to'])
            : $this->buildMonthList($bounds['from'], $bounds['to']);

        $keys = [];
        foreach ($periods as $period) {
            if (!empty($period['key'])) {
                $keys[(string) $period['key']] = true;
            }
        }

        return $keys;
    }

    private function calculateComparisonCarryOver(array $targetHistory, array $achievedHistory, array $targetOutstandingHistory, $activePeriodKey, $groupBy)
    {
        $previousPeriodKey = $this->comparisonPreviousPeriodKey($activePeriodKey, $groupBy);
        if ($previousPeriodKey === '') {
            return 0;
        }

        return (float) ($targetOutstandingHistory[$previousPeriodKey] ?? 0);
    }

    private function comparisonPreviousPeriodKey($activePeriodKey, $groupBy)
    {
        $activePeriodKey = strtoupper(trim((string) $activePeriodKey));
        if ($groupBy === 'week') {
            if (!preg_match('/^(\d{4})-W(\d{1,2})$/', $activePeriodKey, $match)) {
                return '';
            }

            $year = (int) $match[1];
            $week = (int) $match[2] - 1;
            if ($week <= 0) {
                $year--;
                $week = 53;
            }

            return $this->weekKey($year, $week);
        }

        if (!preg_match('/^(\d{4})-(\d{1,2})$/', $activePeriodKey)) {
            return '';
        }

        return date('Y-m', strtotime($activePeriodKey . '-01 -1 month'));
    }

    private function comparisonCumulativeBreakdown($idBowheer, $activePeriodKey, $groupBy)
    {
        $items = [];
        foreach ($this->comparisonPreviousPeriodKeysFromCutoff($activePeriodKey, $groupBy) as $periodKey) {
            $amount = $this->sumComparisonUninvoicedTargetAmount((int) $idBowheer, $periodKey, $groupBy);
            if ($amount <= 0.000001) {
                continue;
            }

            $items[] = [
                'key' => $periodKey,
                'label' => $this->comparisonShortPeriodLabel($periodKey, $groupBy),
                'amount' => $amount
            ];
        }

        return $items;
    }

    private function comparisonPreviousPeriodKeysFromCutoff($activePeriodKey, $groupBy)
    {
        $groupBy = $groupBy === 'week' ? 'week' : 'month';
        $activePeriodKey = strtoupper(trim((string) $activePeriodKey));
        $keys = [];

        if ($groupBy === 'week') {
            if (!preg_match('/^(\d{4})-W(\d{1,2})$/', $activePeriodKey, $matches)) {
                return [];
            }

            $year = (int) $matches[1];
            $activeWeek = (int) $matches[2];
            for ($week = 1; $week < $activeWeek; $week++) {
                $keys[] = $this->weekKey($year, $week);
            }

            return $keys;
        }

        if (!preg_match('/^(\d{4})-(\d{1,2})$/', $activePeriodKey, $matches)) {
            return [];
        }

        $year = (int) $matches[1];
        $activeMonth = (int) $matches[2];
        for ($month = 1; $month < $activeMonth; $month++) {
            $keys[] = sprintf('%04d-%02d', $year, $month);
        }

        return $keys;
    }

    private function comparisonShortPeriodLabel($periodKey, $groupBy)
    {
        $groupBy = $groupBy === 'week' ? 'week' : 'month';
        $periodKey = strtoupper(trim((string) $periodKey));

        if ($groupBy === 'week' && preg_match('/^(\d{4})-W(\d{1,2})$/', $periodKey, $matches)) {
            return 'W' . (int) $matches[2];
        }

        if (preg_match('/^(\d{4})-(\d{1,2})$/', $periodKey, $matches)) {
            return $this->indonesianMonthName((int) $matches[2]);
        }

        return $periodKey;
    }

    public function comparisonPreviousPeriodKeyPublic($activePeriodKey, $groupBy)
    {
        return $this->comparisonPreviousPeriodKey($activePeriodKey, $groupBy);
    }

    private function comparisonPeriodSortValue($periodKey, $groupBy)
    {
        $periodKey = strtoupper(trim((string) $periodKey));
        if ($groupBy === 'week') {
            if (!preg_match('/^(\d{4})-W(\d{1,2})$/', $periodKey, $match)) {
                return null;
            }

            return ((int) $match[1] * 100) + (int) $match[2];
        }

        if (!preg_match('/^(\d{4})-(\d{1,2})$/', $periodKey, $match)) {
            return null;
        }

        return ((int) $match[1] * 100) + (int) $match[2];
    }

    private function comparisonPeriodIsBeforeSameYear($periodKey, $activePeriodKey, $groupBy)
    {
        $periodKey = strtoupper(trim((string) $periodKey));
        $activePeriodKey = strtoupper(trim((string) $activePeriodKey));

        if ($groupBy === 'week') {
            if (!preg_match('/^(\d{4})-W(\d{1,2})$/', $periodKey, $periodMatch)
                || !preg_match('/^(\d{4})-W(\d{1,2})$/', $activePeriodKey, $activeMatch)
            ) {
                return false;
            }

            return (int) $periodMatch[1] === (int) $activeMatch[1]
                && (int) $periodMatch[2] < (int) $activeMatch[2];
        }

        if (!preg_match('/^(\d{4})-(\d{2})$/', $periodKey, $periodMatch)
            || !preg_match('/^(\d{4})-(\d{2})$/', $activePeriodKey, $activeMatch)
        ) {
            return false;
        }

        return (int) $periodMatch[1] === (int) $activeMatch[1]
            && (int) $periodMatch[2] < (int) $activeMatch[2];
    }

    private function resolveComparisonBounds($fromMonth, $toMonth)
    {
        $from = $this->normalizeMonthInput($fromMonth);
        $to = $this->normalizeMonthInput($toMonth);

        if ($from === null || $to === null) {
            $dataBounds = $this->getComparisonDataBounds();
            $from = $from ?: $dataBounds['from'];
            $to = $to ?: $dataBounds['to'];
        }

        if (strtotime($from . '-01') > strtotime($to . '-01')) {
            $swap = $from;
            $from = $to;
            $to = $swap;
        }

        return ['from' => $from, 'to' => $to];
    }

    private function parseRegionalAreaFromTypeProject($typeProject)
    {
        $parts = array_values(array_filter(array_map('trim', explode('-', (string) $typeProject)), static function ($part) {
            return $part !== '';
        }));

        $result = ['regional' => '', 'area' => ''];
        foreach ($parts as $index => $part) {
            if (preg_match('/^REGIONAL\s*\d+/i', $part)) {
                $result['regional'] = strtoupper($part);
                $result['area'] = strtoupper(trim((string) ($parts[$index + 1] ?? '')));
                break;
            }
        }

        return $result;
    }

    private function getComparisonDataBounds()
    {
        $keys = [];

        $targetRows = $this->db->query("SELECT a.target_week_start, a.target_week_end
            FROM tb_po_term_allocation a
            WHERE CONVERT(a.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                AND a.target_week_start IS NOT NULL
                AND a.target_week_end IS NOT NULL
            UNION ALL
            SELECT t.target_week_start, t.target_week_end
            FROM tb_po_term t
            WHERE CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                AND t.target_week_start IS NOT NULL
                AND t.target_week_end IS NOT NULL
                AND NOT EXISTS (
                    SELECT 1 FROM tb_po_term_allocation a WHERE a.id_term = t.id_term
                )
            UNION ALL
            SELECT pl.target_week_start, pl.target_week_end
            FROM tb_po_target_pipeline pl
            WHERE CONVERT(pl.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                AND pl.linked_id_po IS NULL
                AND pl.target_week_start IS NOT NULL
                AND pl.target_week_end IS NOT NULL")->result_array();

        foreach ($targetRows as $row) {
            $keys[] = $this->majorityMonthKey($row['target_week_start'], $row['target_week_end']);
        }

        $claimRows = $this->db
            ->select('invoice_date')
            ->where('invoice_date IS NOT NULL', null, false)
            ->get('tb_po_term_claim')
            ->result_array();

        foreach ($claimRows as $row) {
            $keys[] = $this->monthKeyFromInvoiceWeek($row['invoice_date']);
        }

        $keys = array_values(array_filter(array_unique($keys)));
        sort($keys);

        if (empty($keys)) {
            $current = date('Y-m');
            return ['from' => $current, 'to' => $current];
        }

        return ['from' => $keys[0], 'to' => $keys[count($keys) - 1]];
    }

    private function normalizeMonthInput($value)
    {
        $value = trim((string) $value);
        return preg_match('/^\d{4}-\d{2}$/', $value) ? $value : null;
    }

    private function buildMonthList($from, $to)
    {
        $months = [];
        $cursor = new DateTime($from . '-01');
        $end = new DateTime($to . '-01');

        while ($cursor <= $end) {
            $months[] = [
                'key' => $cursor->format('Y-m'),
                'label' => $this->indonesianMonthName((int) $cursor->format('n')),
                'year' => $cursor->format('Y')
            ];
            $cursor->modify('+1 month');
        }

        return $months;
    }

    private function buildWeekList($from, $to)
    {
        $weeks = [];
        $fromTime = strtotime($from . '-01');
        $toTime = strtotime($to . '-01');

        for ($week = 1; $week <= 53; $week++) {
            $period = $this->weekPeriod(2026, $week);
            $majorityMonth = $this->majorityMonthKey($period['start'], $period['end']);
            $majorityTime = strtotime($majorityMonth . '-01');

            if ($majorityTime < $fromTime || $majorityTime > $toTime) {
                continue;
            }

            $weeks[] = [
                'key' => $this->weekKey(2026, $week),
                'label' => 'W' . $week,
                'month_key' => $majorityMonth,
                'month_label' => $this->indonesianMonthName((int) date('n', strtotime($majorityMonth . '-01'))),
                'year' => '2026',
                'period' => $this->indonesianDate($period['start']) . ' s/d ' . $this->indonesianDate($period['end'])
            ];
        }

        return $weeks;
    }

    private function majorityMonthKey($startDate, $endDate)
    {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $counts = [];

        while ($start <= $end) {
            $key = $start->format('Y-m');
            $counts[$key] = isset($counts[$key]) ? $counts[$key] + 1 : 1;
            $start->modify('+1 day');
        }

        arsort($counts);
        return (string) array_key_first($counts);
    }

    private function weekNumberFromPeriod($startDate, $endDate)
    {
        $start = strtotime($startDate);
        $end = strtotime($endDate);

        for ($week = 1; $week <= 53; $week++) {
            $period = $this->weekPeriod(2026, $week);
            if (strtotime($period['start']) === $start && strtotime($period['end']) === $end) {
                return $week;
            }
        }

        return (int) date('W', $start);
    }

    private function weekKeyFromDate($date)
    {
        $timestamp = strtotime($date);
        $year = (int) date('Y', $timestamp);
        $jan1 = new DateTime($year . '-01-01');
        $weekZeroStart = clone $jan1;
        $weekZeroStart->modify('-' . (int) $jan1->format('w') . ' days');
        $invoiceDate = new DateTime(date('Y-m-d', $timestamp));
        $diffDays = (int) floor(($invoiceDate->getTimestamp() - $weekZeroStart->getTimestamp()) / 86400);
        $week = (int) floor($diffDays / 7) + 1;

        return $this->weekKey($year, $week);
    }

    private function monthKeyFromInvoiceWeek($date)
    {
        $timestamp = strtotime($date);
        if (!$timestamp) {
            return '';
        }

        $year = (int) date('Y', $timestamp);
        $weekKey = $this->weekKeyFromDate($date);
        if (!preg_match('/^(\d{4})-W(\d{1,2})$/', $weekKey, $matches)) {
            return date('Y-m', $timestamp);
        }

        $period = $this->weekPeriod((int) $matches[1], (int) $matches[2]);
        return $this->majorityMonthKey($period['start'], $period['end']);
    }

    private function weekKey($year, $week)
    {
        return sprintf('%04d-W%02d', (int) $year, (int) $week);
    }

    private function indonesianMonthName($month)
    {
        $names = [
            1 => 'JANUARI',
            2 => 'FEBRUARI',
            3 => 'MARET',
            4 => 'APRIL',
            5 => 'MEI',
            6 => 'JUNI',
            7 => 'JULI',
            8 => 'AGUSTUS',
            9 => 'SEPTEMBER',
            10 => 'OKTOBER',
            11 => 'NOVEMBER',
            12 => 'DESEMBER'
        ];

        return isset($names[$month]) ? $names[$month] : (string) $month;
    }

    private function indonesianDate($date)
    {
        $timestamp = strtotime($date);
        if (!$timestamp) {
            return (string) $date;
        }

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return (int) date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }

    public function claimTerm($idTerm, $invoiceDate, $amount, $userId, $idAllocation = 0)
    {
        $term = $this->db->get_where('tb_po_term', ['id_term' => (int) $idTerm])->row_array();
        if (!$term) {
            return ['status' => false, 'message' => 'Term not found'];
        }

        $amount = (float) $amount;
        if (abs($amount) < 0.000001 || empty($invoiceDate)) {
            return ['status' => false, 'message' => 'Invoice date and amount are required'];
        }

        $idAllocation = (int) $idAllocation;
        $allocation = null;
        if ($idAllocation > 0) {
            $allocation = $this->db
                ->select('a.*, COALESCE(SUM(tc.invoice_amount),0) AS invoiced_amount', false)
                ->from('tb_po_term_allocation a')
                ->join('tb_po_term_claim tc', 'tc.id_allocation = a.id_allocation', 'left')
                ->where('a.id_allocation', $idAllocation)
                ->where('a.id_term', (int) $idTerm)
                ->group_by('a.id_allocation')
                ->get()
                ->row_array();

            if (!$allocation) {
                return ['status' => false, 'message' => 'Sub PO allocation not found'];
            }

            $allocationValue = (float) (($allocation['plan_amount'] ?? 0) ?: ($allocation['allocation_value'] ?? 0));
            if ($allocationValue <= 0) {
                $releaseValue = (float) ($this->db->query(
                    "SELECT COALESCE((SELECT am.release_value FROM tb_po_amend am WHERE am.id_po = ? ORDER BY am.amend_no DESC LIMIT 1), total_value, 0) AS value FROM tb_po WHERE id_po = ?",
                    [(int) $term['id_po'], (int) $term['id_po']]
                )->row_array()['value'] ?? 0);
                $allocationValue = (float) (($term['value'] ?? 0) ?: ($releaseValue * (float) ($term['percent'] ?? 0) / 100));
            }
            $poTotalValue = (float) ($this->db->select('COALESCE(total_value,0) AS value', false)->get_where('tb_po', ['id_po' => (int) $term['id_po']])->row_array()['value'] ?? 0);
            $percentLimit = $poTotalValue * (float) ($term['percent'] ?? 0) / 100;
            $allocationValue = max($allocationValue, $percentLimit);
        } else {
            $hasAllocation = $this->db
                ->where('id_term', (int) $idTerm)
                ->count_all_results('tb_po_term_allocation') > 0;
            if ($hasAllocation) {
                return ['status' => false, 'message' => 'Claim invoice wajib pilih Sub PO'];
            }

            $claimed = $this->db
                ->select('COALESCE(SUM(invoice_amount),0) AS amount', false)
                ->where('id_term', (int) $idTerm)
                ->where('id_allocation IS NULL', null, false)
                ->get('tb_po_term_claim')
                ->row_array();
            $poTotalValue = (float) ($this->db->select('COALESCE(total_value,0) AS value', false)->get_where('tb_po', ['id_po' => (int) $term['id_po']])->row_array()['value'] ?? 0);
            $termClaimLimit = max((float) ($term['value'] ?? 0), $poTotalValue * (float) ($term['percent'] ?? 0) / 100);
        }

        $this->db->trans_begin();
        $this->db->insert('tb_po_term_claim', [
            'id_term' => (int) $idTerm,
            'id_allocation' => $idAllocation > 0 ? $idAllocation : null,
            'invoice_date' => $invoiceDate,
            'invoice_amount' => $amount,
            'claim_source' => 'MANUAL',
            'created_by' => $userId ?: null
        ]);

        $this->db->where('id_term', (int) $idTerm)->update('tb_po_term', [
            'invoice_date' => $invoiceDate,
            'submit_raw' => $invoiceDate
        ]);

        if ($idAllocation > 0) {
            $this->db->where('id_allocation', $idAllocation)->update('tb_po_term_allocation', [
                'invoice_date' => $invoiceDate,
                'submit_raw' => $invoiceDate
            ]);
        }

        $this->db->set('dashboard_all_invoice', 'COALESCE(dashboard_all_invoice, 0) + ' . $this->db->escape($amount), false);
        if ((int) date('Y', strtotime($invoiceDate)) === 2026) {
            $this->db->set('dashboard_invoice_2026', 'COALESCE(dashboard_invoice_2026, 0) + ' . $this->db->escape($amount), false);
            $claimTargetStatus = $allocation ? ($allocation['target_status'] ?? '') : ($term['target_status'] ?? '');
            if ($claimTargetStatus === 'TARGET_WEEK') {
                $this->db->set('dashboard_outs_2026', 'GREATEST(COALESCE(dashboard_outs_2026, 0) - ' . $this->db->escape($amount) . ', 0)', false);
            } elseif ($claimTargetStatus === 'CARRY_OVER') {
                $this->db->set('dashboard_co_2027', 'GREATEST(COALESCE(dashboard_co_2027, 0) - ' . $this->db->escape($amount) . ', 0)', false);
            }
        }
        $this->db->where('id_po', (int) $term['id_po'])->update('tb_po');
        $this->refreshPoDashboardMetrics((int) $term['id_po']);

        $this->rebuildDashboardCache(null);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Failed to claim term'];
        }

        $this->db->trans_commit();
        $this->syncEmrNroMonitorInvoiceToMyRep($idTerm, $invoiceDate, $amount, $userId, false);
        return ['status' => true, 'message' => 'Term claimed'];
    }

    public function replaceInvoiceClaim($idTerm, $idAllocation, $invoiceDate, $amount, $userId)
    {
        $idTerm = (int) $idTerm;
        $idAllocation = (int) $idAllocation;
        $amount = (float) $amount;

        $term = $this->db->get_where('tb_po_term', ['id_term' => $idTerm])->row_array();
        if (!$term) {
            return ['status' => false, 'message' => 'Term not found'];
        }
        if (abs($amount) < 0.000001 || empty($invoiceDate)) {
            return ['status' => false, 'message' => 'Invoice date and amount are required'];
        }

        $allocation = null;
        $claimValue = (float) ($term['value'] ?? 0);
        $targetStatus = (string) ($term['target_status'] ?? '');
        if ($idAllocation > 0) {
            $allocation = $this->db
                ->where('id_allocation', $idAllocation)
                ->where('id_term', $idTerm)
                ->get('tb_po_term_allocation')
                ->row_array();
            if (!$allocation) {
                return ['status' => false, 'message' => 'Sub PO allocation not found'];
            }

            $claimValue = (float) (($allocation['plan_amount'] ?? 0) ?: ($allocation['allocation_value'] ?? 0));
            if ($claimValue <= 0) {
                $releaseValue = (float) ($this->db->query(
                    "SELECT COALESCE((SELECT am.release_value FROM tb_po_amend am WHERE am.id_po = ? ORDER BY am.amend_no DESC LIMIT 1), total_value, 0) AS value FROM tb_po WHERE id_po = ?",
                    [(int) $term['id_po'], (int) $term['id_po']]
                )->row_array()['value'] ?? 0);
                $claimValue = (float) (($term['value'] ?? 0) ?: ($releaseValue * (float) ($term['percent'] ?? 0) / 100));
            }
            $poTotalValue = (float) ($this->db->select('COALESCE(total_value,0) AS value', false)->get_where('tb_po', ['id_po' => (int) $term['id_po']])->row_array()['value'] ?? 0);
            $claimValue = max($claimValue, $poTotalValue * (float) ($term['percent'] ?? 0) / 100);
            $targetStatus = (string) ($allocation['target_status'] ?? '');
        } else {
            $hasAllocation = $this->db
                ->where('id_term', $idTerm)
                ->count_all_results('tb_po_term_allocation') > 0;
            if ($hasAllocation) {
                $legacyClaimCount = $this->db
                    ->where('id_term', $idTerm)
                    ->where('id_allocation IS NULL', null, false)
                    ->count_all_results('tb_po_term_claim');
                if ($legacyClaimCount <= 0) {
                    return ['status' => false, 'message' => 'Edit invoice wajib pilih Sub PO'];
                }
            }
        }
        if ($idAllocation <= 0) {
            $poTotalValue = (float) ($this->db->select('COALESCE(total_value,0) AS value', false)->get_where('tb_po', ['id_po' => (int) $term['id_po']])->row_array()['value'] ?? 0);
            $claimValue = max($claimValue, $poTotalValue * (float) ($term['percent'] ?? 0) / 100);
        }

        $this->db->select('COALESCE(SUM(invoice_amount),0) AS total_amount', false);
        $this->db->select("COALESCE(SUM(CASE WHEN YEAR(invoice_date) = 2026 THEN invoice_amount ELSE 0 END),0) AS amount_2026", false);
        $this->db->where('id_term', $idTerm);
        if ($idAllocation > 0) {
            $this->db->where('id_allocation', $idAllocation);
        } else {
            $this->db->where('id_allocation IS NULL', null, false);
        }
        $oldClaim = $this->db->get('tb_po_term_claim')->row_array();
        $oldTotal = (float) ($oldClaim['total_amount'] ?? 0);
        $old2026 = (float) ($oldClaim['amount_2026'] ?? 0);
        $new2026 = (int) date('Y', strtotime($invoiceDate)) === 2026 ? $amount : 0;

        $this->db->trans_begin();
        $this->db->where('id_term', $idTerm);
        if ($idAllocation > 0) {
            $this->db->where('id_allocation', $idAllocation);
        } else {
            $this->db->where('id_allocation IS NULL', null, false);
        }
        $this->db->delete('tb_po_term_claim');

        $this->db->insert('tb_po_term_claim', [
            'id_term' => $idTerm,
            'id_allocation' => $idAllocation > 0 ? $idAllocation : null,
            'invoice_date' => $invoiceDate,
            'invoice_amount' => $amount,
            'claim_source' => 'MANUAL',
            'created_by' => $userId ?: null
        ]);

        $this->db->where('id_term', $idTerm)->update('tb_po_term', [
            'invoice_date' => $invoiceDate,
            'submit_raw' => $invoiceDate
        ]);

        if ($idAllocation > 0) {
            $this->db->where('id_allocation', $idAllocation)->update('tb_po_term_allocation', [
                'invoice_date' => $invoiceDate,
                'submit_raw' => $invoiceDate
            ]);
        }

        $this->db->set('dashboard_all_invoice', 'GREATEST(COALESCE(dashboard_all_invoice, 0) + ' . $this->db->escape($amount - $oldTotal) . ', 0)', false);
        $this->db->set('dashboard_invoice_2026', 'GREATEST(COALESCE(dashboard_invoice_2026, 0) + ' . $this->db->escape($new2026 - $old2026) . ', 0)', false);
        if ($targetStatus === 'TARGET_WEEK') {
            $this->db->set('dashboard_outs_2026', 'GREATEST(COALESCE(dashboard_outs_2026, 0) + ' . $this->db->escape($old2026 - $new2026) . ', 0)', false);
        } elseif ($targetStatus === 'CARRY_OVER') {
            $this->db->set('dashboard_co_2027', 'GREATEST(COALESCE(dashboard_co_2027, 0) + ' . $this->db->escape($old2026 - $new2026) . ', 0)', false);
        }
        $this->db->where('id_po', (int) $term['id_po'])->update('tb_po');
        $this->refreshPoDashboardMetrics((int) $term['id_po']);

        $this->rebuildDashboardCache(null);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Failed to update invoice'];
        }

        $this->db->trans_commit();
        $this->syncEmrNroMonitorInvoiceToMyRep($idTerm, $invoiceDate, $amount, $userId, false);
        return ['status' => true, 'message' => 'Invoice term berhasil diupdate'];
    }

    public function resetInvoiceClaim($idTerm, $idAllocation = 0)
    {
        $idTerm = (int) $idTerm;
        $idAllocation = (int) $idAllocation;

        $term = $this->db->get_where('tb_po_term', ['id_term' => $idTerm])->row_array();
        if (!$term) {
            return ['status' => false, 'message' => 'Term not found'];
        }

        $targetStatus = (string) ($term['target_status'] ?? '');
        if ($idAllocation > 0) {
            $allocation = $this->db
                ->where('id_allocation', $idAllocation)
                ->where('id_term', $idTerm)
                ->get('tb_po_term_allocation')
                ->row_array();
            if (!$allocation) {
                return ['status' => false, 'message' => 'Sub PO allocation not found'];
            }
            $targetStatus = (string) ($allocation['target_status'] ?? '');
        }

        $this->db->select('COALESCE(SUM(invoice_amount),0) AS total_amount', false);
        $this->db->select("COALESCE(SUM(CASE WHEN YEAR(invoice_date) = 2026 THEN invoice_amount ELSE 0 END),0) AS amount_2026", false);
        $this->db->where('id_term', $idTerm);
        if ($idAllocation > 0) {
            $this->db->where('id_allocation', $idAllocation);
        } else {
            $this->db->where('id_allocation IS NULL', null, false);
        }
        $oldClaim = $this->db->get('tb_po_term_claim')->row_array();
        $oldTotal = (float) ($oldClaim['total_amount'] ?? 0);
        $old2026 = (float) ($oldClaim['amount_2026'] ?? 0);
        if (abs($oldTotal) < 0.000001) {
            return ['status' => false, 'message' => 'Invoice claim tidak ditemukan'];
        }

        $this->db->trans_begin();
        $this->db->where('id_term', $idTerm);
        if ($idAllocation > 0) {
            $this->db->where('id_allocation', $idAllocation);
        } else {
            $this->db->where('id_allocation IS NULL', null, false);
        }
        $this->db->delete('tb_po_term_claim');

        if ($idAllocation > 0) {
            $this->db->where('id_allocation', $idAllocation)->update('tb_po_term_allocation', [
                'invoice_date' => null,
                'submit_raw' => null
            ]);
        }

        $remainingTermClaims = $this->db
            ->where('id_term', $idTerm)
            ->count_all_results('tb_po_term_claim');
        $syncMyRepReset = $remainingTermClaims <= 0;
        if ($syncMyRepReset) {
            $this->db->where('id_term', $idTerm)->update('tb_po_term', [
                'invoice_date' => null,
                'submit_raw' => null
            ]);
        }

        $this->db->set('dashboard_all_invoice', 'GREATEST(COALESCE(dashboard_all_invoice, 0) - ' . $this->db->escape($oldTotal) . ', 0)', false);
        $this->db->set('dashboard_invoice_2026', 'GREATEST(COALESCE(dashboard_invoice_2026, 0) - ' . $this->db->escape($old2026) . ', 0)', false);
        if ($targetStatus === 'TARGET_WEEK') {
            $this->db->set('dashboard_outs_2026', 'COALESCE(dashboard_outs_2026, 0) + ' . $this->db->escape($old2026), false);
        } elseif ($targetStatus === 'CARRY_OVER') {
            $this->db->set('dashboard_co_2027', 'COALESCE(dashboard_co_2027, 0) + ' . $this->db->escape($old2026), false);
        }
        $this->db->where('id_po', (int) $term['id_po'])->update('tb_po');
        $this->refreshPoDashboardMetrics((int) $term['id_po']);

        $this->rebuildDashboardCache(null);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Failed to reset invoice'];
        }

        $this->db->trans_commit();
        if ($syncMyRepReset) {
            $this->syncEmrNroMonitorInvoiceToMyRep($idTerm, null, 0, 0, true);
        }
        return ['status' => true, 'message' => 'Invoice term berhasil direset'];
    }

    private function syncEmrNroMonitorInvoiceToMyRep($idTerm, $invoiceDate, $amount, $userId = 0, $reset = false)
    {
        if (!$this->db->table_exists('tb_myrep_po_header') || !$this->db->table_exists('tb_myrep_po_termin')) {
            return false;
        }

        $term = $this->db
            ->select('t.id_term, t.term_index, t.value, p.po_number, COALESCE(bp.bowheer, b.nama_bowheer, "") AS bowheer')
            ->from('tb_po_term t')
            ->join('tb_po p', 'p.id_po = t.id_po', 'inner')
            ->join('tb_bowheer_po bp', 'bp.id_bowheer = p.id_bowheer', 'left')
            ->join('tb_master_bowheer_bilco b', 'b.id_bowheer = p.id_bowheer', 'left')
            ->where('t.id_term', (int) $idTerm)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($term) || strtoupper(trim((string) ($term['bowheer'] ?? ''))) !== 'PT EMR - NRO') {
            return false;
        }

        $myrepTermin = $this->db
            ->select('t.*, h.po_number, h.po_type, h.id_myrep_cluster')
            ->from('tb_myrep_po_termin t')
            ->join('tb_myrep_po_header h', 'h.id_po_header = t.id_po_header', 'inner')
            ->where("CONVERT(h.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape((string) $term['po_number']) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci", null, false)
            ->where('t.termin_no', (int) $term['term_index'])
            ->order_by('t.invoice_date IS NULL', 'ASC', false)
            ->order_by('t.invoice_date', 'DESC')
            ->order_by('t.id_po_termin', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($myrepTermin)) {
            return false;
        }

        $this->load->model('MPO_MyRep');
        $currentStatus = strtoupper(trim((string) ($myrepTermin['status_termin'] ?? 'NOT READY')));
        $status = $reset ? 'READY BILLING' : ($currentStatus === 'PAID' ? 'PAID' : 'BILLED');
        $invoiceValue = $reset ? null : (float) $amount;

        return $this->MPO_MyRep->updateTermin((int) $myrepTermin['id_po_termin'], [
            'status_termin' => $status,
            'invoice_number' => $reset ? '' : (string) ($myrepTermin['invoice_number'] ?? ''),
            'invoice_date' => $reset ? null : $this->normalizeSyncDate($invoiceDate),
            'invoice_value' => $invoiceValue,
            'bast_date' => $reset ? null : ($myrepTermin['bast_date'] ?? null),
            'payment_date' => $reset ? null : ($myrepTermin['payment_date'] ?? null),
            'remark_termin' => (string) ($myrepTermin['remark_termin'] ?? ''),
            'updated_by' => (int) $userId
        ]);
    }

    public function syncMyRepTerminClaim($myrepTerminId, $userId = 0, $cutoffDate = null)
    {
        $this->ensureStandaloneSchema();
        $myrepTerminId = (int) $myrepTerminId;
        if ($myrepTerminId <= 0) {
            return ['status' => false, 'action' => 'skipped', 'message' => 'Invalid MyRep termin id'];
        }

        $myrep = $this->getMyRepTerminForMonitorSync($myrepTerminId);
        if (empty($myrep)) {
            return ['status' => false, 'action' => 'skipped', 'message' => 'MyRep termin not found'];
        }

        $this->ensurePoMonitorFromMyRepPoHeader((int) ($myrep['id_po_header'] ?? 0), $userId);
        return $this->syncMyRepTerminRowToMonitor($myrep, $userId, $cutoffDate);
    }

    public function ensurePoMonitorFromMyRepCluster($clusterId, $userId = 0)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0 || !$this->db->table_exists('tb_myrep_po_header')) {
            return ['status' => false, 'created' => 0, 'existing' => 0, 'synced' => 0, 'message' => 'Cluster MyRep tidak valid'];
        }

        $headers = $this->db
            ->select('id_po_header')
            ->from('tb_myrep_po_header')
            ->where('id_myrep_cluster', $clusterId)
            ->order_by('po_date', 'ASC')
            ->order_by('id_po_header', 'ASC')
            ->get()
            ->result_array();

        $summary = ['status' => true, 'created' => 0, 'existing' => 0, 'synced' => 0, 'message' => 'Sync PO MyRep ke PO Monitor selesai'];
        foreach ($headers as $header) {
            $result = $this->ensurePoMonitorFromMyRepPoHeader((int) ($header['id_po_header'] ?? 0), $userId);
            if (!empty($result['created'])) {
                $summary['created']++;
            } elseif (!empty($result['status'])) {
                $summary['existing']++;
            }
            $summary['synced'] += (int) ($result['synced'] ?? 0);
        }

        return $summary;
    }

    public function ensurePoMonitorFromMyRepPoHeader($poHeaderId, $userId = 0)
    {
        $this->ensureStandaloneSchema();
        $poHeaderId = (int) $poHeaderId;
        if ($poHeaderId <= 0 || !$this->db->table_exists('tb_myrep_po_header') || !$this->db->table_exists('tb_myrep_po_termin')) {
            return ['status' => false, 'created' => false, 'synced' => 0, 'message' => 'PO MyRep tidak valid'];
        }

        $header = $this->db->query("SELECT
                h.*,
                c.cluster_name,
                c.cluster_code,
                c.regional_name,
                c.city_name
            FROM tb_myrep_po_header h
            LEFT JOIN tb_myrep_cluster c ON c.id_myrep_cluster = h.id_myrep_cluster
            WHERE h.id_po_header = ?
            LIMIT 1", [$poHeaderId])->row_array();

        $poNumber = trim((string) ($header['po_number'] ?? ''));
        if (empty($header) || $poNumber === '') {
            return ['status' => false, 'created' => false, 'synced' => 0, 'message' => 'Nomor PO MyRep kosong'];
        }
        $header = $this->resolveActiveMyRepPoHeaderForMonitorSync($header);
        $poHeaderId = (int) ($header['id_po_header'] ?? $poHeaderId);
        $poNumber = trim((string) ($header['po_number'] ?? $poNumber));
        $header = $this->enrichMyRepHeaderLocationForMonitorSync($header);

        $existing = $this->db
            ->select('id_po')
            ->from('tb_po')
            ->where("CONVERT(po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape($poNumber) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci", null, false)
            ->order_by('id_po', 'ASC')
            ->limit(1)
            ->get()
            ->row_array();

        if (!empty($existing)) {
            $this->ensurePoMonitorTermsFromMyRepHeader((int) $existing['id_po'], $poHeaderId, $userId);
            $this->refreshPoMonitorHeaderFromMyRepHeader((int) $existing['id_po'], $header, $userId);
            $sync = $this->syncMyRepClaimsForPoNumber($poNumber, $userId, false);
            return [
                'status' => true,
                'created' => false,
                'id_po' => (int) $existing['id_po'],
                'synced' => (int) ($sync['matched'] ?? 0),
                'message' => 'PO Monitor sudah ada'
            ];
        }

        $idBowheer = $this->resolveBowheerId('PT EMR - NRO');
        $poDate = $this->normalizeSyncDate($header['po_date'] ?? null);
        $poValue = (float) ($header['po_value'] ?? 0);
        $sourceHash = hash('sha256', 'MYREP_PO_HEADER|' . $poHeaderId . '|' . strtoupper($poNumber));
        $typeProjectParts = array_filter([
            strtoupper(trim((string) ($header['po_type'] ?? ''))),
            trim((string) ($header['regional_name'] ?? '')),
            trim((string) ($header['city_name'] ?? ''))
        ]);

        $this->db->trans_begin();
        $this->db->insert('tb_po', [
            'po_number' => $poNumber,
            'po_date' => $poDate,
            'id_bowheer' => $idBowheer,
            'total_value' => $poValue,
            'status_po' => 'ON PO',
            'dashboard_bowheer' => 'PT EMR - NRO',
            'type_project' => implode(' - ', $typeProjectParts) ?: 'MYREP',
            'dashboard_all_invoice' => 0,
            'dashboard_invoice_2026' => 0,
            'dashboard_outs_2026' => 0,
            'dashboard_co_2027' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $userId ?: null,
            'notes' => 'Auto mirror dari PO MyRep #' . $poHeaderId,
            'source_file' => 'MYREP_PO_HEADER',
            'source_row_no' => $poHeaderId,
            'source_hash' => $sourceHash
        ]);
        $idPo = (int) $this->db->insert_id();

        $this->db->insert('tb_po_amend', [
            'id_po' => $idPo,
            'amend_no' => 1,
            'release_value' => $poValue,
            'release_date' => $poDate,
            'notes' => 'Auto mirror dari PO MyRep'
        ]);
        $idAmend = (int) $this->db->insert_id();

        $this->ensurePoMonitorTermsFromMyRepHeader($idPo, $poHeaderId, $userId, $idAmend);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'created' => false, 'synced' => 0, 'message' => 'Gagal membuat PO Monitor dari MyRep'];
        }

        $this->db->trans_commit();
        $sync = $this->syncMyRepClaimsForPoNumber($poNumber, $userId, false);
        $this->rebuildDashboardCache(null);

        return [
            'status' => true,
            'created' => true,
            'id_po' => $idPo,
            'synced' => (int) ($sync['matched'] ?? 0),
            'message' => 'PO Monitor dibuat dari MyRep'
        ];
    }

    public function deletePoMonitorMirrorFromMyRepHeader($poHeaderId)
    {
        $this->ensureStandaloneSchema();
        $poHeaderId = (int) $poHeaderId;
        if ($poHeaderId <= 0 || !$this->db->table_exists('tb_po')) {
            return ['status' => false, 'deleted' => 0, 'message' => 'Header PO MyRep tidak valid'];
        }

        $rows = $this->db
            ->select('id_po')
            ->from('tb_po')
            ->where('source_file', 'MYREP_PO_HEADER')
            ->where('source_row_no', $poHeaderId)
            ->get()
            ->result_array();
        $idPoList = array_values(array_filter(array_map('intval', array_column($rows, 'id_po'))));

        if (empty($idPoList)) {
            return ['status' => true, 'deleted' => 0, 'message' => 'Tidak ada mirror PO Monitor'];
        }

        $this->db->trans_begin();
        if ($this->db->table_exists('tb_po_target_pipeline')) {
            $this->db
                ->where_in('linked_id_po', $idPoList)
                ->update('tb_po_target_pipeline', [
                    'linked_id_po' => null,
                    'linked_po_number' => null,
                    'pipeline_status' => 'OPEN',
                    'converted_at' => null,
                    'converted_by' => null,
                ]);
        }
        $this->db->where_in('id_po', $idPoList)->delete('tb_po');

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'deleted' => 0, 'message' => 'Gagal menghapus mirror PO Monitor'];
        }

        $this->db->trans_commit();
        $this->rebuildDashboardCache(null);

        return ['status' => true, 'deleted' => count($idPoList), 'message' => 'Mirror PO Monitor dihapus'];
    }

    private function ensurePoMonitorTermsFromMyRepHeader($idPo, $poHeaderId, $userId = 0, $idAmend = 0)
    {
        $idPo = (int) $idPo;
        $poHeaderId = (int) $poHeaderId;
        if ($idPo <= 0 || $poHeaderId <= 0) {
            return 0;
        }

        if ($idAmend <= 0) {
            $amend = $this->db
                ->select('id_amend')
                ->from('tb_po_amend')
                ->where('id_po', $idPo)
                ->order_by('amend_no', 'DESC')
                ->limit(1)
                ->get()
                ->row_array();
            $idAmend = (int) ($amend['id_amend'] ?? 0);
        }

        $terms = $this->db
            ->select('termin_no, termin_percent, termin_value')
            ->from('tb_myrep_po_termin')
            ->where('id_po_header', $poHeaderId)
            ->order_by('termin_no', 'ASC')
            ->get()
            ->result_array();

        $inserted = 0;
        foreach ($terms as $term) {
            $termIndex = (int) ($term['termin_no'] ?? 0);
            if ($termIndex < 1 || $termIndex > 5) {
                continue;
            }

            $existing = $this->db
                ->select('id_term')
                ->from('tb_po_term')
                ->where('id_po', $idPo)
                ->where('term_index', $termIndex)
                ->order_by('id_term', 'ASC')
                ->limit(1)
                ->get()
                ->row_array();

            $payload = [
                'id_amend' => $idAmend > 0 ? $idAmend : null,
                'percent' => (float) ($term['termin_percent'] ?? 0),
                'value' => (float) ($term['termin_value'] ?? 0),
                'plan_amount' => (float) ($term['termin_value'] ?? 0),
            ];

            if (!empty($existing)) {
                $this->db->where('id_term', (int) $existing['id_term'])->update('tb_po_term', $payload);
            } else {
                $payload['id_po'] = $idPo;
                $payload['term_index'] = $termIndex;
                $payload['target_status'] = 'OPEN';
                $this->db->insert('tb_po_term', $payload);
                $inserted++;
            }
        }

        return $inserted;
    }

    private function refreshPoMonitorHeaderFromMyRepHeader($idPo, array $header, $userId = 0)
    {
        $idPo = (int) $idPo;
        if ($idPo <= 0) {
            return false;
        }

        $poDate = $this->normalizeSyncDate($header['po_date'] ?? null);
        $poValue = (float) ($header['po_value'] ?? 0);
        $typeProjectParts = array_filter([
            strtoupper(trim((string) ($header['po_type'] ?? ''))),
            trim((string) ($header['regional_name'] ?? '')),
            trim((string) ($header['city_name'] ?? ''))
        ]);

        $this->db
            ->where('id_po', $idPo)
            ->update('tb_po', [
                'po_date' => $poDate,
                'total_value' => $poValue,
                'status_po' => 'ON PO',
                'dashboard_bowheer' => 'PT EMR - NRO',
                'type_project' => implode(' - ', $typeProjectParts) ?: 'MYREP',
                'source_row_no' => (int) ($header['id_po_header'] ?? 0),
                'source_hash' => hash('sha256', 'MYREP_PO_HEADER|' . (int) ($header['id_po_header'] ?? 0) . '|' . strtoupper(trim((string) ($header['po_number'] ?? '')))),
                'created_by' => $userId ?: null,
                'notes' => 'Auto mirror dari PO MyRep #' . (int) ($header['id_po_header'] ?? 0)
            ]);

        $amend = $this->db
            ->select('id_amend')
            ->from('tb_po_amend')
            ->where('id_po', $idPo)
            ->order_by('amend_no', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();
        if (!empty($amend)) {
            $this->db
                ->where('id_amend', (int) $amend['id_amend'])
                ->update('tb_po_amend', [
                    'release_value' => $poValue,
                    'release_date' => $poDate,
                    'notes' => 'Auto mirror dari PO MyRep'
                ]);
        }

        return true;
    }

    public function backfillPoMonitorFromMyRepHeaders(array $poNumbers = [], $userId = 0, $limit = 50, $offset = 0, $allowAll = false)
    {
        $this->ensureStandaloneSchema();
        if (!$this->db->table_exists('tb_myrep_po_header')) {
            return [
                'status' => false,
                'created' => 0,
                'existing' => 0,
                'synced' => 0,
                'processed' => 0,
                'errors' => ['Tabel PO MyRep belum tersedia.']
            ];
        }

        $poNumbers = array_values(array_unique(array_filter(array_map(function ($poNumber) {
            return strtoupper(trim((string) $poNumber));
        }, $poNumbers))));

        $limit = max(1, min(200, (int) $limit));
        $offset = max(0, (int) $offset);
        if (empty($poNumbers) && !$allowAll) {
            return [
                'status' => false,
                'created' => 0,
                'existing' => 0,
                'synced' => 0,
                'processed' => 0,
                'limit' => $limit,
                'offset' => $offset,
                'next_offset' => $offset,
                'errors' => ['Isi po_numbers, atau gunakan all=1 dengan limit/offset untuk backfill bertahap.']
            ];
        }

        $query = $this->db
            ->select('MIN(id_po_header) AS id_po_header, UPPER(TRIM(po_number)) AS po_number', false)
            ->from('tb_myrep_po_header')
            ->where("COALESCE(TRIM(po_number), '') !=", '');

        if (!empty($poNumbers)) {
            $query->where_in('UPPER(TRIM(po_number))', $poNumbers);
        }

        $headers = $query
            ->order_by('po_number', 'ASC')
            ->group_by('UPPER(TRIM(po_number))')
            ->limit($limit, $offset)
            ->get()
            ->result_array();

        $summary = [
            'status' => true,
            'created' => 0,
            'existing' => 0,
            'synced' => 0,
            'processed' => 0,
            'limit' => $limit,
            'offset' => $offset,
            'next_offset' => $offset + count($headers),
            'errors' => []
        ];

        foreach ($headers as $header) {
            $summary['processed']++;
            $result = $this->ensurePoMonitorFromMyRepPoHeader((int) ($header['id_po_header'] ?? 0), $userId);
            if (empty($result['status'])) {
                $summary['errors'][] = trim((string) ($header['po_number'] ?? '-')) . ': ' . ($result['message'] ?? 'gagal sync');
                continue;
            }

            if (!empty($result['created'])) {
                $summary['created']++;
            } else {
                $summary['existing']++;
            }
            $summary['synced'] += (int) ($result['synced'] ?? 0);
        }

        $summary['status'] = empty($summary['errors']);
        if ($summary['processed'] > 0) {
            $this->rebuildDashboardCache(null);
        }

        return $summary;
    }

    private function resolveActiveMyRepPoHeaderForMonitorSync(array $header)
    {
        $poNumber = trim((string) ($header['po_number'] ?? ''));
        $poType = strtoupper(trim((string) ($header['po_type'] ?? '')));
        $idCluster = (int) ($header['id_myrep_cluster'] ?? 0);
        $idMainfeeder = (int) ($header['id_mainfeeder'] ?? 0);
        if ($poNumber === '' || $poType === '') {
            return $header;
        }

        $query = $this->db
            ->select('h.*, c.cluster_name, c.cluster_code, c.regional_name, c.city_name')
            ->from('tb_myrep_po_header h')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = h.id_myrep_cluster', 'left')
            ->where("CONVERT(UPPER(TRIM(h.po_number)) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape(strtoupper($poNumber)) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci", null, false)
            ->where("CONVERT(UPPER(TRIM(COALESCE(h.po_type, ''))) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape($poType) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci", null, false);

        if (in_array($poType, ['MAINFEEDER', 'FWA'], true)) {
            if ($idMainfeeder <= 0 || !$this->db->field_exists('id_mainfeeder', 'tb_myrep_po_header')) {
                return $header;
            }
            $query->where('h.id_mainfeeder', $idMainfeeder);
        } else {
            if ($idCluster <= 0) {
                return $header;
            }
            $query->where('h.id_myrep_cluster', $idCluster);
        }

        $active = $query
            ->order_by("CASE UPPER(TRIM(COALESCE(h.po_category, 'INITIAL'))) WHEN 'FINAL' THEN 1 WHEN 'AMANDMENT' THEN 2 WHEN 'AMENDMENT' THEN 2 WHEN 'INITIAL' THEN 3 ELSE 4 END", 'ASC', false)
            ->order_by('h.po_date', 'DESC')
            ->order_by('h.id_po_header', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        return !empty($active) ? $active : $header;
    }

    private function enrichMyRepHeaderLocationForMonitorSync(array $header)
    {
        $mainfeederId = (int) ($header['id_mainfeeder'] ?? 0);
        if ($mainfeederId <= 0 || !$this->db->table_exists('tb_rfs_myrep_mainfeeder')) {
            return $header;
        }

        $mainfeeder = $this->db
            ->select('project_type, mainfeeder_name, regional_name, province_name, city_name')
            ->from('tb_rfs_myrep_mainfeeder')
            ->where('id_mainfeeder', $mainfeederId)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($mainfeeder)) {
            return $header;
        }

        $cityName = trim((string) ($mainfeeder['city_name'] ?? ''));
        $mapping = $this->resolveCityMappingForMonitorSync($cityName);
        $header['po_type'] = trim((string) ($mainfeeder['project_type'] ?? $header['po_type'] ?? '')) ?: ($header['po_type'] ?? '');
        $header['cluster_name'] = trim((string) ($mainfeeder['mainfeeder_name'] ?? $header['cluster_name'] ?? '')) ?: ($header['cluster_name'] ?? '');
        $header['regional_name'] = trim((string) ($mainfeeder['regional_name'] ?? '')) ?: (trim((string) ($mapping['regional_name'] ?? '')) ?: ($header['regional_name'] ?? ''));
        $header['province_name'] = trim((string) ($mainfeeder['province_name'] ?? '')) ?: (trim((string) ($mapping['province_name'] ?? '')) ?: ($header['province_name'] ?? ''));
        $header['city_name'] = $cityName !== '' ? $cityName : ($header['city_name'] ?? '');

        return $header;
    }

    private function resolveCityMappingForMonitorSync($cityName)
    {
        $cityName = strtoupper(trim((string) $cityName));
        if ($cityName === '' || !$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return [];
        }

        return (array) $this->db
            ->select('regional_name, province_name, city_name')
            ->from('tb_myrep_pic_mapping_city')
            ->where("CONVERT(UPPER(TRIM(city_name)) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape($cityName) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci", null, false)
            ->where("COALESCE(TRIM(regional_name), '') !=", '')
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function syncMyRepClaimsSince($cutoffDate = null, $userId = 0)
    {
        $this->ensureStandaloneSchema();
        $cutoffDate = $this->normalizeSyncDate($cutoffDate);
        if (!$this->db->table_exists('tb_myrep_po_termin') || !$this->db->table_exists('tb_myrep_po_header')) {
            return [
                'status' => false,
                'message' => 'Tabel PO MyRep belum tersedia.',
                'matched' => 0,
                'inserted' => 0,
                'updated' => 0,
                'deleted' => 0,
                'skipped' => 0,
                'unmatched' => []
            ];
        }

        $invoiceValueSql = $this->db->field_exists('invoice_value', 'tb_myrep_po_termin')
            ? 'COALESCE(t.invoice_value, t.termin_value, 0)'
            : 'COALESCE(t.termin_value, 0)';

        $whereDateSql = $cutoffDate !== null ? 'AND DATE(t.invoice_date) >= ?' : '';
        $queryParams = $cutoffDate !== null ? [$cutoffDate] : [];

        $rows = $this->db->query("SELECT
                t.id_po_termin,
                t.id_po_header,
                t.termin_no,
                t.termin_value,
                {$invoiceValueSql} AS invoice_amount,
                t.status_termin,
                t.invoice_date,
                t.invoice_number,
                p.po_number,
                p.po_type,
                p.po_category
            FROM tb_myrep_po_termin t
            JOIN tb_myrep_po_header p ON p.id_po_header = t.id_po_header
            WHERE t.invoice_date IS NOT NULL
                {$whereDateSql}
                AND CONVERT(UPPER(TRIM(COALESCE(t.status_termin, ''))) USING utf8mb4) COLLATE utf8mb4_unicode_ci IN (
                    CONVERT('READY BILLING' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
                    CONVERT('BILLED' USING utf8mb4) COLLATE utf8mb4_unicode_ci,
                    CONVERT('PAID' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                )
            ORDER BY t.invoice_date ASC, p.po_number ASC, t.termin_no ASC", $queryParams)->result_array();

        $summary = [
            'status' => true,
            'message' => 'Sync selesai.',
            'matched' => 0,
            'inserted' => 0,
            'updated' => 0,
            'deleted' => 0,
            'skipped' => 0,
            'unmatched' => []
        ];

        foreach ($rows as $row) {
            $result = $this->syncMyRepTerminRowToMonitor($row, $userId, $cutoffDate, false);
            $action = (string) ($result['action'] ?? 'skipped');
            if (!empty($result['status'])) {
                $summary['matched']++;
            }
            if (isset($summary[$action])) {
                $summary[$action]++;
            } else {
                $summary['skipped']++;
            }
            if (empty($result['status']) && !empty($result['po_number'])) {
                $summary['unmatched'][] = [
                    'po_number' => $result['po_number'],
                    'term' => $result['term'] ?? null,
                    'message' => $result['message'] ?? 'Unmatched'
                ];
            }
        }

        $this->rebuildDashboardCache(null);
        return $summary;
    }

    public function syncMyRepClaimsForPoNumber($poNumber, $userId = 0, $ensureMonitor = true)
    {
        $this->ensureStandaloneSchema();
        $poNumber = trim((string) $poNumber);
        if ($poNumber === '' || !$this->db->table_exists('tb_myrep_po_termin') || !$this->db->table_exists('tb_myrep_po_header')) {
            return [
                'status' => false,
                'message' => 'PO MyRep tidak ditemukan.',
                'matched' => 0,
                'inserted' => 0,
                'updated' => 0,
                'deleted' => 0,
                'skipped' => 0,
                'unmatched' => []
            ];
        }

        $invoiceValueSql = $this->db->field_exists('invoice_value', 'tb_myrep_po_termin')
            ? 'COALESCE(t.invoice_value, t.termin_value, 0)'
            : 'COALESCE(t.termin_value, 0)';

        $rows = $this->db->query("SELECT
                t.id_po_termin,
                t.id_po_header,
                t.termin_no,
                t.termin_value,
                {$invoiceValueSql} AS invoice_amount,
                t.status_termin,
                t.invoice_date,
                t.invoice_number,
                p.po_number,
                p.po_type,
                p.po_category
            FROM tb_myrep_po_termin t
            JOIN tb_myrep_po_header p ON p.id_po_header = t.id_po_header
            WHERE p.po_number = ?
            ORDER BY
                t.termin_no ASC,
                CASE UPPER(TRIM(COALESCE(p.po_category, 'INITIAL')))
                    WHEN 'FINAL' THEN 1
                    WHEN 'AMANDMENT' THEN 2
                    WHEN 'AMENDMENT' THEN 2
                    WHEN 'INITIAL' THEN 3
                    ELSE 4
                END ASC,
                t.invoice_date IS NULL ASC,
                t.invoice_date DESC,
                CASE WHEN COALESCE(t.termin_value, 0) != 0 THEN 0 ELSE 1 END ASC,
                t.id_po_termin DESC", [$poNumber])->result_array();

        $rowsByTerm = [];
        foreach ($rows as $row) {
            $termNo = (int) ($row['termin_no'] ?? 0);
            if ($termNo > 0 && !isset($rowsByTerm[$termNo])) {
                $rowsByTerm[$termNo] = $row;
            }
        }
        $rows = array_values($rowsByTerm);
        if ($ensureMonitor && !empty($rows[0]['id_po_header'])) {
            $this->ensurePoMonitorFromMyRepPoHeader((int) $rows[0]['id_po_header'], $userId);
        }

        $summary = [
            'status' => true,
            'message' => 'Sync PO selesai.',
            'matched' => 0,
            'inserted' => 0,
            'updated' => 0,
            'deleted' => 0,
            'skipped' => 0,
            'unmatched' => []
        ];

        foreach ($rows as $row) {
            $result = $this->syncMyRepTerminRowToMonitor($row, $userId, null, false);
            $action = (string) ($result['action'] ?? 'skipped');
            if (!empty($result['status'])) {
                $summary['matched']++;
            }
            if (isset($summary[$action])) {
                $summary[$action]++;
            } else {
                $summary['skipped']++;
            }
            if (empty($result['status']) && !empty($result['po_number'])) {
                $summary['unmatched'][] = [
                    'po_number' => $result['po_number'],
                    'term' => $result['term'] ?? null,
                    'message' => $result['message'] ?? 'Unmatched'
                ];
            }
        }

        return $summary;
    }

    public function rebuildMyRepSyncClaimsSince($cutoffDate = null, $userId = 0)
    {
        $this->ensureStandaloneSchema();
        $cutoffDate = $this->normalizeSyncDate($cutoffDate);

        $existingClaimsQuery = $this->db
            ->select('id_claim, id_term, invoice_date, invoice_amount')
            ->from('tb_po_term_claim')
            ->where('claim_source', 'MYREP_SYNC');
        if ($cutoffDate !== null) {
            $existingClaimsQuery->where('invoice_date >=', $cutoffDate);
        }
        $existingClaims = $existingClaimsQuery->get()->result_array();

        $affectedTermIds = [];
        $this->db->trans_begin();
        foreach ($existingClaims as $claim) {
            $idTerm = (int) ($claim['id_term'] ?? 0);
            $affectedTermIds[] = $idTerm;
            $this->applyPoMonitorClaimDelta($idTerm, $claim['invoice_date'], -1 * (float) $claim['invoice_amount']);
        }

        if ($cutoffDate !== null) {
            $this->db
                ->where('claim_source', 'MYREP_SYNC')
                ->where('invoice_date >=', $cutoffDate)
                ->delete('tb_po_term_claim');
        } else {
            $this->db
                ->where('claim_source', 'MYREP_SYNC')
                ->delete('tb_po_term_claim');
        }

        foreach (array_unique(array_filter($affectedTermIds)) as $idTerm) {
            $this->refreshPoMonitorTermInvoiceDate((int) $idTerm);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return [
                'status' => false,
                'message' => 'Gagal clear claim sync lama.',
                'matched' => 0,
                'inserted' => 0,
                'updated' => 0,
                'deleted' => 0,
                'skipped' => 0,
                'unmatched' => []
            ];
        }

        $this->db->trans_commit();
        $summary = $this->syncMyRepClaimsSince($cutoffDate, $userId);
        $summary['deleted'] = count($existingClaims);
        return $summary;
    }

    private function syncMyRepTerminRowToMonitor(array $myrep, $userId = 0, $cutoffDate = null, $rebuildCache = true)
    {
        $sourceRaw = 'MYREP_TERMIN:' . (int) ($myrep['id_po_termin'] ?? 0);
        $existingClaim = $this->db
            ->get_where('tb_po_term_claim', [
                'claim_source' => 'MYREP_SYNC',
                'source_raw' => $sourceRaw
            ])
            ->row_array();

        $invoiceDate = $this->normalizeSyncDate($myrep['invoice_date'] ?? null);
        $amount = (float) ($myrep['invoice_amount'] ?? 0);
        if (abs($amount) < 0.000001 && isset($myrep['termin_value'])) {
            $amount = (float) $myrep['termin_value'];
        }

        $statusTermin = strtoupper(trim((string) ($myrep['status_termin'] ?? '')));
        $termNo = (int) ($myrep['termin_no'] ?? 0);
        $poNumber = (string) ($myrep['po_number'] ?? '');
        $cutoffDate = $this->normalizeSyncDate($cutoffDate);
        $isSyncable = $invoiceDate !== null
            && ($cutoffDate === null || strtotime($invoiceDate) >= strtotime($cutoffDate))
            && abs($amount) >= 0.000001
            && in_array($statusTermin, ['READY BILLING', 'BILLED', 'PAID'], true);

        if (!$isSyncable) {
            if (!empty($existingClaim)) {
                $this->db->trans_begin();
                $this->applyPoMonitorClaimDelta((int) $existingClaim['id_term'], $existingClaim['invoice_date'], -1 * (float) $existingClaim['invoice_amount']);
                $this->db->where('id_claim', (int) $existingClaim['id_claim'])->delete('tb_po_term_claim');
                $this->refreshPoMonitorTermInvoiceDate((int) $existingClaim['id_term']);
                if ($rebuildCache) {
                    $this->rebuildDashboardCache(null);
                }
                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    return ['status' => false, 'action' => 'skipped', 'message' => 'Failed to remove old sync claim'];
                }
                $this->db->trans_commit();
                return ['status' => true, 'action' => 'deleted', 'message' => 'Sync claim removed'];
            }

            $term = $this->findPoMonitorTermForMyRep($poNumber, $termNo);
            $removedSibling = false;
            if (!empty($term)) {
                $siblingClaims = $this->db
                    ->from('tb_po_term_claim')
                    ->where('id_term', (int) $term['id_term'])
                    ->where('claim_source', 'MYREP_SYNC')
                    ->where('source_raw !=', $sourceRaw)
                    ->get()
                    ->result_array();

                if (!empty($siblingClaims)) {
                    $this->db->trans_begin();
                    foreach ($siblingClaims as $claim) {
                        $this->applyPoMonitorClaimDelta((int) $term['id_term'], $claim['invoice_date'], -1 * (float) $claim['invoice_amount']);
                        $this->db->where('id_claim', (int) $claim['id_claim'])->delete('tb_po_term_claim');
                    }
                    $this->refreshPoMonitorTermInvoiceDate((int) $term['id_term']);
                    if ($rebuildCache) {
                        $this->rebuildDashboardCache(null);
                    }
                    if ($this->db->trans_status() === false) {
                        $this->db->trans_rollback();
                        return ['status' => false, 'action' => 'skipped', 'message' => 'Failed to remove sibling sync claim'];
                    }
                    $this->db->trans_commit();
                    $removedSibling = true;
                }
            }
            if (!empty($term) && abs($amount) >= 0.000001) {
                $staleClaim = $this->db
                    ->from('tb_po_term_claim')
                    ->where('id_term', (int) $term['id_term'])
                    ->where('ABS(invoice_amount - ' . $this->db->escape($amount) . ') <', 1, false)
                    ->where_in('claim_source', ['MYREP_SYNC', 'IMPORT'])
                    ->order_by("CASE WHEN claim_source = 'MYREP_SYNC' THEN 0 ELSE 1 END", '', false)
                    ->order_by('id_claim', 'ASC')
                    ->limit(1)
                    ->get()
                    ->row_array();

                if (!empty($staleClaim)) {
                    $this->db->trans_begin();
                    $this->applyPoMonitorClaimDelta((int) $term['id_term'], $staleClaim['invoice_date'], -1 * (float) $staleClaim['invoice_amount']);
                    $this->db->where('id_claim', (int) $staleClaim['id_claim'])->delete('tb_po_term_claim');
                    $this->refreshPoMonitorTermInvoiceDate((int) $term['id_term']);
                    if ($rebuildCache) {
                        $this->rebuildDashboardCache(null);
                    }
                    if ($this->db->trans_status() === false) {
                        $this->db->trans_rollback();
                        return ['status' => false, 'action' => 'skipped', 'message' => 'Failed to remove stale claim'];
                    }
                    $this->db->trans_commit();
                    return ['status' => true, 'action' => 'deleted', 'message' => 'Stale claim removed'];
                }
            }

            if ($removedSibling) {
                return ['status' => true, 'action' => 'deleted', 'message' => 'Sibling sync claim removed'];
            }

            return [
                'status' => false,
                'action' => 'skipped',
                'po_number' => $poNumber,
                'term' => $termNo,
                'message' => 'Termin belum eligible sync'
            ];
        }

        $term = $this->findPoMonitorTermForMyRep($poNumber, $termNo);
        if (empty($term)) {
            return [
                'status' => false,
                'action' => 'skipped',
                'po_number' => $poNumber,
                'term' => $termNo,
                'message' => 'PO/term tidak ditemukan di PO Monitor'
            ];
        }

        $this->db->trans_begin();
        $siblingClaims = $this->db
            ->from('tb_po_term_claim')
            ->where('id_term', (int) $term['id_term'])
            ->where('claim_source', 'MYREP_SYNC')
            ->where('source_raw !=', $sourceRaw)
            ->get()
            ->result_array();
        foreach ($siblingClaims as $claim) {
            $this->applyPoMonitorClaimDelta((int) $term['id_term'], $claim['invoice_date'], -1 * (float) $claim['invoice_amount']);
            $this->db->where('id_claim', (int) $claim['id_claim'])->delete('tb_po_term_claim');
        }

        if (!empty($existingClaim)) {
            $changed = (int) $existingClaim['id_term'] !== (int) $term['id_term']
                || (string) $existingClaim['invoice_date'] !== (string) $invoiceDate
                || abs((float) $existingClaim['invoice_amount'] - $amount) >= 0.01;

            if ($changed) {
                $this->applyPoMonitorClaimDelta((int) $existingClaim['id_term'], $existingClaim['invoice_date'], -1 * (float) $existingClaim['invoice_amount']);
                $this->db
                    ->where('id_claim', (int) $existingClaim['id_claim'])
                    ->update('tb_po_term_claim', [
                        'id_term' => (int) $term['id_term'],
                        'id_allocation' => null,
                        'invoice_date' => $invoiceDate,
                        'invoice_amount' => $amount,
                        'created_by' => $userId ?: null
                    ]);
                $this->applyPoMonitorClaimDelta((int) $term['id_term'], $invoiceDate, $amount);
                $this->refreshPoMonitorTermInvoiceDate((int) $existingClaim['id_term']);
                $this->refreshPoMonitorTermInvoiceDate((int) $term['id_term']);
                $action = 'updated';
            } else {
                $action = 'unchanged';
            }

            $duplicateClaim = $this->db
                ->from('tb_po_term_claim')
                ->where('id_term', (int) $term['id_term'])
                ->where('id_claim !=', (int) $existingClaim['id_claim'])
                ->where('ABS(invoice_amount - ' . $this->db->escape($amount) . ') <', 1, false)
                ->where('claim_source', 'IMPORT')
                ->order_by('id_claim', 'ASC')
                ->limit(1)
                ->get()
                ->row_array();
            if (!empty($duplicateClaim)) {
                $this->applyPoMonitorClaimDelta((int) $term['id_term'], $duplicateClaim['invoice_date'], -1 * (float) $duplicateClaim['invoice_amount']);
                $this->db->where('id_claim', (int) $duplicateClaim['id_claim'])->delete('tb_po_term_claim');
                $this->refreshPoMonitorTermInvoiceDate((int) $term['id_term']);
                $action = $action === 'unchanged' ? 'updated' : $action;
            }
        } else {
            $siblingSyncClaim = $this->db
                ->from('tb_po_term_claim')
                ->where('id_term', (int) $term['id_term'])
                ->where('claim_source', 'MYREP_SYNC')
                ->where('ABS(invoice_amount - ' . $this->db->escape($amount) . ') <', 1, false)
                ->order_by('id_claim', 'ASC')
                ->limit(1)
                ->get()
                ->row_array();

            if (!empty($siblingSyncClaim)) {
                $changed = (string) $siblingSyncClaim['invoice_date'] !== (string) $invoiceDate
                    || abs((float) $siblingSyncClaim['invoice_amount'] - $amount) >= 0.01
                    || (string) ($siblingSyncClaim['source_raw'] ?? '') !== $sourceRaw;

                if ($changed) {
                    $this->applyPoMonitorClaimDelta((int) $term['id_term'], $siblingSyncClaim['invoice_date'], -1 * (float) $siblingSyncClaim['invoice_amount']);
                    $this->db
                        ->where('id_claim', (int) $siblingSyncClaim['id_claim'])
                        ->update('tb_po_term_claim', [
                            'invoice_date' => $invoiceDate,
                            'invoice_amount' => $amount,
                            'source_raw' => $sourceRaw,
                            'created_by' => $userId ?: null
                        ]);
                    $this->applyPoMonitorClaimDelta((int) $term['id_term'], $invoiceDate, $amount);
                    $this->refreshPoMonitorTermInvoiceDate((int) $term['id_term']);
                    $action = 'updated';
                } else {
                    $action = 'unchanged';
                }

                $duplicateClaim = $this->db
                    ->from('tb_po_term_claim')
                    ->where('id_term', (int) $term['id_term'])
                    ->where('id_claim !=', (int) $siblingSyncClaim['id_claim'])
                    ->where('ABS(invoice_amount - ' . $this->db->escape($amount) . ') <', 1, false)
                    ->where('claim_source', 'IMPORT')
                    ->order_by('id_claim', 'ASC')
                    ->limit(1)
                    ->get()
                    ->row_array();
                if (!empty($duplicateClaim)) {
                    $this->applyPoMonitorClaimDelta((int) $term['id_term'], $duplicateClaim['invoice_date'], -1 * (float) $duplicateClaim['invoice_amount']);
                    $this->db->where('id_claim', (int) $duplicateClaim['id_claim'])->delete('tb_po_term_claim');
                    $this->refreshPoMonitorTermInvoiceDate((int) $term['id_term']);
                    $action = $action === 'unchanged' ? 'updated' : $action;
                }
            } else {
                $adoptedClaim = $this->db
                    ->from('tb_po_term_claim')
                    ->where('id_term', (int) $term['id_term'])
                    ->where('ABS(invoice_amount - ' . $this->db->escape($amount) . ') <', 1, false)
                    ->group_start()
                        ->where('claim_source !=', 'MYREP_SYNC')
                        ->or_where('claim_source IS NULL', null, false)
                    ->group_end()
                    ->order_by("CASE WHEN claim_source = 'IMPORT' THEN 0 ELSE 1 END", '', false)
                    ->order_by('id_claim', 'ASC')
                    ->limit(1)
                    ->get()
                    ->row_array();

                if (!empty($adoptedClaim)) {
                    $this->applyPoMonitorClaimDelta((int) $term['id_term'], $adoptedClaim['invoice_date'], -1 * (float) $adoptedClaim['invoice_amount']);
                    $this->db
                        ->where('id_claim', (int) $adoptedClaim['id_claim'])
                        ->update('tb_po_term_claim', [
                            'invoice_date' => $invoiceDate,
                            'claim_source' => 'MYREP_SYNC',
                            'source_raw' => $sourceRaw,
                            'created_by' => $userId ?: null
                        ]);
                    $this->applyPoMonitorClaimDelta((int) $term['id_term'], $invoiceDate, $amount);
                    $this->refreshPoMonitorTermInvoiceDate((int) $term['id_term']);
                    $action = 'updated';
                } else {
                    $this->db->insert('tb_po_term_claim', [
                        'id_term' => (int) $term['id_term'],
                        'id_allocation' => null,
                        'invoice_date' => $invoiceDate,
                        'invoice_amount' => $amount,
                        'claim_source' => 'MYREP_SYNC',
                        'source_raw' => $sourceRaw,
                        'created_by' => $userId ?: null
                    ]);
                    $this->applyPoMonitorClaimDelta((int) $term['id_term'], $invoiceDate, $amount);
                    $this->refreshPoMonitorTermInvoiceDate((int) $term['id_term']);
                    $action = 'inserted';
                }
            }
        }

        $this->refreshPoDashboardMetrics((int) $term['id_po']);

        if ($rebuildCache) {
            $this->rebuildDashboardCache(null);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return [
                'status' => false,
                'action' => 'skipped',
                'po_number' => $poNumber,
                'term' => $termNo,
                'message' => 'Gagal sync ke PO Monitor'
            ];
        }

        $this->db->trans_commit();
        return [
            'status' => true,
            'action' => $action,
            'po_number' => $poNumber,
            'term' => $termNo,
            'message' => 'Synced'
        ];
    }

    private function getMyRepTerminForMonitorSync($myrepTerminId)
    {
        if (!$this->db->table_exists('tb_myrep_po_termin') || !$this->db->table_exists('tb_myrep_po_header')) {
            return [];
        }

        $invoiceValueSql = $this->db->field_exists('invoice_value', 'tb_myrep_po_termin')
            ? 'COALESCE(t.invoice_value, t.termin_value, 0)'
            : 'COALESCE(t.termin_value, 0)';

        return $this->db->query("SELECT
                t.id_po_termin,
                t.id_po_header,
                t.termin_no,
                t.termin_value,
                {$invoiceValueSql} AS invoice_amount,
                t.status_termin,
                t.invoice_date,
                t.invoice_number,
                p.po_number,
                p.po_type,
                p.po_category
            FROM tb_myrep_po_termin t
            JOIN tb_myrep_po_header p ON p.id_po_header = t.id_po_header
            WHERE t.id_po_termin = ?
            LIMIT 1", [(int) $myrepTerminId])->row_array();
    }

    private function findPoMonitorTermForMyRep($poNumber, $termNo)
    {
        $termNo = (int) $termNo;
        $poNumber = trim((string) $poNumber);
        $normalizedPo = $this->normalizePoMonitorSyncPoNumber($poNumber);
        if ($normalizedPo === '' || $termNo < 1 || $termNo > 5) {
            return [];
        }

        $directRow = $this->db
            ->select('t.id_term, t.id_po, t.term_index, t.target_status, p.po_number')
            ->from('tb_po p')
            ->join('tb_po_term t', 't.id_po = p.id_po', 'inner')
            ->where('p.po_number', $poNumber)
            ->where('t.term_index', $termNo)
            ->order_by('t.id_term', 'ASC')
            ->limit(1)
            ->get()
            ->row_array();
        if (!empty($directRow)) {
            return $directRow;
        }

        $rows = $this->db
            ->select('t.id_term, t.id_po, t.term_index, t.target_status, p.po_number')
            ->from('tb_po_term t')
            ->join('tb_po p', 'p.id_po = t.id_po', 'inner')
            ->where('t.term_index', $termNo)
            ->like('p.po_number', $normalizedPo)
            ->get()
            ->result_array();

        foreach ($rows as $row) {
            if ($this->normalizePoMonitorSyncPoNumber((string) ($row['po_number'] ?? '')) === $normalizedPo) {
                return $row;
            }
        }

        return [];
    }

    private function applyPoMonitorClaimDelta($idTerm, $invoiceDate, $amountDelta)
    {
        $term = $this->db->get_where('tb_po_term', ['id_term' => (int) $idTerm])->row_array();
        if (!$term || empty($invoiceDate) || abs((float) $amountDelta) < 0.01) {
            return;
        }

        $amountDelta = (float) $amountDelta;
        $this->db->set('dashboard_all_invoice', 'GREATEST(COALESCE(dashboard_all_invoice, 0) + ' . $this->db->escape($amountDelta) . ', 0)', false);
        if ((int) date('Y', strtotime($invoiceDate)) === 2026) {
            $this->db->set('dashboard_invoice_2026', 'GREATEST(COALESCE(dashboard_invoice_2026, 0) + ' . $this->db->escape($amountDelta) . ', 0)', false);
            if (($term['target_status'] ?? '') === 'TARGET_WEEK') {
                $this->db->set('dashboard_outs_2026', 'GREATEST(COALESCE(dashboard_outs_2026, 0) - ' . $this->db->escape($amountDelta) . ', 0)', false);
            } elseif (($term['target_status'] ?? '') === 'CARRY_OVER') {
                $this->db->set('dashboard_co_2027', 'GREATEST(COALESCE(dashboard_co_2027, 0) - ' . $this->db->escape($amountDelta) . ', 0)', false);
            }
        }
        $this->db->where('id_po', (int) $term['id_po'])->update('tb_po');
        $this->refreshPoDashboardMetrics((int) $term['id_po']);
    }

    private function refreshPoDashboardMetrics($idPo)
    {
        $idPo = (int) $idPo;
        if ($idPo <= 0) {
            return;
        }

        $baseRows = $this->db->query("SELECT
                target_status,
                invoice_date,
                SUM(amount) AS amount
            FROM (
                SELECT
                    COALESCE(CONVERT(NULLIF(a.target_status, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci, CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci) AS target_status,
                    COALESCE(a.invoice_date, t.invoice_date) AS invoice_date,
                    COALESCE(NULLIF(a.plan_amount, 0), a.allocation_value, 0) AS amount
                FROM tb_po_term_allocation a
                JOIN tb_po_term t ON t.id_term = a.id_term
                WHERE t.id_po = ?
                UNION ALL
                SELECT
                    CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci AS target_status,
                    t.invoice_date,
                    COALESCE(NULLIF(t.plan_amount, 0), t.value, 0) AS amount
                FROM tb_po_term t
                WHERE t.id_po = ?
                    AND NOT EXISTS (
                        SELECT 1 FROM tb_po_term_allocation a WHERE a.id_term = t.id_term
                    )
            ) x
            GROUP BY target_status, invoice_date", [$idPo, $idPo])->result_array();

        $metrics = [
            'all_invoice' => 0,
            'invoice_2026' => 0,
            'outs_2026' => 0,
            'co_2027' => 0
        ];

        foreach ($baseRows as $row) {
            $status = strtoupper(trim((string) ($row['target_status'] ?? '')));
            $amount = (float) ($row['amount'] ?? 0);
            $invoiceDate = $this->normalizeSyncDate($row['invoice_date'] ?? null);

            if ($status === 'TARGET_WEEK') {
                $metrics['outs_2026'] += $amount;
            } elseif ($status === 'CARRY_OVER') {
                $metrics['co_2027'] += $amount;
            }
        }

        $claimRows = $this->db->query("SELECT
                COALESCE(CONVERT(a.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci, CONVERT(t.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci) AS target_status,
                tc.invoice_date,
                SUM(tc.invoice_amount) AS amount
            FROM tb_po_term_claim tc
            JOIN tb_po_term t ON t.id_term = tc.id_term
            LEFT JOIN tb_po_term_allocation a ON a.id_allocation = tc.id_allocation
            WHERE t.id_po = ?
            GROUP BY target_status, tc.invoice_date", [$idPo])->result_array();

        foreach ($claimRows as $row) {
            $status = strtoupper(trim((string) ($row['target_status'] ?? '')));
            $amount = (float) ($row['amount'] ?? 0);
            $invoiceDate = $this->normalizeSyncDate($row['invoice_date'] ?? null);

            $metrics['all_invoice'] += $amount;
            if ($invoiceDate !== null && (int) date('Y', strtotime($invoiceDate)) === 2026) {
                $metrics['invoice_2026'] += $amount;
                if ($status === 'TARGET_WEEK') {
                    $metrics['outs_2026'] -= $amount;
                } elseif ($status === 'CARRY_OVER') {
                    $metrics['co_2027'] -= $amount;
                }
            }
        }

        $this->db->where('id_po', $idPo)->update('tb_po', [
            'dashboard_all_invoice' => max($metrics['all_invoice'], 0),
            'dashboard_invoice_2026' => max($metrics['invoice_2026'], 0),
            'dashboard_outs_2026' => max($metrics['outs_2026'], 0),
            'dashboard_co_2027' => max($metrics['co_2027'], 0)
        ]);
    }

    private function refreshPoMonitorTermInvoiceDate($idTerm)
    {
        $latest = $this->db
            ->select('MAX(invoice_date) AS invoice_date')
            ->from('tb_po_term_claim')
            ->where('id_term', (int) $idTerm)
            ->get()
            ->row_array();
        $invoiceDate = !empty($latest['invoice_date']) ? $latest['invoice_date'] : null;

        $this->db->where('id_term', (int) $idTerm)->update('tb_po_term', [
            'invoice_date' => $invoiceDate,
            'submit_raw' => $invoiceDate
        ]);
    }

    private function normalizePoMonitorSyncPoNumber($value)
    {
        $value = strtoupper(trim((string) $value));
        $value = preg_replace('/^PO[\s\.\-:]*/', '', $value);
        return preg_replace('/[^A-Z0-9]/', '', $value);
    }

    private function normalizeSyncDate($date)
    {
        if (empty($date)) {
            return null;
        }

        $legacyDate = $this->normalizeLegacyJulyImportDate($date);
        if ($legacyDate !== null) {
            return $legacyDate;
        }

        $timestamp = strtotime((string) $date);
        if (!$timestamp) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    public function createBatchPo(array $rows, $userId)
    {
        $summary = [
            'inserted' => 0,
            'skipped' => 0,
            'replaced' => 0,
            'terms' => 0,
            'allocations' => 0,
            'errors' => []
        ];

        if (empty($rows)) {
            return ['status' => false, 'message' => 'Tidak ada data PO.', 'summary' => $summary];
        }

        $groups = $this->buildBatchPoGroups($rows, $summary);
        if (empty($groups)) {
            return ['status' => false, 'message' => 'Tidak ada data PO valid.', 'summary' => $summary];
        }

        $this->db->trans_begin();

        foreach ($groups as $groupIndex => $group) {
            $row = $group['base'];
            $poNumber = $group['po_number'];
            $bowheer = $group['bowheer'];
            $poDate = $group['po_date'];
            $poValue = $group['po_value'];
            $poFinalValue = $group['po_final_value'];
            $effectiveValue = $group['effective_value'];
            $statusPo = $group['status_po'];
            $idBowheer = $this->resolveBowheerId($bowheer);
            $sourceHash = hash('sha256', implode('|', [
                'MANUAL_BATCH_PO',
                $poNumber,
                $idBowheer,
                $poDate,
                number_format($poValue, 2, '.', ''),
                number_format($poFinalValue, 2, '.', ''),
                $group['allocation_hash']
            ]));

            if ($statusPo === 'NY PO') {
                $nyResult = $this->insertManualBatchNyPoPipeline($group, $idBowheer, $bowheer, $userId);
                if (empty($nyResult['status'])) {
                    $summary['skipped']++;
                    $summary['errors'][] = 'NY PO row ' . (int) ($group['first_row_no'] ?? ($groupIndex + 1)) . ': ' . ($nyResult['message'] ?? 'gagal disimpan.');
                    continue;
                }

                $summary['inserted']++;
                continue;
            }

            $pipelineMatch = $this->resolveNyPoPipelineForBatchGroup($group);
            if (!empty($pipelineMatch['error'])) {
                $summary['skipped']++;
                $summary['errors'][] = 'PO ' . $poNumber . ': ' . $pipelineMatch['error'];
                continue;
            }

            $linkedPipeline = !empty($pipelineMatch['row']) ? $pipelineMatch['row'] : null;
            $linkedPipelines = [];
            if ($linkedPipeline) {
                $linkedPipelines = $this->getNyPoReferencePipelineGroup($linkedPipeline);
                if (empty($linkedPipelines)) {
                    $linkedPipelines = [$linkedPipeline];
                }
            }
            $linkedPipelineByTerm = [];
            foreach ($linkedPipelines as $pipelineRow) {
                $pipelineTermIndex = (int) ($pipelineRow['term_index'] ?? 0);
                if ($pipelineTermIndex > 0 && !isset($linkedPipelineByTerm[$pipelineTermIndex])) {
                    $linkedPipelineByTerm[$pipelineTermIndex] = $pipelineRow;
                }
            }
            $autoTarget2026 = empty($linkedPipelines);
            $existingPo = $this->getPoByNumberInsensitive($poNumber);
            if ($existingPo) {
                if (!empty($linkedPipelines) && trim((string) ($group['ny_po_ref'] ?? '')) !== '') {
                    foreach ($linkedPipelines as $pipelineRow) {
                        $replace = $this->replaceNyPoPipelineLink($pipelineRow, (int) $existingPo['id_po'], $poNumber, $group, $userId);
                        if (empty($replace['status'])) {
                            $summary['skipped']++;
                            $summary['errors'][] = 'PO ' . $poNumber . ': ' . ($replace['message'] ?? 'gagal replace NY PO REF.');
                            continue 2;
                        }
                    }

                    $summary['replaced']++;
                    continue;
                }

                $summary['skipped']++;
                $summary['errors'][] = 'PO ' . $poNumber . ' sudah ada di PO Monitor.';
                continue;
            }

            $existing = $this->db->get_where('tb_po', ['source_hash' => $sourceHash])->row_array();
            if ($existing) {
                $summary['skipped']++;
                $summary['errors'][] = 'PO ' . $poNumber . ' sudah pernah diproses.';
                continue;
            }

            $this->db->insert('tb_po', [
                'po_number' => $poNumber,
                'po_date' => $poDate,
                'id_bowheer' => $idBowheer,
                'total_value' => $poValue > 0 ? $poValue : $effectiveValue,
                'status_po' => $statusPo,
                'dashboard_bowheer' => $bowheer,
                'type_project' => trim((string) ($row['type_project'] ?? '')),
                'dashboard_all_invoice' => 0,
                'dashboard_invoice_2026' => 0,
                'dashboard_outs_2026' => $autoTarget2026 ? $effectiveValue : 0,
                'dashboard_co_2027' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $userId ?: null,
                'notes' => 'Batch manual tambah PO',
                'source_file' => 'BATCH_MANUAL_PO',
                'source_row_no' => $group['first_row_no'],
                'source_hash' => $sourceHash
            ]);
            $idPo = (int) $this->db->insert_id();

            $this->db->insert('tb_po_amend', [
                'id_po' => $idPo,
                'amend_no' => 1,
                'release_value' => $effectiveValue,
                'release_date' => $poDate,
                'notes' => $poFinalValue > 0 ? 'Batch manual from PO FINAL VALUE' : 'Batch manual initial value'
            ]);
            $idAmend = (int) $this->db->insert_id();

            $splits = $this->buildTermSplitsFromPoTerm((string) ($row['po_term'] ?? ''), $effectiveValue);

            foreach ($splits as $split) {
                $termPipeline = $linkedPipelineByTerm[(int) $split['term_index']] ?? null;
                $termIsLinked = !empty($termPipeline);
                $targetPayload = $termIsLinked ? [
                    'plan_amount' => (float) $split['value'],
                    'target_status' => 'TARGET_WEEK',
                    'target_year' => (int) ($termPipeline['target_year'] ?? 0) ?: null,
                    'target_week' => (int) ($termPipeline['target_week'] ?? 0) ?: null,
                    'target_week_start' => $termPipeline['target_week_start'] ?? null,
                    'target_week_end' => $termPipeline['target_week_end'] ?? null,
                    'submit_raw' => $termPipeline['submit_raw'] ?? null
                ] : [
                    'plan_amount' => (float) $split['value'],
                    'target_status' => $autoTarget2026 ? 'TARGET_WEEK' : 'OPEN',
                    'target_year' => $autoTarget2026 ? 2026 : null
                ];

                $this->db->insert('tb_po_term', [
                    'id_po' => $idPo,
                    'id_amend' => $idAmend,
                    'term_index' => (int) $split['term_index'],
                    'percent' => (float) $split['percent'],
                    'value' => (float) $split['value']
                ] + $targetPayload);
                $idTerm = (int) $this->db->insert_id();
                $summary['terms']++;

                if ($group['has_allocations']) {
                    $remainingAllocationValue = (float) $split['value'];
                    $allocationCount = count($group['allocations']);
                    foreach ($group['allocations'] as $allocationIndex => $allocation) {
                        if ($allocationIndex === $allocationCount - 1) {
                            $allocationValue = $remainingAllocationValue;
                        } else {
                            $allocationValue = $effectiveValue > 0 ? round(((float) $split['value'] * (float) $allocation['effective_value']) / $effectiveValue, 2) : 0;
                            $remainingAllocationValue -= $allocationValue;
                        }

                        $allocationIsLinked = $termIsLinked
                            && ($this->batchAllocationMatchesPipeline($allocation, $termPipeline, $allocationValue)
                                || (trim((string) ($group['ny_po_ref'] ?? '')) !== '' && $allocationIndex === 0));
                        $allocationIsAutoTarget2026 = $autoTarget2026 && !$allocationIsLinked;

                        $this->db->insert('tb_po_term_allocation', [
                            'id_term' => $idTerm,
                            'no_po_sub' => $allocation['no_po_sub'],
                            'regional' => $allocation['regional'],
                            'kota_po' => $allocation['kota_po'],
                            'detail_po' => $allocation['detail_po'],
                            'remarks' => $allocation['remarks'],
                            'allocation_value' => $allocationValue,
                            'plan_amount' => $allocationValue,
                            'target_status' => ($allocationIsLinked || $allocationIsAutoTarget2026) ? 'TARGET_WEEK' : 'OPEN',
                            'target_year' => $allocationIsLinked ? ((int) ($termPipeline['target_year'] ?? 0) ?: null) : ($allocationIsAutoTarget2026 ? 2026 : null),
                            'target_week' => $allocationIsLinked ? ((int) ($termPipeline['target_week'] ?? 0) ?: null) : null,
                            'target_week_start' => $allocationIsLinked ? ($termPipeline['target_week_start'] ?? null) : null,
                            'target_week_end' => $allocationIsLinked ? ($termPipeline['target_week_end'] ?? null) : null,
                            'submit_raw' => $allocationIsLinked ? ($termPipeline['submit_raw'] ?? null) : null,
                            'source_row_no' => $allocation['row_no']
                        ]);
                        $summary['allocations']++;
                    }
                }
            }

            if (!empty($linkedPipelines)) {
                foreach ($linkedPipelines as $pipelineRow) {
                    $this->replaceNyPoPipelineLink($pipelineRow, $idPo, $poNumber, $group, $userId, false);
                }
            }

            $summary['inserted']++;
        }

        $this->rebuildDashboardCache(null);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Gagal menyimpan batch PO.', 'summary' => $summary];
        }

        $this->db->trans_commit();
        return ['status' => $summary['inserted'] > 0 || $summary['replaced'] > 0, 'message' => 'Batch PO selesai.', 'summary' => $summary];
    }

    private function buildBatchPoGroups(array $rows, array &$summary)
    {
        $groups = [];

        foreach ($rows as $index => $row) {
            $poNumber = trim((string) ($row['po_number'] ?? ''));
            $bowheer = $this->resolveImportBowheerName((string) ($row['bowheer'] ?? ''), (string) ($row['type_project'] ?? ''));
            $poDate = $this->parseDate((string) ($row['po_date'] ?? ''));
            $poValue = $this->normalizeAmountLocal($row['po_value'] ?? 0);
            $poFinalValue = $this->normalizeAmountLocal($row['po_final_value'] ?? 0);
            $effectiveValue = $poFinalValue > 0 ? $poFinalValue : $poValue;
            $statusPo = strtoupper(trim((string) ($row['status_po'] ?? 'ON PO'))) ?: 'ON PO';

            if ($bowheer === '' || $effectiveValue <= 0 || ($statusPo !== 'NY PO' && $poNumber === '')) {
                $summary['skipped']++;
                $summary['errors'][] = 'Row ' . ($index + 1) . ($statusPo === 'NY PO' ? ' wajib isi BOWHEER dan nilai PO.' : ' wajib isi NO PO, BOWHEER, dan nilai PO.');
                continue;
            }

            $groupKey = hash('sha256', implode('|', [
                $bowheer,
                $poNumber !== '' ? $poNumber : ('NY_PO_ROW_' . ($index + 1)),
                $poDate,
                trim((string) ($row['po_term'] ?? '')),
                $statusPo
            ]));

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'base' => $row,
                    'po_number' => $poNumber,
                    'bowheer' => $bowheer,
                    'po_date' => $poDate,
                    'status_po' => $statusPo,
                    'po_value' => 0,
                    'po_final_value' => 0,
                    'effective_value' => 0,
                    'has_allocations' => false,
                    'allocations' => [],
                    'first_row_no' => $index + 1,
                    'ny_po_ref' => trim((string) ($row['ny_po_ref'] ?? '')),
                    'type_projects' => [],
                    'allocation_hash' => ''
                ];
            }

            if ($groups[$groupKey]['ny_po_ref'] === '' && trim((string) ($row['ny_po_ref'] ?? '')) !== '') {
                $groups[$groupKey]['ny_po_ref'] = trim((string) $row['ny_po_ref']);
            }

            $groups[$groupKey]['po_value'] += $poValue;
            $groups[$groupKey]['po_final_value'] += $poFinalValue;
            $groups[$groupKey]['effective_value'] += $effectiveValue;
            $typeProject = trim((string) ($row['type_project'] ?? ''));
            if ($typeProject !== '') {
                $groups[$groupKey]['type_projects'][$typeProject] = $typeProject;
            }

            $hasAllocation = trim((string) ($row['no_po_sub'] ?? '')) !== ''
                || trim((string) ($row['regional'] ?? '')) !== ''
                || trim((string) ($row['kota_po'] ?? '')) !== ''
                || trim((string) ($row['detail_po'] ?? '')) !== ''
                || trim((string) ($row['remarks'] ?? '')) !== '';
            if ($hasAllocation) {
                $groups[$groupKey]['has_allocations'] = true;
                $groups[$groupKey]['allocations'][] = [
                    'no_po_sub' => trim((string) ($row['no_po_sub'] ?? '')),
                    'regional' => trim((string) ($row['regional'] ?? '')),
                    'kota_po' => trim((string) ($row['kota_po'] ?? '')),
                    'detail_po' => trim((string) ($row['detail_po'] ?? '')),
                    'remarks' => trim((string) ($row['remarks'] ?? '')),
                    'effective_value' => $effectiveValue,
                    'row_no' => $index + 1
                ];
            }
        }

        foreach ($groups as &$group) {
            $allocationHashParts = [];
            foreach ($group['allocations'] as $allocation) {
                $allocationHashParts[] = implode('|', [
                    $allocation['no_po_sub'],
                    $allocation['regional'],
                    $allocation['kota_po'],
                    $allocation['detail_po'],
                    $allocation['remarks'],
                    number_format((float) $allocation['effective_value'], 2, '.', '')
                ]);
            }
            $group['allocation_hash'] = hash('sha256', implode('~', $allocationHashParts));
            if (!empty($group['type_projects'])) {
                $group['base']['type_project'] = implode(', ', array_values($group['type_projects']));
            }
        }
        unset($group);

        return array_values($groups);
    }

    private function insertManualBatchNyPoPipeline(array $group, $idBowheer, $dashboardBowheer, $userId = 0)
    {
        $effectiveValue = (float) ($group['effective_value'] ?? 0);
        if ((int) $idBowheer <= 0 || trim((string) $dashboardBowheer) === '' || $effectiveValue <= 0) {
            return ['status' => false, 'message' => 'BOWHEER dan nilai PO wajib valid.'];
        }

        $row = (array) ($group['base'] ?? []);
        $splits = $this->buildTermSplitsFromPoTerm((string) ($row['po_term'] ?? ''), $effectiveValue);
        $sourceRowNo = (int) ($group['first_row_no'] ?? 0);
        if ($sourceRowNo <= 0) {
            $sourceRowNo = time();
        }

        $typeProject = trim((string) ($row['type_project'] ?? ''));
        $regional = '';
        $kotaPo = '';
        $detailPo = '';
        $remarks = '';
        if (!empty($group['allocations'][0])) {
            $regional = trim((string) ($group['allocations'][0]['regional'] ?? ''));
            $kotaPo = trim((string) ($group['allocations'][0]['kota_po'] ?? ''));
            $detailPo = trim((string) ($group['allocations'][0]['detail_po'] ?? ''));
            $remarks = trim((string) ($group['allocations'][0]['remarks'] ?? ''));
        } else {
            $regional = trim((string) ($row['regional'] ?? ''));
            $kotaPo = trim((string) ($row['kota_po'] ?? ''));
            $detailPo = trim((string) ($row['detail_po'] ?? ''));
            $remarks = trim((string) ($row['remarks'] ?? ''));
        }

        $poTerm = trim((string) ($row['po_term'] ?? ''));
        if ($poTerm === '') {
            $poTerm = implode(':', array_map(function ($split) {
                $percent = (float) ($split['percent'] ?? 0);
                return rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.');
            }, $splits));
        }

        $inserted = 0;
        foreach ($splits as $split) {
            $termIndex = (int) ($split['term_index'] ?? 0);
            if ($termIndex <= 0) {
                continue;
            }

            $planAmount = (float) ($split['value'] ?? 0);
            if ($planAmount <= 0) {
                continue;
            }

            $sourceHash = hash('sha256', implode('|', [
                'MANUAL_BATCH_NY_PO',
                $sourceRowNo,
                $dashboardBowheer,
                $termIndex,
                number_format($planAmount, 2, '.', ''),
                $typeProject,
                $regional,
                $kotaPo,
                $detailPo,
                $remarks
            ]));

            $this->db->insert('tb_po_target_pipeline', [
                'id_bowheer' => (int) $idBowheer,
                'dashboard_bowheer' => $dashboardBowheer,
                'status_po' => 'NY PO',
                'regional' => $regional !== '' ? $regional : null,
                'kota_po' => $kotaPo !== '' ? $kotaPo : null,
                'detail_po' => $detailPo !== '' ? $detailPo : null,
                'remarks' => $remarks !== '' ? $remarks : null,
                'type_project' => $typeProject !== '' ? $typeProject : null,
                'po_date' => null,
                'po_term' => $poTerm,
                'term_index' => $termIndex,
                'plan_amount' => $planAmount,
                'submit_raw' => '',
                'target_year' => 2026,
                'target_week' => null,
                'target_week_start' => null,
                'target_week_end' => null,
                'target_status' => 'TARGET_WEEK',
                'ny_po_2026_amount' => $planAmount,
                'ny_po_2027_amount' => 0,
                'source_file' => 'MANUAL_BATCH_NY_PO',
                'source_row_no' => $sourceRowNo,
                'source_hash' => $sourceHash,
                'pipeline_status' => 'OPEN',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $inserted++;
        }

        return ['status' => $inserted > 0, 'inserted' => $inserted, 'message' => $inserted > 0 ? 'NY PO berhasil disimpan.' : 'Tidak ada nilai term NY PO.'];
    }

    private function poNumberExists($poNumber)
    {
        return !empty($this->getPoByNumberInsensitive($poNumber));
    }

    private function getPoByNumberInsensitive($poNumber)
    {
        $poNumber = trim((string) $poNumber);
        if ($poNumber === '') {
            return null;
        }

        return $this->db
            ->from('tb_po')
            ->where("CONVERT(po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape($poNumber) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci", null, false)
            ->order_by('id_po', 'ASC')
            ->limit(1)
            ->get()
            ->row_array();
    }

    private function resolveNyPoPipelineForBatchGroup(array $group)
    {
        $ref = trim((string) ($group['ny_po_ref'] ?? ''));
        if ($ref !== '') {
            $idPipeline = $this->parseNyPoReferenceId($ref);
            if ($idPipeline <= 0) {
                return ['row' => null, 'error' => 'NY PO REF tidak valid. Gunakan format NY-123.'];
            }

            $row = $this->db
                ->where('id_pipeline', $idPipeline)
                ->get('tb_po_target_pipeline')
                ->row_array();

            if (!$row) {
                return ['row' => null, 'error' => 'NY PO REF ' . $ref . ' tidak ditemukan.'];
            }

            return ['row' => $row, 'error' => null];
        }

        $candidates = $this->findAutoNyPoPipelineCandidates($group);
        if (count($candidates) > 1) {
            return ['row' => null, 'error' => 'ada lebih dari 1 kandidat NY PO. Isi kolom NY PO REF dari download reference.'];
        }

        return ['row' => $candidates[0] ?? null, 'error' => null];
    }

    public function linkNyPoReferenceToPo($nyPoRef, $idPo, $poNumber, $userId = 0)
    {
        $this->ensureStandaloneSchema();
        $nyPoRef = trim((string) $nyPoRef);
        $idPo = (int) $idPo;
        $poNumber = trim((string) $poNumber);
        if ($nyPoRef === '' || $idPo <= 0) {
            return ['status' => false, 'message' => 'NY PO REF atau PO tujuan tidak valid.'];
        }

        $idPipeline = $this->parseNyPoReferenceId($nyPoRef);
        if ($idPipeline <= 0) {
            return ['status' => false, 'message' => 'NY PO REF tidak valid. Gunakan format NY-123.'];
        }

        $pipeline = $this->db
            ->where('id_pipeline', $idPipeline)
            ->get('tb_po_target_pipeline')
            ->row_array();
        if (!$pipeline) {
            return ['status' => false, 'message' => 'NY PO REF ' . $nyPoRef . ' tidak ditemukan.'];
        }

        $pipelines = $this->getNyPoReferencePipelineGroup($pipeline);
        if (empty($pipelines)) {
            $pipelines = [$pipeline];
        }

        $this->db->trans_begin();
        foreach ($pipelines as $pipelineRow) {
            $result = $this->replaceNyPoPipelineLink($pipelineRow, $idPo, $poNumber, [], $userId, true);
            if (empty($result['status'])) {
                $this->db->trans_rollback();
                return $result;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'NY PO REF gagal diproses.'];
        }

        $this->db->trans_commit();
        return ['status' => true];
    }

    public function unlinkNyPoReferenceFromPo($nyPoRef, $idPo = 0)
    {
        $this->ensureStandaloneSchema();
        $nyPoRef = trim((string) $nyPoRef);
        $idPipeline = $this->parseNyPoReferenceId($nyPoRef);
        if ($idPipeline <= 0) {
            return false;
        }

        $pipeline = $this->db
            ->where('id_pipeline', $idPipeline)
            ->get('tb_po_target_pipeline')
            ->row_array();
        if (!$pipeline) {
            return false;
        }

        $linkedIdPo = (int) ($pipeline['linked_id_po'] ?? 0);
        $idPo = (int) $idPo;
        if ($idPo > 0 && $linkedIdPo > 0 && $linkedIdPo !== $idPo) {
            return false;
        }

        $pipelines = $this->getNyPoReferencePipelineGroup($pipeline);
        if (empty($pipelines)) {
            $pipelines = [$pipeline];
        }

        foreach ($pipelines as $pipelineRow) {
            $pipelineLinkedIdPo = (int) ($pipelineRow['linked_id_po'] ?? 0);
            if ($pipelineLinkedIdPo > 0) {
                $this->clearNyPipelineTargetFromPo($pipelineLinkedIdPo, $pipelineRow);
            }

            $this->db
                ->where('id_pipeline', (int) $pipelineRow['id_pipeline'])
                ->update('tb_po_target_pipeline', [
                    'linked_id_po' => null,
                    'linked_po_number' => null,
                    'pipeline_status' => 'OPEN',
                    'converted_at' => null,
                    'converted_by' => null
                ]);
        }

        if ($linkedIdPo > 0) {
            $this->refreshPoDashboardMetrics($linkedIdPo);
        }

        return true;
    }

    public function linkNyPoReferenceToMyRepHeader($poHeaderId, $nyPoRef, $userId = 0)
    {
        $poHeaderId = (int) $poHeaderId;
        $nyPoRef = trim((string) $nyPoRef);
        if ($poHeaderId <= 0 || $nyPoRef === '') {
            return ['status' => false, 'message' => 'PO MyRep atau NY PO REF kosong.'];
        }

        $ensure = $this->ensurePoMonitorFromMyRepPoHeader($poHeaderId, $userId);
        if (empty($ensure['status']) || empty($ensure['id_po'])) {
            return ['status' => false, 'message' => $ensure['message'] ?? 'PO Monitor belum berhasil dibuat.'];
        }

        $poNumber = '';
        if ($this->db->table_exists('tb_myrep_po_header')) {
            $header = $this->db
                ->select('po_number')
                ->where('id_po_header', $poHeaderId)
                ->get('tb_myrep_po_header')
                ->row_array();
            $poNumber = trim((string) ($header['po_number'] ?? ''));
        }
        if ($poNumber === '') {
            $po = $this->db->select('po_number')->where('id_po', (int) $ensure['id_po'])->get('tb_po')->row_array();
            $poNumber = trim((string) ($po['po_number'] ?? ''));
        }

        return $this->linkNyPoReferenceToPo($nyPoRef, (int) $ensure['id_po'], $poNumber, $userId);
    }

    public function backfillNyPoReferenceGroupLinks($userId = 0)
    {
        $this->ensureStandaloneSchema();
        $summary = [
            'myrep_headers_checked' => 0,
            'myrep_headers_linked' => 0,
            'po_monitor_groups_checked' => 0,
            'po_monitor_groups_linked' => 0,
            'pipeline_rows_linked' => 0,
            'errors' => [],
        ];

        if ($this->db->table_exists('tb_myrep_po_header') && $this->db->field_exists('po_monitor_ny_ref', 'tb_myrep_po_header')) {
            $headers = $this->db
                ->select('id_po_header, po_monitor_ny_ref')
                ->from('tb_myrep_po_header')
                ->where("COALESCE(po_monitor_ny_ref, '') !=", '')
                ->order_by('id_po_header', 'ASC')
                ->get()
                ->result_array();

            foreach ($headers as $header) {
                $summary['myrep_headers_checked']++;
                $result = $this->linkNyPoReferenceToMyRepHeader(
                    (int) ($header['id_po_header'] ?? 0),
                    (string) ($header['po_monitor_ny_ref'] ?? ''),
                    (int) $userId
                );
                if (!empty($result['status'])) {
                    $summary['myrep_headers_linked']++;
                } else {
                    $summary['errors'][] = 'Header #' . (int) ($header['id_po_header'] ?? 0) . ': ' . ($result['message'] ?? 'gagal relink.');
                }
            }
        }

        $convertedRows = $this->db
            ->from('tb_po_target_pipeline')
            ->where('COALESCE(linked_id_po, 0) > 0', null, false)
            ->order_by('id_pipeline', 'ASC')
            ->get()
            ->result_array();

        $groups = [];
        foreach ($convertedRows as $row) {
            $groupKey = $this->buildNyPoReferenceGroupKey($row);
            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = $row;
            }
        }

        foreach ($groups as $pipeline) {
            $summary['po_monitor_groups_checked']++;
            $linkedIdPo = (int) ($pipeline['linked_id_po'] ?? 0);
            $linkedPoNumber = trim((string) ($pipeline['linked_po_number'] ?? ''));
            if ($linkedIdPo <= 0) {
                continue;
            }
            if ($linkedPoNumber === '') {
                $poRow = $this->db
                    ->select('po_number')
                    ->where('id_po', $linkedIdPo)
                    ->get('tb_po')
                    ->row_array();
                $linkedPoNumber = trim((string) ($poRow['po_number'] ?? ''));
            }

            $groupRows = $this->getNyPoReferencePipelineGroup($pipeline);
            $result = $this->linkNyPoReferenceToPo(
                'NY-' . (int) ($pipeline['id_pipeline'] ?? 0),
                $linkedIdPo,
                $linkedPoNumber,
                (int) $userId
            );
            if (!empty($result['status'])) {
                $summary['po_monitor_groups_linked']++;
                $summary['pipeline_rows_linked'] += count($groupRows);
            } else {
                $summary['errors'][] = 'NY-' . (int) ($pipeline['id_pipeline'] ?? 0) . ': ' . ($result['message'] ?? 'gagal relink.');
            }
        }

        return $summary;
    }

    private function replaceNyPoPipelineLink(array $pipeline, $idPo, $poNumber, array $group, $userId, $applyTarget = true)
    {
        $idPo = (int) $idPo;
        $oldIdPo = (int) ($pipeline['linked_id_po'] ?? 0);
        if ($idPo <= 0) {
            return ['status' => false, 'message' => 'PO tujuan tidak valid.'];
        }

        if ($oldIdPo > 0 && $oldIdPo !== $idPo) {
            $this->clearNyPipelineTargetFromPo($oldIdPo, $pipeline);
        }

        if ($applyTarget && !$this->applyNyPipelineTargetToPo($idPo, $pipeline, $group)) {
            return ['status' => false, 'message' => 'Term tujuan untuk NY PO REF tidak ditemukan.'];
        }

        $this->db
            ->where('id_pipeline', (int) $pipeline['id_pipeline'])
            ->update('tb_po_target_pipeline', [
                'linked_id_po' => $idPo,
                'linked_po_number' => $poNumber,
                'pipeline_status' => 'CONVERTED',
                'converted_at' => date('Y-m-d H:i:s'),
                'converted_by' => $userId ?: null
            ]);

        if ($oldIdPo > 0 && $oldIdPo !== $idPo) {
            $this->refreshPoDashboardMetrics($oldIdPo);
        }
        $this->refreshPoDashboardMetrics($idPo);

        return ['status' => true];
    }

    private function getNyPoReferencePipelineGroup(array $pipeline)
    {
        $this->ensureStandaloneSchema();
        $this->db
            ->from('tb_po_target_pipeline')
            ->where('COALESCE(id_bowheer, 0) = ' . (int) ($pipeline['id_bowheer'] ?? 0), null, false)
            ->where("CONVERT(COALESCE(dashboard_bowheer, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape((string) ($pipeline['dashboard_bowheer'] ?? '')) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci", null, false)
            ->where("CONVERT(COALESCE(type_project, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape((string) ($pipeline['type_project'] ?? '')) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci", null, false)
            ->where("CONVERT(COALESCE(regional, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape((string) ($pipeline['regional'] ?? '')) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci", null, false)
            ->where("CONVERT(COALESCE(kota_po, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape((string) ($pipeline['kota_po'] ?? '')) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci", null, false)
            ->where("CONVERT(COALESCE(detail_po, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape((string) ($pipeline['detail_po'] ?? '')) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci", null, false)
            ->where("CONVERT(COALESCE(remarks, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape((string) ($pipeline['remarks'] ?? '')) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci", null, false);

        return $this->db
            ->order_by('term_index', 'ASC')
            ->order_by('id_pipeline', 'ASC')
            ->get()
            ->result_array();
    }

    private function buildNyPoReferenceGroupKey(array $row)
    {
        $parts = [
            (string) ($row['id_bowheer'] ?? ''),
            $this->normalizeNyPoMatchText((string) ($row['bowheer'] ?? $row['dashboard_bowheer'] ?? '')),
            $this->normalizeNyPoMatchText((string) ($row['type_project'] ?? '')),
            $this->normalizeNyPoMatchText((string) ($row['regional'] ?? '')),
            $this->normalizeNyPoMatchText((string) ($row['kota_po'] ?? '')),
            $this->normalizeNyPoMatchText((string) ($row['detail_po'] ?? '')),
            $this->normalizeNyPoMatchText((string) ($row['remarks'] ?? '')),
        ];

        return hash('sha256', implode('|', $parts));
    }

    private function clearNyPipelineTargetFromPo($idPo, array $pipeline)
    {
        $termIndex = (int) ($pipeline['term_index'] ?? 0);
        if ($idPo <= 0 || $termIndex <= 0) {
            return;
        }

        $start = $pipeline['target_week_start'] ?? null;
        $end = $pipeline['target_week_end'] ?? null;

        $this->db->query("UPDATE tb_po_term_allocation a
            JOIN tb_po_term t ON t.id_term = a.id_term
            SET a.target_status = 'OPEN',
                a.target_year = NULL,
                a.target_week = NULL,
                a.target_week_start = NULL,
                a.target_week_end = NULL,
                a.submit_raw = NULL
            WHERE t.id_po = ?
                AND t.term_index = ?
                AND CONVERT(a.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                AND ((a.target_week_start <=> ?) AND (a.target_week_end <=> ?))", [$idPo, $termIndex, $start, $end]);

        $this->db->query("UPDATE tb_po_term
            SET target_status = 'OPEN',
                target_year = NULL,
                target_week = NULL,
                target_week_start = NULL,
                target_week_end = NULL,
                submit_raw = NULL
            WHERE id_po = ?
                AND term_index = ?
                AND CONVERT(target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                AND ((target_week_start <=> ?) AND (target_week_end <=> ?))", [$idPo, $termIndex, $start, $end]);
    }

    private function applyNyPipelineTargetToPo($idPo, array $pipeline, array $group)
    {
        $termIndex = (int) ($pipeline['term_index'] ?? 0);
        if ($idPo <= 0 || $termIndex <= 0) {
            return false;
        }

        $term = $this->db
            ->where('id_po', (int) $idPo)
            ->where('term_index', $termIndex)
            ->get('tb_po_term')
            ->row_array();
        if (!$term) {
            return false;
        }

        $payload = [
            'target_status' => 'TARGET_WEEK',
            'target_year' => (int) ($pipeline['target_year'] ?? 0) ?: null,
            'target_week' => (int) ($pipeline['target_week'] ?? 0) ?: null,
            'target_week_start' => $pipeline['target_week_start'] ?? null,
            'target_week_end' => $pipeline['target_week_end'] ?? null,
            'submit_raw' => $pipeline['submit_raw'] ?? null
        ];

        $allocations = $this->db
            ->where('id_term', (int) $term['id_term'])
            ->order_by('source_row_no', 'ASC')
            ->order_by('id_allocation', 'ASC')
            ->get('tb_po_term_allocation')
            ->result_array();

        if (!empty($allocations)) {
            $targetAllocation = $this->chooseNyPipelineTargetAllocation($allocations, $pipeline, $group);
            if (!$targetAllocation) {
                return false;
            }

            $this->db->where('id_allocation', (int) $targetAllocation['id_allocation'])->update('tb_po_term_allocation', $payload);
        }

        $this->db->where('id_term', (int) $term['id_term'])->update('tb_po_term', $payload);
        return true;
    }

    private function chooseNyPipelineTargetAllocation(array $allocations, array $pipeline, array $group)
    {
        $groupAllocation = !empty($group['allocations']) ? (array) $group['allocations'][0] : [];
        foreach ($allocations as $allocation) {
            if ($this->batchAllocationMatchesPipeline($allocation, $pipeline, (float) ($allocation['allocation_value'] ?? 0))) {
                return $allocation;
            }
        }

        if (!empty($groupAllocation)) {
            foreach ($allocations as $allocation) {
                if ($this->normalizeNyPoMatchText((string) ($allocation['regional'] ?? '')) === $this->normalizeNyPoMatchText((string) ($groupAllocation['regional'] ?? ''))
                    && $this->normalizeNyPoMatchText((string) ($allocation['kota_po'] ?? '')) === $this->normalizeNyPoMatchText((string) ($groupAllocation['kota_po'] ?? ''))
                    && $this->normalizeNyPoMatchText((string) ($allocation['detail_po'] ?? '')) === $this->normalizeNyPoMatchText((string) ($groupAllocation['detail_po'] ?? ''))) {
                    return $allocation;
                }
            }
        }

        return count($allocations) === 1 ? $allocations[0] : null;
    }

    private function parseNyPoReferenceId($ref)
    {
        if (preg_match('/(\d+)/', strtoupper(trim((string) $ref)), $match)) {
            return (int) $match[1];
        }

        return 0;
    }

    private function findAutoNyPoPipelineCandidates(array $group)
    {
        $base = (array) ($group['base'] ?? []);
        $splits = $this->buildTermSplitsFromPoTerm((string) ($base['po_term'] ?? ''), (float) ($group['effective_value'] ?? 0));
        $termAmounts = [];
        foreach ($splits as $split) {
            $termAmounts[(int) $split['term_index']] = (float) $split['value'];
        }
        $allocation = !empty($group['allocations']) ? (array) $group['allocations'][0] : $base;
        $typeProject = (string) ($base['type_project'] ?? '');
        $regional = (string) ($allocation['regional'] ?? '');
        $kotaPo = (string) ($allocation['kota_po'] ?? '');

        $rows = $this->db->query("SELECT pl.*
            FROM tb_po_target_pipeline pl
            WHERE pl.linked_id_po IS NULL
                AND CONVERT(pl.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                AND CONVERT(pl.dashboard_bowheer USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape((string) ($group['bowheer'] ?? '')) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci
                AND CONVERT(COALESCE(pl.type_project, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape($typeProject) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci
                AND CONVERT(COALESCE(pl.regional, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape($regional) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci
                AND CONVERT(COALESCE(pl.kota_po, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(" . $this->db->escape($kotaPo) . " USING utf8mb4) COLLATE utf8mb4_unicode_ci")->result_array();

        $matches = [];
        foreach ($rows as $row) {
            $termIndex = (int) ($row['term_index'] ?? 0);
            $expectedAmount = $termAmounts[$termIndex] ?? 0;
            $pipelineAmount = (float) ($row['ny_po_2026_amount'] ?? 0);
            if ($pipelineAmount <= 0) {
                $pipelineAmount = (float) ($row['plan_amount'] ?? 0);
            }

            if ($termIndex <= 0 || $expectedAmount <= 0 || abs($pipelineAmount - $expectedAmount) > 1) {
                continue;
            }

            if ($this->normalizeNyPoMatchText((string) ($row['type_project'] ?? '')) !== $this->normalizeNyPoMatchText((string) ($base['type_project'] ?? ''))) {
                continue;
            }
            if ($this->normalizeNyPoMatchText((string) ($row['regional'] ?? '')) !== $this->normalizeNyPoMatchText((string) ($allocation['regional'] ?? ''))) {
                continue;
            }
            if ($this->normalizeNyPoMatchText((string) ($row['kota_po'] ?? '')) !== $this->normalizeNyPoMatchText((string) ($allocation['kota_po'] ?? ''))) {
                continue;
            }
            if ($this->normalizeNyPoMatchText((string) ($row['detail_po'] ?? '')) !== $this->normalizeNyPoMatchText((string) ($allocation['detail_po'] ?? ''))) {
                continue;
            }

            $matches[] = $row;
        }

        return $matches;
    }

    private function batchAllocationMatchesPipeline(array $allocation, $pipeline, $allocationValue)
    {
        if (!$pipeline) {
            return false;
        }

        $pipelineAmount = (float) ($pipeline['ny_po_2026_amount'] ?? 0);
        if ($pipelineAmount <= 0) {
            $pipelineAmount = (float) ($pipeline['plan_amount'] ?? 0);
        }

        return abs($pipelineAmount - (float) $allocationValue) <= 1
            && $this->normalizeNyPoMatchText((string) ($pipeline['regional'] ?? '')) === $this->normalizeNyPoMatchText((string) ($allocation['regional'] ?? ''))
            && $this->normalizeNyPoMatchText((string) ($pipeline['kota_po'] ?? '')) === $this->normalizeNyPoMatchText((string) ($allocation['kota_po'] ?? ''))
            && $this->normalizeNyPoMatchText((string) ($pipeline['detail_po'] ?? '')) === $this->normalizeNyPoMatchText((string) ($allocation['detail_po'] ?? ''));
    }

    private function normalizeNyPoMatchText($value)
    {
        $value = strtoupper(trim((string) $value));
        $value = preg_replace('/\s+/', ' ', $value);
        return $value === '-' ? '' : $value;
    }

    private function buildTermSplitsFromPoTerm($poTerm, $totalValue)
    {
        $percents = array_values($this->parseTermPercents($poTerm));
        $percents = array_values(array_filter($percents, static function ($percent) {
            return (float) $percent > 0;
        }));

        if (empty($percents)) {
            $percents = [100.0];
        }

        $percents = array_slice($percents, 0, 5);
        $sumPercent = array_sum($percents);
        if ($sumPercent <= 0) {
            $percents = [100.0];
            $sumPercent = 100.0;
        }

        $result = [];
        $remainingValue = (float) $totalValue;
        $count = count($percents);
        foreach ($percents as $idx => $percent) {
            $normalizedPercent = ((float) $percent / $sumPercent) * 100;
            if ($idx === $count - 1) {
                $value = $remainingValue;
            } else {
                $value = round(((float) $totalValue * $normalizedPercent) / 100, 2);
                $remainingValue -= $value;
            }

            $result[] = [
                'term_index' => $idx + 1,
                'percent' => round($normalizedPercent, 2),
                'value' => $value
            ];
        }

        return $result;
    }

    public function getImportReportHeaders()
    {
        $headers = [
            'NY PO REF', 'BOWHEER', 'STATUS PO', 'NO PO', 'NO PO SUB', 'REGIONAL', 'KOTA PO',
            'DETAIL PO', 'REMARKS', 'TYPE PROJECT', 'TGL PO', 'PO VALUE',
            'PO FINAL VALUE', 'PO TERM', 'OUTSTANDING TOTAL',
            'OUTSTANDING ON TARGET 2026', 'OUTSTANDING CO 2027', 'INVOICE ALL',
            'INVOICE 2026', 'NY PO 2026', 'NY PO 2027'
        ];

        for ($term = 1; $term <= 5; $term++) {
            $headers[] = 'PLAN ' . $term;
            $headers[] = 'SUBMIT ' . $term;
            $headers[] = 'NILAI ' . $term;
        }

        return $headers;
    }

    public function getImportReportRows()
    {
        $rows = array_merge($this->getOnPoImportReportRows(), $this->getNyPoImportReportRows());
        usort($rows, function ($a, $b) {
            $bowheerCompare = strcmp((string) ($a['BOWHEER'] ?? ''), (string) ($b['BOWHEER'] ?? ''));
            return $bowheerCompare !== 0 ? $bowheerCompare : strcmp((string) ($a['NO PO'] ?? ''), (string) ($b['NO PO'] ?? ''));
        });
        return $rows;
    }

    private function getEmptyImportReportRow()
    {
        $row = array_fill_keys($this->getImportReportHeaders(), '');
        $row['STATUS PO'] = 'ON PO';
        return $row;
    }

    private function getOnPoImportReportRows()
    {
        $rows = [];
        $rows = array_merge($rows, $this->getAllocatedOnPoImportReportRows());
        $rows = array_merge($rows, $this->getStandaloneOnPoImportReportRows());
        return $rows;
    }

    private function getAllocatedOnPoImportReportRows()
    {
        $queryRows = $this->db->query("SELECT
                p.id_po,
                COALESCE(NULLIF(p.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer') AS bowheer,
                COALESCE(p.status_po, 'ON PO') AS status_po,
                p.po_number,
                (SELECT GROUP_CONCAT(DISTINCT CONCAT('NY-', pl.id_pipeline) ORDER BY pl.id_pipeline SEPARATOR ', ')
                    FROM tb_po_target_pipeline pl
                    WHERE pl.linked_id_po = p.id_po) AS ny_po_ref,
                p.po_date,
                p.total_value,
                COALESCE((SELECT release_value FROM tb_po_amend am WHERE am.id_po = p.id_po ORDER BY am.amend_no DESC LIMIT 1), p.total_value) AS current_release_value,
                p.type_project,
                t.term_index,
                t.percent,
                a.no_po_sub,
                a.regional,
                a.kota_po,
                a.detail_po,
                a.remarks,
                a.source_row_no,
                COALESCE(NULLIF(a.plan_amount, 0), a.allocation_value) AS plan_amount,
                COALESCE(a.submit_raw, '') AS submit_raw,
                a.target_status,
                a.target_week,
                COALESCE(
                    (SELECT MAX(tc.invoice_date) FROM tb_po_term_claim tc WHERE tc.id_allocation = a.id_allocation),
                    (SELECT MAX(tc.invoice_date) FROM tb_po_term_claim tc WHERE tc.id_term = t.id_term AND tc.id_allocation IS NULL),
                    a.invoice_date,
                    t.invoice_date
                ) AS invoice_date,
                COALESCE(
                    (SELECT SUM(tc.invoice_amount) FROM tb_po_term_claim tc WHERE tc.id_allocation = a.id_allocation),
                    (SELECT SUM(tc.invoice_amount) FROM tb_po_term_claim tc WHERE tc.id_term = t.id_term AND tc.id_allocation IS NULL),
                    0
                ) AS invoice_amount
            FROM tb_po_term_allocation a
            JOIN tb_po_term t ON t.id_term = a.id_term
            JOIN tb_po p ON p.id_po = t.id_po
            LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = p.id_bowheer
            ORDER BY bowheer ASC, p.po_number ASC, a.source_row_no ASC, t.term_index ASC")->result_array();

        $groups = [];
        foreach ($queryRows as $item) {
            $groupKey = (int) $item['id_po'] . '|alloc|' . (
                $item['source_row_no'] !== null && $item['source_row_no'] !== ''
                    ? (string) $item['source_row_no']
                    : md5(implode('|', [(string) $item['no_po_sub'], (string) $item['regional'], (string) $item['kota_po'], (string) $item['detail_po'], (string) $item['remarks']]))
            );
            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = $this->baseOnPoImportReportRow($item);
            }
            $this->applyImportReportTerm($groups[$groupKey], $item);
        }

        return $this->finalizedImportReportRows($groups);
    }

    private function getStandaloneOnPoImportReportRows()
    {
        $queryRows = $this->db->query("SELECT
                p.id_po,
                COALESCE(NULLIF(p.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer') AS bowheer,
                COALESCE(p.status_po, 'ON PO') AS status_po,
                p.po_number,
                (SELECT GROUP_CONCAT(DISTINCT CONCAT('NY-', pl.id_pipeline) ORDER BY pl.id_pipeline SEPARATOR ', ')
                    FROM tb_po_target_pipeline pl
                    WHERE pl.linked_id_po = p.id_po) AS ny_po_ref,
                p.po_date,
                p.total_value,
                COALESCE((SELECT release_value FROM tb_po_amend am WHERE am.id_po = p.id_po ORDER BY am.amend_no DESC LIMIT 1), p.total_value) AS current_release_value,
                p.type_project,
                t.term_index,
                t.percent,
                '' AS no_po_sub,
                '' AS regional,
                '' AS kota_po,
                '' AS detail_po,
                '' AS remarks,
                NULL AS source_row_no,
                COALESCE(NULLIF(t.plan_amount, 0), t.value) AS plan_amount,
                COALESCE(t.submit_raw, '') AS submit_raw,
                t.target_status,
                t.target_week,
                t.invoice_date,
                COALESCE((SELECT SUM(tc.invoice_amount) FROM tb_po_term_claim tc WHERE tc.id_term = t.id_term AND tc.id_allocation IS NULL), 0) AS invoice_amount
            FROM tb_po_term t
            JOIN tb_po p ON p.id_po = t.id_po
            LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = p.id_bowheer
            WHERE NOT EXISTS (SELECT 1 FROM tb_po_term_allocation a WHERE a.id_term = t.id_term)
            ORDER BY bowheer ASC, p.po_number ASC, t.term_index ASC")->result_array();

        $groups = [];
        foreach ($queryRows as $item) {
            $groupKey = (int) $item['id_po'] . '|term';
            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = $this->baseOnPoImportReportRow($item);
            }
            $this->applyImportReportTerm($groups[$groupKey], $item);
        }

        return $this->finalizedImportReportRows($groups);
    }

    private function getNyPoImportReportRows()
    {
        if (!$this->db->table_exists('tb_po_target_pipeline')) {
            return [];
        }

        $queryRows = $this->db->query("SELECT
                pl.*,
                COALESCE(bp.bowheer, pl.dashboard_bowheer, 'Tanpa Bowheer') AS bowheer
            FROM tb_po_target_pipeline pl
            LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = pl.id_bowheer
            WHERE pl.linked_id_po IS NULL
            ORDER BY bowheer ASC, pl.source_row_no ASC, pl.term_index ASC")->result_array();

        $groups = [];
        $refByGroup = [];
        foreach ($queryRows as $item) {
            $groupKey = 'pipeline|' . $this->buildNyPoReferenceGroupKey($item);
            $idPipeline = (int) ($item['id_pipeline'] ?? 0);
            if ($idPipeline > 0 && (!isset($refByGroup[$groupKey]) || $idPipeline < $refByGroup[$groupKey])) {
                $refByGroup[$groupKey] = $idPipeline;
            }
        }

        foreach ($queryRows as $item) {
            $groupKey = 'pipeline|' . $this->buildNyPoReferenceGroupKey($item);
            if (!isset($groups[$groupKey])) {
                $row = $this->getEmptyImportReportRow();
                $row['NY PO REF'] = 'NY-' . (int) ($refByGroup[$groupKey] ?? $item['id_pipeline'] ?? 0);
                $row['BOWHEER'] = (string) ($item['dashboard_bowheer'] ?: $item['bowheer']);
                $row['STATUS PO'] = 'NY PO';
                $row['REGIONAL'] = (string) ($item['regional'] ?? '');
                $row['KOTA PO'] = (string) ($item['kota_po'] ?? '');
                $row['DETAIL PO'] = (string) ($item['detail_po'] ?? '');
                $row['REMARKS'] = (string) ($item['remarks'] ?? '');
                $row['TYPE PROJECT'] = (string) ($item['type_project'] ?? '');
                $row['TGL PO'] = !empty($item['po_date']) ? (string) $item['po_date'] : '';
                $row['PO TERM'] = (string) ($item['po_term'] ?? '');
                $groups[$groupKey] = $row;
            }

            $term = (int) ($item['term_index'] ?? 0);
            if ($term < 1 || $term > 5) {
                continue;
            }

            $plan = (float) ($item['plan_amount'] ?? 0);
            $groups[$groupKey]['PLAN ' . $term] = $this->formatImportReportAmount($plan);
            $groups[$groupKey]['SUBMIT ' . $term] = $this->importReportSubmitValue($item);
            $groups[$groupKey]['PO VALUE'] = $this->formatImportReportAmount($this->parseImportReportAmount($groups[$groupKey]['PO VALUE']) + $plan);
            $groups[$groupKey]['OUTSTANDING TOTAL'] = $this->formatImportReportAmount($this->parseImportReportAmount($groups[$groupKey]['OUTSTANDING TOTAL']) + $plan);
            $groups[$groupKey]['NY PO 2026'] = $this->formatImportReportAmount($this->parseImportReportAmount($groups[$groupKey]['NY PO 2026']) + (float) ($item['ny_po_2026_amount'] ?? 0));
            $groups[$groupKey]['NY PO 2027'] = $this->formatImportReportAmount($this->parseImportReportAmount($groups[$groupKey]['NY PO 2027']) + (float) ($item['ny_po_2027_amount'] ?? 0));
        }

        return array_values($groups);
    }

    public function getNyPoReferenceRows()
    {
        if (!$this->db->table_exists('tb_po_target_pipeline')) {
            return [];
        }

        $rows = $this->db->query("SELECT
                pl.id_pipeline,
                pl.id_bowheer,
                CONCAT('NY-', pl.id_pipeline) AS ny_po_ref,
                COALESCE(NULLIF(pl.dashboard_bowheer, ''), bp.bowheer, 'Tanpa Bowheer') AS bowheer,
                COALESCE(pl.linked_po_number, '') AS linked_po_number,
                COALESCE(pl.pipeline_status, 'OPEN') AS pipeline_status,
                COALESCE(pl.type_project, '') AS type_project,
                COALESCE(pl.regional, '') AS regional,
                COALESCE(pl.kota_po, '') AS kota_po,
                COALESCE(pl.detail_po, '') AS detail_po,
                COALESCE(pl.remarks, '') AS remarks,
                COALESCE(pl.source_row_no, 0) AS source_row_no,
                pl.term_index,
                COALESCE(NULLIF(pl.ny_po_2026_amount, 0), pl.plan_amount, 0) AS amount,
                pl.target_week_start,
                pl.target_week_end
            FROM tb_po_target_pipeline pl
            LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = pl.id_bowheer
            WHERE CONVERT(pl.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
            ORDER BY bowheer ASC, pl.regional ASC, pl.kota_po ASC, pl.term_index ASC, pl.id_pipeline ASC")->result_array();

        $refByGroup = [];
        foreach ($rows as $row) {
            $groupKey = $this->buildNyPoReferenceGroupKey($row);
            $currentId = (int) ($row['id_pipeline'] ?? 0);
            if (!isset($refByGroup[$groupKey]) || $currentId < $refByGroup[$groupKey]) {
                $refByGroup[$groupKey] = $currentId;
            }
        }

        $dedupedRows = [];
        foreach ($rows as &$row) {
            $groupKey = $this->buildNyPoReferenceGroupKey($row);
            $row['ny_po_ref'] = 'NY-' . (int) ($refByGroup[$groupKey] ?? $row['id_pipeline'] ?? 0);
            $row['term_label'] = 'Term ' . (int) ($row['term_index'] ?? 0);
            $row['amount'] = $this->formatImportReportAmount((float) ($row['amount'] ?? 0));
            $row['period'] = !empty($row['target_week_start']) && !empty($row['target_week_end'])
                ? $row['target_week_start'] . ' s/d ' . $row['target_week_end']
                : '';
            $dedupeKey = implode('|', [
                $groupKey,
                (int) ($row['term_index'] ?? 0),
                (string) ($row['amount'] ?? ''),
                (string) ($row['period'] ?? '')
            ]);

            if (!isset($dedupedRows[$dedupeKey])) {
                $dedupedRows[$dedupeKey] = $row;
                continue;
            }

            $existingLinkedPo = trim((string) ($dedupedRows[$dedupeKey]['linked_po_number'] ?? ''));
            $currentLinkedPo = trim((string) ($row['linked_po_number'] ?? ''));
            if ($existingLinkedPo === '' && $currentLinkedPo !== '') {
                $dedupedRows[$dedupeKey] = $row;
            }
        }
        unset($row);

        return array_values($dedupedRows);
    }

    private function baseOnPoImportReportRow($item)
    {
        $row = $this->getEmptyImportReportRow();
        $row['NY PO REF'] = (string) ($item['ny_po_ref'] ?? '');
        $row['BOWHEER'] = (string) ($item['bowheer'] ?? '');
        $row['STATUS PO'] = (string) ($item['status_po'] ?: 'ON PO');
        $row['NO PO'] = (string) ($item['po_number'] ?? '');
        $row['NO PO SUB'] = (string) ($item['no_po_sub'] ?? '');
        $row['REGIONAL'] = (string) ($item['regional'] ?? '');
        $row['KOTA PO'] = (string) ($item['kota_po'] ?? '');
        $row['DETAIL PO'] = (string) ($item['detail_po'] ?? '');
        $row['REMARKS'] = (string) ($item['remarks'] ?? '');
        $row['TYPE PROJECT'] = (string) ($item['type_project'] ?? '');
        $row['TGL PO'] = !empty($item['po_date']) ? (string) $item['po_date'] : '';
        $row['PO FINAL VALUE'] = (float) ($item['current_release_value'] ?? 0) !== (float) ($item['total_value'] ?? 0)
            ? $this->formatImportReportAmount($item['current_release_value'])
            : '';

        $this->applyMyRepImportReportFallback($row);

        return $row;
    }

    private function applyMyRepImportReportFallback(&$row)
    {
        $poNumber = trim((string) ($row['NO PO'] ?? ''));
        if ($poNumber === '') {
            return;
        }

        if (
            trim((string) ($row['REGIONAL'] ?? '')) !== ''
            && trim((string) ($row['KOTA PO'] ?? '')) !== ''
            && trim((string) ($row['DETAIL PO'] ?? '')) !== ''
        ) {
            return;
        }

        $meta = $this->getMyRepImportReportMetaByPoNumber($poNumber);
        if (empty($meta)) {
            return;
        }

        if (trim((string) ($row['REGIONAL'] ?? '')) === '') {
            $row['REGIONAL'] = (string) ($meta['regional'] ?? '');
        }
        if (trim((string) ($row['KOTA PO'] ?? '')) === '') {
            $row['KOTA PO'] = (string) ($meta['city'] ?? '');
        }
        if (trim((string) ($row['DETAIL PO'] ?? '')) === '') {
            $row['DETAIL PO'] = (string) ($meta['detail'] ?? '');
        }
    }

    private function getMyRepImportReportMetaByPoNumber($poNumber)
    {
        static $cache = [];

        $cacheKey = strtoupper(trim((string) $poNumber));
        if ($cacheKey === '') {
            return [];
        }
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        if (!$this->db->table_exists('tb_myrep_po_header') || !$this->db->table_exists('tb_myrep_cluster')) {
            $cache[$cacheKey] = [];
            return [];
        }

        $sqlParts = [
            "SELECT
                CONVERT(COALESCE(c.regional_name, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS regional,
                CONVERT(COALESCE(c.city_name, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS city,
                CONVERT(CONCAT(COALESCE(NULLIF(p.po_type, ''), 'CLUSTER'), ' - ', c.cluster_name) USING utf8mb4) COLLATE utf8mb4_unicode_ci AS detail
            FROM tb_myrep_po_header p
            JOIN tb_myrep_cluster c ON c.id_myrep_cluster = p.id_myrep_cluster
            WHERE UPPER(TRIM(CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci)) = UPPER(TRIM(CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci))"
        ];
        $params = [$poNumber];

        if ($this->db->table_exists('tb_rfs_myrep_mainfeeder')) {
            $sqlParts[] = "SELECT
                CONVERT(COALESCE(NULLIF(mf.regional_name, ''), '') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS regional,
                CONVERT(COALESCE(mf.city_name, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS city,
                CONVERT(CONCAT(COALESCE(NULLIF(p.po_type, ''), 'MAINFEEDER'), ' - ', mf.mainfeeder_name) USING utf8mb4) COLLATE utf8mb4_unicode_ci AS detail
            FROM tb_myrep_po_header p
            JOIN tb_rfs_myrep_mainfeeder mf ON mf.id_mainfeeder = p.id_mainfeeder
            WHERE UPPER(TRIM(CONVERT(p.po_number USING utf8mb4) COLLATE utf8mb4_unicode_ci)) = UPPER(TRIM(CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci))";
            $params[] = $poNumber;
        }

        $rows = $this->db->query(implode(' UNION ALL ', $sqlParts), $params)->result_array();
        $regional = [];
        $city = [];
        $detail = [];

        foreach ($rows as $row) {
            foreach (['regional' => &$regional, 'city' => &$city, 'detail' => &$detail] as $field => &$bucket) {
                $value = trim((string) ($row[$field] ?? ''));
                if ($value !== '') {
                    $bucket[$value] = $value;
                }
            }
            unset($bucket);
        }

        $cache[$cacheKey] = [
            'regional' => implode(', ', array_values($regional)),
            'city' => implode(', ', array_values($city)),
            'detail' => implode(', ', array_values($detail)),
        ];

        return $cache[$cacheKey];
    }

    private function applyImportReportTerm(&$row, $item)
    {
        $term = (int) ($item['term_index'] ?? 0);
        if ($term < 1 || $term > 5) {
            return;
        }

        $plan = (float) ($item['plan_amount'] ?? 0);
        $invoice = (float) ($item['invoice_amount'] ?? 0);
        $submitValue = $this->importReportSubmitValue($item);
        $submitIsDate = $this->isImportReportSubmitDate($submitValue);
        $percent = (float) ($item['percent'] ?? 0);
        if ($percent > 0) {
            $row['_PO_TERM_PARTS'][$term] = rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.');
        }
        $row['PLAN ' . $term] = $submitIsDate ? '' : $this->formatImportReportAmount($plan);
        $row['SUBMIT ' . $term] = $submitValue;
        $row['NILAI ' . $term] = $submitIsDate ? $this->formatImportReportAmount($invoice > 0 ? $invoice : $plan) : '';
        $row['PO VALUE'] = $this->formatImportReportAmount($this->parseImportReportAmount($row['PO VALUE']) + $plan);
        $row['INVOICE ALL'] = $this->formatImportReportAmount($this->parseImportReportAmount($row['INVOICE ALL']) + $invoice);

        if ($invoice > 0 && !empty($item['invoice_date']) && (int) date('Y', strtotime($item['invoice_date'])) === 2026) {
            $row['INVOICE 2026'] = $this->formatImportReportAmount($this->parseImportReportAmount($row['INVOICE 2026']) + $invoice);
        }
        if (strtoupper((string) ($item['target_status'] ?? '')) === 'TARGET_WEEK') {
            $row['OUTSTANDING ON TARGET 2026'] = $this->formatImportReportAmount($this->parseImportReportAmount($row['OUTSTANDING ON TARGET 2026']) + $plan);
        }
        if (strtoupper((string) ($item['target_status'] ?? '')) === 'CARRY_OVER') {
            $row['OUTSTANDING CO 2027'] = $this->formatImportReportAmount($this->parseImportReportAmount($row['OUTSTANDING CO 2027']) + $plan);
        }
    }

    private function isImportReportSubmitDate($value)
    {
        $value = trim((string) $value);
        if ($value === '' || preg_match('/^W\d{1,2}$/i', $value) || preg_match('/^\d{4}$/', $value)) {
            return false;
        }

        return strtotime($value) !== false;
    }

    private function finalizedImportReportRows($groups)
    {
        $rows = [];
        foreach ($groups as $row) {
            $poValue = $this->parseImportReportAmount($row['PO VALUE']);
            $invoiceAll = $this->parseImportReportAmount($row['INVOICE ALL']);
            $row['OUTSTANDING TOTAL'] = $this->formatImportReportAmount(max($poValue - $invoiceAll, 0));
            if ($row['PO TERM'] === '') {
                $percents = [];
                if (!empty($row['_PO_TERM_PARTS']) && is_array($row['_PO_TERM_PARTS'])) {
                    ksort($row['_PO_TERM_PARTS']);
                    $percents = array_values($row['_PO_TERM_PARTS']);
                } else {
                    for ($term = 1; $term <= 5; $term++) {
                        $plan = $this->parseImportReportAmount($row['PLAN ' . $term]);
                        if ($plan > 0 && $poValue > 0) {
                            $percent = round(($plan / $poValue) * 100, 2);
                            $percents[] = rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.');
                        }
                    }
                }
                $row['PO TERM'] = !empty($percents) ? implode(':', $percents) : '';
            }
            unset($row['_PO_TERM_PARTS']);
            $rows[] = $row;
        }
        return $rows;
    }

    private function importReportSubmitValue($item)
    {
        $invoice = (float) ($item['invoice_amount'] ?? 0);
        if ($invoice > 0 && !empty($item['invoice_date'])) {
            return (string) $item['invoice_date'];
        }

        $submit = trim((string) ($item['submit_raw'] ?? ''));
        if ($submit !== '') {
            return $submit;
        }

        $status = strtoupper(trim((string) ($item['target_status'] ?? '')));
        if ($status === 'TARGET_WEEK' && (int) ($item['target_week'] ?? 0) > 0) {
            return 'W' . (int) $item['target_week'];
        }
        if ($status === 'CARRY_OVER') {
            return '2027';
        }
        if (!empty($item['invoice_date'])) {
            return (string) $item['invoice_date'];
        }
        return '';
    }

    private function formatImportReportAmount($value)
    {
        $value = (float) $value;
        return abs($value) < 0.000001 ? '' : rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    private function parseImportReportAmount($value)
    {
        return (float) str_replace(',', '', (string) $value);
    }

    public function importCsv($filePath, $sourceFile, $userId)
    {
        if (!is_readable($filePath)) {
            return ['status' => false, 'message' => 'CSV file is not readable: ' . $filePath];
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['status' => false, 'message' => 'Unable to open CSV file'];
        }

        $header = null;
        $rowNo = 0;
        while (($line = fgetcsv($handle)) !== false) {
            $rowNo++;
            $candidateMap = [];
            foreach ($line as $index => $name) {
                $candidateMap[$this->normalizeHeader($name)] = $index;
            }

            if (isset($candidateMap['BOWHEER']) && isset($candidateMap['NO PO'])) {
                $header = $line;
                break;
            }
        }

        if (!$header) {
            fclose($handle);
            return ['status' => false, 'message' => 'CSV header is missing'];
        }

        $headerMap = [];
        foreach ($header as $index => $name) {
            $headerMap[$this->normalizeHeader($name)] = $index;
        }

        $rows = [];
        $pipelineRows = [];
        while (($line = fgetcsv($handle)) !== false) {
            $rowNo++;
            $bowheer = $this->csvValue($line, $headerMap, 'BOWHEER');
            $poNumber = $this->csvValue($line, $headerMap, 'NO PO');
            if (trim($bowheer) === '' && trim($poNumber) === '') {
                continue;
            }

            $row = [
                'row_no' => $rowNo,
                'raw' => $line,
                'bowheer' => trim($bowheer),
                'dashboard_bowheer' => trim($bowheer),
                'status_po' => strtoupper(trim($this->csvValue($line, $headerMap, 'STATUS PO'))) ?: 'ON PO',
                'po_number' => trim($poNumber),
                'no_po_sub' => trim($this->csvValue($line, $headerMap, 'NO PO SUB')),
                'regional' => trim($this->csvValue($line, $headerMap, 'REGIONAL')),
                'kota_po' => trim($this->csvValue($line, $headerMap, 'KOTA PO')),
                'detail_po' => trim($this->csvValue($line, $headerMap, 'DETAIL PO')),
                'remarks' => trim($this->csvValue($line, $headerMap, 'REMARKS')),
                'type_project' => trim($this->csvValue($line, $headerMap, 'TYPE PROJECT')),
                'po_date' => $this->parseDate($this->csvValue($line, $headerMap, 'TGL PO')),
                'po_value' => $this->normalizeAmountLocal($this->csvValue($line, $headerMap, 'PO VALUE')),
                'po_final_value' => $this->normalizeAmountLocal($this->csvValue($line, $headerMap, 'PO FINAL VALUE')),
                'po_term' => trim($this->csvValue($line, $headerMap, 'PO TERM')),
                'outstanding' => $this->normalizeAmountLocal($this->csvValue($line, $headerMap, 'OUTSTANDING TOTAL')),
                'helper_outs_2026' => $this->normalizeAmountLocal($this->csvValue($line, $headerMap, 'OUTSTANDING ON TARGET 2026')),
                'helper_co_2027' => $this->normalizeAmountLocal($this->csvValue($line, $headerMap, 'OUTSTANDING CO 2027')),
                'helper_all_invoice' => $this->normalizeAmountLocal($this->csvValue($line, $headerMap, 'INVOICE ALL')),
                'helper_invoice_2026' => $this->normalizeAmountLocal($this->csvValue($line, $headerMap, 'INVOICE 2026')),
                'helper_ny_po_2026' => $this->normalizeAmountLocal($this->csvValue($line, $headerMap, 'NY PO 2026')),
                'helper_ny_po_2027' => $this->normalizeAmountLocal($this->csvValue($line, $headerMap, 'NY PO 2027'))
            ];

            for ($i = 1; $i <= 5; $i++) {
                $row['plan_' . $i] = $this->normalizeAmountLocal($this->csvValue($line, $headerMap, 'PLAN ' . $i));
                $row['submit_' . $i] = trim($this->csvValue($line, $headerMap, 'SUBMIT ' . $i));
                $row['nilai_' . $i] = $this->normalizeAmountLocal($this->csvValue($line, $headerMap, 'NILAI ' . $i));
            }
            $this->autoFillNyPoTarget2026FromValue($row);
            $row['dashboard_metrics'] = $this->computeDashboardMetrics($row);

            if ($row['status_po'] === 'NY PO') {
                $pipelineRows[] = $row;
            } else {
                $row['bowheer'] = $this->resolveImportBowheerName($row['bowheer'], $row['type_project']);
                $rows[] = $row;
            }
        }
        fclose($handle);

        if (empty($rows) && empty($pipelineRows)) {
            return ['status' => false, 'message' => 'No data rows found'];
        }

        $groups = $this->buildImportGroups($rows);

        $this->db->trans_begin();
        $this->cleanupImportedPoCsv($sourceFile);
        $this->db->insert('tb_po_import_batch', [
            'source_file' => $sourceFile,
            'imported_by' => $userId ?: null,
            'row_count' => count($rows) + count($pipelineRows),
            'status' => 'COMMITTED'
        ]);
        $batchId = (int) $this->db->insert_id();

        $summary = [
            'inserted' => 0,
            'updated' => 0,
            'pipeline' => 0,
            'terms' => 0,
            'allocations' => 0,
            'claims' => 0,
            'target_week' => 0,
            'carry_over' => 0,
            'total_effective' => 0,
            'total_invoiced' => 0,
            'total_target_2026' => 0,
            'total_carry_2027' => 0
        ];

        foreach ($groups as $group) {
            $row = $group['base'];
            $effectiveValue = $group['effective_value'];
            $summary['total_effective'] += $effectiveValue;
            $sourceHash = $group['source_hash'];

            $idBowheer = $this->resolveBowheerId($row['bowheer']);
            $existing = $this->db->get_where('tb_po', ['source_hash' => $sourceHash])->row_array();
            if ($existing) {
                $idPo = (int) $existing['id_po'];
                $this->deletePoChildren($idPo);
                $this->db->where('id_po', $idPo)->update('tb_po', [
                    'po_number' => $row['po_number'],
                    'po_date' => $row['po_date'],
                    'id_bowheer' => $idBowheer,
                    'total_value' => $group['po_value'],
                    'status_po' => 'ON PO',
                    'dashboard_bowheer' => $row['dashboard_bowheer'],
                    'type_project' => $row['type_project'],
                    'dashboard_all_invoice' => $group['dashboard_all_invoice'],
                    'dashboard_invoice_2026' => $group['dashboard_invoice_2026'],
                    'dashboard_outs_2026' => $group['dashboard_outs_2026'],
                    'dashboard_co_2027' => $group['dashboard_co_2027'],
                    'source_file' => $sourceFile,
                    'source_row_no' => $row['row_no'],
                    'import_batch_id' => $batchId,
                    'notes' => 'Imported from PO CSV'
                ]);
                $summary['updated']++;
            } else {
                $this->db->insert('tb_po', [
                    'po_number' => $row['po_number'],
                    'po_date' => $row['po_date'],
                    'id_bowheer' => $idBowheer,
                    'total_value' => $group['po_value'],
                    'status_po' => 'ON PO',
                    'dashboard_bowheer' => $row['dashboard_bowheer'],
                    'type_project' => $row['type_project'],
                    'dashboard_all_invoice' => $group['dashboard_all_invoice'],
                    'dashboard_invoice_2026' => $group['dashboard_invoice_2026'],
                    'dashboard_outs_2026' => $group['dashboard_outs_2026'],
                    'dashboard_co_2027' => $group['dashboard_co_2027'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $userId ?: null,
                    'notes' => 'Imported from PO CSV',
                    'source_file' => $sourceFile,
                    'source_row_no' => $row['row_no'],
                    'source_hash' => $sourceHash,
                    'import_batch_id' => $batchId
                ]);
                $idPo = (int) $this->db->insert_id();
                $summary['inserted']++;
            }

            $this->db->insert('tb_po_amend', [
                'id_po' => $idPo,
                'amend_no' => 1,
                'release_value' => $effectiveValue,
                'release_date' => $row['po_date'],
                'notes' => $group['has_final_value'] ? 'CSV effective value from PO FINAL VALUE' : 'CSV initial value'
            ]);
            $idAmend = (int) $this->db->insert_id();

            $percents = $this->parseTermPercents($row['po_term']);
            $termBuckets = $this->buildImportTermBuckets($group);
            for ($i = 1; $i <= 5; $i++) {
                if (empty($termBuckets[$i])) {
                    continue;
                }

                $bucket = $termBuckets[$i];
                $termMeta = $bucket['term_meta'];

                $this->db->insert('tb_po_term', [
                    'id_po' => $idPo,
                    'id_amend' => $idAmend,
                    'term_index' => $i,
                    'percent' => isset($percents[$i]) ? $percents[$i] : 0,
                    'value' => $bucket['value'],
                    'plan_amount' => $bucket['plan_amount'],
                    'submit_raw' => $termMeta['submit_raw'],
                    'target_year' => $termMeta['target_year'],
                    'target_week' => $termMeta['target_week'],
                    'target_week_start' => $termMeta['target_week_start'],
                    'target_week_end' => $termMeta['target_week_end'],
                    'target_status' => $termMeta['target_status'],
                    'invoice_date' => $termMeta['invoice_date']
                ]);
                $idTerm = (int) $this->db->insert_id();
                $summary['terms']++;

                if ($group['has_allocations']) {
                    foreach ($bucket['allocations'] as $allocation) {
                        $meta = $allocation['meta'];
                        $this->db->insert('tb_po_term_allocation', [
                            'id_term' => $idTerm,
                            'no_po_sub' => $allocation['row']['no_po_sub'],
                            'regional' => $allocation['row']['regional'],
                            'kota_po' => $allocation['row']['kota_po'],
                            'detail_po' => $allocation['row']['detail_po'],
                            'remarks' => $allocation['row']['remarks'],
                            'allocation_value' => $allocation['amount'],
                            'plan_amount' => $allocation['plan_amount'],
                            'submit_raw' => $meta['submit_raw'],
                            'target_year' => $meta['target_year'],
                            'target_week' => $meta['target_week'],
                            'target_week_start' => $meta['target_week_start'],
                            'target_week_end' => $meta['target_week_end'],
                            'target_status' => $meta['target_status'],
                            'invoice_date' => $meta['invoice_date'],
                            'outstanding_amount' => $allocation['row']['outstanding'],
                            'source_row_no' => $allocation['row']['row_no']
                        ]);
                        $idAllocation = (int) $this->db->insert_id();
                        $summary['allocations']++;
                        $this->addSubmitMetaToSummary($meta, $allocation['amount'], $allocation['nilai'], $summary);

                        if ($meta['target_status'] === 'INVOICED' && $allocation['nilai'] > 0) {
                            $this->db->insert('tb_po_term_claim', [
                                'id_term' => $idTerm,
                                'id_allocation' => $idAllocation,
                                'invoice_date' => $meta['invoice_date'],
                                'invoice_amount' => $allocation['nilai'],
                                'claim_source' => 'IMPORT',
                                'source_raw' => $meta['submit_raw'],
                                'created_by' => $userId ?: null
                            ]);
                            $summary['claims']++;
                        }
                    }
                } else {
                    $this->addSubmitMetaToSummary($termMeta, $bucket['value'], $bucket['nilai'], $summary);
                }

                if (!$group['has_allocations'] && $termMeta['target_status'] === 'INVOICED' && $bucket['nilai'] > 0) {
                    $this->db->insert('tb_po_term_claim', [
                        'id_term' => $idTerm,
                        'invoice_date' => $termMeta['invoice_date'],
                        'invoice_amount' => $bucket['nilai'],
                        'claim_source' => 'IMPORT',
                        'source_raw' => $termMeta['submit_raw'],
                        'created_by' => $userId ?: null
                    ]);
                    $summary['claims']++;
                }
            }
        }

        foreach ($pipelineRows as $pipelineRow) {
            $idBowheer = $this->resolveBowheerId($this->resolveImportBowheerName($pipelineRow['bowheer'], $pipelineRow['type_project']));
            for ($i = 1; $i <= 5; $i++) {
                $plan = (float) $pipelineRow['plan_' . $i];
                $submit = trim((string) $pipelineRow['submit_' . $i]);
                if ($plan <= 0 && $submit === '') {
                    continue;
                }

                $meta = $this->resolveSubmitMeta($submit);
                $hasWeek = stripos($submit, 'W') !== false
                    || (strtoupper(trim((string) ($pipelineRow['status_po'] ?? ''))) === 'NY PO' && $plan > 0 && $submit === '');
                $sourceHash = hash('sha256', implode('|', [
                    'NY',
                    $pipelineRow['dashboard_bowheer'],
                    $pipelineRow['row_no'],
                    $i,
                    number_format($plan, 2, '.', ''),
                    $submit
                ]));

                $this->db->insert('tb_po_target_pipeline', [
                    'id_bowheer' => $idBowheer,
                    'dashboard_bowheer' => $pipelineRow['dashboard_bowheer'],
                    'status_po' => 'NY PO',
                    'regional' => $pipelineRow['regional'],
                    'kota_po' => $pipelineRow['kota_po'],
                    'detail_po' => $pipelineRow['detail_po'],
                    'remarks' => $pipelineRow['remarks'],
                    'type_project' => $pipelineRow['type_project'],
                    'po_date' => $pipelineRow['po_date'],
                    'po_term' => $pipelineRow['po_term'],
                    'term_index' => $i,
                    'plan_amount' => $plan,
                    'submit_raw' => $submit,
                    'target_year' => $hasWeek ? 2026 : 2027,
                    'target_week' => $hasWeek ? $meta['target_week'] : null,
                    'target_week_start' => $hasWeek ? $meta['target_week_start'] : null,
                    'target_week_end' => $hasWeek ? $meta['target_week_end'] : null,
                    'target_status' => $hasWeek ? 'TARGET_WEEK' : 'CARRY_OVER',
                    'ny_po_2026_amount' => $hasWeek ? $plan : 0,
                    'ny_po_2027_amount' => $hasWeek ? 0 : $plan,
                    'source_file' => $sourceFile,
                    'source_row_no' => $pipelineRow['row_no'],
                    'import_batch_id' => $batchId,
                    'source_hash' => $sourceHash
                ]);
                $summary['pipeline']++;
                if ($hasWeek) {
                    $summary['target_week']++;
                    $summary['total_target_2026'] += $plan;
                } else {
                    $summary['carry_over']++;
                    $summary['total_carry_2027'] += $plan;
                }
            }
        }

        $this->rebuildDashboardCache($batchId);

        $this->db->where('id_batch', $batchId)->update('tb_po_import_batch', [
            'total_effective' => $summary['total_effective'],
            'total_invoiced' => $summary['total_invoiced'],
            'total_target_2026' => $summary['total_target_2026'],
            'total_carry_2027' => $summary['total_carry_2027']
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Database error while importing CSV'];
        }

        $this->db->trans_commit();
        $summary['batch_id'] = $batchId;
        return ['status' => true, 'message' => 'CSV imported', 'summary' => $summary];
    }

    private function autoFillNyPoTarget2026FromValue(array &$row)
    {
        if (strtoupper(trim((string) ($row['status_po'] ?? ''))) !== 'NY PO') {
            return;
        }

        $hasPlan = false;
        for ($i = 1; $i <= 5; $i++) {
            if ((float) ($row['plan_' . $i] ?? 0) > 0 || trim((string) ($row['submit_' . $i] ?? '')) !== '') {
                $hasPlan = true;
                break;
            }
        }
        if ($hasPlan) {
            return;
        }

        $totalValue = (float) (($row['po_final_value'] ?? 0) > 0 ? $row['po_final_value'] : ($row['po_value'] ?? 0));
        if ($totalValue <= 0) {
            return;
        }

        $splits = $this->buildTermSplitsFromPoTerm((string) ($row['po_term'] ?? ''), $totalValue);
        foreach ($splits as $split) {
            $termIndex = (int) ($split['term_index'] ?? 0);
            if ($termIndex < 1 || $termIndex > 5) {
                continue;
            }
            $row['plan_' . $termIndex] = (float) ($split['value'] ?? 0);
            $row['submit_' . $termIndex] = '';
            $row['nilai_' . $termIndex] = 0;
        }
    }

    private function buildImportGroups($rows)
    {
        $groups = [];

        foreach ($rows as $row) {
            $hasSub = trim((string) $row['no_po_sub']) !== ''
                || trim((string) $row['regional']) !== ''
                || trim((string) $row['kota_po']) !== ''
                || trim((string) $row['detail_po']) !== ''
                || trim((string) $row['remarks']) !== '';
            $keyParts = $hasSub
                ? ['sub', $row['bowheer'], $row['po_number'], $row['po_date'], $row['po_term']]
                : ['row', $row['bowheer'], $row['po_number'], $row['po_date'], $row['row_no']];
            $groupKey = hash('sha256', implode('|', $keyParts));

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'base' => $row,
                    'rows' => [],
                    'has_allocations' => $hasSub,
                    'source_hash' => $groupKey,
                    'po_value' => 0,
                    'effective_value' => 0,
                    'has_final_value' => false,
                    'dashboard_all_invoice' => 0,
                    'dashboard_invoice_2026' => 0,
                    'dashboard_outs_2026' => 0,
                    'dashboard_co_2027' => 0
                ];
            }

            $effectiveValue = $row['po_final_value'] > 0 ? $row['po_final_value'] : $row['po_value'];
            $metrics = $row['dashboard_metrics'];
            $groups[$groupKey]['rows'][] = $row;
            $groups[$groupKey]['po_value'] += (float) $row['po_value'];
            $groups[$groupKey]['effective_value'] += $effectiveValue;
            $groups[$groupKey]['has_final_value'] = $groups[$groupKey]['has_final_value'] || $row['po_final_value'] > 0;
            $groups[$groupKey]['dashboard_all_invoice'] += (float) $metrics['all_invoice'];
            $groups[$groupKey]['dashboard_invoice_2026'] += (float) $metrics['invoice_2026'];
            $groups[$groupKey]['dashboard_outs_2026'] += (float) $metrics['outs_2026'];
            $groups[$groupKey]['dashboard_co_2027'] += (float) $metrics['co_2027'];
        }

        return array_values($groups);
    }

    private function computeDashboardMetrics($row)
    {
        $status = strtoupper(trim((string) $row['status_po']));
        $result = [
            'outs_2026' => 0,
            'co_2027' => 0,
            'all_invoice' => 0,
            'invoice_2026' => 0,
            'ny_po_2026' => 0,
            'ny_po_2027' => 0
        ];

        for ($i = 1; $i <= 5; $i++) {
            $plan = (float) $row['plan_' . $i];
            $nilai = (float) $row['nilai_' . $i];
            $submit = trim((string) $row['submit_' . $i]);
            $hasWeek = stripos($submit, 'W') !== false;

            if ($status === 'ON PO') {
                $result['all_invoice'] += $nilai;
                if ($hasWeek) {
                    $result['outs_2026'] += $plan;
                } else {
                    $result['co_2027'] += $plan;
                }

                $invoiceDate = $this->parseDate($submit);
                if ($invoiceDate !== null && (int) date('Y', strtotime($invoiceDate)) === 2026) {
                    $result['invoice_2026'] += $nilai;
                }
            } elseif ($status === 'NY PO') {
                if ($hasWeek) {
                    $result['ny_po_2026'] += $plan;
                } else {
                    $result['ny_po_2027'] += $plan;
                }
            }
        }

        $helperKeys = [
            'helper_outs_2026' => 'outs_2026',
            'helper_co_2027' => 'co_2027',
            'helper_all_invoice' => 'all_invoice',
            'helper_invoice_2026' => 'invoice_2026',
            'helper_ny_po_2026' => 'ny_po_2026',
            'helper_ny_po_2027' => 'ny_po_2027'
        ];

        foreach ($helperKeys as $sourceKey => $targetKey) {
            if (isset($row[$sourceKey]) && (float) $row[$sourceKey] != 0.0) {
                $result[$targetKey] = (float) $row[$sourceKey];
            }
        }

        return $result;
    }

    private function buildImportTermBuckets($group)
    {
        $buckets = [];

        foreach ($group['rows'] as $row) {
            for ($i = 1; $i <= 5; $i++) {
                $plan = (float) $row['plan_' . $i];
                $nilai = (float) $row['nilai_' . $i];
                $submit = trim((string) $row['submit_' . $i]);
                $amount = $plan > 0 ? $plan : $nilai;

                if ($amount <= 0 && $submit === '') {
                    continue;
                }

                $meta = $this->resolveSubmitMeta($submit);
                if (!isset($buckets[$i])) {
                    $buckets[$i] = [
                        'value' => 0,
                        'plan_amount' => 0,
                        'nilai' => 0,
                        'allocations' => [],
                        'term_meta' => null,
                        'metas' => []
                    ];
                }

                $buckets[$i]['value'] += $amount;
                $buckets[$i]['plan_amount'] += $plan;
                $buckets[$i]['nilai'] += $nilai;
                $buckets[$i]['metas'][] = $meta;

                if ($group['has_allocations']) {
                    $buckets[$i]['allocations'][] = [
                        'row' => $row,
                        'amount' => $amount,
                        'plan_amount' => $plan,
                        'nilai' => $nilai,
                        'meta' => $meta
                    ];
                }
            }
        }

        foreach ($buckets as &$bucket) {
            $bucket['term_meta'] = $this->resolveTermMeta($bucket['metas']);
        }
        unset($bucket);

        return $buckets;
    }

    private function resolveSubmitMeta($submit)
    {
        $submit = trim((string) $submit);
        $meta = [
            'submit_raw' => $submit,
            'target_status' => 'OPEN',
            'target_year' => null,
            'target_week' => null,
            'target_week_start' => null,
            'target_week_end' => null,
            'invoice_date' => null
        ];

        if (preg_match('/^W(\d{1,2})$/i', $submit, $match)) {
            $period = $this->weekPeriod(2026, (int) $match[1]);
            $meta['target_status'] = 'TARGET_WEEK';
            $meta['target_year'] = 2026;
            $meta['target_week'] = (int) $match[1];
            $meta['target_week_start'] = $period['start'];
            $meta['target_week_end'] = $period['end'];
            return $meta;
        }

        if ($submit === '2027') {
            $meta['target_status'] = 'CARRY_OVER';
            $meta['target_year'] = 2027;
            return $meta;
        }

        $invoiceDate = $this->parseDate($submit);
        if ($invoiceDate !== null) {
            $meta['target_status'] = 'INVOICED';
            $meta['invoice_date'] = $invoiceDate;
        }

        return $meta;
    }

    private function resolveTermMeta($metas)
    {
        if (empty($metas)) {
            return $this->resolveSubmitMeta('');
        }

        $first = $metas[0];
        foreach ($metas as $meta) {
            if ($meta != $first) {
                return [
                    'submit_raw' => 'MIXED',
                    'target_status' => 'OPEN',
                    'target_year' => null,
                    'target_week' => null,
                    'target_week_start' => null,
                    'target_week_end' => null,
                    'invoice_date' => null
                ];
            }
        }

        return $first;
    }

    private function addSubmitMetaToSummary($meta, $amount, $invoiceAmount, &$summary)
    {
        if ($meta['target_status'] === 'TARGET_WEEK') {
            $summary['target_week']++;
            $summary['total_target_2026'] += (float) $amount;
        } elseif ($meta['target_status'] === 'CARRY_OVER') {
            $summary['carry_over']++;
            $summary['total_carry_2027'] += (float) $amount;
        } elseif ($meta['target_status'] === 'INVOICED') {
            $summary['total_invoiced'] += (float) $invoiceAmount;
        }
    }

    private function normalizeHeader($value)
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', (string) $value);
        return strtoupper(trim(preg_replace('/\s+/', ' ', $value)));
    }

    private function csvValue($row, $headerMap, $name)
    {
        $key = $this->normalizeHeader($name);
        if (!isset($headerMap[$key])) {
            return '';
        }

        $index = $headerMap[$key];
        return isset($row[$index]) ? $row[$index] : '';
    }

    private function normalizeAmountLocal($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0.0;
        }

        $isNegative = preg_match('/^\s*\(.*\)\s*$/', $value) === 1;
        $normalized = preg_replace('/[^\d,.\-]/', '', $value);
        $lastDot = strrpos($normalized, '.');
        $lastComma = strrpos($normalized, ',');

        if ($lastDot !== false && $lastComma !== false) {
            if ($lastDot > $lastComma) {
                $normalized = str_replace(',', '', $normalized);
            } else {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            }
        } elseif ($lastComma !== false) {
            $parts = explode(',', $normalized);
            if (count($parts) > 2 || strlen(end($parts)) === 3) {
                $normalized = str_replace(',', '', $normalized);
            } else {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            }
        } else {
            $parts = explode('.', $normalized);
            if (count($parts) > 2) {
                $normalized = str_replace('.', '', $normalized);
            } elseif (count($parts) === 2 && strlen(end($parts)) === 3 && strlen($parts[0]) <= 3) {
                $normalized = str_replace('.', '', $normalized);
            }
        }

        if (!is_numeric($normalized)) {
            return 0.0;
        }

        $amount = (float) $normalized;
        return $isNegative && $amount > 0 ? -$amount : $amount;
    }

    private function parseDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $legacyDate = $this->normalizeLegacyJulyImportDate($value);
        if ($legacyDate !== null) {
            return $legacyDate;
        }

        if (is_numeric($value) && (float) $value > 30000) {
            $timestamp = ((float) $value - 25569) * 86400;
            return gmdate('Y-m-d', (int) $timestamp);
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function normalizeLegacyJulyImportDate($value)
    {
        $value = trim((string) $value);
        if (preg_match('/^7[\/-]26[\/-]200([1-9])$/', $value, $match)) {
            return sprintf('2026-07-%02d', (int) $match[1]);
        }
        if (preg_match('/^200([1-9])-07-26$/', $value, $match)) {
            return sprintf('2026-07-%02d', (int) $match[1]);
        }

        return null;
    }

    private function parseTermPercents($term)
    {
        $result = [];
        $parts = preg_split('/\s*:\s*/', trim((string) $term));
        $index = 1;
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $result[$index] = $this->normalizePercentLocal($part);
            $index++;
        }

        return $result;
    }

    private function normalizePercentLocal($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0.0;
        }

        if (!preg_match('/-?\d+(?:[.,]\d+)?/', $value, $match)) {
            return 0.0;
        }

        $number = str_replace(',', '.', $match[0]);
        $percent = is_numeric($number) ? (float) $number : 0.0;

        if ($percent < 0) {
            return 0.0;
        }

        return $percent > 100 ? 100.0 : $percent;
    }

    private function weekPeriod($year, $week)
    {
        $jan1 = new DateTime($year . '-01-01');
        $start = clone $jan1;
        $start->modify('-' . (int) $jan1->format('w') . ' days');
        $start->modify('+' . (($week - 1) * 7) . ' days');
        $end = clone $start;
        $end->modify('+6 days');

        return [
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d')
        ];
    }

    private function resolveBowheerId($name)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        foreach ($this->bowheerKeyCandidates($name) as $key) {
            $row = $this->db->get_where('tb_bowheer_po', ['bowheer_key' => $key])->row_array();
            if ($row) {
                return (int) $row['id_bowheer'];
            }
        }

        $row = $this->db->get_where('tb_master_bowheer_bilco', ['nama_bowheer' => $name])->row_array();
        if ($row) {
            return (int) $row['id_bowheer'];
        }

        $this->db->insert('tb_bowheer_po', [
            'bowheer' => $name,
            'bowheer_key' => $this->normalizeBowheerKey($name)
        ]);
        return (int) $this->db->insert_id();
    }

    private function resolveImportBowheerName($name, $typeProject)
    {
        $name = trim((string) $name);
        $type = strtoupper(trim((string) $typeProject));

        if ($this->normalizeBowheerKey($name) === 'PT VGREEN') {
            if ($type === 'EVCS') {
                return 'PT VGREEN ( EVCS )';
            }
            if ($type === 'BSS') {
                return 'PT VGREEN ( BSS )';
            }
        }

        return $name;
    }

    private function dashboardBowheerOrder()
    {
        return [
            'PT BANGTELINDO' => 'Bp Zaenul',
            'PT PERSADA SOKKA TAMA' => 'Bp Zaenul',
            'PT TELKOM AKSES' => 'Bp Zaenul',
            'PT TELKOM AKSES - STAR' => 'Bp Hendry',
            'PT MORATEL' => 'Bp Zaenul',
            'PT TBG ( PERMIT )' => 'Bp Zaenul',
            'PT XL SMART' => 'Bp Zaenul',
            'PT EMR - NRO' => 'Bp Wardani',
            'PT EMR - PU ( NON PPN )' => 'Bp Slamet',
            'PT FS - PU' => 'Bp Slamet',
            'PT MORATEL - PU' => 'Bp Slamet',
            'PT EMR - DONASI' => 'Bp Fringga',
            'PT FS - OSP' => 'Bp Donny',
            'PT FS - DONASI' => 'Bp Donny',
            'PT IFORTE - FIBERIZATION' => 'Bp Sumirat',
            'PT IFORTE - FTTH XL' => 'Bp Sumirat',
            'PT IFORTE - FTTH IOH' => 'Bp Sumirat',
            'PT IFORTE - REGULAR & CONN' => 'Bp Sumirat',
            'PT IFORTE - LBS RECTIFIKASI' => 'Bp Hendry',
            'PT VGREEN' => 'Bp Wendy',
            'PT ADT' => 'LOGISTIK',
            'PT DIAN KARYA' => 'LOGISTIK'
        ];
    }

    private function dashboardPic($name)
    {
        $order = $this->dashboardBowheerOrder();
        if (isset($order[$name])) {
            return $order[$name];
        }

        $row = $this->db
            ->select('pic')
            ->where('bowheer_key', $this->normalizeBowheerKey($name))
            ->get('tb_bowheer_po')
            ->row_array();

        return !empty($row['pic']) ? $row['pic'] : '-';
    }

    private function bowheerKeyCandidates($name)
    {
        $key = $this->normalizeBowheerKey($name);
        $candidates = [$key];

        if (strpos($key, 'PT PT ') === 0) {
            $candidates[] = 'PT ' . substr($key, 6);
        } elseif (strpos($key, 'PT ') === 0 && strpos($key, 'PT PT ') !== 0) {
            $candidates[] = 'PT PT ' . substr($key, 3);
        }

        return array_values(array_unique($candidates));
    }

    private function normalizeBowheerKey($name)
    {
        $name = strtoupper(trim((string) $name));
        $name = str_replace('.', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }

    private function deletePoChildren($idPo)
    {
        $terms = $this->db->select('id_term')->get_where('tb_po_term', ['id_po' => (int) $idPo])->result_array();
        foreach ($terms as $term) {
            $this->db->delete('tb_po_term_allocation', ['id_term' => (int) $term['id_term']]);
            $this->db->delete('tb_po_term_claim', ['id_term' => (int) $term['id_term']]);
        }
        $this->db->delete('tb_po_term', ['id_po' => (int) $idPo]);
        $this->db->delete('tb_po_amend', ['id_po' => (int) $idPo]);
    }

    private function cleanupImportedPoCsv($sourceFile)
    {
        $this->db->group_start()
            ->where('source_file', $sourceFile)
            ->or_like('source_file', 'DATABASE PO CSV', 'after')
            ->group_end();
        $this->db->delete('tb_po_target_pipeline');

        $this->db->group_start()
            ->where('source_file', $sourceFile)
            ->or_like('source_file', 'DATABASE PO CSV', 'after')
            ->group_end()
            ->where('notes', 'Imported from PO CSV');

        $rows = $this->db->select('id_po')->get('tb_po')->result_array();
        foreach ($rows as $row) {
            $idPo = (int) $row['id_po'];
            $this->deletePoChildren($idPo);
            $this->db->delete('tb_po', ['id_po' => $idPo]);
        }
    }

    public function purgeStandaloneData()
    {
        $before = [
            'po' => $this->db->table_exists('tb_po') ? (int) $this->db->count_all('tb_po') : 0,
            'pipeline' => $this->db->table_exists('tb_po_target_pipeline') ? (int) $this->db->count_all('tb_po_target_pipeline') : 0
        ];

        $this->db->trans_begin();

        $this->deleteAllIfTableExists('tb_po_term_invoice');
        $this->deleteAllIfTableExists('tb_po_term_allocation');
        $this->deleteAllIfTableExists('tb_po_term_claim');
        $this->deleteAllIfTableExists('tb_po_term');
        $this->deleteAllIfTableExists('tb_po_amend');
        $this->deleteAllIfTableExists('tb_po');
        $this->deleteAllIfTableExists('tb_po_target_pipeline');
        $this->deleteAllIfTableExists('tb_po_dashboard_cache');
        $this->deleteAllIfTableExists('tb_po_import_batch');

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return [
                'status' => false,
                'message' => 'Gagal menghapus data PO Monitor.'
            ];
        }

        $this->db->trans_commit();

        return [
            'status' => true,
            'message' => 'Semua data PO Monitor berhasil dihapus. PO: ' . number_format($before['po'], 0, ',', '.') . ', NY PO pipeline: ' . number_format($before['pipeline'], 0, ',', '.') . '. Data PO_MyRep tidak terpengaruh.'
        ];
    }

    private function deleteAllIfTableExists($table)
    {
        if ($this->db->table_exists($table)) {
            $this->db->empty_table($table);
        }
    }

    /**
     * Auto-allocate invoices (from tb_billingpayment) for a PO to its terms.
     * It will create rows in tb_po_term_invoice splitting invoices across terms sequentially.
     * Returns summary array with counts and remaining totals.
     */
    public function allocateInvoicesToTermsByPoNumber($po_number)
    {
        $this->db->trans_begin();

        $po = $this->getPOByNumber($po_number);
        if (!$po) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'PO not found'];
        }

        // load terms with already allocated sum
        $terms = $this->db->select('t.*, COALESCE(SUM(ti.invoice_amount),0) AS allocated_sum', false)
            ->from('tb_po_term t')
            ->join('tb_po_term_invoice ti', 't.id_term = ti.id_term', 'left')
            ->where('t.id_po', $po['id_po'])
            ->group_by('t.id_term')
            ->order_by('t.term_index', 'ASC')
            ->get()
            ->result_array();

        if (empty($terms)) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'No terms defined for PO'];
        }

        // Prepare term remaining array
        $termRemaining = [];
        foreach ($terms as $t) {
            $remaining = (float) $t['value'] - (float) $t['allocated_sum'];
            if ($remaining < 0) $remaining = 0.0;
            $termRemaining[] = [
                'id_term' => (int) $t['id_term'],
                'remaining' => $remaining,
                'term_index' => (int) $t['term_index']
            ];
        }

        // Fetch invoices for this PO and how much of each is already allocated
        $invoices = $this->db->select('b.id_billing, COALESCE(b.invoice_price_nett,0) AS invoice_price_nett, COALESCE(SUM(ti.invoice_amount),0) AS allocated_amount', false)
            ->from('tb_billingpayment b')
            ->join('tb_po_term_invoice ti', 'b.id_billing = ti.id_billing', 'left')
            ->where('b.po_number', $po_number)
            ->group_by('b.id_billing')
            ->order_by('b.tgl_submit_invoice', 'ASC')
            ->get()
            ->result_array();

        $inserted = 0;

        // index pointer for terms
        $termPtr = 0;
        $termCount = count($termRemaining);

        foreach ($invoices as $inv) {
            $invId = (int) $inv['id_billing'];
            $invTotal = (float) $inv['invoice_price_nett'];
            $invAllocated = (float) $inv['allocated_amount'];
            $invRemain = $invTotal - $invAllocated;
            if ($invRemain <= 0) continue; // already fully allocated

            // move termPtr to the next term with remaining > 0
            while ($termPtr < $termCount && $termRemaining[$termPtr]['remaining'] <= 0.000001) {
                $termPtr++;
            }

            while ($invRemain > 0.000001 && $termPtr < $termCount) {
                $term = &$termRemaining[$termPtr];
                $alloc = min($invRemain, $term['remaining']);
                if ($alloc > 0.000001) {
                    $this->db->insert('tb_po_term_invoice', [
                        'id_term' => $term['id_term'],
                        'id_billing' => $invId,
                        'invoice_amount' => $alloc,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $inserted++;
                    $invRemain -= $alloc;
                    $term['remaining'] -= $alloc;
                }

                if ($term['remaining'] <= 0.000001) {
                    $termPtr++;
                }
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Database error while allocating'];
        }

        $this->db->trans_commit();

        // compute post-allocation totals
        $totalInvoiced = (float) $this->db->select('COALESCE(SUM(invoice_amount),0) AS s')->get_where('tb_po_term_invoice', ['id_term IN (SELECT id_term FROM tb_po_term WHERE id_po = ' . (int) $po['id_po'] . ') ' => null])->row()->s;

        return [
            'status' => true,
            'message' => 'Allocation completed',
            'allocations_inserted' => $inserted,
            'po_id' => $po['id_po']
        ];
    }
}
