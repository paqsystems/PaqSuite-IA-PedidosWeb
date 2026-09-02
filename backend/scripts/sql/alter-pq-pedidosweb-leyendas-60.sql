/*
  PedidosWeb — CC PQ #13
  Acorta leyenda_1..5 a nvarchar(60) en:
    - pq_pedidosweb_pedidoscabecera
    - pq_pedidosweb_clientes

  Idempotente: recorta valores > 60 y ALTER solo si max_length > 120 (nvarchar(60)).
  Sin DROP / TRUNCATE de tablas.
*/

SET NOCOUNT ON;

DECLARE @tablas TABLE (tabla sysname NOT NULL);
INSERT INTO @tablas (tabla)
VALUES (N'pq_pedidosweb_pedidoscabecera'), (N'pq_pedidosweb_clientes');

DECLARE @columnas TABLE (columna sysname NOT NULL);
INSERT INTO @columnas (columna)
VALUES (N'leyenda_1'), (N'leyenda_2'), (N'leyenda_3'), (N'leyenda_4'), (N'leyenda_5');

DECLARE @tabla sysname;
DECLARE @columna sysname;
DECLARE @sql nvarchar(max);

DECLARE tablas_cursor CURSOR LOCAL FAST_FORWARD FOR
    SELECT tabla FROM @tablas;

OPEN tablas_cursor;
FETCH NEXT FROM tablas_cursor INTO @tabla;

WHILE @@FETCH_STATUS = 0
BEGIN
    IF OBJECT_ID(N'dbo.' + @tabla, N'U') IS NOT NULL
    BEGIN
        DECLARE columnas_cursor CURSOR LOCAL FAST_FORWARD FOR
            SELECT columna FROM @columnas;

        OPEN columnas_cursor;
        FETCH NEXT FROM columnas_cursor INTO @columna;

        WHILE @@FETCH_STATUS = 0
        BEGIN
            IF EXISTS (
                SELECT 1
                FROM sys.columns c WITH (NOLOCK)
                INNER JOIN sys.tables t WITH (NOLOCK) ON t.object_id = c.object_id
                INNER JOIN sys.schemas s WITH (NOLOCK) ON s.schema_id = t.schema_id
                WHERE s.name = N'dbo'
                  AND t.name = @tabla
                  AND c.name = @columna
                  AND c.max_length > 120
            )
            BEGIN
                SET @sql = N'UPDATE dbo.' + QUOTENAME(@tabla)
                    + N' SET ' + QUOTENAME(@columna) + N' = LEFT(' + QUOTENAME(@columna) + N', 60)'
                    + N' WHERE ' + QUOTENAME(@columna) + N' IS NOT NULL AND LEN(' + QUOTENAME(@columna) + N') > 60;';
                EXEC sp_executesql @sql;

                SET @sql = N'ALTER TABLE dbo.' + QUOTENAME(@tabla)
                    + N' ALTER COLUMN ' + QUOTENAME(@columna) + N' nvarchar(60) NULL;';
                EXEC sp_executesql @sql;

                PRINT N'OK: ' + @tabla + N'.' + @columna + N' → nvarchar(60).';
            END
            ELSE
                PRINT N'OK: ' + @tabla + N'.' + @columna + N' ya es nvarchar(60) o no existe.';

            FETCH NEXT FROM columnas_cursor INTO @columna;
        END

        CLOSE columnas_cursor;
        DEALLOCATE columnas_cursor;
    END
    ELSE
        PRINT N'SKIP: no existe dbo.' + @tabla + N'.';

    FETCH NEXT FROM tablas_cursor INTO @tabla;
END

CLOSE tablas_cursor;
DEALLOCATE tablas_cursor;

PRINT N'OK: CC PQ #13 leyendas nvarchar(60) verificado.';
