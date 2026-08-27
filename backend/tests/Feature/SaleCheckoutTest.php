<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SaleCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_completes_sale_updates_stock_and_calculates_cash_change(): void
    {
        [$tenant, $branch, $product] = $this->saleContext(5);

        $response = $this->postJson("/api/tenants/{$tenant->id}/sales/checkout", [
            'branch_id' => $branch->id,
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 12000, 'discount_amount' => 1000]],
            'payment' => ['method' => 'cash', 'tendered_amount' => 30000, 'settle_full' => true],
        ])->assertCreated();

        $response->assertJsonPath('data.sale.status', 'completed')
            ->assertJsonPath('data.total', '23000.00')
            ->assertJsonPath('data.change_amount', '7000.00');
        $this->assertDatabaseHas('sales', ['tenant_id' => $tenant->id, 'status' => 'completed', 'total' => 23000]);
        $this->assertDatabaseHas('payments', ['tenant_id' => $tenant->id, 'amount' => 23000, 'tendered_amount' => 30000, 'change_amount' => 7000]);
        $this->assertSame('3.000', Inventory::where('branch_id', $branch->id)->where('product_id', $product->id)->value('quantity'));
    }

    public function test_checkout_rolls_back_sale_when_stock_is_insufficient(): void
    {
        [$tenant, $branch, $product] = $this->saleContext(1);

        $this->postJson("/api/tenants/{$tenant->id}/sales/checkout", [
            'branch_id' => $branch->id,
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 12000]],
            'payment' => ['method' => 'cash', 'tendered_amount' => 24000, 'settle_full' => true],
        ])->assertUnprocessable()->assertJsonValidationErrors('items');

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertSame('1.000', Inventory::where('branch_id', $branch->id)->where('product_id', $product->id)->value('quantity'));
    }

    /** @return array{Tenant, Branch, Product} */
    private function saleContext(int $stock): array
    {
        $tenant = Tenant::create(['name' => 'Repuestos test', 'tax_id' => '80012345']);
        $branch = Branch::create(['tenant_id' => $tenant->id, 'code' => 'MAIN', 'name' => 'Principal']);
        $product = Product::create(['tenant_id' => $tenant->id, 'name' => 'Filtro', 'sale_price' => 12000]);
        Inventory::create(['branch_id' => $branch->id, 'product_id' => $product->id, 'quantity' => $stock, 'reserved_quantity' => 0]);
        $user = User::factory()->create();
        $tenant->users()->attach($user, ['role' => 'seller', 'is_active' => true]);
        Sanctum::actingAs($user);
        return [$tenant, $branch, $product];
    }
}
