<?php

namespace App\Repositories\Gen\Tasks;

use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Tasks\Backup\MultiDiskBackupGateway;

/**
 * BACKUP DATABASE … TO DISK en SQL Server local (Windows).
 */
final class WindowsSqlServerMultiDiskBackupGateway implements MultiDiskBackupGateway
{
    public function backupToDisk(string $databaseName, string $destinationPath, bool $overwrite): array
    {
        $databaseName = trim($databaseName);
        $destinationPath = trim($destinationPath);
        if ($databaseName === '' || $destinationPath === '') {
            return ['ok' => false, 'message' => 'databaseName and destinationPath are required'];
        }

        $option = $overwrite ? 'INIT' : 'NOINIT';
        $escapedPath = str_replace("'", "''", $destinationPath);
        $quotedDb = $this->quoteIdentifier($databaseName);

        try {
            DB::statement(
                "BACKUP DATABASE {$quotedDb} TO DISK = N'{$escapedPath}' WITH {$option}, CHECKSUM, STATS = 10"
            );

            return ['ok' => true, 'destinationPath' => $destinationPath];
        } catch (\Throwable $exception) {
            return ['ok' => false, 'message' => $exception->getMessage()];
        }
    }

    private function quoteIdentifier(string $name): string
    {
        return '['.str_replace(']', ']]', $name).']';
    }
}
