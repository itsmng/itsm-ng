<?php

namespace itsmng\Database\Schema\Dialect;

use RuntimeException;
use itsmng\Database\Runtime\DatabaseInterface;

class DialectResolver
{
    public function resolve(?DatabaseInterface $database = null): DialectInterface
    {
        $database ??= $GLOBALS['DB'] ?? null;

        if ($database instanceof DatabaseInterface && $database->getDbType() === 'pgsql') {
            return new PostgreSqlDialect();
        }

        if ($database instanceof DatabaseInterface) {
            return new MySqlDialect();
        }

        throw new RuntimeException('Unable to resolve database schema dialect.');
    }
}
