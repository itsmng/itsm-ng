<?php

namespace itsmng\Database\Schema\Dialect;

use InvalidArgumentException;

class PostgreSqlDialect extends AbstractDialect
{
    public function name(): string
    {
        return 'pgsql';
    }

    public function supportsTransactionalDdl(): bool
    {
        return true;
    }

    public function createTableStatements(array $table): array
    {
        $primary_key = $this->primaryKeyColumns($table);
        $parts = [];
        foreach ($table['columns'] as $column) {
            $parts[] = '  ' . $this->columnDefinition($column);
        }

        if ($primary_key !== []) {
            $columns = implode(', ', array_map(fn (array $column): string => $this->quoteIndexColumn($column), $primary_key));
            $parts[] = '  PRIMARY KEY (' . $columns . ')';
        }

        $statements = [
            sprintf(
                "CREATE TABLE %s (\n%s\n)",
                $this->quoteIdentifier($table['name']),
                implode(",\n", $parts)
            ),
        ];

        foreach ($table['columns'] as $column) {
            if (!empty($column['comment'])) {
                $statements[] = sprintf(
                    'COMMENT ON COLUMN %s.%s IS %s',
                    $this->quoteIdentifier($table['name']),
                    $this->quoteIdentifier($column['name']),
                    $this->quoteString($column['comment'])
                );
            }
        }

        return array_merge($statements, $this->createIndexStatements($table));
    }

    protected function alterColumnStatement(string $table, array $column): string
    {
        $column_name = $this->quoteIdentifier($column['name']);
        $table_name  = $this->quoteIdentifier($table);
        $type        = $this->columnType($column, false);
        $parts = [
            sprintf('ALTER TABLE %s ALTER COLUMN %s TYPE %s', $table_name, $column_name, $type),
            sprintf(
                'ALTER TABLE %s ALTER COLUMN %s %s NOT NULL',
                $table_name,
                $column_name,
                ($column['nullable'] ?? true) ? 'DROP' : 'SET'
            ),
        ];

        if (array_key_exists('default', $column)) {
            $parts[] = sprintf(
                'ALTER TABLE %s ALTER COLUMN %s SET DEFAULT %s',
                $table_name,
                $column_name,
                $this->defaultValue($column['default'])
            );
        } else {
            $parts[] = sprintf('ALTER TABLE %s ALTER COLUMN %s DROP DEFAULT', $table_name, $column_name);
        }

        return implode('; ', $parts);
    }

    protected function deleteIndexStatement(string $table, string $name): string
    {
        return 'DROP INDEX IF EXISTS ' . $this->quoteIdentifier($name);
    }

    protected function quoteIdentifier(string $identifier): string
    {
        if ($identifier === '*') {
            return $identifier;
        }

        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    protected function columnDefinition(array $column): string
    {
        $type = $this->columnType($column, true);
        $sql = $this->quoteIdentifier($column['name']) . ' ' . $type;
        $sql .= ($column['nullable'] ?? true) ? ' NULL' : ' NOT NULL';

        if (array_key_exists('default', $column)) {
            $sql .= ' DEFAULT ' . $this->defaultValue($column['default']);
        }

        return $sql;
    }

    private function columnType(array $column, bool $allow_identity): string
    {
        if ($allow_identity && !empty($column['autoIncrement'])) {
            return match ($column['type']) {
                'int16' => 'SMALLSERIAL',
                'int64' => 'BIGSERIAL',
                default => 'SERIAL',
            };
        }

        return match ($column['type']) {
            'boolean' => 'BOOLEAN',
            'char'    => 'CHAR(' . (int) ($column['length'] ?? 1) . ')',
            'string'  => empty($column['length']) ? 'TEXT' : 'VARCHAR(' . (int) $column['length'] . ')',
            'text', 'longtext' => 'TEXT',
            'int16'   => 'SMALLINT',
            'int32'   => 'INTEGER',
            'int64'   => 'BIGINT',
            'decimal' => sprintf('NUMERIC(%d,%d)', (int) $column['precision'], (int) $column['scale']),
            'float'   => 'DOUBLE PRECISION',
            'date'    => 'DATE',
            'time'    => 'TIME WITHOUT TIME ZONE',
            'timestamp' => 'TIMESTAMP WITHOUT TIME ZONE',
            'json'    => 'JSONB',
            'binary'  => 'BYTEA',
            'custom'  => (string) $column['custom'],
            default   => throw new InvalidArgumentException('Unsupported column type "' . $column['type'] . '" for PostgreSQL'),
        };
    }

    private function defaultValue(mixed $default): string
    {
        if (is_array($default) && ($default['kind'] ?? null) === 'expression') {
            return match ($default['value']) {
                'CURRENT_TIMESTAMP' => 'CURRENT_TIMESTAMP',
                default => $default['value'],
            };
        }

        if ($default === null) {
            return 'NULL';
        }

        if (is_bool($default)) {
            return $default ? 'TRUE' : 'FALSE';
        }

        if (is_int($default) || is_float($default)) {
            return (string) $default;
        }

        return $this->quoteString((string) $default);
    }

    private function quoteString(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }
}
