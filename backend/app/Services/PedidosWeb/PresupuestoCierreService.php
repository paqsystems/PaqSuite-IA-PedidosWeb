<?php

namespace App\Services\PedidosWeb;

use App\Contracts\PedidosWeb\PedidoRepositoryInterface;
use App\Exceptions\PedidosWebBusinessException;
use App\Models\PqPedidoswebMotivoCierre;
use App\Models\User;
use App\Services\Visibility\PedidosWebVisibilityGuard;

final class PresupuestoCierreService
{
    public function __construct(
        private readonly PedidoRepositoryInterface $pedidoRepository,
        private readonly PedidosWebParameterService $parameterService,
        private readonly PedidosWebVisibilityGuard $pedidosWebVisibilityGuard,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function cerrarRechazo(
        string $codPresupuesto,
        int $idMotivo,
        ?string $observacion,
        User $user
    ): array {
        $this->pedidosWebVisibilityGuard->ensureComprobanteVisible($user, $codPresupuesto);
        $presupuesto = $this->pedidoRepository->findByCodPedido($codPresupuesto, true);

        if ($presupuesto === null) {
            throw new PedidosWebBusinessException(4000, 'business.notFound', 404);
        }

        if ((int) $presupuesto->estado !== 99) {
            throw new PedidosWebBusinessException(2000, 'business.presupuestoNotEditable', 422);
        }

        $motivo = $this->resolveMotivo($idMotivo, 'negativo');

        $this->pedidoRepository->updateEstado($codPresupuesto, 98);

        $cierre = $this->pedidoRepository->insertPresupuestoCierre([
            'cod_presupuesto' => $codPresupuesto,
            'cod_pedido_generado' => null,
            'tipo_cierre' => 'negativo',
            'id_motivo' => (int) $motivo->id_motivo,
            'fecha_cierre' => now(),
            'cod_usuario_web' => $user->codigo,
            'observacion' => $observacion,
        ]);

        return [
            'id_cierre' => (int) $cierre->id_cierre,
            'cod_presupuesto' => $codPresupuesto,
            'estado' => 98,
        ];
    }

    public function cerrarPorConversion(string $codPresupuesto, string $codPedidoGenerado, User $user): void
    {
        $motivo = $this->resolveMotivo(
            $this->parameterService->getCodMotivoCierreExitoso(),
            'positivo'
        );

        $this->pedidoRepository->updateEstado($codPresupuesto, 98);

        $this->pedidoRepository->insertPresupuestoCierre([
            'cod_presupuesto' => $codPresupuesto,
            'cod_pedido_generado' => $codPedidoGenerado,
            'tipo_cierre' => 'positivo',
            'id_motivo' => (int) $motivo->id_motivo,
            'fecha_cierre' => now(),
            'cod_usuario_web' => $user->codigo,
            'observacion' => null,
        ]);
    }

    private function resolveMotivo(int $idMotivo, string $tipoCierre): PqPedidoswebMotivoCierre
    {
        $motivo = PqPedidoswebMotivoCierre::query()
            ->where('id_motivo', $idMotivo)
            ->where('activo', true)
            ->where('tipo_cierre', $tipoCierre)
            ->first();

        if ($motivo === null) {
            throw new PedidosWebBusinessException(2000, 'business.invalidMotivoCierre', 422);
        }

        return $motivo;
    }
}
