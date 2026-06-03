<?php

namespace App\OpenApi;

/**
 * Rutas REST PedidosWeb (SPEC-101-05 / SPEC-101-07).
 *
 * @OA\Tag(name="PedidosWeb", description="Carga, consultas, dashboard operativo e integración")
 *
 * @OA\Post(
 *     path="/api/v1/comprobantes/grabar",
 *     summary="Grabar pedido o presupuesto (contrato unificado)",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"accionGrabacion","cabecera","renglones"},
 *             @OA\Property(property="accionGrabacion", type="string", enum={"pedido","presupuesto"}),
 *             @OA\Property(property="cod_pedido", type="string", nullable=true),
 *             @OA\Property(property="cod_pedido_origen", type="string", nullable=true),
 *             @OA\Property(property="cod_presupuesto_origen", type="string", nullable=true),
 *             @OA\Property(property="cabecera", type="object"),
 *             @OA\Property(property="renglones", type="array", @OA\Items(type="object"))
 *         )
 *     ),
 *     @OA\Response(response=200, description="Comprobante grabado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=422, description="Validacion o regla de negocio")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/comprobantes/copiar",
 *     summary="Copiar comprobante existente a pedido o presupuesto",
 *     tags={"PedidosWeb"},
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
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"cabecera","renglones"},
 *             @OA\Property(property="cabecera", type="object"),
 *             @OA\Property(property="renglones", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="cod_presupuesto_origen", type="string", nullable=true)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Pedido creado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso alta"),
 *     @OA\Response(response=422, description="Validacion o regla de negocio")
 * )
 *
 * @OA\Put(
 *     path="/api/v1/pedidos/{cod_pedido}",
 *     summary="Modificar pedido existente",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod_pedido", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"cabecera","renglones"},
 *             @OA\Property(property="cabecera", type="object"),
 *             @OA\Property(property="renglones", type="array", @OA\Items(type="object"))
 *         )
 *     ),
 *     @OA\Response(response=200, description="Pedido modificado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
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
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod_pedido", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Pedido", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo"),
 *     @OA\Response(response=404, description="Pedido inexistente")
 * )
 *
 * @OA\Delete(
 *     path="/api/v1/pedidos/{cod_pedido}",
 *     summary="Eliminar pedido en estado 0",
 *     tags={"PedidosWeb"},
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
 *     tags={"PedidosWeb"},
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
 *     tags={"PedidosWeb"},
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
 *     tags={"PedidosWeb"},
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
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"cabecera","renglones"},
 *             @OA\Property(property="cabecera", type="object"),
 *             @OA\Property(property="renglones", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="cod_pedido_origen", type="string", nullable=true)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Presupuesto creado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso alta"),
 *     @OA\Response(response=422, description="Validacion o regla de negocio")
 * )
 *
 * @OA\Put(
 *     path="/api/v1/presupuestos/{cod_pedido}",
 *     summary="Modificar presupuesto existente",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod_pedido", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"cabecera","renglones"},
 *             @OA\Property(property="cabecera", type="object"),
 *             @OA\Property(property="renglones", type="array", @OA\Items(type="object"))
 *         )
 *     ),
 *     @OA\Response(response=200, description="Presupuesto modificado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
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
 *     tags={"PedidosWeb"},
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
 *     summary="Catalogo de motivos de cierre de presupuesto",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="tipo_cierre", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="activo", in="query", required=false, @OA\Schema(type="string", example="1")),
 *     @OA\Response(response=200, description="Motivos de cierre", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/presupuestos/{cod}/tratativas",
 *     summary="Listar tratativas de un presupuesto",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="cod", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Tratativas", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo"),
 *     @OA\Response(response=404, description="Presupuesto inexistente")
 * )
 *
 * @OA\Post(
 *     path="/api/v1/presupuestos/{cod}/tratativas",
 *     summary="Registrar tratativa en presupuesto",
 *     tags={"PedidosWeb"},
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
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/pedidos-pendientes",
 *     summary="Consulta pedidos pendientes",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/presupuestos",
 *     summary="Consulta presupuestos",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/stock",
 *     summary="Consulta stock de articulos",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/deuda",
 *     summary="Consulta deuda del cliente",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/cheques",
 *     summary="Consulta cheques del cliente",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/historial-ventas",
 *     summary="Historial de ventas del cliente",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/consultas/detalle-pedidos",
 *     summary="Consulta detalle de pedidos (cabecera + renglon)",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Listado paginado por renglon", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso consulta"),
 *     @OA\Response(response=404, description="Cliente no visible")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/integracion/logs",
 *     summary="Logs de integracion (mail, etc.)",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Listado paginado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/dashboard/operativo",
 *     summary="Dashboard operativo PedidosWeb",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Indicadores operativos", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso dashboard")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/config/parametros-carga",
 *     summary="Parametros Modifica* y flags UI para pantalla de carga",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Flags segun perfil comercial", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/config/parametros",
 *     summary="Consulta de parametros generales (solo lectura)",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="programa", in="query", required=false, @OA\Schema(type="string", default="PedidosWeb")),
 *     @OA\Response(response=200, description="Listado informativo de parametros", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/clientes/{codCliente}/cabecera-inicial",
 *     summary="Inicializar cabecera de comprobante desde cliente (HU-101-005)",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="codCliente", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Cabecera y catalogos", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo"),
 *     @OA\Response(response=404, description="Cliente no visible o inexistente")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/articulos",
 *     summary="Autocompletar articulos para carga de comprobantes",
 *     tags={"PedidosWeb"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="q", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="page_size", in="query", required=false, @OA\Schema(type="integer", maximum=50)),
 *     @OA\Parameter(name="lista_precios", in="query", required=false, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Listado de articulos", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso repo")
 * )
 */
final class PedidosWebOpenApiPaths
{
}
