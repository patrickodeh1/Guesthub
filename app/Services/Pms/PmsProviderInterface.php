<?php

namespace App\Services\Pms;

/**
 * Implemented by each channel-manager integration (Channex now, NextPax
 * later). Consuming code (sync job, webhook controller, admin sync UI) only
 * ever depends on this interface — never on a concrete provider — so
 * switching providers is: write a new class implementing this interface,
 * then flip PMS_PROVIDER in config. No other code should need to change.
 *
 * Deliberately read-only / import-only: Guesthub never writes reservation
 * data back to the PMS. See project notes — Guesthub is a guest-management
 * tool, not a booking engine or rate/availability manager.
 */
interface PmsProviderInterface
{
    /**
     * Fetch bookings changed since a given point (or all, if null) — used by
     * the scheduled poll job. Each provider decides what "changed since"
     * means in its own terms (e.g. Channex's booking revisions feed).
     *
     * @return PmsBooking[]
     */
    public function getBookings(?\DateTimeInterface $since = null): array;

    public function getBooking(string $externalBookingId): ?PmsBooking;

    /**
     * Some providers (Channex) require every pulled booking to be
     * acknowledged or they keep re-sending it. Providers that don't need
     * this (e.g. NextPax may not) can make this a no-op.
     */
    public function acknowledgeBooking(string $externalBookingId): void;

    /**
     * Parse an incoming webhook payload into a normalized PmsBooking (or
     * null if the payload isn't a booking event this provider cares about).
     */
    public function handleWebhookPayload(array $payload): ?PmsBooking;

    /**
     * BACKFILL ONLY. Fetch the full/current list of bookings, bypassing
     * whatever "changed since" mechanism getBookings() normally uses.
     *
     * This exists for one-time historical imports (e.g. a Channex gap where
     * bookings existed before revision tracking started and can never be
     * recovered via the revision feed, since acknowledged/older revisions
     * are permanently dropped from it). Providers whose regular
     * getBookings() already returns everything (no revision/ack concept)
     * may simply delegate this to getBookings($since = null).
     *
     * Must NOT be called from the regular scheduled sync -- only from a
     * manual, explicitly-invoked backfill command.
     *
     * @param array{arrival_date_from?: string, arrival_date_to?: string, departure_date_from?: string, departure_date_to?: string}|null $dateRange
     * @return PmsBooking[]
     */
    public function getAllBookings(?array $dateRange = null): array;
}
