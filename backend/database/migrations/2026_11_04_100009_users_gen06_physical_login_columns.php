<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * GEN-06 escribe usuario/name; PedidosWeb usa codigo/name_user — columnas físicas + trigger.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv' || ! Schema::hasTable('users')) {
            return;
        }

        $this->dropColumnIfExists('users', 'usuario');
        $this->dropColumnIfExists('users', 'name');

        if (! Schema::hasColumn('users', 'usuario') && Schema::hasColumn('users', 'codigo')) {
            DB::statement('ALTER TABLE dbo.users ADD usuario NVARCHAR(64) NULL');
        }
        if (! Schema::hasColumn('users', 'name') && Schema::hasColumn('users', 'name_user')) {
            DB::statement('ALTER TABLE dbo.users ADD name NVARCHAR(255) NULL');
        }

        DB::statement(<<<'SQL'
UPDATE dbo.users
SET usuario = COALESCE(usuario, codigo),
    name = COALESCE(name, name_user)
WHERE codigo IS NOT NULL OR name_user IS NOT NULL;
SQL);

        DB::unprepared(<<<'SQL'
CREATE OR ALTER TRIGGER dbo.tr_users_pedidosweb_gen06_sync
ON dbo.users
AFTER INSERT, UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE u
    SET
        u.usuario = COALESCE(i.usuario, i.codigo, u.usuario),
        u.codigo = COALESCE(i.codigo, i.usuario, u.codigo),
        u.name = COALESCE(i.name, i.name_user, u.name),
        u.name_user = COALESCE(i.name_user, i.name, u.name_user)
    FROM dbo.users u
    INNER JOIN inserted i ON i.id = u.id;
END;
SQL);
    }

    private function dropColumnIfExists(string $table, string $column): void
    {
        DB::statement(
            "IF COL_LENGTH(N'dbo.{$table}', N'{$column}') IS NOT NULL
             BEGIN
                 DECLARE @sql NVARCHAR(MAX);
                 IF EXISTS (
                     SELECT 1 FROM sys.computed_columns
                     WHERE object_id = OBJECT_ID(N'dbo.{$table}') AND name = N'{$column}'
                 )
                 BEGIN
                     SET @sql = N'ALTER TABLE dbo.{$table} DROP COLUMN [{$column}];';
                     EXEC sp_executesql @sql;
                 END
             END"
        );
    }

    public function down(): void
    {
        DB::statement(
            "IF OBJECT_ID(N'dbo.tr_users_pedidosweb_gen06_sync', N'TR') IS NOT NULL DROP TRIGGER dbo.tr_users_pedidosweb_gen06_sync;"
        );
    }
};
