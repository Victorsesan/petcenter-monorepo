# Second-Round Account Reports

This directory is the central reporting location for the 11 second-round Manus accounts. Each account must create or update `reports/second-round/<ACCOUNT-ID>.md` on its own branch and include the starting commit, documents read, files inspected, issue classifications, changes, tests, unresolved blockers, and commit hashes. A blocked dependency is not a reason to omit the report: record the missing evidence, classify the affected work as Blocked or Needs My Review, and report all checks that were still possible.

The aggregate status is maintained in [ACCOUNT-REPORT-INDEX.md](ACCOUNT-REPORT-INDEX.md). The issue source is [the master issue register](../../docs/second-round/SECOND-ROUND-ISSUE-REGISTER.md), and the work ownership is defined in [the coordination plan](../../docs/second-round/SECOND-ROUND-MANUS-COORDINATION.md).

**Universal evidence rule:** The uploaded worker archive has now been imported as Markdown evidence under `docs/second-round/completion-evidence/`, with per-archive subdirectories and an `IMPORT-MANIFEST.md`. Every account should use the matching evidence file when present, compare it with the assignment and current source, and record only genuinely absent files as evidence gaps. Accounts must continue independently verifiable checks and submit their report even when some items are Blocked or Needs My Review.

Do not put GitHub tokens, passwords, API keys, private keys, applicant data, or other secrets in these reports. Reports are repository artifacts and are visible to every participating account.

## Required report structure

```markdown
# Second-Round Report — ACCOUNT-ID

## Assignment

## Starting repository state

## Documents read

## Verification results

## Changes made

## Tests performed

## Unresolved issues and handoffs

## Commits and branch
```

Only the final coordinator may mark an account report as reconciled in the aggregate index.
