<?php

namespace App\Console\Commands;

use App\Models\Sales;
use App\Models\WarehouseStatus;
use Illuminate\Console\Command;

class ClearStornoSales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear sales from storno invoices';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $stornoInvoices = \App\Models\Invoice::where('status', \App\Models\Invoice::STATUS_REFUNDED)->pluck('id');
        Sales::whereIn('invoice_id', $stornoInvoices)->delete();
        WarehouseStatus::whereIn('batch_id', $stornoInvoices)->delete();
        return 0;
    }
}
