/*
  Support foto comply on Implementasi BOQ MyRep.
  Safe to re-run on MySQL 8+.
*/

ALTER TABLE tb_myrep_boq_progress_photo
ADD COLUMN IF NOT EXISTS photo_category VARCHAR(30) NOT NULL DEFAULT 'HARIAN'
AFTER caption;

ALTER TABLE tb_myrep_boq_progress_photo
ADD COLUMN IF NOT EXISTS comply_label VARCHAR(255) NULL
AFTER photo_category;

ALTER TABLE tb_myrep_boq_progress_photo
ADD COLUMN IF NOT EXISTS status_photo VARCHAR(30) NOT NULL DEFAULT 'APPROVED'
AFTER comply_label;

ALTER TABLE tb_myrep_boq_progress_photo
ADD COLUMN IF NOT EXISTS review_remark TEXT NULL
AFTER status_photo;

ALTER TABLE tb_myrep_boq_progress_photo
ADD COLUMN IF NOT EXISTS reviewed_by INT NULL
AFTER review_remark;

ALTER TABLE tb_myrep_boq_progress_photo
ADD COLUMN IF NOT EXISTS reviewed_at DATETIME NULL
AFTER reviewed_by;

ALTER TABLE tb_myrep_boq_progress_photo
ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL
AFTER reviewed_at;
