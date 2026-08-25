<?php

namespace App\Services;

use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(private InventoryService $inventory) {}

    public function receive(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase) {
            $purchase = Purchase::query()->with('items')->lockForUpdate()->findOrFail($purchase->id);

            if ($purchase->status !== 'draft') {
                throw ValidationException::withMessages(['status' => ['Solo se pueden recibir compras en borrador.']]);
            }

            foreach ($purchase->items as $item) {
                $this->inventory->add($purchase->tenant_id, $purchase->branch_id, $item->product_id, (float) $item->quantity, (float) $item->unit_cost, $purchase, $purchase->created_by);
            }

            $purchase->update(['status' => 'received', 'purchased_at' => now()]);

            return $purchase;
        });
    }
}
