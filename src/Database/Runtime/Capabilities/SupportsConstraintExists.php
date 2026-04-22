<?php

namespace itsmng\Database\Runtime\Capabilities;

interface SupportsConstraintExists
{
    public function constraintExists($table, $constraint);
}
