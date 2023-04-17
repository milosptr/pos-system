<?php

namespace App\Console\Commands;

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
        \App\Models\Sales::whereIn('invoice_id', $stornoInvoices)->delete();
        return 0;
    }
}
