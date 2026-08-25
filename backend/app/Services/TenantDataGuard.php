<?php

namespace App\Services;

use App\Models\Branch;
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

    public function sale(int $tenantId, int $branchId, ?int $customerId, array $items): void
    {
        $this->branch($tenantId, $branchId);
        if ($customerId) $this->model(Customer::class, $tenantId, $customerId, 'customer_id');
        foreach ($items as $item) $this->model(Product::class, $tenantId, $item['product_id'], 'items');
    }

    private function branch(int $tenantId, int $id): void { $this->model(Branch::class, $tenantId, $id, 'branch_id'); }
    private function model(string $class, int $tenantId, int $id, string $field): void
    {
        if (! $class::query()->whereKey($id)->where('tenant_id', $tenantId)->exists()) throw ValidationException::withMessages([$field => ['El recurso no pertenece a la empresa seleccionada.']]);
    }
}
