/*
  PedidosWeb — CC PQ #11
  Tabla pq_pedidosweb_clientescontactos (API GET /clientes y GET /clientes/{codCliente}).

  Idempotente: solo CREATE si la tabla no existe. No DROP.
*/

SET NOCOUNT ON;

IF OBJECT_ID(N'dbo.pq_pedidosweb_clientescontactos', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.pq_pedidosweb_clientescontactos (
        id int IDENTITY(1,1) NOT NULL PRIMARY KEY,
        cod_client nvarchar(20) NOT NULL,
        cod_contacto nvarchar(50) NOT NULL,
        nombre nvarchar(120) NOT NULL,
        telefono nvarchar(50) NULL,
        mail nvarchar(120) NULL,
        CONSTRAINT UQ_pw_clicont_cli_cod UNIQUE (cod_client, cod_contacto)
    );
END;

PRINT 'OK: tabla pq_pedidosweb_clientescontactos verificada.';
