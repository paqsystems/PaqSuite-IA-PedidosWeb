<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\UserMenuController;
use App\Http\Controllers\UserPreferencesController;
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

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
            Route::post('/auth/password/change', [AuthController::class, 'changePassword'])
                ->name('api.v1.auth.password.change');
            Route::get('/auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
            Route::get('/user/menu', UserMenuController::class)->name('api.v1.user.menu');
            Route::get('/users/me/preferences', [UserPreferencesController::class, 'show'])
                ->name('api.v1.users.me.preferences.show');
            Route::patch('/users/me/preferences', [UserPreferencesController::class, 'update'])
                ->name('api.v1.users.me.preferences.update');
            Route::patch('/users/me/preferences/locale', [UserPreferencesController::class, 'updateLocale'])
                ->name('api.v1.users.me.preferences.locale');
            Route::patch('/users/me/preferences/theme', [UserPreferencesController::class, 'updateTheme'])
                ->name('api.v1.users.me.preferences.theme');
        });
    });
});
