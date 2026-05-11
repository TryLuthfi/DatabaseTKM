-- Patch: Progress Harian Aktivitas Implementasi BOQ MyRep
-- Date: 2026-05-11

CREATE TABLE IF NOT EXISTS `tb_myrep_impl_daily_activity` (
  `id_daily_activity` int(11) NOT NULL AUTO_INCREMENT,
  `id_myrep_cluster` int(11) NOT NULL,
  `activity_date` date NOT NULL,
  `activity_code` varchar(64) NOT NULL,
  `activity_name` varchar(150) NOT NULL,
  `activity_detail` varchar(150) DEFAULT NULL,
  `boq_type` varchar(80) NOT NULL,
  `scope_type` enum('CLUSTER','SUBFEEDER') NOT NULL DEFAULT 'CLUSTER',
  `team_count` int(11) NOT NULL DEFAULT 0,
  `worker_count` int(11) NOT NULL DEFAULT 0,
  `qty_activity` decimal(18,2) NOT NULL DEFAULT 0.00,
  `unit_activity` varchar(32) DEFAULT NULL,
  `remark_activity` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_daily_activity`),
  KEY `idx_impl_daily_activity_cluster` (`id_myrep_cluster`),
  KEY `idx_impl_daily_activity_date` (`activity_date`),
  KEY `idx_impl_daily_activity_scope` (`scope_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tb_myrep_impl_daily_activity_photo` (
  `id_activity_photo` int(11) NOT NULL AUTO_INCREMENT,
  `id_daily_activity` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_activity_photo`),
  KEY `idx_impl_activity_photo_activity` (`id_daily_activity`),
  CONSTRAINT `fk_impl_activity_photo_activity` FOREIGN KEY (`id_daily_activity`) REFERENCES `tb_myrep_impl_daily_activity` (`id_daily_activity`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
