<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\KitchenOrder;
use App\Models\KitchenOrderItem;
use App\Models\ThirdPartyOrder;
use App\Models\ThirdPartyOrderItem;
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
}
