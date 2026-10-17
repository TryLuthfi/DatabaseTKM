# PO Monitor — lazy Achieved, Breakdown and Filter optimization

The October 2026 change reduces work only when an existing lazy endpoint is requested. It adds no database migration, index, persistent cache, preload, synchronization, or business-data write.

## Backend

`getComparisonAchievedDetail()` preserves its original claims, allocations, fallback projections, SQL ordering and final PHP calendar filter. Its MyRep-header selection is grouped once by the same `CONVERT(TRIM(po_number) USING utf8mb4) COLLATE utf8mb4_unicode_ci` comparison key. `COALESCE(MAX(FINAL id), MAX(AMANDMENT id), MAX(id))` exactly preserves FINAL precedence, AMANDMENT fallback and newest-id selection. The category equality predicates retain the source-column collation. Blank PO keys still match blank keys; SQL NULL keys cannot match through equality.

`getBreakdownTargetInvoiceRows()` retains both original source queries and all output formatting. Its original majority-month and week routines now run once for each identical start/end date pair in that method invocation. The temporary array is released on return. The initial dashboard and monthly matrix paths do not call this new memoization or header aggregation.

Existing schema maintenance, bowheer seeding, NRO claim synchronization and period-lock behavior are unchanged. HTTP read endpoints can therefore still write through those existing paths. Production verification must use an explicit read-only model/renderer harness when changes to business data are prohibited.

## Verification

`tests/po_monitor_lazy_data_integration.php --baseline=<pre-change-model.php>` uses the dedicated loopback schema `zeyn_po_invoice_test_20261017`, seeds synthetic rows within one transaction and always rolls back. It compares exact row arrays (including order and types), full rendered HTML, all six Breakdown grouping modes and dependent Filter options against the supplied original model. It covers precedence/newest header selection, accent/case/space-equivalent PO keys, empty keys, allocation fallbacks, repeated dates, zero and negative values, custom weeks, cross-month/year periods and total selections. No schema change or real-data copy is required.

The fixture currently passes 66 comparisons. Production snapshot performance and independent QC are recorded by ZEYN separately; fixture execution time is not an HTTP latency measurement.

## Production-data read-only preflight (2026-10-17)

ZEYN ran the original and candidate model/renderer in paired read-only transactions against production data, without the controller constructor, synchronization or production application deployment. Each before/after pair shared its transaction snapshot; the 132 cases were split across five transactions, with a sixth transaction for Breakdown. All 132 comparison detail results matched exactly in row order, types and complete rendered HTML. This includes NRO October (438 rows; candidate 0.446 s), W41 (149 rows; 0.258 s) and W42 (289 rows; 0.258 s). The earlier unoptimized audit exceeded its 8 s single-query guard for those three NRO requests.

Because the original unbounded correlated query exceeded that guard, its parity reference was restricted to the exact claim IDs selected using the original PHP calendar; the original query projection, joins, sorting and final PHP calendar filter still ran. Its bounded runtime is therefore not an unoptimized full-request performance measurement. The candidate measurements exclude synchronization, constructor/schema checks, HTTP/network and browser rendering.

The complete 28,101-row Breakdown source matched strictly. All six grouping modes across seven filter combinations (42 grouped results) and 49 dependent-option results matched strictly. The source retrieval/mapping benchmark was 4.9904 s before and 1.2882 s after, with four observed SQL statements in each case. No production writes were attempted by this harness. Artifacts are retained under the parent workspace `outputs/po-monitor-data-optimization/parity-*.json`.

## Lazy browser requests

Filter options are requested when the filter modal opens, including refresh on every reopen. The initial options request is removed; the initial Project table still requests data once. Option requests use cancellation, sequence and current-filter checks so an obsolete response cannot overwrite newer selections. Apply is disabled while the latest options are loading or unavailable, and reopening the modal retries a failed request.

Changing filters/search/limits refreshes only the active Breakdown tab and marks initialized hidden tabs dirty. A new tab makes one initial request. Every existing tab still requests fresh data once on reopening, preserving the previous freshness behavior: clean tabs keep their page, while dirty tabs reset pagination. No response cache persists across page loads, and no hidden-tab prefetch is added.

`node tests/po_breakdown_lazy_frontend.test.cjs` extracts the actual coordinator from the view and checks 16 scenarios, including changed server data while a tab is hidden, response races, dependent options, error/retry and duplicate-request avoidance. Actual plugin/browser verification is recorded separately by CIKO.
