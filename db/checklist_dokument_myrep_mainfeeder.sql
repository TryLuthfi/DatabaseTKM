CREATE TABLE IF NOT EXISTS `tb_rfs_myrep_mainfeeder` (
  `id_mainfeeder` BIGINT NOT NULL AUTO_INCREMENT,
  `id_target` INT NOT NULL,
  `mainfeeder_name` VARCHAR(255) NOT NULL,
  `length_meter` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `atp_date` DATE NOT NULL,
  `created_by` INT DEFAULT NULL,
  `updated_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mainfeeder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `md_rfs_myrep_mainfeeder_doc_group` (
  `id_doc_group_mainfeeder` INT NOT NULL AUTO_INCREMENT,
  `sow_type` ENUM('CW ATP','FULL OPM','RFS') NOT NULL,
  `group_label` VARCHAR(100) NOT NULL,
  `sort_no` TINYINT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_doc_group_mainfeeder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `md_rfs_myrep_mainfeeder_doc_item` (
  `id_doc_item_mainfeeder` INT NOT NULL AUTO_INCREMENT,
  `id_doc_group_mainfeeder` INT NOT NULL,
  `doc_name` VARCHAR(255) NOT NULL,
  `sort_no` TINYINT NOT NULL DEFAULT 0,
  `is_required` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_doc_item_mainfeeder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_rfs_myrep_mainfeeder_doc_package` (
  `id_doc_package_mainfeeder` BIGINT NOT NULL AUTO_INCREMENT,
  `id_mainfeeder` BIGINT NOT NULL,
  `id_doc_group_mainfeeder` INT NOT NULL,
  `atp_date` DATE DEFAULT NULL,
  `plan_submit_doc_date` DATE DEFAULT NULL,
  `actual_submit_doc_date` DATE DEFAULT NULL,
  `status_package` ENUM('NOT STARTED','ON PROGRESS','DONE') NOT NULL DEFAULT 'NOT STARTED',
  `remarks` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `updated_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_doc_package_mainfeeder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_rfs_myrep_mainfeeder_doc_file` (
  `id_doc_file_mainfeeder` BIGINT NOT NULL AUTO_INCREMENT,
  `id_doc_package_mainfeeder` BIGINT NOT NULL,
  `id_doc_item_mainfeeder` INT NOT NULL,
  `file_name` VARCHAR(255) DEFAULT NULL,
  `file_path` VARCHAR(255) DEFAULT NULL,
  `is_document_not_required` TINYINT(1) NOT NULL DEFAULT 0,
  `status_file` ENUM('NOT UPLOADED','UPLOADED','APPROVED','REJECTED') NOT NULL DEFAULT 'UPLOADED',
  `remark` TEXT DEFAULT NULL,
  `uploaded_by` INT DEFAULT NULL,
  `uploaded_at` DATETIME DEFAULT NULL,
  `approved_by` INT DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `astri_submitted_date` DATE DEFAULT NULL,
  `astri_status` ENUM('NY','ON REVIEW','REJECTED','APPROVED') NOT NULL DEFAULT 'NY',
  `astri_status_updated_at` DATETIME DEFAULT NULL,
  `astri_remark` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_doc_file_mainfeeder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_rfs_myrep_mainfeeder_doc_file_log` (
  `id_doc_file_log_mainfeeder` BIGINT NOT NULL AUTO_INCREMENT,
  `id_doc_file_mainfeeder` BIGINT NOT NULL,
  `id_doc_package_mainfeeder` BIGINT NOT NULL,
  `id_doc_item_mainfeeder` INT NOT NULL,
  `action_type` ENUM('UPLOADED','REUPLOADED','REJECTED','APPROVED') NOT NULL,
  `status_after` VARCHAR(30) NOT NULL,
  `file_name` VARCHAR(255) DEFAULT NULL,
  `remark` TEXT DEFAULT NULL,
  `action_by` INT DEFAULT NULL,
  `action_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_doc_file_log_mainfeeder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `md_rfs_myrep_mainfeeder_doc_group` (`sow_type`, `group_label`, `sort_no`, `is_active`) VALUES
('CW ATP', 'CW ATP MAIN FEEDER', 1, 1),
('FULL OPM', 'FULL OPM MAIN FEEDER', 2, 1),
('RFS', 'RFS MAIN FEEDER', 3, 1)
ON DUPLICATE KEY UPDATE `group_label` = VALUES(`group_label`);

INSERT INTO `md_rfs_myrep_mainfeeder_doc_item` (`id_doc_group_mainfeeder`, `doc_name`, `sort_no`, `is_required`, `is_active`) VALUES
(1, 'Redline', 1, 1, 1),
(1, 'CW ATP Feeder', 2, 1, 1),
(1, 'Rekomtek', 3, 1, 1),
(1, 'PTSP', 4, 1, 1),
(1, 'TSSR', 5, 1, 1),
(2, 'FULL OPM', 1, 1, 1),
(3, 'Project Opname', 1, 1, 1),
(3, 'Justifikasi Berita Acara Opname', 2, 1, 1),
(3, 'ABD Core Management Main Feeder', 3, 1, 1),
(3, 'ABD Main Feeder KMZ', 4, 1, 1),
(3, 'ABD Main Feeder DWG', 5, 1, 1),
(3, 'ABD BoQ Main Feeder', 6, 1, 1);
