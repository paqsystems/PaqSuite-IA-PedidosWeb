<?php

use App\Http\Controllers\Api\V1\Admin\AdminPermisoController;
use App\Http\Controllers\Api\V1\Admin\AdminRoleController;
use App\Http\Controllers\Api\V1\Admin\AdminUsuarioLookupController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GridLayoutController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Api\V1\ChatAssistant\ChatAssistantConfigurationController;
use App\Http\Controllers\Api\V1\ChatAssistant\ChatAssistantMessageController;
use App\Http\Controllers\Api\V1\ChatAssistant\ChatAssistantProviderCatalogController;
use App\Http\Controllers\Api\V1\Config\ParametrosController;
use App\Http\Controllers\Api\V1\ExcelImport\ExcelImportHistoryController;
use App\Http\Controllers\Api\V1\ExcelImport\ExcelImportLotController;
use App\Http\Controllers\Api\V1\ExcelImport\ExcelImportProcessController;
use App\Http\Controllers\Api\V1\ExcelImport\ExcelImportStagingController;
use App\Http\Controllers\Api\V1\Pivots\PivotConfigController;
use App\Http\Controllers\Api\V1\Pivots\PivotController;
use App\Http\Controllers\Api\V1\PedidosWeb\ArticuloController;
use App\Http\Controllers\Api\V1\PedidosWeb\ClienteCabeceraController;
use App\Http\Controllers\Api\V1\PedidosWeb\ComprobanteController;
use App\Http\Controllers\Api\V1\PedidosWeb\ConsultaController;
use App\Http\Controllers\Api\V1\PedidosWeb\DashboardController;
use App\Http\Controllers\Api\V1\PedidosWeb\IntegracionLogController;
use App\Http\Controllers\Api\V1\PedidosWeb\MotivoCierreController;
use App\Http\Controllers\Api\V1\PedidosWeb\ParametrosCargaController;
use App\Http\Controllers\Api\V1\PedidosWeb\PedidoController;
use App\Http\Controllers\Api\V1\PedidosWeb\PresupuestoCierreController;
use App\Http\Controllers\Api\V1\PedidosWeb\PresupuestoController;
use App\Http\Controllers\Api\V1\PedidosWeb\TratativaController;
use App\Http\Controllers\PublicConfigController;
use App\Http\Controllers\UserMenuController;
use App\Http\Controllers\UserPreferencesController;
use App\Http\Controllers\VisibilityDataController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (prefijo global: /api — ver RouteServiceProvider)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class)->name('api.v1.health');

    Route::middleware('paq.tenant')->group(function (): void {
        Route::post('/auth/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
        Route::post('/auth/password/forgot', [AuthController::class, 'forgotPassword'])
            ->name('api.v1.auth.password.forgot');
        Route::post('/auth/password/reset', [AuthController::class, 'resetPassword'])
            ->name('api.v1.auth.password.reset');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
            Route::post('/auth/password/change', [AuthController::class, 'changePassword'])
                ->name('api.v1.auth.password.change');
            Route::get('/auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
            Route::get('/config/public', PublicConfigController::class)->name('api.v1.config.public');
            Route::get('/user/menu', UserMenuController::class)->name('api.v1.user.menu');
            Route::get('/grid-layouts', [GridLayoutController::class, 'index'])->name('api.v1.grid-layouts.index');
            Route::get('/grid-layouts/active', [GridLayoutController::class, 'active'])->name('api.v1.grid-layouts.active');
            Route::post('/grid-layouts', [GridLayoutController::class, 'store'])->name('api.v1.grid-layouts.store');
            Route::put('/grid-layouts/active', [GridLayoutController::class, 'setActive'])->name('api.v1.grid-layouts.active.set');
            Route::put('/grid-layouts/{id}', [GridLayoutController::class, 'update'])->name('api.v1.grid-layouts.update');
            Route::delete('/grid-layouts/{id}', [GridLayoutController::class, 'destroy'])->name('api.v1.grid-layouts.destroy');
            Route::get('/pivot-configs', [PivotConfigController::class, 'index'])->name('api.v1.pivot-configs.index');
            Route::get('/pivot-configs/active', [PivotConfigController::class, 'active'])->name('api.v1.pivot-configs.active');
            Route::post('/pivot-configs', [PivotConfigController::class, 'store'])->name('api.v1.pivot-configs.store');
            Route::put('/pivot-configs/active', [PivotConfigController::class, 'setActive'])->name('api.v1.pivot-configs.active.set');
            Route::put('/pivot-configs/{configId}', [PivotConfigController::class, 'update'])->name('api.v1.pivot-configs.update');
            Route::delete('/pivot-configs/{configId}', [PivotConfigController::class, 'destroy'])->name('api.v1.pivot-configs.destroy');
            Route::get('/users/me/preferences', [UserPreferencesController::class, 'show'])
                ->name('api.v1.users.me.preferences.show');
            Route::patch('/users/me/preferences', [UserPreferencesController::class, 'update'])
                ->name('api.v1.users.me.preferences.update');
            Route::patch('/users/me/preferences/locale', [UserPreferencesController::class, 'updateLocale'])
                ->name('api.v1.users.me.preferences.locale');
            Route::patch('/users/me/preferences/theme', [UserPreferencesController::class, 'updateTheme'])
                ->name('api.v1.users.me.preferences.theme');
            Route::get('/chat-assistant/providers', [ChatAssistantProviderCatalogController::class, 'index'])
                ->name('api.v1.chat-assistant.providers');
            Route::get('/chat-assistant/me/configurations', [ChatAssistantConfigurationController::class, 'index'])
                ->name('api.v1.chat-assistant.me.configurations.index');
            Route::post('/chat-assistant/me/configurations', [ChatAssistantConfigurationController::class, 'store'])
                ->name('api.v1.chat-assistant.me.configurations.store');
            Route::put('/chat-assistant/me/configurations/{credentialId}', [ChatAssistantConfigurationController::class, 'update'])
                ->whereNumber('credentialId')
                ->name('api.v1.chat-assistant.me.configurations.update');
            Route::delete('/chat-assistant/me/configurations/{credentialId}', [ChatAssistantConfigurationController::class, 'destroy'])
                ->whereNumber('credentialId')
                ->name('api.v1.chat-assistant.me.configurations.destroy');
            Route::patch('/chat-assistant/me/configurations/{credentialId}/status', [ChatAssistantConfigurationController::class, 'updateItemStatus'])
                ->whereNumber('credentialId')
                ->name('api.v1.chat-assistant.me.configurations.status');
            Route::get('/chat-assistant/me/configuration', [ChatAssistantConfigurationController::class, 'show'])
                ->name('api.v1.chat-assistant.me.configuration.show');
            Route::put('/chat-assistant/me/configuration', [ChatAssistantConfigurationController::class, 'upsert'])
                ->name('api.v1.chat-assistant.me.configuration.upsert');
            Route::patch('/chat-assistant/me/configuration/status', [ChatAssistantConfigurationController::class, 'updateStatus'])
                ->name('api.v1.chat-assistant.me.configuration.status');
            Route::post('/chat-assistant/messages', [ChatAssistantMessageController::class, 'store'])
                ->name('api.v1.chat-assistant.messages.store');
            Route::get('/config/parametros-carga', [ParametrosCargaController::class, 'show'])
                ->name('api.v1.config.parametros-carga');
            Route::get('/config/parametros', [ParametrosController::class, 'index'])
                ->name('api.v1.config.parametros');
            Route::get('/articulos', [ArticuloController::class, 'index'])
                ->name('api.v1.articulos.index');
            Route::get('/clientes', [VisibilityDataController::class, 'clients'])
                ->name('api.v1.clientes.index');
            Route::get('/clientes/{codCliente}/cabecera-inicial', [ClienteCabeceraController::class, 'show'])
                ->name('api.v1.clientes.cabecera-inicial');
            Route::get('/comprobantes/{id}', [VisibilityDataController::class, 'showComprobante'])
                ->name('api.v1.comprobantes.show');
            Route::get('/dashboard/resumen', [VisibilityDataController::class, 'dashboardResumen'])
                ->name('api.v1.dashboard.resumen');

            Route::post('/comprobantes/grabar', [ComprobanteController::class, 'grabar'])
                ->name('api.v1.comprobantes.grabar');
            Route::post('/comprobantes/copiar', [ComprobanteController::class, 'copiar'])
                ->name('api.v1.comprobantes.copiar');

            Route::post('/pedidos', [PedidoController::class, 'store'])->name('api.v1.pedidos.store');
            Route::put('/pedidos/{cod_pedido}', [PedidoController::class, 'update'])->name('api.v1.pedidos.update');
            Route::get('/pedidos/{cod_pedido}', [PedidoController::class, 'show'])->name('api.v1.pedidos.show');
            Route::delete('/pedidos/{cod_pedido}', [PedidoController::class, 'destroy'])->name('api.v1.pedidos.destroy');
            Route::post('/pedidos/{cod_pedido}/edicion/iniciar', [PedidoController::class, 'iniciarEdicion'])
                ->name('api.v1.pedidos.edicion.iniciar');
            Route::post('/pedidos/{cod_pedido}/edicion/actividad', [PedidoController::class, 'touchActividad'])
                ->name('api.v1.pedidos.edicion.actividad');
            Route::post('/pedidos/{cod_pedido}/edicion/cancelar', [PedidoController::class, 'cancelarEdicion'])
                ->name('api.v1.pedidos.edicion.cancelar');

            Route::post('/presupuestos', [PresupuestoController::class, 'store'])->name('api.v1.presupuestos.store');
            Route::put('/presupuestos/{cod_pedido}', [PresupuestoController::class, 'update'])
                ->name('api.v1.presupuestos.update');
            Route::get('/presupuestos/{cod_pedido}', [PresupuestoController::class, 'show'])->name('api.v1.presupuestos.show');
            Route::post('/presupuestos/{cod}/cerrar', [PresupuestoCierreController::class, 'cerrar'])
                ->name('api.v1.presupuestos.cerrar');

            Route::get('/motivos-cierre', [MotivoCierreController::class, 'index'])->name('api.v1.motivos-cierre.index');
            Route::get('/presupuestos/{cod}/tratativas', [TratativaController::class, 'index'])
                ->name('api.v1.tratativas.index');
            Route::post('/presupuestos/{cod}/tratativas', [TratativaController::class, 'store'])
                ->name('api.v1.tratativas.store');

            Route::prefix('excel-import')->group(function (): void {
                Route::get('/procesos/{codigoProceso}', [ExcelImportProcessController::class, 'show'])
                    ->name('api.v1.excel-import.procesos.show');
                Route::get('/procesos/{codigoProceso}/plantilla', [ExcelImportProcessController::class, 'plantilla'])
                    ->name('api.v1.excel-import.procesos.plantilla');
                Route::post('/procesos/{codigoProceso}/archivo/hojas', [ExcelImportLotController::class, 'listarHojas'])
                    ->name('api.v1.excel-import.procesos.hojas');
                Route::post('/procesos/{codigoProceso}/lotes', [ExcelImportLotController::class, 'crearLote'])
                    ->name('api.v1.excel-import.procesos.lotes');
                Route::get('/lotes/{guidImportacion}', [ExcelImportLotController::class, 'show'])
                    ->name('api.v1.excel-import.lotes.show');
                Route::post('/lotes/{guidImportacion}/cancelar', [ExcelImportLotController::class, 'cancelar'])
                    ->name('api.v1.excel-import.lotes.cancelar');
                Route::get('/lotes/{guidImportacion}/filas', [ExcelImportStagingController::class, 'filas'])
                    ->name('api.v1.excel-import.lotes.filas');
                Route::get('/lotes/{guidImportacion}/filas/validas', [ExcelImportStagingController::class, 'filasValidas'])
                    ->name('api.v1.excel-import.lotes.filas-validas');
                Route::get('/lotes/{guidImportacion}/export-errores', [ExcelImportStagingController::class, 'exportErrores'])
                    ->name('api.v1.excel-import.lotes.export-errores');
                Route::get('/lotes/{guidImportacion}/columnas', [ExcelImportStagingController::class, 'columnas'])
                    ->name('api.v1.excel-import.lotes.columnas');
                Route::post('/lotes/{guidImportacion}/procesar', [ExcelImportStagingController::class, 'procesar'])
                    ->name('api.v1.excel-import.lotes.procesar');
                Route::get('/historial', [ExcelImportHistoryController::class, 'index'])
                    ->name('api.v1.excel-import.historial');
            });

            Route::prefix('pivots/consultas')->group(function (): void {
                Route::get('/{consultaId}/metadata', [PivotController::class, 'metadata'])
                    ->name('api.v1.pivots.consultas.metadata');
                Route::post('/{consultaId}/data', [PivotController::class, 'data'])
                    ->name('api.v1.pivots.consultas.data');
                Route::post('/{consultaId}/validate-structure', [PivotController::class, 'validateStructure'])
                    ->name('api.v1.pivots.consultas.validate-structure');
            });

            Route::prefix('consultas')->group(function (): void {
                Route::get('/pedidos-ingresados', [ConsultaController::class, 'pedidosIngresados'])
                    ->name('api.v1.consultas.pedidos-ingresados');
                Route::get('/pedidos-pendientes', [ConsultaController::class, 'pedidosPendientes'])
                    ->name('api.v1.consultas.pedidos-pendientes');
                Route::get('/presupuestos', [ConsultaController::class, 'presupuestos'])
                    ->name('api.v1.consultas.presupuestos');
                Route::get('/stock', [ConsultaController::class, 'stock'])->name('api.v1.consultas.stock');
                Route::get('/deuda', [ConsultaController::class, 'deuda'])->name('api.v1.consultas.deuda');
                Route::get('/cheques', [ConsultaController::class, 'cheques'])->name('api.v1.consultas.cheques');
                Route::get('/historial-ventas', [ConsultaController::class, 'historialVentas'])
                    ->name('api.v1.consultas.historial-ventas');
                Route::get('/detalle-pedidos', [ConsultaController::class, 'detallePedidos'])
                    ->name('api.v1.consultas.detalle-pedidos');
            });

            Route::get('/integracion/logs', [IntegracionLogController::class, 'index'])
                ->name('api.v1.integracion.logs');
            Route::get('/dashboard/operativo', [DashboardController::class, 'operativo'])
                ->name('api.v1.dashboard.operativo');
            Route::get('/dashboard/resumen-mensual', [DashboardController::class, 'resumenMensual'])
                ->name('api.v1.dashboard.resumen-mensual');

            Route::middleware('admin.security.enabled')->prefix('admin')->group(function (): void {
                Route::get('/roles', [AdminRoleController::class, 'index'])->name('api.v1.admin.roles.index');
                Route::post('/roles', [AdminRoleController::class, 'store'])->name('api.v1.admin.roles.store');
                Route::put('/roles/{id}', [AdminRoleController::class, 'update'])->name('api.v1.admin.roles.update');
                Route::delete('/roles/{id}', [AdminRoleController::class, 'destroy'])->name('api.v1.admin.roles.destroy');
                Route::get('/roles/{id}/atributos', [AdminRoleController::class, 'showAttributes'])->name('api.v1.admin.roles.atributos.show');
                Route::put('/roles/{id}/atributos', [AdminRoleController::class, 'updateAttributes'])->name('api.v1.admin.roles.atributos.update');
                Route::get('/permisos', [AdminPermisoController::class, 'index'])->name('api.v1.admin.permisos.index');
                Route::post('/permisos', [AdminPermisoController::class, 'store'])->name('api.v1.admin.permisos.store');
                Route::put('/permisos/{id}', [AdminPermisoController::class, 'update'])->name('api.v1.admin.permisos.update');
                Route::delete('/permisos/{id}', [AdminPermisoController::class, 'destroy'])->name('api.v1.admin.permisos.destroy');
                Route::post('/permisos/batch', [AdminPermisoController::class, 'batch'])->name('api.v1.admin.permisos.batch');
                Route::get('/usuarios', [AdminUsuarioLookupController::class, 'index'])->name('api.v1.admin.usuarios.index');
            });
        });
    });
});
