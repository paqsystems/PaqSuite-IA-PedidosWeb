<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GridLayoutController;
use App\Http\Controllers\HealthController;
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
            Route::get('/clientes', [VisibilityDataController::class, 'clients'])
                ->name('api.v1.clientes.index');
            Route::get('/comprobantes/{id}', [VisibilityDataController::class, 'showComprobante'])
                ->name('api.v1.comprobantes.show');
            Route::get('/dashboard/resumen', [VisibilityDataController::class, 'dashboardResumen'])
                ->name('api.v1.dashboard.resumen');
        });
    });
});
