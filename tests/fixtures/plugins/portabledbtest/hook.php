<?php

use itsmng\Database\Migrations\MigrationHistoryRepository;
use itsmng\Database\Migrations\MigrationRepository;
use itsmng\Database\Migrations\MigrationRunner;
use itsmng\Database\Schema\SchemaInstaller;

function plugin_portabledbtest_table(): string
{
    return 'glpi_plugin_portabledbtest_records';
}

function plugin_portabledbtest_context(): string
{
    return 'plugin:portabledbtest';
}

function plugin_portabledbtest_migrations_directory(): string
{
    return __DIR__ . '/migrations';
}

function plugin_portabledbtest_trace(string $event, $payload = null): void
{
    $_SESSION['plugin_api_traces']['portabledbtest'][] = [
        'event'   => $event,
        'payload' => $payload,
    ];
}

function plugin_portabledbtest_init_session(): void
{
    plugin_portabledbtest_trace('init_session');
}

function plugin_portabledbtest_change_profile(): void
{
    plugin_portabledbtest_trace('change_profile');
}

function plugin_portabledbtest_check_config(): bool
{
    return true;
}

function plugin_portabledbtest_check_prerequisites(): bool
{
    global $DB;

    return in_array($DB->getDbType(), ['mysql', 'pgsql'], true);
}

function plugin_portabledbtest_install(array $params = []): bool
{
    plugin_portabledbtest_trace('install', $params);

    $versions = plugin_portabledbtest_apply_pending_migrations();

    Config::setConfigurationValues(plugin_portabledbtest_context(), [
        'installed'             => '1',
        'last_applied_versions' => json_encode($versions),
    ]);

    return true;
}

function plugin_portabledbtest_uninstall(): void
{
    plugin_portabledbtest_trace('uninstall');

    $versions = plugin_portabledbtest_rollback_all_migrations();

    Config::deleteConfigurationValues(plugin_portabledbtest_context(), [
        'installed',
        'last_applied_versions',
    ]);

    plugin_portabledbtest_trace('rollback', $versions);
}

function plugin_portabledbtest_handle_standard_hook(array $data): void
{
    plugin_portabledbtest_trace('standard_hook', $data);
}

function plugin_portabledbtest_handle_object_hook(CommonDBTM $item): void
{
    plugin_portabledbtest_trace('object_hook', get_class($item));
}

function plugin_portabledbtest_handle_reduce_hook(array $data): array
{
    $data['plugins'][] = 'portabledbtest';
    plugin_portabledbtest_trace('reduce_hook', $data);

    return $data;
}

function plugin_portabledbtest_apply_pending_migrations(): array
{
    global $DB;

    $repository = new MigrationRepository(plugin_portabledbtest_migrations_directory());
    $runner = new MigrationRunner($DB, $repository);
    $versions = $runner->migrate();

    plugin_portabledbtest_trace('migrate', $versions);

    return $versions;
}

function plugin_portabledbtest_rollback_all_migrations(): array
{
    global $DB;

    $repository = new MigrationRepository(plugin_portabledbtest_migrations_directory());
    $history = new MigrationHistoryRepository($DB);
    $applied = $history->applied();
    $available = $repository->all();
    $installer = new SchemaInstaller();
    $rolled_back = [];

    foreach (array_reverse($available, true) as $version => $metadata) {
        if (!isset($applied[$version])) {
            continue;
        }

        $migration = new $metadata['class']();
        $installer->executeOperations($migration->buildOperations('down'), $DB);
        $history->delete($version);
        $rolled_back[] = $version;
    }

    return $rolled_back;
}

function plugin_portabledbtest_run_builder_cycle(array $input): array
{
    global $DB;

    $table = plugin_portabledbtest_table();
    $code = (string) ($input['code'] ?? 'portable');
    $payload = (string) ($input['payload'] ?? 'payload');
    $note = isset($input['note']) ? (string) $input['note'] : null;

    $insert_sql = $DB->buildInsert($table, [
        'code'    => $code,
        'payload' => $payload,
    ]);
    $DB->queryOrDie($insert_sql, 'Insert portable DB fixture row');

    $row = $DB->request($table, ['code' => $code])->next();
    $update_sql = $DB->buildUpdate($table, [
        'payload' => $payload . '-updated',
        'note'    => $note,
    ], [
        'id' => $row['id'],
    ]);
    $DB->queryOrDie($update_sql, 'Update portable DB fixture row');

    $updated = $DB->request($table, ['id' => $row['id']])->next();
    $delete_sql = $DB->buildDelete($table, [
        'id' => $row['id'],
    ]);
    $DB->queryOrDie($delete_sql, 'Delete portable DB fixture row');

    $remaining = countElementsInTable($table, ['id' => $row['id']]);

    plugin_portabledbtest_trace('builder_cycle', [
        'insert_sql' => $insert_sql,
        'update_sql' => $update_sql,
        'delete_sql' => $delete_sql,
        'updated'    => $updated,
    ]);

    return [
        'insert_sql'        => $insert_sql,
        'update_sql'        => $update_sql,
        'delete_sql'        => $delete_sql,
        'updated'           => $updated,
        'remaining_records' => $remaining,
        'dbtype'            => $DB->getDbType(),
    ];
}
