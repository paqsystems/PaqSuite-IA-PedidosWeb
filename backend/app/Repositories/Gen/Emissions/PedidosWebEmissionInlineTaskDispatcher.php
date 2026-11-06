<?php

namespace App\Repositories\Gen\Emissions;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionTaskDispatcher;
use PaqSuite\LaravelCore\Emissions\Tasks\EmissionBatchTaskHandler;

/**
 * Ejecuta EMISSION_BATCH en línea (piloto; datasets acotados bajo umbral async).
 */
final class PedidosWebEmissionInlineTaskDispatcher implements EmissionTaskDispatcher
{
    public function __construct(private readonly Application $app)
    {
    }

    public function dispatchSystemTask(
        string $processCode,
        array $parameters,
        int|string|null $companyId,
    ): string {
        unset($processCode);
        $taskId = (string) Str::uuid();
        $this->app->make(EmissionBatchTaskHandler::class)->handle([
            'jobId' => (string) ($parameters['jobId'] ?? ''),
            'companyId' => $companyId,
        ]);

        return $taskId;
    }
}
