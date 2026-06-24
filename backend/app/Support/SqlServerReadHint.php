<?php

namespace App\Support;

/**
 * Hint explícito WITH (NOLOCK) para SQL crudo en SQL Server.
 *
 * Complementa isolation_level READ UNCOMMITTED de la conexión en consultas críticas
 * documentadas (legibilidad en SSMS y planes de ejecución).
 */
final class SqlServerReadHint
{
    public static function fromAs(string $table, string $alias): string
    {
        return self::bracket($table).' AS '.self::bracket($alias).' WITH (NOLOCK)';
    }

    private static function bracket(string $identifier): string
    {
        return '['.str_replace(']', ']]', $identifier).']';
    }
}
