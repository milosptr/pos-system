<?php

namespace App\Console\Commands;

use App\Models\ExceptionLog;
use App\Models\Sales;
use App\Models\WarehouseStatus;
use Illuminate\Console\Command;
use Services\SalesService;

class ParseUnproccessedImports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:process-unprocessed-imports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process unprocessed imports from the sales import queue.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get all unprocessed imports
        $imports = ExceptionLog::where('type', WarehouseStatus::EXCEPTION_TYPE)->where('processed', 0)->get();
        foreach($imports as $import) {
            try {
                $data = json_decode($import->payload, true);
                $this->info('Processing import for ' . $data['created_at']);
                $this->info('Processing import for ' . $data['id']);
                $sale = new Sales($data);
                // Process the import
                SalesService::populateWarehouseFromSaleImport($sale, $data['created_at']);
                // Mark the import as processed
                $import->processed = true;
                $import->save();
            } catch (\Exception $e) {
                $this->error('Failed to process import for ' . $data['id']);
                $this->error($e->getMessage());
            }
        }
        return Command::SUCCESS;
    }
}
