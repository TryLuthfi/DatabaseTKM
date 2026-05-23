-- Patch: pisahkan Master PIC Budget dari user login aplikasi
-- Date : 2026-05-23

CREATE TABLE IF NOT EXISTS `tb_budget_master_pic` (
  `id_budget_pic` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_user` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_budget_pic`),
  UNIQUE KEY `uniq_budget_pic_nama_user` (`nama_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed awal dari histori cashflow (PIC yang pernah dipakai)
INSERT IGNORE INTO `tb_budget_master_pic` (`nama_user`)
SELECT DISTINCT TRIM(`pic_project`) AS `nama_user`
FROM `tb_budget_cashflow_header`
WHERE `pic_project` IS NOT NULL
  AND TRIM(`pic_project`) <> '';

