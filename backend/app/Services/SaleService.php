<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(private InventoryService $inventory) {}

    public function complete(Sale $sale): Sale
    {
        return DB::transaction(function () use ($sale) {
            $sale = Sale::query()->with('items')->lockForUpdate()->findOrFail($sale->id);

            if ($sale->status !== 'draft') {
                throw ValidationException::withMessages(['status' => ['Solo se pueden completar ventas en borrador.']]);
            }

            foreach ($sale->items as $item) {
                $this->inventory->remove($sale->tenant_id, $sale->branch_id, $item->product_id, (float) $item->quantity, $sale, $sale->created_by);
            }

            $sale->update(['status' => 'completed', 'sold_at' => now()]);

            return $sale;
        });
    }
}
