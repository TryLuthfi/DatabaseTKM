UPDATE md_myrep_flow_doc_item i
JOIN md_myrep_flow_doc_group g ON g.id_doc_group = i.id_doc_group
SET
    i.doc_name = 'Tanda Terima Donasi',
    i.doc_requirement_note = 'Dokumen setelah pembayaran donasi'
WHERE g.flow_type = 'BATCH_APPROVAL'
  AND g.group_label = 'POST PAYMENT ZEYN DOCUMENT'
  AND UPPER(TRIM(i.doc_name)) = 'KWITANSI';

UPDATE tb_myrep_batch_approval ba
JOIN (
    SELECT
        p.id_myrep_cluster,
        SUM(i.is_required = 1) AS required_cnt,
        SUM(i.is_required = 1 AND f.status_file = 'APPROVED') AS approved_cnt
    FROM tb_myrep_flow_doc_package p
    JOIN md_myrep_flow_doc_group g
        ON g.id_doc_group = p.id_doc_group
        AND g.flow_type = 'BATCH_APPROVAL'
        AND g.group_label = 'POST PAYMENT ZEYN DOCUMENT'
    JOIN md_myrep_flow_doc_item i
        ON i.id_doc_group = g.id_doc_group
        AND i.is_active = 1
    LEFT JOIN tb_myrep_flow_doc_file f
        ON f.id_doc_package = p.id_doc_package
        AND f.id_doc_item = i.id_doc_item
    WHERE p.flow_type = 'BATCH_APPROVAL'
    GROUP BY p.id_myrep_cluster
) s ON s.id_myrep_cluster = ba.id_myrep_cluster
SET
    ba.staging_status = 'WAITING_ASTRI_SUBMISSION',
    ba.post_zeyn_doc_approved_at = COALESCE(ba.post_zeyn_doc_approved_at, NOW())
WHERE ba.staging_status = 'POST_ZEYN_FINANCE_ON_REVIEW'
  AND s.required_cnt > 0
  AND s.approved_cnt >= s.required_cnt;
