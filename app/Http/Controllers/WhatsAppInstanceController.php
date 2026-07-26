<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterMessageLogRequest;
use App\Http\Requests\WhatsAppInstanceRequest;
use App\Http\Resources\MessageLogResource;
use App\Http\Services\WhatsAppInstanceService;
use App\Models\Condominium;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WhatsAppInstanceController extends Controller
{
    public function __construct(
        private readonly WhatsAppInstanceService $whatsAppInstanceService
    ) {
    }

    public function show(WhatsAppInstanceRequest $request): JsonResponse
    {
        $membership = $request->validated()['membership'];

        return response()->json($this->whatsAppInstanceService->getStatus($membership));
    }

    public function connect(WhatsAppInstanceRequest $request): JsonResponse
    {
        $membership = $request->validated()['membership'];
        $condominium = Condominium::findOrFail($membership->condominium_id);

        return response()->json($this->whatsAppInstanceService->connect($membership, $condominium));
    }

    public function messages(FilterMessageLogRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        return MessageLogResource::collection(
            $this->whatsAppInstanceService->listMessages($validated['membership'], $validated)
        );
    }
}
