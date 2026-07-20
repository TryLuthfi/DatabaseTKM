SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Link NY PO pipeline ke PO Monitor saat user menambahkan PO baru.
-- Jalankan di production sebelum memakai kolom NY PO REF jika auto-schema aplikasi tidak punya izin ALTER.

ALTER TABLE `tb_po_target_pipeline`
  ADD COLUMN IF NOT EXISTS `linked_id_po` int(11) DEFAULT NULL AFTER `source_hash`,
  ADD COLUMN IF NOT EXISTS `linked_po_number` varchar(100) DEFAULT NULL AFTER `linked_id_po`,
  ADD COLUMN IF NOT EXISTS `pipeline_status` varchar(30) DEFAULT 'OPEN' AFTER `linked_po_number`,
  ADD COLUMN IF NOT EXISTS `converted_at` datetime DEFAULT NULL AFTER `pipeline_status`,
  ADD COLUMN IF NOT EXISTS `converted_by` int(11) DEFAULT NULL AFTER `converted_at`;

ALTER TABLE `tb_po_target_pipeline`
  ADD INDEX IF NOT EXISTS `idx_tb_po_target_pipeline_linked` (`linked_id_po`);
