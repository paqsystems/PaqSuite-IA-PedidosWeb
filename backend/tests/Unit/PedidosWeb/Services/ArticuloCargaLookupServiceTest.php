<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Services\PedidosWeb\ArticuloCargaLookupService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ArticuloCargaLookupServiceTest extends TestCase
{
    #[Test]
    public function buscarBrowseUsaUnaSolaQueryDesdeArticulos(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_articulos')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_articulos no disponible.');
        }

        DB::enableQueryLog();

        $service = $this->app->make(ArticuloCargaLookupService::class);
        $service->buscar(q: null, pageSize: 5, codLista: 0);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $sql = strtolower($this->findBuscarSql($queries));

        $this->assertStringContainsString('pq_pedidosweb_articulos', $sql);
        $this->assertStringContainsString('[pq_pedidosweb_stock] as [b]', $sql);
        $this->assertStringNotContainsString('group by', $sql);
        $this->assertStringNotContainsString('where [s].[cod_articulo] in', $sql);
    }

    #[Test]
    public function buscarBrowseConListaPreciosUnePreciosEnLaMismaQuery(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_articulos')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_articulos no disponible.');
        }

        if (! Schema::hasTable('pq_pedidosweb_listaprecios_articulos')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_listaprecios_articulos no disponible.');
        }

        DB::enableQueryLog();

        $service = $this->app->make(ArticuloCargaLookupService::class);
        $service->buscar(q: 'ART', pageSize: 3, codLista: 1);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $sql = strtolower($this->findBuscarSql($queries));

        $this->assertStringContainsString('pq_pedidosweb_listaprecios_articulos', $sql);
        $this->assertStringContainsString('pw_art_presentacion', $sql);
    }

    #[Test]
    public function buscarBrowseDescuentaComprometidoWebEnDisponible(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_articulos')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_articulos no disponible.');
        }

        DB::enableQueryLog();

        $service = $this->app->make(ArticuloCargaLookupService::class);
        $service->buscar(q: null, pageSize: 5, codLista: 0);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $sql = strtolower($this->findBuscarSql($queries));

        $this->assertStringContainsString('pq_pedidosweb_pedidosdetalle', $sql);
        $this->assertStringContainsString('pq_pedidosweb_pedidoscabecera', $sql);
        $this->assertStringContainsString('comprometido_web', $sql);
    }

    /**
     * @param  list<array{query: string}>  $queries
     */
    private function findBuscarSql(array $queries): string
    {
        foreach ($queries as $query) {
            $sql = strtolower($query['query']);
            if (str_contains($sql, 'pq_pedidosweb_articulos') && str_contains($sql, '[pq_pedidosweb_stock] as [b]')) {
                return $query['query'];
            }
        }

        $this->fail('No se encontró la consulta unificada de artículos para carga.');

        return '';
    }
}
