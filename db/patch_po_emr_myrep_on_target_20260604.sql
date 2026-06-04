-- Kolom flag PO yang boleh tampil pada modul PO_EMR_Myrep.
-- Runtime modul EMR membaca tb_myrep_po_header.on_target = 1.

ALTER TABLE tb_myrep_po_header
  ADD COLUMN IF NOT EXISTS on_target TINYINT(1) NOT NULL DEFAULT 0 AFTER status_po;

-- Contoh pemakaian:
-- UPDATE tb_myrep_po_header SET on_target = 1 WHERE id_po_header IN (1, 2, 3);
-- UPDATE tb_myrep_po_header SET on_target = 0 WHERE id_po_header IN (4, 5, 6);
