<?php

namespace itsmng\Database\Migrations\Builders;

use itsmng\Database\Migrations\MigrationBuilderInterface;

final class DeleteColumnOperationBuilder implements MigrationBuilderInterface
{
    /**
     * @param string[] $columns
     */
    public function __construct(
        private readonly string $table,
        private readonly array $columns
    ) {
    }

    public function build(): array
    {
        return [
            'kind'    => 'delete_column',
            'table'   => $this->table,
            'columns' => $this->columns,
        ];
    }
}
