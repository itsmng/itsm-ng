<?php

namespace itsmng\Database\Runtime\Capabilities;

interface SupportsIndexListing
{
    public function listIndexes($table);
}
