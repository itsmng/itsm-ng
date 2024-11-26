<?php

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\JsonFile;
use Doctrine\Migrations\DependencyFactory;
use Itsmng\Infrastructure\Persistence\EntityManagerProvider;

require_once "src/Infrastructure/Env.php";

$config = new JsonFile("migrations.json");

$entityManager = EntityManagerProvider::getEntityManager();

return DependencyFactory::fromEntityManager($config, new ExistingEntityManager($entityManager));

