<?php

namespace App\Http\Controllers;

use App\Http\Requests\WhatsAppWebhookRequest;
use App\Services\WhatsApp\WhatsAppService;

class WhatsAppController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    public function handleWebhook(WhatsAppWebhookRequest $request)
    {
        $processed = $this->whatsAppService->handleIncomingWebhook($request->validated());

        if (!$processed) {
            return response()->json(['status' => 'ignored']);
        }

        return response()->json(['status' => 'queued']);
    }
}
