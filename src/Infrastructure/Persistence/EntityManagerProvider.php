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
        include_once __DIR__ . '/../Env.php';
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

            // Ensure PostgreSQL looks into the right schema when tables are not in "public"
            // Configure via DB_SCHEMA env var (e.g., DB_SCHEMA=orm_test) to set search_path accordingly
            if (($connectionParams['driver'] ?? null) === 'pdo_pgsql') {
                $schema = $_ENV['DB_SCHEMA'] ?? null;
                if (is_string($schema) && $schema !== '') {
                    // Validate identifier (simple safe pattern to avoid SQL injection on SET search_path)
                    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $schema)) {
                        throw new \InvalidArgumentException('Invalid DB_SCHEMA value');
                    }
                    $connection->executeStatement(sprintf('SET search_path TO %s, public', $schema));
                }
            }

            self::$entityManager = new (EntityManager::class)($connection, $config);
        }

        return self::$entityManager;
    }
}
