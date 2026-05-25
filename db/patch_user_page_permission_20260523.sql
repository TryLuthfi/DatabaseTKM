-- Patch: User Detail Access (Per Page) untuk SuperAdmin_UserAccess
-- Date : 2026-05-23

CREATE TABLE IF NOT EXISTS `tb_user_page_permission` (
  `id_permission` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_user` INT(11) NOT NULL,
  `module_key` VARCHAR(50) NOT NULL,
  `page_key` VARCHAR(100) NOT NULL,
  `action_key` VARCHAR(40) NOT NULL,
  `is_allowed` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `effective_start` DATETIME NULL DEFAULT NULL,
  `effective_end` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_permission`),
  UNIQUE KEY `uk_user_page_permission_unique` (`id_user`, `page_key`, `action_key`),
  KEY `idx_user_page_permission_lookup` (`id_user`, `module_key`, `page_key`, `action_key`, `is_active`),
  KEY `idx_user_page_permission_window` (`effective_start`, `effective_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

