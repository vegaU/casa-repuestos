<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function add(int $tenantId, int $branchId, int $productId, float $quantity, ?float $unitCost, Model $reference, ?int $userId, string $type = 'purchase'): void
    {
        $inventory = $this->lockedInventory($branchId, $productId);
        $inventory->increment('quantity', $quantity);

        $this->movement($tenantId, $branchId, $productId, $type, $quantity, $unitCost, $reference, $userId);
    }

    public function remove(int $tenantId, int $branchId, int $productId, float $quantity, Model $reference, ?int $userId, string $type = 'sale'): void
    {
        $inventory = $this->lockedInventory($branchId, $productId);
        $available = (float) $inventory->quantity - (float) $inventory->reserved_quantity;

        if ($available < $quantity) {
            throw ValidationException::withMessages([
                'items' => ['No hay stock disponible suficiente para completar la venta.'],
            ]);
        }

        $inventory->decrement('quantity', $quantity);

        $this->movement($tenantId, $branchId, $productId, $type, -$quantity, null, $reference, $userId);
    }

    private function lockedInventory(int $branchId, int $productId): Inventory
    {
        $inventory = Inventory::query()
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if (! $inventory) {
            Inventory::create([
                'branch_id' => $branchId,
                'product_id' => $productId,
                'quantity' => 0,
                'reserved_quantity' => 0,
            ]);

            $inventory = Inventory::query()
                ->where('branch_id', $branchId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->firstOrFail();
        }

        return $inventory;
    }

    private function movement(int $tenantId, int $branchId, int $productId, string $type, float $quantity, ?float $unitCost, Model $reference, ?int $userId): void
    {
        StockMovement::create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'product_id' => $productId,
            'movement_type' => $type,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'reference_type' => $reference::class,
            'reference_id' => $reference->getKey(),
            'created_by' => $userId,
        ]);
    }
}
