/* ============================================================
   GEN-08 Pivots — DDL tablas SQL Server
   Ejecutar en la BD tenant (ej. paqsystems_pedidosweb_*)

   Requiere: tabla [users] existente (FK en pq_pivots_config)

   Después de este script:
     backend/scripts/sql/seed-pivot-catalog.sql

   Migraciones Laravel equivalentes:
     2026_06_11_100000_create_pq_pivots_catalog_tables
     2026_06_11_110000_create_pq_pivots_config_tables
   ============================================================ */

SET NOCOUNT ON;

/* --- Catálogo metadata --- */

IF OBJECT_ID(N'pq_pivots_validaciones', N'U') IS NULL
BEGIN
    CREATE TABLE [pq_pivots_validaciones] (
        [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        [consulta_id] NVARCHAR(100) NOT NULL,
        [tipo_validacion] NVARCHAR(100) NOT NULL,
        [configuracion_json] NVARCHAR(MAX) NOT NULL,
        [activo] BIT NOT NULL CONSTRAINT [DF_pq_pivots_validaciones_activo] DEFAULT (1)
    );
END;
GO

IF OBJECT_ID(N'pq_pivots_plantillas_det', N'U') IS NULL
BEGIN
    CREATE TABLE [pq_pivots_plantillas_det] (
        [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        [plantilla_id] NVARCHAR(100) NOT NULL,
        [propiedad] NVARCHAR(100) NOT NULL,
        [valor] NVARCHAR(MAX) NOT NULL
    );
END;
GO

IF OBJECT_ID(N'pq_pivots_campos', N'U') IS NULL
BEGIN
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
        [activo] BIT NOT NULL CONSTRAINT [DF_pq_pivots_campos_activo] DEFAULT (1),
        [orden] INT NOT NULL CONSTRAINT [DF_pq_pivots_campos_orden] DEFAULT (0)
    );

    CREATE UNIQUE INDEX [UX_pq_pivots_campos_consulta_campo]
        ON [pq_pivots_campos] ([consulta_id], [campo_id]);
END;
GO

IF OBJECT_ID(N'pq_pivots_plantillas', N'U') IS NULL
BEGIN
    CREATE TABLE [pq_pivots_plantillas] (
        [plantilla_id] NVARCHAR(100) NOT NULL PRIMARY KEY,
        [nombre] NVARCHAR(200) NOT NULL,
        [descripcion] NVARCHAR(500) NULL,
        [propiedades_json] NVARCHAR(MAX) NOT NULL,
        [activo] BIT NOT NULL CONSTRAINT [DF_pq_pivots_plantillas_activo] DEFAULT (1)
    );
END;
GO

IF OBJECT_ID(N'pq_pivots_consultas', N'U') IS NULL
BEGIN
    CREATE TABLE [pq_pivots_consultas] (
        [consulta_id] NVARCHAR(100) NOT NULL PRIMARY KEY,
        [nombre] NVARCHAR(200) NOT NULL,
        [descripcion] NVARCHAR(500) NULL,
        [fuente_tipo] NVARCHAR(50) NOT NULL,
        [fuente_nombre] NVARCHAR(200) NOT NULL,
        [procedimiento_host] NVARCHAR(128) NOT NULL,
        [version_definicion] INT NOT NULL,
        [pivot_habilitado] BIT NOT NULL CONSTRAINT [DF_pq_pivots_consultas_pivot_hab] DEFAULT (1),
        [admite_drilldown] BIT NOT NULL CONSTRAINT [DF_pq_pivots_consultas_drilldown] DEFAULT (0),
        [activo] BIT NOT NULL CONSTRAINT [DF_pq_pivots_consultas_activo] DEFAULT (1),
        [pivot_base_json] NVARCHAR(MAX) NOT NULL,
        [configuracion_general_json] NVARCHAR(MAX) NULL,
        [exportacion_json] NVARCHAR(MAX) NULL,
        [persistencia_json] NVARCHAR(MAX) NULL,
        [fecha_creacion] DATETIME2 NOT NULL CONSTRAINT [DF_pq_pivots_consultas_fecha] DEFAULT (SYSUTCDATETIME()),
        [usuario_creacion] NVARCHAR(100) NOT NULL CONSTRAINT [DF_pq_pivots_consultas_usuario] DEFAULT (N'system')
    );
END;
GO

/* FKs catálogo (idempotentes) */
IF NOT EXISTS (
    SELECT 1 FROM sys.foreign_keys WHERE name = N'FK_pq_pivots_campos_consulta'
)
    ALTER TABLE [pq_pivots_campos]
        ADD CONSTRAINT [FK_pq_pivots_campos_consulta]
        FOREIGN KEY ([consulta_id]) REFERENCES [pq_pivots_consultas]([consulta_id]);
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.foreign_keys WHERE name = N'FK_pq_pivots_campos_plantilla'
)
    ALTER TABLE [pq_pivots_campos]
        ADD CONSTRAINT [FK_pq_pivots_campos_plantilla]
        FOREIGN KEY ([plantilla_global_id]) REFERENCES [pq_pivots_plantillas]([plantilla_id]);
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.foreign_keys WHERE name = N'FK_pq_pivots_plantillas_det_plantilla'
)
    ALTER TABLE [pq_pivots_plantillas_det]
        ADD CONSTRAINT [FK_pq_pivots_plantillas_det_plantilla]
        FOREIGN KEY ([plantilla_id]) REFERENCES [pq_pivots_plantillas]([plantilla_id]);
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.foreign_keys WHERE name = N'FK_pq_pivots_validaciones_consulta'
)
    ALTER TABLE [pq_pivots_validaciones]
        ADD CONSTRAINT [FK_pq_pivots_validaciones_consulta]
        FOREIGN KEY ([consulta_id]) REFERENCES [pq_pivots_consultas]([consulta_id]);
GO

/* --- Diseños guardados --- */

IF OBJECT_ID(N'pq_pivots_config', N'U') IS NULL
BEGIN
    CREATE TABLE [pq_pivots_config] (
        [pivot_id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        [consulta_id] NVARCHAR(100) NOT NULL,
        [nombre] NVARCHAR(200) NOT NULL,
        [configuracion_json] NVARCHAR(MAX) NOT NULL,
        [version_definicion_consulta] INT NOT NULL,
        [created_by_user_id] BIGINT NOT NULL,
        [eliminado] BIT NOT NULL CONSTRAINT [DF_pq_pivots_config_eliminado] DEFAULT (0),
        [activo] BIT NOT NULL CONSTRAINT [DF_pq_pivots_config_activo] DEFAULT (1),
        [created_at] DATETIME2 NULL,
        [updated_at] DATETIME2 NULL,
        CONSTRAINT [FK_pq_pivots_config_user] FOREIGN KEY ([created_by_user_id]) REFERENCES [users]([id])
    );

    CREATE UNIQUE INDEX [UX_pq_pivots_config_consulta_nombre]
        ON [pq_pivots_config] ([consulta_id], [nombre])
        WHERE [eliminado] = 0;
END;
GO

IF OBJECT_ID(N'pq_pivots_config_last_used', N'U') IS NULL
BEGIN
    CREATE TABLE [pq_pivots_config_last_used] (
        [user_id] BIGINT NOT NULL,
        [consulta_id] NVARCHAR(100) NOT NULL,
        [pivot_id] BIGINT NULL,
        [updated_at] DATETIME2 NULL,
        CONSTRAINT [PK_pq_pivots_config_last_used] PRIMARY KEY ([user_id], [consulta_id]),
        CONSTRAINT [FK_pq_pivots_config_last_used_user] FOREIGN KEY ([user_id]) REFERENCES [users]([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.foreign_keys WHERE name = N'FK_pq_pivots_config_last_used_pivot'
)
    ALTER TABLE [pq_pivots_config_last_used]
        ADD CONSTRAINT [FK_pq_pivots_config_last_used_pivot]
        FOREIGN KEY ([pivot_id]) REFERENCES [pq_pivots_config]([pivot_id])
        ON DELETE SET NULL;
GO

/* Registrar migraciones (opcional, si usa Laravel migrate) */
INSERT INTO [migrations] ([migration], [batch])
SELECT v.[migration], ISNULL((SELECT MAX([batch]) FROM [migrations]), 0) + 1
FROM (VALUES
    (N'2026_06_11_100000_create_pq_pivots_catalog_tables'),
    (N'2026_06_11_110000_create_pq_pivots_config_tables')
) AS v([migration])
WHERE NOT EXISTS (SELECT 1 FROM [migrations] m WHERE m.[migration] = v.[migration]);

PRINT N'OK — tablas pivot creadas. Ejecutar seed-pivot-catalog.sql';
