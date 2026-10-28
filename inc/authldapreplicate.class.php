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

/**
 *  Class used to manage LDAP replicate config
 */
class AuthLdapReplicate extends CommonDBTM
{
    public static $rightname = 'config';

    public static function canCreate()
    {
        return static::canUpdate();
    }

    public static function canPurge()
    {
        return static::canUpdate();
    }

    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }

    public function prepareInputForAdd($input)
    {

        if (isset($input["port"]) && (intval($input["port"]) == 0)) {
            $input["port"] = 389;
        }
        return $input;
    }

    public function prepareInputForUpdate($input)
    {

        return $this->prepareInputForAdd($input);
    }

    /**
     * Form to add a replicate to a ldap server
     *
     * @param string  $target    target page for add new replicate
     * @param integer $master_id master ldap server ID
     *
     * @return void
     */
    public static function addNewReplicateForm($target, $master_id)
    {
        renderTwigForm([
            'action' => $target,
            'attributes' => ['name' => 'add_replicate_form', 'id' => 'add_replicate_form'],
            'buttons' => [[
                'type' => 'submit', 'name' => 'add_replicate', 'value' => _x('button', 'Add'),
                'class' => 'btn btn-primary', 'icon' => 'fas fa-plus',
            ]],
            'content' => [__('Add a LDAP directory replica') => [
                'visible' => true,
                'inputs' => [
                    ['type' => 'hidden', 'name' => 'next', 'value' => 'extauth_ldap'],
                    ['type' => 'hidden', 'name' => 'authldaps_id', 'value' => $master_id],
                    __('Name') => ['type' => 'text', 'name' => 'name', 'value' => ''],
                    __('Server') => ['type' => 'text', 'name' => 'host', 'value' => ''],
                    _n('Port', 'Ports', 1) => ['type' => 'number', 'name' => 'port', 'value' => 389, 'min' => 1, 'max' => 65535],
                ],
            ]],
        ]);
    }
}
