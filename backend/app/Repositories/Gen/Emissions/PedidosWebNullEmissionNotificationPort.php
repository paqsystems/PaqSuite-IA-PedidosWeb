<?php

namespace App\Repositories\Gen\Emissions;

use PaqSuite\LaravelCore\Emissions\Contracts\EmissionNotificationPort;

final class PedidosWebNullEmissionNotificationPort implements EmissionNotificationPort
{
    public function notify(
        int|string $userId,
        string $titleKey,
        string $bodyKey,
        array $params,
        string $jobId,
    ): void {
        unset($userId, $titleKey, $bodyKey, $params, $jobId);
    }
}
