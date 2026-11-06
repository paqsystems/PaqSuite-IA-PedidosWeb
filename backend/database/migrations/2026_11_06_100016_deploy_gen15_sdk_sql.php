<?php

use App\Support\Installation\PedidosWebSdkSqlDeployer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Productivos Forge no corren paqsuite:installation:update (GEN-34).
 * Esta migración aplica CREATE/ALTER de SP GEN-15 + catálogo, sin DROP.
 */
return new class extends Migration
{
    public function up(): void
    {
        try {
            app(PedidosWebSdkSqlDeployer::class)->deployEmission();
        } catch (Throwable $exception) {
            Log::warning('GEN-15 SDK SQL deploy skipped: '.$exception->getMessage());
        }
    }

    public function down(): void
    {
        // CREATE OR ALTER; no revertir SP ni catálogo de emisión.
    }
};
