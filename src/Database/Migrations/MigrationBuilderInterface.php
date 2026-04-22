<?php

namespace itsmng\Database\Migrations;

interface MigrationBuilderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function build(): array;
}
