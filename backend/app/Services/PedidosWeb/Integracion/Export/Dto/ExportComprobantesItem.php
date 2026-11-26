<?php

namespace App\Services\PedidosWeb\Integracion\Export\Dto;

final class ExportComprobantesItem
{
    /**
     * @param  list<array<string, mixed>>  $renglones
     * @param  array<string, mixed>|null  $cierre
     */
    public function __construct(
        public readonly array $cabecera,
        public readonly array $renglones,
        public readonly ?array $cierre,
    ) {}

    /**
     * @return array{cabecera: array<string, mixed>, renglones: list<array<string, mixed>>, cierre: array<string, mixed>|null}
     */
    public function toArray(): array
    {
        return [
            'cabecera' => $this->cabecera,
            'renglones' => $this->renglones,
            'cierre' => $this->cierre,
        ];
    }
}