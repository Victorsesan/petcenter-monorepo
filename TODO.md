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
