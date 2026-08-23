<?php

namespace App\Console\Commands;

use App\Models\KitchenOrder;
use App\Models\KitchenOrderItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CleanupKitchenOrders extends Command
{
    protected $signature = 'kitchen:cleanup';

    protected $description = 'Delete all kitchen orders for end-of-day cleanup';

    public function handle()
    {
        $count = KitchenOrder::count();

        Schema::disableForeignKeyConstraints();
        KitchenOrderItem::truncate();
        KitchenOrder::truncate();
        Schema::enableForeignKeyConstraints();

        $this->info("Deleted {$count} kitchen orders.");

        return 0;
    }
}
