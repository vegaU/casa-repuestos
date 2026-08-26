<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Tenant;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, Tenant $tenant, Sale $sale)
    {
        abort_unless($sale->tenant_id === $tenant->id, 404);
        $data=$request->validate(['amount'=>['required','numeric','gt:0'],'method'=>['required','string','max:30'],'reference'=>['nullable','string'],'paid_at'=>['nullable','date']]);
        if ((float) $data['amount'] > (float) $sale->balance) abort(422,'El pago supera el saldo pendiente.');
        $data += ['tenant_id'=>$tenant->id,'created_by'=>$request->user()->id];
        $payment=$sale->payments()->create($data);
        return response()->json(['data'=>$payment,'paid_amount'=>$sale->fresh()->paid_amount,'balance'=>$sale->fresh()->balance],201);
    }
}
