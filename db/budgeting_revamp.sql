SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `tb_budget_cashflow_detail`;
DROP TABLE IF EXISTS `tb_budget_cashflow_header`;
DROP TABLE IF EXISTS `tb_budget_monthly`;
DROP TABLE IF EXISTS `tb_budget_annual`;
DROP TABLE IF EXISTS `tb_budget_items`;
DROP TABLE IF EXISTS `tb_budget_import_log`;

DROP TABLE IF EXISTS `budget_cashflow`;
DROP TABLE IF EXISTS `budget_monthly`;
DROP TABLE IF EXISTS `budget_years`;
DROP TABLE IF EXISTS `budget_masterakunbiaya`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `tb_budget_items` (
  `id_budget_item` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_code` VARCHAR(50) NOT NULL,
  `item_name` VARCHAR(255) NOT NULL,
  `item_category` VARCHAR(100) DEFAULT NULL,
  `item_group` VARCHAR(100) DEFAULT NULL,
  `uom` VARCHAR(50) DEFAULT NULL,
  `default_direction` ENUM('DEBIT','KREDIT') NOT NULL DEFAULT 'DEBIT',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_budget_item`),
  UNIQUE KEY `uk_budget_item_code` (`item_code`),
  KEY `idx_budget_item_name` (`item_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tb_budget_annual` (
  `id_budget_annual` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `budget_year` SMALLINT NOT NULL,
  `id_budget_item` INT UNSIGNED NOT NULL,
  `annual_budget` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `notes` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_budget_annual`),
  UNIQUE KEY `uk_budget_annual` (`budget_year`,`id_budget_item`),
  KEY `idx_budget_annual_item` (`id_budget_item`),
  CONSTRAINT `fk_budget_annual_item`
    FOREIGN KEY (`id_budget_item`) REFERENCES `tb_budget_items` (`id_budget_item`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tb_budget_monthly` (
  `id_budget_monthly` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_budget_annual` INT UNSIGNED NOT NULL,
  `month_no` TINYINT NOT NULL,
  `monthly_budget` DECIMAL(18,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_budget_monthly`),
  UNIQUE KEY `uk_budget_monthly` (`id_budget_annual`,`month_no`),
  KEY `idx_budget_monthly_month` (`month_no`),
  CONSTRAINT `fk_budget_monthly_annual`
    FOREIGN KEY (`id_budget_annual`) REFERENCES `tb_budget_annual` (`id_budget_annual`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tb_budget_cashflow_header` (
  `id_cashflow_header` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_tec` VARCHAR(100) NOT NULL,
  `tanggal_cashflow` DATE NOT NULL,
  `id_bowheer` INT DEFAULT NULL,
  `project_name` VARCHAR(255) NOT NULL,
  `pic_project` VARCHAR(255) DEFAULT NULL,
  `regional` VARCHAR(100) DEFAULT NULL,
  `kota` VARCHAR(100) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `source_type` ENUM('MANUAL','IMPORT') NOT NULL DEFAULT 'MANUAL',
  `created_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cashflow_header`),
  KEY `idx_cashflow_header_date` (`tanggal_cashflow`),
  KEY `idx_cashflow_header_tec` (`nomor_tec`),
  KEY `idx_cashflow_header_project` (`project_name`),
  KEY `idx_cashflow_header_pic` (`pic_project`),
  KEY `idx_cashflow_header_area` (`regional`,`kota`),
  KEY `idx_cashflow_header_bowheer` (`id_bowheer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tb_budget_cashflow_detail` (
  `id_cashflow_detail` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_cashflow_header` BIGINT UNSIGNED NOT NULL,
  `id_budget_item` INT UNSIGNED NOT NULL,
  `direction` ENUM('DEBIT','KREDIT') NOT NULL DEFAULT 'DEBIT',
  `qty` DECIMAL(18,2) NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `nominal` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `remarks_item` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_cashflow_detail`),
  KEY `idx_cashflow_detail_header` (`id_cashflow_header`),
  KEY `idx_cashflow_detail_item` (`id_budget_item`),
  KEY `idx_cashflow_detail_direction` (`direction`),
  CONSTRAINT `fk_cashflow_detail_header`
    FOREIGN KEY (`id_cashflow_header`) REFERENCES `tb_budget_cashflow_header` (`id_cashflow_header`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cashflow_detail_item`
    FOREIGN KEY (`id_budget_item`) REFERENCES `tb_budget_items` (`id_budget_item`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tb_budget_import_log` (
  `id_budget_import` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_name` VARCHAR(255) NOT NULL,
  `import_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_rows` INT NOT NULL DEFAULT 0,
  `success_rows` INT NOT NULL DEFAULT 0,
  `failed_rows` INT NOT NULL DEFAULT 0,
  `notes` TEXT DEFAULT NULL,
  `uploaded_by` INT DEFAULT NULL,
  PRIMARY KEY (`id_budget_import`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
