<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_tenant(): void
    {
        [$first, $second] = $this->tenants();
        $user = User::factory()->create();
        $first->users()->attach($user, ['role' => 'viewer', 'is_active' => true]);
        Sanctum::actingAs($user);

        $this->getJson("/api/tenants/{$second->id}/categories")->assertForbidden();
    }

    public function test_role_permission_is_enforced_by_the_backend(): void
    {
        [$tenant] = $this->tenants();
        $user = User::factory()->create();
        $tenant->users()->attach($user, ['role' => 'seller', 'is_active' => true]);
        Sanctum::actingAs($user);

        $this->getJson("/api/tenants/{$tenant->id}/categories")->assertOk();
        $this->postJson("/api/tenants/{$tenant->id}/categories", ['name' => 'Motor'])->assertForbidden();
    }

    public function test_product_rejects_category_from_another_tenant(): void
    {
        [$first, $second] = $this->tenants();
        $category = Category::create(['tenant_id' => $second->id, 'name' => 'Ajena']);
        $user = User::factory()->create();
        $first->users()->attach($user, ['role' => 'tenant_admin', 'is_active' => true]);
        Sanctum::actingAs($user);

        $this->postJson("/api/tenants/{$first->id}/products", ['name' => 'Filtro', 'category_id' => $category->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category_id');
    }

    public function test_inactive_branch_cannot_receive_operations(): void
    {
        [$tenant] = $this->tenants();
        $branch = Branch::create(['tenant_id' => $tenant->id, 'code' => 'OFF', 'name' => 'Inactiva', 'is_active' => false]);
        $user = User::factory()->create();
        $tenant->users()->attach($user, ['role' => 'warehouse', 'is_active' => true]);
        $product = Product::create(['tenant_id' => $tenant->id, 'name' => 'Filtro']);
        Sanctum::actingAs($user);

        $this->postJson("/api/tenants/{$tenant->id}/purchases", ['branch_id' => $branch->id, 'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_cost' => 1]]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('branch_id');
    }

    /** @return array{Tenant,Tenant} */
    private function tenants(): array
    {
        $first = Tenant::create(['name' => 'Uno', 'tax_id' => '100']);
        $second = Tenant::create(['name' => 'Dos', 'tax_id' => '200']);
        Branch::create(['tenant_id' => $first->id, 'code' => 'MAIN', 'name' => 'Principal']);
        Branch::create(['tenant_id' => $second->id, 'code' => 'MAIN', 'name' => 'Principal']);
        return [$first, $second];
    }
}
