<?php

namespace App\OpenApi;

/**
 * Paths OpenAPI — GET restantes no documentados previamente.
 *
 * @OA\Get(
 *     path="/api/v1/config/public",
 *     summary="Flags públicos de producto (post-login)",
 *     tags={"Framework"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Flags de features", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopePublicConfig")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/grid-layouts",
 *     summary="Listar layouts de grilla del proceso",
 *     tags={"Framework"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="proceso", in="query", required=true, @OA\Schema(type="string")),
 *     @OA\Parameter(name="gridId", in="query", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Layouts disponibles", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeGridLayoutsList")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=422, description="Validacion")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/grid-layouts/active",
 *     summary="Layout activo de grilla",
 *     tags={"Framework"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="proceso", in="query", required=true, @OA\Schema(type="string")),
 *     @OA\Parameter(name="gridId", in="query", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Layout activo o vacio", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeGridLayoutActive")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=422, description="Validacion")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/pivot-configs",
 *     summary="Listar configuraciones pivot guardadas",
 *     tags={"Pivots"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="consultaId", in="query", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Configs pivot", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopePivotConfigsList")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=404, description="Consulta no encontrada")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/pivot-configs/active",
 *     summary="Configuracion pivot activa",
 *     tags={"Pivots"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="consultaId", in="query", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Config activa o restoreMode", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopePivotConfigActive")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=404, description="Consulta no encontrada")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/dashboard/resumen-mensual",
 *     summary="Dashboard resumen mensual por estado",
 *     tags={"Framework"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="KPIs del mes en curso", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeDashboardResumenMensual")),
 *     @OA\Response(response=400, description="Tenant invalido"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso dashboard")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/excel-import/lotes/{guidImportacion}/filas",
 *     summary="Filas staging de lote Excel",
 *     tags={"Excel Import"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="guidImportacion", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
 *     @OA\Parameter(name="pageSize", in="query", required=false, @OA\Schema(type="integer", default=50)),
 *     @OA\Parameter(name="soloConError", in="query", required=false, @OA\Schema(type="boolean")),
 *     @OA\Response(response=200, description="Filas paginadas", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeExcelStagingFilas")),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=404, description="Lote no encontrado")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/excel-import/lotes/{guidImportacion}/filas/validas",
 *     summary="Filas validas de lote Excel",
 *     tags={"Excel Import"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="guidImportacion", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *     @OA\Response(response=200, description="Filas validas (y grupos si masivo)", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeExcelStagingFilasValidas")),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=404, description="Lote no encontrado")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/excel-import/lotes/{guidImportacion}/columnas",
 *     summary="Metadata de columnas staging",
 *     tags={"Excel Import"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="guidImportacion", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *     @OA\Response(response=200, description="Columnas y flags de proceso", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeExcelStagingColumnas")),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=404, description="Lote no encontrado")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/excel-import/lotes/{guidImportacion}/export-errores",
 *     summary="Exportar errores del lote a Excel",
 *     tags={"Excel Import"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="guidImportacion", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *     @OA\Response(
 *         response=200,
 *         description="Archivo xlsx de errores",
 *         @OA\MediaType(
 *             mediaType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
 *             @OA\Schema(type="string", format="binary")
 *         )
 *     ),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=404, description="Lote no encontrado")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/admin/roles",
 *     summary="Listar roles de seguridad",
 *     tags={"Admin"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Roles", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeAdminRolesList")),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso admin o feature deshabilitada")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/admin/roles/{id}/atributos",
 *     summary="Atributos/permisos de un rol",
 *     tags={"Admin"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Matriz de atributos", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeAdminRolAtributos")),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso"),
 *     @OA\Response(response=404, description="Rol no encontrado")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/admin/permisos",
 *     summary="Listar asignaciones usuario-rol",
 *     tags={"Admin"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Response(response=200, description="Permisos", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeAdminPermisosList")),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso")
 * )
 *
 * @OA\Get(
 *     path="/api/v1/admin/usuarios",
 *     summary="Lookup de usuarios para asignacion",
 *     tags={"Admin"},
 *     security={{"sanctum":{}},{"tenant":{}}},
 *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
 *     @OA\Parameter(name="pageSize", in="query", required=false, @OA\Schema(type="integer", default=20)),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Usuarios paginados", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeAdminUsuariosLookup")),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="Sin permiso")
 * )
 */
final class OpenApiExtraGetsPaths
{
}
