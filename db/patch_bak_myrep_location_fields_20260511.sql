ALTER TABLE `tb_myrep_cluster`
ADD COLUMN `regency_id` CHAR(4) NULL AFTER `city_name`,
ADD COLUMN `district_id` CHAR(7) NULL AFTER `regency_id`,
ADD COLUMN `district_name` VARCHAR(150) NULL AFTER `district_id`,
ADD COLUMN `village_id` CHAR(10) NULL AFTER `district_name`,
ADD COLUMN `village_name` VARCHAR(150) NULL AFTER `village_id`;
