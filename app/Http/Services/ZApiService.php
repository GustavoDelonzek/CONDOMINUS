<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;

class ZApiService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.zapi.base_url');
        $this->token = config('services.zapi.admin_token');
    }

    public function createInstance(array $payload)
    {
        return Http::withHeaders([
            'Client-Token' => $this->clientToken
        ])
            ->withToken($this->token)
            ->post("{$this->baseUrl}/instances", $payload)
            ->throw()
            ->json();
    }

    public function getQrCode(string $instanceId, string $instanceToken)
    {
        return Http::withHeaders([
            'Client-Token' => $this->clientToken
        ])
            ->withToken($this->token)
    }

}
