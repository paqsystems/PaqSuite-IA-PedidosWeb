<?php

declare(strict_types=1);

namespace Tests\Unit\Gen\Tasks;

use PaqSuite\LaravelCore\Tasks\Repositories\TaskDefinitionRepository;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskExecutionRepository;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskProcessRepository;
use PaqSuite\LaravelCore\Tasks\TaskExecutionContext;
use PaqSuite\LaravelCore\Tasks\TaskProcessInterface;
use PaqSuite\LaravelCore\Tasks\TaskProcessRegistry;
use PaqSuite\LaravelCore\Tasks\TaskResult;
use PaqSuite\LaravelCore\Tasks\TaskScheduleCalculator;
final class SpyProcess implements TaskProcessInterface
{
    public static bool $executed = false;

    public function validate(TaskExecutionContext $context): void
    {
    }

    public function execute(TaskExecutionContext $context): TaskResult
    {
        self::$executed = true;

        return TaskResult::exitosa('ok');
    }
}

final class EnEjecucionSpyProcess implements TaskProcessInterface
{
    public static bool $executed = false;

    public function validate(TaskExecutionContext $context): void
    {
    }

    public function execute(TaskExecutionContext $context): TaskResult
    {
        self::$executed = true;

        return TaskResult::enEjecucion('async');
    }
}

final class InMemoryProcessRepository implements TaskProcessRepository
{
    public mixed $isActive = true;

    /** @var list<array<string, mixed>> */
    public array $parameters = [];

    public function listProcesses(bool $includeInactive = true): array
    {
        return [$this->findProcessById(1)];
    }

    public function findProcessById(int $processId): ?array
    {
        return [
            'id' => 1,
            'processCode' => 'P1',
            'isActive' => $this->isActive,
            'supportsSimulation' => true,
        ];
    }

    public function findProcessByCode(string $processCode): ?array
    {
        return $this->findProcessById(1);
    }

    public function listParameters(int $processId): array
    {
        return $this->parameters;
    }

    public function setActive(int $processId, bool $isActive): bool
    {
        return true;
    }
}

class InMemoryDefinitionRepository implements TaskDefinitionRepository
{
    public ?\DateTimeInterface $nextRunAt = null;
    public ?\DateTimeInterface $lastRunAt = null;
    public ?string $lastStatusCode = null;
    public ?\DateTimeInterface $nextRunAtDue = null;
    public mixed $isActive = true;
    public string $frequencyType = 'DAILY';
    public bool $nextRunCleared = false;
    public int $updateNextRunAtCalls = 0;
    public int $claimNextRunAtCalls = 0;
    public bool $claimNextRunAtSucceeds = true;
    /** @var list<string> */
    public array $timeline = [];

    public function listDefinitions(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return ['items' => [], 'total' => 0];
    }

    public function findDefinitionById(int $definitionId): ?array
    {
        return [
            'id' => 1,
            'processId' => 1,
            'isActive' => $this->isActive,
            'frequencyType' => $this->frequencyType,
            'frequencyInterval' => 1,
            'frequencyDaysMask' => null,
            'baseDate' => '2026-07-25',
            'baseTime' => '08:00',
            'nextRunAt' => ($this->nextRunAtDue ?? new \DateTimeImmutable('2020-01-01 00:00:00'))->format('Y-m-d H:i:s'),
            'taskTitle' => 'Demo',
            'notifyEnabled' => false,
        ];
    }

    public function createDefinition(array $payload): int
    {
        return 1;
    }

    public function updateDefinition(int $definitionId, array $payload): bool
    {
        return true;
    }

    public function softDeactivate(int $definitionId): bool
    {
        return true;
    }
    public function deleteDefinition(int $definitionId): bool
    {
        return true;
    }


    public function listCompanyIds(int $definitionId): array
    {
        return [10];
    }

    public function listDefinitionParameterValues(int $definitionId): array
    {
        return [];
    }

    /** @var list<array<string, mixed>> */
    public array $dueDefinitions = [];

    public function listDueDefinitions(\DateTimeInterface $asOf): array
    {
        return $this->dueDefinitions;
    }

    public function updateNextRunAt(int $definitionId, \DateTimeInterface $nextRunAt): bool
    {
        $this->nextRunAt = $nextRunAt;
        $this->updateNextRunAtCalls++;

        return true;
    }

    public function claimNextRunAt(
        int $definitionId,
        \DateTimeInterface $dueSlot,
        \DateTimeInterface $nextRunAt,
    ): bool {
        $this->claimNextRunAtCalls++;
        $this->timeline[] = 'claimSchedule';
        if (! $this->claimNextRunAtSucceeds) {
            return false;
        }
        $this->nextRunAt = $nextRunAt;

        return true;
    }

    public function clearNextRunAt(int $definitionId): bool
    {
        $this->nextRunAt = null;
        $this->nextRunCleared = true;
        $this->timeline[] = 'claimSchedule';

        return true;
    }

    public function updateLastRun(int $definitionId, \DateTimeInterface $lastRunAt, string $lastStatusCode): bool
    {
        $this->lastRunAt = $lastRunAt;
        $this->lastStatusCode = $lastStatusCode;

        return true;
    }
}

class InMemoryExecutionRepository implements TaskExecutionRepository
{
    /** @var array<string, bool> */
    public array $runningPairs = [];
    public int $createExecutionCalls = 0;
    private int $seq = 0;

    public function createBatch(array $batch): int
    {
        return ++$this->seq;
    }

    public function createExecution(array $execution): int
    {
        $this->createExecutionCalls++;

        return ++$this->seq + 100;
    }

    public function saveParameterSnapshot(int $executionId, array $parameters): void
    {
    }

    public function appendLog(int $executionId, string $level, string $message, array $context = []): void
    {
    }

    public function updateExecutionStatus(
        int $executionId,
        string $statusCode,
        ?\DateTimeInterface $finishedAt = null,
        ?string $message = null,
        bool $isOverlappedSkip = false,
    ): bool {
        return true;
    }

    public function hasRunningOverlap(int $definitionId, int $companyId): bool
    {
        return !empty($this->runningPairs[$definitionId.':'.$companyId]);
    }

    public function listExecutions(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return ['items' => [], 'total' => 0];
    }

    public function findExecutionById(int $executionId): ?array
    {
        return null;
    }

    public function listLogs(int $executionId): array
    {
        return [];
    }

    public function purgeOlderThan(int $olderThanDays, int $chunkSize = 1000): int
    {
        return 0;
    }
}

final class ClaimSlotTrackingDefinitionRepository extends InMemoryDefinitionRepository
{
    public ?\DateTimeInterface $lastClaimDueSlot = null;

    public function claimNextRunAt(
        int $definitionId,
        \DateTimeInterface $dueSlot,
        \DateTimeInterface $nextRunAt,
    ): bool {
        $this->lastClaimDueSlot = $dueSlot;

        return parent::claimNextRunAt($definitionId, $dueSlot, $nextRunAt);
    }
}

final class OrderTrackingExecutionRepository extends InMemoryExecutionRepository
{
    public function __construct(private readonly InMemoryDefinitionRepository $definitions)
    {
    }

    public function createBatch(array $batch): int
    {
        $this->definitions->timeline[] = 'createBatch';

        return parent::createBatch($batch);
    }
}
