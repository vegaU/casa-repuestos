<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Tenant $tenant): JsonResponse
    {
        return response()->json(['data' => $tenant->categories()->orderBy('name')->get()]);
    }

    public function store(Request $request, Tenant $tenant): JsonResponse
    {
        $data = $request->validate(['parent_id' => ['nullable', 'integer'], 'name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'is_active' => ['boolean']]);
        $data['tenant_id'] = $tenant->id;
        $category = Category::create($data);
        return response()->json(['data' => $category], 201);
    }

    public function update(Request $request, Tenant $tenant, Category $category): JsonResponse
    {
        abort_unless($category->tenant_id === $tenant->id, 404);
        $category->update($request->validate(['parent_id' => ['nullable', 'integer'], 'name' => ['sometimes', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'is_active' => ['boolean']]));
        return response()->json(['data' => $category]);
    }
}
