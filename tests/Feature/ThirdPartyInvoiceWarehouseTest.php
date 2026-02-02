<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Sales;
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
    private function createInventoryWithWarehouse(string $name, float $norm = 1.0, ?string $sku = null): Inventory
    {
        $inventory = Inventory::create([
            'category_id' => $this->category->id,
            'name' => $name,
            'price' => 100,
            'sku' => $sku ?? str_pad(rand(1, 99999), 6, '0', STR_PAD_LEFT),
        ]);

        WarehouseInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'inventory_id' => $inventory->id,
            'norm' => $norm,
        ]);

        return $inventory;
    }

    /**
     * Test that inventory is matched by sifraArtikla (SKU).
     */
    public function test_matches_inventory_by_sifra_artikla()
    {
        // Create inventory with specific SKU
        $inventory = $this->createInventoryWithWarehouse('Sljiv Bukovo', 1.0, '000220');

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
                'sifraArtikla' => 220, // Matches SKU "000220" as integer
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
     * Test all SKU matching edge cases with leading zeros.
     * Covers: SKU with/without padding, sifraArtikla as int/string with/without padding.
     */
    public function test_sku_matching_all_edge_cases()
    {
        // Test case 1: SKU "000220" with sifraArtikla as integer 220
        $inv1 = $this->createInventoryWithWarehouse('Product 1', 1.0, '000220');
        $response1 = $this->postJson('/api/third-party-invoice', [[
            'kolicina' => 1, 'cena' => 100, 'naziv' => 'X', 'jm' => 'kom',
            'gotovina' => 100, 'kartica' => 0, 'prenosnaracun' => 0,
            'brojracuna' => 'EDGE-1', 'sto' => '1', 'porudzbinaid' => 90001,
            'sifraArtikla' => 220, // Integer
        ]]);
        $response1->assertStatus(201);
        $invoice1 = ThirdPartyInvoice::where('invoice_number', 'EDGE-1')->first();
        $this->assertEquals($inv1->id, $invoice1->order[0]['inventory_id']);

        // Test case 2: SKU "000220" with sifraArtikla as string "220"
        $inv2 = $this->createInventoryWithWarehouse('Product 2', 1.0, '000221');
        $response2 = $this->postJson('/api/third-party-invoice', [[
            'kolicina' => 1, 'cena' => 100, 'naziv' => 'X', 'jm' => 'kom',
            'gotovina' => 100, 'kartica' => 0, 'prenosnaracun' => 0,
            'brojracuna' => 'EDGE-2', 'sto' => '1', 'porudzbinaid' => 90002,
            'sifraArtikla' => '221', // String without padding
        ]]);
        $response2->assertStatus(201);
        $invoice2 = ThirdPartyInvoice::where('invoice_number', 'EDGE-2')->first();
        $this->assertEquals($inv2->id, $invoice2->order[0]['inventory_id']);

        // Test case 3: SKU "000220" with sifraArtikla as string "000220"
        $inv3 = $this->createInventoryWithWarehouse('Product 3', 1.0, '000222');
        $response3 = $this->postJson('/api/third-party-invoice', [[
            'kolicina' => 1, 'cena' => 100, 'naziv' => 'X', 'jm' => 'kom',
            'gotovina' => 100, 'kartica' => 0, 'prenosnaracun' => 0,
            'brojracuna' => 'EDGE-3', 'sto' => '1', 'porudzbinaid' => 90003,
            'sifraArtikla' => '000222', // String with same padding
        ]]);
        $response3->assertStatus(201);
        $invoice3 = ThirdPartyInvoice::where('invoice_number', 'EDGE-3')->first();
        $this->assertEquals($inv3->id, $invoice3->order[0]['inventory_id']);

        // Test case 4: SKU "220" (no padding) with sifraArtikla as integer 220
        $inv4 = $this->createInventoryWithWarehouse('Product 4', 1.0, '223');
        $response4 = $this->postJson('/api/third-party-invoice', [[
            'kolicina' => 1, 'cena' => 100, 'naziv' => 'X', 'jm' => 'kom',
            'gotovina' => 100, 'kartica' => 0, 'prenosnaracun' => 0,
            'brojracuna' => 'EDGE-4', 'sto' => '1', 'porudzbinaid' => 90004,
            'sifraArtikla' => 223, // Integer matching unpadded SKU
        ]]);
        $response4->assertStatus(201);
        $invoice4 = ThirdPartyInvoice::where('invoice_number', 'EDGE-4')->first();
        $this->assertEquals($inv4->id, $invoice4->order[0]['inventory_id']);

        // Test case 5: SKU "220" (no padding) with sifraArtikla as string "000220"
        $inv5 = $this->createInventoryWithWarehouse('Product 5', 1.0, '224');
        $response5 = $this->postJson('/api/third-party-invoice', [[
            'kolicina' => 1, 'cena' => 100, 'naziv' => 'X', 'jm' => 'kom',
            'gotovina' => 100, 'kartica' => 0, 'prenosnaracun' => 0,
            'brojracuna' => 'EDGE-5', 'sto' => '1', 'porudzbinaid' => 90005,
            'sifraArtikla' => '000224', // Padded string matching unpadded SKU
        ]]);
        $response5->assertStatus(201);
        $invoice5 = ThirdPartyInvoice::where('invoice_number', 'EDGE-5')->first();
        $this->assertEquals($inv5->id, $invoice5->order[0]['inventory_id']);
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
        $inventory = $this->createInventoryWithWarehouse('Cevapi', 1.0, '000301');

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
                'sifraArtikla' => 301,
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
        $inventory = $this->createInventoryWithWarehouse('Pogaca', 1.0, '000302');

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
                'sifraArtikla' => 302,
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
        $inventory = $this->createInventoryWithWarehouse('Vino 0.2', 0.5, '000303');

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
                'sifraArtikla' => 303,
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
        $inventory1 = $this->createInventoryWithWarehouse('Cevapi', 1.0, '000304');

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
                'sifraArtikla' => 304, // Matches SKU "000304"
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
        $sljiv = $this->createInventoryWithWarehouse('Sljiv Bukovo, Đorđe 0.05', 1.0, '000305');
        $cocaCola = $this->createInventoryWithWarehouse('Coca cola 0.25', 1.0, '000306');
        $cevapi = $this->createInventoryWithWarehouse('Cevapi', 0.25, '000307'); // 250g per portion

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
                'sifraArtikla' => 305,
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
                'sifraArtikla' => 306,
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
                'sifraArtikla' => 307,
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
        $inventory = $this->createInventoryWithWarehouse('Test Item', 1.0, '000308');

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
                'sifraArtikla' => 308,
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
        $inventory = $this->createInventoryWithWarehouse('Item', 1.0, '000309');

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
                'sifraArtikla' => 309,
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
        $inventory = $this->createInventoryWithWarehouse('Item', 1.0, '000310');

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
                'sifraArtikla' => 310,
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
        $inventory = $this->createInventoryWithWarehouse('Item', 1.0, '000311');

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
                'sifraArtikla' => 311,
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
        $inventory = $this->createInventoryWithWarehouse('Item', 1.0, '000312');

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
                'sifraArtikla' => 312,
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

    /**
     * Test that external_invoice_id (racunid) is stored correctly.
     */
    public function test_stores_external_invoice_id()
    {
        $inventory = $this->createInventoryWithWarehouse('Item', 1.0, '000400');

        $requestData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 100,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'INV-001',
                'racunid' => 12345,
                'sto' => '1',
                'porudzbinaid' => 99900,
                'sifraArtikla' => 400,
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);

        $response->assertStatus(201);

        $invoice = ThirdPartyInvoice::first();
        $this->assertNotNull($invoice);
        $this->assertEquals(12345, $invoice->external_invoice_id);
        $this->assertEquals('INV-001', $invoice->invoice_number);
    }

    /**
     * Test that stornoreferenceid marks the referenced invoice as storno.
     */
    public function test_storno_reference_marks_referenced_invoice_as_storno()
    {
        $inventory = $this->createInventoryWithWarehouse('Item', 1.0, '000401');

        // First, create an original invoice with racunid
        $originalInvoiceData = [
            [
                'kolicina' => 2,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 200,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'ORIG-001',
                'racunid' => 100,
                'sto' => '1',
                'porudzbinaid' => 99901,
                'sifraArtikla' => 401,
            ],
        ];

        $this->postJson('/api/third-party-invoice', $originalInvoiceData);

        $originalInvoice = ThirdPartyInvoice::where('invoice_number', 'ORIG-001')->first();
        $this->assertNotNull($originalInvoice);
        $this->assertEquals(ThirdPartyInvoice::STATUS_PAYED, $originalInvoice->status);
        $this->assertEquals(100, $originalInvoice->external_invoice_id);

        // Verify warehouse and sales records exist
        $this->assertEquals(1, WarehouseStatus::where('batch_id', $originalInvoice->id)->count());
        $this->assertEquals(1, Sales::where('batch_id', $originalInvoice->id)->count());

        // Now create a storno invoice that references the original
        $stornoInvoiceData = [
            [
                'kolicina' => 2,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 0,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'STORNO-001',
                'racunid' => 101,
                'stornoreferenceid' => 100, // References the original invoice
                'sto' => '1',
                'porudzbinaid' => 99902,
                'sifraArtikla' => 401,
            ],
        ];

        $this->postJson('/api/third-party-invoice', $stornoInvoiceData);

        // Refresh the original invoice
        $originalInvoice->refresh();

        // Original invoice should now be marked as storno
        $this->assertEquals(ThirdPartyInvoice::STATUS_STORNO, $originalInvoice->status);

        // Warehouse and sales records for original invoice should be deleted
        $this->assertEquals(0, WarehouseStatus::where('batch_id', $originalInvoice->id)->count());
        $this->assertEquals(0, Sales::where('batch_id', $originalInvoice->id)->count());

        // Storno invoice should be created
        $stornoInvoice = ThirdPartyInvoice::where('invoice_number', 'STORNO-001')->first();
        $this->assertNotNull($stornoInvoice);
        $this->assertEquals(101, $stornoInvoice->external_invoice_id);
    }

    /**
     * Test that storno reference to non-existent invoice continues and creates the storno invoice.
     */
    public function test_storno_reference_not_found_continues()
    {
        $stornoInvoiceData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 0,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'STORNO-NOTFOUND',
                'racunid' => 200,
                'stornoreferenceid' => 99999, // Non-existent reference
                'sto' => '1',
                'porudzbinaid' => 99903,
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $stornoInvoiceData);

        $response->assertStatus(201);

        // Storno invoice should still be created even when reference not found
        $invoice = ThirdPartyInvoice::where('invoice_number', 'STORNO-NOTFOUND')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(200, $invoice->external_invoice_id);
    }

    /**
     * Test invoice with both stornoporudzbine and stornoreferenceid.
     */
    public function test_both_stornoporudzbine_and_stornoreferenceid()
    {
        $inventory = $this->createInventoryWithWarehouse('Item', 1.0, '000402');

        // Create original invoice
        $originalInvoiceData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 100,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'ORIG-002',
                'racunid' => 300,
                'sto' => '1',
                'porudzbinaid' => 99904,
                'sifraArtikla' => 402,
            ],
        ];

        $this->postJson('/api/third-party-invoice', $originalInvoiceData);

        $originalInvoice = ThirdPartyInvoice::where('invoice_number', 'ORIG-002')->first();
        $this->assertEquals(ThirdPartyInvoice::STATUS_PAYED, $originalInvoice->status);

        // Create storno invoice with both flags
        $stornoInvoiceData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 0,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'STORNO-002',
                'racunid' => 301,
                'stornoreferenceid' => 300,
                'stornoporudzbine' => 1, // Self-storno flag
                'sto' => '1',
                'porudzbinaid' => 99905,
                'sifraArtikla' => 402,
            ],
        ];

        $this->postJson('/api/third-party-invoice', $stornoInvoiceData);

        // Original invoice should be marked as storno
        $originalInvoice->refresh();
        $this->assertEquals(ThirdPartyInvoice::STATUS_STORNO, $originalInvoice->status);

        // Storno invoice itself should also be marked as storno (stornoporudzbine=1)
        $stornoInvoice = ThirdPartyInvoice::where('invoice_number', 'STORNO-002')->first();
        $this->assertEquals(ThirdPartyInvoice::STATUS_STORNO, $stornoInvoice->status);

        // No warehouse records should be created for the storno invoice
        $this->assertEquals(0, WarehouseStatus::where('batch_id', $stornoInvoice->id)->count());
    }

    /**
     * Test findByExternalId method.
     */
    public function test_find_by_external_id()
    {
        // Create invoice with external_invoice_id
        $invoice = ThirdPartyInvoice::create([
            'external_invoice_id' => 500,
            'invoice_number' => 'TEST-500',
            'status' => ThirdPartyInvoice::STATUS_PAYED,
            'order' => [['name' => 'Test', 'qty' => 1, 'price' => 100]],
            'total' => 100,
        ]);

        // Should find by external ID
        $found = ThirdPartyInvoice::findByExternalId(500);
        $this->assertNotNull($found);
        $this->assertEquals($invoice->id, $found->id);

        // Should return null for non-existent ID
        $notFound = ThirdPartyInvoice::findByExternalId(999);
        $this->assertNull($notFound);
    }

    /**
     * Test markAsStorno method.
     */
    public function test_mark_as_storno_method()
    {
        $inventory = $this->createInventoryWithWarehouse('Item', 1.0, '000403');

        // Create invoice with warehouse and sales records
        $requestData = [
            [
                'kolicina' => 3,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 300,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'MARK-STORNO',
                'racunid' => 600,
                'sto' => '1',
                'porudzbinaid' => 99906,
                'sifraArtikla' => 403,
            ],
        ];

        $this->postJson('/api/third-party-invoice', $requestData);

        $invoice = ThirdPartyInvoice::where('invoice_number', 'MARK-STORNO')->first();
        $this->assertEquals(ThirdPartyInvoice::STATUS_PAYED, $invoice->status);
        $this->assertEquals(1, WarehouseStatus::where('batch_id', $invoice->id)->count());
        $this->assertEquals(1, Sales::where('batch_id', $invoice->id)->count());

        // Call markAsStorno - should return true (first time)
        $result = $invoice->markAsStorno();
        $this->assertTrue($result);

        // Status should be storno
        $this->assertEquals(ThirdPartyInvoice::STATUS_STORNO, $invoice->status);

        // Warehouse and sales records should be deleted
        $this->assertEquals(0, WarehouseStatus::where('batch_id', $invoice->id)->count());
        $this->assertEquals(0, Sales::where('batch_id', $invoice->id)->count());
    }

    /**
     * Test markAsStorno is idempotent - calling twice returns false on second call.
     */
    public function test_mark_as_storno_is_idempotent()
    {
        $invoice = ThirdPartyInvoice::create([
            'external_invoice_id' => 700,
            'invoice_number' => 'IDEMPOTENT-TEST',
            'status' => ThirdPartyInvoice::STATUS_PAYED,
            'order' => [['name' => 'Test', 'qty' => 1, 'price' => 100]],
            'total' => 100,
        ]);

        // First call should return true
        $result1 = $invoice->markAsStorno();
        $this->assertTrue($result1);
        $this->assertTrue($invoice->isStorno());

        // Second call should return false (already stornoed)
        $result2 = $invoice->markAsStorno();
        $this->assertFalse($result2);
        $this->assertTrue($invoice->isStorno());
    }

    /**
     * Test duplicate storno reference is handled gracefully (idempotency).
     */
    public function test_duplicate_storno_reference_is_idempotent()
    {
        $inventory = $this->createInventoryWithWarehouse('Item', 1.0, '000404');

        // Create original invoice
        $originalData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 100,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'ORIG-IDEM',
                'racunid' => 800,
                'sto' => '1',
                'porudzbinaid' => 99907,
                'sifraArtikla' => 404,
            ],
        ];

        $this->postJson('/api/third-party-invoice', $originalData);
        $originalInvoice = ThirdPartyInvoice::where('invoice_number', 'ORIG-IDEM')->first();
        $this->assertEquals(ThirdPartyInvoice::STATUS_PAYED, $originalInvoice->status);

        // First storno request
        $stornoData1 = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 0,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'STORNO-IDEM-1',
                'racunid' => 801,
                'stornoreferenceid' => 800,
                'sto' => '1',
                'porudzbinaid' => 99908,
            ],
        ];

        $response1 = $this->postJson('/api/third-party-invoice', $stornoData1);
        $response1->assertStatus(201);

        $originalInvoice->refresh();
        $this->assertEquals(ThirdPartyInvoice::STATUS_STORNO, $originalInvoice->status);

        // Second storno request for the same original (duplicate/retry)
        $stornoData2 = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 0,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'STORNO-IDEM-2',
                'racunid' => 802,
                'stornoreferenceid' => 800, // Same reference
                'sto' => '1',
                'porudzbinaid' => 99909,
            ],
        ];

        $response2 = $this->postJson('/api/third-party-invoice', $stornoData2);
        $response2->assertStatus(201); // Should succeed, not error

        // Original should still be storno
        $originalInvoice->refresh();
        $this->assertEquals(ThirdPartyInvoice::STATUS_STORNO, $originalInvoice->status);

        // Both storno invoices should be created
        $this->assertNotNull(ThirdPartyInvoice::where('invoice_number', 'STORNO-IDEM-1')->first());
        $this->assertNotNull(ThirdPartyInvoice::where('invoice_number', 'STORNO-IDEM-2')->first());
    }

    /**
     * Test racunid as string is handled correctly.
     */
    public function test_racunid_as_string_is_handled()
    {
        $requestData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 100,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'STRING-RACUNID',
                'racunid' => '12345', // String instead of integer
                'sto' => '1',
                'porudzbinaid' => 99910,
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);
        $response->assertStatus(201);

        $invoice = ThirdPartyInvoice::where('invoice_number', 'STRING-RACUNID')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(12345, $invoice->external_invoice_id);
    }

    /**
     * Test stornoreferenceid of 0 is treated as no reference.
     */
    public function test_storno_reference_id_zero_is_ignored()
    {
        $requestData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Item',
                'jm' => 'kom',
                'gotovina' => 100,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'ZERO-REF',
                'racunid' => 900,
                'stornoreferenceid' => 0, // Zero should be treated as no reference
                'sto' => '1',
                'porudzbinaid' => 99911,
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $requestData);
        $response->assertStatus(201);

        // Invoice should be created with status PAYED (not treated as storno)
        $invoice = ThirdPartyInvoice::where('invoice_number', 'ZERO-REF')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(ThirdPartyInvoice::STATUS_PAYED, $invoice->status);
    }

    /**
     * Test markAsStorno on invoice without warehouse/sales records.
     */
    public function test_mark_as_storno_without_related_records()
    {
        // Create invoice directly without warehouse/sales records
        $invoice = ThirdPartyInvoice::create([
            'external_invoice_id' => 950,
            'invoice_number' => 'NO-RECORDS',
            'status' => ThirdPartyInvoice::STATUS_PAYED,
            'order' => [['name' => 'Test', 'qty' => 1, 'price' => 100]],
            'total' => 100,
        ]);

        // Ensure no related records exist
        $this->assertEquals(0, WarehouseStatus::where('batch_id', $invoice->id)->count());
        $this->assertEquals(0, Sales::where('batch_id', $invoice->id)->count());

        // markAsStorno should still work without errors
        $result = $invoice->markAsStorno();
        $this->assertTrue($result);
        $this->assertEquals(ThirdPartyInvoice::STATUS_STORNO, $invoice->status);
    }

    /**
     * Test isStorno helper method.
     */
    public function test_is_storno_helper()
    {
        $payedInvoice = ThirdPartyInvoice::create([
            'invoice_number' => 'IS-STORNO-PAYED',
            'status' => ThirdPartyInvoice::STATUS_PAYED,
            'order' => [],
            'total' => 100,
        ]);

        $stornoInvoice = ThirdPartyInvoice::create([
            'invoice_number' => 'IS-STORNO-STORNO',
            'status' => ThirdPartyInvoice::STATUS_STORNO,
            'order' => [],
            'total' => 100,
        ]);

        $this->assertFalse($payedInvoice->isStorno());
        $this->assertTrue($stornoInvoice->isStorno());
    }
}
