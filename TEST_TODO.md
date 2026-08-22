# Checkout Test Plan

Manual test checklist for the checkout-related todos. Task 0 is already tested —
skipped here. Task 2 is a text-only UI change, no logic to test — skipped here.

---

## Todo 1: Checkout rules and behavior

For each item below: what it is, and what to do to confirm it's working.

### 1. Effective checkout time resolution
**What it is:** The checkout time actually enforced for a booking is the
property's standard `checkout_time` (default `10:00` in code, real per-property
value in DB), unless the guest's preference was submitted and admin-approved
(task 0), in which case the approved preference is used instead.

**How to test:**
- Open a booking with no preference set → confirm the guide/checkout page shows
  the property's standard checkout time.
- Submit a non-standard checkout time as a guest, leave it pending → confirm the
  standard time is still shown/enforced, not the requested one.
- Approve the pending request as admin → confirm the guide/checkout page now
  shows and enforces the approved time instead.

### 2. Guest-facing state machine on checkout day
**What it is:** `GuestController::state()` drives what the guest sees:
- Before effective checkout time, checkout day → `checkout_available` (guide +
  "Thanks for staying" button)
- After effective checkout time, checkout day, not yet confirmed → `checkout_locked`
  (still shows step wizard if steps exist)
- Day before checkout, after 6pm → `checkout_notice` (early warning)
- After the checkout day has fully passed → `post_checkout` ("Thank you for
  staying" page), regardless of whether the guest ever pressed the button

**How to test:**
- As a test booking, walk through each date/time boundary (day before at 6pm,
  checkout day before the time, checkout day after the time, day after checkout)
  and confirm the guest portal shows the expected state at each point.
- Confirm a booking that was never manually checked out still shows
  `post_checkout` once the checkout day has passed.

### 3. Manual checkout confirmation flow
**What it is:** Guest walks the checkout step wizard, and on the last step gets
a "Ready to check out?" modal (Not Yet / Yes, I'm Checked Out) before
`guest.confirm-checkout` fires, setting `status = checked_out` and
`checked_out_at = now()`.

**How to test:**
- Walk a test booking through checkout steps to the last step.
- Confirm the modal appears before the action is final.
- Click "Not Yet" → confirm nothing changes, guest stays on the wizard.
- Click "Yes, I'm Checked Out" → confirm status flips to `checked_out` and
  `checked_out_at` is set to the current time.

### 4. Auto-checkout scheduled command
**What it is:** `bookings:auto-checkout` runs every 5 minutes (see
`routes/console.php`) and auto-flips any booking not already `checked_out` to
`checked_out` once it's 30 minutes past the effective checkout time
(`isPastCheckoutGracePeriod`).

**How to test:**
- Set up a test booking with an effective checkout time in the past (more than
  30 min ago) that hasn't been manually checked out.
- Run `php artisan bookings:auto-checkout` manually (or wait for the scheduler).
- Confirm the booking flips to `checked_out` with `checked_out_at` set.
- Confirm a booking that's past checkout time but *within* the 30-minute grace
  window is NOT auto-checked-out yet.

### 5. Lock/unlock button gating vs. auto-checkout grace period
**What it is:** Door lock/unlock controls disappear exactly at the effective
checkout time (via `$lockWindowStates` in `category.blade.php`), which is
*before* the 30-minute grace period that auto-checkout uses. So there's an
intentional window where the guest is locked out of doors but the booking
status hasn't flipped to `checked_out` yet.

**How to test:**
- On a test booking, confirm the lock/unlock control is visible before the
  effective checkout time.
- Right at/after the effective checkout time, confirm the lock/unlock control
  disappears (or shows the "outside window" message) even though the booking
  status is still active.
- Confirm the control stays hidden through the grace period and after
  auto-checkout fires.

### 6. Late-checkout billing decoupling (task 26)
**What it is:** Late-checkout billing fields (`late_checkout_type`,
`late_checkout_hours`, `late_checkout_actual_time`) never read or write
`checked_out_at`, and are computed independently of the auto-checkout command.
Unauthorized late checkout bills hourly based on an admin-recorded actual
checkout time vs. the standard checkout time.

**How to test:**
- Set an admin-recorded `late_checkout_actual_time` on a booking, mark it
  unauthorized, and confirm the billed hours are calculated from
  standard-checkout-time → actual-time, not from `checked_out_at`.
- Let auto-checkout fire on the same booking (or simulate it) and confirm the
  late-checkout charge is unaffected — still reflects the admin-recorded time,
  not whenever auto-checkout happened to run.
- Confirm switching between authorized/unauthorized recalculates the charge
  using the correct rate field from the property.

---

## Todo 2: Checkout confirmation modal wording

Text-only UI change (added the missing "alerts the cleaning staff that it's OK
to come in" line to the modal). No behavioral logic changed — nothing to test
here beyond a visual check that the new copy renders correctly.
