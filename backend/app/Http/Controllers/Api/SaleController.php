<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Tenant;
use App\Services\SaleService;
use App\Services\TenantDataGuard;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function store(Request $request, Tenant $tenant, TenantDataGuard $guard, AuditService $audit)
    {
        $data=$request->validate(['branch_id'=>['required','integer'],'customer_id'=>['nullable','integer'],'items'=>['required','array','min:1'],'items.*.product_id'=>['required','integer'],'items.*.quantity'=>['required','numeric','gt:0'],'items.*.unit_price'=>['required','numeric','min:0']]);
        $guard->sale($tenant->id,$data['branch_id'],$data['customer_id']??null,$data['items']);
        $sale=DB::transaction(function() use($data,$tenant,$request) {
            $sale=Sale::create(['tenant_id'=>$tenant->id,'branch_id'=>$data['branch_id'],'customer_id'=>$data['customer_id']??null,'created_by'=>$request->user()->id]);
            foreach($data['items'] as $item) $sale->items()->create($item+['line_total'=>$item['quantity']*$item['unit_price']]);
            $total=$sale->items()->sum('line_total');
            $sale->update(['subtotal'=>$total,'total'=>$total]);
            return $sale;
        });
        $audit->record($request, $tenant->id, 'sale.created', $sale, [], ['total'=>$sale->total,'items_count'=>$sale->items()->count()]);
        return response()->json(['data'=>$sale->load('items')],201);
    }

    public function complete(Request $request, Tenant $tenant, Sale $sale, SaleService $service, AuditService $audit)
    {
        abort_unless($sale->tenant_id === $tenant->id, 404);
        $completed=$service->complete($sale);
        $audit->record($request, $tenant->id, 'sale.completed', $completed, ['status'=>'draft'], ['status'=>'completed']);
        return ['data'=>$completed];
    }
}
