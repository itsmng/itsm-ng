<?php

namespace itsmng\Database\Schema;

use DBmysql;
use RuntimeException;
use itsmng\Database\Migrations\MigrationHistoryRepository;
use itsmng\Database\Schema\Dialect\DialectInterface;
use itsmng\Database\Schema\Dialect\DialectResolver;

class SchemaInstaller
{
    public function __construct(
        private readonly ?DialectResolver $dialect_resolver = null
    ) {
    }

    public function install(array $schema, ?DBmysql $database = null): void
    {
        $database ??= $GLOBALS['DB'] ?? null;
        if (!$database instanceof DBmysql) {
            throw new RuntimeException('Schema installation requires an active database connection.');
        }

        $dialect = ($this->dialect_resolver ?? new DialectResolver())->resolve($database);
        $existing_tables = $database->listTables();
        while ($table = $existing_tables->next()) {
            $table_name = $table['TABLE_NAME'];
            if ($table_name === MigrationHistoryRepository::TABLE || str_starts_with($table_name, 'glpi_')) {
                $database->queryOrDie('DROP TABLE IF EXISTS ' . $database->quoteName($table_name), 'Drop existing schema table');
            }
        }

        foreach ($schema['tables'] ?? [] as $table) {
            foreach ($dialect->createTableStatements($table) as $statement) {
                $database->queryOrDie($statement, 'Schema installation');
            }
        }
    }

    public function executeOperations(array $operations, ?DBmysql $database = null): void
    {
        $database ??= $GLOBALS['DB'] ?? null;
        if (!$database instanceof DBmysql) {
            throw new RuntimeException('Schema operation execution requires an active database connection.');
        }

        $dialect = ($this->dialect_resolver ?? new DialectResolver())->resolve($database);
        foreach ($operations as $operation) {
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
}
