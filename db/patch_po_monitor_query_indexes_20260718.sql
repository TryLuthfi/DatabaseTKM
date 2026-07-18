SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Optimasi query PO_Monitor dan sync PO_MyRep -> PO_Monitor.
-- Jalankan di production sebelum membuka PO_Monitor jika ingin menghindari ALTER TABLE di request pertama.

ALTER TABLE `tb_po`
  ADD INDEX `idx_tb_po_status_date` (`status_po`, `po_date`);

ALTER TABLE `tb_po_term`
  ADD INDEX `idx_tb_po_term_po_term` (`id_po`, `term_index`);

ALTER TABLE `tb_po_term_claim`
  ADD INDEX `idx_tb_po_term_claim_term_source_amount` (`id_term`, `claim_source`, `invoice_amount`);

ALTER TABLE `tb_myrep_po_header`
  ADD INDEX `idx_myrep_po_header_number` (`po_number`);

ALTER TABLE `tb_myrep_po_termin`
  ADD INDEX `idx_myrep_po_termin_invoice_status` (`invoice_date`, `status_termin`);
