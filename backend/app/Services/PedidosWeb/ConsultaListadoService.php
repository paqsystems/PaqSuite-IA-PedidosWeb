<?php

namespace App\Services\PedidosWeb;

use App\Contracts\PedidosWeb\ConsultaRepositoryInterface;
use App\Models\PqPedidoswebClienteDireccionEntrega;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\User;
use App\Services\Visibility\PedidosWebVisibilityGuard;
use App\Services\Visibility\VisibleClientsResolver;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\ConsultaFechaProcesoFormatter;
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
        private readonly StockConsultaService $stockConsultaService,
        private readonly DeudaConsultaService $deudaConsultaService,
        private readonly ChequesConsultaService $chequesConsultaService,
        private readonly HistorialVentasConsultaService $historialVentasConsultaService,
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

        $permisos = $this->resolveCargaPermisos($user);

        return $this->paginate($query, $filters, fn (PqPedidoswebPedidoCabecera $item): array => [
            ...$this->mapComprobanteItem($item, 'codPedido'),
            'puedeEditar' => false,
            'puedeEliminar' => false,
            'puedeCopiar' => $permisos['alta'],
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
            ->with(['presupuestoCierre.motivo'])
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
        return $this->stockConsultaService->listar($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function deuda(User $user, array $filters): array
    {
        return $this->deudaConsultaService->listar($user, $filters);
    }

    /**
     * Consulta de cheques — join clientes y columnas ERP/legacy.
     *
     * @see docs/02-producto/PedidosWeb/consulta-cheques.md
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function cheques(User $user, array $filters): array
    {
        return $this->chequesConsultaService->listar($user, $filters);
    }

    /**
     * Consulta historial de ventas — columnas ERP ventadetallada.
     *
     * @see docs/02-producto/PedidosWeb/consulta-historial-ventas.md
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function historialVentas(User $user, array $filters): array
    {
        return $this->historialVentasConsultaService->listar($user, $filters);
    }

    /**
     * @return array<string, mixed>
     */
    public function mapComprobanteForConsulta(PqPedidoswebPedidoCabecera $item, string $codigoKey = 'codPedido'): array
    {
        return $this->mapComprobanteItem($item, $codigoKey);
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
                'fecha_proceso' => ConsultaFechaProcesoFormatter::now(),
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
                'fecha_proceso' => ConsultaFechaProcesoFormatter::format($fechaProceso) ?? ConsultaFechaProcesoFormatter::now(),
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
            ->with([
                'cliente',
                'vendedor',
                'condicionVenta',
                'transporte',
                'listaPrecios',
                'perfil',
            ])
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
            'nombreFantasia' => $this->resolveNombreFantasia($item),
            'fecha' => optional($item->fecha)?->toIso8601String(),
            'nivel' => $item->nivel !== null ? (int) $item->nivel : null,
            'observaciones' => (string) ($item->observaciones ?? ''),
            'incluyeIva' => (bool) $item->incluye_iva,
            'moneda' => (int) ($item->moneda ?? 1),
            'estado' => (int) $item->estado,
            'fechaModif' => optional($item->fecha_modif)?->toIso8601String(),
            'total' => (float) $item->total,
            'totalIva' => (float) ($item->total_iva ?? 0),
            'leyenda1' => (string) ($item->leyenda_1 ?? ''),
            'leyenda2' => (string) ($item->leyenda_2 ?? ''),
            'leyenda3' => (string) ($item->leyenda_3 ?? ''),
            'leyenda4' => (string) ($item->leyenda_4 ?? ''),
            'leyenda5' => (string) ($item->leyenda_5 ?? ''),
            'descuento' => (float) ($item->descuento ?? 0),
            'bonif1' => (float) ($item->bonif_1 ?? 0),
            'bonif2' => (float) ($item->bonif_2 ?? 0),
            'bonif3' => (float) ($item->bonif_3 ?? 0),
            'codPerfil' => (string) ($item->cod_perfil ?? ''),
            'perfilDescripcion' => (string) ($item->perfil?->descripcion ?? ''),
            'codVended' => (string) ($item->cod_vended ?? ''),
            'vendedorDescripcion' => (string) ($item->vendedor?->nombre ?? ''),
            'codCondvta' => $item->cod_condvta !== null ? (int) $item->cod_condvta : null,
            'condicionVentaDescripcion' => (string) ($item->condicionVenta?->descripcion ?? ''),
            'idDe' => $item->id_de !== null ? (int) $item->id_de : null,
            'direccionEntregaDescripcion' => $this->resolveDireccionEntregaDescripcion($item),
            'codTranspor' => (string) ($item->cod_transpor ?? ''),
            'transporteDescripcion' => (string) ($item->transporte?->descripcion ?? ''),
            'listaPrecios' => $item->lista_precios !== null ? (int) $item->lista_precios : null,
            'listaPreciosDescripcion' => (string) ($item->listaPrecios?->descripcion ?? ''),
            'expreso' => (string) ($item->expreso ?? ''),
            'expresoDire' => (string) ($item->expreso_dire ?? ''),
            'fechaEntrega' => optional($item->fecha_entrega)?->toIso8601String(),
            'usuarioCreacion' => (string) ($item->usuario_creacion ?? ''),
            'fechaCreacion' => optional($item->fecha_creacion)?->toIso8601String(),
            'usuarioModificacion' => (string) ($item->usuario_modificacion ?? ''),
            'fechahoraInicioProceso' => optional($item->fechahora_inicio_proceso)?->toIso8601String(),
            'fechahoraUltimaActividad' => optional($item->fechahora_ultima_actividad)?->toIso8601String(),
            'numeroVisible' => (int) ($item->nro_visible ?? 0),
            'guidSufijo' => strtoupper(substr((string) $item->cod_pedido, -6)),
        ];
    }

    private function resolveNombreFantasia(PqPedidoswebPedidoCabecera $item): string
    {
        $fantasia = trim((string) ($item->cliente?->fantasia ?? ''));

        if ($fantasia !== '') {
            return $fantasia;
        }

        return (string) ($item->cliente?->nombre ?? '');
    }

    private function resolveDireccionEntregaDescripcion(PqPedidoswebPedidoCabecera $item): string
    {
        if ($item->id_de === null || ! filled($item->cod_cliente)) {
            return '';
        }

        return (string) (PqPedidoswebClienteDireccionEntrega::query()
            ->where('cod_client', $item->cod_cliente)
            ->where('id_de', $item->id_de)
            ->value('direccion') ?? '');
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
