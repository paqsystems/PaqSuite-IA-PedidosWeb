<?php

namespace App\Services\PedidosWeb;

use App\Models\User;
use App\Services\Visibility\PedidosWebVisibilityGuard;
use App\Services\Visibility\VisibleClientsResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Consulta historial de ventas detallado — columnas ERP.
 *
 * @see docs/02-producto/PedidosWeb/consulta-historial-ventas.md
 */
final class HistorialVentasConsultaService
{
    public function __construct(
        private readonly VisibleClientsResolver $visibleClientsResolver,
        private readonly PedidosWebVisibilityGuard $visibilityGuard,
        private readonly PedidosWebParameterService $parameterService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listar(User $user, array $filters): array
    {
        if (! Schema::hasTable('pq_pedidosweb_ventadetallada')) {
            return $this->emptyResult($filters);
        }

        $dias = $this->parameterService->getDiasVentasDetalladas();
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = min(100, max(1, (int) ($filters['page_size'] ?? 20)));

        $codCliente = $this->resolveCodCliente($user, $filters);
        $query = $this->buildQuery($codCliente, $user, $dias);

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($pageSize, ['*'], 'page', $page);

        $items = collect($paginator->items())
            ->map(fn (object $row): array => $this->mapRow($row))
            ->values()
            ->all();

        $fechaProceso = DB::table('pq_pedidosweb_ventadetallada')->max('fecha_proceso');

        return [
            'items' => $items,
            'page' => (int) $paginator->currentPage(),
            'page_size' => (int) $paginator->perPage(),
            'total' => (int) $paginator->total(),
            'total_pages' => (int) $paginator->lastPage(),
            'metadata' => [
                'fecha_proceso' => $fechaProceso !== null ? (string) $fechaProceso : null,
                'dias_ventas_detalladas' => $dias,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function resolveCodCliente(User $user, array $filters): ?string
    {
        $codCliente = filled($filters['cod_cliente'] ?? null)
            ? trim((string) $filters['cod_cliente'])
            : null;

        if ($codCliente !== null) {
            $this->visibilityGuard->ensureCodClienteVisible($user, $codCliente);

            return $codCliente;
        }

        return null;
    }

    private function buildQuery(?string $codCliente, User $user, int $dias): Builder
    {
        $fechaDesde = Carbon::today()->subDays($dias)->startOfDay();

        $query = DB::table('pq_pedidosweb_ventadetallada as v')
            ->select([
                'v.cod_cli',
                'v.razon_soci',
                'v.n_remito',
                'v.t_comp',
                'v.n_comp',
                'v.fecha_emi',
                'v.cond_vta',
                'v.porc_desc',
                'v.cotiz',
                'v.moneda',
                'v.total_comp',
                'v.cod_transp',
                'v.nom_transp',
                'v.cod_articu',
                'v.descripcio',
                'v.cod_dep',
                'v.um',
                'v.cantidad',
                'v.precio',
                'v.tot_s_imp',
                'v.n_comp_rem',
                'v.cant_rem',
                'v.fecha_rem',
            ])
            ->where('v.fecha_emi', '>=', $fechaDesde)
            ->orderByDesc('v.fecha_emi')
            ->orderBy('v.cod_cli')
            ->orderBy('v.n_comp');

        if ($codCliente !== null) {
            $query->where('v.cod_cli', $codCliente);
        } else {
            $query->whereIn(
                'v.cod_cli',
                $this->visibleClientsResolver->visibleClientsForUser($user)->select('cod_client')
            );
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRow(object $row): array
    {
        return [
            'codCliente' => (string) ($row->cod_cli ?? ''),
            'razonSocial' => (string) ($row->razon_soci ?? ''),
            'nRemito' => (string) ($row->n_remito ?? ''),
            'tipo' => (string) ($row->t_comp ?? ''),
            'numero' => (string) ($row->n_comp ?? ''),
            'fechaEmision' => $row->fecha_emi !== null
                ? Carbon::parse((string) $row->fecha_emi)->toIso8601String()
                : null,
            'condVta' => $row->cond_vta !== null ? (int) $row->cond_vta : null,
            'porcDesc' => round((float) ($row->porc_desc ?? 0), 2),
            'cotiz' => round((float) ($row->cotiz ?? 0), 2),
            'moneda' => (string) ($row->moneda ?? ''),
            'totalComp' => round((float) ($row->total_comp ?? 0), 2),
            'codTransp' => (string) ($row->cod_transp ?? ''),
            'nomTransp' => (string) ($row->nom_transp ?? ''),
            'codArticulo' => (string) ($row->cod_articu ?? ''),
            'descripcion' => (string) ($row->descripcio ?? ''),
            'codDep' => (string) ($row->cod_dep ?? ''),
            'um' => (string) ($row->um ?? ''),
            'cantidad' => round((float) ($row->cantidad ?? 0), 2),
            'precio' => round((float) ($row->precio ?? 0), 2),
            'totSinImp' => round((float) ($row->tot_s_imp ?? 0), 2),
            'nCompRem' => (string) ($row->n_comp_rem ?? ''),
            'cantRem' => round((float) ($row->cant_rem ?? 0), 2),
            'fechaRem' => $row->fecha_rem !== null
                ? Carbon::parse((string) $row->fecha_rem)->toIso8601String()
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function emptyResult(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = min(100, max(1, (int) ($filters['page_size'] ?? 20)));
        $dias = $this->parameterService->getDiasVentasDetalladas();

        return [
            'items' => [],
            'page' => $page,
            'page_size' => $pageSize,
            'total' => 0,
            'total_pages' => 0,
            'metadata' => [
                'fecha_proceso' => null,
                'dias_ventas_detalladas' => $dias,
            ],
        ];
    }
}
