<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Validation\ValidationException;

class TenantDataGuard
{
    public function purchase(int $tenantId, int $branchId, ?int $supplierId, array $items): void
    {
        $this->branch($tenantId, $branchId);
        if ($supplierId) $this->model(Supplier::class, $tenantId, $supplierId, 'supplier_id');
        foreach ($items as $item) $this->model(Product::class, $tenantId, $item['product_id'], 'items');
    }

    public function product(int $tenantId, ?int $categoryId, ?int $brandId): void
    {
        if ($categoryId) $this->model(Category::class, $tenantId, $categoryId, 'category_id');
        if ($brandId) $this->model(Brand::class, $tenantId, $brandId, 'brand_id');
    }

    public function categoryParent(int $tenantId, ?int $parentId): void
    {
        if ($parentId) $this->model(Category::class, $tenantId, $parentId, 'parent_id');
    }

    public function sale(int $tenantId, int $branchId, ?int $customerId, array $items): void
    {
        $this->branch($tenantId, $branchId);
        if ($customerId) $this->model(Customer::class, $tenantId, $customerId, 'customer_id');
        foreach ($items as $item) $this->model(Product::class, $tenantId, $item['product_id'], 'items');
    }

    private function branch(int $tenantId, int $id): void
    {
        $branch = Branch::query()->whereKey($id)->where('tenant_id', $tenantId)->where('is_active', true)->first();
        if (! $branch) throw ValidationException::withMessages(['branch_id' => ['La sucursal no está disponible para operar.']]);
    }
    private function model(string $class, int $tenantId, int $id, string $field): void
    {
        if (! $class::query()->whereKey($id)->where('tenant_id', $tenantId)->exists()) throw ValidationException::withMessages([$field => ['El recurso no pertenece a la empresa seleccionada.']]);
    }
}
