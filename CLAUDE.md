# CLAUDE.md — Working Agreement for This Project

This file is the standing prompt/context for AI-assisted work on GuestHub.
Read it at the start of every conversation before touching code.

---

## 0. Fundamental constraint: no filesystem access

The AI has no direct access to this repo, this server, or this terminal.
It cannot `cat`, `grep`, `ls`, or edit files on its own — every command it
"runs" against this codebase is actually a command it asks the human to run
and paste back the output of. Never assume a prior view of a file is still
accurate; files change between messages. Re-view/re-grep before editing
anything you haven't seen in the current message.

**Practical result:** every fix is a two-step loop:
1. AI asks for `grep`/`cat` output to see current real state.
2. Human pastes it. AI gives exact commands (sed/python/cat heredocs) to run.
3. Human runs them, pastes confirmation output (another grep/diff/wc -l).
4. AI verifies the output actually matches intent before calling it done.

Never skip step 3/4. "I wrote the code" is not "it shipped correctly."

---

## 1. How to deliver code changes

- **Prefer inline paste-able commands over downloadable files.** This
  project's workflow is copy/paste into a live terminal, not downloading
  artifacts. Use `sed`, `python3 - << 'EOF' ... EOF`, or
  `cat > path << 'EOF' ... EOF` heredocs directly in the response.
- **Use `python3` for anything regex-fragile or multi-line** (e.g. deleting
  a 3-line if-block across 4 files, restructuring a markdown file). `sed`
  is fine for single-line, unambiguous replacements only — Blade files with
  nested quotes/braces routinely break naive sed patterns; don't fight it,
  switch to python3 with an explicit line-based or careful regex approach.
- **Always follow a write/edit command with a verification command**
  (`grep -c`, `grep -n`, `wc -l`, `git diff`) in the same reply, and ask for
  its output before declaring the change complete.
- **When asked to remove/clean something, actually delete it — don't
  comment it out** — unless explicitly told otherwise. Commented-out dead
  code left in "just in case" is not what "clean it out" means here.
- **Don't mark a task DONE based on code existing.** Confirm via grep/cat
  that the code matches the actual spec, and where possible get the human
  to functionally test it (click through the UI) before updating
  task-list.md / tracker.md.

---

## 2. Diagnosing "not working" reports

When something is reported broken, don't guess — narrow it down in this
order before proposing a fix:
1. Confirm the file/config actually changed as intended (`grep`/`cat`).
2. Confirm the change is reaching the client (curl status codes, correct
   script load order, cache-busting / hard refresh — browser cache is a
   very common false "still broken" signal; test in incognito to rule it
   out before assuming leftover code).
3. Get browser DevTools console + Network tab output for client-side
   issues — don't speculate about CSP, load order, or JS errors without
   seeing them.
4. Only then propose a code fix, and explain *why* that's the cause based
   on what was actually observed, not a hunch.

If a fix doesn't work on the first try, don't just reapply variations —
isolate what specifically is still failing (which of several sub-symptoms
persists) before writing the next patch.

---

## 3. Scoping and task hygiene

- **The task list / tracker.md can drift from what the client actually
  asked for** — summarized/paraphrased task entries lose precision over
  time. When a task is vague, go back to the original client message
  (chat exports, text files) and quote their exact words before building
  anything. Paraphrase-of-a-paraphrase is how scope gets built wrong.
- **A single client message often bundles multiple distinct asks** (e.g.
  "send a URL to guests" also implied session persistence AND access
  revocation — three different systems). Split these explicitly and name
  the split, rather than building one blended, ambiguous feature.
- **Flag architectural dependencies before building.** Auth/session
  systems, status-flow state machines, and routing structure are the kind
  of work that, if built piecemeal per-feature, gets rebuilt when the
  "real" version lands later. When a task touches one of these, say so and
  propose sequencing (design/self-contained work first, foundational
  systems next, entangled/auth work last) rather than just starting.
- **Don't assume a task is done because chat shows the dev said "I'll do
  that."** Look for the client's later confirmation (or lack of one) in
  the same conversation before crediting it as shipped. Verify against
  actual code when in doubt (see Section 0).
- **Distinguish "blocked, needs client answer" from "blocked, needs
  internal work" from "actually already answered somewhere in the chat
  history but not reflected in the tracker."** Re-read chat exports fully
  before declaring something an open question.
- **Some reported issues are out of scope / won't-fix** (e.g. third-party
  platform's own activity log labeling) — confirm this plainly with the
  client and close the task with a reason, don't leave it in limbo marked
  "blocked."

---

## 4. Task list conventions

- Tasks are grouped in **phases by dependency risk**, not by feature area
  alone: (1) self-contained design/layout, (2) self-contained backend/data,
  (3) bugs, (4) foundational systems multiple things depend on (status
  flow), (5) dashboard/content work that depends on phase 4, (6) misc
  content, (7) auth rework — built last, highest architectural risk.
- Every task entry should be traceable to either an original client quote
  or an explicit "(new, found in chat log)" / "(new, found in code)" tag —
  no task should exist that can't be sourced.
- Closed tasks (shipped or won't-fix) move to a CLOSED section rather than
  being deleted, so there's a record of what happened and why.

---

## 5. Communication style expected in this project

- Be direct about trade-offs and uncertainty. Don't oversell a fix as
  definitely working before it's actually been tested.
- When multiple valid approaches exist (e.g. free vs paid spellcheck
  options), research and present the real landscape with sources, not just
  the first idea — this client explicitly cares about being able to show
  they've done real research, not just picked something quickly.
- When a request reveals a performance or UX problem in a previous fix
  (e.g. "it freezes now"), diagnose root cause before patching again —
  don't just add a workaround on top of a flawed approach.
