/*
  PedidosWeb — columna canónica bonificacion en detalle
  Tabla: pq_pedidosweb_pedidosdetalle

  Idempotente. Si existe porc_bonif (legado portal), copia valores una vez.

  Referencia: docs/02-producto/PedidosWeb/PedidosWeb_Modelo_Datos_Final.md §2.2
*/

SET NOCOUNT ON;

IF OBJECT_ID(N'dbo.pq_pedidosweb_pedidosdetalle', N'U') IS NULL
BEGIN
    RAISERROR('La tabla dbo.pq_pedidosweb_pedidosdetalle no existe en esta base.', 16, 1);
    RETURN;
END;

IF COL_LENGTH('dbo.pq_pedidosweb_pedidosdetalle', 'bonificacion') IS NULL
    ALTER TABLE dbo.pq_pedidosweb_pedidosdetalle
        ADD bonificacion decimal(6, 2) NULL;

IF COL_LENGTH('dbo.pq_pedidosweb_pedidosdetalle', 'porc_bonif') IS NOT NULL
BEGIN
    UPDATE d
    SET d.bonificacion = d.porc_bonif
    FROM dbo.pq_pedidosweb_pedidosdetalle AS d
    WHERE d.bonificacion IS NULL
      AND d.porc_bonif IS NOT NULL;
END;

PRINT 'OK: bonificacion decimal(6,2) en pq_pedidosweb_pedidosdetalle verificada.';
