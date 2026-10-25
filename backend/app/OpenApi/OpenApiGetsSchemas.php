<?php

namespace App\OpenApi;

/**
 * Schemas OpenAPI — GET tipados (Parametros, Maestros, Framework, Tratativas, PedidosWeb show, Excel, Chat, Pivots).
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroClienteItem",
 *     type="object",
 *     required={"codClient"},
 *     @OA\Property(property="codClient", type="string", example="CL-001"),
 *     @OA\Property(property="nombre", type="string", nullable=true),
 *     @OA\Property(property="fantasia", type="string", nullable=true),
 *     @OA\Property(property="codVended", type="string", nullable=true),
 *     @OA\Property(property="listaPrecios", type="integer", nullable=true),
 *     @OA\Property(property="codCondvta", type="integer", nullable=true),
 *     @OA\Property(property="codTranspor", type="string", nullable=true),
 *     @OA\Property(property="bonificacion", type="number", format="double", nullable=true),
 *     @OA\Property(property="nivel", type="integer", nullable=true),
 *     @OA\Property(property="expreso", type="string", nullable=true),
 *     @OA\Property(property="expresoDire", type="string", nullable=true),
 *     @OA\Property(property="eMail", type="string", nullable=true),
 *     @OA\Property(property="razonSoci", type="string", nullable=true),
 *     @OA\Property(property="leyenda1", type="string", maxLength=60, nullable=true),
 *     @OA\Property(property="leyenda2", type="string", maxLength=60, nullable=true),
 *     @OA\Property(property="leyenda3", type="string", maxLength=60, nullable=true),
 *     @OA\Property(property="leyenda4", type="string", maxLength=60, nullable=true),
 *     @OA\Property(property="leyenda5", type="string", maxLength=60, nullable=true),
 *     @OA\Property(property="codLogin", type="string", nullable=true, description="Ignorado por la API"),
 *     @OA\Property(property="eliminarSiAusente", type="boolean", default=false),
 *     @OA\Property(
 *         property="direcciones",
 *         type="array",
 *         description="Direcciones de entrega del cliente",
 *         @OA\Items(ref="#/components/schemas/IntegracionMaestroDireccionItem")
 *     ),
 *     @OA\Property(
 *         property="contactos",
 *         type="array",
 *         description="Contactos del cliente",
 *         @OA\Items(ref="#/components/schemas/IntegracionMaestroContactoItem")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroDireccionItem",
 *     type="object",
 *     required={"codClient","idDe"},
 *     @OA\Property(property="codClient", type="string", example="CL-001"),
 *     @OA\Property(property="idDe", type="integer", example=1),
 *     @OA\Property(property="codDe", type="string", nullable=true),
 *     @OA\Property(property="direccion", type="string", nullable=true),
 *     @OA\Property(property="localidad", type="string", nullable=true),
 *     @OA\Property(property="cPostal", type="string", nullable=true),
 *     @OA\Property(property="codProvin", type="string", nullable=true),
 *     @OA\Property(property="habitual", oneOf={@OA\Schema(type="boolean"), @OA\Schema(type="string", enum={"S","N"})}, example=true)
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroContactoItem",
 *     type="object",
 *     required={"codClient","codContacto","nombre"},
 *     @OA\Property(property="codClient", type="string", example="CL-001"),
 *     @OA\Property(property="codContacto", type="string", example="C1"),
 *     @OA\Property(property="nombre", type="string"),
 *     @OA\Property(property="telefono", type="string", nullable=true),
 *     @OA\Property(property="mail", type="string", format="email", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroParametroItem",
 *     type="object",
 *     required={"clave"},
 *     description="El valor debe enviarse en la propiedad correspondiente al tipo canónico: B=valorBool, I=valorInt, N=valorDecimal, S=valorString, T=valorText, D=valorDateTime.",
 *     @OA\Property(property="clave", type="string", example="MinutosWeb"),
 *     @OA\Property(property="valorBool", type="boolean", nullable=true),
 *     @OA\Property(property="valorInt", type="integer", nullable=true),
 *     @OA\Property(property="valorDecimal", type="number", format="double", nullable=true),
 *     @OA\Property(property="valorString", type="string", nullable=true),
 *     @OA\Property(property="valorText", type="string", nullable=true),
 *     @OA\Property(property="valorDateTime", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroClientesLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionMaestroLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroClienteItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroDireccionesLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionMaestroLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroDireccionItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroContactosLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionMaestroLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroContactoItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroParametrosLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionMaestroLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroParametroItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroProvinciaItem",
 *     type="object",
 *     required={"codProvin"},
 *     @OA\Property(property="codProvin", type="string", example="B"),
 *     @OA\Property(property="descripcion", type="string", nullable=true, example="Buenos Aires")
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroCondicionVentaItem",
 *     type="object",
 *     required={"codigo"},
 *     @OA\Property(property="codigo", type="integer", example=1),
 *     @OA\Property(property="descripcion", type="string", nullable=true, example="Contado"),
 *     @OA\Property(property="dias", type="integer", nullable=true, example=0)
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroTransporteItem",
 *     type="object",
 *     required={"codigo"},
 *     @OA\Property(property="codigo", type="string", example="TR01"),
 *     @OA\Property(property="descripcion", type="string", nullable=true, example="Retiro en planta")
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroPerfilItem",
 *     type="object",
 *     required={"codPerfil"},
 *     @OA\Property(property="codPerfil", type="string", example="P01"),
 *     @OA\Property(property="descripcion", type="string", nullable=true, example="Perfil estándar")
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroVendedorItem",
 *     type="object",
 *     required={"codVended"},
 *     @OA\Property(property="codVended", type="string", example="VEN01"),
 *     @OA\Property(property="nombre", type="string", nullable=true, example="Vendedor Uno"),
 *     @OA\Property(property="supervisor", type="boolean", nullable=true, example=false),
 *     @OA\Property(property="codLogin", type="string", nullable=true, description="Ignorado por la API")
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroListaPrecioItem",
 *     type="object",
 *     required={"codLista"},
 *     @OA\Property(property="codLista", type="integer", example=1),
 *     @OA\Property(property="descripcion", type="string", nullable=true, example="Lista general"),
 *     @OA\Property(property="moneda", type="integer", nullable=true, example=1),
 *     @OA\Property(property="incluyeIva", type="boolean", nullable=true, example=false),
 *     @OA\Property(property="decimales", type="integer", nullable=true, example=2),
 *     @OA\Property(
 *         property="articulos",
 *         type="array",
 *         description="Precios de los artículos pertenecientes a la lista",
 *         @OA\Items(ref="#/components/schemas/IntegracionMaestroListaPrecioArticuloItem")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroEscalaValorItem",
 *     type="object",
 *     required={"codValor"},
 *     @OA\Property(property="codValor", type="string", example="U"),
 *     @OA\Property(property="descValor", type="string", nullable=true, example="Unidad")
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroEscalaItem",
 *     type="object",
 *     required={"codEscala"},
 *     @OA\Property(property="codEscala", type="string", example="01"),
 *     @OA\Property(property="descripEs", type="string", nullable=true, example="Unidades"),
 *     @OA\Property(property="nroEscala", type="integer", nullable=true, example=1),
 *     @OA\Property(property="valores", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroEscalaValorItem")),
 *     @OA\Property(property="eliminarSiAusente", type="boolean", default=false)
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroArticuloItem",
 *     type="object",
 *     required={"codigo"},
 *     @OA\Property(property="codigo", type="string", example="ART001"),
 *     @OA\Property(property="descripcion", type="string", nullable=true, example="Artículo de prueba"),
 *     @OA\Property(property="bonificacion", type="number", format="double", nullable=true, example=0),
 *     @OA\Property(property="usaEsc", type="boolean", nullable=true, example=false),
 *     @OA\Property(property="base", type="string", nullable=true, example=""),
 *     @OA\Property(property="valor1", type="number", format="double", nullable=true, example=0),
 *     @OA\Property(property="valor2", type="number", format="double", nullable=true, example=0),
 *     @OA\Property(property="porcIva", type="number", format="double", nullable=true, example=21),
 *     @OA\Property(property="equivalenciaVentas", type="number", format="double", nullable=true, example=1),
 *     @OA\Property(property="stockeable", type="boolean", nullable=true, example=true),
 *     @OA\Property(property="especial", type="boolean", nullable=true, example=false),
 *     @OA\Property(property="eliminarSiAusente", type="boolean", default=false)
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroListaPrecioArticuloItem",
 *     type="object",
 *     required={"codArticulo"},
 *     @OA\Property(property="codArticulo", type="string", example="ART001"),
 *     @OA\Property(property="precio", type="number", format="double", nullable=true, example=123.5)
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroDescuentoCantidadItem",
 *     type="object",
 *     required={"codArticu","cantidad"},
 *     @OA\Property(property="codArticu", type="string", example="ART001"),
 *     @OA\Property(property="cantidad", type="number", format="double", example=10),
 *     @OA\Property(property="porcentaje", type="number", format="double", nullable=true, example=5)
 * )

 * @OA\Schema(
 *     schema="IntegracionTransaccionStockItem",
 *     type="object",
 *     required={"codArticulo","stock","comprometido"},
 *     @OA\Property(property="codArticulo", type="string", example="ART001"),
 *     @OA\Property(property="stock", type="number", format="double", example=100.5),
 *     @OA\Property(property="comprometido", type="number", format="double", example=10)
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionTransaccionDeudaItem",
 *     type="object",
 *     required={"codCliente","tComp","nComp","fechaEmis","fechaVto","saldo"},
 *     @OA\Property(property="codCliente", type="string", example="CL-001"),
 *     @OA\Property(property="tComp", type="string", example="FC"),
 *     @OA\Property(property="nComp", type="string", example="0001-00001234"),
 *     @OA\Property(property="fechaEmis", type="string", format="date", example="2026-10-20"),
 *     @OA\Property(property="fechaVto", type="string", format="date", example="2026-11-20"),
 *     @OA\Property(property="saldo", type="number", format="double", example=1500.25)
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionTransaccionChequeItem",
 *     type="object",
 *     required={"interno","numero","codCliente","banco","fecha","importe","origen","estado"},
 *     @OA\Property(property="interno", type="string", example="1001"),
 *     @OA\Property(property="numero", type="string", example="456789"),
 *     @OA\Property(property="codCliente", type="string", example="CL-001"),
 *     @OA\Property(property="banco", type="string", example="Galicia"),
 *     @OA\Property(property="fecha", type="string", format="date", example="2026-10-20"),
 *     @OA\Property(property="importe", type="number", format="double", example=25000.5),
 *     @OA\Property(property="origen", type="string", example="Cartera"),
 *     @OA\Property(property="estado", type="string", example="En cartera")
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionTransaccionHistorialVentaItem",
 *     type="object",
 *     required={"idGva53","codCliente","tipo","numero","fechaEmision"},
 *     @OA\Property(property="idGva53", type="integer", example=1001),
 *     @OA\Property(property="codCliente", type="string", example="CL-001"),
 *     @OA\Property(property="tipo", type="string", example="FC"),
 *     @OA\Property(property="numero", type="string", example="0001-00001234"),
 *     @OA\Property(property="fechaEmision", type="string", format="date", example="2026-10-20"),
 *     @OA\Property(property="codArticu", type="string", nullable=true, example="ART001"),
 *     @OA\Property(property="nRemito", type="string", nullable=true, example="R-001"),
 *     @OA\Property(property="condVta", type="integer", nullable=true, example=1),
 *     @OA\Property(property="porcDesc", type="number", format="double", nullable=true, example=0),
 *     @OA\Property(property="cotiz", type="number", format="double", nullable=true, example=1),
 *     @OA\Property(property="moneda", type="string", nullable=true, example="ARS"),
 *     @OA\Property(property="totalComp", type="number", format="double", nullable=true, example=10000),
 *     @OA\Property(property="codTransp", type="string", nullable=true, example="TR01"),
 *     @OA\Property(property="nomTransp", type="string", nullable=true, example="Retiro"),
 *     @OA\Property(property="descripcion", type="string", nullable=true, example="Artículo de prueba"),
 *     @OA\Property(property="codDep", type="string", nullable=true, example="01"),
 *     @OA\Property(property="um", type="string", nullable=true, example="UN"),
 *     @OA\Property(property="cantidad", type="number", format="double", nullable=true, example=10),
 *     @OA\Property(property="precio", type="number", format="double", nullable=true, example=123.5),
 *     @OA\Property(property="totSinImp", type="number", format="double", nullable=true, example=1235),
 *     @OA\Property(property="nCompRem", type="string", nullable=true, example=""),
 *     @OA\Property(property="cantRem", type="number", format="double", nullable=true, example=0),
 *     @OA\Property(property="fechaRem", type="string", format="date", nullable=true, example="2026-10-21")
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroProvinciasLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionMaestroLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroProvinciaItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroCondventaLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionMaestroLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroCondicionVentaItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroTransportesLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionMaestroLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroTransporteItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroPerfilesLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionMaestroLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroPerfilItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroVendedoresLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionMaestroLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroVendedorItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroListapreciosLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionMaestroLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroListaPrecioItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroEscalasLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionMaestroLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroEscalaItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroArticulosLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionMaestroLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroArticuloItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroListapreciosArticulosLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionMaestroLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroListaPrecioArticuloItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroDescuentocantidadLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionMaestroLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroDescuentoCantidadItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionTransaccionStockLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionTransaccionLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionTransaccionStockItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionTransaccionDeudaLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionTransaccionLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionTransaccionDeudaItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionTransaccionChequesLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionTransaccionLoteRequest"),
 *         @OA\Schema(type="object", @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionTransaccionChequeItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionTransaccionHistorialVentasLoteRequest",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/IntegracionTransaccionLoteRequest"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="modo", type="string", enum={"delta"}, example="delta"),
 *             @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/IntegracionTransaccionHistorialVentaItem"))
 *         )
 *     }
 * )
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
 *     @OA\Property(property="equivalencia_ventas", type="number", format="float", example=5),
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
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroLoteRequest",
 *     type="object",
 *     required={"modo","fechaProceso","items"},
 *     @OA\Property(property="modo", type="string", enum={"delta","snapshot"}, example="delta"),
 *     @OA\Property(property="fechaProceso", type="string", format="date-time", example="2026-10-21T12:00:00-03:00"),
 *     @OA\Property(property="origen", type="string", maxLength=50, example="tango"),
 *     @OA\Property(property="items", type="array", @OA\Items(type="object"))
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroItemError",
 *     type="object",
 *     @OA\Property(property="indice", type="integer", example=1),
 *     @OA\Property(property="clave", type="string", nullable=true, example="X"),
 *     @OA\Property(property="codigo", type="string", example="integracion.maestro.itemInvalido")
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionMaestroLoteResultado",
 *     type="object",
 *     required={"recurso","modo","recibidos","procesados","agregados","modificados","eliminados","agregadosErroneos","modificadosErroneos","eliminadosErroneos","errores"},
 *     @OA\Property(property="recurso", type="string", example="provincias"),
 *     @OA\Property(property="modo", type="string", example="delta"),
 *     @OA\Property(property="recibidos", type="integer", example=3),
 *     @OA\Property(property="procesados", type="integer", example=3),
 *     @OA\Property(property="agregados", type="integer", example=1),
 *     @OA\Property(property="modificados", type="integer", example=1),
 *     @OA\Property(property="eliminados", type="integer", example=1),
 *     @OA\Property(property="agregadosErroneos", type="integer", example=0),
 *     @OA\Property(property="modificadosErroneos", type="integer", example=0),
 *     @OA\Property(property="eliminadosErroneos", type="integer", example=0),
 *     @OA\Property(property="omitidos", type="integer", example=0),
 *     @OA\Property(property="errores", type="array", @OA\Items(ref="#/components/schemas/IntegracionMaestroItemError"))
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeIntegracionMaestroLote",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/IntegracionMaestroLoteResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionImportacionLoteRequest",
 *     type="object",
 *     required={"importacionId","numeroLote","ultimoEnvio","modo","fechaProceso","items"},
 *     @OA\Property(property="importacionId", type="string", maxLength=100, example="ERP-IMP-20261024-0001"),
 *     @OA\Property(property="numeroLote", type="integer", minimum=1, example=1),
 *     @OA\Property(property="ultimoEnvio", type="boolean", example=false),
 *     @OA\Property(property="modo", type="string", enum={"delta","snapshot"}, example="delta"),
 *     @OA\Property(property="fechaProceso", type="string", format="date-time", example="2026-10-24T12:00:00-03:00"),
 *     @OA\Property(property="origen", type="string", nullable=true, maxLength=50, example="tango"),
 *     @OA\Property(property="items", type="array", @OA\Items(type="object", additionalProperties=true))
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionImportacionResultado",
 *     type="object",
 *     @OA\Property(property="importacionId", type="string", example="ERP-IMP-20261024-0001"),
 *     @OA\Property(property="recurso", type="string", example="condventa"),
 *     @OA\Property(property="estado", type="string", enum={"received","staging","queued","processing","completed","failed"}),
 *     @OA\Property(property="duracion", type="number", format="float", nullable=true, example=2.315),
 *     @OA\Property(property="procesados", type="integer", nullable=true, example=100),
 *     @OA\Property(property="agregados", type="integer", nullable=true, example=20),
 *     @OA\Property(property="modificados", type="integer", nullable=true, example=70),
 *     @OA\Property(property="eliminados", type="integer", nullable=true, example=10),
 *     @OA\Property(property="agregadosErroneos", type="integer", nullable=true, example=0),
 *     @OA\Property(property="modificadosErroneos", type="integer", nullable=true, example=0),
 *     @OA\Property(property="eliminadosErroneos", type="integer", nullable=true, example=0),
 *     @OA\Property(property="errores", type="array", nullable=true, @OA\Items(type="object", additionalProperties=true))
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeIntegracionImportacion",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/IntegracionImportacionResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionTransaccionLoteRequest",
 *     type="object",
 *     required={"modo","fechaProceso","items"},
 *     @OA\Property(property="modo", type="string", enum={"delta","snapshot"}, example="delta"),
 *     @OA\Property(property="fechaProceso", type="string", format="date-time", example="2026-10-22T12:00:00-03:00"),
 *     @OA\Property(property="origen", type="string", nullable=true, maxLength=50, example="tango"),
 *     @OA\Property(property="items", type="array", @OA\Items(type="object", additionalProperties=true))
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionTransaccionItemError",
 *     type="object",
 *     @OA\Property(property="indice", type="integer", example=1),
 *     @OA\Property(property="clave", type="string", nullable=true, example="A-001"),
 *     @OA\Property(property="codigo", type="string", example="integracion.transaccion.itemInvalido")
 * )
 *
 * @OA\Schema(
 *     schema="IntegracionTransaccionLoteResultado",
 *     type="object",
 *     required={"recurso","modo","recibidos","aplicados","omitidos","rechazados","errores"},
 *     @OA\Property(property="recurso", type="string", example="stock"),
 *     @OA\Property(property="modo", type="string", example="delta"),
 *     @OA\Property(property="recibidos", type="integer", example=3),
 *     @OA\Property(property="aplicados", type="integer", example=2),
 *     @OA\Property(property="omitidos", type="integer", example=0),
 *     @OA\Property(property="rechazados", type="integer", example=1),
 *     @OA\Property(property="errores", type="array", @OA\Items(ref="#/components/schemas/IntegracionTransaccionItemError"))
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeIntegracionTransaccionLote",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/IntegracionTransaccionLoteResultado"))
 *     }
 * )
 */
final class OpenApiGetsSchemas
{
}
