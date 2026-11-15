<?php

declare(strict_types=1);

namespace Tests\Unit\Gen\Tasks;

use Illuminate\Http\JsonResponse;
use PaqSuite\LaravelCore\Http\Controllers\Tasks\TaskDefinitionController;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskDefinitionRepository;
use PaqSuite\LaravelCore\Tasks\TaskDomainException;
use PaqSuite\LaravelCore\Tasks\TaskErrorCodes;
use Tests\TestCase;

/** CC PQ #22 — eliminación física vía deleteDefinition (cascade historial en SP). */
final class Gen13Cc22TaskDefinitionControllerDestroyTest extends TestCase
{
    public function testDestroyCascadeWithHistorySucceeds(): void
    {
        $repository = $this->repository(fn (int $id): bool => true);
        $controller = new TaskDefinitionController($repository);
        $response = $controller->destroy(7);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertSame(0, $payload['error']);
        $this->assertTrue($payload['resultado']['deleted']);
    }

    public function testDestroyNotFoundReturns4404(): void
    {
        $repository = $this->repository(
            fn (int $id): bool => throw TaskDomainException::of(TaskErrorCodes::DEFINITION_NOT_FOUND)
        );
        $controller = new TaskDefinitionController($repository);
        $response = $controller->destroy(99);

        $this->assertSame(404, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertSame(4404, $payload['error']);
    }

  /**
   * @param  callable(int): bool  $delete
   */
    private function repository(callable $delete): TaskDefinitionRepository
    {
        return new class ($delete) implements TaskDefinitionRepository {
            public function __construct(private $delete)
            {
            }

            public function listDefinitions(array $filters = [], int $page = 1, int $pageSize = 25): array
            {
                return ['items' => [], 'total' => 0];
            }

            public function findDefinitionById(int $definitionId): ?array
            {
                return ['id' => $definitionId];
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
                return ($this->delete)($definitionId);
            }

            public function listCompanyIds(int $definitionId): array
            {
                return [];
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
        };
    }
}