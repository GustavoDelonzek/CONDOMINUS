<?php

namespace App\Http\Services;

use App\Http\Enums\EnumRoleUser;
use App\Jobs\SendOccurrenceResponseNotification;
use App\Models\Occurrence;
use App\Models\WhatsappInstance;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OccurrenceService
{
    private const ALLOWED_TRANSITIONS = [
        'open' => ['open', 'in_progress', 'resolved', 'closed'],
        'in_progress' => ['in_progress', 'resolved', 'closed'],
        'resolved' => ['closed'],
    ];

    public function getAllOccurrences(array $filters): LengthAwarePaginator
    {
        $membership = data_get($filters, 'membership');

        $query = Occurrence::query()
            ->with(['unit.block', 'user', 'occurrenceMedias'])
            ->where('condominium_id', $membership->condominium_id);

        if ($status = data_get($filters, 'status')) {
            $query->where('status', $status);
        }

        if ($priority = data_get($filters, 'priority')) {
            $query->where('priority', $priority);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate((int) data_get($filters, 'per_page', 15));
    }

    public function updateStatus(Occurrence $occurrence, array $data): Occurrence
    {
        $membership = data_get($data, 'membership');

        if ($membership->condominium_id !== $occurrence->condominium_id || $membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        $nextStatus = data_get($data, 'status');
        $allowed = self::ALLOWED_TRANSITIONS[$occurrence->status] ?? [];

        if (!in_array($nextStatus, $allowed, true)) {
            throw new UnprocessableEntityHttpException(
                "Invalid status transition from \"{$occurrence->status}\" to \"{$nextStatus}\"."
            );
        }

        $updatePayload = ['status' => $nextStatus];

        if (array_key_exists('priority', $data)) {
            $updatePayload['priority'] = $data['priority'];
        }

        $adminResponse = data_get($data, 'admin_response');
        if ($adminResponse) {
            $updatePayload['admin_response'] = $adminResponse;
            $updatePayload['responded_at'] = now();
        }

        $occurrence->update($updatePayload);

        if (data_get($data, 'notify_resident') && $adminResponse) {
            $occurrence->loadMissing('user');
            if ($occurrence->user?->phone_number) {
                $instanceName = WhatsappInstance::where('condominium_id', $occurrence->condominium_id)
                    ->value('instance_id');

                SendOccurrenceResponseNotification::dispatch(
                    $occurrence->condominium_id,
                    $occurrence->user->id,
                    $occurrence->user->phone_number,
                    $adminResponse,
                    $instanceName,
                );
            }
        }

        return $occurrence->fresh(['unit.block', 'user', 'occurrenceMedias']);
    }
}
