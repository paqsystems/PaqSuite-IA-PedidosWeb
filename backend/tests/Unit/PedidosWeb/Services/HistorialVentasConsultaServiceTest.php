<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Models\User;
use App\Services\PedidosWeb\HistorialVentasConsultaService;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

final class HistorialVentasConsultaServiceTest extends TestCase
{
    #[Test]
    public function buildQueryFiltraRangoCuandoHayFechaDesdeYHasta(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_ventadetallada')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_ventadetallada no disponible.');
        }

        $query = $this->invokeBuildQuery([
            'fecha_desde' => '2026-01-15',
            'fecha_hasta' => '2026-06-30',
        ]);

        $sql = strtolower($query->toSql());

        $this->assertStringContainsString('fecha_emi', $sql);
        $this->assertStringContainsString('cast', $sql);
        $this->assertStringContainsString('between', $sql);
        $this->assertStringNotContainsString('dateadd', $sql);
    }

    #[Test]
    public function buildQueryFiltraDesdeCuandoSoloHayFechaDesde(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_ventadetallada')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_ventadetallada no disponible.');
        }

        $query = $this->invokeBuildQuery([
            'fecha_desde' => '2026-03-01',
        ]);

        $sql = strtolower($query->toSql());

        $this->assertStringContainsString('fecha_emi', $sql);
        $this->assertStringContainsString('cast', $sql);
        $this->assertStringContainsString('>=', $sql);
        $this->assertStringNotContainsString('between', $sql);
        $this->assertStringNotContainsString('dateadd', $sql);
    }

    #[Test]
    public function buildQueryFiltraHastaCuandoSoloHayFechaHasta(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_ventadetallada')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_ventadetallada no disponible.');
        }

        $query = $this->invokeBuildQuery([
            'fecha_hasta' => '2026-12-31',
        ]);

        $sql = strtolower($query->toSql());

        $this->assertStringContainsString('fecha_emi', $sql);
        $this->assertStringContainsString('cast', $sql);
        $this->assertStringContainsString('<=', $sql);
        $this->assertStringNotContainsString('between', $sql);
        $this->assertStringNotContainsString('dateadd', $sql);
    }

    #[Test]
    public function buildQueryUsaDiasVentasDetalladasSinFechas(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_ventadetallada')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_ventadetallada no disponible.');
        }

        $query = $this->invokeBuildQuery([]);

        $sql = strtolower($query->toSql());

        $this->assertStringContainsString('dateadd', $sql);
        $this->assertStringContainsString('fecha_emi', $sql);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function invokeBuildQuery(array $filters): \Illuminate\Database\Query\Builder
    {
        $service = $this->app->make(HistorialVentasConsultaService::class);
        $method = new ReflectionMethod(HistorialVentasConsultaService::class, 'buildQuery');
        $method->setAccessible(true);

        $user = new User();

        /** @var \Illuminate\Database\Query\Builder $query */
        $query = $method->invoke($service, 'CLI001', $user, 90, $filters);

        return $query;
    }
}
