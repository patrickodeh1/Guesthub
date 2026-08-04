# CLAUDE.md — Agent Operating Instructions for GuestHub

You are working through `task-list.md`, logging everything in `tracker.md`. Read both fully before starting anything. This file governs how you work, not what to build — task intent is in `task-list.md`.

## THE CORE RULE — read this twice

**You may not move to a new task until the current task is verified working AND the person you're working with has explicitly told you to move on.**

This means:
- Finishing writing code is not "done." Done means it has been tested against real behavior and confirmed working.
- You do not get to decide a fix is "probably fine" and move forward. If you cannot personally verify it (e.g. you don't have server/browser access), you must hand back clear verification steps and **wait for the human to run them and report back** before touching the next task.
- If a fix doesn't work on the first attempt, that is not a signal to try something else immediately — investigate why it didn't work, explain what you found, and only proceed with a new attempt once you understand the actual cause. Guessing repeatedly wastes the person's time re-running things that don't fix the real problem (see: the APP_URL/symlink investigation in `tracker.md` — an unnecessary detour happened because a symlink theory was acted on without confirming it against actual evidence first).
- If you get stuck (multiple attempts haven't resolved something), say so explicitly, log exactly what's been tried and ruled out in `tracker.md`, and ask for direction rather than continuing to guess indefinitely.

## Before touching any task

1. Read the task's full description in `task-list.md` — don't work from a one-line paraphrase in your own memory.
2. Check `tracker.md` for whether this task (or something adjacent to it) has partial history — don't repeat investigation that's already been done and logged.
3. Explore the actual codebase yourself. Do not assume standard framework behavior — this codebase has existing helper methods, existing status enums, and existing patterns (see below). Reusing what exists is mandatory, not optional.
4. If a task depends on another unresolved task or an unanswered open question (both are flagged explicitly in `task-list.md`), do not build around it with a guess. Flag the dependency and either wait or ask.

## While working

- Prefer small, verifiable, defensive changes over large speculative ones. When editing a file, confirm your exact target string exists uniquely in the file before writing (guard against silent wrong edits) — check, don't assume formatting matches what you remember from earlier in the session.
- After every change, verify by re-reading the file/output, not by assuming the edit worked because no error was thrown.
- One task's changes should be traceable as their own unit in `tracker.md` — do not silently bundle unrelated changes into another task's entry.

## MANDATORY: tracker.md logging

Every task must be logged with:
- Status (`Not Started` / `In Progress` / `Blocked` / `Done`)
- Every file changed, with what changed in it
- What was tested, and how — be specific, not "tested and works"
- Any assumption made in place of missing information, clearly labeled as an assumption
- Anything the person needs to manually do (migrations, config, manual testing steps)

If a task turns out to be more complex than expected mid-build (e.g. what looked like one bug is actually two, or a fix requires touching a system you didn't expect), **stop, log what you found, and confirm scope before continuing** — don't silently expand the task without checking in.

If an investigation goes down a wrong path (like a theory that turns out to be incorrect), log that explicitly in `tracker.md` too, including what was reverted and why — this prevents the same wrong path being retried later, by you or anyone else picking this up.

## Hard constraints — do not violate

- Do not implement direct August/Bluetooth lock integration outside Seam. Already decided — Seam-only for now.
- Do not guess the answer to anything marked `[OPEN QUESTION]`. Log as blocked, move to something else only with explicit sign-off, or wait.
- Do not touch the full guest-detail page layout redesign — explicitly deprioritized.
- Do not remove the booking_id/token from guest URLs without a working, tested alternative already in place.
- `.env` and secrets are never included in anything shared outside the local/production environment.
- If you add a database migration, say so explicitly and loudly in `tracker.md` — migrations are run manually via cPanel terminal on production, they will not be discovered automatically.
- Do not build the new full booking-status flow (Task 68) as a system parallel to the existing approve/decline system built today — they must be reconciled into one model first.

## When something is ambiguous

Stop, log it as blocked/open in `tracker.md` with a clear explanation of the ambiguity, and wait for direction rather than guessing at user-facing behavior (copy, colors, layout, state transitions) not explicitly specified in `task-list.md`.

## Definition of "verified" (do not skip this)

A task is only Done when at least one of these is true, and it's stated in `tracker.md` which one:
- It was tested live against real data/a real scenario and the actual observed behavior matches what was expected, described in the tracker in specific terms (not "it works")
- The person explicitly confirmed it themselves and told you to mark it done

Code that compiles, a patch that applies cleanly, or a script that runs without error is **not** verification. Only observed correct behavior is.