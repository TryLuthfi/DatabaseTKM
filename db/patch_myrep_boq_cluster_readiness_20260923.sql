CREATE TABLE IF NOT EXISTS `tb_myrep_boq_cluster_readiness` (
  `id_boq_cluster_readiness` INT NOT NULL AUTO_INCREMENT,
  `id_myrep_cluster` INT NOT NULL,
  `cable_status` ENUM('ON PROGRESS','COMPLETE') NOT NULL DEFAULT 'ON PROGRESS',
  `fat_status` ENUM('ON PROGRESS','COMPLETE') NOT NULL DEFAULT 'ON PROGRESS',
  `tiang_status` ENUM('ON PROGRESS','COMPLETE') NOT NULL DEFAULT 'ON PROGRESS',
  `progress_status` ENUM('ON PROGRESS','COMPLETE') NOT NULL DEFAULT 'ON PROGRESS',
  `remark` TEXT DEFAULT NULL,
  `updated_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_boq_cluster_readiness`),
  UNIQUE KEY `uniq_myrep_boq_cluster_readiness_cluster` (`id_myrep_cluster`),
  KEY `idx_myrep_boq_cluster_readiness_status` (`progress_status`),
  CONSTRAINT `fk_myrep_boq_cluster_readiness_cluster` FOREIGN KEY (`id_myrep_cluster`) REFERENCES `tb_myrep_cluster` (`id_myrep_cluster`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_myrep_boq_cluster_readiness_history` (
  `id_boq_cluster_readiness_history` INT NOT NULL AUTO_INCREMENT,
  `id_myrep_cluster` INT NOT NULL,
  `old_payload` TEXT DEFAULT NULL,
  `new_payload` TEXT DEFAULT NULL,
  `remark` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_boq_cluster_readiness_history`),
  KEY `idx_myrep_boq_cluster_readiness_history_cluster` (`id_myrep_cluster`),
  CONSTRAINT `fk_myrep_boq_cluster_readiness_history_cluster` FOREIGN KEY (`id_myrep_cluster`) REFERENCES `tb_myrep_cluster` (`id_myrep_cluster`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
