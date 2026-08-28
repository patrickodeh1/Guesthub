#!/usr/bin/env python3
"""
Adds OTA-cancellation tracking + always-live field sync to the Channex/PMS
booking import path.

Run from your Guesthub project root:
    python3 apply_cancellation_sync.py

Then:
    docker compose exec app php artisan migrate
"""
import pathlib

root = pathlib.Path('.')

# ---------------------------------------------------------------------------
# 1. Migration: add cancelled_at to bookings
# ---------------------------------------------------------------------------
migration_path = root / 'database/migrations/2026_08_28_130500_add_cancelled_at_to_bookings.php'
if migration_path.exists():
    raise SystemExit(f'{migration_path} already exists -- aborting without changes')

migration_path.write_text('''<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('cancelled_at');
        });
    }
};
''')
print(f'Created {migration_path}')

# ---------------------------------------------------------------------------
# 2. Booking model: fillable + cast
# ---------------------------------------------------------------------------
model_path = root / 'app/Models/Booking.php'
content = model_path.read_text()

anchor1 = "'manually_checked_in', 'checked_in_at', 'checked_out_at', 'late_checkout_type', 'late_checkout_hours', 'late_checkout_actual_time', 'gps_overridden', 'status', 'notes', 'welcome_message', 'identity_confirmed_at',"
replacement1 = "'manually_checked_in', 'checked_in_at', 'checked_out_at', 'late_checkout_type', 'late_checkout_hours', 'late_checkout_actual_time', 'gps_overridden', 'status', 'cancelled_at', 'notes', 'welcome_message', 'identity_confirmed_at',"
if content.count(anchor1) != 1:
    raise SystemExit('Model anchor 1 (fillable) not found or not unique -- aborting without changes')
content = content.replace(anchor1, replacement1)

anchor2 = "            'archived_at' => 'datetime',"
replacement2 = "            'archived_at' => 'datetime',\n            'cancelled_at' => 'datetime',"
if content.count(anchor2) != 1:
    raise SystemExit('Model anchor 2 (casts) not found or not unique -- aborting without changes')
content = content.replace(anchor2, replacement2)

model_path.write_text(content)
print(f'Updated {model_path}')

# ---------------------------------------------------------------------------
# 3. BookingImportService: always sync fields + handle cancellation
# ---------------------------------------------------------------------------
service_path = root / 'app/Services/Pms/BookingImportService.php'
content = service_path.read_text()

anchor3 = """        } else {
            // Existing PMS-sourced booking: only refresh dates/property in
            // case the OTA reservation changed — never touch guest-entered
            // contact info, and never touch anything if this booking has
            // already progressed past 'pending' (admin/guest has started
            // working with it — a stale re-poll shouldn't reset progress).
            if ($booking->status === 'pending') {
                $booking->update($attributes);
            }
        }"""

replacement3 = """        } else {
            // Cancellation always wins, regardless of how far the booking
            // has progressed internally (approved/checked-in/etc.) -- a
            // guest who cancelled on the OTA needs staff to see that, not
            // have it silently swallowed because the workflow had moved on.
            // Once cancelled, the booking is terminal: further stale
            // revisions (retries, late polls) are ignored rather than
            // reviving it.
            if ($booking->status === 'cancelled') {
                Log::info('PMS booking import skipped: booking already cancelled', [
                    'booking_id' => $booking->id,
                    'external_booking_id' => $pmsBooking->externalBookingId,
                ]);
                return $booking->fresh();
            }

            if (in_array($pmsBooking->status, ['cancelled', 'declined'], true)) {
                $booking->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

                Log::info('PMS booking marked cancelled', [
                    'booking_id' => $booking->id,
                    'external_booking_id' => $pmsBooking->externalBookingId,
                ]);

                \\App\\Services\\GuestAlertService::send('pms_booking_cancelled', $booking);

                return $booking->fresh();
            }

            // Existing PMS-sourced booking, still active on the OTA: refresh
            // dates/property/name as soon as a webhook or poll picks up a
            // change -- no longer gated on 'pending' only, since a guest can
            // change dates on the OTA after Guesthub has already moved the
            // booking further along (approved, checked in, etc.), and staff
            // need to see that change immediately, not just on first import.
            // Guest-entered contact info is still never touched here (see
            // $attributes construction above).
            $booking->update($attributes);
        }"""

if content.count(anchor3) != 1:
    raise SystemExit('Service anchor 3 not found or not unique -- aborting without changes')
content = content.replace(anchor3, replacement3)
service_path.write_text(content)
print(f'Updated {service_path}')

# ---------------------------------------------------------------------------
# 4. Admin BookingController: allow 'cancelled' in the two status enums
# ---------------------------------------------------------------------------
controller_path = root / 'app/Http/Controllers/Admin/BookingController.php'
content = controller_path.read_text()

old_enum = "in:pending,pre_checkin_complete,awaiting_deposit,guest_approved,currently_hosting,checked_out"
new_enum = "in:pending,pre_checkin_complete,awaiting_deposit,guest_approved,currently_hosting,checked_out,cancelled"
count = content.count(old_enum)
if count == 0:
    raise SystemExit('Controller status enum string not found -- aborting without changes')
content = content.replace(old_enum, new_enum)
controller_path.write_text(content)
print(f'Updated {controller_path} ({count} occurrence(s))')

# ---------------------------------------------------------------------------
# 5. Badge CSS for the new status
# ---------------------------------------------------------------------------
css_path = root / 'resources/css/app.css'
content = css_path.read_text()

anchor5 = """    .badge-checked_out {
        @apply border-slate-200 bg-slate-100 text-slate-600;
    }"""
replacement5 = """    .badge-checked_out {
        @apply border-slate-200 bg-slate-100 text-slate-600;
    }

    .badge-cancelled {
        @apply border-red-200 bg-red-50 text-red-700;
    }"""
if content.count(anchor5) != 1:
    raise SystemExit('CSS anchor not found or not unique -- aborting without changes')
content = content.replace(anchor5, replacement5)
css_path.write_text(content)
print(f'Updated {css_path}')

print('\nAll edits applied successfully.')
print('Next: docker compose exec app php artisan migrate')
