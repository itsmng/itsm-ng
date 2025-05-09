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
        $criteria = ["SELECT * FROM glpi_specialstatuses"];
        $requests = self::getAdapter()->request($criteria);
        $checksum = 0;
        echo Html::script("js/specialstatus.js");

        if (isset($_POST["update"])) {
            $before = Ticket::getAllStatusArray(false, true);
            while ($update = $requests->fetchAssociative()) {
                $checksum += $_POST["is_active_" . $update["id"]];
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

        echo "<form aria-label='Informations' method='post' action='./specialstatus.php' method='post'>";
        echo "<table style='width:40%' class='tab_cadre' cellpadding='5' aria-label='Special Status'>";
        echo "<tr><th colspan='5'>" . __("Special Status") . "</th></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td><b>" . __("Name") . "</b></td>";
        echo "<td><b>" . __("Weight") . "</b></td>";
        echo "<td><b>" . __("Active") . "</b></td>";
        echo "<td><b>" . __("Color") . "</b></td>";
        echo "<td><b>" . __("Delete") . "</b></td>";
        echo "</tr>";

        $requests = $this::getAdapter()->request($criteria);
        while ($data = $requests->fetchAssociative()) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . $data["name"] . "</td>";
            echo "<td><input type='number' id='weight_" . $data["id"] . "' name='weight_" . $data["id"] . "' value='" . $data["weight"] . "' min='1'></td>";
            echo "<td>";
            Dropdown::showYesNo("is_active_" . $data["id"], $data["is_active"], -1, ['use_checkbox' => true]);
            echo "</td>";
            if ($data["color"] == "Default") {
                echo "<td>";
                echo "<td>";
            } else {
                echo "<td><input type='color' id='color_" . $data["id"] . "' name='color_" . $data["id"] . "' value='" . $data["color"] . "'>";
                echo "<td>";
                echo "<a class='planning_link planning_add_filter' href='javascript:specialstatus.showStatusModal(" . $data["id"] . ");'>";
                echo "<i style='color:#772317' class='fa fa-trash-alt pointer fa-2x' title='" . __("Delete") . "'></i>";
                echo "</a>";
            }
            echo "</td></tr>";
        }
        echo "<tr class='tab_bg_1'><td class='center' colspan='5'>";
        echo "<input type='submit' name='update' value='" . _sx('button', 'Save') . "' class='btn btn-secondary'>";
        echo "</td></tr>";
        echo "</table>";
        Html::closeForm();
    }

    public static function deleteStatus($id)
    {
        $tab = Ticket::getAllStatusArray(false, true);
        global $CFG_GLPI;

        $criteria = ["SELECT * FROM glpi_tickets"];
        $requests = self::getAdapter()->request($criteria);

        $requests = self::getAdapter()->request($criteria);
        while ($data = $requests->fetchAssociative()) {
            if (isset($tab["id"][$data["status"]]) && $id == $tab["id"][$data["status"]]) {
                $result[] = $tab["id"][$data["status"]];
            }
        }
        echo "<form aria-label='Status' method='post' action='./specialstatus.php' method='post'>";
        if (isset($result)) {
            $count = count($result);

            if ($count <= 1) {
                echo "<b>" . "$count " . __('Ticket is still using this status, are you sure to remove the status?') . "</b>";
            } else {
                echo "<b>" . "$count " . __('Tickets are still using this status, are you sure to remove the status?') . "</b>";
            }
        } else {
            echo "<b>" . __('Are you sure to remove the status?') . "</b>";
        }
        echo "<br>";
        $_SESSION["id"] = $id;
        echo "<table style='width:40%; margin-top: 2.5em; text-align:center' cellpadding='2'><tr class='tab_bg_1' aria-label='Confirm / Cancel'>";
        echo "<td><input type='submit' name='force' value='" . _sx('button', 'Confirm') . "' class='submit'></td>";
        echo "<td><input type='submit' name='cancel' value='" . _sx('button', 'Cancel') . "' class='submit'></td>";
        echo "</tr></table>";
        Html::closeForm();
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
        echo "<form aria-label='Status' method='post' action='./specialstatus.form.php' method='post'>";
        echo "<table style='width:40%' class='tab_cadre' cellpadding='5' aria-label='New / Special Status'>";
        echo "<tr><th colspan='4'>" . __("New Status - Special status") . "</th></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td><b>" . __("Name") . "</b></td>";
        echo "<td><b>" . __("Weight") . "</b></td>";
        echo "<td><b>" . __("Active") . "</b></td>";
        echo "<td><b>" . __("Color") . "</b></td></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td><input type='text' id='name' name='name' placeholder='Name'></td>";
        echo "<td><input type='number' id='weight' name='weight' value='1' min='1'></td>";
        echo "<td>";
        Dropdown::showYesNo("is_active", 1, -1, ['use_checkbox' => true]);
        echo "</td>";
        echo "<td><input type='color' id='color' name='color'value='#131425'>";
        echo "</td></tr>";
        echo "<tr class='tab_bg_1'><td class='center' colspan='4'>";
        echo "<input type='submit' name='update' class='submit' value=\"" . _sx('button', 'Add') . "\">";
        echo "</td></tr>";
        echo "</table>";
        Html::closeForm();
    }

    public function keepStatusSet($before, $after)
    {
        global $DB;

        $criteria = ["SELECT * FROM glpi_tickets"];
        $requests = $this::getAdapter()->request($criteria);

        $requests = $this::getAdapter()->request($criteria);
        while ($data = $requests->fetchAssociative()) {
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
