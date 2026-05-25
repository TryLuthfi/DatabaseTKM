-- Patch: tabel token reset password untuk flow first-login by email link
-- Date: 2026-05-25

CREATE TABLE IF NOT EXISTS `tb_user_password_reset` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(128) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tb_user_password_reset_token` (`token`),
    KEY `idx_tb_user_password_reset_user` (`user_id`),
    KEY `idx_tb_user_password_reset_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

