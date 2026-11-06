<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv' || ! Schema::hasTable('pq_grid_layouts')) {
            return;
        }

        $this->dropComputedColumn('pq_grid_layouts', 'user_id');

        if (! Schema::hasColumn('pq_grid_layouts', 'user_id')) {
            DB::statement('ALTER TABLE dbo.pq_grid_layouts ADD user_id BIGINT NULL');
        }

        if (Schema::hasColumn('pq_grid_layouts', 'created_by_user_id')) {
            DB::statement(
                'UPDATE dbo.pq_grid_layouts SET user_id = created_by_user_id WHERE user_id IS NULL'
            );
        }

        DB::unprepared(<<<'SQL'
CREATE OR ALTER TRIGGER dbo.tr_pq_grid_layouts_user_id_sync
ON dbo.pq_grid_layouts
AFTER INSERT, UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE g
    SET
        g.user_id = COALESCE(i.user_id, i.created_by_user_id, g.user_id),
        g.created_by_user_id = COALESCE(i.created_by_user_id, i.user_id, g.created_by_user_id)
    FROM dbo.pq_grid_layouts g
    INNER JOIN inserted i ON i.id = g.id;
END;
SQL);
    }

    private function dropComputedColumn(string $table, string $column): void
    {
        DB::statement(
            "IF EXISTS (
                SELECT 1 FROM sys.computed_columns
                WHERE object_id = OBJECT_ID(N'dbo.{$table}') AND name = N'{$column}'
             )
             BEGIN
                 ALTER TABLE dbo.{$table} DROP COLUMN [{$column}];
             END"
        );
    }

    public function down(): void
    {
        DB::statement(
            "IF OBJECT_ID(N'dbo.tr_pq_grid_layouts_user_id_sync', N'TR') IS NOT NULL DROP TRIGGER dbo.tr_pq_grid_layouts_user_id_sync;"
        );
    }
};
