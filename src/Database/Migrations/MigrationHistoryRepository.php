<?php

namespace itsmng\Database\Migrations;

use itsmng\Database\Runtime\DatabaseInterface;
use itsmng\Database\Runtime\LegacySqlQuoter;
use itsmng\Database\Schema\Dialect\DialectResolver;

class MigrationHistoryRepository
{
    public const TABLE = 'glpi_schema_migrations';
    public const BASELINE_MIGRATION = 'baseline';

    private bool $tableEnsured = false;

    public function __construct(
        private readonly DatabaseInterface $database
    ) {
    }

    public function ensureTable(): void
    {
        if ($this->tableEnsured) {
            return;
        }

        if ($this->database->tableExists(self::TABLE, false)) {
            $this->ensureCompatibleSchema();
        } else {
            $dialect = (new DialectResolver())->resolve($this->database);
            foreach ($dialect->createTableStatements($this->getTableDefinition()) as $statement) {
                $this->database->queryOrDie(
                    $statement,
                    'Create schema migration history table'
                );
            }
        }

        $this->tableEnsured = true;
    }

    private function ensureCompatibleSchema(): void
    {
        $this->ensureColumnLength('version', 191);
        $this->ensureColumnLength('migration', 255);
    }

    private function ensureColumnLength(string $column, int $minimum_length): void
    {
        if (!method_exists($this->database, 'getField')) {
            return;
        }

        $definition = $this->database->getField(self::TABLE, $column, false);
        $current_length = $this->extractVarcharLength($definition['Type'] ?? null);
        if ($current_length === null || $current_length >= $minimum_length) {
            return;
        }

        foreach ($this->alterColumnLengthStatements($column, $minimum_length) as $statement) {
            $this->database->queryOrDie($statement, sprintf('Alter schema migration history column %s', $column));
        }
    }

    private function extractVarcharLength(mixed $type): ?int
    {
        if (!is_string($type) || preg_match('/^varchar\((\d+)\)$/i', $type, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * @return string[]
     */
    private function alterColumnLengthStatements(string $column, int $length): array
    {
        $db_type = $this->database->getDbType();
        $table = LegacySqlQuoter::quoteName(self::TABLE, $db_type);
        $quoted_column = LegacySqlQuoter::quoteName($column, $db_type);

        return match ($db_type) {
            'pgsql' => [
                sprintf(
                    'ALTER TABLE %s ALTER COLUMN %s TYPE VARCHAR(%d)',
                    $table,
                    $quoted_column,
                    $length
                ),
            ],
            default => [
                sprintf(
                    'ALTER TABLE %s MODIFY COLUMN %s VARCHAR(%d) NOT NULL',
                    $table,
                    $quoted_column,
                    $length
                ),
            ],
        };
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
        return $this->maxBatch() + 1;
    }

    private function maxBatch(): int
    {
        $this->ensureTable();
        $table = $this->database->quoteName(self::TABLE);
        $batch = $this->database->quoteName('batch');
        $result = $this->database->queryOrDie(
            sprintf('SELECT MAX(%s) AS %s FROM %s', $batch, $batch, $table),
            'Compute max schema migration batch'
        );
        $row = $this->database->fetchAssoc($result);

        return (int) ($row['batch'] ?? 0);
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

    public function isBaselineMigration(array|string $migration): bool
    {
        if (is_array($migration)) {
            $migration = $migration['migration'] ?? '';
        }

        return $migration === self::BASELINE_MIGRATION;
    }

    /**
     * @return array<int, array{version: string, migration: string, batch: int}>
     */
    public function latestBatchMigrations(): array
    {
        $batch = $this->maxBatch();
        if ($batch === 0) {
            return [];
        }

        $table = $this->database->quoteName(self::TABLE);
        $version = $this->database->quoteName('version');
        $migration = $this->database->quoteName('migration');
        $batch_column = $this->database->quoteName('batch');

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
        while ($row = $this->database->fetchAssoc($result)) {
            $migrations[] = [
                'version'   => $row['version'],
                'migration' => $row['migration'],
                'batch'     => (int) $row['batch'],
            ];
        }

        return $migrations;
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
                    'length'   => 191,
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
