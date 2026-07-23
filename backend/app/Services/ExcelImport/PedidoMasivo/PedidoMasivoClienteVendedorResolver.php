<?php

namespace App\Services\ExcelImport\PedidoMasivo;

use App\Models\PqPedidoswebCliente;

class PedidoMasivoClienteVendedorResolver
{
    /** @var array<string, array{codVended: ?string, nombre: string}> */
    private array $cacheByCliente = [];

    /**
     * @return array{codVended: ?string, nombre: string}
     */
    public function resolve(string $codCliente): array
    {
        $codCliente = trim($codCliente);
        if ($codCliente === '') {
            return ['codVended' => null, 'nombre' => ''];
        }

        if (isset($this->cacheByCliente[$codCliente])) {
            return $this->cacheByCliente[$codCliente];
        }

        $cliente = PqPedidoswebCliente::query()
            ->with('vendedor')
            ->where('cod_client', $codCliente)
            ->first();

        $codVended = $cliente?->cod_vended;
        $codVended = is_string($codVended) ? trim($codVended) : null;
        if ($codVended === '') {
            $codVended = null;
        }

        $resolved = [
            'codVended' => $codVended,
            'nombre' => (string) ($cliente?->vendedor?->nombre ?? ''),
        ];

        $this->cacheByCliente[$codCliente] = $resolved;

        return $resolved;
    }
}
