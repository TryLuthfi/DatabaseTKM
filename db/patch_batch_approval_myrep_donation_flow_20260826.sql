-- Patch Flow Donasi baru untuk Batch_Approval_MyRep
-- Tujuan:
-- 1. Mengganti staging Batch Approval menjadi flow donasi lengkap.
-- 2. Mengunci finance release setelah 9 dokumen pra-finance approved.
-- 3. Mengunci submit Astri/PO/Invoice setelah 6 dokumen post-payment approved.

ALTER TABLE `tb_myrep_batch_approval`
  MODIFY COLUMN `staging_status` VARCHAR(50) NOT NULL DEFAULT 'WAITING_BATCH_APPROVAL',
  ADD COLUMN IF NOT EXISTS `astri_initial_submitted_at` DATETIME DEFAULT NULL AFTER `staging_status`,
  ADD COLUMN IF NOT EXISTS `astri_batch_approved_at` DATETIME DEFAULT NULL AFTER `astri_initial_submitted_at`,
  ADD COLUMN IF NOT EXISTS `hold_at` DATETIME DEFAULT NULL AFTER `astri_batch_approved_at`,
  ADD COLUMN IF NOT EXISTS `hold_remark` TEXT DEFAULT NULL AFTER `hold_at`,
  ADD COLUMN IF NOT EXISTS `rejected_at` DATETIME DEFAULT NULL AFTER `hold_remark`,
  ADD COLUMN IF NOT EXISTS `rejected_remark` TEXT DEFAULT NULL AFTER `rejected_at`,
  ADD COLUMN IF NOT EXISTS `pre_zeyn_doc_approved_at` DATETIME DEFAULT NULL AFTER `rejected_remark`,
  ADD COLUMN IF NOT EXISTS `finance_submitted_at` DATETIME DEFAULT NULL AFTER `pre_zeyn_doc_approved_at`,
  ADD COLUMN IF NOT EXISTS `post_zeyn_doc_approved_at` DATETIME DEFAULT NULL AFTER `released_at`,
  ADD COLUMN IF NOT EXISTS `final_astri_submitted_at` DATETIME DEFAULT NULL AFTER `post_zeyn_doc_approved_at`,
  ADD COLUMN IF NOT EXISTS `final_astri_approved_at` DATETIME DEFAULT NULL AFTER `final_astri_submitted_at`,
  ADD COLUMN IF NOT EXISTS `po_donasi_number` VARCHAR(150) DEFAULT NULL AFTER `final_astri_approved_at`,
  ADD COLUMN IF NOT EXISTS `po_donasi_date` DATE DEFAULT NULL AFTER `po_donasi_number`,
  ADD COLUMN IF NOT EXISTS `po_donasi_value` DECIMAL(18,2) DEFAULT NULL AFTER `po_donasi_date`,
  ADD COLUMN IF NOT EXISTS `po_donasi_status` VARCHAR(50) DEFAULT NULL AFTER `po_donasi_value`,
  ADD COLUMN IF NOT EXISTS `invoice_donasi_number` VARCHAR(150) DEFAULT NULL AFTER `po_donasi_status`,
  ADD COLUMN IF NOT EXISTS `invoice_donasi_date` DATE DEFAULT NULL AFTER `invoice_donasi_number`,
  ADD COLUMN IF NOT EXISTS `invoice_donasi_value` DECIMAL(18,2) DEFAULT NULL AFTER `invoice_donasi_date`,
  ADD COLUMN IF NOT EXISTS `invoice_donasi_status` VARCHAR(50) DEFAULT NULL AFTER `invoice_donasi_value`,
  ADD COLUMN IF NOT EXISTS `invoice_donasi_remark` TEXT DEFAULT NULL AFTER `invoice_donasi_status`,
  ADD KEY IF NOT EXISTS `idx_myrep_batch_donation_status` (`staging_status`),
  ADD KEY IF NOT EXISTS `idx_myrep_batch_astri_batch` (`astri_batch_number`);

ALTER TABLE `tb_myrep_flow_doc_file`
  ADD COLUMN IF NOT EXISTS `astri_submitted_date` DATE DEFAULT NULL AFTER `approved_at`,
  ADD COLUMN IF NOT EXISTS `astri_approved_date` DATE DEFAULT NULL AFTER `astri_submitted_date`,
  ADD COLUMN IF NOT EXISTS `astri_status` VARCHAR(50) NOT NULL DEFAULT 'NY' AFTER `astri_approved_date`,
  ADD COLUMN IF NOT EXISTS `astri_status_updated_at` DATETIME DEFAULT NULL AFTER `astri_status`,
  ADD COLUMN IF NOT EXISTS `astri_remark` TEXT DEFAULT NULL AFTER `astri_status_updated_at`,
  ADD KEY IF NOT EXISTS `idx_myrep_flow_doc_astri_status` (`astri_status`);

ALTER TABLE `tb_myrep_po_header`
  MODIFY COLUMN `po_type` ENUM('CLUSTER','SUBFEEDER','MAINFEEDER','FWA','DONASI') NOT NULL DEFAULT 'CLUSTER';

INSERT INTO `md_myrep_flow_doc_group` (`flow_type`, `group_label`, `sort_no`, `is_active`)
SELECT 'BATCH_APPROVAL', 'PRE ZEYN DOCUMENT', 2, 1
WHERE NOT EXISTS (
  SELECT 1 FROM `md_myrep_flow_doc_group`
  WHERE `flow_type` = 'BATCH_APPROVAL' AND `group_label` = 'PRE ZEYN DOCUMENT'
);

INSERT INTO `md_myrep_flow_doc_group` (`flow_type`, `group_label`, `sort_no`, `is_active`)
SELECT 'BATCH_APPROVAL', 'POST PAYMENT ZEYN DOCUMENT', 3, 1
WHERE NOT EXISTS (
  SELECT 1 FROM `md_myrep_flow_doc_group`
  WHERE `flow_type` = 'BATCH_APPROVAL' AND `group_label` = 'POST PAYMENT ZEYN DOCUMENT'
);

SET @pre_zeyn_group_id := (
  SELECT `id_doc_group` FROM `md_myrep_flow_doc_group`
  WHERE `flow_type` = 'BATCH_APPROVAL' AND `group_label` = 'PRE ZEYN DOCUMENT'
  LIMIT 1
);
  
SET @post_zeyn_group_id := (
  SELECT `id_doc_group` FROM `md_myrep_flow_doc_group`
  WHERE `flow_type` = 'BATCH_APPROVAL' AND `group_label` = 'POST PAYMENT ZEYN DOCUMENT'
  LIMIT 1
);

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @pre_zeyn_group_id, 'Screenshot Evidence Upload DRM di Astri', 'Upload gambar screenshot evidence upload DRM di Astri', 'SITAC HO', 1, 1, 1
WHERE @pre_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @pre_zeyn_group_id AND `doc_name` = 'Screenshot Evidence Upload DRM di Astri');

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @pre_zeyn_group_id, 'Surat Ijin RT/RW', 'Dokumen pendukung pengajuan donasi', 'SITAC HO', 2, 1, 1
WHERE @pre_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @pre_zeyn_group_id AND `doc_name` = 'Surat Ijin RT/RW');

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @pre_zeyn_group_id, 'Form Cluster Survey', 'Dokumen pendukung pengajuan donasi', 'SITAC HO', 3, 1, 1
WHERE @pre_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @pre_zeyn_group_id AND `doc_name` = 'Form Cluster Survey');

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @pre_zeyn_group_id, 'BAP Open', 'Dokumen pendukung pengajuan donasi', 'SITAC HO', 4, 1, 1
WHERE @pre_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @pre_zeyn_group_id AND `doc_name` = 'BAP Open');

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @pre_zeyn_group_id, 'BAP SND & SND Kasar', 'Dokumen pendukung pengajuan donasi', 'SITAC HO', 5, 1, 1
WHERE @pre_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @pre_zeyn_group_id AND `doc_name` = 'BAP SND & SND Kasar');

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @pre_zeyn_group_id, 'Cluster Approval', 'Dokumen pendukung pengajuan donasi', 'SITAC HO', 6, 1, 1
WHERE @pre_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @pre_zeyn_group_id AND `doc_name` = 'Cluster Approval');

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @pre_zeyn_group_id, 'Perjanjian Donasi & Pemberian Izin', 'Dokumen pendukung pengajuan donasi', 'SITAC HO', 7, 1, 1
WHERE @pre_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @pre_zeyn_group_id AND `doc_name` = 'Perjanjian Donasi & Pemberian Izin');

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @pre_zeyn_group_id, 'KTP Penerima Donasi', 'Dokumen pendukung pengajuan donasi', 'SITAC HO', 8, 1, 1
WHERE @pre_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @pre_zeyn_group_id AND `doc_name` = 'KTP Penerima Donasi');

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @pre_zeyn_group_id, 'Form Free Wifi & KTP', 'Opsional jika ada free wifi', 'SITAC HO', 9, 0, 1
WHERE @pre_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @pre_zeyn_group_id AND `doc_name` = 'Form Free Wifi & KTP');

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @post_zeyn_group_id, 'Kwitansi', 'Dokumen setelah pembayaran donasi', 'SITAC HO', 1, 1, 1
WHERE @post_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @post_zeyn_group_id AND `doc_name` = 'Kwitansi');

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @post_zeyn_group_id, 'Bukti Transfer', 'Area wajib upload ulang bukti transfer', 'SITAC HO', 2, 1, 1
WHERE @post_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @post_zeyn_group_id AND `doc_name` = 'Bukti Transfer');

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @post_zeyn_group_id, 'Bukti Penyerahan Dana', 'Dokumen setelah pembayaran donasi', 'SITAC HO', 3, 1, 1
WHERE @post_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @post_zeyn_group_id AND `doc_name` = 'Bukti Penyerahan Dana');

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @post_zeyn_group_id, 'Dokumentasi CSR Banner', 'Upload PDF dokumentasi CSR banner', 'SITAC HO', 4, 1, 1
WHERE @post_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @post_zeyn_group_id AND `doc_name` = 'Dokumentasi CSR Banner');

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @post_zeyn_group_id, 'Dokumentasi Banner Pre Sales', 'Upload PDF dokumentasi banner pre sales', 'SITAC HO', 5, 1, 1
WHERE @post_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @post_zeyn_group_id AND `doc_name` = 'Dokumentasi Banner Pre Sales');

INSERT INTO `md_myrep_flow_doc_item` (`id_doc_group`, `doc_name`, `doc_requirement_note`, `verification_team`, `sort_no`, `is_required`, `is_active`)
SELECT @post_zeyn_group_id, 'Dokumentasi Sosialisasi Warga', 'Upload PDF dokumentasi sosialisasi warga', 'SITAC HO', 6, 1, 1
WHERE @post_zeyn_group_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `md_myrep_flow_doc_item` WHERE `id_doc_group` = @post_zeyn_group_id AND `doc_name` = 'Dokumentasi Sosialisasi Warga');
