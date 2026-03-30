<?php

namespace itsmng\Database\Schema\Dialect;

use InvalidArgumentException;

abstract class AbstractDialect implements DialectInterface
{
    public function renderOperation(array $operation): array
    {
        return match ($operation['kind'] ?? null) {
            'create_table'  => $this->createTableStatements($operation['table']),
            'delete_table'  => [$this->deleteTableStatement($operation['table'])],
            'rename_table'  => [$this->renameTableStatement($operation['from'], $operation['to'])],
            'rename_column' => [$this->renameColumnStatement($operation['table'], $operation['from'], $operation['to'])],
            'delete_column' => [$this->deleteColumnStatement($operation['table'], $operation['columns'])],
            'delete_index'  => [$this->deleteIndexStatement($operation['table'], $operation['name'])],
            'alter_table'   => $this->alterTableStatements($operation),
            default         => throw new InvalidArgumentException('Unsupported schema operation: ' . ($operation['kind'] ?? 'unknown')),
        };
    }

    protected function alterTableStatements(array $operation): array
    {
        $table = $operation['table'];
        $statements = [];

        foreach ($operation['add_columns'] ?? [] as $column) {
            $statements[] = sprintf(
                'ALTER TABLE %s ADD %s',
                $this->quoteIdentifier($table),
                $this->columnDefinition($column)
            );
        }

        foreach ($operation['alter_columns'] ?? [] as $column) {
            $statements[] = $this->alterColumnStatement($table, $column);
        }

        foreach ($operation['drop_columns'] ?? [] as $column) {
            $statements[] = $this->deleteColumnStatement($table, [$column]);
        }

        foreach ($operation['indexes'] ?? [] as $index) {
            $statements[] = $this->createIndexStatement($table, $index);
        }

        return $statements;
    }

    protected function createIndexStatements(array $table): array
    {
        $statements = [];
        foreach ($table['indexes'] ?? [] as $index) {
            if (($index['type'] ?? 'index') === 'primary') {
                continue;
            }

            $statements[] = $this->createIndexStatement($table['name'], $index);
        }

        return $statements;
    }

    protected function createIndexStatement(string $table, array $index): string
    {
        $index_name = $this->quoteIdentifier($index['name']);
        $table_name = $this->quoteIdentifier($table);
        $columns = implode(', ', array_map(fn (array $column): string => $this->quoteIndexColumn($column), $index['columns']));

        return match ($index['type'] ?? 'index') {
            'unique'   => sprintf('CREATE UNIQUE INDEX %s ON %s (%s)', $index_name, $table_name, $columns),
            'fulltext' => $this->createFulltextIndexStatement($table_name, $index_name, $columns),
            default    => sprintf('CREATE INDEX %s ON %s (%s)', $index_name, $table_name, $columns),
        };
    }

    protected function createFulltextIndexStatement(string $table_name, string $index_name, string $columns): string
    {
        throw new InvalidArgumentException(sprintf('Dialect "%s" does not support FULLTEXT indexes.', $this->name()));
    }

    protected function primaryKeyColumns(array $table): array
    {
        foreach ($table['indexes'] ?? [] as $index) {
            if (($index['type'] ?? null) === 'primary') {
                return $index['columns'];
            }
        }

        return [];
    }

    protected function quoteIndexColumn(array $column): string
    {
        $sql = $this->quoteIdentifier($column['name']);

        if (!empty($column['length'])) {
            $sql .= '(' . (int) $column['length'] . ')';
        }

        return $sql;
    }

    protected function deleteColumnStatement(string $table, array $columns): string
    {
        $parts = array_map(
            fn (string $column): string => 'DROP COLUMN ' . $this->quoteIdentifier($column),
            $columns
        );

        return sprintf('ALTER TABLE %s %s', $this->quoteIdentifier($table), implode(', ', $parts));
    }

    protected function renameTableStatement(string $from, string $to): string
    {
        return sprintf('ALTER TABLE %s RENAME TO %s', $this->quoteIdentifier($from), $this->quoteIdentifier($to));
    }

    protected function renameColumnStatement(string $table, string $from, string $to): string
    {
        return sprintf(
            'ALTER TABLE %s RENAME COLUMN %s TO %s',
            $this->quoteIdentifier($table),
            $this->quoteIdentifier($from),
            $this->quoteIdentifier($to)
        );
    }

    protected function deleteTableStatement(string $table): string
    {
        return 'DROP TABLE IF EXISTS ' . $this->quoteIdentifier($table);
    }

    abstract protected function deleteIndexStatement(string $table, string $name): string;

    abstract protected function alterColumnStatement(string $table, array $column): string;

    abstract protected function columnDefinition(array $column): string;

    abstract protected function quoteIdentifier(string $identifier): string;
}
