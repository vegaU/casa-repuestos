<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Tenant;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    private function rules(): array { return ['name'=>['required','string','max:255'],'legal_name'=>['nullable','string'],'tax_id'=>['nullable','string','max:20'],'email'=>['nullable','email'],'phone'=>['nullable','string','max:30'],'address'=>['nullable','string'],'contact_name'=>['nullable','string'],'is_active'=>['boolean']]; }
    public function index(Request $request, Tenant $tenant) { $suppliers=$tenant->suppliers()->orderBy('name'); if($search=$request->string('search')->toString()) $suppliers->where(fn($query)=>$query->where('name','ilike',"%$search%")->orWhere('tax_id','ilike',"%$search%")); return ['data'=>$suppliers->paginate(20)]; }
    public function store(Request $request, Tenant $tenant) { $data=$request->validate($this->rules()); $data['tenant_id']=$tenant->id; return response()->json(['data'=>Supplier::create($data)],201); }
    public function show(Tenant $tenant, Supplier $supplier) { abort_unless($supplier->tenant_id===$tenant->id,404); return ['data'=>$supplier]; }
    public function update(Request $request, Tenant $tenant, Supplier $supplier) { abort_unless($supplier->tenant_id===$tenant->id,404); $supplier->update($request->validate(array_map(fn($rules)=>array_merge(['sometimes'],$rules),$this->rules()))); return ['data'=>$supplier]; }
}
