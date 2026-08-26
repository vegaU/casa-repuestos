<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\Tenant;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request, Tenant $tenant)
    {
        $inventory = Inventory::query()->with(['product.brand','product.category','branch'])->whereHas('branch', fn ($query) => $query->where('tenant_id', $tenant->id));
        if ($request->filled('branch_id')) $inventory->where('branch_id', $request->integer('branch_id'));
        return ['data' => $inventory->orderBy('product_id')->get()];
    }

    public function movements(Request $request, Tenant $tenant)
    {
        $movements = StockMovement::query()->with(['product','branch','creator'])->where('tenant_id',$tenant->id)->latest('occurred_at');
        if ($request->filled('branch_id')) $movements->where('branch_id', $request->integer('branch_id'));
        return ['data' => $movements->paginate(30)];
    }
}
