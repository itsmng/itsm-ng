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
 * @since 0.85
 */

$AJAX_INCLUDE = 1;
include('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (isset($_POST["validatortype"])) {
    switch ($_POST["validatortype"]) {
        case 'user':
            if (isset($_POST['validate_users_id']['groups_id'])) {
                $_POST['validate_users_id'] = [];
            }
            $value = (isset($_POST['validate_users_id'][0]) ? $_POST['validate_users_id'][0] : 0);
            echo json_encode(getOptionsForUsers($_POST['right'], $_POST['entity'] ? ['entities_id' => $_POST['entity']] : []));
            break;

        case 'group':
            $name = !empty($_POST['name']) ? $_POST['name'] . '[groups_id]' : 'groups_id';
            $value = (isset($_POST['validate_users_id']['groups_id']) ? $_POST['validate_users_id']['groups_id'] : $_POST['groups_id']);

            echo json_encode(getItemByEntity(Group::class, $_POST['entity'] ?? Session::getActiveEntity()));
            break;

        case 'list_users':
            if (isset($_POST['validate_users_id']['groups_id'])) {
                $_POST['validate_users_id'] = [];
            }
            $opt             = ['groups_id' => $_POST["groups_id"],
                                     'right'     => $_POST['right'],
                                     'entity'    => $_POST["entity"]];
            $data_users      = TicketValidation::getGroupUserHaveRights($opt);
            $users           = [];
            $param['values'] = [];
            $values          = [];
            if (isset($_POST['validate_users_id']) && is_array($_POST['validate_users_id'])) {
                $values = $_POST['validate_users_id'];
            }
            foreach ($data_users as $data) {
                $users[$data['id']] = formatUserName(
                    $data['id'],
                    $data['name'],
                    $data['realname'],
                    $data['firstname']
                );
                if (in_array($data['id'], $values)) {
                    $param['values'][] = $data['id'];
                }
            }

            // Display all users
            if (
                isset($_POST['all_users'])
                && $_POST['all_users']
            ) {
                $param['values'] =  array_keys($users);
            }
            $param['multiple'] = true;
            $param['display'] = true;
            $param['size']    = count($users);

            $users = Toolbox::stripslashes_deep($users);
            $rand  = Dropdown::showFromArray(
                !empty($_POST['name']) ? $_POST['name'] : 'validate_users_id',
                $users,
                $param
            );

            // Display all/none buttons to select all or no users in group
            if (!empty($_POST['groups_id'])) {
                echo "<br><br><a id='all_users' class='vsubmit'>" . __('All') . "</a>";
                $param_button = [
                   'validatortype'     => 'list_users',
                   'name'              => !empty($_POST['name']) ? $_POST['name'] : '',
                   'validate_users_id' => '',
                   'all_users'         => 1,
                   'groups_id'         => $_POST['groups_id'],
                   'entity'            => $_POST['entity'],
                   'right'             => $_POST['right'],
                ];
                Ajax::updateItemOnEvent(
                    'all_users',
                    'show_list_users',
                    $CFG_GLPI["root_doc"] . "/ajax/dropdownValidator.php",
                    $param_button,
                    ['click']
                );

                echo "&nbsp;<a id='no_users' class='vsubmit'>" . __('None') . "</a>";
                $param_button['all_users'] = 0;
                Ajax::updateItemOnEvent(
                    'no_users',
                    'show_list_users',
                    $CFG_GLPI["root_doc"] . "/ajax/dropdownValidator.php",
                    $param_button,
                    ['click']
                );
            }
            break;
    }
}
