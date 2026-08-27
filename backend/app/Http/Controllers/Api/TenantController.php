<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenants = $user->is_super_admin
            ? Tenant::query()->get()
            : $user->tenants()
                ->where('tenants.is_active', true)
                ->wherePivot('is_active', true)
                ->get();

        return response()->json(['data' => $tenants]);
    }

    public function show(Tenant $tenant): JsonResponse
    {
        return response()->json(['data' => $tenant->load('branches')]);
    }
}
