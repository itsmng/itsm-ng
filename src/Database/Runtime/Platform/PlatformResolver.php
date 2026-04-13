<?php

namespace itsmng\Database\Runtime\Platform;

use itsmng\Database\Runtime\LegacyDatabase;

class PlatformResolver
{
    private static array $cache = [];

    public static function resolve(LegacyDatabase $database): DatabasePlatformInterface
    {
        $key = spl_object_id($database);
        return self::$cache[$key] ??= strtolower((string) $database->dbtype) === 'pgsql'
            ? new PostgreSqlPlatform($database)
            : new MySqlPlatform($database);
    }

    /**
     * Resolve a platform by database type string only (no connection required).
     * Use this only when a LegacyDatabase instance is not available and you only need
     * connection-independent methods like getIdentifierQuoteChar() or normalizeOperator().
     */
    public static function resolveByType(?string $dbtype = null): DatabasePlatformInterface
    {
        $type = strtolower((string) $dbtype);
        return self::$cache['type:' . $type] ??= $type === 'pgsql'
            ? new PostgreSqlPlatform(null)
            : new MySqlPlatform(null);
    }
}
