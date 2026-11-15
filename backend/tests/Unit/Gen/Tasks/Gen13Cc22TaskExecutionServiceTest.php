<?php

declare(strict_types=1);

namespace Tests\Unit\Gen\Tasks;

require_once __DIR__.'/Gen13Cc22TaskExecutionDoubles.php';

use PaqSuite\LaravelCore\Tasks\TaskDomainException;
use PaqSuite\LaravelCore\Tasks\TaskExecutionContext;
use PaqSuite\LaravelCore\Tasks\TaskExecutionService;
use PaqSuite\LaravelCore\Tasks\TaskExecutionStatuses;
use PaqSuite\LaravelCore\Tasks\TaskProcessRegistry;
use PHPUnit\Framework\TestCase;

/** CC PQ #22 — ejecución manual async (EN_EJECUCION) y CC #20 inactiva. */
final class Gen13Cc22TaskExecutionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SpyProcess::$executed = false;
        EnEjecucionSpyProcess::$executed = false;
    }

    public function testManualRunAggregateStatusEnEjecucion(): void
    {
        EnEjecucionSpyProcess::$executed = false;
        $registry = new TaskProcessRegistry();
        $registry->register('P1', EnEjecucionSpyProcess::class);

        $service = new TaskExecutionService(
            $registry,
            new InMemoryProcessRepository(),
            new InMemoryDefinitionRepository(),
            new InMemoryExecutionRepository(),
        );

        $result = $service->run(1, TaskExecutionContext::MODE_MANUAL);
        $this->assertSame(TaskExecutionStatuses::EN_EJECUCION, $result['statusCode']);
        $this->assertSame(TaskExecutionStatuses::EN_EJECUCION, $result['executions'][0]['statusCode']);
        $this->assertTrue(EnEjecucionSpyProcess::$executed);
    }

    public function testManualInactiveThrowsPerCc20(): void
    {
        $definitionRepo = new InMemoryDefinitionRepository();
        $definitionRepo->isActive = false;

        $service = new TaskExecutionService(
            new TaskProcessRegistry(),
            new InMemoryProcessRepository(),
            $definitionRepo,
            new InMemoryExecutionRepository(),
        );

        $this->expectException(TaskDomainException::class);
        $this->expectExceptionMessage('definition inactive');

        $service->run(1, TaskExecutionContext::MODE_MANUAL);
        $this->assertFalse(SpyProcess::$executed);
    }
}