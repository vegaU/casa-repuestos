<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleCheckoutService
{
    public function __construct(private SaleService $sales) {}

    public function checkout(Tenant $tenant, User $user, array $data): array
    {
        return DB::transaction(function () use ($tenant, $user, $data) {
            Tenant::query()->lockForUpdate()->findOrFail($tenant->id);
            // The tenant row is already locked above, serializing sale numbers per tenant.
            // PostgreSQL does not allow FOR UPDATE on aggregate queries.
            $next = Sale::query()->where('tenant_id', $tenant->id)->count() + 1;
            $sale = Sale::create(['tenant_id'=>$tenant->id,'branch_id'=>$data['branch_id'],'customer_id'=>$data['customer_id']??null,'sale_number'=>sprintf('V-%06d',$next),'notes'=>$data['notes']??null,'created_by'=>$user->id]);
            $subtotal = '0.00';
            $discount = '0.00';
            foreach ($data['items'] as $item) {
                $lineDiscount = (string) ($item['discount_amount'] ?? 0);
                $lineTotal = bcsub(bcmul((string)$item['quantity'], (string)$item['unit_price'], 2), $lineDiscount, 2);
                if (bccomp($lineTotal, '0', 2) < 0) throw ValidationException::withMessages(['items'=>['El descuento no puede superar el importe de la línea.']]);
                $sale->items()->create($item + ['discount_amount'=>$lineDiscount,'line_total'=>$lineTotal]);
                $subtotal = bcadd($subtotal, bcmul((string)$item['quantity'], (string)$item['unit_price'], 2), 2);
                $discount = bcadd($discount, $lineDiscount, 2);
            }
            $total = bcsub($subtotal, $discount, 2);
            $sale->update(['subtotal'=>$subtotal,'discount_total'=>$discount,'total'=>$total]);
            $sale = $this->sales->complete($sale);
            $payment = null;
            $tendered = '0.00';
            $change = '0.00';
            if (!empty($data['payment'])) {
                $paymentData = $data['payment'];
                $method = $paymentData['method'];
                $requested = (string) ($paymentData['amount'] ?? $total);
                if (bccomp($requested, $total, 2) > 0 && $method !== 'cash') throw ValidationException::withMessages(['payment.amount'=>['El pago no puede superar el saldo.']]);
                if ($method === 'cash') {
                    $tendered = (string) ($paymentData['tendered_amount'] ?? $requested);
                    if (!empty($paymentData['settle_full']) && bccomp($tendered, $total, 2) < 0) throw ValidationException::withMessages(['payment.tendered_amount'=>['El efectivo recibido no alcanza para cobrar el total.']]);
                    $requested = bccomp($tendered, $total, 2) > 0 ? $total : $tendered;
                    $change = bcsub($tendered, $requested, 2);
                }
                $payment = $sale->payments()->create(['tenant_id'=>$tenant->id,'amount'=>$requested,'tendered_amount'=>$method==='cash'?$tendered:null,'change_amount'=>$method==='cash'?$change:0,'method'=>$method,'reference'=>$paymentData['reference']??null,'created_by'=>$user->id]);
            }
            $sale = $sale->fresh(['items.product','customer','branch','payments']);
            return ['sale'=>$sale,'payment'=>$payment,'total'=>$sale->total,'paid_amount'=>$sale->paid_amount,'tendered_amount'=>$tendered,'change_amount'=>$change,'balance'=>$sale->balance];
        });
    }
}
