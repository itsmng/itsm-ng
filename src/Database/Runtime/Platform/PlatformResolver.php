<?php

namespace itsmng\Database\Runtime\Platform;

use itsmng\Database\Runtime\LegacyDatabase;

class PlatformResolver
{
    private static array $type_cache = [];

    public static function resolve(LegacyDatabase $database): DatabasePlatformInterface
    {
        return self::resolveForType(strtolower((string) $database->dbtype), $database);
    }

    /**
     * Resolve a platform by database type string only (no connection required).
     * Use this only when a LegacyDatabase instance is not available and you only need
     * connection-independent methods like getIdentifierQuoteChar() or normalizeOperator().
     */
    public static function resolveByType(?string $dbtype = null): DatabasePlatformInterface
    {
        $type = strtolower((string) $dbtype);
        return self::$type_cache[$type] ??= self::resolveForType($type, null);
    }

    private static function resolveForType(string $dbtype, ?LegacyDatabase $database): DatabasePlatformInterface
    {
        return $dbtype === 'pgsql'
            ? new PostgreSqlPlatform($database)
            : new MySqlPlatform($database);
    }
}
