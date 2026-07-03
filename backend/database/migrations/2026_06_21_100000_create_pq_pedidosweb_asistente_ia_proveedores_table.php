<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo proveedores Chat Asistente IA — TR-GEN-10-catalogo-proveedores-ia.
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
        if (Schema::hasTable('pq_asistente_ia_proveedores')) {
            return;
        }

        DB::statement(<<<'SQL'
CREATE TABLE [pq_asistente_ia_proveedores] (
    [id_proveedor] INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [provider_id] NVARCHAR(50) NOT NULL,
    [nombre_visible] NVARCHAR(80) NOT NULL,
    [tipo_integracion] NVARCHAR(50) NOT NULL,
    [soporta_byok] BIT NOT NULL DEFAULT 1,
    [soporta_imagenes] BIT NOT NULL DEFAULT 0,
    [requiere_base_url_editable] BIT NOT NULL DEFAULT 0,
    [url_documentacion] NVARCHAR(255) NULL,
    [url_onboarding] NVARCHAR(255) NULL,
    [activo] BIT NOT NULL DEFAULT 1,
    [observacion] NVARCHAR(255) NULL
);
SQL);
        DB::statement(<<<'SQL'
CREATE UNIQUE INDEX [UX_pq_asistente_ia_proveedores_provider_id]
    ON [pq_asistente_ia_proveedores] ([provider_id]);
SQL);
    }

    private function upGeneric(): void
    {
        if (Schema::hasTable('pq_asistente_ia_proveedores')) {
            return;
        }

        Schema::create('pq_asistente_ia_proveedores', function (Blueprint $table): void {
            $table->id('id_proveedor');
            $table->string('provider_id', 50)->unique();
            $table->string('nombre_visible', 80);
            $table->string('tipo_integracion', 50);
            $table->boolean('soporta_byok')->default(true);
            $table->boolean('soporta_imagenes')->default(false);
            $table->boolean('requiere_base_url_editable')->default(false);
            $table->string('url_documentacion', 255)->nullable();
            $table->string('url_onboarding', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->string('observacion', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_asistente_ia_proveedores');
    }
};
