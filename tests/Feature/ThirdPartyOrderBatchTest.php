<?php

namespace Tests\Feature;

use App\Http\Controllers\ThirdPartyOrderController;
use App\Http\Middleware\VerifyExternalApiKey;
use App\Models\KitchenOrder;
use App\Models\ThirdPartyInvoice;
use App\Models\ThirdPartyOrder;
use App\Models\ThirdPartyOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Services\Pusher;
use Tests\TestCase;

/**
 * The external system (ebar) posts every active table in one request on
 * startup, instead of one table at a time. These tests cover that batch
 * behaviour: per-order isolation, resends of paid orders, id reuse across
 * working days, and the single-broadcast guarantee.
 */
class ThirdPartyOrderBatchTest extends TestCase
{
    use RefreshDatabase;

    private array $broadcastEvents = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyExternalApiKey::class);
    }

    /**
     * Swap in a Pusher mock that records every triggered event name, so tests
     * can count broadcasts (a batch must not fan out one call per order).
     */
    private function captureBroadcasts(): void
    {
        $this->broadcastEvents = [];
        $mock = $this->createMock(Pusher::class);
        $mock->method('trigger')
            ->willReturnCallback(function ($channel, $event, $data) {
                $this->broadcastEvents[] = $event;
            });
        $this->app->instance(Pusher::class, $mock);
    }

    private function broadcastCount(string $event): int
    {
        return collect($this->broadcastEvents)->filter(fn ($e) => $e === $event)->count();
    }

    /**
     * A realistic ebar row. stampanjenalogaid = 2 marks a kitchen item.
     */
    private function orderRow(int $orderId, int $itemId, array $overrides = []): array
    {
        return array_merge([
            'porudzbinaid' => $orderId,
            'stavkaid' => $itemId,
            'naziv' => 'Item ' . $itemId,
            'kolicina' => 1,
            'cena' => 100,
            'jm' => 'kom',
            'sto' => (string) (($orderId % 90) + 1),
            'stoid' => ($orderId % 90) + 1,
            'datum' => now()->toDateTimeString(),
            'konobar' => 'Pera',
            'stampanjenalogaid' => 2,
        ], $overrides);
    }

    /**
     * Close an order the way a real invoice does (listastavki path), which
     * stamps invoiced_at before soft-deleting.
     */
    private function closeByInvoice(array $externalItemIds): void
    {
        ThirdPartyOrder::deleteByExternalItemIds($externalItemIds);
    }

    // =========================================================================
    // Full snapshot
    // =========================================================================

    public function test_full_snapshot_of_thirty_tables_is_processed_with_one_broadcast()
    {
        $payload = [];
        for ($i = 1; $i <= 30; $i++) {
            $orderId = 200000 + $i;
            // one kitchen item + one bar item per table
            $payload[] = $this->orderRow($orderId, $orderId * 10 + 1, ['cena' => 100]);
            $payload[] = $this->orderRow($orderId, $orderId * 10 + 2, [
                'cena' => 150,
                'stampanjenalogaid' => 1,
            ]);
        }

        $this->captureBroadcasts();
        $response = $this->postJson('/api/third-party-order', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('summary.processed', 30);
        $response->assertJsonPath('summary.created', 30);
        $response->assertJsonPath('summary.failed', []);

        $this->assertEquals(30, ThirdPartyOrder::count());
        $this->assertEquals(60, ThirdPartyOrderItem::count());
        $this->assertEquals(
            30,
            KitchenOrder::where('orderable_type', 'third_party_order')->count(),
            'Every table with a kitchen item reaches the display'
        );

        $order = ThirdPartyOrder::where('external_order_id', 200001)->first();
        $this->assertEquals(250, $order->total);
        $this->assertEquals((200001 % 90) + 1, $order->table_id);

        $this->assertEquals(1, $this->broadcastCount('tables-update'), 'One tables-update per request, not per order');
        $this->assertEquals(1, $this->broadcastCount('kitchen-update'), 'One kitchen-update per request, not per order');
    }

    public function test_same_batch_posted_twice_is_idempotent()
    {
        $payload = [];
        for ($i = 1; $i <= 5; $i++) {
            $orderId = 210000 + $i;
            $payload[] = $this->orderRow($orderId, $orderId * 10 + 1);
            $payload[] = $this->orderRow($orderId, $orderId * 10 + 2, ['cena' => 200]);
        }

        $this->postJson('/api/third-party-order', $payload)->assertStatus(201);
        $totalsAfterFirst = ThirdPartyOrder::pluck('total', 'external_order_id')->toArray();

        $response = $this->postJson('/api/third-party-order', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('summary.created', 0);
        $response->assertJsonPath('summary.updated', 5);

        $this->assertEquals(5, ThirdPartyOrder::count(), 'No duplicate orders');
        $this->assertEquals(10, ThirdPartyOrderItem::count(), 'No duplicate items');
        $this->assertEquals(5, KitchenOrder::count(), 'No duplicate kitchen orders');
        $this->assertEquals(
            $totalsAfterFirst,
            ThirdPartyOrder::pluck('total', 'external_order_id')->toArray(),
            'Totals stay stable across resends'
        );
    }

    public function test_modifier_rows_merge_per_order_across_a_batch()
    {
        $payload = [
            $this->orderRow(220001, 100),
            $this->orderRow(220001, 101, ['modifikatorslobodan' => 'bez luka', 'cena' => 0]),
            $this->orderRow(220002, 200),
            $this->orderRow(220002, 201, ['modifikatorslobodan' => 'ljuto', 'cena' => 0]),
        ];

        $this->postJson('/api/third-party-order', $payload)->assertStatus(201);

        $this->assertEquals(2, ThirdPartyOrderItem::count(), 'Modifier rows are merged, not stored as items');

        $first = ThirdPartyOrder::where('external_order_id', 220001)->first();
        $this->assertEquals('bez luka', $first->items->first()->modifier);

        $second = ThirdPartyOrder::where('external_order_id', 220002)->first();
        $this->assertEquals('ljuto', $second->items->first()->modifier);
    }

    // =========================================================================
    // Isolation and row hygiene
    // =========================================================================

    public function test_one_bad_order_does_not_break_the_rest_of_the_batch()
    {
        $payload = [
            $this->orderRow(230001, 2301),
            $this->orderRow(230002, 2302, ['datum' => 'not-a-date']),
            $this->orderRow(230003, 2303),
        ];

        $response = $this->postJson('/api/third-party-order', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('summary.processed', 2);
        $response->assertJsonPath('summary.failed.0.external_order_id', 230002);

        $this->assertNotNull(ThirdPartyOrder::where('external_order_id', 230001)->first());
        $this->assertNull(ThirdPartyOrder::where('external_order_id', 230002)->first());
        $this->assertNotNull(ThirdPartyOrder::where('external_order_id', 230003)->first());
    }

    public function test_rows_with_missing_or_invalid_porudzbinaid_are_skipped()
    {
        $validRow = $this->orderRow(240001, 2401);
        $missing = $this->orderRow(240001, 2402);
        unset($missing['porudzbinaid']);

        $payload = [
            $validRow,
            $missing,
            $this->orderRow(240001, 2403, ['porudzbinaid' => 'abc']),
            $this->orderRow(240001, 2404, ['porudzbinaid' => 0]),
        ];

        $response = $this->postJson('/api/third-party-order', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('summary.invalid_rows', 3);

        $this->assertFalse(
            ThirdPartyOrder::withTrashed()->where('external_order_id', 0)->exists(),
            'Invalid rows must not collapse into a bogus order 0'
        );
        $this->assertEquals(1, ThirdPartyOrder::count());
        $this->assertEquals(1, ThirdPartyOrderItem::count(), 'Only the valid row becomes an item');
    }

    public function test_payload_with_only_invalid_rows_is_rejected()
    {
        $row = $this->orderRow(250001, 2501);
        unset($row['porudzbinaid']);

        $response = $this->postJson('/api/third-party-order', [$row]);

        $response->assertStatus(422);
        $this->assertEquals(0, ThirdPartyOrder::count());
    }

    public function test_oversized_payload_is_rejected()
    {
        $payload = array_fill(0, ThirdPartyOrderController::MAX_ROWS + 1, ['porudzbinaid' => 1]);

        $response = $this->postJson('/api/third-party-order', $payload);

        $response->assertStatus(422);
        $this->assertEquals(0, ThirdPartyOrder::count());
    }

    // =========================================================================
    // Resends of orders an invoice already settled
    // =========================================================================

    public function test_resent_paid_order_is_skipped_not_resurrected()
    {
        // Table paid earlier this working day
        $this->postJson('/api/third-party-order', [$this->orderRow(260001, 2601)])
            ->assertStatus(201);
        $this->closeByInvoice([2601]);

        $this->assertEquals(0, ThirdPartyOrder::count());

        // Startup sync resends it alongside a genuinely open table
        $response = $this->postJson('/api/third-party-order', [
            $this->orderRow(260001, 2601),
            $this->orderRow(260002, 2602),
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('summary.skipped_invoiced', [260001]);
        $response->assertJsonPath('summary.created', 1);

        $this->assertNull(
            ThirdPartyOrder::where('external_order_id', 260001)->first(),
            'The paid order must not come back as a ghost table'
        );
        $this->assertNotNull(ThirdPartyOrder::where('external_order_id', 260002)->first());
    }

    public function test_order_settled_by_a_paid_invoice_without_stamp_is_still_skipped()
    {
        // A row trashed before invoiced_at existed: bare soft delete, no stamp,
        // but a paid invoice from this working day references the order id.
        $order = ThirdPartyOrder::create([
            'external_order_id' => 270001,
            'table_id' => 3,
            'table_name' => '3',
            'total' => 100,
        ]);
        $order->delete();

        ThirdPartyInvoice::create([
            'invoice_number' => 'TP-270001',
            'external_order_id' => 270001,
            'status' => ThirdPartyInvoice::STATUS_PAYED,
            'order' => [],
            'total' => 100,
            'payment_type' => ThirdPartyInvoice::PAYMENT_CASH,
        ]);

        $response = $this->postJson('/api/third-party-order', [$this->orderRow(270001, 2701)]);

        $response->assertStatus(201);
        $response->assertJsonPath('summary.skipped_invoiced', [270001]);
        $this->assertNull(ThirdPartyOrder::where('external_order_id', 270001)->first());
    }

    public function test_stornoed_invoice_does_not_suppress_a_resend()
    {
        $order = ThirdPartyOrder::create([
            'external_order_id' => 280001,
            'table_id' => 4,
            'table_name' => '4',
            'total' => 100,
        ]);
        $order->delete();

        ThirdPartyInvoice::create([
            'invoice_number' => 'TP-280001',
            'external_order_id' => 280001,
            'status' => ThirdPartyInvoice::STATUS_STORNO,
            'order' => [],
            'total' => 100,
            'payment_type' => ThirdPartyInvoice::PAYMENT_CASH,
        ]);

        $response = $this->postJson('/api/third-party-order', [$this->orderRow(280001, 2801)]);

        $response->assertStatus(201);
        $response->assertJsonPath('summary.skipped_invoiced', []);
        $this->assertNotNull(
            ThirdPartyOrder::where('external_order_id', 280001)->first(),
            'A stornoed payment means the order is open again'
        );
    }

    public function test_order_id_reused_after_a_previous_working_day_is_created()
    {
        // Settled YESTERDAY (previous working day) — ebar reuses porudzbinaid
        // across days, so today's order with the same id is a new order.
        $order = ThirdPartyOrder::create([
            'external_order_id' => 290001,
            'table_id' => 5,
            'table_name' => '5',
            'total' => 100,
            'invoiced_at' => now()->subDay(),
        ]);
        $order->delete();

        $invoice = ThirdPartyInvoice::create([
            'invoice_number' => 'TP-290001',
            'external_order_id' => 290001,
            'status' => ThirdPartyInvoice::STATUS_PAYED,
            'order' => [],
            'total' => 100,
            'payment_type' => ThirdPartyInvoice::PAYMENT_CASH,
        ]);
        $invoice->created_at = now()->subDay();
        $invoice->save();

        $response = $this->postJson('/api/third-party-order', [$this->orderRow(290001, 2901)]);

        $response->assertStatus(201);
        $response->assertJsonPath('summary.skipped_invoiced', []);
        $this->assertNotNull(
            ThirdPartyOrder::where('external_order_id', 290001)->first(),
            'Yesterday\'s invoice must not blacklist a reused order id'
        );
    }

    public function test_order_trashed_by_the_4am_cleanup_reaches_the_kitchen_when_resent()
    {
        // Regression: the old unbounded onlyTrashed() check meant any order id
        // seen before the 4am cleanup never reached the kitchen again.
        $this->postJson('/api/third-party-order', [$this->orderRow(300001, 3001)])
            ->assertStatus(201);

        $this->artisan('third-party-orders:cleanup');
        $this->assertEquals(0, ThirdPartyOrder::count());

        $response = $this->postJson('/api/third-party-order', [$this->orderRow(300001, 3001)]);

        $response->assertStatus(201);
        $response->assertJsonPath('summary.created', 1);

        $newOrder = ThirdPartyOrder::where('external_order_id', 300001)->first();
        $this->assertNotNull($newOrder, 'A cleanup-trashed order is recreated on resend');
        $this->assertNotNull(
            KitchenOrder::where('orderable_type', 'third_party_order')
                ->where('orderable_id', $newOrder->id)
                ->first(),
            'The resent order must reach the kitchen display'
        );
    }

    // =========================================================================
    // Partially paid orders
    // =========================================================================

    public function test_item_cleared_by_a_partial_invoice_is_not_resurrected_on_resend()
    {
        $this->postJson('/api/third-party-order', [
            $this->orderRow(310001, 3101, ['cena' => 100]),
            $this->orderRow(310001, 3102, ['cena' => 200]),
            $this->orderRow(310001, 3103, ['cena' => 300]),
        ])->assertStatus(201);

        // Invoice pays item 3101 only (listastavki)
        $this->closeByInvoice([3101]);

        $order = ThirdPartyOrder::where('external_order_id', 310001)->first();
        $this->assertNotNull($order, 'Order with remaining items stays open');
        $this->assertEquals(2, $order->items()->count());

        // ebar resends the whole order, paid item included
        $response = $this->postJson('/api/third-party-order', [
            $this->orderRow(310001, 3101, ['cena' => 100]),
            $this->orderRow(310001, 3102, ['cena' => 200]),
            $this->orderRow(310001, 3103, ['cena' => 300]),
        ]);

        $response->assertStatus(201);

        $order = $order->fresh();
        $this->assertEquals(2, $order->items()->count(), 'The paid item must not come back');
        $this->assertEquals(
            1,
            ThirdPartyOrderItem::withTrashed()->where('external_item_id', 3101)->count(),
            'No duplicate row for the paid item'
        );
        $this->assertEquals(500, $order->total, 'The paid item stays out of the total');
    }
}
