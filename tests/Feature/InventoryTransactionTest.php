<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for inventory transaction integrity.
 *
 * Covers:
 *   1. Inter-warehouse transfer atomically updates BOTH warehouse stocks.
 *   2. A transfer exceeding available stock is rejected and no stock changes.
 *   3. Creating a product via the web route writes an entry to audit_logs.
 */
class InventoryTransactionTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------ helpers

    private function adminUser(): User
    {
        return User::create([
            'name'      => 'Admin Test',
            'email'     => 'admin@test.local',
            'password'  => bcrypt('password'),
            'role'      => 'Admin',
            'is_active' => true,
        ]);
    }

    private function staffUser(): User
    {
        return User::create([
            'name'      => 'Staf Test',
            'email'     => 'staf@test.local',
            'password'  => bcrypt('password'),
            'role'      => 'Staf Gudang',
            'is_active' => true,
        ]);
    }

    private function category(): Category
    {
        return Category::create([
            'name'  => 'Elektronik Test',
            'color' => '#533AFD',
        ]);
    }

    private function product(Category $category, int $threshold = 5): Product
    {
        return Product::create([
            'name'              => 'Produk Test ' . uniqid(),
            'sku'               => 'SKU-' . strtoupper(substr(uniqid(), -6)),
            'category_id'       => $category->id,
            'price'             => 100000,
            'minimum_threshold' => $threshold,
            'unit'              => 'pcs',
            'is_active'         => true,
        ]);
    }

    private function warehouse(string $name = 'Gudang Test'): Warehouse
    {
        return Warehouse::create([
            'name'    => $name . ' ' . uniqid(),
            'city'    => 'Jakarta',
            'lat'     => -6.2000,
            'lng'     => 106.8167,
            'address' => 'Jl. Test No. 1',
        ]);
    }

    private function setStock(Product $product, Warehouse $warehouse, int $qty): WarehouseStock
    {
        return WarehouseStock::create([
            'product_id'   => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity'     => $qty,
        ]);
    }

    // ------------------------------------------------------------------ tests

    /**
     * Test 1: An inter-warehouse transfer correctly decrements the source
     * warehouse stock and increments the destination warehouse stock.
     * Both changes must be reflected in the database after one POST request.
     */
    public function test_transfer_updates_both_warehouse_stocks(): void
    {
        $actor    = $this->staffUser();
        $category = $this->category();
        $product  = $this->product($category);
        $src      = $this->warehouse('Asal');
        $dst      = $this->warehouse('Tujuan');

        $this->setStock($product, $src, 100);
        // Destination has no row yet — controller should create it.

        $response = $this->actingAs($actor)->post(route('transfers.store'), [
            'product_id'               => $product->id,
            'source_warehouse_id'      => $src->id,
            'destination_warehouse_id' => $dst->id,
            'quantity'                 => 30,
            'notes'                    => 'Test transfer',
        ]);

        $response->assertRedirectToRoute('transfers.index');
        $response->assertSessionHas('success');

        // Source must have 100 - 30 = 70
        $this->assertDatabaseHas('warehouse_stocks', [
            'product_id'   => $product->id,
            'warehouse_id' => $src->id,
            'quantity'     => 70,
        ]);

        // Destination must have 0 + 30 = 30
        $this->assertDatabaseHas('warehouse_stocks', [
            'product_id'   => $product->id,
            'warehouse_id' => $dst->id,
            'quantity'     => 30,
        ]);

        // A Transfer InventoryTransaction record must exist
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id'               => $product->id,
            'type'                     => 'Transfer',
            'quantity'                 => 30,
            'source_warehouse_id'      => $src->id,
            'destination_warehouse_id' => $dst->id,
            'status'                   => 'completed',
        ]);
    }

    /**
     * Test 2: A transfer requesting more stock than available is rejected.
     * Stock levels in BOTH warehouses must remain unchanged, and the database
     * must contain zero new InventoryTransaction rows for this attempt.
     */
    public function test_transfer_fails_when_insufficient_stock(): void
    {
        $actor    = $this->staffUser();
        $category = $this->category();
        $product  = $this->product($category);
        $src      = $this->warehouse('Asal Kurang');
        $dst      = $this->warehouse('Tujuan Kurang');

        $this->setStock($product, $src, 10);

        $txCountBefore = InventoryTransaction::count();

        $response = $this->actingAs($actor)->post(route('transfers.store'), [
            'product_id'               => $product->id,
            'source_warehouse_id'      => $src->id,
            'destination_warehouse_id' => $dst->id,
            'quantity'                 => 50,           // 50 > 10 — must fail
            'notes'                    => 'Test gagal',
        ]);

        // Should redirect back with a validation error on quantity
        $response->assertSessionHasErrors(['quantity']);

        // Source stock must be untouched at 10
        $this->assertDatabaseHas('warehouse_stocks', [
            'product_id'   => $product->id,
            'warehouse_id' => $src->id,
            'quantity'     => 10,
        ]);

        // Destination must have NO stock row created
        $this->assertDatabaseMissing('warehouse_stocks', [
            'product_id'   => $product->id,
            'warehouse_id' => $dst->id,
        ]);

        // No new InventoryTransaction rows must have been written
        $this->assertSame($txCountBefore, InventoryTransaction::count());
    }

    /**
     * Test 3: Submitting a new product via the web interface causes the
     * AuditLogMiddleware to write an entry to the audit_logs table, recording
     * the user ID, the model type, and the HTTP action.
     */
    public function test_creating_product_logs_to_audit_logs(): void
    {
        $actor    = $this->adminUser();
        $category = $this->category();
        $warehouse = $this->warehouse('Gudang Log');

        $auditCountBefore = AuditLog::count();

        $response = $this->actingAs($actor)->post(route('products.store'), [
            'name'              => 'Produk Audit Log Test',
            'sku'               => 'AUDIT-001',
            'category_id'       => $category->id,
            'price'             => 75000,
            'minimum_threshold' => 5,
            'unit'              => 'pcs',
            'warehouse_id'      => $warehouse->id,
            'initial_stock'     => 20,
        ]);

        $response->assertRedirectToRoute('products.index');
        $response->assertSessionHas('success');

        // AuditLogMiddleware must have written exactly one new entry
        $this->assertGreaterThan($auditCountBefore, AuditLog::count());

        // The new entry must reference the correct user and model type
        $this->assertDatabaseHas('audit_logs', [
            'user_id'    => $actor->id,
            'model_type' => 'Product',
        ]);

        // The product itself must exist in the database
        $this->assertDatabaseHas('products', [
            'name' => 'Produk Audit Log Test',
            'sku'  => 'AUDIT-001',
        ]);

        // Initial stock InventoryTransaction must have been recorded atomically
        $product = Product::where('sku', 'AUDIT-001')->firstOrFail();
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id'   => $product->id,
            'warehouse_id' => $warehouse->id,
            'type'         => 'Masuk',
            'quantity'     => 20,
            'notes'        => 'Stok awal produk',
            'status'       => 'completed',
        ]);
    }
}
