-- =========================================================
-- PO MONITOR STANDALONE - FULL SCHEMA PATCH
-- Updated: 2026-07-09
-- Notes:
-- - Jalankan setelah backup database.
-- - File ini membuat/menambah struktur yang dipakai halaman PO_Monitor.
-- - Data PO Monitor bisa dikosongkan terpisah memakai block TRUNCATE di bawah.
-- - Tidak menyentuh data PO_MyRep.
-- =========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- =========================================================
-- 1. BASE TABLE PO MONITOR
-- =========================================================

CREATE TABLE IF NOT EXISTS `tb_po` (
  `id_po` int(11) NOT NULL AUTO_INCREMENT,
  `po_number` varchar(100) NOT NULL,
  `po_date` date DEFAULT NULL,
  `id_bowheer` int(11) DEFAULT NULL,
  `total_value` decimal(18,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id_po`),
  UNIQUE KEY `uk_tb_po_po_number` (`po_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_po_amend` (
  `id_amend` int(11) NOT NULL AUTO_INCREMENT,
  `id_po` int(11) NOT NULL,
  `amend_no` tinyint(4) NOT NULL,
  `release_value` decimal(18,2) NOT NULL,
  `release_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id_amend`),
  UNIQUE KEY `uk_tb_po_amend_po_amendno` (`id_po`, `amend_no`),
  KEY `idx_tb_po_amend_id_po` (`id_po`),
  CONSTRAINT `fk_tb_po_amend_id_po`
    FOREIGN KEY (`id_po`) REFERENCES `tb_po` (`id_po`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_po_term` (
  `id_term` int(11) NOT NULL AUTO_INCREMENT,
  `id_po` int(11) NOT NULL,
  `id_amend` int(11) DEFAULT NULL,
  `term_index` tinyint(4) NOT NULL,
  `percent` decimal(7,2) NOT NULL DEFAULT 0.00,
  `value` decimal(18,2) DEFAULT 0.00,
  `due_date` date DEFAULT NULL,
  `sla_days` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_term`),
  KEY `idx_tb_po_term_id_po` (`id_po`),
  KEY `idx_tb_po_term_id_amend` (`id_amend`),
  KEY `idx_tb_po_term_term_index` (`term_index`),
  CONSTRAINT `fk_tb_po_term_id_po`
    FOREIGN KEY (`id_po`) REFERENCES `tb_po` (`id_po`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tb_po_term_id_amend`
    FOREIGN KEY (`id_amend`) REFERENCES `tb_po_amend` (`id_amend`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Legacy helper table. Tidak dipakai untuk dashboard standalone baru,
-- tapi dibuat agar fitur lama/detail tidak error jika masih ada referensi.
CREATE TABLE IF NOT EXISTS `tb_po_term_invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_term` int(11) NOT NULL,
  `id_billing` int(11) DEFAULT NULL,
  `invoice_amount` decimal(18,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tb_po_term_invoice_id_term` (`id_term`),
  KEY `idx_tb_po_term_invoice_id_billing` (`id_billing`),
  CONSTRAINT `fk_tb_po_term_invoice_id_term`
    FOREIGN KEY (`id_term`) REFERENCES `tb_po_term` (`id_term`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 2. MASTER BOWHEER PO
-- =========================================================

CREATE TABLE IF NOT EXISTS `tb_bowheer_po` (
  `id_bowheer` int(11) NOT NULL AUTO_INCREMENT,
  `no_urut` int(11) DEFAULT NULL,
  `pic` varchar(100) DEFAULT NULL,
  `bowheer` varchar(150) NOT NULL,
  `bowheer_key` varchar(180) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_bowheer`),
  UNIQUE KEY `uk_tb_bowheer_po_key` (`bowheer_key`),
  KEY `idx_tb_bowheer_po_pic` (`pic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tb_bowheer_po` (`id_bowheer`, `no_urut`, `pic`, `bowheer`, `bowheer_key`) VALUES
(1, 1, 'Bp Zaenul', 'PT BANGTELINDO', 'PT BANGTELINDO'),
(2, 2, 'Bp Zaenul', 'PT PERSADA SOKKA TAMA', 'PT PERSADA SOKKA TAMA'),
(3, 3, 'Bp Zaenul', 'PT TELKOM AKSES', 'PT TELKOM AKSES'),
(27, 27, 'Bp Hendry', 'PT TELKOM AKSES - STAR', 'PT TELKOM AKSES - STAR'),
(4, 4, 'Bp Zaenul', 'PT MORATEL', 'PT MORATEL'),
(5, 5, 'Bp Zaenul', 'PT TBG ( PERMIT )', 'PT TBG ( PERMIT )'),
(6, 6, 'Bp Zaenul', 'PT XL SMART', 'PT XL SMART'),
(8, 8, 'Bp Wardani', 'PT NFT', 'PT NFT'),
(9, 9, 'Bp Wardani', 'PT EMR - NRO', 'PT EMR - NRO'),
(10, 10, 'Bp Slamet', 'PT EMR - PU ( NON PPN )', 'PT EMR - PU ( NON PPN )'),
(11, 11, 'Bp Slamet', 'PT FS - PU', 'PT FS - PU'),
(12, 12, 'Bp Slamet', 'PT MORATEL - PU', 'PT MORATEL - PU'),
(13, 13, 'Bp Fringga', 'PT EMR - DONASI', 'PT EMR - DONASI'),
(14, 14, 'Bp Donny', 'PT FS - OSP', 'PT FS - OSP'),
(15, 15, 'Bp Donny', 'PT FS - DONASI', 'PT FS - DONASI'),
(16, 16, 'Bp Sumirat', 'PT IFORTE - FIBERIZATION', 'PT IFORTE - FIBERIZATION'),
(17, 17, 'Bp Sumirat', 'PT IFORTE - FTTH XL', 'PT IFORTE - FTTH XL'),
(18, 18, 'Bp Sumirat', 'PT IFORTE - FTTH IOH', 'PT IFORTE - FTTH IOH'),
(19, 19, 'Bp Sumirat', 'PT IFORTE - REGULAR & CONN', 'PT IFORTE - REGULAR & CONN'),
(20, 20, 'Bp Hendry', 'PT IFORTE - LBS RECTIFIKASI', 'PT IFORTE - LBS RECTIFIKASI'),
(21, 21, 'Bp Wendy', 'PT VGREEN ( EVCS )', 'PT VGREEN ( EVCS )'),
(22, 22, 'Bp Wendy', 'PT VGREEN ( BSS )', 'PT VGREEN ( BSS )'),
(23, 23, 'LOGISTIK', 'PT ADT', 'PT ADT'),
(24, 24, 'LOGISTIK', 'PT DIAN KARYA', 'PT DIAN KARYA')
ON DUPLICATE KEY UPDATE
  `no_urut` = VALUES(`no_urut`),
  `pic` = VALUES(`pic`),
  `bowheer` = VALUES(`bowheer`),
  `bowheer_key` = VALUES(`bowheer_key`);

-- =========================================================
-- 3. STANDALONE TARGET / IMPORT / DASHBOARD TABLES
-- =========================================================

CREATE TABLE IF NOT EXISTS `tb_po_import_batch` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_po_term_claim` (
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
  CONSTRAINT `fk_tb_po_term_claim_id_term`
    FOREIGN KEY (`id_term`) REFERENCES `tb_po_term` (`id_term`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_po_term_allocation` (
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
  CONSTRAINT `fk_tb_po_term_allocation_id_term`
    FOREIGN KEY (`id_term`) REFERENCES `tb_po_term` (`id_term`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_po_target_pipeline` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_po_dashboard_cache` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 4. ALTER EXISTING TABLES TO CURRENT SHAPE
-- =========================================================

ALTER TABLE `tb_po`
  ADD COLUMN IF NOT EXISTS `source_file` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `source_row_no` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `source_hash` varchar(64) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `import_batch_id` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `status_po` varchar(30) DEFAULT 'ON PO',
  ADD COLUMN IF NOT EXISTS `dashboard_bowheer` varchar(150) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `type_project` varchar(150) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `dashboard_all_invoice` decimal(18,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS `dashboard_invoice_2026` decimal(18,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS `dashboard_outs_2026` decimal(18,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS `dashboard_co_2027` decimal(18,2) DEFAULT 0.00;

ALTER TABLE `tb_po_term`
  ADD COLUMN IF NOT EXISTS `plan_amount` decimal(18,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS `submit_raw` varchar(50) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `target_year` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `target_week` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `target_week_start` date DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `target_week_end` date DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `target_status` varchar(30) DEFAULT 'OPEN',
  ADD COLUMN IF NOT EXISTS `invoice_date` date DEFAULT NULL;

ALTER TABLE `tb_po_term`
  MODIFY COLUMN `percent` decimal(7,2) NOT NULL DEFAULT 0.00;

ALTER TABLE `tb_po_term_claim`
  ADD COLUMN IF NOT EXISTS `id_allocation` int(11) DEFAULT NULL AFTER `id_term`,
  ADD COLUMN IF NOT EXISTS `source_raw` varchar(100) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `created_by` int(11) DEFAULT NULL;

ALTER TABLE `tb_po_term_allocation`
  ADD COLUMN IF NOT EXISTS `no_po_sub` varchar(150) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `regional` varchar(150) DEFAULT NULL AFTER `no_po_sub`,
  ADD COLUMN IF NOT EXISTS `kota_po` varchar(150) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `detail_po` text NULL,
  ADD COLUMN IF NOT EXISTS `remarks` text NULL AFTER `detail_po`,
  ADD COLUMN IF NOT EXISTS `plan_amount` decimal(18,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS `submit_raw` varchar(50) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `target_year` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `target_week` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `target_week_start` date DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `target_week_end` date DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `target_status` varchar(30) DEFAULT 'OPEN',
  ADD COLUMN IF NOT EXISTS `invoice_date` date DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `outstanding_amount` decimal(18,2) DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS `source_row_no` int(11) DEFAULT NULL;

ALTER TABLE `tb_po_target_pipeline`
  ADD COLUMN IF NOT EXISTS `regional` varchar(150) DEFAULT NULL AFTER `status_po`,
  ADD COLUMN IF NOT EXISTS `remarks` text NULL AFTER `detail_po`;

-- =========================================================
-- 5. INDEX PATCH
-- Jalankan sekali. Jika index sudah ada, bagian ini bisa di-skip.
-- =========================================================

ALTER TABLE `tb_po` DROP INDEX `uk_tb_po_po_number`;
ALTER TABLE `tb_po` ADD KEY `idx_tb_po_source_hash` (`source_hash`);
ALTER TABLE `tb_po` ADD KEY `idx_tb_po_number_bowheer` (`po_number`, `id_bowheer`);
ALTER TABLE `tb_po` ADD KEY `idx_tb_po_dashboard_bowheer` (`dashboard_bowheer`);
ALTER TABLE `tb_po_term` ADD KEY `idx_tb_po_term_target_week` (`target_year`, `target_week`);
ALTER TABLE `tb_po_term` ADD KEY `idx_tb_po_term_status` (`target_status`);
ALTER TABLE `tb_po_term_claim` ADD KEY `idx_tb_po_term_claim_allocation` (`id_allocation`);

-- =========================================================
-- 6. OPTIONAL: EMPTY PO MONITOR STANDALONE DATA
-- Uncomment hanya kalau mau kosongkan data PO Monitor.
-- Tidak menghapus tb_bowheer_po dan tidak menyentuh PO_MyRep.
-- =========================================================

-- SET FOREIGN_KEY_CHECKS = 0;
-- TRUNCATE TABLE `tb_po_term_invoice`;
-- TRUNCATE TABLE `tb_po_term_allocation`;
-- TRUNCATE TABLE `tb_po_term_claim`;
-- TRUNCATE TABLE `tb_po_term`;
-- TRUNCATE TABLE `tb_po_amend`;
-- TRUNCATE TABLE `tb_po_target_pipeline`;
-- TRUNCATE TABLE `tb_po_dashboard_cache`;
-- TRUNCATE TABLE `tb_po_import_batch`;
-- TRUNCATE TABLE `tb_po`;
-- SET FOREIGN_KEY_CHECKS = 1;
