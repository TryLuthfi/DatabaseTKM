# Excel PO VALUE follows released PO value

For ON PO rows, the report now reconciles `PO VALUE` to the same source as PO Breakdown: latest `tb_po_amend.release_value` by descending `amend_no`, falling back to `tb_po.total_value` when no amendment exists. A release of zero remains zero.

The export has one row per allocation/source group, and can also contain standalone terms for the same PO. Its rows remain unchanged. Release reconciliation runs after combining allocated and standalone rows, grouped by internal `id_po`. The released value is distributed in proportion to the previous term-plan subtotal for each row. A cumulative-cent calculation makes the sum exact and prevents rounding from creating a negative allocation. With no positive plan weights, equal shares preserve the released value. A single-row PO receives its full released value.

`OUTSTANDING TOTAL` follows each corrected row's PO value minus its existing invoice amount, floored at zero. All invoice amounts, invoice-year selection, dates, term PLAN/NILAI, target-week/carry-over fields, metadata, NY PO rows and export headers keep their existing behavior. There is no database migration, claim synchronization, source-value update or change to PO Breakdown.

Example PO7400124474: `PO VALUE` Rp11,895,223; recorded invoice Rp10,036,000; derived outstanding Rp1,859,223. This fixes the report projection, not the source term/claim values.

Focused offline regression: `php tests/po_report_release_value.php`. Independent QC: `php tests/po_report_release_qc.php`. Local whole-report comparison additionally checks every field except PO VALUE/derived outstanding against the original exporter, matches per-PO released totals and checks source-table fingerprints before/after.
