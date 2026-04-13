<?php

namespace itsmng\Database\Schema\Dialect;

use RuntimeException;
use itsmng\Database\Runtime\DatabaseInterface;

class DialectResolver
{
    public function resolve(?DatabaseInterface $database = null): DialectInterface
    {
        $database ??= $GLOBALS['DB'] ?? null;

        if (!$database instanceof DatabaseInterface) {
            throw new RuntimeException('Unable to resolve database schema dialect.');
        }

        return match ($database->getDbType()) {
            'pgsql' => new PostgreSqlDialect(),
            default => new MySqlDialect(),
        };
    }
}
