-- Patch: import nama dari tb_master_user_new ke tb_budget_master_pic
-- Date : 2026-05-23
-- Tujuan:
-- 1) Menambahkan data PIC dari master user
-- 2) Mencegah duplikat berdasarkan LOWER(TRIM(nama))

CREATE TABLE IF NOT EXISTS `tb_budget_master_pic` (
  `id_budget_pic` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_user` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_budget_pic`),
  UNIQUE KEY `uniq_budget_pic_nama_user` (`nama_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tb_budget_master_pic` (`nama_user`)
SELECT src.`nama_user`
FROM (
    SELECT
        MIN(TRIM(u.`nama_karyawan`)) AS `nama_user`,
        LOWER(TRIM(u.`nama_karyawan`)) COLLATE utf8mb4_general_ci AS `norm_name`
    FROM `tb_master_user_new` u
    WHERE u.`nama_karyawan` IS NOT NULL
      AND TRIM(u.`nama_karyawan`) <> ''
    GROUP BY LOWER(TRIM(u.`nama_karyawan`)) COLLATE utf8mb4_general_ci
) src
LEFT JOIN (
    SELECT
        p.`id_budget_pic`,
        LOWER(TRIM(p.`nama_user`)) COLLATE utf8mb4_general_ci AS `norm_name`
    FROM `tb_budget_master_pic` p
) pic
    ON pic.`norm_name` = src.`norm_name`
WHERE pic.`id_budget_pic` IS NULL;
