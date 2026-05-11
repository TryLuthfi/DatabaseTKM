-- Patch: tambah kolom jumlah team dan jumlah orang aktivitas harian
-- Date: 2026-05-11

ALTER TABLE `tb_myrep_impl_daily_activity`
ADD COLUMN `team_count` int(11) NOT NULL DEFAULT 0 AFTER `scope_type`,
ADD COLUMN `worker_count` int(11) NOT NULL DEFAULT 0 AFTER `team_count`;
