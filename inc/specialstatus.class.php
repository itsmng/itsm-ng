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

use Glpi\Event;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class SpecialStatus extends CommonTreeDropdown
{
    public $can_be_translated = true;
    public static $rightname = 'status_ticket';

    public static function getTypeName($nb = 0)
    {
        return __('Special Status');
    }


    public static function getFieldLabel()
    {
        return __('Status');
    }

    public static function oldStatusOrder()
    {
        $tab = Ticket::getAllStatusArray(false, true);
        $done = 0;

        for ($i = 0; $done < count($tab['name']); $i++) {
            if (isset($tab['name'][$i])) {
                switch ($tab['name'][$i]) {
                    case "New":
                        $_SESSION['INCOMING'] = $i;
                        break;
                    case "Processing (assigned)":
                        $_SESSION['ASSIGNED'] = $i;
                        break;
                    case "Processing (planned)":
                        $_SESSION['PLANNED'] = $i;
                        break;
                    case "Pending":
                        $_SESSION['WAITING'] = $i;
                        break;
                    case "Solved":
                        $_SESSION['SOLVED'] = $i;
                        break;
                    case "Closed":
                        $_SESSION['CLOSED'] = $i;
                        break;
                }
                $done++;
            }
        }
    }

    public function statusForm()
    {
        global $DB, $CFG_GLPI;
        $criteria = "SELECT * FROM glpi_specialstatuses";
        $iterators = $DB->request($criteria);
        $checksum = 0;
        echo Html::script("js/specialstatus.js");

        if (isset($_POST["update"])) {
            $before = Ticket::getAllStatusArray(false, true);
            while ($update = $iterators->next()) {
                $checksum = $checksum + $_POST["is_active_" . $update["id"]];
                $DB->update(
                    "glpi_specialstatuses",
                    ['weight' => $_POST["weight_" . $update["id"]]],
                    ['id' => $update["id"]]
                );
                $DB->update(
                    "glpi_specialstatuses",
                    ['is_active' => $_POST["is_active_" . $update["id"]]],
                    ['id' => $update["id"]]
                );
                if (isset($_POST["color_" . $update["id"]])) {
                    $DB->update(
                        "glpi_specialstatuses",
                        ['color' => $_POST["color_" . $update["id"]]],
                        ['id' => $update["id"]]
                    );
                }
                Session::addMessageAfterRedirect(
                    sprintf(__("Status has been updated!")),
                    true,
                    INFO
                );
            }
            if ($checksum == 0) {
                $DB->update(
                    "glpi_specialstatuses",
                    ['is_active' => 1],
                    ['id' => 1]
                );
            }
            $after = Ticket::getAllStatusArray(false, true);
            self::keepStatusSet($before, $after);
        }
        if (isset($_POST["delete"])) {
            $before = Ticket::getAllStatusArray(false, true);
            self::deleteStatus($_POST["delete"]);
            $after = Ticket::getAllStatusArray(false, true);
            self::keepStatusSet($before, $after);
        }
        if (isset($_POST["force"])) {
            $DB->delete(
                "glpi_specialstatuses",
                ['id' => $_SESSION['id']]
            );
            unset($_SESSION['id']);
            Session::addMessageAfterRedirect(
                sprintf(__("Status has been removed!")),
                true,
                INFO
            );
        }

        $content = [];
        $iterators = $DB->request($criteria);
        while ($data = $iterators->next()) {
            $id = (int) $data['id'];
            $inputs = [
                __('Weight') => ['type' => 'number', 'id' => 'weight_' . $id, 'name' => 'weight_' . $id, 'value' => $data['weight'], 'min' => 1],
                __('Active') => ['type' => 'checkbox', 'name' => 'is_active_' . $id, 'value' => $data['is_active']],
            ];
            if ($data['color'] != 'Default') {
                $inputs[__('Color')] = ['type' => 'color', 'id' => 'color_' . $id, 'name' => 'color_' . $id, 'value' => $data['color']];
                $inputs[__('Delete')] = ['content' => '<button type="button" class="btn btn-outline-danger" onclick="specialstatus.showStatusModal(' . $id . ')"><i class="fas fa-trash-alt me-2" aria-hidden="true"></i>' . __('Delete') . '</button>'];
            }
            // Keep the complete ordering configuration in one submission.
            $content[] = ['visible' => false, 'inputs' => [
                ['content' => '<h3 class="h5">' . htmlspecialchars($data['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) . '</h3>', 'col_lg' => 12, 'col_md' => 12],
            ] + $inputs];
        }
        echo '<h2 class="form-section-header">' . __('Special Status') . '</h2>';
        renderTwigForm([
            'action' => './specialstatus.php',
            'buttons' => [['type' => 'submit', 'name' => 'update', 'value' => _x('button', 'Save'), 'class' => 'btn btn-primary']],
            'content' => $content,
        ]);
    }

    public static function deleteStatus($id)
    {
        $tab = Ticket::getAllStatusArray(false, true);
        global $DB, $CFG_GLPI;

        $criteria = "SELECT * FROM glpi_tickets";
        $iterators = $DB->request($criteria);

        $iterators = $DB->request($criteria);
        while ($data = $iterators->next()) {
            if (isset($tab["id"][$data["status"]]) && $id == $tab["id"][$data["status"]]) {
                $result[] = $tab["id"][$data["status"]];
            }
        }
        $count = count($result ?? []);
        if ($count == 1) {
            $message = $count . ' ' . __('Ticket is still using this status, are you sure to remove the status?');
        } elseif ($count > 1) {
            $message = $count . ' ' . __('Tickets are still using this status, are you sure to remove the status?');
        } else {
            $message = __('Are you sure to remove the status?');
        }
        $_SESSION['id'] = $id;
        renderTwigForm([
            'action' => './specialstatus.php',
            'buttons' => [
                ['type' => 'submit', 'name' => 'force', 'value' => _x('button', 'Confirm'), 'class' => 'btn btn-danger'],
                ['type' => 'submit', 'name' => 'cancel', 'value' => _x('button', 'Cancel'), 'class' => 'btn btn-secondary'],
            ],
            'content' => [__('Delete') => ['visible' => true, 'inputs' => [
                ['content' => '<p>' . $message . '</p>', 'col_lg' => 12, 'col_md' => 12],
            ]]],
        ]);
    }

    public function addStatus()
    {
        global $DB;

        if (isset($_POST["update"])) {
            $before = Ticket::getAllStatusArray(false, true);
            $status_db = [
               'name'   => $_POST["name"],
               'weight'   => $_POST["weight"],
               'is_active'  => $_POST["is_active"],
               'color'  => $_POST["color"]
            ];
            $DB->updateOrInsert("glpi_specialstatuses", $status_db, ['id'   => 0]);
            Session::addMessageAfterRedirect(
                sprintf(__("Status has been added!")),
                true,
                INFO
            );
            $after = Ticket::getAllStatusArray(false, true);
            self::keepStatusSet($before, $after);
        }
        renderTwigForm([
            'action' => './specialstatus.form.php',
            'buttons' => [['type' => 'submit', 'name' => 'update', 'value' => _x('button', 'Add'), 'class' => 'btn btn-primary', 'icon' => 'fas fa-plus']],
            'content' => [__('New Status - Special status') => ['visible' => true, 'inputs' => [
                __('Name') => ['type' => 'text', 'id' => 'name', 'name' => 'name'],
                __('Weight') => ['type' => 'number', 'id' => 'weight', 'name' => 'weight', 'value' => 1, 'min' => 1],
                __('Active') => ['type' => 'checkbox', 'name' => 'is_active', 'value' => 1],
                __('Color') => ['type' => 'color', 'id' => 'color', 'name' => 'color', 'value' => '#131425'],
            ]]],
        ]);
    }

    public function keepStatusSet($before, $after)
    {
        global $DB;

        $criteria = "SELECT * FROM glpi_tickets";
        $iterators = $DB->request($criteria);

        $iterators = $DB->request($criteria);
        while ($data = $iterators->next()) {
            for ($i = 0; $i < count($after["name"]) + max($after["weight"]); $i++) {
                if (!isset($before["name"][$data["status"]])) {
                    continue;
                }
                if (!isset($after["name"][$i])) {
                    continue;
                }
                if ($before["name"][$data["status"]] == $after["name"][$i]) {
                    $DB->update(
                        "glpi_tickets",
                        ['status' => $i],
                        ['id' => $data["id"]]
                    );
                    break;
                }
            }
        }
    }
}
