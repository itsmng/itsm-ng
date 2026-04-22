<?php

namespace itsmng\Database\Runtime\Platform;

class MySqlPlatform extends AbstractPlatform
{
    public function getDbType(): string
    {
        return 'mysql';
    }

    public function getIdentifierQuoteChar(): string
    {
        return '`';
    }

    public function normalizeOperator(string $operator): string
    {
        return $operator;
    }
}
