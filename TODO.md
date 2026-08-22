# Working TODO List

Tracks the 10 items from the client's active todo list. Each item gets its plan
restated and confirmed before implementation, per the process note in TASKS.md.

---

## 0. Guest check-in/check-out time preferences should not auto-override system time

**Observation (client):** checkout and checkin time preference should not override
the system's check-in and checkout time as it should be reviewed by admin, not just
accepted, since a charge might be required.

**Confirmed via code review:** `checkin_time_preference` / `checkout_time_preference`
on `bookings` flow directly into `effectiveCheckinTime()` / `effectiveCheckoutTime()`
with no admin gate, and those drive address release, door-lock timing, and
auto-checkout. Task 26 added billing fields (`early_checkin_tier`,
`late_checkout_type`, `late_checkout_hours`, rate fields on `properties`) but nothing
connects them to the guest's raw preference — a guest can pick a non-standard time
and it's silently honored with zero charge and zero review.

Nothing in TASKS.md or CLIENT_TASKS.md states or implies this override-without-review
behavior is intentional. No existing task covers this gap.

**Plan (pending final confirmation, implementing on this branch first):**

1. Add `properties.checkin_time` — nullable, no DB default, mirrors `checkout_time`.
   Existing rows stay `NULL`; zero effect on prod data.
2. Do not touch `properties.checkout_time`'s existing column/default — no migration,
   no backfill.
3. Fix code-level fallback constants only (not DB defaults):
   - `effectiveCheckinTime()` falls back to `property->checkin_time`, default
     `'16:00'`, replacing the global `Setting::getValue('default_checkin_time', ...)`
     lookup.
   - `effectiveCheckoutTime()` fallback becomes `'10:00'` in code (never reached for
     existing properties, which already have a real stored value).
4. Add `bookings.checkin_time_status` / `checkout_time_status`
   (`pending` / `approved` / `denied`, nullable) **with a backfill in the same
   migration**: any existing booking that already has a preference set is backfilled
   to `approved` so no currently-staying or already-booked guest's effective time
   changes on deploy.
5. Gate `effectiveCheckinTime()` / `effectiveCheckoutTime()`: only honor the guest
   preference when status is `approved`; otherwise fall back to the property standard
   time.
6. `GuestController` submission: non-standard time request sets status to `pending`.
   A request matching the standard time stays `NULL` and applies immediately — no
   review needed. Resubmitting the same value must not reset an existing
   approved/denied decision.
7. Add `checkin_time` to the property edit form (admin), mirroring `checkout_time`.
8. Add an admin approve/deny action on the booking detail page for pending time
   preferences, positioned to feed naturally into the task 26 billing fields. No
   automatic guest notification — manual only, per client instruction.

**Status:** Implemented on this branch (`fix/task-0-checkin-checkout-approval`), not yet reviewed/tested by client.

- `database/migrations/2026_08_22_120000_add_checkin_time_to_properties.php` — new
  nullable `properties.checkin_time`, no DB default.
- `database/migrations/2026_08_22_120100_add_time_preference_status_to_bookings.php`
  — new `bookings.checkin_time_status` / `checkout_time_status`, with backfill to
  `approved` for any existing preference.
- `app/Models/Property.php` — `checkin_time` added to fillable.
- `app/Models/Booking.php` — `standardCheckinTime()`/`standardCheckoutTime()`
  (+ formatted variants) added; `effectiveCheckinTime()`/`effectiveCheckoutTime()`
  gated behind `*_time_status === 'approved'`.
- `app/Http/Controllers/GuestController.php` — sets `pending`/`null` status on
  submission, preserves existing decision on resubmission of the same value.
- `app/Http/Controllers/Admin/PropertyController.php` + `routes/web.php` —
  `updateCheckinTime` action mirroring `updateCheckoutTime`.
- `app/Http/Controllers/Admin/BookingController.php` + `routes/web.php` —
  `updateTimePreferenceStatus` approve/deny action, no auto-notification.
- `resources/views/admin/properties/form.blade.php` — check-in time mini-form.
- `resources/views/admin/bookings/show.blade.php` — status shown inline;
  "Time Preference Review" card appears only when something is pending.

**Not yet done:** wiring the approval action to auto-populate the task 26 billing
fields (`early_checkin_tier`, `late_checkout_type`, etc.) — admin still sets those
separately after approving, per plan. No `php artisan migrate` or automated tests
have been run in this sandbox (no PHP runtime available here) — please run the
migration and exercise the flow locally/staging before merging.

---

## 1-9. (pending — to be filled in as we work through the list)
