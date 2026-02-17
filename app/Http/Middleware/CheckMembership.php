<?php

namespace App\Http\Middleware;
use http\Exception\BadHeaderException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class CheckMembership
{
    protected array $roles = [];

    public function handle($request, \Closure $next)
    {
        $condoId = $request->header('X-Condo-Id');

        if (!$condoId || !Str::isUuid($condoId)) {
            abort(404, 'Missing X-Condo-Id header');
        }

        $membership = $request->user()->memberships()
            ->where('condominium_id', $condoId)
            ->where('is_active', true)
            ->first();

        if (!$membership) {
            throw new AccessDeniedException('Invalid membership.');
        }

        $request->merge(['current_membership' => $membership]);

        return $next($request);
    }
}
