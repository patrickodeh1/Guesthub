# Setup & Testing — `feature/channex-terms-stripe`

This covers everything added on this branch: Channex PMS sync, terms &
rental contract, Stripe payments (deposit/parking/early check-in/late
checkout/incidentals), the door-lock security fix, the Telnyx SMS switch,
and the automated test suite.

## 1. Pull and install

```bash
git checkout feature/channex-terms-stripe
git pull
composer install
```

`composer install` is required — `stripe/stripe-php` was added to
`composer.json` this branch but never installed in this sandbox.

## 2. Run the new migrations

```bash
docker compose exec app php artisan migrate
```

All migrations on this branch are additive/nullable — nothing here touches
or breaks existing properties or bookings. New columns just sit unused
until you fill them in.

## 3. Environment variables to add to your real `.env`

None of these are required to just run the app — everything degrades
gracefully (Stripe UI simply doesn't show, Channex sync no-ops, etc.) if
left unset. Set only what you're ready to test.

### Stripe (payments)
```
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```
Get test-mode keys from the Stripe dashboard. Nothing charges for real
until you switch to live keys.

### Channex (PMS sync)
```
CHANNEX_API_KEY=...
CHANNEX_BASE_URL=https://app.channex.io/api/v1
CHANNEX_WEBHOOK_SECRET=...
PMS_PROVIDER=channex
PMS_POLL_INTERVAL_MINUTES=15
```
**Heads up:** the exact request/response shape in `ChannexProvider` was
written against their public docs, not verified against a live account —
flagged inline in the code wherever an assumption is made. Expect to need
small adjustments once you have real API access and can see actual
payloads.

Webhook endpoint to give Channex (or point ngrok at):
```
POST https://your-domain/webhooks/channex
```

### Telnyx (SMS, replacing Twilio)
```
TELNYX_API_KEY=...
TELNYX_FROM_NUMBER=+1...
TELNYX_MESSAGING_PROFILE_ID=...
TELNYX_ADMIN_NOTIFY_NUMBER=+1...
```
Old `TWILIO_*` vars can stay in `.env` untouched — the code no longer reads
them, they're just inert.

### Email (dropping Resend, using SMTP)
No third-party service required. cPanel's own mail service works, or for
local testing, Gmail SMTP:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=youraddress@gmail.com
MAIL_PASSWORD=your-16-char-app-password   # myaccount.google.com/apppasswords — regular Gmail password won't work
MAIL_FROM_ADDRESS=youraddress@gmail.com
```
Swap `MAIL_HOST`/`MAIL_PORT`/`MAIL_USERNAME`/`MAIL_PASSWORD` for cPanel's
real SMTP details when ready — same code path either way.

## 4. Run the automated tests

```bash
docker compose exec app php artisan test tests/Feature/PreCheckinChargeTest.php tests/Feature/PayByCcGatingTest.php
```

Runs against an isolated in-memory SQLite database — doesn't touch your
real MySQL data. Should complete in a few seconds.

Covers:
- The combined pre-checkin charge formula (parking + incidentals + early
  check-in, capped, + processing fee)
- The double-charge-prevention logic (`incidentals_billed_cents`)
- The three-tier early check-in windows and half-hour-block late checkout
  billing
- `pay_by_cc` gating on every guest-facing charge endpoint

**Not covered by automated tests yet** (manual testing needed):
- Channex sync (needs real API credentials)
- The actual Stripe payment flow end-to-end (needs real/test Stripe keys
  and a browser, since it's an embedded Payment Element)
- The door-lock GPS/stay-window gate (needs a real device with location
  services, or manually POSTing coordinates)
- Telnyx SMS sending (needs real API key)

## 5. Manual testing checklist

- **Property setup:** open a property's edit page, confirm the new "Deposit
  cap", "Channel manager" (Channex ID), and the three early check-in / two
  late-checkout-per-30min rate fields all save correctly.
- **Booking form:** confirm the "Guest pays by credit card on our site"
  checkbox and the three-option early check-in window select both save.
- **Guest payment flow:** with Stripe test keys set and `pay_by_cc` checked
  on a booking, walk through precheckin as that guest — confirm the
  embedded card form appears after ID upload, uses Stripe's test card
  `4242 4242 4242 4242`, and completes without leaving your domain or
  showing Stripe branding.
- **Alt-instructions path:** same booking with `pay_by_cc` unchecked —
  confirm zero Stripe UI appears anywhere in the guest flow, and the
  existing "pay via the platform" message shows instead.
- **Admin Payments page** (`/admin/payments`): confirm charges show with
  the new clearer type labels and the breakdown description.
- **Door lock:** attempt unlock/lock from a browser with location
  permission denied — should be blocked with a clear message. From outside
  the stay dates — should also be blocked.

## 6. About the merge conflict you hit

If you saw a conflict on `phpunit.xml` or `config/database.php` while
merging — this branch and `fix/notifications-and-misc-todos` (the one
you've asked not to merge) each independently added their own versions of
these two files for test infrastructure. If you ever do merge them, keep
**this branch's** version of both files; they're equivalent in purpose, no
functionality is lost either way.
