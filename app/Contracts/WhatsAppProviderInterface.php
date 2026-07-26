<?php

namespace App\Contracts;

interface WhatsAppProviderInterface
{
    public function extractPayloadData(array $payload): ?array;
    public function sendMessage(string $instance, string $to, string $text): void;
    public function createInstance(string $instance): array;
    public function getConnectionState(string $instance): string;
    public function getQrCode(string $instance): ?string;
    public function setWebhook(string $instance, string $url): void;
}
