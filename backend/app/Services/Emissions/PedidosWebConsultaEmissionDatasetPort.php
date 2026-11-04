<?php

namespace App\Services\Emissions;

use App\Models\User;
use App\Services\PedidosWeb\ConsultaListadoService;
use App\Services\PedidosWeb\DeudaConsultaService;
use Illuminate\Support\Facades\Auth;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionDatasetPort;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionContext;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionDataset;
use PaqSuite\LaravelCore\Emissions\EmissionException;

final class PedidosWebConsultaEmissionDatasetPort implements EmissionDatasetPort
{
    private const PILOT_PAGE_SIZE = 500;

    public function __construct(
        private readonly ConsultaListadoService $consultaListadoService,
        private readonly DeudaConsultaService $deudaConsultaService,
    ) {
    }

    public function resolveDataset(EmissionContext $context): EmissionDataset
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            throw EmissionException::fromCode(4703);
        }

        $filters = ['page' => 1, 'page_size' => self::PILOT_PAGE_SIZE];

        $result = match ($context->processCode) {
            'pw_pedidosingresados' => $this->consultaListadoService->pedidosIngresados($user, $filters),
            'pw_deudaclientes' => $this->deudaConsultaService->listar($user, $filters),
            default => throw EmissionException::fromCode(4701),
        };

        /** @var list<array<string, mixed>> $items */
        $items = is_array($result['items'] ?? null) ? $result['items'] : [];

        return new EmissionDataset($items, $this->inferSchema($items), null);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, string>
     */
    private function inferSchema(array $items): array
    {
        $schema = [];
        $sample = $items[0] ?? [];
        foreach (array_keys($sample) as $key) {
            $schema[(string) $key] = 'string';
        }

        return $schema;
    }
}
