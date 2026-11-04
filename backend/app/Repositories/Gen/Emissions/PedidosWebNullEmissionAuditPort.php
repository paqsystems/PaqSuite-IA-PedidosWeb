<?php

namespace App\Repositories\Gen\Emissions;

use PaqSuite\LaravelCore\Emissions\Contracts\EmissionAuditPort;

final class PedidosWebNullEmissionAuditPort implements EmissionAuditPort
{
    public function record(
        string $action,
        string $result,
        string $jobId,
        int|string|null $companyId,
        int|string|null $userId,
        array $payload,
    ): void {
        unset($action, $result, $jobId, $companyId, $userId, $payload);
    }
}
