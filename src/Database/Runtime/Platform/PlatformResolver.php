<?php

namespace itsmng\Database\Runtime\Platform;

class PlatformResolver
{
    public static function resolve(?string $dbtype = null): DatabasePlatformInterface
    {
        return strtolower((string) $dbtype) === 'pgsql'
            ? new PostgreSqlPlatform()
            : new MySqlPlatform();
    }
}
