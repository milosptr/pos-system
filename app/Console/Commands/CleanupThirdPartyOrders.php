<?php

namespace App\Console\Commands;

use App\Models\ThirdPartyOrder;
use App\Models\ThirdPartyOrderItem;
use Illuminate\Console\Command;

class CleanupThirdPartyOrders extends Command
{
    protected $signature = 'third-party-orders:cleanup';

    protected $description = 'Soft-delete all active third-party orders for end-of-day cleanup';

    public function handle()
    {
        $orderIds = ThirdPartyOrder::pluck('id');
        $itemCount = ThirdPartyOrderItem::whereIn('third_party_order_id', $orderIds)->count();

        ThirdPartyOrderItem::whereIn('third_party_order_id', $orderIds)->delete();
        ThirdPartyOrder::whereIn('id', $orderIds)->delete();

        $this->info("Soft-deleted {$orderIds->count()} orders and {$itemCount} items.");

        return 0;
    }
}
