/*
  Add NAMA OLT column to tb_myrep_drm if it does not exist yet.
  Safe to re-run on MySQL 8+.
*/

ALTER TABLE tb_myrep_drm
ADD COLUMN IF NOT EXISTS nama_olt VARCHAR(255) NULL
AFTER homepass_drm;
