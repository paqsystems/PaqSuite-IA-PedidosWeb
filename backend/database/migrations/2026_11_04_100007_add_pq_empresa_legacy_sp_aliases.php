<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alias legacy (IDEmpresa, …) sobre pq_empresa: SQL Server trata PQ_Empresa = pq_empresa.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv' || ! Schema::hasTable('pq_empresa')) {
            return;
        }

        $adds = [
            'IDEmpresa' => 'id',
            'NombreEmpresa' => 'nombre',
            'NombreBD' => 'nombre_bd',
            'Habilita' => 'CAST([activo] AS INT)',
        ];

        foreach ($adds as $alias => $expression) {
            if (Schema::hasColumn('pq_empresa', $alias)) {
                continue;
            }

            $isCast = str_contains($expression, 'CAST');
            $persisted = $isCast ? '' : ' PERSISTED';
            $exprSql = $isCast ? $expression : "[{$expression}]";

            DB::statement(
                "IF COL_LENGTH(N'dbo.pq_empresa', N'{$alias}') IS NULL
                 BEGIN
                     ALTER TABLE dbo.pq_empresa ADD {$alias} AS {$exprSql}{$persisted};
                 END"
            );
        }
    }

    public function down(): void
    {
        // Sin revert en demo.
    }
};
