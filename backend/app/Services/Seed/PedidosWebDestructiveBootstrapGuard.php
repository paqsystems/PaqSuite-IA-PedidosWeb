<?php

namespace App\Services\Seed;

use RuntimeException;

/**
 * Evita DROP/recreate accidental sobre bases ERP compartidas (p. ej. Ankas_del_sur).
 */
final class PedidosWebDestructiveBootstrapGuard
{
    public function assertAllowed(string $operation): void
    {
        if ($this->isExplicitlyAllowed()) {
            return;
        }

        $database = strtolower(trim((string) config('database.connections.sqlsrv.database', '')));

        if ($database === '') {
            throw new RuntimeException("{$operation} abortado: DB_DATABASE vacío.");
        }

        if ($this->isDevDatabaseName($database)) {
            return;
        }

        throw new RuntimeException(
            "{$operation} abortado: la base [{$database}] no parece dedicada a dev/test. "
            .'Este comando ejecuta DROP TABLE sobre todas las pq_pedidosweb_* y PQ_parametros_gral. '
            .'Para continuar en esta base, definí ALLOW_PEDIDOSWEB_DESTRUCTIVE_BOOTSTRAP=true en .env '
            .'o usá una base con sufijo _dev / _test / _local.'
        );
    }

    private function isExplicitlyAllowed(): bool
    {
        return filter_var(
            env('ALLOW_PEDIDOSWEB_DESTRUCTIVE_BOOTSTRAP', false),
            FILTER_VALIDATE_BOOL
        );
    }

    private function isDevDatabaseName(string $database): bool
    {
        return (bool) preg_match('/(_dev|_test|_local|pedidosweb_dev)$/', $database);
    }
}
