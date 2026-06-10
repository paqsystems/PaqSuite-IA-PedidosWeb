<?php

namespace App\Services\PedidosWeb;

use App\Models\User;
use App\Services\Auth\CommercialProfileResolver;

final class ParametrosCargaService
{
    public function __construct(
        private readonly CommercialProfileResolver $commercialProfileResolver,
        private readonly PedidosWebParameterService $parameterService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $functionalProfile = $this->resolveFunctionalProfile($user);
        $modificaFlags = $this->parameterService->resolveModificaFlags($functionalProfile);

        return [
            ...$modificaFlags,
            ...$this->parameterService->resolveClienteLeyendaFlags(),
            'functionalProfile' => $functionalProfile,
            'codMotivoCierreExitoso' => $this->parameterService->getCodMotivoCierreExitoso(),
            'noEliminaPedido' => $this->parameterService->getNoEliminaPedido(),
            'noModificaPedido' => $this->parameterService->getNoModificaPedido(),
            'cargaRecurrente' => $this->parameterService->getCargaRecurrente(),
        ];
    }

    private function resolveFunctionalProfile(User $user): string
    {
        $commercialProfile = $this->commercialProfileResolver->resolveForUser($user);

        if ($commercialProfile['cliente'] !== null) {
            return 'cliente';
        }

        if ($commercialProfile['vendedor'] !== null) {
            return $commercialProfile['vendedor']->supervisor ? 'supervisor' : 'vendedor';
        }

        return 'vendedor';
    }
}
