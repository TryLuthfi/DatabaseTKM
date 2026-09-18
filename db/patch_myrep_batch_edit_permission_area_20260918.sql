-- Patch MyRep Batch Approval edit permission for area/HO roles.
-- Reason: updateBatchApproval is guarded as EDIT by global page access,
-- while controller-level edit logic already allows ADMIN_AREA, SITAC_HO, SND_AREA, and FINANCE_HO.
-- Run on VPS after deploying the related code/data changes.

INSERT INTO `tb_myrep_role_permission`
  (`page_key`, `action_key`, `role_key`, `is_allowed`, `is_active`, `effective_start`, `effective_end`, `created_at`, `updated_at`, `submitted_at`)
VALUES
  ('Batch_Approval_MyRep', 'EDIT', 'ADMIN_AREA', 1, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('Batch_Approval_MyRep', 'EDIT', 'SITAC_HO', 1, 1, NULL, NULL, NOW(), NOW(), NOW()),
  ('Batch_Approval_MyRep', 'EDIT', 'FINANCE_HO', 1, 1, NULL, NULL, NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `is_allowed` = VALUES(`is_allowed`),
  `is_active` = 1,
  `updated_at` = NOW(),
  `submitted_at` = NOW();

SELECT
  `page_key`,
  `action_key`,
  `role_key`,
  `is_allowed`,
  `is_active`
FROM `tb_myrep_role_permission`
WHERE `page_key` = 'Batch_Approval_MyRep'
  AND `action_key` = 'EDIT'
  AND `role_key` IN ('ADMIN_AREA', 'SITAC_HO', 'SND_AREA', 'FINANCE_HO')
ORDER BY `role_key`;

