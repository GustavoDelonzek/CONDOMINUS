<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppProviderInterface;
use App\Jobs\ProcessWhatsAppMessage;
use App\Models\WhatsappInstance;

class WhatsAppService
{
    protected $whatsappProvider;

    public function __construct(WhatsAppProviderInterface $whatsappProvider)
    {
        $this->whatsappProvider = $whatsappProvider;
    }

    public function handleIncomingWebhook(array $payload, WhatsappInstance $instance): bool
    {
        $messageData = $this->whatsappProvider->extractPayloadData($payload);

        if (!$messageData) {
            return false;
        }

        ProcessWhatsAppMessage::dispatch($messageData, $instance->condominium_id, $instance->instance_id);

        return true;
    }
}
