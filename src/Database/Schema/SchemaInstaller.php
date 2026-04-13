<?php

namespace itsmng\Database\Schema;

use RuntimeException;
use itsmng\Database\Migrations\MigrationHistoryRepository;
use itsmng\Database\Runtime\DatabaseInterface;
use itsmng\Database\Schema\Dialect\DialectInterface;
use itsmng\Database\Schema\Dialect\DialectResolver;

class SchemaInstaller
{
    public function __construct(
        private readonly ?DialectResolver $dialect_resolver = null
    ) {
    }

    public function install(array $schema, ?DatabaseInterface $database = null): void
    {
        $database ??= $GLOBALS['DB'] ?? null;
        if (!$database instanceof DatabaseInterface) {
            throw new RuntimeException('Schema installation requires an active database connection.');
        }

        $dialect = ($this->dialect_resolver ?? new DialectResolver())->resolve($database);
        $existing_tables = $database->listTables();
        while ($table = $existing_tables->next()) {
            $table_name = $table['TABLE_NAME'];
            if ($table_name === MigrationHistoryRepository::TABLE || str_starts_with($table_name, 'glpi_')) {
                $statement = 'DROP TABLE IF EXISTS ' . $database->quoteName($table_name);
                if ($database->getDbType() === 'pgsql') {
                    $statement .= ' CASCADE';
                }

                $database->queryOrDie($statement, 'Drop existing schema table');
            }
        }

        foreach ($schema['tables'] ?? [] as $table) {
            foreach ($dialect->createTableStatements($table) as $statement) {
                $database->queryOrDie($statement, 'Schema installation');
            }
        }
    }

    public function executeOperations(array $operations, ?DatabaseInterface $database = null): void
    {
        $database ??= $GLOBALS['DB'] ?? null;
        if (!$database instanceof DatabaseInterface) {
            throw new RuntimeException('Schema operation execution requires an active database connection.');
        }

        $dialect = ($this->dialect_resolver ?? new DialectResolver())->resolve($database);
        $prepared_mysql_tables = [];
        foreach ($operations as $operation) {
            $this->prepareMySqlTablesForForeignKeys($operation, $database, $prepared_mysql_tables);
            foreach ($dialect->renderOperation($operation) as $statement) {
                $database->queryOrDie($statement, 'Schema migration');
            }
        }
    }

    public function statementsForSchema(array $schema, DialectInterface $dialect): array
    {
        $statements = [];
        foreach ($schema['tables'] ?? [] as $table) {
            foreach ($dialect->createTableStatements($table) as $statement) {
                $statements[] = $statement;
            }
        }

        return $statements;
    }

    /**
     * MySQL only supports foreign keys on InnoDB tables.
     * Legacy update databases may still contain MyISAM tables, so convert the
     * target and referenced tables before executing FK DDL.
     *
     * @param array<string, mixed> $operation
     * @param array<string, bool> $prepared_tables
     */
    private function prepareMySqlTablesForForeignKeys(array $operation, DatabaseInterface $database, array &$prepared_tables): void
    {
        if ($database->getDbType() !== 'mysql' || ($operation['kind'] ?? null) !== 'alter_table') {
            return;
        }

        $tables_to_prepare = [];
        foreach ($operation['foreign_keys'] ?? [] as $foreign_key) {
            if (($foreign_key['action'] ?? 'add') !== 'add') {
                continue;
            }

            $tables_to_prepare[$operation['table']] = true;
            if (!empty($foreign_key['referenced_table'])) {
                $tables_to_prepare[$foreign_key['referenced_table']] = true;
            }
        }

        foreach (array_keys($tables_to_prepare) as $table) {
            if (isset($prepared_tables[$table])) {
                continue;
            }

            if ($database->listTables($table, ['engine' => 'MyIsam'])->count() === 0) {
                $prepared_tables[$table] = true;
                continue;
            }

            $database->queryOrDie(
                sprintf('ALTER TABLE %s ENGINE = InnoDB', $database::quoteName($table)),
                'Prepare table engine for foreign key migration'
            );
            $prepared_tables[$table] = true;
        }
    }
}
