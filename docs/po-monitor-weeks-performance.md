# PO Monitor Weeks Column

Weeks Column remains lazy: the existing weekly endpoint runs only when requested.
The initial monthly page continues calling the original `getComparisonMatrix`
path. No weekly work is added to initial page loading.

The weekly endpoint now calls `getComparisonWeekMatrices`. Its temporary context:

- Reuses the projects, raw target summary and raw claim queries between the weekly
  and monthly matrices required by the weekly view.
- Executes the original target-detail query once per project. Its extra MyRep
  joins and their row multiplicity are deliberately preserved; the simpler raw
  target-summary query cannot replace these detail rows.
- Indexes raw detail row offsets by week/month, preserving original row order.
  Historical cumulative calculations inspect only the applicable period rows.
- Releases the context in `finally`, including exceptions. It adds no persistent
  cache, invalidation policy, schema migration or background/initial page work.

The amount/date/calendar routines, existing lock lookup and lock insertion logic,
detail endpoints, monthly calculations and view remain unchanged. A missing
current-period lock can still produce the same existing `INSERT IGNORE` during a
normal request; this optimization does not introduce additional write behavior.

## Verification

The independent production check executes old/new model sources against the same
read-only snapshot, intercepting any lock insertion rather than writing it.
For October 2026, complete weekly and monthly matrix arrays matched exactly:
42.87 seconds before versus 2.95 seconds after (about 93% less elapsed time).
Direct raw-query calls decreased from 1,516 to 29; this counter excludes builder
queries such as lock lookups. Both versions proposed zero lock insertions in that
snapshot. These are measured backend model times, not a browser/network SLA.

An independent initial-month comparison also matched the full matrix exactly,
with 272 direct raw-query calls in both versions: the initial page adds no queries.
Separate-process October measurements with query logging disabled recorded peak
memory of 36 MiB before and 38 MiB after, a temporary 2 MiB increase during the
weekly endpoint. Both processes loaded the same two model sources for comparison,
so their common test-harness overhead is included in both figures. That check
also matched output hashes and measured 37.95 versus 3.42 seconds.

The rollback-only fixture suite accepts a saved original model:

```text
php tests/po_comparison_weeks_integration.php --baseline=<original-model.php>
php tests/po_comparison_weeks_integration.php --baseline=<original-model.php> --zero-lock
php tests/po_comparison_weeks_integration.php --baseline=<original-model.php> --missing-lock-table
```

It uses only the local disposable schema `zeyn_po_invoice_test_20261017`, never
business data. Each mode checks full matrix equality and exact proposed lock-write
arguments/order across January, October, December, multiple months and year
boundaries. Fixtures include duplicate MyRep headers, allocation and term fallback,
pipeline rows, zero/negative amounts and invoice dates before/within periods.
The normal initial-month matrix and temporary-context cleanup are also checked.

Temporary source reuse retains detail rows and small row-offset indexes until the
weekly request ends. This trades bounded request memory (the measured 2 MiB peak
increase above) for substantially fewer queries and repeated date conversions;
memory is not retained between requests. It is not a claim of zero additional
memory within the weekly request.
