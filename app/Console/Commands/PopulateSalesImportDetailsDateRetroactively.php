<?php

namespace App\Console\Commands;

use App\Models\Sales;
use Illuminate\Console\Command;

class PopulateSalesImportDetailsDateRetroactively extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:populate-import-details-date-retroactively';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate sales import details date retroactively.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $salesImportDetails = \App\Models\SalesImportDetail::where('import_date', null)->get();
        foreach ($salesImportDetails as $salesImportDetail) {
            $sale = Sales::where('batch_id', $salesImportDetail->id)->first();
            if ($sale) {
                $salesImportDetail->import_date = $sale->created_at->format('Y-m-d');
                $salesImportDetail->save();
            }
        }
        return Command::SUCCESS;
    }
}
