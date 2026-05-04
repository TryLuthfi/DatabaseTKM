ALTER TABLE tb_logistik_purchase_request_detail
ADD COLUMN stok_planning INT(11) DEFAULT NULL AFTER stok_area,
ADD COLUMN volume_planning INT(11) DEFAULT NULL AFTER qty_planning;

UPDATE tb_logistik_purchase_request_detail
SET
    stok_planning = COALESCE(stok_planning, stok_area),
    volume_planning = COALESCE(NULLIF(volume_planning, 0), NULLIF(qty_planning, 0), qty_request)
WHERE stok_planning IS NULL
   OR volume_planning IS NULL;
