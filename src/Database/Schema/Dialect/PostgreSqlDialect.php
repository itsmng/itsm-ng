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
                $this->defaultValue($column['default'], $column['type'])
            );
        } else {
            $parts[] = sprintf('ALTER TABLE %s ALTER COLUMN %s DROP DEFAULT', $table_name, $column_name);
        }

        return implode('; ', $parts);
    }

    protected function createIndexStatement(string $table, array $index): string
    {
        $index['name'] = $this->normalizeIndexName($table, $index['name']);

        return parent::createIndexStatement($table, $index);
    }

    protected function deleteIndexStatement(string $table, string $name): string
    {
        return 'DROP INDEX IF EXISTS ' . $this->quoteIdentifier($this->normalizeIndexName($table, $name));
    }

    protected function quoteIdentifier(string $identifier): string
    {
        if ($identifier === '*') {
            return $identifier;
        }

        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    protected function quoteIndexColumn(array $column): string
    {
        return $this->quoteIdentifier($column['name']);
    }

    protected function createFulltextIndexStatement(string $table_name, string $index_name, string $columns): string
    {
        return sprintf('CREATE INDEX %s ON %s (%s)', $index_name, $table_name, $columns);
    }

    protected function columnDefinition(array $column): string
    {
        $type = $this->columnType($column, true);
        $sql = $this->quoteIdentifier($column['name']) . ' ' . $type;
        $sql .= ($column['nullable'] ?? true) ? ' NULL' : ' NOT NULL';

        if (array_key_exists('default', $column)) {
            $sql .= ' DEFAULT ' . $this->defaultValue($column['default'], $column['type']);
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

        $is_unsigned = !empty($column['unsigned']);

        return match ($column['type']) {
            'boolean' => 'BOOLEAN',
            'char'    => 'CHAR(' . (int) ($column['length'] ?? 1) . ')',
            'string'  => empty($column['length']) ? 'TEXT' : 'VARCHAR(' . (int) $column['length'] . ')',
            'text', 'longtext' => 'TEXT',
            'int16'   => $is_unsigned ? 'INTEGER' : 'SMALLINT',
            'int32'   => $is_unsigned ? 'BIGINT' : 'INTEGER',
            'int64'   => $is_unsigned ? 'NUMERIC(20,0)' : 'BIGINT',
            'decimal' => sprintf('NUMERIC(%d,%d)', (int) $column['precision'], (int) $column['scale']),
            'float'   => 'DOUBLE PRECISION',
            'date'    => 'DATE',
            'time'    => 'TIME WITHOUT TIME ZONE',
            'timestamp' => 'TIMESTAMP WITH TIME ZONE',
            'json'    => 'JSONB',
            'binary'  => 'BYTEA',
            'custom'  => $this->sanitizeCustomType((string) $column['custom']),
            default   => throw new InvalidArgumentException('Unsupported column type "' . $column['type'] . '" for PostgreSQL'),
        };
    }

    private function defaultValue(mixed $default, string $column_type): string
    {
        if (is_array($default) && ($default['kind'] ?? null) === 'expression') {
            $value = preg_replace('/\s+ON UPDATE CURRENT_TIMESTAMP$/i', '', (string) $default['value']) ?? (string) $default['value'];

            return match ($default['value']) {
                'CURRENT_TIMESTAMP' => 'CURRENT_TIMESTAMP',
                default => $value,
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

        $default = $this->sanitizeStringDefault((string) $default);
        if (strtoupper($default) === 'NULL') {
            return 'NULL';
        }

        if ($column_type === 'boolean') {
            return match ($default) {
                '0', 'false', 'FALSE' => 'FALSE',
                '1', 'true', 'TRUE'   => 'TRUE',
                default               => throw new InvalidArgumentException(
                    'Unsupported boolean default "' . $default . '" for PostgreSQL'
                ),
            };
        }

        return $this->quoteString($default);
    }

    private function quoteString(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }

    private function sanitizeCustomType(string $type): string
    {
        $type = preg_replace("/\\s+COLLATE\\s+'?utf8_unicode_ci'?/i", '', $type) ?? $type;

        return match (strtoupper($type)) {
            'LONGTEXT' => 'TEXT',
            default    => $type,
        };
    }

    private function sanitizeStringDefault(string $default): string
    {
        $default = preg_replace("/\\s+COLLATE\\s+'?utf8_unicode_ci'?/i", '', $default) ?? $default;

        return $default === "'" ? '' : $default;
    }

    private function normalizeIndexName(string $table, string $name): string
    {
        if ($name === 'PRIMARY') {
            return $name;
        }

        $prefixed_name = str_starts_with($name, $table . '_') ? $name : $table . '_' . $name;
        if (strlen($prefixed_name) <= 63) {
            return $prefixed_name;
        }

        return substr($table, 0, 38) . '_' . substr(sha1($table . ':' . $name), 0, 24);
    }
}
