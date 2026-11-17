# AKSELERASI 2026

Menu Database All PO: **AKSELERASI 2026**. Route `Akselerasi_2026`, title **TARGET INVOICE AKSELERASI 2026**. CodeIgniter 3 MVC follows existing templates and the Target 250 M VIEW permission.

## Sources and accounting

- Locked target baseline and target-owner PIC snapshot are in `MAkselerasi_2026::definitions()`. There is no target editing endpoint, database migration, claim submission, or schema initialization.
- Baseline total Rp58,071,247,160 = regular Rp29,526,472,569 + CO Rp28,544,774,591. This uses the sum of the agreed source rows (the pasted grand total differed by Rp2).
- Achievement reads `tb_po_term_claim.invoice_amount`, signed, joined once through term to PO. Period starts 2026-11-16, inclusive. `end_date` is inclusive and flexible; default is today in Asia/Jakarta. Claims before the start or after the selected cutoff are excluded. Allocation tables, term-level all-time SUM/MAX snapshots and dashboard caches are not used.
- Source bowheer uses nonblank `tb_po.dashboard_bowheer`, then `tb_bowheer_po.bowheer`; source type is `tb_po.type_project`. Exact aliases mirror the confirmed Target 250 M categories. Explicit VGREEN EVCS/BSS bowheers and generic PT VGREEN EVCS/DC*/BSS/battery configuration types are supported. Other sources are shown in an unmapped reconciliation table, not silently included in a target.
- Achievement consumes regular first, then CO **within each target row**, never across different bowheers/types. Signed negative net reduces regular achievement; corrected net above regular reduces CO first mathematically. Percentages may exceed 100% or be negative. Progress bars are bounded visually. Remaining is clamped per row before summing, so one row exceeding its target cannot hide another row's unmet target.
- IFORTE LBS and KOMITEL REKTIF retain independent source targets internally. Display combines them until an explicit KOMITEL master bowheer is present; then they split automatically without changing baseline totals. Adding master alone cannot reclassify old IFORTE claims: real PO source bowheer must identify KOMITEL before its Achievement can be separate.
- Target-owner PICs are frozen with the baseline rather than inferred from individual invoice submitters. User revision 17 Nov: NRO/DONASI WARDANI, SOP SLAMET, IFORTE FTTT/FTTH/REGULER DONNY, LBS/KOMITEL/TA STAR HENDRY, other OSP/PERMIT ZAENUL, VGREEN WENDY. Operational PIC changes do not silently move the target.

## UI

PIC summary table contains Total Target, Achievement, Sisa Target and Capaian. Details put PIC before Bowheer and Type, followed by regular/CO amounts. Search and PIC filter update row numbering, all filtered footer totals, summary and KPI/progress values together. Zero monetary/percentage values display a dash; arithmetic and CSV retain numeric zero. CSV exports all matching rows. The source-period reconciliation total stays fixed and is labelled separately. There is no pagination for the small fixed target set.

## Verification

Run PHP with the existing XAMPP runtime, and Node:

```
php tests/akselerasi_2026_data_test.php
php tests/akselerasi_ciko_test.php
php tests/akselerasi_ciko_controller.php
node --test tests/akselerasi_ui.test.cjs
```

These cover locked targets/PICs, signed ledger reconciliation, aliases, future KOMITEL separation, allocation boundaries, cutoff validation, authorization, query failure, safe HTML/JSON/DOM rendering, filters/numbering/totals, empty states and CSV formula protection. Synthetic accounting tests do not write the business database.

Historical reconciliation snapshot (17 Nov 2026): 16–17 Nov net Rp3,884,457,751 from 204 claim rows (202 positive, two negative), across 100 PO. Positive-only Rp3,916,144,874 minus corrections Rp31,687,123. This is a snapshot, not a hardcoded dashboard achievement.

Development preview uses the copied local production database. Any disposable synthetic-session preview router is outside this repository, bound to loopback and GET/dashboard/assets only; it is not part of the release. Production login and hooks must be checked under a real authorized session after a separately requested deploy.
