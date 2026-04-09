-- PO Monitoring module schema
-- Run this SQL on your DatabaseTKM database to create tables for PO monitoring

CREATE TABLE IF NOT EXISTS `tb_po` (
  `id_po` INT AUTO_INCREMENT PRIMARY KEY,
  `po_number` VARCHAR(100) NOT NULL UNIQUE,
  `po_date` DATE DEFAULT NULL,
  `id_bowheer` INT DEFAULT NULL,
  `total_value` DECIMAL(18,2) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_by` INT DEFAULT NULL,
  `notes` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tb_po_amend` (
  `id_amend` INT AUTO_INCREMENT PRIMARY KEY,
  `id_po` INT NOT NULL,
  `amend_no` TINYINT NOT NULL,
  `release_value` DECIMAL(18,2) NOT NULL,
  `release_date` DATE DEFAULT NULL,
  `notes` TEXT,
  FOREIGN KEY (`id_po`) REFERENCES `tb_po`(`id_po`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tb_po_term` (
  `id_term` INT AUTO_INCREMENT PRIMARY KEY,
  `id_po` INT NOT NULL,
  `id_amend` INT DEFAULT NULL,
  `term_index` TINYINT NOT NULL,
  `percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `value` DECIMAL(18,2) DEFAULT 0,
  `due_date` DATE DEFAULT NULL,
  `sla_days` INT DEFAULT NULL,
  FOREIGN KEY (`id_po`) REFERENCES `tb_po`(`id_po`) ON DELETE CASCADE,
  FOREIGN KEY (`id_amend`) REFERENCES `tb_po_amend`(`id_amend`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tb_po_term_invoice` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_term` INT NOT NULL,
  `id_billing` INT NOT NULL,
  `invoice_amount` DECIMAL(18,2) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_term`) REFERENCES `tb_po_term`(`id_term`) ON DELETE CASCADE,
  FOREIGN KEY (`id_billing`) REFERENCES `tb_billingpayment`(`id_billing`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tb_term_master` (
  `id_master` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tb_term_master_split` (
  `id_split` INT AUTO_INCREMENT PRIMARY KEY,
  `id_master` INT NOT NULL,
  `term_index` TINYINT NOT NULL,
  `percent` DECIMAL(5,2) NOT NULL,
  FOREIGN KEY (`id_master`) REFERENCES `tb_term_master`(`id_master`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Seed common master term presets
INSERT INTO `tb_term_master` (`name`, `description`) VALUES
('Master 1', 'Single term 100%'),
('Master 2', 'Two terms 50% - 50%'),
('Master 3', 'Three terms 40% - 30% - 30%')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- splits
INSERT INTO `tb_term_master_split` (`id_master`, `term_index`, `percent`) VALUES
(1, 1, 100.00),
(2, 1, 50.00),(2, 2, 50.00),
(3, 1, 40.00),(3, 2, 30.00),(3, 3, 30.00)
ON DUPLICATE KEY UPDATE `percent` = VALUES(`percent`);

-- Notes:
-- 1) After creating `tb_po` and `tb_po_term`, you can create PO records and add their terms.
-- 2) The module includes an allocation helper (in model) which can auto-allocate existing
--    invoices (from `tb_billingpayment`) to PO terms and calculate remaining balances.

-- ==========================
-- Sample data for testing
-- ==========================

-- Sample PO #1 (100,000,000) with 2 terms 50% - 50%
INSERT INTO `tb_po` (`po_number`, `po_date`, `id_bowheer`, `total_value`, `created_at`, `created_by`, `notes`) VALUES
('PO-2026-001','2026-02-10',NULL,100000000,'2026-02-10 10:00:00',1,'Contoh PO 100jt');
-- resolve inserted PO id reliably by selecting it
SELECT @po1 := id_po FROM tb_po WHERE po_number = 'PO-2026-001' LIMIT 1;

INSERT INTO `tb_po_amend` (`id_po`,`amend_no`,`release_value`,`release_date`,`notes`) VALUES
(@po1,1,100000000,'2026-02-10','Rilis awal 100jt');
-- resolve inserted amend id
SELECT @amend1 := id_amend FROM tb_po_amend WHERE id_po = @po1 AND amend_no = 1 LIMIT 1;

INSERT INTO `tb_po_term` (`id_po`,`id_amend`,`term_index`,`percent`,`value`,`due_date`,`sla_days`) VALUES
(@po1,@amend1,1,50.00,50000000,'2026-03-10',30),
(@po1,@amend1,2,50.00,50000000,'2026-04-10',30);

-- Sample PO #2 (example with amendment to show release change)
INSERT INTO `tb_po` (`po_number`, `po_date`, `id_bowheer`, `total_value`, `created_at`, `created_by`, `notes`) VALUES
('PO-2026-002','2026-01-15',NULL,100000000,'2026-01-15 09:00:00',1,'PO contoh dengan amandemen');
SELECT @po2 := id_po FROM tb_po WHERE po_number = 'PO-2026-002' LIMIT 1;

-- initial release 100jt
INSERT INTO `tb_po_amend` (`id_po`,`amend_no`,`release_value`,`release_date`,`notes`) VALUES
(@po2,1,100000000,'2026-01-15','Initial release 100jt');
SELECT @amend2 := id_amend FROM tb_po_amend WHERE id_po = @po2 AND amend_no = 1 LIMIT 1;

INSERT INTO `tb_po_term` (`id_po`,`id_amend`,`term_index`,`percent`,`value`,`due_date`,`sla_days`) VALUES
(@po2,@amend2,1,100.00,100000000,'2026-02-15',30);

-- amandement: new release 80jt (amend_no = 2)
INSERT INTO `tb_po_amend` (`id_po`,`amend_no`,`release_value`,`release_date`,`notes`) VALUES
(@po2,2,80000000,'2026-03-01','Amendment release 80jt');
SELECT @amend2b := id_amend FROM tb_po_amend WHERE id_po = @po2 AND amend_no = 2 LIMIT 1;

INSERT INTO `tb_po_term` (`id_po`,`id_amend`,`term_index`,`percent`,`value`,`due_date`,`sla_days`) VALUES
(@po2,@amend2b,1,100.00,80000000,'2026-04-01',30);

-- NOTE: sample `tb_po_term_invoice` rows are not included since `tb_billingpayment`
-- may have required columns/constraints in your schema. After importing sample PO
-- data, you may create invoices in the application (BillingPayment) and use the
-- Auto-allocate button in the PO Monitor to map invoices to terms.

