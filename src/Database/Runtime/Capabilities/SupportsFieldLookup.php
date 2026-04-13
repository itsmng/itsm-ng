<?php

namespace itsmng\Database\Runtime\Capabilities;

interface SupportsFieldLookup
{
    public function getField(string $table, string $field, $usecache = true): ?array;
}
