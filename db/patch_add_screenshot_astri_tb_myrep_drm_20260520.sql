ALTER TABLE `tb_myrep_drm`
ADD COLUMN `screenshot_astri_path` varchar(255) DEFAULT NULL AFTER `status_drm`,
ADD COLUMN `screenshot_astri_name` varchar(255) DEFAULT NULL AFTER `screenshot_astri_path`;
