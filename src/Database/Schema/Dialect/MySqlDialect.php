<?php

namespace itsmng\Database\Schema\Dialect;

use InvalidArgumentException;

class MySqlDialect extends AbstractDialect
{
    public function name(): string
    {
        return 'mysql';
    }

    public function supportsTransactionalDdl(): bool
    {
        return false;
    }

    public function createTableStatements(array $table): array
    {
        $table = $this->ensureAutoIncrementColumnsAreKeyed($table);
        $parts = [];
        foreach ($table['columns'] as $column) {
            $parts[] = '  ' . $this->columnDefinition($column);
        }

        foreach ($table['indexes'] ?? [] as $index) {
            $parts[] = '  ' . $this->inlineIndexDefinition($index);
        }

        $table_sql = sprintf(
            "CREATE TABLE %s (\n%s\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
            $this->quoteIdentifier($table['name']),
            implode(",\n", $parts)
        );

        return [$table_sql];
    }

    private function ensureAutoIncrementColumnsAreKeyed(array $table): array
    {
        $keyed_columns = [];
        foreach ($table['indexes'] ?? [] as $index) {
            foreach ($index['columns'] ?? [] as $column) {
                $keyed_columns[$column['name']] = true;
            }
        }

        foreach ($table['columns'] as $column) {
            if (!empty($column['autoIncrement']) && !isset($keyed_columns[$column['name']])) {
                $table['indexes'][] = [
                    'name'    => $column['name'],
                    'type'    => 'unique',
                    'columns' => [
                        ['name' => $column['name']],
                    ],
                ];
            }
        }

        return $table;
    }

    protected function alterColumnStatement(string $table, array $column): string
    {
        return sprintf(
            'ALTER TABLE %s MODIFY %s',
            $this->quoteIdentifier($table),
            $this->columnDefinition($column)
        );
    }

    protected function renameTableStatement(string $from, string $to): string
    {
        return sprintf('RENAME TABLE %s TO %s', $this->quoteIdentifier($from), $this->quoteIdentifier($to));
    }

    protected function createFulltextIndexStatement(string $table_name, string $index_name, string $columns): string
    {
        return sprintf('CREATE FULLTEXT INDEX %s ON %s (%s)', $index_name, $table_name, $columns);
    }

    protected function deleteIndexStatement(string $table, string $name): string
    {
        return sprintf(
            'ALTER TABLE %s DROP INDEX %s',
            $this->quoteIdentifier($table),
            $this->quoteIdentifier($name)
        );
    }

    protected function quoteIdentifier(string $identifier): string
    {
        if ($identifier === '*') {
            return $identifier;
        }

        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    protected function inlineIndexDefinition(array $index): string
    {
        $columns = implode(', ', array_map(fn (array $column): string => $this->quoteIndexColumn($column), $index['columns']));

        return match ($index['type'] ?? 'index') {
            'primary'  => sprintf('PRIMARY KEY (%s)', $columns),
            'unique'   => sprintf('UNIQUE KEY %s (%s)', $this->quoteIdentifier($index['name']), $columns),
            'fulltext' => sprintf('FULLTEXT KEY %s (%s)', $this->quoteIdentifier($index['name']), $columns),
            default    => sprintf('KEY %s (%s)', $this->quoteIdentifier($index['name']), $columns),
        };
    }

    protected function columnDefinition(array $column): string
    {
        $sql = $this->quoteIdentifier($column['name']) . ' ' . $this->columnType($column);
        $sql .= ($column['nullable'] ?? true) ? ' NULL' : ' NOT NULL';

        if (array_key_exists('default', $column)) {
            $sql .= ' DEFAULT ' . $this->defaultValue($column['default']);
        }

        if (!empty($column['autoIncrement'])) {
            $sql .= ' AUTO_INCREMENT';
        }

        if (!empty($column['comment'])) {
            $sql .= ' COMMENT ' . $this->quoteString($column['comment']);
        }

        return $sql;
    }

    private function columnType(array $column): string
    {
        return match ($column['type']) {
            'boolean'   => 'BOOLEAN',
            'char'      => 'CHAR(' . (int) ($column['length'] ?? 1) . ')',
            'string'    => 'VARCHAR(' . (int) ($column['length'] ?? 255) . ') COLLATE utf8_unicode_ci',
            'text'      => 'TEXT COLLATE utf8_unicode_ci',
            'longtext'  => 'LONGTEXT COLLATE utf8_unicode_ci',
            'int16'     => 'SMALLINT' . $this->unsignedSuffix($column),
            'int32'     => 'INT(11)' . $this->unsignedSuffix($column),
            'int64'     => 'BIGINT' . $this->unsignedSuffix($column),
            'decimal'   => sprintf('DECIMAL(%d,%d)', (int) $column['precision'], (int) $column['scale']),
            'float'     => 'FLOAT',
            'date'      => 'DATE',
            'time'      => 'TIME',
            'timestamp' => 'TIMESTAMP',
            'json'      => 'JSON',
            'binary'    => empty($column['length']) ? 'BLOB' : 'VARBINARY(' . (int) $column['length'] . ')',
            'custom'    => (string) $column['custom'],
            default     => throw new InvalidArgumentException('Unsupported column type "' . $column['type'] . '" for MySQL'),
        };
    }

    private function unsignedSuffix(array $column): string
    {
        return !empty($column['unsigned']) ? ' UNSIGNED' : '';
    }

    private function defaultValue(mixed $default): string
    {
        if (is_array($default) && ($default['kind'] ?? null) === 'expression') {
            return $default['value'];
        }

        if ($default === null) {
            return 'NULL';
        }

        if (is_bool($default)) {
            return $default ? 'TRUE' : 'FALSE';
        }

        if (is_int($default) || is_float($default)) {
            return "'" . (string) $default . "'";
        }

        return $this->quoteString((string) $default);
    }

    private function quoteString(string $value): string
    {
        return "'" . str_replace("'", "\\'", $value) . "'";
    }
}
