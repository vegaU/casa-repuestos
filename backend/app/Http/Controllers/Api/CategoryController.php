<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tenant;
use App\Services\TenantDataGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Tenant $tenant): JsonResponse
    {
        return response()->json(['data' => $tenant->categories()->orderBy('name')->get()]);
    }

    public function store(Request $request, Tenant $tenant, TenantDataGuard $guard): JsonResponse
    {
        $data = $request->validate(['parent_id' => ['nullable', 'integer'], 'name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'is_active' => ['boolean']]);
        $guard->categoryParent($tenant->id, $data['parent_id'] ?? null);
        $data['tenant_id'] = $tenant->id;
        $category = Category::create($data);
        return response()->json(['data' => $category], 201);
    }

    public function update(Request $request, Tenant $tenant, Category $category, TenantDataGuard $guard): JsonResponse
    {
        abort_unless($category->tenant_id === $tenant->id, 404);
        $data = $request->validate(['parent_id' => ['nullable', 'integer'], 'name' => ['sometimes', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'is_active' => ['boolean']]);
        $guard->categoryParent($tenant->id, $data['parent_id'] ?? $category->parent_id);
        $category->update($data);
        return response()->json(['data' => $category]);
    }
}
