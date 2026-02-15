<?php

namespace App\Console\Commands;

use App\Models\KitchenOrder;
use App\Models\KitchenOrderItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupKitchenOrders extends Command
{
    protected $signature = 'kitchen:cleanup';

    protected $description = 'Delete all kitchen orders for end-of-day cleanup';

    public function handle()
    {
        $count = KitchenOrder::count();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        KitchenOrderItem::truncate();
        KitchenOrder::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info("Deleted {$count} kitchen orders.");

        return 0;
    }
}
