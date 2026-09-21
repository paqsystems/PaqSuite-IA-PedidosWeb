/*
  PedidosWeb — DDL canónico pq_pedidosweb_articulos (fuente: esquema ERP / PQ 31/08/2026)

  Idempotente: crea la tabla solo si no existe.
  No DROP. No altera columnas existentes en tablas ya creadas.
*/

SET NOCOUNT ON;

IF OBJECT_ID(N'dbo.pq_pedidosweb_articulos', N'U') IS NULL
BEGIN
    CREATE TABLE [dbo].[pq_pedidosweb_articulos](
        [codigo] [varchar](15) NOT NULL,
        [descripcion] [varchar](60) NULL,
        [bonificacion] [decimal](6, 2) NULL,
        [usa_esc] [char](1) NULL,
        [base] [varchar](15) NULL,
        [valor1] [varchar](15) NULL,
        [valor2] [varchar](15) NULL,
        [porc_iva] [numeric](6, 2) NULL,
        [equivalencia_ventas] [decimal](18, 4) NOT NULL,
        [stockeable] [bit] NOT NULL,
        [especial] [bit] NOT NULL,
        CONSTRAINT [PK_pq_pedidosweb_articulos] PRIMARY KEY CLUSTERED
        (
            [codigo] ASC
        ) WITH (
            PAD_INDEX = OFF,
            STATISTICS_NORECOMPUTE = OFF,
            IGNORE_DUP_KEY = OFF,
            ALLOW_ROW_LOCKS = ON,
            ALLOW_PAGE_LOCKS = ON,
            OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF
        ) ON [PRIMARY]
    ) ON [PRIMARY];

    ALTER TABLE [dbo].[pq_pedidosweb_articulos]
        ADD CONSTRAINT [DF_pq_pedidosweb_articulos_descripcion]
        DEFAULT (NULL) FOR [descripcion];

    ALTER TABLE [dbo].[pq_pedidosweb_articulos]
        ADD CONSTRAINT [DF_pq_pedidosweb_articulos_bonificacion]
        DEFAULT (NULL) FOR [bonificacion];

    ALTER TABLE [dbo].[pq_pedidosweb_articulos]
        ADD CONSTRAINT [DF_pq_pedidosweb_articulos_equivalencia_ventas]
        DEFAULT (1) FOR [equivalencia_ventas];

    ALTER TABLE [dbo].[pq_pedidosweb_articulos]
        ADD CONSTRAINT [DF_pq_pedidosweb_articulos_stockeable]
        DEFAULT ((1)) FOR [stockeable];

    ALTER TABLE [dbo].[pq_pedidosweb_articulos]
        ADD CONSTRAINT [DF_pq_pedidosweb_articulos_especial]
        DEFAULT ((0)) FOR [especial];

    PRINT 'OK: creada pq_pedidosweb_articulos (DDL canónico).';
END
ELSE
    PRINT 'OK: pq_pedidosweb_articulos ya existía (sin cambios).';
