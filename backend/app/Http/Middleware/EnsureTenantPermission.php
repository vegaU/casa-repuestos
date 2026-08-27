<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\TenantPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        $tenant = $request->route('tenant');

        abort_unless($tenant instanceof Tenant, 404);
        if ($user->is_super_admin) return $next($request);

        $membership = $user->tenants()->whereKey($tenant->id)->wherePivot('is_active', true)->first();
        abort_unless($membership && TenantPermissions::allows($membership->pivot->role, $permission), 403, 'No tienes permiso para realizar esta operación.');

        return $next($request);
    }
}
