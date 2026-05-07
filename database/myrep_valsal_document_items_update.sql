/*
  Ensure VALSAL MyRep document master contains the 3 required items:
  1. SND Kasar
  2. Form SND
  3. Boundary KMZ

  Safe to re-run multiple times.
*/

SET @valsal_group_id := (
    SELECT id_doc_group
    FROM md_myrep_flow_doc_group
    WHERE flow_type = 'VALSAL'
      AND group_label = 'VALIDASI SALES'
      AND is_active = 1
    ORDER BY id_doc_group ASC
    LIMIT 1
);

/* Normalize legacy item naming */
UPDATE md_myrep_flow_doc_item
SET doc_name = 'SND Kasar',
    sort_no = 1,
    is_active = 1
WHERE id_doc_group = @valsal_group_id
  AND UPPER(TRIM(doc_name)) = 'SND KASAR';

UPDATE md_myrep_flow_doc_item
SET doc_name = 'Form SND',
    sort_no = 2,
    is_active = 1
WHERE id_doc_group = @valsal_group_id
  AND UPPER(TRIM(doc_name)) = 'FORM SND';

UPDATE md_myrep_flow_doc_item
SET doc_name = 'Boundary KMZ',
    sort_no = 3,
    is_active = 1
WHERE id_doc_group = @valsal_group_id
  AND UPPER(TRIM(doc_name)) = 'BOUNDARY KMZ';

/* Add missing items */
INSERT INTO md_myrep_flow_doc_item (id_doc_group, doc_name, sort_no, is_active)
SELECT @valsal_group_id, 'SND Kasar', 1, 1
FROM DUAL
WHERE @valsal_group_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM md_myrep_flow_doc_item
      WHERE id_doc_group = @valsal_group_id
        AND UPPER(TRIM(doc_name)) = 'SND KASAR'
  );

INSERT INTO md_myrep_flow_doc_item (id_doc_group, doc_name, sort_no, is_active)
SELECT @valsal_group_id, 'Form SND', 2, 1
FROM DUAL
WHERE @valsal_group_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM md_myrep_flow_doc_item
      WHERE id_doc_group = @valsal_group_id
        AND UPPER(TRIM(doc_name)) = 'FORM SND'
  );

INSERT INTO md_myrep_flow_doc_item (id_doc_group, doc_name, sort_no, is_active)
SELECT @valsal_group_id, 'Boundary KMZ', 3, 1
FROM DUAL
WHERE @valsal_group_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM md_myrep_flow_doc_item
      WHERE id_doc_group = @valsal_group_id
        AND UPPER(TRIM(doc_name)) = 'BOUNDARY KMZ'
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
WHERE g.id_doc_group = @valsal_group_id
  AND i.is_active = 1
ORDER BY i.sort_no ASC, i.id_doc_item ASC;
