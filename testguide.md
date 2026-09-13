# GuestHub Test Guide

This document has two parts:

1. **Setup** - everything needed to get the app running and configured.
2. **Feature test checklist** - what to click/enter for each requested feature and
   what you should see.

> Verification note: every item below was verified by inspecting the code and
> routes in this repo. The app could not be booted or migrated in the
> environment used to prepare this guide (no PHP/Composer), so the checklist is
> written so you can confirm each behavior in a running instance.

## Status summary

| # | Request | Status |
|---|---------|--------|
| 1 | Remove Ticketmaster / show free local events | PARTIAL - Ticketmaster fully removed; free-events source not built (returns empty list) |
| 2 | ID scanned on upload, expiry check, DOB + age | DONE |
| 3 | Contact page form instead of public email | DONE |
| 4 | Guests "This week": priority order, Today, full names, status/actions corners, times | DONE |
| 5 | Incidentals: guest sees total only, per-property required hold added | DONE (parking uses per-weekday rates, see Gaps) |
| 6 | Edit guest amounts: show breakdown, adjust each | DONE |
| 7 | Card field border/rounded, sliding steps, ZIP letters, button on ZIP | DONE |
| 8 | "Pay on <platform>" label + customizable instructions | DONE |
| 9 | Ledger: late checkout / early check-in visible in totals | DONE |
| 10 | Manual check-in/out time changes reflect on guest side | DONE |
| 11 | "Call Guest Services" button, bigger lime icon, no text overlap | DONE |
| 12 | License plate upload size limit | DONE (8 MB / 20 MB) |
| 13 | Checked-out guests only shown on their checkout day | DONE |
| 14 | Show check-in/out times on every guest card | DONE |
| 15 | Merge guest edit + view into one page with edit pencils | DONE |
| 16 | Stripe Link / autofill | PARTIAL - browser autofill wired; Stripe Link element not mounted |
| 17 | Email subject/body wording (no repeated app name) | DONE |
| 18 | "Checks in/out tomorrow" instead of "1 day" | DONE |
| 19 | Link guide categories across properties | DONE |
| 20 | Dashboard redesign | DONE |
| 21 | 20-second arrival disclaimer + type "agree" | DONE |
| 22 | Block guest access until unit marked ready | DONE |
| 23 | Host/business name + rental agreement e-sign + PDF | DONE |
| 24 | Full guest lifecycle with notifications | DONE |
| 25 | Guest cancellation handling (30-day window lock) | DONE |
| 26 | Auto check-in on door unlock, auto check-out on lock / checkout time | DONE |
| 27 | August lock reporting accuracy + battery thresholds | DONE |
| 28 | Registration greeting: first name + "we can't wait to see you!" | DONE |
| 29 | Requested check-in/out times next to the dates (not at the bottom) | DONE |
| 30 | Dashboard Today / Upcoming cards in priority order | DONE |
| 31 | Late-arrival (11pm+) smart-lights pop-up | DONE |
| 32 | Universal customizable conditional pop-up / step system | DONE |
| 33 | Incidentals: card auto-advances; Airbnb advances to "pending approval" | DONE |
| 34 | Only one notification for registration + ID | DONE |
| 35 | Pages no longer scroll left/right | DONE |
| 36 | Check-in details time = one hour before approved/default check-in | DONE |
| 37 | Auto-reject IDs (on-device, no billing): name mismatch, expired, underage, unreadable | DONE |
| 38 | No welcome-screen flash on pre-check-in reload | DONE |
| 39 | Rejected ID forces guest back to the ID step (not step 3) | DONE |
| 40 | Step 3 never shown while ID still needs upload/approval | DONE |
| 41 | Registration email sent once; no email on auto-rejected uploads | DONE |
| 42 | ID photo capture is auto-capture only (no manual shutter button) | DONE |
| 43 | On-device OCR text detection + specific rejection reasons | DONE |

---

## 1. Setup

### 1.1 Requirements

- PHP 8.2+ with `pdo_mysql`, `mbstring`, `gd`/`imagick`, `dom`, `zip`
- Composer 2
- Node 20.19+ or 22.12+ (Vite 7; Node 18 builds but warns)
- MySQL 8 (or MariaDB)
- A clean database

### 1.2 Install

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Create the database, then set the DB values in `.env`:

```env
DB_DATABASE=welcome_guide
DB_USERNAME=root
DB_PASSWORD=
```

Run the schema and storage link:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Then open `http://127.0.0.1:8000`.

### 1.3 Required / recommended `.env` values

Add anything missing from the list below to `.env`.

```env
# Used for all admin/guest date+time display. Server stores UTC; this controls
# what the admin sees. Update the README/env docs to keep it around.
APP_DISPLAY_TIMEZONE=America/New_York

# Stripe (card + deposit payments)
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Government-ID scanning (passports via Vision OCR; state IDs via on-device barcode)
ID_SCAN_PROVIDER=google_vision
GOOGLE_VISION_API_KEY=AIza...

# Telnyx SMS notifications (replaces Twilio)
TELNYX_API_KEY=
TELNYX_PUBLIC_KEY=
TELNYX_FROM_NUMBER=
TELNYX_MESSAGING_PROFILE_ID=
TELNYX_ADMIN_NOTIFY_NUMBER=

# Mail (SMTP; used for guest/staff lifecycle alert emails)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=you@example.com
MAIL_PASSWORD=app-password
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Queues, cache and sessions default to `database`, which is fine for local
testing once migrations have run.

### 1.4 Scheduled jobs

The checkout reminder runs daily at 18:00 and requires the scheduler:

```bash
php artisan schedule:work      # or run `* * * * * php artisan schedule:run` in prod
```

Checkout reminder command (can also be run manually):

```bash
php artisan bookings:send-checkout-reminders
```

### 1.5 Settings to configure in the admin UI

Go to **Admin > Settings** and set at minimum:

- Site/business name and logo
- `processing_fee_percent` (adds a % fee to all guest charges)
- `default_deposit_cap_cents` (fallback cap when a property has none)
- Contact phone (used by the "Call Guest Services" button)
- Notification settings (**Settings > Notifications**) for who receives each
  lifecycle alert over SMS/email

Per property (**Properties > edit**):

- `required_incidentals_hold_amount` (default hold added to the guest total)
- Per-weekday parking rates
- Early check-in and late checkout rates
- Smart locks (Seam)

Host name (**Users > create/edit**, owner role):

- `Host / business name` - inserted into the rental agreement and email sender name

---

## 2. Feature test checklist

### 2.1 Local events (PARTIAL)

Current state: Ticketmaster is gone, but no replacement source is wired up, so
the "Local Events" category renders an empty list.

Test:
1. Create a category with action `local_events`, assign it to a property.
2. Open the guest guide and view that category.
3. **Expected now:** empty events section (no Ticketmaster results), no errors.

What still needs to be done: pick/introduce a free-events source (e.g. a
curated per-city list, CitySpark, or a host-managed table) and populate
`$localEvents` in `GuestController::category()` / `moreEvents()`. The intended
content is free community events (festivals, fireworks, etc.), never ticketed
concerts.

### 2.2 ID verification (`2`)

Prep: create a booking and open the guest link; complete steps until the
Identity step. Add `GOOGLE_VISION_API_KEY` for passport scanning.

Test A - US state ID (front + back):
1. Upload the front photo, then the back photo.
2. **Expected:** the browser decodes the PDF417 barcode on the back (the Next
   button is briefly disabled while decoding), the app stores DOB, expiry and
   document number, and shows age on the admin side.

Test B - international passport:
1. Choose passport, upload the photo page.
2. **Expected:** OCR reads the MRZ and captures DOB/expiry (no back photo
   required).

Test C - expired ID:
1. Upload a photo of an expired document.
2. **Expected:** auto-rejected (no admin review): front/back cleared, guest asked
   to re-upload, status returns to pending, `photo_id_expired` alert sent.

Test D - age/DOB on admin:
1. Open the booking in **Admin > Guests**.
2. **Expected:** "Scanned ID details" shows DOB, age, expiry, scan status,
   name on ID and document number; under-18/expired/auto-reject warnings appear
   when applicable.

Test E - invalid IDs (see 2.30 for full detail): name on the ID not matching
the reservation, an under-18 holder, and a non-genuine document are all
rejected automatically with no admin review.

### 2.3 Contact page (`3`)

1. Visit `/contact`.
2. **Expected:** a Name/Email/Phone/Subject/Message form. No mailto link or
   public email address is shown anywhere on the page.
3. Submit the form.
4. **Expected:** success message; a `contact_messages` row is created; if
   `contact_email` is set in settings an admin notification email is sent, and
   an SMS goes to `TELNYX_ADMIN_NOTIFY_NUMBER` if configured.

### 2.4 Guests page - This Week / Today (`4`, `13`, `14`, `18`)

Open **Admin > Guests**.

1. **Today section:** guests checking in today with incomplete steps appear
   first, then other today check-ins, then today's checkouts.
2. **This Week:** remaining operational window, same ordering logic.
3. **Names:** full guest name wraps; it is not truncated.
4. **Card layout:** status badge top-right; small actions button bottom-right;
   guest info fills the rest.
5. **Labels:** a not-yet-checked-in guest always reads "Checks in today /
   tomorrow / in N days". Only after marking checked in does it read "Checks out
   ...". Wording uses "tomorrow" (never "1 day").
6. **Times:** check-in time shows for today's arrivals, check-out time for
   today's departures, both for everyone else.
7. **Checked-out guests:** only appear in Today on their checkout day; after that
   day they leave the current week (archive them, or they drop off once
   `checked_out_at` is set).

### 2.5 Incidentals / amounts (`5`, `6`, `9`)

Guest side:
1. Complete registration and reach the incidentals payment step.
2. **Expected:** the guest sees only the total amount (no line-item breakdown).
   The total is `parking + incidentals hold + early check-in (if charged)`,
   capped, then the processing fee added. The per-property
   `required_incidentals_hold_amount` is included automatically.

Admin side:
1. Open a booking and find the **Ledger** card.
2. **Expected:** hold deductions (late checkout, early check-in from hold),
   net charge to guest, and estimated refund, with a warning when deductions
   exceed the hold.
3. Edit each line in **Guest Details** using the pencil icons:
   parking override, incidentals hold override, early check-in window/override/
   billing, late checkout type/hours/actual time/override.
4. Change a value and save.
5. **Expected:** the guest's amount updates to match once you re-send/reload
   their step.

### 2.6 Card field (`7`, `16`)

Requires Stripe keys. Reach the guest payment step and choose "Pay here".

1. **Border:** card inputs have a visible rounded border (not borderless).
2. Enter a card number. **Expected:** once complete, a summary row shows the
   brand + masked number and the form slides to the expiry field.
3. Enter expiry. **Expected:** slides to CVC.
4. Enter CVC. **Expected:** slides to ZIP/postal.
5. **ZIP field:** `type="text"`, accepts letters (international postal codes),
   not a numbers-only box.
6. **Pay button:** stays disabled until the postal field has 3+ characters, then
   highlights.
7. **Autofill:** browser autofill is wired via `autocomplete` attributes
   (`cc-number`, `cc-exp`, `cc-csc`, `postal-code`) on the Stripe iframes.
   Note: Stripe's Link element is not mounted (see Gaps).

### 2.7 Pay on booking platform (`8`)

1. Add a booking with a `booking_platform` (e.g. VRBO).
2. Guest chooses the "pay on platform" option.
3. **Expected:** the button reads "Pay on VRBO" (or the saved platform name),
   and the instructions message is the host-customizable `ota_instructions`
   with `[[platform]]` replaced by the platform name.

### 2.8 Time changes reflect on guest side (`10`)

1. In a booking, approve or change check-in/check-out time preferences
   (`admin.guests.time-preference.update`).
2. Reload the guest link.
3. **Expected:** the guest sees the updated approved times.

### 2.9 Call Guest Services button (`11`)

1. Open any guest page.
2. **Expected:** a lime-green floating call button, bottom-right. On hover/tap
   it expands and shows "Call Guest Services"; the icon is large and the label
   does not overlap it. It links to the configured contact phone.

### 2.10 License plate upload (`12`)

1. In the vehicle/parking step, upload a license plate photo up to 8 MB
   (guest) / 20 MB (final submit).
2. **Expected:** the upload succeeds; no "must not be greater than 5120
   kilobytes" error.

### 2.11 Merge edit + view (`15`)

1. Open a guest. There is no separate edit page.
2. **Expected:** all information is on the single show page; editable fields
   have a pencil icon that unlocks the field inline. Saving stays on the page.

### 2.12 Email wording (`17`)

Trigger any lifecycle alert (e.g. check-in completed) with email enabled.

1. **Expected subject:** `<Guest Name> Check-in completed`
2. **Expected body:** starts with "Check-in has been completed and <Guest Name>
   is successfully checked into <Property>." - no repeated "Guest Hub" prefix.
3. SMS still includes the sender prefix (SMS has no sender display name).

### 2.13 Category linking across properties (`19`)

1. In a property's guide, click **Customize locally** on a category page to
   break the link, or use **Copy guide** to clone a whole set to another
   property.
2. Edit the shared page once and confirm linked properties update.
3. **Expected:** per-user, per-property linking (not a global setting); a
   linked property follows the source page, and customizing a copy unlinks it.

### 2.14 Dashboard (`20`)

Open **Admin > Dashboard**.

1. **Expected:** compact greeting-only hero, one-tap **Add Guest** button, smart
   lock status line.
2. **Expected:** "Today's Overview" figures are clickable and scroll to the
   guests section. The old cards (properties, active guests, pending IDs, etc.)
   are gone.
3. **Guests by property:** only today's arrivals/checkouts, current stays, and
   upcoming arrivals, ordered arrivals first. Property thumbnail in the header.
4. **Timezone:** greeting, check-in and check-out times all match
   `APP_DISPLAY_TIMEZONE` (no 12-hour drift; no "today" for a tomorrow arrival).

### 2.15 Check-in disclaimer (`21`)

1. On the day of arrival, open the guest link before the address is revealed.
2. **Expected:** a disclaimer stays for 20 seconds; the "agree" input and the
   Continue button stay disabled, then enable. Typing "agree" is required to
   continue. The agreement is stored in `checkin_disclaimer_agreed_at`.

### 2.16 Block until unit ready (`22`)

1. Complete pre-check-in fully, then open the guest link on arrival day.
2. **Expected:** a "Your unit isn't quite ready yet" screen; it auto-refreshes.
3. In admin, click the action that marks the unit ready / approves check-in
   (`admin.guests.approve-checkin`).
4. **Expected:** the guest is released to the check-in screen, and a
   `checkin_ready` alert is sent.

### 2.17 Host name + rental agreement (`23`)

1. As owner, create/edit a user and fill **Host / business name**.
2. In the guest flow, open the rental agreement link and complete signing
   (checkbox + typed legal name).
3. **Expected:** the typed name must match the government ID name; a mismatch
   shows an inline error and blocks submit. On success the booking stores
   `contract_signed_name`, IP, user agent, device id, version and timestamp.
4. Download the PDF (`guest.rental-agreement.pdf`).
5. **Expected:** the PDF includes the host name, site name, guest name, dates,
   reservation ID, signed name and signature metadata.

### 2.18 Guest lifecycle (`24`)

Walk a new booking through:

1. Guest added -> fills their part (email/phone, times, parking, ID, payment
   method) -> "That's it for now".
2. Notifications to admin; admin approves ID, background, times, parking and
   adjusts incidentals if needed.
3. Guest notification with amount; guest pays incidentals (card or platform).
4. Admin notified; booking marked confirmed.
5. Day of arrival: guest notified when check-in is available (after the
   cleaner/unit-ready mark).
6. Check-in -> guide unlocked -> admin notified.
7. Day before checkout: checkout reminder sent (also via the scheduled command).
8. Checkout -> both guest and admin notified.

### 2.19 Cancellations (`25`)

1. Cancel a reservation from the guest side.
2. **Expected:** if more than 30 days before arrival, it is marked cancelled by
   guest and archived; if within 30 days, it is not archived, a cancellation fee
   applies, and all edit/approve/GPS functions are locked (`guardNotCancelled`).

### 2.20 Auto check-in / auto check-out (`26`)

Auto check-in on unlock:
1. On arrival day, get a booking into the check-in wizard / guide with a lock
   configured, and stand at the property.
2. Tap the lock button to unlock.
3. **Expected:** once the poll confirms the door is unlocked, the stay is
   marked checked in automatically (no "I'm Checked In!" press) and the page
   reloads into the guide. A `checkin_completed` alert fires.

Auto check-out on lock:
1. On checkout day, open the guest link (checkout available screen) and lock
   the door.
2. **Expected:** once confirmed locked, the stay is marked checked out
   automatically and the page reloads to the thank-you screen.

Auto check-out when checkout time passes:
1. Leave a checked-in guest on the checkout page past their checkout time with
   the door locked.
2. **Expected:** the page's 5-second `id-status` poll detects it and reloads to
   `post_checkout`; the scheduled `bookings:auto-checkout` (30-minute grace)
   also closes it even without a page open.

Notes / safety: unlocking only auto-checks-in when the guest is not yet checked
in; locking only auto-checks-out on checkout day. A guest locking the door
mid-stay is not checked out. If a property has locks, the time-based auto-close
requires the door to be reported locked (properties without locks use time
alone). Tell me if you'd rather auto-check-out purely on time with no lock
condition.

### 2.21 Lock reporting (`27`)

1. Open **Admin > Dashboard** and look at the Smart Locks line.
2. **Expected:** the Locked/Unlocked state comes from a **live** Seam lookup (so
   it matches the actual door, including August locks) rather than the
   webhook-cached value; the cached row is updated to match.
3. Battery shows as "Battery N%": grey above 50%, amber 20-50%, red below 20%.
   Battery values that Seam already reports as a percentage are no longer
   multiplied by 100.
4. Guest-side confirmation polls (`guest.lock-status`) also use the live Seam
   state, cached for 2 seconds so polling doesn't hammer the API.

### 2.22 Registration greeting (`28`)

1. Open a guest's first registration page.
2. **Expected:** heading reads "<First name>, we can't wait to see you!" (first
   name only, then comma).

### 2.23 Requested times near the dates (`29`)

1. Open a booking in admin with a requested check-in/out time.
2. **Expected:** "Requested check-in" / "Requested check-out" appear in the
   header right under the stay dates, with approve/reject buttons and an
   Approved/Declined/Needs-review badge. They are no longer at the bottom of
   the Guest Details card.

### 2.24 Dashboard Today / Upcoming (`30`)

1. Open **Admin > Dashboard**.
2. **Today card:** every guest checking in or checking out today. Order:
   pending check-ins first, then approved/checked-in guests, then check-outs.
   Each row shows property (with a small hero thumbnail) + status on the top
   line, then guest name, then the arrival/check-out line.
3. **Upcoming card:** the same priority order for future arrivals.
4. **Expected:** greeting is compact (greeting only), one-tap **Add Guest**,
   clickable "Today's Overview" figures, and correct local times.

### 2.25 Conditional guest notices (`31`, `32`)

The late-arrival smart-lights notice is seeded and active for all properties, so
it works immediately after migrating.

Test the seeded notice:
1. Set a booking's check-in date to today and make sure it is not yet checked
   in.
2. Open the guest link at/after 11:00 PM local time (or temporarily edit the
   notice window to cover "now").
3. **Expected:** a pop-up about the dim nighttime smart lights appears; tapping
   "Got it" dismisses it and it does not reappear for that booking.

Manage notices:
1. Go to **Admin > Guest Notices** (sidebar, under Guests).
2. Create/edit a notice: title, message, property (or all), show-as
   (pop-up or check-in/out step), phase, day scope (any / arrival day /
   check-out day), optional time window (supports windows across midnight,
   e.g. 23:00-04:00), optional parking condition, and "show once per booking".
3. **Expected:** matching notices appear as modals on the relevant guest screen,
   and "step" notices are injected into the check-in or check-out wizard as
   their own step. Set active/inactive to toggle.

### 2.26 Incidentals payment: card vs platform (`33`)

Card:
1. Reach the incidentals screen with a Stripe test key configured.
2. Pay with a test card.
3. **Expected:** on success the deposit is auto-verified server-side
   (`deposit_verified_at` set), the page briefly shows "Payment received", then
   reloads and advances to the next step — no admin action needed.

Pay on the booking platform:
1. Reach the incidentals screen and tap **Pay on <platform>**.
2. **Expected:** the choice is saved (`platform_payment_selected_at`), the page
   reloads to a **"Pending approval"** screen (no amount, no payment buttons),
   and the platform instructions are shown. Revisiting the registration link
   keeps showing "Pending approval" — the payment amount never returns.
3. The admin can later mark the deposit verified to advance the guest.

> Because there is no webhook for off-platform payments, the guest is moved
> forward on their own selection; the admin still verifies receipt.

### 2.27 One notification for registration + ID (`34`)

1. Complete registration + ID upload in one step for a new booking.
2. **Expected:** exactly one alert fires (`registration_received`) — not a
   separate ID-upload alert. The ID-upload alert only fires on a genuine
   re-upload after a decline.

### 2.28 No horizontal scrolling (`35`)

1. Open any guest or admin page and scroll up and down at various widths.
2. **Expected:** the page does not shift or scroll left/right. The base styles
   now use `overflow-x: clip` with `width/max-width: 100%` instead of
   `max-width: 100vw` (which included the scrollbar and caused the drift).

### 2.29 Check-in details time (`36`)

1. Approve a guest's requested 1:00 PM check-in, then view their guest portal.
2. **Expected:** the "Check In Details Available" banner reads 12:00 PM
   (check-in minus one hour). If no time is approved, it defaults to the
   property's standard check-in time minus one hour.

### 2.30 Automatic ID rejection (`37`)

The check runs entirely on-device (no cloud provider, no billing): US state IDs
via the PDF417 barcode decoded in the browser, passports via Tesseract.js OCR of
the MRZ sent as text.

What gets auto-rejected (only two things):
1. **Name mismatch** — the name read from the ID doesn't match the reservation
   name (order-insensitive and middle-name tolerant, so "James Smith" matches
   "SMITH JOHN JAMES", but "Jim Smith" does not match "SMITH JAMES").
2. **Expired** — the ID's expiry date is in the past.

Everything else is left for the host to verify manually (photo, age, etc.).
**Under 18** and **unreadable** (no name/dates could be read) are recorded on
the booking and shown under "Scanned ID details", but they do **not** block the
guest — so an ID the browser "read" is never falsely rejected as "couldn't read".

Test:
1. Set a booking's guest name to "James Smith".
2. Upload an ID whose name reads "Jim Smith" → auto-rejected (`name_mismatch`).
3. Upload the same ID after changing the reservation name to match → passes.
4. Upload an expired ID → auto-rejected (`expired`).
5. Upload an unreadable/random image → **not** blocked; passes to the admin for
   manual verification (status `unreadable` recorded).

Expected in a rejection: front and back cleared, a decline reason set,
`photo_id_received=false`, status back to `pending`, and the guest is sent back
to the ID step with the reason and can re-upload (no admin action).

> The name check uses the machine-readable data (AAMVA barcode for state IDs,
> MRZ for passports). If the reservation name was entered incorrectly, the
> guest will be rejected — correct the booking name first. The host still
> verifies the ID photo (and age) manually — the automated check only confirms
> the name matches and the document hasn't expired. No Google Cloud / billing
> is required.


### 2.31 Pre-check-in reload + rejected ID step (`38`, `39`)

No flash on reload:
1. Advance the pre-check-in wizard to step 2 (ID), then reload the page.
2. **Expected:** the ID step renders immediately — the welcome step 0 does not
   flash first. The server picks the starting step from the booking's progress
   and an inline script (run right after the steps, before paint) restores any
   saved step from sessionStorage.

Rejected ID keeps you on the ID step:
1. From step 2, upload an ID that should be rejected (e.g. name mismatch).
2. **Expected:** the AJAX submit returns the rejection reason, the captured
   photos are cleared, and the guest is forced back to step 2 to re-upload —
   the wizard does **not** advance to the Smart Lock step (step 3). A later
   reload also lands on step 2, not the welcome page.

### 2.32 Step 3 lock-out + notification emails (`40`, `41`)

Step 3 while the ID still needs attention:
1. Submit a rejected/incomplete ID, then (for example) open the page fresh or
   revisit the link.
2. **Expected:** the wizard is clamped to the ID step (step 2) with the
   re-upload banner; it never restores or stays on the "get the August app"
   step (step 3) while the ID is not fully approved. The clamp applies on the
   initial render, in the pre-paint script, and in the later restore.

Notification emails:
1. First successful registration + ID upload → one **Registration completed**
   email, and `registration_notified_at` is stamped.
2. Re-upload after an admin decline → an **ID upload** email instead (not
   another registration email).
3. Auto-rejected upload (name mismatch / expired / underage / unreadable /
   verification failed) → **no email at all**; the guest just sees the reason
   in the portal and is sent back to the ID step.
4. Re-upload of a rejected ID → still no registration email (the flag is
   durable), only the ID-upload notification.

### 2.33 ID capture is auto-capture only (`42`)

1. Open the ID step and start the camera for the front (and back).
2. **Expected:** there is **no shutter/capture button** — the camera captures
   automatically once the ID is steady (OpenCV detects a stable, sharp,
   well-framed ID). If OpenCV can't load, it still auto-captures after a short
   hold ("Hold steady — capturing automatically…"), so the guest is never
   shown a manual capture button.
3. After capture, the normal preview + Retake controls appear.

### 2.34 On-device OCR + specific rejection reasons (`43`)

On-device text detection (feedback):
1. Capture the ID photo (any type).
2. **Expected:** a status line appears under the preview — "We read the details
   on your ID ✓" when text is found. The raw OCR text (Tesseract.js, run in the
   browser) is sent to the server as `id_ocr_text` on submit.

No cloud provider needed:
1. Submit a passport (or any ID) with no Google Vision configured.
2. **Expected:** the server verifies name + expiry from the on-device barcode
   (state IDs) or OCR text (passports). A valid matching ID passes; a
   name-mismatch/expired ID is rejected specifically. No billing required.

Specific rejection reasons (only these two reject):
- name mismatch → quotes the ID's name and the reservation name.
- expired → quotes the detected expiry date.

Recorded but **not** rejected (host verifies manually): under-18 (age) and
unreadable (no name/dates readable). The browser "We read the details ✓" note
now matches reality — a read-but-unparseable ID is handed to the host instead
of being rejected as "couldn't read".

---

## 3. Known gaps / follow-ups

1. **Local events source not implemented.** Ticketmaster was removed as
   requested, but the free community-events feed is still empty
   (`GuestController::category()` / `moreEvents()` return no events). This is
   the main outstanding item.
2. **Per-property parking field.** The incidentals hold is now per-property
   (`required_incidentals_hold_amount`), but parking is configured as
   per-weekday rates rather than a single per-property required amount. If you
   want a single "required parking" field too, it needs to be added.
3. **Stripe Link.** Browser autofill is enabled via `autocomplete` attributes
   on the Stripe iframes, but a Stripe `linkAuthenticationElement` /
   Payment Element is not mounted, so Stripe Link's one-tap wallet flow is not
   explicitly enabled.
4. **Auto-checkout safety condition.** The lock-based auto-close requires the
   door to be reported locked when the property has a lock (so a guest who is
   still inside isn't checked out). The time-based sweep already closes stays
   30 minutes past checkout regardless. If you want time-only auto-checkout with
   no lock condition, say so and it's a one-line change.

## 4. Commands reference

```bash
php artisan migrate                # applies 2026_09_12_* (guest_notices) + earlier
php artisan bookings:auto-checkout # time-based auto checkout (also scheduled every 5 min)
php artisan bookings:send-checkout-reminders
php artisan schedule:work
npm run build
```
