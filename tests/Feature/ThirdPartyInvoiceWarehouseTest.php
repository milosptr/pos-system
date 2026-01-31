<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\ThirdPartyInvoice;
use App\Models\Warehouse;
use App\Models\WarehouseCategory;
use App\Models\WarehouseInventory;
use App\Models\WarehouseStatus;
use App\Http\Middleware\VerifyExternalApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThirdPartyInvoiceWarehouseTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;
    private WarehouseCategory $warehouseCategory;
    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        // Bypass API key middleware for tests
        $this->withoutMiddleware(VerifyExternalApiKey::class);

        // Create base category for inventory items
        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        // Create warehouse category and warehouse for stock tracking
        $this->warehouseCategory = WarehouseCategory::create([
            'name' => 'Test Warehouse Category',
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Test Warehouse',
            'unit' => 'kom',
            'category_id' => $this->warehouseCategory->id,
        ]);
    }

    /**
     * Helper to create inventory item with warehouse link.
     */
    private function createInventoryWithWarehouse(string $name, float $norm = 1.0): Inventory
    {
        $inventory = Inventory::create([
            'category_id' => $this->category->id,
            'name' => $name,
            'price' => 100,
            'sku' => str_pad(rand(1, 99999), 6, '0', STR_PAD_LEFT),
        ]);

        WarehouseInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'inventory_id' => $inventory->id,
            'norm' => $norm,
        ]);

        return $inventory;
    }

    /**
     * Test that inventory is matched by sifraArtikla (external system ID).
     */
    public function test_matches_inventory_by_sifra_artikla()
    {
        // Create inventory
        $inventory = $this->createInventoryWithWarehouse('Sljiv Bukovo');

        $requestData = [
            [
                'kolicina' => 2,
                'cena' => 240,
                'naziv' => 'Some different name', // Name doesn't match
                'jm' => 'kom',
                'gotovina' => 480,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => '12345',
                'sto' => '6',
                'porudzbinaid' => 99800,
                'sifraArtikla' => $inventory->id, // Use actual ID
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        // Verify invoice was created with inventory_id in order JSON
        $invoice = ThirdPartyInvoice::first();
        $this->assertNotNull($invoice);
        $this->assertEquals($inventory->id, $invoice->order[0]['inventory_id']);

        // Verify warehouse status was created
        $warehouseStatus = WarehouseStatus::where('batch_id', $invoice->id)->first();
        $this->assertNotNull($warehouseStatus);
        $this->assertEquals($inventory->id, $warehouseStatus->inventory_id);
        $this->assertEquals(WarehouseStatus::TYPE_OUT, $warehouseStatus->type);
        $this->assertEquals(2, $warehouseStatus->quantity); // qty * norm (2 * 1.0)
        $this->assertEquals('Sale from Third Party System', $warehouseStatus->comment);
    }

    /**
     * Test that inventory is matched by name when sifraArtikla doesn't match.
     */
    public function test_matches_inventory_by_name_fallback()
    {
        // Create inventory
        $inventory = $this->createInventoryWithWarehouse('Coca cola 0.25');

        $requestData = [
            [
                'kolicina' => 2,
                'cena' => 210,
                'naziv' => 'Coca cola 0.25', // Exact name match
                'jm' => 'kom',
                'gotovina' => 420,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => '12346',
                'sto' => '6',
                'porudzbinaid' => 99801,
                'sifraArtikla' => 99999, // Non-existent ID
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);

        $response->assertStatus(201);

        $invoice = ThirdPartyInvoice::first();
        $this->assertEquals($inventory->id, $invoice->order[0]['inventory_id']);

        // Verify warehouse deduction happened
        $warehouseStatus = WarehouseStatus::where('batch_id', $invoice->id)->first();
        $this->assertNotNull($warehouseStatus);
        $this->assertEquals($inventory->id, $warehouseStatus->inventory_id);
    }

    /**
     * Test case-insensitive name matching.
     */
    public function test_matches_inventory_by_name_case_insensitive()
    {
        $inventory = $this->createInventoryWithWarehouse('Kafa');

        $requestData = [
            [
                'kolicina' => 1,
                'cena' => 120,
                'naziv' => 'KAFA', // Uppercase
                'jm' => 'kom',
                'gotovina' => 120,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => '12347',
                'sto' => '1',
                'porudzbinaid' => 99802,
                'sifraArtikla' => 99999, // Non-existent
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);

        $response->assertStatus(201);

        $invoice = ThirdPartyInvoice::first();
        $this->assertEquals($inventory->id, $invoice->order[0]['inventory_id']);
    }

    /**
     * Test that unmatched items don't create warehouse records.
     */
    public function test_unmatched_items_no_warehouse_deduction()
    {
        // Don't create any matching inventory

        $requestData = [
            [
                'kolicina' => 1,
                'cena' => 500,
                'naziv' => 'Non-existent Item',
                'jm' => 'kom',
                'gotovina' => 500,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => '12348',
                'sto' => '1',
                'porudzbinaid' => 99803,
                'sifraArtikla' => 8888, // Non-existent
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);

        $response->assertStatus(201);

        $invoice = ThirdPartyInvoice::first();
        $this->assertNotNull($invoice);

        // Verify no inventory_id in order JSON
        $this->assertArrayNotHasKey('inventory_id', $invoice->order[0]);

        // Verify no warehouse status created
        $this->assertEquals(0, WarehouseStatus::count());
    }

    /**
     * Test that storno invoices don't create warehouse deductions.
     */
    public function test_storno_invoice_no_warehouse_deduction()
    {
        $inventory = $this->createInventoryWithWarehouse('Cevapi');

        $requestData = [
            [
                'kolicina' => 2,
                'cena' => 1050,
                'naziv' => 'Cevapi',
                'jm' => 'kom',
                'gotovina' => 0,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => '12349',
                'sto' => '6',
                'porudzbinaid' => 99804,
                'sifraArtikla' => $inventory->id,
                'stornoporudzbine' => 1, // Storno flag
                'originalnacena' => 1050,
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);

        $response->assertStatus(201);

        $invoice = ThirdPartyInvoice::first();
        $this->assertEquals(ThirdPartyInvoice::STATUS_STORNO, $invoice->status);

        // Inventory should still be matched and stored
        $this->assertEquals($inventory->id, $invoice->order[0]['inventory_id']);

        // But NO warehouse status should be created for storno
        $this->assertEquals(0, WarehouseStatus::count());
    }

    /**
     * Test that delete removes both invoice and warehouse records.
     */
    public function test_delete_removes_invoice_and_warehouse_status()
    {
        $inventory = $this->createInventoryWithWarehouse('Pogaca');

        // First create an invoice
        $requestData = [
            [
                'kolicina' => 2,
                'cena' => 210,
                'naziv' => 'Pogaca',
                'jm' => 'kom',
                'gotovina' => 420,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => '12350',
                'sto' => '6',
                'porudzbinaid' => 99805,
                'sifraArtikla' => $inventory->id,
            ],
        ];

        $this->postJson('/api/third-party-invoice', $requestData);

        $invoice = ThirdPartyInvoice::first();
        $this->assertNotNull($invoice);
        $this->assertEquals(1, WarehouseStatus::where('batch_id', $invoice->id)->count());

        // Now delete it
        $response = $this->deleteJson('/api/backoffice/third-party-invoices/' . $invoice->id);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Invoice deleted successfully',
        ]);

        // Verify both invoice and warehouse status are gone
        $this->assertEquals(0, ThirdPartyInvoice::count());
        $this->assertEquals(0, WarehouseStatus::count());
    }

    /**
     * Test delete returns 404 for non-existent invoice.
     */
    public function test_delete_returns_404_for_nonexistent_invoice()
    {
        $response = $this->deleteJson('/api/backoffice/third-party-invoices/non-existent-uuid');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Invoice not found',
        ]);
    }

    /**
     * Test warehouse deduction uses correct norm multiplier.
     */
    public function test_warehouse_deduction_uses_norm_multiplier()
    {
        // Create inventory with norm of 0.5 (half unit consumed per item sold)
        $inventory = $this->createInventoryWithWarehouse('Vino 0.2', 0.5);

        $requestData = [
            [
                'kolicina' => 4,
                'cena' => 300,
                'naziv' => 'Vino 0.2',
                'jm' => 'kom',
                'gotovina' => 1200,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => '12351',
                'sto' => '6',
                'porudzbinaid' => 99806,
                'sifraArtikla' => $inventory->id,
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);

        $response->assertStatus(201);

        $warehouseStatus = WarehouseStatus::first();
        $this->assertNotNull($warehouseStatus);
        // qty(4) * norm(0.5) = 2
        $this->assertEquals(2, $warehouseStatus->quantity);
    }

    /**
     * Test mixed items - some matched, some not.
     */
    public function test_mixed_items_partial_warehouse_deduction()
    {
        // Create only one matching inventory
        $inventory1 = $this->createInventoryWithWarehouse('Cevapi');

        $requestData = [
            [
                'kolicina' => 2.5,
                'cena' => 1050,
                'naziv' => 'Cevapi',
                'jm' => 'kom',
                'gotovina' => 6525,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => '28925',
                'sto' => '6',
                'porudzbinaid' => 99803,
                'sifraArtikla' => $inventory1->id, // Matches
            ],
            [
                'kolicina' => 2,
                'cena' => 240,
                'naziv' => 'Unknown Item',
                'jm' => 'kom',
                'gotovina' => 6525,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => '28925',
                'sto' => '6',
                'porudzbinaid' => 99803,
                'sifraArtikla' => 99999, // Non-existent
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);

        $response->assertStatus(201);

        $invoice = ThirdPartyInvoice::first();

        // First item should have inventory_id
        $this->assertEquals($inventory1->id, $invoice->order[0]['inventory_id']);

        // Second item should NOT have inventory_id
        $this->assertArrayNotHasKey('inventory_id', $invoice->order[1]);

        // Only one warehouse status should exist (for matched item)
        $this->assertEquals(1, WarehouseStatus::count());
        $warehouseStatus = WarehouseStatus::first();
        $this->assertEquals($inventory1->id, $warehouseStatus->inventory_id);
        $this->assertEquals(2.5, $warehouseStatus->quantity);
    }

    /**
     * Test with real production-like request structure.
     */
    public function test_production_request_structure()
    {
        // Create some inventory items that match the production data
        $sljiv = $this->createInventoryWithWarehouse('Sljiv Bukovo, Đorđe 0.05');
        $cocaCola = $this->createInventoryWithWarehouse('Coca cola 0.25');
        $cevapi = $this->createInventoryWithWarehouse('Cevapi', 0.25); // 250g per portion

        $requestData = [
            [
                'kolicina' => 2,
                'cena' => 240,
                'naziv' => 'Sljiv Bukovo, Đorđe 0.05',
                'jm' => 'kom',
                'gotovina' => 13645,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-01-05 16:45:15',
                'brojracuna' => 28925,
                'sto' => '6',
                'klijentid' => 0,
                'pibkupca' => "\0",
                'porudzbinaid' => 99800,
                'sifraArtikla' => $sljiv->id,
            ],
            [
                'kolicina' => 2,
                'cena' => 210,
                'naziv' => 'Coca cola 0.25',
                'jm' => 'kom',
                'gotovina' => 13645,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-01-05 16:45:15',
                'brojracuna' => 28925,
                'sto' => '6',
                'klijentid' => 0,
                'pibkupca' => "\0",
                'porudzbinaid' => 99800,
                'sifraArtikla' => $cocaCola->id,
            ],
            [
                'kolicina' => 2.5,
                'cena' => 1050,
                'naziv' => 'Cevapi',
                'jm' => 'kom',
                'gotovina' => 13645,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-01-05 16:45:15',
                'brojracuna' => 28925,
                'sto' => '6',
                'klijentid' => 0,
                'pibkupca' => "\0",
                'porudzbinaid' => 99803,
                'sifraArtikla' => $cevapi->id,
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);

        $response->assertStatus(201);

        $invoice = ThirdPartyInvoice::first();
        $this->assertNotNull($invoice);
        $this->assertEquals(ThirdPartyInvoice::STATUS_PAYED, $invoice->status);
        $this->assertEquals('28925', $invoice->invoice_number);
        $this->assertEquals('6', $invoice->table_name);

        // All items should have inventory_id
        foreach ($invoice->order as $item) {
            $this->assertArrayHasKey('inventory_id', $item);
        }

        // 3 warehouse status records should exist
        $this->assertEquals(3, WarehouseStatus::count());

        // Verify Cevapi deduction uses norm
        $cevapiStatus = WarehouseStatus::where('inventory_id', $cevapi->id)->first();
        $this->assertNotNull($cevapiStatus);
        $this->assertEqualsWithDelta(0.625, $cevapiStatus->quantity, 0.01); // 2.5 * 0.25
    }

    /**
     * Test invoice uses UUID as batch_id for warehouse records.
     */
    public function test_warehouse_status_uses_invoice_uuid_as_batch_id()
    {
        $inventory = $this->createInventoryWithWarehouse('Test Item');

        $requestData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Test Item',
                'jm' => 'kom',
                'gotovina' => 100,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => '12352',
                'sto' => '1',
                'porudzbinaid' => 99807,
                'sifraArtikla' => $inventory->id,
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);

        $response->assertStatus(201);

        $invoice = ThirdPartyInvoice::first();
        $warehouseStatus = WarehouseStatus::first();

        // batch_id should be the invoice UUID
        $this->assertEquals($invoice->id, $warehouseStatus->batch_id);

        // Verify it's a UUID format (36 chars with hyphens)
        $this->assertEquals(36, strlen($invoice->id));
    }

    /**
     * Test card payment type is correctly set.
     */
    public function test_card_payment_type()
    {
        $inventory = $this->createInventoryWithWarehouse('Item');

        $requestData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 0,
                'kartica' => 100, // Card payment
                'prenosnaracun' => 0,
                'brojracuna' => '12353',
                'sto' => '1',
                'porudzbinaid' => 99808,
                'sifraArtikla' => $inventory->id,
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);

        $response->assertStatus(201);

        $invoice = ThirdPartyInvoice::first();
        $this->assertEquals(ThirdPartyInvoice::PAYMENT_CARD, $invoice->payment_type);

        // Warehouse deduction should still occur
        $this->assertEquals(1, WarehouseStatus::count());
    }

    /**
     * Test duplicate invoices do NOT create warehouse deductions.
     */
    public function test_duplicate_invoice_no_warehouse_deduction()
    {
        $inventory = $this->createInventoryWithWarehouse('Item');

        $requestData = [
            [
                'kolicina' => 2,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 200,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'DUPLICATE-TEST-123',
                'sto' => '1',
                'porudzbinaid' => 99809,
                'sifraArtikla' => $inventory->id,
            ],
        ];

        // First request - should create warehouse deduction
        $response1 = $this->postJson('/api/third-party-invoice', $requestData);
        $response1->assertStatus(201);
        $this->assertEquals(1, ThirdPartyInvoice::count());
        $this->assertEquals(1, WarehouseStatus::count());

        $firstStatus = WarehouseStatus::first();
        $this->assertEquals(2, $firstStatus->quantity);

        // Second request with same invoice number - should NOT create additional warehouse deduction
        $response2 = $this->postJson('/api/third-party-invoice', $requestData);
        $response2->assertStatus(201);
        $response2->assertJsonFragment(['is_duplicate' => true]);

        // Should have 2 invoices now
        $this->assertEquals(2, ThirdPartyInvoice::count());

        // But still only 1 warehouse status (no double-deduction)
        $this->assertEquals(1, WarehouseStatus::count());
    }

    /**
     * Test warehouse status uses date from request when provided.
     */
    public function test_warehouse_status_uses_date_from_request()
    {
        $inventory = $this->createInventoryWithWarehouse('Item');

        $requestData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 100,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => '12354',
                'sto' => '1',
                'porudzbinaid' => 99810,
                'sifraArtikla' => $inventory->id,
                'datum' => '2026-01-15 14:30:00', // Specific date
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);

        $response->assertStatus(201);

        $warehouseStatus = WarehouseStatus::first();
        $this->assertNotNull($warehouseStatus);

        // Date should be 2026-01-15 (the working day from the request)
        $this->assertEquals('2026-01-15', $warehouseStatus->date);
    }

    /**
     * Test warehouse status uses working day correctly for late night dates.
     */
    public function test_warehouse_status_uses_working_day_for_late_night()
    {
        $inventory = $this->createInventoryWithWarehouse('Item');

        $requestData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 100,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => '12355',
                'sto' => '1',
                'porudzbinaid' => 99811,
                'sifraArtikla' => $inventory->id,
                'datum' => '2026-01-16 02:30:00', // 2:30 AM on the 16th = working day of 15th
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);

        $response->assertStatus(201);

        $warehouseStatus = WarehouseStatus::first();
        $this->assertNotNull($warehouseStatus);

        // 2:30 AM on Jan 16 should be counted as Jan 15's working day
        $this->assertEquals('2026-01-15', $warehouseStatus->date);
    }
}
