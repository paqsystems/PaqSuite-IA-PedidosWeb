/*
  PedidosWeb — pq_pedidosweb_clientesde.habitual canónico char(1)

  Idempotente: si la columna existe como bit (u otro tipo), la convierte a char(1).
  Valores: S = habitual, N = no habitual (también se leen 1/0 en runtime).
*/

SET NOCOUNT ON;

IF OBJECT_ID(N'dbo.pq_pedidosweb_clientesde', N'U') IS NULL
BEGIN
    PRINT 'SKIP: pq_pedidosweb_clientesde no existe.';
    RETURN;
END;

IF COL_LENGTH(N'dbo.pq_pedidosweb_clientesde', N'habitual') IS NULL
BEGIN
    ALTER TABLE dbo.pq_pedidosweb_clientesde
        ADD habitual char(1) NULL CONSTRAINT DF_pw_cde_hab DEFAULT ('N');
    PRINT 'OK: columna habitual char(1) agregada.';
END
ELSE
BEGIN
    DECLARE @tipo sysname;
    SELECT @tipo = t.name
    FROM sys.columns c
    INNER JOIN sys.types t ON t.user_type_id = c.user_type_id
    WHERE c.object_id = OBJECT_ID(N'dbo.pq_pedidosweb_clientesde')
      AND c.name = N'habitual';

    IF @tipo <> N'char'
    BEGIN
        /* bit / otras → char(1): 1/true → S, resto → N */
        ALTER TABLE dbo.pq_pedidosweb_clientesde ADD habitual_chr char(1) NULL;

        UPDATE dbo.pq_pedidosweb_clientesde
        SET habitual_chr = CASE
            WHEN CAST(habitual AS varchar(10)) IN (N'1', N'S', N's', N'Y', N'y', N'True', N'true') THEN N'S'
            ELSE N'N'
        END;

        DECLARE @df sysname;
        SELECT @df = dc.name
        FROM sys.default_constraints dc
        INNER JOIN sys.columns c
            ON c.object_id = dc.parent_object_id
           AND c.column_id = dc.parent_column_id
        WHERE dc.parent_object_id = OBJECT_ID(N'dbo.pq_pedidosweb_clientesde')
          AND c.name = N'habitual';

        IF @df IS NOT NULL
            EXEC(N'ALTER TABLE dbo.pq_pedidosweb_clientesde DROP CONSTRAINT [' + @df + N']');

        ALTER TABLE dbo.pq_pedidosweb_clientesde DROP COLUMN habitual;
        EXEC sp_rename N'dbo.pq_pedidosweb_clientesde.habitual_chr', N'habitual', N'COLUMN';

        IF NOT EXISTS (
            SELECT 1
            FROM sys.default_constraints dc
            INNER JOIN sys.columns c
                ON c.object_id = dc.parent_object_id
               AND c.column_id = dc.parent_column_id
            WHERE dc.parent_object_id = OBJECT_ID(N'dbo.pq_pedidosweb_clientesde')
              AND c.name = N'habitual'
        )
            ALTER TABLE dbo.pq_pedidosweb_clientesde
                ADD CONSTRAINT DF_pw_cde_hab DEFAULT ('N') FOR habitual;

        PRINT 'OK: habitual convertido a char(1).';
    END
    ELSE
        PRINT 'OK: habitual ya es char.';
END;
