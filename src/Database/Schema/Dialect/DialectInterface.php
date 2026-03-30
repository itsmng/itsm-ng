<?php

namespace itsmng\Database\Schema\Dialect;

interface DialectInterface
{
    public function name(): string;

    public function supportsTransactionalDdl(): bool;

    /**
     * @return string[]
     */
    public function createTableStatements(array $table): array;

    /**
     * @return string[]
     */
    public function renderOperation(array $operation): array;
}
