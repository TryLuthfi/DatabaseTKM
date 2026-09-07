-- Delayed Telegram notification queue for MyRep upload notifications.

CREATE TABLE IF NOT EXISTS `tb_myrep_telegram_notification_queue` (
  `id_queue` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `module_name` VARCHAR(100) NOT NULL,
  `event_name` VARCHAR(100) NOT NULL,
  `cluster_ref_id` INT UNSIGNED NOT NULL,
  `group_key` VARCHAR(50) NOT NULL DEFAULT '',
  `group_label` VARCHAR(150) NULL,
  `document_labels` TEXT NULL,
  `payload_json` TEXT NULL,
  `status_queue` VARCHAR(20) NOT NULL DEFAULT 'PENDING',
  `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `scheduled_at` DATETIME NOT NULL,
  `sent_at` DATETIME NULL,
  `last_error` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id_queue`),
  KEY `idx_myrep_telegram_queue_due` (`status_queue`, `scheduled_at`),
  KEY `idx_myrep_telegram_queue_group` (`module_name`, `event_name`, `cluster_ref_id`, `group_key`, `status_queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
