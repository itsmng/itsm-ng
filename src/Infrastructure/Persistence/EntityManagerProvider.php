<?php

// src/Infrastructure/Persistence/EntityManagerProvider.php

namespace Itsmng\Infrastructure\Persistence;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

class EntityManagerProvider
{
    private static ?EntityManager $entityManager = null;

    public static function getEntityManagerConfig(): array
    {
        if (isset($_ENV['DB_URL'])) {
            $dsnParser = new DsnParser();
            return $dsnParser->parse($_ENV['DB_URL']);
        } else {
            foreach ([ 'DB_DRIVER', 'DB_HOST', 'DB_USER', 'DB_PASSWORD', 'DB_NAME' ] as $envVar) {
                if (!isset($_ENV[$envVar])) {
                    throw new \Exception("$envVar environment variable not found");
                }
            }
            return [
                'driver'   => $_ENV['DB_DRIVER'],
                'host'     => $_ENV['DB_HOST'],
                'user'     => $_ENV['DB_USER'],
                'password' => $_ENV['DB_PASSWORD'],
                'dbname'   => $_ENV['DB_NAME'],
            ];
        }
    }

    public static function getEntityManager(): EntityManager
    {

        if (self::$entityManager === null) {
            $domainPath = realpath(__DIR__ . '/../../Domain');
            $config = ORMSetup::createAttributeMetadataConfiguration(
                paths: [$domainPath],
                isDevMode: true
            );

            $connectionParams = self::getEntityManagerConfig();
            $connection = DriverManager::getConnection($connectionParams, $config);
            $connection->connect();
            if (!$connection->isConnected()) {
                throw new \Exception('Could not connect to database');
            }

            self::$entityManager = new (EntityManager::class)($connection, $config);
        }

        return self::$entityManager;
    }
}
