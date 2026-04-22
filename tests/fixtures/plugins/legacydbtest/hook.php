<?php

function plugin_legacydbtest_table(): string
{
    return 'glpi_plugin_legacydbtest_rawrecords';
}

function plugin_legacydbtest_trace(string $event, $payload = null): void
{
    $_SESSION['plugin_api_traces']['legacydbtest'][] = [
        'event'   => $event,
        'payload' => $payload,
    ];
}

function plugin_legacydbtest_init_session(): void
{
    plugin_legacydbtest_trace('init_session');
}

function plugin_legacydbtest_change_profile(): void
{
    plugin_legacydbtest_trace('change_profile');
}

function plugin_legacydbtest_check_config(): bool
{
    return true;
}

function plugin_legacydbtest_check_prerequisites(): bool
{
    global $DB;

    if (($DB->getDbType() ?? 'mysql') !== 'mysql') {
        echo 'Legacy DB test plugin requires MySQL.';
        return false;
    }

    return true;
}

function plugin_legacydbtest_install(array $params = []): bool
{
    global $DB;

    plugin_legacydbtest_trace('install', $params);

    $table = plugin_legacydbtest_table();
    $DB->queryOrDie(
        "CREATE TABLE `$table` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `code` varchar(64) NOT NULL,
            `payload` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_code` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
        'Create legacy DB fixture table'
    );

    Config::setConfigurationValues('plugin:legacydbtest', [
        'installed' => '1',
    ]);

    return true;
}

function plugin_legacydbtest_uninstall(): void
{
    global $DB;

    plugin_legacydbtest_trace('uninstall');

    $table = plugin_legacydbtest_table();
    if ($DB->tableExists($table, false)) {
        $DB->queryOrDie("DROP TABLE IF EXISTS `$table`", 'Drop legacy DB fixture table');
    }

    Config::deleteConfigurationValues('plugin:legacydbtest', ['installed']);
}

function plugin_legacydbtest_handle_standard_hook(array $data): void
{
    plugin_legacydbtest_trace('standard_hook', $data);
}

function plugin_legacydbtest_handle_object_hook(CommonDBTM $item): void
{
    plugin_legacydbtest_trace('object_hook', get_class($item));
}

function plugin_legacydbtest_handle_reduce_hook(array $data): array
{
    $data['plugins'][] = 'legacydbtest';
    plugin_legacydbtest_trace('reduce_hook', $data);

    return $data;
}

function plugin_legacydbtest_run_raw_mysql_cycle(array $input): array
{
    global $DB;

    $table = plugin_legacydbtest_table();
    $code = (string) ($input['code'] ?? 'legacy');
    $payloads = (array) ($input['payloads'] ?? ['first', 'second']);
    $payloads = array_pad($payloads, 2, end($payloads));

    foreach ($payloads as $payload) {
        $DB->queryOrDie(
            sprintf(
                "INSERT INTO `%s` (`code`, `payload`) VALUES (%s, %s) ON DUPLICATE KEY UPDATE `payload` = VALUES(`payload`)",
                $table,
                $DB->quoteValue($code),
                $DB->quoteValue((string) $payload)
            ),
            'Run raw MySQL upsert'
        );
    }

    $row = $DB->fetchAssoc(
        $DB->queryOrDie(
            sprintf(
                "SELECT `id`, `code`, `payload` FROM `%s` WHERE `code` = %s",
                $table,
                $DB->quoteValue($code)
            ),
            'Read legacy DB fixture row'
        )
    );

    $indexes = [];
    $index_result = $DB->queryOrDie(
        sprintf("SHOW INDEX FROM `%s`", $table),
        'Read legacy DB fixture indexes'
    );
    while ($index = $DB->fetchAssoc($index_result)) {
        $indexes[] = $index['Key_name'];
    }

    plugin_legacydbtest_trace('raw_mysql_cycle', [
        'code'    => $code,
        'row'     => $row,
        'indexes' => $indexes,
    ]);

    return [
        'row'     => $row,
        'indexes' => array_values(array_unique($indexes)),
        'dbtype'  => $DB->getDbType(),
    ];
}
