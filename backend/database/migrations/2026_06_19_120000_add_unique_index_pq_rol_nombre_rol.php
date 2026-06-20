<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('Pq_Rol')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement(<<<'SQL'
IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'uq_pq_rol_nombre_rol' AND object_id = OBJECT_ID(N'Pq_Rol')
)
BEGIN
    CREATE UNIQUE INDEX [uq_pq_rol_nombre_rol] ON [Pq_Rol] ([nombre_rol])
    WHERE [nombre_rol] IS NOT NULL
END
SQL);

            return;
        }

        Schema::table('Pq_Rol', function ($table): void {
            $table->unique('nombre_rol', 'uq_pq_rol_nombre_rol');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('Pq_Rol')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            DB::statement('DROP INDEX IF EXISTS [uq_pq_rol_nombre_rol] ON [Pq_Rol]');

            return;
        }

        Schema::table('Pq_Rol', function ($table): void {
            $table->dropUnique('uq_pq_rol_nombre_rol');
        });
    }
};
