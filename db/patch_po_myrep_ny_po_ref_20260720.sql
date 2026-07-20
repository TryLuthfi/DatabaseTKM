-- Add PO Monitor NY PO reference marker to MyRep PO headers.
-- This keeps PO_MyRep new PO rows traceable to NY PO rows in PO_Monitor.

ALTER TABLE `tb_myrep_po_header`
    ADD COLUMN IF NOT EXISTS `po_monitor_ny_ref` VARCHAR(50) NULL AFTER `remark_po`;

