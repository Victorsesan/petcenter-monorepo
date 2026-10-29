
## Latest update — B5/B6 and C1a/C1b checkpoint

| Task | Result | Commit or reason |
|---|---|---|
| Rehoming Platform Task B5 | applied | Commit `b6a8b2a`; added `adopt/account/settings/index.html`; diff checks passed. |
| Rehoming Platform Task B6 | applied | Commit `9eb816e`; added `adopt/merchants/index.html` using the deliberately chosen plural public-profile path; diff checks passed. |
| Rehoming Platform Task C1a/C1b | stopped | Blocks 1–6 passed sequential exact matching, but block 7 for `adopt/admin/index.html` had old-snippet count 0 after B2’s shared verification-queue integration. No C1a/C1b files were applied. |

Current feature commit before this documentation update is `9eb816e`. The next account must reconcile C1a/C1b block 7 against the current admin page instead of retrying blindly.
