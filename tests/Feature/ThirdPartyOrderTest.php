<?php

namespace Tests\Feature;

use App\Models\ThirdPartyOrder;
use App\Models\ThirdPartyOrderItem;
use App\Models\ThirdPartyInvoice;
use App\Models\Category;
use App\Http\Middleware\VerifyExternalApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThirdPartyOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Bypass API key middleware for tests
        $this->withoutMiddleware(VerifyExternalApiKey::class);
    }

    /**
     * Test creating a new order with items.
     */
    public function test_creates_order_with_items()
    {
        $requestData = [
            [
                'kolicina' => 2,
                'cena' => 150,
                'naziv' => 'Mala dunjevaca 0.03',
                'jm' => 'kom',
                'datum' => '2026-01-15 14:03:55',
                'sto' => '9',
                'stampanjenalogaid' => 1,
                'modifikatorslobodan' => null,
                'porudzbinaid' => 100575,
                'sifraArtikla' => 211,
                'stavkaid' => 317858,
                'stoid' => 740,
            ],
            [
                'kolicina' => 1,
                'cena' => 230,
                'naziv' => 'Rosa 0.75',
                'jm' => 'kom',
                'datum' => '2026-01-15 14:03:55',
                'sto' => '9',
                'stampanjenalogaid' => 1,
                'modifikatorslobodan' => null,
                'porudzbinaid' => 100575,
                'sifraArtikla' => 146,
                'stavkaid' => 317859,
                'stoid' => 740,
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $requestData);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        // Verify order was created
        $this->assertEquals(1, ThirdPartyOrder::count());
        $order = ThirdPartyOrder::first();
        $this->assertEquals(100575, $order->external_order_id);
        $this->assertEquals(740, $order->table_id);
        $this->assertEquals('9', $order->table_name);
        $this->assertEquals(530, $order->total); // 2*150 + 1*230 = 530

        // Verify items were created
        $this->assertEquals(2, ThirdPartyOrderItem::count());

        $item1 = ThirdPartyOrderItem::where('external_item_id', 317858)->first();
        $this->assertNotNull($item1);
        $this->assertEquals('Mala dunjevaca 0.03', $item1->name);
        $this->assertEquals(2, $item1->qty);
        $this->assertEquals(150, $item1->price);
        $this->assertEquals(1, $item1->active);

        $item2 = ThirdPartyOrderItem::where('external_item_id', 317859)->first();
        $this->assertNotNull($item2);
        $this->assertEquals('Rosa 0.75', $item2->name);
        $this->assertEquals(1, $item2->qty);
        $this->assertEquals(230, $item2->price);
        $this->assertEquals(1, $item2->active);
    }

    /**
     * Test updating existing order preserves active flag.
     */
    public function test_update_order_preserves_active_flag()
    {
        // Create initial order
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 300,
        ]);

        // Create item with active = 0 (deactivated)
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 317858,
            'name' => 'Mala dunjevaca 0.03',
            'qty' => 2,
            'price' => 150,
            'unit' => 'kom',
            'active' => 0, // Deactivated!
        ]);

        // Update order with same item (different qty)
        $requestData = [
            [
                'kolicina' => 3, // Changed qty
                'cena' => 150,
                'naziv' => 'Mala dunjevaca 0.03',
                'jm' => 'kom',
                'sto' => '9',
                'porudzbinaid' => 100575,
                'stavkaid' => 317858,
                'stoid' => 740,
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $requestData);

        $response->assertStatus(201);

        // Verify item was updated but active flag preserved
        $item = ThirdPartyOrderItem::where('external_item_id', 317858)->first();
        $this->assertEquals(3, $item->qty); // Updated
        $this->assertEquals(0, $item->active); // Preserved as inactive
    }

    /**
     * Test new items default to active = 1.
     */
    public function test_new_items_default_to_active()
    {
        $requestData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Test Item',
                'jm' => 'kom',
                'sto' => '5',
                'porudzbinaid' => 200001,
                'stavkaid' => 400001,
                'stoid' => 500,
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $requestData);

        $response->assertStatus(201);

        $item = ThirdPartyOrderItem::first();
        $this->assertEquals(1, $item->active);
    }

    /**
     * Test items removed from order are deleted.
     */
    public function test_removed_items_are_deleted()
    {
        // Create initial order with 2 items
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 300,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 317858,
            'name' => 'Item 1',
            'qty' => 1,
            'price' => 100,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 317859,
            'name' => 'Item 2',
            'qty' => 1,
            'price' => 200,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $this->assertEquals(2, ThirdPartyOrderItem::count());

        // Update order with only one item
        $requestData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Item 1',
                'jm' => 'kom',
                'sto' => '9',
                'porudzbinaid' => 100575,
                'stavkaid' => 317858, // Only this item
                'stoid' => 740,
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $requestData);

        $response->assertStatus(201);

        // Item 2 should be deleted
        $this->assertEquals(1, ThirdPartyOrderItem::count());
        $this->assertNull(ThirdPartyOrderItem::where('external_item_id', 317859)->first());
        $this->assertNotNull(ThirdPartyOrderItem::where('external_item_id', 317858)->first());
    }

    /**
     * Test multiple orders in one request are processed separately.
     */
    public function test_multiple_orders_in_one_request()
    {
        $requestData = [
            // Order 1
            [
                'kolicina' => 2,
                'cena' => 100,
                'naziv' => 'Item A',
                'jm' => 'kom',
                'sto' => '5',
                'porudzbinaid' => 100001, // First order
                'stavkaid' => 1001,
                'stoid' => 501,
            ],
            // Order 2
            [
                'kolicina' => 1,
                'cena' => 200,
                'naziv' => 'Item B',
                'jm' => 'kom',
                'sto' => '6',
                'porudzbinaid' => 100002, // Second order
                'stavkaid' => 2001,
                'stoid' => 502,
            ],
            [
                'kolicina' => 3,
                'cena' => 50,
                'naziv' => 'Item C',
                'jm' => 'kom',
                'sto' => '6',
                'porudzbinaid' => 100002, // Same order as Item B
                'stavkaid' => 2002,
                'stoid' => 502,
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $requestData);

        $response->assertStatus(201);
        $response->assertJsonFragment(['message' => '2 order(s) processed']);

        // Verify 2 orders created
        $this->assertEquals(2, ThirdPartyOrder::count());

        // Order 1
        $order1 = ThirdPartyOrder::where('external_order_id', 100001)->first();
        $this->assertNotNull($order1);
        $this->assertEquals(501, $order1->table_id);
        $this->assertEquals('5', $order1->table_name);
        $this->assertEquals(200, $order1->total); // 2*100
        $this->assertEquals(1, $order1->items()->count());

        // Order 2
        $order2 = ThirdPartyOrder::where('external_order_id', 100002)->first();
        $this->assertNotNull($order2);
        $this->assertEquals(502, $order2->table_id);
        $this->assertEquals('6', $order2->table_name);
        $this->assertEquals(350, $order2->total); // 1*200 + 3*50
        $this->assertEquals(2, $order2->items()->count());
    }

    /**
     * Test order cascade delete removes items.
     */
    public function test_order_cascade_deletes_items()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 300,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 317858,
            'name' => 'Test Item',
            'qty' => 1,
            'price' => 300,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $this->assertEquals(1, ThirdPartyOrder::count());
        $this->assertEquals(1, ThirdPartyOrderItem::count());

        $order->delete();

        $this->assertEquals(0, ThirdPartyOrder::count());
        $this->assertEquals(0, ThirdPartyOrderItem::count());
    }

    /**
     * Test all endpoint returns orders with items.
     */
    public function test_all_endpoint_returns_orders_with_items()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 300,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 317858,
            'name' => 'Test Item',
            'qty' => 2,
            'price' => 150,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $response = $this->getJson('/api/backoffice/third-party-orders');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'external_order_id',
                    'table_id',
                    'table_name',
                    'items',
                    'total',
                    'active_total',
                    'created_at',
                    'created_at_full',
                ],
            ],
        ]);

        $data = $response->json('data.0');
        $this->assertEquals(100575, $data['external_order_id']);
        $this->assertEquals(740, $data['table_id']);
        $this->assertCount(1, $data['items']);
        $this->assertEquals('Test Item', $data['items'][0]['name']);
    }

    /**
     * Test active_total calculation excludes inactive items.
     */
    public function test_active_total_excludes_inactive_items()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 500,
        ]);

        // Active item
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 317858,
            'name' => 'Active Item',
            'qty' => 2,
            'price' => 100,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // Inactive item
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 317859,
            'name' => 'Inactive Item',
            'qty' => 3,
            'price' => 100,
            'unit' => 'kom',
            'active' => 0,
        ]);

        $response = $this->getJson('/api/backoffice/third-party-orders');

        $response->assertStatus(200);
        $data = $response->json('data.0');

        // Total includes all items: 2*100 + 3*100 = 500
        $this->assertEquals(500, $data['total']);

        // Active total only includes active items: 2*100 = 200
        $this->assertEquals(200, $data['active_total']);
    }

    /**
     * Test empty request returns error.
     */
    public function test_empty_request_returns_error()
    {
        $response = $this->postJson('/api/third-party-order', []);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'No data provided',
        ]);
    }

    /**
     * Test table_id can be changed.
     */
    public function test_table_id_can_be_changed()
    {
        // Create initial order on table 740
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 100,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 317858,
            'name' => 'Test Item',
            'qty' => 1,
            'price' => 100,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // Update order moving to different table
        $requestData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Test Item',
                'jm' => 'kom',
                'sto' => '10', // Changed table name
                'porudzbinaid' => 100575, // Same order
                'stavkaid' => 317858,
                'stoid' => 750, // Changed table ID
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $requestData);

        $response->assertStatus(201);

        $order->refresh();
        $this->assertEquals(750, $order->table_id);
        $this->assertEquals('10', $order->table_name);
    }

    /**
     * Test modifier field is stored correctly.
     */
    public function test_modifier_field_stored()
    {
        $requestData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Test Item',
                'jm' => 'kom',
                'sto' => '5',
                'porudzbinaid' => 100001,
                'stavkaid' => 1001,
                'stoid' => 500,
                'modifikatorslobodan' => 'bez luka',
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $requestData);

        $response->assertStatus(201);

        $item = ThirdPartyOrderItem::first();
        $this->assertEquals('bez luka', $item->modifier);
    }

    /**
     * Test print_station_id field is stored correctly.
     */
    public function test_print_station_id_stored()
    {
        $requestData = [
            [
                'kolicina' => 1,
                'cena' => 100,
                'naziv' => 'Test Item',
                'jm' => 'kom',
                'sto' => '5',
                'porudzbinaid' => 100001,
                'stavkaid' => 1001,
                'stoid' => 500,
                'stampanjenalogaid' => 3,
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $requestData);

        $response->assertStatus(201);

        $item = ThirdPartyOrderItem::first();
        $this->assertEquals(3, $item->print_station_id);
    }

    /**
     * Test deleteByExternalOrderId static method.
     */
    public function test_delete_by_external_order_id()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 100,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 317858,
            'name' => 'Test Item',
            'qty' => 1,
            'price' => 100,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $result = ThirdPartyOrder::deleteByExternalOrderId(100575);

        $this->assertTrue($result);
        $this->assertEquals(0, ThirdPartyOrder::count());
        $this->assertEquals(0, ThirdPartyOrderItem::count());
    }

    /**
     * Test deleteByExternalOrderId returns false for non-existent order.
     */
    public function test_delete_by_external_order_id_returns_false_for_nonexistent()
    {
        $result = ThirdPartyOrder::deleteByExternalOrderId(999999);

        $this->assertFalse($result);
    }

    /**
     * Test with exact production request from third-party-order-request.json.
     * This request contains 3 orders on the same table (stoid: 740):
     * - Order 100575: 3 items (Mala dunjevaca, Rosa, Tegla Dva brata)
     * - Order 100577: 4 items (Kiseli kupus, Lepinja, Svinjska rebra, Pileca krilca)
     * - Order 100579: 1 item (Mala dunjevaca)
     */
    public function test_production_request_multiple_orders_same_table()
    {
        $requestData = [
            [
                'kolicina' => 2,
                'cena' => 150,
                'naziv' => 'Mala dunjevaca 0.03',
                'jm' => 'kom',
                'datum' => '2026-01-15 14:03:55',
                'sto' => '9',
                'stampanjenalogaid' => 1,
                'modifikatorslobodan' => null,
                'porudzbinaid' => 100575,
                'sifraArtikla' => 211,
                'stavkaid' => 317858,
                'stoid' => 740,
            ],
            [
                'kolicina' => 1,
                'cena' => 230,
                'naziv' => 'Rosa 0.75',
                'jm' => 'kom',
                'datum' => '2026-01-15 14:03:55',
                'sto' => '9',
                'stampanjenalogaid' => 1,
                'modifikatorslobodan' => null,
                'porudzbinaid' => 100575,
                'sifraArtikla' => 146,
                'stavkaid' => 317859,
                'stoid' => 740,
            ],
            [
                'kolicina' => 1,
                'cena' => 470,
                'naziv' => 'Tegla Dva brata',
                'jm' => 'kom',
                'datum' => '2026-01-15 14:03:55',
                'sto' => '9',
                'stampanjenalogaid' => 2,
                'modifikatorslobodan' => null,
                'porudzbinaid' => 100575,
                'sifraArtikla' => 17,
                'stavkaid' => 317860,
                'stoid' => 740,
            ],
            [
                'kolicina' => 1,
                'cena' => 280,
                'naziv' => 'Kiseli kupus',
                'jm' => 'kom',
                'datum' => '2026-01-15 14:07:51',
                'sto' => '9',
                'stampanjenalogaid' => 2,
                'modifikatorslobodan' => null,
                'porudzbinaid' => 100577,
                'sifraArtikla' => 55,
                'stavkaid' => 317862,
                'stoid' => 740,
            ],
            [
                'kolicina' => 1,
                'cena' => 140,
                'naziv' => 'Lepinja sa lukom',
                'jm' => 'kom',
                'datum' => '2026-01-15 14:07:51',
                'sto' => '9',
                'stampanjenalogaid' => 2,
                'modifikatorslobodan' => null,
                'porudzbinaid' => 100577,
                'sifraArtikla' => 107,
                'stavkaid' => 317863,
                'stoid' => 740,
            ],
            [
                'kolicina' => 1,
                'cena' => 1250,
                'naziv' => 'Svinjska rebra',
                'jm' => 'kom',
                'datum' => '2026-01-15 14:07:51',
                'sto' => '9',
                'stampanjenalogaid' => 2,
                'modifikatorslobodan' => null,
                'porudzbinaid' => 100577,
                'sifraArtikla' => 74,
                'stavkaid' => 317864,
                'stoid' => 740,
            ],
            [
                'kolicina' => 1,
                'cena' => 680,
                'naziv' => 'Pileca krilca',
                'jm' => 'kom',
                'datum' => '2026-01-15 14:07:51',
                'sto' => '9',
                'stampanjenalogaid' => 2,
                'modifikatorslobodan' => null,
                'porudzbinaid' => 100577,
                'sifraArtikla' => 26,
                'stavkaid' => 317865,
                'stoid' => 740,
            ],
            [
                'kolicina' => 2,
                'cena' => 150,
                'naziv' => 'Mala dunjevaca 0.03',
                'jm' => 'kom',
                'datum' => '2026-01-15 14:17:48',
                'sto' => '9',
                'stampanjenalogaid' => 1,
                'modifikatorslobodan' => null,
                'porudzbinaid' => 100579,
                'sifraArtikla' => 211,
                'stavkaid' => 317874,
                'stoid' => 740,
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $requestData);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $response->assertJsonFragment(['message' => '3 order(s) processed']);

        // Verify 3 orders were created
        $this->assertEquals(3, ThirdPartyOrder::count());

        // All orders should be on the same table
        $orders = ThirdPartyOrder::all();
        foreach ($orders as $order) {
            $this->assertEquals(740, $order->table_id);
            $this->assertEquals('9', $order->table_name);
        }

        // Order 100575: 3 items, total = 2*150 + 1*230 + 1*470 = 1000
        $order1 = ThirdPartyOrder::where('external_order_id', 100575)->first();
        $this->assertNotNull($order1);
        $this->assertEquals(3, $order1->items()->count());
        $this->assertEquals(1000, $order1->total);

        // Order 100577: 4 items, total = 1*280 + 1*140 + 1*1250 + 1*680 = 2350
        $order2 = ThirdPartyOrder::where('external_order_id', 100577)->first();
        $this->assertNotNull($order2);
        $this->assertEquals(4, $order2->items()->count());
        $this->assertEquals(2350, $order2->total);

        // Order 100579: 1 item, total = 2*150 = 300
        $order3 = ThirdPartyOrder::where('external_order_id', 100579)->first();
        $this->assertNotNull($order3);
        $this->assertEquals(1, $order3->items()->count());
        $this->assertEquals(300, $order3->total);

        // Total items across all orders: 8
        $this->assertEquals(8, ThirdPartyOrderItem::count());
    }

    /**
     * Test API response structure for production request.
     */
    public function test_production_request_api_response_structure()
    {
        $requestData = [
            [
                'kolicina' => 2,
                'cena' => 150,
                'naziv' => 'Mala dunjevaca 0.03',
                'jm' => 'kom',
                'datum' => '2026-01-15 14:03:55',
                'sto' => '9',
                'stampanjenalogaid' => 1,
                'modifikatorslobodan' => null,
                'porudzbinaid' => 100575,
                'sifraArtikla' => 211,
                'stavkaid' => 317858,
                'stoid' => 740,
            ],
            [
                'kolicina' => 1,
                'cena' => 280,
                'naziv' => 'Kiseli kupus',
                'jm' => 'kom',
                'datum' => '2026-01-15 14:07:51',
                'sto' => '9',
                'stampanjenalogaid' => 2,
                'porudzbinaid' => 100577,
                'sifraArtikla' => 55,
                'stavkaid' => 317862,
                'stoid' => 740,
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $requestData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'external_order_id',
                    'table_id',
                    'table_name',
                    'items' => [
                        '*' => [
                            'id',
                            'external_item_id',
                            'name',
                            'qty',
                            'price',
                            'unit',
                            'active',
                        ],
                    ],
                    'total',
                    'active_total',
                    'created_at',
                    'created_at_full',
                ],
            ],
        ]);
    }

    /**
     * Test all endpoint groups orders correctly for UI.
     */
    public function test_all_endpoint_returns_grouped_data_for_ui()
    {
        // Create 3 orders on same table like production data
        $order1 = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 1000,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order1->id,
            'external_item_id' => 317858,
            'name' => 'Mala dunjevaca 0.03',
            'qty' => 2,
            'price' => 150,
            'unit' => 'kom',
            'active' => 1,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order1->id,
            'external_item_id' => 317859,
            'name' => 'Rosa 0.75',
            'qty' => 1,
            'price' => 230,
            'unit' => 'kom',
            'active' => 1,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order1->id,
            'external_item_id' => 317860,
            'name' => 'Tegla Dva brata',
            'qty' => 1,
            'price' => 470,
            'unit' => 'kom',
            'active' => 0, // Inactive item
        ]);

        $order2 = ThirdPartyOrder::create([
            'external_order_id' => 100577,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 2350,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order2->id,
            'external_item_id' => 317862,
            'name' => 'Kiseli kupus',
            'qty' => 1,
            'price' => 280,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $order3 = ThirdPartyOrder::create([
            'external_order_id' => 100579,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 300,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order3->id,
            'external_item_id' => 317874,
            'name' => 'Mala dunjevaca 0.03',
            'qty' => 2,
            'price' => 150,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $response = $this->getJson('/api/backoffice/third-party-orders');

        $response->assertStatus(200);
        $data = $response->json('data');

        // Should have 3 orders
        $this->assertCount(3, $data);

        // All orders should have same table_id
        foreach ($data as $order) {
            $this->assertEquals(740, $order['table_id']);
            $this->assertEquals('9', $order['table_name']);
        }

        // Order 1 has inactive item - active_total should exclude it
        $orderData1 = collect($data)->firstWhere('external_order_id', 100575);
        $this->assertNotNull($orderData1);
        $this->assertCount(3, $orderData1['items']);
        // Total: 2*150 + 1*230 + 1*470 = 1000
        $this->assertEquals(1000, $orderData1['total']);
        // Active total: 2*150 + 1*230 = 530 (excludes inactive Tegla)
        $this->assertEquals(530, $orderData1['active_total']);

        // Check inactive item has active = 0
        $inactiveItem = collect($orderData1['items'])->firstWhere('name', 'Tegla Dva brata');
        $this->assertEquals(0, $inactiveItem['active']);
    }

    /**
     * Test update preserves active flag when same order sent again.
     */
    public function test_production_update_preserves_active_flags()
    {
        // First, create order with initial data
        $initialRequest = [
            [
                'kolicina' => 2,
                'cena' => 150,
                'naziv' => 'Mala dunjevaca 0.03',
                'jm' => 'kom',
                'sto' => '9',
                'porudzbinaid' => 100575,
                'stavkaid' => 317858,
                'stoid' => 740,
            ],
            [
                'kolicina' => 1,
                'cena' => 230,
                'naziv' => 'Rosa 0.75',
                'jm' => 'kom',
                'sto' => '9',
                'porudzbinaid' => 100575,
                'stavkaid' => 317859,
                'stoid' => 740,
            ],
        ];

        $this->postJson('/api/third-party-order', $initialRequest);
        $this->assertEquals(2, ThirdPartyOrderItem::count());

        // Deactivate one item (simulating UI toggle)
        $item = ThirdPartyOrderItem::where('external_item_id', 317859)->first();
        $item->update(['active' => 0]);
        $this->assertEquals(0, $item->fresh()->active);

        // Send same order again (simulating sync from external system)
        $updateRequest = [
            [
                'kolicina' => 3, // qty changed
                'cena' => 150,
                'naziv' => 'Mala dunjevaca 0.03',
                'jm' => 'kom',
                'sto' => '9',
                'porudzbinaid' => 100575,
                'stavkaid' => 317858,
                'stoid' => 740,
            ],
            [
                'kolicina' => 1,
                'cena' => 230,
                'naziv' => 'Rosa 0.75',
                'jm' => 'kom',
                'sto' => '9',
                'porudzbinaid' => 100575,
                'stavkaid' => 317859,
                'stoid' => 740,
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $updateRequest);
        $response->assertStatus(201);

        // Verify item qty was updated
        $item1 = ThirdPartyOrderItem::where('external_item_id', 317858)->first();
        $this->assertEquals(3, $item1->qty);
        $this->assertEquals(1, $item1->active); // Still active

        // Verify deactivated item stayed deactivated
        $item2 = ThirdPartyOrderItem::where('external_item_id', 317859)->first();
        $this->assertEquals(1, $item2->qty);
        $this->assertEquals(0, $item2->active); // Preserved as inactive!
    }

    /**
     * Test item gets properly deleted when removed from update.
     */
    public function test_production_item_removed_on_update()
    {
        // Create order with 3 items
        $initialRequest = [
            [
                'kolicina' => 2,
                'cena' => 150,
                'naziv' => 'Mala dunjevaca 0.03',
                'jm' => 'kom',
                'sto' => '9',
                'porudzbinaid' => 100575,
                'stavkaid' => 317858,
                'stoid' => 740,
            ],
            [
                'kolicina' => 1,
                'cena' => 230,
                'naziv' => 'Rosa 0.75',
                'jm' => 'kom',
                'sto' => '9',
                'porudzbinaid' => 100575,
                'stavkaid' => 317859,
                'stoid' => 740,
            ],
            [
                'kolicina' => 1,
                'cena' => 470,
                'naziv' => 'Tegla Dva brata',
                'jm' => 'kom',
                'sto' => '9',
                'porudzbinaid' => 100575,
                'stavkaid' => 317860,
                'stoid' => 740,
            ],
        ];

        $this->postJson('/api/third-party-order', $initialRequest);
        $this->assertEquals(3, ThirdPartyOrderItem::count());
        $this->assertEquals(1000, ThirdPartyOrder::first()->total); // 2*150+230+470

        // Update: Rosa removed from order
        $updateRequest = [
            [
                'kolicina' => 2,
                'cena' => 150,
                'naziv' => 'Mala dunjevaca 0.03',
                'jm' => 'kom',
                'sto' => '9',
                'porudzbinaid' => 100575,
                'stavkaid' => 317858,
                'stoid' => 740,
            ],
            [
                'kolicina' => 1,
                'cena' => 470,
                'naziv' => 'Tegla Dva brata',
                'jm' => 'kom',
                'sto' => '9',
                'porudzbinaid' => 100575,
                'stavkaid' => 317860,
                'stoid' => 740,
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $updateRequest);
        $response->assertStatus(201);

        // Rosa should be deleted
        $this->assertEquals(2, ThirdPartyOrderItem::count());
        $this->assertNull(ThirdPartyOrderItem::where('external_item_id', 317859)->first());

        // Total should be recalculated
        $this->assertEquals(770, ThirdPartyOrder::first()->total); // 2*150+470
    }

    /**
     * Test different print stations are stored correctly.
     */
    public function test_production_different_print_stations()
    {
        $requestData = [
            [
                'kolicina' => 2,
                'cena' => 150,
                'naziv' => 'Mala dunjevaca 0.03',
                'jm' => 'kom',
                'sto' => '9',
                'stampanjenalogaid' => 1, // Bar printer
                'porudzbinaid' => 100575,
                'stavkaid' => 317858,
                'stoid' => 740,
            ],
            [
                'kolicina' => 1,
                'cena' => 470,
                'naziv' => 'Tegla Dva brata',
                'jm' => 'kom',
                'sto' => '9',
                'stampanjenalogaid' => 2, // Kitchen printer
                'porudzbinaid' => 100575,
                'stavkaid' => 317860,
                'stoid' => 740,
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $requestData);
        $response->assertStatus(201);

        $barItem = ThirdPartyOrderItem::where('external_item_id', 317858)->first();
        $this->assertEquals(1, $barItem->print_station_id);

        $kitchenItem = ThirdPartyOrderItem::where('external_item_id', 317860)->first();
        $this->assertEquals(2, $kitchenItem->print_station_id);
    }

    /**
     * Test decimal quantities work correctly.
     */
    public function test_production_decimal_quantities()
    {
        $requestData = [
            [
                'kolicina' => 2.5, // 2.5 portions
                'cena' => 150,
                'naziv' => 'Mala dunjevaca 0.03',
                'jm' => 'kom',
                'sto' => '9',
                'porudzbinaid' => 100575,
                'stavkaid' => 317858,
                'stoid' => 740,
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $requestData);
        $response->assertStatus(201);

        $item = ThirdPartyOrderItem::first();
        $this->assertEquals(2.5, $item->qty);

        $order = ThirdPartyOrder::first();
        $this->assertEquals(375, $order->total); // 2.5 * 150
    }

    /**
     * Test deleteByTableId deletes all orders for a table.
     */
    public function test_delete_by_table_id()
    {
        // Create 3 orders on same table
        $order1 = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 1000,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order1->id,
            'external_item_id' => 317858,
            'name' => 'Item 1',
            'qty' => 1,
            'price' => 1000,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $order2 = ThirdPartyOrder::create([
            'external_order_id' => 100577,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 2350,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order2->id,
            'external_item_id' => 317862,
            'name' => 'Item 2',
            'qty' => 1,
            'price' => 2350,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // Create order on different table
        $order3 = ThirdPartyOrder::create([
            'external_order_id' => 100580,
            'table_id' => 750,
            'table_name' => '10',
            'total' => 500,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order3->id,
            'external_item_id' => 317900,
            'name' => 'Item 3',
            'qty' => 1,
            'price' => 500,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $this->assertEquals(3, ThirdPartyOrder::count());
        $this->assertEquals(3, ThirdPartyOrderItem::count());

        // Delete orders for table 740
        $deleted = ThirdPartyOrder::deleteByTableId(740);

        $this->assertEquals(2, $deleted);
        $this->assertEquals(1, ThirdPartyOrder::count());
        $this->assertEquals(1, ThirdPartyOrderItem::count());

        // Only table 10 order remains
        $remaining = ThirdPartyOrder::first();
        $this->assertEquals(750, $remaining->table_id);
        $this->assertEquals('10', $remaining->table_name);
    }

    /**
     * Test invoice with stoid deletes all orders for that table.
     */
    public function test_invoice_with_stoid_deletes_table_orders()
    {
        // Create category for inventory
        $category = Category::create([
            'name' => 'Test',
            'slug' => 'test',
        ]);

        // Create 3 orders on table 740
        $order1 = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 1000,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order1->id,
            'external_item_id' => 317858,
            'name' => 'Mala dunjevaca',
            'qty' => 2,
            'price' => 150,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $order2 = ThirdPartyOrder::create([
            'external_order_id' => 100577,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 2350,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order2->id,
            'external_item_id' => 317862,
            'name' => 'Kiseli kupus',
            'qty' => 1,
            'price' => 280,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $order3 = ThirdPartyOrder::create([
            'external_order_id' => 100579,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 300,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order3->id,
            'external_item_id' => 317874,
            'name' => 'Mala dunjevaca',
            'qty' => 2,
            'price' => 150,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $this->assertEquals(3, ThirdPartyOrder::count());
        $this->assertEquals(3, ThirdPartyOrderItem::count());

        // Create invoice with stoid - this should delete all orders for table 740
        $invoiceRequest = [
            [
                'kolicina' => 2,
                'cena' => 150,
                'naziv' => 'Mala dunjevaca',
                'jm' => 'kom',
                'gotovina' => 3650,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'INV-001',
                'sto' => '9',
                'stoid' => 740, // This should trigger deletion of all orders for this table
                'porudzbinaid' => 100575,
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $invoiceRequest);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $response->assertJsonFragment(['orders_deleted' => 3]);
        $response->assertJsonPath('message', 'Invoice stored successfully, 3 order(s) for table closed');

        // All orders should be deleted
        $this->assertEquals(0, ThirdPartyOrder::count());
        $this->assertEquals(0, ThirdPartyOrderItem::count());

        // Invoice should be created
        $this->assertEquals(1, ThirdPartyInvoice::count());
    }

    /**
     * Test invoice with stoid only deletes orders for that specific table.
     */
    public function test_invoice_only_deletes_orders_for_specific_table()
    {
        // Create category for inventory
        $category = Category::create([
            'name' => 'Test',
            'slug' => 'test',
        ]);

        // Create order on table 740
        $order1 = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 1000,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order1->id,
            'external_item_id' => 317858,
            'name' => 'Item 1',
            'qty' => 1,
            'price' => 1000,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // Create order on table 750
        $order2 = ThirdPartyOrder::create([
            'external_order_id' => 100580,
            'table_id' => 750,
            'table_name' => '10',
            'total' => 500,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order2->id,
            'external_item_id' => 317900,
            'name' => 'Item 2',
            'qty' => 1,
            'price' => 500,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $this->assertEquals(2, ThirdPartyOrder::count());

        // Create invoice for table 740 only
        $invoiceRequest = [
            [
                'kolicina' => 1,
                'cena' => 1000,
                'naziv' => 'Item 1',
                'jm' => 'kom',
                'gotovina' => 1000,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'INV-001',
                'sto' => '9',
                'stoid' => 740,
                'porudzbinaid' => 100575,
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $invoiceRequest);

        $response->assertStatus(201);
        $response->assertJsonFragment(['orders_deleted' => 1]);

        // Only table 740 order deleted, table 750 order remains
        $this->assertEquals(1, ThirdPartyOrder::count());
        $remaining = ThirdPartyOrder::first();
        $this->assertEquals(750, $remaining->table_id);
    }

    /**
     * Test invoice without stoid does not delete any orders.
     */
    public function test_invoice_without_stoid_does_not_delete_orders()
    {
        // Create category for inventory
        $category = Category::create([
            'name' => 'Test',
            'slug' => 'test',
        ]);

        // Create order
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 1000,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 317858,
            'name' => 'Item 1',
            'qty' => 1,
            'price' => 1000,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $this->assertEquals(1, ThirdPartyOrder::count());

        // Create invoice WITHOUT stoid
        $invoiceRequest = [
            [
                'kolicina' => 1,
                'cena' => 1000,
                'naziv' => 'Item 1',
                'jm' => 'kom',
                'gotovina' => 1000,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'brojracuna' => 'INV-001',
                'sto' => '9',
                // No stoid!
                'porudzbinaid' => 100575,
            ],
        ];

        $response = $this->postJson('/api/third-party-invoice', $invoiceRequest);

        $response->assertStatus(201);
        $response->assertJsonFragment(['orders_deleted' => 0]);

        // Order should NOT be deleted
        $this->assertEquals(1, ThirdPartyOrder::count());
    }

    /**
     * Test storno endpoint sets items to inactive.
     */
    public function test_storno_sets_items_inactive()
    {
        // Create order with items
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 1000,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 1,
            'name' => 'Item 1',
            'qty' => 1,
            'price' => 500,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 2,
            'name' => 'Item 2',
            'qty' => 1,
            'price' => 500,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // All items active
        $this->assertEquals(2, ThirdPartyOrderItem::where('active', 1)->count());

        // Storno item 1
        $response = $this->postJson('/api/third-party-order-storno', [
            ['stavkaId' => 1, 'storno' => 1],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonFragment(['updated' => 1]);

        // Item 1 should be inactive, item 2 still active
        $this->assertEquals(0, ThirdPartyOrderItem::where('external_item_id', 1)->first()->active);
        $this->assertEquals(1, ThirdPartyOrderItem::where('external_item_id', 2)->first()->active);
    }

    /**
     * Test storno endpoint can reactivate items.
     */
    public function test_storno_can_reactivate_items()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 500,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 1,
            'name' => 'Item 1',
            'qty' => 1,
            'price' => 500,
            'unit' => 'kom',
            'active' => 0, // Already inactive
        ]);

        // Item is inactive
        $this->assertEquals(0, ThirdPartyOrderItem::first()->active);

        // Remove storno (storno = 0)
        $response = $this->postJson('/api/third-party-order-storno', [
            ['stavkaId' => 1, 'storno' => 0],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Item should now be active
        $this->assertEquals(1, ThirdPartyOrderItem::first()->active);
    }

    /**
     * Test storno endpoint with multiple items.
     */
    public function test_storno_multiple_items()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 1500,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 1,
            'name' => 'Item 1',
            'qty' => 1,
            'price' => 500,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 2,
            'name' => 'Item 2',
            'qty' => 1,
            'price' => 500,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 3,
            'name' => 'Item 3',
            'qty' => 1,
            'price' => 500,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // Storno items 1 and 2, keep item 3 active
        $response = $this->postJson('/api/third-party-order-storno', [
            ['stavkaId' => 1, 'storno' => 1],
            ['stavkaId' => 2, 'storno' => 1],
            ['stavkaId' => 3, 'storno' => 0],
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['updated' => 3]);

        // Items 1 and 2 inactive, item 3 active
        $this->assertEquals(0, ThirdPartyOrderItem::where('external_item_id', 1)->first()->active);
        $this->assertEquals(0, ThirdPartyOrderItem::where('external_item_id', 2)->first()->active);
        $this->assertEquals(1, ThirdPartyOrderItem::where('external_item_id', 3)->first()->active);
    }

    /**
     * Test storno endpoint with non-existent items.
     */
    public function test_storno_not_found_items()
    {
        $response = $this->postJson('/api/third-party-order-storno', [
            ['stavkaId' => 999, 'storno' => 1],
            ['stavkaId' => 888, 'storno' => 1],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonFragment(['updated' => 0]);
        $response->assertJsonFragment(['not_found' => 2]);
    }

    /**
     * Test storno endpoint with mixed found and not found items.
     */
    public function test_storno_mixed_found_and_not_found()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 500,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 1,
            'name' => 'Item 1',
            'qty' => 1,
            'price' => 500,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $response = $this->postJson('/api/third-party-order-storno', [
            ['stavkaId' => 1, 'storno' => 1],    // Exists
            ['stavkaId' => 999, 'storno' => 1],  // Does not exist
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['updated' => 1]);
        $response->assertJsonFragment(['not_found' => 1]);

        // Item 1 should be inactive
        $this->assertEquals(0, ThirdPartyOrderItem::where('external_item_id', 1)->first()->active);
    }

    /**
     * Test storno endpoint with empty request.
     */
    public function test_storno_empty_request()
    {
        $response = $this->postJson('/api/third-party-order-storno', []);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'No data provided',
        ]);
    }

    /**
     * Test storno endpoint response structure.
     */
    public function test_storno_response_structure()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 500,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 1,
            'name' => 'Item 1',
            'qty' => 1,
            'price' => 500,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $response = $this->postJson('/api/third-party-order-storno', [
            ['stavkaId' => 1, 'storno' => 1],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'updated',
                'not_found',
                'results' => [
                    '*' => [
                        'external_item_id',
                        'storno',
                        'active',
                        'status',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test storno with lowercase stavkaid (case insensitive).
     */
    public function test_storno_lowercase_stavkaid()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100575,
            'table_id' => 740,
            'table_name' => '9',
            'total' => 500,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 1,
            'name' => 'Item 1',
            'qty' => 1,
            'price' => 500,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // Use lowercase stavkaid
        $response = $this->postJson('/api/third-party-order-storno', [
            ['stavkaid' => 1, 'storno' => 1],
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['updated' => 1]);
        $this->assertEquals(0, ThirdPartyOrderItem::first()->active);
    }
}
