<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Tenant;
use App\Services\InventoryService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancellationController extends Controller
{
    public function purchase(Request $request, Tenant $tenant, Purchase $purchase, InventoryService $inventory, AuditService $audit)
    {
        abort_unless($purchase->tenant_id === $tenant->id, 404);
        $reason = $request->validate(['reason' => ['required', 'string', 'max:1000']])['reason'];

        $cancelled = DB::transaction(function () use ($purchase, $reason, $inventory) {
            $purchase = Purchase::with('items')->lockForUpdate()->findOrFail($purchase->id);
            if ($purchase->status !== 'received') throw ValidationException::withMessages(['status' => ['Solo se pueden anular compras recibidas.']]);
            foreach ($purchase->items as $item) $inventory->remove($purchase->tenant_id, $purchase->branch_id, $item->product_id, (float) $item->quantity, $purchase, $purchase->created_by, 'purchase_cancel');
            $purchase->update(['status' => 'cancelled', 'cancellation_reason' => $reason]);
            return $purchase;
        });
        $audit->record($request, $tenant->id, 'purchase.cancelled', $cancelled, ['status'=>'received'], ['status'=>'cancelled','reason'=>$reason]);
        return ['data' => $cancelled];
    }

    public function sale(Request $request, Tenant $tenant, Sale $sale, InventoryService $inventory, AuditService $audit)
    {
        abort_unless($sale->tenant_id === $tenant->id, 404);
        $reason = $request->validate(['reason' => ['required', 'string', 'max:1000']])['reason'];

        $cancelled = DB::transaction(function () use ($sale, $reason, $inventory) {
            $sale = Sale::with('items')->lockForUpdate()->findOrFail($sale->id);
            if ($sale->status !== 'completed') throw ValidationException::withMessages(['status' => ['Solo se pueden anular ventas completadas.']]);
            foreach ($sale->items as $item) $inventory->add($sale->tenant_id, $sale->branch_id, $item->product_id, (float) $item->quantity, null, $sale, $sale->created_by, 'sale_cancel');
            $sale->update(['status' => 'cancelled', 'cancellation_reason' => $reason]);
            return $sale;
        });
        $audit->record($request, $tenant->id, 'sale.cancelled', $cancelled, ['status'=>'completed'], ['status'=>'cancelled','reason'=>$reason]);
        return ['data' => $cancelled];
    }
}
