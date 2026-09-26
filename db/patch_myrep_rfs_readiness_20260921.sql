CREATE TABLE IF NOT EXISTS `tb_myrep_rfs_readiness_period` (
  `id_period` INT NOT NULL AUTO_INCREMENT,
  `year_num` SMALLINT NOT NULL,
  `month_num` TINYINT NOT NULL,
  `meeting_date` DATE DEFAULT NULL,
  `status_period` ENUM('DRAFT','LOCKED','CLOSED') NOT NULL DEFAULT 'DRAFT',
  `remark` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `locked_by` INT DEFAULT NULL,
  `locked_at` DATETIME DEFAULT NULL,
  `closed_by` INT DEFAULT NULL,
  `closed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_period`),
  UNIQUE KEY `uniq_myrep_rfs_readiness_period` (`year_num`,`month_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_myrep_rfs_readiness_item` (
  `id_item` INT NOT NULL AUTO_INCREMENT,
  `id_period` INT NOT NULL,
  `id_myrep_cluster` INT NOT NULL,
  `source_type` ENUM('NEW','CARRY_OVER','LATE_ADDITION','SHIFTED_IN') NOT NULL DEFAULT 'NEW',
  `source_item_id` INT DEFAULT NULL,
  `homepass_drm_snapshot` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `baseline_week` TINYINT DEFAULT NULL,
  `baseline_planned_rfs_date` DATE DEFAULT NULL,
  `current_week` TINYINT DEFAULT NULL,
  `current_planned_rfs_date` DATE DEFAULT NULL,
  `material_status` ENUM('READY','NOT READY') NOT NULL DEFAULT 'NOT READY',
  `olt_status` ENUM('READY','NOT READY') NOT NULL DEFAULT 'NOT READY',
  `tenaga_kerja_status` ENUM('READY','NOT READY') NOT NULL DEFAULT 'NOT READY',
  `operasional_status` ENUM('READY','NOT READY') NOT NULL DEFAULT 'NOT READY',
  `accessories_status` ENUM('READY','NOT READY') NOT NULL DEFAULT 'NOT READY',
  `morep_owner` VARCHAR(150) DEFAULT NULL,
  `tkm_owner` VARCHAR(150) DEFAULT NULL,
  `priority_level` ENUM('PRIORITAS 1','PRIORITAS 2') NOT NULL DEFAULT 'PRIORITAS 2',
  `final_status` ENUM('OPEN','RFS','CARRY_OVER','SHIFTED_OUT','IMPOSSIBLE','DROPPED','CANCELLED_BY_LATE_RFS') NOT NULL DEFAULT 'OPEN',
  `actual_rfs_date` DATE DEFAULT NULL,
  `checklist_completed_at` DATETIME DEFAULT NULL,
  `checklist_completed_by` INT DEFAULT NULL,
  `remark` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `updated_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_item`),
  UNIQUE KEY `uniq_myrep_rfs_readiness_item` (`id_period`,`id_myrep_cluster`),
  KEY `idx_myrep_rfs_readiness_item_cluster` (`id_myrep_cluster`),
  KEY `idx_myrep_rfs_readiness_item_source` (`source_item_id`),
  CONSTRAINT `fk_myrep_rfs_readiness_item_period` FOREIGN KEY (`id_period`) REFERENCES `tb_myrep_rfs_readiness_period` (`id_period`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tb_myrep_rfs_readiness_item`
  ADD COLUMN IF NOT EXISTS `morep_owner` VARCHAR(150) DEFAULT NULL AFTER `accessories_status`,
  ADD COLUMN IF NOT EXISTS `tkm_owner` VARCHAR(150) DEFAULT NULL AFTER `morep_owner`;

ALTER TABLE `tb_myrep_rfs_readiness_item`
  ADD COLUMN IF NOT EXISTS `checklist_completed_at` DATETIME DEFAULT NULL AFTER `actual_rfs_date`,
  ADD COLUMN IF NOT EXISTS `checklist_completed_by` INT DEFAULT NULL AFTER `checklist_completed_at`;

UPDATE `tb_myrep_rfs_readiness_item`
SET
  `material_status` = IF(`material_status` = 'READY', 'READY', 'NOT READY'),
  `olt_status` = IF(`olt_status` = 'READY', 'READY', 'NOT READY'),
  `tenaga_kerja_status` = IF(`tenaga_kerja_status` = 'READY', 'READY', 'NOT READY'),
  `operasional_status` = IF(`operasional_status` = 'READY', 'READY', 'NOT READY'),
  `accessories_status` = IF(`accessories_status` = 'READY', 'READY', 'NOT READY');

ALTER TABLE `tb_myrep_rfs_readiness_item`
  MODIFY COLUMN `material_status` ENUM('READY','NOT READY') NOT NULL DEFAULT 'NOT READY',
  MODIFY COLUMN `olt_status` ENUM('READY','NOT READY') NOT NULL DEFAULT 'NOT READY',
  MODIFY COLUMN `tenaga_kerja_status` ENUM('READY','NOT READY') NOT NULL DEFAULT 'NOT READY',
  MODIFY COLUMN `operasional_status` ENUM('READY','NOT READY') NOT NULL DEFAULT 'NOT READY',
  MODIFY COLUMN `accessories_status` ENUM('READY','NOT READY') NOT NULL DEFAULT 'NOT READY';

CREATE TABLE IF NOT EXISTS `tb_myrep_rfs_readiness_change_request` (
  `id_change_request` INT NOT NULL AUTO_INCREMENT,
  `id_item` INT NOT NULL,
  `request_type` ENUM('READINESS_CHANGE','WEEK_SHIFT','BOTH') NOT NULL DEFAULT 'BOTH',
  `old_week` TINYINT DEFAULT NULL,
  `new_week` TINYINT DEFAULT NULL,
  `old_planned_rfs_date` DATE DEFAULT NULL,
  `new_planned_rfs_date` DATE DEFAULT NULL,
  `old_material_status` VARCHAR(20) DEFAULT NULL,
  `new_material_status` VARCHAR(20) DEFAULT NULL,
  `old_olt_status` VARCHAR(20) DEFAULT NULL,
  `new_olt_status` VARCHAR(20) DEFAULT NULL,
  `old_tenaga_kerja_status` VARCHAR(20) DEFAULT NULL,
  `new_tenaga_kerja_status` VARCHAR(20) DEFAULT NULL,
  `old_operasional_status` VARCHAR(20) DEFAULT NULL,
  `new_operasional_status` VARCHAR(20) DEFAULT NULL,
  `old_accessories_status` VARCHAR(20) DEFAULT NULL,
  `new_accessories_status` VARCHAR(20) DEFAULT NULL,
  `reason_category` VARCHAR(80) DEFAULT NULL,
  `remark` TEXT NOT NULL,
  `status_request` ENUM('WAITING_SM','WAITING_RPM','WAITING_RFS_HO','APPROVED','REJECTED') NOT NULL DEFAULT 'WAITING_SM',
  `requested_by` INT DEFAULT NULL,
  `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sm_approved_by` INT DEFAULT NULL,
  `sm_approved_at` DATETIME DEFAULT NULL,
  `sm_approval_note` TEXT DEFAULT NULL,
  `rpm_approved_by` INT DEFAULT NULL,
  `rpm_approved_at` DATETIME DEFAULT NULL,
  `rpm_approval_note` TEXT DEFAULT NULL,
  `rfs_ho_approved_by` INT DEFAULT NULL,
  `rfs_ho_approved_at` DATETIME DEFAULT NULL,
  `rfs_ho_approval_note` TEXT DEFAULT NULL,
  `rejected_by` INT DEFAULT NULL,
  `rejected_at` DATETIME DEFAULT NULL,
  `rejection_note` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_change_request`),
  KEY `idx_myrep_rfs_readiness_cr_item` (`id_item`),
  KEY `idx_myrep_rfs_readiness_cr_status` (`status_request`),
  CONSTRAINT `fk_myrep_rfs_readiness_cr_item` FOREIGN KEY (`id_item`) REFERENCES `tb_myrep_rfs_readiness_item` (`id_item`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_myrep_rfs_readiness_evidence` (
  `id_evidence` INT NOT NULL AUTO_INCREMENT,
  `id_change_request` INT NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `uploaded_by` INT DEFAULT NULL,
  `uploaded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_evidence`),
  KEY `idx_myrep_rfs_readiness_evidence_cr` (`id_change_request`),
  CONSTRAINT `fk_myrep_rfs_readiness_evidence_cr` FOREIGN KEY (`id_change_request`) REFERENCES `tb_myrep_rfs_readiness_change_request` (`id_change_request`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_myrep_rfs_readiness_history` (
  `id_history` INT NOT NULL AUTO_INCREMENT,
  `id_item` INT NOT NULL,
  `id_change_request` INT DEFAULT NULL,
  `event_type` VARCHAR(60) NOT NULL,
  `old_payload` TEXT DEFAULT NULL,
  `new_payload` TEXT DEFAULT NULL,
  `remark` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_history`),
  KEY `idx_myrep_rfs_readiness_history_item` (`id_item`),
  KEY `idx_myrep_rfs_readiness_history_cr` (`id_change_request`),
  CONSTRAINT `fk_myrep_rfs_readiness_history_item` FOREIGN KEY (`id_item`) REFERENCES `tb_myrep_rfs_readiness_item` (`id_item`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_myrep_rfs_readiness_notification` (
  `id_notification` INT NOT NULL AUTO_INCREMENT,
  `id_change_request` INT DEFAULT NULL,
  `target_user_id` INT NOT NULL,
  `target_role` VARCHAR(40) DEFAULT NULL,
  `message` VARCHAR(255) NOT NULL,
  `status_notification` ENUM('UNREAD','READ') NOT NULL DEFAULT 'UNREAD',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_notification`),
  KEY `idx_myrep_rfs_readiness_notif_user` (`target_user_id`,`status_notification`),
  KEY `idx_myrep_rfs_readiness_notif_cr` (`id_change_request`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tb_myrep_role_permission` (`page_key`,`action_key`,`role_key`,`is_allowed`,`is_active`)
SELECT 'RFS_Readiness_MyRep', x.action_key, x.role_key, 1, 1
FROM (
  SELECT 'VIEW' AS action_key, 'SPV_AREA' AS role_key UNION ALL
  SELECT 'VIEW', 'SM_AREA' UNION ALL
  SELECT 'VIEW', 'RPM_AREA' UNION ALL
  SELECT 'VIEW', 'RFS_HO' UNION ALL
  SELECT 'TAMBAH', 'RFS_HO' UNION ALL
  SELECT 'EDIT', 'RFS_HO' UNION ALL
  SELECT 'EDIT', 'SPV_AREA' UNION ALL
  SELECT 'APPROVAL', 'SM_AREA' UNION ALL
  SELECT 'APPROVAL', 'RPM_AREA' UNION ALL
  SELECT 'APPROVAL', 'RFS_HO'
) x
WHERE EXISTS (
  SELECT 1 FROM INFORMATION_SCHEMA.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_myrep_role_permission'
)
AND NOT EXISTS (
  SELECT 1 FROM `tb_myrep_role_permission` p
  WHERE p.page_key = 'RFS_Readiness_MyRep'
    AND p.action_key = x.action_key
    AND p.role_key = x.role_key
);
