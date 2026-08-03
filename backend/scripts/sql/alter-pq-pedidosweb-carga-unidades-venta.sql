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

/* Parámetro CargaUnidadesVenta (idempotente: solo INSERT si falta). */
IF OBJECT_ID(N'dbo.PQ_parametros_gral', N'U') IS NOT NULL
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM dbo.PQ_parametros_gral WITH (NOLOCK)
        WHERE Programa = N'PedidosWeb'
          AND Clave = N'CargaUnidadesVenta'
    )
    BEGIN
        INSERT INTO dbo.PQ_parametros_gral (
            Programa,
            Clave,
            tipo_valor,
            Valor_String,
            Valor_Text,
            Valor_Int,
            Valor_DateTime,
            Valor_Bool,
            Valor_Decimal,
            CAPTION,
            TOOLTIP
        )
        VALUES (
            N'PedidosWeb',
            N'CargaUnidadesVenta',
            N'B',
            NULL,
            NULL,
            NULL,
            NULL,
            0,
            NULL,
            N'Carga de pedidos por unidades de venta',
            N'Si está activo, la cantidad ingresada en renglón, Excel o asistente se interpreta como unidades de venta (cantidad_venta). Si está inactivo, se interpreta como unidades de stock/precio (cantidad). Los importes se calculan siempre sobre cantidad.'
        );

        PRINT 'OK: INSERT CargaUnidadesVenta.';
    END
    ELSE
        PRINT 'OK: CargaUnidadesVenta ya existía.';
END;
