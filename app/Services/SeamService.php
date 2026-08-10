<?php

namespace App\Services;

use Seam\SeamClient;

class SeamService
{
    protected SeamClient $client;

    public function __construct()
    {
        $this->client = new SeamClient(api_key: config('services.seam.api_key'));
    }

    public function unlock(string $deviceId): array
    {
        $attempt = $this->client->locks->unlock_door(device_id: $deviceId, wait_for_action_attempt: false);
        $result = (array) $attempt;
        \Illuminate\Support\Facades\Log::info('Seam unlock command sent', ['device_id' => $deviceId, 'response' => $result]);
        return $result;
    }

    public function lock(string $deviceId): array
    {
        $attempt = $this->client->locks->lock_door(device_id: $deviceId, wait_for_action_attempt: false);
        $result = (array) $attempt;
        \Illuminate\Support\Facades\Log::info('Seam lock command sent', ['device_id' => $deviceId, 'response' => $result]);
        return $result;
    }

    public function getDevice(string $deviceId): array
    {
        return (array) $this->client->devices->get(device_id: $deviceId);
    }

    public function getLockStatus(string $deviceId): ?bool
    {
        $device = $this->client->devices->get(device_id: $deviceId);
        return $device->properties->locked ?? null;
    }

    public function getBatteryLevel(string $deviceId): ?int
    {
        $device = $this->client->devices->get(device_id: $deviceId);
        $level = $device->properties->battery->level ?? null;

        if ($level === null) {
            return null;
        }

        return (int) round($level * 100);
    }

    public function createGuestAccessGrant(string $deviceId, string $guestName, string $guestEmail, \DateTime $startsAt, \DateTime $endsAt): array
    {
        $grant = $this->client->access_grants->create(
            user_identity: [
                'full_name' => $guestName,
                'email_address' => $guestEmail,
            ],
            device_ids: [$deviceId],
            requested_access_methods: [['mode' => 'code']],
            starts_at: $startsAt->format(DATE_ATOM),
            ends_at: $endsAt->format(DATE_ATOM),
        );

        return (array) $grant;
    }

    public function getAccessMethod(string $accessMethodId): array
    {
        return (array) $this->client->access_methods->get(access_method_id: $accessMethodId);
    }
}
