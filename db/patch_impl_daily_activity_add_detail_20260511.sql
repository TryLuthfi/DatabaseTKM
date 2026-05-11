-- Patch: tambah kolom detail aktivitas harian
-- Date: 2026-05-11

ALTER TABLE `tb_myrep_impl_daily_activity`
ADD COLUMN `activity_detail` varchar(150) DEFAULT NULL AFTER `activity_name`;

