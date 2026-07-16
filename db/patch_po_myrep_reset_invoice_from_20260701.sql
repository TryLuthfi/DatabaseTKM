SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- =========================================================
-- RESET PO_MYREP INVOICE FROM 1 JULI 2026
-- Tujuan:
-- 1. Backup termin PO_MyRep yang sudah punya invoice_date mulai 2026-07-01.
-- 2. Kembalikan termin tersebut ke READY BILLING / NY Invoice.
-- 3. Hitung ulang status header PO_MyRep.
--
-- Setelah query ini dijalankan, user bisa upload ulang invoice dari 1 Juli 2026
-- sampai sekarang. PO_Monitor akan mengikuti lagi lewat auto sync PO_MyRep.
-- =========================================================

SET @reset_cutoff_date = '2026-07-01';
SET @reset_user_id = 9999;

CREATE TABLE IF NOT EXISTS `tb_myrep_po_termin_reset_invoice_20260701_backup` (
  `id_backup` int(11) NOT NULL AUTO_INCREMENT,
  `backup_at` datetime NOT NULL DEFAULT current_timestamp(),
  `id_po_termin` int(11) NOT NULL,
  `id_po_header` int(11) NOT NULL,
  `po_number` varchar(150) DEFAULT NULL,
  `termin_no` int(11) DEFAULT NULL,
  `termin_value` decimal(18,2) DEFAULT NULL,
  `status_termin` varchar(50) DEFAULT NULL,
  `invoice_number` varchar(150) DEFAULT NULL,
  `invoice_value` decimal(18,2) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `bast_date` date DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `remark_termin` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_backup`),
  KEY `idx_myrep_reset_invoice_termin` (`id_po_termin`),
  KEY `idx_myrep_reset_invoice_header` (`id_po_header`),
  KEY `idx_myrep_reset_invoice_date` (`invoice_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TEMPORARY TABLE IF EXISTS `tmp_myrep_reset_invoice_20260701`;
CREATE TEMPORARY TABLE `tmp_myrep_reset_invoice_20260701` AS
SELECT DISTINCT
  t.`id_po_termin`,
  t.`id_po_header`
FROM `tb_myrep_po_termin` t
WHERE t.`invoice_date` BETWEEN @reset_cutoff_date AND CURDATE();

INSERT INTO `tb_myrep_po_termin_reset_invoice_20260701_backup` (
  `id_po_termin`,
  `id_po_header`,
  `po_number`,
  `termin_no`,
  `termin_value`,
  `status_termin`,
  `invoice_number`,
  `invoice_value`,
  `invoice_date`,
  `bast_date`,
  `payment_date`,
  `remark_termin`,
  `created_by`,
  `updated_by`,
  `created_at`,
  `updated_at`
)
SELECT
  t.`id_po_termin`,
  t.`id_po_header`,
  p.`po_number`,
  t.`termin_no`,
  t.`termin_value`,
  t.`status_termin`,
  t.`invoice_number`,
  t.`invoice_value`,
  t.`invoice_date`,
  t.`bast_date`,
  t.`payment_date`,
  t.`remark_termin`,
  t.`created_by`,
  t.`updated_by`,
  t.`created_at`,
  t.`updated_at`
FROM `tb_myrep_po_termin` t
JOIN `tmp_myrep_reset_invoice_20260701` x
  ON x.`id_po_termin` = t.`id_po_termin`
LEFT JOIN `tb_myrep_po_header` p
  ON p.`id_po_header` = t.`id_po_header`;

UPDATE `tb_myrep_po_termin` t
JOIN `tmp_myrep_reset_invoice_20260701` x
  ON x.`id_po_termin` = t.`id_po_termin`
SET
  t.`status_termin` = 'READY BILLING',
  t.`invoice_number` = NULL,
  t.`invoice_value` = NULL,
  t.`invoice_date` = NULL,
  t.`bast_date` = NULL,
  t.`payment_date` = NULL,
  t.`updated_by` = @reset_user_id,
  t.`updated_at` = NOW();

UPDATE `tb_myrep_po_header` p
JOIN (
  SELECT
    t.`id_po_header`,
    COUNT(*) AS total_term,
    SUM(CASE WHEN t.`status_termin` = 'PAID' THEN 1 ELSE 0 END) AS paid_term,
    SUM(CASE WHEN t.`status_termin` IN ('BILLED', 'PAID') THEN 1 ELSE 0 END) AS invoiced_term,
    SUM(CASE WHEN t.`status_termin` IN ('READY BILLING', 'BILLED', 'PAID') THEN 1 ELSE 0 END) AS ready_term
  FROM `tb_myrep_po_termin` t
  WHERE t.`id_po_header` IN (
    SELECT DISTINCT `id_po_header`
    FROM `tmp_myrep_reset_invoice_20260701`
  )
  GROUP BY t.`id_po_header`
) s
  ON s.`id_po_header` = p.`id_po_header`
SET
  p.`status_po` = CASE
    WHEN s.`total_term` > 0 AND s.`paid_term` = s.`total_term` THEN 'FULLY PAID'
    WHEN s.`invoiced_term` > 0 THEN 'PARTIAL PAYMENT'
    WHEN s.`ready_term` > 0 THEN 'ISSUED'
    ELSE 'NOT ISSUED'
  END,
  p.`updated_by` = @reset_user_id,
  p.`updated_at` = NOW();

SELECT
  COUNT(*) AS total_reset_termin
FROM `tmp_myrep_reset_invoice_20260701`;

DROP TEMPORARY TABLE IF EXISTS `tmp_myrep_reset_invoice_20260701`;
