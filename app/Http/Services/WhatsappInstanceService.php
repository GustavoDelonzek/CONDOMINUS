<?php

namespace App\Http\Services;

use App\Http\Enums\EnumRoleUser;
use App\Http\Enums\EnumWhatsappInstanceStatus;
use App\Models\Condominium;
use App\Models\Membership;
use App\Models\WhatsappInstance;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WhatsappInstanceService
{
    public function __construct(
        private readonly ZApiService $zApiService,
    )   {
    }

    //TODO: remove unnecessary duplicate validation, centralize in a function.
    public function createNewWhatsappInstance(Membership $membership)
    {
        if ($membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        $whatsappInstances = WhatsappInstance::query()
            ->where('condominium_id', $membership->condominium_id)
            ->whereIn('status', [
                EnumWhatsappInstanceStatus::CONNECTED->value,
                EnumWhatsappInstanceStatus::QRCODE->value
            ]);

        if ($whatsappInstances->count() > 0) {
            throw new HttpException(403, 'Limit exceeded. Only one whatsapp instance is allowed.');
        }

        $whatsappInstance = WhatsappInstance::create([
            'condominium_id' => $membership->condominium_id,
            'status' => EnumWhatsappInstanceStatus::DISCONNECTED->value,
        ]);

        $instance = $this->zApiService->createInstance([
            'externalId' => $whatsappInstance->id,
            'name' => $membership->condominium->name,
            'webhookUrl' => config('services.zapi.webhook_url'),
        ]);

        $whatsappInstance->update([
            'instance_id' => $instance['instanceId'],
            'instance_token' => $instance['instanceToken'],
        ]);

        return $whatsappInstance;
    }

    public function showWhatsappInstance(WhatsappInstance $whatsappInstance, Membership $membership)
    {
        if ($membership->condominium_id !== $whatsappInstance->condominium_id || $membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        return $whatsappInstance;
    }

    public function whatsappInstanceStatus(WhatsappInstance $whatsappInstance, Membership $membership)
    {
        if ($membership->condominium_id !== $whatsappInstance->condominium_id || $membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        $qrCode = $this->zApiService->qrCode($whatsappInstance->instance_id, $whatsappInstance->instance_token);


    }

}
