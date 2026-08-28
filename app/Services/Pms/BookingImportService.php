<?php

namespace App\Services\Pms;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Turns a normalized PmsBooking into a Guesthub Booking row.
 *
 * Deliberately narrow: only ever creates/updates bookings that are
 * themselves PMS-sourced (source = the provider's name + matching
 * channex_booking_id). A manually-created booking is never touched by this,
 * even if names/dates happen to look similar — there's no fuzzy matching
 * here on purpose, to avoid ever silently overwriting an admin's manual
 * entry.
 */
class BookingImportService
{
    public function __construct(private readonly string $providerName = 'channex')
    {
    }

    /**
     * @return Booking|null null if the booking's property isn't mapped yet
     *                       (admin hasn't set channex_property_id) — logged,
     *                       not thrown, so one unmapped property doesn't
     *                       break the rest of the sync batch.
     */
    public function import(PmsBooking $pmsBooking): ?Booking
    {
        $property = Property::query()
            ->where('channex_property_id', $pmsBooking->externalPropertyId)
            ->first();

        if (! $property) {
            Log::warning('PMS booking import skipped: no property mapped for external property id', [
                'external_property_id' => $pmsBooking->externalPropertyId,
                'external_booking_id' => $pmsBooking->externalBookingId,
            ]);
            return null;
        }

        // check_in_date/check_out_date are NOT NULL on bookings. Some PMS
        // revisions (e.g. cancelled Channex test/staging bookings) arrive
        // with no arrival/departure dates at all -- there is nothing useful
        // to store yet, so skip rather than violate the DB constraint.
        if (! $pmsBooking->checkInDate || ! $pmsBooking->checkOutDate) {
            Log::warning('PMS booking import skipped: missing check-in/check-out date', [
                'external_property_id' => $pmsBooking->externalPropertyId,
                'external_booking_id' => $pmsBooking->externalBookingId,
                'status' => $pmsBooking->status,
            ]);
            return null;
        }

        $booking = Booking::query()
            ->where('source', $this->providerName)
            ->where('channex_booking_id', $pmsBooking->externalBookingId)
            ->first();

        $attributes = [
            'source' => $this->providerName,
            'channex_booking_id' => $pmsBooking->externalBookingId,
            'property_id' => $property->id,
            'check_in_date' => $pmsBooking->checkInDate ?: null,
            'check_out_date' => $pmsBooking->checkOutDate ?: null,
        ];

        // Guest name is the one field we always trust from the PMS — it's
        // needed to even show a placeholder booking in the admin list.
        // Contact info (email/phone) is treated as prefill only: the guest
        // still self-reports at precheckin regardless of source (Airbnb
        // proxy numbers etc. shouldn't be trusted as the final record), so
        // we only set it on first import and never overwrite what the
        // guest has since entered themselves.
        if ($pmsBooking->guestName) {
            $attributes['guest_name'] = $pmsBooking->guestName;
        }

        if (! $booking) {
            $attributes['booking_id'] = 'CX-' . Str::upper(Str::random(8));
            $attributes['reservation_id'] = $pmsBooking->externalBookingId;
            $attributes['token'] = (string) Str::uuid();
            $attributes['status'] = 'pending';

            if ($pmsBooking->guestEmail) {
                $attributes['email'] = $pmsBooking->guestEmail;
            }
            if ($pmsBooking->guestPhone) {
                $attributes['phone'] = $pmsBooking->guestPhone;
            }

            $booking = Booking::create($attributes);

            // Notify staff (owner/contact desk, email + SMS) that a booking
            // just arrived from the channel manager -- otherwise a
            // PMS-sourced booking can sit unnoticed until someone happens
            // to open the admin panel. Guest is never messaged for this
            // event; they only hear from us once staff has reviewed it.
            \App\Services\GuestAlertService::send('pms_booking_received', $booking);
        } else {
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

                \App\Services\GuestAlertService::send('pms_booking_cancelled', $booking);

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
        }

        return $booking->fresh();
    }
}
