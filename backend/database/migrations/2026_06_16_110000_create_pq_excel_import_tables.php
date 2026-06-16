<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lotes, staging y notificaciones — TR-GEN-07-carga-staging-excel.
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
        if (! Schema::hasTable('pq_excel_importaciones')) {
            DB::statement(<<<'SQL'
CREATE TABLE [pq_excel_importaciones] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [guid_importacion] UNIQUEIDENTIFIER NOT NULL CONSTRAINT [DF_pq_excel_imp_guid] DEFAULT (NEWID()),
    [id_proceso] BIGINT NOT NULL,
    [usuario_ejecucion] VARCHAR(100) NOT NULL,
    [terminal_ejecucion] VARCHAR(100) NULL,
    [archivo_original_nombre] VARCHAR(260) NOT NULL,
    [archivo_original_extension] VARCHAR(10) NOT NULL,
    [hoja_seleccionada] VARCHAR(150) NOT NULL,
    [mantener_espacios_en_blanco] BIT NOT NULL CONSTRAINT [DF_pq_excel_imp_espacios] DEFAULT (0),
    [mantener_caracteres_especiales] BIT NOT NULL CONSTRAINT [DF_pq_excel_imp_caracteres] DEFAULT (0),
    [estado_importacion] VARCHAR(30) NOT NULL,
    [es_asincronica] BIT NOT NULL CONSTRAINT [DF_pq_excel_imp_async] DEFAULT (0),
    [fecha_inicio] DATETIME2(0) NOT NULL CONSTRAINT [DF_pq_excel_imp_fecha_ini] DEFAULT (SYSDATETIME()),
    [fecha_fin] DATETIME2(0) NULL,
    [cantidad_filas_leidas] INT NOT NULL CONSTRAINT [DF_pq_excel_imp_leidas] DEFAULT (0),
    [cantidad_filas_descartadas] INT NOT NULL CONSTRAINT [DF_pq_excel_imp_descartadas] DEFAULT (0),
    [cantidad_filas_validas] INT NOT NULL CONSTRAINT [DF_pq_excel_imp_validas] DEFAULT (0),
    [cantidad_filas_con_error] INT NOT NULL CONSTRAINT [DF_pq_excel_imp_errores] DEFAULT (0),
    [cantidad_filas_procesadas] INT NOT NULL CONSTRAINT [DF_pq_excel_imp_procesadas] DEFAULT (0),
    [mensaje_resultado] VARCHAR(1000) NULL,
    [puede_cancelar] BIT NOT NULL CONSTRAINT [DF_pq_excel_imp_cancelar] DEFAULT (1),
    CONSTRAINT [UQ_pq_excel_imp_guid] UNIQUE ([guid_importacion]),
    CONSTRAINT [FK_pq_excel_imp_proceso] FOREIGN KEY ([id_proceso]) REFERENCES [pq_excel_procesos]([id])
);
SQL);
            DB::statement('CREATE INDEX [IX_pq_excel_imp_proceso_fecha] ON [pq_excel_importaciones] ([id_proceso], [fecha_inicio] DESC);');
            DB::statement('CREATE INDEX [IX_pq_excel_imp_usuario_fecha] ON [pq_excel_importaciones] ([usuario_ejecucion], [fecha_inicio] DESC);');
        }

        if (! Schema::hasTable('pq_excel_importaciones_filas')) {
            DB::statement(<<<'SQL'
CREATE TABLE [pq_excel_importaciones_filas] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [id_importacion] BIGINT NOT NULL,
    [numero_fila_excel] INT NOT NULL,
    [estado_fila] VARCHAR(20) NOT NULL,
    [fila_ajustada_automaticamente] BIT NOT NULL CONSTRAINT [DF_pq_excel_filas_ajustada] DEFAULT (0),
    [tiene_error] BIT NOT NULL CONSTRAINT [DF_pq_excel_filas_error] DEFAULT (0),
    [error_importacion] VARCHAR(MAX) NULL,
    [datos_originales_json] NVARCHAR(MAX) NULL,
    [datos_normalizados_json] NVARCHAR(MAX) NULL,
    [fecha_alta] DATETIME2(0) NOT NULL CONSTRAINT [DF_pq_excel_filas_fecha] DEFAULT (SYSDATETIME()),
    CONSTRAINT [FK_pq_excel_filas_importacion] FOREIGN KEY ([id_importacion]) REFERENCES [pq_excel_importaciones]([id]),
    CONSTRAINT [UQ_pq_excel_filas_imp_fila] UNIQUE ([id_importacion], [numero_fila_excel])
);
SQL);
            DB::statement('CREATE INDEX [IX_pq_excel_filas_importacion] ON [pq_excel_importaciones_filas] ([id_importacion], [numero_fila_excel]);');
        }

        if (! Schema::hasTable('pq_excel_importaciones_filas_errores')) {
            DB::statement(<<<'SQL'
CREATE TABLE [pq_excel_importaciones_filas_errores] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [id_importacion_fila] BIGINT NOT NULL,
    [secuencia_error] INT NOT NULL,
    [codigo_error] VARCHAR(50) NULL,
    [tipo_error] VARCHAR(20) NOT NULL,
    [nombre_campo_interno] VARCHAR(100) NULL,
    [nombre_columna_excel] VARCHAR(150) NULL,
    [mensaje_error] VARCHAR(1000) NOT NULL,
    CONSTRAINT [FK_pq_excel_filas_err_fila] FOREIGN KEY ([id_importacion_fila]) REFERENCES [pq_excel_importaciones_filas]([id]),
    CONSTRAINT [UQ_pq_excel_filas_err_seq] UNIQUE ([id_importacion_fila], [secuencia_error])
);
SQL);
        }

        if (! Schema::hasTable('pq_excel_importaciones_notificaciones')) {
            DB::statement(<<<'SQL'
CREATE TABLE [pq_excel_importaciones_notificaciones] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [id_importacion] BIGINT NOT NULL,
    [usuario_destino] VARCHAR(100) NOT NULL,
    [tipo_notificacion] VARCHAR(30) NOT NULL,
    [fecha_generacion] DATETIME2(0) NOT NULL CONSTRAINT [DF_pq_excel_notif_fecha] DEFAULT (SYSDATETIME()),
    [fecha_leida] DATETIME2(0) NULL,
    [titulo] VARCHAR(200) NOT NULL,
    [mensaje] VARCHAR(1000) NOT NULL,
    [leida] BIT NOT NULL CONSTRAINT [DF_pq_excel_notif_leida] DEFAULT (0),
    CONSTRAINT [FK_pq_excel_notif_importacion] FOREIGN KEY ([id_importacion]) REFERENCES [pq_excel_importaciones]([id])
);
SQL);
            DB::statement('CREATE INDEX [IX_pq_excel_notif_usuario] ON [pq_excel_importaciones_notificaciones] ([usuario_destino], [leida], [fecha_generacion] DESC);');
        }
    }

    private function upGeneric(): void
    {
        if (! Schema::hasTable('pq_excel_importaciones')) {
            Schema::create('pq_excel_importaciones', function (Blueprint $table): void {
                $table->id();
                $table->uuid('guid_importacion')->unique();
                $table->foreignId('id_proceso')->constrained('pq_excel_procesos');
                $table->string('usuario_ejecucion', 100);
                $table->string('terminal_ejecucion', 100)->nullable();
                $table->string('archivo_original_nombre', 260);
                $table->string('archivo_original_extension', 10);
                $table->string('hoja_seleccionada', 150);
                $table->boolean('mantener_espacios_en_blanco')->default(false);
                $table->boolean('mantener_caracteres_especiales')->default(false);
                $table->string('estado_importacion', 30);
                $table->boolean('es_asincronica')->default(false);
                $table->timestamp('fecha_inicio')->useCurrent();
                $table->timestamp('fecha_fin')->nullable();
                $table->integer('cantidad_filas_leidas')->default(0);
                $table->integer('cantidad_filas_descartadas')->default(0);
                $table->integer('cantidad_filas_validas')->default(0);
                $table->integer('cantidad_filas_con_error')->default(0);
                $table->integer('cantidad_filas_procesadas')->default(0);
                $table->string('mensaje_resultado', 1000)->nullable();
                $table->boolean('puede_cancelar')->default(true);
                $table->index(['id_proceso', 'fecha_inicio']);
                $table->index(['usuario_ejecucion', 'fecha_inicio']);
            });
        }

        if (! Schema::hasTable('pq_excel_importaciones_filas')) {
            Schema::create('pq_excel_importaciones_filas', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('id_importacion')->constrained('pq_excel_importaciones');
                $table->integer('numero_fila_excel');
                $table->string('estado_fila', 20);
                $table->boolean('fila_ajustada_automaticamente')->default(false);
                $table->boolean('tiene_error')->default(false);
                $table->text('error_importacion')->nullable();
                $table->text('datos_originales_json')->nullable();
                $table->text('datos_normalizados_json')->nullable();
                $table->timestamp('fecha_alta')->useCurrent();
                $table->unique(['id_importacion', 'numero_fila_excel']);
            });
        }

        if (! Schema::hasTable('pq_excel_importaciones_filas_errores')) {
            Schema::create('pq_excel_importaciones_filas_errores', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('id_importacion_fila')->constrained('pq_excel_importaciones_filas');
                $table->integer('secuencia_error');
                $table->string('codigo_error', 50)->nullable();
                $table->string('tipo_error', 20);
                $table->string('nombre_campo_interno', 100)->nullable();
                $table->string('nombre_columna_excel', 150)->nullable();
                $table->string('mensaje_error', 1000);
                $table->unique(['id_importacion_fila', 'secuencia_error']);
            });
        }

        if (! Schema::hasTable('pq_excel_importaciones_notificaciones')) {
            Schema::create('pq_excel_importaciones_notificaciones', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('id_importacion')->constrained('pq_excel_importaciones');
                $table->string('usuario_destino', 100);
                $table->string('tipo_notificacion', 30);
                $table->timestamp('fecha_generacion')->useCurrent();
                $table->timestamp('fecha_leida')->nullable();
                $table->string('titulo', 200);
                $table->string('mensaje', 1000);
                $table->boolean('leida')->default(false);
                $table->index(['usuario_destino', 'leida', 'fecha_generacion']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_excel_importaciones_notificaciones');
        Schema::dropIfExists('pq_excel_importaciones_filas_errores');
        Schema::dropIfExists('pq_excel_importaciones_filas');
        Schema::dropIfExists('pq_excel_importaciones');
    }
};
