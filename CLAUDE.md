# CLAUDE.md — Working Guide for AI Assistants

This file exists so any AI agent (Claude or otherwise) can pick up this project
mid-stream without the human re-explaining everything. Read this file first,
then `TASKS.md`, then `CLIENT_TASKS.md`, then `PROD_INSTRUCTIONS.md`.

## What this project is

Laravel app (`Guesthub`) — a multi-property short-term-rental / guest-management
platform. Property owners ("admins") manage bookings; guests go through a
token-based portal (no login) to submit ID, get approved, check in via
GPS + smart lock (Seam integration), see a digital welcome guide, and check out.

## Where things live

- `TASKS.md` — the numbered master task list, working interpretation of each
  task, and status (not started / done / no-op). This is the source of truth
  for progress. **Update it every time a task is completed or its status
  changes.**
- `CLIENT_TASKS.md` — the client's **original, unedited wording** for each
  numbered task, kept separate from our interpretation so anyone can verify
  we didn't misread the ask. When the client clarifies or replies about a
  task, append their reply verbatim under that task's entry rather than
  editing/summarizing it away.
- `PROD_INSTRUCTIONS.md` — anything that needs prod server access, an exact
  error message, business-rule numbers, or other client/human action before
  it can be finished. Living doc — update as items get resolved.

## How this session has been working (keep doing this)

1. **One task at a time.** Before writing code for a task, restate the
   client's original wording (pull it from `CLIENT_TASKS.md`) plus your
   interpretation of what to build, and get explicit confirmation before
   touching code. The client has repeatedly flagged that assistants
   (including prior Claude sessions) tend to miss nuance in these requests —
   don't skip this confirmation step even if a task looks obvious.
2. **Don't guess at business rules or numbers** (pricing tiers, copy, rate
   structures, etc.) — ask, or build the field/UI with the mechanism in place
   and leave the actual number for the client to fill in, and note it in
   `PROD_INSTRUCTIONS.md`.
3. **Don't "clean up" or remove code without confirming it's actually dead.**
   Trace every usage across the codebase (grep controllers, views, routes,
   JS) before deleting or rewriting something that looks unused.
4. **Small, reviewable diffs per task**, not sweeping rewrites. Use
   `str_replace` over regenerating whole files where possible.
5. **Write/update feature tests for what you build**, using the existing
   sqlite in-memory test setup (`phpunit.xml` already configures this).
   You (the AI) typically cannot run `php`/`composer`/PHPUnit directly — no
   PHP interpreter is available in the sandbox this project has been worked
   from. Say so explicitly, write the tests anyway, and ask the human to run
   `php artisan test` locally and paste output back.
6. **Commit and push after each task** (or logical unit of work) to the
   working branch — don't batch unrelated tasks into one commit. Write
   descriptive commit messages; they double as a changelog since there's no
   separate one.
7. **Update `TASKS.md`** (mark done / add findings) in its own small commit
   right after the feature commit for that task.
8. **Flag prod-only issues, don't guess-fix them.** If a bug is reported as
   "only happens in prod," review the code for any real defect, but if
   everything checks out locally, document exactly what's needed from prod
   (exact error text, `migrate:status` output, `.env` values to check) in
   `PROD_INSTRUCTIONS.md` rather than making speculative changes.

## Known environment constraints (as of this writing)

- No PHP/Composer/PHPUnit binaries available in the AI's sandbox — can't lint
  or run tests directly. Manual code review substitutes for `php -l`.
- Sandbox network is allowlisted and does **not** include `packagist.org` —
  cannot run `composer require` for new PHP dependencies. If a task needs a
  new package (e.g. `resend/resend-php` for email), write the code assuming
  it will be installed, and tell the human to run the composer command
  locally after pulling.
- No prod database/`.env` access — anything requiring prod state must go
  through `PROD_INSTRUCTIONS.md` for the client to check/run.

## Working branch & repo access

Whoever continues this should be given a GitHub Personal Access Token (PAT)
scoped to this repo, ideally short-lived/revocable, plus the repo URL. With
that:

```bash
git clone https://github.com/patrickodeh1/Guesthub.git
cd Guesthub
git remote set-url origin https://<PAT>@github.com/patrickodeh1/Guesthub.git
git checkout fixes/session-1   # or create the next session branch off it
git config user.email "claude-agent@session.local"
git config user.name "Claude (session agent)"
```

Push directly to the working branch as you go (this has been the agreed flow
so far — not PR-per-task, direct pushes with the human reviewing via
`git diff`/pulling locally and running tests). Confirm this is still the
preferred flow with the human at the start of a new session in case it's
changed.

## Notification stack decisions already made

- **Email:** Resend (`resend/resend-php`, Laravel's built-in `resend` mail
  transport is already configured in `config/mail.php`). Needs
  `RESEND_API_KEY` in prod `.env` and `MAIL_MAILER=resend`.
- **SMS:** Twilio, via `App\Services\SmsNotificationService`. Was
  admin-only originally; being extended to guest-facing sends. Config keys
  live under `services.twilio.*`.
- Client has said (see `CLIENT_TASKS.md` task 30/5-followup) they want
  **both email and text working, and guest-selectable** which channel(s)
  they receive, with **text preferred**. Build both channels as first-class,
  not email-with-SMS-as-afterthought.

## Do not re-litigate (already resolved, don't ask again)

- "Guest ID" field naming = same as booking ID, just renamed. Do not remove
  it, only display as "booking ID" per task language.
- Task 33 (deposit-paid incorrectly flipping status to checked-in) — no fix
  needed, undo path already exists via booking edit page, client already
  informed.
- SMS "not working" (original report) was a client-side Twilio
  misconfiguration, not a code bug — don't chase this as a bug in isolation;
  see task 30 for the actual notification-system build-out.
