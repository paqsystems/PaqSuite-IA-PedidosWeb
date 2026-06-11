<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
            $this->upSqlServer();

            return;
        }

        $this->upGeneric();
    }

    private function upSqlServer(): void
    {
        if (! Schema::hasTable('pq_pivots_config')) {
            DB::statement(<<<'SQL'
CREATE TABLE [pq_pivots_config] (
    [pivot_id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [consulta_id] NVARCHAR(100) NOT NULL,
    [nombre] NVARCHAR(200) NOT NULL,
    [configuracion_json] NVARCHAR(MAX) NOT NULL,
    [version_definicion_consulta] INT NOT NULL,
    [created_by_user_id] BIGINT NOT NULL,
    [eliminado] BIT NOT NULL DEFAULT 0,
    [activo] BIT NOT NULL DEFAULT 1,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [FK_pq_pivots_config_user] FOREIGN KEY ([created_by_user_id]) REFERENCES [users]([id])
);
SQL);
            DB::statement(<<<'SQL'
CREATE UNIQUE INDEX [UX_pq_pivots_config_consulta_nombre]
    ON [pq_pivots_config] ([consulta_id], [nombre])
    WHERE [eliminado] = 0;
SQL);
        }

        if (! Schema::hasTable('pq_pivots_config_last_used')) {
            DB::statement(<<<'SQL'
CREATE TABLE [pq_pivots_config_last_used] (
    [user_id] BIGINT NOT NULL,
    [consulta_id] NVARCHAR(100) NOT NULL,
    [pivot_id] BIGINT NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [PK_pq_pivots_config_last_used] PRIMARY KEY ([user_id], [consulta_id]),
    CONSTRAINT [FK_pq_pivots_config_last_used_user] FOREIGN KEY ([user_id]) REFERENCES [users]([id]),
    CONSTRAINT [FK_pq_pivots_config_last_used_pivot] FOREIGN KEY ([pivot_id]) REFERENCES [pq_pivots_config]([pivot_id]) ON DELETE SET NULL
);
SQL);
        }
    }

    private function upGeneric(): void
    {
        if (! Schema::hasTable('pq_pivots_config')) {
            Schema::create('pq_pivots_config', function (Blueprint $table): void {
                $table->id('pivot_id');
                $table->string('consulta_id', 100);
                $table->string('nombre', 200);
                $table->longText('configuracion_json');
                $table->integer('version_definicion_consulta');
                $table->foreignId('created_by_user_id')->constrained('users');
                $table->boolean('eliminado')->default(false);
                $table->boolean('activo')->default(true);
                $table->timestamps();
                $table->unique(['consulta_id', 'nombre'], 'UX_pq_pivots_config_consulta_nombre');
            });
        }

        if (! Schema::hasTable('pq_pivots_config_last_used')) {
            Schema::create('pq_pivots_config_last_used', function (Blueprint $table): void {
                $table->foreignId('user_id')->constrained('users');
                $table->string('consulta_id', 100);
                $table->unsignedBigInteger('pivot_id')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->primary(['user_id', 'consulta_id']);
                $table->foreign('pivot_id')->references('pivot_id')->on('pq_pivots_config')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_pivots_config_last_used');
        Schema::dropIfExists('pq_pivots_config');
    }
};
