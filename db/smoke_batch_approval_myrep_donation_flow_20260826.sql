-- Smoke test Flow Donasi Batch_Approval_MyRep
-- Aman dijalankan di DB lokal karena seluruh perubahan di-ROLLBACK.

START TRANSACTION;

SET @cluster_id := (
  SELECT c.id_myrep_cluster
  FROM tb_myrep_cluster c
  LEFT JOIN tb_myrep_batch_approval b ON b.id_myrep_cluster = c.id_myrep_cluster
  JOIN tb_myrep_valsal v ON v.id_myrep_cluster = c.id_myrep_cluster
  WHERE b.id_batch_approval IS NULL
    AND UPPER(TRIM(COALESCE(c.status_current, ''))) = 'VALSAL'
    AND UPPER(TRIM(COALESCE(v.status_valsal, ''))) IN ('DONE', 'APPROVED')
  ORDER BY c.id_myrep_cluster DESC
  LIMIT 1
);

SET @user_id := (
  SELECT id
  FROM tb_master_user_new
  ORDER BY id ASC
  LIMIT 1
);

INSERT INTO tb_myrep_batch_approval (
  id_myrep_cluster, 
  submission_date,
  hp_donasi,
  nominal_pengajuan_area,
  nominal_release_finance,
  nominal_per_homepass,
  bank_name,
  bank_account_number,
  recipient_name,
  staging_status,
  astri_initial_submitted_at,
  created_by,
  updated_by
)
SELECT
  @cluster_id,
  CURDATE(),
  100,
  1000000,
  NULL,
  10000,
  'BANK SMOKE',
  '000111222',
  'PENERIMA SMOKE',
  'WAITING_BATCH_APPROVAL',
  NOW(),
  @user_id,
  @user_id
WHERE @cluster_id IS NOT NULL;

SET @batch_id := LAST_INSERT_ID();

UPDATE tb_myrep_batch_approval
SET staging_status = 'BATCH_APPROVED',
    astri_batch_number = CONCAT('SMOKE-', DATE_FORMAT(NOW(), '%Y%m%d%H%i%s')),
    astri_batch_approved_at = NOW()
WHERE id_batch_approval = @batch_id;

SET @pre_group_id := (
  SELECT id_doc_group
  FROM md_myrep_flow_doc_group
  WHERE flow_type = 'BATCH_APPROVAL'
    AND group_label = 'PRE ZEYN DOCUMENT'
  LIMIT 1
);

SET @post_group_id := (
  SELECT id_doc_group
  FROM md_myrep_flow_doc_group
  WHERE flow_type = 'BATCH_APPROVAL'
    AND group_label = 'POST PAYMENT ZEYN DOCUMENT'
  LIMIT 1
);

SET @pre_required := (
  SELECT COUNT(*)
  FROM md_myrep_flow_doc_item
  WHERE id_doc_group = @pre_group_id
    AND is_active = 1
    AND is_required = 1
);

SET @pre_approved_before := 0;

INSERT INTO tb_myrep_flow_doc_package (
  id_myrep_cluster,
  flow_type,
  id_doc_group,
  status_package,
  created_by,
  updated_by
)
VALUES (@cluster_id, 'BATCH_APPROVAL', @pre_group_id, 'DONE', @user_id, @user_id);

SET @pre_package_id := LAST_INSERT_ID();

INSERT INTO tb_myrep_flow_doc_file (
  id_doc_package,
  id_doc_item,
  file_name,
  file_path,
  status_file,
  uploaded_by,
  uploaded_at,
  approved_by,
  approved_at
)
SELECT
  @pre_package_id,
  id_doc_item,
  CONCAT('smoke-pre-', sort_no, '.pdf'),
  CONCAT('uploads/smoke/pre-', sort_no, '.pdf'),
  'APPROVED',
  @user_id,
  NOW(),
  @user_id,
  NOW()
FROM md_myrep_flow_doc_item
WHERE id_doc_group = @pre_group_id
  AND is_active = 1
  AND is_required = 1;

SET @pre_approved_after := (
  SELECT COUNT(*)
  FROM tb_myrep_flow_doc_file f
  JOIN md_myrep_flow_doc_item i ON i.id_doc_item = f.id_doc_item
  WHERE f.id_doc_package = @pre_package_id
    AND i.is_required = 1
    AND f.status_file = 'APPROVED'
);

UPDATE tb_myrep_batch_approval
SET staging_status = 'WAITING_FINANCE_RELEASE',
    pre_zeyn_doc_approved_at = NOW(),
    finance_submitted_at = NOW(),
    submitted_to_finance_at = NOW()
WHERE id_batch_approval = @batch_id
  AND @pre_approved_after >= @pre_required;

UPDATE tb_myrep_batch_approval
SET staging_status = 'RELEASED',
    released_at = NOW(),
    nominal_release_finance = 1000000,
    transfer_proof_file_name = 'smoke-transfer.pdf',
    transfer_proof_file_path = 'uploads/smoke/smoke-transfer.pdf'
WHERE id_batch_approval = @batch_id;

SET @post_required := (
  SELECT COUNT(*)
  FROM md_myrep_flow_doc_item
  WHERE id_doc_group = @post_group_id
    AND is_active = 1
    AND is_required = 1
);

INSERT INTO tb_myrep_flow_doc_package (
  id_myrep_cluster,
  flow_type,
  id_doc_group,
  status_package,
  created_by,
  updated_by
)
VALUES (@cluster_id, 'BATCH_APPROVAL', @post_group_id, 'DONE', @user_id, @user_id);

SET @post_package_id := LAST_INSERT_ID();

INSERT INTO tb_myrep_flow_doc_file (
  id_doc_package,
  id_doc_item,
  file_name,
  file_path,
  status_file,
  uploaded_by,
  uploaded_at,
  approved_by,
  approved_at,
  astri_status,
  astri_submitted_date,
  astri_status_updated_at
)
SELECT
  @post_package_id,
  id_doc_item,
  CONCAT('smoke-post-', sort_no, '.pdf'),
  CONCAT('uploads/smoke/post-', sort_no, '.pdf'),
  'APPROVED',
  @user_id,
  NOW(),
  @user_id,
  NOW(),
  'APPROVED',
  CURDATE(),
  NOW()
FROM md_myrep_flow_doc_item
WHERE id_doc_group = @post_group_id
  AND is_active = 1
  AND is_required = 1;

SET @post_approved_after := (
  SELECT COUNT(*)
  FROM tb_myrep_flow_doc_file f
  JOIN md_myrep_flow_doc_item i ON i.id_doc_item = f.id_doc_item
  WHERE f.id_doc_package = @post_package_id
    AND i.is_required = 1
    AND f.status_file = 'APPROVED'
);

SET @astri_approved_after := (
  SELECT COUNT(*)
  FROM tb_myrep_flow_doc_file f
  JOIN md_myrep_flow_doc_item i ON i.id_doc_item = f.id_doc_item
  WHERE f.id_doc_package = @post_package_id
    AND i.is_required = 1
    AND f.status_file = 'APPROVED'
    AND f.astri_status = 'APPROVED'
);

UPDATE tb_myrep_batch_approval
SET staging_status = 'ASTRI_APPROVED',
    post_zeyn_doc_approved_at = NOW(),
    final_astri_submitted_at = NOW(),
    final_astri_approved_at = NOW()
WHERE id_batch_approval = @batch_id
  AND @post_approved_after >= @post_required
  AND @astri_approved_after >= @post_required;

INSERT INTO tb_myrep_po_header (
  id_myrep_cluster,
  po_type,
  po_category,
  po_number,
  po_date,
  po_value,
  status_po,
  remark_po,
  created_by,
  updated_by
)
VALUES (
  @cluster_id,
  'DONASI',
  'INITIAL',
  CONCAT('PO-SMOKE-', DATE_FORMAT(NOW(), '%Y%m%d%H%i%s')),
  CURDATE(),
  1000000,
  'ISSUED',
  'PT EMR - DONASI',
  @user_id,
  @user_id
);

SET @po_header_id := LAST_INSERT_ID();

INSERT INTO tb_myrep_po_termin (
  id_po_header,
  termin_no,
  termin_percent,
  termin_value,
  status_termin,
  invoice_number,
  invoice_date,
  invoice_value,
  created_by,
  updated_by
)
VALUES (
  @po_header_id,
  1,
  100,
  1000000,
  'BILLED',
  CONCAT('INV-SMOKE-', DATE_FORMAT(NOW(), '%Y%m%d%H%i%s')),
  CURDATE(),
  1000000,
  @user_id,
  @user_id
);

SELECT
  @cluster_id AS cluster_id,
  @batch_id AS batch_id,
  @pre_required AS pre_required,
  @pre_approved_before AS pre_approved_before,
  @pre_approved_after AS pre_approved_after,
  IF(@pre_approved_before < @pre_required, 'PASS', 'FAIL') AS finance_gate_before_docs,
  IF(@pre_approved_after >= @pre_required, 'PASS', 'FAIL') AS finance_gate_after_docs,
  @post_required AS post_required,
  @post_approved_after AS post_approved_after,
  @astri_approved_after AS astri_approved_after,
  IF(@post_approved_after >= @post_required, 'PASS', 'FAIL') AS post_doc_gate,
  IF(@astri_approved_after >= @post_required, 'PASS', 'FAIL') AS astri_gate,
  (
    SELECT staging_status
    FROM tb_myrep_batch_approval
    WHERE id_batch_approval = @batch_id
  ) AS final_smoke_stage,
  (
    SELECT po_type
    FROM tb_myrep_po_header
    WHERE id_po_header = @po_header_id
  ) AS po_type,
  (
    SELECT termin_percent
    FROM tb_myrep_po_termin
    WHERE id_po_header = @po_header_id
      AND termin_no = 1
    LIMIT 1
  ) AS termin_percent;

ROLLBACK;
