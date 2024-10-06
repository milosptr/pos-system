<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Class BackupService
 * @package App\Services
 * I'm going to use this service later if I need to restore a backup file.
 */
class BackupService
{
    public static function readFile($filename)
    {
      $file = Storage::disk('s3')->get('backups/' . $filename);

      if (empty($file)) {
        return 'No backup files found.';
      }

      if (!file_exists(storage_path('app/backups'))) {
        mkdir(storage_path('app/backups'), 0755, true);
      }

      // Save the file to the local storage
      Storage::disk('local')->put("app/backups/{$filename}", $file);


      return 'Backup file has been downloaded successfully.';
    }

    public static function deleteFile($filename)
    {
      $file = Storage::disk('s3')->delete('backups/' . $filename);

      if (empty($file)) {
        return 'No backup files found.';
      }

      return 'Backup file has been deleted successfully.';
    }
}
