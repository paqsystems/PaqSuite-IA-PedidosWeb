<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo pivots — BD tenant PedidosWeb (desviación MONO Dictionary DB, ver TR-GEN-08 R-C1-01).
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
        if (! Schema::hasTable('pq_pivots_consultas')) {
            DB::statement(<<<'SQL'
CREATE TABLE [pq_pivots_consultas] (
    [consulta_id] NVARCHAR(100) NOT NULL PRIMARY KEY,
    [nombre] NVARCHAR(200) NOT NULL,
    [descripcion] NVARCHAR(500) NULL,
    [fuente_tipo] NVARCHAR(50) NOT NULL,
    [fuente_nombre] NVARCHAR(200) NOT NULL,
    [procedimiento_host] NVARCHAR(128) NOT NULL,
    [version_definicion] INT NOT NULL,
    [pivot_habilitado] BIT NOT NULL DEFAULT 1,
    [admite_drilldown] BIT NOT NULL DEFAULT 0,
    [activo] BIT NOT NULL DEFAULT 1,
    [pivot_base_json] NVARCHAR(MAX) NOT NULL,
    [configuracion_general_json] NVARCHAR(MAX) NULL,
    [exportacion_json] NVARCHAR(MAX) NULL,
    [persistencia_json] NVARCHAR(MAX) NULL,
    [fecha_creacion] DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    [usuario_creacion] NVARCHAR(100) NOT NULL DEFAULT 'system'
);
SQL);
        }

        if (! Schema::hasTable('pq_pivots_plantillas')) {
            DB::statement(<<<'SQL'
CREATE TABLE [pq_pivots_plantillas] (
    [plantilla_id] NVARCHAR(100) NOT NULL PRIMARY KEY,
    [nombre] NVARCHAR(200) NOT NULL,
    [descripcion] NVARCHAR(500) NULL,
    [propiedades_json] NVARCHAR(MAX) NOT NULL,
    [activo] BIT NOT NULL DEFAULT 1
);
SQL);
        }

        if (! Schema::hasTable('pq_pivots_campos')) {
            DB::statement(<<<'SQL'
CREATE TABLE [pq_pivots_campos] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [consulta_id] NVARCHAR(100) NOT NULL,
    [campo_id] NVARCHAR(100) NOT NULL,
    [nombre_tecnico] NVARCHAR(200) NOT NULL,
    [nombre_visible] NVARCHAR(200) NOT NULL,
    [tipo_dato] NVARCHAR(50) NOT NULL,
    [rol_campo] NVARCHAR(50) NOT NULL,
    [roles_permitidos_json] NVARCHAR(MAX) NOT NULL,
    [agregacion_default] NVARCHAR(50) NULL,
    [agregaciones_permitidas_json] NVARCHAR(MAX) NULL,
    [formato_json] NVARCHAR(MAX) NULL,
    [plantilla_global_id] NVARCHAR(100) NULL,
    [override_json] NVARCHAR(MAX) NULL,
    [activo] BIT NOT NULL DEFAULT 1,
    [orden] INT NOT NULL DEFAULT 0,
    CONSTRAINT [FK_pq_pivots_campos_consulta] FOREIGN KEY ([consulta_id]) REFERENCES [pq_pivots_consultas]([consulta_id]),
    CONSTRAINT [FK_pq_pivots_campos_plantilla] FOREIGN KEY ([plantilla_global_id]) REFERENCES [pq_pivots_plantillas]([plantilla_id])
);
SQL);
            DB::statement(<<<'SQL'
CREATE UNIQUE INDEX [UX_pq_pivots_campos_consulta_campo]
    ON [pq_pivots_campos] ([consulta_id], [campo_id]);
SQL);
        }

        if (! Schema::hasTable('pq_pivots_plantillas_det')) {
            DB::statement(<<<'SQL'
CREATE TABLE [pq_pivots_plantillas_det] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [plantilla_id] NVARCHAR(100) NOT NULL,
    [propiedad] NVARCHAR(100) NOT NULL,
    [valor] NVARCHAR(MAX) NOT NULL,
    CONSTRAINT [FK_pq_pivots_plantillas_det_plantilla] FOREIGN KEY ([plantilla_id]) REFERENCES [pq_pivots_plantillas]([plantilla_id])
);
SQL);
        }

        if (! Schema::hasTable('pq_pivots_validaciones')) {
            DB::statement(<<<'SQL'
CREATE TABLE [pq_pivots_validaciones] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [consulta_id] NVARCHAR(100) NOT NULL,
    [tipo_validacion] NVARCHAR(100) NOT NULL,
    [configuracion_json] NVARCHAR(MAX) NOT NULL,
    [activo] BIT NOT NULL DEFAULT 1,
    CONSTRAINT [FK_pq_pivots_validaciones_consulta] FOREIGN KEY ([consulta_id]) REFERENCES [pq_pivots_consultas]([consulta_id])
);
SQL);
        }
    }

    private function upGeneric(): void
    {
        if (! Schema::hasTable('pq_pivots_consultas')) {
            Schema::create('pq_pivots_consultas', function (Blueprint $table): void {
                $table->string('consulta_id', 100)->primary();
                $table->string('nombre', 200);
                $table->string('descripcion', 500)->nullable();
                $table->string('fuente_tipo', 50);
                $table->string('fuente_nombre', 200);
                $table->string('procedimiento_host', 128);
                $table->integer('version_definicion');
                $table->boolean('pivot_habilitado')->default(true);
                $table->boolean('admite_drilldown')->default(false);
                $table->boolean('activo')->default(true);
                $table->longText('pivot_base_json');
                $table->longText('configuracion_general_json')->nullable();
                $table->longText('exportacion_json')->nullable();
                $table->longText('persistencia_json')->nullable();
                $table->timestamp('fecha_creacion')->useCurrent();
                $table->string('usuario_creacion', 100)->default('system');
            });
        }

        if (! Schema::hasTable('pq_pivots_plantillas')) {
            Schema::create('pq_pivots_plantillas', function (Blueprint $table): void {
                $table->string('plantilla_id', 100)->primary();
                $table->string('nombre', 200);
                $table->string('descripcion', 500)->nullable();
                $table->longText('propiedades_json');
                $table->boolean('activo')->default(true);
            });
        }

        if (! Schema::hasTable('pq_pivots_campos')) {
            Schema::create('pq_pivots_campos', function (Blueprint $table): void {
                $table->id();
                $table->string('consulta_id', 100);
                $table->string('campo_id', 100);
                $table->string('nombre_tecnico', 200);
                $table->string('nombre_visible', 200);
                $table->string('tipo_dato', 50);
                $table->string('rol_campo', 50);
                $table->longText('roles_permitidos_json');
                $table->string('agregacion_default', 50)->nullable();
                $table->longText('agregaciones_permitidas_json')->nullable();
                $table->longText('formato_json')->nullable();
                $table->string('plantilla_global_id', 100)->nullable();
                $table->longText('override_json')->nullable();
                $table->boolean('activo')->default(true);
                $table->integer('orden')->default(0);
                $table->foreign('consulta_id')->references('consulta_id')->on('pq_pivots_consultas');
                $table->foreign('plantilla_global_id')->references('plantilla_id')->on('pq_pivots_plantillas');
                $table->unique(['consulta_id', 'campo_id']);
            });
        }

        if (! Schema::hasTable('pq_pivots_plantillas_det')) {
            Schema::create('pq_pivots_plantillas_det', function (Blueprint $table): void {
                $table->id();
                $table->string('plantilla_id', 100);
                $table->string('propiedad', 100);
                $table->longText('valor');
                $table->foreign('plantilla_id')->references('plantilla_id')->on('pq_pivots_plantillas');
            });
        }

        if (! Schema::hasTable('pq_pivots_validaciones')) {
            Schema::create('pq_pivots_validaciones', function (Blueprint $table): void {
                $table->id();
                $table->string('consulta_id', 100);
                $table->string('tipo_validacion', 100);
                $table->longText('configuracion_json');
                $table->boolean('activo')->default(true);
                $table->foreign('consulta_id')->references('consulta_id')->on('pq_pivots_consultas');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_pivots_validaciones');
        Schema::dropIfExists('pq_pivots_plantillas_det');
        Schema::dropIfExists('pq_pivots_campos');
        Schema::dropIfExists('pq_pivots_plantillas');
        Schema::dropIfExists('pq_pivots_consultas');
    }
};
