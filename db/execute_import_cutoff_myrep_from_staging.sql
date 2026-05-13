SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '');

/*
  PRE-REQ:
  1) CSV sudah diimport ke tabel stg_myrep_cutoff_import_full
  2) Struktur kolom staging sesuai template v2_clean
*/

DROP TEMPORARY TABLE IF EXISTS tmp_myrep_cutoff_norm_full;
CREATE TEMPORARY TABLE tmp_myrep_cutoff_norm_full AS
SELECT
  UPPER(TRIM(COALESCE(cutoff_group, ''))) AS cutoff_group,
  TRIM(COALESCE(cluster_name, '')) AS cluster_name,
  NULLIF(TRIM(COALESCE(cluster_code, '')), '') AS cluster_code,
  UPPER(TRIM(COALESCE(regional_name, ''))) AS regional_name,
  UPPER(TRIM(COALESCE(province_name, ''))) AS province_name,
  UPPER(TRIM(COALESCE(city_name, ''))) AS city_name,
  TRIM(COALESCE(team_name, '')) AS team_name,
  TRIM(COALESCE(rpm, '')) AS rpm,
  TRIM(COALESCE(sm, '')) AS sm,
  TRIM(COALESCE(spv, '')) AS spv,

  UPPER(TRIM(COALESCE(status_current, ''))) AS status_current_raw,
  UPPER(TRIM(COALESCE(status_bak, ''))) AS status_bak_raw,
  UPPER(TRIM(COALESCE(status_valsal, ''))) AS status_valsal_raw,
  UPPER(TRIM(COALESCE(status_batch_approval, ''))) AS status_batch_raw,
  UPPER(TRIM(COALESCE(status_drm, ''))) AS status_drm_raw,
  UPPER(TRIM(COALESCE(status_atp, ''))) AS status_atp_raw,
  UPPER(TRIM(COALESCE(status_checklist_document, ''))) AS status_checklist_raw,

  CAST(NULLIF(REPLACE(REPLACE(TRIM(COALESCE(hp_plan, '')), '.', ''), ',', ''), '') AS DECIMAL(18,2)) AS hp_plan,
  CAST(NULLIF(REPLACE(REPLACE(TRIM(COALESCE(homepass_bak, '')), '.', ''), ',', ''), '') AS DECIMAL(18,2)) AS homepass_bak,
  CAST(NULLIF(REPLACE(REPLACE(TRIM(COALESCE(homepass_valsal, '')), '.', ''), ',', ''), '') AS DECIMAL(18,2)) AS homepass_valsal,
  CAST(NULLIF(REPLACE(REPLACE(TRIM(COALESCE(hp_donasi, '')), '.', ''), ',', ''), '') AS DECIMAL(18,2)) AS hp_donasi,
  CAST(NULLIF(REPLACE(REPLACE(TRIM(COALESCE(homepass_drm, '')), '.', ''), ',', ''), '') AS DECIMAL(18,2)) AS homepass_drm,
  NULLIF(TRIM(COALESCE(olt_name, '')), '') AS olt_name,

  DATE(NULLIF(TRIM(COALESCE(ba_open_date, '')), '')) AS ba_open_date,
  DATE(NULLIF(TRIM(COALESCE(bak_date, '')), '')) AS bak_date,
  DATE(NULLIF(TRIM(COALESCE(valsal_date, '')), '')) AS valsal_date,
  DATE(NULLIF(TRIM(COALESCE(submission_date, '')), '')) AS submission_date,
  STR_TO_DATE(NULLIF(TRIM(COALESCE(released_at, '')), ''), '%Y-%m-%d %H:%i:%s') AS released_at,
  DATE(NULLIF(TRIM(COALESCE(drm_date, '')), '')) AS drm_date,
  DATE(NULLIF(TRIM(COALESCE(email_atp_date, '')), '')) AS email_atp_date,
  DATE(NULLIF(TRIM(COALESCE(actual_atp_date, '')), '')) AS actual_atp_date,

  CAST(NULLIF(REPLACE(REPLACE(TRIM(COALESCE(rfs_cluster_id, '')), '.', ''), ',', ''), '') AS UNSIGNED) AS rfs_cluster_id,
  NULLIF(TRIM(COALESCE(remark_general, '')), '') AS remark_general
FROM stg_myrep_cutoff_import_full
WHERE TRIM(COALESCE(cluster_name, '')) <> '';

DROP TEMPORARY TABLE IF EXISTS tmp_myrep_cutoff_final_full;
CREATE TEMPORARY TABLE tmp_myrep_cutoff_final_full AS
SELECT
  n.*,
  CASE
    WHEN n.status_current_raw <> '' THEN n.status_current_raw
    WHEN n.cutoff_group = 'ATP' THEN 'ATP'
    WHEN n.cutoff_group = 'RFS' THEN 'RFS'
    WHEN n.cutoff_group = 'IMPLEMENTASI' THEN 'DRM'
    WHEN n.cutoff_group = 'RELEASED' THEN 'RELEASED'
    WHEN n.cutoff_group = 'VALSAL' THEN 'VALSAL'
    WHEN n.cutoff_group = 'BAK' THEN 'BAK'
    ELSE 'DRAFT'
  END AS status_current_final,
  CASE
    WHEN n.status_current_raw = 'ATP' OR n.cutoff_group = 'ATP' THEN 'DONE'
    WHEN n.status_bak_raw <> '' THEN n.status_bak_raw
    ELSE 'DONE'
  END AS status_bak_final,
  CASE
    WHEN n.status_current_raw = 'ATP' OR n.cutoff_group = 'ATP' THEN 'DONE'
    WHEN n.status_valsal_raw <> '' THEN n.status_valsal_raw
    ELSE 'DONE'
  END AS status_valsal_final,
  CASE
    WHEN n.status_current_raw = 'ATP' OR n.cutoff_group = 'ATP' THEN 'RELEASED'
    WHEN n.status_batch_raw <> '' THEN n.status_batch_raw
    ELSE 'RELEASED'
  END AS status_batch_final,
  CASE
    WHEN n.status_current_raw = 'ATP' OR n.cutoff_group = 'ATP' THEN 'DONE'
    WHEN n.status_drm_raw <> '' THEN n.status_drm_raw
    ELSE 'DONE'
  END AS status_drm_final,
  CASE
    WHEN n.status_atp_raw IN ('DONE','PUNCLIST') THEN n.status_atp_raw
    WHEN n.status_current_raw = 'ATP' OR n.cutoff_group = 'ATP' THEN 'DONE'
    ELSE NULL
  END AS status_atp_final,
  CASE
    WHEN n.status_checklist_raw <> '' THEN n.status_checklist_raw
    WHEN n.status_current_raw = 'ATP' OR n.cutoff_group = 'ATP' THEN 'DONE'
    ELSE NULL
  END AS status_checklist_final
FROM tmp_myrep_cutoff_norm_full n;

UPDATE tb_myrep_cluster c
INNER JOIN tmp_myrep_cutoff_final_full n
  ON UPPER(TRIM(c.cluster_name)) COLLATE utf8mb4_uca1400_ai_ci = UPPER(TRIM(n.cluster_name)) COLLATE utf8mb4_uca1400_ai_ci
 AND UPPER(TRIM(c.city_name)) COLLATE utf8mb4_uca1400_ai_ci = UPPER(TRIM(n.city_name)) COLLATE utf8mb4_uca1400_ai_ci
SET
  c.rfs_cluster_id = COALESCE(n.rfs_cluster_id, c.rfs_cluster_id),
  c.cluster_code = COALESCE(n.cluster_code, c.cluster_code),
  c.regional_name = n.regional_name,
  c.province_name = n.province_name,
  c.city_name = n.city_name,
  c.team_name = n.team_name,
  c.rpm = n.rpm,
  c.sm = n.sm,
  c.spv = n.spv,
  c.hp_plan = COALESCE(n.hp_plan, c.hp_plan),
  c.status_current = n.status_current_final,
  c.remark_general = COALESCE(n.remark_general, c.remark_general),
  c.updated_at = NOW();

UPDATE tb_myrep_bak b
INNER JOIN tb_myrep_cluster c ON c.id_myrep_cluster = b.id_myrep_cluster
INNER JOIN tmp_myrep_cutoff_final_full n
  ON UPPER(TRIM(c.cluster_name)) COLLATE utf8mb4_uca1400_ai_ci = UPPER(TRIM(n.cluster_name)) COLLATE utf8mb4_uca1400_ai_ci
 AND UPPER(TRIM(c.city_name)) COLLATE utf8mb4_uca1400_ai_ci = UPPER(TRIM(n.city_name)) COLLATE utf8mb4_uca1400_ai_ci
SET
  b.ba_open_date = COALESCE(n.ba_open_date, b.ba_open_date),
  b.bak_date = COALESCE(n.bak_date, b.bak_date),
  b.homepass_bak = COALESCE(n.homepass_bak, b.homepass_bak),
  b.status_bak = n.status_bak_final,
  b.remark_bak = COALESCE(n.remark_general, b.remark_bak),
  b.updated_at = NOW();

UPDATE tb_myrep_valsal v
INNER JOIN tb_myrep_cluster c ON c.id_myrep_cluster = v.id_myrep_cluster
INNER JOIN tmp_myrep_cutoff_final_full n
  ON UPPER(TRIM(c.cluster_name)) COLLATE utf8mb4_uca1400_ai_ci = UPPER(TRIM(n.cluster_name)) COLLATE utf8mb4_uca1400_ai_ci
 AND UPPER(TRIM(c.city_name)) COLLATE utf8mb4_uca1400_ai_ci = UPPER(TRIM(n.city_name)) COLLATE utf8mb4_uca1400_ai_ci
SET
  v.valsal_date = COALESCE(n.valsal_date, v.valsal_date),
  v.homepass_valsal = COALESCE(n.homepass_valsal, v.homepass_valsal),
  v.status_valsal = n.status_valsal_final,
  v.remark_valsal = COALESCE(n.remark_general, v.remark_valsal),
  v.updated_at = NOW();

UPDATE tb_myrep_batch_approval ba
INNER JOIN tb_myrep_cluster c ON c.id_myrep_cluster = ba.id_myrep_cluster
INNER JOIN tmp_myrep_cutoff_final_full n
  ON UPPER(TRIM(c.cluster_name)) COLLATE utf8mb4_uca1400_ai_ci = UPPER(TRIM(n.cluster_name)) COLLATE utf8mb4_uca1400_ai_ci
 AND UPPER(TRIM(c.city_name)) COLLATE utf8mb4_uca1400_ai_ci = UPPER(TRIM(n.city_name)) COLLATE utf8mb4_uca1400_ai_ci
SET
  ba.submission_date = COALESCE(n.submission_date, ba.submission_date),
  ba.hp_donasi = COALESCE(n.hp_donasi, ba.hp_donasi),
  ba.released_at = COALESCE(n.released_at, ba.released_at),
  ba.staging_status = n.status_batch_final,
  ba.remark_batch_approval = COALESCE(n.remark_general, ba.remark_batch_approval),
  ba.updated_at = NOW();

UPDATE tb_myrep_drm d
INNER JOIN tb_myrep_cluster c ON c.id_myrep_cluster = d.id_myrep_cluster
INNER JOIN tmp_myrep_cutoff_final_full n
  ON UPPER(TRIM(c.cluster_name)) COLLATE utf8mb4_uca1400_ai_ci = UPPER(TRIM(n.cluster_name)) COLLATE utf8mb4_uca1400_ai_ci
 AND UPPER(TRIM(c.city_name)) COLLATE utf8mb4_uca1400_ai_ci = UPPER(TRIM(n.city_name)) COLLATE utf8mb4_uca1400_ai_ci
SET
  d.drm_date = COALESCE(n.drm_date, d.drm_date),
  d.homepass_drm = COALESCE(n.homepass_drm, d.homepass_drm),
  d.nama_olt = COALESCE(n.olt_name, d.nama_olt),
  d.status_drm = n.status_drm_final,
  d.remark_drm = COALESCE(n.remark_general, d.remark_drm),
  d.updated_at = NOW();

UPDATE tb_rfs_myrep_cluster r
INNER JOIN tmp_myrep_cutoff_final_full n ON n.rfs_cluster_id = r.id_cluster
SET
  r.email_atp_date = COALESCE(n.email_atp_date, r.email_atp_date),
  r.status_atp = COALESCE(n.status_atp_final, r.status_atp);

UPDATE tb_rfs_myrep_doc_package p
INNER JOIN tmp_myrep_cutoff_final_full n ON n.rfs_cluster_id = p.cluster_id
SET p.status_package = 'DONE'
WHERE n.status_checklist_final = 'DONE';

SELECT
  n.status_current_final AS status_current,
  COUNT(*) AS total_rows
FROM tmp_myrep_cutoff_final_full n
GROUP BY n.status_current_final
ORDER BY FIELD(n.status_current_final, 'BAK', 'VALSAL', 'RELEASED', 'DRM', 'RFS', 'ATP', 'DONE'), n.status_current_final;
