<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Aislamiento SQL Server: lecturas sin bloqueo en sesión, escrituras en READ COMMITTED.
 */
final class SqlServerIsolation
{
    public static function transaction(Closure $callback, int $attempts = 1): mixed
    {
        return DB::transaction(function () use ($callback) {
            self::useReadCommittedForWrite();

            return $callback();
        }, $attempts);
    }

    public static function useReadCommittedForWrite(): void
    {
        if (self::usesSqlServer()) {
            DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        }
    }

    public static function usesSqlServer(): bool
    {
        return DB::connection()->getDriverName() === 'sqlsrv';
    }
}
