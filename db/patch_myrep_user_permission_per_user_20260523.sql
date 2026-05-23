-- Patch: User-specific access matrix for MyRep pages/actions
-- Date : 2026-05-23
-- Usage: jalankan di database aktif sebelum memakai halaman SuperAdmin_UserAccess (mode matrix)

CREATE TABLE IF NOT EXISTS `tb_myrep_user_permission` (
  `id_user_permission` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_user` BIGINT(20) UNSIGNED NOT NULL,
  `page_key` VARCHAR(100) NOT NULL,
  `action_key` VARCHAR(50) NOT NULL,
  `is_allowed` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `effective_start` DATETIME NULL DEFAULT NULL,
  `effective_end` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user_permission`),
  UNIQUE KEY `uk_myrep_user_permission_unique` (`id_user`,`page_key`,`action_key`),
  KEY `idx_myrep_user_permission_lookup` (`id_user`,`page_key`,`action_key`,`is_active`),
  KEY `idx_myrep_user_permission_window` (`effective_start`,`effective_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

