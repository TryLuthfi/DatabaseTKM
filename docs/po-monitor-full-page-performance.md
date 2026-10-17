# PO Monitor: remaining full-page performance work

Scope: PO Monitor dashboard, monthly/weekly comparison and details, Breakdown's six
tabs/options/details, Excel export, local input modals, and their request paths.
This does not claim to audit every module of DatabaseTKM. Business-write actions
(import, rebuild, backfill, delete, save/claim) were inspected, not submitted as tests.

## Changes

- Seed master: read the 22 managed bowheer entries together, update only changed
  payloads, retain insertion/repair of missing or drifted entries and unrelated fields.
  Unchanged master work drops from 22 reads + 22 updates to one read + zero updates.
  Schema checking remains in place; no migration or new index is required.
- Comparison: apply the existing operation-local source reuse to the initial
  monthly matrix and each lazy target/cumulative detail. Nested week/month calls
  share the same context. The owning operation discards it even on exceptions;
  no persistent cache or additional initial request is introduced.
- Excel: preload only missing MyRep metadata, in batches of at most 100 distinct
  PO keys. Matching still uses the original SQL trim/case/accent collation.
  Duplicate concatenation order matters: when a source has multiple distinct
  nonempty field values, use the original per-PO query and its original access
  order. Cache fallback results only for the current report operation.
- Browser: coalesce resize notifications and check final sidebar width. Adjust
  columns without data/footer redraws. Mode switches draw only the active table;
  lazy week initialization has no second draw. ResizeObserver can still perform
  sizing over several animation frames while width actually changes.

## Validation

The production database was queried through a read-only transaction and fail-closed
SQL guard, without constructor, seed, claim sync or executed lock INSERTs. Baseline
and candidate used the same snapshot. Comparison checks cover 132 visible-detail
tuples with identical typed/ordered rows and complete renderer HTML. Overview checks
cover monthly bounds (including year crossing/invoice-only), combined week/month,
the complete 7,685-row export, and current/initial dashboard totals.

Observed SQL counts in the paired run:

| Operation | Baseline | Candidate | Equality |
|---|---:|---:|---|
| Initial October monthly matrix | 300 | 57 | exact |
| December–January invoice-only monthly matrix | 435 | 84 | exact |
| Week + month matrices (warm sequence) | 138 | 138 | exact |
| Full Excel report | 744 | 13 | all 7,685 rows exact |
| Example NRO effective target detail | 20 | 2 | 1,901 rows + HTML exact |

Initial October model work measured about 0.94 -> 0.46 seconds in the final paired
run. The NRO detail sample measured about 0.64 -> 0.12 seconds. Export timings vary
with DB/OS caches; query reduction is the more stable evidence. These are model
measurements, not browser/HTTP end-to-end or cold-load guarantees. Sequential calls
also warm existing PHP static caches; do not extrapolate later-case counts to an
independent cold request.

Local rollback fixtures: comparison weeks 21 assertions in each normal/missing-lock/
zero-lock mode; detail/Breakdown 128 assertions; export metadata 9 assertions over
213 adversarial rows; master seed 11 checks without a DB connection. Frontend:
11 layout tests and 16 lazy-data tests. CIKO additionally tested actual bundled
jQuery/DataTables/Bootstrap at container widths 320/600/900/1235px, preserving page,
search, footer and header/body alignment; initial fixture network zero and one lazy
week request. POCO independently verified metadata ordering and fresh report reads.

Production browser checks are representative normal flows, not a claim that every
detail button was clicked. Input modals manual/paste were opened without saving;
Invoiced Only works through its visible slider. Complete detail coverage is provided
by the read-only model/renderer parity above. Final production smoke/deployment
receipts are recorded in workspace artifacts, not assumed from an unauthenticated
redirect.

## Remaining measured costs

Large detail responses still transfer/render all rows (e.g. Regional 3 has 10,757
rows and about 3.2 MB HTML). Pagination/virtualization needs a separate data/summary/
export design to preserve full totals and downloads; this change preserves that
behavior. Schema/metadata checks and normal MyRep sync remain on request paths;
removing them requires a separately verified deployment/migration policy. Backend
Breakdown calculations and initial dashboard figures remain unchanged from the
previous optimization. No business-data migration, report formula or saved data
was changed by this patch.
