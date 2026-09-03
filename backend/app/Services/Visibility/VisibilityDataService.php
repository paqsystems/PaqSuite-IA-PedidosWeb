<?php

namespace App\Services\Visibility;

use App\Exceptions\AuthFlowException;
use App\Models\PqPedidoswebClienteContacto;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\User;
use App\Support\SqlSchemaPresence;
use App\Support\VisibilityErrorCodes;
use Illuminate\Database\Eloquent\Builder;

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

        $contactosByClient = $this->contactosGroupedForUser($user);

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

        return VisibleClientPayloadMapper::mapCliente(
            $cliente,
            $this->contactosOfClient($normalized)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function findVisibleComprobante(User $user, string $comprobanteId): array
    {
        $comprobante = $this->visibleClientsResolver
            ->joinVisibleClients(
                PqPedidoswebPedidoCabecera::query()->where('cod_pedido', $comprobanteId),
                $user,
                'pq_pedidosweb_pedidoscabecera.cod_cliente'
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
        $visibleClientsCount = $this->visibleClientsResolver->visibleClientsForUser($user)->count();

        if ($visibleClientsCount === 0) {
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
            'visibleClientsCount' => $visibleClientsCount,
            'activeQuotesCount' => $this->countComprobantesForStates($user, 'activeQuotes'),
            'enteredOrdersCount' => $this->countComprobantesForStates($user, 'enteredOrders'),
            'pendingOrdersCount' => $this->countComprobantesForStates($user, 'pendingOrders'),
            'activeQuotesTotal' => $this->sumComprobantesForStates($user, 'activeQuotes'),
            'enteredOrdersTotal' => $this->sumComprobantesForStates($user, 'enteredOrders'),
            'pendingOrdersTotal' => $this->sumComprobantesForStates($user, 'pendingOrders'),
        ];
    }

    private function countComprobantesForStates(User $user, string $stateGroup): int
    {
        return $this->comprobantesVisibleQuery($user)
            ->whereIn('estado', $this->dashboardStates($stateGroup))
            ->count();
    }

    private function sumComprobantesForStates(User $user, string $stateGroup): float
    {
        return (float) $this->comprobantesVisibleQuery($user)
            ->whereIn('estado', $this->dashboardStates($stateGroup))
            ->sum('total');
    }

    private function comprobantesVisibleQuery(User $user): Builder
    {
        return $this->visibleClientsResolver->joinVisibleClients(
            PqPedidoswebPedidoCabecera::query(),
            $user,
            'pq_pedidosweb_pedidoscabecera.cod_cliente'
        );
    }

    /**
     * Contactos del universo visible: JOIN a subconsulta de clientes, no lista IN.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function contactosGroupedForUser(User $user): array
    {
        if (! SqlSchemaPresence::hasTable('pq_pedidosweb_clientescontactos')) {
            return [];
        }

        $grouped = [];

        $this->visibleClientsResolver
            ->joinVisibleClients(
                PqPedidoswebClienteContacto::query(),
                $user,
                'pq_pedidosweb_clientescontactos.cod_client'
            )
            ->orderBy('pq_pedidosweb_clientescontactos.cod_contacto')
            ->get(['pq_pedidosweb_clientescontactos.*'])
            ->each(static function (PqPedidoswebClienteContacto $contacto) use (&$grouped): void {
                $key = (string) $contacto->cod_client;
                $grouped[$key][] = VisibleClientPayloadMapper::mapContacto($contacto);
            });

        return $grouped;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function contactosOfClient(string $codCliente): array
    {
        if ($codCliente === '' || ! SqlSchemaPresence::hasTable('pq_pedidosweb_clientescontactos')) {
            return [];
        }

        return PqPedidoswebClienteContacto::query()
            ->where('cod_client', $codCliente)
            ->orderBy('cod_contacto')
            ->get()
            ->map(static fn (PqPedidoswebClienteContacto $contacto): array => VisibleClientPayloadMapper::mapContacto($contacto))
            ->all();
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
