<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org/
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ITSM-NG.
 *
 * ITSM-NG is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ITSM-NG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ITSM-NG. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

namespace tests\units;

/** Schema changes commit implicitly, so these tests cannot use DbTestCase's transaction. */
class OidcProvider extends \GLPITestCase
{
    public function testStableAndEncryptedLegacyMigrationAreResumable()
    {
        global $DB;
        require_once GLPI_ROOT . '/install/oidc-migration.php';
        $tables = ['glpi_oidcproviders', 'glpi_oidcmappings', 'glpi_oidcidentities', 'glpi_oidcgroupusers'];
        $snapshots = [];
        foreach ($tables as $table) {
            $snapshots[$table] = iterator_to_array($DB->request($table));
        }
        $names = ['oidc_migration_completed', 'oidc_auto_login_provider', 'itsmversion'];
        $configs = iterator_to_array($DB->request(['FROM' => 'glpi_configs', 'WHERE' => ['context' => 'core', 'name' => $names]]));
        try {
            foreach ([false, true] as $encrypted) {
                foreach ($tables as $table) {
                    $DB->delete($table, [1 => 1]);
                }
                $DB->delete('glpi_configs', ['context' => 'core', 'name' => ['oidc_migration_completed', 'oidc_auto_login_provider']]);
                $DB->queryOrDie('DROP VIEW IF EXISTS glpi_oidc_mapping');
                $DB->queryOrDie('CREATE TABLE glpi_oidc_config (id int PRIMARY KEY, Provider varchar(255), ClientID varchar(255), ClientSecret text, is_activate tinyint, is_forced tinyint, sso_link_users tinyint, scope varchar(255)) ENGINE=InnoDB');
                $DB->queryOrDie('CREATE TABLE glpi_oidc_mapping (id int PRIMARY KEY, name varchar(255), given_name varchar(255), email varchar(255), `group` varchar(255)) ENGINE=InnoDB');
                $DB->queryOrDie('CREATE TABLE glpi_oidc_users (id int PRIMARY KEY, user_id int, `update` tinyint) ENGINE=InnoDB');
                \Config::setConfigurationValues('core', ['itsmversion' => $encrypted ? '2.2.0' : '1.6.18']);
                $secret = $encrypted ? \Toolbox::sodiumEncrypt('migration-secret') : 'migration-secret';
                $DB->insert('glpi_oidc_config', ['id' => 0, 'Provider' => 'https://legacy.example.org', 'ClientID' => \Oidc::literal("legacy'client"),
                    'ClientSecret' => $secret, 'is_activate' => 1, 'is_forced' => 1, 'sso_link_users' => 1, 'scope' => 'openid,email']);
                $DB->insert('glpi_oidc_mapping', ['id' => 0, 'name' => '/legacy~login', 'given_name' => 'given_name', 'email' => 'email', 'group' => 'groups']);
                $userId = getItemByTypeName('User', TU_USER, true);
                $DB->insert('glpi_oidc_users', ['id' => 1, 'user_id' => $userId, 'update' => 0]);
                migrateOidcProviders($encrypted);
                $this->boolean($DB->tableExists('glpi_oidc_config', false))->isFalse();
                $this->boolean($DB->tableExists('glpi_oidc_users', false))->isFalse();
                $sources = \OidcProvider::all();
                $this->integer(count($sources))->isIdenticalTo(1);
                $provider = reset($sources);
                $this->string(\Toolbox::sodiumDecrypt($provider['client_secret']))->isIdenticalTo('migration-secret');
                $this->string($provider['login_claim'])->isIdenticalTo('/~1legacy~0login');
                $this->string($provider['client_id'])->isIdenticalTo("legacy'client");
                $this->integer((int) $provider['legacy_link_users'])->isIdenticalTo(1);
                $this->string($provider['legacy_login_fallback'])->isIdenticalTo($encrypted ? 'name' : 'sub');
                $this->string(\Oidc::username($provider, ['userinfo' => ['name' => 'display-name'], 'id_token' => ['sub' => 'opaque-sub']]))
                    ->isIdenticalTo($encrypted ? 'display-name' : 'opaque-sub');
                $this->integer(count(\OidcMapping::rows($provider['id'])))->isIdenticalTo(4);
                $mapping = $DB->request('glpi_oidc_mapping')->next();
                $this->string($mapping['name'])->isIdenticalTo('/legacy~login');
                $pending = $DB->request('glpi_oidcidentities')->next();
                $this->integer((int) $pending['users_id'])->isIdenticalTo((int) $userId);
                $this->variable($pending['subject'])->isNull();
                $this->variable($pending['identity_key'])->isNull();
                $this->integer((int) $pending['needs_sync'])->isIdenticalTo(1);
                // Once converted, settings must not be copied or encrypted again.
                migrateOidcProviders(true);
                $again = \OidcProvider::load($provider['id']);
                $this->string($again['client_secret'])->isIdenticalTo($provider['client_secret']);
                $this->integer(count(\OidcProvider::all()))->isIdenticalTo(1);
                $this->integer(count(\OidcMapping::rows($provider['id'])))->isIdenticalTo(4);
                $this->integer(count($DB->request('glpi_oidcidentities')))->isIdenticalTo(1);
                $auto = \Config::getConfigurationValues('core', ['oidc_auto_login_provider']);
                $this->integer((int) $auto['oidc_auto_login_provider'])->isIdenticalTo((int) $provider['id']);
            }
        } finally {
            foreach (['glpi_oidc_config', 'glpi_oidc_users'] as $table) {
                if ($DB->tableExists($table, false)) {
                    $DB->queryOrDie('DROP TABLE ' . $table);
                }
            }
            $kind = $DB->request("SHOW FULL TABLES LIKE 'glpi_oidc_mapping'")->next();
            if ($kind) {
                $DB->queryOrDie((in_array('VIEW', $kind, true) ? 'DROP VIEW ' : 'DROP TABLE ') . 'glpi_oidc_mapping');
            }
            foreach ($tables as $table) {
                $DB->delete($table, [1 => 1]);
                foreach ($snapshots[$table] as $row) {
                    $DB->insertOrDie($table, $row);
                }
            }
            $DB->delete('glpi_configs', ['context' => 'core', 'name' => $names]);
            foreach ($configs as $row) {
                $DB->insertOrDie('glpi_configs', $row);
            }
            createOidcLegacyMappingView();
        }
    }
}
