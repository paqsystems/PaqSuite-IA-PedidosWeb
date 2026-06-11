<?php

namespace Database\Seeders\Pivots;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class PivotCatalogPilotSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('pq_pivots_consultas')) {
            return;
        }

        $this->seedPlantillaMetrica();
        $this->seedConsultaPiloto();
        $this->seedConsultaSoloGrilla();
        $this->seedSupervisorPivotConfig();
    }

    private function seedPlantillaMetrica(): void
    {
        DB::table('pq_pivots_plantillas')->updateOrInsert(
            ['plantilla_id' => 'PLANTILLA_METRICA_NUM'],
            [
                'nombre' => 'Métrica numérica estándar',
                'descripcion' => 'Defaults para campos numéricos agregables',
                'propiedades_json' => json_encode([
                    'tipoDato' => 'number',
                    'rolCampo' => 'metrica',
                    'agregacionDefault' => 'sum',
                    'agregacionesPermitidas' => ['sum', 'avg', 'count', 'min', 'max'],
                ], JSON_THROW_ON_ERROR),
                'activo' => true,
            ]
        );
    }

    private function seedConsultaPiloto(): void
    {
        $consultaId = 'CONSULTA_PILOTO_PIVOT';

        DB::table('pq_pivots_consultas')->updateOrInsert(
            ['consulta_id' => $consultaId],
            [
                'nombre' => 'Historial ventas (piloto pivot)',
                'descripcion' => 'Consulta piloto epic pivots — historial ventas',
                'fuente_tipo' => 'service',
                'fuente_nombre' => 'historial_ventas',
                'procedimiento_host' => 'pw_historialventas',
                'version_definicion' => 1,
                'pivot_habilitado' => true,
                'admite_drilldown' => true,
                'activo' => true,
                'pivot_base_json' => json_encode([
                    'filas' => ['codCliente'],
                    'columnas' => [],
                    'valores' => [['campoId' => 'cantidad', 'agregacion' => 'sum']],
                    'filtrosInternos' => [],
                    'mostrarSubtotales' => true,
                    'mostrarTotalesGenerales' => true,
                ], JSON_THROW_ON_ERROR),
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
            ]
        );

        $this->upsertCampo($consultaId, 'codCliente', [
            'nombre_tecnico' => 'codCliente',
            'nombre_visible' => 'Cliente',
            'tipo_dato' => 'string',
            'rol_campo' => 'dimension',
            'roles_permitidos_json' => json_encode(['fila', 'columna', 'filtro'], JSON_THROW_ON_ERROR),
            'orden' => 10,
        ]);

        $this->upsertCampo($consultaId, 'razonSocial', [
            'nombre_tecnico' => 'razonSocial',
            'nombre_visible' => 'Razón social',
            'tipo_dato' => 'string',
            'rol_campo' => 'dimension',
            'roles_permitidos_json' => json_encode(['fila', 'columna'], JSON_THROW_ON_ERROR),
            'orden' => 20,
        ]);

        $this->upsertCampo($consultaId, 'fechaEmision', [
            'nombre_tecnico' => 'fechaEmision',
            'nombre_visible' => 'Fecha emisión',
            'tipo_dato' => 'date',
            'rol_campo' => 'dimension',
            'roles_permitidos_json' => json_encode(['fila', 'columna'], JSON_THROW_ON_ERROR),
            'orden' => 30,
        ]);

        $this->upsertCampo($consultaId, 'cantidad', [
            'nombre_tecnico' => 'cantidad',
            'nombre_visible' => 'Cantidad',
            'tipo_dato' => 'number',
            'rol_campo' => 'metrica',
            'roles_permitidos_json' => json_encode(['valor'], JSON_THROW_ON_ERROR),
            'agregacion_default' => 'sum',
            'agregaciones_permitidas_json' => json_encode(['sum', 'avg', 'count'], JSON_THROW_ON_ERROR),
            'plantilla_global_id' => 'PLANTILLA_METRICA_NUM',
            'orden' => 40,
        ]);

        $this->upsertCampo($consultaId, 'totSinImp', [
            'nombre_tecnico' => 'totSinImp',
            'nombre_visible' => 'Total s/ imp.',
            'tipo_dato' => 'number',
            'rol_campo' => 'metrica',
            'roles_permitidos_json' => json_encode(['valor'], JSON_THROW_ON_ERROR),
            'agregacion_default' => 'sum',
            'agregaciones_permitidas_json' => json_encode(['sum', 'avg'], JSON_THROW_ON_ERROR),
            'plantilla_global_id' => 'PLANTILLA_METRICA_NUM',
            'orden' => 50,
        ]);

        DB::table('pq_pivots_validaciones')
            ->where('consulta_id', $consultaId)
            ->delete();

        DB::table('pq_pivots_validaciones')->insert([
            [
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
            ],
            [
                'consulta_id' => $consultaId,
                'tipo_validacion' => 'filtro_obligatorio',
                'configuracion_json' => json_encode([
                    'filtroId' => 'codCliente',
                    'dataField' => 'codCliente',
                    'caption' => 'Cliente',
                    'obligatorio' => true,
                    'tipoControl' => 'select',
                ], JSON_THROW_ON_ERROR),
                'activo' => true,
            ],
        ]);
    }

    private function seedConsultaSoloGrilla(): void
    {
        $consultaId = 'CONSULTA_SOLO_GRILLA';

        DB::table('pq_pivots_consultas')->updateOrInsert(
            ['consulta_id' => $consultaId],
            [
                'nombre' => 'Solo grilla (piloto)',
                'descripcion' => 'Consulta sin pivot habilitado',
                'fuente_tipo' => 'service',
                'fuente_nombre' => 'historial_ventas',
                'procedimiento_host' => 'pw_historialventas',
                'version_definicion' => 1,
                'pivot_habilitado' => false,
                'admite_drilldown' => false,
                'activo' => true,
                'pivot_base_json' => json_encode(['filas' => [], 'columnas' => [], 'valores' => []], JSON_THROW_ON_ERROR),
                'configuracion_general_json' => json_encode([
                    'mostrarGrillaYPivot' => false,
                    'vistaInicial' => 'grilla',
                ], JSON_THROW_ON_ERROR),
                'exportacion_json' => null,
                'persistencia_json' => null,
                'fecha_creacion' => now(),
                'usuario_creacion' => 'seed',
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function seedSupervisorPivotConfig(): void
    {
        if (! Schema::hasTable('pq_pivots_config')) {
            return;
        }

        $supervisorUserId = DB::table('users')->where('codigo', 'supervisor.mvp')->value('id');

        if ($supervisorUserId === null) {
            return;
        }

        $consultaId = 'CONSULTA_PILOTO_PIVOT';
        $configuracionJson = json_encode([
            'fields' => [
                [
                    'caption' => 'Cliente',
                    'dataField' => 'codCliente',
                    'dataType' => 'string',
                    'area' => 'row',
                ],
                [
                    'caption' => 'Cantidad',
                    'dataField' => 'cantidad',
                    'dataType' => 'number',
                    'area' => 'data',
                    'summaryType' => 'sum',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $existing = DB::table('pq_pivots_config')
            ->where('consulta_id', $consultaId)
            ->where('nombre', 'Vista resumen')
            ->where('eliminado', false)
            ->first();

        if ($existing === null) {
            DB::table('pq_pivots_config')->insert([
                'consulta_id' => $consultaId,
                'nombre' => 'Vista resumen',
                'configuracion_json' => $configuracionJson,
                'version_definicion_consulta' => 1,
                'created_by_user_id' => $supervisorUserId,
                'eliminado' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function upsertCampo(string $consultaId, string $campoId, array $attributes): void
    {
        $existing = DB::table('pq_pivots_campos')
            ->where('consulta_id', $consultaId)
            ->where('campo_id', $campoId)
            ->first();

        $payload = array_merge([
            'activo' => true,
            'agregacion_default' => null,
            'agregaciones_permitidas_json' => null,
            'formato_json' => null,
            'plantilla_global_id' => null,
            'override_json' => null,
        ], $attributes, [
            'consulta_id' => $consultaId,
            'campo_id' => $campoId,
        ]);

        if ($existing === null) {
            DB::table('pq_pivots_campos')->insert($payload);

            return;
        }

        DB::table('pq_pivots_campos')
            ->where('id', $existing->id)
            ->update($payload);
    }
}
