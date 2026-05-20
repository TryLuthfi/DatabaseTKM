DROP TABLE IF EXISTS tb_user_new;
CREATE TABLE tb_user_new (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nikk VARCHAR(30) NOT NULL,
  nama_karyawan VARCHAR(150) NOT NULL,
  jenis_kelamin VARCHAR(20) NULL,
  jabatan VARCHAR(120) NULL,
  divisi VARCHAR(120) NULL,
  departemen VARCHAR(120) NULL,
  unit VARCHAR(120) NULL,
  job_level VARCHAR(50) NULL,
  status_pegawai VARCHAR(50) NULL,
  status_user ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  telegram_user_id VARCHAR(50) NULL,
  username_user VARCHAR(100) NULL,
  password_user VARCHAR(255) NULL,
  mulai_kerja DATE NULL,
  akhir_kontrak DATE NULL,
  shift VARCHAR(30) NULL,
  waktu_kerja VARCHAR(50) NULL,
  homebase VARCHAR(120) NULL,
  lokasi_kantor VARCHAR(150) NULL,
  project VARCHAR(150) NULL,
  telepon VARCHAR(30) NULL,
  email_kantor VARCHAR(150) NULL,
  email_pribadi VARCHAR(150) NULL,
  alamat_ktp TEXT NULL,
  alamat_tinggal TEXT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_tb_user_new_nikk (nikk),
  UNIQUE KEY uk_tb_user_new_username (username_user),
  KEY idx_tb_user_new_nama (nama_karyawan),
  KEY idx_tb_user_new_status (status_pegawai),
  KEY idx_tb_user_new_project (project),
  KEY idx_tb_user_new_telegram (telegram_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOAD DATA LOCAL INFILE 'D:/XAMPP/htdocs/DatabaseTKM/db/EMR/tb_user_new.csv'
INTO TABLE tb_user_new
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(id,nikk,nama_karyawan,jenis_kelamin,jabatan,divisi,departemen,unit,job_level,status_pegawai,status_user,telegram_user_id,username_user,password_user,@mulai_kerja,@akhir_kontrak,shift,waktu_kerja,homebase,lokasi_kantor,project,telepon,email_kantor,email_pribadi,alamat_ktp,alamat_tinggal,@created_at,@updated_at)
SET
  mulai_kerja = IF(@mulai_kerja='' OR @mulai_kerja IS NULL, NULL, STR_TO_DATE(@mulai_kerja, '%d/%m/%Y')),
  akhir_kontrak = IF(@akhir_kontrak='' OR @akhir_kontrak IS NULL, NULL, STR_TO_DATE(@akhir_kontrak, '%d/%m/%Y')),
  created_at = IF(@created_at='' OR @created_at IS NULL, NULL, STR_TO_DATE(@created_at, '%c/%e/%Y')),
  updated_at = IF(@updated_at='' OR @updated_at IS NULL, NULL, STR_TO_DATE(@updated_at, '%c/%e/%Y'));

SELECT COUNT(*) AS total_rows FROM tb_user_new;
