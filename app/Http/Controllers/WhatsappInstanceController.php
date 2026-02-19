<?php

namespace App\Http\Controllers;

use App\Http\Resources\WhatsappInstanceResource;
use App\Http\Services\WhatsappInstanceService;
use App\Models\WhatsappInstance;
use Illuminate\Http\Request;

class WhatsappInstanceController extends Controller
{
    public function __construct(
        private readonly WhatsappInstanceService $whatsappInstanceService
    ) {
    }


    public function store(): WhatsappInstanceResource
    {
        return WhatsappInstanceResource::make(
            $this->whatsappInstanceService->createNewWhatsappInstance(request()->current_membership)
        );
    }

    public function show(WhatsappInstance $whatsappInstance): WhatsappInstanceResource
    {
        return WhatsappInstanceResource::make(
            $this->whatsappInstanceService->showWhatsappInstance($whatsappInstance, request()->current_membership)
        );
    }

    public function qrCode(WhatsappInstance $whatsappInstance)
    {
        return $this->whatsappInstanceService->whatsappInstanceQrCode($whatsappInstance, request()->current_membership);
    }

    public function update(Request $request, WhatsappInstance $whatsappInstance)
    {
        //
    }

    public function destroy(WhatsappInstance $whatsappInstance)
    {
        //
    }
}
