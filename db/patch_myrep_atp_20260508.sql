ALTER TABLE `tb_rfs_myrep_cluster`
    ADD COLUMN `email_atp_date` DATE NULL AFTER `status_rfs`,
    ADD COLUMN `status_atp` ENUM('PUNCLIST','DONE') NULL AFTER `email_atp_date`;

CREATE TABLE `tb_rfs_myrep_atp_file` (
    `id_atp_file` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `cluster_id` INT(11) NOT NULL,
    `doc_type` ENUM('RECORD_PUNCLIST','BA_RECTIFICATION') NOT NULL,
    `file_name` VARCHAR(255) DEFAULT NULL,
    `file_path` VARCHAR(255) DEFAULT NULL,
    `remark` TEXT DEFAULT NULL,
    `uploaded_by` INT(11) DEFAULT NULL,
    `uploaded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    PRIMARY KEY (`id_atp_file`),
    KEY `idx_myrep_atp_file_cluster` (`cluster_id`),
    KEY `idx_myrep_atp_file_type` (`doc_type`),
    CONSTRAINT `fk_myrep_atp_file_cluster`
        FOREIGN KEY (`cluster_id`) REFERENCES `tb_rfs_myrep_cluster` (`id_cluster`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
