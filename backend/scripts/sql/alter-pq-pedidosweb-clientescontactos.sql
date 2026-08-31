/*
  PedidosWeb — CC PQ #11
  Tabla pq_pedidosweb_clientescontactos (API GET /clientes y GET /clientes/{codCliente}).

  Idempotente:
  - CREATE TABLE si no existe (PK id + UNIQUE (cod_client, cod_contacto)).
  - Si la tabla existe sin UQ_pw_clicont_cli_cod, ADD CONSTRAINT.
  No DROP.
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
    PRINT 'OK: tabla pq_pedidosweb_clientescontactos creada.';
END
ELSE
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM sys.key_constraints
        WHERE parent_object_id = OBJECT_ID(N'dbo.pq_pedidosweb_clientescontactos')
          AND name = N'UQ_pw_clicont_cli_cod'
          AND type = N'UQ'
    )
    AND NOT EXISTS (
        SELECT 1
        FROM sys.indexes
        WHERE object_id = OBJECT_ID(N'dbo.pq_pedidosweb_clientescontactos')
          AND name = N'UQ_pw_clicont_cli_cod'
          AND is_unique = 1
    )
    BEGIN
        IF EXISTS (
            SELECT 1
            FROM dbo.pq_pedidosweb_clientescontactos WITH (NOLOCK)
            GROUP BY cod_client, cod_contacto
            HAVING COUNT(*) > 1
        )
        BEGIN
            THROW 50001, N'No se puede crear UQ_pw_clicont_cli_cod: hay duplicados (cod_client, cod_contacto).', 1;
        END;

        ALTER TABLE dbo.pq_pedidosweb_clientescontactos
            ADD CONSTRAINT UQ_pw_clicont_cli_cod UNIQUE (cod_client, cod_contacto);

        PRINT 'OK: constraint UQ_pw_clicont_cli_cod agregado.';
    END
    ELSE
    BEGIN
        PRINT 'OK: constraint UQ_pw_clicont_cli_cod ya existe.';
    END;
END;

PRINT 'OK: tabla pq_pedidosweb_clientescontactos verificada.';
