<?php

namespace App\OpenApi;

/**
 * Rutas REST PedidosWeb (SPEC-101-05 / SPEC-101-07).
 * Tags: ver {@see OpenApiTags}.
 *
 * @OA\Post(
 *     path="/api/v1/comprobantes/grabar",
 *     summary="Grabar pedido o presupuesto (contrato unificado)",
 *     tags={"Pedidos Web"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/ComprobanteGrabarRequest")
 *     ),
 *     @OA\Response(response=200, description="Comprobante grabado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeComprobanteGrabar")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Validacion o regla de negocio")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/comprobantes/copiar",
 *     summary="Copiar comprobante existente a pedido o presupuesto",
 *     tags={"Pedidos Web"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"codComprobanteOrigen","tipoDestino"},
 *             @OA\Property(property="codComprobanteOrigen", type="string"),
 *             @OA\Property(property="tipoDestino", type="string", enum={"pedido","presupuesto"})
 *         )
 *     ),
 *     @OA\Response(response=200, description="Comprobante copiado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Regla de negocio")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/pedidos",
 *     summary="Alta de pedido",
 *     tags={"Pedidos Web"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/ComprobanteUpsertRequest")
 *     ),
 *     @OA\Response(response=200, description="Pedido creado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeComprobanteGrabar")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso alta"),
 *     @OA\Response(response=422, description="Validacion o regla de negocio")
 * )
 *
 * @OA\Put(
 *     path="/api/v1/pedidos/{cod_pedido}",
 *     summary="Modificar pedido existente",
 *     tags={"Pedidos Web"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod_pedido", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/ComprobanteUpsertRequest")
 *     ),
 *     @OA\Response(response=200, description="Pedido modificado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeComprobanteGrabar")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso modi"),
 *     @OA\Response(response=404, description="Pedido inexistente"),
 *     @OA\Response(response=422, description="Regla de negocio")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/pedidos/{cod_pedido}",
 *     summary="Obtener pedido con detalle",
 *     tags={"Pedidos Web"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod_pedido", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Pedido", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeComprobanteDetalle")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo"),
 *     @OA\Response(response=404, description="Pedido inexistente")
 * )
 *
 * @OA\Delete(
 *     path="/api/v1/pedidos/{cod_pedido}",
 *     summary="Eliminar pedido en estado 0",
 *     tags={"Pedidos Web"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod_pedido", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Pedido eliminado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeEmpty")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso baja"),
 *     @OA\Response(response=422, description="Eliminacion deshabilitada o estado invalido")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/pedidos/{cod_pedido}/edicion/iniciar",
 *     summary="Iniciar edicion concurrente del pedido",
 *     tags={"Pedidos Web"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod_pedido", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Edicion iniciada (estado -1)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso modi"),
 *     @OA\Response(response=404, description="Pedido inexistente"),
 *     @OA\Response(response=409, description="Otro usuario editando")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/pedidos/{cod_pedido}/edicion/actividad",
 *     summary="Renovar timestamp de actividad en edicion",
 *     tags={"Pedidos Web"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod_pedido", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Actividad actualizada", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso modi"),
 *     @OA\Response(response=404, description="Pedido inexistente"),
 *     @OA\Response(response=422, description="Pedido no en edicion")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/pedidos/{cod_pedido}/edicion/cancelar",
 *     summary="Cancelar edicion y volver a estado 0",
 *     tags={"Pedidos Web"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod_pedido", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Edicion cancelada", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso modi"),
 *     @OA\Response(response=404, description="Pedido inexistente"),
 *     @OA\Response(response=422, description="Pedido no en edicion")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/presupuestos",
 *     summary="Alta de presupuesto",
 *     tags={"Pedidos Web"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/ComprobanteUpsertRequest")
 *     ),
 *     @OA\Response(response=200, description="Presupuesto creado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeComprobanteGrabar")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso alta"),
 *     @OA\Response(response=422, description="Validacion o regla de negocio")
 * )
 *
 * @OA\Put(
 *     path="/api/v1/presupuestos/{cod_pedido}",
 *     summary="Modificar presupuesto existente",
 *     tags={"Pedidos Web"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod_pedido", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/ComprobanteUpsertRequest")
 *     ),
 *     @OA\Response(response=200, description="Presupuesto modificado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeComprobanteGrabar")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso modi"),
 *     @OA\Response(response=404, description="Presupuesto inexistente"),
 *     @OA\Response(response=422, description="Regla de negocio")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/presupuestos/{cod}/cerrar",
 *     summary="Cerrar presupuesto por rechazo",
 *     tags={"Pedidos Web"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"id_motivo"},
 *             @OA\Property(property="id_motivo", type="integer"),
 *             @OA\Property(property="observacion", type="string", nullable=true)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Presupuesto cerrado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso modi"),
 *     @OA\Response(response=404, description="Presupuesto inexistente"),
 *     @OA\Response(response=422, description="Estado invalido para cierre")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/motivos-cierre",
 *     summary="Motivos de cierre",
 *     tags={"Tratativas"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="tipo_cierre", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="activo", in="query", required=false, @OA\Schema(type="string", example="1")),
 *     @OA\Response(response=200, description="Motivos de cierre", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeMotivosCierre")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/presupuestos/{cod}/tratativas",
 *     summary="Listar tratativas de un presupuesto",
 *     tags={"Tratativas"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Tratativas", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeTratativasListado")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo"),
 *     @OA\Response(response=404, description="Presupuesto inexistente")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/presupuestos/{cod}/tratativas",
 *     summary="Registrar tratativa en presupuesto",
 *     tags={"Tratativas"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"comentario"},
 *             @OA\Property(property="comentario", type="string"),
 *             @OA\Property(property="id_resultado", type="integer", nullable=true),
 *             @OA\Property(property="proxima_fecha", type="string", format="date", nullable=true),
 *             @OA\Property(property="proxima_accion", type="string", nullable=true)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Tratativa creada", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso modi"),
 *     @OA\Response(response=404, description="Presupuesto inexistente"),
 *     @OA\Response(response=422, description="Validacion")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/pedidos-ingresados",
 *     summary="Consulta pedidos ingresados",
 *     tags={"Informes"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
 *     @OA\Parameter(name="page_size", in="query", required=false, @OA\Schema(type="integer", default=20, maximum=1000)),
 *     @OA\Parameter(name="cod_cliente", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeConsultaComprobantes")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/pedidos-pendientes",
 *     summary="Consulta pedidos pendientes",
 *     tags={"Informes"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
 *     @OA\Parameter(name="page_size", in="query", required=false, @OA\Schema(type="integer", default=20, maximum=1000)),
 *     @OA\Parameter(name="cod_cliente", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeConsultaComprobantes")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/presupuestos",
 *     summary="Consulta presupuestos ingresados",
 *     tags={"Informes"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
 *     @OA\Parameter(name="page_size", in="query", required=false, @OA\Schema(type="integer", default=20, maximum=1000)),
 *     @OA\Parameter(name="cod_cliente", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="estado", in="query", required=false, @OA\Schema(type="integer", default=99, description="99=activos; otros=cerrados")),
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeConsultaComprobantes")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/stock",
 *     summary="Consulta stock",
 *     tags={"Informes"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
 *     @OA\Parameter(name="page_size", in="query", required=false, @OA\Schema(type="integer", default=20, maximum=1000)),
 *     @OA\Parameter(name="q", in="query", required=false, @OA\Schema(type="string", description="Filtro código/descripción")),
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeConsultaStock")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/deuda",
 *     summary="Consulta deuda",
 *     tags={"Informes"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
 *     @OA\Parameter(name="page_size", in="query", required=false, @OA\Schema(type="integer", default=20, maximum=1000)),
 *     @OA\Parameter(name="cod_cliente", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeConsultaDeuda")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/cheques",
 *     summary="Consulta cheques",
 *     tags={"Informes"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
 *     @OA\Parameter(name="page_size", in="query", required=false, @OA\Schema(type="integer", default=20, maximum=1000)),
 *     @OA\Parameter(name="cod_cliente", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeConsultaCheques")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/historial-ventas",
 *     summary="Consulta historial de ventas",
 *     tags={"Informes"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
 *     @OA\Parameter(name="page_size", in="query", required=false, @OA\Schema(type="integer", default=20, maximum=1000)),
 *     @OA\Parameter(name="cod_cliente", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeConsultaHistorialVentas")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/detalle-pedidos",
 *     summary="Consulta detalle de pedidos",
 *     tags={"Informes"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
 *     @OA\Parameter(name="page_size", in="query", required=false, @OA\Schema(type="integer", default=20, maximum=1000)),
 *     @OA\Parameter(name="cod_cliente", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="cod_pedido", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="estado", in="query", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="q", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Listado paginado por renglon", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeConsultaDetallePedidos")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta"),
 *     @OA\Response(response=404, description="Cliente no visible")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/integracion/logs",
 *     summary="Integracion logs",
 *     tags={"Framework"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeIntegracionLogs")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/dashboard/operativo",
 *     summary="Dashboard operativo",
 *     tags={"Framework"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Indicadores operativos", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeDashboardOperativo")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso dashboard")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/config/parametros-carga",
 *     summary="Parametros carga",
 *     tags={"Parametros"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Flags segun perfil comercial", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeParametrosCarga")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/config/parametros",
 *     summary="Parametros",
 *     tags={"Parametros"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="programa", in="query", required=false, @OA\Schema(type="string", default="PedidosWeb")),
 *     @OA\Response(response=200, description="Listado informativo de parametros", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeParametrosConsulta")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/clientes/{codCliente}/cabecera-inicial",
 *     summary="Clientes - cabecera inicial",
 *     tags={"Maestros y Tablas"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="codCliente", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Cabecera y catalogos", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeCabeceraInicial")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo"),
 *     @OA\Response(response=404, description="Cliente no visible o inexistente")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/articulos",
 *     summary="Articulos",
 *     tags={"Maestros y Tablas"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="q", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="page_size", in="query", required=false, @OA\Schema(type="integer", maximum=50)),
 *     @OA\Parameter(name="lista_precios", in="query", required=false, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Listado de articulos", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeArticulosCarga")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/presupuestos/{cod_pedido}",
 *     summary="Obtener presupuesto con detalle",
 *     tags={"Pedidos Web"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod_pedido", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Presupuesto", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeComprobanteDetalle")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo"),
 *     @OA\Response(response=404, description="Presupuesto inexistente")
 * )
 */
final class PedidosWebOpenApiPaths
{
}
