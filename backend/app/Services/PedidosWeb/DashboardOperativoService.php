<?php

namespace App\Services\PedidosWeb;

use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\PqPedidoswebPedidoDetalle;
use App\Models\User;
use App\Services\Visibility\VisibleClientsResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

final class DashboardOperativoService
{
    /** @var list<int> */
    private const ESTADOS_MES_EN_CURSO = [0, 1, 2, 3, 98, 99];
    public function __construct(
        private readonly VisibleClientsResolver $visibleClientsResolver,
        private readonly PedidosWebParameterService $parameterService,
    ) {}

    /**
     * Indica si un pedido en estado 0 o -1 cuenta para KPI pedidos ingresados (regla AMB-C09).
     */
    public static function pedidoIngresadoCuentaEnKpi(
        int $estado,
        ?Carbon $ultimaActividad,
        Carbon $now,
        int $minutosWeb
    ): bool {
        if ($estado === 0) {
            return true;
        }

        if ($estado !== -1) {
            return false;
        }

        if ($ultimaActividad === null) {
            return true;
        }

        return $ultimaActividad->lt($now->copy()->subMinutes($minutosWeb));
    }

    /**
     * @return array<string, mixed>
     */
    public function obtener(User $user): array
    {
        if (! Schema::hasTable('pq_pedidosweb_pedidoscabecera')) {
            return $this->emptyResult();
        }

        $visibleClientsQuery = $this->visibleClientsResolver->visibleClientsForUser($user);

        if (! (clone $visibleClientsQuery)->exists()) {
            return $this->emptyResult();
        }

        $now = Carbon::now();
        $minutosWeb = $this->parameterService->getMinutosWeb();

        $presupuestosQuery = PqPedidoswebPedidoCabecera::query()
            ->whereIn('cod_cliente', $visibleClientsQuery->clone()->select('cod_client'))
            ->where('estado', 99);

        $pedidosIngresadosQuery = PqPedidoswebPedidoCabecera::query()
            ->whereIn('cod_cliente', $visibleClientsQuery->clone()->select('cod_client'))
            ->where(function (Builder $query) use ($now, $minutosWeb): void {
                $query->where('estado', 0)
                    ->orWhere(function (Builder $subQuery) use ($now, $minutosWeb): void {
                        $subQuery->where('estado', -1)
                            ->where(function (Builder $windowQuery) use ($now, $minutosWeb): void {
                                $windowQuery->whereNull('fechahora_ultima_actividad')
                                    ->orWhere('fechahora_ultima_actividad', '<', $now->copy()->subMinutes($minutosWeb));
                            });
                    });
            });

        $pedidosPendientesQuery = PqPedidoswebPedidoCabecera::query()
            ->whereIn('cod_cliente', $visibleClientsQuery->clone()->select('cod_client'))
            ->where('estado', 1);

        return [
            'moneda' => [
                'simbolo' => $this->parameterService->getMonedaSimbolo(),
                'codigo' => $this->parameterService->getMonedaCodigo(),
            ],
            'presupuestosActivos' => [
                'cantidad' => (int) (clone $presupuestosQuery)->count(),
                'importe' => (float) (clone $presupuestosQuery)->sum('total'),
                'unidades' => $this->sumUnidadesForQuery($presupuestosQuery),
            ],
            'pedidosIngresados' => [
                'cantidad' => (int) (clone $pedidosIngresadosQuery)->count(),
                'importe' => (float) (clone $pedidosIngresadosQuery)->sum('total'),
                'unidades' => $this->sumUnidadesForQuery($pedidosIngresadosQuery),
            ],
            'pedidosPendientes' => [
                'cantidad' => (int) (clone $pedidosPendientesQuery)->count(),
                'importe' => (float) (clone $pedidosPendientesQuery)->sum('total'),
                'unidades' => $this->sumUnidadesForQuery($pedidosPendientesQuery),
            ],
            'topClientePresupuestos' => $this->topCliente(clone $presupuestosQuery),
            'topClientePedidosIngresados' => $this->topCliente(clone $pedidosIngresadosQuery),
            'fechaCalculo' => $now->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function resumenMensual(User $user): array
    {
        $now = Carbon::now();

        if (! Schema::hasTable('pq_pedidosweb_pedidoscabecera')) {
            return $this->emptyMesEnCursoResult($now);
        }

        $visibleClientsQuery = $this->visibleClientsResolver->visibleClientsForUser($user);

        if (! (clone $visibleClientsQuery)->exists()) {
            return $this->emptyMesEnCursoResult($now);
        }

        $cabTable = (new PqPedidoswebPedidoCabecera)->getTable();
        $detTable = (new PqPedidoswebPedidoDetalle)->getTable();

        $rows = PqPedidoswebPedidoCabecera::query()
            ->from("{$cabTable} as cab")
            ->join("{$detTable} as det", 'det.cod_pedido', '=', 'cab.cod_pedido')
            ->whereIn('cab.cod_cliente', $visibleClientsQuery->clone()->select('cod_client'))
            ->whereIn('cab.estado', self::ESTADOS_MES_EN_CURSO)
            ->whereNotNull('cab.fecha')
            ->whereRaw('YEAR(cab.fecha) = YEAR(GETDATE())')
            ->whereRaw('MONTH(cab.fecha) = MONTH(GETDATE())')
            ->selectRaw('cab.estado as estado')
            ->selectRaw('COUNT(DISTINCT cab.cod_pedido) as cantidad')
            ->selectRaw('SUM(cab.total) as importe')
            ->selectRaw('SUM(det.cantidad) as unidades')
            ->groupBy('cab.estado')
            ->get()
            ->keyBy(static fn ($row): int => (int) $row->estado);

        return [
            'anio' => (int) $now->year,
            'mes' => (int) $now->month,
            'porEstado' => $this->mapPorEstado($rows),
            'fechaCalculo' => $now->toIso8601String(),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $rowsByEstado
     * @return list<array<string, int|float>>
     */
    private function mapPorEstado($rowsByEstado): array
    {
        $porEstado = [];

        foreach (self::ESTADOS_MES_EN_CURSO as $estado) {
            $row = $rowsByEstado->get($estado);
            $porEstado[] = [
                'estado' => $estado,
                'cantidad' => (int) ($row->cantidad ?? 0),
                'importe' => (float) ($row->importe ?? 0),
                'unidades' => (float) ($row->unidades ?? 0),
            ];
        }

        return $porEstado;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyMesEnCursoResult(Carbon $now): array
    {
        return [
            'anio' => (int) $now->year,
            'mes' => (int) $now->month,
            'porEstado' => $this->mapPorEstado(collect()),
            'fechaCalculo' => $now->toIso8601String(),
        ];
    }

    private function sumUnidadesForQuery(Builder $query): float
    {
        $codPedidos = (clone $query)->pluck('cod_pedido');

        if ($codPedidos->isEmpty()) {
            return 0.0;
        }

        return (float) PqPedidoswebPedidoDetalle::query()
            ->whereIn('cod_pedido', $codPedidos)
            ->sum('cantidad');
    }

    /**
     * @return array<string, mixed>
     */
    private function topCliente(Builder $query): array
    {
        $row = $query
            ->selectRaw('cod_cliente, SUM(total) AS importe')
            ->with('cliente')
            ->groupBy('cod_cliente')
            ->get()
            ->map(static fn (object $item): array => [
                'cod_client' => (string) $item->cod_cliente,
                'razon_social' => (string) ($item->cliente?->nombre ?? ''),
                'importe' => (float) $item->importe,
            ])
            ->sort(static function (array $a, array $b): int {
                if ((float) $a['importe'] !== (float) $b['importe']) {
                    return (float) $a['importe'] < (float) $b['importe'] ? 1 : -1;
                }

                if ((string) $a['razon_social'] !== (string) $b['razon_social']) {
                    return strcmp((string) $a['razon_social'], (string) $b['razon_social']);
                }

                return strcmp((string) $a['cod_client'], (string) $b['cod_client']);
            })
            ->first();

        if (! is_array($row)) {
            return [
                'cod_client' => '',
                'razon_social' => '',
                'importe' => 0,
            ];
        }

        return $row;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyResult(): array
    {
        return [
            'moneda' => [
                'simbolo' => $this->parameterService->getMonedaSimbolo(),
                'codigo' => $this->parameterService->getMonedaCodigo(),
            ],
            'presupuestosActivos' => ['cantidad' => 0, 'importe' => 0, 'unidades' => 0],
            'pedidosIngresados' => ['cantidad' => 0, 'importe' => 0, 'unidades' => 0],
            'pedidosPendientes' => ['cantidad' => 0, 'importe' => 0, 'unidades' => 0],
            'topClientePresupuestos' => ['cod_client' => '', 'razon_social' => '', 'importe' => 0],
            'topClientePedidosIngresados' => ['cod_client' => '', 'razon_social' => '', 'importe' => 0],
            'fechaCalculo' => now()->toIso8601String(),
        ];
    }
}
