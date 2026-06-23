/* ============================================================
   GEN-07 Excel — seed catálogo PEDIDO_INDIVIDUAL (PedidosWeb)
   Equivalente a PedidosWebExcelImportCatalogSeeder

   Requiere: create-excel-tables.sql ejecutado antes
   Idempotente: se puede re-ejecutar

   Alternativa Laravel:
     php artisan db:seed --class=Database\Seeders\ExcelImport\PedidosWebExcelImportCatalogSeeder --force
   ============================================================ */

SET NOCOUNT ON;

BEGIN TRY
    BEGIN TRAN;

    DECLARE @idProceso BIGINT;
    DECLARE @procHost VARCHAR(100) = N'pw_cargapedidos';

    MERGE [pq_excel_procesos] AS t
    USING (SELECT N'PEDIDO_INDIVIDUAL' AS codigo_proceso) AS s
        ON t.[codigo_proceso] = s.[codigo_proceso]
    WHEN MATCHED THEN
        UPDATE SET
            [nombre_proceso] = N'Importacion pedido individual',
            [descripcion] = N'Importacion de un comprobante desde plantilla Excel',
            [permite_procesamiento_parcial] = 0,
            [permite_solo_validar] = 0,
            [genera_plantilla] = 1,
            [handler_backend] = N'Importacion.Pedidos.IndividualHandler',
            [procedimiento_host] = @procHost,
            [formato_booleano_plantilla] = N'0_1',
            [activo] = 1,
            [usuario_alta] = N'system'
    WHEN NOT MATCHED THEN
        INSERT (
            [codigo_proceso], [nombre_proceso], [descripcion],
            [permite_procesamiento_parcial], [permite_solo_validar], [genera_plantilla],
            [handler_backend], [procedimiento_host], [formato_booleano_plantilla],
            [activo], [usuario_alta]
        )
        VALUES (
            N'PEDIDO_INDIVIDUAL',
            N'Importacion pedido individual',
            N'Importacion de un comprobante desde plantilla Excel',
            0, 0, 1,
            N'Importacion.Pedidos.IndividualHandler',
            @procHost,
            N'0_1',
            1,
            N'system'
        );

    SELECT @idProceso = [id]
    FROM [pq_excel_procesos]
    WHERE [codigo_proceso] = N'PEDIDO_INDIVIDUAL';

  DELETE FROM [pq_excel_procesos_campos] WHERE [id_proceso] = @idProceso;

    INSERT INTO [pq_excel_procesos_campos] (
        [id_proceso], [orden_campo], [nombre_columna_excel], [nombre_campo_interno],
        [tipo_dato], [largo_maximo], [cantidad_decimales],
        [es_columna_obligatoria_estructural], [es_campo_codigo], [activo], [observaciones]
    )
    VALUES
        (@idProceso,  1, N'codigo cliente',      N'cod_cliente',   N'codigo', 20,  NULL, 1, 1, 1, NULL),
        (@idProceso,  2, N'codigo de articulo',  N'cod_articulo',  N'codigo', 50,  NULL, 1, 1, 1, NULL),
        (@idProceso,  3, N'cantidad',            N'cantidad',      N'decimal', NULL, 4,    1, 0, 1, NULL),
        (@idProceso,  4, N'precio lista',        N'precio_lista',  N'decimal', NULL, 4,    0, 0, 1, NULL),
        (@idProceso,  5, N'bonif renglon',       N'bonif_renglon', N'decimal', NULL, 4,    0, 0, 1, NULL),
        (@idProceso,  6, N'codigo perfil',       N'cod_perfil',    N'codigo', 20,  NULL, 0, 0, 1, NULL),
        (@idProceso,  7, N'condicion de venta',  N'cod_condvta',   N'entero',  NULL, NULL, 0, 0, 1, NULL),
        (@idProceso,  8, N'codigo transporte',   N'cod_transpor',  N'codigo', 20,  NULL, 0, 0, 1, NULL),
        (@idProceso,  9, N'direccion entrega',   N'id_de',         N'entero',  NULL, NULL, 0, 0, 1, NULL),
        (@idProceso, 10, N'codigo lista',        N'cod_lista',     N'entero',  NULL, NULL, 0, 0, 1, NULL),
        (@idProceso, 11, N'nivel',               N'nivel',         N'entero',  NULL, NULL, 0, 0, 1, NULL),
        (@idProceso, 12, N'bonificacion 1',      N'bonif1',        N'decimal', NULL, 4,    0, 0, 1, NULL),
        (@idProceso, 13, N'bonificacion 2',      N'bonif2',        N'decimal', NULL, 4,    0, 0, 1, NULL),
        (@idProceso, 14, N'bonificacion 3',      N'bonif3',        N'decimal', NULL, 4,    0, 0, 1, NULL),
        (@idProceso, 15, N'expreso',             N'expreso',       N'texto',   80,  NULL, 0, 0, 1, NULL),
        (@idProceso, 16, N'direccion expreso',   N'expreso_dire',  N'texto',   200, NULL, 0, 0, 1, NULL),
        (@idProceso, 17, N'fecha entrega',       N'fecha_entrega', N'fecha',   NULL, NULL, 0, 0, 1, NULL),
        (@idProceso, 18, N'observaciones',       N'observaciones', N'texto',   500, NULL, 0, 0, 1, NULL),
        (@idProceso, 19, N'leyenda 1',           N'leyenda1',      N'texto',   255, NULL, 0, 0, 1, NULL),
        (@idProceso, 20, N'leyenda 2',           N'leyenda2',      N'texto',   255, NULL, 0, 0, 1, NULL),
        (@idProceso, 21, N'leyenda 3',           N'leyenda3',      N'texto',   255, NULL, 0, 0, 1, NULL),
        (@idProceso, 22, N'leyenda 4',           N'leyenda4',      N'texto',   255, NULL, 0, 0, 1, NULL),
        (@idProceso, 23, N'leyenda 5',           N'leyenda5',      N'texto',   255, NULL, 0, 0, 1, NULL);

    COMMIT TRAN;

    PRINT N'OK — catálogo Excel PEDIDO_INDIVIDUAL cargado.';
END TRY
BEGIN CATCH
    IF @@TRANCOUNT > 0
        ROLLBACK TRAN;

    THROW;
END CATCH;

SELECT p.[codigo_proceso], p.[procedimiento_host], COUNT(c.[id]) AS campos
FROM [pq_excel_procesos] p
LEFT JOIN [pq_excel_procesos_campos] c ON c.[id_proceso] = p.[id]
WHERE p.[codigo_proceso] = N'PEDIDO_INDIVIDUAL'
GROUP BY p.[codigo_proceso], p.[procedimiento_host];
