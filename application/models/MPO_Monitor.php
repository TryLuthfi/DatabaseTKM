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
            `created_at` datetime DEFAULT current_timestamp(),
            PRIMARY KEY (`id_pipeline`),
            KEY `idx_tb_po_target_pipeline_bowheer` (`dashboard_bowheer`),
            KEY `idx_tb_po_target_pipeline_target` (`target_year`, `target_week`),
            KEY `idx_tb_po_target_pipeline_status` (`target_status`),
            KEY `idx_tb_po_target_pipeline_batch` (`import_batch_id`)
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

    public function getBatchInvoiceTerminRows()
    {
        return $this->db->query("SELECT *
            FROM (
                SELECT
                    p.id_po,
                    CONVERT(COALESCE(NULLIF(a.no_po_sub, ''), p.po_number) USING utf8mb4) COLLATE utf8mb4_unicode_ci AS po_number,
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
                    CONVERT(COALESCE(NULLIF(a.no_po_sub, ''), p.po_number) USING utf8mb4) COLLATE utf8mb4_unicode_ci AS po_number,
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
        return $this->db->get_where('tb_po', ['id_po' => (int) $id_po])->row_array();
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
        $rows = $this->db->select('a.*', false)
            ->select('t.term_index', false)
            ->select($allocationClaimLimitSql . ' AS allocation_value', false)
            ->select('GREATEST(' . $allocationClaimLimitSql . ' - COALESCE(SUM(tc.invoice_amount),0), 0) AS outstanding_amount', false)
            ->select('COALESCE(SUM(tc.invoice_amount),0) AS invoiced_amount', false)
            ->from('tb_po_term_allocation a')
            ->join('tb_po_term t', 't.id_term = a.id_term')
            ->join('tb_po_term_claim tc', 'tc.id_allocation = a.id_allocation', 'left')
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
            ) x
            GROUP BY target_year, target_week, target_week_start, target_week_end, term_index
            ORDER BY target_year ASC, target_week ASC, term_index ASC")->result_array();

        return $rows;
    }

    public function getDashboardSummary()
    {
        $cachedRows = $this->db
            ->order_by('sort_order', 'ASC')
            ->order_by('bowheer', 'ASC')
            ->get('tb_po_dashboard_cache')
            ->result_array();

        if (!empty($cachedRows)) {
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

        $recordsTotal = (int) $this->db->count_all('tb_po_dashboard_cache');

        $rows = $this->db
            ->select('*')
            ->order_by('sort_order', 'ASC')
            ->order_by('bowheer', 'ASC')
            ->get('tb_po_dashboard_cache')
            ->result_array();

        if ($mode === 'initial') {
            $adjustments = $this->getDashboardManualClaimAdjustments();
            foreach ($rows as &$row) {
                $key = (string) $row['bowheer'];
                $manualDone = (float) ($adjustments[$key]['manual_done_2026'] ?? 0);
                $manualTargetWeek = (float) ($adjustments[$key]['manual_target_week_2026'] ?? 0);

                $row['all_invoice'] = (float) $row['all_invoice'] - $manualDone;
                $row['done_inv_2026'] = (float) $row['done_inv_2026'] - $manualDone;
                $row['outs_2026_on_target'] = (float) $row['outs_2026_on_target'] + $manualTargetWeek;
                $row['grandtotal_target'] = (float) $row['outs_2026_on_target'] + (float) $row['ny_po_on_target_2026'];
                $row['ny_po_total'] = (float) $row['ny_po_on_target_2026'] + (float) $row['co_to_2027'];
                $row['total_outs'] = (float) $row['grandtotal_target'] + (float) $row['co_to_2027'];
            }
            unset($row);
        }

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

    public function getDashboardInitialTotals()
    {
        $rows = $this->db
            ->select('bowheer, done_inv_2026, outs_2026_on_target, ny_po_on_target_2026')
            ->get('tb_po_dashboard_cache')
            ->result_array();
        $adjustments = $this->getDashboardManualClaimAdjustments();

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
            $ny = (float) $row['ny_po_on_target_2026'];

            $totals['done_inv_2026'] += $done;
            $totals['outs_2026_on_target'] += $outs;
            $totals['ny_po_on_target_2026'] += $ny;
        }

        $totals['done_outs_ny_2026'] = $totals['done_inv_2026'] + $totals['outs_2026_on_target'] + $totals['ny_po_on_target_2026'];
        return $totals;
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
                'total_target' => 0,
                'total_achieved' => 0
            ];

            foreach ($periods as $period) {
                $projectMap[$id]['months'][$period['key']] = [
                    'target' => 0,
                    'achieved' => 0
                ];
            }
        }

        $targetRows = $this->db->query("SELECT
                p.id_bowheer,
                a.target_week_start,
                a.target_week_end,
                COALESCE(NULLIF(a.plan_amount, 0), a.allocation_value) AS amount
            FROM tb_po_term_allocation a
            JOIN tb_po_term t ON t.id_term = a.id_term
            JOIN tb_po p ON p.id_po = t.id_po
            WHERE CONVERT(a.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                AND a.target_week_start IS NOT NULL
                AND a.target_week_end IS NOT NULL
            UNION ALL
            SELECT
                p.id_bowheer,
                t.target_week_start,
                t.target_week_end,
                COALESCE(NULLIF(t.plan_amount, 0), t.value) AS amount
            FROM tb_po_term t
            JOIN tb_po p ON p.id_po = t.id_po
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
                pl.plan_amount AS amount
            FROM tb_po_target_pipeline pl
            WHERE CONVERT(pl.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
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

            if (!isset($projectMap[$id]['months'][$periodKey])) {
                continue;
            }

            $amount = (float) $row['amount'];
            $projectMap[$id]['months'][$periodKey]['target'] += $amount;
            $projectMap[$id]['total_target'] += $amount;
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

            if (!isset($projectMap[$id]['months'][$periodKey])) {
                continue;
            }

            $amount = (float) $row['invoice_amount'];
            $projectMap[$id]['months'][$periodKey]['achieved'] += $amount;
            $projectMap[$id]['total_achieved'] += $amount;
        }

        $totals = [
            'months' => [],
            'total_target' => 0,
            'total_achieved' => 0
        ];
        foreach ($periods as $period) {
            $totals['months'][$period['key']] = [
                'target' => 0,
                'achieved' => 0
            ];
        }

        foreach ($projectMap as &$project) {
            foreach ($periods as $period) {
                $target = (float) $project['months'][$period['key']]['target'];
                $achieved = (float) $project['months'][$period['key']]['achieved'];
                $project['months'][$period['key']]['percent'] = $target > 0 ? ($achieved / $target) * 100 : ($achieved > 0 ? 100 : 0);
                $totals['months'][$period['key']]['target'] += $target;
                $totals['months'][$period['key']]['achieved'] += $achieved;
            }

            $project['deviasi'] = max($project['total_target'] - $project['total_achieved'], 0);
            $project['achieved_percent'] = $project['total_target'] > 0 ? ($project['total_achieved'] / $project['total_target']) * 100 : ($project['total_achieved'] > 0 ? 100 : 0);
            $project['deviasi_percent'] = max(100 - $project['achieved_percent'], 0);
        }
        unset($project);

        foreach ($totals['months'] as $monthKey => &$monthTotal) {
            $monthTotal['percent'] = $monthTotal['target'] > 0 ? ($monthTotal['achieved'] / $monthTotal['target']) * 100 : ($monthTotal['achieved'] > 0 ? 100 : 0);
            $totals['total_target'] += $monthTotal['target'];
            $totals['total_achieved'] += $monthTotal['achieved'];
        }
        unset($monthTotal);

        $totals['deviasi'] = max($totals['total_target'] - $totals['total_achieved'], 0);
        $totals['achieved_percent'] = $totals['total_target'] > 0 ? ($totals['total_achieved'] / $totals['total_target']) * 100 : ($totals['total_achieved'] > 0 ? 100 : 0);
        $totals['deviasi_percent'] = max(100 - $totals['achieved_percent'], 0);

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
            $row['regional'] = trim((string) ($row['regional'] ?? '-')) ?: '-';
            $row['area'] = trim((string) ($row['area'] ?? '-')) ?: '-';
            $row['month'] = strtoupper($monthKey);
            $row['month_label'] = $monthKey ? strtoupper(date('F', strtotime($monthKey . '-01'))) : '-';
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
            $this->appendBreakdownOption($options['months'], (string) ($row['month'] ?? ''), (string) ($row['month_label'] ?? $row['month'] ?? ''));
            $this->appendBreakdownOption($options['weeks'], (string) ($row['week'] ?? ''), (string) ($row['week'] ?? ''));
        }

        foreach ($options as &$items) {
            uasort($items, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
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

    public function getComparisonDetail($idBowheer, $periodKey, $groupBy, $type)
    {
        $idBowheer = (int) $idBowheer;
        $groupBy = $groupBy === 'week' ? 'week' : 'month';
        $type = $type === 'achieved' ? 'achieved' : 'target';

        if ($type === 'achieved') {
            return $this->getComparisonAchievedDetail($idBowheer, $periodKey, $groupBy);
        }

        return $this->getComparisonTargetDetail($idBowheer, $periodKey, $groupBy);
    }

    private function getComparisonTargetDetail($idBowheer, $periodKey, $groupBy)
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
                    CAST(NULL AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci AS regional,
                    CAST(NULL AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci AS kota_po,
                    CAST(NULL AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci AS detail_po,
                    CAST(NULL AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci AS remarks,
                    t.target_week_start,
                    t.target_week_end,
                    COALESCE(NULLIF(t.plan_amount, 0), t.value) AS amount,
                    COALESCE(tc.invoice_amount, 0) AS invoiced_amount,
                    tc.invoice_date AS claim_invoice_date,
                    CONVERT('Target Term' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS source_label
                FROM tb_po_term t
                JOIN tb_po p ON p.id_po = t.id_po
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
                    pl.target_week_start,
                    pl.target_week_end,
                    pl.plan_amount AS amount,
                    0 AS invoiced_amount,
                    CAST(NULL AS DATE) AS claim_invoice_date,
                    CONVERT('NY PO Target' USING utf8mb4) COLLATE utf8mb4_unicode_ci AS source_label
                FROM tb_po_target_pipeline pl
                WHERE CONVERT(pl.target_status USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT('TARGET_WEEK' USING utf8mb4) COLLATE utf8mb4_unicode_ci
                    AND pl.id_bowheer = ?
                    AND pl.target_week_start IS NOT NULL
                    AND pl.target_week_end IS NOT NULL
            ) x
            ORDER BY target_week_start ASC, po_number ASC, term_index ASC", [$idBowheer, $idBowheer, $idBowheer])->result_array();

        return array_values(array_filter($rows, function ($row) use ($periodKey, $groupBy) {
            $rowPeriod = $groupBy === 'week'
                ? $this->weekKey((int) date('Y', strtotime($row['target_week_start'])), (int) $this->weekNumberFromPeriod($row['target_week_start'], $row['target_week_end']))
                : $this->majorityMonthKey($row['target_week_start'], $row['target_week_end']);

            return $rowPeriod === $periodKey;
        }));
    }

    private function getComparisonAchievedDetail($idBowheer, $periodKey, $groupBy)
    {
        $rows = $this->db->query("SELECT
                p.id_bowheer,
                p.po_number,
                p.type_project,
                p.po_date,
                t.term_index,
                COALESCE(NULLIF(a.no_po_sub, ''), aa.no_po_sub) AS no_po_sub,
                COALESCE(NULLIF(a.regional, ''), aa.regional) AS regional,
                COALESCE(NULLIF(a.kota_po, ''), aa.kota_po) AS kota_po,
                COALESCE(NULLIF(a.detail_po, ''), aa.detail_po) AS detail_po,
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
            WHERE tc.invoice_date IS NOT NULL
                AND p.id_bowheer = ?
            ORDER BY tc.invoice_date ASC, p.po_number ASC, t.term_index ASC", [$idBowheer])->result_array();

        return array_values(array_filter($rows, function ($row) use ($periodKey, $groupBy) {
            $rowPeriod = $groupBy === 'week'
                ? $this->weekKeyFromDate($row['invoice_date'])
                : $this->monthKeyFromInvoiceWeek($row['invoice_date']);

            return $rowPeriod === $periodKey;
        }));
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
        if ($amount <= 0 || empty($invoiceDate)) {
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
            $allocationRemaining = max($allocationValue - (float) ($allocation['invoiced_amount'] ?? 0), 0);
            if ($amount > $allocationRemaining + 0.000001) {
                return ['status' => false, 'message' => 'Invoice amount exceeds sub PO remaining'];
            }
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
            $termRemaining = max($termClaimLimit - (float) ($claimed['amount'] ?? 0), 0);
            if ($amount > $termRemaining + 0.000001) {
                return ['status' => false, 'message' => 'Invoice amount exceeds term remaining'];
            }
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
            }
        }
        $this->db->where('id_po', (int) $term['id_po'])->update('tb_po');

        $this->rebuildDashboardCache(null);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Failed to claim term'];
        }

        $this->db->trans_commit();
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
        if ($amount <= 0 || empty($invoiceDate)) {
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

        if ($amount > $claimValue + 0.000001) {
            return ['status' => false, 'message' => 'Invoice amount exceeds claim value'];
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
        }
        $this->db->where('id_po', (int) $term['id_po'])->update('tb_po');

        $this->rebuildDashboardCache(null);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Failed to update invoice'];
        }

        $this->db->trans_commit();
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
        if ($oldTotal <= 0) {
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
        if ($remainingTermClaims <= 0) {
            $this->db->where('id_term', $idTerm)->update('tb_po_term', [
                'invoice_date' => null,
                'submit_raw' => null
            ]);
        }

        $this->db->set('dashboard_all_invoice', 'GREATEST(COALESCE(dashboard_all_invoice, 0) - ' . $this->db->escape($oldTotal) . ', 0)', false);
        $this->db->set('dashboard_invoice_2026', 'GREATEST(COALESCE(dashboard_invoice_2026, 0) - ' . $this->db->escape($old2026) . ', 0)', false);
        if ($targetStatus === 'TARGET_WEEK') {
            $this->db->set('dashboard_outs_2026', 'COALESCE(dashboard_outs_2026, 0) + ' . $this->db->escape($old2026), false);
        }
        $this->db->where('id_po', (int) $term['id_po'])->update('tb_po');

        $this->rebuildDashboardCache(null);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Failed to reset invoice'];
        }

        $this->db->trans_commit();
        return ['status' => true, 'message' => 'Invoice term berhasil direset'];
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

        return $this->syncMyRepTerminRowToMonitor($myrep, $userId, $cutoffDate);
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

    public function syncMyRepClaimsForPoNumber($poNumber, $userId = 0)
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
            ORDER BY t.termin_no ASC, t.invoice_date IS NULL ASC, t.invoice_date DESC, t.id_po_termin DESC", [$poNumber])->result_array();

        $rowsByTerm = [];
        foreach ($rows as $row) {
            $termNo = (int) ($row['termin_no'] ?? 0);
            if ($termNo > 0 && !isset($rowsByTerm[$termNo])) {
                $rowsByTerm[$termNo] = $row;
            }
        }
        $rows = array_values($rowsByTerm);

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
        if ($amount <= 0 && isset($myrep['termin_value'])) {
            $amount = (float) $myrep['termin_value'];
        }

        $statusTermin = strtoupper(trim((string) ($myrep['status_termin'] ?? '')));
        $termNo = (int) ($myrep['termin_no'] ?? 0);
        $poNumber = (string) ($myrep['po_number'] ?? '');
        $cutoffDate = $this->normalizeSyncDate($cutoffDate);
        $isSyncable = $invoiceDate !== null
            && ($cutoffDate === null || strtotime($invoiceDate) >= strtotime($cutoffDate))
            && $amount > 0
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
            if (!empty($term) && $amount > 0) {
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
            }
        }
        $this->db->where('id_po', (int) $term['id_po'])->update('tb_po');
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

            $existing = $this->db->get_where('tb_po', ['source_hash' => $sourceHash])->row_array();
            if ($existing) {
                $summary['skipped']++;
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
                'dashboard_outs_2026' => 0,
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
                $this->db->insert('tb_po_term', [
                    'id_po' => $idPo,
                    'id_amend' => $idAmend,
                    'term_index' => (int) $split['term_index'],
                    'percent' => (float) $split['percent'],
                    'value' => (float) $split['value'],
                    'plan_amount' => (float) $split['value'],
                    'target_status' => 'OPEN'
                ]);
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

                        $this->db->insert('tb_po_term_allocation', [
                            'id_term' => $idTerm,
                            'no_po_sub' => $allocation['no_po_sub'],
                            'regional' => $allocation['regional'],
                            'kota_po' => $allocation['kota_po'],
                            'detail_po' => $allocation['detail_po'],
                            'remarks' => $allocation['remarks'],
                            'allocation_value' => $allocationValue,
                            'plan_amount' => $allocationValue,
                            'target_status' => 'OPEN',
                            'source_row_no' => $allocation['row_no']
                        ]);
                        $summary['allocations']++;
                    }
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
        return ['status' => $summary['inserted'] > 0, 'message' => 'Batch PO selesai.', 'summary' => $summary];
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

            if ($poNumber === '' || $bowheer === '' || $effectiveValue <= 0) {
                $summary['skipped']++;
                $summary['errors'][] = 'Row ' . ($index + 1) . ' wajib isi NO PO, BOWHEER, dan nilai PO.';
                continue;
            }

            $groupKey = hash('sha256', implode('|', [
                $bowheer,
                $poNumber,
                $poDate,
                trim((string) ($row['po_term'] ?? '')),
                trim((string) ($row['type_project'] ?? '')),
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
                    'allocation_hash' => ''
                ];
            }

            $groups[$groupKey]['po_value'] += $poValue;
            $groups[$groupKey]['po_final_value'] += $poFinalValue;
            $groups[$groupKey]['effective_value'] += $effectiveValue;

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
        }
        unset($group);

        return array_values($groups);
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
            'BOWHEER', 'STATUS PO', 'NO PO', 'NO PO SUB', 'REGIONAL', 'KOTA PO',
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
                a.invoice_date,
                COALESCE((SELECT SUM(tc.invoice_amount) FROM tb_po_term_claim tc WHERE tc.id_allocation = a.id_allocation), 0) AS invoice_amount
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
            ORDER BY bowheer ASC, pl.source_row_no ASC, pl.term_index ASC")->result_array();

        $groups = [];
        foreach ($queryRows as $item) {
            $groupKey = 'pipeline|' . (
                $item['source_row_no'] !== null && $item['source_row_no'] !== ''
                    ? (string) $item['source_row_no']
                    : md5(implode('|', [(string) $item['dashboard_bowheer'], (string) $item['regional'], (string) $item['kota_po'], (string) $item['detail_po'], (string) $item['remarks'], (string) $item['type_project']]))
            );
            if (!isset($groups[$groupKey])) {
                $row = $this->getEmptyImportReportRow();
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

    private function baseOnPoImportReportRow($item)
    {
        $row = $this->getEmptyImportReportRow();
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
        return $row;
    }

    private function applyImportReportTerm(&$row, $item)
    {
        $term = (int) ($item['term_index'] ?? 0);
        if ($term < 1 || $term > 5) {
            return;
        }

        $plan = (float) ($item['plan_amount'] ?? 0);
        $invoice = (float) ($item['invoice_amount'] ?? 0);
        $row['PLAN ' . $term] = $this->formatImportReportAmount($plan);
        $row['SUBMIT ' . $term] = $this->importReportSubmitValue($item);
        $row['NILAI ' . $term] = $invoice > 0 ? $this->formatImportReportAmount($invoice) : '';
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

    private function finalizedImportReportRows($groups)
    {
        $rows = [];
        foreach ($groups as $row) {
            $poValue = $this->parseImportReportAmount($row['PO VALUE']);
            $invoiceAll = $this->parseImportReportAmount($row['INVOICE ALL']);
            $row['OUTSTANDING TOTAL'] = $this->formatImportReportAmount(max($poValue - $invoiceAll, 0));
            if ($row['PO TERM'] === '') {
                $percents = [];
                for ($term = 1; $term <= 5; $term++) {
                    $plan = $this->parseImportReportAmount($row['PLAN ' . $term]);
                    if ($plan > 0 && $poValue > 0) {
                        $percent = round(($plan / $poValue) * 100, 2);
                        $percents[] = rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.');
                    }
                }
                $row['PO TERM'] = !empty($percents) ? implode(':', $percents) : '';
            }
            $rows[] = $row;
        }
        return $rows;
    }

    private function importReportSubmitValue($item)
    {
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
                $hasWeek = stripos($submit, 'W') !== false;
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

        if (is_numeric($value) && (float) $value > 30000) {
            $timestamp = ((float) $value - 25569) * 86400;
            return gmdate('Y-m-d', (int) $timestamp);
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
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
