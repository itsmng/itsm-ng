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

class SpecialStatus extends CommonTreeDropdown {
   public $can_be_translated = true;
   static $rightname = 'state';

   static function getTypeName($nb = 0) {
      return _n('Special Status', 'Special Status', $nb);
   }


   static function getFieldLabel() {
      return __('Status');
   }

   public function oldStatusOrder()
   {
      $tab = Ticket::getAllStatusArray(false, true);
      for ($i=0; $i <= count($tab['name']); $i++) { 
         switch ($tab['name'][$i]) {
            case "New" :
               $_SESSION['INCOMING'] = $i;
               break;
            case "Processing (assigned)" :
               $_SESSION['ASSIGNED'] = $i;
               break;
            case "Processing (planned)" :
               $_SESSION['PLANNED'] = $i;
               break;
            case "Pending" :
               $_SESSION['WAITING'] = $i;
               break;
            case "Solved" :
               $_SESSION['SOLVED'] = $i;
               break;
            case "Closed" :
               Ticket::CLOSED = $i;
               break;
         }
      }
      var_dump(Ticket::CLOSED);
   }

   function statusForm() {
      global $DB;
      $criteria = "SELECT * FROM glpi_ticket_status";
      $iterators = $DB->request($criteria);
      if (isset($_POST["update"])) {
         while ($update = $iterators->next()) {
            $DB->update(
               "glpi_ticket_status",
               ['weight' => $_POST["weight_".$update["id"]]],
               ['id' => $update["id"]]
            );
            $DB->update(
               "glpi_ticket_status",
               ['is_active' => $_POST["is_active_" .$update["id"]]],
               ['id' => $update["id"]]
            );
            $DB->update(
               "glpi_ticket_status",
               ['color' => $_POST["color_" .$update["id"]]],
               ['id' => $update["id"]]
            );
         }
      }
      echo "<form method='post' action='./specialstatus.php' method='post'>";
      echo "<table style='width:40%' class='tab_cadre' cellpadding='5'>";
      echo "<tr><th colspan='4'>".__("Special status")."</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td><b>".__("Name")."</b></td>";
      echo "<td><b>".__("Weight")."</b></td>";
      echo "<td><b>".__("Is active")."</b></td>";
      echo "<td><b>".__("Color")."</b></td></tr>";

      $iterators = $DB->request($criteria);
      while ($data = $iterators->next()) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . $data["name"]. "</td>";
         echo "<td><input type='number' id='weight_". $data["id"] ."' name='weight_". $data["id"] ."' value='". $data["weight"] ."' min='1'></td>";
         echo "<td>";
         Dropdown::showYesNo("is_active_" . $data["id"], $data["is_active"],-1,['use_checkbox' => true]);
         echo "</td>";
         if ($data["color"] == "Default") {
            echo "<td>".__("Default");
         } else {
            echo "<td><input type='color' id='color_". $data["id"] ."' name='color_". $data["id"] ."' value='". $data["color"] ."'>";
         }
         echo "</td></tr>";
      }
      echo "<tr class='tab_bg_1'><td class='center' colspan='4'>";
      echo "<input type='submit' name='update' class='submit'>";
      echo "</td></tr>";
      echo "</table>";
      Html::closeForm();
      
  }

  public function addStatus()
  {
   global $DB;

   if (isset($_POST["update"])) {
      $status_db = [
         'name'   => $_POST["name"],
         'weight'   => $_POST["weight"],
         'is_active'  => $_POST["is_active"],
         'color'  => $_POST["color"]
      ];
     $DB->updateOrInsert("glpi_ticket_status", $status_db, ['id'   => 0]);
   }
   echo "<form method='post' action='./specialstatus.form.php' method='post'>";
   echo "<table style='width:40%' class='tab_cadre' cellpadding='5'>";
   echo "<tr><th colspan='4'>".__("New Status - Special status")."</th></tr>";
   echo "<tr class='tab_bg_1'>";
   echo "<td><b>".__("Name")."</b></td>";
   echo "<td><b>".__("Weight")."</b></td>";
   echo "<td><b>".__("Is active")."</b></td>";
   echo "<td><b>".__("Color")."</b></td></tr>";
   echo "<tr class='tab_bg_1'>";
   echo "<td><input type='text' id='name' name='name' placeholder='Name'></td>";
   echo "<td><input type='number' id='weight' name='weight' value='1' min='1'></td>";
   echo "<td>";
   Dropdown::showYesNo("is_active", 1,-1,['use_checkbox' => true]);
   echo "</td>";
   echo "<td><input type='color' id='color' name='color'value='#131425'>";
   echo "</td></tr>";
   echo "<tr class='tab_bg_1'><td class='center' colspan='4'>";
   echo "<input type='submit' name='update' class='submit' value=\""._sx('button', 'Add')."\">";
   echo "</td></tr>";
   echo "</table>";
   Html::closeForm();
  }
}
