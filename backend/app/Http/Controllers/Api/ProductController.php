<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Tenant;
use App\Services\TenantDataGuard;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private function rules(): array
    {
        return ['category_id'=>['nullable','integer'], 'brand_id'=>['nullable','integer'], 'sku'=>['nullable','string','max:100'], 'barcode'=>['nullable','string','max:100'], 'name'=>['required','string','max:255'], 'description'=>['nullable','string'], 'unit'=>['nullable','string','max:20'], 'cost_price'=>['nullable','numeric','min:0'], 'sale_price'=>['nullable','numeric','min:0'], 'reorder_point'=>['nullable','numeric','min:0'], 'is_active'=>['boolean']];
    }

    public function index(Request $request, Tenant $tenant)
    {
        $products = $tenant->products()->with(['brand','category'])->orderBy('name');
        if ($search = $request->string('search')->toString()) $products->where(fn ($query) => $query->where('name','ilike',"%$search%")->orWhere('sku','ilike',"%$search%")->orWhere('barcode','ilike',"%$search%"));
        return ['data' => $products->paginate(20)];
    }

    public function store(Request $request, Tenant $tenant, TenantDataGuard $guard)
    {
        $data = $request->validate($this->rules());
        $guard->product($tenant->id, $data['category_id'] ?? null, $data['brand_id'] ?? null);
        $data['tenant_id'] = $tenant->id;
        return response()->json(['data' => Product::create($data)], 201);
    }

    public function show(Tenant $tenant, Product $product)
    {
        abort_unless($product->tenant_id === $tenant->id, 404);
        return ['data' => $product->load(['brand','category','inventories.branch'])];
    }

    public function update(Request $request, Tenant $tenant, Product $product, TenantDataGuard $guard)
    {
        abort_unless($product->tenant_id === $tenant->id, 404);
        $data = $request->validate(array_map(fn ($rules) => array_merge(['sometimes'], $rules), $this->rules()));
        $guard->product($tenant->id, $data['category_id'] ?? $product->category_id, $data['brand_id'] ?? $product->brand_id);
        $product->update($data);
        return ['data' => $product];
    }
}
