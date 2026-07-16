-- Sync tb_myrep_cluster.status_current from RFS/ATP progress.
-- Runs only for rows where derived RFS/ATP status is ahead of current status.
-- Safe to rerun: rows already in the correct/equal/newer status are ignored.

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP TEMPORARY TABLE IF EXISTS tmp_myrep_status_current_sync;

CREATE TEMPORARY TABLE tmp_myrep_status_current_sync AS
SELECT
    x.id_myrep_cluster,
    x.cluster_name,
    x.city_name,
    x.current_status,
    x.derived_status,
    x.status_rfs,
    x.status_atp,
    x.tanggal_rfs,
    x.actual_atp_date,
    FIELD(x.current_status,
        'DRAFT',
        'BA OPEN',
        'BAK',
        'VALSAL',
        'WAITING HO',
        'WAITING MYREP',
        'WAITING FINANCE',
        'RELEASED',
        'DONE BATCH APPROVAL',
        'DRM',
        'RFS',
        'ATP',
        'CHECKLIST',
        'CHECKLIST DOKUMENT',
        'DONE',
        'IMPLEMENTASI',
        'REJECTED',
        'HOLD'
    ) AS current_rank,
    FIELD(x.derived_status,
        'DRAFT',
        'BA OPEN',
        'BAK',
        'VALSAL',
        'WAITING HO',
        'WAITING MYREP',
        'WAITING FINANCE',
        'RELEASED',
        'DONE BATCH APPROVAL',
        'DRM',
        'RFS',
        'ATP',
        'CHECKLIST',
        'CHECKLIST DOKUMENT',
        'DONE',
        'IMPLEMENTASI',
        'REJECTED',
        'HOLD'
    ) AS derived_rank
FROM (
    SELECT
        c.id_myrep_cluster,
        c.cluster_name,
        c.city_name,
        (
            CASE
                WHEN UPPER(TRIM(COALESCE(c.status_current, ''))) COLLATE utf8mb4_unicode_ci = 'CHECKLIST' THEN 'CHECKLIST DOKUMENT'
                ELSE UPPER(TRIM(COALESCE(c.status_current, ''))) COLLATE utf8mb4_unicode_ci
            END
        ) COLLATE utf8mb4_unicode_ci AS current_status,
        (
            CASE
            WHEN UPPER(TRIM(COALESCE(r.status_atp, ''))) COLLATE utf8mb4_unicode_ci = 'DONE'
                AND p.actual_atp_date IS NOT NULL
                THEN 'CHECKLIST DOKUMENT'
            WHEN UPPER(TRIM(COALESCE(r.status_atp, ''))) COLLATE utf8mb4_unicode_ci = 'PUNCLIST'
                OR p.actual_atp_date IS NOT NULL
                THEN 'ATP'
            WHEN UPPER(TRIM(COALESCE(r.status_rfs, ''))) COLLATE utf8mb4_unicode_ci IN ('PARTIAL', 'PARTIAL RFS', 'FULL RFS')
                OR p.tanggal_rfs IS NOT NULL
                THEN 'RFS'
            ELSE NULL
            END
        ) COLLATE utf8mb4_unicode_ci AS derived_status,
        r.status_rfs,
        r.status_atp,
        p.tanggal_rfs,
        p.actual_atp_date
    FROM tb_myrep_cluster c
    LEFT JOIN tb_rfs_myrep_cluster r
        ON r.id_cluster = c.rfs_cluster_id
    LEFT JOIN (
        SELECT
            cluster_id,
            MAX(tanggal_rfs) AS tanggal_rfs,
            MAX(actual_atp_date) AS actual_atp_date
        FROM tb_rfs_myrep_doc_package
        GROUP BY cluster_id
    ) p
        ON p.cluster_id = r.id_cluster
    WHERE c.rfs_cluster_id IS NOT NULL
        AND c.rfs_cluster_id > 0
) x;

DELETE FROM tmp_myrep_status_current_sync
WHERE derived_status IS NULL
    OR derived_status COLLATE utf8mb4_unicode_ci = current_status COLLATE utf8mb4_unicode_ci
    OR current_rank <= 0
    OR derived_rank <= 0
    OR derived_rank <= current_rank;

-- Preview rows that will be updated.
SELECT
    derived_status AS will_update_to,
    current_status,
    COUNT(*) AS total
FROM tmp_myrep_status_current_sync
GROUP BY derived_status, current_status
ORDER BY derived_rank, current_rank;

SELECT
    id_myrep_cluster,
    cluster_name,
    city_name,
    current_status,
    derived_status AS will_update_to,
    status_rfs,
    status_atp,
    tanggal_rfs,
    actual_atp_date
FROM tmp_myrep_status_current_sync
ORDER BY derived_rank DESC, city_name, cluster_name;

START TRANSACTION;

UPDATE tb_myrep_cluster c
JOIN tmp_myrep_status_current_sync s
    ON s.id_myrep_cluster = c.id_myrep_cluster
SET
    c.status_current = s.derived_status,
    c.updated_at = NOW()
WHERE UPPER(TRIM(COALESCE(c.status_current, ''))) COLLATE utf8mb4_unicode_ci <> s.derived_status COLLATE utf8mb4_unicode_ci;

SELECT ROW_COUNT() AS updated_rows;

COMMIT;

DROP TEMPORARY TABLE IF EXISTS tmp_myrep_status_current_sync;
