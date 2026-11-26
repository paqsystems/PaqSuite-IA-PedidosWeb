<?php

namespace App\Services\PedidosWeb\Integracion\Export\Dto;

final class ExportComprobantesPage
{
    /**
     * @param  list<ExportComprobantesItem>  $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int $page,
        public readonly int $pageSize,
        public readonly int $total,
    ) {}

    public function totalPages(): int
    {
        if ($this->total === 0) {
            return 0;
        }

        return (int) ceil($this->total / $this->pageSize);
    }
}