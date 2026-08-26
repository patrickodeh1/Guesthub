<?php

namespace App\Services\Pms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Channex implementation of PmsProviderInterface. Import-only: we never
 * write reservation data back to Channex.
 *
 * NOTE ON ENDPOINT PATHS: written against Channex's publicly documented API
 * shape (JSON REST, api_key header auth, /bookings + a booking-revisions
 * feed for polling, acknowledgement required per pulled booking). Exact
 * paths/payload field names should be verified against Channex's live docs
 * / Postman collection once real API access is available — flagged inline
 * below wherever an assumption is baked in, rather than left silent.
 */
class ChannexProvider implements PmsProviderInterface
{
    private string $baseUrl;
    private ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.channex.base_url'), '/');
        $this->apiKey = config('services.channex.api_key');
    }

    private function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders(['user-api-key' => $this->apiKey])
            ->acceptJson();
    }

    public function getBookings(?\DateTimeInterface $since = null): array
    {
        // ASSUMPTION: revisions/bookings feed accepts an `updated_since`
        // query param for incremental polling. Confirm against live docs.
        $response = $this->client()->get('/bookings', array_filter([
            'updated_since' => $since?->format(DATE_ATOM),
        ]));

        if (! $response->successful()) {
            Log::warning('Channex getBookings failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        }

        return collect($response->json('data', []))
            ->map(fn (array $item) => $this->normalize($item))
            ->filter()
            ->values()
            ->all();
    }

    public function getBooking(string $externalBookingId): ?PmsBooking
    {
        $response = $this->client()->get("/bookings/{$externalBookingId}");

        if (! $response->successful()) {
            return null;
        }

        return $this->normalize($response->json('data', []));
    }

    public function acknowledgeBooking(string $externalBookingId): void
    {
        // Channex requires pulled bookings to be acknowledged or it keeps
        // re-sending them on the revisions feed. ASSUMPTION: this endpoint
        // shape — confirm against live docs before relying on it.
        $response = $this->client()->post("/bookings/{$externalBookingId}/ack");

        if (! $response->successful()) {
            Log::warning('Channex acknowledgeBooking failed', [
                'booking_id' => $externalBookingId,
                'status' => $response->status(),
            ]);
        }
    }

    public function handleWebhookPayload(array $payload): ?PmsBooking
    {
        // ASSUMPTION: webhook payload wraps the booking under a top-level
        // "booking" key — confirm exact envelope shape against live docs.
        $bookingData = $payload['booking'] ?? $payload['data'] ?? null;

        if (! is_array($bookingData)) {
            return null;
        }

        return $this->normalize($bookingData);
    }

    private function normalize(array $item): ?PmsBooking
    {
        if (empty($item['id']) || empty($item['property_id'])) {
            return null;
        }

        $guest = $item['customer'] ?? [];

        return new PmsBooking(
            externalBookingId: (string) $item['id'],
            externalPropertyId: (string) $item['property_id'],
            guestName: trim(($guest['name'] ?? '') . ' ' . ($guest['surname'] ?? '')) ?: null,
            guestEmail: $guest['mail'] ?? null,
            guestPhone: $guest['phone'] ?? null,
            checkInDate: $item['arrival_date'] ?? $item['check_in_date'] ?? '',
            checkOutDate: $item['departure_date'] ?? $item['check_out_date'] ?? '',
            status: $item['status'] ?? null,
            raw: $item,
        );
    }
}
