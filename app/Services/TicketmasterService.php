<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TicketmasterService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://app.ticketmaster.com/discovery/v2/events.json';

    protected array $classifications = [
        'Music',
        'Sports',
        'Arts & Theatre',
        'Film',
        'Family',
        'Miscellaneous',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.ticketmaster.api_key');
    }

    /**
     * Find events near a given lat/long within the next 7 days, grouped by
     * unique event (same event with multiple showtimes collapses into one
     * entry with a list of dates). Queries each category concurrently so
     * that one noisy category (e.g. a walk-in attraction with many daily
     * showtimes) doesn't crowd out other categories' results.
     *
     * Returns: ['events' => [...], 'totalElements' => int]
     */
    public function findNearbyEvents(float $latitude, float $longitude, int $radiusMiles = 25, int $perCategorySize = 20): array
    {
        if (!$this->apiKey) {
            Log::warning('Ticketmaster API key not configured; skipping local events lookup.');
            return ['events' => [], 'totalElements' => 0, 'hasMore' => false];
        }

        try {
            $startDateTime = now()->utc()->format('Y-m-d\\TH:i:s\\Z');
            $endDateTime = now()->utc()->addDays(7)->format('Y-m-d\\TH:i:s\\Z');

            $responses = Http::pool(function ($pool) use ($latitude, $longitude, $radiusMiles, $perCategorySize, $startDateTime, $endDateTime) {
                return collect($this->classifications)->map(function ($classification) use ($pool, $latitude, $longitude, $radiusMiles, $perCategorySize, $startDateTime, $endDateTime) {
                    return $pool->as($classification)->timeout(25)->get($this->baseUrl, [
                        'apikey' => $this->apiKey,
                        'latlong' => $latitude.','.$longitude,
                        'radius' => $radiusMiles,
                        'unit' => 'miles',
                        'size' => $perCategorySize,
                        'sort' => 'date,asc',
                        'classificationName' => $classification,
                        'startDateTime' => $startDateTime,
                        'endDateTime' => $endDateTime,
                    ]);
                })->all();
            });

            $allRawEvents = [];
            $totalElements = 0;

            foreach ($this->classifications as $classification) {
                $response = $responses[$classification] ?? null;

                if ($response instanceof \Throwable) {
                    Log::warning('Ticketmaster API request failed for classification', [
                        'classification' => $classification,
                        'error' => $response->getMessage(),
                    ]);
                    continue;
                }

                if (!$response || !$response->successful()) {
                    if ($response) {
                        Log::warning('Ticketmaster API request failed for classification', [
                            'classification' => $classification,
                            'status' => $response->status(),
                        ]);
                    }
                    continue;
                }

                $events = $response->json('_embedded.events', []);
                $totalElements += (int) $response->json('page.totalElements', 0);
                $allRawEvents = array_merge($allRawEvents, $events);
            }

            $grouped = $this->groupEvents($allRawEvents);

            $rawEventCount = count($allRawEvents);

            return [
                'events' => $grouped,
                'totalElements' => $totalElements,
                'hasMore' => $rawEventCount < $totalElements,
            ];
        } catch (\Throwable $e) {
            Log::error('Ticketmaster API request threw an exception', ['message' => $e->getMessage()]);
            return ['events' => [], 'totalElements' => 0, 'hasMore' => false];
        }
    }

    /**
     * Collapse raw Ticketmaster event occurrences into one entry per unique
     * event (matched by name + venue), each carrying its full list of
     * upcoming dates/times.
     */
    protected function groupEvents(array $events): array
    {
        return collect($events)
            ->groupBy(function ($event) {
                $venue = $event['_embedded']['venues'][0]['name'] ?? 'unknown-venue';
                return ($event['name'] ?? 'untitled') . '|' . $venue;
            })
            ->map(function ($occurrences) {
                $first = $occurrences->first();
                $dates = $occurrences->map(function ($event) {
                    return [
                        'date' => $event['dates']['start']['localDate'] ?? null,
                        'time' => $event['dates']['start']['localTime'] ?? null,
                    ];
                })
                    ->filter(fn ($d) => $d['date'])
                    ->unique(fn ($d) => $d['date'].'|'.$d['time'])
                    ->sortBy('date')
                    ->values()
                    ->all();

                return [
                    'name' => $first['name'] ?? 'Untitled event',
                    'url' => $first['url'] ?? null,
                    'dates' => $dates,
                    'date' => $dates[0]['date'] ?? null,
                    'time' => $dates[0]['time'] ?? null,
                    'image' => collect($first['images'] ?? [])->firstWhere('width', '>=', 300)['url']
                        ?? ($first['images'][0]['url'] ?? null),
                    'venue' => $first['_embedded']['venues'][0]['name'] ?? null,
                    'category' => $first['classifications'][0]['segment']['name'] ?? 'Other',
                ];
            })
            ->sortBy('date')
            ->values()
            ->all();
    }
}
