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
            // Create a simple "default" Doctrine ORM configuration for Attributes
            $config = ORMSetup::createAttributeMetadataConfiguration(
                paths: [__DIR__ . '/../../Domain'], // Adjust path to your domain entities
                isDevMode: true
            );

            // Configure the database connection
            $connectionParams = [
                'driver' => 'pdo_sqlite',
                'path' => __DIR__ . '/../../db.sqlite', // Adjust path as needed
            ];
            $connection = DriverManager::getConnection($connectionParams, $config);

            // Obtain the entity manager
            self::$entityManager = new (EntityManager::class)($connection, $config);
        }

        return self::$entityManager;
    }
}
