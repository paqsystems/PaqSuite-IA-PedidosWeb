<?php

namespace App\Services\Visibility;

use App\Exceptions\AuthFlowException;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\User;
use App\Support\VisibilityErrorCodes;
use Illuminate\Support\Collection;

final class VisibilityDataService
{
    public function __construct(
        private readonly VisibleClientsResolver $visibleClientsResolver,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listVisibleClients(User $user): array
    {
        return $this->visibleClientsResolver->visibleClientsForUser($user)
            ->orderByRaw("COALESCE(NULLIF(LTRIM(RTRIM(razon_soci)), ''), nombre) ASC")
            ->get()
            ->map(static fn ($cliente): array => [
                'codCliente' => (string) $cliente->cod_client,
                'nombre' => (string) $cliente->nombre,
                'razonSocial' => trim((string) ($cliente->razon_soci ?? '')) !== ''
                    ? (string) $cliente->razon_soci
                    : (string) $cliente->nombre,
                'fantasia' => $cliente->fantasia !== null ? (string) $cliente->fantasia : null,
                'codVendedor' => $cliente->cod_vended !== null ? (string) $cliente->cod_vended : null,
                'email' => $cliente->e_mail !== null ? (string) $cliente->e_mail : null,
            ])
            ->all();
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
     * @return array<int, int>
     */
    private function dashboardStates(string $stateGroup): array
    {
        /** @var array<int, int> $states */
        $states = config('paqsuite_visibility.dashboardStates.'.$stateGroup, []);

        return $states;
    }
}
