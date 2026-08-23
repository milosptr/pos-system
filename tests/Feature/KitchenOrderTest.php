<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\KitchenOrder;
use App\Models\KitchenOrderItem;
use App\Models\Order;
use App\Models\Table;
use App\Models\ThirdPartyOrder;
use App\Models\ThirdPartyOrderItem;
use App\Models\User;
use App\Http\Middleware\VerifyExternalApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KitchenOrderTest extends TestCase
{
    use RefreshDatabase;

    private function createKitchenOrderWithItems($attributes = [], $items = [])
    {
        $kitchenOrder = KitchenOrder::create(array_merge([
            'orderable_type' => 'order',
            'orderable_id' => '1',
            'table_name' => 'Sto 5',
        ], $attributes));

        if (empty($items)) {
            $items = [['name' => 'Cevapi', 'qty' => 2, 'modifier' => null]];
        }

        foreach ($items as $item) {
            KitchenOrderItem::create(array_merge([
                'kitchen_order_id' => $kitchenOrder->id,
            ], $item));
        }

        return $kitchenOrder;
    }

    /**
     * Test marking a kitchen order as ready.
     */
    public function test_kitchen_order_mark_ready()
    {
        $kitchenOrder = $this->createKitchenOrderWithItems();

        $this->assertNull($kitchenOrder->ready_at);

        $response = $this->postJson("/api/kitchen/orders/{$kitchenOrder->id}/ready");

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['id', 'ready_at', 'items']]);

        $kitchenOrder->refresh();
        $this->assertNotNull($kitchenOrder->ready_at);
    }

    /**
     * An order whose bill was already paid is removed when the kitchen marks it
     * ready — it must never land in "Izdate", because nothing clears it there.
     */
    public function test_invoiced_kitchen_order_is_deleted_on_mark_ready()
    {
        $kitchenOrder = $this->createKitchenOrderWithItems(['invoiced_at' => now()]);

        $response = $this->postJson("/api/kitchen/orders/{$kitchenOrder->id}/ready");

        $response->assertStatus(200);
        $response->assertJson(['deleted' => true]);

        $this->assertEquals(0, KitchenOrder::count(), 'The paid order should be gone');
        $this->assertEquals(0, KitchenOrderItem::count(), 'Its items should be gone too');

        $index = $this->getJson('/api/kitchen/orders');
        $this->assertEmpty($index->json('active'));
        $this->assertEmpty($index->json('ready'), 'A paid order should never reach "Izdate"');
    }

    /**
     * Cashing out a POS table keeps unfinished kitchen orders on the display
     * (flagged as paid) and clears only the ones already handed out.
     */
    public function test_pos_invoice_keeps_active_kitchen_order_and_deletes_ready_one()
    {
        $waiter = User::create([
            'name' => 'Miloš',
            'username' => 'milos',
            'password' => bcrypt('secret'),
        ]);
        $table = Table::create(['name' => '5', 'area' => 1, 'table_number' => 5]);

        $stillCooking = Order::create(['table_id' => $table->id, 'total' => 500, 'order' => []]);
        $alreadyServed = Order::create(['table_id' => $table->id, 'total' => 300, 'order' => []]);

        $activeKitchenOrder = $this->createKitchenOrderWithItems([
            'orderable_id' => $stillCooking->id,
        ]);
        $readyKitchenOrder = $this->createKitchenOrderWithItems([
            'orderable_id' => $alreadyServed->id,
            'ready_at' => now(),
        ]);

        $response = $this->postJson('/api/invoices', [
            'user_id' => $waiter->id,
            'table_id' => $table->id,
            'total' => 800,
            'status' => Invoice::STATUS_REFUNDED,
            'order' => [['name' => 'Cevapi', 'qty' => 2, 'price' => 400]],
        ]);

        $response->assertStatus(201);

        $this->assertEquals(0, Order::count(), 'Both POS orders should be cashed out');

        $this->assertNull(
            KitchenOrder::find($readyKitchenOrder->id),
            'The order already in "Izdate" should be deleted by the invoice'
        );
        $this->assertNotNull(
            KitchenOrder::find($activeKitchenOrder->id),
            'The order the kitchen is still preparing should stay on the display'
        );
        $this->assertNotNull(
            $activeKitchenOrder->fresh()->invoiced_at,
            'It should be flagged as invoiced so it disappears once marked ready'
        );
    }

    /**
     * The same cash-out through the real path waiters use (a PAYED invoice,
     * which also runs SalesService) keeps the unfinished kitchen order too.
     */
    public function test_paid_pos_invoice_runs_sales_and_keeps_active_kitchen_order()
    {
        $waiter = User::create([
            'name' => 'Miloš',
            'username' => 'milos2',
            'password' => bcrypt('secret'),
        ]);
        $table = Table::create(['name' => '6', 'area' => 1, 'table_number' => 6]);

        $stillCooking = Order::create(['table_id' => $table->id, 'total' => 500, 'order' => []]);
        $alreadyServed = Order::create(['table_id' => $table->id, 'total' => 300, 'order' => []]);

        $activeKitchenOrder = $this->createKitchenOrderWithItems([
            'orderable_id' => $stillCooking->id,
        ]);
        $readyKitchenOrder = $this->createKitchenOrderWithItems([
            'orderable_id' => $alreadyServed->id,
            'ready_at' => now(),
        ]);

        $response = $this->postJson('/api/invoices', [
            'user_id' => $waiter->id,
            'table_id' => $table->id,
            'total' => 800,
            'status' => \App\Models\Invoice::STATUS_PAYED,
            'order' => [[
                'id' => 1,
                'name' => 'Cevapi',
                'qty' => 2,
                'price' => 400,
                'category_id' => 1,
                'category_name' => 'Roštilj',
                'table_id' => $table->id,
                'sku' => '000055',
            ]],
        ]);

        $response->assertStatus(201);

        $this->assertEquals(0, Order::count(), 'Both POS orders should be cashed out');
        $this->assertEquals(
            1,
            \App\Models\Sales::count(),
            'A PAYED invoice must record the sale'
        );

        $this->assertNull(
            KitchenOrder::find($readyKitchenOrder->id),
            'The order already in "Izdate" should be deleted by the invoice'
        );
        $activeKitchenOrder = $activeKitchenOrder->fresh();
        $this->assertNotNull(
            $activeKitchenOrder,
            'The order the kitchen is still preparing should stay on the display'
        );
        $this->assertNotNull($activeKitchenOrder->invoiced_at);
    }

    /**
     * New items arriving on a kitchen order already flagged as invoiced mean
     * the order is no longer fully paid — the flag (and ready state) must be
     * cleared so the fresh food does not vanish on the next "ready".
     */
    public function test_new_items_clear_invoiced_flag_on_kitchen_order()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 700001,
            'table_id' => 7,
            'table_name' => '7',
            'total' => 300,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 71,
            'name' => 'Cevapi',
            'qty' => 1,
            'price' => 300,
            'unit' => 'kom',
            'active' => 1,
            'print_station_id' => 2,
        ]);

        $kitchenOrder = KitchenOrder::create([
            'orderable_type' => 'third_party_order',
            'orderable_id' => $order->id,
            'table_name' => 'Sala 7',
            'ready_at' => now(),
            'invoiced_at' => now(),
        ]);
        KitchenOrderItem::create([
            'kitchen_order_id' => $kitchenOrder->id,
            'external_item_id' => 71,
            'name' => 'Cevapi',
            'qty' => 1,
            'storno' => false,
            'is_done' => false,
        ]);

        // A new (unpaid) item lands on the order
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 72,
            'name' => 'Pljeskavica',
            'qty' => 1,
            'price' => 350,
            'unit' => 'kom',
            'active' => 1,
            'print_station_id' => 2,
        ]);

        \Services\KitchenService::processThirdPartyOrder($order->fresh());

        $kitchenOrder = $kitchenOrder->fresh();
        $this->assertNull($kitchenOrder->invoiced_at, 'New items mean the order is no longer fully paid');
        $this->assertNull($kitchenOrder->ready_at, 'New items put the order back into "Aktivne"');
        $this->assertEquals(2, $kitchenOrder->items()->count());
    }

    /**
     * The kitchen display exposes invoiced_at so a paid order can be marked.
     */
    public function test_invoiced_at_is_returned_in_index()
    {
        $this->createKitchenOrderWithItems(['invoiced_at' => now()]);

        $response = $this->getJson('/api/kitchen/orders');

        $response->assertStatus(200);
        $this->assertNotNull($response->json('active.0.invoiced_at'));
    }

    /**
     * Test undoing a kitchen order's ready status.
     */
    public function test_kitchen_order_undo_ready()
    {
        $kitchenOrder = $this->createKitchenOrderWithItems(['ready_at' => now()]);

        $this->assertNotNull($kitchenOrder->ready_at);

        $response = $this->postJson("/api/kitchen/orders/{$kitchenOrder->id}/undo");

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['id', 'ready_at', 'items']]);

        $kitchenOrder->refresh();
        $this->assertNull($kitchenOrder->ready_at);
    }

    /**
     * Test kitchen orders index returns active and ready lists.
     */
    public function test_kitchen_orders_index_returns_active_and_ready()
    {
        $this->createKitchenOrderWithItems([
            'orderable_id' => '1',
            'table_name' => 'Sto 1',
        ], [['name' => 'Cevapi', 'qty' => 1, 'modifier' => null]]);

        $this->createKitchenOrderWithItems([
            'orderable_id' => '2',
            'table_name' => 'Sto 2',
        ], [['name' => 'Pljeskavica', 'qty' => 1, 'modifier' => null]]);

        $this->createKitchenOrderWithItems([
            'orderable_type' => 'third_party_order',
            'orderable_id' => '3',
            'table_name' => 'Sto 3',
            'ready_at' => now(),
        ], [['name' => 'Karadjordjeva', 'qty' => 1, 'modifier' => null]]);

        $response = $this->getJson('/api/kitchen/orders');

        $response->assertStatus(200);
        $response->assertJsonStructure(['active', 'ready']);

        $data = $response->json();
        $this->assertCount(2, $data['active']);
        $this->assertCount(1, $data['ready']);
    }

    /**
     * Test third-party order item stores SKU field.
     */
    public function test_third_party_order_sku_stored()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 100001,
            'table_id' => 500,
            'table_name' => 'Sto 5',
            'total' => 100,
        ]);

        $item = ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 1001,
            'name' => 'Cevapi',
            'qty' => 1,
            'price' => 100,
            'unit' => 'kom',
            'sku' => '000055',
            'active' => 1,
        ]);

        $this->assertEquals('000055', $item->fresh()->sku);
    }

    /**
     * Test kitchen:cleanup command truncates all kitchen orders and items.
     */
    public function test_kitchen_cleanup_command()
    {
        $this->createKitchenOrderWithItems([
            'orderable_id' => '1',
            'table_name' => 'Sto 1',
        ], [['name' => 'Cevapi', 'qty' => 1, 'modifier' => null]]);

        $this->createKitchenOrderWithItems([
            'orderable_id' => '2',
            'table_name' => 'Sto 2',
            'ready_at' => now(),
        ], [['name' => 'Pljeskavica', 'qty' => 1, 'modifier' => null]]);

        $this->assertEquals(2, KitchenOrder::count());
        $this->assertEquals(2, KitchenOrderItem::count());

        $this->artisan('kitchen:cleanup')
            ->expectsOutput('Deleted 2 kitchen orders.')
            ->assertExitCode(0);

        $this->assertEquals(0, KitchenOrder::count());
        $this->assertEquals(0, KitchenOrderItem::count());
    }

    /**
     * Test toggling item done on and off.
     */
    public function test_toggle_item_done()
    {
        $kitchenOrder = $this->createKitchenOrderWithItems(['orderable_id' => '100']);
        $item = $kitchenOrder->items->first();

        $this->assertFalse($item->is_done);

        // Toggle on
        $response = $this->postJson("/api/kitchen/items/{$item->id}/toggle-done");
        $response->assertStatus(200);
        $response->assertJson(['is_done' => true]);

        $item->refresh();
        $this->assertTrue($item->is_done);

        // Toggle off
        $response = $this->postJson("/api/kitchen/items/{$item->id}/toggle-done");
        $response->assertStatus(200);
        $response->assertJson(['is_done' => false]);

        $item->refresh();
        $this->assertFalse($item->is_done);
    }

    /**
     * Test kitchen order items are returned in index with correct structure.
     */
    public function test_kitchen_order_items_returned_in_index()
    {
        $this->createKitchenOrderWithItems(['orderable_id' => '200'], [
            ['name' => 'Cevapi', 'qty' => 2, 'modifier' => 'bez luka'],
        ]);

        $response = $this->getJson('/api/kitchen/orders');
        $response->assertStatus(200);

        $items = $response->json('active.0.items');
        $this->assertCount(1, $items);
        $this->assertArrayHasKey('id', $items[0]);
        $this->assertArrayHasKey('name', $items[0]);
        $this->assertArrayHasKey('is_done', $items[0]);
        $this->assertEquals('Cevapi', $items[0]['name']);
        $this->assertEquals('bez luka', $items[0]['modifier']);
        $this->assertFalse($items[0]['is_done']);
    }

    /**
     * Test third-party kitchen items use print_station_id for filtering.
     */
    public function test_third_party_kitchen_items_use_print_station_id()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 200001,
            'table_id' => 600,
            'table_name' => 'Sto 6',
            'total' => 500,
        ]);

        // Kitchen item (print_station_id = 2)
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 2001,
            'name' => 'Cevapi',
            'qty' => 1,
            'price' => 300,
            'unit' => 'kom',
            'print_station_id' => 2,
            'active' => 1,
        ]);

        // Non-kitchen item (print_station_id = 1)
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 2002,
            'name' => 'Coca Cola',
            'qty' => 1,
            'price' => 200,
            'unit' => 'kom',
            'print_station_id' => 1,
            'active' => 1,
        ]);

        $kitchenOrder = \Services\KitchenService::processThirdPartyOrder($order);

        $this->assertNotNull($kitchenOrder);
        $this->assertCount(1, $kitchenOrder->items);
        $this->assertEquals('Cevapi', $kitchenOrder->items->first()->name);
    }

    /**
     * An order already closed by an invoice this working day is skipped when
     * the external system resends it — no ghost table, no kitchen dispatch.
     */
    public function test_invoiced_orders_not_re_added_to_kitchen()
    {
        $this->withoutMiddleware(VerifyExternalApiKey::class);

        // 1. Create a third-party order with a kitchen item via the API
        $payload = [[
            'porudzbinaid' => 999001,
            'stoid' => 800,
            'sto' => 'Sto 8',
            'datum' => now()->toDateTimeString(),
            'stavkaid' => 9001,
            'naziv' => 'Cevapi',
            'kolicina' => 2,
            'cena' => 300,
            'jm' => 'kom',
            'stampanjenalogaid' => 2,
            'sifraArtikla' => '000055',
        ]];

        $response = $this->postJson('/api/third-party-order', $payload);
        $response->assertStatus(201);

        $order = ThirdPartyOrder::where('external_order_id', 999001)->first();
        $this->assertNotNull($order);

        $kitchenOrder = KitchenOrder::where('orderable_type', 'third_party_order')
            ->where('orderable_id', $order->id)
            ->first();
        $this->assertNotNull($kitchenOrder, 'Kitchen order should exist after initial import');

        // 2. Close the order through the real invoice path (stamps invoiced_at)
        ThirdPartyOrder::deleteByExternalItemIds([9001]);

        $this->assertNull(ThirdPartyOrder::find($order->id), 'Order should be soft-deleted');
        $trashed = ThirdPartyOrder::onlyTrashed()->where('external_order_id', 999001)->first();
        $this->assertNotNull($trashed);
        $this->assertNotNull($trashed->invoiced_at, 'Invoice path should stamp invoiced_at');

        // 3. Re-send the same order (ebar sends all orders for the table)
        $response = $this->postJson('/api/third-party-order', $payload);
        $response->assertStatus(201);
        $response->assertJsonPath('summary.skipped_invoiced', [999001]);

        // 4. The paid order does not come back at all — no ghost table row...
        $this->assertNull(
            ThirdPartyOrder::where('external_order_id', 999001)->first(),
            'A resent paid order must not reappear as a live order'
        );

        // ...and no new kitchen dispatch: only the original (still unfinished,
        // now flagged as invoiced) kitchen order remains on the display.
        $this->assertEquals(
            1,
            KitchenOrder::where('orderable_type', 'third_party_order')->count(),
            'Invoiced order should NOT be re-added to kitchen'
        );
        $this->assertNotNull($kitchenOrder->fresh()->invoiced_at);
    }

    /**
     * Test re-sending same order does not reset ready_at on kitchen order.
     */
    public function test_resend_same_order_preserves_ready_at()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 400001,
            'table_id' => 900,
            'table_name' => 'Sto 9',
            'total' => 600,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 4001,
            'name' => 'Cevapi',
            'qty' => 2,
            'price' => 300,
            'unit' => 'kom',
            'print_station_id' => 2,
            'active' => 1,
        ]);

        // First process — creates kitchen order
        $kitchenOrder = \Services\KitchenService::processThirdPartyOrder($order);
        $this->assertNotNull($kitchenOrder);
        $this->assertNull($kitchenOrder->ready_at);

        // Mark as ready (moved to "Izdate")
        $kitchenOrder->update(['ready_at' => now()]);
        $this->assertNotNull($kitchenOrder->fresh()->ready_at);

        // Re-process same order (ebar re-sends it with no changes)
        $kitchenOrder = \Services\KitchenService::processThirdPartyOrder($order);

        // ready_at should still be set
        $this->assertNotNull($kitchenOrder->fresh()->ready_at, 'Re-sending same order should NOT reset ready_at');
    }

    /**
     * Test third-party sync preserves is_done state for existing items.
     */
    public function test_third_party_sync_preserves_is_done()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 300001,
            'table_id' => 700,
            'table_name' => 'Sto 7',
            'total' => 300,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 3001,
            'name' => 'Cevapi',
            'qty' => 1,
            'price' => 300,
            'unit' => 'kom',
            'print_station_id' => 2,
            'active' => 1,
        ]);

        // First process
        $kitchenOrder = \Services\KitchenService::processThirdPartyOrder($order);
        $this->assertCount(1, $kitchenOrder->items);

        // Mark item as done
        $kitchenOrder->items->first()->update(['is_done' => true]);

        // Add a new item to the third-party order
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 3002,
            'name' => 'Pljeskavica',
            'qty' => 1,
            'price' => 400,
            'unit' => 'kom',
            'print_station_id' => 2,
            'active' => 1,
        ]);

        // Reprocess
        $kitchenOrder = \Services\KitchenService::processThirdPartyOrder($order);
        $kitchenOrder->load('items');

        $this->assertCount(2, $kitchenOrder->items);

        $cevapiItem = $kitchenOrder->items->firstWhere('external_item_id', 3001);
        $this->assertTrue($cevapiItem->is_done);

        $pljeskavicaItem = $kitchenOrder->items->firstWhere('external_item_id', 3002);
        $this->assertFalse($pljeskavicaItem->is_done);
    }

    /**
     * Test POS order saves category_id and sku to kitchen items.
     */
    public function test_pos_order_saves_category_id_and_sku()
    {
        // Create a kitchen category (parent_id = 1)
        $category = Category::create([
            'name' => 'Roštilj',
            'parent_id' => 1,
        ]);

        $table = Table::create(['name' => 'Sto 10', 'table_number' => 10]);

        $order = Order::create([
            'table_id' => $table->id,
            'total' => 600,
            'order' => [
                [
                    'name' => 'Cevapi',
                    'qty' => 2,
                    'modifier' => null,
                    'category_id' => $category->id,
                    'sku' => '000055',
                ],
            ],
        ]);

        $kitchenOrder = \Services\KitchenService::processOrder($order);

        $this->assertNotNull($kitchenOrder);
        $item = $kitchenOrder->items->first();
        $this->assertEquals($category->id, $item->category_id);
        $this->assertEquals('000055', $item->sku);
    }

    /**
     * Test third-party order resolves category_id via inventory lookup.
     */
    public function test_third_party_order_resolves_category_id()
    {
        $category = Category::create([
            'name' => 'Roštilj',
            'parent_id' => 1,
        ]);

        Inventory::create([
            'name' => 'Cevapi',
            'sku' => '000055',
            'category_id' => $category->id,
            'price' => 300,
        ]);

        $order = ThirdPartyOrder::create([
            'external_order_id' => 500001,
            'table_id' => 800,
            'table_name' => 'Sto 8',
            'total' => 300,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 5001,
            'name' => 'Cevapi',
            'qty' => 1,
            'price' => 300,
            'unit' => 'kom',
            'sku' => '000055',
            'print_station_id' => 2,
            'active' => 1,
        ]);

        $kitchenOrder = \Services\KitchenService::processThirdPartyOrder($order);

        $this->assertNotNull($kitchenOrder);
        $item = $kitchenOrder->items->first();
        $this->assertEquals($category->id, $item->category_id);
        $this->assertEquals('000055', $item->sku);
    }

    /**
     * Test assigning a waiter to a kitchen order.
     */
    public function test_assign_waiter_to_kitchen_order()
    {
        $kitchenOrder = $this->createKitchenOrderWithItems();

        $this->assertNull($kitchenOrder->waiter_name);

        $response = $this->postJson("/api/kitchen/orders/{$kitchenOrder->id}/assign-waiter", [
            'waiter_name' => 'Marko',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.waiter_name', 'Marko');

        $kitchenOrder->refresh();
        $this->assertEquals('Marko', $kitchenOrder->waiter_name);
    }

    /**
     * Test waiter_name is included in index response.
     */
    public function test_waiter_name_included_in_index_response()
    {
        $this->createKitchenOrderWithItems([
            'orderable_id' => '1',
            'table_name' => 'Sto 1',
            'waiter_name' => 'Jelena',
        ]);

        $response = $this->getJson('/api/kitchen/orders');
        $response->assertStatus(200);
        $this->assertEquals('Jelena', $response->json('active.0.waiter_name'));
    }

    /**
     * Test waiter_name is nullable (orders without assignment).
     */
    public function test_waiter_name_is_nullable()
    {
        $kitchenOrder = $this->createKitchenOrderWithItems();

        $response = $this->getJson('/api/kitchen/orders');
        $response->assertStatus(200);
        $this->assertNull($response->json('active.0.waiter_name'));
    }

    /**
     * Test assigning waiter to non-existent order returns 404.
     */
    public function test_assign_waiter_to_nonexistent_order_returns_404()
    {
        $response = $this->postJson('/api/kitchen/orders/99999/assign-waiter', [
            'waiter_name' => 'Marko',
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test processThirdPartyOrder sets waiter_name when provided.
     */
    public function test_process_third_party_order_sets_waiter_name()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 600001,
            'table_id' => 100,
            'table_name' => 'Sto 1',
            'total' => 300,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 6001,
            'name' => 'Cevapi',
            'qty' => 1,
            'price' => 300,
            'unit' => 'kom',
            'print_station_id' => 2,
            'active' => 1,
        ]);

        $kitchenOrder = \Services\KitchenService::processThirdPartyOrder($order, 'Marko');

        $this->assertNotNull($kitchenOrder);
        $this->assertEquals('Marko', $kitchenOrder->waiter_name);
    }

    /**
     * Test processThirdPartyOrder does not overwrite waiter_name when null.
     */
    public function test_process_third_party_order_preserves_waiter_name_when_null()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 600002,
            'table_id' => 100,
            'table_name' => 'Sto 1',
            'total' => 300,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 6002,
            'name' => 'Cevapi',
            'qty' => 1,
            'price' => 300,
            'unit' => 'kom',
            'print_station_id' => 2,
            'active' => 1,
        ]);

        // First call with waiter name
        $kitchenOrder = \Services\KitchenService::processThirdPartyOrder($order, 'Jelena');
        $this->assertEquals('Jelena', $kitchenOrder->waiter_name);

        // Second call without waiter name — should preserve existing
        $kitchenOrder = \Services\KitchenService::processThirdPartyOrder($order, null);
        $this->assertEquals('Jelena', $kitchenOrder->fresh()->waiter_name);
    }

    /**
     * Test processThirdPartyOrder does not set waiter_name for empty string.
     */
    public function test_process_third_party_order_ignores_empty_waiter_name()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 600003,
            'table_id' => 100,
            'table_name' => 'Sto 1',
            'total' => 300,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 6003,
            'name' => 'Cevapi',
            'qty' => 1,
            'price' => 300,
            'unit' => 'kom',
            'print_station_id' => 2,
            'active' => 1,
        ]);

        $kitchenOrder = \Services\KitchenService::processThirdPartyOrder($order, '');

        $this->assertNotNull($kitchenOrder);
        $this->assertNull($kitchenOrder->waiter_name);
    }

    /**
     * Test third-party order API passes konobar field to kitchen order.
     */
    public function test_third_party_order_api_passes_konobar_to_kitchen()
    {
        $this->withoutMiddleware(VerifyExternalApiKey::class);

        $payload = [
            [
                'porudzbinaid' => 600004,
                'stoid' => 100,
                'sto' => 'Sto 1',
                'datum' => now()->toDateTimeString(),
                'stavkaid' => 6004,
                'naziv' => 'Cevapi',
                'kolicina' => 1,
                'cena' => 300,
                'jm' => 'kom',
                'stampanjenalogaid' => 1,
            ],
            [
                'porudzbinaid' => 600004,
                'stoid' => 100,
                'sto' => 'Sto 1',
                'datum' => now()->toDateTimeString(),
                'stavkaid' => 6005,
                'naziv' => 'Kafa',
                'kolicina' => 1,
                'cena' => 120,
                'jm' => 'kom',
                'stampanjenalogaid' => 2,
                'konobar' => 'Petar',
            ],
        ];

        $response = $this->postJson('/api/third-party-order', $payload);
        $response->assertStatus(201);

        $order = ThirdPartyOrder::where('external_order_id', 600004)->first();
        $kitchenOrder = KitchenOrder::where('orderable_type', 'third_party_order')
            ->where('orderable_id', $order->id)
            ->first();

        $this->assertNotNull($kitchenOrder);
        $this->assertEquals('Petar', $kitchenOrder->waiter_name);
    }

    /**
     * Test third-party order API without konobar field leaves waiter_name null.
     */
    public function test_third_party_order_api_without_konobar_leaves_waiter_null()
    {
        $this->withoutMiddleware(VerifyExternalApiKey::class);

        $payload = [[
            'porudzbinaid' => 600005,
            'stoid' => 100,
            'sto' => 'Sto 1',
            'datum' => now()->toDateTimeString(),
            'stavkaid' => 6005,
            'naziv' => 'Cevapi',
            'kolicina' => 1,
            'cena' => 300,
            'jm' => 'kom',
            'stampanjenalogaid' => 2,
        ]];

        $response = $this->postJson('/api/third-party-order', $payload);
        $response->assertStatus(201);

        $order = ThirdPartyOrder::where('external_order_id', 600005)->first();
        $kitchenOrder = KitchenOrder::where('orderable_type', 'third_party_order')
            ->where('orderable_id', $order->id)
            ->first();

        $this->assertNotNull($kitchenOrder);
        $this->assertNull($kitchenOrder->waiter_name);
    }

    /**
     * Test API response includes category_id in kitchen order items.
     */
    public function test_api_response_includes_category_id()
    {
        $this->createKitchenOrderWithItems(['orderable_id' => '300'], [
            ['name' => 'Cevapi', 'qty' => 2, 'modifier' => null, 'category_id' => 14, 'sku' => '000055'],
        ]);

        $response = $this->getJson('/api/kitchen/orders');
        $response->assertStatus(200);

        $items = $response->json('active.0.items');
        $this->assertArrayHasKey('category_id', $items[0]);
        $this->assertEquals(14, $items[0]['category_id']);
    }
}
