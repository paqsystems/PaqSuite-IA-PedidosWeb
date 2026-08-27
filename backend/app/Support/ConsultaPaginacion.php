<?php

namespace App\Support;

/**
 * Paginación estándar de consultas PedidosWeb (`/api/v1/consultas/*`).
 *
 * Default 20 conserva contrato API; el máximo elevado permite que la UI web
 * (DataGrid cliente + pivot) cargue el dataset completo en pocas páginas.
 */
final class ConsultaPaginacion
{
    public const DEFAULT_PAGE_SIZE = 20;

    public const MAX_PAGE_SIZE = 1000;

    public static function resolvePage(mixed $page): int
    {
        return max(1, (int) ($page ?? 1));
    }

    public static function resolvePageSize(mixed $pageSize): int
    {
        if ($pageSize === null || $pageSize === '' || (int) $pageSize < 1) {
            return self::DEFAULT_PAGE_SIZE;
        }

        return min(self::MAX_PAGE_SIZE, (int) $pageSize);
    }
}
