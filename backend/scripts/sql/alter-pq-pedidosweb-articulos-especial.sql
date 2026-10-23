/*
  PedidosWeb — CC PQ #17
  - pq_pedidosweb_articulos.especial (bit NOT NULL DEFAULT 0)

  Idempotente: solo agrega columna si falta.
  CREATE completo: create-pq-pedidosweb-articulos.sql
*/

SET NOCOUNT ON;

IF OBJECT_ID(N'dbo.pq_pedidosweb_articulos', N'U') IS NOT NULL
BEGIN
    IF COL_LENGTH('dbo.pq_pedidosweb_articulos', 'especial') IS NULL
        ALTER TABLE dbo.pq_pedidosweb_articulos
            ADD especial bit NOT NULL
                CONSTRAINT DF_pw_art_especial DEFAULT (0);
END;

PRINT 'OK: columna especial CC PQ #17 verificada.';
