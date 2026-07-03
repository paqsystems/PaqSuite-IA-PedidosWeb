-- Renombrar tablas Chat Asistente IA transversales PaqSuite (quitar prefijo pedidosweb_).
-- Ejecutar en SQL Server cuando migrate Laravel no esté disponible.
-- Idempotente: solo renombra si existen las tablas legacy.

IF OBJECT_ID(N'pq_pedidosweb_asistente_ia_proveedores', N'U') IS NOT NULL
   AND OBJECT_ID(N'pq_asistente_ia_proveedores', N'U') IS NULL
    EXEC sp_rename N'pq_pedidosweb_asistente_ia_proveedores', N'pq_asistente_ia_proveedores';
GO

IF OBJECT_ID(N'pq_pedidosweb_asistente_ia_credenciales', N'U') IS NOT NULL
   AND OBJECT_ID(N'pq_asistente_ia_credenciales', N'U') IS NULL
    EXEC sp_rename N'pq_pedidosweb_asistente_ia_credenciales', N'pq_asistente_ia_credenciales';
GO

IF EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'UX_pq_pedidosweb_asistente_ia_proveedores_provider_id'
      AND object_id = OBJECT_ID(N'pq_asistente_ia_proveedores')
)
    EXEC sp_rename N'pq_asistente_ia_proveedores.UX_pq_pedidosweb_asistente_ia_proveedores_provider_id',
        N'UX_pq_asistente_ia_proveedores_provider_id', N'INDEX';
GO

IF EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'UX_pq_pedidosweb_asistente_ia_credenciales_user_id'
      AND object_id = OBJECT_ID(N'pq_asistente_ia_credenciales')
)
    EXEC sp_rename N'pq_asistente_ia_credenciales.UX_pq_pedidosweb_asistente_ia_credenciales_user_id',
        N'UX_pq_asistente_ia_credenciales_user_id', N'INDEX';
GO

IF EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'IX_pq_pedidosweb_asistente_ia_credenciales_user_id'
      AND object_id = OBJECT_ID(N'pq_asistente_ia_credenciales')
)
    EXEC sp_rename N'pq_asistente_ia_credenciales.IX_pq_pedidosweb_asistente_ia_credenciales_user_id',
        N'IX_pq_asistente_ia_credenciales_user_id', N'INDEX';
GO

IF EXISTS (
    SELECT 1 FROM sys.default_constraints
    WHERE name = N'DF_pq_pedidosweb_asistente_ia_credenciales_display_name'
      AND parent_object_id = OBJECT_ID(N'pq_asistente_ia_credenciales')
)
    EXEC sp_rename N'DF_pq_pedidosweb_asistente_ia_credenciales_display_name',
        N'DF_pq_asistente_ia_credenciales_display_name', N'OBJECT';
GO
