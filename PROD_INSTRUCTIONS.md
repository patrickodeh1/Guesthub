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

Status: living doc, update as items are resolved.
