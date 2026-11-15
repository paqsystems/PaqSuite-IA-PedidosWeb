<?php

declare(strict_types=1);

namespace Tests\Unit\Gen\Tasks;

use PaqSuite\LaravelCore\Mail\MailEngine;
use PaqSuite\LaravelCore\Mail\MailMessage;
use PaqSuite\LaravelCore\Mail\MailSendResult;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskDefinitionRepository;
use PaqSuite\LaravelCore\Tasks\TaskExecutionStatuses;
use PaqSuite\LaravelCore\Tasks\TaskMailNotifier;
use PHPUnit\Framework\TestCase;

/** CC PQ #22 — notificación por mail según tildes de definición. */
final class Gen13Cc22TaskMailNotifierTest extends TestCase
{
    public function testSendsOnFailureWhenNotifyOnError(): void
    {
        $engine = new Gen13Cc22CountingMailEngine();
        $notifier = new TaskMailNotifier($engine, new Gen13Cc22StubDefinitionRepository(
            notifyEnabled: true,
            notifyOnError: true,
            notifyOnSuccess: false,
            notifyEmails: ['ops@example.com'],
        ));

        $notifier->notifyAfterRun([
            'definitionId' => 1,
            'executionId' => 10,
            'companyId' => 5,
            'statusCode' => TaskExecutionStatuses::FALLIDA,
            'isSimulation' => false,
        ]);

        $this->assertSame(1, $engine->sendCount);
    }

    public function testDoesNotSendOnFailureWhenNotifyOnErrorOff(): void
    {
        $engine = new Gen13Cc22CountingMailEngine();
        $notifier = new TaskMailNotifier($engine, new Gen13Cc22StubDefinitionRepository(
            notifyEnabled: true,
            notifyOnError: false,
            notifyOnSuccess: true,
            notifyEmails: ['ops@example.com'],
        ));

        $notifier->notifyAfterRun([
            'definitionId' => 1,
            'executionId' => 10,
            'companyId' => 5,
            'statusCode' => TaskExecutionStatuses::FALLIDA,
            'isSimulation' => false,
        ]);

        $this->assertSame(0, $engine->sendCount);
    }

    public function testSendsOnSuccessWhenNotifyOnSuccess(): void
    {
        $engine = new Gen13Cc22CountingMailEngine();
        $notifier = new TaskMailNotifier($engine, new Gen13Cc22StubDefinitionRepository(
            notifyEnabled: true,
            notifyOnError: false,
            notifyOnSuccess: true,
            notifyEmails: ['ops@example.com'],
        ));

        $notifier->notifyAfterRun([
            'definitionId' => 1,
            'executionId' => 10,
            'companyId' => 5,
            'statusCode' => TaskExecutionStatuses::EXITOSA,
            'isSimulation' => false,
        ]);

        $this->assertSame(1, $engine->sendCount);
    }

    public function testSimulationNeverSends(): void
    {
        $engine = new Gen13Cc22CountingMailEngine();
        $notifier = new TaskMailNotifier($engine, new Gen13Cc22StubDefinitionRepository(
            notifyEnabled: true,
            notifyOnError: true,
            notifyOnSuccess: true,
            notifyEmails: ['ops@example.com'],
        ));

        $notifier->notifyAfterRun([
            'definitionId' => 1,
            'executionId' => 10,
            'companyId' => 5,
            'statusCode' => TaskExecutionStatuses::FALLIDA,
            'isSimulation' => true,
        ]);

        $this->assertSame(0, $engine->sendCount);
    }
}

final class Gen13Cc22CountingMailEngine implements MailEngine
{
    public int $sendCount = 0;

    public ?MailMessage $lastMessage = null;

    public function send(MailMessage $message): MailSendResult
    {
        $this->sendCount++;
        $this->lastMessage = $message;

        return MailSendResult::success('test-id');
    }
}

final class Gen13Cc22StubDefinitionRepository implements TaskDefinitionRepository
{
    public function __construct(
        private readonly bool $notifyEnabled,
        private readonly bool $notifyOnError,
        private readonly bool $notifyOnSuccess,
        /** @var list<string> */
        private readonly array $notifyEmails,
    ) {
    }

    public function listDefinitions(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return ['items' => [], 'total' => 0];
    }

    public function findDefinitionById(int $definitionId): ?array
    {
        return [
            'id' => $definitionId,
            'processId' => 1,
            'taskTitle' => 'Backup',
            'notifyEnabled' => $this->notifyEnabled,
            'notifyOnError' => $this->notifyOnError,
            'notifyOnSuccess' => $this->notifyOnSuccess,
            'notifyEmails' => $this->notifyEmails,
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

    public function listDueDefinitions(\DateTimeInterface $asOf): array
    {
        return [];
    }

    public function updateNextRunAt(int $definitionId, \DateTimeInterface $nextRunAt): bool
    {
        return true;
    }

    public function claimNextRunAt(
        int $definitionId,
        \DateTimeInterface $dueSlot,
        \DateTimeInterface $nextRunAt,
    ): bool {
        return true;
    }

    public function clearNextRunAt(int $definitionId): bool
    {
        return true;
    }

    public function updateLastRun(int $definitionId, \DateTimeInterface $lastRunAt, string $lastStatusCode): bool
    {
        return true;
    }
}