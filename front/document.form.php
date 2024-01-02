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

use Glpi\Event;

include('../inc/includes.php');

Session::checkLoginUser();

if (!isset($_GET["id"])) {
   $_GET["id"] = -1;
}

$doc          = new Document();

if (isset($_POST["add"])) {
   $doc->check(-1, CREATE, $_POST);
   if (
      isset($_POST['files']) && isset($_POST['entities_id']) &&
      isset($_POST['is_recursive']) && isset($_POST['documentcategories_id'])
   ) {
      $files = json_decode(stripslashes($_POST['files']), true);
      foreach ($files as $file) {
         $doc = ItsmngUploadHandler::addFileToDb($file);
         if (isset($_POST['items_id']) && isset($_POST['itemtype'])) {
            ItsmngUploadHandler::linkDocToItem(
               $doc->getID(),
               $_POST['entities_id'],
               $_POST['is_recursive'],
               $_POST['itemtype'],
               $_POST['items_id'],
               Session::getLoginUserID());
         }
      }
   } else {
      Html::displayMessageAfterRedirect(__('Could not add document'), false, false, 'error');
   }

   Html::back();
} else if (isset($_POST["delete"])) {
   $doc->check($_POST["id"], DELETE);

   if ($doc->delete($_POST)) {
      Event::log(
         $_POST["id"],
         "documents",
         4,
         "document",
         //TRANS: %s is the user login
         sprintf(__('%s deletes an item'), $_SESSION["glpiname"])
      );
   }
   $doc->redirectToList();
} else if (isset($_POST["restore"])) {
   $doc->check($_POST["id"], DELETE);

   if ($doc->restore($_POST)) {
      Event::log(
         $_POST["id"],
         "documents",
         4,
         "document",
         //TRANS: %s is the user login
         sprintf(__('%s restores an item'), $_SESSION["glpiname"])
      );
   }
   $doc->redirectToList();
} else if (isset($_POST["purge"])) {
   $doc->check($_POST["id"], PURGE);

   if ($doc->delete($_POST, 1)) {
      Event::log(
         $_POST["id"],
         "documents",
         4,
         "document",
         //TRANS: %s is the user login
         sprintf(__('%s purges an item'), $_SESSION["glpiname"])
      );
   }
   $doc->redirectToList();
} else if (isset($_POST["update"])) {
   $doc->check($_POST["id"], UPDATE);

   if ((isset($_POST['files']) && $_POST['files'] != '[]')) {
      $file = json_decode(stripslashes($_POST['files']), true)[0];
      $_POST['filename'] = $file['name'];
      $_POST['filepath'] = ItsmngUploadHandler::uploadFiles(
         $file['path'],
         $file['format'],
         $file['name']
      );
   }
   if ($doc->update($_POST)) {
      Event::log(
         $_POST["id"],
         "documents",
         4,
         "document",
         //TRANS: %s is the user login
         sprintf(__('%s updates an item'), $_SESSION["glpiname"])
      );
   }
   Html::back();
} else {
   Html::header(Document::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "management", "document");
   $doc->display([
      'id'           => $_GET["id"],
      'formoptions'  => "data-track-changes=true"
   ]);
   Html::footer();
}
