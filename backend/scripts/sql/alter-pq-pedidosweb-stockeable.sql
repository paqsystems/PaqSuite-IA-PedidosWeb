/*
  PedidosWeb — CC PQ #12
  - pq_pedidosweb_articulos.stockeable
  - parámetro IncluyeArticulosNoStockeables (informativo)

  Idempotente: solo agrega columna / INSERT param si faltan.
*/

SET NOCOUNT ON;

IF OBJECT_ID(N'dbo.pq_pedidosweb_articulos', N'U') IS NOT NULL
BEGIN
    IF COL_LENGTH('dbo.pq_pedidosweb_articulos', 'stockeable') IS NULL
        ALTER TABLE dbo.pq_pedidosweb_articulos
            ADD stockeable bit NOT NULL
                CONSTRAINT DF_pw_art_stockeable DEFAULT (1);
END;

IF OBJECT_ID(N'dbo.PQ_parametros_gral', N'U') IS NOT NULL
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM dbo.PQ_parametros_gral WITH (NOLOCK)
        WHERE Programa = N'PedidosWeb'
          AND Clave = N'IncluyeArticulosNoStockeables'
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
            N'IncluyeArticulosNoStockeables',
            N'B',
            NULL,
            NULL,
            NULL,
            NULL,
            0,
            NULL,
            N'Incluye artículos no stockeables',
            N'Indica si el origen de artículos incluye ítems no stockeables. En PedidosWeb es solo informativo; el uso operativo está en la aplicación que alimenta el catálogo.'
        );

        PRINT 'OK: INSERT IncluyeArticulosNoStockeables.';
    END
    ELSE
        PRINT 'OK: IncluyeArticulosNoStockeables ya existía.';
END;

PRINT 'OK: columnas CC PQ #12 verificadas.';
