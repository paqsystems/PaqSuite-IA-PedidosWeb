<?php

namespace Database\Seeders\ExcelImport;

use App\Models\PqExcelProceso;
use App\Models\PqExcelProcesoCampo;
use Illuminate\Database\Seeder;

final class PedidosWebExcelImportCatalogSeeder extends Seeder
{
    /** @var list<array{int, string, string, string, ?int, ?int, bool, bool, ?string}> */
    private const camposPedido = [
        [1, 'codigo cliente', 'cod_cliente', 'codigo', 20, null, true, true, null],
        [2, 'codigo de articulo', 'cod_articulo', 'codigo', 50, null, true, true, null],
        [3, 'cantidad', 'cantidad', 'decimal', null, 4, true, false, null],
        [4, 'precio lista', 'precio_lista', 'decimal', null, 4, false, false, null],
        [5, 'bonif renglon', 'bonif_renglon', 'decimal', null, 4, false, false, null],
        [6, 'codigo perfil', 'cod_perfil', 'codigo', 20, null, false, false, null],
        [7, 'condicion de venta', 'cod_condvta', 'entero', null, null, false, false, null],
        [8, 'codigo transporte', 'cod_transpor', 'codigo', 20, null, false, false, null],
        [9, 'direccion entrega', 'id_de', 'entero', null, null, false, false, null],
        [10, 'codigo lista', 'cod_lista', 'entero', null, null, false, false, null],
        [11, 'nivel', 'nivel', 'entero', null, null, false, false, null],
        [12, 'bonificacion 1', 'bonif1', 'decimal', null, 4, false, false, null],
        [13, 'bonificacion 2', 'bonif2', 'decimal', null, 4, false, false, null],
        [14, 'bonificacion 3', 'bonif3', 'decimal', null, 4, false, false, null],
        [15, 'expreso', 'expreso', 'texto', 80, null, false, false, null],
        [16, 'direccion expreso', 'expreso_dire', 'texto', 200, null, false, false, null],
        [17, 'fecha entrega', 'fecha_entrega', 'fecha', null, null, false, false, null],
        [18, 'observaciones', 'observaciones', 'texto', 500, null, false, false, null],
        [19, 'leyenda 1', 'leyenda1', 'texto', 255, null, false, false, null],
        [20, 'leyenda 2', 'leyenda2', 'texto', 255, null, false, false, null],
        [21, 'leyenda 3', 'leyenda3', 'texto', 255, null, false, false, null],
        [22, 'leyenda 4', 'leyenda4', 'texto', 255, null, false, false, null],
        [23, 'leyenda 5', 'leyenda5', 'texto', 255, null, false, false, null],
    ];

    public function run(): void
    {
        $procedimientoCarga = (string) config('paqsuite_visibility.procedimientos.cargaComprobantes', 'pw_cargapedidos');
        $procedimientoMasivo = (string) config('paqsuite_visibility.procedimientos.importacionMasiva', 'pw_importacionmasiva');

        $this->seedProceso(
            'PEDIDO_INDIVIDUAL',
            'Importacion pedido individual',
            'Importacion de un comprobante desde plantilla Excel',
            'Importacion.Pedidos.IndividualHandler',
            $procedimientoCarga,
        );

        $this->seedProceso(
            'PEDIDO_MASIVO',
            'Importacion pedido masivo',
            'Importacion multi-comprobante para grilla de importacion masiva',
            'Importacion.Pedidos.MasivoHandler',
            $procedimientoMasivo,
        );
    }

    private function seedProceso(
        string $codigoProceso,
        string $nombreProceso,
        string $descripcion,
        string $handlerBackend,
        string $procedimientoHost,
    ): void {
        $proceso = PqExcelProceso::query()->updateOrCreate(
            ['codigo_proceso' => $codigoProceso],
            [
                'nombre_proceso' => $nombreProceso,
                'descripcion' => $descripcion,
                'permite_procesamiento_parcial' => false,
                'permite_solo_validar' => false,
                'genera_plantilla' => true,
                'handler_backend' => $handlerBackend,
                'procedimiento_host' => $procedimientoHost,
                'formato_booleano_plantilla' => '0_1',
                'activo' => true,
                'usuario_alta' => 'system',
            ]
        );

        foreach (self::camposPedido as [$orden, $nombreExcel, $interno, $tipo, $largo, $decimales, $oblig, $esCodigo, $obs]) {
            PqExcelProcesoCampo::query()->updateOrCreate(
                [
                    'id_proceso' => $proceso->id,
                    'nombre_campo_interno' => $interno,
                ],
                [
                    'orden_campo' => $orden,
                    'nombre_columna_excel' => $nombreExcel,
                    'tipo_dato' => $tipo,
                    'largo_maximo' => $largo,
                    'cantidad_decimales' => $decimales,
                    'es_columna_obligatoria_estructural' => $oblig,
                    'es_campo_codigo' => $esCodigo,
                    'activo' => true,
                    'observaciones' => $obs,
                ]
            );
        }
    }
}
