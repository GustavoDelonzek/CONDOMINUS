<?php

namespace App\Http\Middleware;
use App\Http\Enums\EnumRoleUser;
use App\Models\Membership;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckMembership
{
    protected array $roles = [];

    public function handle($request, \Closure $next)
    {
        $condoId = $request->header('X-Condo-Id');

        if (!$condoId || !Str::isUuid($condoId)) {
            abort(404, 'Missing X-Condo-Id header');
        }

        $user = $request->user();

        $membership = $user->memberships()
            ->where('condominium_id', $condoId)
            ->where('is_active', true)
            ->first();

        if (!$membership) {
            if (!$user->is_super_admin) {
                throw new AccessDeniedHttpException('Invalid membership.');
            }

            $membership = new Membership([
                'user_id' => $user->id,
                'condominium_id' => $condoId,
                'role' => EnumRoleUser::SYNDIC->value,
                'is_active' => true,
            ]);
        }

        $request->merge(['current_membership' => $membership]);

        return $next($request);
    }
}
