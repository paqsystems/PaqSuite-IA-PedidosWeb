<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CC PQ #13 — leyenda_1..5 nvarchar(60) en cabecera y clientes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            $this->shrinkStringColumns(60);

            return;
        }

        $this->truncateThenAlterSqlServer(60);
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            $this->shrinkStringColumns(255);

            return;
        }

        $this->alterSqlServerLength(255);
    }

    private function truncateThenAlterSqlServer(int $length): void
    {
        foreach ($this->targets() as [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            if (! $this->columnLongerThan($table, $column, $length)) {
                continue;
            }

            DB::statement(
                "UPDATE [{$table}] SET [{$column}] = LEFT([{$column}], {$length}) WHERE [{$column}] IS NOT NULL AND LEN([{$column}]) > {$length}"
            );
            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [{$column}] nvarchar({$length}) NULL");
        }
    }

    private function alterSqlServerLength(int $length): void
    {
        foreach ($this->targets() as [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::statement("ALTER TABLE [{$table}] ALTER COLUMN [{$column}] nvarchar({$length}) NULL");
        }
    }

    private function shrinkStringColumns(int $length): void
    {
        foreach ($this->targets() as [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            Schema::table($table, function ($blueprint) use ($column, $length): void {
                $blueprint->string($column, $length)->nullable()->change();
            });
        }
    }

    private function columnLongerThan(string $table, string $column, int $length): bool
    {
        $maxLength = DB::selectOne(
            <<<'SQL'
SELECT c.max_length AS max_length
FROM sys.columns c WITH (NOLOCK)
INNER JOIN sys.tables t WITH (NOLOCK) ON t.object_id = c.object_id
INNER JOIN sys.schemas s WITH (NOLOCK) ON s.schema_id = t.schema_id
WHERE s.name = N'dbo' AND t.name = ? AND c.name = ?
SQL,
            [$table, $column]
        );

        $bytes = (int) ($maxLength->max_length ?? 0);

        return $bytes > ($length * 2);
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private function targets(): array
    {
        $columns = ['leyenda_1', 'leyenda_2', 'leyenda_3', 'leyenda_4', 'leyenda_5'];
        $targets = [];

        foreach (['pq_pedidosweb_pedidoscabecera', 'pq_pedidosweb_clientes'] as $table) {
            foreach ($columns as $column) {
                $targets[] = [$table, $column];
            }
        }

        return $targets;
    }
};
