<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class OpenApiDocumentationTest extends TestCase
{
    public function testOpenApiSpecCanBeGenerated(): void
    {
        Artisan::call('l5-swagger:generate');

        $this->assertFileExists(storage_path('api-docs/api-docs.json'));
    }

    public function testDocumentationUiIsAccessible(): void
    {
        if (! is_file(storage_path('api-docs/api-docs.json'))) {
            Artisan::call('l5-swagger:generate');
        }

        $response = $this->get('/api/documentation');

        $response->assertOk();
    }

    public function testGeneratedSpecIncludesCorePaths(): void
    {
        $spec = $this->loadGeneratedSpec();

        $this->assertIsArray($spec);
        $this->assertArrayHasKey('paths', $spec);
        $this->assertArrayHasKey('/api/v1/health', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/auth/login', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/auth/password/forgot', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/auth/password/reset', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/auth/logout', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/auth/me', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/clientes', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/clientes/{codCliente}', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/comprobantes/{id}', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/dashboard/resumen', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/user/menu', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/users/me/preferences/locale', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/users/me/preferences', $spec['paths']);
        $this->assertArrayHasKey('patch', $spec['paths']['/api/v1/users/me/preferences']);
        $this->assertArrayHasKey('/api/v1/users/me/preferences/theme', $spec['paths']);
        $this->assertArrayHasKey('patch', $spec['paths']['/api/v1/users/me/preferences/theme']);
        $this->assertArrayHasKey('/api/v1/chat-assistant/providers', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/chat-assistant/me/configuration', $spec['paths']);
        $this->assertArrayHasKey('put', $spec['paths']['/api/v1/chat-assistant/me/configuration']);
        $this->assertArrayHasKey('/api/v1/chat-assistant/me/configuration/status', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/chat-assistant/messages', $spec['paths']);
    }

    public function testGeneratedSpecIncludesPedidosWebPaths(): void
    {
        $spec = $this->loadGeneratedSpec();

        $pedidosWebPaths = [
            '/api/v1/comprobantes/grabar',
            '/api/v1/comprobantes/copiar',
            '/api/v1/pedidos',
            '/api/v1/pedidos/{cod_pedido}',
            '/api/v1/pedidos/{cod_pedido}/edicion/iniciar',
            '/api/v1/presupuestos',
            '/api/v1/presupuestos/{cod_pedido}',
            '/api/v1/presupuestos/{cod}/cerrar',
            '/api/v1/motivos-cierre',
            '/api/v1/presupuestos/{cod}/tratativas',
            '/api/v1/consultas/pedidos-ingresados',
            '/api/v1/consultas/pedidos-pendientes',
            '/api/v1/consultas/presupuestos',
            '/api/v1/consultas/stock',
            '/api/v1/consultas/deuda',
            '/api/v1/consultas/cheques',
            '/api/v1/consultas/historial-ventas',
            '/api/v1/integracion/logs',
            '/api/v1/dashboard/operativo',
            '/api/v1/config/parametros-carga',
            '/api/v1/articulos',
        ];

        foreach ($pedidosWebPaths as $path) {
            $this->assertArrayHasKey($path, $spec['paths'], "Falta path OpenAPI: {$path}");
        }

        $grabarOperation = $spec['paths']['/api/v1/comprobantes/grabar']['post'];
        $this->assertSame([['sanctum' => []], ['tenant' => []]], $grabarOperation['security'] ?? null);
        $this->assertArrayHasKey('401', $grabarOperation['responses']);
        $this->assertArrayHasKey('403', $grabarOperation['responses']);

        $grabarBodyRef = $grabarOperation['requestBody']['content']['application/json']['schema']['$ref']
            ?? $grabarOperation['requestBody']['content']['application/json']['schema']['properties']
            ?? null;
        $this->assertNotNull($grabarBodyRef, 'POST /comprobantes/grabar debe tipar el request body');

        $cabeceraSchema = $spec['components']['schemas']['ComprobanteCabeceraRequest']['properties'] ?? null;
        $this->assertIsArray($cabeceraSchema);
        $this->assertArrayHasKey('cod_cliente', $cabeceraSchema);
        $this->assertArrayHasKey('lista_precios', $cabeceraSchema);
        $this->assertArrayHasKey('bonif_1', $cabeceraSchema);
        $this->assertSame(60, $cabeceraSchema['leyenda_1']['maxLength'] ?? null);
        $this->assertSame(60, $cabeceraSchema['leyenda_5']['maxLength'] ?? null);

        $renglonSchema = $spec['components']['schemas']['ComprobanteRenglonRequest']['properties'] ?? null;
        $this->assertIsArray($renglonSchema);
        $this->assertArrayHasKey('cod_articulo', $renglonSchema);
        $this->assertArrayHasKey('cantidad', $renglonSchema);
        $this->assertArrayHasKey('cantidad_venta', $renglonSchema);
        $this->assertArrayHasKey('precio', $renglonSchema);

        $clientesTags = $spec['paths']['/api/v1/clientes']['get']['tags'] ?? [];
        $this->assertContains('Maestros y Tablas', $clientesTags);
        $this->assertNotContains('Visibilidad', $clientesTags);
        $this->assertNotContains('Maestros', $clientesTags);

        $this->assertContains('Informes', $spec['paths']['/api/v1/consultas/stock']['get']['tags'] ?? []);
        $this->assertContains('Parametros', $spec['paths']['/api/v1/config/parametros']['get']['tags'] ?? []);
        $this->assertContains('Framework', $spec['paths']['/api/v1/health']['get']['tags'] ?? []);
        $this->assertContains('Framework', $spec['paths']['/api/v1/user/menu']['get']['tags'] ?? []);
        $this->assertContains('Framework', $spec['paths']['/api/v1/dashboard/resumen']['get']['tags'] ?? []);
        $this->assertContains('Tratativas', $spec['paths']['/api/v1/motivos-cierre']['get']['tags'] ?? []);

        foreach ([
            '/api/v1/perfiles',
            '/api/v1/condiciones-venta',
            '/api/v1/transportes',
            '/api/v1/listas-precios',
            '/api/v1/clientes/{codCliente}/direcciones-entrega',
        ] as $catalogPath) {
            $this->assertArrayHasKey($catalogPath, $spec['paths'], "Falta path OpenAPI: {$catalogPath}");
            $this->assertContains('Maestros y Tablas', $spec['paths'][$catalogPath]['get']['tags'] ?? []);
        }

        $informesEnvelopeRefs = [
            '/api/v1/consultas/pedidos-ingresados' => 'ApiEnvelopeConsultaComprobantes',
            '/api/v1/consultas/pedidos-pendientes' => 'ApiEnvelopeConsultaComprobantes',
            '/api/v1/consultas/presupuestos' => 'ApiEnvelopeConsultaComprobantes',
            '/api/v1/consultas/stock' => 'ApiEnvelopeConsultaStock',
            '/api/v1/consultas/deuda' => 'ApiEnvelopeConsultaDeuda',
            '/api/v1/consultas/cheques' => 'ApiEnvelopeConsultaCheques',
            '/api/v1/consultas/historial-ventas' => 'ApiEnvelopeConsultaHistorialVentas',
            '/api/v1/consultas/detalle-pedidos' => 'ApiEnvelopeConsultaDetallePedidos',
        ];

        foreach ($informesEnvelopeRefs as $path => $schemaName) {
            $ref = $spec['paths'][$path]['get']['responses']['200']['content']['application/json']['schema']['$ref'] ?? null;
            $this->assertSame(
                '#/components/schemas/'.$schemaName,
                $ref,
                "Informes {$path} debe tipar resultado con {$schemaName}"
            );
            $this->assertArrayHasKey($schemaName, $spec['components']['schemas'] ?? []);
        }

        $historialParams = array_column(
            $spec['paths']['/api/v1/consultas/historial-ventas']['get']['parameters'] ?? [],
            'name'
        );
        $this->assertContains('fecha_desde', $historialParams);
        $this->assertContains('fecha_hasta', $historialParams);

        $this->assertArrayHasKey('items', $spec['components']['schemas']['ConsultaListadoStockResultado']['properties'] ?? []);
        $this->assertArrayHasKey('codArticulo', $spec['components']['schemas']['ConsultaStockItem']['properties'] ?? []);
        $this->assertArrayHasKey('saldo', $spec['components']['schemas']['ConsultaDeudaItem']['properties'] ?? []);

        $getsTipados = [
            '/api/v1/articulos' => 'ApiEnvelopeArticulosCarga',
            '/api/v1/clientes/{codCliente}/cabecera-inicial' => 'ApiEnvelopeCabeceraInicial',
            '/api/v1/config/parametros' => 'ApiEnvelopeParametrosConsulta',
            '/api/v1/config/parametros-carga' => 'ApiEnvelopeParametrosCarga',
            '/api/v1/dashboard/operativo' => 'ApiEnvelopeDashboardOperativo',
            '/api/v1/integracion/logs' => 'ApiEnvelopeIntegracionLogs',
            '/api/v1/motivos-cierre' => 'ApiEnvelopeMotivosCierre',
            '/api/v1/pedidos/{cod_pedido}' => 'ApiEnvelopeComprobanteDetalle',
            '/api/v1/presupuestos/{cod}/tratativas' => 'ApiEnvelopeTratativasListado',
            '/api/v1/presupuestos/{cod_pedido}' => 'ApiEnvelopeComprobanteDetalle',
            '/api/v1/excel-import/historial' => 'ApiEnvelopeExcelImportHistorial',
            '/api/v1/excel-import/lotes/{guidImportacion}' => 'ApiEnvelopeExcelImportLoteDetalle',
            '/api/v1/excel-import/procesos/{codigoProceso}' => 'ApiEnvelopeExcelImportProcesoMetadata',
            '/api/v1/chat-assistant/me/configurations' => 'ApiEnvelopeChatAssistantConfigurationsList',
            '/api/v1/pivots/consultas/{consultaId}/metadata' => 'ApiEnvelopePivotMetadata',
        ];

        foreach ($getsTipados as $path => $schemaName) {
            $this->assertArrayHasKey($path, $spec['paths'], "Falta path OpenAPI: {$path}");
            $ref = $spec['paths'][$path]['get']['responses']['200']['content']['application/json']['schema']['$ref'] ?? null;
            $this->assertSame(
                '#/components/schemas/'.$schemaName,
                $ref,
                "GET {$path} debe tipar resultado con {$schemaName}"
            );
        }

        $plantillaMedia = $spec['paths']['/api/v1/excel-import/procesos/{codigoProceso}/plantilla']['get']['responses']['200']['content'] ?? [];
        $this->assertArrayHasKey(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $plantillaMedia,
            'GET plantilla debe documentar response binario xlsx'
        );

        $extraGets = [
            '/api/v1/config/public' => 'ApiEnvelopePublicConfig',
            '/api/v1/grid-layouts' => 'ApiEnvelopeGridLayoutsList',
            '/api/v1/grid-layouts/active' => 'ApiEnvelopeGridLayoutActive',
            '/api/v1/pivot-configs' => 'ApiEnvelopePivotConfigsList',
            '/api/v1/pivot-configs/active' => 'ApiEnvelopePivotConfigActive',
            '/api/v1/dashboard/resumen-mensual' => 'ApiEnvelopeDashboardResumenMensual',
            '/api/v1/excel-import/lotes/{guidImportacion}/filas' => 'ApiEnvelopeExcelStagingFilas',
            '/api/v1/excel-import/lotes/{guidImportacion}/filas/validas' => 'ApiEnvelopeExcelStagingFilasValidas',
            '/api/v1/excel-import/lotes/{guidImportacion}/columnas' => 'ApiEnvelopeExcelStagingColumnas',
            '/api/v1/admin/roles' => 'ApiEnvelopeAdminRolesList',
            '/api/v1/admin/roles/{id}/atributos' => 'ApiEnvelopeAdminRolAtributos',
            '/api/v1/admin/permisos' => 'ApiEnvelopeAdminPermisosList',
            '/api/v1/admin/usuarios' => 'ApiEnvelopeAdminUsuariosLookup',
        ];

        foreach ($extraGets as $path => $schemaName) {
            $this->assertArrayHasKey($path, $spec['paths'], "Falta path OpenAPI: {$path}");
            $ref = $spec['paths'][$path]['get']['responses']['200']['content']['application/json']['schema']['$ref'] ?? null;
            $this->assertSame(
                '#/components/schemas/'.$schemaName,
                $ref,
                "GET {$path} debe tipar resultado con {$schemaName}"
            );
        }

        $exportErroresMedia = $spec['paths']['/api/v1/excel-import/lotes/{guidImportacion}/export-errores']['get']['responses']['200']['content'] ?? [];
        $this->assertArrayHasKey(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $exportErroresMedia,
            'GET export-errores debe documentar response binario xlsx'
        );

        $adminTags = $spec['paths']['/api/v1/admin/roles']['get']['tags'] ?? [];
        $this->assertContains('Admin', $adminTags);

        $this->assertContains('Excel Import', $spec['paths']['/api/v1/excel-import/historial']['get']['tags'] ?? []);
        $this->assertContains('Chat Assistant', $spec['paths']['/api/v1/chat-assistant/providers']['get']['tags'] ?? []);
        $this->assertContains('Pedidos Web', $spec['paths']['/api/v1/comprobantes/grabar']['post']['tags'] ?? []);

        $tagNames = array_column($spec['tags'] ?? [], 'name');
        $sorted = $tagNames;
        sort($sorted, SORT_STRING);
        $this->assertSame(
            $sorted,
            $tagNames,
            'Los tags OpenAPI deben declararse en orden alfabético'
        );
        $this->assertNotContains('ExcelImport', $tagNames);
        $this->assertNotContains('ChatAssistant', $tagNames);
        $this->assertNotContains('PedidosWeb', $tagNames);
    }

    public function testGeneratedSpecDocumentsTransversalSecurityRules(): void
    {
        $spec = $this->loadGeneratedSpec();

        $this->assertArrayHasKey('components', $spec);
        $this->assertArrayHasKey('securitySchemes', $spec['components']);
        $this->assertArrayHasKey('sanctum', $spec['components']['securitySchemes']);
        $this->assertArrayHasKey('tenant', $spec['components']['securitySchemes']);
        $this->assertSame(
            'integer',
            $spec['components']['schemas']['SessionContextResultado']['properties']['inactivityTimeoutMinutes']['type'] ?? null
        );

        $healthOperation = $spec['paths']['/api/v1/health']['get'];
        $this->assertArrayNotHasKey('security', $healthOperation);

        $loginOperation = $spec['paths']['/api/v1/auth/login']['post'];
        $this->assertSame([['tenant' => []]], $loginOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $loginOperation['responses']);
        $this->assertArrayHasKey('401', $loginOperation['responses']);
        $this->assertArrayHasKey('403', $loginOperation['responses']);

        $forgotOperation = $spec['paths']['/api/v1/auth/password/forgot']['post'];
        $this->assertSame([['tenant' => []]], $forgotOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $forgotOperation['responses']);
        $this->assertArrayHasKey('422', $forgotOperation['responses']);

        $resetOperation = $spec['paths']['/api/v1/auth/password/reset']['post'];
        $this->assertSame([['tenant' => []]], $resetOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $resetOperation['responses']);
        $this->assertArrayHasKey('422', $resetOperation['responses']);

        $clientesOperation = $spec['paths']['/api/v1/clientes']['get'];
        $this->assertSame([['sanctum' => []], ['tenant' => []]], $clientesOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $clientesOperation['responses']);
        $this->assertArrayHasKey('401', $clientesOperation['responses']);
        $this->assertArrayHasKey('403', $clientesOperation['responses']);

        $clienteUnitarioOperation = $spec['paths']['/api/v1/clientes/{codCliente}']['get'];
        $this->assertSame([['sanctum' => []], ['tenant' => []]], $clienteUnitarioOperation['security'] ?? null);
        $this->assertContains('Maestros y Tablas', $clienteUnitarioOperation['tags'] ?? []);
        $this->assertSame(
            '#/components/schemas/ApiEnvelopeVisibleClient',
            $clienteUnitarioOperation['responses']['200']['content']['application/json']['schema']['$ref'] ?? null
        );
        $this->assertArrayHasKey('400', $clienteUnitarioOperation['responses']);
        $this->assertArrayHasKey('401', $clienteUnitarioOperation['responses']);
        $this->assertArrayHasKey('403', $clienteUnitarioOperation['responses']);
        $this->assertArrayHasKey('404', $clienteUnitarioOperation['responses']);
        $this->assertArrayHasKey('VisibleClientContactItem', $spec['components']['schemas'] ?? []);
        $this->assertArrayHasKey('contactos', $spec['components']['schemas']['VisibleClientItem']['properties'] ?? []);

        $comprobantesOperation = $spec['paths']['/api/v1/comprobantes/{id}']['get'];
        $this->assertSame([['sanctum' => []], ['tenant' => []]], $comprobantesOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $comprobantesOperation['responses']);
        $this->assertArrayHasKey('401', $comprobantesOperation['responses']);
        $this->assertArrayHasKey('403', $comprobantesOperation['responses']);
        $this->assertArrayHasKey('404', $comprobantesOperation['responses']);

        $dashboardOperation = $spec['paths']['/api/v1/dashboard/resumen']['get'];
        $this->assertSame([['sanctum' => []], ['tenant' => []]], $dashboardOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $dashboardOperation['responses']);
        $this->assertArrayHasKey('401', $dashboardOperation['responses']);
        $this->assertArrayHasKey('403', $dashboardOperation['responses']);

        $logoutOperation = $spec['paths']['/api/v1/auth/logout']['post'];
        $this->assertSame([['sanctum' => []], ['tenant' => []]], $logoutOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $logoutOperation['responses']);
        $this->assertArrayHasKey('401', $logoutOperation['responses']);

        $meOperation = $spec['paths']['/api/v1/auth/me']['get'];
        $this->assertSame([['sanctum' => []], ['tenant' => []]], $meOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $meOperation['responses']);
        $this->assertArrayHasKey('401', $meOperation['responses']);
        $this->assertArrayHasKey('403', $meOperation['responses']);

        $menuOperation = $spec['paths']['/api/v1/user/menu']['get'];
        $this->assertSame([['sanctum' => []], ['tenant' => []]], $menuOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $menuOperation['responses']);
        $this->assertArrayHasKey('401', $menuOperation['responses']);
        $this->assertArrayHasKey('403', $menuOperation['responses']);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadGeneratedSpec(): array
    {
        Artisan::call('l5-swagger:generate');

        /** @var array<string, mixed> $spec */
        $spec = json_decode((string) file_get_contents(storage_path('api-docs/api-docs.json')), true);

        return $spec;
    }
}
