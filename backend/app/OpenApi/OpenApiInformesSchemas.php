<?php

namespace App\OpenApi;

/**
 * Schemas OpenAPI — tag Informes (consultas paginadas).
 *
 * @OA\Schema(
 *     schema="ConsultaMetadata",
 *     type="object",
 *     @OA\Property(property="fecha_proceso", type="string", nullable=true, example="2026-08-03 18:00:00"),
 *     @OA\Property(property="dias_ventas_detalladas", type="integer", nullable=true, example=90, description="Solo historial de ventas")
 * )
 *
 * @OA\Schema(
 *     schema="ConsultaPresupuestoCierre",
 *     type="object",
 *     @OA\Property(property="tipoCierre", type="string", example="rechazo"),
 *     @OA\Property(property="idMotivo", type="integer", nullable=true, example=3),
 *     @OA\Property(property="motivoDescripcion", type="string", example="Precio"),
 *     @OA\Property(property="fechaCierre", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="codPedidoGenerado", type="string", nullable=true),
 *     @OA\Property(property="observacion", type="string", example="")
 * )
 *
 * @OA\Schema(
 *     schema="ConsultaComprobanteItem",
 *     type="object",
 *     description="Fila de consulta de cabecera (pedidos ingresados/pendientes; en presupuestos la clave es codPresupuesto)",
 *     @OA\Property(property="codPedido", type="string", example="A1B2C3D4-E5F6-7890-ABCD-EF1234567890"),
 *     @OA\Property(property="codPresupuesto", type="string", nullable=true, example=null),
 *     @OA\Property(property="codCliente", type="string", example="CLIMVP001"),
 *     @OA\Property(property="razonSocial", type="string", example="Cliente MVP SA"),
 *     @OA\Property(property="nombreFantasia", type="string", example="Cliente MVP"),
 *     @OA\Property(property="fecha", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="nivel", type="integer", nullable=true, example=0),
 *     @OA\Property(property="observaciones", type="string", example=""),
 *     @OA\Property(property="incluyeIva", type="boolean", example=false),
 *     @OA\Property(property="moneda", type="integer", example=1),
 *     @OA\Property(property="estado", type="integer", example=0),
 *     @OA\Property(property="fechaModif", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="total", type="number", format="float", example=1500.25),
 *     @OA\Property(property="totalIva", type="number", format="float", example=315.05),
 *     @OA\Property(property="leyenda1", type="string", example=""),
 *     @OA\Property(property="leyenda2", type="string", example=""),
 *     @OA\Property(property="leyenda3", type="string", example=""),
 *     @OA\Property(property="leyenda4", type="string", example=""),
 *     @OA\Property(property="leyenda5", type="string", example=""),
 *     @OA\Property(property="descuento", type="number", format="float", example=0),
 *     @OA\Property(property="bonif1", type="number", format="float", example=0),
 *     @OA\Property(property="bonif2", type="number", format="float", example=0),
 *     @OA\Property(property="bonif3", type="number", format="float", example=0),
 *     @OA\Property(property="codPerfil", type="string", example="P01"),
 *     @OA\Property(property="perfilDescripcion", type="string", example="Estándar"),
 *     @OA\Property(property="codVended", type="string", example="VEN01"),
 *     @OA\Property(property="vendedorDescripcion", type="string", example="Vendedor Uno"),
 *     @OA\Property(property="codCondvta", type="integer", nullable=true, example=1),
 *     @OA\Property(property="condicionVentaDescripcion", type="string", example="Contado"),
 *     @OA\Property(property="idDe", type="integer", nullable=true, example=10),
 *     @OA\Property(property="direccionEntregaDescripcion", type="string", example="Av. Siempre Viva 742"),
 *     @OA\Property(property="codTranspor", type="string", example="TR01"),
 *     @OA\Property(property="transporteDescripcion", type="string", example="Retiro"),
 *     @OA\Property(property="listaPrecios", type="integer", nullable=true, example=1),
 *     @OA\Property(property="listaPreciosDescripcion", type="string", example="Lista general"),
 *     @OA\Property(property="expreso", type="string", example=""),
 *     @OA\Property(property="expresoDire", type="string", example=""),
 *     @OA\Property(property="fechaEntrega", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="usuarioCreacion", type="string", example="vendedor.mvp"),
 *     @OA\Property(property="fechaCreacion", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="usuarioModificacion", type="string", example=""),
 *     @OA\Property(property="fechahoraInicioProceso", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="fechahoraUltimaActividad", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="numeroVisible", type="integer", example=1234),
 *     @OA\Property(property="guidSufijo", type="string", example="567890"),
 *     @OA\Property(property="puedeEditar", type="boolean", example=true),
 *     @OA\Property(property="puedeEliminar", type="boolean", example=true),
 *     @OA\Property(property="puedeCopiar", type="boolean", example=true),
 *     @OA\Property(property="puedeConvertir", type="boolean", nullable=true, example=true, description="Solo presupuestos"),
 *     @OA\Property(property="puedeCerrar", type="boolean", nullable=true, example=true, description="Solo presupuestos"),
 *     @OA\Property(property="cierre", ref="#/components/schemas/ConsultaPresupuestoCierre", nullable=true, description="Solo presupuestos cerrados")
 * )
 *
 * @OA\Schema(
 *     schema="ConsultaStockItem",
 *     type="object",
 *     @OA\Property(property="codArticulo", type="string", example="ATS 0500"),
 *     @OA\Property(property="descripcion", type="string", example="ALMENDRA TOSTADA"),
 *     @OA\Property(property="stock", type="number", format="float", example=100.5),
 *     @OA\Property(property="comprometido", type="number", format="float", example=10),
 *     @OA\Property(property="comprometidoWeb", type="number", format="float", example=5),
 *     @OA\Property(property="disponibleNeto", type="number", format="float", example=85.5),
 *     @OA\Property(property="codBase", type="string", nullable=true, example="ATS"),
 *     @OA\Property(property="stockBase", type="number", format="float", nullable=true, example=200),
 *     @OA\Property(property="comprometidoBase", type="number", format="float", nullable=true, example=20),
 *     @OA\Property(property="comprometidoBaseWeb", type="number", format="float", nullable=true, example=8),
 *     @OA\Property(property="disponibleNetoBase", type="number", format="float", nullable=true, example=172)
 * )
 *
 * @OA\Schema(
 *     schema="ConsultaDeudaItem",
 *     type="object",
 *     @OA\Property(property="codCliente", type="string", example="CLIMVP001"),
 *     @OA\Property(property="razonSocial", type="string", example="Cliente MVP SA"),
 *     @OA\Property(property="tipo", type="string", example="FC"),
 *     @OA\Property(property="numero", type="string", example="0001-00001234"),
 *     @OA\Property(property="fecha", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="vencimiento", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="saldo", type="number", format="float", example=1500.25)
 * )
 *
 * @OA\Schema(
 *     schema="ConsultaChequeItem",
 *     type="object",
 *     @OA\Property(property="interno", type="string", example="1001"),
 *     @OA\Property(property="numero", type="string", example="456789"),
 *     @OA\Property(property="codCliente", type="string", example="CLIMVP001"),
 *     @OA\Property(property="nombreCliente", type="string", example="Cliente MVP"),
 *     @OA\Property(property="banco", type="string", example="Galicia"),
 *     @OA\Property(property="fecha", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="importe", type="number", format="float", example=25000.5),
 *     @OA\Property(property="origen", type="string", example="Cartera"),
 *     @OA\Property(property="estado", type="string", example="En cartera")
 * )
 *
 * @OA\Schema(
 *     schema="ConsultaHistorialVentaItem",
 *     type="object",
 *     @OA\Property(property="codCliente", type="string", example="CLIMVP001"),
 *     @OA\Property(property="razonSocial", type="string", example="Cliente MVP SA"),
 *     @OA\Property(property="nRemito", type="string", example="R-001"),
 *     @OA\Property(property="tipo", type="string", example="FC"),
 *     @OA\Property(property="numero", type="string", example="0001-00005555"),
 *     @OA\Property(property="fechaEmision", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="condVta", type="integer", nullable=true, example=1),
 *     @OA\Property(property="porcDesc", type="number", format="float", example=0),
 *     @OA\Property(property="cotiz", type="number", format="float", example=1),
 *     @OA\Property(property="moneda", type="string", example="ARS"),
 *     @OA\Property(property="totalComp", type="number", format="float", example=10000),
 *     @OA\Property(property="codTransp", type="string", example="TR01"),
 *     @OA\Property(property="nomTransp", type="string", example="Retiro"),
 *     @OA\Property(property="codArticulo", type="string", example="ATS 0500"),
 *     @OA\Property(property="descripcion", type="string", example="ALMENDRA TOSTADA"),
 *     @OA\Property(property="codDep", type="string", example="01"),
 *     @OA\Property(property="um", type="string", example="KG"),
 *     @OA\Property(property="cantidad", type="number", format="float", example=10),
 *     @OA\Property(property="precio", type="number", format="float", example=123.5),
 *     @OA\Property(property="totSinImp", type="number", format="float", example=1235),
 *     @OA\Property(property="nCompRem", type="string", example=""),
 *     @OA\Property(property="cantRem", type="number", format="float", example=0),
 *     @OA\Property(property="fechaRem", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ConsultaDetallePedidoItem",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ConsultaComprobanteItem"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="renglon", type="integer", example=1),
 *             @OA\Property(property="codArticulo", type="string", example="ATS 0500"),
 *             @OA\Property(property="descripcionArticulo", type="string", example="ALMENDRA TOSTADA"),
 *             @OA\Property(property="cantidad", type="number", format="float", example=10),
 *             @OA\Property(property="cantidadVenta", type="number", format="float", example=10),
 *             @OA\Property(property="porcBonif", type="number", format="float", example=3),
 *             @OA\Property(property="precioLista", type="number", format="float", example=130),
 *             @OA\Property(property="precioNeto", type="number", format="float", example=126.1),
 *             @OA\Property(property="importeBruto", type="number", format="float", example=1300),
 *             @OA\Property(property="importeNeto", type="number", format="float", example=1261),
 *             @OA\Property(property="ivaNeto", type="number", format="float", example=264.81),
 *             @OA\Property(property="importeNetoConIva", type="number", format="float", example=1525.81)
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ConsultaListadoComprobantesResultado",
 *     type="object",
 *     required={"items","page","page_size","total","total_pages","metadata"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ConsultaComprobanteItem")),
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="page_size", type="integer", example=20),
 *     @OA\Property(property="total", type="integer", example=42),
 *     @OA\Property(property="total_pages", type="integer", example=3),
 *     @OA\Property(property="metadata", ref="#/components/schemas/ConsultaMetadata")
 * )
 *
 * @OA\Schema(
 *     schema="ConsultaListadoStockResultado",
 *     type="object",
 *     required={"items","page","page_size","total","total_pages","metadata"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ConsultaStockItem")),
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="page_size", type="integer", example=20),
 *     @OA\Property(property="total", type="integer", example=100),
 *     @OA\Property(property="total_pages", type="integer", example=5),
 *     @OA\Property(property="metadata", ref="#/components/schemas/ConsultaMetadata")
 * )
 *
 * @OA\Schema(
 *     schema="ConsultaListadoDeudaResultado",
 *     type="object",
 *     required={"items","page","page_size","total","total_pages","metadata"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ConsultaDeudaItem")),
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="page_size", type="integer", example=20),
 *     @OA\Property(property="total", type="integer", example=10),
 *     @OA\Property(property="total_pages", type="integer", example=1),
 *     @OA\Property(property="metadata", ref="#/components/schemas/ConsultaMetadata")
 * )
 *
 * @OA\Schema(
 *     schema="ConsultaListadoChequesResultado",
 *     type="object",
 *     required={"items","page","page_size","total","total_pages","metadata"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ConsultaChequeItem")),
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="page_size", type="integer", example=20),
 *     @OA\Property(property="total", type="integer", example=8),
 *     @OA\Property(property="total_pages", type="integer", example=1),
 *     @OA\Property(property="metadata", ref="#/components/schemas/ConsultaMetadata")
 * )
 *
 * @OA\Schema(
 *     schema="ConsultaListadoHistorialVentasResultado",
 *     type="object",
 *     required={"items","page","page_size","total","total_pages","metadata"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ConsultaHistorialVentaItem")),
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="page_size", type="integer", example=20),
 *     @OA\Property(property="total", type="integer", example=50),
 *     @OA\Property(property="total_pages", type="integer", example=3),
 *     @OA\Property(property="metadata", ref="#/components/schemas/ConsultaMetadata")
 * )
 *
 * @OA\Schema(
 *     schema="ConsultaListadoDetallePedidosResultado",
 *     type="object",
 *     required={"items","page","page_size","total","total_pages","metadata"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ConsultaDetallePedidoItem")),
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="page_size", type="integer", example=20),
 *     @OA\Property(property="total", type="integer", example=200),
 *     @OA\Property(property="total_pages", type="integer", example=10),
 *     @OA\Property(property="metadata", ref="#/components/schemas/ConsultaMetadata")
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeConsultaComprobantes",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ConsultaListadoComprobantesResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeConsultaStock",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ConsultaListadoStockResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeConsultaDeuda",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ConsultaListadoDeudaResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeConsultaCheques",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ConsultaListadoChequesResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeConsultaHistorialVentas",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ConsultaListadoHistorialVentasResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeConsultaDetallePedidos",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ConsultaListadoDetallePedidosResultado"))
 *     }
 * )
 */
final class OpenApiInformesSchemas
{
}
