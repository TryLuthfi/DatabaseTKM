ALTER TABLE `tb_master_user`
    ADD COLUMN `telegram_user_id` VARCHAR(50) NULL AFTER `status_user`;

-- Contoh update manual:
-- UPDATE `tb_master_user`
-- SET `telegram_user_id` = '6244806081'
-- WHERE `nama_user` = 'Gilang';
