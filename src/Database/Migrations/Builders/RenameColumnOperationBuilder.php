<?php

namespace itsmng\Database\Migrations\Builders;

use itsmng\Database\Migrations\MigrationBuilderInterface;

final class RenameColumnOperationBuilder implements MigrationBuilderInterface
{
    public function __construct(
        private readonly string $table,
        private readonly string $from,
        private readonly string $to
    ) {
    }

    public function build(): array
    {
        return [
            'kind'  => 'rename_column',
            'table' => $this->table,
            'from'  => $this->from,
            'to'    => $this->to,
        ];
    }
}
