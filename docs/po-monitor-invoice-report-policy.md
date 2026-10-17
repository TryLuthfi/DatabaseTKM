# PO Monitor: Done Inv 2026 follows Download Excel Report

The current dashboard previously added all claims stored in `tb_po.dashboard_invoice_2026`, while Download Excel Report selected invoice claims per allocation with a standalone-term fallback. This counted overlapping IMPORT and MYREP_SYNC records differently.

The current dashboard now obtains Done Inv 2026 from the same SQL source projection as the report. It includes all PO statuses, terms 1–5, positive selected invoice amounts and the report-selected invoice date in 2026. Allocated invoice SUM/date MAX and NULL fallback behavior remain identical to the existing report; a zero allocated sum does not activate the unallocated fallback. Standalone terms continue to use their term invoice date. No source-label-only IMPORT filter was added: the selected report source can contain MANUAL or MYREP_SYNC fallback claims.

Current live summary, cached summary, dashboard table filtering/sorting/totals and target-invoice breakdown receive a fresh read-only aggregate. Invoice-only bowheers such as cancelled-only POs appear with zero values for unrelated financial metrics. VGREEN EVCS/BSS categorization is retained. Current Done + Outs + NY and percentages consume the corrected Done value through existing controller/view calculations.

The historical **Data awal** toggle, its MANUAL adjustments, raw header values and cache rebuilding retain their previous behavior. The compact dashboard UNION explicitly converts text projections to utf8mb4_unicode_ci before combining branches, accommodating heterogeneous legacy production collations. The export contents remain unchanged. No migration or update to business records, claims or cache is required.

## Verification

Run with the project PHP runtime:

```powershell
D:/XAMPP/php/php.exe -l application/models/MPO_Monitor.php
D:/XAMPP/php/php.exe tests/po_invoice_report_integration.php
D:/XAMPP/php/php.exe tests/po_invoice_report_integration.php --mixed-collation
D:/XAMPP/php/php.exe tests/po_invoice_snapshot_golden.php /absolute/private/path/snapshot.json
```

The integration suite uses only the dedicated local schema `zeyn_po_invoice_test_20261017` (empty schema clones must already exist). Fixtures and cleanup run in a rollback transaction. The standard suite passes 30 assertions. Optional mixed-collation mode passes 31 assertions: it requires an empty dedicated schema, temporarily changes only its PO/bowheer column collations before the fixture transaction, then restores their original definitions after rollback. It covers allocated/standalone sources, zero versus NULL fallback, positive/negative amounts, multiple allocations, mixed invoice years, report term limits, cancelled-only groups, VGREEN categories, stale cache, filtering/sorting/pagination, historical initial mode and unchanged table checksums across reads.

The optional frozen golden suite verifies all 371 affected POs / 1,855 terms in the private 17 October 2026 production reconciliation snapshot. That snapshot's full report total is Rp87,077,215,159.25. Current local data differs from production; its NRO report and corrected dashboard total are Rp86,247,106,049.25. The intended invariant is equality to the report using the same database snapshot, not a hardcoded monetary amount.

Production push/deploy requires a separate explicit request. Use the mandated deployment gate and verify its report plus authenticated PO Monitor behavior after deployment.
