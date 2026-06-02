CREATE TABLE IF NOT EXISTS `tb_myrep_scope_requirement` (
  `id_scope_requirement` INT(11) NOT NULL AUTO_INCREMENT,
  `id_myrep_cluster` INT(11) NOT NULL,
  `scope_type` ENUM('CLUSTER','SUBFEEDER') NOT NULL DEFAULT 'SUBFEEDER',
  `requirement_status` ENUM('REQUIRED','NOT_REQUIRED_PENDING','NOT_REQUIRED_APPROVED','NOT_REQUIRED_REJECTED') NOT NULL DEFAULT 'REQUIRED',
  `request_remark` TEXT NULL,
  `review_remark` TEXT NULL,
  `requested_by` INT(11) NULL,
  `requested_at` DATETIME NULL,
  `reviewed_by` INT(11) NULL,
  `reviewed_at` DATETIME NULL,
  `reopened_by` INT(11) NULL,
  `reopened_at` DATETIME NULL,
  `reopen_remark` TEXT NULL,
  `created_by` INT(11) NULL,
  `updated_by` INT(11) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_scope_requirement`),
  UNIQUE KEY `uniq_myrep_scope_requirement_cluster_scope` (`id_myrep_cluster`,`scope_type`),
  KEY `idx_myrep_scope_requirement_status` (`requirement_status`),
  KEY `idx_myrep_scope_requirement_scope` (`scope_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_myrep_scope_requirement_log` (
  `id_scope_requirement_log` INT(11) NOT NULL AUTO_INCREMENT,
  `id_scope_requirement` INT(11) NOT NULL,
  `id_myrep_cluster` INT(11) NOT NULL,
  `scope_type` ENUM('CLUSTER','SUBFEEDER') NOT NULL DEFAULT 'SUBFEEDER',
  `action_type` VARCHAR(50) NOT NULL,
  `status_after` VARCHAR(50) NOT NULL,
  `remark` TEXT NULL,
  `action_by` INT(11) NULL,
  `action_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_scope_requirement_log`),
  KEY `idx_myrep_scope_requirement_log_req` (`id_scope_requirement`),
  KEY `idx_myrep_scope_requirement_log_cluster` (`id_myrep_cluster`,`scope_type`),
  KEY `idx_myrep_scope_requirement_log_action_at` (`action_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
