<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppProviderInterface;
use App\Jobs\ProcessWhatsAppMessage;

class WhatsAppService
{
    protected $whatsappProvider;

    public function __construct(WhatsAppProviderInterface $whatsappProvider)
    {
        $this->whatsappProvider = $whatsappProvider;
    }

    public function handleIncomingWebhook(array $payload): bool
    {
        $messageData = $this->whatsappProvider->extractPayloadData($payload);

        if (!$messageData) {
            return false;
        }

        ProcessWhatsAppMessage::dispatch($messageData);

        return true;
    }
}
