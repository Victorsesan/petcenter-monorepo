# PET CENTER — TO-DO (THE SHARED INBOX)

**This is the one file every department uses to talk to each other.** Think of it as your email inbox. If a Worker, a VDM or an Integrator needs you to know something, check something, fix something or do something, it is written here, addressed to you.

- **Workers, VDMs and Integrators can all add entries and update entries.**
- **Everyone must read the Table of Contents below at the start of every session, and again before finishing.** Find the rows addressed to you (your task number, your role, or `ALL`) and read those entries in full.
- This file lives at the **root of the repo on `main`**, never inside a task branch.

---

## RULES (SHORT VERSION)

1. **Never edit this file on a task branch.** Edit it only on `main`, using the exact steps in "How to add or update an entry" below. It is the one file (together with VDM task docs) that any role may commit straight to `main`.
2. **Never put secrets in this file.** No tokens, passwords, API keys, `config.php` contents, or applicants' personal data. The repo is shared by many accounts.
3. **No code dumps.** Point to the file, the line range, and the branch instead. A few lines of code in a code block are fine when they are needed to explain the problem.
4. **Never delete or rewrite someone else's entry.** To answer or close one, update its `Status` and add a dated line under `Updates`.
5. **Every entry gets a number and a Table of Contents row.** Numbers go up by one and are never reused.
6. **If an entry is addressed to you, it counts as permission to touch the files it names**, even if they are outside your original task. Put `TODO #N` in your commit message and mention it in your report.
7. **Entries are in plain language and in full.** The reader has no memory of anything you know. Say what, where, why, and what you want done.

---

## HOW TO ADD OR UPDATE AN ENTRY

### A. Write your entry using this template

Copy this block to the **bottom of the Entries section** and fill in every field.

```
### #[NUMBER] — [Short topic]

- **From:** [Your role and name or task number — e.g. "Worker, Task 3", "VDM, Web Projects 1-2", "Integrator, Web", "CEO"]
- **To:** [One or more recipients, separated by commas — e.g. "Worker Task 5, Worker Task 3b", "VDM Admin Project 1", "Integrator Web", or "ALL"]
- **Type:** [Suggestion | Notice | Bug outside task scope (bigger fix) | Instruction | Question | Future improvement]
- **Severity:** [Critical | High | Medium | Low]
- **Date and time:** [YYYY-MM-DD HH:MM UTC]
- **Where:** [File path(s), line range(s) or section, and the branch — or "N/A"]
- **Reason / issue (in full):** [Everything the reader needs: what is wrong or what is wanted, how you found it, how to reproduce it, why it matters.]
- **What is needed:** [The action you want from the recipient(s), or "For your information, no action."]
- **Status:** OPEN
- **Updates:** [none yet — later lines look like: "2026-10-12 09:10 UTC — Worker Task 5: fixed on branch task/5, commit abc1234."]
```

**Severity guide:** Critical = live site broken, data loss or security hole. High = a feature is broken or blocks another task. Medium = a real bug or gap that does not block anyone. Low = polish, cleanup, nice-to-have.

**Status values:** OPEN → IN PROGRESS → DONE (or WON'T DO, with the reason under Updates).

### B. Add your row to the Table of Contents

Add one row at the **bottom of the table** in the same order. Keep the topic short.

### C. Commit and push it to `main`

Commit or stash your own work first, so your task branch is clean.

```
git fetch origin
git switch main
git pull origin main
# edit TODO.md: add your entry at the bottom + your Table of Contents row
git add TODO.md
git commit -m "TODO #[NUMBER]: [short topic]"
git push origin main
git switch task/[your-branch]      # go back to your own work
```

**If the push is rejected** (someone else added an entry at the same moment):

```
git pull --rebase origin main
```

If TODO.md conflicts, keep **both** people's entries. Renumber **your** entry to the next free number and fix your Table of Contents row, then:

```
git add TODO.md
git rebase --continue
git push origin main
```

If you get a "shallow" history error, run `git fetch --depth=200 origin main` first and repeat.

Back on your task branch, merge the latest main (`git merge origin/main`) when you next do your normal sync, so you can see the update.

### D. Updating or closing an entry

Switch to `main`, pull, edit **only** the `Status` and the `Updates` line of that entry (and its Table of Contents status), commit with `TODO #[NUMBER]: status → DONE`, and push the same way.

---

## TABLE OF CONTENTS

Look for your task number, your role, or `ALL` in the **To** column.

| # | Date (UTC) | From | To | Severity | Status | Topic |
|---|------------|------|----|----------|--------|-------|
| 1 | 2026-10-11 | CEO | ALL | Medium | OPEN | This inbox exists: how to use it and where to look |
| 2 | 2026-10-12 | VDM, Web Projects 3-4-5 | CEO | Medium | DONE | Rule 6 missing from adoption-website-project-summary.md's Warning & Instructions |
| 3 | 2026-10-12 | VDM, Unified Status Dashboard | CEO, ALL | High | DONE | Branch names collide across projects — `task/[number]` alone is not unique |
| 4 | 2026-10-13 | CEO | VDM Unified Status Dashboard, VDM Web Projects 3-4-5 | High | OPEN | Trackers still tell workers to "request completion files from the CEO" |
| 5 | 2026-10-30 | Integrator | ALL second-round Manus accounts | High | OPEN | Second-round coordination, central account reports, and master issue register |

---

## ENTRIES

### #1 — This inbox exists: how to use it and where to look

- **From:** CEO
- **To:** ALL
- **Type:** Notice
- **Severity:** Medium
- **Date and time:** 2026-10-11 13:40 UTC
- **Where:** `TODO.md` at the repo root on `main`
- **Reason / issue (in full):** Workers can now see every other worker's code and branches in the shared repo. Out-of-scope bugs, suggestions, instructions for specific workers, and bigger fixes that are not small enough to do on the spot are all communicated in this one file instead of being buried in task reports. Workers, VDMs and Integrators all use it the same way.
- **What is needed:** Read the Table of Contents at the start and the end of every session. Handle every entry addressed to you or to `ALL`. Add entries using the template above. Do not edit this file from a task branch.
- **Status:** OPEN
- **Updates:** 2026-10-14 07:10 UTC — VDM, Web Projects 3-4-5: fixed on `main`, commit a9a9b8b. Rule 3 already had no completion-file exception from the earlier git-aware rewrite — nothing to change there. Replaced all 13 "Task N's completion file" references (Shared File Coordination table, Tasks 1, 2, 3b, 4b, 5b, 6a, 6b, 7b, 8, 9) with "code on `main` once merged, plus `reports/task-[project]-N.md`, or `git diff origin/main...origin/task/[project]-N` if not yet merged." Also fixed 5 places still saying "pull the most recently submitted version" (pre-git phrasing left over from before the branch workflow) to "merge latest `main` into your branch." Status left OPEN since this entry also covers `Unified-Status-Dashboard-Task-Tracker-v3-FINAL.md`, which I don't own.

<!-- NEW ENTRIES GO BELOW THIS LINE. COPY THE TEMPLATE FROM "HOW TO ADD OR UPDATE AN ENTRY". -->

### #5 — Second-round coordination and central account reports

- **From:** Integrator, second-round preparation
- **To:** ALL second-round Manus accounts, final coordinator
- **Type:** Instruction
- **Severity:** High
- **Date and time:** 2026-10-30 01:47 UTC
- **Where:** `docs/second-round/`, `reports/second-round/`, and this shared inbox
- **Reason / issue (in full):** The second debugging round has 11 assigned work packages. Every account must read the core documents, verify its scope against the current repository, classify reported issues, and publish a traceable report. The account prompt packet, source documents, issue register, and report index are now in the repository.
- **What is needed:** Read `docs/second-round/SECOND-ROUND-MANUS-COORDINATION.md` and `docs/second-round/MANUS-ACCOUNT-PROMPTS.md` before starting. Use the assigned account ID and report path in `reports/second-round/ACCOUNT-REPORT-INDEX.md`. Never put credentials in TODO, reports, or code.
- **Status:** OPEN
- **Updates:** 2026-10-30 01:47 UTC — Integrator: created the source-document bundle, worker-package inventory, master issue register, pre-filled account prompts, and central report index in the documentation commit containing this entry.

### #2 — Rule 6 missing from adoption-website-project-summary.md's Warning & Instructions

- **From:** VDM, Web Projects 3-4-5
- **To:** CEO
- **Type:** Bug outside task scope (bigger fix)
- **Severity:** Medium
- **Date and time:** 2026-10-12 07:06 UTC
- **Where:** `adoption-website-project-summary.md`, Warning & Instructions section — the numbered list jumps 5 → (unnumbered admin-file exception note) → 7, with no standalone rule 6 anywhere in the document. Confirmed by direct search, not assumption.
- **Reason / issue (in full):** Directive 14a in this same document, and Directive 13a in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, both say every code block needs an explanatory comment "per Warning & Instructions rule 6" — but that rule doesn't exist in the adoption summary. It does exist in the admin summary, in the expected position between rules 5 and 7: "6. Write code comments. You must always write code comments on all your codes to help other AI easily find specific codes during debugging and also update the code comment after any changes made in the code. Give a name for each code and write its comment." This strongly suggests rule 6 was accidentally dropped from the adoption summary specifically, not a deliberate removal, since both documents' later rules still reference it as if present. I did not edit the document myself, per Directive 14a (never edit this document directly) — flagging here instead since only the CEO/Dev Manager extends it.
- **What is needed:** Restore rule 6 in `adoption-website-project-summary.md`'s Warning & Instructions (the admin summary's existing rule 6 text is a reasonable candidate, or your own preferred wording), so the two documents' numbering and cross-references line up again. Until fixed, my own tracker's copy of the Warning & Instructions section borrows the admin summary's rule 6 text directly, noted as such in the tracker itself.
- **Status:** DONE
- **Updates:** 2026-10-12 08:00 UTC — CEO / Dev Manager: confirmed. Rule 6 was already missing in the original upload, not removed by the git changes. Restored in `adoption-website-project-summary.md` with wording identical to rule 6 of the admin summary. Thank you for catching it; use the repo copy of the summary from now on.

### #3 — Branch names collide across projects — `task/[number]` alone is not unique

- **From:** VDM, Unified Status Dashboard
- **To:** CEO, ALL
- **Type:** Bug outside task scope (bigger fix)
- **Severity:** High
- **Date and time:** 2026-10-12 09:03 UTC
- **Where:** Both `VDM-GIT-SYNC-COORDINATION-PROTOCOL.md` and `WORKER-GIT-SYNC-PROTOCOL.md`, every branch-naming example (`task/3`, `task/3b`). Confirmed in the live repo, not assumption: `origin/task/3` already exists and belongs to a different VDM's project entirely (`geo-filter.php` and the pet-category pages — location/shipping work, task numbers 3 and 3b), with its own `reports/task-3.md` and `reports/task-3b.md` already pushed.
- **Reason / issue (in full):** Every VDM numbers their own tasks starting at 1, independently, because that's how our own project trackers work and nothing in either protocol document says otherwise. The branch-naming convention in both protocols is just `task/[task-number]`, with no project name in it. My own project's tracker also has a Task 3. If I assign a worker branch `task/3`, it collides directly with the branch that's already there — same name, two unrelated pieces of work, no way to tell them apart in `git branch -r`, and a worker merging `origin/main` into a same-named local branch could get very confused about whose history they're looking at. This isn't hypothetical: it has already happened once. With multiple VDMs now assigning tasks under the same flat `task/N` namespace, it will keep happening as more projects reach task 1, 2, 3...
- **What is needed:** A project-scoped branch-naming convention added to both protocol documents (for example `task/[project-slug]-[number]`, so mine would be `task/status-1` … `task/status-12`, and the existing location/shipping work would ideally be renamed `task/geo-3` / `task/geo-3b` if that's still practical, or at minimum anyone new is told to use a scoped prefix going forward). I'm not editing either protocol document myself since only the CEO/Dev Manager does that (per each document's own rule). Until this is resolved, my own project's tracker uses `task/status-[number]` to avoid colliding with what's already in the repo — noted there as a deliberate deviation from the documents' literal examples, for this reason.
- **Status:** DONE
- **Updates:** 2026-10-12 18:00 UTC — CEO / Dev Manager: agreed and fixed. Branches are now `task/[project]-[number]` (for example `task/status-1`), and the same scoped id is used for the report (`reports/task-status-1.md`) and the code anchors (`TASK-status-1 START/END`). Added to the Worker, VDM and Integrator protocols. Your `task/status-[number]` choice is correct. The existing `task/3` keeps its name and is not renamed.

### #4 — Trackers still tell workers to "request completion files from the CEO"

- **From:** CEO
- **To:** VDM Unified Status Dashboard, VDM Web Projects 3-4-5
- **Type:** Instruction
- **Severity:** High
- **Date and time:** 2026-10-13 19:00 UTC
- **Where:** `Unified-Status-Dashboard-Task-Tracker-v3-FINAL.md` (Directive 3 at line 47, and the "Files to request from the CEO" / "Read first" lines of Tasks 5, 6, 7, 8, 9, 10, 11, 12 — about a dozen places) and `Web-Projects-3-4-5.md` (lines about 269, 332, 393, 410, 506: "Task 1's completion file", "Files to Request Before Starting"). Both on `main`.
- **Reason / issue (in full):** Both trackers were updated to the git workflow (branches, short reports, scoped names), but they still contain the old hand-off wording. Directive 3 of the Status tracker still says completion files "are required, not optional", which contradicts Directive 8a in the same file. Many tasks still say "Files to request from the CEO: Task N's completion file" or "Read first: Task N's completion file". There are no completion files any more. If a worker follows those lines it will stop and ask the CEO for a file that does not exist, which wastes the worker's tokens and sends work back to the CEO by hand. Everything now lives in the repo: a finished task is a branch `task/[project]-[N]` plus `reports/task-[project]-[N].md`, and once the CEO merges it, the code is on `main`.
- **What is needed:** In both trackers: (1) delete the "required completion files" exception in Directive 3 and say that the only `.md` file a worker adds is its short `reports/task-[project]-[N].md`. (2) Replace every "Task N's completion file" with "the code from Task N on `main` once it has been merged (the CEO merges after approval), plus `reports/task-[project]-[N].md`". If Task N is not merged yet, say "read branch `origin/task/[project]-[N]` with `git diff origin/main...origin/task/[project]-[N]`". (3) Replace "Files to request from the CEO: [files that are in the repo]" with the repo path and "read it from `main`". Keep "Files to request" only for things that are NOT in the repo (for example the SVG Icon Library doc, until it is added). (4) Re-upload the tracker the same way as before and add a line under Updates here.
- **Status:** OPEN (Unified Status Dashboard side only — Web Projects 3-4-5 still needs its own fix)
- **Updates:** 2026-10-13 — VDM, Unified Status Dashboard: fixed in `Unified-Status-Dashboard-Task-Tracker-v3-FINAL.md`, re-uploaded to `main`. All three items done for this tracker, plus two more found while fixing it: Task 5's "admin-dashboard.html" was never the real file (it's `adopt/admin/index.html`, with a decoy `Old/` copy — same pattern as the `adoption-application.php` / `Old/` decoy on the adoption side), and Task 4/12's reference to `update-adoption.php` needed a flag — `adopt/admin/` has both `update-adoption.php` and a meaningfully different `update-adoption8.php`, and this tracker doesn't know which one is actually live, so both tasks now tell the worker to confirm with the CEO before touching either.
