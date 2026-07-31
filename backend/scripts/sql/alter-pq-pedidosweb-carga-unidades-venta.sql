/*
  PedidosWeb — CC PQ #10
  - pq_pedidosweb_articulos.equivalencia_ventas
  - pq_pedidosweb_pedidosdetalle.cantidad_venta

  Idempotente: solo agrega columnas si no existen.
*/

SET NOCOUNT ON;

IF OBJECT_ID(N'dbo.pq_pedidosweb_articulos', N'U') IS NOT NULL
BEGIN
    IF COL_LENGTH('dbo.pq_pedidosweb_articulos', 'equivalencia_ventas') IS NULL
        ALTER TABLE dbo.pq_pedidosweb_articulos
            ADD equivalencia_ventas decimal(18, 4) NOT NULL
                CONSTRAINT DF_pw_art_equiv_ventas DEFAULT (1);
END;

IF OBJECT_ID(N'dbo.pq_pedidosweb_pedidosdetalle', N'U') IS NOT NULL
BEGIN
    IF COL_LENGTH('dbo.pq_pedidosweb_pedidosdetalle', 'cantidad_venta') IS NULL
        ALTER TABLE dbo.pq_pedidosweb_pedidosdetalle
            ADD cantidad_venta decimal(18, 4) NULL;

    UPDATE d
    SET d.cantidad_venta = d.cantidad
    FROM dbo.pq_pedidosweb_pedidosdetalle AS d WITH (NOLOCK)
    WHERE d.cantidad_venta IS NULL;
END;

PRINT 'OK: columnas CargaUnidadesVenta verificadas.';
