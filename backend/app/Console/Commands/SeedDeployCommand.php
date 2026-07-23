<?php

namespace App\Console\Commands;

use App\Models\PqRol;
use App\Models\PqRolAtributo;
use Database\Seeders\ChatAssistant\ChatAssistantProviderCatalogSeeder;
use Database\Seeders\ExcelImport\PedidosWebExcelImportCatalogSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Seeds idempotentes seguros para el Deploy Script de Forge (post-migrate).
 * No ejecuta bootstrap destructivo ni seed-seguridad completo salvo --with-seguridad.
 */
final class SeedDeployCommand extends Command
{
    protected $signature = 'paqsuite:seed-deploy
                            {--with-seguridad : También ejecuta paqsuite:seed-seguridad-mvp (usuarios/roles MVP; puede actualizar password_hash de usuarios seed)}
                            {--skip-excel : No seedear catálogo Excel PEDIDO_*}
                            {--skip-menus : No seedear pq_menus MVP}
                            {--skip-chat : No seedear catálogo proveedores chat asistente}
                            {--skip-atributos : No upsert de PQ_RolAtributo desde visibilityProcedimientosByRole}';

    protected $description = 'Post-deploy: menú MVP, catálogo Excel, atributos visibility y catálogo chat (idempotente)';

    public function handle(): int
    {
        try {
            if (! $this->option('skip-menus')) {
                $exit = $this->call('paqsuite:seed-menus-mvp');
                if ($exit !== self::SUCCESS) {
                    return $exit;
                }
            }

            if (! $this->option('skip-excel')) {
                if (! Schema::hasTable('pq_excel_procesos') || ! Schema::hasTable('pq_excel_procesos_campos')) {
                    $this->warn('Tablas Excel no presentes; se omite catálogo PEDIDO_*. Correr migrate primero.');
                } else {
                    $this->call('db:seed', [
                        '--class' => PedidosWebExcelImportCatalogSeeder::class,
                        '--force' => true,
                    ]);
                    $this->info('Catálogo Excel PedidosWeb (PEDIDO_INDIVIDUAL / PEDIDO_MASIVO) actualizado.');
                }
            }

            if (! $this->option('skip-chat')) {
                if (! Schema::hasTable('pq_asistente_ia_proveedores')) {
                    $this->warn('Tabla pq_asistente_ia_proveedores no presente; se omite catálogo chat.');
                } else {
                    $this->call('db:seed', [
                        '--class' => ChatAssistantProviderCatalogSeeder::class,
                        '--force' => true,
                    ]);
                    $this->info('Catálogo chat asistente actualizado.');
                }
            }

            if (! $this->option('skip-atributos')) {
                $this->syncVisibilityRoleAtributos();
            }

            if ($this->option('with-seguridad')) {
                $this->warn('Ejecutando seed-seguridad-mvp (puede tocar usuarios MVP).');
                $exit = $this->call('paqsuite:seed-seguridad-mvp');
                if ($exit !== self::SUCCESS) {
                    return $exit;
                }
            }

            $this->info('paqsuite:seed-deploy completado.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Upsert solo de procedimientos listados en config (no borra atributos existentes).
     */
    private function syncVisibilityRoleAtributos(): void
    {
        if (! Schema::hasTable('Pq_Rol') || ! Schema::hasTable('PQ_RolAtributo')) {
            $this->warn('Tablas de roles/atributos no presentes; se omite sync visibility.');

            return;
        }

        /** @var array<string, list<string>> $byRole */
        $byRole = config('paqsuite_mvp.visibilityProcedimientosByRole', []);
        $synced = 0;

        foreach ($byRole as $nombreRol => $procedimientos) {
            $rol = PqRol::query()->where('nombre_rol', $nombreRol)->first();
            if ($rol === null) {
                $this->line("Rol «{$nombreRol}» no existe; se omite sync de atributos.");
                continue;
            }

            foreach (array_values(array_unique(array_filter($procedimientos, 'is_string'))) as $procedimiento) {
                $exists = PqRolAtributo::query()
                    ->where('id_rol', $rol->id)
                    ->where('procedimiento', $procedimiento)
                    ->exists();

                if ($exists) {
                    continue;
                }

                PqRolAtributo::query()->create([
                    'id_rol' => $rol->id,
                    'procedimiento' => $procedimiento,
                    'permiso_alta' => false,
                    'permiso_baja' => false,
                    'permiso_modi' => false,
                    'permiso_repo' => true,
                ]);
                $synced++;
            }
        }

        $this->info("Atributos visibility: {$synced} filas nuevas (sin pisar existentes).");
    }
}
