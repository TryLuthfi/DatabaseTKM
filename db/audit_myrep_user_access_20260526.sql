-- Audit MyRep access untuk 1 user
-- Ganti @target_nik sesuai user di dropdown.

SET @target_nik := '9900618';

SELECT
  u.id,
  u.nik,
  u.nama_karyawan,
  u.id_level,
  COALESCE(l.nama_level, '-') AS nama_level,
  u.status_user
FROM tb_master_user_new u
LEFT JOIN tb_level l ON l.id_level = u.id_level
WHERE u.nik = @target_nik;

SELECT
  validation_user,
  COUNT(*) AS total
FROM tb_master_user_child muc
JOIN tb_master_user_new u ON u.id = muc.id_master_user
WHERE u.nik = @target_nik
GROUP BY validation_user
ORDER BY validation_user;

SELECT 'RPM_AREA' AS role_key, COUNT(*) AS city_count FROM tb_myrep_pic_mapping_city WHERE rpm_area = @target_nik
UNION ALL SELECT 'SM_AREA', COUNT(*) FROM tb_myrep_pic_mapping_city WHERE sm_area = @target_nik
UNION ALL SELECT 'SPV_AREA', COUNT(*) FROM tb_myrep_pic_mapping_city WHERE spv_area = @target_nik
UNION ALL SELECT 'SND_AREA', COUNT(*) FROM tb_myrep_pic_mapping_city WHERE snd_area = @target_nik
UNION ALL SELECT 'ADMIN_AREA', COUNT(*) FROM tb_myrep_pic_mapping_city WHERE admin_area = @target_nik
UNION ALL SELECT 'SND_HO', COUNT(*) FROM tb_myrep_pic_mapping_city WHERE snd_ho = @target_nik
UNION ALL SELECT 'ATP_HO', COUNT(*) FROM tb_myrep_pic_mapping_city WHERE atp_ho = @target_nik
UNION ALL SELECT 'RFS_HO', COUNT(*) FROM tb_myrep_pic_mapping_city WHERE rfs_ho = @target_nik
UNION ALL SELECT 'SITAC_HO', COUNT(*) FROM tb_myrep_pic_mapping_city WHERE sitac_ho = @target_nik
UNION ALL SELECT 'DC_HO', COUNT(*) FROM tb_myrep_pic_mapping_city WHERE dc_ho = @target_nik
UNION ALL SELECT 'QA_HO', COUNT(*) FROM tb_myrep_pic_mapping_city WHERE qa_ho = @target_nik;

SELECT
  rp.page_key,
  rp.action_key,
  rp.role_key,
  COUNT(*) AS rule_count
FROM tb_myrep_role_permission rp
JOIN (
  SELECT DISTINCT role_key
  FROM (
    SELECT 'RPM_AREA' AS role_key FROM tb_myrep_pic_mapping_city WHERE rpm_area = @target_nik
    UNION ALL SELECT 'SM_AREA' FROM tb_myrep_pic_mapping_city WHERE sm_area = @target_nik
    UNION ALL SELECT 'SPV_AREA' FROM tb_myrep_pic_mapping_city WHERE spv_area = @target_nik
    UNION ALL SELECT 'SND_AREA' FROM tb_myrep_pic_mapping_city WHERE snd_area = @target_nik
    UNION ALL SELECT 'ADMIN_AREA' FROM tb_myrep_pic_mapping_city WHERE admin_area = @target_nik
    UNION ALL SELECT 'SND_HO' FROM tb_myrep_pic_mapping_city WHERE snd_ho = @target_nik
    UNION ALL SELECT 'ATP_HO' FROM tb_myrep_pic_mapping_city WHERE atp_ho = @target_nik
    UNION ALL SELECT 'RFS_HO' FROM tb_myrep_pic_mapping_city WHERE rfs_ho = @target_nik
    UNION ALL SELECT 'SITAC_HO' FROM tb_myrep_pic_mapping_city WHERE sitac_ho = @target_nik
    UNION ALL SELECT 'DC_HO' FROM tb_myrep_pic_mapping_city WHERE dc_ho = @target_nik
    UNION ALL SELECT 'QA_HO' FROM tb_myrep_pic_mapping_city WHERE qa_ho = @target_nik
  ) role_hits
) roles ON roles.role_key = rp.role_key
WHERE rp.is_active = 1
  AND rp.is_allowed = 1
GROUP BY rp.page_key, rp.action_key, rp.role_key
ORDER BY rp.page_key, rp.action_key, rp.role_key;

SELECT
  upp.module_key,
  upp.page_key,
  upp.action_key,
  upp.is_allowed,
  COUNT(*) AS override_count
FROM tb_user_page_permission upp
JOIN tb_master_user_new u ON u.id = upp.id_user
WHERE u.nik = @target_nik
  AND upp.module_key = 'MyRepublik'
  AND upp.is_active = 1
GROUP BY upp.module_key, upp.page_key, upp.action_key, upp.is_allowed
ORDER BY upp.page_key, upp.action_key, upp.is_allowed;
