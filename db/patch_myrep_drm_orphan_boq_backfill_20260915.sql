-- Backfill DRM headers for clusters that already have DRM BOQ/APD BOQ rows
-- but do not have a row in tb_myrep_drm.

INSERT INTO `tb_myrep_drm`
  (`id_myrep_cluster`, `drm_date`, `homepass_drm`, `status_drm`, `remark_drm`, `created_by`, `updated_by`, `created_at`, `updated_at`, `submitted_at`)
SELECT
  h.`id_myrep_cluster`,
  DATE(COALESCE(MIN(h.`submitted_at`), MIN(h.`created_at`), NOW())) AS `drm_date`,
  COALESCE(NULLIF(MAX(v.`homepass_valsal`), 0), NULLIF(MAX(c.`hp_plan`), 0), 0) AS `homepass_drm`,
  'DRAFT' AS `status_drm`,
  'Auto-created to link existing APD BOQ/BOQ data.' AS `remark_drm`,
  COALESCE(NULLIF(MAX(h.`updated_by`), 0), NULLIF(MAX(h.`created_by`), 0), NULL) AS `created_by`,
  COALESCE(NULLIF(MAX(h.`updated_by`), 0), NULLIF(MAX(h.`created_by`), 0), NULL) AS `updated_by`,
  COALESCE(MIN(h.`submitted_at`), MIN(h.`created_at`), NOW()) AS `created_at`,
  NOW() AS `updated_at`,
  COALESCE(MIN(h.`submitted_at`), MIN(h.`created_at`), NOW()) AS `submitted_at`
FROM `tb_myrep_drm_boq` h
JOIN `tb_myrep_cluster` c
  ON c.`id_myrep_cluster` = h.`id_myrep_cluster`
LEFT JOIN `tb_myrep_valsal` v
  ON v.`id_myrep_cluster` = h.`id_myrep_cluster`
LEFT JOIN `tb_myrep_drm` d
  ON d.`id_myrep_cluster` = h.`id_myrep_cluster`
WHERE d.`id_drm` IS NULL
GROUP BY h.`id_myrep_cluster`;

UPDATE `tb_myrep_drm_boq` h
JOIN `tb_myrep_drm` d
  ON d.`id_myrep_cluster` = h.`id_myrep_cluster`
SET h.`id_drm` = d.`id_drm`,
    h.`updated_at` = NOW()
WHERE h.`id_drm` IS NULL OR h.`id_drm` = 0;

UPDATE `tb_myrep_flow_doc_package` p
JOIN `tb_myrep_drm` d
  ON d.`id_myrep_cluster` = p.`id_myrep_cluster`
SET p.`ref_process_id` = d.`id_drm`,
    p.`updated_at` = NOW()
WHERE p.`flow_type` IN ('DRM', 'DRM_SUBFEEDER')
  AND (p.`ref_process_id` IS NULL OR p.`ref_process_id` = 0);

UPDATE `tb_myrep_cluster` c
JOIN `tb_myrep_drm` d
  ON d.`id_myrep_cluster` = c.`id_myrep_cluster`
SET c.`status_current` = 'DRM',
    c.`updated_at` = NOW()
WHERE UPPER(TRIM(c.`status_current`)) IN ('VALSAL', 'RELEASED', 'DONE BATCH APPROVAL');

SELECT COUNT(*) AS remaining_orphan_drm_boq
FROM `tb_myrep_drm_boq`
WHERE `id_drm` IS NULL OR `id_drm` = 0;
