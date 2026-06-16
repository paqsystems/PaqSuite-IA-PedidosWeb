<?php

namespace Database\Seeders\ExcelImport;

use App\Models\PqExcelProceso;
use App\Models\PqExcelProcesoCampo;
use Illuminate\Database\Seeder;

final class ExcelImportCatalogPilotSeeder extends Seeder
{
    public function run(): void
    {
        $procedimientoHost = (string) config('paqsuite_visibility.procedimientos.cargaComprobantes', 'pw_cargapedidos');

        $proceso = PqExcelProceso::query()->updateOrCreate(
            ['codigo_proceso' => 'ARTICULOS_ALTA'],
            [
                'nombre_proceso' => 'Importacion de Articulos',
                'descripcion' => 'Importacion de articulos desde plantilla Excel',
                'permite_procesamiento_parcial' => false,
                'permite_solo_validar' => true,
                'genera_plantilla' => true,
                'handler_backend' => 'Importacion.Articulos.AltaHandler',
                'procedimiento_host' => $procedimientoHost,
                'formato_booleano_plantilla' => '0_1',
                'activo' => true,
                'usuario_alta' => 'system',
            ]
        );

        $campos = [
            [1, 'Codigo', 'codigo', 'codigo', 50, null, true, true, 'Debe venir como texto'],
            [2, 'Descripcion', 'descripcion', 'texto', 255, null, true, false, null],
            [3, 'Rubro', 'rubro', 'texto', 100, null, false, false, null],
            [4, 'Precio', 'precio', 'decimal', null, 2, false, false, null],
            [5, 'Fecha Alta', 'fecha_alta', 'fecha', null, null, false, false, null],
        ];

        foreach ($campos as [$orden, $nombreExcel, $interno, $tipo, $largo, $decimales, $oblig, $esCodigo, $obs]) {
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
