<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZApiProvider implements WhatsAppProviderInterface
{
    public function extractPayloadData(array $payload): ?array
    {
        if (data_get($payload, 'isGroup') || data_get($payload, 'fromMe')) {
            return null;
        }

        return [
            'phone' => data_get($payload, 'phone'),
            'text'  => data_get($payload, 'text.message'),
        ];
    }

    public function sendMessage(string $to, string $text): void
    {
        $instanceId = env('ZAPI_INSTANCE');
        $token = env('ZAPI_TOKEN');
        $url = "https://api.z-api.io/instances/{$instanceId}/token/{$token}/send-text";

        try {
            Http::post($url, [
                'phone'   => $to,
                'message' => $text
            ]);
        } catch (\Exception $e) {
            Log::error("Z-API Error: " . $e->getMessage());
        }
    }
}
