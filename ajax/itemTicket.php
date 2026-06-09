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

Session::checkLoginUser();
$item_ticket = new Item_Ticket();

switch ($_POST['action']) {
   case 'add':
      if (isset($_POST['my_items']) && !empty($_POST['my_items'])) {
         list($_POST['itemtype'], $_POST['items_id']) = explode('_', $_POST['my_items']);
      }
      if (isset($_POST['items_id']) && isset($_POST['itemtype']) && !empty($_POST['items_id'])) {
         $_POST['params']['items_id'][$_POST['itemtype']][$_POST['items_id']] = $_POST['items_id'];
      }
      Item_Ticket::itemAddForm(new Ticket(), $_POST['params']);
      break;

   case 'delete':
      if (isset($_POST['items_id']) && isset($_POST['itemtype']) && !empty($_POST['items_id'])) {
         if ($_POST['params']['id'] > 0) {
            if ($item_ticket->canPurge()) {
               global $DB;

               $iterator = $DB->request([
                  'FROM'  => Item_Ticket::getTable(),
                  'WHERE' => [
                     'tickets_id' => $_POST['params']['id'],
                     'items_id'   => $_POST['items_id'],
                     'itemtype'   => $_POST['itemtype'],
                  ],
               ]);

               while ($data = $iterator->next()) {
                  if ($item_ticket->can($data['id'], DELETE)) {
                     $item_ticket->getFromDB($data['id']);
                     $item_ticket->delete(['id' => $data['id']]);
                  }
               }
            }
            unset($_POST['params']['items_id'][$_POST['itemtype']][array_search($_POST['items_id'], $_POST['params']['items_id'][$_POST['itemtype']])]);
         }
         Item_Ticket::itemAddForm(new Ticket(), $_POST['params']);
      }

      break;
}
