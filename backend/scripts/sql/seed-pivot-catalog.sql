/* ============================================================
   Seed catálogo pivots — equivalente a PivotCatalogPilotSeeder
   + PivotCatalogInformesSeeder

   SQL Server — ejecutar en la BD tenant PedidosWeb
   (ej. Ankas_del_sur). Idempotente: se puede re-ejecutar.

   Origen seeders:
   - backend/database/seeders/Pivots/PivotCatalogPilotSeeder.php
   - backend/database/seeders/Pivots/PivotCatalogInformesSeeder.php

   Alternativa Laravel (mismo resultado):
   php artisan db:seed --class=Database\Seeders\Pivots\PivotCatalogPilotSeeder --force
   ============================================================ */

SET NOCOUNT ON;

BEGIN TRY
    BEGIN TRAN;

    /* 1) Plantilla métrica (FK de campos numéricos) */
    MERGE [pq_pivots_plantillas] AS t
    USING (SELECT N'PLANTILLA_METRICA_NUM' AS plantilla_id) AS s
        ON t.[plantilla_id] = s.[plantilla_id]
    WHEN MATCHED THEN
        UPDATE SET
            [nombre] = N'Métrica numérica estándar',
            [descripcion] = N'Defaults para campos numéricos agregables',
            [propiedades_json] = N'{"tipoDato":"number","rolCampo":"metrica","agregacionDefault":"sum","formato":{"format":"#,##0.00"}}',
            [activo] = 1
    WHEN NOT MATCHED THEN
        INSERT ([plantilla_id], [nombre], [descripcion], [propiedades_json], [activo])
        VALUES (
            N'PLANTILLA_METRICA_NUM',
            N'Métrica numérica estándar',
            N'Defaults para campos numéricos agregables',
            N'{"tipoDato":"number","rolCampo":"metrica","agregacionDefault":"sum","formato":{"format":"#,##0.00"}}',
            1
        );

    DECLARE @restriccionesJson NVARCHAR(MAX) = N'{"maximoFilas":10,"maximoColumnas":10,"maximoMetricas":15,"maximoRegistrosBase":5000,"bloquearSiExcedeVolumen":true,"requiereFiltroPrevio":false}';
    DECLARE @configGeneralJson NVARCHAR(MAX) = N'{"mostrarGrillaYPivot":true,"vistaInicial":"grilla"}';
    DECLARE @exportJson NVARCHAR(MAX) = N'{"excelBasicoHabilitado":true,"excelFormateadoHabilitado":true,"incluirFiltrosAplicados":true,"incluirMetadatos":true}';
    DECLARE @persistJson NVARCHAR(MAX) = N'{"habilitarDiseños":true}';

    /* 2) CONSULTA_PILOTO_PIVOT — Historial ventas */
    MERGE [pq_pivots_consultas] AS t
    USING (SELECT N'CONSULTA_PILOTO_PIVOT' AS consulta_id) AS s
        ON t.[consulta_id] = s.[consulta_id]
    WHEN MATCHED THEN
        UPDATE SET
            [nombre] = N'Historial ventas (piloto pivot)',
            [descripcion] = N'Consulta piloto epic pivots — historial ventas',
            [fuente_tipo] = N'service',
            [fuente_nombre] = N'historial_ventas',
            [procedimiento_host] = N'pw_historialventas',
            [version_definicion] = 1,
            [pivot_habilitado] = 1,
            [admite_drilldown] = 1,
            [activo] = 1,
            [pivot_base_json] = N'{"filas":["codCliente","razonSocial"],"columnas":[],"valores":[{"campoId":"cantidad","agregacion":"sum"}],"filtrosInternos":[],"mostrarSubtotales":true,"mostrarTotalesGenerales":true}',
            [configuracion_general_json] = @configGeneralJson,
            [exportacion_json] = @exportJson,
            [persistencia_json] = @persistJson,
            [usuario_creacion] = N'seed'
    WHEN NOT MATCHED THEN
        INSERT (
            [consulta_id], [nombre], [descripcion], [fuente_tipo], [fuente_nombre], [procedimiento_host],
            [version_definicion], [pivot_habilitado], [admite_drilldown], [activo], [pivot_base_json],
            [configuracion_general_json], [exportacion_json], [persistencia_json], [fecha_creacion], [usuario_creacion]
        )
        VALUES (
            N'CONSULTA_PILOTO_PIVOT',
            N'Historial ventas (piloto pivot)',
            N'Consulta piloto epic pivots — historial ventas',
            N'service',
            N'historial_ventas',
            N'pw_historialventas',
            1, 1, 1, 1,
            N'{"filas":["codCliente","razonSocial"],"columnas":[],"valores":[{"campoId":"cantidad","agregacion":"sum"}],"filtrosInternos":[],"mostrarSubtotales":true,"mostrarTotalesGenerales":true}',
            @configGeneralJson,
            @exportJson,
            @persistJson,
            SYSUTCDATETIME(),
            N'seed'
        );

    DELETE FROM [pq_pivots_campos] WHERE [consulta_id] = N'CONSULTA_PILOTO_PIVOT';

    INSERT INTO [pq_pivots_campos] (
        [consulta_id], [campo_id], [nombre_tecnico], [nombre_visible], [tipo_dato], [rol_campo],
        [roles_permitidos_json], [agregacion_default], [plantilla_global_id], [activo], [orden]
    )
    VALUES
        (N'CONSULTA_PILOTO_PIVOT', N'codCliente', N'codCliente', N'Cliente', N'string', N'dimension', N'["fila","columna","filtro"]', NULL, NULL, 1, 10),
        (N'CONSULTA_PILOTO_PIVOT', N'razonSocial', N'razonSocial', N'Razón social', N'string', N'dimension', N'["fila","columna"]', NULL, NULL, 1, 20),
        (N'CONSULTA_PILOTO_PIVOT', N'fechaEmision', N'fechaEmision', N'Fecha emisión', N'date', N'dimension', N'["fila","columna"]', NULL, NULL, 1, 30),
        (N'CONSULTA_PILOTO_PIVOT', N'cantidad', N'cantidad', N'Cantidad', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 40),
        (N'CONSULTA_PILOTO_PIVOT', N'totSinImp', N'totSinImp', N'Total s/ imp.', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 50),
        (N'CONSULTA_PILOTO_PIVOT', N'nRemito', N'nRemito', N'Nº remito', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 60),
        (N'CONSULTA_PILOTO_PIVOT', N'tipo', N'tipo', N'Tipo', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 70),
        (N'CONSULTA_PILOTO_PIVOT', N'numero', N'numero', N'Número', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 80),
        (N'CONSULTA_PILOTO_PIVOT', N'condVta', N'condVta', N'Cond. venta', N'number', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 90),
        (N'CONSULTA_PILOTO_PIVOT', N'porcDesc', N'porcDesc', N'% desc.', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 100),
        (N'CONSULTA_PILOTO_PIVOT', N'cotiz', N'cotiz', N'Cotización', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 110),
        (N'CONSULTA_PILOTO_PIVOT', N'moneda', N'moneda', N'Moneda', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 120),
        (N'CONSULTA_PILOTO_PIVOT', N'totalComp', N'totalComp', N'Total comprob.', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 130),
        (N'CONSULTA_PILOTO_PIVOT', N'codTransp', N'codTransp', N'Cód. transporte', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 140),
        (N'CONSULTA_PILOTO_PIVOT', N'nomTransp', N'nomTransp', N'Transporte', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 150),
        (N'CONSULTA_PILOTO_PIVOT', N'codArticulo', N'codArticulo', N'Artículo', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 160),
        (N'CONSULTA_PILOTO_PIVOT', N'descripcion', N'descripcion', N'Descripción', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 170),
        (N'CONSULTA_PILOTO_PIVOT', N'codDep', N'codDep', N'Depósito', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 180),
        (N'CONSULTA_PILOTO_PIVOT', N'um', N'um', N'UM', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 190),
        (N'CONSULTA_PILOTO_PIVOT', N'precio', N'precio', N'Precio', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 200),
        (N'CONSULTA_PILOTO_PIVOT', N'nCompRem', N'nCompRem', N'Nº comp. rem.', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 210),
        (N'CONSULTA_PILOTO_PIVOT', N'cantRem', N'cantRem', N'Cant. rem.', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 220),
        (N'CONSULTA_PILOTO_PIVOT', N'fechaRem', N'fechaRem', N'Fecha rem.', N'date', N'dimension', N'["fila","columna"]', NULL, NULL, 1, 230);

    DELETE FROM [pq_pivots_validaciones] WHERE [consulta_id] = N'CONSULTA_PILOTO_PIVOT';

    INSERT INTO [pq_pivots_validaciones] ([consulta_id], [tipo_validacion], [configuracion_json], [activo])
    VALUES
        (N'CONSULTA_PILOTO_PIVOT', N'restricciones', @restriccionesJson, 1),
        (N'CONSULTA_PILOTO_PIVOT', N'filtro_obligatorio', N'{"filtroId":"codCliente","dataField":"codCliente","caption":"Cliente","obligatorio":false,"tipoControl":"select"}', 1);

    /* 3) CONSULTA_DETALLE_PEDIDOS */
    MERGE [pq_pivots_consultas] AS t
    USING (SELECT N'CONSULTA_DETALLE_PEDIDOS' AS consulta_id) AS s
        ON t.[consulta_id] = s.[consulta_id]
    WHEN MATCHED THEN
        UPDATE SET
            [nombre] = N'Detalle de pedidos',
            [descripcion] = N'Informe detalle pedidos — adopción pivot CC PQ #4',
            [fuente_tipo] = N'service',
            [fuente_nombre] = N'detalle_pedidos',
            [procedimiento_host] = N'pw_detallepedidos',
            [version_definicion] = 1,
            [pivot_habilitado] = 1,
            [admite_drilldown] = 1,
            [activo] = 1,
            [pivot_base_json] = N'{"filas":["codCliente","razonSocial"],"columnas":[],"valores":[{"campoId":"cantidad","agregacion":"sum"}],"filtrosInternos":[],"mostrarSubtotales":true,"mostrarTotalesGenerales":true}',
            [configuracion_general_json] = @configGeneralJson,
            [exportacion_json] = @exportJson,
            [persistencia_json] = @persistJson,
            [usuario_creacion] = N'seed'
    WHEN NOT MATCHED THEN
        INSERT (
            [consulta_id], [nombre], [descripcion], [fuente_tipo], [fuente_nombre], [procedimiento_host],
            [version_definicion], [pivot_habilitado], [admite_drilldown], [activo], [pivot_base_json],
            [configuracion_general_json], [exportacion_json], [persistencia_json], [fecha_creacion], [usuario_creacion]
        )
        VALUES (
            N'CONSULTA_DETALLE_PEDIDOS',
            N'Detalle de pedidos',
            N'Informe detalle pedidos — adopción pivot CC PQ #4',
            N'service',
            N'detalle_pedidos',
            N'pw_detallepedidos',
            1, 1, 1, 1,
            N'{"filas":["codCliente","razonSocial"],"columnas":[],"valores":[{"campoId":"cantidad","agregacion":"sum"}],"filtrosInternos":[],"mostrarSubtotales":true,"mostrarTotalesGenerales":true}',
            @configGeneralJson,
            @exportJson,
            @persistJson,
            SYSUTCDATETIME(),
            N'seed'
        );

    DELETE FROM [pq_pivots_campos] WHERE [consulta_id] = N'CONSULTA_DETALLE_PEDIDOS';

    INSERT INTO [pq_pivots_campos] (
        [consulta_id], [campo_id], [nombre_tecnico], [nombre_visible], [tipo_dato], [rol_campo],
        [roles_permitidos_json], [agregacion_default], [plantilla_global_id], [activo], [orden]
    )
    VALUES
        (N'CONSULTA_DETALLE_PEDIDOS', N'codCliente', N'codCliente', N'Cliente', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 10),
        (N'CONSULTA_DETALLE_PEDIDOS', N'razonSocial', N'razonSocial', N'Razón social', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 20),
        (N'CONSULTA_DETALLE_PEDIDOS', N'nombreFantasia', N'nombreFantasia', N'Nombre comercial', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 30),
        (N'CONSULTA_DETALLE_PEDIDOS', N'fecha', N'fecha', N'Fecha', N'date', N'dimension', N'["fila","columna"]', NULL, NULL, 1, 40),
        (N'CONSULTA_DETALLE_PEDIDOS', N'codArticulo', N'codArticulo', N'Artículo', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 50),
        (N'CONSULTA_DETALLE_PEDIDOS', N'descripcionArticulo', N'descripcionArticulo', N'Descripción artículo', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 60),
        (N'CONSULTA_DETALLE_PEDIDOS', N'renglon', N'renglon', N'Renglón', N'number', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 70),
        (N'CONSULTA_DETALLE_PEDIDOS', N'cantidad', N'cantidad', N'Cantidad', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 80),
        (N'CONSULTA_DETALLE_PEDIDOS', N'precioNeto', N'precioNeto', N'Precio neto unit.', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 90),
        (N'CONSULTA_DETALLE_PEDIDOS', N'importeNeto', N'importeNeto', N'Importe neto', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 100),
        (N'CONSULTA_DETALLE_PEDIDOS', N'importeNetoConIva', N'importeNetoConIva', N'Importe c/ IVA', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 110),
        (N'CONSULTA_DETALLE_PEDIDOS', N'precioLista', N'precioLista', N'Precio lista', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 120),
        (N'CONSULTA_DETALLE_PEDIDOS', N'importeBruto', N'importeBruto', N'Importe bruto', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 130),
        (N'CONSULTA_DETALLE_PEDIDOS', N'porcBonif', N'porcBonif', N'% bonif. renglón', N'number', N'metrica', N'["fila","columna","valor"]', N'avg', N'PLANTILLA_METRICA_NUM', 1, 140),
        (N'CONSULTA_DETALLE_PEDIDOS', N'estado', N'estado', N'Estado', N'number', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 150),
        (N'CONSULTA_DETALLE_PEDIDOS', N'codPedido', N'codPedido', N'Cód. pedido', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 160);

    DELETE FROM [pq_pivots_validaciones]
    WHERE [consulta_id] = N'CONSULTA_DETALLE_PEDIDOS' AND [tipo_validacion] = N'restricciones';

    INSERT INTO [pq_pivots_validaciones] ([consulta_id], [tipo_validacion], [configuracion_json], [activo])
    VALUES (N'CONSULTA_DETALLE_PEDIDOS', N'restricciones', @restriccionesJson, 1);

    /* 4) CONSULTA_DEUDA */
    MERGE [pq_pivots_consultas] AS t
    USING (SELECT N'CONSULTA_DEUDA' AS consulta_id) AS s
        ON t.[consulta_id] = s.[consulta_id]
    WHEN MATCHED THEN
        UPDATE SET
            [nombre] = N'Deuda clientes',
            [descripcion] = N'Informe deuda — adopción pivot CC PQ #4',
            [fuente_tipo] = N'service',
            [fuente_nombre] = N'deuda',
            [procedimiento_host] = N'pw_deudaclientes',
            [version_definicion] = 1,
            [pivot_habilitado] = 1,
            [admite_drilldown] = 0,
            [activo] = 1,
            [pivot_base_json] = N'{"filas":["codCliente","razonSocial"],"columnas":["tipo"],"valores":[{"campoId":"saldo","agregacion":"sum"}],"filtrosInternos":[],"mostrarSubtotales":true,"mostrarTotalesGenerales":true}',
            [configuracion_general_json] = @configGeneralJson,
            [exportacion_json] = @exportJson,
            [persistencia_json] = @persistJson,
            [usuario_creacion] = N'seed'
    WHEN NOT MATCHED THEN
        INSERT (
            [consulta_id], [nombre], [descripcion], [fuente_tipo], [fuente_nombre], [procedimiento_host],
            [version_definicion], [pivot_habilitado], [admite_drilldown], [activo], [pivot_base_json],
            [configuracion_general_json], [exportacion_json], [persistencia_json], [fecha_creacion], [usuario_creacion]
        )
        VALUES (
            N'CONSULTA_DEUDA',
            N'Deuda clientes',
            N'Informe deuda — adopción pivot CC PQ #4',
            N'service',
            N'deuda',
            N'pw_deudaclientes',
            1, 1, 0, 1,
            N'{"filas":["codCliente","razonSocial"],"columnas":["tipo"],"valores":[{"campoId":"saldo","agregacion":"sum"}],"filtrosInternos":[],"mostrarSubtotales":true,"mostrarTotalesGenerales":true}',
            @configGeneralJson,
            @exportJson,
            @persistJson,
            SYSUTCDATETIME(),
            N'seed'
        );

    DELETE FROM [pq_pivots_campos] WHERE [consulta_id] = N'CONSULTA_DEUDA';

    INSERT INTO [pq_pivots_campos] (
        [consulta_id], [campo_id], [nombre_tecnico], [nombre_visible], [tipo_dato], [rol_campo],
        [roles_permitidos_json], [agregacion_default], [plantilla_global_id], [activo], [orden]
    )
    VALUES
        (N'CONSULTA_DEUDA', N'codCliente', N'codCliente', N'Cliente', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 10),
        (N'CONSULTA_DEUDA', N'razonSocial', N'razonSocial', N'Razón social', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 20),
        (N'CONSULTA_DEUDA', N'tipo', N'tipo', N'Tipo', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 30),
        (N'CONSULTA_DEUDA', N'numero', N'numero', N'Número', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 40),
        (N'CONSULTA_DEUDA', N'fecha', N'fecha', N'Fecha emisión', N'date', N'dimension', N'["fila","columna"]', NULL, NULL, 1, 50),
        (N'CONSULTA_DEUDA', N'vencimiento', N'vencimiento', N'Vencimiento', N'date', N'dimension', N'["fila","columna"]', NULL, NULL, 1, 60),
        (N'CONSULTA_DEUDA', N'saldo', N'saldo', N'Saldo', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 70);

    DELETE FROM [pq_pivots_validaciones]
    WHERE [consulta_id] = N'CONSULTA_DEUDA' AND [tipo_validacion] = N'restricciones';

    INSERT INTO [pq_pivots_validaciones] ([consulta_id], [tipo_validacion], [configuracion_json], [activo])
    VALUES (N'CONSULTA_DEUDA', N'restricciones', @restriccionesJson, 1);

    /* 5) CONSULTA_CHEQUES */
    MERGE [pq_pivots_consultas] AS t
    USING (SELECT N'CONSULTA_CHEQUES' AS consulta_id) AS s
        ON t.[consulta_id] = s.[consulta_id]
    WHEN MATCHED THEN
        UPDATE SET
            [nombre] = N'Cheques en cartera',
            [descripcion] = N'Informe cheques — adopción pivot CC PQ #4',
            [fuente_tipo] = N'service',
            [fuente_nombre] = N'cheques',
            [procedimiento_host] = N'pw_consultacheques',
            [version_definicion] = 1,
            [pivot_habilitado] = 1,
            [admite_drilldown] = 0,
            [activo] = 1,
            [pivot_base_json] = N'{"filas":["codCliente","banco"],"columnas":["estado"],"valores":[{"campoId":"importe","agregacion":"sum"}],"filtrosInternos":[],"mostrarSubtotales":true,"mostrarTotalesGenerales":true}',
            [configuracion_general_json] = @configGeneralJson,
            [exportacion_json] = @exportJson,
            [persistencia_json] = @persistJson,
            [usuario_creacion] = N'seed'
    WHEN NOT MATCHED THEN
        INSERT (
            [consulta_id], [nombre], [descripcion], [fuente_tipo], [fuente_nombre], [procedimiento_host],
            [version_definicion], [pivot_habilitado], [admite_drilldown], [activo], [pivot_base_json],
            [configuracion_general_json], [exportacion_json], [persistencia_json], [fecha_creacion], [usuario_creacion]
        )
        VALUES (
            N'CONSULTA_CHEQUES',
            N'Cheques en cartera',
            N'Informe cheques — adopción pivot CC PQ #4',
            N'service',
            N'cheques',
            N'pw_consultacheques',
            1, 1, 0, 1,
            N'{"filas":["codCliente","banco"],"columnas":["estado"],"valores":[{"campoId":"importe","agregacion":"sum"}],"filtrosInternos":[],"mostrarSubtotales":true,"mostrarTotalesGenerales":true}',
            @configGeneralJson,
            @exportJson,
            @persistJson,
            SYSUTCDATETIME(),
            N'seed'
        );

    DELETE FROM [pq_pivots_campos] WHERE [consulta_id] = N'CONSULTA_CHEQUES';

    INSERT INTO [pq_pivots_campos] (
        [consulta_id], [campo_id], [nombre_tecnico], [nombre_visible], [tipo_dato], [rol_campo],
        [roles_permitidos_json], [agregacion_default], [plantilla_global_id], [activo], [orden]
    )
    VALUES
        (N'CONSULTA_CHEQUES', N'interno', N'interno', N'Interno', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 10),
        (N'CONSULTA_CHEQUES', N'numero', N'numero', N'Número', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 20),
        (N'CONSULTA_CHEQUES', N'codCliente', N'codCliente', N'Cliente', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 30),
        (N'CONSULTA_CHEQUES', N'nombreCliente', N'nombreCliente', N'Nombre cliente', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 40),
        (N'CONSULTA_CHEQUES', N'banco', N'banco', N'Banco', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 50),
        (N'CONSULTA_CHEQUES', N'fecha', N'fecha', N'Fecha', N'date', N'dimension', N'["fila","columna"]', NULL, NULL, 1, 60),
        (N'CONSULTA_CHEQUES', N'importe', N'importe', N'Importe', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 70),
        (N'CONSULTA_CHEQUES', N'origen', N'origen', N'Origen', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 80),
        (N'CONSULTA_CHEQUES', N'estado', N'estado', N'Estado', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 90);

    DELETE FROM [pq_pivots_validaciones]
    WHERE [consulta_id] = N'CONSULTA_CHEQUES' AND [tipo_validacion] = N'restricciones';

    INSERT INTO [pq_pivots_validaciones] ([consulta_id], [tipo_validacion], [configuracion_json], [activo])
    VALUES (N'CONSULTA_CHEQUES', N'restricciones', @restriccionesJson, 1);

    /* 6) CONSULTA_STOCK */
    MERGE [pq_pivots_consultas] AS t
    USING (SELECT N'CONSULTA_STOCK' AS consulta_id) AS s
        ON t.[consulta_id] = s.[consulta_id]
    WHEN MATCHED THEN
        UPDATE SET
            [nombre] = N'Stock',
            [descripcion] = N'Informe stock — adopción pivot CC PQ #4',
            [fuente_tipo] = N'service',
            [fuente_nombre] = N'stock',
            [procedimiento_host] = N'pw_consultastock',
            [version_definicion] = 1,
            [pivot_habilitado] = 1,
            [admite_drilldown] = 0,
            [activo] = 1,
            [pivot_base_json] = N'{"filas":["codArticulo","descripcion"],"columnas":[],"valores":[{"campoId":"disponibleNeto","agregacion":"sum"}],"filtrosInternos":[],"mostrarSubtotales":true,"mostrarTotalesGenerales":true}',
            [configuracion_general_json] = @configGeneralJson,
            [exportacion_json] = @exportJson,
            [persistencia_json] = @persistJson,
            [usuario_creacion] = N'seed'
    WHEN NOT MATCHED THEN
        INSERT (
            [consulta_id], [nombre], [descripcion], [fuente_tipo], [fuente_nombre], [procedimiento_host],
            [version_definicion], [pivot_habilitado], [admite_drilldown], [activo], [pivot_base_json],
            [configuracion_general_json], [exportacion_json], [persistencia_json], [fecha_creacion], [usuario_creacion]
        )
        VALUES (
            N'CONSULTA_STOCK',
            N'Stock',
            N'Informe stock — adopción pivot CC PQ #4',
            N'service',
            N'stock',
            N'pw_consultastock',
            1, 1, 0, 1,
            N'{"filas":["codArticulo","descripcion"],"columnas":[],"valores":[{"campoId":"disponibleNeto","agregacion":"sum"}],"filtrosInternos":[],"mostrarSubtotales":true,"mostrarTotalesGenerales":true}',
            @configGeneralJson,
            @exportJson,
            @persistJson,
            SYSUTCDATETIME(),
            N'seed'
        );

    DELETE FROM [pq_pivots_campos] WHERE [consulta_id] = N'CONSULTA_STOCK';

    INSERT INTO [pq_pivots_campos] (
        [consulta_id], [campo_id], [nombre_tecnico], [nombre_visible], [tipo_dato], [rol_campo],
        [roles_permitidos_json], [agregacion_default], [plantilla_global_id], [activo], [orden]
    )
    VALUES
        (N'CONSULTA_STOCK', N'codArticulo', N'codArticulo', N'Artículo', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 10),
        (N'CONSULTA_STOCK', N'descripcion', N'descripcion', N'Descripción', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 20),
        (N'CONSULTA_STOCK', N'stock', N'stock', N'Stock', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 30),
        (N'CONSULTA_STOCK', N'comprometido', N'comprometido', N'Comprometido', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 40),
        (N'CONSULTA_STOCK', N'comprometidoWeb', N'comprometidoWeb', N'Comprometido web', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 50),
        (N'CONSULTA_STOCK', N'disponibleNeto', N'disponibleNeto', N'Disponible neto', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 60),
        (N'CONSULTA_STOCK', N'codBase', N'codBase', N'Artículo base', N'string', N'dimension', N'["fila","columna","valor"]', NULL, NULL, 1, 70),
        (N'CONSULTA_STOCK', N'stockBase', N'stockBase', N'Stock base', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 80),
        (N'CONSULTA_STOCK', N'comprometidoBase', N'comprometidoBase', N'Comprometido base', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 90),
        (N'CONSULTA_STOCK', N'comprometidoBaseWeb', N'comprometidoBaseWeb', N'Comp. base web', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 100),
        (N'CONSULTA_STOCK', N'disponibleNetoBase', N'disponibleNetoBase', N'Disp. neto base', N'number', N'metrica', N'["fila","columna","valor"]', N'sum', N'PLANTILLA_METRICA_NUM', 1, 110);

    DELETE FROM [pq_pivots_validaciones]
    WHERE [consulta_id] = N'CONSULTA_STOCK' AND [tipo_validacion] = N'restricciones';

    INSERT INTO [pq_pivots_validaciones] ([consulta_id], [tipo_validacion], [configuracion_json], [activo])
    VALUES (N'CONSULTA_STOCK', N'restricciones', @restriccionesJson, 1);

    COMMIT TRAN;

    PRINT N'OK — catálogo pivot cargado.';
END TRY
BEGIN CATCH
    IF @@TRANCOUNT > 0
        ROLLBACK TRAN;

    THROW;
END CATCH;

/* Verificación */
SELECT [consulta_id], [pivot_habilitado], [activo]
FROM [pq_pivots_consultas]
ORDER BY [consulta_id];

SELECT [consulta_id], COUNT(*) AS campos
FROM [pq_pivots_campos]
GROUP BY [consulta_id]
ORDER BY [consulta_id];
