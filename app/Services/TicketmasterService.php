<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TicketmasterService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://app.ticketmaster.com/discovery/v2/events.json';

    public function __construct()
    {
        $this->apiKey = config('services.ticketmaster.api_key');
    }

    /**
     * Find events near a given lat/long within a radius (miles).
     * Returns an array of simplified event data, or an empty array on failure.
     */
    public function findNearbyEvents(float $latitude, float $longitude, int $radiusMiles = 25, int $size = 10): array
    {
        if (!$this->apiKey) {
            Log::warning('Ticketmaster API key not configured; skipping local events lookup.');
            return [];
        }

        try {
            $response = Http::timeout(15)->get($this->baseUrl, [
                'apikey' => $this->apiKey,
                'latlong' => $latitude.','.$longitude,
                'radius' => $radiusMiles,
                'unit' => 'miles',
                'size' => $size,
                'sort' => 'date,asc',
            ]);

            if (!$response->successful()) {
                Log::warning('Ticketmaster API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $events = $response->json('_embedded.events', []);

            return collect($events)->map(function ($event) {
                return [
                    'name' => $event['name'] ?? 'Untitled event',
                    'url' => $event['url'] ?? null,
                    'date' => $event['dates']['start']['localDate'] ?? null,
                    'time' => $event['dates']['start']['localTime'] ?? null,
                    'image' => collect($event['images'] ?? [])->firstWhere('width', '>=', 300)['url']
                        ?? ($event['images'][0]['url'] ?? null),
                    'venue' => $event['_embedded']['venues'][0]['name'] ?? null,
                ];
            })->all();
        } catch (\Throwable $e) {
            Log::error('Ticketmaster API request threw an exception', ['message' => $e->getMessage()]);
            return [];
        }
    }
}
