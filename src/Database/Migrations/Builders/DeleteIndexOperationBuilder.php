<?php

namespace itsmng\Database\Migrations\Builders;

use itsmng\Database\Migrations\MigrationBuilderInterface;

final class DeleteIndexOperationBuilder implements MigrationBuilderInterface
{
    public function __construct(
        private readonly string $table,
        private readonly string $name
    ) {
    }

    public function build(): array
    {
        return [
            'kind'  => 'delete_index',
            'table' => $this->table,
            'name'  => $this->name,
        ];
    }
}
