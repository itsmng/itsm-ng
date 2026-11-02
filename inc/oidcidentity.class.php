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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/** Associate provider-issued (issuer, subject) pairs with local users. */
class OidcIdentity extends CommonDBTM
{
    public static $rightname = 'config';

    public static function key($issuer, $subject)
    {
        return hash('sha256', strlen($issuer) . ':' . $issuer . $subject);
    }

    public static function findUser(array $provider, array $identity)
    {
        global $DB;
        $row = $DB->request(['FROM' => 'glpi_oidcidentities', 'WHERE' => [
            'oidcproviders_id' => $provider['id'], 'identity_key' => self::key($identity['iss'], $identity['sub']),
        ]])->next();
        if ($row && ($row['issuer'] !== $identity['iss'] || $row['subject'] !== $identity['sub'])) {
            throw new RuntimeException('Conflicting OIDC identity.');
        }
        return $row ? (int) $row['users_id'] : null;
    }

    public static function bind(array $provider, array $identity, $userId)
    {
        global $DB;
        $existing = self::findUser($provider, $identity);
        if ($existing !== null && $existing !== (int) $userId) {
            throw new RuntimeException('This OIDC identity is already associated with another account.');
        }
        $userId = (int) $userId;
        if ($existing === null) {
            $statement = $DB->prepare('INSERT INTO glpi_oidcidentities (oidcproviders_id, users_id, issuer, subject, identity_key) VALUES (?, ?, ?, ?, ?)');
            if (!$statement) {
                throw new RuntimeException('Unable to prepare the OIDC identity association.');
            }
            $providerId = (int) $provider['id'];
            $key = self::key($identity['iss'], $identity['sub']);
            try {
                $statement->bind_param('iisss', $providerId, $userId, $identity['iss'], $identity['sub'], $key);
                if (!$statement->execute()) {
                    throw new RuntimeException('Unable to associate OIDC identity.');
                }
            } finally {
                $statement->close();
            }
        }
        $DB->delete('glpi_oidcidentities', ['oidcproviders_id' => $provider['id'], 'users_id' => $userId, 'identity_key' => null]);
    }

    public static function resolve(array $provider, array $identity, array $claims)
    {
        $id = self::findUser($provider, $identity);
        if ($id !== null) {
            return $id;
        }
        $matched = self::resolveWithPlugins($provider, $identity, $claims);
        if ($matched !== null) {
            return $matched;
        }
        switch ($provider['link_method']) {
            case 'plugin':
                throw new RuntimeException('The OIDC source plugin did not resolve an account.');
            case 'none':
                return null;
            case 'email':
                $ids = self::matchEmail($provider, $claims);
                break;
            default:
                $ids = self::matchUsername($provider, $claims);
        }
        if (count($ids) > 1) {
            throw new RuntimeException('OIDC account matching is ambiguous.');
        }
        return $ids[0] ?? null;
    }

    private static function resolveWithPlugins(array $provider, array $identity, array $claims)
    {
        global $PLUGIN_HOOKS;
        $context = [
            'provider' => $provider, 'identity' => $identity, 'claims' => $claims,
            'decision' => 'continue', 'users_id' => null,
        ];
        $matched = null;
        foreach ($PLUGIN_HOOKS['oidc_resolve_user'] ?? [] as $plugin => $callback) {
            if (!Plugin::isPluginActive($plugin)) {
                continue;
            }
            $response = Plugin::doOneHook($plugin, $callback, $context);
            if (!is_array($response) || !in_array($response['decision'] ?? '', ['continue', 'match', 'deny'], true)) {
                throw new RuntimeException('Invalid OIDC account-resolution response.');
            }
            if ($response['decision'] === 'deny') {
                throw new RuntimeException('OIDC login was denied by the source plugin.');
            }
            if ($response['decision'] !== 'match') {
                continue;
            }
            if (filter_var($response['users_id'], FILTER_VALIDATE_INT) === false || (int) $response['users_id'] < 1) {
                throw new RuntimeException('Invalid OIDC user association.');
            }
            if ($matched !== null && $matched !== (int) $response['users_id']) {
                throw new RuntimeException('Conflicting OIDC plugin account matches.');
            }
            $matched = (int) $response['users_id'];
        }
        return $matched;
    }

    private static function matchEmail(array $provider, array $claims)
    {
        global $DB;
        $payload = $claims[$provider['email_claim_source'] ?? 'userinfo'];
        $email = OidcMapping::claim($payload, $provider['email_claim']);
        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Initial email linking requires a verified email claim.');
        }
        if (($payload['email_verified'] ?? false) !== true) {
            throw new RuntimeException('Initial email linking requires a verified email claim.');
        }
        $rows = $DB->request(['SELECT' => ['users_id'], 'DISTINCT' => true, 'FROM' => 'glpi_useremails', 'WHERE' => ['email' => Oidc::literal($email)]]);
        return array_values(array_unique(array_map('intval', array_column(iterator_to_array($rows), 'users_id'))));
    }

    private static function matchUsername(array $provider, array $claims)
    {
        global $DB;
        $username = Oidc::username($provider, $claims);
        $rows = $DB->request(['FROM' => 'glpi_users', 'WHERE' => ['name' => Oidc::literal($username)]]);
        $ids = [];
        foreach ($rows as $row) {
            if (!self::canLinkLegacyUser($provider, $row)) {
                continue;
            }
            $ids[] = (int) $row['id'];
        }
        return $ids;
    }

    private static function canLinkLegacyUser(array $provider, array $user)
    {
        global $DB;
        if ($provider['link_method'] !== 'legacy' || !empty($provider['legacy_link_users'])) {
            return true;
        }
        if ((int) $user['authtype'] !== Auth::EXTERNAL) {
            return false;
        }
        $pending = $DB->request(['FROM' => 'glpi_oidcidentities', 'WHERE' => [
            'users_id' => $user['id'], 'oidcproviders_id' => $provider['id'], 'identity_key' => null,
        ]]);
        if (count($pending)) {
            return true;
        }
        $other = $DB->request(['FROM' => 'glpi_oidcidentities', 'WHERE' => [
            'users_id' => $user['id'], 'oidcproviders_id' => ['!=', $provider['id']],
        ]]);
        return count($other) === 0;
    }
}
