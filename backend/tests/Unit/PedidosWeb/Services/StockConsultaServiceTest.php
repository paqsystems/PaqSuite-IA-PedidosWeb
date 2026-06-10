<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Services\PedidosWeb\StockConsultaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class StockConsultaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testListarSinTablaStockDevuelveListadoVacio(): void
    {
        if (Schema::hasTable('pq_pedidosweb_stock')) {
            $this->markTestSkipped('Requiere entorno sin tabla pq_pedidosweb_stock.');
        }

        $service = $this->app->make(StockConsultaService::class);
        $result = $service->listar(['page' => 1, 'page_size' => 20]);

        $this->assertSame([], $result['items']);
        $this->assertSame(0, $result['total']);
        $this->assertNull($result['metadata']['fecha_proceso']);
    }

    public function testListarCalculaDisponibleNetoYMetricasBase(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_stock')) {
            $this->markTestSkipped('Tablas de stock no disponibles en el entorno de test.');
        }

        $this->seedStockFixture();

        $service = $this->app->make(StockConsultaService::class);
        $result = $service->listar(['page' => 1, 'page_size' => 50]);

        $byCodigo = collect($result['items'])->keyBy('codArticulo');

        $this->assertSame(100.0, $byCodigo->get('ART-A')['stock']);
        $this->assertSame(10.0, $byCodigo->get('ART-A')['comprometido']);
        $this->assertSame(5.0, $byCodigo->get('ART-A')['comprometidoWeb']);
        $this->assertSame(85.0, $byCodigo->get('ART-A')['disponibleNeto']);

        $this->assertNull($byCodigo->get('ART-SIN-BASE')['stockBase']);

        $presentacion = $byCodigo->get('ART-P1');
        $this->assertSame('BASE01', $presentacion['codBase']);
        $this->assertSame(150.0, $presentacion['stockBase']);
        $this->assertSame(20.0, $presentacion['comprometidoBase']);
        $this->assertSame(8.0, $presentacion['comprometidoBaseWeb']);
        $this->assertSame(122.0, $presentacion['disponibleNetoBase']);
    }

    private function seedStockFixture(): void
    {
        DB::table('pq_pedidosweb_articulos')->whereIn('codigo', ['ART-A', 'ART-SIN-BASE', 'ART-P1', 'ART-P2'])->delete();
        DB::table('pq_pedidosweb_stock')->whereIn('cod_articulo', ['ART-A', 'ART-SIN-BASE', 'ART-P1', 'ART-P2'])->delete();
        DB::table('pq_pedidosweb_pedidosdetalle')->where('cod_pedido', 'PED-STK-01')->delete();
        DB::table('pq_pedidosweb_pedidoscabecera')->where('cod_pedido', 'PED-STK-01')->delete();

        foreach (
            [
                ['codigo' => 'ART-A', 'descripcion' => 'Articulo simple', 'base' => ''],
                ['codigo' => 'ART-SIN-BASE', 'descripcion' => 'Sin base', 'base' => ''],
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
                ['cod_articulo' => 'ART-A', 'stock' => 100, 'comprometido' => 10],
                ['cod_articulo' => 'ART-SIN-BASE', 'stock' => 40, 'comprometido' => 4],
                ['cod_articulo' => 'ART-P1', 'stock' => 50, 'comprometido' => 5],
                ['cod_articulo' => 'ART-P2', 'stock' => 100, 'comprometido' => 15],
            ] as $stock
        ) {
            DB::table('pq_pedidosweb_stock')->insert($stock);
        }

        DB::table('pq_pedidosweb_pedidoscabecera')->insert([
            'cod_pedido' => 'PED-STK-01',
            'cod_cliente' => 'CLI001',
            'estado' => 0,
            'fecha' => now(),
            'total' => 0,
        ]);

        foreach (
            [
                ['renglon' => 1, 'cod_articulo' => 'ART-A', 'cantidad' => 5],
                ['renglon' => 2, 'cod_articulo' => 'ART-P1', 'cantidad' => 3],
                ['renglon' => 3, 'cod_articulo' => 'ART-P2', 'cantidad' => 5],
            ] as $detalle
        ) {
            DB::table('pq_pedidosweb_pedidosdetalle')->insert([
                'cod_pedido' => 'PED-STK-01',
                'renglon' => $detalle['renglon'],
                'cod_articulo' => $detalle['cod_articulo'],
                'cantidad' => $detalle['cantidad'],
                'precio' => 1,
                'importe_total' => 1,
            ]);
        }
    }
}
