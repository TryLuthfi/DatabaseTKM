SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '');

DROP TEMPORARY TABLE IF EXISTS stg_myrep_cutoff_import;
CREATE TEMPORARY TABLE stg_myrep_cutoff_import (
  cutoff_group varchar(50) DEFAULT NULL,
  cluster_name varchar(255) DEFAULT NULL,
  regional_name varchar(100) DEFAULT NULL,
  province_name varchar(100) DEFAULT NULL,
  city_name varchar(100) DEFAULT NULL,
  team_name varchar(100) DEFAULT NULL,
  rpm varchar(100) DEFAULT NULL,
  sm varchar(100) DEFAULT NULL,
  spv varchar(100) DEFAULT NULL,
  status_current varchar(50) DEFAULT NULL,
  hp_plan varchar(50) DEFAULT NULL,
  homepass_bak varchar(50) DEFAULT NULL,
  homepass_valsal varchar(50) DEFAULT NULL,
  hp_donasi varchar(50) DEFAULT NULL,
  homepass_drm varchar(50) DEFAULT NULL,
  ba_open_date varchar(50) DEFAULT NULL,
  bak_date varchar(50) DEFAULT NULL,
  valsal_date varchar(50) DEFAULT NULL,
  submission_date varchar(50) DEFAULT NULL,
  released_at varchar(50) DEFAULT NULL,
  drm_date varchar(50) DEFAULT NULL,
  rfs_cluster_id varchar(50) DEFAULT NULL,
  remark_general text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

LOAD DATA LOCAL INFILE 'D:\XAMPP\htdocs\DatabaseTKM\db\DATA_FOR_IMPORT.csv'
INTO TABLE stg_myrep_cutoff_import
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(
  cutoff_group,
  cluster_name,
  regional_name,
  province_name,
  city_name,
  team_name,
  rpm,
  sm,
  spv,
  status_current,
  hp_plan,
  homepass_bak,
  homepass_valsal,
  hp_donasi,
  homepass_drm,
  ba_open_date,
  bak_date,
  valsal_date,
  submission_date,
  released_at,
  drm_date,
  rfs_cluster_id,
  remark_general
);

DROP TEMPORARY TABLE IF EXISTS tmp_myrep_cutoff_stage1;
CREATE TEMPORARY TABLE tmp_myrep_cutoff_stage1 AS
SELECT
  UPPER(TRIM(COALESCE(cutoff_group, ''))) AS cutoff_group,
  TRIM(COALESCE(cluster_name, '')) AS cluster_name,
  UPPER(TRIM(COALESCE(regional_name, ''))) AS regional_name,
  UPPER(TRIM(COALESCE(province_name, ''))) AS province_name,
  UPPER(TRIM(COALESCE(city_name, ''))) AS city_name,
  TRIM(COALESCE(team_name, '')) AS team_name,
  TRIM(COALESCE(rpm, '')) AS rpm,
  TRIM(COALESCE(sm, '')) AS sm,
  TRIM(COALESCE(spv, '')) AS spv,
  UPPER(TRIM(COALESCE(status_current, ''))) AS status_current_raw,

  CAST(NULLIF(REPLACE(REPLACE(TRIM(COALESCE(hp_plan, '')), '.', ''), ',', ''), '') AS DECIMAL(18,2)) AS hp_plan_raw,
  CAST(NULLIF(REPLACE(REPLACE(TRIM(COALESCE(homepass_bak, '')), '.', ''), ',', ''), '') AS DECIMAL(18,2)) AS homepass_bak_raw,
  CAST(NULLIF(REPLACE(REPLACE(TRIM(COALESCE(homepass_valsal, '')), '.', ''), ',', ''), '') AS DECIMAL(18,2)) AS homepass_valsal_raw,
  CAST(NULLIF(REPLACE(REPLACE(TRIM(COALESCE(hp_donasi, '')), '.', ''), ',', ''), '') AS DECIMAL(18,2)) AS hp_donasi_raw,
  CAST(NULLIF(REPLACE(REPLACE(TRIM(COALESCE(homepass_drm, '')), '.', ''), ',', ''), '') AS DECIMAL(18,2)) AS homepass_drm_raw,

  DATE(COALESCE(
    STR_TO_DATE(NULLIF(TRIM(COALESCE(ba_open_date, '')), ''), '%d/%m/%Y %H:%i:%s'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(ba_open_date, '')), ''), '%d/%m/%Y'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(ba_open_date, '')), ''), '%Y-%m-%d %H:%i:%s'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(ba_open_date, '')), ''), '%Y-%m-%d')
  )) AS ba_open_date_raw,
  DATE(COALESCE(
    STR_TO_DATE(NULLIF(TRIM(COALESCE(bak_date, '')), ''), '%d/%m/%Y %H:%i:%s'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(bak_date, '')), ''), '%d/%m/%Y'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(bak_date, '')), ''), '%Y-%m-%d %H:%i:%s'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(bak_date, '')), ''), '%Y-%m-%d')
  )) AS bak_date_raw,
  DATE(COALESCE(
    STR_TO_DATE(NULLIF(TRIM(COALESCE(valsal_date, '')), ''), '%d/%m/%Y %H:%i:%s'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(valsal_date, '')), ''), '%d/%m/%Y'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(valsal_date, '')), ''), '%Y-%m-%d %H:%i:%s'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(valsal_date, '')), ''), '%Y-%m-%d')
  )) AS valsal_date_raw,
  DATE(COALESCE(
    STR_TO_DATE(NULLIF(TRIM(COALESCE(submission_date, '')), ''), '%d/%m/%Y %H:%i:%s'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(submission_date, '')), ''), '%d/%m/%Y'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(submission_date, '')), ''), '%Y-%m-%d %H:%i:%s'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(submission_date, '')), ''), '%Y-%m-%d')
  )) AS submission_date_raw,
  COALESCE(
    STR_TO_DATE(NULLIF(TRIM(COALESCE(released_at, '')), ''), '%d/%m/%Y %H:%i:%s'),
    STR_TO_DATE(CONCAT(NULLIF(TRIM(COALESCE(released_at, '')), ''), ' 00:00:00'), '%d/%m/%Y %H:%i:%s'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(released_at, '')), ''), '%Y-%m-%d %H:%i:%s'),
    STR_TO_DATE(CONCAT(NULLIF(TRIM(COALESCE(released_at, '')), ''), ' 00:00:00'), '%Y-%m-%d %H:%i:%s')
  ) AS released_at_raw,
  DATE(COALESCE(
    STR_TO_DATE(NULLIF(TRIM(COALESCE(drm_date, '')), ''), '%d/%m/%Y %H:%i:%s'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(drm_date, '')), ''), '%d/%m/%Y'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(drm_date, '')), ''), '%Y-%m-%d %H:%i:%s'),
    STR_TO_DATE(NULLIF(TRIM(COALESCE(drm_date, '')), ''), '%Y-%m-%d')
  )) AS drm_date_raw,

  CAST(NULLIF(REPLACE(REPLACE(TRIM(COALESCE(rfs_cluster_id, '')), '.', ''), ',', ''), '') AS UNSIGNED) AS rfs_cluster_id,
  NULLIF(TRIM(COALESCE(remark_general, '')), '') AS remark_general
FROM stg_myrep_cutoff_import
WHERE TRIM(COALESCE(cluster_name, '')) <> '';

DROP TEMPORARY TABLE IF EXISTS tmp_myrep_cutoff_norm;
CREATE TEMPORARY TABLE tmp_myrep_cutoff_norm AS
SELECT
  cutoff_group,
  cluster_name,
  regional_name,
  province_name,
  city_name,
  team_name,
  rpm,
  sm,
  spv,
  CASE
    WHEN status_current_raw <> '' THEN status_current_raw
    WHEN cutoff_group = 'ATP' THEN 'ATP'
    WHEN cutoff_group = 'RFS' THEN 'RFS'
    WHEN cutoff_group = 'IMPLEMENTASI' THEN 'DRM'
    WHEN cutoff_group = 'RELEASED' THEN 'RELEASED'
    WHEN cutoff_group = 'VALSAL' THEN 'VALSAL'
    WHEN cutoff_group = 'BAK' THEN 'BAK'
    ELSE 'DRAFT'
  END AS status_current,

  COALESCE(NULLIF(hp_plan_raw, 0), NULLIF(homepass_bak_raw, 0), NULLIF(homepass_valsal_raw, 0), NULLIF(hp_donasi_raw, 0), NULLIF(homepass_drm_raw, 0), 0) AS hp_plan_final,
  COALESCE(NULLIF(homepass_bak_raw, 0), NULLIF(homepass_valsal_raw, 0), NULLIF(hp_donasi_raw, 0), NULLIF(homepass_drm_raw, 0), NULLIF(hp_plan_raw, 0), 0) AS homepass_bak_final,
  COALESCE(NULLIF(homepass_valsal_raw, 0), NULLIF(hp_donasi_raw, 0), NULLIF(homepass_drm_raw, 0), NULLIF(homepass_bak_raw, 0), NULLIF(hp_plan_raw, 0), 0) AS homepass_valsal_final,
  COALESCE(NULLIF(hp_donasi_raw, 0), NULLIF(homepass_drm_raw, 0), NULLIF(homepass_valsal_raw, 0), NULLIF(homepass_bak_raw, 0), NULLIF(hp_plan_raw, 0), 0) AS hp_donasi_final,
  COALESCE(NULLIF(homepass_drm_raw, 0), NULLIF(hp_donasi_raw, 0), NULLIF(homepass_valsal_raw, 0), NULLIF(homepass_bak_raw, 0), NULLIF(hp_plan_raw, 0), 0) AS homepass_drm_final,

  COALESCE(ba_open_date_raw, bak_date_raw, valsal_date_raw, submission_date_raw, DATE(released_at_raw), drm_date_raw) AS ba_open_date_final,
  COALESCE(bak_date_raw, ba_open_date_raw, valsal_date_raw, submission_date_raw, DATE(released_at_raw), drm_date_raw) AS bak_date_final,
  COALESCE(valsal_date_raw, submission_date_raw, DATE(released_at_raw), drm_date_raw, bak_date_raw, ba_open_date_raw) AS valsal_date_final,
  COALESCE(submission_date_raw, DATE(released_at_raw), drm_date_raw, valsal_date_raw, bak_date_raw, ba_open_date_raw) AS submission_date_final,
  COALESCE(
    released_at_raw,
    CASE WHEN drm_date_raw IS NOT NULL THEN STR_TO_DATE(CONCAT(DATE_FORMAT(drm_date_raw, '%Y-%m-%d'), ' 00:00:00'), '%Y-%m-%d %H:%i:%s') END,
    CASE WHEN submission_date_raw IS NOT NULL THEN STR_TO_DATE(CONCAT(DATE_FORMAT(submission_date_raw, '%Y-%m-%d'), ' 00:00:00'), '%Y-%m-%d %H:%i:%s') END,
    CASE WHEN valsal_date_raw IS NOT NULL THEN STR_TO_DATE(CONCAT(DATE_FORMAT(valsal_date_raw, '%Y-%m-%d'), ' 00:00:00'), '%Y-%m-%d %H:%i:%s') END,
    CASE WHEN bak_date_raw IS NOT NULL THEN STR_TO_DATE(CONCAT(DATE_FORMAT(bak_date_raw, '%Y-%m-%d'), ' 00:00:00'), '%Y-%m-%d %H:%i:%s') END,
    CASE WHEN ba_open_date_raw IS NOT NULL THEN STR_TO_DATE(CONCAT(DATE_FORMAT(ba_open_date_raw, '%Y-%m-%d'), ' 00:00:00'), '%Y-%m-%d %H:%i:%s') END
  ) AS released_at_final,
  COALESCE(drm_date_raw, DATE(released_at_raw), submission_date_raw, valsal_date_raw, bak_date_raw, ba_open_date_raw) AS drm_date_final,

  rfs_cluster_id,
  remark_general,

  CASE WHEN cutoff_group IN ('BAK', 'VALSAL', 'RELEASED', 'IMPLEMENTASI', 'RFS', 'ATP') THEN 1 ELSE 0 END AS needs_bak,
  CASE WHEN cutoff_group IN ('VALSAL', 'RELEASED', 'IMPLEMENTASI', 'RFS', 'ATP') THEN 1 ELSE 0 END AS needs_valsal,
  CASE WHEN cutoff_group IN ('RELEASED', 'IMPLEMENTASI', 'RFS', 'ATP') THEN 1 ELSE 0 END AS needs_batch,
  CASE WHEN cutoff_group IN ('IMPLEMENTASI', 'RFS', 'ATP') THEN 1 ELSE 0 END AS needs_drm
FROM tmp_myrep_cutoff_stage1;

UPDATE tb_myrep_cluster c
INNER JOIN tmp_myrep_cutoff_norm n
  ON UPPER(TRIM(c.cluster_name)) = UPPER(TRIM(n.cluster_name))
 AND UPPER(TRIM(c.city_name)) = UPPER(TRIM(n.city_name))
SET
  c.rfs_cluster_id = COALESCE(n.rfs_cluster_id, c.rfs_cluster_id),
  c.cluster_name = n.cluster_name,
  c.regional_name = n.regional_name,
  c.province_name = n.province_name,
  c.city_name = n.city_name,
  c.team_name = n.team_name,
  c.rpm = n.rpm,
  c.sm = n.sm,
  c.spv = n.spv,
  c.hp_plan = n.hp_plan_final,
  c.status_current = n.status_current,
  c.remark_general = COALESCE(n.remark_general, c.remark_general),
  c.updated_at = NOW();

INSERT INTO tb_myrep_cluster (
  rfs_cluster_id,
  cluster_name,
  regional_name,
  province_name,
  city_name,
  team_name,
  rpm,
  sm,
  spv,
  hp_plan,
  status_current,
  remark_general,
  is_active,
  created_at,
  updated_at
)
SELECT
  n.rfs_cluster_id,
  n.cluster_name,
  n.regional_name,
  n.province_name,
  n.city_name,
  n.team_name,
  n.rpm,
  n.sm,
  n.spv,
  n.hp_plan_final,
  n.status_current,
  n.remark_general,
  1,
  NOW(),
  NOW()
FROM tmp_myrep_cutoff_norm n
LEFT JOIN tb_myrep_cluster c
  ON UPPER(TRIM(c.cluster_name)) = UPPER(TRIM(n.cluster_name))
 AND UPPER(TRIM(c.city_name)) = UPPER(TRIM(n.city_name))
WHERE c.id_myrep_cluster IS NULL;

DROP TEMPORARY TABLE IF EXISTS tmp_myrep_cutoff_cluster_map;
CREATE TEMPORARY TABLE tmp_myrep_cutoff_cluster_map AS
SELECT
  c.id_myrep_cluster,
  n.*
FROM tmp_myrep_cutoff_norm n
INNER JOIN tb_myrep_cluster c
  ON UPPER(TRIM(c.cluster_name)) = UPPER(TRIM(n.cluster_name))
 AND UPPER(TRIM(c.city_name)) = UPPER(TRIM(n.city_name));

UPDATE tb_myrep_bak b
INNER JOIN tmp_myrep_cutoff_cluster_map m ON m.id_myrep_cluster = b.id_myrep_cluster
SET
  b.ba_open_date = COALESCE(m.ba_open_date_final, b.ba_open_date),
  b.bak_date = COALESCE(m.bak_date_final, b.bak_date),
  b.homepass_bak = m.homepass_bak_final,
  b.status_bak = 'DONE',
  b.remark_bak = COALESCE(m.remark_general, b.remark_bak),
  b.updated_at = NOW()
WHERE m.needs_bak = 1;

INSERT INTO tb_myrep_bak (
  id_myrep_cluster,
  ba_open_date,
  bak_date,
  homepass_bak,
  status_bak,
  remark_bak,
  created_at,
  updated_at
)
SELECT
  m.id_myrep_cluster,
  m.ba_open_date_final,
  m.bak_date_final,
  m.homepass_bak_final,
  'DONE',
  m.remark_general,
  NOW(),
  NOW()
FROM tmp_myrep_cutoff_cluster_map m
LEFT JOIN tb_myrep_bak b ON b.id_myrep_cluster = m.id_myrep_cluster
WHERE m.needs_bak = 1
  AND b.id_bak IS NULL;

UPDATE tb_myrep_valsal v
INNER JOIN tmp_myrep_cutoff_cluster_map m ON m.id_myrep_cluster = v.id_myrep_cluster
SET
  v.valsal_date = COALESCE(m.valsal_date_final, v.valsal_date),
  v.homepass_valsal = m.homepass_valsal_final,
  v.status_valsal = 'DONE',
  v.remark_valsal = COALESCE(m.remark_general, v.remark_valsal),
  v.updated_at = NOW()
WHERE m.needs_valsal = 1;

INSERT INTO tb_myrep_valsal (
  id_myrep_cluster,
  valsal_date,
  homepass_valsal,
  status_valsal,
  remark_valsal,
  created_at,
  updated_at
)
SELECT
  m.id_myrep_cluster,
  m.valsal_date_final,
  m.homepass_valsal_final,
  'DONE',
  m.remark_general,
  NOW(),
  NOW()
FROM tmp_myrep_cutoff_cluster_map m
LEFT JOIN tb_myrep_valsal v ON v.id_myrep_cluster = m.id_myrep_cluster
WHERE m.needs_valsal = 1
  AND v.id_valsal IS NULL;

UPDATE tb_myrep_batch_approval ba
INNER JOIN (
  SELECT id_myrep_cluster, MAX(id_batch_approval) AS last_batch_id
  FROM tb_myrep_batch_approval
  GROUP BY id_myrep_cluster
) pick ON pick.last_batch_id = ba.id_batch_approval
INNER JOIN tmp_myrep_cutoff_cluster_map m ON m.id_myrep_cluster = ba.id_myrep_cluster
SET
  ba.submission_date = COALESCE(m.submission_date_final, ba.submission_date),
  ba.hp_donasi = m.hp_donasi_final,
  ba.nominal_pengajuan_area = COALESCE(ba.nominal_pengajuan_area, 0),
  ba.nominal_per_homepass = CASE WHEN m.hp_donasi_final > 0 THEN COALESCE(ba.nominal_release_finance, ba.nominal_pengajuan_area, 0) / m.hp_donasi_final ELSE 0 END,
  ba.staging_status = 'RELEASED',
  ba.submitted_to_ho_at = COALESCE(ba.submitted_to_ho_at, CASE WHEN m.submission_date_final IS NOT NULL THEN STR_TO_DATE(CONCAT(DATE_FORMAT(m.submission_date_final, '%Y-%m-%d'), ' 00:00:00'), '%Y-%m-%d %H:%i:%s') END),
  ba.submitted_to_astri_at = COALESCE(ba.submitted_to_astri_at, CASE WHEN m.submission_date_final IS NOT NULL THEN STR_TO_DATE(CONCAT(DATE_FORMAT(m.submission_date_final, '%Y-%m-%d'), ' 00:00:00'), '%Y-%m-%d %H:%i:%s') END),
  ba.submitted_to_finance_at = COALESCE(ba.submitted_to_finance_at, m.released_at_final),
  ba.released_at = COALESCE(m.released_at_final, ba.released_at),
  ba.remark_batch_approval = COALESCE(m.remark_general, ba.remark_batch_approval),
  ba.updated_at = NOW()
WHERE m.needs_batch = 1;

INSERT INTO tb_myrep_batch_approval (
  id_myrep_cluster,
  submission_date,
  hp_donasi,
  nominal_pengajuan_area,
  nominal_per_homepass,
  staging_status,
  submitted_to_ho_at,
  submitted_to_astri_at,
  submitted_to_finance_at,
  released_at,
  remark_batch_approval,
  created_at,
  updated_at
)
SELECT
  m.id_myrep_cluster,
  m.submission_date_final,
  m.hp_donasi_final,
  0,
  0,
  'RELEASED',
  CASE WHEN m.submission_date_final IS NOT NULL THEN STR_TO_DATE(CONCAT(DATE_FORMAT(m.submission_date_final, '%Y-%m-%d'), ' 00:00:00'), '%Y-%m-%d %H:%i:%s') END,
  CASE WHEN m.submission_date_final IS NOT NULL THEN STR_TO_DATE(CONCAT(DATE_FORMAT(m.submission_date_final, '%Y-%m-%d'), ' 00:00:00'), '%Y-%m-%d %H:%i:%s') END,
  m.released_at_final,
  m.released_at_final,
  m.remark_general,
  NOW(),
  NOW()
FROM tmp_myrep_cutoff_cluster_map m
LEFT JOIN (
  SELECT id_myrep_cluster, MAX(id_batch_approval) AS last_batch_id
  FROM tb_myrep_batch_approval
  GROUP BY id_myrep_cluster
) pick ON pick.id_myrep_cluster = m.id_myrep_cluster
WHERE m.needs_batch = 1
  AND pick.last_batch_id IS NULL;

UPDATE tb_myrep_drm d
INNER JOIN tmp_myrep_cutoff_cluster_map m ON m.id_myrep_cluster = d.id_myrep_cluster
SET
  d.drm_date = COALESCE(m.drm_date_final, d.drm_date),
  d.homepass_drm = m.homepass_drm_final,
  d.status_drm = 'DONE',
  d.remark_drm = COALESCE(m.remark_general, d.remark_drm),
  d.updated_at = NOW()
WHERE m.needs_drm = 1;

INSERT INTO tb_myrep_drm (
  id_myrep_cluster,
  drm_date,
  homepass_drm,
  status_drm,
  remark_drm,
  created_at,
  updated_at
)
SELECT
  m.id_myrep_cluster,
  m.drm_date_final,
  m.homepass_drm_final,
  'DONE',
  m.remark_general,
  NOW(),
  NOW()
FROM tmp_myrep_cutoff_cluster_map m
LEFT JOIN tb_myrep_drm d ON d.id_myrep_cluster = m.id_myrep_cluster
WHERE m.needs_drm = 1
  AND d.id_drm IS NULL;

UPDATE tb_myrep_cluster c
INNER JOIN tmp_myrep_cutoff_cluster_map m ON m.id_myrep_cluster = c.id_myrep_cluster
SET
  c.status_current = m.status_current,
  c.hp_plan = m.hp_plan_final,
  c.remark_general = COALESCE(m.remark_general, c.remark_general),
  c.updated_at = NOW();

SELECT
  status_current,
  COUNT(*) AS total_cluster,
  SUM(hp_plan) AS total_hp_plan
FROM tb_myrep_cluster
GROUP BY status_current
ORDER BY FIELD(status_current, 'BAK', 'VALSAL', 'RELEASED', 'DRM', 'RFS', 'ATP', 'DONE'), status_current;
