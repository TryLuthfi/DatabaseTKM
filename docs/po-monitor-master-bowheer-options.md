# PO Monitor master bowheer visibility

The main Tambah PO modal previously built suggestions from dashboard rows.
Master-only bowheers therefore disappeared from its suggestions and the dashboard.

The controller now reads the existing `tb_bowheer_po` option method for the modal.
The view adds master names to the existing suggestions, retaining transaction names.
Dashboard current/cache and Data Awal append missing master names with zero values.
Existing rows, PICs, amounts, summary totals and detail/export calculations remain
unchanged. No master seed IDs, schema, transaction records or persisted dashboard
snapshot are changed. Future master additions appear without hardcoded names.

Production read-only candidate validation on 2026-10-30: 150 checks passed.
Existing dashboard rows and totals were identical; PO summaries and all export
detail rows were identical. Actual view datalist sections rendered ASIANET/GILANG.
IDs 23/24 remain ADT/DIAN KARYA; IDs 28/29 remain ASIANET/GILANG. Eight protected
tables were fingerprinted before/after in a read-only transaction, with no changes.
All six master-only names appear, including the four pre-existing master entries
MULTIPOLAR, NFT, INTI PKPU and NET. No PO records are created by listing a master.

Run `php tests/po_monitor_master_bowheer_regression.php` for isolated regression
coverage. Production verification does not submit business forms or claim a full
authenticated browser test.
