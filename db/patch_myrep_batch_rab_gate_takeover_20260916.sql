-- Patch MyRep Batch Approval RAB gate + takeover.
-- Rule:
-- 1. Batch below "ON PROSES PENGAJUAN SAKU" must already be RAB DONE to appear in Batch Approval.
-- 2. Batch already at "ON PROSES PENGAJUAN SAKU" or above is taken over as RAB DONE.

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

INSERT INTO `tb_myrep_rab`
  (
    `id_myrep_cluster`,
    `id_drm`,
    `id_drm_boq`,
    `id_apd_boq_file`,
    `rab_status`,
    `detail_rab`,
    `rab_done_at`,
    `rab_done_by`,
    `cancelled_at`,
    `cancelled_by`,
    `cancel_reason`,
    `created_at`,
    `updated_at`
  )
SELECT
  ba.`id_myrep_cluster`,
  MAX(d.`id_drm`) AS `id_drm`,
  NULL AS `id_drm_boq`,
  NULL AS `id_apd_boq_file`,
  'RAB DONE' AS `rab_status`,
  'Auto takeover: Batch Approval sudah mencapai ON PROSES PENGAJUAN SAKU atau lebih sebelum RAB Done.' AS `detail_rab`,
  NOW() AS `rab_done_at`,
  NULL AS `rab_done_by`,
  NULL AS `cancelled_at`,
  NULL AS `cancelled_by`,
  NULL AS `cancel_reason`,
  NOW() AS `created_at`,
  NOW() AS `updated_at`
FROM `tb_myrep_batch_approval` ba
LEFT JOIN `tb_myrep_drm` d
  ON d.`id_myrep_cluster` = ba.`id_myrep_cluster`
LEFT JOIN `tb_myrep_rab` rab_done
  ON rab_done.`id_myrep_cluster` = ba.`id_myrep_cluster`
 AND UPPER(TRIM(rab_done.`rab_status`)) = 'RAB DONE'
WHERE rab_done.`id_myrep_rab` IS NULL
  AND (
    UPPER(TRIM(ba.`staging_status`)) IN (
      'PRE_ZEYN_FINANCE_APPROVED',
      'WAITING_SAKU_FINANCE_APPROVAL',
      'WAITING_FINANCE_RELEASE',
      'RELEASED',
      'WAITING_POST_ZEYN_DOC',
      'POST_ZEYN_DOC_ON_REVIEW',
      'POST_ZEYN_DOC_APPROVED',
      'POST_ZEYN_FINANCE_ON_REVIEW',
      'WAITING_ASTRI_SUBMISSION',
      'ASTRI_ON_REVIEW',
      'NEED_REVISE_ASTRI',
      'ASTRI_APPROVED',
      'PO_DONASI',
      'INVOICE',
      'COMPLETED',
      'DONE BATCH APPROVAL'
    )
    OR ba.`id_myrep_cluster` IN (
      SELECT pre_done.`id_myrep_cluster`
      FROM (
        SELECT
          ba_pre.`id_myrep_cluster`,
          SUM(CASE WHEN i.`is_required` = 1 THEN 1 ELSE 0 END) AS required_total,
          SUM(
            CASE
              WHEN i.`is_required` = 1
               AND UPPER(TRIM(COALESCE(f.`status_file`, ''))) = 'APPROVED'
               AND UPPER(TRIM(COALESCE(f.`finance_status`, ''))) = 'APPROVED'
              THEN 1 ELSE 0
            END
          ) AS finance_approved_total
        FROM `tb_myrep_batch_approval` ba_pre
        JOIN `md_myrep_flow_doc_group` g
          ON g.`flow_type` = 'BATCH_APPROVAL'
         AND g.`group_label` = 'PRE ZEYN DOCUMENT'
         AND g.`is_active` = 1
        JOIN `md_myrep_flow_doc_item` i
          ON i.`id_doc_group` = g.`id_doc_group`
         AND i.`is_active` = 1
        LEFT JOIN `tb_myrep_flow_doc_package` p
          ON p.`id_myrep_cluster` = ba_pre.`id_myrep_cluster`
         AND p.`flow_type` = 'BATCH_APPROVAL'
         AND p.`id_doc_group` = g.`id_doc_group`
        LEFT JOIN `tb_myrep_flow_doc_file` f
          ON f.`id_doc_package` = p.`id_doc_package`
         AND f.`id_doc_item` = i.`id_doc_item`
        GROUP BY ba_pre.`id_myrep_cluster`
        HAVING required_total > 0
           AND finance_approved_total >= required_total
      ) pre_done
    )
  )
GROUP BY ba.`id_myrep_cluster`
ON DUPLICATE KEY UPDATE
  `id_drm` = COALESCE(VALUES(`id_drm`), `id_drm`),
  `rab_status` = 'RAB DONE',
  `detail_rab` = VALUES(`detail_rab`),
  `rab_done_at` = COALESCE(`rab_done_at`, NOW()),
  `cancelled_at` = NULL,
  `cancelled_by` = NULL,
  `cancel_reason` = NULL,
  `updated_at` = NOW();

SELECT
  COUNT(*) AS `advanced_batch_without_rab_done_after_patch`
FROM `tb_myrep_batch_approval` ba
LEFT JOIN `tb_myrep_rab` rab_done
  ON rab_done.`id_myrep_cluster` = ba.`id_myrep_cluster`
 AND UPPER(TRIM(rab_done.`rab_status`)) = 'RAB DONE'
WHERE rab_done.`id_myrep_rab` IS NULL
  AND (
    UPPER(TRIM(ba.`staging_status`)) IN (
      'PRE_ZEYN_FINANCE_APPROVED',
      'WAITING_SAKU_FINANCE_APPROVAL',
      'WAITING_FINANCE_RELEASE',
      'RELEASED',
      'WAITING_POST_ZEYN_DOC',
      'POST_ZEYN_DOC_ON_REVIEW',
      'POST_ZEYN_DOC_APPROVED',
      'POST_ZEYN_FINANCE_ON_REVIEW',
      'WAITING_ASTRI_SUBMISSION',
      'ASTRI_ON_REVIEW',
      'NEED_REVISE_ASTRI',
      'ASTRI_APPROVED',
      'PO_DONASI',
      'INVOICE',
      'COMPLETED',
      'DONE BATCH APPROVAL'
    )
    OR ba.`id_myrep_cluster` IN (
      SELECT pre_done.`id_myrep_cluster`
      FROM (
        SELECT
          ba_pre.`id_myrep_cluster`,
          SUM(CASE WHEN i.`is_required` = 1 THEN 1 ELSE 0 END) AS required_total,
          SUM(
            CASE
              WHEN i.`is_required` = 1
               AND UPPER(TRIM(COALESCE(f.`status_file`, ''))) = 'APPROVED'
               AND UPPER(TRIM(COALESCE(f.`finance_status`, ''))) = 'APPROVED'
              THEN 1 ELSE 0
            END
          ) AS finance_approved_total
        FROM `tb_myrep_batch_approval` ba_pre
        JOIN `md_myrep_flow_doc_group` g
          ON g.`flow_type` = 'BATCH_APPROVAL'
         AND g.`group_label` = 'PRE ZEYN DOCUMENT'
         AND g.`is_active` = 1
        JOIN `md_myrep_flow_doc_item` i
          ON i.`id_doc_group` = g.`id_doc_group`
         AND i.`is_active` = 1
        LEFT JOIN `tb_myrep_flow_doc_package` p
          ON p.`id_myrep_cluster` = ba_pre.`id_myrep_cluster`
         AND p.`flow_type` = 'BATCH_APPROVAL'
         AND p.`id_doc_group` = g.`id_doc_group`
        LEFT JOIN `tb_myrep_flow_doc_file` f
          ON f.`id_doc_package` = p.`id_doc_package`
         AND f.`id_doc_item` = i.`id_doc_item`
        GROUP BY ba_pre.`id_myrep_cluster`
        HAVING required_total > 0
           AND finance_approved_total >= required_total
      ) pre_done
    )
  );
