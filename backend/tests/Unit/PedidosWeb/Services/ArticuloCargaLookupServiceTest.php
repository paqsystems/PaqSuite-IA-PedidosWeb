<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Services\PedidosWeb\ArticuloCargaLookupService;
use Carbon\CarbonImmutable;
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
        $this->assertStringContainsString('stock_base', $sql);
        $this->assertStringContainsString(' as [bs]', $sql);
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
    public function buscarBrowseDescuentaComprometidoWebDePedidosIngresados(): void
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
        $this->assertStringContainsString('[estado]', $sql);
    }

    #[Test]
    public function buscarBrowseCalculaDisponibleNetoBaseSumandoStockPorBase(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_articulos') || ! Schema::hasTable('pq_pedidosweb_stock')) {
            $this->markTestSkipped('Tablas de artículos/stock no disponibles.');
        }

        DB::table('pq_pedidosweb_articulos')->whereIn('codigo', ['ART-P1', 'ART-P2'])->delete();
        DB::table('pq_pedidosweb_stock')->whereIn('cod_articulo', ['ART-P1', 'ART-P2'])->delete();
        DB::table('pq_pedidosweb_pedidosdetalle')->where('cod_pedido', 'PED-LOOKUP-01')->delete();
        DB::table('pq_pedidosweb_pedidoscabecera')->where('cod_pedido', 'PED-LOOKUP-01')->delete();

        foreach (
            [
                ['codigo' => 'ART-P1', 'descripcion' => 'Presentacion 1', 'base' => 'BASE01'],
                ['codigo' => 'ART-P2', 'descripcion' => 'Presentacion 2', 'base' => 'BASE01'],
            ] as $articulo
        ) {
            DB::table('pq_pedidosweb_articulos')->insert([
                'codigo' => $articulo['codigo'],
                'descripcion' => $articulo['descripcion'],
                'base' => $articulo['base'],
            ]);
        }

        foreach (
            [
                ['cod_articulo' => 'ART-P1', 'stock' => 50, 'comprometido' => 5],
                ['cod_articulo' => 'ART-P2', 'stock' => 100, 'comprometido' => 15],
            ] as $stock
        ) {
            DB::table('pq_pedidosweb_stock')->insert($stock);
        }

        DB::table('pq_pedidosweb_pedidoscabecera')->insert([
            'cod_pedido' => 'PED-LOOKUP-01',
            'cod_cliente' => 'CLI001',
            'estado' => 0,
            'fecha' => CarbonImmutable::now()->format('Ymd H:i:s'),
            'total' => 0,
        ]);

        foreach (
            [
                ['renglon' => 1, 'cod_articulo' => 'ART-P1', 'cantidad' => 3],
                ['renglon' => 2, 'cod_articulo' => 'ART-P2', 'cantidad' => 5],
            ] as $detalle
        ) {
            DB::table('pq_pedidosweb_pedidosdetalle')->insert([
                'cod_pedido' => 'PED-LOOKUP-01',
                'renglon' => $detalle['renglon'],
                'cod_articulo' => $detalle['cod_articulo'],
                'cantidad' => $detalle['cantidad'],
                'precio' => 1,
                'importe_total' => 1,
            ]);
        }

        $service = $this->app->make(ArticuloCargaLookupService::class);
        $items = $service->buscar(q: null, pageSize: 50, codLista: 0, codigos: ['ART-P1', 'ART-P2']);

        $byCodigo = collect($items)->keyBy('codArticulo');

        $this->assertSame(122.0, $byCodigo->get('ART-P1')['disponibleNetoBase']);
        $this->assertSame(122.0, $byCodigo->get('ART-P2')['disponibleNetoBase']);
    }

    /**
     * @param  list<array{query: string}>  $queries
     */
    private function findBuscarSql(array $queries): string
    {
        foreach ($queries as $query) {
            $sql = strtolower($query['query']);
            if (str_contains($sql, 'pq_pedidosweb_articulos') && str_contains($sql, ' as [bs]')) {
                return $query['query'];
            }
        }

        $this->fail('No se encontró la consulta unificada de artículos para carga.');

        return '';
    }
}
