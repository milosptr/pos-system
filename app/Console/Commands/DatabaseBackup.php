<?php

namespace App\Console\Commands;

use App\Models\S3Backup;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command as CommandAlias;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database and upload it to S3';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
      parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      $filename = 'backup-' . Carbon::now()->format('Y-m-d') . '.sql';

      try {
        $localPath = storage_path("app/backups/{$filename}");

        if (!file_exists(storage_path('app/backups'))) {
          mkdir(storage_path('app/backups'), 0755, true);
        }

        $command = "mysqldump --user=" . env('DB_USERNAME') .
          " --password=" . env('DB_PASSWORD') .
          " --host=" . env('DB_HOST') .
          " " . env('DB_DATABASE') .
          " > " . $localPath;

        $returnVar = null;
        $output = null;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
          $this->error('Failed to create database backup.');
          $this->error('Output: ' . implode("\n", $output));
          $this->error('Return code: ' . $returnVar);
          return 1;
        }

        $s3Path = 'backups/' . $filename;
        Storage::disk('s3')->put($s3Path, file_get_contents($localPath));

        S3Backup::create([
          'filename' => $filename,
          'path' => $s3Path,
          'size' => filesize($localPath),
          'is_uploaded' => true,
        ]);

        // Delete local backup file
        unlink($localPath);

        // Delete 7 days old backup if exists
        $weekAgo = Carbon::now()->subDays(7)->format('Y-m-d');
        $oldFilename = 'backup-' . $weekAgo . '.sql';
        $oldS3Path = 'backups/' . $oldFilename;
        $oldBackup = S3Backup::where('filename', $oldFilename)->first();
        if ($oldBackup) {
          Storage::disk('s3')->delete($oldS3Path);
          $oldBackup->update([
            'is_deleted' => true,
          ]);
          Log::info('Old backup file deleted from S3. Filename: ' . $oldFilename);
        }

        $this->info('Database backup successfully uploaded to S3. Stored at: ' . $s3Path);
        Log::info('Database backup successfully uploaded to S3. Stored at: ' . $s3Path);
        return CommandAlias::SUCCESS;
      } catch (\Exception $e) {
        S3Backup::create([
          'filename' => $filename,
          'path' => 'none',
          'size' => "0",
          'is_uploaded' => false,
        ]);
        $this->error('Failed to create database backup.');
        $this->error('Error: ' . $e->getMessage());
        Log::error('Failed to create database backup. Path: '. $s3Path .'. Error: ' . $e->getMessage());
        return CommandAlias::FAILURE;
      }
    }
}
