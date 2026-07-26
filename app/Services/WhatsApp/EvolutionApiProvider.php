<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiProvider implements WhatsAppProviderInterface
{
    public function extractPayloadData(array $payload): ?array
    {
        $event = data_get($payload, 'event');
        
        if ($event !== 'messages.upsert') {
            return null;
        }

        $fromMe = data_get($payload, 'data.message.fromMe', data_get($payload, 'data.key.fromMe'));
        if ($fromMe === true) {
            return null;
        }

        // Group chats use a "...@g.us" JID — the bot only talks 1:1 with
        // residents, never inside a group.
        $rawRemoteJid = data_get($payload, 'data.key.remoteJid', data_get($payload, 'data.message.remoteJid'));
        if ($rawRemoteJid && str_ends_with($rawRemoteJid, '@g.us')) {
            return null;
        }

        // WhatsApp's "LID" privacy mode sends the real phone-number JID in
        // remoteJidAlt, while remoteJid becomes an opaque Linked ID that
        // can't be used to send a reply back.
        $remoteJid = data_get($payload, 'data.key.remoteJidAlt') ?? $rawRemoteJid;
        if (!$remoteJid) {
            return null;
        }

        $phone = explode('@', $remoteJid)[0];

        $text = data_get($payload, 'data.message.conversation') 
             ?? data_get($payload, 'data.message.extendedTextMessage.text')
             ?? data_get($payload, 'data.message.message.conversation')
             ?? data_get($payload, 'data.message.message.extendedTextMessage.text');

        if ($phone === '' || $text === null || $text === '') {
            return null;
        }

        return [
            'phone' => $phone,
            'text'  => $text,
        ];
    }

    public function sendMessage(string $instance, string $to, string $text): void
    {
        $url = env('EVOLUTION_API_URL', 'http://evolution_api:8080');
        $apiKey = env('EVOLUTION_API_KEY');

        $remoteJid = $to;
        if (!str_contains($remoteJid, '@')) {
            $remoteJid .= '@s.whatsapp.net';
        }

        try {
            Http::withHeaders([
                'apikey' => $apiKey,
                'Content-Type' => 'application/json'
            ])
            ->post("{$url}/message/sendText/{$instance}", [
                'number' => $remoteJid,
                'text' => $text
            ]);
        } catch (\Exception $e) {
            Log::error("Evolution API Error: " . $e->getMessage());
        }
    }

    public function createInstance(string $instance): array
    {
        $url = env('EVOLUTION_API_URL', 'http://evolution_api:8080');
        $apiKey = env('EVOLUTION_API_KEY');

        $response = Http::withHeaders([
            'apikey' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$url}/instance/create", [
            'instanceName' => $instance,
            'qrcode' => true,
            'integration' => 'WHATSAPP-BAILEYS',
        ]);

        return $response->json() ?? [];
    }

    public function getConnectionState(string $instance): string
    {
        $url = env('EVOLUTION_API_URL', 'http://evolution_api:8080');
        $apiKey = env('EVOLUTION_API_KEY');

        $response = Http::withHeaders(['apikey' => $apiKey])
            ->get("{$url}/instance/connectionState/{$instance}");

        return data_get($response->json(), 'instance.state', 'close');
    }

    public function getQrCode(string $instance): ?string
    {
        $url = env('EVOLUTION_API_URL', 'http://evolution_api:8080');
        $apiKey = env('EVOLUTION_API_KEY');

        $response = Http::withHeaders(['apikey' => $apiKey])
            ->get("{$url}/instance/connect/{$instance}");

        return data_get($response->json(), 'base64');
    }

    public function setWebhook(string $instance, string $url): void
    {
        $baseUrl = env('EVOLUTION_API_URL', 'http://evolution_api:8080');
        $apiKey = env('EVOLUTION_API_KEY');

        Http::withHeaders([
            'apikey' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$baseUrl}/webhook/set/{$instance}", [
            'webhook' => [
                'url' => $url,
                'enabled' => true,
                'events' => ['MESSAGES_UPSERT'],
            ],
        ]);
    }
}
