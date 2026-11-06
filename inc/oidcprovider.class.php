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
                'link_method', 'login_claim', 'email_claim', 'login_claim_source', 'email_claim_source', 'required_plugin',
                'button_label', 'button_color', 'button_icon'], true)
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
        if (isset($input['button_label'])) {
            $input['button_label'] = trim($input['button_label']);
        }
        $label = trim(stripslashes($config['button_label'] ?? ''));
        if (mb_strlen($label) > 255) {
            throw new InvalidArgumentException('The OIDC button text cannot exceed 255 characters.');
        }
        $color = $config['button_color'] ?? '';
        if ($color !== '' && !preg_match('/^#[0-9a-f]{6}$/iD', $color)) {
            throw new InvalidArgumentException('Use a six-digit hexadecimal button color.');
        }
        if (!array_key_exists($config['button_icon'] ?? '', self::buttonIcons())) {
            throw new InvalidArgumentException('Invalid OIDC button icon.');
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

    public static function pluginChoices($selected = '')
    {
        global $DB;
        $choices = ['' => __('None')];
        foreach ($DB->request([
            'SELECT' => ['directory', 'name', 'state'], 'FROM' => 'glpi_plugins',
            'WHERE' => ['state' => [Plugin::ACTIVATED, Plugin::NOTACTIVATED, Plugin::TOBECONFIGURED, Plugin::NOTUPDATED]],
            'ORDER' => ['name', 'directory'],
        ]) as $plugin) {
            $choices[$plugin['directory']] = (int) $plugin['state'] === Plugin::ACTIVATED
                ? $plugin['name'] : sprintf(__('%s (inactive)'), $plugin['name']);
        }
        if ($selected !== '' && !isset($choices[$selected])) {
            $choices[$selected] = sprintf(__('%s (unavailable)'), $selected);
        }
        return $choices;
    }

    public static function buttonIcons()
    {
        return ['' => __('No icon'), 'fa-key' => __('Key'), 'fa-building' => __('Organization'),
            'fa-users' => __('Users'), 'fa-shield' => __('Shield'), 'fa-globe' => __('Globe')];
    }

    public static function loginButton(array $source)
    {
        $label = trim($source['button_label'] ?? '');
        $button = ['label' => $label !== '' ? $label : $source['name'], 'style' => '', 'icon' => ''];
        $icon = $source['button_icon'] ?? '';
        if (array_key_exists($icon, self::buttonIcons())) {
            $button['icon'] = $icon;
        }
        $color = $source['button_color'] ?? '';
        if (!preg_match('/^#[0-9a-f]{6}$/iD', $color)) {
            return $button;
        }
        $channels = [];
        foreach (str_split(substr($color, 1), 2) as $channel) {
            $value = hexdec($channel) / 255;
            $channels[] = $value <= 0.04045 ? $value / 12.92 : pow(($value + 0.055) / 1.055, 2.4);
        }
        $luminance = 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
        $foreground = $luminance > 0.179 ? '#000000' : '#ffffff';
        $button['style'] = 'background:' . $color . ';border-color:' . $color . ';color:' . $foreground . ';';
        return $button;
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

    public static function saveConfiguration()
    {
        Session::checkRight('config', UPDATE);
        if (!$_POST) {
            return;
        }
        try {
            if (isset($_POST['automatic'])) {
                $id = (int) ($_POST['automatic_provider'] ?? 0);
                if ($id) {
                    self::load($id);
                }
                Config::setConfigurationValues('core', ['oidc_auto_login_provider' => $id]);
                Html::redirect('auth.oidc.php');
            }
            if (!isset($_POST['save'])) {
                return;
            }
            $provider = new self();
            $id = (int) ($_POST['id'] ?? 0);
            $fields = array_intersect_key($_POST, array_flip([
                'name', 'issuer', 'client_id', 'client_secret', 'scope', 'proxy', 'cert',
                'logout_redirect', 'link_method', 'login_claim', 'email_claim', 'required_plugin',
                'login_claim_source', 'email_claim_source', 'button_label', 'button_color', 'button_icon',
            ]));
            if (empty($_POST['use_button_color'])) {
                $fields['button_color'] = '';
            }
            foreach (['is_active', 'allow_provisioning', 'use_pkce'] as $field) {
                $fields[$field] = empty($_POST[$field]) ? 0 : 1;
            }
            if ($id) {
                $saved = $provider->update(['id' => $id] + $fields);
            } else {
                $id = $provider->add($fields);
                $saved = (bool) $id;
            }
            if (!$saved) {
                throw new RuntimeException('Unable to save OIDC source.');
            }
            Html::redirect('auth.oidc.form.php?id=' . $id);
        } catch (InvalidArgumentException | RuntimeException $e) {
            Session::addMessageAfterRedirect($e->getMessage(), false, ERROR);
            Html::redirect('auth.oidc.form.php' . (!empty($_POST['id']) ? '?id=' . (int) $_POST['id'] : ''));
        }
    }
}
