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

class OidcProvider extends CommonDBTM
{
    public static $rightname = 'config';
    public static $undisclosedFields = ['client_secret'];

    public static function getTypeName($nb = 0)
    {
        return __('OpenID Connect sources');
    }

    public static function all($active = false)
    {
        global $DB;
        return iterator_to_array($DB->request([
            'FROM' => 'glpi_oidcproviders',
            'WHERE' => $active ? ['is_active' => 1] : [],
            'ORDER' => ['name', 'id'],
        ]));
    }

    public static function load($id, $active = true)
    {
        $provider = new self();
        if (!$provider->getFromDB((int) $id) || ($active && !$provider->fields['is_active'])) {
            throw new RuntimeException('OIDC source is unavailable.');
        }
        $plugin = $provider->fields['required_plugin'];
        if ($active && $plugin !== '' && !Plugin::isPluginActive($plugin)) {
            throw new RuntimeException('The plugin required by this OIDC source is unavailable.');
        }
        return $provider->fields;
    }

    public function prepareInputForAdd($input)
    {
        return $this->validateConfiguration($input);
    }

    public function cleanDBonPurge()
    {
        global $DB, $CFG_GLPI;
        OidcMapping::releaseGroups($this->getID());
        $DB->delete('glpi_oidcmappings', ['oidcproviders_id' => $this->getID()]);
        $DB->delete('glpi_oidcidentities', ['oidcproviders_id' => $this->getID()]);
        if ((int) ($CFG_GLPI['oidc_auto_login_provider'] ?? 0) === (int) $this->getID()) {
            Config::setConfigurationValues('core', ['oidc_auto_login_provider' => 0]);
        }
    }

    public function prepareInputForUpdate($input)
    {
        return $this->validateConfiguration($input);
    }

    private function validateConfiguration(array $input)
    {
        foreach ($input as $field => $value) {
            if (in_array($field, ['name', 'issuer', 'client_id', 'client_secret', 'scope', 'proxy', 'cert', 'logout_redirect',
                'link_method', 'login_claim', 'email_claim', 'login_claim_source', 'email_claim_source', 'required_plugin'], true)
                && !is_string($value)) {
                throw new InvalidArgumentException('OIDC configuration values must be strings.');
            }
        }
        $config = $input + $this->fields;
        foreach (['name', 'issuer', 'client_id'] as $field) {
            if (!isset($config[$field]) || !is_string($config[$field]) || trim($config[$field]) === '') {
                throw new InvalidArgumentException('Name, issuer and client ID are required.');
            }
            $input[$field] = trim($config[$field]);
        }
        self::validateUrl(stripslashes($config['issuer']), false);
        $logoutRedirect = stripslashes($config['logout_redirect'] ?? '');
        if ($logoutRedirect !== '') {
            self::validateUrl($logoutRedirect, true);
        }
        $linkMethod = $config['link_method'] ?? 'none';
        if (!in_array($linkMethod, ['none', 'username', 'email', 'plugin', 'legacy'], true)) {
            throw new InvalidArgumentException('Invalid account-linking policy.');
        }
        if ($linkMethod === 'legacy' && empty($this->fields['is_legacy'])) {
            throw new InvalidArgumentException('The legacy linking policy is reserved for migrated sources.');
        }
        if (!preg_match('/^[a-z0-9_]*$/i', $config['required_plugin'] ?? '')) {
            throw new InvalidArgumentException('Invalid OIDC plugin name.');
        }
        foreach (['login_claim', 'email_claim'] as $field) {
            OidcMapping::validatePath(stripslashes($config[$field] ?? ''));
        }
        foreach (['login_claim_source', 'email_claim_source'] as $field) {
            if (!in_array($config[$field] ?? 'userinfo', ['userinfo', 'id_token'], true)) {
                throw new InvalidArgumentException('Invalid OIDC account-matching payload.');
            }
        }
        if (isset($input['client_secret']) && $input['client_secret'] !== '') {
            $input['client_secret'] = Toolbox::sodiumEncrypt(stripslashes($input['client_secret']));
        } else {
            unset($input['client_secret']);
        }
        unset($input['is_legacy']);
        $input['date_mod'] = $_SESSION['glpi_currenttime'];
        return $input;
    }

    private static function validateUrl($url, $allowQuery)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid OIDC URL.');
        }
        $parts = parse_url($url);
        if (strtolower($parts['scheme']) !== 'https') {
            throw new InvalidArgumentException('OIDC URLs must use HTTPS.');
        }
        if (isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) {
            throw new InvalidArgumentException('OIDC URLs cannot contain credentials or fragments.');
        }
        if (!$allowQuery && isset($parts['query'])) {
            throw new InvalidArgumentException('The OIDC issuer cannot contain a query string.');
        }
    }

    public static function legacyId()
    {
        global $DB;
        $rows = $DB->request(['FROM' => 'glpi_oidcproviders', 'WHERE' => ['is_legacy' => 1]]);
        if (count($rows) !== 1) {
            throw new RuntimeException('An explicit OIDC source is required.');
        }
        return (int) $rows->next()['id'];
    }

    public static function escape($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function select($name, array $choices, $value, $id = null)
    {
        echo '<select class="form-select" id="' . self::escape($id ?? $name) . '" name="' . self::escape($name) . '">';
        foreach ($choices as $key => $label) {
            echo '<option value="' . self::escape($key) . '"' . ((string) $key === (string) $value ? ' selected' : '') . '>'
                . self::escape($label) . '</option>';
        }
        echo '</select>';
    }

    public static function showConfiguration()
    {
        global $CFG_GLPI;
        Session::checkRight('config', UPDATE);
        if ($_POST) {
            try {
                if (isset($_POST['automatic'])) {
                    $id = (int) ($_POST['automatic_provider'] ?? 0);
                    if ($id) {
                        self::load($id);
                    }
                    Config::setConfigurationValues('core', ['oidc_auto_login_provider' => $id]);
                    Html::redirect('auth.oidc.php');
                } elseif (isset($_POST['save'])) {
                    $provider = new self();
                    $id = (int) ($_POST['id'] ?? 0);
                    $fields = array_intersect_key($_POST, array_flip([
                        'name', 'issuer', 'client_id', 'client_secret', 'scope', 'proxy', 'cert',
                        'logout_redirect', 'link_method', 'login_claim', 'email_claim', 'required_plugin',
                        'login_claim_source', 'email_claim_source',
                    ]));
                    $fields['is_active'] = empty($_POST['is_active']) ? 0 : 1;
                    $fields['allow_provisioning'] = empty($_POST['allow_provisioning']) ? 0 : 1;
                    $fields['use_pkce'] = empty($_POST['use_pkce']) ? 0 : 1;
                    if ($id) {
                        $saved = $provider->update(['id' => $id] + $fields);
                    } else {
                        $id = $provider->add($fields);
                        $saved = (bool) $id;
                    }
                    if (!$saved) {
                        throw new RuntimeException('Unable to save OIDC source.');
                    }
                    Html::redirect('auth.oidc.php?id=' . $id);
                }
            } catch (InvalidArgumentException | RuntimeException $e) {
                Session::addMessageAfterRedirect($e->getMessage(), false, ERROR);
                Html::redirect('auth.oidc.php' . (!empty($_GET['id']) ? '?id=' . (int) $_GET['id'] : '?new=1'));
            }
        }
        $base = $CFG_GLPI['root_doc'] . '/front/';
        echo '<p><a class="btn btn-primary" href="' . self::escape($base . 'auth.oidc.php?new=1') . '">' . __('Add') . '</a></p>';
        echo '<table class="tab_cadre_fixe table"><tr><th>' . __('Name') . '</th><th>' . __('Active') . '</th><th>' . __('Issuer') . '</th></tr>';
        $choices = [0 => __('No automatic login')];
        foreach (self::all() as $source) {
            echo '<tr><td><a href="' . self::escape($base . 'auth.oidc.php?id=' . $source['id']) . '">' . self::escape($source['name'])
                . '</a></td><td>' . ($source['is_active'] ? __('Yes') : __('No')) . '</td><td>' . self::escape($source['issuer']) . '</td></tr>';
            if ($source['is_active']) {
                $choices[$source['id']] = $source['name'];
            }
        }
        echo '</table><form method="post"><label for="automatic_provider">' . __('Automatic login source') . '</label>';
        self::select('automatic_provider', $choices, $CFG_GLPI['oidc_auto_login_provider'] ?? 0);
        echo '<button class="btn btn-secondary" name="automatic" value="1">' . __('Save') . '</button>';
        Html::closeForm();
        if (!isset($_GET['id']) && !isset($_GET['new'])) {
            return;
        }
        $source = isset($_GET['id']) ? self::load((int) $_GET['id'], false) : [];
        echo '<form method="post"><input type="hidden" name="id" value="' . (int) ($source['id'] ?? 0) . '"><table class="tab_cadre_fixe table">';
        $labels = ['name' => __('Name'), 'issuer' => __('Issuer'), 'client_id' => __('Client ID'),
            'client_secret' => __('Client secret (leave blank to retain)'), 'scope' => __('Scopes'),
            'proxy' => __('Proxy'), 'cert' => __('Certificate path'), 'logout_redirect' => __('Logout return URL'),
            'login_claim' => __('Username claim path'), 'email_claim' => __('Email claim path'), 'required_plugin' => __('Required plugin')];
        foreach ($labels as $key => $label) {
            $value = $key === 'client_secret' ? '' : ($source[$key] ?? ['scope' => 'openid profile email', 'login_claim' => '/preferred_username', 'email_claim' => '/email'][$key] ?? '');
            echo '<tr><td><label for="oidc_' . $key . '">' . self::escape($label) . '</label></td><td><input class="form-control" id="oidc_' . $key . '" name="' . $key
                . '" type="' . ($key === 'client_secret' ? 'password' : 'text') . '" value="' . self::escape($value) . '" autocomplete="off"></td></tr>';
        }
        foreach (['login_claim_source' => __('Username claim payload'), 'email_claim_source' => __('Email claim payload')] as $key => $label) {
            echo '<tr><td><label for="' . $key . '">' . self::escape($label) . '</label></td><td>';
            self::select($key, ['userinfo' => 'UserInfo', 'id_token' => 'ID token'], $source[$key] ?? 'userinfo');
            echo '</td></tr>';
        }
        foreach (['is_active' => __('Active'), 'allow_provisioning' => __('Allow user creation'), 'use_pkce' => __('Use PKCE (S256)')] as $key => $label) {
            echo '<tr><td><label for="oidc_' . $key . '">' . self::escape($label) . '</label></td><td><input type="checkbox" id="oidc_' . $key . '" name="' . $key . '" value="1"' . (!empty($source[$key] ?? ($key === 'use_pkce' ? 1 : 0)) ? ' checked' : '') . '></td></tr>';
        }
        echo '<tr><td><label for="link_method">' . __('Initial account linking') . '</label></td><td>';
        $methods = ['none' => __('Disabled'), 'username' => __('Username'), 'email' => __('Verified email'), 'plugin' => __('Plugin')];
        if (!empty($source['is_legacy'])) {
            $methods['legacy'] = __('Migrated legacy policy');
        }
        self::select('link_method', $methods, $source['link_method'] ?? 'none');
        echo '</td></tr></table><button class="btn btn-primary" name="save" value="1">' . __('Save') . '</button>';
        if (!empty($source['id'])) {
            echo ' <a class="btn btn-secondary" href="' . self::escape($base . 'auth.oidc_profile.php?id=' . $source['id']) . '">' . __('Mapping') . '</a>';
        }
        Html::closeForm();
    }
}
