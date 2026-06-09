<?php

namespace Tests\Feature\Api\PedidosWeb;

use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AuthenticatesPaqTenant;
use Tests\TestCase;

final class ArticuloCargaIndexTest extends TestCase
{
    use AuthenticatesPaqTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAuthenticatesPaqTenant();
    }

    #[Test]
    public function indexBrowseExcluyeArticulosBase(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_articulos')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_articulos no disponible.');
        }

        if (! $this->ensureSeedSeguridadMvp()) {
            $this->markTestSkipped('Tenant desarrollo / SQL Server no disponible para test de articulos.');
        }

        $articuloBase = \Illuminate\Support\Facades\DB::selectOne(
            "SELECT TOP 1 a.codigo
             FROM pq_pedidosweb_articulos a
             WHERE EXISTS (
                 SELECT 1
                 FROM pq_pedidosweb_articulos p
                 WHERE NULLIF(LTRIM(RTRIM(CAST(p.base AS NVARCHAR(50)))), '') IS NOT NULL
                   AND LTRIM(RTRIM(CAST(p.base AS NVARCHAR(50)))) = LTRIM(RTRIM(CAST(a.codigo AS NVARCHAR(50))))
                   AND LTRIM(RTRIM(CAST(p.codigo AS NVARCHAR(50)))) <> LTRIM(RTRIM(CAST(a.codigo AS NVARCHAR(50))))
             )",
        );

        if ($articuloBase === null || ! filled($articuloBase->codigo ?? null)) {
            $this->markTestSkipped('Sin artículos BASE en catálogo para probar browse.');
        }

        $codigoBase = trim((string) $articuloBase->codigo);

        $browse = $this->getJson(
            '/api/v1/articulos?q='.urlencode($codigoBase).'&page_size=500',
            $this->authHeadersFor('supervisor.mvp'),
        );

        $browse->assertOk();
        $this->assertNotContains(
            $codigoBase,
            collect($browse->json('resultado.items'))->pluck('codArticulo')->all(),
        );
    }

    #[Test]
    public function indexPorCodigosNoAplicaFiltroBaseEnBrowse(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_articulos')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_articulos no disponible.');
        }

        if (! $this->ensureSeedSeguridadMvp()) {
            $this->markTestSkipped('Tenant desarrollo / SQL Server no disponible para test de articulos.');
        }

        $browse = $this->getJson('/api/v1/articulos?page_size=5', $this->authHeadersFor('supervisor.mvp'));
        $browse->assertOk();

        $primerCodigo = collect($browse->json('resultado.items'))->pluck('codArticulo')->first();
        if (! is_string($primerCodigo) || $primerCodigo === '') {
            $this->markTestSkipped('Sin artículos en catálogo para probar refresh por codigos.');
        }

        $porCodigos = $this->getJson(
            '/api/v1/articulos?codigos='.urlencode($primerCodigo).'&lista_precios=1',
            $this->authHeadersFor('supervisor.mvp'),
        );

        $porCodigos->assertOk();
        $this->assertContains($primerCodigo, collect($porCodigos->json('resultado.items'))->pluck('codArticulo')->all());
    }

    private function ensureSeedSeguridadMvp(): bool
    {
        try {
            $this->artisan('paqsuite:seed-seguridad-mvp')->assertExitCode(0);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
