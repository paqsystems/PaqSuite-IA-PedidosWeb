<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GridLayoutController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Api\V1\PedidosWeb\ArticuloController;
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
            Route::get('/users/me/preferences', [UserPreferencesController::class, 'show'])
                ->name('api.v1.users.me.preferences.show');
            Route::patch('/users/me/preferences', [UserPreferencesController::class, 'update'])
                ->name('api.v1.users.me.preferences.update');
            Route::patch('/users/me/preferences/locale', [UserPreferencesController::class, 'updateLocale'])
                ->name('api.v1.users.me.preferences.locale');
            Route::patch('/users/me/preferences/theme', [UserPreferencesController::class, 'updateTheme'])
                ->name('api.v1.users.me.preferences.theme');
            Route::get('/config/parametros-carga', [ParametrosCargaController::class, 'show'])
                ->name('api.v1.config.parametros-carga');
            Route::get('/articulos', [ArticuloController::class, 'index'])
                ->name('api.v1.articulos.index');
            Route::get('/clientes', [VisibilityDataController::class, 'clients'])
                ->name('api.v1.clientes.index');
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
            });

            Route::get('/integracion/logs', [IntegracionLogController::class, 'index'])
                ->name('api.v1.integracion.logs');
            Route::get('/dashboard/operativo', [DashboardController::class, 'operativo'])
                ->name('api.v1.dashboard.operativo');
        });
    });
});
