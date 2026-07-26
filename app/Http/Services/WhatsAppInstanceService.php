<?php

namespace App\Http\Services;

use App\Contracts\WhatsAppProviderInterface;
use App\Http\Enums\EnumRoleUser;
use App\Models\Condominium;
use App\Models\Membership;
use App\Models\MessageLog;
use App\Models\WhatsappInstance;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class WhatsAppInstanceService
{
    public function __construct(
        private readonly WhatsAppProviderInterface $whatsapp
    ) {
    }

    public function getStatus(Membership $membership): array
    {
        $instance = WhatsappInstance::where('condominium_id', $membership->condominium_id)->first();

        if (!$instance) {
            return ['status' => 'not_configured', 'phone_number_connected' => null];
        }

        $state = $this->whatsapp->getConnectionState($instance->instance_id);

        if ($state !== $instance->status) {
            $instance->update(['status' => $state]);
        }

        return [
            'status' => $instance->status,
            'phone_number_connected' => $instance->phone_number_connected,
        ];
    }

    public function connect(Membership $membership, Condominium $condominium): array
    {
        if ($membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        $instance = WhatsappInstance::where('condominium_id', $condominium->id)->first();

        if (!$instance) {
            $instanceName = 'condo-' . $condominium->id;
            $created = $this->whatsapp->createInstance($instanceName);

            $instance = WhatsappInstance::create([
                'condominium_id' => $condominium->id,
                'instance_id' => $instanceName,
                'instance_token' => data_get($created, 'hash', ''),
                'status' => data_get($created, 'instance.status', 'connecting'),
                'phone_number_connected' => null,
            ]);

            $webhookUrl = rtrim(env('APP_INTERNAL_URL', 'http://nginx'), '/')
                . '/api/v1/whatsapp/webhook/' . $instanceName;
            $this->whatsapp->setWebhook($instanceName, $webhookUrl);
        }

        $state = $this->whatsapp->getConnectionState($instance->instance_id);
        if ($state !== $instance->status) {
            $instance->update(['status' => $state]);
        }

        $qrcode = $state === 'open' ? null : $this->whatsapp->getQrCode($instance->instance_id);

        return [
            'status' => $instance->status,
            'qrcode' => $qrcode,
        ];
    }

    public function listMessages(Membership $membership, array $filters): LengthAwarePaginator
    {
        return MessageLog::query()
            ->with('user')
            ->where('condominium_id', $membership->condominium_id)
            ->orderBy('created_at', 'desc')
            ->paginate((int) data_get($filters, 'per_page', 20));
    }
}
