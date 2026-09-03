-- Temporary Finance HO setup for Batch_Approval_MyRep donation finance approval.
-- Finance HO user: tb_master_user_new.id = 18, nik = 7208222.

ALTER TABLE `tb_myrep_pic_mapping_city`
  ADD COLUMN IF NOT EXISTS `finance_ho` VARCHAR(255) NULL AFTER `sitac_ho`;

UPDATE `tb_myrep_pic_mapping_city`
SET `finance_ho` = '7208222',
    `updated_at` = NOW()
WHERE `is_active` = 1;

INSERT INTO `tb_myrep_role_permission`
  (`page_key`, `action_key`, `role_key`, `is_allowed`, `is_active`, `effective_start`, `effective_end`, `created_at`, `updated_at`, `submitted_at`)
VALUES
  ('Batch_Approval_MyRep', 'VIEW', 'FINANCE_HO', 1, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('Batch_Approval_MyRep', 'APPROVAL', 'FINANCE_HO', 1, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('Batch_Approval_MyRep', 'TAMBAH', 'FINANCE_HO', 0, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('Batch_Approval_MyRep', 'EDIT', 'FINANCE_HO', 0, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('Batch_Approval_MyRep', 'HAPUS', 'FINANCE_HO', 0, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('Batch_Approval_MyRep', 'APPROVAL_DAILY', 'FINANCE_HO', 0, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('Batch_Approval_MyRep', 'APPROVAL_FOTO_COMPLY', 'FINANCE_HO', 0, 1, NULL, NULL, NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `is_allowed` = VALUES(`is_allowed`),
  `is_active` = 1,
  `updated_at` = NOW(),
  `submitted_at` = NOW();
