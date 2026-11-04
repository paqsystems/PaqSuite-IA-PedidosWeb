<?php

use App\Http\Controllers\DxReportingProxyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Proxy diseñador DevExpress Reporting (GEN-15)
|--------------------------------------------------------------------------
| El SPA en Vercel llama /DXXRD* same-origin; Vercel reescribe a este host
| Forge, que a su vez proxea al sidecar ASP.NET en loopback :5055.
*/

Route::any('DXXRD/{path?}', [DxReportingProxyController::class, 'forward'])->where('path', '.*');
Route::any('DXXRDV/{path?}', [DxReportingProxyController::class, 'forward'])->where('path', '.*');
Route::any('DXXQB/{path?}', [DxReportingProxyController::class, 'forward'])->where('path', '.*');
