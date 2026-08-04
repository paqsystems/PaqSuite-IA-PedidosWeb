<?php

namespace App\OpenApi;

/**
 * Schemas OpenAPI — GET tipados (Parametros, Maestros, Framework, Tratativas, PedidosWeb show, Excel, Chat, Pivots).
 *
 * @OA\Schema(
 *     schema="ParametrosCargaResultado",
 *     type="object",
 *     @OA\Property(property="modificaPrecio", type="boolean", example=true),
 *     @OA\Property(property="modificaBonArt", type="boolean", example=true),
 *     @OA\Property(property="modificaBonCli", type="boolean", example=true),
 *     @OA\Property(property="modificaListaPrec", type="boolean", example=true),
 *     @OA\Property(property="modificaCondVta", type="boolean", example=true),
 *     @OA\Property(property="modificaDirEntr", type="boolean", example=true),
 *     @OA\Property(property="modificaExpreso", type="boolean", example=true),
 *     @OA\Property(property="clienteLeyenda1", type="boolean", example=false),
 *     @OA\Property(property="clienteLeyenda2", type="boolean", example=false),
 *     @OA\Property(property="clienteLeyenda3", type="boolean", example=false),
 *     @OA\Property(property="clienteLeyenda4", type="boolean", example=false),
 *     @OA\Property(property="clienteLeyenda5", type="boolean", example=false),
 *     @OA\Property(property="functionalProfile", type="string", example="vendedor"),
 *     @OA\Property(property="codMotivoCierreExitoso", type="string", example="1"),
 *     @OA\Property(property="noEliminaPedido", type="boolean", example=false),
 *     @OA\Property(property="noModificaPedido", type="boolean", example=false),
 *     @OA\Property(property="cargaRecurrente", type="boolean", example=false),
 *     @OA\Property(property="cargaUnidadesVenta", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeParametrosCarga",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ParametrosCargaResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ParametroConsultaItem",
 *     type="object",
 *     @OA\Property(property="clave", type="string", example="CargaUnidadesVenta"),
 *     @OA\Property(property="caption", type="string", example="Carga por unidades de venta"),
 *     @OA\Property(property="tooltip", type="string", example="Si está activo…"),
 *     @OA\Property(property="tipoValor", type="string", example="B"),
 *     @OA\Property(property="valorMostrado", type="string", example="No")
 * )
 *
 * @OA\Schema(
 *     schema="ParametrosConsultaResultado",
 *     type="object",
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ParametroConsultaItem")),
 *     @OA\Property(property="programa", type="string", example="PedidosWeb"),
 *     @OA\Property(property="total", type="integer", example=25)
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeParametrosConsulta",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ParametrosConsultaResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="CabeceraCatalogosResultado",
 *     type="object",
 *     @OA\Property(property="condicionesVenta", type="array", @OA\Items(ref="#/components/schemas/CatalogoCondicionVentaItem")),
 *     @OA\Property(property="transportes", type="array", @OA\Items(ref="#/components/schemas/CatalogoTransporteItem")),
 *     @OA\Property(property="listasPrecios", type="array", @OA\Items(ref="#/components/schemas/CatalogoListaPreciosItem")),
 *     @OA\Property(property="direccionesEntrega", type="array", @OA\Items(ref="#/components/schemas/CatalogoDireccionEntregaItem")),
 *     @OA\Property(property="perfiles", type="array", @OA\Items(ref="#/components/schemas/CatalogoPerfilItem"))
 * )
 *
 * @OA\Schema(
 *     schema="CabeceraInicialCabecera",
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ComprobanteCabeceraRequest"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="razon_soci", type="string", example="Cliente MVP SA"),
 *             @OA\Property(property="vendedor_nombre", type="string", example="Vendedor Uno"),
 *             @OA\Property(property="direccion_entrega", type="string", example="Av. Siempre Viva 742"),
 *             @OA\Property(property="lista_precios_descripcion", type="string", example="Lista general")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="CabeceraInicialResultado",
 *     type="object",
 *     required={"cabecera","catalogos"},
 *     @OA\Property(property="cabecera", ref="#/components/schemas/CabeceraInicialCabecera"),
 *     @OA\Property(property="catalogos", ref="#/components/schemas/CabeceraCatalogosResultado")
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeCabeceraInicial",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/CabeceraInicialResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ArticuloCargaItem",
 *     type="object",
 *     @OA\Property(property="codArticulo", type="string", example="ATS 0500"),
 *     @OA\Property(property="descripcion", type="string", example="ALMENDRA TOSTADA"),
 *     @OA\Property(property="porcIva", type="number", format="float", example=21),
 *     @OA\Property(property="bonificacion", type="number", format="float", example=0),
 *     @OA\Property(property="precio", type="number", format="float", example=123.5),
 *     @OA\Property(property="equivalenciaVentas", type="number", format="float", example=1),
 *     @OA\Property(property="disponibleNeto", type="number", format="float", nullable=true, example=85.5),
 *     @OA\Property(property="disponibleNetoBase", type="number", format="float", nullable=true, example=172)
 * )
 *
 * @OA\Schema(
 *     schema="ArticulosCargaResultado",
 *     type="object",
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ArticuloCargaItem"))
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeArticulosCarga",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ArticulosCargaResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ComprobanteDetalleLinea",
 *     type="object",
 *     @OA\Property(property="renglon", type="integer", example=1),
 *     @OA\Property(property="cod_articulo", type="string", example="ATS 0500"),
 *     @OA\Property(property="descripcion_articulo", type="string", example="ALMENDRA TOSTADA"),
 *     @OA\Property(property="cantidad", type="number", format="float", example=10),
 *     @OA\Property(property="cantidad_venta", type="number", format="float", example=10),
 *     @OA\Property(property="porc_bonif", type="number", format="float", example=3),
 *     @OA\Property(property="precio", type="number", format="float", example=123.5),
 *     @OA\Property(property="precio_neto", type="number", format="float", example=119.8),
 *     @OA\Property(property="porc_iva", type="number", format="float", example=21),
 *     @OA\Property(property="importe_total", type="number", format="float", example=1449.58)
 * )
 *
 * @OA\Schema(
 *     schema="ComprobanteDetalleCabecera",
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/CabeceraInicialCabecera"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="cod_pedido", type="string", example="A1B2C3D4-E5F6-7890-ABCD-EF1234567890"),
 *             @OA\Property(property="estado", type="integer", example=0),
 *             @OA\Property(property="fecha", type="string", format="date-time", nullable=true),
 *             @OA\Property(property="nro_visible", type="integer", example=1234),
 *             @OA\Property(property="total", type="number", format="float", example=1500.25),
 *             @OA\Property(property="total_iva", type="number", format="float", example=315.05)
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ComprobanteDetalleResultado",
 *     type="object",
 *     required={"cabecera","catalogos","detalle"},
 *     @OA\Property(property="cabecera", ref="#/components/schemas/ComprobanteDetalleCabecera"),
 *     @OA\Property(property="catalogos", ref="#/components/schemas/CabeceraCatalogosResultado"),
 *     @OA\Property(property="detalle", type="array", @OA\Items(ref="#/components/schemas/ComprobanteDetalleLinea"))
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeComprobanteDetalle",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ComprobanteDetalleResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="MotivoCierreItem",
 *     type="object",
 *     @OA\Property(property="id_motivo", type="integer", example=3),
 *     @OA\Property(property="tipo_cierre", type="string", example="rechazo"),
 *     @OA\Property(property="descripcion", type="string", example="Precio"),
 *     @OA\Property(property="activo", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="MotivosCierreResultado",
 *     type="object",
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/MotivoCierreItem"))
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeMotivosCierre",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/MotivosCierreResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="TratativaItem",
 *     type="object",
 *     @OA\Property(property="id_tratativa", type="integer", example=10),
 *     @OA\Property(property="fecha_hora", type="string", format="date-time"),
 *     @OA\Property(property="cod_usuario_web", type="string", example="vendedor.mvp"),
 *     @OA\Property(property="comentario", type="string", example="Llamar el viernes"),
 *     @OA\Property(property="id_resultado", type="integer", nullable=true, example=1),
 *     @OA\Property(property="resultado", type="string", nullable=true, example="Pendiente"),
 *     @OA\Property(property="proxima_fecha", type="string", format="date", nullable=true),
 *     @OA\Property(property="proxima_accion", type="string", nullable=true, example="Llamada")
 * )
 *
 * @OA\Schema(
 *     schema="TratativasListadoResultado",
 *     type="object",
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/TratativaItem"))
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeTratativasListado",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/TratativasListadoResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionLogItem",
 *     type="object",
 *     @OA\Property(property="id_log", type="integer", example=100),
 *     @OA\Property(property="fecha", type="string", format="date-time"),
 *     @OA\Property(property="tipo", type="string", example="mail"),
 *     @OA\Property(property="severidad", type="string", example="info"),
 *     @OA\Property(property="origen", type="string", example="comprobante"),
 *     @OA\Property(property="mensaje", type="string", example="Mail enviado"),
 *     @OA\Property(property="procesado", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionLogsResultado",
 *     type="object",
 *     required={"items","page","page_size","total","total_pages","metadata"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionLogItem")),
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="page_size", type="integer", example=20),
 *     @OA\Property(property="total", type="integer", example=5),
 *     @OA\Property(property="total_pages", type="integer", example=1),
 *     @OA\Property(property="metadata", ref="#/components/schemas/ConsultaMetadata")
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeIntegracionLogs",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/IntegracionLogsResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="DashboardOperativoKpi",
 *     type="object",
 *     @OA\Property(property="cantidad", type="integer", example=3),
 *     @OA\Property(property="importe", type="number", format="float", example=15000.5),
 *     @OA\Property(property="unidades", type="number", format="float", example=120)
 * )
 *
 * @OA\Schema(
 *     schema="DashboardOperativoTopCliente",
 *     type="object",
 *     nullable=true,
 *     @OA\Property(property="cod_client", type="string", example="CLIMVP001"),
 *     @OA\Property(property="razon_social", type="string", example="Cliente MVP SA"),
 *     @OA\Property(property="importe", type="number", format="float", example=5000)
 * )
 *
 * @OA\Schema(
 *     schema="DashboardOperativoResultado",
 *     type="object",
 *     @OA\Property(property="moneda", type="object",
 *         @OA\Property(property="simbolo", type="string", example="$"),
 *         @OA\Property(property="codigo", type="string", example="ARS")
 *     ),
 *     @OA\Property(property="presupuestosActivos", ref="#/components/schemas/DashboardOperativoKpi"),
 *     @OA\Property(property="pedidosIngresados", ref="#/components/schemas/DashboardOperativoKpi"),
 *     @OA\Property(property="pedidosPendientes", ref="#/components/schemas/DashboardOperativoKpi"),
 *     @OA\Property(property="topClientePresupuestos", ref="#/components/schemas/DashboardOperativoTopCliente"),
 *     @OA\Property(property="topClientePedidosIngresados", ref="#/components/schemas/DashboardOperativoTopCliente"),
 *     @OA\Property(property="fechaCalculo", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeDashboardOperativo",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/DashboardOperativoResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ExcelImportHistorialItem",
 *     type="object",
 *     @OA\Property(property="guidImportacion", type="string", format="uuid"),
 *     @OA\Property(property="codigoProceso", type="string", example="PEDIDO_MASIVO"),
 *     @OA\Property(property="nombreProceso", type="string", example="Importación masiva"),
 *     @OA\Property(property="usuarioEjecucion", type="string", example="vendedor.mvp"),
 *     @OA\Property(property="archivoOriginalNombre", type="string", example="pedidos.xlsx"),
 *     @OA\Property(property="hojaSeleccionada", type="string", example="Hoja1"),
 *     @OA\Property(property="estadoImportacion", type="string", example="completado"),
 *     @OA\Property(property="fechaInicio", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="fechaFin", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="cantidadFilasLeidas", type="integer", example=100),
 *     @OA\Property(property="cantidadFilasValidas", type="integer", example=90),
 *     @OA\Property(property="cantidadFilasConError", type="integer", example=10),
 *     @OA\Property(property="cantidadFilasProcesadas", type="integer", example=90),
 *     @OA\Property(property="cantidadFilasDescartadas", type="integer", example=0)
 * )
 *
 * @OA\Schema(
 *     schema="ExcelImportHistorialResultado",
 *     type="object",
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ExcelImportHistorialItem")),
 *     @OA\Property(property="total", type="integer", example=12),
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="pageSize", type="integer", example=20)
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeExcelImportHistorial",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ExcelImportHistorialResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ExcelImportLoteDetalleResultado",
 *     type="object",
 *     @OA\Property(property="guidImportacion", type="string", format="uuid"),
 *     @OA\Property(property="codigoProceso", type="string", example="PEDIDO_INDIVIDUAL"),
 *     @OA\Property(property="nombreProceso", type="string", example="Pedido individual"),
 *     @OA\Property(property="estadoImportacion", type="string", example="validado"),
 *     @OA\Property(property="esAsincronica", type="boolean", example=false),
 *     @OA\Property(property="archivoOriginalNombre", type="string", example="pedido.xlsx"),
 *     @OA\Property(property="hojaSeleccionada", type="string", example="Hoja1"),
 *     @OA\Property(property="cantidadFilasLeidas", type="integer", example=20),
 *     @OA\Property(property="cantidadFilasValidas", type="integer", example=18),
 *     @OA\Property(property="cantidadFilasConError", type="integer", example=2),
 *     @OA\Property(property="cantidadFilasProcesadas", type="integer", example=0),
 *     @OA\Property(property="cantidadFilasDescartadas", type="integer", example=0),
 *     @OA\Property(property="permiteProcesamientoParcial", type="boolean", example=true),
 *     @OA\Property(property="permiteSoloValidar", type="boolean", example=true),
 *     @OA\Property(property="puedeCancelar", type="boolean", example=true),
 *     @OA\Property(property="mensajeResultado", type="string", nullable=true),
 *     @OA\Property(property="fechaInicio", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="fechaFin", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeExcelImportLoteDetalle",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ExcelImportLoteDetalleResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ExcelImportProcesoMetadataResultado",
 *     type="object",
 *     @OA\Property(property="codigoProceso", type="string", example="PEDIDO_MASIVO"),
 *     @OA\Property(property="nombreProceso", type="string", example="Importación masiva"),
 *     @OA\Property(property="generaPlantilla", type="boolean", example=true),
 *     @OA\Property(property="permiteProcesamientoParcial", type="boolean", example=true),
 *     @OA\Property(property="permiteSoloValidar", type="boolean", example=true),
 *     @OA\Property(property="mantenerEspaciosEnBlancoDefault", type="boolean", example=false),
 *     @OA\Property(property="mantenerCaracteresEspecialesDefault", type="boolean", example=false),
 *     @OA\Property(property="procedimientoHost", type="string", example="pw_importacionmasiva")
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeExcelImportProcesoMetadata",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ExcelImportProcesoMetadataResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ChatAssistantConfigurationsListResultado",
 *     type="object",
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ChatAssistantConfigurationResultado")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeChatAssistantConfigurationsList",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ChatAssistantConfigurationsListResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="PivotCampoMetadata",
 *     type="object",
 *     @OA\Property(property="campoId", type="string", example="importe"),
 *     @OA\Property(property="dataField", type="string", example="importe"),
 *     @OA\Property(property="caption", type="string", example="Importe"),
 *     @OA\Property(property="tipoDato", type="string", example="number"),
 *     @OA\Property(property="rolCampo", type="string", example="valor"),
 *     @OA\Property(property="rolesPermitidos", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="agregacionDefault", type="string", nullable=true, example="sum"),
 *     @OA\Property(property="agregacionesPermitidas", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="formato", type="string", nullable=true, example="#,##0.00")
 * )
 *
 * @OA\Schema(
 *     schema="PivotMetadataResultado",
 *     type="object",
 *     @OA\Property(property="consultaId", type="string", example="ventas_mensuales"),
 *     @OA\Property(property="versionDefinicion", type="integer", example=1),
 *     @OA\Property(property="pivotHabilitado", type="boolean", example=true),
 *     @OA\Property(property="admiteDrilldown", type="boolean", example=false),
 *     @OA\Property(property="configuracionGeneral", type="object"),
 *     @OA\Property(property="pivotBase", type="object"),
 *     @OA\Property(property="campos", type="array", @OA\Items(ref="#/components/schemas/PivotCampoMetadata")),
 *     @OA\Property(property="filtrosGenerales", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="restricciones", type="object"),
 *     @OA\Property(property="exportacion", type="object"),
 *     @OA\Property(property="persistencia", type="object")
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopePivotMetadata",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/PivotMetadataResultado"))
 *     }
 * )
 */
final class OpenApiGetsSchemas
{
}
