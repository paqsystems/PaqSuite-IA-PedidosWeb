<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GEN-13: `pq_sp_task_definition_parameter_set` inserta `process_parameter_id`
 * (modelo MONO). Sin la columna, SQL Server falla al compilar/ejecutar el SP.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_task_definition_parameter')) {
            return;
        }

        if (Schema::hasColumn('pq_task_definition_parameter', 'process_parameter_id')) {
            return;
        }

        Schema::table('pq_task_definition_parameter', function (Blueprint $table) {
            $table->unsignedBigInteger('process_parameter_id')->nullable();
            // SQL Server: sin FK aquí (cascadas múltiples con pq_task_definition).
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pq_task_definition_parameter')) {
            return;
        }

        if (! Schema::hasColumn('pq_task_definition_parameter', 'process_parameter_id')) {
            return;
        }

        Schema::table('pq_task_definition_parameter', function (Blueprint $table) {
            $table->dropColumn('process_parameter_id');
        });
    }
};
