<?php

namespace App\Repositories\Gen\Tasks;

use PaqSuite\LaravelCore\Tasks\Backup\MonoRdsBackupGateway;

/**
 * Gateway RDS en memoria para desarrollo local (smoke backup sin AWS).
 */
final class InMemoryMonoRdsBackupGateway implements MonoRdsBackupGateway
{
    public function startExport(
        string $databaseName,
        string $s3Arn,
        string $filePrefix,
        bool $overwrite = true,
    ): array {
        return [
            'taskId' => 'rds-'.md5($databaseName.'|'.$s3Arn.'|'.$filePrefix.'|'.($overwrite ? '1' : '0')),
        ];
    }

    public function getTaskStatus(string $taskId): array
    {
        return [
            'status' => 'success',
            'taskId' => $taskId,
            'percentProgress' => 100,
        ];
    }
}
