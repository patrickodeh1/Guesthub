# Prod Access / Client Action Items

Things that cannot be verified or fixed from local dev — either need prod server
access, or need the client to run a command/check something and report back.

---

## 1. ID decline "database query exception" (prod only, not reproducible locally)

**Client report (paraphrased):** Laravel error when declining an ID — after entering
notes and submitting, gets a database query exception. Happens on prod only, not local.

**Diagnosis so far:** Reviewed `declineBooking()` in `Admin/BookingController.php`
against the `bookings` table schema, model `$fillable`, and casts. Everything the
code writes (`decline_reason`, `photo_id_received`, `status`) is consistent locally.
No code-level defect found that would explain a query exception in general.

**Most likely cause:** schema drift — one or more migrations that added these columns
were never run on the production database (common when local dev runs `migrate:fresh`
but prod only gets `migrate`).

**What we need:**
- Run on prod: `php artisan migrate:status`
- Send us that output, OR
- Next time the error happens, copy the exact exception message/stack trace shown
  (Laravel error page or log line from `storage/logs/laravel.log`)

Once we have either, we can confirm and fix in one pass instead of guessing.

---

## 2. SMS / Twilio

**Status:** Not a code bug per client's own diagnosis — Twilio is configured for
admin-only currently and was not configured correctly for guest-facing SMS on the
client's end. We will verify the code path wires up correctly to whatever Twilio
config keys exist, but will not "fix" working code based on a config issue.

**What we need (if we get further reports of guest SMS not sending):**
- Confirm Twilio Account SID / Auth Token / From-number are set correctly in prod `.env`
- Confirm the "From" number is SMS-capable and not a toll-free number pending verification

---

## 3. Email provider

**Decision:** Using Resend for guest email notifications going forward. Will need
a `RESEND_API_KEY` (or equivalent) set in prod `.env` when the notification feature
(registration received / declined / approved / etc.) is deployed. We'll flag exact
env var names once that code is written.

---

## 4. Rate/business-rule inputs still needed (see TASKS.md Q3–Q6)

- Incidentals charge structure
- Parking per-day rate tiers
- Early check-in tiers (8am / 12noon) and pricing
- Late checkout rates: authorized vs unauthorized, hourly rate
- Coming-soon landing page copy (or confirm we draft it)
- Early-access registration: payment required? which processor?
- Confirm "custom verification step naming" = existing extra-steps feature

---

## 5. Admin gets logged out / "page doesn't exist" after being idle (Task 1)

**Client report (paraphrased):** After some time, admin gets logged out and has to
log back in, even losing the page they were on. Client believes "remember me"
should prevent this.

**Code review result:** No bug found. Session config, auth guard config, and the
"Remember me" checkbox on login are all standard, correct Laravel implementations.
`Auth::attempt($credentials, $request->boolean('remember'))` is wired properly —
checking "Remember me" should keep the admin logged in via a long-lived cookie
independent of normal session expiry.

**Fixed regardless (safe, low-risk):** Added `trustProxies(at: '*')` in
`bootstrap/app.php`. If prod sits behind any reverse proxy / load balancer / CDN
that terminates HTTPS (nginx, Cloudflare, a PaaS, etc.), Laravel previously had
no way to reliably detect the original request was secure — this can cause
session/remember-me cookies to behave inconsistently. This is safe to have even
if it turns out not to be the root cause.

**What we need you to check in prod `.env`:**
- `SESSION_LIFETIME` — how many minutes before an idle session expires *without*
  "Remember me" checked. Default is 120. If this is set unusually low, that alone
  would explain frequent logouts for anyone not checking "Remember me."
- `SESSION_SECURE_COOKIE` — should generally be left unset/`null` unless you know
  you need to force it. If it's explicitly set to `true` but the app isn't
  correctly recognized as HTTPS (see proxy note above), cookies won't be stored
  at all, breaking sessions AND remember-me.
- `SESSION_DOMAIN` — should almost always be `null` unless intentionally sharing
  the session across subdomains. A leftover/incorrect value here can silently
  break cookie storage.
- `APP_URL` — should exactly match the real prod URL (including `https://`).
- Confirm the `sessions` table exists and is migrated (`SESSION_DRIVER=database`
  is the default) — run `php artisan migrate:status` and check for it.
- Confirm `APP_KEY` has NOT changed/been regenerated recently. Rotating `APP_KEY`
  invalidates all existing sessions and cookies instantly for every logged-in
  user — this alone would explain sudden, unexplained logouts across the board.

**If none of the above explains it:** next time it happens, note the exact URL
being visited and whether "Remember me" was checked at login, and we can dig
further with that specific case.

Status: living doc, update as items are resolved.
