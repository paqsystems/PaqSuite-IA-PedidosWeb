/* ============================================================
   GEN-07 Excel import — DDL tablas SQL Server
   Ejecutar en la BD tenant (ej. paqsystems_pedidosweb_*)

   Después de este script:
     backend/scripts/sql/seed-excel-catalog-pedidosweb.sql

   Migraciones Laravel equivalentes:
     2026_06_16_100000_create_pq_excel_catalog_tables
     2026_06_16_110000_create_pq_excel_import_tables
   ============================================================ */

SET NOCOUNT ON;

IF OBJECT_ID(N'pq_excel_procesos', N'U') IS NULL
BEGIN
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
END;
GO

IF OBJECT_ID(N'pq_excel_procesos_campos', N'U') IS NULL
BEGIN
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

    CREATE INDEX [IX_pq_excel_campos_proceso_orden]
        ON [pq_excel_procesos_campos] ([id_proceso], [orden_campo]);
END;
GO

IF OBJECT_ID(N'pq_excel_importaciones', N'U') IS NULL
BEGIN
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

    CREATE INDEX [IX_pq_excel_imp_proceso_fecha]
        ON [pq_excel_importaciones] ([id_proceso], [fecha_inicio] DESC);
    CREATE INDEX [IX_pq_excel_imp_usuario_fecha]
        ON [pq_excel_importaciones] ([usuario_ejecucion], [fecha_inicio] DESC);
END;
GO

IF OBJECT_ID(N'pq_excel_importaciones_filas', N'U') IS NULL
BEGIN
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

    CREATE INDEX [IX_pq_excel_filas_importacion]
        ON [pq_excel_importaciones_filas] ([id_importacion], [numero_fila_excel]);
END;
GO

IF OBJECT_ID(N'pq_excel_importaciones_filas_errores', N'U') IS NULL
BEGIN
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
END;
GO

IF OBJECT_ID(N'pq_excel_importaciones_notificaciones', N'U') IS NULL
BEGIN
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

    CREATE INDEX [IX_pq_excel_notif_usuario]
        ON [pq_excel_importaciones_notificaciones] ([usuario_destino], [leida], [fecha_generacion] DESC);
END;
GO

INSERT INTO [migrations] ([migration], [batch])
SELECT v.[migration], ISNULL((SELECT MAX([batch]) FROM [migrations]), 0) + 1
FROM (VALUES
    (N'2026_06_16_100000_create_pq_excel_catalog_tables'),
    (N'2026_06_16_110000_create_pq_excel_import_tables')
) AS v([migration])
WHERE NOT EXISTS (SELECT 1 FROM [migrations] m WHERE m.[migration] = v.[migration]);

PRINT N'OK — tablas Excel creadas. Ejecutar seed-excel-catalog-pedidosweb.sql';
