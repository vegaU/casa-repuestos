<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Models\Supplier; use App\Models\Tenant; use Illuminate\Http\Request;
class SupplierController extends Controller { public function index(Tenant $tenant) { return ['data'=>$tenant->suppliers()->orderBy('name')->get()]; } public function store(Request $r,Tenant $t) { $d=$r->validate(['name'=>['required','string','max:255'],'legal_name'=>['nullable','string'],'tax_id'=>['nullable','string','max:20'],'email'=>['nullable','email'],'phone'=>['nullable','string','max:30'],'address'=>['nullable','string'],'contact_name'=>['nullable','string'],'is_active'=>['boolean']]); $d['tenant_id']=$t->id; return response()->json(['data'=>Supplier::create($d)],201); } }
