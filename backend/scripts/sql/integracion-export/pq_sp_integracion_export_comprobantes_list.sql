CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_export_comprobantes_list
    @estado_rangos_json NVARCHAR(MAX),
    @page INT,
    @page_size INT,
    @cod_cliente VARCHAR(32) = NULL,
    @cod_pedido VARCHAR(64) = NULL,
    @fecha_desde DATETIME2 = NULL,
    @fecha_hasta DATETIME2 = NULL,
    @total INT OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    IF @page < 1 SET @page = 1;
    IF @page_size < 1 SET @page_size = 1;
    IF @page_size > 1000 SET @page_size = 1000;

    DECLARE @offset INT = (@page - 1) * @page_size;

    SELECT @total = COUNT(*)
    FROM dbo.pq_pedidosweb_pedidoscabecera c WITH (NOLOCK)
    WHERE EXISTS (
            SELECT 1
            FROM OPENJSON(@estado_rangos_json)
                WITH (desde INT '$.desde', hasta INT '$.hasta') r
            WHERE c.estado >= r.desde AND c.estado <= r.hasta
        )
      AND (@cod_cliente IS NULL OR c.cod_cliente = @cod_cliente)
      AND (@cod_pedido IS NULL OR c.cod_pedido = @cod_pedido)
      AND (@fecha_desde IS NULL OR c.fecha >= @fecha_desde)
      AND (@fecha_hasta IS NULL OR c.fecha <= @fecha_hasta);

    CREATE TABLE #page (
        cod_pedido VARCHAR(64) NOT NULL PRIMARY KEY
    );

    INSERT INTO #page (cod_pedido)
    SELECT c.cod_pedido
    FROM dbo.pq_pedidosweb_pedidoscabecera c WITH (NOLOCK)
    WHERE EXISTS (
            SELECT 1
            FROM OPENJSON(@estado_rangos_json)
                WITH (desde INT '$.desde', hasta INT '$.hasta') r
            WHERE c.estado >= r.desde AND c.estado <= r.hasta
        )
      AND (@cod_cliente IS NULL OR c.cod_cliente = @cod_cliente)
      AND (@cod_pedido IS NULL OR c.cod_pedido = @cod_pedido)
      AND (@fecha_desde IS NULL OR c.fecha >= @fecha_desde)
      AND (@fecha_hasta IS NULL OR c.fecha <= @fecha_hasta)
    ORDER BY c.fecha DESC, c.cod_pedido ASC
    OFFSET @offset ROWS FETCH NEXT @page_size ROWS ONLY;

    SELECT
        c.cod_pedido,
        c.cod_cliente,
        c.fecha,
        c.nivel,
        c.observaciones,
        c.incluye_iva,
        c.moneda,
        c.estado,
        c.tal_pedido_tango,
        c.nro_pedido_tango,
        c.cod_usuario_web,
        c.fecha_modif,
        c.total,
        c.total_iva,
        c.leyenda_1,
        c.leyenda_2,
        c.leyenda_3,
        c.leyenda_4,
        c.leyenda_5,
        c.descuento,
        c.bonif_1,
        c.bonif_2,
        c.bonif_3,
        c.cod_perfil,
        c.cod_vended,
        c.cod_condvta,
        c.id_de,
        c.cod_transpor,
        c.lista_precios,
        c.expreso,
        c.expreso_dire,
        c.fecha_entrega,
        c.usuario_creacion,
        c.fecha_creacion,
        c.usuario_modificacion,
        c.fechahora_inicio_proceso,
        c.fechahora_ultima_actividad,
        c.nro_visible,
        c.origen_comprobante,
        c.cod_pedido_origen,
        c.cod_presupuesto_origen,
        cl.nombre AS cliente_nombre,
        cl.fantasia AS cliente_fantasia,
        vend.nombre AS vendedor_nombre,
        perf.descripcion AS perfil_descripcion,
        cv.descripcion AS condventa_descripcion,
        transp.descripcion AS transporte_descripcion,
        lp.descripcion AS listaprecios_descripcion,
        de.direccion AS direccion_entrega,
        pc.tipo_cierre AS cierre_tipo_cierre,
        pc.id_motivo AS cierre_id_motivo,
        mc.descripcion AS cierre_motivo_descripcion,
        pc.fecha_cierre AS cierre_fecha_cierre,
        pc.cod_pedido_generado AS cierre_cod_pedido_generado,
        pc.observacion AS cierre_observacion
    FROM #page p
    INNER JOIN dbo.pq_pedidosweb_pedidoscabecera c WITH (NOLOCK) ON c.cod_pedido = p.cod_pedido
    LEFT JOIN dbo.pq_pedidosweb_clientes cl WITH (NOLOCK) ON cl.cod_client = c.cod_cliente
    LEFT JOIN dbo.pq_pedidosweb_vendedores vend WITH (NOLOCK) ON vend.cod_vended = c.cod_vended
    LEFT JOIN dbo.pq_pedidosweb_perfil perf WITH (NOLOCK) ON perf.cod_perfil = c.cod_perfil
    LEFT JOIN dbo.pq_pedidosweb_condventa cv WITH (NOLOCK) ON cv.codigo = c.cod_condvta
    LEFT JOIN dbo.pq_pedidosweb_transportes transp WITH (NOLOCK) ON transp.codigo = c.cod_transpor
    LEFT JOIN dbo.pq_pedidosweb_listaprecios lp WITH (NOLOCK) ON lp.cod_lista = c.lista_precios
    LEFT JOIN dbo.pq_pedidosweb_clientesde de WITH (NOLOCK) ON de.cod_client = c.cod_cliente AND de.id_de = c.id_de
    LEFT JOIN dbo.pq_pedidosweb_presupuestos_cierres pc WITH (NOLOCK) ON pc.cod_presupuesto = c.cod_pedido AND c.estado = 98
    LEFT JOIN dbo.pq_pedidosweb_motivos_cierre mc WITH (NOLOCK) ON mc.id_motivo = pc.id_motivo
    ORDER BY c.fecha DESC, c.cod_pedido ASC;

    SELECT
        d.cod_pedido,
        d.renglon,
        d.cod_articulo,
        d.descripcion_articulo,
        d.cantidad,
        d.cantidad_venta,
        d.porc_bonif,
        d.bonificacion,
        d.precio,
        d.precio_neto,
        d.precio_bruto,
        d.importe_neto,
        d.iva,
        d.importe_total,
        d.importe_lista,
        a.especial AS articulo_especial,
        a.descripcion AS articulo_descripcion
    FROM #page p
    INNER JOIN dbo.pq_pedidosweb_pedidosdetalle d WITH (NOLOCK) ON d.cod_pedido = p.cod_pedido
    LEFT JOIN dbo.pq_pedidosweb_articulos a WITH (NOLOCK) ON a.codigo = d.cod_articulo
    ORDER BY d.cod_pedido ASC, d.renglon ASC;

    DROP TABLE #page;
END
GO