-- Idempotent patch: add CITY_ROLE support for MyRep notification routing

-- 1) Ensure enum target_type supports CITY_ROLE
ALTER TABLE `tb_myrep_notification_route`
  MODIFY `target_type` ENUM('FIXED_USER','CLUSTER_PIC','CITY_ROLE') NOT NULL;

-- 2) Add target_role column only if it does not exist
SET @col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_myrep_notification_route'
    AND COLUMN_NAME = 'target_role'
);

SET @sql_add_col := IF(
  @col_exists = 0,
  'ALTER TABLE `tb_myrep_notification_route` ADD COLUMN `target_role` VARCHAR(50) NULL AFTER `target_user_id`',
  'SELECT ''target_role already exists'''
);
PREPARE stmt_add_col FROM @sql_add_col;
EXECUTE stmt_add_col;
DEALLOCATE PREPARE stmt_add_col;

-- 3) Add index only if it does not exist
SET @idx_exists := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_myrep_notification_route'
    AND INDEX_NAME = 'idx_myrep_notification_target_role'
);

SET @sql_add_idx := IF(
  @idx_exists = 0,
  'CREATE INDEX `idx_myrep_notification_target_role` ON `tb_myrep_notification_route` (`target_role`)',
  'SELECT ''idx_myrep_notification_target_role already exists'''
);
PREPARE stmt_add_idx FROM @sql_add_idx;
EXECUTE stmt_add_idx;
DEALLOCATE PREPARE stmt_add_idx;
