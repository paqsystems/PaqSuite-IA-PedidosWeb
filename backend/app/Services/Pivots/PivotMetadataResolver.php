<?php

namespace App\Services\Pivots;

use App\Exceptions\PivotFlowException;
use App\Support\PivotErrorCodes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class PivotMetadataResolver
{
    /**
     * @return array<string, mixed>
     */
    public function resolveMetadata(string $consultaId): array
    {
        $consulta = $this->findActiveConsulta($consultaId);
        $pivotHabilitado = (bool) $consulta->pivot_habilitado;

        $campos = $this->resolveCampos($consultaId);
        $validaciones = $this->loadValidaciones($consultaId);
        $filtrosGenerales = $this->buildFiltrosGenerales($validaciones);
        $restricciones = $this->buildRestricciones($validaciones);

        $configuracionGeneral = $this->decodeJsonObject($consulta->configuracion_general_json) ?? [
            'mostrarGrillaYPivot' => $pivotHabilitado,
            'vistaInicial' => 'grilla',
        ];

        if (! $pivotHabilitado) {
            $configuracionGeneral['permiteCambiarAVistaPivot'] = false;
        }

        $pivotBase = $pivotHabilitado
            ? $this->decodeJsonObject($consulta->pivot_base_json)
            : null;

        if ($pivotHabilitado) {
            $this->assertPivotBaseIntegrity($pivotBase, $campos);
        }

        return [
            'consultaId' => (string) $consulta->consulta_id,
            'versionDefinicion' => (int) $consulta->version_definicion,
            'pivotHabilitado' => $pivotHabilitado,
            'admiteDrilldown' => (bool) $consulta->admite_drilldown,
            'configuracionGeneral' => $configuracionGeneral,
            'pivotBase' => $pivotBase ?? new \stdClass(),
            'campos' => $campos,
            'filtrosGenerales' => $filtrosGenerales,
            'restricciones' => $restricciones,
            'exportacion' => $this->decodeJsonObject($consulta->exportacion_json) ?? new \stdClass(),
            'persistencia' => $this->decodeJsonObject($consulta->persistencia_json) ?? new \stdClass(),
        ];
    }

    /**
     * @return object{consulta_id: string, procedimiento_host: string, pivot_habilitado: bool|int, admite_drilldown: bool|int, version_definicion: int, fuente_tipo: string, fuente_nombre: string, pivot_base_json: string, configuracion_general_json: ?string, exportacion_json: ?string, persistencia_json: ?string}
     */
    public function findActiveConsulta(string $consultaId): object
    {
        if (! Schema::hasTable('pq_pivots_consultas')) {
            throw new PivotFlowException(
                PivotErrorCodes::consultaNotFound,
                'pivot.consultaNotFound',
                404
            );
        }

        $consulta = DB::table('pq_pivots_consultas')
            ->where('consulta_id', $consultaId)
            ->where('activo', true)
            ->first();

        if ($consulta === null) {
            throw new PivotFlowException(
                PivotErrorCodes::consultaNotFound,
                'pivot.consultaNotFound',
                404
            );
        }

        return $consulta;
    }

    /**
     * @param  array<int, array<string, mixed>>  $campos
     */
    public function assertPivotBaseIntegrity(?array $pivotBase, array $campos): void
    {
        if ($pivotBase === null || $pivotBase === []) {
            throw new PivotFlowException(
                PivotErrorCodes::metadataInvalid,
                'pivot.metadataInvalid',
                422
            );
        }

        $campoIds = collect($campos)->pluck('campoId')->all();
        $referencedIds = $this->collectPivotBaseCampoIds($pivotBase);

        foreach ($referencedIds as $campoId) {
            if (! in_array($campoId, $campoIds, true)) {
                throw new PivotFlowException(
                    PivotErrorCodes::metadataInvalid,
                    'pivot.metadataInvalid',
                    422
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveCampos(string $consultaId): array
    {
        if (! Schema::hasTable('pq_pivots_campos')) {
            return [];
        }

        $rows = DB::table('pq_pivots_campos')
            ->where('consulta_id', $consultaId)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        return $rows->map(function (object $row): array {
            $plantillaProps = $this->resolvePlantillaProperties($row->plantilla_global_id);
            $override = $this->decodeJsonObject($row->override_json) ?? [];
            $merged = array_merge($plantillaProps, $override);

            $rolesPermitidos = $merged['rolesPermitidos']
                ?? $this->decodeJsonArray($row->roles_permitidos_json)
                ?? [];

            $agregacionesPermitidas = $merged['agregacionesPermitidas']
                ?? $this->decodeJsonArray($row->agregaciones_permitidas_json);

            return [
                'campoId' => (string) $row->campo_id,
                'dataField' => (string) $row->nombre_tecnico,
                'caption' => (string) ($merged['nombreVisible'] ?? $row->nombre_visible),
                'tipoDato' => (string) ($merged['tipoDato'] ?? $row->tipo_dato),
                'rolCampo' => (string) ($merged['rolCampo'] ?? $row->rol_campo),
                'rolesPermitidos' => $rolesPermitidos,
                'agregacionDefault' => $merged['agregacionDefault'] ?? $row->agregacion_default,
                'agregacionesPermitidas' => $agregacionesPermitidas,
                'formato' => $merged['formato'] ?? $this->decodeJsonObject($row->formato_json),
            ];
        })->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function resolvePlantillaProperties(?string $plantillaId): array
    {
        if ($plantillaId === null || $plantillaId === '' || ! Schema::hasTable('pq_pivots_plantillas')) {
            return [];
        }

        $plantilla = DB::table('pq_pivots_plantillas')
            ->where('plantilla_id', $plantillaId)
            ->where('activo', true)
            ->first();

        if ($plantilla === null) {
            return [];
        }

        $props = $this->decodeJsonObject($plantilla->propiedades_json) ?? [];

        if (Schema::hasTable('pq_pivots_plantillas_det')) {
            $detalle = DB::table('pq_pivots_plantillas_det')
                ->where('plantilla_id', $plantillaId)
                ->get();

            foreach ($detalle as $item) {
                $decoded = json_decode((string) $item->valor, true);
                $props[(string) $item->propiedad] = json_last_error() === JSON_ERROR_NONE ? $decoded : $item->valor;
            }
        }

        return $props;
    }

    /**
     * @return array<int, object>
     */
    private function loadValidaciones(string $consultaId): array
    {
        if (! Schema::hasTable('pq_pivots_validaciones')) {
            return [];
        }

        return DB::table('pq_pivots_validaciones')
            ->where('consulta_id', $consultaId)
            ->where('activo', true)
            ->get()
            ->all();
    }

    /**
     * @param  array<int, object>  $validaciones
     * @return array<int, array<string, mixed>>
     */
    private function buildFiltrosGenerales(array $validaciones): array
    {
        $filtros = [];

        foreach ($validaciones as $validacion) {
            if ((string) $validacion->tipo_validacion !== 'filtro_obligatorio') {
                continue;
            }

            $config = $this->decodeJsonObject($validacion->configuracion_json);

            if ($config !== null) {
                $filtros[] = $config;
            }
        }

        return $filtros;
    }

    /**
     * @param  array<int, object>  $validaciones
     * @return array<string, mixed>
     */
    public function buildRestricciones(array $validaciones): array
    {
        $defaults = [
            'maximoFilas' => 10,
            'maximoColumnas' => 10,
            'maximoMetricas' => 15,
            'maximoRegistrosBase' => 5000,
            'bloquearSiExcedeVolumen' => true,
            'requiereFiltroPrevio' => false,
        ];

        foreach ($validaciones as $validacion) {
            $config = $this->decodeJsonObject($validacion->configuracion_json);

            if ($config === null) {
                continue;
            }

            if ((string) $validacion->tipo_validacion === 'restricciones') {
                $defaults = array_merge($defaults, $config);
            }
        }

        return $defaults;
    }

    /**
     * @param  array<string, mixed>  $pivotBase
     * @return array<int, string>
     */
    private function collectPivotBaseCampoIds(array $pivotBase): array
    {
        $ids = [];

        foreach (['filas', 'columnas'] as $area) {
            foreach ($pivotBase[$area] ?? [] as $campoId) {
                $ids[] = (string) $campoId;
            }
        }

        foreach ($pivotBase['valores'] ?? [] as $valor) {
            if (is_array($valor) && isset($valor['campoId'])) {
                $ids[] = (string) $valor['campoId'];
            }
        }

        foreach ($pivotBase['filtrosInternos'] ?? [] as $filtro) {
            if (is_array($filtro) && isset($filtro['campoId'])) {
                $ids[] = (string) $filtro['campoId'];
            } elseif (is_string($filtro)) {
                $ids[] = $filtro;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJsonObject(?string $json): ?array
    {
        if ($json === null || trim($json) === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @return array<int, mixed>|null
     */
    private function decodeJsonArray(?string $json): ?array
    {
        if ($json === null || trim($json) === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }
}
