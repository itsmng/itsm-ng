<?php

declare(strict_types=1);

define('GLPI_ROOT', dirname(__DIR__, 2));
define('GLPI_CONFIG_DIR', __DIR__ . '/../config');
define('GLPI_VAR_DIR', __DIR__ . '/../files');
define(
    'PLUGINS_DIRECTORIES',
    [
        GLPI_ROOT . '/plugins',
        GLPI_ROOT . '/tests/fixtures/plugins',
    ]
);

require_once GLPI_ROOT . '/inc/based_config.php';

// Ensure all VAR directories exist (cache etc)
foreach (get_defined_constants() as $constant_name => $constant_value) {
    if (
        preg_match('/^GLPI_[\w]+_DIR$/', $constant_name)
        && preg_match('/^' . preg_quote(GLPI_VAR_DIR, '/') . '\//', $constant_value)
    ) {
        is_dir($constant_value) or mkdir($constant_value, 0755, true);
    }
}

require_once GLPI_ROOT . '/inc/includes.php';

$outputFile = $argv[1] ?? (__DIR__ . '/../files/_playwright/app-token');

Session::start();

$auth = new Auth();
if (!$auth->login('itsm', 'itsm', true)) {
    throw new RuntimeException('Unable to login with the default E2E admin account.');
}

// Prevents output polution from DEBUG mode (this thing is horrible)
Toolbox::setDebugMode(Session::NORMAL_MODE, false, false, false);

$user = new User();
if (!$user->getFromDBbyName('itsm')) {
    throw new RuntimeException('Unable to load the default E2E admin account.');
}

if ((int) ($user->fields['use_mode'] ?? Session::NORMAL_MODE) !== Session::NORMAL_MODE) {
    $result = $user->update([
        'id' => $user->getID(),
        'use_mode' => Session::NORMAL_MODE,
    ]);
    if (!$result) {
        throw new RuntimeException('Unable to force the default E2E admin account into normal mode.');
    }
}

$client = new APIClient();
$input = [
    'name' => 'E2E Playwright',
    'is_active' => 1,
    'ipv4_range_start' => '',
    'ipv4_range_end' => '',
    'ipv6' => '',
    '_reset_app_token' => true,
];

if ($client->getFromDBByCrit(['name' => $input['name']])) {
    $result = $client->update(['id' => $client->getID()] + $input);
    if (!$result) {
        throw new RuntimeException('Unable to update the E2E API client.');
    }
    $client->getFromDB((int) $client->getID());
} else {
    $clientId = $client->add($input);
    if (!$clientId) {
        throw new RuntimeException('Unable to create the E2E API client.');
    }
    $client->getFromDB((int) $clientId);
}

$appToken = $client->fields['app_token'] ?? null;
if (!is_string($appToken) || $appToken === '') {
    throw new RuntimeException('Unable to retrieve the E2E API client application token.');
}

$directory = dirname($outputFile);
if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
    throw new RuntimeException(sprintf('Unable to create output directory "%s".', $directory));
}

file_put_contents($outputFile, $appToken);
