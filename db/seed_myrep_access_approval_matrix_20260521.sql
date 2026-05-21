-- Seed baseline akses & line approval MyRep
-- Jalankan setelah patch_myrep_access_approval_matrix_20260521.sql

START TRANSACTION;

-- 1) VIEW: semua role yang ada di matrix bisa lihat modul
INSERT INTO `tb_myrep_role_permission` (`page_key`,`action_key`,`role_key`,`is_allowed`,`is_active`)
VALUES
('BAK_MyRep','VIEW','RPM_AREA',1,1),('BAK_MyRep','VIEW','SM_AREA',1,1),('BAK_MyRep','VIEW','SPV_AREA',1,1),('BAK_MyRep','VIEW','SND_AREA',1,1),('BAK_MyRep','VIEW','ADMIN_AREA',1,1),('BAK_MyRep','VIEW','SND_HO',1,1),('BAK_MyRep','VIEW','ATP_HO',1,1),('BAK_MyRep','VIEW','RFS_HO',1,1),('BAK_MyRep','VIEW','SITAC_HO',1,1),('BAK_MyRep','VIEW','DC_HO',1,1),('BAK_MyRep','VIEW','QA_HO',1,1),
('VALSAL_MyRep','VIEW','RPM_AREA',1,1),('VALSAL_MyRep','VIEW','SM_AREA',1,1),('VALSAL_MyRep','VIEW','SPV_AREA',1,1),('VALSAL_MyRep','VIEW','SND_AREA',1,1),('VALSAL_MyRep','VIEW','ADMIN_AREA',1,1),('VALSAL_MyRep','VIEW','SND_HO',1,1),('VALSAL_MyRep','VIEW','RFS_HO',1,1),('VALSAL_MyRep','VIEW','SITAC_HO',1,1),('VALSAL_MyRep','VIEW','DC_HO',1,1),('VALSAL_MyRep','VIEW','QA_HO',1,1),
('Batch_Approval_MyRep','VIEW','RPM_AREA',1,1),('Batch_Approval_MyRep','VIEW','SM_AREA',1,1),('Batch_Approval_MyRep','VIEW','SPV_AREA',1,1),('Batch_Approval_MyRep','VIEW','SND_AREA',1,1),('Batch_Approval_MyRep','VIEW','ADMIN_AREA',1,1),('Batch_Approval_MyRep','VIEW','SND_HO',1,1),('Batch_Approval_MyRep','VIEW','RFS_HO',1,1),('Batch_Approval_MyRep','VIEW','SITAC_HO',1,1),('Batch_Approval_MyRep','VIEW','DC_HO',1,1),('Batch_Approval_MyRep','VIEW','QA_HO',1,1),
('DRM_MyRep','VIEW','RPM_AREA',1,1),('DRM_MyRep','VIEW','SM_AREA',1,1),('DRM_MyRep','VIEW','SPV_AREA',1,1),('DRM_MyRep','VIEW','SND_AREA',1,1),('DRM_MyRep','VIEW','ADMIN_AREA',1,1),('DRM_MyRep','VIEW','SND_HO',1,1),('DRM_MyRep','VIEW','RFS_HO',1,1),('DRM_MyRep','VIEW','SITAC_HO',1,1),('DRM_MyRep','VIEW','DC_HO',1,1),('DRM_MyRep','VIEW','QA_HO',1,1),
('Implementasi_BOQ_MyRep','VIEW','RPM_AREA',1,1),('Implementasi_BOQ_MyRep','VIEW','SM_AREA',1,1),('Implementasi_BOQ_MyRep','VIEW','SPV_AREA',1,1),('Implementasi_BOQ_MyRep','VIEW','SND_AREA',1,1),('Implementasi_BOQ_MyRep','VIEW','ADMIN_AREA',1,1),('Implementasi_BOQ_MyRep','VIEW','SND_HO',1,1),('Implementasi_BOQ_MyRep','VIEW','ATP_HO',1,1),('Implementasi_BOQ_MyRep','VIEW','RFS_HO',1,1),('Implementasi_BOQ_MyRep','VIEW','SITAC_HO',1,1),('Implementasi_BOQ_MyRep','VIEW','DC_HO',1,1),('Implementasi_BOQ_MyRep','VIEW','QA_HO',1,1),
('PO_MyRep','VIEW','RPM_AREA',1,1),('PO_MyRep','VIEW','SM_AREA',1,1),('PO_MyRep','VIEW','ADMIN_AREA',1,1),('PO_MyRep','VIEW','SND_HO',1,1),('PO_MyRep','VIEW','ATP_HO',1,1),('PO_MyRep','VIEW','RFS_HO',1,1),('PO_MyRep','VIEW','SITAC_HO',1,1),('PO_MyRep','VIEW','DC_HO',1,1),('PO_MyRep','VIEW','QA_HO',1,1),
('Monitoring_RFS_MyRep','VIEW','RPM_AREA',1,1),('Monitoring_RFS_MyRep','VIEW','SM_AREA',1,1),('Monitoring_RFS_MyRep','VIEW','SPV_AREA',1,1),('Monitoring_RFS_MyRep','VIEW','SND_AREA',1,1),('Monitoring_RFS_MyRep','VIEW','ADMIN_AREA',1,1),('Monitoring_RFS_MyRep','VIEW','SND_HO',1,1),('Monitoring_RFS_MyRep','VIEW','RFS_HO',1,1),('Monitoring_RFS_MyRep','VIEW','DC_HO',1,1),('Monitoring_RFS_MyRep','VIEW','QA_HO',1,1),
('ATP_MyRep','VIEW','RPM_AREA',1,1),('ATP_MyRep','VIEW','SM_AREA',1,1),('ATP_MyRep','VIEW','SPV_AREA',1,1),('ATP_MyRep','VIEW','SND_AREA',1,1),('ATP_MyRep','VIEW','ADMIN_AREA',1,1),('ATP_MyRep','VIEW','SND_HO',1,1),('ATP_MyRep','VIEW','ATP_HO',1,1),('ATP_MyRep','VIEW','RFS_HO',1,1),('ATP_MyRep','VIEW','SITAC_HO',1,1),('ATP_MyRep','VIEW','DC_HO',1,1),('ATP_MyRep','VIEW','QA_HO',1,1),
('Checklist_Dokument_MyRep','VIEW','RPM_AREA',1,1),('Checklist_Dokument_MyRep','VIEW','SM_AREA',1,1),('Checklist_Dokument_MyRep','VIEW','SPV_AREA',1,1),('Checklist_Dokument_MyRep','VIEW','SND_AREA',1,1),('Checklist_Dokument_MyRep','VIEW','ADMIN_AREA',1,1),('Checklist_Dokument_MyRep','VIEW','SND_HO',1,1),('Checklist_Dokument_MyRep','VIEW','ATP_HO',1,1),('Checklist_Dokument_MyRep','VIEW','RFS_HO',1,1),('Checklist_Dokument_MyRep','VIEW','SITAC_HO',1,1),('Checklist_Dokument_MyRep','VIEW','DC_HO',1,1),('Checklist_Dokument_MyRep','VIEW','QA_HO',1,1)
ON DUPLICATE KEY UPDATE
`is_allowed`=VALUES(`is_allowed`), `is_active`=VALUES(`is_active`), `updated_at`=CURRENT_TIMESTAMP;

-- 2) Hak tindakan baseline (bisa disesuaikan lagi dari matrix final)
INSERT INTO `tb_myrep_role_permission` (`page_key`,`action_key`,`role_key`,`is_allowed`,`is_active`)
VALUES
('DRM_MyRep','TAMBAH','SND_AREA',1,1),('DRM_MyRep','EDIT','SND_AREA',1,1),('DRM_MyRep','HAPUS','SND_HO',1,1),('DRM_MyRep','APPROVAL','SND_HO',1,1),
('VALSAL_MyRep','TAMBAH','SND_AREA',1,1),('VALSAL_MyRep','EDIT','SND_AREA',1,1),('VALSAL_MyRep','HAPUS','SND_HO',1,1),('VALSAL_MyRep','APPROVAL','SND_HO',1,1),
('BAK_MyRep','TAMBAH','SND_AREA',1,1),('BAK_MyRep','EDIT','SND_AREA',1,1),('BAK_MyRep','HAPUS','SITAC_HO',1,1),('BAK_MyRep','APPROVAL','SITAC_HO',1,1),
('Batch_Approval_MyRep','TAMBAH','SND_AREA',1,1),('Batch_Approval_MyRep','EDIT','SND_AREA',1,1),('Batch_Approval_MyRep','HAPUS','SITAC_HO',1,1),('Batch_Approval_MyRep','APPROVAL','SITAC_HO',1,1),
('Implementasi_BOQ_MyRep','TAMBAH','SM_AREA',1,1),('Implementasi_BOQ_MyRep','EDIT','SM_AREA',1,1),('Implementasi_BOQ_MyRep','APPROVAL_DAILY','RPM_AREA',1,1),('Implementasi_BOQ_MyRep','APPROVAL_FOTO_COMPLY','ATP_HO',1,1),
('PO_MyRep','TAMBAH','QA_HO',1,1),('PO_MyRep','EDIT','QA_HO',1,1),('PO_MyRep','HAPUS','QA_HO',1,1),('PO_MyRep','APPROVAL','QA_HO',1,1),
('Monitoring_RFS_MyRep','TAMBAH','RFS_HO',1,1),('Monitoring_RFS_MyRep','EDIT','RFS_HO',1,1),('Monitoring_RFS_MyRep','HAPUS','RFS_HO',1,1),('Monitoring_RFS_MyRep','APPROVAL','RFS_HO',1,1),
('ATP_MyRep','TAMBAH','ATP_HO',1,1),('ATP_MyRep','EDIT','ATP_HO',1,1),('ATP_MyRep','HAPUS','ATP_HO',1,1),('ATP_MyRep','APPROVAL','ATP_HO',1,1),
('Checklist_Dokument_MyRep','TAMBAH','ADMIN_AREA',1,1),('Checklist_Dokument_MyRep','EDIT','ADMIN_AREA',1,1),('Checklist_Dokument_MyRep','HAPUS','DC_HO',1,1),('Checklist_Dokument_MyRep','APPROVAL','DC_HO',1,1)
ON DUPLICATE KEY UPDATE
`is_allowed`=VALUES(`is_allowed`), `is_active`=VALUES(`is_active`), `updated_at`=CURRENT_TIMESTAMP;

-- 3) Line approval berjenjang contoh baseline
INSERT INTO `tb_myrep_approval_line`
(`page_key`,`action_key`,`step_no`,`target_type`,`target_role`,`target_user_id`,`fallback_type`,`fallback_role`,`fallback_user_id`,`is_final_approver`,`is_active`)
VALUES
('DRM_MyRep','APPROVAL',1,'CITY_ROLE','SND_AREA',NULL,'CITY_ROLE','SND_HO',NULL,0,1),
('DRM_MyRep','APPROVAL',2,'CITY_ROLE','SND_HO',NULL,NULL,NULL,NULL,1,1),

('VALSAL_MyRep','APPROVAL',1,'CITY_ROLE','SND_AREA',NULL,'CITY_ROLE','SND_HO',NULL,0,1),
('VALSAL_MyRep','APPROVAL',2,'CITY_ROLE','SND_HO',NULL,NULL,NULL,NULL,1,1),

('BAK_MyRep','APPROVAL',1,'CITY_ROLE','SND_AREA',NULL,'CITY_ROLE','SITAC_HO',NULL,0,1),
('BAK_MyRep','APPROVAL',2,'CITY_ROLE','SITAC_HO',NULL,NULL,NULL,NULL,1,1),

('Batch_Approval_MyRep','APPROVAL',1,'CITY_ROLE','SND_AREA',NULL,'CITY_ROLE','SITAC_HO',NULL,0,1),
('Batch_Approval_MyRep','APPROVAL',2,'CITY_ROLE','SITAC_HO',NULL,NULL,NULL,NULL,1,1),

('Implementasi_BOQ_MyRep','APPROVAL_DAILY',1,'CITY_ROLE','RPM_AREA',NULL,'CITY_ROLE','RFS_HO',NULL,1,1),
('Implementasi_BOQ_MyRep','APPROVAL_FOTO_COMPLY',1,'CITY_ROLE','ATP_HO',NULL,'CITY_ROLE','RFS_HO',NULL,1,1),

('PO_MyRep','APPROVAL',1,'CITY_ROLE','QA_HO',NULL,NULL,NULL,NULL,1,1),
('Monitoring_RFS_MyRep','APPROVAL',1,'CITY_ROLE','RFS_HO',NULL,NULL,NULL,NULL,1,1),
('ATP_MyRep','APPROVAL',1,'CITY_ROLE','ATP_HO',NULL,NULL,NULL,NULL,1,1),
('Checklist_Dokument_MyRep','APPROVAL',1,'CITY_ROLE','DC_HO',NULL,NULL,NULL,NULL,1,1)
ON DUPLICATE KEY UPDATE
`target_type`=VALUES(`target_type`),
`target_role`=VALUES(`target_role`),
`target_user_id`=VALUES(`target_user_id`),
`fallback_type`=VALUES(`fallback_type`),
`fallback_role`=VALUES(`fallback_role`),
`fallback_user_id`=VALUES(`fallback_user_id`),
`is_final_approver`=VALUES(`is_final_approver`),
`is_active`=VALUES(`is_active`),
`updated_at`=CURRENT_TIMESTAMP;

COMMIT;

