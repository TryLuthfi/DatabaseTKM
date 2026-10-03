-- Patch MyRep SPK monitoring.
-- Rule:
-- 1. SPK can be input after RAB DONE.
-- 2. One cluster can have CLUSTER and SUBFEEDER SPK, or one GABUNGAN SPK applied to both scopes.
-- 3. Existing clusters that already have RFS date are migrated as SPK DONE with number "MIGRATED SPK BY SYSTEM".

CREATE TABLE IF NOT EXISTS `tb_myrep_spk` (
  `id_myrep_spk` INT(11) NOT NULL AUTO_INCREMENT,
  `id_myrep_cluster` INT(11) NOT NULL,
  `scope_type` VARCHAR(20) NOT NULL DEFAULT 'CLUSTER',
  `spk_mode` VARCHAR(30) NOT NULL DEFAULT 'CLUSTER_ONLY',
  `spk_number` VARCHAR(120) NOT NULL,
  `spk_status` VARCHAR(30) NOT NULL DEFAULT 'SPK DONE',
  `spk_done_at` DATETIME DEFAULT NULL,
  `spk_done_by` INT(11) DEFAULT NULL,
  `cancelled_at` DATETIME DEFAULT NULL,
  `cancelled_by` INT(11) DEFAULT NULL,
  `cancel_reason` TEXT NULL,
  `migrated_from_rfs` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_myrep_spk`),
  UNIQUE KEY `uniq_myrep_spk_cluster_scope` (`id_myrep_cluster`, `scope_type`),
  KEY `idx_myrep_spk_status` (`spk_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tb_myrep_spk`
  ADD COLUMN IF NOT EXISTS `scope_type` VARCHAR(20) NOT NULL DEFAULT 'CLUSTER' AFTER `id_myrep_cluster`,
  ADD COLUMN IF NOT EXISTS `spk_mode` VARCHAR(30) NOT NULL DEFAULT 'CLUSTER_ONLY' AFTER `scope_type`,
  ADD COLUMN IF NOT EXISTS `spk_number` VARCHAR(120) NOT NULL AFTER `spk_mode`,
  ADD COLUMN IF NOT EXISTS `spk_status` VARCHAR(30) NOT NULL DEFAULT 'SPK DONE' AFTER `spk_number`,
  ADD COLUMN IF NOT EXISTS `spk_done_at` DATETIME DEFAULT NULL AFTER `spk_status`,
  ADD COLUMN IF NOT EXISTS `spk_done_by` INT(11) DEFAULT NULL AFTER `spk_done_at`,
  ADD COLUMN IF NOT EXISTS `cancelled_at` DATETIME DEFAULT NULL AFTER `spk_done_by`,
  ADD COLUMN IF NOT EXISTS `cancelled_by` INT(11) DEFAULT NULL AFTER `cancelled_at`,
  ADD COLUMN IF NOT EXISTS `cancel_reason` TEXT NULL AFTER `cancelled_by`,
  ADD COLUMN IF NOT EXISTS `migrated_from_rfs` TINYINT(1) NOT NULL DEFAULT 0 AFTER `cancel_reason`;

ALTER TABLE `tb_myrep_spk`
  ADD UNIQUE KEY IF NOT EXISTS `uniq_myrep_spk_cluster_scope` (`id_myrep_cluster`, `scope_type`),
  ADD KEY IF NOT EXISTS `idx_myrep_spk_status` (`spk_status`);

INSERT INTO `tb_myrep_spk`
  (
    `id_myrep_cluster`,
    `scope_type`,
    `spk_mode`,
    `spk_number`,
    `spk_status`,
    `spk_done_at`,
    `spk_done_by`,
    `cancelled_at`,
    `cancelled_by`,
    `cancel_reason`,
    `migrated_from_rfs`,
    `created_at`,
    `updated_at`
  )
SELECT
  c.`id_myrep_cluster`,
  scope_rows.`scope_type`,
  CASE WHEN scope_rows.`scope_type` = 'SUBFEEDER' THEN 'GABUNGAN' ELSE 'CLUSTER_ONLY' END AS `spk_mode`,
  'MIGRATED SPK BY SYSTEM' AS `spk_number`,
  'SPK DONE' AS `spk_status`,
  COALESCE(latest_claim.`rfs_date`, NOW()) AS `spk_done_at`,
  NULL AS `spk_done_by`,
  NULL AS `cancelled_at`,
  NULL AS `cancelled_by`,
  NULL AS `cancel_reason`,
  1 AS `migrated_from_rfs`,
  NOW() AS `created_at`,
  NOW() AS `updated_at`
FROM `tb_myrep_cluster` c
JOIN (
  SELECT `cluster_id`, MAX(`claim_date`) AS `rfs_date`
  FROM `tb_rfs_myrep_claim`
  WHERE `claim_date` IS NOT NULL
    AND `claim_date` <> '0000-00-00'
  GROUP BY `cluster_id`
) latest_claim
  ON latest_claim.`cluster_id` = c.`rfs_cluster_id`
JOIN (
  SELECT 'CLUSTER' AS `scope_type`
  UNION ALL
  SELECT 'SUBFEEDER' AS `scope_type`
) scope_rows
LEFT JOIN `tb_myrep_scope_requirement` sr
  ON sr.`id_myrep_cluster` = c.`id_myrep_cluster`
 AND sr.`scope_type` = 'SUBFEEDER'
WHERE c.`id_myrep_cluster` IS NOT NULL
  AND (
    scope_rows.`scope_type` = 'CLUSTER'
    OR UPPER(TRIM(COALESCE(sr.`requirement_status`, 'REQUIRED'))) <> 'NOT_REQUIRED_APPROVED'
  )
ON DUPLICATE KEY UPDATE
  `spk_status` = 'SPK DONE',
  `spk_number` = CASE
    WHEN `spk_number` IS NULL OR TRIM(`spk_number`) = '' THEN VALUES(`spk_number`)
    ELSE `spk_number`
  END,
  `spk_done_at` = COALESCE(`spk_done_at`, VALUES(`spk_done_at`)),
  `cancelled_at` = NULL,
  `cancelled_by` = NULL,
  `cancel_reason` = NULL,
  `migrated_from_rfs` = CASE
    WHEN `migrated_from_rfs` = 1 THEN 1
    WHEN `spk_number` = 'MIGRATED SPK BY SYSTEM' THEN 1
    ELSE `migrated_from_rfs`
  END,
  `updated_at` = NOW();

SELECT
  COUNT(DISTINCT `id_myrep_cluster`) AS `cluster_with_spk_done`,
  SUM(CASE WHEN `scope_type` = 'CLUSTER' AND UPPER(`spk_status`) = 'SPK DONE' THEN 1 ELSE 0 END) AS `cluster_spk_done`,
  SUM(CASE WHEN `scope_type` = 'SUBFEEDER' AND UPPER(`spk_status`) = 'SPK DONE' THEN 1 ELSE 0 END) AS `subfeeder_spk_done`
FROM `tb_myrep_spk`
WHERE UPPER(`spk_status`) = 'SPK DONE';
