<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tablas Chat Asistente IA transversales PaqSuite: quitar prefijo pedidosweb_.
 *
 * pq_pedidosweb_asistente_ia_proveedores → pq_asistente_ia_proveedores
 * pq_pedidosweb_asistente_ia_credenciales → pq_asistente_ia_credenciales
 */
return new class extends Migration
{
    private const OLD_PROVEEDORES = 'pq_pedidosweb_asistente_ia_proveedores';

    private const NEW_PROVEEDORES = 'pq_asistente_ia_proveedores';

    private const OLD_CREDENCIALES = 'pq_pedidosweb_asistente_ia_credenciales';

    private const NEW_CREDENCIALES = 'pq_asistente_ia_credenciales';

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            $this->upSqlServer();

            return;
        }

        $this->renameTableIfNeeded(self::OLD_PROVEEDORES, self::NEW_PROVEEDORES);
        $this->renameTableIfNeeded(self::OLD_CREDENCIALES, self::NEW_CREDENCIALES);
    }

    private function upSqlServer(): void
    {
        $this->renameTableIfNeeded(self::OLD_PROVEEDORES, self::NEW_PROVEEDORES);
        $this->renameSqlServerIndex(
            self::NEW_PROVEEDORES,
            'UX_pq_pedidosweb_asistente_ia_proveedores_provider_id',
            'UX_pq_asistente_ia_proveedores_provider_id',
        );

        $this->renameTableIfNeeded(self::OLD_CREDENCIALES, self::NEW_CREDENCIALES);
        $this->renameSqlServerIndex(
            self::NEW_CREDENCIALES,
            'UX_pq_pedidosweb_asistente_ia_credenciales_user_id',
            'UX_pq_asistente_ia_credenciales_user_id',
        );
        $this->renameSqlServerIndex(
            self::NEW_CREDENCIALES,
            'IX_pq_pedidosweb_asistente_ia_credenciales_user_id',
            'IX_pq_asistente_ia_credenciales_user_id',
        );
        $this->renameSqlServerDefault(
            self::NEW_CREDENCIALES,
            'DF_pq_pedidosweb_asistente_ia_credenciales_display_name',
            'DF_pq_asistente_ia_credenciales_display_name',
        );
    }

    private function renameTableIfNeeded(string $oldName, string $newName): void
    {
        if (! Schema::hasTable($oldName) || Schema::hasTable($newName)) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement("EXEC sp_rename N'[{$oldName}]', N'{$newName}';");

            return;
        }

        Schema::rename($oldName, $newName);
    }

    private function renameSqlServerIndex(string $table, string $oldName, string $newName): void
    {
        DB::statement(<<<SQL
IF EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'{$oldName}'
      AND object_id = OBJECT_ID(N'{$table}')
) AND NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'{$newName}'
      AND object_id = OBJECT_ID(N'{$table}')
)
    EXEC sp_rename N'{$table}.{$oldName}', N'{$newName}', N'INDEX';
SQL);
    }

    private function renameSqlServerDefault(string $table, string $oldName, string $newName): void
    {
        DB::statement(<<<SQL
IF EXISTS (
    SELECT 1 FROM sys.default_constraints
    WHERE name = N'{$oldName}'
      AND parent_object_id = OBJECT_ID(N'{$table}')
) AND NOT EXISTS (
    SELECT 1 FROM sys.default_constraints
    WHERE name = N'{$newName}'
      AND parent_object_id = OBJECT_ID(N'{$table}')
)
    EXEC sp_rename N'{$oldName}', N'{$newName}', N'OBJECT';
SQL);
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            $this->renameSqlServerDefault(
                self::OLD_CREDENCIALES,
                'DF_pq_asistente_ia_credenciales_display_name',
                'DF_pq_pedidosweb_asistente_ia_credenciales_display_name',
            );
            $this->renameSqlServerIndex(
                self::OLD_CREDENCIALES,
                'IX_pq_asistente_ia_credenciales_user_id',
                'IX_pq_pedidosweb_asistente_ia_credenciales_user_id',
            );
            $this->renameSqlServerIndex(
                self::OLD_CREDENCIALES,
                'UX_pq_asistente_ia_credenciales_user_id',
                'UX_pq_pedidosweb_asistente_ia_credenciales_user_id',
            );
            $this->renameSqlServerIndex(
                self::OLD_PROVEEDORES,
                'UX_pq_asistente_ia_proveedores_provider_id',
                'UX_pq_pedidosweb_asistente_ia_proveedores_provider_id',
            );
        }

        $this->renameTableIfNeeded(self::NEW_CREDENCIALES, self::OLD_CREDENCIALES);
        $this->renameTableIfNeeded(self::NEW_PROVEEDORES, self::OLD_PROVEEDORES);
    }
};
