<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GEN-34 / GEN-15: `kind` varchar(16) truncaba `emissionDesignerSchema` (22).
 * Framework migration 2026_10_27_000037 creó la columna corta; el host la amplía.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_installation_run_step')) {
            return;
        }

        Schema::table('pq_installation_run_step', function (Blueprint $table): void {
            $table->string('kind', 64)->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pq_installation_run_step')) {
            return;
        }

        Schema::table('pq_installation_run_step', function (Blueprint $table): void {
            $table->string('kind', 16)->change();
        });
    }
};
