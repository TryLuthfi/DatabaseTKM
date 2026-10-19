# PO MyRep: consistent List PO and detail invoice sources

PO 7400093997 has INITIAL header 36532 and FINAL header 38355 in cluster
13241. INITIAL terms 1–3 contain invoice dates; FINAL terms are blank. List PO
already resolves each term across headers of the same cluster, type and PO number.
The detail previously hid INITIAL and read only FINAL, showing zero invoices.

Both paths now share `resolvePoGroupTerminRows`. Detail keeps its selected header
and displays the resolved full term records, including their original term IDs.
Update therefore receives the source record shown in that row. Raw
`getTerminRowsByPoId` remains unchanged for synchronization and write workflows.
The fix does not copy invoices, migrate tables or modify business records.

Grouping preserves cluster, PO type and normalized PO number. Blank numbers remain
isolated by header ID. Existing list precedence (invoice date, certificate date,
nonzero value, category and header ID) is preserved.

Validation:

- `php tests/po_myrep_detail_termin_regression.php`: 15 isolated checks for
  historical invoices, full source IDs, grouping isolation, denied clusters,
  missing tables, invalid dates, explicit zero values and raw-read preservation.
- Candidate executed against production in a read-only transaction: all 3,539
  List PO rows strictly identical to the prior model; target invoice/outstanding
  amounts agree with detail; another 12 detail/list samples agree.
- Actual detail PHP template rendered with production rows: source IDs
  184195/184196/184197/193313/193314 and invoice dates are preserved in Update
  modal attributes. Invoiced total 12,534,451; outstanding total 8,356,300.
- PHP lint for changed runtime files and regression test.

Production probing bypasses controller estimate/monitor synchronization and does
not submit Update forms. Template rendering is not an authenticated browser test.
