-- Patch MyRep SITAC HO mapping for NIK 9406522.
-- Run on VPS after deploying the related access/mapping data changes.

ALTER TABLE `tb_myrep_pic_mapping_city`
  MODIFY COLUMN `sitac_ho` VARCHAR(255) NULL;

UPDATE `tb_myrep_pic_mapping_city`
SET
  `sitac_ho` = NULLIF(
    TRIM(BOTH ',' FROM REPLACE(
      CONCAT(',', REPLACE(REPLACE(REPLACE(COALESCE(`sitac_ho`, ''), ';', ','), '|', ','), ' ', ''), ','),
      ',9801919,',
      ','
    )),
    ''
  ),
  `updated_at` = NOW()
WHERE FIND_IN_SET('9801919', REPLACE(REPLACE(REPLACE(COALESCE(`sitac_ho`, ''), ';', ','), '|', ','), ' ', '')) > 0;

UPDATE `tb_myrep_pic_mapping_city`
SET
  `sitac_ho` = CASE
    WHEN `sitac_ho` IS NULL OR TRIM(`sitac_ho`) = '' THEN '9406522'
    WHEN FIND_IN_SET('9406522', REPLACE(REPLACE(REPLACE(`sitac_ho`, ';', ','), '|', ','), ' ', '')) = 0 THEN CONCAT(TRIM(BOTH ',' FROM `sitac_ho`), ',9406522')
    ELSE `sitac_ho`
  END,
  `updated_at` = NOW()
WHERE FIND_IN_SET('9406522', REPLACE(REPLACE(REPLACE(COALESCE(`sitac_ho`, ''), ';', ','), '|', ','), ' ', '')) = 0;

SELECT
  COUNT(*) AS `total_city_rows`,
  SUM(
    CASE
      WHEN FIND_IN_SET('9406522', REPLACE(REPLACE(REPLACE(COALESCE(`sitac_ho`, ''), ';', ','), '|', ','), ' ', '')) > 0 THEN 1
      ELSE 0
    END
  ) AS `mapped_sitac_ho_9406522`
FROM `tb_myrep_pic_mapping_city`;

SELECT
  COUNT(*) AS `remaining_sitac_ho_9801919`
FROM `tb_myrep_pic_mapping_city`
WHERE FIND_IN_SET('9801919', REPLACE(REPLACE(REPLACE(COALESCE(`sitac_ho`, ''), ';', ','), '|', ','), ' ', '')) > 0;
