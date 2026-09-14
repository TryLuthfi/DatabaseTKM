-- Patch: approval Finance untuk pengajuan Saku Batch Approval MyRep.
-- Jalankan di database VPS setelah deploy kode.

ALTER TABLE `tb_myrep_batch_approval`
    ADD COLUMN IF NOT EXISTS `saku_finance_approval_status` VARCHAR(50) NOT NULL DEFAULT 'NY' AFTER `remark_batch_approval`,
    ADD COLUMN IF NOT EXISTS `saku_finance_request_remark` TEXT NULL AFTER `saku_finance_approval_status`,
    ADD COLUMN IF NOT EXISTS `saku_finance_requested_at` DATETIME NULL AFTER `saku_finance_request_remark`,
    ADD COLUMN IF NOT EXISTS `saku_finance_requested_by` INT(11) NULL AFTER `saku_finance_requested_at`,
    ADD COLUMN IF NOT EXISTS `saku_finance_review_remark` TEXT NULL AFTER `saku_finance_requested_by`,
    ADD COLUMN IF NOT EXISTS `saku_finance_reviewed_at` DATETIME NULL AFTER `saku_finance_review_remark`,
    ADD COLUMN IF NOT EXISTS `saku_finance_reviewed_by` INT(11) NULL AFTER `saku_finance_reviewed_at`;

UPDATE `tb_myrep_batch_approval`
SET `saku_finance_approval_status` = 'APPROVED',
    `saku_finance_reviewed_at` = COALESCE(`saku_finance_reviewed_at`, `finance_submitted_at`, `submitted_to_finance_at`),
    `saku_finance_requested_at` = COALESCE(`saku_finance_requested_at`, `finance_submitted_at`, `submitted_to_finance_at`)
WHERE UPPER(TRIM(COALESCE(`staging_status`, ''))) IN (
    'WAITING_FINANCE_RELEASE',
    'RELEASED',
    'WAITING_POST_ZEYN_DOC',
    'POST_ZEYN_DOC_ON_REVIEW',
    'POST_ZEYN_DOC_APPROVED',
    'POST_ZEYN_FINANCE_ON_REVIEW',
    'WAITING_ASTRI_SUBMISSION',
    'ASTRI_ON_REVIEW',
    'ASTRI_APPROVED',
    'PO_DONASI',
    'INVOICE'
)
AND UPPER(TRIM(COALESCE(`saku_finance_approval_status`, 'NY'))) IN ('', 'NY');
