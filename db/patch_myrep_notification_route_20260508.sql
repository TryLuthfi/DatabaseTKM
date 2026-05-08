CREATE TABLE `tb_myrep_notification_route` (
    `id_route` INT(11) NOT NULL AUTO_INCREMENT,
    `module_name` VARCHAR(100) NOT NULL,
    `event_name` VARCHAR(100) NOT NULL,
    `target_type` ENUM('FIXED_USER','CLUSTER_PIC') NOT NULL,
    `target_user_id` INT(11) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_route`),
    UNIQUE KEY `uniq_myrep_notification_route` (`module_name`,`event_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tb_myrep_notification_route` (`module_name`, `event_name`, `target_type`, `target_user_id`, `is_active`) VALUES
('BAK_MyRep', 'document_masuk', 'FIXED_USER', 22, 1),
('BAK_MyRep', 'document_revised', 'FIXED_USER', 22, 1),
('BAK_MyRep', 'cluster_masuk', 'FIXED_USER', 22, 1),
('VALSAL_MyRep', 'document_masuk', 'FIXED_USER', 121, 1),
('VALSAL_MyRep', 'document_revised', 'FIXED_USER', 121, 1),
('VALSAL_MyRep', 'cluster_masuk', 'FIXED_USER', 121, 1),
('Batch_Approval_MyRep', 'cluster_masuk', 'FIXED_USER', 22, 1),
('Batch_Approval_MyRep', 'document_masuk', 'FIXED_USER', 22, 1),
('Batch_Approval_MyRep', 'document_revised', 'FIXED_USER', 22, 1),
('DRM_MyRep', 'cluster_masuk', 'FIXED_USER', 121, 1),
('DRM_MyRep', 'document_masuk', 'FIXED_USER', 121, 1),
('DRM_MyRep', 'document_revised', 'FIXED_USER', 121, 1),
('Monitoring_RFS_MyRep', 'claim_rfs_approved', 'FIXED_USER', 14, 1),
('Checklist_Dokument_MyRep', 'document_masuk', 'CLUSTER_PIC', NULL, 1),
('Checklist_Dokument_MyRep', 'document_revised', 'CLUSTER_PIC', NULL, 1);
