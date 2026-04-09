-- =========================================================
-- PO MONITORING MODULE SCHEMA + SAFE DUMMY DATA
-- VERSI AMAN UNTUK PHPMYADMIN
-- =========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- =========================================================
-- DROP TABLE JIKA MAU FRESH INSTALL
-- =========================================================
-- HAPUS COMMENT DI BAWAH INI KALAU MAU RESET TOTAL
-- DROP TABLE IF EXISTS `tb_po_term_invoice`;
-- DROP TABLE IF EXISTS `tb_po_term`;
-- DROP TABLE IF EXISTS `tb_po_amend`;
-- DROP TABLE IF EXISTS `tb_term_master_split`;
-- DROP TABLE IF EXISTS `tb_term_master`;
-- DROP TABLE IF EXISTS `tb_po`;

-- =========================================================
-- 1. TABLES
-- =========================================================

CREATE TABLE IF NOT EXISTS `tb_po` (
  `id_po` INT NOT NULL AUTO_INCREMENT,
  `po_number` VARCHAR(100) NOT NULL,
  `po_date` DATE DEFAULT NULL,
  `id_bowheer` INT DEFAULT NULL,
  `total_value` DECIMAL(18,2) DEFAULT 0.00,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_by` INT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_po`),
  UNIQUE KEY `uk_tb_po_po_number` (`po_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_po_amend` (
  `id_amend` INT NOT NULL AUTO_INCREMENT,
  `id_po` INT NOT NULL,
  `amend_no` TINYINT NOT NULL,
  `release_value` DECIMAL(18,2) NOT NULL,
  `release_date` DATE DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_amend`),
  UNIQUE KEY `uk_tb_po_amend_po_amendno` (`id_po`,`amend_no`),
  KEY `idx_tb_po_amend_id_po` (`id_po`),
  CONSTRAINT `fk_tb_po_amend_id_po`
    FOREIGN KEY (`id_po`) REFERENCES `tb_po` (`id_po`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_po_term` (
  `id_term` INT NOT NULL AUTO_INCREMENT,
  `id_po` INT NOT NULL,
  `id_amend` INT DEFAULT NULL,
  `term_index` TINYINT NOT NULL,
  `percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `value` DECIMAL(18,2) DEFAULT 0.00,
  `due_date` DATE DEFAULT NULL,
  `sla_days` INT DEFAULT NULL,
  PRIMARY KEY (`id_term`),
  KEY `idx_tb_po_term_id_po` (`id_po`),
  KEY `idx_tb_po_term_id_amend` (`id_amend`),
  KEY `idx_tb_po_term_term_index` (`term_index`),
  CONSTRAINT `fk_tb_po_term_id_po`
    FOREIGN KEY (`id_po`) REFERENCES `tb_po` (`id_po`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tb_po_term_id_amend`
    FOREIGN KEY (`id_amend`) REFERENCES `tb_po_amend` (`id_amend`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_po_term_invoice` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_term` INT NOT NULL,
  `id_billing` INT NOT NULL,
  `invoice_amount` DECIMAL(18,2) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tb_po_term_invoice_id_term` (`id_term`),
  KEY `idx_tb_po_term_invoice_id_billing` (`id_billing`),
  CONSTRAINT `fk_tb_po_term_invoice_id_term`
    FOREIGN KEY (`id_term`) REFERENCES `tb_po_term` (`id_term`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tb_po_term_invoice_id_billing`
    FOREIGN KEY (`id_billing`) REFERENCES `tb_billingpayment` (`id_billing`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_term_master` (
  `id_master` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_master`),
  UNIQUE KEY `uk_tb_term_master_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_term_master_split` (
  `id_split` INT NOT NULL AUTO_INCREMENT,
  `id_master` INT NOT NULL,
  `term_index` TINYINT NOT NULL,
  `percent` DECIMAL(5,2) NOT NULL,
  PRIMARY KEY (`id_split`),
  UNIQUE KEY `uk_tb_term_master_split_master_term` (`id_master`,`term_index`),
  KEY `idx_tb_term_master_split_id_master` (`id_master`),
  CONSTRAINT `fk_tb_term_master_split_id_master`
    FOREIGN KEY (`id_master`) REFERENCES `tb_term_master` (`id_master`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 2. MASTER TERM
-- =========================================================

INSERT IGNORE INTO `tb_term_master` (`id_master`, `name`, `description`) VALUES
(1, 'Master 1', 'Single term 100%'),
(2, 'Master 2', 'Two terms 50% - 50%'),
(3, 'Master 3', 'Three terms 40% - 30% - 30%');

INSERT IGNORE INTO `tb_term_master_split` (`id_master`, `term_index`, `percent`) VALUES
(1, 1, 100.00),
(2, 1, 50.00),
(2, 2, 50.00),
(3, 1, 40.00),
(3, 2, 30.00),
(3, 3, 30.00);

-- =========================================================
-- 3. SAMPLE DATA AWAL
-- =========================================================

INSERT IGNORE INTO `tb_po`
(`po_number`, `po_date`, `id_bowheer`, `total_value`, `created_at`, `created_by`, `notes`)
VALUES
('PO-2026-001', '2026-02-10', 1, 100000000.00, '2026-02-10 10:00:00', 1, 'Contoh PO 100jt');

SET @po1 = (SELECT `id_po` FROM `tb_po` WHERE `po_number` = 'PO-2026-001' LIMIT 1);

INSERT IGNORE INTO `tb_po_amend`
(`id_po`, `amend_no`, `release_value`, `release_date`, `notes`)
VALUES
(@po1, 1, 100000000.00, '2026-02-10', 'Rilis awal 100jt');

SET @amend1 = (
  SELECT `id_amend`
  FROM `tb_po_amend`
  WHERE `id_po` = @po1 AND `amend_no` = 1
  LIMIT 1
);

INSERT IGNORE INTO `tb_po_term`
(`id_po`, `id_amend`, `term_index`, `percent`, `value`, `due_date`, `sla_days`)
VALUES
(@po1, @amend1, 1, 50.00, 50000000.00, '2026-03-10', 30),
(@po1, @amend1, 2, 50.00, 50000000.00, '2026-04-10', 30);

INSERT IGNORE INTO `tb_po`
(`po_number`, `po_date`, `id_bowheer`, `total_value`, `created_at`, `created_by`, `notes`)
VALUES
('PO-2026-002', '2026-01-15', 2, 100000000.00, '2026-01-15 09:00:00', 1, 'PO contoh dengan amandemen');

SET @po2 = (SELECT `id_po` FROM `tb_po` WHERE `po_number` = 'PO-2026-002' LIMIT 1);

INSERT IGNORE INTO `tb_po_amend`
(`id_po`, `amend_no`, `release_value`, `release_date`, `notes`)
VALUES
(@po2, 1, 100000000.00, '2026-01-15', 'Initial release 100jt');

SET @amend2 = (
  SELECT `id_amend`
  FROM `tb_po_amend`
  WHERE `id_po` = @po2 AND `amend_no` = 1
  LIMIT 1
);

INSERT IGNORE INTO `tb_po_term`
(`id_po`, `id_amend`, `term_index`, `percent`, `value`, `due_date`, `sla_days`)
VALUES
(@po2, @amend2, 1, 100.00, 100000000.00, '2026-02-15', 30);

INSERT IGNORE INTO `tb_po_amend`
(`id_po`, `amend_no`, `release_value`, `release_date`, `notes`)
VALUES
(@po2, 2, 80000000.00, '2026-03-01', 'Amendment release 80jt');

SET @amend2b = (
  SELECT `id_amend`
  FROM `tb_po_amend`
  WHERE `id_po` = @po2 AND `amend_no` = 2
  LIMIT 1
);

INSERT IGNORE INTO `tb_po_term`
(`id_po`, `id_amend`, `term_index`, `percent`, `value`, `due_date`, `sla_days`)
VALUES
(@po2, @amend2b, 1, 100.00, 80000000.00, '2026-04-01', 30);

-- =========================================================
-- 4. DUMMY DATA BOWHEER 1-18 x 10 PO
-- =========================================================

INSERT IGNORE INTO `tb_po`
(`po_number`, `po_date`, `id_bowheer`, `total_value`, `created_at`, `created_by`, `notes`)
SELECT
  CONCAT('PO-B', LPAD(b.id_bowheer, 2, '0'), '-', LPAD(s.seq, 2, '0')) AS po_number,
  DATE_ADD('2026-01-01', INTERVAL (s.seq + b.id_bowheer) DAY) AS po_date,
  b.id_bowheer,
  (50000000 + (b.id_bowheer * 2000000) + (s.seq * 1500000)) AS total_value,
  NOW(),
  1,
  CONCAT('Dummy PO Bowheer ', b.id_bowheer)
FROM
(
  SELECT 1 AS id_bowheer UNION ALL
  SELECT 2 UNION ALL
  SELECT 3 UNION ALL
  SELECT 4 UNION ALL
  SELECT 5 UNION ALL
  SELECT 6 UNION ALL
  SELECT 7 UNION ALL
  SELECT 8 UNION ALL
  SELECT 9 UNION ALL
  SELECT 10 UNION ALL
  SELECT 11 UNION ALL
  SELECT 12 UNION ALL
  SELECT 13 UNION ALL
  SELECT 14 UNION ALL
  SELECT 15 UNION ALL
  SELECT 16 UNION ALL
  SELECT 17 UNION ALL
  SELECT 18
) b
CROSS JOIN
(
  SELECT 1 AS seq UNION ALL
  SELECT 2 UNION ALL
  SELECT 3 UNION ALL
  SELECT 4 UNION ALL
  SELECT 5 UNION ALL
  SELECT 6 UNION ALL
  SELECT 7 UNION ALL
  SELECT 8 UNION ALL
  SELECT 9 UNION ALL
  SELECT 10
) s;

-- =========================================================
-- 5. AMENDMENT AWAL UNTUK SEMUA DUMMY PO
-- =========================================================

INSERT IGNORE INTO `tb_po_amend`
(`id_po`, `amend_no`, `release_value`, `release_date`, `notes`)
SELECT
  p.id_po,
  1,
  p.total_value,
  p.po_date,
  'Initial'
FROM `tb_po` p
WHERE p.po_number LIKE 'PO-B%';

-- =========================================================
-- 6. AMENDMENT KE-2 UNTUK SEBAGIAN PO
-- =========================================================

INSERT IGNORE INTO `tb_po_amend`
(`id_po`, `amend_no`, `release_value`, `release_date`, `notes`)
SELECT
  p.id_po,
  2,
  ROUND(p.total_value * 0.85, 2),
  DATE_ADD(p.po_date, INTERVAL 30 DAY),
  'Amendment turun'
FROM `tb_po` p
WHERE p.po_number LIKE 'PO-B%'
  AND RIGHT(p.po_number, 2) IN ('04', '08', '10');

-- =========================================================
-- 7. TERM GENERATE
-- RULE:
-- 01,04,07,10 => 1 TERM
-- 02,05,08    => 2 TERM
-- 03,06,09    => 3 TERM
-- =========================================================

-- 1 TERM
INSERT IGNORE INTO `tb_po_term`
(`id_po`, `id_amend`, `term_index`, `percent`, `value`, `due_date`, `sla_days`)
SELECT
  a.id_po,
  a.id_amend,
  1,
  100.00,
  a.release_value,
  DATE_ADD(a.release_date, INTERVAL 30 DAY),
  30
FROM `tb_po_amend` a
JOIN `tb_po` p ON p.id_po = a.id_po
WHERE p.po_number LIKE 'PO-B%'
  AND MOD(CAST(RIGHT(p.po_number, 2) AS UNSIGNED), 3) = 1;

-- 2 TERM
INSERT IGNORE INTO `tb_po_term`
(`id_po`, `id_amend`, `term_index`, `percent`, `value`, `due_date`, `sla_days`)
SELECT
  a.id_po,
  a.id_amend,
  x.term_index,
  50.00,
  ROUND(a.release_value * 0.5, 2),
  DATE_ADD(a.release_date, INTERVAL (30 * x.term_index) DAY),
  30
FROM `tb_po_amend` a
JOIN `tb_po` p ON p.id_po = a.id_po
JOIN (
  SELECT 1 AS term_index
  UNION ALL
  SELECT 2
) x
WHERE p.po_number LIKE 'PO-B%'
  AND MOD(CAST(RIGHT(p.po_number, 2) AS UNSIGNED), 3) = 2;

-- 3 TERM
INSERT IGNORE INTO `tb_po_term`
(`id_po`, `id_amend`, `term_index`, `percent`, `value`, `due_date`, `sla_days`)
SELECT
  a.id_po,
  a.id_amend,
  x.term_index,
  x.percent,
  ROUND(a.release_value * x.percent / 100, 2),
  DATE_ADD(a.release_date, INTERVAL (30 * x.term_index) DAY),
  30
FROM `tb_po_amend` a
JOIN `tb_po` p ON p.id_po = a.id_po
JOIN (
  SELECT 1 AS term_index, 40.00 AS percent
  UNION ALL
  SELECT 2, 30.00
  UNION ALL
  SELECT 3, 30.00
) x
WHERE p.po_number LIKE 'PO-B%'
  AND MOD(CAST(RIGHT(p.po_number, 2) AS UNSIGNED), 3) = 0;

-- =========================================================
-- SELESAI
-- =========================================================
-- Script ini TIDAK insert ke tb_po_term_invoice
-- supaya aman walau tb_billingpayment belum siap.