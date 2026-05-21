-- Master matrix untuk hak akses + line approval MyRep
-- Aman dijalankan berulang (idempotent basic)

CREATE TABLE IF NOT EXISTS `tb_myrep_role_permission` (
  `id_permission` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_key` VARCHAR(100) NOT NULL,
  `action_key` VARCHAR(50) NOT NULL,
  `role_key` VARCHAR(50) NOT NULL,
  `is_allowed` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `effective_start` DATETIME NULL,
  `effective_end` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_permission`),
  UNIQUE KEY `uniq_myrep_role_permission` (`page_key`, `action_key`, `role_key`),
  KEY `idx_myrep_role_permission_page` (`page_key`),
  KEY `idx_myrep_role_permission_role` (`role_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tb_myrep_approval_line` (
  `id_line` BIGINT UNSIGNED NOT NULL AUTO_INCREMENokeT,
  `page_key` VARCHAR(100) NOT NULL,
  `action_key` VARCHAR(50) NOT NULL,
  `step_no` INT NOT NULL,
  `target_type` ENUM('CITY_ROLE','FIXED_USER') NOT NULL DEFAULT 'CITY_ROLE',
  `target_role` VARCHAR(50) NULL,
  `target_user_id` BIGINT NULL,
  `fallback_type` ENUM('CITY_ROLE','FIXED_USER') NULL,
  `fallback_role` VARCHAR(50) NULL,
  `fallback_user_id` BIGINT NULL,
  `is_final_approver` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `effective_start` DATETIME NULL,
  `effective_end` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_line`),
  UNIQUE KEY `uniq_myrep_approval_line` (`page_key`, `action_key`, `step_no`),
  KEY `idx_myrep_approval_line_target_role` (`target_role`),
  KEY `idx_myrep_approval_line_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

