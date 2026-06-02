<?php

namespace App\Services\PedidosWeb;

use App\Contracts\PedidosWeb\ConsultaRepositoryInterface;
use App\Models\PqPedidoswebCheque;
use App\Models\PqPedidoswebDeuda;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\PqPedidoswebStock;
use App\Models\PqPedidoswebVentaDetallada;
use App\Models\User;
use App\Services\Visibility\PedidosWebVisibilityGuard;
use App\Services\Visibility\VisibleClientsResolver;
use App\Services\Visibility\VisibilityPermissionGuard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class ConsultaListadoService
{
    public function __construct(
        private readonly VisibleClientsResolver $visibleClientsResolver,
        private readonly ConsultaRepositoryInterface $consultaRepository,
        private readonly PedidosWebParameterService $parameterService,
        private readonly PedidosWebVisibilityGuard $pedidosWebVisibilityGuard,
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function pedidosIngresados(User $user, array $filters): array
    {
        $query = $this->baseComprobantesQuery($user)
            ->whereIn('estado', [0, -1])
            ->orderByDesc('fecha');
        $this->applyCodClienteFilter($user, $query, $filters);

        $permisos = $this->resolveCargaPermisos($user);
        $noModifica = $this->parameterService->getNoModificaPedido();
        $noElimina = $this->parameterService->getNoEliminaPedido();

        return $this->paginate($query, $filters, function (PqPedidoswebPedidoCabecera $item) use ($permisos, $noModifica, $noElimina): array {
            $estado = (int) $item->estado;

            return [
                ...$this->mapComprobanteItem($item, 'codPedido'),
                'puedeEditar' => $permisos['modi'] && ! $noModifica && in_array($estado, [0, -1], true),
                'puedeEliminar' => $permisos['baja'] && ! $noElimina && $estado === 0,
                'puedeCopiar' => $permisos['alta'],
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function pedidosPendientes(User $user, array $filters): array
    {
        $query = $this->baseComprobantesQuery($user)
            ->where('estado', 1)
            ->orderByDesc('fecha');
        $this->applyCodClienteFilter($user, $query, $filters);

        return $this->paginate($query, $filters, fn (PqPedidoswebPedidoCabecera $item): array => [
            ...$this->mapComprobanteItem($item, 'codPedido'),
            'puedeEditar' => false,
            'puedeEliminar' => false,
            'puedeCopiar' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function presupuestos(User $user, array $filters): array
    {
        $estado = (int) ($filters['estado'] ?? 99);

        $query = $this->baseComprobantesQuery($user)
            ->where('estado', $estado)
            ->with(['cliente', 'presupuestoCierre.motivo'])
            ->orderByDesc('fecha');
        $this->applyCodClienteFilter($user, $query, $filters);

        $permisos = $this->resolveCargaPermisos($user);
        $noModifica = $this->parameterService->getNoModificaPedido();
        $esActivo = $estado === 99;

        return $this->paginate($query, $filters, function (PqPedidoswebPedidoCabecera $item) use ($permisos, $noModifica, $esActivo): array {
            $row = [
                ...$this->mapComprobanteItem($item, 'codPresupuesto'),
                'puedeEditar' => $esActivo && $permisos['modi'] && ! $noModifica,
                'puedeConvertir' => $esActivo && $permisos['alta'],
                'puedeCerrar' => $esActivo && $permisos['modi'],
                'puedeCopiar' => $esActivo && $permisos['alta'],
            ];

            if (! $esActivo) {
                $row['cierre'] = $this->mapPresupuestoCierre($item);
            }

            return $row;
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function stock(array $filters): array
    {
        $query = PqPedidoswebStock::query()
            ->with('articulo')
            ->orderBy('cod_articulo');

        if (filled($filters['q'] ?? null)) {
            $query->where(function (Builder $builder) use ($filters): void {
                $search = '%'.trim((string) $filters['q']).'%';

                $builder->where('cod_articulo', 'like', $search)
                    ->orWhereHas('articulo', static fn (Builder $articulosQuery) => $articulosQuery->where('descripcion', 'like', $search));
            });
        }

        return $this->paginate($query, $filters, static fn (PqPedidoswebStock $item): array => [
            'codArticulo' => (string) $item->cod_articulo,
            'descripcion' => (string) ($item->articulo?->descripcion ?? ''),
            'stock' => (float) $item->stock,
            'comprometido' => (float) $item->comprometido,
            'fechaProceso' => optional($item->uma_fecha)?->toIso8601String(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function deuda(User $user, array $filters): array
    {
        $codCliente = $this->resolveCodCliente($user, $filters);

        $items = $codCliente !== null
            ? $this->consultaRepository->findDeudaByCodCliente($codCliente)->all()
            : PqPedidoswebDeuda::query()
                ->whereIn('cod_cliente', $this->visibleClientsResolver->visibleClientsForUser($user)->select('cod_client'))
                ->orderByDesc('fecha_vto')
                ->get()
                ->all();

        return $this->paginateCollection($items, $filters, static fn (PqPedidoswebDeuda $item): array => [
            'codCliente' => (string) $item->cod_cliente,
            'tipoComprobante' => (string) $item->tipo_comprobante,
            'nroComprobante' => (string) $item->nro_comprobante,
            'fecha' => optional($item->fecha)?->toIso8601String(),
            'fechaVto' => optional($item->fecha_vto)?->toIso8601String(),
            'saldo' => (float) $item->saldo,
        ], $items !== [] ? optional($items[0]->fecha_proceso ?? null)?->toIso8601String() : null);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function cheques(User $user, array $filters): array
    {
        $codCliente = $this->resolveCodCliente($user, $filters);

        $items = $codCliente !== null
            ? $this->consultaRepository->findChequesByCodClient($codCliente)->all()
            : PqPedidoswebCheque::query()
                ->whereIn('cod_client', $this->visibleClientsResolver->visibleClientsForUser($user)->select('cod_client'))
                ->where('fecha', '>=', Carbon::today())
                ->orderByDesc('fecha')
                ->get()
                ->all();

        return $this->paginateCollection($items, $filters, static fn (PqPedidoswebCheque $item): array => [
            'codCliente' => (string) $item->cod_client,
            'numero' => (string) $item->numero,
            'banco' => (string) $item->banco,
            'importe' => (float) $item->importe,
            'fecha' => optional($item->fecha)?->toIso8601String(),
            'origen' => (string) $item->origen,
            'estado' => (string) $item->estado,
        ], $items !== [] ? optional($items[0]->fecha_proceso ?? null)?->toIso8601String() : null);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function historialVentas(User $user, array $filters): array
    {
        $codCliente = $this->resolveCodCliente($user, $filters);
        $dias = $this->parameterService->getDiasVentasDetalladas();
        $fechaDesde = Carbon::today()->subDays($dias);

        $query = PqPedidoswebVentaDetallada::query()
            ->where('fecha', '>=', $fechaDesde)
            ->orderByDesc('fecha');

        if ($codCliente !== null) {
            $query->where('cod_client', $codCliente);
        } else {
            $query->whereIn('cod_client', $this->visibleClientsResolver->visibleClientsForUser($user)->select('cod_client'));
        }

        $result = $this->paginate($query, $filters, static fn (PqPedidoswebVentaDetallada $item): array => [
            'codCliente' => (string) ($item->cod_client ?? ''),
            'fecha' => isset($item->fecha) ? Carbon::parse((string) $item->fecha)->toIso8601String() : null,
            'codArticulo' => (string) ($item->cod_articulo ?? ''),
            'descripcion' => (string) ($item->descripcion ?? ''),
            'cantidad' => (float) ($item->cantidad ?? 0),
            'importe' => (float) ($item->importe ?? 0),
        ]);
        $result['metadata']['dias_ventas_detalladas'] = $dias;

        return $result;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function paginate(Builder $query, array $filters, callable $mapper): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = min(100, max(1, (int) ($filters['page_size'] ?? 20)));

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($pageSize, ['*'], 'page', $page);

        return [
            'items' => collect($paginator->items())->map($mapper)->values()->all(),
            'page' => (int) $paginator->currentPage(),
            'page_size' => (int) $paginator->perPage(),
            'total' => (int) $paginator->total(),
            'total_pages' => (int) $paginator->lastPage(),
            'metadata' => [
                'fecha_proceso' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * @param  list<object>  $items
     * @return array<string, mixed>
     */
    private function paginateCollection(
        array $items,
        array $filters,
        callable $mapper,
        ?string $fechaProceso = null
    ): array {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = min(100, max(1, (int) ($filters['page_size'] ?? 20)));
        $total = count($items);
        $offset = ($page - 1) * $pageSize;
        $slice = array_slice($items, $offset, $pageSize);

        return [
            'items' => array_values(array_map($mapper, $slice)),
            'page' => $page,
            'page_size' => $pageSize,
            'total' => $total,
            'total_pages' => (int) ceil($total / $pageSize),
            'metadata' => [
                'fecha_proceso' => $fechaProceso ?? now()->toIso8601String(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function resolveCodCliente(User $user, array $filters): ?string
    {
        if (! filled($filters['cod_cliente'] ?? null)) {
            return null;
        }

        $codCliente = (string) $filters['cod_cliente'];
        $this->pedidosWebVisibilityGuard->ensureCodClienteVisible($user, $codCliente);

        return $codCliente;
    }

    private function baseComprobantesQuery(User $user): Builder
    {
        return PqPedidoswebPedidoCabecera::query()
            ->with('cliente')
            ->whereIn('cod_cliente', $this->visibleClientsResolver->visibleClientsForUser($user)->select('cod_client'));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyCodClienteFilter(User $user, Builder $query, array $filters): void
    {
        if (! filled($filters['cod_cliente'] ?? null)) {
            return;
        }

        $codCliente = (string) $filters['cod_cliente'];
        $this->pedidosWebVisibilityGuard->ensureCodClienteVisible($user, $codCliente);
        $query->where('cod_cliente', $codCliente);
    }

    /**
     * @return array{alta: bool, modi: bool, baja: bool}
     */
    private function resolveCargaPermisos(User $user): array
    {
        $procedimiento = (string) config('paqsuite_visibility.procedimientos.cargaComprobantes');

        return [
            'alta' => $this->visibilityPermissionGuard->hasPermission($user, $procedimiento, 'alta'),
            'modi' => $this->visibilityPermissionGuard->hasPermission($user, $procedimiento, 'modi'),
            'baja' => $this->visibilityPermissionGuard->hasPermission($user, $procedimiento, 'baja'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapComprobanteItem(PqPedidoswebPedidoCabecera $item, string $codigoKey): array
    {
        return [
            $codigoKey => (string) $item->cod_pedido,
            'codCliente' => (string) $item->cod_cliente,
            'razonSocial' => (string) ($item->cliente?->nombre ?? ''),
            'estado' => (int) $item->estado,
            'fecha' => optional($item->fecha)?->toIso8601String(),
            'numeroVisible' => (int) ($item->nro_visible ?? 0),
            'guidSufijo' => strtoupper(substr((string) $item->cod_pedido, -6)),
            'total' => (float) $item->total,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPresupuestoCierre(PqPedidoswebPedidoCabecera $item): array
    {
        $cierre = $item->presupuestoCierre;

        return [
            'tipoCierre' => (string) ($cierre?->tipo_cierre ?? ''),
            'idMotivo' => $cierre?->id_motivo,
            'motivoDescripcion' => (string) ($cierre?->motivo?->descripcion ?? ''),
            'fechaCierre' => optional($cierre?->fecha_cierre)?->toIso8601String(),
            'codPedidoGenerado' => $cierre?->cod_pedido_generado,
            'observacion' => (string) ($cierre?->observacion ?? ''),
        ];
    }
}
