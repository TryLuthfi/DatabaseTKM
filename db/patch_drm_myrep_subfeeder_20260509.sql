ALTER TABLE `md_myrep_flow_doc_group`
MODIFY COLUMN `flow_type` ENUM('BAK','VALSAL','BATCH_APPROVAL','POST_DONASI','DRM','DRM_SUBFEEDER') NOT NULL;

ALTER TABLE `tb_myrep_flow_doc_package`
MODIFY COLUMN `flow_type` ENUM('BAK','VALSAL','BATCH_APPROVAL','POST_DONASI','DRM','DRM_SUBFEEDER') NOT NULL;

ALTER TABLE `tb_myrep_drm_boq`
ADD COLUMN `scope_type` ENUM('CLUSTER','SUBFEEDER') NOT NULL DEFAULT 'CLUSTER' AFTER `id_myrep_cluster`;

ALTER TABLE `tb_myrep_boq_baseline`
ADD COLUMN `scope_type` ENUM('CLUSTER','SUBFEEDER') NOT NULL DEFAULT 'CLUSTER' AFTER `id_myrep_cluster`;

ALTER TABLE `tb_myrep_drm_boq`
DROP INDEX `uniq_myrep_drm_boq_cluster`,
ADD UNIQUE KEY `uniq_myrep_drm_boq_cluster_scope` (`id_myrep_cluster`,`scope_type`),
ADD KEY `idx_myrep_drm_boq_scope` (`scope_type`);

ALTER TABLE `tb_myrep_boq_baseline`
DROP INDEX `uniq_myrep_boq_baseline_cluster`,
ADD UNIQUE KEY `uniq_myrep_boq_baseline_cluster_scope` (`id_myrep_cluster`,`scope_type`,`status_baseline`),
ADD KEY `idx_myrep_boq_baseline_scope` (`scope_type`);

INSERT INTO `md_myrep_flow_doc_group` (`flow_type`, `group_label`, `sort_no`, `is_active`, `created_at`)
SELECT
    'DRM_SUBFEEDER',
    src.`group_label`,
    src.`sort_no`,
    src.`is_active`,
    NOW()
FROM `md_myrep_flow_doc_group` src
WHERE src.`flow_type` = 'DRM'
AND NOT EXISTS (
    SELECT 1
    FROM `md_myrep_flow_doc_group` tgt
    WHERE tgt.`flow_type` = 'DRM_SUBFEEDER'
      AND tgt.`group_label` = src.`group_label`
);

INSERT INTO `md_myrep_flow_doc_item`
(`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`, `created_at`)
SELECT
    tgt.`id_doc_group`,
    src_item.`doc_name`,
    src_item.`doc_requirement_note`,
    src_item.`verification_team`,
    src_item.`sort_no`,
    src_item.`is_required`,
    src_item.`is_active`,
    NOW()
FROM `md_myrep_flow_doc_group` src_group
INNER JOIN `md_myrep_flow_doc_item` src_item
    ON src_item.`id_doc_group` = src_group.`id_doc_group`
INNER JOIN `md_myrep_flow_doc_group` tgt
    ON tgt.`flow_type` = 'DRM_SUBFEEDER'
   AND tgt.`group_label` = src_group.`group_label`
WHERE src_group.`flow_type` = 'DRM'
AND NOT EXISTS (
    SELECT 1
    FROM `md_myrep_flow_doc_item` chk
    WHERE chk.`id_doc_group` = tgt.`id_doc_group`
      AND chk.`doc_name` = src_item.`doc_name`
);
