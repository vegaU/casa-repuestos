<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Tenant;
use App\Services\SaleService;
use App\Services\TenantDataGuard;
use App\Services\AuditService;
use App\Services\SaleCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function checkout(Request $request, Tenant $tenant, TenantDataGuard $guard, SaleCheckoutService $checkout, AuditService $audit)
    {
        $data=$request->validate(['branch_id'=>['required','integer'],'customer_id'=>['nullable','integer'],'notes'=>['nullable','string'],'items'=>['required','array','min:1'],'items.*.product_id'=>['required','integer'],'items.*.quantity'=>['required','numeric','gt:0'],'items.*.unit_price'=>['required','numeric','min:0'],'items.*.discount_amount'=>['nullable','numeric','min:0'],'payment'=>['nullable','array'],'payment.method'=>['required_with:payment','in:cash,card,transfer'],'payment.amount'=>['nullable','numeric','gt:0'],'payment.tendered_amount'=>['nullable','numeric','gt:0'],'payment.reference'=>['nullable','string','max:255'],'payment.settle_full'=>['nullable','boolean']]);
        $guard->sale($tenant->id,$data['branch_id'],$data['customer_id']??null,$data['items']);
        $result=$checkout->checkout($tenant,$request->user(),$data);
        $audit->record($request,$tenant->id,'sale.checkout',$result['sale'],[],['total'=>$result['total'],'paid_amount'=>$result['paid_amount'],'change_amount'=>$result['change_amount']]);
        return response()->json(['data'=>$result],201);
    }

    public function index(Request $request, Tenant $tenant)
    {
        $sales = $tenant->sales()->with(['customer','branch'])->latest();
        if ($request->filled('status')) $sales->where('status', $request->string('status'));
        if ($request->filled('customer_id')) $sales->where('customer_id', $request->integer('customer_id'));
        return ['data' => $sales->paginate(20)];
    }

    public function store(Request $request, Tenant $tenant, TenantDataGuard $guard, AuditService $audit)
    {
        $data=$request->validate(['branch_id'=>['required','integer'],'customer_id'=>['nullable','integer'],'notes'=>['nullable','string'],'items'=>['required','array','min:1'],'items.*.product_id'=>['required','integer'],'items.*.quantity'=>['required','numeric','gt:0'],'items.*.unit_price'=>['required','numeric','min:0'],'items.*.discount_amount'=>['nullable','numeric','min:0']]);
        $guard->sale($tenant->id,$data['branch_id'],$data['customer_id']??null,$data['items']);
        $sale=DB::transaction(function() use($data,$tenant,$request) {
            // Lock the tenant row first; PostgreSQL does not support locking MAX().
            Tenant::query()->lockForUpdate()->findOrFail($tenant->id);
            $last = Sale::query()->where('tenant_id',$tenant->id)->max('id') ?? 0;
            $sale=Sale::create(['tenant_id'=>$tenant->id,'branch_id'=>$data['branch_id'],'customer_id'=>$data['customer_id']??null,'sale_number'=>sprintf('V-%06d',$last + 1),'notes'=>$data['notes']??null,'created_by'=>$request->user()->id]);
            foreach($data['items'] as $item) { $discount=(float)($item['discount_amount']??0); $sale->items()->create($item+['discount_amount'=>$discount,'line_total'=>($item['quantity']*$item['unit_price'])-$discount]); }
            $subtotal=$sale->items()->sum(DB::raw('quantity * unit_price'));
            $discount=$sale->items()->sum('discount_amount');
            $sale->update(['subtotal'=>$subtotal,'discount_total'=>$discount,'total'=>$subtotal-$discount]);
            return $sale;
        });
        $audit->record($request, $tenant->id, 'sale.created', $sale, [], ['total'=>$sale->total,'items_count'=>$sale->items()->count()]);
        return response()->json(['data'=>$sale->load('items')],201);
    }

    public function show(Tenant $tenant, Sale $sale)
    {
        abort_unless($sale->tenant_id === $tenant->id, 404);
        return ['data'=>$sale->load(['items.product','customer','branch','payments'])];
    }

    public function complete(Request $request, Tenant $tenant, Sale $sale, SaleService $service, AuditService $audit)
    {
        abort_unless($sale->tenant_id === $tenant->id, 404);
        $completed=$service->complete($sale);
        $audit->record($request, $tenant->id, 'sale.completed', $completed, ['status'=>'draft'], ['status'=>'completed']);
        return ['data'=>$completed];
    }
}
