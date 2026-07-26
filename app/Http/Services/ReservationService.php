<?php

namespace App\Http\Services;

use App\Http\Enums\EnumRoleUser;
use App\Models\Reservation;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ReservationService
{
    private const ALLOWED_TRANSITIONS = [
        'pending' => ['confirmed', 'denied', 'canceled'],
        'confirmed' => ['canceled', 'completed'],
    ];

    public function getAllReservations(array $filters): LengthAwarePaginator
    {
        $membership = data_get($filters, 'membership');

        $query = Reservation::query()
            ->with(['commonArea', 'unit', 'user'])
            ->where('condominium_id', $membership->condominium_id);

        if ($status = data_get($filters, 'status')) {
            $query->where('status', $status);
        }

        if ($commonAreaId = data_get($filters, 'common_area_id')) {
            $query->where('common_area_id', $commonAreaId);
        }

        return $query->orderBy('start_time', 'desc')
            ->paginate((int) data_get($filters, 'per_page', 15));
    }

    public function updateStatus(Reservation $reservation, array $data): Reservation
    {
        $membership = data_get($data, 'membership');

        if ($membership->condominium_id !== $reservation->condominium_id || $membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        $nextStatus = data_get($data, 'status');
        $allowed = self::ALLOWED_TRANSITIONS[$reservation->status] ?? [];

        if (!in_array($nextStatus, $allowed, true)) {
            throw new UnprocessableEntityHttpException(
                "Invalid status transition from \"{$reservation->status}\" to \"{$nextStatus}\"."
            );
        }

        $reservation->update(['status' => $nextStatus]);

        return $reservation->fresh(['commonArea', 'unit', 'user']);
    }
}
