<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Invoice;
use App\Models\Inventory;
use App\Models\Sales;
use App\Models\Table;
use App\Models\ThirdPartyInvoice;
use App\Models\User;
use App\Models\WarehouseStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsArticleSalesTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;
    private Inventory $inventory;
    private User $user;
    private Table $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['username' => 'testuser']);
        $this->table = Table::create(['name' => 'Test Table', 'table_number' => 1]);

        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $this->inventory = Inventory::create([
            'category_id' => $this->category->id,
            'name' => 'Test Item',
            'price' => 500,
            'sku' => '000001',
        ]);
    }

    private function createInvoice(int $status, int $total = 500): Invoice
    {
        return Invoice::create([
            'user_id' => $this->user->id,
            'table_id' => $this->table->id,
            'status' => $status,
            'order' => [],
            'total' => $total,
        ]);
    }

    private function createSale(array $overrides = []): Sales
    {
        return Sales::create(array_merge([
            'inventory_id' => $this->inventory->id,
            'category_id' => $this->category->id,
            'category_name' => $this->category->name,
            'name' => $this->inventory->name,
            'sku' => $this->inventory->sku,
            'qty' => 1,
            'price' => 500,
            'total' => 500,
            'type' => Sales::TYPE_EPOS,
            'status' => Sales::STATUS_ACTIVE,
        ], $overrides));
    }

    public function test_normal_paid_invoice_sales_appear()
    {
        $invoice = $this->createInvoice(Invoice::STATUS_PAYED);
        $this->createSale(['invoice_id' => $invoice->id]);

        $response = $this->getJson('/api/backoffice/reports/1');

        $response->assertOk();
        $this->assertCount(1, $response->json('sales'));
        $this->assertEquals(500, $response->json('stats.total'));
    }

    public function test_refunded_invoice_sales_excluded()
    {
        $invoice = $this->createInvoice(Invoice::STATUS_REFUNDED);
        // Simulate orphaned Sales record (refund deletion failed)
        $this->createSale(['invoice_id' => $invoice->id]);

        $response = $this->getJson('/api/backoffice/reports/1');

        $response->assertOk();
        $this->assertCount(0, $response->json('sales'));
        $this->assertEquals(0, (int) $response->json('stats.total'));
    }

    public function test_normal_third_party_invoice_sales_appear()
    {
        $invoice = ThirdPartyInvoice::create([
            'invoice_number' => 'TP-001',
            'status' => ThirdPartyInvoice::STATUS_PAYED,
            'order' => [],
            'total' => 300,
            'payment_type' => ThirdPartyInvoice::PAYMENT_CASH,
        ]);

        $this->createSale([
            'invoice_id' => null,
            'batch_id' => $invoice->id,
            'type' => Sales::TYPE_EBAR,
        ]);

        $response = $this->getJson('/api/backoffice/reports/1');

        $response->assertOk();
        $this->assertCount(1, $response->json('sales'));
    }

    public function test_stornoed_third_party_invoice_sales_excluded()
    {
        $invoice = ThirdPartyInvoice::create([
            'invoice_number' => 'TP-002',
            'status' => ThirdPartyInvoice::STATUS_STORNO,
            'order' => [],
            'total' => 300,
            'payment_type' => ThirdPartyInvoice::PAYMENT_CASH,
        ]);

        // Simulate orphaned Sales record
        $this->createSale([
            'invoice_id' => null,
            'batch_id' => $invoice->id,
            'type' => Sales::TYPE_EBAR,
        ]);

        $response = $this->getJson('/api/backoffice/reports/1');

        $response->assertOk();
        $this->assertCount(0, $response->json('sales'));
        $this->assertEquals(0, (int) $response->json('stats.total'));
    }

    public function test_on_the_house_third_party_invoice_sales_appear()
    {
        $invoice = ThirdPartyInvoice::create([
            'invoice_number' => 'TP-003',
            'status' => ThirdPartyInvoice::STATUS_ON_THE_HOUSE,
            'order' => [],
            'total' => 300,
            'payment_type' => ThirdPartyInvoice::PAYMENT_KASA_I,
        ]);

        $this->createSale([
            'invoice_id' => null,
            'batch_id' => $invoice->id,
            'type' => Sales::TYPE_EBAR,
        ]);

        $response = $this->getJson('/api/backoffice/reports/1');

        $response->assertOk();
        $this->assertCount(1, $response->json('sales'));
        $this->assertEquals(500, $response->json('stats.total'));
    }

    public function test_on_the_house_pos_invoice_sales_appear()
    {
        $invoice = $this->createInvoice(Invoice::STATUS_ON_THE_HOUSE);
        $this->createSale(['invoice_id' => $invoice->id]);

        $response = $this->getJson('/api/backoffice/reports/1');

        $response->assertOk();
        $this->assertCount(1, $response->json('sales'));
    }

    public function test_sales_import_records_appear()
    {
        // SalesImport records have batch_id pointing to sales_import_details (not an invoice)
        $this->createSale([
            'invoice_id' => null,
            'batch_id' => 'fake-import-uuid',
            'type' => Sales::TYPE_EBAR,
        ]);

        $response = $this->getJson('/api/backoffice/reports/1');

        $response->assertOk();
        $this->assertCount(1, $response->json('sales'));
    }

    public function test_mixed_scenario_only_valid_sales_counted()
    {
        // Paid POS invoice - should count
        $paidInvoice = $this->createInvoice(Invoice::STATUS_PAYED);
        $this->createSale(['invoice_id' => $paidInvoice->id, 'total' => 500]);

        // Refunded POS invoice - should NOT count
        $refundedInvoice = $this->createInvoice(Invoice::STATUS_REFUNDED, 300);
        $this->createSale(['invoice_id' => $refundedInvoice->id, 'total' => 300]);

        // On-the-house ThirdParty - should count
        $onTheHouse = ThirdPartyInvoice::create([
            'invoice_number' => 'TP-MIX-1',
            'status' => ThirdPartyInvoice::STATUS_ON_THE_HOUSE,
            'order' => [],
            'total' => 200,
            'payment_type' => ThirdPartyInvoice::PAYMENT_KASA_I,
        ]);
        $this->createSale([
            'invoice_id' => null,
            'batch_id' => $onTheHouse->id,
            'type' => Sales::TYPE_EBAR,
            'total' => 200,
        ]);

        // Stornoed ThirdParty - should NOT count
        $stornoed = ThirdPartyInvoice::create([
            'invoice_number' => 'TP-MIX-2',
            'status' => ThirdPartyInvoice::STATUS_STORNO,
            'order' => [],
            'total' => 400,
            'payment_type' => ThirdPartyInvoice::PAYMENT_CASH,
        ]);
        $this->createSale([
            'invoice_id' => null,
            'batch_id' => $stornoed->id,
            'type' => Sales::TYPE_EBAR,
            'total' => 400,
        ]);

        $response = $this->getJson('/api/backoffice/reports/1');

        $response->assertOk();
        // Only paid POS + on-the-house ThirdParty should appear (same inventory_id, grouped)
        $this->assertCount(1, $response->json('sales'));
        $this->assertEquals(700, $response->json('sales.0.total')); // 500 + 200
        $this->assertEquals(700, $response->json('stats.total'));
    }

    public function test_pos_refund_deletes_sales_and_warehouse_transactionally()
    {
        $invoice = $this->createInvoice(Invoice::STATUS_PAYED);
        $this->createSale(['invoice_id' => $invoice->id]);
        WarehouseStatus::create([
            'warehouse_id' => 1,
            'inventory_id' => $this->inventory->id,
            'batch_id' => (string) $invoice->id,
            'date' => now()->format('Y-m-d'),
            'quantity' => 1,
            'type' => WarehouseStatus::TYPE_OUT,
        ]);

        $this->assertEquals(1, Sales::where('invoice_id', $invoice->id)->count());
        $this->assertEquals(1, WarehouseStatus::where('batch_id', (string) $invoice->id)->count());

        // Refund the invoice
        $this->postJson("/api/invoices/{$invoice->id}/refund", [
            'status' => Invoice::STATUS_REFUNDED,
        ]);

        // Invoice status should be refunded
        $this->assertEquals(Invoice::STATUS_REFUNDED, $invoice->fresh()->status);

        // Sales and warehouse records should both be deleted
        $this->assertEquals(0, Sales::where('invoice_id', $invoice->id)->count());
        $this->assertEquals(0, WarehouseStatus::where('batch_id', (string) $invoice->id)->count());

        // Report should not include the refunded invoice's sales
        $response = $this->getJson('/api/backoffice/reports/1');
        $response->assertOk();
        $this->assertCount(0, $response->json('sales'));
    }

    public function test_epos_ebar_breakdown_excludes_invalid_records()
    {
        // Valid epos sale
        $paidInvoice = $this->createInvoice(Invoice::STATUS_PAYED);
        $this->createSale([
            'invoice_id' => $paidInvoice->id,
            'type' => Sales::TYPE_EPOS,
            'qty' => 3,
            'total' => 1500,
        ]);

        // Valid ebar sale
        $tpInvoice = ThirdPartyInvoice::create([
            'invoice_number' => 'TP-BREAKDOWN-1',
            'status' => ThirdPartyInvoice::STATUS_PAYED,
            'order' => [],
            'total' => 1000,
            'payment_type' => ThirdPartyInvoice::PAYMENT_CASH,
        ]);
        $this->createSale([
            'invoice_id' => null,
            'batch_id' => $tpInvoice->id,
            'type' => Sales::TYPE_EBAR,
            'qty' => 2,
            'total' => 1000,
        ]);

        // Stornoed ebar sale — should NOT appear
        $stornoInvoice = ThirdPartyInvoice::create([
            'invoice_number' => 'TP-BREAKDOWN-2',
            'status' => ThirdPartyInvoice::STATUS_STORNO,
            'order' => [],
            'total' => 500,
            'payment_type' => ThirdPartyInvoice::PAYMENT_CASH,
        ]);
        $this->createSale([
            'invoice_id' => null,
            'batch_id' => $stornoInvoice->id,
            'type' => Sales::TYPE_EBAR,
            'qty' => 5,
            'total' => 500,
        ]);

        $response = $this->getJson('/api/backoffice/reports/1');

        $response->assertOk();
        $this->assertCount(1, $response->json('sales')); // grouped by inventory_id
        $this->assertEquals(3, $response->json('sales.0.epos'));
        $this->assertEquals(2, $response->json('sales.0.ebar'));
        $this->assertEquals(5, $response->json('sales.0.qty')); // 3 + 2 (stornoed 5 excluded)
        $this->assertEquals(2500, $response->json('sales.0.total')); // 1500 + 1000
        $this->assertEquals(2500, $response->json('stats.total'));
    }
}
