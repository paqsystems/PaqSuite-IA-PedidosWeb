<?php

namespace App\Console\Commands;

use App\Support\Installation\PedidosWebSdkSqlDeployer;
use Illuminate\Console\Command;
use Throwable;

/**
 * Despliega SQL GEN-15 del SDK (CREATE/ALTER) + catálogo host. Seguro para Forge.
 */
final class DeploySdkSqlCommand extends Command
{
    protected $signature = 'paqsuite:deploy-sdk-sql
                            {--skip-emission : No desplegar SP/catálogo GEN-15}';

    protected $description = 'Host PedidosWeb: SQL SDK GEN-15 (SPs + schemas diseñador + catálogo 8 informes), sin DROP';

    public function handle(PedidosWebSdkSqlDeployer $deployer): int
    {
        if ($this->option('skip-emission')) {
            $this->info('GEN-15 omitido (--skip-emission).');

            return self::SUCCESS;
        }

        try {
            $result = $deployer->deployEmission();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($result['skipped']) {
            $this->warn('GEN-15 no desplegado: '.($result['reason'] ?? 'motivo no informado'));

            return self::SUCCESS;
        }

        $this->info('SP GEN-15: '.implode(', ', $result['sqlFiles']));
        $this->info('Catálogo emisión: '.($result['catalogSeeded'] ? 'actualizado' : 'omitido (faltan tablas pq_emission_*)'));
        $this->info('Designer schemas: '.$result['designerSchemas'].' upsert(s)');

        return self::SUCCESS;
    }
}
