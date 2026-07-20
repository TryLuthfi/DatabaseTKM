SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Delete PO Monitor yang salah input berdasarkan DETAIL PO.
-- Guard: hanya menghapus PO dari fitur Tambah PO / Batch manual PO Monitor
--        (source_file = BATCH_MANUAL_PO dan notes = Batch manual tambah PO).
-- Default hanya audit. Ubah @do_delete jadi 1 jika MATCHED/COUNT sudah sesuai.

SET @do_delete := 0;

START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS tmp_delete_po_monitor_details (
  detail_po varchar(500) NOT NULL PRIMARY KEY
) ENGINE=Memory;

TRUNCATE TABLE tmp_delete_po_monitor_details;

INSERT INTO tmp_delete_po_monitor_details (detail_po) VALUES
('DONASI Cluster - WARUNGPRING RW 01 PEMALANG'),
('DONASI Cluster - WARUNGPRING RW 03 PEMALANG'),
('DONASI Cluster - DESA GUNUNGJATI BOJONG TEGAL'),
('DONASI Cluster - CICANGKANG GIRANG RW 06 BANDUNG'),
('DONASI Cluster - DESA BATUNYANA BOJONG TEGAL'),
('DONASI Cluster - LINGKUNGAN WATAMPONE BONE'),
('DONASI Cluster - CIBUYUR RW 03 PEMALANG'),
('DONASI Cluster - CIBUYUR RW 04 PEMALANG'),
('DONASI Cluster - SALOK API DARAT RT 01, 02, 08, DAN 09 KUTAI KARTANEGARA'),
('DONASI Cluster - LOA IPUH RT 36 SAMPAI 45 KUTAI KARTANEGARA'),
('DONASI Cluster - CIKANDANG RW 01 BREBES'),
('DONASI Cluster - CIKANDANG RW 02'),
('DONASI Cluster - CIKANDANG RW 03'),
('DONASI Cluster - CIKANDANG RW 04'),
('DONASI Cluster - CIKANDANG RW 05'),
('DONASI Cluster - DESA KALIJAMBU BOJONG TEGAL'),
('DONASI Cluster - RAJI RW 01 DEMAK'),
('DONASI Cluster - RAJI RW 02 DEMAK'),
('DONASI Cluster - RAJI RW 03 DEMAK'),
('DONASI Cluster - RAJI RW 04 DEMAK'),
('DONASI Cluster - RANCAPANGGUNG RW 09 BANDUNG'),
('DONASI Cluster - DESA KEDAWUNG BOJONG TEGAL');

CREATE TEMPORARY TABLE IF NOT EXISTS tmp_delete_po_monitor_ids (
  id_po int(11) NOT NULL PRIMARY KEY,
  po_number varchar(100) NOT NULL
) ENGINE=Memory;

TRUNCATE TABLE tmp_delete_po_monitor_ids;

INSERT INTO tmp_delete_po_monitor_ids (id_po, po_number)
SELECT DISTINCT p.id_po, p.po_number
FROM tb_po p
JOIN tb_po_term t ON t.id_po = p.id_po
JOIN tb_po_term_allocation a ON a.id_term = t.id_term
JOIN tmp_delete_po_monitor_details d
  ON TRIM(CONVERT(a.detail_po USING utf8mb4)) COLLATE utf8mb4_unicode_ci
   = TRIM(CONVERT(d.detail_po USING utf8mb4)) COLLATE utf8mb4_unicode_ci
WHERE COALESCE(p.source_file, '') = 'BATCH_MANUAL_PO'
  AND COALESCE(p.notes, '') = 'Batch manual tambah PO';

SELECT
  'MATCHED_PO_BEFORE_DELETE' AS step_name,
  p.id_po,
  p.po_number,
  COALESCE(p.dashboard_bowheer, bp.bowheer, 'Tanpa Bowheer') AS bowheer,
  p.source_file,
  p.notes,
  GROUP_CONCAT(DISTINCT a.detail_po ORDER BY a.detail_po SEPARATOR ' | ') AS matched_detail
FROM tmp_delete_po_monitor_ids x
JOIN tb_po p ON p.id_po = x.id_po
LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = p.id_bowheer
JOIN tb_po_term t ON t.id_po = p.id_po
JOIN tb_po_term_allocation a ON a.id_term = t.id_term
JOIN tmp_delete_po_monitor_details d
  ON TRIM(CONVERT(a.detail_po USING utf8mb4)) COLLATE utf8mb4_unicode_ci
   = TRIM(CONVERT(d.detail_po USING utf8mb4)) COLLATE utf8mb4_unicode_ci
GROUP BY p.id_po, p.po_number, bowheer, p.source_file, p.notes
ORDER BY p.po_number ASC;

SELECT
  'UNMATCHED_DETAILS' AS step_name,
  d.detail_po
FROM tmp_delete_po_monitor_details d
WHERE NOT EXISTS (
  SELECT 1
  FROM tb_po p
  JOIN tb_po_term t ON t.id_po = p.id_po
  JOIN tb_po_term_allocation a ON a.id_term = t.id_term
  WHERE COALESCE(p.source_file, '') = 'BATCH_MANUAL_PO'
    AND COALESCE(p.notes, '') = 'Batch manual tambah PO'
    AND TRIM(CONVERT(a.detail_po USING utf8mb4)) COLLATE utf8mb4_unicode_ci
      = TRIM(CONVERT(d.detail_po USING utf8mb4)) COLLATE utf8mb4_unicode_ci
)
ORDER BY d.detail_po ASC;

SELECT
  'BEFORE_DELETE_COUNT' AS step_name,
  COUNT(DISTINCT p.id_po) AS po_rows,
  COUNT(DISTINCT t.id_term) AS term_rows,
  COUNT(DISTINCT a.id_allocation) AS allocation_rows,
  COUNT(DISTINCT tc.id_claim) AS claim_rows,
  COUNT(DISTINCT ti.id) AS term_invoice_rows
FROM tmp_delete_po_monitor_ids p
LEFT JOIN tb_po_term t ON t.id_po = p.id_po
LEFT JOIN tb_po_term_allocation a ON a.id_term = t.id_term
LEFT JOIN tb_po_term_claim tc ON tc.id_term = t.id_term
LEFT JOIN tb_po_term_invoice ti ON ti.id_term = t.id_term;

UPDATE tb_po_target_pipeline pl
JOIN tmp_delete_po_monitor_ids p ON p.id_po = pl.linked_id_po
SET pl.linked_id_po = NULL,
    pl.linked_po_number = NULL,
    pl.pipeline_status = 'OPEN',
    pl.converted_at = NULL,
    pl.converted_by = NULL
WHERE @do_delete = 1;

DELETE ti
FROM tb_po_term_invoice ti
JOIN tb_po_term t ON t.id_term = ti.id_term
JOIN tmp_delete_po_monitor_ids p ON p.id_po = t.id_po
WHERE @do_delete = 1;

DELETE tc
FROM tb_po_term_claim tc
JOIN tb_po_term t ON t.id_term = tc.id_term
JOIN tmp_delete_po_monitor_ids p ON p.id_po = t.id_po
WHERE @do_delete = 1;

DELETE a
FROM tb_po_term_allocation a
JOIN tb_po_term t ON t.id_term = a.id_term
JOIN tmp_delete_po_monitor_ids p ON p.id_po = t.id_po
WHERE @do_delete = 1;

DELETE t
FROM tb_po_term t
JOIN tmp_delete_po_monitor_ids p ON p.id_po = t.id_po
WHERE @do_delete = 1;

DELETE am
FROM tb_po_amend am
JOIN tmp_delete_po_monitor_ids p ON p.id_po = am.id_po
WHERE @do_delete = 1;

DELETE po
FROM tb_po po
JOIN tmp_delete_po_monitor_ids p ON p.id_po = po.id_po
WHERE @do_delete = 1;

SELECT
  'AFTER_DELETE_REMAINING' AS step_name,
  COUNT(*) AS remaining_po_rows
FROM tb_po po
JOIN tmp_delete_po_monitor_ids p ON p.id_po = po.id_po;

COMMIT;
