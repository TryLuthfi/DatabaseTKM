-- Cleanup master item Checklist_Dokument_MyRep (Cluster - CW ATP)
-- 1) Nonaktifkan item yang tidak dipakai lagi: Layout Validasi Sales
-- 2) Tambahkan keterangan linked source pada item terkait

UPDATE md_rfs_myrep_doc_item i
INNER JOIN md_rfs_myrep_doc_group g ON g.id_doc_group = i.id_doc_group
SET i.is_active = 0
WHERE g.scope_type = 'CLUSTER'
  AND g.sow_type = 'CW ATP'
  AND UPPER(TRIM(i.doc_name)) = 'LAYOUT VALIDASI SALES';

UPDATE md_rfs_myrep_doc_item i
INNER JOIN md_rfs_myrep_doc_group g ON g.id_doc_group = i.id_doc_group
SET i.doc_requirement_note = CASE
    WHEN TRIM(COALESCE(i.doc_requirement_note, '')) = '' THEN 'Linked ke Batch Approval Post Donasi'
    WHEN UPPER(i.doc_requirement_note) LIKE '%LINKED KE BATCH APPROVAL POST DONASI%' THEN i.doc_requirement_note
    ELSE CONCAT(i.doc_requirement_note, '\nLinked ke Batch Approval Post Donasi')
END
WHERE g.scope_type = 'CLUSTER'
  AND g.sow_type = 'CW ATP'
  AND UPPER(TRIM(i.doc_name)) IN (
      'BERITA ACARA OPEN',
      'CLUSTER APPROVAL PROPOSAL',
      'FORM CLUSTER SURVEY',
      'FORM FREE LAYANAN'
  );

UPDATE md_rfs_myrep_doc_item i
INNER JOIN md_rfs_myrep_doc_group g ON g.id_doc_group = i.id_doc_group
SET i.doc_requirement_note = CASE
    WHEN TRIM(COALESCE(i.doc_requirement_note, '')) = '' THEN 'Linked ke VALSAL'
    WHEN UPPER(i.doc_requirement_note) LIKE '%LINKED KE VALSAL%' THEN i.doc_requirement_note
    ELSE CONCAT(i.doc_requirement_note, '\nLinked ke VALSAL')
END
WHERE g.scope_type = 'CLUSTER'
  AND g.sow_type = 'CW ATP'
  AND UPPER(REPLACE(TRIM(i.doc_name), '.', '')) = 'LAYOUT SND KASAR';
