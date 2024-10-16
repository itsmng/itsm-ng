<?php
/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org
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

include('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkCentralAccess();

// Make a select box
if (isset($_POST["type"])
    && isset($_POST["actorType"])
    && isset($_POST["itemtype"])) {
    $rand = mt_rand();
    $withemail = isset($_POST['allow_email']) && filter_var($_POST['allow_email'], FILTER_VALIDATE_BOOLEAN);

    $ticket = new Ticket();
    if (isset($_POST['ticketId']) && $_POST['ticketId'] > 0) {
        $ticket->getFromDB($_POST['ticketId']);
    }
    if ($item = getItemForItemtype($_POST["itemtype"])) {
        switch ($_POST["type"]) {
            case "user":
                $right = 'all';
                // Only steal or own ticket whit empty assign
                if ($_POST["actorType"] == 'assign') {
                    $right = "own_ticket";
                    if (!$item->canAssign()) {
                        $right = 'id';
                    }
                }

                $forbiddenActors = [];
                if (isset($_POST['actorTypeId'])) {
                    $forbiddenActors = $ticket->getUsers($_POST['actorTypeId']);
                    $forbiddenActors = array_filter($forbiddenActors, function ($actor) {
                        return isset($actor['users_id']);
                    });
                }
                $forbiddenActors = array_column($forbiddenActors, 'users_id');
                $options = getOptionsForUsers($right);
                $options = array_diff_key($options, array_combine($forbiddenActors, $forbiddenActors));
                echo json_encode($options);

                break;

            case "group":
                $cond = ['is_requester' => 1];
                if ($_POST["actorType"] == 'assign') {
                    $cond = ['is_assign' => 1];
                }
                if ($_POST["actorType"] == 'observer') {
                    $cond = ['is_watcher' => 1];
                }

                if (isset($_POST['entity_restrict'])) {
                    $cond['entities_id'] = $_POST['entity_restrict'];
                }

                $forbiddenActors = [];
                if (isset($_POST['actorTypeId'])) {
                    $forbiddenActors = $ticket->getUsers($_POST['actorTypeId']);
                    $forbiddenActors = array_filter($forbiddenActors, function ($actor) {
                        return isset($actor['users_id']);
                    });
                }
                $forbiddenActors = array_column($forbiddenActors, 'groups_id');
                $options = getItemByEntity(Group::class, Session::getActiveEntity());
                $options = array_diff_key($options, array_combine($forbiddenActors, $forbiddenActors));
                echo json_encode($options);
                break;

            case "supplier":
                $cond = [];
                if (isset($_POST['entity_restrict'])) {
                    $cond['entities_id'] = $_POST['entity_restrict'];
                }

                $forbiddenActors = $ticket->getSuppliers($_POST['actorTypeId']);
                $forbiddenActors = array_filter($forbiddenActors, function ($actor) {
                    return isset($actor['suppliers_id']);
                });
                $options = getItemByEntity('Supplier', $ticket->fields['entities_id'] ?? Session::getActiveEntity());
                $forbiddenActors = array_column($forbiddenActors, 'suppliers_id');
                $options = array_diff_key($options, array_combine($forbiddenActors, $forbiddenActors));
                echo json_encode($options);
                break;
            default:
                echo json_encode([Dropdown::EMPTY_VALUE]);
                break;

        }
    }
}
