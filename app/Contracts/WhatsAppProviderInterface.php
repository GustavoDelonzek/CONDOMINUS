<?php

namespace App\Contracts;

interface WhatsAppProviderInterface
{
    public function extractPayloadData(array $payload): ?array;
    public function sendMessage(string $to, string $text): void;
}
