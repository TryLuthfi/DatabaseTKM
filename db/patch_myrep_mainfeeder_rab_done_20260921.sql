-- Patch MyRep Mainfeeder RAB DONE gate.
-- Run on local/VPS before using RAB DONE for DRM_MyRep/mainfeeder.

ALTER TABLE `tb_rfs_myrep_mainfeeder`
  MODIFY COLUMN `current_status` ENUM('DRM','RAB DONE','IMPLEMENTASI','ATP','CHECKLIST','DONE') NOT NULL DEFAULT 'DRM';

ALTER TABLE `tb_myrep_rab`
  MODIFY COLUMN `id_myrep_cluster` INT(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `id_mainfeeder` INT(11) DEFAULT NULL AFTER `id_myrep_cluster`;

ALTER TABLE `tb_myrep_rab`
  ADD UNIQUE KEY IF NOT EXISTS `uniq_myrep_rab_mainfeeder` (`id_mainfeeder`);

SELECT
  `COLUMN_TYPE` AS `mainfeeder_current_status_type`
FROM `INFORMATION_SCHEMA`.`COLUMNS`
WHERE `TABLE_SCHEMA` = DATABASE()
  AND `TABLE_NAME` = 'tb_rfs_myrep_mainfeeder'
  AND `COLUMN_NAME` = 'current_status';

SHOW COLUMNS FROM `tb_myrep_rab` LIKE 'id_mainfeeder';
SHOW INDEX FROM `tb_myrep_rab` WHERE `Key_name` = 'uniq_myrep_rab_mainfeeder';
