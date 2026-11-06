<?php

use App\Http\Controllers\Api\V1\Gen\PedidosWebEmissionsController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas host GEN-15 (emisiones / diseñador) — CC #19.
 * Incluido dentro del grupo `api/v1` de routes/api.php.
 * GEN-13 task routes: paqsuite/laravel-core routes/tasks.php (PAQSUITE_TASKS_ROUTES_ENABLED).
 */
Route::get('/emissions/processes', [PedidosWebEmissionsController::class, 'processes']);
Route::get('/emissions/processes/{processCode}', [PedidosWebEmissionsController::class, 'showProcess']);
Route::get(
    '/emissions/design/processes/{processCode}/reports',
    [PedidosWebEmissionsController::class, 'designReports'],
);
Route::put(
    '/emissions/design/reports/{reportId}/layout',
    [PedidosWebEmissionsController::class, 'designUpdateLayout'],
)->whereNumber('reportId');
Route::post(
    '/emissions/design/reports/{reportId}/set-principal',
    [PedidosWebEmissionsController::class, 'designSetPrincipal'],
)->whereNumber('reportId');
Route::post('/emissions/jobs', [PedidosWebEmissionsController::class, 'storeJob']);
Route::get('/emissions/jobs/{jobId}', [PedidosWebEmissionsController::class, 'showJob']);
Route::get('/emissions/jobs/{jobId}/download', [PedidosWebEmissionsController::class, 'download']);
Route::post('/emissions/preview', [PedidosWebEmissionsController::class, 'preview']);
