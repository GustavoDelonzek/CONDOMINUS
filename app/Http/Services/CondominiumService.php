<?php

namespace App\Http\Services;

use App\Filters\CondominiumFilter;
use App\Http\Enums\EnumRoleUser;
use App\Models\Condominium;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CondominiumService
{
    public function getCondominiums(array $filters, User $user): LengthAwarePaginator
    {
        $query = (new CondominiumFilter(Condominium::query(), $filters))->applyFilters();

        if (!$user->is_super_admin) {
            $query->whereHas('memberships', function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('is_active', true);
            });
        }

        $query->orderBy('created_at', 'desc');

        return $query->paginate((int) data_get($filters, 'perPage', 15));
    }

    public function store(array $data, User $user): Condominium
    {
        if (!$user->is_super_admin) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        return Condominium::create($data);
    }

    public function showCondominium(Condominium $condominium, User $user): Condominium
    {
        if (!$user->is_super_admin && !$this->hasActiveMembership($condominium, $user)) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        return $condominium;
    }

    public function updateCondominium(Condominium $condominium, array $data, User $user): Condominium
    {
        if (!$user->is_super_admin && !$this->hasManagementRole($condominium, $user)) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        $condominium->update($data);

        return $condominium->fresh();
    }

    private function hasActiveMembership(Condominium $condominium, User $user): bool
    {
        return $condominium->memberships()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    private function hasManagementRole(Condominium $condominium, User $user): bool
    {
        return $condominium->memberships()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereIn('role', [EnumRoleUser::SYNDIC->value, EnumRoleUser::COMPANY_ADMIN->value])
            ->exists();
    }
}
