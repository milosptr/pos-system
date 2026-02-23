<?php

namespace Tests\Feature;

use App\Models\KitchenOrder;
use App\Models\KitchenOrderItem;
use App\Models\ThirdPartyInvoice;
use App\Models\ThirdPartyOrder;
use App\Models\ThirdPartyOrderItem;
use App\Http\Middleware\VerifyExternalApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Services\Pusher;
use Tests\TestCase;

class ThirdPartyInvoiceOrderClearingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Bypass API key middleware for tests
        $this->withoutMiddleware(VerifyExternalApiKey::class);
    }

    // =========================================================================
    // Group 1: deleteByExternalItemIds Unit-Style Tests
    // =========================================================================

    /**
     * Test that deleteByExternalItemIds deletes matching items by their external_item_id.
     */
    public function test_deleteByExternalItemIds_deletes_matching_items()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 630,
        ]);

        $item1 = ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $item2 = ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $item3 = ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 327010,
            'name' => 'Rosa 0.75',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $this->assertEquals(3, ThirdPartyOrderItem::count(), 'Should start with 3 items');

        ThirdPartyOrder::deleteByExternalItemIds([326984, 326998]);

        $this->assertEquals(
            1,
            ThirdPartyOrderItem::count(),
            'Only the item NOT in the external ID list should remain'
        );
        $this->assertNotNull(
            ThirdPartyOrderItem::where('external_item_id', 327010)->first(),
            'Item 327010 should still exist because it was not in the deletion list'
        );
        $this->assertNull(
            ThirdPartyOrderItem::where('external_item_id', 326984)->first(),
            'Item 326984 should be deleted'
        );
        $this->assertNull(
            ThirdPartyOrderItem::where('external_item_id', 326998)->first(),
            'Item 326998 should be deleted'
        );
    }

    /**
     * Test that deleteByExternalItemIds soft-deletes the parent order when all items are removed.
     */
    public function test_deleteByExternalItemIds_deletes_empty_orders()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 420,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $this->assertEquals(1, ThirdPartyOrder::count(), 'Should start with 1 order');

        $deleted = ThirdPartyOrder::deleteByExternalItemIds([326984, 326998]);

        $this->assertEquals(1, $deleted, 'Should report 1 order deleted');
        $this->assertEquals(
            0,
            ThirdPartyOrder::count(),
            'Order should be soft-deleted after all its items were removed'
        );
        $this->assertEquals(
            0,
            ThirdPartyOrderItem::count(),
            'All items should be soft-deleted'
        );
    }

    /**
     * Test that deleteByExternalItemIds keeps orders that still have remaining items.
     */
    public function test_deleteByExternalItemIds_keeps_orders_with_remaining_items()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 630,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 327010,
            'name' => 'Rosa 0.75',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $deleted = ThirdPartyOrder::deleteByExternalItemIds([326984, 326998]);

        $this->assertEquals(
            0,
            $deleted,
            'Should report 0 orders deleted because 1 item still remains'
        );
        $this->assertEquals(
            1,
            ThirdPartyOrder::count(),
            'Order should still exist because it has a remaining item'
        );
        $this->assertEquals(
            1,
            ThirdPartyOrderItem::count(),
            'Only the remaining item (327010) should exist'
        );
    }

    /**
     * Test that deleteByExternalItemIds also deletes matching KitchenOrderItems.
     */
    public function test_deleteByExternalItemIds_deletes_kitchen_order_items()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 630,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 327010,
            'name' => 'Rosa 0.75',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // Create corresponding kitchen order and items
        $kitchenOrder = KitchenOrder::create([
            'orderable_type' => 'third_party_order',
            'orderable_id' => $order->id,
            'table_name' => 'Sala 2',
        ]);

        KitchenOrderItem::create([
            'kitchen_order_id' => $kitchenOrder->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'storno' => false,
            'is_done' => false,
        ]);

        KitchenOrderItem::create([
            'kitchen_order_id' => $kitchenOrder->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'storno' => false,
            'is_done' => false,
        ]);

        KitchenOrderItem::create([
            'kitchen_order_id' => $kitchenOrder->id,
            'external_item_id' => 327010,
            'name' => 'Rosa 0.75',
            'qty' => 1,
            'storno' => false,
            'is_done' => false,
        ]);

        $this->assertEquals(3, KitchenOrderItem::count(), 'Should start with 3 kitchen items');

        ThirdPartyOrder::deleteByExternalItemIds([326984, 326998]);

        $this->assertEquals(
            1,
            KitchenOrderItem::count(),
            'Only the kitchen item for 327010 should remain after deleting items 326984 and 326998'
        );
        $this->assertNotNull(
            KitchenOrderItem::where('external_item_id', 327010)->first(),
            'Kitchen item 327010 should still exist'
        );
    }

    /**
     * Test full cascade: items deleted -> order empty -> kitchen order also deleted.
     */
    public function test_deleteByExternalItemIds_deletes_kitchen_order_when_order_empty()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 420,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $kitchenOrder = KitchenOrder::create([
            'orderable_type' => 'third_party_order',
            'orderable_id' => $order->id,
            'table_name' => 'Sala 2',
        ]);

        KitchenOrderItem::create([
            'kitchen_order_id' => $kitchenOrder->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'storno' => false,
            'is_done' => false,
        ]);

        KitchenOrderItem::create([
            'kitchen_order_id' => $kitchenOrder->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'storno' => false,
            'is_done' => false,
        ]);

        $this->assertEquals(1, KitchenOrder::count(), 'Should start with 1 kitchen order');

        $deleted = ThirdPartyOrder::deleteByExternalItemIds([326984, 326998]);

        $this->assertEquals(1, $deleted, 'Should delete 1 order');
        $this->assertEquals(
            0,
            ThirdPartyOrder::count(),
            'ThirdPartyOrder should be soft-deleted when all items are removed'
        );
        $this->assertEquals(
            0,
            KitchenOrder::count(),
            'KitchenOrder should be deleted when its parent order has no items left'
        );
        $this->assertEquals(
            0,
            KitchenOrderItem::count(),
            'KitchenOrderItems should be deleted along with their kitchen order items'
        );
    }

    /**
     * Test that kitchen order persists when its parent order still has remaining items.
     */
    public function test_deleteByExternalItemIds_keeps_kitchen_order_with_remaining_items()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 630,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 327010,
            'name' => 'Rosa 0.75',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $kitchenOrder = KitchenOrder::create([
            'orderable_type' => 'third_party_order',
            'orderable_id' => $order->id,
            'table_name' => 'Sala 2',
        ]);

        KitchenOrderItem::create([
            'kitchen_order_id' => $kitchenOrder->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'storno' => false,
            'is_done' => false,
        ]);

        KitchenOrderItem::create([
            'kitchen_order_id' => $kitchenOrder->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'storno' => false,
            'is_done' => false,
        ]);

        KitchenOrderItem::create([
            'kitchen_order_id' => $kitchenOrder->id,
            'external_item_id' => 327010,
            'name' => 'Rosa 0.75',
            'qty' => 1,
            'storno' => false,
            'is_done' => false,
        ]);

        ThirdPartyOrder::deleteByExternalItemIds([326984, 326998]);

        $this->assertEquals(
            1,
            ThirdPartyOrder::count(),
            'Order should persist because item 327010 still remains'
        );
        $this->assertEquals(
            1,
            KitchenOrder::count(),
            'Kitchen order should persist because the parent order still has items'
        );
        $this->assertEquals(
            1,
            KitchenOrderItem::count(),
            'Only kitchen item 327010 should remain'
        );
    }

    /**
     * Test deleteByExternalItemIds handles items spread across multiple orders.
     */
    public function test_deleteByExternalItemIds_handles_items_across_multiple_orders()
    {
        // Order 1: 2 items, both will be deleted -> order should be deleted
        $order1 = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 420,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order1->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order1->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // Order 2: 1 item, will be deleted -> order should be deleted
        $order2 = ThirdPartyOrder::create([
            'external_order_id' => 104131,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 230,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order2->id,
            'external_item_id' => 327020,
            'name' => 'Rosa 0.75',
            'qty' => 1,
            'price' => 230,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // Order 3: 3 items, only 1 will be deleted -> order should persist with 2 items
        $order3 = ThirdPartyOrder::create([
            'external_order_id' => 104132,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 790,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order3->id,
            'external_item_id' => 327030,
            'name' => 'Kiseli kupus',
            'qty' => 1,
            'price' => 280,
            'unit' => 'kom',
            'active' => 1,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order3->id,
            'external_item_id' => 327031,
            'name' => 'Vocni sok',
            'qty' => 1,
            'price' => 300,
            'unit' => 'kom',
            'active' => 1,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order3->id,
            'external_item_id' => 327032,
            'name' => 'Palacinke',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $this->assertEquals(3, ThirdPartyOrder::count(), 'Should start with 3 orders');
        $this->assertEquals(6, ThirdPartyOrderItem::count(), 'Should start with 6 items total');

        // Delete all items from order 1 and order 2, plus 1 item from order 3
        $deleted = ThirdPartyOrder::deleteByExternalItemIds([
            326984, 326998, // all from order 1
            327020,          // all from order 2
            327030,          // partial from order 3
        ]);

        $this->assertEquals(
            2,
            $deleted,
            'Should delete 2 orders (order 1 and order 2 fully emptied)'
        );
        $this->assertEquals(
            1,
            ThirdPartyOrder::count(),
            'Only order 3 should remain (it still has items 327031 and 327032)'
        );
        $this->assertEquals(
            2,
            ThirdPartyOrderItem::count(),
            'Only items 327031 and 327032 from order 3 should remain'
        );

        $remainingOrder = ThirdPartyOrder::first();
        $this->assertEquals(
            $order3->id,
            $remainingOrder->id,
            'The remaining order should be order 3'
        );
    }

    /**
     * Test deleteByExternalItemIds returns 0 when no matching IDs exist.
     */
    public function test_deleteByExternalItemIds_returns_zero_for_nonexistent_ids()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 210,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $deleted = ThirdPartyOrder::deleteByExternalItemIds([999999, 888888]);

        $this->assertEquals(
            0,
            $deleted,
            'Should return 0 when no external_item_ids match'
        );
        $this->assertEquals(
            1,
            ThirdPartyOrder::count(),
            'No orders should be deleted when IDs do not match'
        );
        $this->assertEquals(
            1,
            ThirdPartyOrderItem::count(),
            'No items should be deleted when IDs do not match'
        );
    }

    /**
     * Test deleteByExternalItemIds returns the correct count of fully deleted orders.
     */
    public function test_deleteByExternalItemIds_returns_count_of_deleted_orders()
    {
        // Create 3 orders, each with 1 item
        $order1 = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 210,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order1->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $order2 = ThirdPartyOrder::create([
            'external_order_id' => 104131,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 210,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order2->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $order3 = ThirdPartyOrder::create([
            'external_order_id' => 104132,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 230,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order3->id,
            'external_item_id' => 327010,
            'name' => 'Rosa 0.75',
            'qty' => 1,
            'price' => 230,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $deleted = ThirdPartyOrder::deleteByExternalItemIds([326984, 326998, 327010]);

        $this->assertEquals(
            3,
            $deleted,
            'Should return 3 because all 3 orders were fully emptied and deleted'
        );
        $this->assertEquals(0, ThirdPartyOrder::count(), 'All orders should be deleted');
        $this->assertEquals(0, ThirdPartyOrderItem::count(), 'All items should be deleted');
    }

    // =========================================================================
    // Group 2: Invoice API Integration Tests (POST /api/third-party-invoice)
    // =========================================================================

    /**
     * Test that an invoice with listastavki clears matching order items.
     */
    public function test_invoice_with_listastavki_clears_matching_order_items()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 420,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $this->assertEquals(1, ThirdPartyOrder::count());
        $this->assertEquals(2, ThirdPartyOrderItem::count());

        $response = $this->postJson('/api/third-party-invoice', [
            [
                'kolicina' => 1,
                'cena' => 210,
                'naziv' => 'Coca cola 0.25',
                'jm' => 'kom',
                'gotovina' => 420,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-02-23 22:01:10',
                'brojracuna' => 29910,
                'sto' => '2',
                'klijentid' => 0,
                'pibkupca' => null,
                'sifraArtikla' => 150,
                'stornoreferenceid' => null,
                'racunid' => 42933,
                'stoid' => 2,
                'stornirano' => 0,
                'listastavki' => '326984, 326998',
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        $this->assertEquals(
            0,
            ThirdPartyOrder::count(),
            'Order should be deleted because all its items were cleared via listastavki'
        );
        $this->assertEquals(
            0,
            ThirdPartyOrderItem::count(),
            'Both items should be deleted via listastavki IDs'
        );
        $this->assertEquals(
            1,
            ThirdPartyInvoice::count(),
            'The invoice itself should be created'
        );
    }

    /**
     * Invoice with multiple rows collects listastavki from ALL rows and clears all matching items.
     */
    public function test_invoice_with_multi_row_listastavki_clears_all_rows_items()
    {
        // Create order 1 with items referenced by row 1's listastavki
        $order1 = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 420,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order1->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order1->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // Create order 2 with items referenced by row 2's listastavki
        $order2 = ThirdPartyOrder::create([
            'external_order_id' => 104131,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 560,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order2->id,
            'external_item_id' => 327050,
            'name' => 'Kiseli kupus',
            'qty' => 1,
            'price' => 280,
            'unit' => 'kom',
            'active' => 1,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order2->id,
            'external_item_id' => 327051,
            'name' => 'Pasulj',
            'qty' => 1,
            'price' => 280,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // Create order 3 with items referenced by row 3's listastavki
        $order3 = ThirdPartyOrder::create([
            'external_order_id' => 104132,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 300,
        ]);
        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order3->id,
            'external_item_id' => 327080,
            'name' => 'Palacinke',
            'qty' => 1,
            'price' => 300,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $this->assertEquals(3, ThirdPartyOrder::count(), 'Should start with 3 orders');
        $this->assertEquals(5, ThirdPartyOrderItem::count(), 'Should start with 5 items total');

        // POST invoice with MULTIPLE rows, each having DIFFERENT listastavki values
        $response = $this->postJson('/api/third-party-invoice', [
            [
                'kolicina' => 2,
                'cena' => 210,
                'naziv' => 'Coca cola 0.25',
                'jm' => 'kom',
                'gotovina' => 1280,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-02-23 22:01:10',
                'brojracuna' => 29910,
                'sto' => '2',
                'klijentid' => 0,
                'pibkupca' => null,
                'sifraArtikla' => 150,
                'stornoreferenceid' => null,
                'racunid' => 42933,
                'stoid' => 2,
                'stornirano' => 0,
                'listastavki' => '326984, 326998', // Row 1: items from order 1
            ],
            [
                'kolicina' => 2,
                'cena' => 280,
                'naziv' => 'Kiseli kupus',
                'jm' => 'kom',
                'gotovina' => 0,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-02-23 22:01:10',
                'brojracuna' => 29910,
                'sto' => '2',
                'klijentid' => 0,
                'pibkupca' => null,
                'sifraArtikla' => 200,
                'stornoreferenceid' => null,
                'racunid' => 42933,
                'stoid' => 2,
                'stornirano' => 0,
                'listastavki' => '327050, 327051', // Row 2: items from order 2 - IGNORED due to bug!
            ],
            [
                'kolicina' => 1,
                'cena' => 300,
                'naziv' => 'Palacinke',
                'jm' => 'kom',
                'gotovina' => 0,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-02-23 22:01:10',
                'brojracuna' => 29910,
                'sto' => '2',
                'klijentid' => 0,
                'pibkupca' => null,
                'sifraArtikla' => 300,
                'stornoreferenceid' => null,
                'racunid' => 42933,
                'stoid' => 2,
                'stornirano' => 0,
                'listastavki' => '327080', // Row 3: items from order 3 - IGNORED due to bug!
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        // All rows' listastavki should be collected and all items cleared.

        // All 3 orders should be deleted (items from all rows are processed)
        $this->assertNull(
            ThirdPartyOrder::find($order1->id),
            'Order 1 should be deleted (items 326984, 326998 from row 1)'
        );
        $this->assertNull(
            ThirdPartyOrder::find($order2->id),
            'Order 2 should be deleted (items 327050, 327051 from row 2)'
        );
        $this->assertNull(
            ThirdPartyOrder::find($order3->id),
            'Order 3 should be deleted (item 327080 from row 3)'
        );

        // All items from all rows should be deleted
        $this->assertNull(
            ThirdPartyOrderItem::where('external_item_id', 327050)->first(),
            'Item 327050 from row 2 should be deleted'
        );
        $this->assertNull(
            ThirdPartyOrderItem::where('external_item_id', 327051)->first(),
            'Item 327051 from row 2 should be deleted'
        );
        $this->assertNull(
            ThirdPartyOrderItem::where('external_item_id', 327080)->first(),
            'Item 327080 from row 3 should be deleted'
        );

        // All orders and items should be cleared
        $this->assertEquals(
            0,
            ThirdPartyOrder::count(),
            'All 3 orders should be deleted when listastavki from all rows is processed'
        );
        $this->assertEquals(
            0,
            ThirdPartyOrderItem::count(),
            'All 5 items should be deleted when listastavki from all rows is processed'
        );

        // Response should report all 3 orders deleted
        $response->assertJsonPath(
            'data.orders_deleted',
            3
        );
    }

    /**
     * Real-world scenario: 12 order items across 6 orders, invoice with 8 rows.
     * Each row has its own listastavki. All items should be cleared and all orders deleted.
     */
    public function test_invoice_clears_all_order_items_for_table_when_all_items_invoiced()
    {
        // Simulate a real table with 6 orders, 12 items total
        $ordersData = [
            ['external_order_id' => 104130, 'items' => [
                ['external_item_id' => 326984, 'name' => 'Coca cola 0.25', 'price' => 210],
                ['external_item_id' => 326985, 'name' => 'Fanta 0.25', 'price' => 210],
            ]],
            ['external_order_id' => 104131, 'items' => [
                ['external_item_id' => 327000, 'name' => 'Kiseli kupus', 'price' => 280],
                ['external_item_id' => 327001, 'name' => 'Pasulj', 'price' => 280],
            ]],
            ['external_order_id' => 104132, 'items' => [
                ['external_item_id' => 327020, 'name' => 'Rosa 0.75', 'price' => 230],
                ['external_item_id' => 327021, 'name' => 'Sok narandza', 'price' => 260],
            ]],
            ['external_order_id' => 104133, 'items' => [
                ['external_item_id' => 327040, 'name' => 'Palacinke', 'price' => 300],
                ['external_item_id' => 327041, 'name' => 'Vocna salata', 'price' => 350],
            ]],
            ['external_order_id' => 104134, 'items' => [
                ['external_item_id' => 327060, 'name' => 'Mala dunjevaca 0.03', 'price' => 150],
                ['external_item_id' => 327061, 'name' => 'Kafa espreso', 'price' => 160],
            ]],
            ['external_order_id' => 104135, 'items' => [
                ['external_item_id' => 327080, 'name' => 'Cedevita 0.25', 'price' => 210],
                ['external_item_id' => 327081, 'name' => 'Sprite 0.25', 'price' => 210],
            ]],
        ];

        foreach ($ordersData as $orderData) {
            $order = ThirdPartyOrder::create([
                'external_order_id' => $orderData['external_order_id'],
                'table_id' => 2,
                'table_name' => '2',
                'total' => collect($orderData['items'])->sum('price'),
            ]);
            foreach ($orderData['items'] as $item) {
                ThirdPartyOrderItem::create([
                    'third_party_order_id' => $order->id,
                    'external_item_id' => $item['external_item_id'],
                    'name' => $item['name'],
                    'qty' => 1,
                    'price' => $item['price'],
                    'unit' => 'kom',
                    'active' => 1,
                ]);
            }
        }

        $this->assertEquals(6, ThirdPartyOrder::count(), 'Should start with 6 orders');
        $this->assertEquals(12, ThirdPartyOrderItem::count(), 'Should start with 12 items');

        // Invoice with 8 rows (some items combined), each with its own listastavki
        // In a real scenario, the POS groups items by article and each row references
        // the specific stavkaids that compose it.
        $invoiceRows = [
            $this->makeInvoiceRow('Coca cola 0.25', 2, 210, '326984, 326985'),
            $this->makeInvoiceRow('Kiseli kupus', 2, 280, '327000, 327001'),
            $this->makeInvoiceRow('Rosa 0.75', 1, 230, '327020'),
            $this->makeInvoiceRow('Sok narandza', 1, 260, '327021'),
            $this->makeInvoiceRow('Palacinke', 1, 300, '327040'),
            $this->makeInvoiceRow('Vocna salata', 1, 350, '327041'),
            $this->makeInvoiceRow('Mala dunjevaca 0.03', 2, 150, '327060, 327061'),
            $this->makeInvoiceRow('Cedevita + Sprite', 2, 210, '327080, 327081'),
        ];
        // Set payment on first row
        $invoiceRows[0]['gotovina'] = 3300;

        $response = $this->postJson('/api/third-party-invoice', $invoiceRows);

        $response->assertStatus(201);

        // All 12 items across all 8 rows should be cleared
        $allItemIds = [326984, 326985, 327000, 327001, 327020, 327021, 327040, 327041, 327060, 327061, 327080, 327081];
        foreach ($allItemIds as $itemId) {
            $this->assertNull(
                ThirdPartyOrderItem::where('external_item_id', $itemId)->first(),
                "Item {$itemId} should be deleted (listastavki collected from all rows)"
            );
        }

        // All 6 orders should be deleted
        $this->assertEquals(
            0,
            ThirdPartyOrder::count(),
            'All 6 orders should be deleted when listastavki from all rows is processed'
        );
    }

    /**
     * Test that the invoice falls back to stoid-based clearing when listastavki is an empty string.
     */
    public function test_invoice_with_listastavki_falls_back_to_stoid_when_listastavki_empty()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 210,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $response = $this->postJson('/api/third-party-invoice', [
            [
                'kolicina' => 1,
                'cena' => 210,
                'naziv' => 'Coca cola 0.25',
                'jm' => 'kom',
                'gotovina' => 210,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-02-23 22:01:10',
                'brojracuna' => 29911,
                'sto' => '2',
                'stoid' => 2,
                'racunid' => 42934,
                'stornirano' => 0,
                'listastavki' => '', // Empty string => should fall back to stoid
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        $this->assertEquals(
            0,
            ThirdPartyOrder::count(),
            'Order should be deleted via stoid fallback when listastavki is empty string'
        );
        $this->assertEquals(
            0,
            ThirdPartyOrderItem::count(),
            'Items should be deleted via stoid fallback when listastavki is empty string'
        );
        $this->assertGreaterThan(
            0,
            $response->json('data.orders_deleted'),
            'Should report orders deleted via stoid fallback'
        );
    }

    /**
     * Test that the invoice falls back to stoid-based clearing when listastavki field is missing.
     */
    public function test_invoice_with_listastavki_falls_back_to_stoid_when_listastavki_missing()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 210,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $response = $this->postJson('/api/third-party-invoice', [
            [
                'kolicina' => 1,
                'cena' => 210,
                'naziv' => 'Coca cola 0.25',
                'jm' => 'kom',
                'gotovina' => 210,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-02-23 22:01:10',
                'brojracuna' => 29912,
                'sto' => '2',
                'stoid' => 2,
                'racunid' => 42935,
                'stornirano' => 0,
                // No listastavki field at all => should fall back to stoid
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        $this->assertEquals(
            0,
            ThirdPartyOrder::count(),
            'Order should be deleted via stoid fallback when listastavki is missing'
        );
        $this->assertEquals(
            0,
            ThirdPartyOrderItem::count(),
            'Items should be deleted via stoid fallback when listastavki is missing'
        );
        $this->assertGreaterThan(
            0,
            $response->json('data.orders_deleted'),
            'Should report orders deleted via stoid fallback'
        );
    }

    /**
     * Test that Pusher notifications are triggered after invoice with order clearing.
     */
    public function test_invoice_with_listastavki_triggers_pusher_notifications()
    {
        $pusherMock = $this->createMock(Pusher::class);

        // Expect trigger to be called at least twice (tables-update and kitchen-update)
        $events = [];
        $pusherMock->expects($this->atLeast(2))
            ->method('trigger')
            ->willReturnCallback(function ($channel, $event, $data) use (&$events) {
                $events[] = ['channel' => $channel, 'event' => $event];
            });

        $this->app->instance(Pusher::class, $pusherMock);

        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 210,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $response = $this->postJson('/api/third-party-invoice', [
            [
                'kolicina' => 1,
                'cena' => 210,
                'naziv' => 'Coca cola 0.25',
                'jm' => 'kom',
                'gotovina' => 210,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-02-23 22:01:10',
                'brojracuna' => 29913,
                'sto' => '2',
                'stoid' => 2,
                'racunid' => 42936,
                'stornirano' => 0,
                'listastavki' => '326984',
            ],
        ]);

        $response->assertStatus(201);

        $eventNames = array_column($events, 'event');
        $this->assertContains(
            'tables-update',
            $eventNames,
            'Pusher should broadcast tables-update event after invoice processing'
        );
        $this->assertContains(
            'kitchen-update',
            $eventNames,
            'Pusher should broadcast kitchen-update event after invoice processing'
        );
    }

    /**
     * Test that the API response includes the orders_deleted count.
     */
    public function test_invoice_response_includes_orders_deleted_count()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 420,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $response = $this->postJson('/api/third-party-invoice', [
            [
                'kolicina' => 2,
                'cena' => 210,
                'naziv' => 'Coca cola + Fanta',
                'jm' => 'kom',
                'gotovina' => 420,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-02-23 22:01:10',
                'brojracuna' => 29914,
                'sto' => '2',
                'stoid' => 2,
                'racunid' => 42937,
                'stornirano' => 0,
                'listastavki' => '326984, 326998',
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'invoice_number',
                'is_duplicate',
                'status',
                'total',
                'orders_deleted',
            ],
        ]);
        $response->assertJsonPath('data.orders_deleted', 1);
        $this->assertStringContains(
            '1 order(s) for table closed',
            $response->json('message'),
            'Response message should include orders deleted count'
        );
    }

    /**
     * Test partial clearing: invoice covers only some items from an order.
     */
    public function test_invoice_with_partial_listastavki_clears_only_matching_items()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 700,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 327010,
            'name' => 'Kiseli kupus',
            'qty' => 1,
            'price' => 280,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // Invoice only covers the first item
        $response = $this->postJson('/api/third-party-invoice', [
            [
                'kolicina' => 1,
                'cena' => 210,
                'naziv' => 'Coca cola 0.25',
                'jm' => 'kom',
                'gotovina' => 210,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-02-23 22:01:10',
                'brojracuna' => 29915,
                'sto' => '2',
                'stoid' => 2,
                'racunid' => 42938,
                'stornirano' => 0,
                'listastavki' => '326984',
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.orders_deleted', 0);

        $this->assertEquals(
            1,
            ThirdPartyOrder::count(),
            'Order should persist because it still has 2 remaining items'
        );
        $this->assertEquals(
            2,
            ThirdPartyOrderItem::count(),
            'Only 1 item (326984) should be deleted, 2 items remain'
        );
        $this->assertNull(
            ThirdPartyOrderItem::where('external_item_id', 326984)->first(),
            'Item 326984 should be deleted via listastavki'
        );
        $this->assertNotNull(
            ThirdPartyOrderItem::where('external_item_id', 326998)->first(),
            'Item 326998 should remain (not in listastavki)'
        );
        $this->assertNotNull(
            ThirdPartyOrderItem::where('external_item_id', 327010)->first(),
            'Item 327010 should remain (not in listastavki)'
        );
    }

    // =========================================================================
    // Group 3: Kitchen Display Integration Tests
    // =========================================================================

    /**
     * Test that kitchen orders are cleared when an invoice clears all items.
     */
    public function test_kitchen_orders_cleared_when_invoice_clears_all_items()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 420,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // Create kitchen order with items
        $kitchenOrder = KitchenOrder::create([
            'orderable_type' => 'third_party_order',
            'orderable_id' => $order->id,
            'table_name' => 'Sala 2',
        ]);

        KitchenOrderItem::create([
            'kitchen_order_id' => $kitchenOrder->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'storno' => false,
            'is_done' => false,
        ]);

        KitchenOrderItem::create([
            'kitchen_order_id' => $kitchenOrder->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'storno' => false,
            'is_done' => false,
        ]);

        $this->assertEquals(1, KitchenOrder::count(), 'Should start with 1 kitchen order');
        $this->assertEquals(2, KitchenOrderItem::count(), 'Should start with 2 kitchen items');

        $response = $this->postJson('/api/third-party-invoice', [
            [
                'kolicina' => 2,
                'cena' => 210,
                'naziv' => 'Drinks',
                'jm' => 'kom',
                'gotovina' => 420,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-02-23 22:01:10',
                'brojracuna' => 29916,
                'sto' => '2',
                'stoid' => 2,
                'racunid' => 42939,
                'stornirano' => 0,
                'listastavki' => '326984, 326998',
            ],
        ]);

        $response->assertStatus(201);

        $this->assertEquals(
            0,
            KitchenOrder::count(),
            'Kitchen order should be deleted when all its items are cleared via invoice'
        );
        $this->assertEquals(
            0,
            KitchenOrderItem::count(),
            'Kitchen order items should be deleted when cleared via invoice listastavki'
        );
    }

    /**
     * Test that the kitchen display API excludes cleared orders after invoice processing.
     */
    public function test_kitchen_display_api_excludes_cleared_orders()
    {
        // Create order and kitchen order
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 210,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $kitchenOrder = KitchenOrder::create([
            'orderable_type' => 'third_party_order',
            'orderable_id' => $order->id,
            'table_name' => 'Sala 2',
        ]);

        KitchenOrderItem::create([
            'kitchen_order_id' => $kitchenOrder->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'storno' => false,
            'is_done' => false,
        ]);

        // Verify kitchen order appears in the API before invoicing
        $kitchenResponse = $this->getJson('/api/kitchen/orders');
        $kitchenResponse->assertStatus(200);
        $activeOrders = $kitchenResponse->json('active');
        $this->assertNotEmpty($activeOrders, 'Kitchen order should appear in active list before invoicing');

        // Now create the invoice that clears the order
        $response = $this->postJson('/api/third-party-invoice', [
            [
                'kolicina' => 1,
                'cena' => 210,
                'naziv' => 'Coca cola 0.25',
                'jm' => 'kom',
                'gotovina' => 210,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-02-23 22:01:10',
                'brojracuna' => 29917,
                'sto' => '2',
                'stoid' => 2,
                'racunid' => 42940,
                'stornirano' => 0,
                'listastavki' => '326984',
            ],
        ]);

        $response->assertStatus(201);

        // Verify kitchen order is gone from the API
        $kitchenResponse = $this->getJson('/api/kitchen/orders');
        $kitchenResponse->assertStatus(200);
        $activeOrders = $kitchenResponse->json('active');
        $this->assertEmpty(
            $activeOrders,
            'Kitchen order should NOT appear in active list after invoice cleared its items'
        );
    }

    // =========================================================================
    // Group 4: Edge Cases
    // =========================================================================

    /**
     * Test that listastavki parsing handles spaces correctly (e.g., "326984, 326998").
     */
    public function test_listastavki_parsing_handles_spaces_correctly()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 420,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // listastavki with spaces around commas
        $response = $this->postJson('/api/third-party-invoice', [
            [
                'kolicina' => 2,
                'cena' => 210,
                'naziv' => 'Drinks',
                'jm' => 'kom',
                'gotovina' => 420,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-02-23 22:01:10',
                'brojracuna' => 29918,
                'sto' => '2',
                'stoid' => 2,
                'racunid' => 42941,
                'stornirano' => 0,
                'listastavki' => '  326984 ,  326998  ', // Extra spaces everywhere
            ],
        ]);

        $response->assertStatus(201);

        $this->assertEquals(
            0,
            ThirdPartyOrderItem::count(),
            'Both items should be deleted even with extra spaces in the listastavki string'
        );
        $this->assertEquals(
            0,
            ThirdPartyOrder::count(),
            'Order should be deleted after all items cleared'
        );
    }

    /**
     * Test that listastavki parsing handles trailing commas without producing a 0 in the array.
     */
    public function test_listastavki_parsing_handles_trailing_comma()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 420,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326998,
            'name' => 'Fanta 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        // listastavki with trailing comma: "326984,326998,"
        // explode produces ["326984", "326998", ""] -> intval("") = 0
        // This could accidentally match items with external_item_id = 0 if any exist
        $response = $this->postJson('/api/third-party-invoice', [
            [
                'kolicina' => 2,
                'cena' => 210,
                'naziv' => 'Drinks',
                'jm' => 'kom',
                'gotovina' => 420,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-02-23 22:01:10',
                'brojracuna' => 29919,
                'sto' => '2',
                'stoid' => 2,
                'racunid' => 42942,
                'stornirano' => 0,
                'listastavki' => '326984,326998,', // Trailing comma
            ],
        ]);

        $response->assertStatus(201);

        // The real items should still be deleted correctly
        $this->assertNull(
            ThirdPartyOrderItem::where('external_item_id', 326984)->first(),
            'Item 326984 should be deleted even with trailing comma in listastavki'
        );
        $this->assertNull(
            ThirdPartyOrderItem::where('external_item_id', 326998)->first(),
            'Item 326998 should be deleted even with trailing comma in listastavki'
        );
        $this->assertEquals(
            0,
            ThirdPartyOrder::count(),
            'Order should be deleted after all items cleared'
        );
    }

    /**
     * Test that listastavki with a single item (no comma) works correctly.
     */
    public function test_listastavki_with_single_item()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 104130,
            'table_id' => 2,
            'table_name' => '2',
            'total' => 210,
        ]);

        ThirdPartyOrderItem::create([
            'third_party_order_id' => $order->id,
            'external_item_id' => 326984,
            'name' => 'Coca cola 0.25',
            'qty' => 1,
            'price' => 210,
            'unit' => 'kom',
            'active' => 1,
        ]);

        $response = $this->postJson('/api/third-party-invoice', [
            [
                'kolicina' => 1,
                'cena' => 210,
                'naziv' => 'Coca cola 0.25',
                'jm' => 'kom',
                'gotovina' => 210,
                'kartica' => 0,
                'prenosnaracun' => 0,
                'datum' => '2026-02-23 22:01:10',
                'brojracuna' => 29920,
                'sto' => '2',
                'stoid' => 2,
                'racunid' => 42943,
                'stornirano' => 0,
                'listastavki' => '326984', // Single item, no comma
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.orders_deleted', 1);

        $this->assertEquals(
            0,
            ThirdPartyOrder::count(),
            'Order should be deleted when its only item is cleared via single-value listastavki'
        );
        $this->assertEquals(
            0,
            ThirdPartyOrderItem::count(),
            'Item should be deleted via single-value listastavki'
        );
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Build an invoice row for testing. Payment fields default to 0.
     */
    private function makeInvoiceRow(
        string $name,
        int $qty,
        int $price,
        string $listastavki
    ): array {
        static $invoiceCounter = 0;
        $invoiceCounter++;

        return [
            'kolicina' => $qty,
            'cena' => $price,
            'naziv' => $name,
            'jm' => 'kom',
            'gotovina' => 0,
            'kartica' => 0,
            'prenosnaracun' => 0,
            'datum' => '2026-02-23 22:01:10',
            'brojracuna' => 29950,
            'sto' => '2',
            'klijentid' => 0,
            'pibkupca' => null,
            'sifraArtikla' => 100 + $invoiceCounter,
            'stornoreferenceid' => null,
            'racunid' => 42950,
            'stoid' => 2,
            'stornirano' => 0,
            'listastavki' => $listastavki,
        ];
    }

    /**
     * Custom assertion helper: assert string contains a substring.
     */
    private function assertStringContains(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            $message ?: "Failed asserting that '{$haystack}' contains '{$needle}'"
        );
    }
}
