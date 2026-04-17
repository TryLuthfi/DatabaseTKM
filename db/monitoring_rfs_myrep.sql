CREATE TABLE IF NOT EXISTS `tb_rfs_myrep_monthly_target` (
  `id_target` INT NOT NULL AUTO_INCREMENT,
  `year_num` SMALLINT NOT NULL,
  `month_num` TINYINT NOT NULL,
  `city_name` VARCHAR(100) NOT NULL,
  `target_myrep` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `realization_myrep` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `target_rkap` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `updated_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_target`),
  UNIQUE KEY `uniq_rfs_myrep_target_period_city` (`year_num`,`month_num`,`city_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_rfs_myrep_cluster` (
  `id_cluster` INT NOT NULL AUTO_INCREMENT,
  `city_name` VARCHAR(100) NOT NULL,
  `cluster_name` VARCHAR(150) NOT NULL,
  `pic_1` VARCHAR(100) DEFAULT NULL,
  `pic_2` VARCHAR(100) DEFAULT NULL,
  `homepass` INT NOT NULL DEFAULT 0,
  `created_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cluster`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_rfs_myrep_cluster_plan` (
  `id_plan` INT NOT NULL AUTO_INCREMENT,
  `cluster_id` INT NOT NULL,
  `year_num` SMALLINT NOT NULL,
  `month_num` TINYINT NOT NULL,
  `optimistic_target` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `updated_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_plan`),
  UNIQUE KEY `uniq_rfs_myrep_cluster_plan` (`cluster_id`,`year_num`,`month_num`),
  CONSTRAINT `fk_rfs_myrep_cluster_plan_cluster` FOREIGN KEY (`cluster_id`) REFERENCES `tb_rfs_myrep_cluster` (`id_cluster`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_rfs_myrep_claim` (
  `id_claim` INT NOT NULL AUTO_INCREMENT,
  `cluster_id` INT NOT NULL,
  `claim_year` SMALLINT NOT NULL,
  `claim_month` TINYINT NOT NULL,
  `claim_date` DATE NOT NULL,
  `claim_qty` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `photo_path` VARCHAR(255) NOT NULL,
  `claim_note` TEXT DEFAULT NULL,
  `status_claim` ENUM('WAITING APPROVAL RPM','WAITING APPROVAL HO','APPROVED','REJECTED') NOT NULL DEFAULT 'WAITING APPROVAL HO',
  `status_rfs` ENUM('PARTIAL','FULL RFS') DEFAULT NULL,
  `rpm_approval_status` ENUM('WAITING APPROVAL RPM','APPROVED','REJECTED','SKIPPED') NOT NULL DEFAULT 'SKIPPED',
  `rpm_approval_note` TEXT DEFAULT NULL,
  `approval_note` TEXT DEFAULT NULL,
  `submitted_by` INT DEFAULT NULL,
  `rpm_approved_by` INT DEFAULT NULL,
  `rpm_approved_at` DATETIME DEFAULT NULL,
  `approved_by` INT DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_claim`),
  KEY `idx_rfs_myrep_claim_cluster` (`cluster_id`),
  KEY `idx_rfs_myrep_claim_period` (`claim_year`,`claim_month`,`status_claim`),
  CONSTRAINT `fk_rfs_myrep_claim_cluster` FOREIGN KEY (`cluster_id`) REFERENCES `tb_rfs_myrep_cluster` (`id_cluster`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tb_rfs_myrep_monthly_target` (`year_num`, `month_num`, `city_name`, `target_myrep`, `realization_myrep`, `target_rkap`)
VALUES
  (2026, 4, 'MALANG', 2000, 1000, 1500),
  (2026, 4, 'BLITAR', 2500, 500, 700),
  (2026, 5, 'MALANG', 1000, 0, 0),
  (2026, 5, 'BLITAR', 500, 0, 0),
  (2026, 6, 'MALANG', 500, 0, 0),
  (2026, 6, 'BLITAR', 3000, 0, 0);

INSERT INTO `tb_rfs_myrep_cluster` (`city_name`, `cluster_name`, `pic_1`, `pic_2`, `homepass`)
VALUES
  ('MALANG', 'Cluster A', 'Hendri', 'Yono', 1000),
  ('BLITAR', 'Cluster B', 'Agung', 'Hapsah', 800);

INSERT INTO `tb_rfs_myrep_cluster_plan` (`cluster_id`, `year_num`, `month_num`, `optimistic_target`)
VALUES
  (1, 2026, 4, 1500),
  (2, 2026, 4, 2000);
