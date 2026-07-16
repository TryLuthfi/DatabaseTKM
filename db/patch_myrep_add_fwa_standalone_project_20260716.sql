SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- =========================================================
-- MyRep FWA standalone project support
-- FWA memakai flow standalone yang sama dengan Mainfeeder:
-- DRM -> ATP -> Checklist Dokument, serta PO/termin/tagihan existing.
-- Data FWA disimpan di tb_rfs_myrep_mainfeeder dengan pembeda project_type.
-- =========================================================

ALTER TABLE `tb_rfs_myrep_mainfeeder`
  ADD COLUMN IF NOT EXISTS `project_type` ENUM('MAINFEEDER','FWA') NOT NULL DEFAULT 'MAINFEEDER' AFTER `id_target`,
  ADD KEY IF NOT EXISTS `idx_myrep_mainfeeder_project_type` (`project_type`);

UPDATE `tb_rfs_myrep_mainfeeder`
SET `project_type` = 'MAINFEEDER'
WHERE `project_type` IS NULL OR TRIM(`project_type`) = '';

ALTER TABLE `tb_myrep_po_header`
  MODIFY COLUMN `id_myrep_cluster` int(11) NULL,
  ADD COLUMN IF NOT EXISTS `project_type` ENUM('CLUSTER','MAINFEEDER','FWA') NOT NULL DEFAULT 'CLUSTER' AFTER `id_myrep_cluster`,
  ADD COLUMN IF NOT EXISTS `id_mainfeeder` BIGINT NULL AFTER `project_type`,
  ADD KEY IF NOT EXISTS `idx_myrep_po_mainfeeder` (`id_mainfeeder`,`po_type`);

UPDATE `tb_myrep_po_header`
SET `project_type` = CASE
    WHEN UPPER(COALESCE(`po_type`, '')) = 'MAINFEEDER' THEN 'MAINFEEDER'
    WHEN UPPER(COALESCE(`po_type`, '')) = 'FWA' THEN 'FWA'
    ELSE 'CLUSTER'
  END
WHERE `project_type` IS NULL OR TRIM(`project_type`) = '';

ALTER TABLE `tb_myrep_po_header`
  MODIFY COLUMN `po_type` ENUM('CLUSTER','SUBFEEDER','MAINFEEDER','FWA') NOT NULL DEFAULT 'CLUSTER',
  MODIFY COLUMN `project_type` ENUM('CLUSTER','MAINFEEDER','FWA') NOT NULL DEFAULT 'CLUSTER';

UPDATE `tb_rfs_myrep_mainfeeder`
SET `project_type` = 'FWA'
WHERE UPPER(`mainfeeder_name`) LIKE '%[FWA]%'
   OR UPPER(`mainfeeder_name`) REGEXP '(^|[^A-Z0-9])FWA([^A-Z0-9]|$)';

UPDATE `tb_myrep_po_header` p
JOIN `tb_rfs_myrep_mainfeeder` mf
  ON mf.`id_mainfeeder` = p.`id_mainfeeder`
SET
  p.`po_type` = 'FWA',
  p.`project_type` = 'FWA'
WHERE mf.`project_type` = 'FWA'
  AND p.`id_mainfeeder` IS NOT NULL;
