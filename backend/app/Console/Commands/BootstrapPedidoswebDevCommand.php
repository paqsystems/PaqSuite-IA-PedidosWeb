<?php

namespace App\Console\Commands;

use App\Services\Seed\PedidosWebDevDataSeeder;
use App\Services\Seed\PedidosWebDevSchemaBootstrap;
use App\Services\Seed\PqParametrosGralPedidosWebSeeder;
use Illuminate\Console\Command;
use Throwable;

final class BootstrapPedidoswebDevCommand extends Command
{
    protected $signature = 'paqsuite:bootstrap-pedidosweb-dev
                            {--skip-menus : No ejecutar paqsuite:seed-menus-mvp}
                            {--skip-seguridad : No ejecutar paqsuite:seed-seguridad-mvp}';

    protected $description = 'Recrea tablas pq_pedidosweb_* y PQ_parametros_gral (PedidosWeb) con datos seed MVP';

    public function handle(
        PedidosWebDevSchemaBootstrap $schemaBootstrap,
        PedidosWebDevDataSeeder $dataSeeder,
        PqParametrosGralPedidosWebSeeder $parametrosSeeder,
    ): int {
        $database = (string) config('database.connections.sqlsrv.database');
        $this->warn("Base objetivo: {$database}");
        $this->warn('DESTRUCTIVO: DROP + CREATE de todas las tablas pq_pedidosweb_* y PQ_parametros_gral.');
        $this->warn('No usar sobre Ankas_del_sur u otras bases ERP compartidas salvo ALLOW_PEDIDOSWEB_DESTRUCTIVE_BOOTSTRAP=true.');

        if (! $this->option('no-interaction') && ! $this->confirm('¿Continuar?', false)) {
            $this->info('Operación cancelada.');

            return self::SUCCESS;
        }

        try {
            $this->info('Recreando tablas pq_pedidosweb_*...');
            $schemaBootstrap->recreatePedidosWebTables();

            $jsonPath = dirname(base_path()).'/docs/backend/seed/PQ_PARAMETROS_GRAL/PQ_PARAMETROS_GRAL.PedidosWeb.seed.json';
            $this->info('Recreando PQ_parametros_gral y cargando parámetros PedidosWeb...');
            $parametrosCount = $parametrosSeeder->seedFromJsonFile($jsonPath, true);
            $this->line("  Parámetros insertados: {$parametrosCount}");

            $this->info('Cargando datos comerciales MVP (artículos, stock, consultas, comprobantes)...');
            $dataSeeder->seedMvpDevData();

            if (! $this->option('skip-menus')) {
                $this->call('paqsuite:seed-menus-mvp');
            }

            config(['paqsuite_seed.syncCommercial' => true]);

            if (! $this->option('skip-seguridad')) {
                $this->call('paqsuite:seed-seguridad-mvp');
            }

            $this->call('paqsuite:sync-pedidosweb-login-from-users');

            $this->newLine();
            $this->info('Bootstrap PedidosWeb completado.');
            $this->line('Login: users.codigo + SEED_MVP_PASSWORD del .env (ej. supervisor.mvp).');
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
