INSERT INTO tb_master_bowheer_bilco (nama_bowheer, pic_user, jt_invoice)
SELECT 'PT. ASIANET', NULL, 30
WHERE NOT EXISTS (
    SELECT 1
    FROM tb_master_bowheer_bilco
    WHERE nama_bowheer = 'PT. ASIANET'
);
