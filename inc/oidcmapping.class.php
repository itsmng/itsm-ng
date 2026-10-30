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

class OidcMapping extends CommonDBTM
{
    public static $rightname = 'config';

    public static function targets()
    {
        global $DB, $CFG_GLPI;
        $targets = [];
        $scalars = ['name', 'firstname', 'realname', 'phone', 'phone2', 'mobile', 'language',
            'registration_number', 'comment'];
        $fields = $DB->listFields('glpi_users');
        foreach (array_unique(array_merge($scalars, $CFG_GLPI['user_pref_field'] ?? [])) as $field) {
            if (isset($fields[$field]) && !preg_match('/password|token|auth|^id$|^_/', $field)) {
                $targets[$field] = ['label' => $field, 'type' => preg_match('/^(tinyint|smallint|int|bigint)/', $fields[$field]['Type']) ? 'integer' : 'string'];
            }
        }
        foreach (['locations_id' => 'Location', 'usertitles_id' => 'UserTitle', 'usercategories_id' => 'UserCategory', 'users_id_supervisor' => 'User', 'default_requesttypes_id' => 'RequestType'] as $field => $class) {
            if (isset($fields[$field])) {
                $targets[$field] = ['label' => $class::getTypeName(1), 'type' => 'reference', 'itemtype' => $class];
            }
        }
        $targets['email'] = ['label' => __('Email'), 'type' => 'email'];
        $targets['picture'] = ['label' => __('Picture (managed file)'), 'type' => 'picture'];
        $targets['groups'] = ['label' => __('Groups'), 'type' => 'groups'];
        return Plugin::doHookFunction('oidc_mapping_targets', $targets);
    }

    public static function validatePath($path)
    {
        if (!is_string($path) || $path === '' || strlen($path) > 255 || ($path[0] === '/' && preg_match('/~(?![01])/', $path))) {
            throw new InvalidArgumentException('Use a claim name or a JSON Pointer such as /address/locality.');
        }
    }

    public static function claim(array $claims, $path, &$found = null)
    {
        self::validatePath($path);
        $parts = $path[0] === '/' ? explode('/', substr($path, 1)) : [$path];
        $value = $claims;
        foreach ($parts as $part) {
            $part = str_replace(['~1', '~0'], ['/', '~'], $part);
            if (is_object($value)) {
                $value = get_object_vars($value);
            }
            if (!is_array($value) || !array_key_exists($part, $value)) {
                $found = false;
                return null;
            }
            $value = $value[$part];
        }
        $found = true;
        return $value;
    }

    public static function rows($providerId)
    {
        global $DB;
        return iterator_to_array($DB->request(['FROM' => 'glpi_oidcmappings', 'WHERE' => ['oidcproviders_id' => (int) $providerId], 'ORDER' => 'id']));
    }

    public function prepareInputForAdd($input)
    {
        return $this->validateMapping($input);
    }

    public function prepareInputForUpdate($input)
    {
        return $this->validateMapping($input);
    }

    private function validateMapping(array $input)
    {
        foreach (['claim_path', 'target', 'claim_source', 'sync_mode', 'missing_policy', 'transform', 'lookup_mode'] as $field) {
            if (isset($input[$field]) && !is_string($input[$field])) {
                throw new InvalidArgumentException('OIDC mapping values must be strings.');
            }
        }
        $row = $input + $this->fields;
        OidcProvider::load($row['oidcproviders_id'] ?? 0, false);
        self::validatePath(stripslashes($row['claim_path'] ?? ''));
        $targets = self::targets();
        if (!isset($targets[$row['target'] ?? ''])) {
            throw new InvalidArgumentException('Invalid OIDC mapping target.');
        }
        $options = [
            'claim_source' => ['userinfo', 'id_token'],
            'sync_mode' => ['always', 'create', 'update'],
            'missing_policy' => ['preserve', 'clear', 'required'],
            'transform' => ['none', 'trim', 'lowercase', 'uppercase'],
            'lookup_mode' => ['id', 'name', 'create'],
        ];
        foreach ($options as $field => $choices) {
            if (!in_array($row[$field] ?? $choices[0], $choices, true)) {
                throw new InvalidArgumentException('Invalid OIDC mapping option: ' . $field);
            }
        }
        if (($row['target'] === 'email' || $row['target'] === 'name') && ($row['missing_policy'] ?? '') === 'clear') {
            throw new InvalidArgumentException('Email and username mappings cannot clear existing values.');
        }
        if (($row['lookup_mode'] ?? '') === 'create' && $row['target'] !== 'groups') {
            throw new InvalidArgumentException('Automatic creation is only supported for groups.');
        }
        return $input;
    }

    /** Resolve claims into validated values for user updates. */
    public static function values($providerId, array $claims, array $user, $created)
    {
        $result = [];
        $targets = self::targets();
        foreach (self::rows($providerId) as $row) {
            if (($row['sync_mode'] === 'create' && !$created) || ($row['sync_mode'] === 'update' && $created)) {
                continue;
            }
            if (!isset($targets[$row['target']])) {
                throw new RuntimeException('An OIDC mapping target is unavailable.');
            }
            $value = self::claim($claims[$row['claim_source']] ?? [], $row['claim_path'], $found);
            if (!$found || $value === null || $value === '' || $value === []) {
                if ($row['missing_policy'] === 'required') {
                    throw new RuntimeException('A required OIDC claim is missing.');
                }
                if ($row['missing_policy'] === 'preserve') {
                    continue;
                }
                $value = self::emptyValue($targets[$row['target']]['type']);
            }
            $target = $targets[$row['target']];
            $value = self::transform($value, $row['transform']);
            if (self::isLegacyPictureUrl($providerId, $target, $value)) {
                continue;
            }
            $value = self::validateValue($target, $value, $row, $user);
            $result[$row['target']] = $value;
        }
        return $result;
    }

    private static function emptyValue($type)
    {
        switch ($type) {
            case 'integer':
            case 'reference':
                return 0;
            case 'groups':
                return [];
            default:
                return '';
        }
    }

    private static function transform($value, $transform)
    {
        if ($transform === 'none') {
            return $value;
        }
        if (!is_string($value)) {
            throw new RuntimeException('OIDC text transformations require a string.');
        }
        switch ($transform) {
            case 'trim':
                return trim($value);
            case 'lowercase':
                return mb_strtolower($value);
            case 'uppercase':
                return mb_strtoupper($value);
            default:
                throw new RuntimeException('Invalid OIDC text transformation.');
        }
    }

    private static function isLegacyPictureUrl($providerId, array $target, $value)
    {
        if (isset($target['validate'])) {
            return false;
        }
        if ($target['type'] !== 'picture' || !is_string($value)) {
            return false;
        }
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return false;
        }
        // Remote URLs are not managed filenames; preserve the current picture for migrated mappings.
        $provider = OidcProvider::load($providerId, false);
        return (bool) $provider['is_legacy'];
    }

    private static function validateValue(array $target, $value, array $row, array $user)
    {
        if (isset($target['validate'])) {
            return $target['validate']($value, $row, $user);
        }
        switch ($target['type']) {
            case 'groups':
                if (!is_array($value)) {
                    throw new RuntimeException('The OIDC group claim must be an array.');
                }
                $groups = [];
                foreach ($value as $group) {
                    $groups[] = self::reference('Group', $group, $row['lookup_mode'], $user);
                }
                return array_values(array_unique($groups));
            case 'reference':
                return self::reference($target['itemtype'], $value, $row['lookup_mode'], $user);
            case 'integer':
                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    throw new RuntimeException('An OIDC mapping requires an integer.');
                }
                return (int) $value;
            default:
                return self::validateTextValue($target, $value, $row);
        }
    }

    private static function validateTextValue(array $target, $value, array $row)
    {
        if (!is_string($value) && !is_numeric($value)) {
            throw new RuntimeException('An OIDC mapping requires a scalar value.');
        }
        $value = (string) $value;
        if ($target['type'] === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('The OIDC email claim is invalid.');
        }
        if ($target['type'] === 'picture' && $value !== '') {
            self::validatePicture($value);
        }
        if ($row['target'] === 'language' && $value !== '') {
            if (!isset($GLOBALS['CFG_GLPI']['languages'][$value])) {
                throw new RuntimeException('The OIDC language claim is invalid.');
            }
        }
        return $value;
    }

    private static function validatePicture($value)
    {
        $directory = realpath(GLPI_PICTURE_DIR);
        $path = realpath(GLPI_PICTURE_DIR . '/' . $value);
        if (!$directory || !$path) {
            throw new RuntimeException('An OIDC picture must reference an existing managed image.');
        }
        if (strpos($path, $directory . DIRECTORY_SEPARATOR) !== 0) {
            throw new RuntimeException('An OIDC picture must stay inside the managed image directory.');
        }
        if (!Document::isImage($path)) {
            throw new RuntimeException('An OIDC picture must be an image.');
        }
    }

    private static function reference($class, $value, $mode, array $user)
    {
        global $DB;
        if (!is_string($value) && !is_int($value)) {
            throw new RuntimeException('Invalid OIDC reference value.');
        }
        if ($mode === 'id' && (string) $value === '0') {
            return 0;
        }
        $table = $class::getTable();
        $where = [$mode === 'id' ? 'id' : 'name' => $mode === 'id' ? (int) $value : Oidc::literal($value)];
        if ($mode === 'id' && (!ctype_digit((string) $value) || (int) $value < 1)) {
            throw new RuntimeException('An OIDC reference requires an existing numeric ID.');
        }
        if ($DB->fieldExists($table, 'entities_id')) {
            $entity = (int) ($user['entities_id'] ?? 0);
            $where['OR'] = ['entities_id' => $entity];
            if ($DB->fieldExists($table, 'is_recursive')) {
                $where['OR'][] = ['entities_id' => array_values(getAncestorsOf('glpi_entities', $entity)) ?: [-1], 'is_recursive' => 1];
            }
        }
        if ($DB->fieldExists($table, 'is_deleted')) {
            $where['is_deleted'] = 0;
        }
        $rows = $DB->request(['FROM' => $table, 'WHERE' => $where]);
        if (count($rows) === 1) {
            return (int) $rows->next()['id'];
        }
        if (count($rows) === 0 && $mode === 'create' && $class === 'Group' && trim($value) !== '') {
            $group = new Group();
            $id = $group->add(Toolbox::addslashes_deep(['name' => $value, 'entities_id' => (int) ($user['entities_id'] ?? 0)]));
            if ($id) {
                return (int) $id;
            }
        }
        throw new RuntimeException('An OIDC reference is missing or ambiguous in the user entity.');
    }

    public static function apply($providerId, User $user, array $values)
    {
        global $DB;
        $targets = self::targets();
        $input = ['id' => $user->getID()];
        foreach ($values as $key => $value) {
            if (isset($targets[$key]['write'])) {
                $targets[$key]['write']($user, $value, $providerId);
                continue;
            }
            switch ($key) {
                case 'groups':
                    self::syncGroups($providerId, $user->getID(), $value);
                    break;
                case 'email':
                    self::syncEmail($user->getID(), $value);
                    break;
                case 'picture':
                    if ($value === '') {
                        $input['_blank_picture'] = 1;
                    } else {
                        $input[$key] = $value;
                    }
                    break;
                case 'name':
                    self::checkUsernameAvailable($user->getID(), $value);
                    $input[$key] = $value;
                    break;
                default:
                    $input[$key] = $value;
            }
        }
        if (count($input) > 1 && !$user->update(Toolbox::addslashes_deep($input))) {
            throw new RuntimeException('Unable to synchronize the OIDC user.');
        }
        $user->getFromDB($user->getID());
    }

    private static function syncEmail($userId, $value)
    {
        global $DB;
        if (UserEmail::isEmailForUser($userId, $DB->escape($value))) {
            return;
        }
        $email = new UserEmail();
        $id = $email->add(Toolbox::addslashes_deep(['users_id' => $userId, 'email' => $value, 'is_dynamic' => 1]));
        if (!$id) {
            throw new RuntimeException('Unable to synchronize the OIDC email.');
        }
    }

    private static function checkUsernameAvailable($userId, $username)
    {
        global $DB;
        $existing = $DB->request(['FROM' => 'glpi_users', 'WHERE' => [
            'name' => Oidc::literal($username), 'id' => ['!=', $userId],
        ]]);
        if (count($existing)) {
            throw new RuntimeException('The mapped username belongs to another user.');
        }
    }

    private static function syncGroups($providerId, $userId, array $groups)
    {
        global $DB;
        foreach ($groups as $groupId) {
            if (!$groupId) {
                continue;
            }
            $membership = $DB->request(['FROM' => 'glpi_groups_users', 'WHERE' => ['users_id' => $userId, 'groups_id' => $groupId]])->next();
            if (!$membership) {
                $link = new Group_User();
                $id = $link->add(['users_id' => $userId, 'groups_id' => $groupId, 'is_dynamic' => 1]);
                if (!$id) {
                    throw new RuntimeException('Unable to synchronize OIDC groups.');
                }
                $DB->insert('glpi_oidcgroupusers', ['oidcproviders_id' => $providerId, 'groups_users_id' => $id]);
            } elseif (count($DB->request(['FROM' => 'glpi_oidcgroupusers', 'WHERE' => ['groups_users_id' => $membership['id']]]))) {
                $DB->updateOrInsert('glpi_oidcgroupusers', ['oidcproviders_id' => $providerId, 'groups_users_id' => $membership['id']], ['oidcproviders_id' => $providerId, 'groups_users_id' => $membership['id']]);
            }
        }
        $owned = $DB->request([
            'SELECT' => ['glpi_oidcgroupusers.id AS ownership_id', 'glpi_groups_users.id', 'glpi_groups_users.groups_id', 'glpi_groups_users.is_dynamic'],
            'FROM' => 'glpi_oidcgroupusers',
            'INNER JOIN' => ['glpi_groups_users' => ['FKEY' => ['glpi_oidcgroupusers' => 'groups_users_id', 'glpi_groups_users' => 'id']]],
            'WHERE' => ['glpi_oidcgroupusers.oidcproviders_id' => $providerId, 'glpi_groups_users.users_id' => $userId],
        ]);
        foreach ($owned as $row) {
            if (in_array((int) $row['groups_id'], $groups, true)) {
                continue;
            }
            $DB->delete('glpi_oidcgroupusers', ['id' => $row['ownership_id']]);
            if ($row['is_dynamic'] && !count($DB->request(['FROM' => 'glpi_oidcgroupusers', 'WHERE' => ['groups_users_id' => $row['id']]]))) {
                (new Group_User())->delete(['id' => $row['id']], true);
            }
        }
    }

    public static function releaseGroups($providerId)
    {
        global $DB;
        foreach ($DB->request(['FROM' => 'glpi_oidcgroupusers', 'WHERE' => ['oidcproviders_id' => $providerId]]) as $owner) {
            $DB->delete('glpi_oidcgroupusers', ['id' => $owner['id']]);
            $membership = new Group_User();
            if ($membership->getFromDB($owner['groups_users_id']) && $membership->fields['is_dynamic']
                && !count($DB->request(['FROM' => 'glpi_oidcgroupusers', 'WHERE' => ['groups_users_id' => $owner['groups_users_id']]]))) {
                $membership->delete(['id' => $owner['groups_users_id']], true);
            }
        }
    }

    public static function showConfiguration($providerId)
    {
        global $CFG_GLPI;
        Session::checkRight('config', UPDATE);
        $source = OidcProvider::load($providerId, false);
        if ($_POST) {
            try {
                $mapping = new self();
                $id = (int) ($_POST['mapping_id'] ?? 0);
                if ($id && (!$mapping->getFromDB($id) || (int) $mapping->fields['oidcproviders_id'] !== (int) $providerId)) {
                    throw new RuntimeException('Invalid OIDC mapping source.');
                }
                if (isset($_POST['delete'])) {
                    $mapping->delete(['id' => $id], true);
                } else {
                    $input = array_intersect_key($_POST, array_flip(['claim_source', 'claim_path', 'target', 'sync_mode', 'missing_policy', 'transform', 'lookup_mode']));
                    $input['oidcproviders_id'] = $providerId;
                    $saved = $id ? $mapping->update(['id' => $id] + $input) : $mapping->add($input);
                    if (!$saved) {
                        throw new RuntimeException('Unable to save OIDC mapping.');
                    }
                }
                Html::redirect('auth.oidc_profile.php?id=' . (int) $providerId);
            } catch (InvalidArgumentException | RuntimeException $e) {
                Session::addMessageAfterRedirect($e->getMessage(), false, ERROR);
                Html::redirect('auth.oidc_profile.php?id=' . (int) $providerId);
            }
        }
        echo '<h2>' . OidcProvider::escape($source['name']) . '</h2><p><a href="' . OidcProvider::escape($CFG_GLPI['root_doc'] . '/front/auth.oidc.php?id=' . $providerId) . '">' . __('Configuration') . '</a></p>';
        echo '<p>' . __('Claim paths accept JSON Pointer notation, for example /address/locality. Missing claims preserve existing values by default.') . '</p>';
        $targets = array_map(function ($target) {
            return $target['label'];
        }, self::targets());
        $columns = ['claim_source' => [__('Payload'), ['userinfo' => 'UserInfo', 'id_token' => 'ID token']],
            'claim_path' => [__('Claim path'), null], 'target' => [__('User field'), $targets],
            'sync_mode' => [__('Apply on'), ['always' => __('Every login'), 'create' => __('Creation'), 'update' => __('Update')]],
            'missing_policy' => [__('Missing value'), ['preserve' => __('Preserve'), 'clear' => __('Clear'), 'required' => __('Reject login')]],
            'transform' => [__('Transformation'), ['none' => __('None'), 'trim' => __('Trim'), 'lowercase' => __('Lowercase'), 'uppercase' => __('Uppercase')]],
            'lookup_mode' => [__('References'), ['id' => __('ID'), 'name' => __('Name'), 'create' => __('Create groups by name')]]];
        foreach (array_merge(self::rows($providerId), [['id' => 0, 'claim_path' => '']]) as $row) {
            echo '<form method="post"><input type="hidden" name="mapping_id" value="' . (int) $row['id'] . '"><div class="table-responsive"><table class="tab_cadre_fixe table"><tr>';
            foreach ($columns as $key => $column) {
                echo '<th><label for="mapping_' . (int) $row['id'] . '_' . $key . '">' . OidcProvider::escape($column[0]) . '</label></th>';
            }
            echo '<th>' . __('Actions') . '</th></tr><tr>';
            foreach ($columns as $key => $column) {
                echo '<td>';
                if ($column[1] !== null) {
                    OidcProvider::select($key, $column[1], $row[$key] ?? array_key_first($column[1]), 'mapping_' . (int) $row['id'] . '_' . $key);
                } else {
                    echo '<input class="form-control" id="mapping_' . (int) $row['id'] . '_' . $key . '" name="' . $key . '" value="' . OidcProvider::escape($row[$key] ?? '') . '">';
                }
                echo '</td>';
            }
            echo '<td><button class="btn btn-primary" name="save" value="1">' . ($row['id'] ? __('Save') : __('Add')) . '</button>';
            if ($row['id']) {
                echo ' <button class="btn btn-secondary" name="delete" value="1">' . __('Delete') . '</button>';
            }
            echo '</td></tr></table></div>';
            Html::closeForm();
        }
    }
}
