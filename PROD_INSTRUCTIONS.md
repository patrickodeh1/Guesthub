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

---

## 6. ID capture auto-fill/auto-capture + haze + blur detection (tasks 9, 10, 11)

**What shipped:** Replaced the manual tap-to-capture button with an OpenCV.js-based
live detection loop. While the camera is open, it checks ~4x/second whether the ID
fills the guide frame AND is sharp; after ~1 second of both being true, it
auto-captures. No button tap needed. If OpenCV.js fails to load (blocked network,
very slow connection), a manual fallback button appears so guests are never stuck.

**Why this should fix the haze issue:** the old flow let guests tap "capture"
instantly, often before the camera's autofocus/exposure had settled — producing a
soft/washed-out frame on nearly every capture. The new flow only captures once the
live frame has been detected as sharp, so it structurally can't fire on an
unsettled frame anymore.

**Needs real-world tuning after deploy — thresholds are untested against real
guest photos:**
- `IDW_CV_FILL_MIN` (currently 0.80) — how much of the guide box the ID must
  fill before auto-capture is allowed to consider it "in frame."
- `IDW_CV_SHARPNESS_MIN` (currently 120) — OpenCV Laplacian variance floor.
- `IDW_CV_STABLE_FRAMES_NEEDED` (currently 4, ~1 second at 250ms/check) — how
  long the frame must stay good before firing.
- All three are constants near the top of the `<script>` block in
  `resources/views/guest/show.blade.php`, clearly commented, easy to adjust
  without touching the detection logic itself.
- **Ask the client to test on a few real phones (iOS Safari + Android Chrome
  especially) after deploy** and report back if it's auto-capturing too early
  (blurry/cut-off results) or taking too long (frustrating wait) — we'll retune
  from real numbers rather than guessing further.

**CDN dependency — recommend self-hosting before going live long-term:**
Currently loads OpenCV.js from `https://docs.opencv.org/4.9.0/opencv.js` at
runtime (client-side, in the guest's browser — this has zero relationship to
your server/cPanel hosting, it's just a script tag). This works out of the box,
but ties correctness to that external site staying up. To remove that
dependency:
1. Download the file from that same URL.
2. Place it at `public/vendor/opencv/opencv.js` in the repo.
3. Change `IDW_OPENCV_SRC` in `resources/views/guest/show.blade.php` to
   `{{ asset('vendor/opencv/opencv.js') }}`.
This is optional (current CDN setup works fine), just a robustness improvement
whenever convenient — not blocking.

## 7. Auto-checkout scheduled job needs a real cron entry (task 23)

Task 23's "auto-checkout 30 minutes after checkout time" is now a real scheduled
command (`php artisan bookings:auto-checkout`), registered in
`routes/console.php` to run every 5 minutes via Laravel's scheduler. That
schedule only fires if your server actually runs the Laravel scheduler.

**Action needed on prod:** add this single cron entry (once, via cPanel's Cron
Jobs page or SSH crontab) if it isn't already there:
```
* * * * * cd /path-to-your-app && php artisan schedule:run >> /dev/null 2>&1
```
Without this cron entry, no auto-checkout will happen — bookings will just sit
in whatever status the guest last confirmed (or never confirmed) indefinitely.
This is likely already set up if any other scheduled feature relies on it;
if unsure, ask your host/dev whether `schedule:run` is already cronned.

Also fixed while diagnosing this: `GuestController@state()` had a stray block
that silently force-flipped a booking to `checked_out` the instant the clock
crossed checkout time, on *any* page view (guest or admin) — zero grace period,
and a completely separate path from the real "All Done" button. That's been
removed; the scheduled command above is now the only thing that auto-flips
checkout status, and only after the 30-minute grace period.

## 8. Parking rates need real numbers entered per property (tasks 20/25)

The auto-calculated parking charge mechanism (7 per-weekday rates per
property, summed across the guest's stay, admin-only visible, override field
available) is now built and live. It calculates correctly, but every
property's 7 rate fields currently default to blank/unset, which the
calculation treats as \$0 for that weekday — so right now every booking with
`parking_needed = true` will show a \$0.00 auto-calculated charge until real
numbers are entered.

**Action needed:** for each active property, go to its edit page in the admin
(Properties → [property] → "Parking rates" section, near the bottom) and fill
in the actual per-weekday parking rate. Existing bookings that already have
`parking_needed` set will need a save on that property's rates (or a save on
the individual booking) to pick up the new numbers — the charge recalculates
automatically whenever a property's rates are saved.

No code changes needed here — this is purely data entry once real rates are
available.

Status: living doc, update as items are resolved.
