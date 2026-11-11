<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PaqSuite\LaravelCore\Tasks\Backup\RdsDeferredBackupExecutionPoller;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskDefinitionRepository;
use PaqSuite\LaravelCore\Tasks\TaskExecutionService;

/**
 * GEN-13 PedidosWeb: polling RDS diferido (backup manual) y ejecución de definiciones due.
 */
final class PedidosWebTasksRunDueCommand extends Command
{
    protected $signature = 'pedidosweb:tasks-run-due
                            {--dry-run : Lista definiciones due sin ejecutar}
                            {--skip-poll : No ejecutar RdsDeferredBackupExecutionPoller}';

    protected $description = 'Tareas programadas: poll backups RDS manuales y ejecutar definiciones due';

    public function handle(
        RdsDeferredBackupExecutionPoller $rdsPoller,
        TaskDefinitionRepository $definitionRepository,
        TaskExecutionService $executionService,
    ): int {
        if (! $this->option('skip-poll')) {
            $polled = $rdsPoller->pollOpenRdsBackups();
            if ($polled > 0) {
                $this->info("Backups RDS cerrados por scheduler: {$polled}.");
            }
        }

        $tzName = (string) config('paqsuite.tasks.scheduleTimezone', config('app.timezone', 'UTC'));
        $scheduleTz = new \DateTimeZone($tzName !== '' ? $tzName : 'UTC');
        $asOf = new \DateTimeImmutable('now', $scheduleTz);

        if ($this->option('dry-run')) {
            $due = $definitionRepository->listDueDefinitions($asOf);
            $this->info('Due definitions: '.count($due));
            foreach ($due as $row) {
                $this->line(sprintf(
                    '  #%d %s next=%s',
                    (int) ($row['id'] ?? $row['taskDefinitionId'] ?? 0),
                    (string) ($row['taskTitle'] ?? ''),
                    (string) ($row['nextRunAt'] ?? ''),
                ));
            }

            return self::SUCCESS;
        }

        $batches = $executionService->runDue($asOf);
        $this->info('Executed batches: '.count($batches));
        foreach ($batches as $batch) {
            $this->line(sprintf(
                '  definition=%d batch=%d',
                (int) ($batch['definitionId'] ?? 0),
                (int) ($batch['batchId'] ?? 0),
            ));
        }

        return self::SUCCESS;
    }
}
