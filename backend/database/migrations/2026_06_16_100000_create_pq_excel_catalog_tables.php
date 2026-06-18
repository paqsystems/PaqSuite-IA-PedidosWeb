<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo importación Excel — TR-GEN-07-plantilla-excel (columnas snake_case en Laravel).
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
        if (! Schema::hasTable('pq_excel_procesos')) {
            DB::statement(<<<'SQL'
CREATE TABLE [pq_excel_procesos] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [codigo_proceso] VARCHAR(50) NOT NULL,
    [nombre_proceso] VARCHAR(150) NOT NULL,
    [descripcion] VARCHAR(500) NULL,
    [nombre_hoja_default] VARCHAR(100) NULL,
    [permite_procesamiento_parcial] BIT NOT NULL CONSTRAINT [DF_pq_excel_procesos_parcial] DEFAULT (0),
    [permite_solo_validar] BIT NOT NULL CONSTRAINT [DF_pq_excel_procesos_solo_validar] DEFAULT (1),
    [genera_plantilla] BIT NOT NULL CONSTRAINT [DF_pq_excel_procesos_genera_plantilla] DEFAULT (1),
    [mantener_espacios_en_blanco_default] BIT NOT NULL CONSTRAINT [DF_pq_excel_procesos_espacios] DEFAULT (0),
    [mantener_caracteres_especiales_default] BIT NOT NULL CONSTRAINT [DF_pq_excel_procesos_caracteres] DEFAULT (0),
    [handler_backend] VARCHAR(200) NULL,
    [procedimiento_host] VARCHAR(100) NOT NULL CONSTRAINT [DF_pq_excel_procesos_proc_host] DEFAULT (''),
    [formato_booleano_plantilla] VARCHAR(20) NOT NULL CONSTRAINT [DF_pq_excel_procesos_formato_bool] DEFAULT ('0_1'),
    [activo] BIT NOT NULL CONSTRAINT [DF_pq_excel_procesos_activo] DEFAULT (1),
    [fecha_alta] DATETIME2(0) NOT NULL CONSTRAINT [DF_pq_excel_procesos_fecha_alta] DEFAULT (SYSDATETIME()),
    [usuario_alta] VARCHAR(100) NOT NULL CONSTRAINT [DF_pq_excel_procesos_usuario_alta] DEFAULT ('system'),
    CONSTRAINT [UQ_pq_excel_procesos_codigo] UNIQUE ([codigo_proceso])
);
SQL);
        }

        if (! Schema::hasTable('pq_excel_procesos_campos')) {
            DB::statement(<<<'SQL'
CREATE TABLE [pq_excel_procesos_campos] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [id_proceso] BIGINT NOT NULL,
    [orden_campo] INT NOT NULL,
    [nombre_columna_excel] VARCHAR(150) NOT NULL,
    [nombre_campo_interno] VARCHAR(100) NOT NULL,
    [tipo_dato] VARCHAR(30) NOT NULL,
    [largo_maximo] INT NULL,
    [cantidad_decimales] INT NULL,
    [es_columna_obligatoria_estructural] BIT NOT NULL CONSTRAINT [DF_pq_excel_campos_oblig] DEFAULT (0),
    [es_campo_codigo] BIT NOT NULL CONSTRAINT [DF_pq_excel_campos_codigo] DEFAULT (0),
    [activo] BIT NOT NULL CONSTRAINT [DF_pq_excel_campos_activo] DEFAULT (1),
    [observaciones] VARCHAR(500) NULL,
    CONSTRAINT [FK_pq_excel_campos_proceso] FOREIGN KEY ([id_proceso]) REFERENCES [pq_excel_procesos]([id]),
    CONSTRAINT [UQ_pq_excel_campos_proceso_excel] UNIQUE ([id_proceso], [nombre_columna_excel]),
    CONSTRAINT [UQ_pq_excel_campos_proceso_interno] UNIQUE ([id_proceso], [nombre_campo_interno])
);
SQL);
            DB::statement('CREATE INDEX [IX_pq_excel_campos_proceso_orden] ON [pq_excel_procesos_campos] ([id_proceso], [orden_campo]);');
        }
    }

    private function upGeneric(): void
    {
        if (! Schema::hasTable('pq_excel_procesos')) {
            Schema::create('pq_excel_procesos', function (Blueprint $table): void {
                $table->id();
                $table->string('codigo_proceso', 50)->unique();
                $table->string('nombre_proceso', 150);
                $table->string('descripcion', 500)->nullable();
                $table->string('nombre_hoja_default', 100)->nullable();
                $table->boolean('permite_procesamiento_parcial')->default(false);
                $table->boolean('permite_solo_validar')->default(true);
                $table->boolean('genera_plantilla')->default(true);
                $table->boolean('mantener_espacios_en_blanco_default')->default(false);
                $table->boolean('mantener_caracteres_especiales_default')->default(false);
                $table->string('handler_backend', 200)->nullable();
                $table->string('procedimiento_host', 100)->default('');
                $table->string('formato_booleano_plantilla', 20)->default('0_1');
                $table->boolean('activo')->default(true);
                $table->timestamp('fecha_alta')->useCurrent();
                $table->string('usuario_alta', 100)->default('system');
            });
        }

        if (! Schema::hasTable('pq_excel_procesos_campos')) {
            Schema::create('pq_excel_procesos_campos', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('id_proceso')->constrained('pq_excel_procesos');
                $table->integer('orden_campo');
                $table->string('nombre_columna_excel', 150);
                $table->string('nombre_campo_interno', 100);
                $table->string('tipo_dato', 30);
                $table->integer('largo_maximo')->nullable();
                $table->integer('cantidad_decimales')->nullable();
                $table->boolean('es_columna_obligatoria_estructural')->default(false);
                $table->boolean('es_campo_codigo')->default(false);
                $table->boolean('activo')->default(true);
                $table->string('observaciones', 500)->nullable();
                $table->unique(['id_proceso', 'nombre_columna_excel']);
                $table->unique(['id_proceso', 'nombre_campo_interno']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_excel_procesos_campos');
        Schema::dropIfExists('pq_excel_procesos');
    }
};
