<?php

namespace App\Services\Pms;

/**
 * A single reservation as normalized from whichever PMS provider is active.
 * Controllers/jobs only ever see this shape — never Channex's or NextPax's
 * raw payload — so swapping providers later never touches consuming code.
 */
class PmsBooking
{
    public function __construct(
        public readonly string $externalBookingId,
        public readonly string $externalPropertyId,
        public readonly ?string $guestName,
        public readonly ?string $guestEmail,
        public readonly ?string $guestPhone,
        public readonly string $checkInDate,
        public readonly string $checkOutDate,
        public readonly ?string $status = null,
        public readonly array $raw = [],
    ) {
    }
}
