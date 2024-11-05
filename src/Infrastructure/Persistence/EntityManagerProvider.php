<?php

// src/Infrastructure/Persistence/EntityManagerProvider.php

namespace Itsmng\Infrastructure\Persistence;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

class EntityManagerProvider
{
    private static ?EntityManager $entityManager = null;

    public static function getEntityManager(): EntityManager
    {
        if (self::$entityManager === null) {
            $domainPath = realpath(__DIR__ . '/../../Domain');
            $config = ORMSetup::createAttributeMetadataConfiguration(
                paths: [$domainPath],
                isDevMode: true
            );

            $connectionParams = [
                'driver' => 'pdo_mysql',
                'host' => 'localhost',
                'user' => 'root',
                'password' => 'mypass',
                'dbname' => 'latest',
            ];
            $connection = DriverManager::getConnection($connectionParams, $config);

            self::$entityManager = new (EntityManager::class)($connection, $config);
        }

        return self::$entityManager;
    }
}
