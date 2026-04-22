<?php

namespace itsmng\Database\Migrations\Core;

use itsmng\Database\Migrations\Attribute\SchemaMigration;
use itsmng\Database\Migrations\ColumnBuilder;
use itsmng\Database\Migrations\Migration;
use itsmng\Database\Schema\CoreSchema;

#[SchemaMigration(
    version: '202603271200_AddForeignKeys',
    description: 'Add portable foreign keys for core join relationships.'
)]
final class Migration202603271200_AddForeignKeys extends Migration
{
    private const FK_POLICY_DEFAULT = 'default';
    private const FK_POLICY_NULLABLE_GLOBAL_DEFAULT = 'nullable_global_default';
    private const FK_POLICY_SKIP_ZERO_SENTINEL = 'skip_zero_sentinel';

    /**
     * Non-standard foreign key field names that cannot be inferred from DbUtils.
     *
     * @var array<string, string>
     */
    private const SPECIAL_FOREIGN_KEYS = [
        'glpi_dashboards.profileId' => 'glpi_profiles',
        'glpi_dashboards.userId'    => 'glpi_users',
    ];

    /**
     * Columns that still rely on sentinel semantics and must not receive a strict FK yet.
     *
     * @var string[]
     */
    private const EXCLUDED_COLUMNS = [
        'glpi_entities.entities_id',
        'glpi_crontasklogs.crontasklogs_id',
    ];

    /**
     * These references legitimately use entity id 0 and should stay non-nullable.
     *
     * @var string[]
     */
    private const NON_NULLABLE_REFERENCED_TABLES = [
        'glpi_entities',
    ];

    /**
     * Explicit policies for columns whose foreign-key semantics are not covered by generic heuristics.
     *
     * @var array<string, string>
     */
    private const COLUMN_POLICIES = [
        'glpi_displaypreferences.users_id' => self::FK_POLICY_NULLABLE_GLOBAL_DEFAULT,
        'glpi_dashboards.userId'           => self::FK_POLICY_SKIP_ZERO_SENTINEL,
    ];

    public function up(): void
    {
        foreach ($this->foreignKeys() as $foreign_key) {
            if ($foreign_key['nullable']) {
                $this->configureColumn(
                    $this->alter()->table($foreign_key['table'])->alterColumn($foreign_key['column']),
                    $foreign_key['column_schema'],
                    true
                )->end();
            }

            if ($foreign_key['cleanup_up'] === 'nullify_non_positive') {
                $this->nullifyNonPositive($foreign_key['table'], $foreign_key['column']);
            }

            if ($foreign_key['nullable']) {
                $this->nullifyMissingReferences(
                    $foreign_key['table'],
                    $foreign_key['column'],
                    $foreign_key['referenced_table']
                );
            } else {
                $this->configureColumn(
                    $this->alter()->table($foreign_key['table'])->alterColumn($foreign_key['column']),
                    $foreign_key['column_schema'],
                    false
                )->end();
            }

            if ($foreign_key['index_name'] !== null) {
                $this->alter()->table($foreign_key['table'])->addIndex([
                    'name'    => $foreign_key['index_name'],
                    'type'    => 'index',
                    'columns' => [
                        ['name' => $foreign_key['column']],
                    ],
                ]);
            }

            $this->alter()->table($foreign_key['table'])->addForeignKey(
                $foreign_key['constraint_name'],
                $foreign_key['column'],
                $foreign_key['referenced_table']
            );
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->foreignKeys()) as $foreign_key) {
            $this->alter()->table($foreign_key['table'])->dropForeignKey($foreign_key['constraint_name']);

            if ($foreign_key['index_name'] !== null) {
                $this->delete()->index($foreign_key['index_name'])->onTable($foreign_key['table']);
            }

            $column_schema = $foreign_key['column_schema'];
            if ($foreign_key['cleanup_down'] === 'replace_null_with_zero') {
                $this->replaceNullWithZero($foreign_key['table'], $foreign_key['column']);
                $column_schema['default'] = $column_schema['default'] ?? '0';
                $column_schema['nullable'] = false;
            }

            $this->configureColumn(
                $this->alter()->table($foreign_key['table'])->alterColumn($foreign_key['column']),
                $column_schema,
                false
            )->end();
        }
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     referenced_table: string,
     *     nullable: bool,
     *     policy: string,
     *     cleanup_up: ?string,
     *     cleanup_down: ?string,
     *     constraint_name: string,
     *     index_name: ?string,
     *     column_schema: array<string, mixed>
     * }>
     */
    private function foreignKeys(): array
    {
        static $foreign_keys = null;
        if (is_array($foreign_keys)) {
            return $foreign_keys;
        }

        $schema = CoreSchema::definition();
        $tables = [];
        foreach ($schema['tables'] ?? [] as $table) {
            $tables[$table['name']] = $table;
        }

        $foreign_keys = [];

        foreach ($tables as $table_name => $table) {
            $column_names = array_map(
                static fn (array $column): string => $column['name'],
                $table['columns'] ?? []
            );
            $primary_key_columns = $this->primaryKeyColumns($table);
            $index_names = [];
            foreach ($table['indexes'] ?? [] as $index) {
                $index_names[$index['name']] = true;
            }

            foreach ($table['columns'] ?? [] as $column) {
                $column_name = $column['name'];
                $qualified_name = $table_name . '.' . $column_name;

                if (
                    in_array($qualified_name, self::EXCLUDED_COLUMNS, true)
                    || !in_array($column['type'] ?? null, ['int16', 'int32', 'int64'], true)
                    || ($column_name === 'items_id' && in_array('itemtype', $column_names, true))
                    || (is_numeric($column['default'] ?? null) && (int) $column['default'] < 0)
                ) {
                    continue;
                }

                $referenced_table = self::SPECIAL_FOREIGN_KEYS[$qualified_name] ?? null;
                if ($referenced_table === null) {
                    if (!$this->isForeignKeyField($column_name)) {
                        continue;
                    }

                    $referenced_table = $this->getTableNameForForeignKeyField($column_name);
                }

                if (!isset($tables[$referenced_table])) {
                    continue;
                }

                $constraint_name = $this->normalizedName('fk_' . $table_name . '_' . $column_name);
                $index_name = null;
                if (!$this->hasLeadingColumnIndex($table, $column_name)) {
                    $candidate_index_name = isset($index_names[$column_name]) ? 'idx_' . $column_name : $column_name;
                    $index_name = $this->nextAvailableName($candidate_index_name, $index_names);
                    $index_names[$index_name] = true;
                }

                $policy = $this->resolvePolicy($qualified_name, $column, $referenced_table, $primary_key_columns, $column_name);
                if ($policy === null) {
                    continue;
                }

                $foreign_keys[] = [
                    'table'            => $table_name,
                    'column'           => $column_name,
                    'referenced_table' => $referenced_table,
                    'nullable'         => $policy['nullable'],
                    'policy'           => $policy['name'],
                    'cleanup_up'       => $policy['cleanup_up'],
                    'cleanup_down'     => $policy['cleanup_down'],
                    'constraint_name'  => $constraint_name,
                    'index_name'       => $index_name,
                    'column_schema'    => $column,
                ];
            }
        }

        usort(
            $foreign_keys,
            static fn (array $left, array $right): int => [$left['table'], $left['column']] <=> [$right['table'], $right['column']]
        );

        return $foreign_keys;
    }

    private function isForeignKeyField(string $field): bool
    {
        return preg_match('/._id$/', $field) === 1 || preg_match('/._id_/', $field) === 1;
    }

    private function getTableNameForForeignKeyField(string $foreign_key_name): string
    {
        if (!$this->isForeignKeyField($foreign_key_name)) {
            return '';
        }

        if (str_starts_with($foreign_key_name, '_')) {
            $foreign_key_name = substr($foreign_key_name, 1);
        }

        return 'glpi_' . (string) preg_replace('/_id.*/', '', $foreign_key_name);
    }

    /**
     * @param array<string, mixed> $column
     */
    private function configureColumn(ColumnBuilder $builder, array $column, bool $nullable): ColumnBuilder
    {
        $builder = match ($column['type']) {
            'int16' => $builder->asInt16((bool) ($column['unsigned'] ?? false)),
            'int64' => $builder->asInt64((bool) ($column['unsigned'] ?? false)),
            default => $builder->asInt32((bool) ($column['unsigned'] ?? false)),
        };

        if ($nullable) {
            $builder->nullable()->withDefaultValue(null);
        } else {
            if (($column['nullable'] ?? true) === false) {
                $builder->notNullable();
            } else {
                $builder->nullable();
            }
            if (array_key_exists('default', $column)) {
                $builder->withDefaultValue($column['default']);
            }
        }

        if (!empty($column['comment'])) {
            $builder->comment((string) $column['comment']);
        }

        return $builder;
    }

    private function nullifyMissingReferences(string $table, string $column, string $referenced_table): void
    {
        $this->execute(sprintf(
            'UPDATE %1$s SET %2$s = NULL WHERE %2$s IS NOT NULL AND %2$s > 0 AND NOT EXISTS (SELECT 1 FROM %3$s WHERE %3$s.id = %1$s.%2$s)',
            $table,
            $column,
            $referenced_table
        ));
    }

    /**
     * @param string[] $primary_key_columns
     * @param array<string, mixed> $column
     * @return array{name: string, nullable: bool, cleanup_up: ?string, cleanup_down: ?string}|null
     */
    private function resolvePolicy(
        string $qualified_name,
        array $column,
        string $referenced_table,
        array $primary_key_columns,
        string $column_name
    ): ?array {
        $is_strict_reference = in_array($referenced_table, self::NON_NULLABLE_REFERENCED_TABLES, true)
            || in_array($column_name, $primary_key_columns, true);
        $is_nullable_column = ($column['nullable'] ?? true) === true;
        $uses_zero_sentinel = (string) ($column['default'] ?? '') === '0';

        return match (self::COLUMN_POLICIES[$qualified_name] ?? self::FK_POLICY_DEFAULT) {
            self::FK_POLICY_SKIP_ZERO_SENTINEL => null,
            self::FK_POLICY_NULLABLE_GLOBAL_DEFAULT => [
                'name'         => self::FK_POLICY_NULLABLE_GLOBAL_DEFAULT,
                'nullable'     => true,
                'cleanup_up'   => 'nullify_non_positive',
                'cleanup_down' => 'replace_null_with_zero',
            ],
            self::FK_POLICY_DEFAULT => $is_nullable_column ? [
                'name'         => self::FK_POLICY_DEFAULT,
                'nullable'     => true,
                'cleanup_up'   => 'nullify_non_positive',
                'cleanup_down' => 'replace_null_with_zero',
            ] : ($is_strict_reference ? [
                'name'         => self::FK_POLICY_DEFAULT,
                'nullable'     => false,
                'cleanup_up'   => null,
                'cleanup_down' => null,
            ] : ($uses_zero_sentinel ? null : [
                'name'         => self::FK_POLICY_DEFAULT,
                'nullable'     => false,
                'cleanup_up'   => null,
                'cleanup_down' => null,
            ])),
        };
    }

    /**
     * @param array<string, mixed> $table
     */
    private function hasLeadingColumnIndex(array $table, string $column_name): bool
    {
        foreach ($table['indexes'] ?? [] as $index) {
            $first_column = $index['columns'][0]['name'] ?? null;
            if ($first_column === $column_name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $table
     * @return string[]
     */
    private function primaryKeyColumns(array $table): array
    {
        foreach ($table['indexes'] ?? [] as $index) {
            if (($index['type'] ?? null) !== 'primary') {
                continue;
            }

            return array_map(
                static fn (array $column): string => $column['name'],
                $index['columns'] ?? []
            );
        }

        return [];
    }

    private function normalizedName(string $name): string
    {
        if (strlen($name) <= 63) {
            return $name;
        }

        return substr($name, 0, 38) . '_' . substr(sha1($name), 0, 24);
    }

    /**
     * @param array<string, bool> $used_names
     */
    private function nextAvailableName(string $base_name, array $used_names): string
    {
        $name = $this->normalizedName($base_name);
        if (!isset($used_names[$name])) {
            return $name;
        }

        $suffix = 1;
        do {
            $name = $this->normalizedName($base_name . '_' . $suffix);
            $suffix++;
        } while (isset($used_names[$name]));

        return $name;
    }
}
