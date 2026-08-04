<?php

namespace App\OpenApi;

/**
 * Schemas OpenAPI — GET restantes (config pública, layouts, pivot configs, dashboard mensual, Excel staging, Admin).
 * Tags: ver {@see OpenApiTags}.
 *
 * @OA\Schema(
 *     schema="PublicConfigResultado",
 *     type="object",
 *     @OA\Property(property="gridLayoutsEnabled", type="boolean", example=true),
 *     @OA\Property(property="pivotsEnabled", type="boolean", example=true),
 *     @OA\Property(property="pivotLayoutsEnabled", type="boolean", example=true),
 *     @OA\Property(property="excelImportEnabled", type="boolean", example=true),
 *     @OA\Property(property="securityAdminEnabled", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopePublicConfig",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/PublicConfigResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="GridLayoutListItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="layoutName", type="string", example="Mi layout"),
 *     @OA\Property(property="createdByUserId", type="integer", example=10),
 *     @OA\Property(property="isOwner", type="boolean", example=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="GridLayoutsListResultado",
 *     type="object",
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/GridLayoutListItem"))
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeGridLayoutsList",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/GridLayoutsListResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="GridLayoutActiveResultado",
 *     type="object",
 *     @OA\Property(property="layoutId", type="integer", nullable=true, example=1),
 *     @OA\Property(property="layoutName", type="string", nullable=true, example="Mi layout"),
 *     @OA\Property(property="stateJson", type="object", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeGridLayoutActive",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/GridLayoutActiveResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="PivotConfigListItem",
 *     type="object",
 *     @OA\Property(property="configId", type="integer", example=5),
 *     @OA\Property(property="nombre", type="string", example="Vista ventas"),
 *     @OA\Property(property="createdByUserId", type="integer", example=10),
 *     @OA\Property(property="isOwner", type="boolean", example=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="PivotConfigsListResultado",
 *     type="object",
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/PivotConfigListItem"))
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopePivotConfigsList",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/PivotConfigsListResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="PivotConfigActiveResultado",
 *     type="object",
 *     @OA\Property(property="configId", type="integer", nullable=true, example=5),
 *     @OA\Property(property="nombre", type="string", nullable=true, example="Vista ventas"),
 *     @OA\Property(property="configuracionJson", type="object", nullable=true),
 *     @OA\Property(property="versionDefinicionConsulta", type="integer", nullable=true, example=1),
 *     @OA\Property(property="restoreMode", type="string", enum={"saved","pivotBase","empty"}, example="saved")
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopePivotConfigActive",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/PivotConfigActiveResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="DashboardResumenMensualEstadoItem",
 *     type="object",
 *     @OA\Property(property="estado", type="integer", example=0),
 *     @OA\Property(property="cantidad", type="integer", example=12),
 *     @OA\Property(property="importe", type="number", format="float", example=15000.5),
 *     @OA\Property(property="unidades", type="number", format="float", example=320)
 * )
 *
 * @OA\Schema(
 *     schema="DashboardResumenMensualResultado",
 *     type="object",
 *     @OA\Property(property="anio", type="integer", example=2026),
 *     @OA\Property(property="mes", type="integer", example=8),
 *     @OA\Property(property="porEstado", type="array", @OA\Items(ref="#/components/schemas/DashboardResumenMensualEstadoItem")),
 *     @OA\Property(property="fechaCalculo", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeDashboardResumenMensual",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/DashboardResumenMensualResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ExcelStagingFilaItem",
 *     type="object",
 *     @OA\Property(property="idImportacionFila", type="integer", example=101),
 *     @OA\Property(property="numeroFilaExcel", type="integer", example=2),
 *     @OA\Property(property="tieneError", type="boolean", example=false),
 *     @OA\Property(property="errorImportacion", type="string", nullable=true),
 *     @OA\Property(property="estadoFila", type="string", example="valida"),
 *     @OA\Property(property="datos", type="object")
 * )
 *
 * @OA\Schema(
 *     schema="ExcelStagingFilasResultado",
 *     type="object",
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ExcelStagingFilaItem")),
 *     @OA\Property(property="total", type="integer", example=100),
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="pageSize", type="integer", example=50)
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeExcelStagingFilas",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ExcelStagingFilasResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ExcelStagingFilaValidaItem",
 *     type="object",
 *     @OA\Property(property="numeroFilaExcel", type="integer", example=2),
 *     @OA\Property(property="estadoFila", type="string", example="valida"),
 *     @OA\Property(property="datos", type="object")
 * )
 *
 * @OA\Schema(
 *     schema="ExcelStagingGrupoMasivo",
 *     type="object",
 *     @OA\Property(property="idGrupo", type="string", example="CLI001|VEN01|0"),
 *     @OA\Property(property="clave", type="object"),
 *     @OA\Property(property="cabecera", type="object"),
 *     @OA\Property(property="renglones", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="vendedor", type="object", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ExcelStagingFilasValidasResultado",
 *     type="object",
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ExcelStagingFilaValidaItem")),
 *     @OA\Property(property="total", type="integer", example=90),
 *     @OA\Property(property="grupos", type="array", @OA\Items(ref="#/components/schemas/ExcelStagingGrupoMasivo"), description="Solo PEDIDO_MASIVO cuando aplica")
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeExcelStagingFilasValidas",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ExcelStagingFilasValidasResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ExcelStagingColumnaMeta",
 *     type="object",
 *     @OA\Property(property="dataField", type="string", example="codCliente"),
 *     @OA\Property(property="caption", type="string", example="Cliente"),
 *     @OA\Property(property="tipoDato", type="string", example="string"),
 *     @OA\Property(property="format", type="string", nullable=true),
 *     @OA\Property(property="fixed", type="boolean", nullable=true, example=true)
 * )
 *
 * @OA\Schema(
 *     schema="ExcelStagingColumnasResultado",
 *     type="object",
 *     @OA\Property(property="columnas", type="array", @OA\Items(ref="#/components/schemas/ExcelStagingColumnaMeta")),
 *     @OA\Property(property="permiteProcesamientoParcial", type="boolean", example=true),
 *     @OA\Property(property="permiteSoloValidar", type="boolean", example=true),
 *     @OA\Property(property="puedeProcesar", type="boolean", example=true),
 *     @OA\Property(property="cantidadFilasValidas", type="integer", example=90),
 *     @OA\Property(property="cantidadFilasConError", type="integer", example=10),
 *     @OA\Property(property="estadoImportacion", type="string", example="validado")
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeExcelStagingColumnas",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/ExcelStagingColumnasResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="AdminRolItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=2),
 *     @OA\Property(property="nombreRol", type="string", example="Vendedor"),
 *     @OA\Property(property="descripcionRol", type="string", example="Perfil vendedor"),
 *     @OA\Property(property="accesoTotal", type="boolean", example=false),
 *     @OA\Property(property="enUso", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="AdminRolesListResultado",
 *     type="object",
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/AdminRolItem"))
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeAdminRolesList",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/AdminRolesListResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="AdminRolAtributoItem",
 *     type="object",
 *     @OA\Property(property="procedimiento", type="string", example="pw_cargapedidos"),
 *     @OA\Property(property="menuText", type="string", example="Carga de pedidos"),
 *     @OA\Property(property="menuKey", type="string", example="cargaPedidos"),
 *     @OA\Property(property="permisoAlta", type="boolean", example=true),
 *     @OA\Property(property="permisoBaja", type="boolean", example=false),
 *     @OA\Property(property="permisoModi", type="boolean", example=true),
 *     @OA\Property(property="permisoRepo", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="AdminRolAtributosResultado",
 *     type="object",
 *     @OA\Property(property="readOnly", type="boolean", example=false),
 *     @OA\Property(
 *         property="rol",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="nombreRol", type="string", example="Vendedor"),
 *         @OA\Property(property="accesoTotal", type="boolean", example=false)
 *     ),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/AdminRolAtributoItem"))
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeAdminRolAtributos",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/AdminRolAtributosResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="AdminPermisoItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=15),
 *     @OA\Property(property="idUsuario", type="integer", example=10),
 *     @OA\Property(property="usuarioCodigo", type="string", example="vendedor.mvp"),
 *     @OA\Property(property="usuarioNombre", type="string", example="Vendedor MVP"),
 *     @OA\Property(property="idRol", type="integer", example=2),
 *     @OA\Property(property="rolNombre", type="string", example="Vendedor")
 * )
 *
 * @OA\Schema(
 *     schema="AdminPermisosListResultado",
 *     type="object",
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/AdminPermisoItem"))
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeAdminPermisosList",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/AdminPermisosListResultado"))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="AdminUsuarioLookupItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="codigo", type="string", example="vendedor.mvp"),
 *     @OA\Property(property="nameUser", type="string", example="Vendedor MVP")
 * )
 *
 * @OA\Schema(
 *     schema="AdminUsuariosLookupResultado",
 *     type="object",
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/AdminUsuarioLookupItem")),
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="page_size", type="integer", example=20),
 *     @OA\Property(property="total", type="integer", example=40),
 *     @OA\Property(property="total_pages", type="integer", example=2)
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeAdminUsuariosLookup",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", ref="#/components/schemas/AdminUsuariosLookupResultado"))
 *     }
 * )
 */
final class OpenApiExtraGetsSchemas
{
}
