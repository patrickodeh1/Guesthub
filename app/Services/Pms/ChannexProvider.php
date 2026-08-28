<?php

namespace App\Services\Pms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Channex implementation of PmsProviderInterface. Import-only: we never
 * write reservation data back to Channex.
 *
 * Verified against Channex's live API docs (docs.channex.io) as of
 * 2026-08-26. Channex's own PMS certification notes explicitly say not to
 * use the /bookings endpoints — use /booking_revisions instead — so this
 * provider is built entirely around Booking Revisions:
 *   - GET  /booking_revisions/feed      pull unacknowledged revisions
 *   - GET  /booking_revisions/:id       pull one revision by its own ID
 *   - POST /booking_revisions/:id/ack   acknowledge a revision
 *
 * A Booking Revision's `id` (used everywhere above) is the *revision* ID,
 * NOT the booking ID — the booking ID is a separate sibling field
 * (`booking_id`) inside each revision's attributes. Mixing these up (e.g.
 * acknowledging using the booking ID) will 404 against the real API.
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

    /**
     * Pulls unacknowledged Booking Revisions. This is Channex's documented
     * primary way to fetch bookings — the feed only ever contains revisions
     * not yet acknowledged, so `$since` doesn't map to a query param here;
     * it's accepted for interface compatibility but revisions are removed
     * from the feed entirely once acked, making an additional time filter
     * redundant.
     */
    public function getBookings(?\DateTimeInterface $since = null): array
    {
        $response = $this->client()->get('/booking_revisions/feed', [
            'order[inserted_at]' => 'asc',
        ]);

        if (! $response->successful()) {
            Log::warning('Channex getBookings (booking_revisions/feed) failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        }

        return collect($response->json('data', []))
            ->map(fn (array $item) => $this->normalizeRevision($item))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Fetches a single Booking Revision by its own (revision) ID.
     */
    public function getBooking(string $externalBookingId): ?PmsBooking
    {
        $response = $this->client()->get("/booking_revisions/{$externalBookingId}");

        if (! $response->successful()) {
            Log::warning('Channex getBooking (booking_revisions/:id) failed', [
                'revision_id' => $externalBookingId,
                'status' => $response->status(),
            ]);
            return null;
        }

        return $this->normalizeRevision($response->json('data', []));
    }

    /**
     * Acknowledges a Booking Revision. IMPORTANT: $externalBookingId here
     * must be the revision ID (PmsBooking::$revisionId), not the booking
     * ID — Channex's ack endpoint is scoped to revisions, and passing the
     * booking ID will 404.
     */
    public function acknowledgeBooking(string $externalBookingId): void
    {
        $response = $this->client()->post("/booking_revisions/{$externalBookingId}/ack");

        if (! $response->successful()) {
            Log::warning('Channex acknowledgeBooking (booking_revisions/:id/ack) failed', [
                'revision_id' => $externalBookingId,
                'status' => $response->status(),
            ]);
        }
    }

    /**
     * Channex booking webhooks (booking / booking_new / booking_modification
     * / booking_cancellation) do NOT carry the full booking record — only a
     * pointer: { "event": "...", "payload": { "booking_id", "property_id",
     * "revision_id" }, ... }. The webhook exists purely to trigger a pull of
     * the full revision via the API, which we do here using revision_id
     * (falling back to booking_id only if revision_id is somehow absent,
     * though that shouldn't happen for booking_new/modification/cancellation).
     */
    public function handleWebhookPayload(array $payload): ?PmsBooking
    {
        $inner = $payload['payload'] ?? null;

        if (! is_array($inner)) {
            return null;
        }

        $revisionId = $inner['revision_id'] ?? $inner['booking_id'] ?? null;

        if (! $revisionId) {
            return null;
        }

        return $this->getBooking((string) $revisionId);
    }

    /**
     * Normalizes a Booking Revision object (as returned by
     * /booking_revisions/feed and /booking_revisions/:id) into a PmsBooking.
     * The revision's own `id` is the revision ID; `booking_id` is the
     * separate, stable booking identifier used elsewhere (e.g. imports).
     */
    private function normalizeRevision(array $item): ?PmsBooking
    {
        // Both feed/get-by-id responses wrap fields under "attributes";
        // guard for either shape defensively.
        $attributes = $item['attributes'] ?? $item;

        if (empty($attributes['booking_id']) || empty($attributes['property_id'])) {
            return null;
        }

        $guest = $attributes['customer'] ?? [];

        return new PmsBooking(
            externalBookingId: (string) $attributes['booking_id'],
            externalPropertyId: (string) $attributes['property_id'],
            guestName: trim(($guest['name'] ?? '') . ' ' . ($guest['surname'] ?? '')) ?: null,
            guestEmail: $guest['mail'] ?? null,
            guestPhone: $guest['phone'] ?? null,
            checkInDate: $attributes['arrival_date'] ?? '',
            checkOutDate: $attributes['departure_date'] ?? '',
            status: $attributes['status'] ?? null,
            raw: $attributes,
            revisionId: isset($attributes['id']) ? (string) $attributes['id'] : null,
        );
    }
}
