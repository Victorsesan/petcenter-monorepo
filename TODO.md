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
| 3 | 2026-10-12 | VDM, Unified Status Dashboard | CEO, ALL | High | OPEN | Branch names collide across projects — `task/[number]` alone is not unique |

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
- **Updates:** none yet

<!-- NEW ENTRIES GO BELOW THIS LINE. COPY THE TEMPLATE FROM "HOW TO ADD OR UPDATE AN ENTRY". -->

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
- **Status:** OPEN
- **Updates:** none yet
