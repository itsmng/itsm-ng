<?php

namespace tests\units\itsmng\Database\Migrations;

class Migration202603271200_AddForeignKeys extends \GLPITestCase
{
    public function getTestedClassName()
    {
        return \itsmng\Database\Migrations\Core\Migration202603271200_AddForeignKeys::class;
    }

    public function testDisplayPreferencesUsersIdUsesNullableGlobalDefaultPolicy()
    {
        $migration = new \itsmng\Database\Migrations\Core\Migration202603271200_AddForeignKeys();

        $up_operations = $migration->buildOperations('up');
        $down_operations = $migration->buildOperations('down');

        $alter_index = $this->findOperationIndex($up_operations, static function (array $operation): bool {
            return ($operation['kind'] ?? null) === 'alter_table'
                && ($operation['table'] ?? null) === 'glpi_displaypreferences'
                && count($operation['alter_columns'] ?? []) > 0
                && (($operation['alter_columns'][0]['name'] ?? null) === 'users_id');
        });
        $cleanup_index = $this->findOperationIndex($up_operations, static function (array $operation): bool {
            return ($operation['kind'] ?? null) === 'nullify_non_positive'
                && ($operation['table'] ?? null) === 'glpi_displaypreferences'
                && ($operation['column'] ?? null) === 'users_id';
        });
        $this->integer($alter_index)->isLessThan($cleanup_index);

        $this->array($this->findOperation($up_operations, static function (array $operation): bool {
            return ($operation['kind'] ?? null) === 'nullify_non_positive'
                && ($operation['table'] ?? null) === 'glpi_displaypreferences'
                && ($operation['column'] ?? null) === 'users_id';
        }))->isIdenticalTo([
            'kind'   => 'nullify_non_positive',
            'table'  => 'glpi_displaypreferences',
            'column' => 'users_id',
        ]);

        $this->array($this->findOperation($up_operations, static function (array $operation): bool {
            return ($operation['kind'] ?? null) === 'raw_sql'
                && str_contains((string) ($operation['sql'] ?? ''), 'UPDATE glpi_displaypreferences SET users_id = NULL')
                && str_contains((string) ($operation['sql'] ?? ''), 'FROM glpi_users');
        }))->hasKey('sql');

        $up_column = $this->findAlterColumn($up_operations, 'glpi_displaypreferences', 'users_id');
        $this->array($up_column)->hasKeys(['name', 'type', 'nullable', 'default']);
        $this->string($up_column['name'])->isIdenticalTo('users_id');
        $this->string($up_column['type'])->isIdenticalTo('int32');
        $this->boolean($up_column['nullable'])->isTrue();
        $this->variable($up_column['default'])->isNull();

        $up_foreign_key = $this->findForeignKey($up_operations, 'glpi_displaypreferences', 'fk_glpi_displaypreferences_users_id', 'add');
        $this->array($up_foreign_key)->hasKeys(['name', 'columns', 'referenced_table', 'referenced_columns']);
        $this->string($up_foreign_key['name'])->isIdenticalTo('fk_glpi_displaypreferences_users_id');
        $this->array($up_foreign_key['columns'])->isIdenticalTo(['users_id']);
        $this->string($up_foreign_key['referenced_table'])->isIdenticalTo('glpi_users');
        $this->array($up_foreign_key['referenced_columns'])->isIdenticalTo(['id']);

        $this->array($this->findOperation($down_operations, static function (array $operation): bool {
            return ($operation['kind'] ?? null) === 'replace_null_with_zero'
                && ($operation['table'] ?? null) === 'glpi_displaypreferences'
                && ($operation['column'] ?? null) === 'users_id';
        }))->isIdenticalTo([
            'kind'   => 'replace_null_with_zero',
            'table'  => 'glpi_displaypreferences',
            'column' => 'users_id',
        ]);

        $down_column = $this->findAlterColumn($down_operations, 'glpi_displaypreferences', 'users_id');
        $this->array($down_column)->hasKeys(['name', 'type', 'nullable', 'default']);
        $this->string($down_column['name'])->isIdenticalTo('users_id');
        $this->string($down_column['type'])->isIdenticalTo('int32');
        $this->boolean($down_column['nullable'])->isFalse();
        $this->string((string) $down_column['default'])->isIdenticalTo('0');

        $down_foreign_key = $this->findForeignKey($down_operations, 'glpi_displaypreferences', 'fk_glpi_displaypreferences_users_id', 'drop');
        $this->array($down_foreign_key)->hasKeys(['name', 'action']);
        $this->string($down_foreign_key['name'])->isIdenticalTo('fk_glpi_displaypreferences_users_id');
        $this->string($down_foreign_key['action'])->isIdenticalTo('drop');
    }

    public function testCronTaskLogsSelfReferenceIsExcludedFromForeignKeys()
    {
        $migration = new \itsmng\Database\Migrations\Core\Migration202603271200_AddForeignKeys();

        $up_operations = $migration->buildOperations('up');

        $this->array($this->findForeignKey($up_operations, 'glpi_crontasklogs', 'fk_glpi_crontasklogs_crontasklogs_id', 'add'))
            ->isEmpty();
    }

    public function testDashboardsUserIdKeepsZeroSentinelSemantics()
    {
        $migration = new \itsmng\Database\Migrations\Core\Migration202603271200_AddForeignKeys();

        $up_operations = $migration->buildOperations('up');
        $down_operations = $migration->buildOperations('down');

        $this->array($this->findAlterColumn($up_operations, 'glpi_dashboards', 'userId'))->isEmpty();
        $this->array($this->findForeignKey($up_operations, 'glpi_dashboards', 'fk_glpi_dashboards_userId', 'add'))->isEmpty();
        $this->array($this->findAlterColumn($down_operations, 'glpi_dashboards', 'userId'))->isEmpty();
        $this->array($this->findForeignKey($down_operations, 'glpi_dashboards', 'fk_glpi_dashboards_userId', 'drop'))->isEmpty();
    }

    private function findOperation(array $operations, callable $predicate): array
    {
        foreach ($operations as $operation) {
            if ($predicate($operation)) {
                return $operation;
            }
        }

        return [];
    }

    private function findOperationIndex(array $operations, callable $predicate): int
    {
        foreach ($operations as $index => $operation) {
            if ($predicate($operation)) {
                return $index;
            }
        }

        return PHP_INT_MAX;
    }

    private function findAlterColumn(array $operations, string $table, string $column): array
    {
        foreach ($operations as $operation) {
            if (($operation['kind'] ?? null) !== 'alter_table' || ($operation['table'] ?? null) !== $table) {
                continue;
            }

            foreach ($operation['alter_columns'] ?? [] as $alter_column) {
                if (($alter_column['name'] ?? null) === $column) {
                    return $alter_column;
                }
            }
        }

        return [];
    }

    private function findForeignKey(array $operations, string $table, string $name, string $action): array
    {
        foreach ($operations as $operation) {
            if (($operation['kind'] ?? null) !== 'alter_table' || ($operation['table'] ?? null) !== $table) {
                continue;
            }

            foreach ($operation['foreign_keys'] ?? [] as $foreign_key) {
                if (($foreign_key['name'] ?? null) === $name && ($foreign_key['action'] ?? null) === $action) {
                    return $foreign_key;
                }
            }
        }

        return [];
    }
}
