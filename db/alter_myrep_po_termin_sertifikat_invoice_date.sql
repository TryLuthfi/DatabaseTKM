ALTER TABLE `tb_myrep_po_termin`
  ADD COLUMN IF NOT EXISTS `sertifikat_invoice_date` VARCHAR(150) NULL AFTER `invoice_date`;

ALTER TABLE `tb_myrep_po_termin`
  MODIFY COLUMN `sertifikat_invoice_date` VARCHAR(150) NULL;
