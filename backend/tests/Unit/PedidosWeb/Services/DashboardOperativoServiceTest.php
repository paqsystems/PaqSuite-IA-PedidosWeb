<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Services\PedidosWeb\DashboardOperativoService;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DashboardOperativoServiceTest extends TestCase
{
    #[Test]
    public function pedidoEstadoCeroSiempreCuentaEnKpi(): void
    {
        $now = Carbon::parse('2026-06-02 12:00:00');

        $this->assertTrue(DashboardOperativoService::pedidoIngresadoCuentaEnKpi(0, null, $now, 30));
    }

    #[Test]
    public function pedidoMenosUnoConActividadRecienteNoCuentaEnKpi(): void
    {
        $now = Carbon::parse('2026-06-02 12:00:00');
        $ultimaActividad = $now->copy()->subMinutes(5);

        $this->assertFalse(
            DashboardOperativoService::pedidoIngresadoCuentaEnKpi(-1, $ultimaActividad, $now, 30)
        );
    }

    #[Test]
    public function pedidoMenosUnoFueraDeVentanaMinutosWebCuentaEnKpi(): void
    {
        $now = Carbon::parse('2026-06-02 12:00:00');
        $ultimaActividad = $now->copy()->subMinutes(45);

        $this->assertTrue(
            DashboardOperativoService::pedidoIngresadoCuentaEnKpi(-1, $ultimaActividad, $now, 30)
        );
    }

    #[Test]
    public function pedidoMenosUnoSinActividadCuentaEnKpi(): void
    {
        $now = Carbon::parse('2026-06-02 12:00:00');

        $this->assertTrue(DashboardOperativoService::pedidoIngresadoCuentaEnKpi(-1, null, $now, 30));
    }

    #[Test]
    public function pedidoPendienteNoCuentaEnKpiIngresados(): void
    {
        $now = Carbon::parse('2026-06-02 12:00:00');

        $this->assertFalse(DashboardOperativoService::pedidoIngresadoCuentaEnKpi(1, null, $now, 30));
    }

    #[Test]
    public function resumenMensualSinTablasRetornaPorEstadoVacio(): void
    {
        $user = new \App\Models\User(['id' => 1]);
        $service = $this->app->make(DashboardOperativoService::class);

        if (! \Illuminate\Support\Facades\Schema::hasTable('pq_pedidosweb_pedidoscabecera')) {
            $result = $service->resumenMensual($user);
            $this->assertCount(6, $result['porEstado']);
            $this->assertSame((int) now()->year, $result['anio']);
            $this->assertSame((int) now()->month, $result['mes']);
        } else {
            $this->markTestSkipped('Requiere SQL Server con tablas PedidosWeb para resumen mensual.');
        }
    }
}
