<?php

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2022 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

namespace tests\units;

use itsmng\Database\Migrations\MigrationHistoryRepository;
use itsmng\Database\Migrations\MigrationRepository;
use itsmng\Database\Schema\SchemaInstaller;

class PluginApi extends \GLPITestCase
{
    private const LEGACY_PLUGIN = 'legacydbtest';
    private const PORTABLE_PLUGIN = 'portabledbtest';
    private const LEGACY_TABLE = 'glpi_plugin_legacydbtest_rawrecords';
    private const PORTABLE_TABLE = 'glpi_plugin_portabledbtest_records';
    private const LEGACY_CONTEXT = 'plugin:legacydbtest';
    private const PORTABLE_CONTEXT = 'plugin:portabledbtest';

    public function getTestedClassName()
    {
        return \Plugin::class;
    }

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);

        unset($_SESSION['plugin_api_traces'], $_SESSION['MESSAGE_AFTER_REDIRECT']);
        $this->cleanupPluginFixture(self::PORTABLE_PLUGIN);
        $this->cleanupPluginFixture(self::LEGACY_PLUGIN);
    }

    public function afterTestMethod($method)
    {
        $this->cleanupPluginFixture(self::PORTABLE_PLUGIN);
        $this->cleanupPluginFixture(self::LEGACY_PLUGIN);
        unset($_SESSION['plugin_api_traces'], $_SESSION['MESSAGE_AFTER_REDIRECT']);

        parent::afterTestMethod($method);
    }

    public function testLegacyPluginLifecycleAndHooksOnMySql()
    {
        global $DB;

        if (!$this->isMysql()) {
            $this->skip('Legacy raw MySQL fixture only runs on MySQL.');
        }

        $this->installPlugin(self::LEGACY_PLUGIN);
        $this->assertPluginState(self::LEGACY_PLUGIN, \Plugin::NOTACTIVATED);

        $this->boolean($this->activatePlugin(self::LEGACY_PLUGIN))->isTrue();
        $this->assertPluginState(self::LEGACY_PLUGIN, \Plugin::ACTIVATED);
        $this->boolean($this->hasTraceEvent(self::LEGACY_PLUGIN, 'init_session'))->isTrue();
        $this->boolean($this->hasTraceEvent(self::LEGACY_PLUGIN, 'change_profile'))->isTrue();

        $cycle = \Plugin::doOneHook(self::LEGACY_PLUGIN, 'run_raw_mysql_cycle', [
            'code'     => 'legacy-code',
            'payloads' => ['first-pass', 'second-pass'],
        ]);

        $this->string($cycle['dbtype'])->isIdenticalTo('mysql');
        $this->array($cycle['indexes'])->containsValues(['PRIMARY', 'uniq_code']);
        $this->string($cycle['row']['code'])->isIdenticalTo('legacy-code');
        $this->string($cycle['row']['payload'])->isIdenticalTo('second-pass');

        \Plugin::doHook(self::LEGACY_PLUGIN . '_standard_hook', ['marker' => 'legacy-standard']);
        \Plugin::doHook(self::LEGACY_PLUGIN . '_object_hook', new \Computer());
        $reduced = \Plugin::doHookFunction(self::LEGACY_PLUGIN . '_reduce_hook', ['plugins' => []]);

        $this->array($reduced['plugins'])->containsValues([self::LEGACY_PLUGIN]);
        $this->boolean($this->hasTracePayload(self::LEGACY_PLUGIN, 'standard_hook', ['marker' => 'legacy-standard']))->isTrue();
        $this->boolean($this->hasTracePayload(self::LEGACY_PLUGIN, 'object_hook', 'Computer'))->isTrue();
        $this->boolean($DB->tableExists(self::LEGACY_TABLE, false))->isTrue();
        $this->array(\Config::getConfigurationValues(self::LEGACY_CONTEXT))->hasKey('installed');

        $this->boolean($this->unactivatePlugin(self::LEGACY_PLUGIN))->isTrue();
        $this->assertPluginState(self::LEGACY_PLUGIN, \Plugin::NOTACTIVATED);

        $this->uninstallPlugin(self::LEGACY_PLUGIN);
        $this->assertPluginState(self::LEGACY_PLUGIN, \Plugin::NOTINSTALLED);
        $this->boolean($DB->tableExists(self::LEGACY_TABLE, false))->isFalse();
        $this->array(\Config::getConfigurationValues(self::LEGACY_CONTEXT))->notHasKey('installed');
    }

    public function testLegacyPluginCannotBeActivatedOnPostgreSql()
    {
        if (!$this->isPgsql()) {
            $this->skip('Legacy PostgreSQL rejection only runs on PostgreSQL.');
        }

        $plugin = $this->discoverPlugin(self::LEGACY_PLUGIN);

        $this->boolean($plugin->activate((int) $plugin->fields['id']))->isFalse();
        $this->assertPluginState(self::LEGACY_PLUGIN, \Plugin::NOTINSTALLED);

        $messages = $this->clearMessages();
        $error_messages = implode("\n", $messages[ERROR] ?? []);
        $this->string($error_messages)->contains('cannot be activated');
        $this->string($error_messages)->contains('requires MySQL');
    }

    public function testPortablePluginInstallHooksAndLegacyQueryBuilderRuntime()
    {
        global $DB;

        $this->installPlugin(self::PORTABLE_PLUGIN);
        $this->assertPluginState(self::PORTABLE_PLUGIN, \Plugin::NOTACTIVATED);
        $this->boolean($DB->tableExists(self::PORTABLE_TABLE, false))->isTrue();
        $this->boolean($DB->fieldExists(self::PORTABLE_TABLE, 'note'))->isTrue();
        $this->array($this->portableMigrationVersions())->isIdenticalTo([
            'portabledbtest_202603270101_create_records_table',
            'portabledbtest_202603270102_add_note_column',
        ]);

        $config = \Config::getConfigurationValues(self::PORTABLE_CONTEXT);
        $this->array($config)->hasKey('installed');
        $this->string($config['installed'])->isIdenticalTo('1');
        $this->array(json_decode($config['last_applied_versions'], true))->isIdenticalTo([
            'portabledbtest_202603270101_create_records_table',
            'portabledbtest_202603270102_add_note_column',
        ]);

        $this->boolean($this->activatePlugin(self::PORTABLE_PLUGIN))->isTrue();
        $this->assertPluginState(self::PORTABLE_PLUGIN, \Plugin::ACTIVATED);
        $this->boolean($this->hasTraceEvent(self::PORTABLE_PLUGIN, 'init_session'))->isTrue();
        $this->boolean($this->hasTraceEvent(self::PORTABLE_PLUGIN, 'change_profile'))->isTrue();

        \Plugin::doHook(self::PORTABLE_PLUGIN . '_standard_hook', ['marker' => 'portable-standard']);
        \Plugin::doHook(self::PORTABLE_PLUGIN . '_object_hook', new \Computer());
        $reduced = \Plugin::doHookFunction(self::PORTABLE_PLUGIN . '_reduce_hook', ['plugins' => []]);
        $cycle = \Plugin::doOneHook(self::PORTABLE_PLUGIN, 'run_builder_cycle', [
            'code'    => 'portable-code',
            'payload' => 'payload',
            'note'    => 'note-text',
        ]);

        $this->array($reduced['plugins'])->containsValues([self::PORTABLE_PLUGIN]);
        $this->boolean($this->hasTracePayload(self::PORTABLE_PLUGIN, 'standard_hook', ['marker' => 'portable-standard']))->isTrue();
        $this->boolean($this->hasTracePayload(self::PORTABLE_PLUGIN, 'object_hook', 'Computer'))->isTrue();
        $this->string($cycle['updated']['payload'])->isIdenticalTo('payload-updated');
        $this->string($cycle['updated']['note'])->isIdenticalTo('note-text');
        $this->integer((int) $cycle['remaining_records'])->isIdenticalTo(0);
        $this->string($cycle['dbtype'])->isIdenticalTo($this->isPgsql() ? 'pgsql' : 'mysql');
        $this->string($cycle['insert_sql'])->contains($DB->quoteName(self::PORTABLE_TABLE));
        $this->string($cycle['update_sql'])->contains($DB->quoteName(self::PORTABLE_TABLE));
        $this->string($cycle['delete_sql'])->contains($DB->quoteName(self::PORTABLE_TABLE));

        $this->boolean($this->unactivatePlugin(self::PORTABLE_PLUGIN))->isTrue();
        $this->assertPluginState(self::PORTABLE_PLUGIN, \Plugin::NOTACTIVATED);

        $this->uninstallPlugin(self::PORTABLE_PLUGIN);
        $this->assertPluginState(self::PORTABLE_PLUGIN, \Plugin::NOTINSTALLED);
        $this->boolean($DB->tableExists(self::PORTABLE_TABLE, false))->isFalse();
        $this->array($this->portableMigrationVersions())->isEmpty();
        $this->array(\Config::getConfigurationValues(self::PORTABLE_CONTEXT))->notHasKey('installed');
    }

    public function testPortablePluginInstallOnlyAppliesPendingMigration()
    {
        global $DB;

        $this->discoverPlugin(self::PORTABLE_PLUGIN);
        $this->seedPortablePluginAtFirstMigration();
        unset($_SESSION['plugin_api_traces'][self::PORTABLE_PLUGIN]);

        $this->boolean($DB->tableExists(self::PORTABLE_TABLE, false))->isTrue();
        $this->boolean($DB->fieldExists(self::PORTABLE_TABLE, 'note'))->isFalse();
        $this->array($this->portableMigrationVersions())->isIdenticalTo([
            'portabledbtest_202603270101_create_records_table',
        ]);

        $this->installPlugin(self::PORTABLE_PLUGIN);
        $this->assertPluginState(self::PORTABLE_PLUGIN, \Plugin::NOTACTIVATED);
        $this->boolean($DB->fieldExists(self::PORTABLE_TABLE, 'note'))->isTrue();
        $this->array($this->portableMigrationVersions())->isIdenticalTo([
            'portabledbtest_202603270101_create_records_table',
            'portabledbtest_202603270102_add_note_column',
        ]);

        $config = \Config::getConfigurationValues(self::PORTABLE_CONTEXT);
        $this->array(json_decode($config['last_applied_versions'], true))->isIdenticalTo([
            'portabledbtest_202603270102_add_note_column',
        ]);
        $this->boolean(
            $this->hasTracePayload(
                self::PORTABLE_PLUGIN,
                'migrate',
                ['portabledbtest_202603270102_add_note_column']
            )
        )->isTrue();
    }

    private function isMysql(): bool
    {
        global $DB;

        return $DB instanceof \DBmysql && $DB->getDbType() === 'mysql';
    }

    private function isPgsql(): bool
    {
        global $DB;

        return $DB instanceof \DBmysql && $DB->getDbType() === 'pgsql';
    }

    private function discoverPlugin(string $directory): \Plugin
    {
        $plugin = new \Plugin();
        $plugin->checkStates(true);
        $this->boolean($plugin->getFromDBByCrit(['directory' => $directory]))->isTrue();

        return $plugin;
    }

    private function reloadPlugin(string $directory): \Plugin
    {
        $plugin = new \Plugin();
        $this->boolean($plugin->getFromDBByCrit(['directory' => $directory]))->isTrue();

        return $plugin;
    }

    private function installPlugin(string $directory): \Plugin
    {
        $plugin = $this->discoverPlugin($directory);
        $plugin->install((int) $plugin->fields['id']);
        $this->clearMessages();

        return $this->reloadPlugin($directory);
    }

    private function activatePlugin(string $directory): bool
    {
        $plugin = $this->reloadPlugin($directory);
        $result = $plugin->activate((int) $plugin->fields['id']);
        $this->clearMessages();

        return $result;
    }

    private function unactivatePlugin(string $directory): bool
    {
        $plugin = $this->reloadPlugin($directory);
        $result = $plugin->unactivate((int) $plugin->fields['id']);
        $this->clearMessages();

        return $result;
    }

    private function uninstallPlugin(string $directory): \Plugin
    {
        $plugin = $this->reloadPlugin($directory);
        $plugin->uninstall((int) $plugin->fields['id']);
        $this->clearMessages();

        return $this->reloadPlugin($directory);
    }

    private function clearMessages(): array
    {
        $messages = $_SESSION['MESSAGE_AFTER_REDIRECT'] ?? [];
        unset($_SESSION['MESSAGE_AFTER_REDIRECT']);

        return $messages;
    }

    private function assertPluginState(string $directory, int $state): void
    {
        $plugin = $this->reloadPlugin($directory);
        $this->integer((int) $plugin->fields['state'])->isIdenticalTo($state);
    }

    private function hasTraceEvent(string $plugin, string $event): bool
    {
        foreach ($_SESSION['plugin_api_traces'][$plugin] ?? [] as $trace) {
            if (($trace['event'] ?? null) === $event) {
                return true;
            }
        }

        return false;
    }

    private function hasTracePayload(string $plugin, string $event, $payload): bool
    {
        foreach ($_SESSION['plugin_api_traces'][$plugin] ?? [] as $trace) {
            if (($trace['event'] ?? null) === $event && ($trace['payload'] ?? null) == $payload) {
                return true;
            }
        }

        return false;
    }

    private function portableMigrationVersions(): array
    {
        global $DB;

        if (!$DB->tableExists(MigrationHistoryRepository::TABLE, false)) {
            return [];
        }

        $history = new MigrationHistoryRepository($DB);
        $versions = array_keys(array_filter(
            $history->applied(),
            static fn (array $metadata): bool => str_starts_with($metadata['migration'], 'tests\\fixtures\\plugins\\portabledbtest\\migrations\\')
        ));
        sort($versions);

        return $versions;
    }

    private function seedPortablePluginAtFirstMigration(): void
    {
        global $DB;

        $repository = new MigrationRepository($this->portableMigrationsDirectory());
        $migrations = $repository->all();
        $this->integer(count($migrations))->isGreaterThanOrEqualTo(2);

        $version = array_key_first($migrations);
        $metadata = $migrations[$version];
        $migration = new $metadata['class']();
        $installer = new SchemaInstaller();
        $installer->executeOperations($migration->buildOperations('up'), $DB);

        $history = new MigrationHistoryRepository($DB);
        $history->record($version, $metadata['class'], 1);
    }

    private function portableMigrationsDirectory(): string
    {
        return GLPI_ROOT . '/tests/fixtures/plugins/' . self::PORTABLE_PLUGIN . '/migrations';
    }

    private function cleanupPluginFixture(string $directory): void
    {
        global $DB;

        $plugin = new \Plugin();
        if ($plugin->getFromDBByCrit(['directory' => $directory])) {
            $id = (int) $plugin->fields['id'];
            $state = (int) $plugin->fields['state'];

            if ($state === \Plugin::ACTIVATED) {
                $plugin->unactivate($id);
                $plugin->getFromDB($id);
                $state = (int) $plugin->fields['state'];
            }

            if (in_array($state, [\Plugin::NOTACTIVATED, \Plugin::TOBECONFIGURED, \Plugin::NOTUPDATED], true)) {
                $plugin->uninstall($id);
                $plugin->getFromDB($id);
                $state = (int) $plugin->fields['state'];
            }

            if ($plugin->getFromDB($id)) {
                $plugin->clean($id);
            }
        }

        foreach ([self::LEGACY_TABLE, self::PORTABLE_TABLE] as $table) {
            if ($DB->tableExists($table, false)) {
                $DB->query('DROP TABLE IF EXISTS ' . $DB->quoteName($table));
            }
        }

        $DB->delete('glpi_configs', ['context' => [self::LEGACY_CONTEXT, self::PORTABLE_CONTEXT]]);

        if ($DB->tableExists(MigrationHistoryRepository::TABLE, false)) {
            $DB->delete(MigrationHistoryRepository::TABLE, [
                'version' => ['LIKE', 'portabledbtest_%'],
            ]);
        }

        $this->clearMessages();
        $plugin->init(false);
    }
}
