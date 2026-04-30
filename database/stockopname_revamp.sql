-- Stock Opname Revamp
-- Target: MySQL / MariaDB
-- Review dulu sebelum dijalankan di production.

START TRANSACTION;

-- =========================================================
-- 1. Tambahan source material khusus adjustment hasil SO
-- =========================================================
INSERT INTO tb_master_logistik_sumber_material (id_sumber_material, nama_sumber_material, status_sumber_material)
SELECT 19, 'SO Adjustment In', 'IN'
WHERE NOT EXISTS (
    SELECT 1 FROM tb_master_logistik_sumber_material WHERE id_sumber_material = 19
);

INSERT INTO tb_master_logistik_sumber_material (id_sumber_material, nama_sumber_material, status_sumber_material)
SELECT 20, 'SO Adjustment Out', 'OUT'
WHERE NOT EXISTS (
    SELECT 1 FROM tb_master_logistik_sumber_material WHERE id_sumber_material = 20
);

-- =========================================================
-- 2. Periode SO
-- =========================================================
ALTER TABLE tb_so_periode
    ADD COLUMN IF NOT EXISTS sop_tanggal_mulai_target DATE NULL AFTER sop_tahun,
    ADD COLUMN IF NOT EXISTS sop_tanggal_cutoff DATE NULL AFTER sop_tanggal_mulai_target,
    ADD COLUMN IF NOT EXISTS created_by INT NULL AFTER sop_status,
    ADD COLUMN IF NOT EXISTS created_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER created_by,
    ADD COLUMN IF NOT EXISTS updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- =========================================================
-- 3. Header SO per kota / gudang
-- =========================================================
ALTER TABLE tb_so_kota
    ADD COLUMN IF NOT EXISTS submitted_by INT NULL AFTER sok_tanggal,
    ADD COLUMN IF NOT EXISTS submitted_at DATETIME NULL AFTER submitted_by,
    ADD COLUMN IF NOT EXISTS reviewed_by INT NULL AFTER submitted_at,
    ADD COLUMN IF NOT EXISTS reviewed_at DATETIME NULL AFTER reviewed_by,
    ADD COLUMN IF NOT EXISTS approved_by INT NULL AFTER reviewed_at,
    ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL AFTER approved_by,
    ADD COLUMN IF NOT EXISTS sok_catatan TEXT NULL AFTER approved_at,
    ADD COLUMN IF NOT EXISTS has_selisih TINYINT(1) NOT NULL DEFAULT 0 AFTER sok_catatan,
    ADD COLUMN IF NOT EXISTS needs_ba TINYINT(1) NOT NULL DEFAULT 0 AFTER has_selisih,
    ADD COLUMN IF NOT EXISTS is_adjusted TINYINT(1) NOT NULL DEFAULT 0 AFTER needs_ba,
    ADD COLUMN IF NOT EXISTS adjusted_at DATETIME NULL AFTER is_adjusted;

-- Backup kandidat duplikat sebelum dibersihkan.
CREATE TABLE IF NOT EXISTS tb_so_kota_duplicate_backup AS
SELECT *
FROM tb_so_kota
WHERE 1 = 0;

INSERT INTO tb_so_kota_duplicate_backup
SELECT tsk.*
FROM tb_so_kota tsk
INNER JOIN (
    SELECT id_so_periode, id_kota
    FROM tb_so_kota
    GROUP BY id_so_periode, id_kota
    HAVING COUNT(*) > 1
) dup
    ON dup.id_so_periode = tsk.id_so_periode
   AND dup.id_kota = tsk.id_kota
WHERE NOT EXISTS (
    SELECT 1
    FROM tb_so_kota_duplicate_backup backup
    WHERE backup.id_so_kota = tsk.id_so_kota
);

-- Bersihkan duplikat dengan menyisakan record terakhir
-- berdasarkan sok_tanggal terbaru lalu id_so_kota terbesar.
DELETE t1
FROM tb_so_kota t1
INNER JOIN tb_so_kota t2
    ON t1.id_so_periode = t2.id_so_periode
   AND t1.id_kota = t2.id_kota
   AND (
        COALESCE(t1.sok_tanggal, '0000-00-00 00:00:00') < COALESCE(t2.sok_tanggal, '0000-00-00 00:00:00')
        OR (
            COALESCE(t1.sok_tanggal, '0000-00-00 00:00:00') = COALESCE(t2.sok_tanggal, '0000-00-00 00:00:00')
            AND t1.id_so_kota < t2.id_so_kota
        )
   );

ALTER TABLE tb_so_kota
    ADD UNIQUE KEY uq_so_kota_periode_lokasi (id_so_periode, id_kota);

-- =========================================================
-- 4. Detail item SO
-- =========================================================
ALTER TABLE tb_so_item
    ADD COLUMN IF NOT EXISTS id_so_item BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
    ADD COLUMN IF NOT EXISTS soi_selisih BIGINT NULL AFTER soi_stok_opname,
    ADD COLUMN IF NOT EXISTS soi_is_selisih TINYINT(1) NOT NULL DEFAULT 0 AFTER soi_selisih,
    ADD COLUMN IF NOT EXISTS soi_remarks TEXT NULL AFTER soi_keterangan,
    ADD COLUMN IF NOT EXISTS soi_remarks_required TINYINT(1) NOT NULL DEFAULT 0 AFTER soi_remarks,
    ADD COLUMN IF NOT EXISTS soi_needs_adjustment TINYINT(1) NOT NULL DEFAULT 0 AFTER soi_remarks_required,
    ADD COLUMN IF NOT EXISTS soi_adjustment_status VARCHAR(30) NULL AFTER soi_needs_adjustment,
    ADD COLUMN IF NOT EXISTS soi_adjusted_qty BIGINT NULL AFTER soi_adjustment_status,
    ADD COLUMN IF NOT EXISTS soi_adjusted_at DATETIME NULL AFTER soi_adjusted_qty,
    ADD COLUMN IF NOT EXISTS soi_adjusted_by INT NULL AFTER soi_adjusted_at;

-- =========================================================
-- 5. Header BA Kronologi
-- =========================================================
CREATE TABLE IF NOT EXISTS tb_so_ba (
    id_so_ba BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_so_periode INT NOT NULL,
    id_so_kota INT NOT NULL,
    nomor_ba VARCHAR(100) NOT NULL,
    ba_tanggal DATE NOT NULL,
    ba_status VARCHAR(30) NOT NULL DEFAULT 'DRAFT',
    ba_file_draft VARCHAR(255) NULL,
    ba_file_signed VARCHAR(255) NULL,
    ba_generated_at DATETIME NULL,
    ba_uploaded_at DATETIME NULL,
    ba_submitted_by INT NULL,
    ba_approved_by INT NULL,
    ba_approved_at DATETIME NULL,
    ba_approval_notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_so_ba_periode (id_so_periode),
    KEY idx_so_ba_kota (id_so_kota),
    CONSTRAINT fk_so_ba_periode FOREIGN KEY (id_so_periode) REFERENCES tb_so_periode(id_sop),
    CONSTRAINT fk_so_ba_kota FOREIGN KEY (id_so_kota) REFERENCES tb_so_kota(id_so_kota)
);

-- =========================================================
-- 6. Item BA Kronologi
-- =========================================================
CREATE TABLE IF NOT EXISTS tb_so_ba_item (
    id_so_ba_item BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_so_ba BIGINT NOT NULL,
    id_so_item BIGINT NOT NULL,
    id_kode_item INT NOT NULL,
    stok_aplikasi BIGINT NOT NULL DEFAULT 0,
    stok_fisik BIGINT NOT NULL DEFAULT 0,
    selisih BIGINT NOT NULL DEFAULT 0,
    remarks TEXT NOT NULL,
    kronologi TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_so_ba_item_ba (id_so_ba),
    KEY idx_so_ba_item_so_item (id_so_item),
    CONSTRAINT fk_so_ba_item_ba FOREIGN KEY (id_so_ba) REFERENCES tb_so_ba(id_so_ba),
    CONSTRAINT fk_so_ba_item_so_item FOREIGN KEY (id_so_item) REFERENCES tb_so_item(id_so_item)
);

-- =========================================================
-- 7. Approval log SO
-- =========================================================
CREATE TABLE IF NOT EXISTS tb_so_approval_log (
    id_so_approval_log BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_so_kota INT NOT NULL,
    status_from VARCHAR(30) NULL,
    status_to VARCHAR(30) NOT NULL,
    action_by INT NULL,
    action_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    action_note TEXT NULL,
    KEY idx_so_approval_kota (id_so_kota),
    CONSTRAINT fk_so_approval_kota FOREIGN KEY (id_so_kota) REFERENCES tb_so_kota(id_so_kota)
);

-- =========================================================
-- 8. Audit trail pada ledger logistik
-- =========================================================
ALTER TABLE tb_logistik_stok
    ADD COLUMN IF NOT EXISTS ref_module VARCHAR(50) NULL AFTER CREATED_AT,
    ADD COLUMN IF NOT EXISTS ref_id BIGINT NULL AFTER ref_module,
    ADD COLUMN IF NOT EXISTS ref_detail_id BIGINT NULL AFTER ref_id,
    ADD COLUMN IF NOT EXISTS ref_number VARCHAR(100) NULL AFTER ref_detail_id,
    ADD COLUMN IF NOT EXISTS is_system_generated TINYINT(1) NOT NULL DEFAULT 0 AFTER ref_number,
    ADD COLUMN IF NOT EXISTS approved_by INT NULL AFTER is_system_generated,
    ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL AFTER approved_by,
    ADD COLUMN IF NOT EXISTS adjustment_reason TEXT NULL AFTER approved_at;

COMMIT;
