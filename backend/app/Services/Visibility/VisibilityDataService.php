<?php

namespace App\Services\Visibility;

use App\Exceptions\AuthFlowException;
use App\Models\PqPedidoswebClienteContacto;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\User;
use App\Support\VisibilityErrorCodes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class VisibilityDataService
{
    public function __construct(
        private readonly VisibleClientsResolver $visibleClientsResolver,
    ) {}

    /**
     * Lectura de maestros vía Eloquent (misma excepción SP que GET /api/v1/clientes vigente).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listVisibleClients(User $user): array
    {
        $clientes = $this->visibleClientsResolver->visibleClientsForUser($user)
            ->orderByRaw("COALESCE(NULLIF(LTRIM(RTRIM(razon_soci)), ''), nombre) ASC")
            ->get();

        $contactosByClient = $this->contactosByClientCodes($clientes->pluck('cod_client'));

        return $clientes
            ->map(static fn ($cliente): array => VisibleClientPayloadMapper::mapCliente(
                $cliente,
                $contactosByClient[(string) $cliente->cod_client] ?? []
            ))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function findVisibleClient(User $user, string $codCliente): array
    {
        $normalized = trim($codCliente);

        $cliente = $this->visibleClientsResolver->visibleClientsForUser($user)
            ->where('cod_client', $normalized)
            ->first();

        if ($cliente === null) {
            throw new AuthFlowException(
                VisibilityErrorCodes::resourceNotFound,
                'resource.notFound',
                404
            );
        }

        $contactosByClient = $this->contactosByClientCodes(collect([(string) $cliente->cod_client]));

        return VisibleClientPayloadMapper::mapCliente(
            $cliente,
            $contactosByClient[(string) $cliente->cod_client] ?? []
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function findVisibleComprobante(User $user, string $comprobanteId): array
    {
        $comprobante = PqPedidoswebPedidoCabecera::query()
            ->where('cod_pedido', $comprobanteId)
            ->whereIn(
                'cod_cliente',
                $this->visibleClientsResolver->visibleClientsForUser($user)->select('cod_client')
            )
            ->first();

        if ($comprobante === null) {
            throw new AuthFlowException(
                VisibilityErrorCodes::resourceNotFound,
                'resource.notFound',
                404
            );
        }

        return [
            'id' => (string) $comprobante->cod_pedido,
            'codCliente' => (string) $comprobante->cod_cliente,
            'codVendedor' => $comprobante->cod_vended !== null ? (string) $comprobante->cod_vended : null,
            'estado' => (int) $comprobante->estado,
            'fecha' => optional($comprobante->fecha)?->toIso8601String(),
            'total' => (float) $comprobante->total,
            'totalIva' => (float) $comprobante->total_iva,
            'observaciones' => $comprobante->observaciones !== null ? (string) $comprobante->observaciones : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildDashboardResumen(User $user): array
    {
        $visibleClientsQuery = $this->visibleClientsResolver->visibleClientsForUser($user);
        $visibleClientCodes = $visibleClientsQuery->pluck('cod_client');

        if ($visibleClientCodes->isEmpty()) {
            return [
                'visibleClientsCount' => 0,
                'activeQuotesCount' => 0,
                'enteredOrdersCount' => 0,
                'pendingOrdersCount' => 0,
                'activeQuotesTotal' => 0.0,
                'enteredOrdersTotal' => 0.0,
                'pendingOrdersTotal' => 0.0,
            ];
        }

        return [
            'visibleClientsCount' => $visibleClientCodes->count(),
            'activeQuotesCount' => $this->countComprobantesForStates($visibleClientCodes, 'activeQuotes'),
            'enteredOrdersCount' => $this->countComprobantesForStates($visibleClientCodes, 'enteredOrders'),
            'pendingOrdersCount' => $this->countComprobantesForStates($visibleClientCodes, 'pendingOrders'),
            'activeQuotesTotal' => $this->sumComprobantesForStates($visibleClientCodes, 'activeQuotes'),
            'enteredOrdersTotal' => $this->sumComprobantesForStates($visibleClientCodes, 'enteredOrders'),
            'pendingOrdersTotal' => $this->sumComprobantesForStates($visibleClientCodes, 'pendingOrders'),
        ];
    }

    /**
     * @param  Collection<int, string>  $visibleClientCodes
     */
    private function countComprobantesForStates(Collection $visibleClientCodes, string $stateGroup): int
    {
        return PqPedidoswebPedidoCabecera::query()
            ->whereIn('cod_cliente', $visibleClientCodes)
            ->whereIn('estado', $this->dashboardStates($stateGroup))
            ->count();
    }

    /**
     * @param  Collection<int, string>  $visibleClientCodes
     */
    private function sumComprobantesForStates(Collection $visibleClientCodes, string $stateGroup): float
    {
        return (float) PqPedidoswebPedidoCabecera::query()
            ->whereIn('cod_cliente', $visibleClientCodes)
            ->whereIn('estado', $this->dashboardStates($stateGroup))
            ->sum('total');
    }

    /**
     * Carga contactos en un query (sin N+1). Solo clientes del universo ya resuelto.
     *
     * @param  Collection<int, mixed>  $codigos
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function contactosByClientCodes(Collection $codigos): array
    {
        $normalized = $codigos
            ->map(static fn ($codigo): string => trim((string) $codigo))
            ->filter(static fn (string $codigo): bool => $codigo !== '')
            ->unique()
            ->values();

        if ($normalized->isEmpty() || ! Schema::hasTable('pq_pedidosweb_clientescontactos')) {
            return [];
        }

        $grouped = [];

        PqPedidoswebClienteContacto::query()
            ->whereIn('cod_client', $normalized->all())
            ->orderBy('cod_contacto')
            ->get()
            ->each(static function (PqPedidoswebClienteContacto $contacto) use (&$grouped): void {
                $key = (string) $contacto->cod_client;
                $grouped[$key][] = VisibleClientPayloadMapper::mapContacto($contacto);
            });

        return $grouped;
    }

    /**
     * @return array<int, int>
     */
    private function dashboardStates(string $stateGroup): array
    {
        /** @var array<int, int> $states */
        $states = config('paqsuite_visibility.dashboardStates.'.$stateGroup, []);

        return $states;
    }
}
