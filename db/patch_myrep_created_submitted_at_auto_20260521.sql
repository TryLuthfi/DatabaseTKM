-- Patch: pastikan kolom created_at / submitted_at di modul MyRep otomatis terisi waktu saat insert
-- Date : 2026-05-21
-- Scope:
--   - semua tabel yang namanya mengandung 'myrep'
--
-- Fitur patch:
-- 1) Jika kolom created_at belum ada -> tambahkan.
-- 2) Jika kolom submitted_at belum ada -> tambahkan.
-- 3) Set default created_at = CURRENT_TIMESTAMP.
-- 4) Set default submitted_at = CURRENT_TIMESTAMP (tetap nullable).

SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';

DROP PROCEDURE IF EXISTS sp_patch_myrep_created_submitted_at;
DELIMITER $$
CREATE PROCEDURE sp_patch_myrep_created_submitted_at()
BEGIN
    DECLARE v_done INT DEFAULT 0;
    DECLARE v_table_name VARCHAR(128);
    DECLARE v_stmt TEXT;
    DECLARE v_data_type VARCHAR(32);
    DECLARE v_has_created INT DEFAULT 0;
    DECLARE v_has_submitted INT DEFAULT 0;

    DECLARE cur_tables CURSOR FOR
        SELECT t.table_name
        FROM information_schema.tables t
        WHERE t.table_schema = DATABASE()
          AND LOWER(t.table_name) LIKE '%myrep%';

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = 1;

    OPEN cur_tables;
    patch_loop: LOOP
        FETCH cur_tables INTO v_table_name;
        IF v_done = 1 THEN
            LEAVE patch_loop;
        END IF;

        -- 1) Tambah created_at jika belum ada
        SELECT COUNT(*)
          INTO v_has_created
        FROM information_schema.columns c
        WHERE c.table_schema = DATABASE()
          AND c.table_name = v_table_name
          AND c.column_name = 'created_at';

        IF v_has_created = 0 THEN
            SET v_stmt = CONCAT(
                'ALTER TABLE `', v_table_name, '` ',
                'ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
            );
            SET @sql_exec = v_stmt;
            PREPARE s1 FROM @sql_exec;
            EXECUTE s1;
            DEALLOCATE PREPARE s1;
        END IF;

        -- 2) Tambah submitted_at jika belum ada
        SELECT COUNT(*)
          INTO v_has_submitted
        FROM information_schema.columns c
        WHERE c.table_schema = DATABASE()
          AND c.table_name = v_table_name
          AND c.column_name = 'submitted_at';

        IF v_has_submitted = 0 THEN
            SET v_stmt = CONCAT(
                'ALTER TABLE `', v_table_name, '` ',
                'ADD COLUMN `submitted_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP'
            );
            SET @sql_exec = v_stmt;
            PREPARE s2 FROM @sql_exec;
            EXECUTE s2;
            DEALLOCATE PREPARE s2;
        END IF;

        -- 3) Normalisasi default created_at = CURRENT_TIMESTAMP
        SELECT MAX(c.data_type)
          INTO v_data_type
        FROM information_schema.columns c
        WHERE c.table_schema = DATABASE()
          AND c.table_name = v_table_name
          AND c.column_name = 'created_at';

        IF v_data_type IS NOT NULL THEN
            IF v_data_type = 'timestamp' THEN
                SET v_stmt = CONCAT(
                    'ALTER TABLE `', v_table_name, '` ',
                    'MODIFY COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP'
                );
            ELSE
                SET v_stmt = CONCAT(
                    'ALTER TABLE `', v_table_name, '` ',
                    'MODIFY COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
                );
            END IF;
            SET @sql_exec = v_stmt;
            PREPARE s3 FROM @sql_exec;
            EXECUTE s3;
            DEALLOCATE PREPARE s3;
        END IF;

        -- 4) Normalisasi default submitted_at = CURRENT_TIMESTAMP (nullable)
        SELECT MAX(c.data_type)
          INTO v_data_type
        FROM information_schema.columns c
        WHERE c.table_schema = DATABASE()
          AND c.table_name = v_table_name
          AND c.column_name = 'submitted_at';

        IF v_data_type IS NOT NULL THEN
            IF v_data_type = 'timestamp' THEN
                SET v_stmt = CONCAT(
                    'ALTER TABLE `', v_table_name, '` ',
                    'MODIFY COLUMN `submitted_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP'
                );
            ELSE
                SET v_stmt = CONCAT(
                    'ALTER TABLE `', v_table_name, '` ',
                    'MODIFY COLUMN `submitted_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP'
                );
            END IF;
            SET @sql_exec = v_stmt;
            PREPARE s4 FROM @sql_exec;
            EXECUTE s4;
            DEALLOCATE PREPARE s4;
        END IF;
    END LOOP;
    CLOSE cur_tables;
END$$
DELIMITER ;

CALL sp_patch_myrep_created_submitted_at();
DROP PROCEDURE IF EXISTS sp_patch_myrep_created_submitted_at;

SET SQL_MODE = @OLD_SQL_MODE;

-- Catatan:
-- 1) `submitted_at` dibuat nullable agar alur yang belum submit tetap bisa menyimpan NULL eksplisit.
-- 2) Jika payload insert tidak mengirim nilai kolom timestamp ini, database otomatis isi waktu saat insert.
