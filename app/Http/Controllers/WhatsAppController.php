<?php

namespace App\Http\Controllers;

use App\Http\Requests\WhatsAppWebhookRequest;
use App\Models\WhatsappInstance;
use App\Services\WhatsApp\WhatsAppService;

class WhatsAppController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    public function handleWebhook(WhatsAppWebhookRequest $request, string $instance)
    {
        $whatsappInstance = WhatsappInstance::where('instance_id', $instance)->first();

        if (!$whatsappInstance) {
            return response()->json(['status' => 'ignored']);
        }

        $processed = $this->whatsAppService->handleIncomingWebhook($request->all(), $whatsappInstance);

        if (!$processed) {
            return response()->json(['status' => 'ignored']);
        }

        return response()->json(['status' => 'queued']);
    }
}
