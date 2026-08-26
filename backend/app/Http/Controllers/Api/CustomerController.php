<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private function rules(): array { return ['name'=>['required','string','max:255'],'legal_name'=>['nullable','string'],'tax_id'=>['nullable','string','max:20'],'email'=>['nullable','email'],'phone'=>['nullable','string','max:30'],'address'=>['nullable','string'],'is_active'=>['boolean']]; }
    public function index(Request $request, Tenant $tenant) { $customers=$tenant->hasMany(Customer::class)->orderBy('name'); if($search=$request->string('search')->toString()) $customers->where(fn($query)=>$query->where('name','ilike',"%$search%")->orWhere('tax_id','ilike',"%$search%")); return ['data'=>$customers->paginate(20)]; }
    public function store(Request $request, Tenant $tenant) { $data=$request->validate($this->rules()); $data['tenant_id']=$tenant->id; return response()->json(['data'=>Customer::create($data)],201); }
    public function show(Tenant $tenant, Customer $customer) { abort_unless($customer->tenant_id===$tenant->id,404); return ['data'=>$customer]; }
    public function update(Request $request, Tenant $tenant, Customer $customer) { abort_unless($customer->tenant_id===$tenant->id,404); $customer->update($request->validate(array_map(fn($rules)=>array_merge(['sometimes'],$rules),$this->rules()))); return ['data'=>$customer]; }
}
