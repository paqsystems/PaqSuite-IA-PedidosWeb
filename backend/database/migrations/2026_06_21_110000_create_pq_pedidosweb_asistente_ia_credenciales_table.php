<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Credenciales Chat Asistente IA — TR-GEN-10-configuracion-asistente-ia.
 */
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
        if (Schema::hasTable('pq_asistente_ia_credenciales')) {
            return;
        }

        DB::statement(<<<'SQL'
CREATE TABLE [pq_asistente_ia_credenciales] (
    [id_credencial] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [user_id] BIGINT NOT NULL,
    [provider_id] NVARCHAR(50) NOT NULL,
    [base_url] NVARCHAR(255) NULL,
    [api_key_encrypted] NVARCHAR(MAX) NOT NULL,
    [model_id] NVARCHAR(120) NOT NULL,
    [supports_vision] BIT NOT NULL DEFAULT 0,
    [is_enabled] BIT NOT NULL DEFAULT 1,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL
);
SQL);
        DB::statement(<<<'SQL'
CREATE UNIQUE INDEX [UX_pq_asistente_ia_credenciales_user_id]
    ON [pq_asistente_ia_credenciales] ([user_id]);
SQL);
    }

    private function upGeneric(): void
    {
        if (Schema::hasTable('pq_asistente_ia_credenciales')) {
            return;
        }

        Schema::create('pq_asistente_ia_credenciales', function (Blueprint $table): void {
            $table->id('id_credencial');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('provider_id', 50);
            $table->string('base_url', 255)->nullable();
            $table->longText('api_key_encrypted');
            $table->string('model_id', 120);
            $table->boolean('supports_vision')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_asistente_ia_credenciales');
    }
};
