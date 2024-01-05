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

include ('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkCentralAccess();

// Make a select box
if (isset($_POST["type"])
    && isset($_POST["actortype"])
    && isset($_POST["itemtype"])) {
   $rand = mt_rand();
   $withemail = isset($_POST['allow_email']) && filter_var($_POST['allow_email'], FILTER_VALIDATE_BOOLEAN);

   if ($item = getItemForItemtype($_POST["itemtype"])) {
      switch ($_POST["type"]) {
         case "user" :
            $right = 'all';
            // Only steal or own ticket whit empty assign
            if ($_POST["actortype"] == 'assign') {
               $right = "own_ticket";
               if (!$item->canAssign()) {
                  $right = 'id';
               }
            }

            echo json_encode(getOptionsForUsers($right, [], false));

            break;

         case "group" :
            $cond = ['is_requester' => 1];
            if ($_POST["actortype"] == 'assign') {
               $cond = ['is_assign' => 1];
            }
            if ($_POST["actortype"] == 'observer') {
               $cond = ['is_watcher' => 1];
            }

            if (isset($_POST['entity_restrict'])) {
              $cond['entities_id'] = $_POST['entity_restrict'];
            }

            echo json_encode(getOptionForItems('Group', $cond, false));
            break;

         case "supplier" :
            $cond = [];
            if (isset($_POST['entity_restrict'])) {
              $cond['entities_id'] = $_POST['entity_restrict'];
            }

            echo json_encode(getOptionForItems('Supplier', $cond, false));
            break;


      }
   }
}
