/*
  PedidosWeb — columnas de detalle requeridas por el portal web
  Tabla: pq_pedidosweb_pedidosdetalle

  Idempotente: solo agrega columnas si no existen.
  Ejecutar en cada base cliente (Ankas + demás tenants).

  Referencia: docs/02-producto/PedidosWeb/PedidosWeb_Modelo_Datos_Final.md §2.2
              backend/app/Services/Seed/PedidosWebDevSchemaBootstrap.php
*/

SET NOCOUNT ON;

IF OBJECT_ID(N'dbo.pq_pedidosweb_pedidosdetalle', N'U') IS NULL
BEGIN
    RAISERROR('La tabla dbo.pq_pedidosweb_pedidosdetalle no existe en esta base.', 16, 1);
    RETURN;
END;

IF COL_LENGTH('dbo.pq_pedidosweb_pedidosdetalle', 'descripcion_articulo') IS NULL
    ALTER TABLE dbo.pq_pedidosweb_pedidosdetalle
        ADD descripcion_articulo nvarchar(100) NULL;

IF COL_LENGTH('dbo.pq_pedidosweb_pedidosdetalle', 'importe_lista') IS NULL
    ALTER TABLE dbo.pq_pedidosweb_pedidosdetalle
        ADD importe_lista decimal(18, 2) NULL;

IF COL_LENGTH('dbo.pq_pedidosweb_pedidosdetalle', 'importe_neto') IS NULL
    ALTER TABLE dbo.pq_pedidosweb_pedidosdetalle
        ADD importe_neto decimal(18, 2) NULL;

IF COL_LENGTH('dbo.pq_pedidosweb_pedidosdetalle', 'importe_total') IS NULL
    ALTER TABLE dbo.pq_pedidosweb_pedidosdetalle
        ADD importe_total decimal(18, 2) NULL;

IF COL_LENGTH('dbo.pq_pedidosweb_pedidosdetalle', 'descuento_origen') IS NULL
    ALTER TABLE dbo.pq_pedidosweb_pedidosdetalle
        ADD descuento_origen nvarchar(20) NULL;

IF COL_LENGTH('dbo.pq_pedidosweb_pedidosdetalle', 'precio_origen') IS NULL
    ALTER TABLE dbo.pq_pedidosweb_pedidosdetalle
        ADD precio_origen nvarchar(20) NULL;

IF COL_LENGTH('dbo.pq_pedidosweb_pedidosdetalle', 'bonificacion') IS NULL
    ALTER TABLE dbo.pq_pedidosweb_pedidosdetalle
        ADD bonificacion decimal(6, 2) NULL;

PRINT 'OK: columnas portal en pq_pedidosweb_pedidosdetalle verificadas.';
