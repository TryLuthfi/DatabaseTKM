-- Patch: sinkron kolom role mapping kota MyRep (ATP HO + QA HO)
-- Date : 2026-05-22
-- Scope: tb_myrep_pic_mapping_city

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';

DROP PROCEDURE IF EXISTS sp_patch_myrep_city_mapping_role_columns;
DELIMITER $$
CREATE PROCEDURE sp_patch_myrep_city_mapping_role_columns()
BEGIN
    IF EXISTS (
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name = 'tb_myrep_pic_mapping_city'
    ) THEN
        IF NOT EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = 'tb_myrep_pic_mapping_city'
              AND column_name = 'atp_ho'
        ) THEN
            ALTER TABLE `tb_myrep_pic_mapping_city`
                ADD COLUMN `atp_ho` VARCHAR(30) NULL AFTER `snd_ho`;
        END IF;

        IF NOT EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = 'tb_myrep_pic_mapping_city'
              AND column_name = 'qa_ho'
        ) THEN
            ALTER TABLE `tb_myrep_pic_mapping_city`
                ADD COLUMN `qa_ho` VARCHAR(30) NULL AFTER `dc_ho`;
        END IF;

        IF NOT EXISTS (
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'tb_myrep_pic_mapping_city'
              AND index_name = 'idx_myrep_pic_mapping_atp_ho'
        ) THEN
            ALTER TABLE `tb_myrep_pic_mapping_city`
                ADD INDEX `idx_myrep_pic_mapping_atp_ho` (`atp_ho`);
        END IF;

        IF NOT EXISTS (
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'tb_myrep_pic_mapping_city'
              AND index_name = 'idx_myrep_pic_mapping_qa_ho'
        ) THEN
            ALTER TABLE `tb_myrep_pic_mapping_city`
                ADD INDEX `idx_myrep_pic_mapping_qa_ho` (`qa_ho`);
        END IF;
    END IF;
END$$
DELIMITER ;

CALL sp_patch_myrep_city_mapping_role_columns();
DROP PROCEDURE IF EXISTS sp_patch_myrep_city_mapping_role_columns;

SET SQL_MODE = @OLD_SQL_MODE;

