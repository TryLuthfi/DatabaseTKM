-- Tambah kolom TTL pada master PKS
ALTER TABLE tb_logistik_pks
ADD COLUMN ttl VARCHAR(150) NULL AFTER pic_pks;
