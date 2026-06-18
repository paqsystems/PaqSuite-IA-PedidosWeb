<?php

namespace App\Services\ExcelImport\Handlers;

use App\Models\User;
use App\Services\ExcelImport\Contracts\ExcelImportLotAwareHandler;
use App\Services\ExcelImport\Dto\ExcelImportLotContext;
use App\Services\ExcelImport\Dto\ExcelRowError;
use App\Services\ExcelImport\PedidoIndividual\PedidoIndividualLotValidator;
use App\Services\ExcelImport\PedidoIndividual\PedidoIndividualRowResolver;

final class PedidoIndividualExcelImportHandler implements ExcelImportLotAwareHandler
{
    public function __construct(
        private readonly PedidoIndividualRowResolver $rowResolver,
        private readonly PedidoIndividualLotValidator $lotValidator,
    ) {}

    public function validateBusinessRow(array $normalizedRow, ExcelImportLotContext $ctx): array
    {
        $user = $this->resolveUser($ctx);
        if ($user === null) {
            return [
                new ExcelRowError('negocio', trans('auth.unauthenticated'), null, null, null),
            ];
        }

        return $this->rowResolver->validateBusinessRow($normalizedRow, $user);
    }

    public function processRow(array $normalizedRow, ExcelImportLotContext $ctx): array
    {
        $user = $this->resolveUser($ctx);
        if ($user === null) {
            return $normalizedRow;
        }

        return $this->rowResolver->enrichRow($normalizedRow, $user);
    }

    public function validateBusinessLot(array $parsedRows, ExcelImportLotContext $ctx): array
    {
        return $this->lotValidator->apply($parsedRows);
    }

    private function resolveUser(ExcelImportLotContext $ctx): ?User
    {
        return User::query()->where('codigo', $ctx->usuarioEjecucion)->first();
    }
}
