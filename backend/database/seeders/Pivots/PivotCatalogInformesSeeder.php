<?php

namespace Database\Seeders\Pivots;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo pivot — informes CC PQ #4 (Detalle, Deuda, Cheques, Stock).
 */
final class PivotCatalogInformesSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('pq_pivots_consultas')) {
            return;
        }

        $this->seedDetallePedidos();
        $this->seedDeuda();
        $this->seedCheques();
        $this->seedStock();
    }

    private function seedDetallePedidos(): void
    {
        $consultaId = 'CONSULTA_DETALLE_PEDIDOS';

        $this->upsertConsulta($consultaId, [
            'nombre' => 'Detalle de pedidos',
            'descripcion' => 'Informe detalle pedidos — adopción pivot CC PQ #4',
            'fuente_tipo' => 'service',
            'fuente_nombre' => 'detalle_pedidos',
            'procedimiento_host' => 'pw_detallepedidos',
            'pivot_habilitado' => true,
            'admite_drilldown' => true,
            'pivot_base_json' => [
                'filas' => ['codCliente', 'razonSocial'],
                'columnas' => [],
                'valores' => [['campoId' => 'cantidad', 'agregacion' => 'sum']],
                'filtrosInternos' => [],
                'mostrarSubtotales' => true,
                'mostrarTotalesGenerales' => true,
            ],
        ]);

        $dimension = ['fila', 'columna', 'valor'];
        $metrica = ['fila', 'columna', 'valor'];

        $campos = [
            ['codCliente', 'Cliente', 'string', 'dimension', $dimension, null, 10],
            ['razonSocial', 'Razón social', 'string', 'dimension', $dimension, null, 20],
            ['nombreFantasia', 'Nombre comercial', 'string', 'dimension', $dimension, null, 30],
            ['fecha', 'Fecha', 'date', 'dimension', ['fila', 'columna'], null, 40],
            ['codArticulo', 'Artículo', 'string', 'dimension', $dimension, null, 50],
            ['descripcionArticulo', 'Descripción artículo', 'string', 'dimension', $dimension, null, 60],
            ['renglon', 'Renglón', 'number', 'dimension', $metrica, null, 70],
            ['cantidad', 'Cantidad', 'number', 'metrica', $metrica, 'sum', 80],
            ['precioNeto', 'Precio neto unit.', 'number', 'metrica', $metrica, 'sum', 90],
            ['importeNeto', 'Importe neto', 'number', 'metrica', $metrica, 'sum', 100],
            ['importeNetoConIva', 'Importe c/ IVA', 'number', 'metrica', $metrica, 'sum', 110],
            ['precioLista', 'Precio lista', 'number', 'metrica', $metrica, 'sum', 120],
            ['importeBruto', 'Importe bruto', 'number', 'metrica', $metrica, 'sum', 130],
            ['porcBonif', '% bonif. renglón', 'number', 'metrica', $metrica, 'avg', 140],
            ['estado', 'Estado', 'number', 'dimension', $metrica, null, 150],
            ['codPedido', 'Cód. pedido', 'string', 'dimension', $dimension, null, 160],
        ];

        $this->seedCampos($consultaId, $campos);
        $this->seedRestricciones($consultaId);
    }

    private function seedDeuda(): void
    {
        $consultaId = 'CONSULTA_DEUDA';

        $this->upsertConsulta($consultaId, [
            'nombre' => 'Deuda clientes',
            'descripcion' => 'Informe deuda — adopción pivot CC PQ #4',
            'fuente_tipo' => 'service',
            'fuente_nombre' => 'deuda',
            'procedimiento_host' => 'pw_deudaclientes',
            'pivot_habilitado' => true,
            'admite_drilldown' => false,
            'pivot_base_json' => [
                'filas' => ['codCliente', 'razonSocial'],
                'columnas' => ['tipo'],
                'valores' => [['campoId' => 'saldo', 'agregacion' => 'sum']],
                'filtrosInternos' => [],
                'mostrarSubtotales' => true,
                'mostrarTotalesGenerales' => true,
            ],
        ]);

        $dimension = ['fila', 'columna', 'valor'];
        $metrica = ['fila', 'columna', 'valor'];

        $campos = [
            ['codCliente', 'Cliente', 'string', 'dimension', $dimension, null, 10],
            ['razonSocial', 'Razón social', 'string', 'dimension', $dimension, null, 20],
            ['tipo', 'Tipo', 'string', 'dimension', $dimension, null, 30],
            ['numero', 'Número', 'string', 'dimension', $dimension, null, 40],
            ['fecha', 'Fecha emisión', 'date', 'dimension', ['fila', 'columna'], null, 50],
            ['vencimiento', 'Vencimiento', 'date', 'dimension', ['fila', 'columna'], null, 60],
            ['saldo', 'Saldo', 'number', 'metrica', $metrica, 'sum', 70],
        ];

        $this->seedCampos($consultaId, $campos);
        $this->seedRestricciones($consultaId);
    }

    private function seedCheques(): void
    {
        $consultaId = 'CONSULTA_CHEQUES';

        $this->upsertConsulta($consultaId, [
            'nombre' => 'Cheques en cartera',
            'descripcion' => 'Informe cheques — adopción pivot CC PQ #4',
            'fuente_tipo' => 'service',
            'fuente_nombre' => 'cheques',
            'procedimiento_host' => 'pw_consultacheques',
            'pivot_habilitado' => true,
            'admite_drilldown' => false,
            'pivot_base_json' => [
                'filas' => ['codCliente', 'banco'],
                'columnas' => ['estado'],
                'valores' => [['campoId' => 'importe', 'agregacion' => 'sum']],
                'filtrosInternos' => [],
                'mostrarSubtotales' => true,
                'mostrarTotalesGenerales' => true,
            ],
        ]);

        $dimension = ['fila', 'columna', 'valor'];
        $metrica = ['fila', 'columna', 'valor'];

        $campos = [
            ['interno', 'Interno', 'string', 'dimension', $dimension, null, 10],
            ['numero', 'Número', 'string', 'dimension', $dimension, null, 20],
            ['codCliente', 'Cliente', 'string', 'dimension', $dimension, null, 30],
            ['nombreCliente', 'Nombre cliente', 'string', 'dimension', $dimension, null, 40],
            ['banco', 'Banco', 'string', 'dimension', $dimension, null, 50],
            ['fecha', 'Fecha', 'date', 'dimension', ['fila', 'columna'], null, 60],
            ['importe', 'Importe', 'number', 'metrica', $metrica, 'sum', 70],
            ['origen', 'Origen', 'string', 'dimension', $dimension, null, 80],
            ['estado', 'Estado', 'string', 'dimension', $dimension, null, 90],
        ];

        $this->seedCampos($consultaId, $campos);
        $this->seedRestricciones($consultaId);
    }

    private function seedStock(): void
    {
        $consultaId = 'CONSULTA_STOCK';

        $this->upsertConsulta($consultaId, [
            'nombre' => 'Stock',
            'descripcion' => 'Informe stock — adopción pivot CC PQ #4',
            'fuente_tipo' => 'service',
            'fuente_nombre' => 'stock',
            'procedimiento_host' => 'pw_consultastock',
            'pivot_habilitado' => true,
            'admite_drilldown' => false,
            'pivot_base_json' => [
                'filas' => ['codArticulo', 'descripcion'],
                'columnas' => [],
                'valores' => [['campoId' => 'disponibleNeto', 'agregacion' => 'sum']],
                'filtrosInternos' => [],
                'mostrarSubtotales' => true,
                'mostrarTotalesGenerales' => true,
            ],
        ]);

        $dimension = ['fila', 'columna', 'valor'];
        $metrica = ['fila', 'columna', 'valor'];

        $campos = [
            ['codArticulo', 'Artículo', 'string', 'dimension', $dimension, null, 10],
            ['descripcion', 'Descripción', 'string', 'dimension', $dimension, null, 20],
            ['stock', 'Stock', 'number', 'metrica', $metrica, 'sum', 30],
            ['comprometido', 'Comprometido', 'number', 'metrica', $metrica, 'sum', 40],
            ['comprometidoWeb', 'Comprometido web', 'number', 'metrica', $metrica, 'sum', 50],
            ['disponibleNeto', 'Disponible neto', 'number', 'metrica', $metrica, 'sum', 60],
            ['codBase', 'Artículo base', 'string', 'dimension', $dimension, null, 70],
            ['stockBase', 'Stock base', 'number', 'metrica', $metrica, 'sum', 80],
            ['comprometidoBase', 'Comprometido base', 'number', 'metrica', $metrica, 'sum', 90],
            ['comprometidoBaseWeb', 'Comp. base web', 'number', 'metrica', $metrica, 'sum', 100],
            ['disponibleNetoBase', 'Disp. neto base', 'number', 'metrica', $metrica, 'sum', 110],
        ];

        $this->seedCampos($consultaId, $campos);
        $this->seedRestricciones($consultaId);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function upsertConsulta(string $consultaId, array $attributes): void
    {
        $pivotBase = $attributes['pivot_base_json'];
        unset($attributes['pivot_base_json']);

        DB::table('pq_pivots_consultas')->updateOrInsert(
            ['consulta_id' => $consultaId],
            array_merge([
                'version_definicion' => 1,
                'activo' => true,
                'configuracion_general_json' => json_encode([
                    'mostrarGrillaYPivot' => true,
                    'vistaInicial' => 'grilla',
                ], JSON_THROW_ON_ERROR),
                'exportacion_json' => json_encode([
                    'excelBasicoHabilitado' => true,
                    'excelFormateadoHabilitado' => true,
                    'incluirFiltrosAplicados' => true,
                    'incluirMetadatos' => true,
                ], JSON_THROW_ON_ERROR),
                'persistencia_json' => json_encode([
                    'habilitarDiseños' => true,
                ], JSON_THROW_ON_ERROR),
                'fecha_creacion' => now(),
                'usuario_creacion' => 'seed',
            ], $attributes, [
                'consulta_id' => $consultaId,
                'pivot_base_json' => json_encode($pivotBase, JSON_THROW_ON_ERROR),
            ])
        );
    }

    /**
     * @param  list<array{0: string, 1: string, 2: string, 3: string, 4: list<string>, 5: ?string, 6: int}>  $campos
     */
    private function seedCampos(string $consultaId, array $campos): void
    {
        foreach ($campos as [$campoId, $nombreVisible, $tipoDato, $rolCampo, $roles, $agregacionDefault, $orden]) {
            $payload = [
                'nombre_tecnico' => $campoId,
                'nombre_visible' => $nombreVisible,
                'tipo_dato' => $tipoDato,
                'rol_campo' => $rolCampo,
                'roles_permitidos_json' => json_encode($roles, JSON_THROW_ON_ERROR),
                'orden' => $orden,
                'activo' => true,
                'agregacion_default' => null,
                'agregaciones_permitidas_json' => null,
                'formato_json' => null,
                'plantilla_global_id' => null,
                'override_json' => null,
            ];

            if ($agregacionDefault !== null) {
                $payload['agregacion_default'] = $agregacionDefault;
                $payload['plantilla_global_id'] = 'PLANTILLA_METRICA_NUM';
            }

            $existing = DB::table('pq_pivots_campos')
                ->where('consulta_id', $consultaId)
                ->where('campo_id', $campoId)
                ->first();

            if ($existing === null) {
                DB::table('pq_pivots_campos')->insert(array_merge($payload, [
                    'consulta_id' => $consultaId,
                    'campo_id' => $campoId,
                ]));

                continue;
            }

            DB::table('pq_pivots_campos')
                ->where('id', $existing->id)
                ->update(array_merge($payload, [
                    'consulta_id' => $consultaId,
                    'campo_id' => $campoId,
                ]));
        }
    }

    private function seedRestricciones(string $consultaId): void
    {
        DB::table('pq_pivots_validaciones')
            ->where('consulta_id', $consultaId)
            ->where('tipo_validacion', 'restricciones')
            ->delete();

        DB::table('pq_pivots_validaciones')->insert([
            'consulta_id' => $consultaId,
            'tipo_validacion' => 'restricciones',
            'configuracion_json' => json_encode([
                'maximoFilas' => 10,
                'maximoColumnas' => 10,
                'maximoMetricas' => 15,
                'maximoRegistrosBase' => 5000,
                'bloquearSiExcedeVolumen' => true,
                'requiereFiltroPrevio' => false,
            ], JSON_THROW_ON_ERROR),
            'activo' => true,
        ]);
    }
}
