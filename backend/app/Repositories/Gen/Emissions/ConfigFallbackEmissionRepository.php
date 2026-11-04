<?php

namespace App\Repositories\Gen\Emissions;

use Illuminate\Database\QueryException;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionRepository;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionJob;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionProcess;
use PaqSuite\LaravelCore\Emissions\StoredProcedureEmissionRepository;

/**
 * Delega al SP adapter; parámetros Emission* caen a config local si el diccionario aún no tiene filas.
 */
final class ConfigFallbackEmissionRepository implements EmissionRepository
{
    public function __construct(
        private readonly StoredProcedureEmissionRepository $inner,
    ) {
    }

    public function findProcess(string $processCode): ?EmissionProcess
    {
        return $this->inner->findProcess($processCode);
    }

    public function parameter(string $program, string $key): string|int|null
    {
        try {
            $value = $this->inner->parameter($program, $key);
        } catch (QueryException) {
            $value = null;
        }

        if ($value !== null) {
            return $value;
        }

        $fromConfig = config('paqsuiteCapabilities.parameters.'.$program.'.'.$key);

        return is_string($fromConfig) || is_int($fromConfig) ? $fromConfig : null;
    }

    public function createJob(EmissionJob $job): void
    {
        $this->inner->createJob($job);
    }

    public function findJob(string $jobId, int|string|null $companyId): ?EmissionJob
    {
        return $this->inner->findJob($jobId, $companyId);
    }

    public function transition(string $jobId, string $expectedStatus, string $status, array $fields = []): bool
    {
        return $this->inner->transition($jobId, $expectedStatus, $status, $fields);
    }

    public function artifactsForPurge(int $olderThanDays): array
    {
        return $this->inner->artifactsForPurge($olderThanDays);
    }

    public function clearArtifact(string $jobId, string $expectedPath): bool
    {
        return $this->inner->clearArtifact($jobId, $expectedPath);
    }
}
