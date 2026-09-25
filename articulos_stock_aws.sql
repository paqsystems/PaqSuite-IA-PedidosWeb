-- Consulta de stock disponible - Cálculo exacto como el Excel
-- Suma todos los artículos que comparten la misma BASE

WITH pedidos_estado_1 AS (
	-- CTE: Cantidad por artículo en pedidos con estado = 1
	SELECT 
		pd.cod_articulo,
		SUM(pd.cantidad) AS cantidad_pedidos_estado_1
	FROM pq_pedidosweb_pedidosdetalle pd
	INNER JOIN pq_pedidosweb_pedidoscabecera pc ON pd.cod_pedido = pc.cod_pedido
	WHERE pc.estado = 0
	GROUP BY pd.cod_articulo
),
stock_disponible_por_articulo AS (
	-- CTE: Stock disponible para cada artículo individual
	SELECT 
		a.codigo,
		a.descripcion,
		a.base,
		/*
		ISNULL(s.stock, 0) AS stock_total,
		ISNULL(s.comprometido, 0) AS comprometido_sistema,
		ISNULL(p.cantidad_pedidos_estado_1, 0) AS cantidad_en_pedidos_estado_1,
		*/
		ISNULL(s.stock, 0) - ISNULL(s.comprometido, 0) - ISNULL(p.cantidad_pedidos_estado_1, 0) AS stock_disponible
	FROM pq_pedidosweb_articulos a
	LEFT JOIN pq_pedidosweb_stock s ON a.codigo = s.cod_articulo
	LEFT JOIN pedidos_estado_1 p ON a.codigo = p.cod_articulo
),
stock_disponible_por_base AS (
	-- CTE: Suma de stock disponible para cada BASE (agrupa todos los artículos de la misma base)
	SELECT 
		a.base,
		/*
		SUM(ISNULL(s.stock, 0)) AS stock_total_base,
		SUM(ISNULL(s.comprometido, 0)) AS comprometido_sistema_base,
		SUM(ISNULL(p.cantidad_pedidos_estado_1, 0)) AS cantidad_en_pedidos_estado_1_base,
		*/
		SUM(ISNULL(s.stock, 0)) - SUM(ISNULL(s.comprometido, 0)) - SUM(ISNULL(p.cantidad_pedidos_estado_1, 0)) AS stock_disponible_base
	FROM pq_pedidosweb_articulos a
	LEFT JOIN pq_pedidosweb_stock s ON a.codigo = s.cod_articulo
	LEFT JOIN pedidos_estado_1 p ON a.codigo = p.cod_articulo
	WHERE a.base IS NOT NULL
	GROUP BY a.base
)
SELECT 
	art.codigo AS cod_articulo,
	art.descripcion,
	/*
	art.stock_total,
	art.comprometido_sistema,
	art.cantidad_en_pedidos_estado_1,
	--
	art.base AS cod_base,
	base.stock_total_base,
	base.comprometido_sistema_base,
	base.cantidad_en_pedidos_estado_1_base,
	*/
	art.stock_disponible,
	base.stock_disponible_base
FROM stock_disponible_por_articulo art
LEFT JOIN stock_disponible_por_base base ON art.base = base.base
WHERE art.base IS NOT NULL
ORDER BY art.codigo;