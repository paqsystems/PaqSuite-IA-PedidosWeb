<?php

namespace App\Services\PedidosWeb;

use App\Contracts\PedidosWeb\PedidoRepositoryInterface;
use App\Exceptions\PedidosWebBusinessException;
use App\Models\PqPedidoswebTratativa;
use App\Models\User;
use App\Services\Visibility\PedidosWebVisibilityGuard;

final class TratativaService
{
    public function __construct(
        private readonly PedidoRepositoryInterface $pedidoRepository,
        private readonly PedidosWebVisibilityGuard $pedidosWebVisibilityGuard,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function listar(string $codPresupuesto, User $user): array
    {
        $this->ensurePresupuestoActivo($codPresupuesto, $user);

        $tratativas = PqPedidoswebTratativa::query()
            ->with('resultado')
            ->where('cod_pedido', $codPresupuesto)
            ->orderByDesc('fecha_hora')
            ->get()
            ->map(static fn (PqPedidoswebTratativa $tratativa): array => [
                'id_tratativa' => (int) $tratativa->id_tratativa,
                'fecha_hora' => optional($tratativa->fecha_hora)?->toIso8601String(),
                'cod_usuario_web' => (string) $tratativa->cod_usuario_web,
                'comentario' => (string) $tratativa->comentario,
                'id_resultado' => $tratativa->id_resultado !== null ? (int) $tratativa->id_resultado : null,
                'resultado' => $tratativa->resultado?->descripcion,
                'proxima_fecha' => optional($tratativa->proxima_fecha)?->toIso8601String(),
                'proxima_accion' => $tratativa->proxima_accion,
            ])
            ->values()
            ->all();

        return [
            'items' => $tratativas,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function crear(string $codPresupuesto, array $payload, User $user): array
    {
        $this->ensurePresupuestoActivo($codPresupuesto, $user);

        $tratativa = PqPedidoswebTratativa::query()->create([
            'cod_pedido' => $codPresupuesto,
            'fecha_hora' => now(),
            'cod_usuario_web' => $user->codigo,
            'comentario' => (string) ($payload['comentario'] ?? ''),
            'id_resultado' => $payload['id_resultado'] ?? null,
            'proxima_fecha' => $payload['proxima_fecha'] ?? null,
            'proxima_accion' => $payload['proxima_accion'] ?? null,
        ]);

        return [
            'id_tratativa' => (int) $tratativa->id_tratativa,
        ];
    }

    private function ensurePresupuestoActivo(string $codPresupuesto, User $user): void
    {
        $this->pedidosWebVisibilityGuard->ensureComprobanteVisible($user, $codPresupuesto);
        $presupuesto = $this->pedidoRepository->findByCodPedido($codPresupuesto);

        if ($presupuesto === null) {
            throw new PedidosWebBusinessException(4000, 'business.notFound', 404);
        }

        if ((int) $presupuesto->estado !== 99) {
            throw new PedidosWebBusinessException(2000, 'business.tratativaOnlyForPresupuestoActivo', 422);
        }
    }
}
