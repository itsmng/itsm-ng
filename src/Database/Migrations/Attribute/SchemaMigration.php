<?php

namespace itsmng\Database\Migrations\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class SchemaMigration
{
    public function __construct(
        public readonly string $version,
        public readonly string $description = ''
    ) {
    }
}
