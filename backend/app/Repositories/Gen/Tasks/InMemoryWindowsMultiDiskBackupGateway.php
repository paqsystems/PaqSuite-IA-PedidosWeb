<?php

namespace App\Repositories\Gen\Tasks;

use PaqSuite\LaravelCore\Tasks\Backup\MultiDiskBackupGateway;

/**
 * Gateway Windows in-memory (lab / CI sin BACKUP TO DISK real).
 */
final class InMemoryWindowsMultiDiskBackupGateway implements MultiDiskBackupGateway
{
    public function backupToDisk(string $databaseName, string $destinationPath, bool $overwrite): array
    {
        return [
            'ok' => true,
            'databaseName' => $databaseName,
            'destinationPath' => $destinationPath,
            'overwrite' => $overwrite,
        ];
    }
}
