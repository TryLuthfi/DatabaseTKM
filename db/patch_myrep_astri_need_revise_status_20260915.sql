-- Patch: tandai Batch Approval yang punya dokumen Astri rejected sebagai NEED_REVISE_ASTRI.
-- Jalankan di database VPS setelah deploy kode.

UPDATE `tb_myrep_batch_approval` ba
SET ba.`staging_status` = 'NEED_REVISE_ASTRI',
    ba.`final_astri_approved_at` = NULL,
    ba.`updated_at` = NOW()
WHERE UPPER(TRIM(COALESCE(ba.`staging_status`, ''))) IN ('WAITING_ASTRI_SUBMISSION', 'ASTRI_ON_REVIEW', 'ASTRI_APPROVED')
  AND EXISTS (
    SELECT 1
    FROM `tb_myrep_flow_doc_package` p
    JOIN `md_myrep_flow_doc_group` g
      ON g.`id_doc_group` = p.`id_doc_group`
    JOIN `tb_myrep_flow_doc_file` f
      ON f.`id_doc_package` = p.`id_doc_package`
    WHERE p.`id_myrep_cluster` = ba.`id_myrep_cluster`
      AND UPPER(TRIM(g.`group_label`)) IN ('PRE ZEYN DOCUMENT', 'POST PAYMENT ZEYN DOCUMENT')
      AND UPPER(TRIM(COALESCE(f.`astri_status`, 'NY'))) = 'REJECTED'
  );

SELECT ba.`id_myrep_cluster`, c.`cluster_name`, ba.`staging_status`
FROM `tb_myrep_batch_approval` ba
JOIN `tb_myrep_cluster` c
  ON c.`id_myrep_cluster` = ba.`id_myrep_cluster`
WHERE ba.`staging_status` = 'NEED_REVISE_ASTRI'
ORDER BY ba.`updated_at` DESC;
