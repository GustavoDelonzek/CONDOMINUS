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

        $remoteJid = data_get($payload, 'data.key.remoteJid', data_get($payload, 'data.message.remoteJid'));
        if (!$remoteJid) {
            return null;
        }
        
        $phone = explode('@', $remoteJid)[0];

        $text = data_get($payload, 'data.message.conversation') 
             ?? data_get($payload, 'data.message.extendedTextMessage.text')
             ?? data_get($payload, 'data.message.message.conversation')
             ?? data_get($payload, 'data.message.message.extendedTextMessage.text');

        if (!$phone || !$text) {
            return null;
        }

        return [
            'phone' => $phone,
            'text'  => $text,
        ];
    }

    public function sendMessage(string $to, string $text): void
    {
        $url = env('EVOLUTION_API_URL', 'http://evolution_api:8080');
        $apiKey = env('EVOLUTION_API_KEY');
        $instance = env('EVOLUTION_INSTANCE', 'padrao');

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
}
