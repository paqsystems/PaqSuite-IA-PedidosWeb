<?php

namespace App\Services\PedidosWeb\Integracion\Export;

use App\Support\ConsultaFechaProcesoFormatter;

final class ExportComprobantesService
{
    public function __construct(
        private readonly ExportComprobantesRepositoryInterface $repository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function list(ExportComprobantesQuery $query): array
    {
        $page = $this->repository->list($query);

        return [
            'items' => array_map(
                static fn ($item) => $item->toArray(),
                $page->items,
            ),
            'page' => $page->page,
            'pageSize' => $page->pageSize,
            'total' => $page->total,
            'totalPages' => $page->totalPages(),
            'metadata' => [
                'fechaProceso' => ConsultaFechaProcesoFormatter::now(),
            ],
        ];
    }
}