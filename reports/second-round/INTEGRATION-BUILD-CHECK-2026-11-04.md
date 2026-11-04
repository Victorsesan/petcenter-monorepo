# Integration Build Check — 2026-11-04

## Scope

This was a non-destructive integration rehearsal against `origin/main` at `251d8c2`. A temporary worktree was created and each available worker branch was merged in assignment order. No merge was pushed to `main`.

Available branches tested: MANUS-01, MANUS-02, MANUS-03, MANUS-04, MANUS-05, MANUS-06, MANUS-07, MANUS-09, MANUS-10, and MANUS-11. MANUS-08 has no remote branch and could not be included.

## Merge result

The full worker set does **not** merge cleanly without an integration pass.

| Branch | Result | Main conflict area |
|---|---|---|
| MANUS-01 | Merged cleanly | — |
| MANUS-02 | Conflict | `adopt/admin/chat-start.php`, `adopt/admin/chat-widget.js` |
| MANUS-03 | Merged cleanly in the rehearsal after MANUS-01 | — |
| MANUS-04 | Conflict | Seven pet-category pages; worker coordination documents |
| MANUS-05 | Merged cleanly | — |
| MANUS-06 | Conflict | `adopt/pets/detail/index.html` |
| MANUS-07 | Conflict | `adopt/adoptions/status/index.html` |
| MANUS-09 | Merged cleanly | — |
| MANUS-10 | Conflict | Shared chat widget plus category, status, application, services, blog, and testimonial pages |
| MANUS-11 | Merged cleanly | — |

The conflicts are genuine shared-file overlaps, not GitHub authentication or repository-sync failures. They must be resolved by preserving each task's documented anchors and combining the changes. Blindly accepting either side would discard work.

Recommended integration order is: backend/chat ownership first (MANUS-01, MANUS-02), public RTL/widget and page changes (MANUS-03, MANUS-04), rehoming/admin work (MANUS-05, MANUS-06), status work (MANUS-07), testimonials/chat-rate work (MANUS-09), then MANUS-10's browser-QA and rollout changes. MANUS-08 must be added when its branch exists.

## Clean-subset build checks

The temporary worktree containing the cleanly merged subset (MANUS-01, MANUS-03, MANUS-05, MANUS-09, and MANUS-11) passed:

- JavaScript syntax: 27 files, 0 failures.
- PHP syntax: 1,281 files, 0 failures.
- HTML structure: 40 non-legacy/non-vendor HTML files, 0 invalid document/body structures.

The MANUS-03 branch independently passed JavaScript syntax for 26 files and PHP syntax for 1,277 files.

## Browser RTL verification

The MANUS-03 branch was served locally and tested at 390×844 after applying `dir="rtl"` to both `html` and `body` for 18 routes:

- All 18 routes returned HTTP 200.
- No uncaught page errors occurred.
- No horizontal overflow occurred on the tested routes except `/apply/`.
- `/apply/` measured `scrollWidth=398` against a `390px` viewport on the MANUS-03 snapshot. MANUS-10's branch contains the targeted step-indicator fix and reports the corrected result as `scrollWidth=390`.
- Hidden filter/review sheets were reported by the raw geometry script as outside the viewport because their closed-state transforms place them below the viewport. This is expected hidden-state geometry, not a visible overflow defect.
- No Arabic-Indic or Eastern Arabic-Indic digits were emitted by the tested pages.

The browser test confirms that the converted logical properties keep the visible page shell in bounds when RTL is applied, but it is not a substitute for real Arabic localized pages because the repository still has no translated language tree.

## RTL implementation gaps

The current active templates do not contain literal `dir="rtl"` attributes because Arabic templates have not yet been delivered. RTL was tested by applying the document direction at runtime.

The active email and phone inputs also do not consistently declare `dir="ltr"`; the audit found missing explicit LTR attributes in the application, contact, pet-match, sign-in, and testimonial forms. This should be corrected as part of the Arabic/RTL implementation before treating Task 7 as complete. It was not silently changed in this integration-only check.

## Numerical behavior

All inspected currency and date formatting calls currently use Western/Latin formatting, overwhelmingly through `en-US` locale calls such as `toLocaleString('en-US')` and `Intl.DateTimeFormat('en-US', ...)`. The browser audit found:

- No Eastern Arabic-Indic or Arabic-Indic output.
- No inconsistent Arabic numeral output between tested templates.
- Currency samples consistently use the existing dollar-plus-Western-digit convention.
- No translated Arabic locale formatter is present yet.

Therefore the current behavior is internally consistent as a Western-numeral English implementation, but Arabic numeral behavior is not yet implemented or decided in localized templates. A future Arabic pass must explicitly choose Western digits or Arabic-Indic digits and apply the decision consistently to currency, dates, times, counts, star-rating labels, and distance values.

## MANUS-10 chat-scroll verification

MANUS-10 commit `8607868` was served independently and tested from a scrolled homepage. Chat close restored the exact saved scroll position immediately and after 800ms (`saved=92`, `immediate=92`, `settled=92`) while restoring the page's prior smooth-scroll setting.

## Conclusion

The repository is synchronized, and the clean subset builds successfully. The full integration is **not yet merge-ready** because of the shared-file conflicts listed above, the missing MANUS-08 branch, and the explicit RTL form-direction/numerical-localization gaps. MANUS-10's `/apply/` and chat-scroll fixes are verified and should be integrated during the conflict-resolution pass.
