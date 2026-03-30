<?php

namespace itsmng\Database\Migrations;

use itsmng\Database\Runtime\DatabaseInterface;
use itsmng\Database\Schema\Dialect\DialectResolver;

class MigrationHistoryRepository
{
    public const TABLE = 'glpi_schema_migrations';
    public const BASELINE_MIGRATION = 'baseline';

    public function __construct(
        private readonly DatabaseInterface $database
    ) {
    }

    public function ensureTable(): void
    {
        if ($this->database->tableExists(self::TABLE, false)) {
            return;
        }

        $dialect = (new DialectResolver())->resolve($this->database);
        foreach ($dialect->createTableStatements($this->getTableDefinition()) as $statement) {
            $this->database->queryOrDie(
                $statement,
                'Create schema migration history table'
            );
        }
    }

    /**
     * @return array<string, array{migration: string, batch: int}>
     */
    public function applied(): array
    {
        $this->ensureTable();
        $table = $this->database->quoteName(self::TABLE);
        $version = $this->database->quoteName('version');
        $migration = $this->database->quoteName('migration');
        $batch = $this->database->quoteName('batch');

        $result = $this->database->queryOrDie(
            sprintf('SELECT %s, %s, %s FROM %s ORDER BY %s ASC', $version, $migration, $batch, $table, $version),
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
        $table = $this->database->quoteName(self::TABLE);
        $batch = $this->database->quoteName('batch');
        $result = $this->database->queryOrDie(
            sprintf('SELECT MAX(%s) AS %s FROM %s', $batch, $batch, $table),
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

    public function ensureBaseline(string $version, string $migration = self::BASELINE_MIGRATION, int $batch = 1): void
    {
        $applied = $this->applied();
        if (isset($applied[$version])) {
            return;
        }

        $this->record($version, $migration, $batch);
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
        $table = $this->database->quoteName(self::TABLE);
        $version = $this->database->quoteName('version');
        $migration = $this->database->quoteName('migration');
        $batch_column = $this->database->quoteName('batch');
        $result = $this->database->queryOrDie(
            sprintf('SELECT MAX(%s) AS %s FROM %s', $batch_column, $batch_column, $table),
            'Load latest schema migration batch'
        );
        $row = $this->database->fetchAssoc($result);
        $batch = (int) ($row['batch'] ?? 0);
        if ($batch === 0) {
            return [];
        }

        $result = $this->database->queryOrDie(
            sprintf(
                'SELECT %s, %s, %s FROM %s WHERE %s = %d ORDER BY %s DESC',
                $version,
                $migration,
                $batch_column,
                $table,
                $batch_column,
                $batch,
                $version
            ),
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

    public function isBaselineMigration(string $migration): bool
    {
        return $migration === self::BASELINE_MIGRATION;
    }

    private function getTableDefinition(): array
    {
        return [
            'name'    => self::TABLE,
            'columns' => [
                [
                    'name'          => 'id',
                    'type'          => 'int32',
                    'nullable'      => false,
                    'unsigned'      => true,
                    'autoIncrement' => true,
                ],
                [
                    'name'     => 'version',
                    'type'     => 'string',
                    'length'   => 32,
                    'nullable' => false,
                ],
                [
                    'name'     => 'migration',
                    'type'     => 'string',
                    'length'   => 255,
                    'nullable' => false,
                ],
                [
                    'name'     => 'applied_at',
                    'type'     => 'timestamp',
                    'nullable' => false,
                    'default'  => [
                        'kind'  => 'expression',
                        'value' => 'CURRENT_TIMESTAMP',
                    ],
                ],
                [
                    'name'     => 'batch',
                    'type'     => 'int32',
                    'nullable' => false,
                    'unsigned' => true,
                    'default'  => 0,
                ],
            ],
            'indexes' => [
                [
                    'name'    => 'PRIMARY',
                    'type'    => 'primary',
                    'columns' => [
                        ['name' => 'id'],
                    ],
                ],
                [
                    'name'    => 'glpi_schema_migrations_version',
                    'type'    => 'unique',
                    'columns' => [
                        ['name' => 'version'],
                    ],
                ],
                [
                    'name'    => 'glpi_schema_migrations_batch',
                    'type'    => 'index',
                    'columns' => [
                        ['name' => 'batch'],
                    ],
                ],
            ],
        ];
    }
}
