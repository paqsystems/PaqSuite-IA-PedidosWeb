-- Idempotente: ampliar descripcion en pq_pedidosweb_articulos (VARCHAR(50) → VARCHAR(60)).
-- Ejecutar en tenant ERP / SQL Server cuando no se use migrate Laravel.

IF COL_LENGTH('pq_pedidosweb_articulos', 'descripcion') IS NOT NULL
BEGIN
    ALTER TABLE [pq_pedidosweb_articulos]
        ALTER COLUMN [descripcion] VARCHAR(60) NULL;
END
GO
