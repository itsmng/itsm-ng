<?php

namespace itsmng\Database\Migrations;

use DBmysql;

class MigrationHistoryRepository
{
    public const TABLE = 'glpi_schema_migrations';

    public function __construct(
        private readonly DBmysql $database
    ) {
    }

    public function ensureTable(): void
    {
        if ($this->database->tableExists(self::TABLE, false)) {
            return;
        }

        $this->database->queryOrDie(
            "CREATE TABLE `glpi_schema_migrations` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `version` VARCHAR(32) NOT NULL,
              `migration` VARCHAR(255) NOT NULL,
              `applied_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `batch` INT(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE KEY `version` (`version`),
              KEY `batch` (`batch`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
            'Create schema migration history table'
        );
    }

    /**
     * @return array<string, array{migration: string, batch: int}>
     */
    public function applied(): array
    {
        $this->ensureTable();

        $result = $this->database->queryOrDie(
            'SELECT `version`, `migration`, `batch` FROM `glpi_schema_migrations` ORDER BY `version` ASC',
            'Load applied schema migrations'
        );

        $applied = [];
        while ($row = $this->database->fetchAssoc($result)) {
            $applied[$row['version']] = [
                'migration' => $row['migration'],
                'batch'     => (int) $row['batch'],
            ];
        }

        return $applied;
    }

    public function nextBatch(): int
    {
        $this->ensureTable();
        $result = $this->database->queryOrDie(
            'SELECT MAX(`batch`) AS `batch` FROM `glpi_schema_migrations`',
            'Compute next schema migration batch'
        );
        $row = $this->database->fetchAssoc($result);

        return ((int) ($row['batch'] ?? 0)) + 1;
    }

    public function record(string $version, string $migration, int $batch): void
    {
        $this->ensureTable();
        $this->database->insertOrDie(self::TABLE, [
            'version'   => $version,
            'migration' => $migration,
            'batch'     => $batch,
        ], 'Record applied schema migration');
    }

    public function delete(string $version): void
    {
        $this->ensureTable();
        $this->database->deleteOrDie(self::TABLE, [
            'version' => $version,
        ], 'Delete applied schema migration');
    }

    /**
     * @return array<int, array{version: string, migration: string, batch: int}>
     */
    public function latestBatchMigrations(): array
    {
        $this->ensureTable();
        $result = $this->database->queryOrDie(
            'SELECT MAX(`batch`) AS `batch` FROM `glpi_schema_migrations`',
            'Load latest schema migration batch'
        );
        $row = $this->database->fetchAssoc($result);
        $batch = (int) ($row['batch'] ?? 0);
        if ($batch === 0) {
            return [];
        }

        $result = $this->database->queryOrDie(
            'SELECT `version`, `migration`, `batch` FROM `glpi_schema_migrations` WHERE `batch` = ' . $batch . ' ORDER BY `version` DESC',
            'Load latest schema migration entries'
        );

        $migrations = [];
        while ($migration = $this->database->fetchAssoc($result)) {
            $migrations[] = [
                'version'   => $migration['version'],
                'migration' => $migration['migration'],
                'batch'     => (int) $migration['batch'],
            ];
        }

        return $migrations;
    }
}
