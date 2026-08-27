<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Tenant;
use App\Services\PurchaseService;
use App\Services\TenantDataGuard;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function store(Request $request, Tenant $tenant, TenantDataGuard $guard, AuditService $audit)
    {
        $data = $request->validate(['branch_id'=>['required','integer'],'supplier_id'=>['nullable','integer'],'supplier_document_number'=>['nullable','string'],'items'=>['required','array','min:1'],'items.*.product_id'=>['required','integer'],'items.*.quantity'=>['required','numeric','gt:0'],'items.*.unit_cost'=>['required','numeric','min:0']]);
        $guard->purchase($tenant->id, $data['branch_id'], $data['supplier_id'] ?? null, $data['items']);
        $purchase = DB::transaction(function () use ($data, $tenant, $request) {
            $purchase = Purchase::create(['tenant_id'=>$tenant->id,'branch_id'=>$data['branch_id'],'supplier_id'=>$data['supplier_id']??null,'supplier_document_number'=>$data['supplier_document_number']??null,'created_by'=>$request->user()->id]);
            foreach ($data['items'] as $item) $purchase->items()->create($item + ['line_total'=>$item['quantity']*$item['unit_cost']]);
            $total = $purchase->items()->sum('line_total');
            $purchase->update(['subtotal'=>$total,'total'=>$total]);
            return $purchase;
        });
        $audit->record($request, $tenant->id, 'purchase.created', $purchase, [], ['total'=>$purchase->total,'items_count'=>$purchase->items()->count()]);
        return response()->json(['data'=>$purchase->load('items')],201);
    }

    public function receive(Request $request, Tenant $tenant, Purchase $purchase, PurchaseService $service, AuditService $audit)
    {
        abort_unless($purchase->tenant_id === $tenant->id, 404);
        $received=$service->receive($purchase);
        $audit->record($request, $tenant->id, 'purchase.received', $received, ['status'=>'draft'], ['status'=>'received']);
        return ['data'=>$received];
    }
}
