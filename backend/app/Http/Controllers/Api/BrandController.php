<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Tenant;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Tenant $tenant) { return ['data' => $tenant->brands()->orderBy('name')->get()]; }
    public function store(Request $request, Tenant $tenant) { $data = $request->validate(['name'=>['required','string','max:255'],'description'=>['nullable','string'],'is_active'=>['boolean']]); $data['tenant_id'] = $tenant->id; return response()->json(['data'=>Brand::create($data)], 201); }
    public function update(Request $request, Tenant $tenant, Brand $brand) { abort_unless($brand->tenant_id === $tenant->id, 404); $brand->update($request->validate(['name'=>['sometimes','string','max:255'],'description'=>['nullable','string'],'is_active'=>['boolean']])); return ['data'=>$brand]; }
}
