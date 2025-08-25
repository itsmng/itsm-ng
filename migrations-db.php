<?php

use Itsmng\Infrastructure\Persistence\EntityManagerProvider;

include_once __DIR__ . '/src/Infrastructure/Env.php';

$connectionParams = EntityManagerProvider::getEntityManagerConfig();

return $connectionParams;
