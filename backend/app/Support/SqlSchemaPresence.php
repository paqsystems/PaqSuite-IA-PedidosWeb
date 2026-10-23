<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Schema;

/**
 * Cache de presencia de tablas/columnas (SQL Server remoto: cada hasTable/hasColumn ~400ms).
 */
final class SqlSchemaPresence
{
    /** @var array<string, bool> */
    private static array $tables = [];

    /** @var array<string, array<string, true>> */
    private static array $columnMaps = [];

    public static function hasTable(string $table): bool
    {
        $key = strtolower($table);

        return self::$tables[$key] ??= Schema::hasTable($table);
    }

    public static function hasColumn(string $table, string $column): bool
    {
        return isset(self::columnMap($table)[strtolower($column)]);
    }

    /**
     * @return array<string, true>
     */
    public static function columnMap(string $table): array
    {
        $key = strtolower($table);

        if (isset(self::$columnMaps[$key])) {
            return self::$columnMaps[$key];
        }

        if (! self::hasTable($table)) {
            return self::$columnMaps[$key] = [];
        }

        $map = [];
        foreach (Schema::getColumnListing($table) as $name) {
            $map[strtolower((string) $name)] = true;
        }

        return self::$columnMaps[$key] = $map;
    }

    public static function forget(string $table): void
    {
        $key = strtolower($table);
        unset(self::$tables[$key], self::$columnMaps[$key]);
    }
}
