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

/** Convert singleton OIDC settings into provider records. */
function migrateOidcProviders($encryptedLegacySecrets)
{
    global $DB;
    $DB->disableTableCaching();
    foreach (preg_split('/;\s*(?:\n|$)/', trim(file_get_contents(__DIR__ . '/oidc-schema.sql'))) as $statement) {
        if (trim($statement) !== '') {
            $DB->queryOrDie($statement, 'Unable to create the OIDC schema');
        }
    }
    $state = Config::getConfigurationValues('core', ['oidc_migration_completed', 'itsmversion']);
    if (empty($state['oidc_migration_completed']) && $DB->tableExists('glpi_oidc_config')) {
        $configs = iterator_to_array($DB->request('glpi_oidc_config'));
        $mappings = $DB->tableExists('glpi_oidc_mapping') ? iterator_to_array($DB->request('glpi_oidc_mapping')) : [];
        if (count($configs) > 1 || count($mappings) > 1) {
            throw new RuntimeException('Multiple legacy OIDC configurations require explicit reconciliation before migration.');
        }
        $DB->beginTransaction();
        try {
            if ($configs) {
                $legacy = reset($configs);
                $mapping = $mappings ? reset($mappings) : [];
                migrateLegacyOidcSource($legacy, $mapping, $state, $encryptedLegacySecrets);
            }
            Config::setConfigurationValues('core', ['oidc_migration_completed' => 1]);
            $DB->commit();
        } catch (Throwable $e) {
            $DB->rollback();
            throw $e;
        }
    }
    // Commit copied settings before dropping legacy tables so interrupted cleanup can be retried.
    foreach (['glpi_oidc_config', 'glpi_oidc_users'] as $table) {
        if ($DB->tableExists($table)) {
            $DB->queryOrDie('DROP TABLE `' . $table . '`', 'Unable to remove legacy OIDC table');
        }
    }
    $kind = $DB->request("SHOW FULL TABLES LIKE 'glpi_oidc_mapping'")->next();
    if ($kind && in_array('BASE TABLE', $kind, true)) {
        $DB->queryOrDie('DROP TABLE `glpi_oidc_mapping`', 'Unable to remove legacy OIDC mappings');
    }
    createOidcLegacyMappingView();
}

/** Copy singleton settings within the migration transaction. */
function migrateLegacyOidcSource(array $legacy, array $mapping, array $state, $encryptedLegacySecrets)
{
    global $DB;
    $existing = $DB->request(['FROM' => 'glpi_oidcproviders', 'WHERE' => ['is_legacy' => 1]])->next();
    if ($existing) {
        throw new RuntimeException('An unfinished OIDC migration conflicts with an existing legacy source.');
    }
    $secret = $legacy['ClientSecret'] ?? '';
    if (!$encryptedLegacySecrets && $secret !== '') {
        $secret = Toolbox::sodiumEncrypt($secret);
    }
    $pointer = function ($key) {
        return '/' . str_replace(['~', '/'], ['~0', '~1'], $key);
    };
    $fallback = version_compare($state['itsmversion'] ?? '1.0.0', '2.0.0', '>=') ? 'name' : 'sub';
    $loginClaim = empty($mapping['name']) ? '/' . $fallback : $pointer($mapping['name']);
    $DB->insertOrDie('glpi_oidcproviders', array_map(function ($value) {
        return is_string($value) ? Oidc::literal($value) : $value;
    }, [
        'name' => 'OpenID Connect', 'issuer' => $legacy['Provider'] ?? '',
        'client_id' => $legacy['ClientID'] ?? '', 'client_secret' => $secret,
        'is_active' => $legacy['is_activate'] ?? 0, 'scope' => $legacy['scope'] ?? '',
        'proxy' => $legacy['proxy'] ?? '', 'cert' => $legacy['cert'] ?? '',
        'logout_redirect' => $legacy['logout'] ?? '', 'link_method' => 'legacy',
        'login_claim' => $loginClaim, 'email_claim' => !empty($mapping['email']) ? $pointer($mapping['email']) : '/email',
        'allow_provisioning' => 1, 'use_pkce' => 0, 'is_legacy' => 1, 'legacy_link_users' => $legacy['sso_link_users'] ?? 1,
        'legacy_login_fallback' => $fallback,
        'date_mod' => $_SESSION['glpi_currenttime'],
    ]));
    $id = (int) $DB->insertId();
    foreach (['name' => 'name', 'given_name' => 'firstname', 'family_name' => 'realname',
        'picture' => 'picture', 'email' => 'email', 'locale' => 'language', 'phone_number' => 'phone', 'group' => 'groups'] as $claim => $target) {
        if (empty($mapping[$claim])) {
            continue;
        }
        // Legacy keys are literal, including keys containing a slash or tilde.
        $path = '/' . str_replace(['~', '/'], ['~0', '~1'], $mapping[$claim]);
        $DB->insertOrDie('glpi_oidcmappings', array_map(function ($value) {
            return is_string($value) ? Oidc::literal($value) : $value;
        }, ['oidcproviders_id' => $id, 'claim_path' => $path,
            'target' => $target, 'lookup_mode' => $target === 'groups' ? 'create' : 'id']));
    }
    migrateLegacyOidcUsers($id);
    Config::setConfigurationValues('core', ['oidc_auto_login_provider' => !empty($legacy['is_forced']) && !empty($legacy['is_activate']) ? $id : 0]);
}

/** Leave issuer and subject unset until the user completes a verified OIDC login. */
function migrateLegacyOidcUsers($providerId)
{
    global $DB;
    if (!$DB->tableExists('glpi_oidc_users')) {
        return;
    }
    $users = [];
    foreach ($DB->request('glpi_oidc_users') as $row) {
        $userId = (int) $row['user_id'];
        if ($userId < 1 || isset($users[$userId])) {
            continue;
        }
        $existing = $DB->request(['FROM' => 'glpi_users', 'WHERE' => ['id' => $userId]]);
        if (!count($existing)) {
            continue;
        }
        $DB->insertOrDie('glpi_oidcidentities', ['oidcproviders_id' => $providerId, 'users_id' => $userId,
            'needs_sync' => empty($row['update']) ? 1 : 0]);
        $users[$userId] = true;
    }
}

/** Expose migrated claim names to importers that still read glpi_oidc_mapping. */
function createOidcLegacyMappingView()
{
    global $DB;
    $columns = [];
    foreach (['name' => 'name', 'given_name' => 'firstname', 'family_name' => 'realname', 'picture' => 'picture',
        'email' => 'email', 'locale' => 'language', 'phone_number' => 'phone', 'group' => 'groups'] as $old => $target) {
        $columns[] = "COALESCE(MAX(CASE WHEN m.target = '$target' THEN REPLACE(REPLACE(SUBSTRING(m.claim_path, 2), '~1', '/'), '~0', '~') END), '') AS `$old`";
    }
    $DB->queryOrDie(
        "CREATE OR REPLACE SQL SECURITY INVOKER VIEW `glpi_oidc_mapping` AS SELECT 0 AS id, " . implode(', ', $columns)
        . ", p.date_mod FROM glpi_oidcproviders p LEFT JOIN glpi_oidcmappings m ON m.oidcproviders_id = p.id WHERE p.is_legacy = 1 GROUP BY p.id, p.date_mod",
        'Unable to create the legacy OIDC mapping view'
    );
}
