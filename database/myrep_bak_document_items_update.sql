/*
  Ensure BAK MyRep document master contains the 3 required items:
  1. Surat Ijin
  2. Form Survey
  3. BA Open

  Safe to re-run multiple times.
*/

SET @bak_group_id := (
    SELECT id_doc_group
    FROM md_myrep_flow_doc_group
    WHERE flow_type = 'BAK'
      AND group_label = 'BA OPEN'
      AND is_active = 1
    ORDER BY id_doc_group ASC
    LIMIT 1
);

/* Normalize legacy BA OPEN item naming */
UPDATE md_myrep_flow_doc_item
SET doc_name = 'BA Open',
    sort_no = 3,
    is_active = 1
WHERE id_doc_group = @bak_group_id
  AND UPPER(TRIM(doc_name)) = 'BA OPEN';

/* Add missing items */
INSERT INTO md_myrep_flow_doc_item (id_doc_group, doc_name, sort_no, is_active)
SELECT @bak_group_id, 'Surat Ijin', 1, 1
FROM DUAL
WHERE @bak_group_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM md_myrep_flow_doc_item
      WHERE id_doc_group = @bak_group_id
        AND UPPER(TRIM(doc_name)) = 'SURAT IJIN'
  );

INSERT INTO md_myrep_flow_doc_item (id_doc_group, doc_name, sort_no, is_active)
SELECT @bak_group_id, 'Form Survey', 2, 1
FROM DUAL
WHERE @bak_group_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM md_myrep_flow_doc_item
      WHERE id_doc_group = @bak_group_id
        AND UPPER(TRIM(doc_name)) = 'FORM SURVEY'
  );

INSERT INTO md_myrep_flow_doc_item (id_doc_group, doc_name, sort_no, is_active)
SELECT @bak_group_id, 'BA Open', 3, 1
FROM DUAL
WHERE @bak_group_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM md_myrep_flow_doc_item
      WHERE id_doc_group = @bak_group_id
        AND UPPER(TRIM(doc_name)) = 'BA OPEN'
  );

/* Final check */
SELECT
    g.id_doc_group,
    g.group_label,
    i.id_doc_item,
    i.doc_name,
    i.sort_no,
    i.is_active
FROM md_myrep_flow_doc_group g
JOIN md_myrep_flow_doc_item i
    ON i.id_doc_group = g.id_doc_group
WHERE g.id_doc_group = @bak_group_id
  AND i.is_active = 1
ORDER BY i.sort_no ASC, i.id_doc_item ASC;
