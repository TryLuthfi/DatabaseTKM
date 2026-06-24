-- Patch: MyRep Mainfeeder full flow
-- Flow: DRM -> IMPLEMENTASI -> ATP -> CHECKLIST -> DONE
-- Notes:
-- - Mainfeeder berdiri sendiri dan tidak memakai dummy cluster.
-- - PO memakai tb_myrep_po_header existing dengan id_mainfeeder + po_type MAINFEEDER.

ALTER TABLE `tb_rfs_myrep_mainfeeder`
  MODIFY COLUMN `id_target` INT NULL,
  MODIFY COLUMN `atp_date` DATE NULL,
  ADD COLUMN IF NOT EXISTS `cluster_code` VARCHAR(100) NULL AFTER `id_target`,
  ADD COLUMN IF NOT EXISTS `current_status` ENUM('DRM','IMPLEMENTASI','ATP','CHECKLIST','DONE') NOT NULL DEFAULT 'DRM' AFTER `mainfeeder_name`,
  ADD COLUMN IF NOT EXISTS `year_num` SMALLINT NULL AFTER `current_status`,
  ADD COLUMN IF NOT EXISTS `month_num` TINYINT NULL AFTER `year_num`,
  ADD COLUMN IF NOT EXISTS `regional_name` VARCHAR(100) NULL AFTER `month_num`,
  ADD COLUMN IF NOT EXISTS `province_name` VARCHAR(100) NULL AFTER `regional_name`,
  ADD COLUMN IF NOT EXISTS `city_name` VARCHAR(100) NULL AFTER `province_name`,
  ADD COLUMN IF NOT EXISTS `team_name` VARCHAR(100) NULL AFTER `city_name`,
  ADD COLUMN IF NOT EXISTS `chief` VARCHAR(100) NULL AFTER `team_name`,
  ADD COLUMN IF NOT EXISTS `rpm` VARCHAR(100) NULL AFTER `chief`,
  ADD COLUMN IF NOT EXISTS `sm` VARCHAR(100) NULL AFTER `rpm`,
  ADD COLUMN IF NOT EXISTS `spv` VARCHAR(100) NULL AFTER `sm`,
  ADD COLUMN IF NOT EXISTS `vendor_name` VARCHAR(150) NULL AFTER `spv`,
  ADD COLUMN IF NOT EXISTS `remark_mainfeeder` TEXT NULL AFTER `length_meter`,
  ADD COLUMN IF NOT EXISTS `email_atp_date` DATE NULL AFTER `atp_date`,
  ADD COLUMN IF NOT EXISTS `status_atp` ENUM('PUNCLIST','DONE') NULL AFTER `email_atp_date`,
  ADD KEY IF NOT EXISTS `idx_myrep_mainfeeder_code` (`cluster_code`),
  ADD KEY IF NOT EXISTS `idx_myrep_mainfeeder_status` (`current_status`),
  ADD KEY IF NOT EXISTS `idx_myrep_mainfeeder_city` (`city_name`);

CREATE TABLE IF NOT EXISTS `tb_myrep_mainfeeder_drm` (
  `id_mainfeeder_drm` BIGINT NOT NULL AUTO_INCREMENT,
  `id_mainfeeder` BIGINT NOT NULL,
  `drm_date` DATE DEFAULT NULL,
  `nama_olt` VARCHAR(255) DEFAULT NULL,
  `status_drm` ENUM('DRAFT','SUBMITTED','ON REVIEW','APPROVED','REJECTED','DONE') NOT NULL DEFAULT 'DRAFT',
  `remark_drm` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `updated_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mainfeeder_drm`),
  UNIQUE KEY `uniq_mainfeeder_drm` (`id_mainfeeder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_myrep_mainfeeder_doc_package` (
  `id_doc_package_mainfeeder_flow` BIGINT NOT NULL AUTO_INCREMENT,
  `id_mainfeeder` BIGINT NOT NULL,
  `flow_type` VARCHAR(40) NOT NULL DEFAULT 'DRM',
  `id_doc_group` INT NOT NULL,
  `status_package` ENUM('NOT STARTED','ON PROGRESS','DONE') NOT NULL DEFAULT 'NOT STARTED',
  `created_by` INT DEFAULT NULL,
  `updated_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_doc_package_mainfeeder_flow`),
  UNIQUE KEY `uniq_mainfeeder_doc_package` (`id_mainfeeder`,`flow_type`,`id_doc_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_myrep_mainfeeder_doc_file` (
  `id_doc_file_mainfeeder_flow` BIGINT NOT NULL AUTO_INCREMENT,
  `id_doc_package_mainfeeder_flow` BIGINT NOT NULL,
  `id_doc_item` INT NOT NULL,
  `file_name` VARCHAR(255) DEFAULT NULL,
  `file_path` VARCHAR(255) DEFAULT NULL,
  `is_document_not_required` TINYINT(1) NOT NULL DEFAULT 0,
  `status_file` ENUM('UPLOADED','APPROVED','REJECTED') DEFAULT NULL,
  `remark` TEXT DEFAULT NULL,
  `uploaded_by` INT DEFAULT NULL,
  `uploaded_at` DATETIME DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `approved_by` INT DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_doc_file_mainfeeder_flow`),
  UNIQUE KEY `uniq_mainfeeder_doc_file` (`id_doc_package_mainfeeder_flow`,`id_doc_item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_myrep_mainfeeder_doc_file_log` (
  `id_doc_file_log_mainfeeder_flow` BIGINT NOT NULL AUTO_INCREMENT,
  `id_doc_file_mainfeeder_flow` BIGINT NOT NULL,
  `id_doc_package_mainfeeder_flow` BIGINT NOT NULL,
  `id_doc_item` INT NOT NULL,
  `action_type` ENUM('UPLOAD','REUPLOAD','APPROVE','REJECT') NOT NULL,
  `status_after` VARCHAR(30) NOT NULL,
  `file_name` VARCHAR(255) DEFAULT NULL,
  `remark` TEXT DEFAULT NULL,
  `action_by` INT DEFAULT NULL,
  `action_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_doc_file_log_mainfeeder_flow`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_myrep_mainfeeder_drm_boq` (
  `id_mainfeeder_drm_boq` BIGINT NOT NULL AUTO_INCREMENT,
  `id_mainfeeder` BIGINT NOT NULL,
  `id_mainfeeder_drm` BIGINT DEFAULT NULL,
  `source_doc_file_id` BIGINT DEFAULT NULL,
  `review_status` ENUM('DRAFT','WAITING HO','APPROVED','REJECTED') NOT NULL DEFAULT 'DRAFT',
  `submitted_at` DATETIME DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `rejected_at` DATETIME DEFAULT NULL,
  `approved_by` INT DEFAULT NULL,
  `ho_review_remark` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `updated_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mainfeeder_drm_boq`),
  UNIQUE KEY `uniq_mainfeeder_drm_boq` (`id_mainfeeder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_myrep_mainfeeder_drm_boq_item` (
  `id_mainfeeder_drm_boq_item` BIGINT NOT NULL AUTO_INCREMENT,
  `id_mainfeeder_drm_boq` BIGINT NOT NULL,
  `id_boq_item` INT NOT NULL,
  `qty_boq` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `jumlah_foto` INT NOT NULL DEFAULT 0,
  `remarks_rule` ENUM('SESUAI ITEM','SAMPLING') NOT NULL DEFAULT 'SESUAI ITEM',
  `target_foto_required` INT NOT NULL DEFAULT 0,
  `item_note` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mainfeeder_drm_boq_item`),
  UNIQUE KEY `uniq_mainfeeder_drm_boq_item` (`id_mainfeeder_drm_boq`,`id_boq_item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_myrep_mainfeeder_boq_baseline` (
  `id_mainfeeder_boq_baseline` BIGINT NOT NULL AUTO_INCREMENT,
  `id_mainfeeder` BIGINT NOT NULL,
  `id_mainfeeder_drm_boq` BIGINT NOT NULL,
  `status_baseline` ENUM('ACTIVE','ARCHIVED') NOT NULL DEFAULT 'ACTIVE',
  `approved_at` DATETIME DEFAULT NULL,
  `approved_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mainfeeder_boq_baseline`),
  KEY `idx_mainfeeder_baseline` (`id_mainfeeder`,`status_baseline`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_myrep_mainfeeder_boq_baseline_item` (
  `id_mainfeeder_boq_baseline_item` BIGINT NOT NULL AUTO_INCREMENT,
  `id_mainfeeder_boq_baseline` BIGINT NOT NULL,
  `id_boq_item` INT NOT NULL,
  `qty_boq` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `jumlah_foto` INT NOT NULL DEFAULT 0,
  `remarks_rule` ENUM('SESUAI ITEM','SAMPLING') NOT NULL DEFAULT 'SESUAI ITEM',
  `target_foto_required` INT NOT NULL DEFAULT 0,
  `item_note` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mainfeeder_boq_baseline_item`),
  UNIQUE KEY `uniq_mainfeeder_baseline_item` (`id_mainfeeder_boq_baseline`,`id_boq_item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_myrep_mainfeeder_impl_daily_activity` (
  `id_daily_activity_mainfeeder` BIGINT NOT NULL AUTO_INCREMENT,
  `id_mainfeeder` BIGINT NOT NULL,
  `activity_date` DATE NOT NULL,
  `activity_code` VARCHAR(64) NOT NULL,
  `activity_name` VARCHAR(150) NOT NULL,
  `activity_detail` VARCHAR(150) DEFAULT NULL,
  `boq_type` VARCHAR(80) NOT NULL,
  `team_count` INT NOT NULL DEFAULT 0,
  `worker_count` INT NOT NULL DEFAULT 0,
  `qty_activity` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `unit_activity` VARCHAR(32) DEFAULT NULL,
  `remark_activity` TEXT DEFAULT NULL,
  `created_by` INT NOT NULL,
  `updated_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_daily_activity_mainfeeder`),
  KEY `idx_mainfeeder_activity` (`id_mainfeeder`,`activity_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_myrep_mainfeeder_impl_daily_activity_photo` (
  `id_activity_photo_mainfeeder` BIGINT NOT NULL AUTO_INCREMENT,
  `id_daily_activity_mainfeeder` BIGINT NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) DEFAULT NULL,
  `uploaded_by` INT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_activity_photo_mainfeeder`),
  KEY `idx_mainfeeder_activity_photo` (`id_daily_activity_mainfeeder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_myrep_mainfeeder_boq_progress_item` (
  `id_progress_item_mainfeeder` BIGINT NOT NULL AUTO_INCREMENT,
  `id_mainfeeder` BIGINT NOT NULL,
  `id_mainfeeder_boq_baseline` BIGINT NOT NULL,
  `id_mainfeeder_boq_baseline_item` BIGINT NOT NULL,
  `progress_date` DATE NOT NULL,
  `qty_progress` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `status_progress` ENUM('DRAFT','SUBMITTED','APPROVED','REJECTED') NOT NULL DEFAULT 'APPROVED',
  `remark_progress` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `updated_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_progress_item_mainfeeder`),
  KEY `idx_mainfeeder_progress` (`id_mainfeeder`,`progress_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_myrep_mainfeeder_atp_file` (
  `id_atp_file_mainfeeder` BIGINT NOT NULL AUTO_INCREMENT,
  `id_mainfeeder` BIGINT NOT NULL,
  `doc_type` ENUM('RECORD_PUNCLIST','BA_RECTIFICATION') NOT NULL,
  `file_name` VARCHAR(255) DEFAULT NULL,
  `file_path` VARCHAR(255) DEFAULT NULL,
  `remark` TEXT DEFAULT NULL,
  `uploaded_by` INT DEFAULT NULL,
  `uploaded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_atp_file_mainfeeder`),
  KEY `idx_mainfeeder_atp_file` (`id_mainfeeder`,`doc_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `tb_myrep_po_header`
  MODIFY COLUMN `id_myrep_cluster` INT NULL,
  MODIFY COLUMN `po_type` ENUM('CLUSTER','SUBFEEDER','MAINFEEDER') NOT NULL DEFAULT 'CLUSTER',
  ADD COLUMN IF NOT EXISTS `project_type` ENUM('CLUSTER','MAINFEEDER') NOT NULL DEFAULT 'CLUSTER' AFTER `id_myrep_cluster`,
  ADD COLUMN IF NOT EXISTS `id_mainfeeder` BIGINT NULL AFTER `project_type`,
  ADD KEY IF NOT EXISTS `idx_myrep_po_mainfeeder` (`id_mainfeeder`,`po_type`);

ALTER TABLE `tb_myrep_po_termin`
  ADD COLUMN IF NOT EXISTS `invoice_value` DECIMAL(18,2) NULL AFTER `invoice_date`,
  ADD COLUMN IF NOT EXISTS `sertifikat_invoice_date` VARCHAR(150) NULL AFTER `invoice_value`;
