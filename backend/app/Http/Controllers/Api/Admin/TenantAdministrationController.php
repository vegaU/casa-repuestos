<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TenantAdministrationController extends Controller
{
    public function index(Request $request)
    {
        $tenants = Tenant::query()->withCount(['branches','users'])->orderBy('name');
        if ($search = $request->string('search')->toString()) $tenants->where(fn ($query) => $query->where('name','ilike',"%$search%")->orWhere('tax_id','ilike',"%$search%"));
        if ($request->has('is_active') && $request->input('is_active') !== '') $tenants->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        return ['data' => $tenants->paginate(20)];
    }

    public function show(Tenant $tenant)
    {
        return ['data' => $tenant->load(['branches','users:id,name,email,is_super_admin'])];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company.name' => ['required','string','max:255'],
            'company.tax_id' => ['nullable','string','max:20','unique:tenants,tax_id'],
            'company.phone' => ['nullable','string','max:30'],
            'company.email' => ['nullable','email','max:255'],
            'company.address' => ['nullable','string','max:255'],
            'company.is_active' => ['required','boolean'],
            'administrator.name' => ['required','string','max:255'],
            'administrator.email' => ['required','email','max:255'],
            'administrator.password' => ['required','string','min:10','max:255'],
            'branch.code' => ['required','string','max:20'],
            'branch.name' => ['required','string','max:255'],
            'branch.address' => ['nullable','string','max:255'],
            'branch.phone' => ['nullable','string','max:30'],
            'branch.is_active' => ['required','boolean'],
        ]);
        $tenant = DB::transaction(function () use ($data, $request) {
            $company = $data['company'];
            $tenant = Tenant::create($company);
            $tenant->branches()->create($data['branch']);
            $administrator = User::query()->where('email', $data['administrator']['email'])->lockForUpdate()->first();
            if (! $administrator) {
                $administrator = User::create(['name'=>$data['administrator']['name'], 'email'=>$data['administrator']['email'], 'password'=>Hash::make($data['administrator']['password']), 'must_change_password'=>true]);
            }
            $tenant->users()->syncWithoutDetaching([$administrator->id => ['role'=>'tenant_admin','is_active'=>true]]);
            AuditLog::create(['actor_id'=>$request->user()->id,'tenant_id'=>$tenant->id,'action'=>'tenant.created','metadata'=>['administrator_id'=>$administrator->id],'ip_address'=>$request->ip()]);
            return $tenant;
        });
        return response()->json(['data'=>$tenant->load(['branches','users:id,name,email'])],201);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate(['name'=>['sometimes','string','max:255'],'tax_id'=>['nullable','string','max:20',Rule::unique('tenants','tax_id')->ignore($tenant->id)],'phone'=>['nullable','string','max:30'],'email'=>['nullable','email','max:255'],'address'=>['nullable','string','max:255'],'is_active'=>['boolean']]);
        $tenant->update($data);
        AuditLog::create(['actor_id'=>$request->user()->id,'tenant_id'=>$tenant->id,'action'=>'tenant.updated','metadata'=>array_keys($data),'ip_address'=>$request->ip()]);
        return ['data'=>$tenant];
    }

    public function assignAdministrator(Request $request, Tenant $tenant)
    {
        $data=$request->validate(['email'=>['required','email']]);
        $user=User::where('email',$data['email'])->firstOrFail();
        $tenant->users()->syncWithoutDetaching([$user->id=>['role'=>'tenant_admin','is_active'=>true]]);
        AuditLog::create(['actor_id'=>$request->user()->id,'tenant_id'=>$tenant->id,'action'=>'tenant.administrator_assigned','metadata'=>['user_id'=>$user->id],'ip_address'=>$request->ip()]);
        return ['data'=>$tenant->load('users:id,name,email,is_super_admin')];
    }

    public function removeAdministrator(Request $request, Tenant $tenant, User $user)
    {
        $tenant->users()->updateExistingPivot($user->id,['is_active'=>false]);
        AuditLog::create(['actor_id'=>$request->user()->id,'tenant_id'=>$tenant->id,'action'=>'tenant.administrator_removed','metadata'=>['user_id'=>$user->id],'ip_address'=>$request->ip()]);
        return response()->json(status:204);
    }

    public function support(Request $request, Tenant $tenant)
    {
        AuditLog::create(['actor_id'=>$request->user()->id,'tenant_id'=>$tenant->id,'action'=>'support.context_entered','ip_address'=>$request->ip()]);
        return ['data'=>['tenant_id'=>$tenant->id,'tenant_name'=>$tenant->name]];
    }
}
