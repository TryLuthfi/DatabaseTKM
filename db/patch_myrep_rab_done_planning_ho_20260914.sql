-- Patch MyRep RAB DONE + Planning HO.
-- Run on VPS after deploying the related code changes.

ALTER TABLE `tb_myrep_pic_mapping_city`
  ADD COLUMN IF NOT EXISTS `planning_ho` VARCHAR(255) NULL AFTER `sitac_ho`;

ALTER TABLE `tb_myrep_cluster`
  MODIFY COLUMN `status_current` ENUM(
    'DRAFT',
    'NTP',
    'BA OPEN',
    'BAK',
    'VALSAL',
    'WAITING HO',
    'WAITING MYREP',
    'WAITING FINANCE',
    'RELEASED',
    'DONE BATCH APPROVAL',
    'DRM',
    'RAB DONE',
    'RFS',
    'ATP',
    'CHECKLIST DOKUMENT',
    'DONE',
    'REJECTED',
    'HOLD'
  ) NOT NULL DEFAULT 'DRAFT';

UPDATE `tb_myrep_pic_mapping_city`
SET
  `planning_ho` = CASE
    WHEN `planning_ho` IS NULL OR TRIM(`planning_ho`) = '' THEN '9808925'
    WHEN FIND_IN_SET('9808925', REPLACE(REPLACE(REPLACE(`planning_ho`, ';', ','), '|', ','), ' ', '')) = 0 THEN CONCAT(TRIM(BOTH ',' FROM `planning_ho`), ',9808925')
    ELSE `planning_ho`
  END,
  `updated_at` = NOW()
WHERE FIND_IN_SET('9808925', REPLACE(REPLACE(REPLACE(COALESCE(`planning_ho`, ''), ';', ','), '|', ','), ' ', '')) = 0;

CREATE TABLE IF NOT EXISTS `tb_myrep_rab` (
  `id_myrep_rab` INT(11) NOT NULL AUTO_INCREMENT,
  `id_myrep_cluster` INT(11) NOT NULL,
  `id_drm` INT(11) DEFAULT NULL,
  `id_drm_boq` INT(11) DEFAULT NULL,
  `id_apd_boq_file` INT(11) DEFAULT NULL,
  `rab_status` VARCHAR(50) NOT NULL DEFAULT 'RAB DONE',
  `detail_rab` TEXT NULL,
  `rab_done_at` DATETIME DEFAULT NULL,
  `rab_done_by` INT(11) DEFAULT NULL,
  `cancelled_at` DATETIME DEFAULT NULL,
  `cancelled_by` INT(11) DEFAULT NULL,
  `cancel_reason` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_myrep_rab`),
  UNIQUE KEY `uniq_myrep_rab_cluster` (`id_myrep_cluster`),
  KEY `idx_myrep_rab_status` (`rab_status`),
  KEY `idx_myrep_rab_drm_boq` (`id_drm_boq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tb_myrep_rab`
  ADD COLUMN IF NOT EXISTS `id_drm` INT(11) DEFAULT NULL AFTER `id_myrep_cluster`,
  ADD COLUMN IF NOT EXISTS `id_drm_boq` INT(11) DEFAULT NULL AFTER `id_drm`,
  ADD COLUMN IF NOT EXISTS `id_apd_boq_file` INT(11) DEFAULT NULL AFTER `id_drm_boq`,
  ADD COLUMN IF NOT EXISTS `rab_status` VARCHAR(50) NOT NULL DEFAULT 'RAB DONE' AFTER `id_apd_boq_file`,
  ADD COLUMN IF NOT EXISTS `detail_rab` TEXT NULL AFTER `rab_status`,
  ADD COLUMN IF NOT EXISTS `rab_done_at` DATETIME DEFAULT NULL AFTER `detail_rab`,
  ADD COLUMN IF NOT EXISTS `rab_done_by` INT(11) DEFAULT NULL AFTER `rab_done_at`,
  ADD COLUMN IF NOT EXISTS `cancelled_at` DATETIME DEFAULT NULL AFTER `rab_done_by`,
  ADD COLUMN IF NOT EXISTS `cancelled_by` INT(11) DEFAULT NULL AFTER `cancelled_at`,
  ADD COLUMN IF NOT EXISTS `cancel_reason` TEXT NULL AFTER `cancelled_by`;

ALTER TABLE `tb_myrep_rab`
  ADD UNIQUE KEY IF NOT EXISTS `uniq_myrep_rab_cluster` (`id_myrep_cluster`),
  ADD KEY IF NOT EXISTS `idx_myrep_rab_status` (`rab_status`),
  ADD KEY IF NOT EXISTS `idx_myrep_rab_drm_boq` (`id_drm_boq`);

UPDATE `tb_myrep_cluster` c
JOIN `tb_myrep_rab` rab
  ON rab.`id_myrep_cluster` = c.`id_myrep_cluster`
LEFT JOIN `tb_myrep_batch_approval` ba
  ON ba.`id_myrep_cluster` = c.`id_myrep_cluster`
SET c.`status_current` = 'RAB DONE',
    c.`updated_at` = NOW()
WHERE UPPER(TRIM(rab.`rab_status`)) = 'RAB DONE'
  AND ba.`id_batch_approval` IS NULL
  AND UPPER(TRIM(COALESCE(c.`status_current`, ''))) NOT IN ('RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE', 'REJECTED', 'HOLD');

INSERT INTO `tb_myrep_role_permission`
  (`page_key`, `action_key`, `role_key`, `is_allowed`, `is_active`, `effective_start`, `effective_end`, `created_at`, `updated_at`, `submitted_at`)
VALUES
  ('DRM_MyRep', 'VIEW', 'PLANNING_HO', 1, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('DRM_MyRep', 'TAMBAH', 'PLANNING_HO', 0, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('DRM_MyRep', 'EDIT', 'PLANNING_HO', 0, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('DRM_MyRep', 'HAPUS', 'PLANNING_HO', 0, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('DRM_MyRep', 'APPROVAL', 'PLANNING_HO', 0, 1, NULL, NULL, NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `is_allowed` = VALUES(`is_allowed`),
  `is_active` = 1,
  `updated_at` = NOW(),
  `submitted_at` = NOW();

SELECT
  COUNT(*) AS total_city_rows,
  SUM(
    CASE
      WHEN FIND_IN_SET('9808925', REPLACE(REPLACE(REPLACE(COALESCE(`planning_ho`, ''), ';', ','), '|', ','), ' ', '')) > 0 THEN 1
      ELSE 0
    END
  ) AS mapped_planning_ho_9808925
FROM `tb_myrep_pic_mapping_city`;

SHOW COLUMNS FROM `tb_myrep_rab`;
