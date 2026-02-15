<?php

namespace App\Http\Controllers;

use App\Http\Resources\KitchenOrderResource;
use App\Models\KitchenOrder;
use App\Models\KitchenOrderItem;
use Services\Pusher;

class KitchenController extends Controller
{
    /**
     * Get all kitchen orders grouped by status.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'active' => KitchenOrderResource::collection(
                KitchenOrder::with('items')->active()->orderBy('created_at', 'asc')->get()
            ),
            'ready' => KitchenOrderResource::collection(
                KitchenOrder::with('items')->ready()->orderBy('ready_at', 'desc')->get()
            ),
        ]);
    }

    /**
     * Mark a kitchen order as ready.
     *
     * @param int $id
     * @return KitchenOrderResource
     */
    public function markReady($id)
    {
        $kitchenOrder = KitchenOrder::findOrFail($id);
        $kitchenOrder->update(['ready_at' => now()]);
        $kitchenOrder->load('items');

        try {
            app(Pusher::class)->trigger('broadcasting', 'kitchen-update', []);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

        return new KitchenOrderResource($kitchenOrder);
    }

    /**
     * Undo a kitchen order's ready status.
     *
     * @param int $id
     * @return KitchenOrderResource
     */
    public function undoReady($id)
    {
        $kitchenOrder = KitchenOrder::findOrFail($id);
        $kitchenOrder->update(['ready_at' => null]);
        $kitchenOrder->load('items');

        try {
            app(Pusher::class)->trigger('broadcasting', 'kitchen-update', []);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

        return new KitchenOrderResource($kitchenOrder);
    }

    /**
     * Toggle the is_done state of a kitchen order item.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleItemDone($id)
    {
        $item = KitchenOrderItem::findOrFail($id);
        $item->update(['is_done' => !$item->is_done]);

        try {
            app(Pusher::class)->trigger('broadcasting', 'kitchen-update', []);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

        return response()->json(['is_done' => $item->is_done]);
    }
}
