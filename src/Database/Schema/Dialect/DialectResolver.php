<?php

namespace itsmng\Database\Schema\Dialect;

use DBmysql;
use RuntimeException;

class DialectResolver
{
    public function resolve(?DBmysql $database = null): DialectInterface
    {
        $database ??= $GLOBALS['DB'] ?? null;

        if ($database !== null && property_exists($database, 'dbtype') && $database->dbtype === 'pgsql') {
            return new PostgreSqlDialect();
        }

        if ($database instanceof DBmysql) {
            return new MySqlDialect();
        }

        throw new RuntimeException('Unable to resolve database schema dialect.');
    }
}
