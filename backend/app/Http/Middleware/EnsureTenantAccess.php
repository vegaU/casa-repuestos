<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->route('tenant');

        if (! $tenant instanceof Tenant) {
            abort(404);
        }

        $user = $request->user();

        if (! $user->is_super_admin && ! $user->tenants()
            ->whereKey($tenant->id)
            ->wherePivot('is_active', true)
            ->exists()) {
            abort(403, 'No tienes acceso a esta empresa.');
        }

        return $next($request);
    }
}
