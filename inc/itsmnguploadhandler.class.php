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

use phpDocumentor\Reflection\PseudoTypes\True_;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/** GLPIUploadHandler class
 *
 * @since 9.2
**/
class ItsmngUploadHandler {

   static function get_upload_path($path) {
      $upload_path = "/_uploads/$path";
      if (!file_exists(GLPI_DOC_DIR . $upload_path)) {
         mkdir(GLPI_DOC_DIR . $upload_path, 0777, true);
      }
      return $upload_path;
   }

   static function generateBaseDocumentFromPost($POST) {
      $baseDoc = [
         'entities_id'           => $POST['entities_id'] ?? 0,
         'is_recursive'          => $POST['is_recursive'] ?? 0,
         'documentcategories_id' => $POST['documentcategories_id'] ?? 0,
         'name'                  => $POST['name'] ?? '',
         'comment'               => $POST['comment'] ?? '',
         'users_id'              => Session::getLoginUserID() ?? 0,
         'is_deleted'            => $POST['is_deleted'] ?? 0,
         'tickets_id'            => $POST['tickets_id'] ?? 0,
      ];
      return $baseDoc;
   }

   static function storeTmpFiles($files) {
      $path = GLPI_DOC_DIR . "/_tmp/";
      if (!file_exists($path)) {
         mkdir($path, 0777, true);
      }
      $tmpFiles = [];
      foreach ($files as $file) {
         $filename = uniqid() . '_' . $file['name'];
         $filepath = $path . $filename;
         if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception("Failed to move uploaded file.");
         }
         $tmpFiles[] = ['format' => $file['type'], 'name' => $file['name'], 'path' => $filepath];
      }
      return $tmpFiles;
   }

   static function uploadFiles($filepath, $format, $filename) {
      $upload_path = self::get_upload_path($format);
      // die(var_dump($upload_path));
      $uploadfile = $upload_path . '/' . $filename;
      if (file_exists($uploadfile)) {
         $filename = uniqid() . '.' . pathinfo($filename, PATHINFO_EXTENSION);
         $uploadfile = $upload_path . '/' . $filename;
      }
      if (!file_exists(GLPI_DOC_DIR . $upload_path)) {
         if (!mkdir(GLPI_DOC_DIR . $upload_path, 0777, true)) {
            throw new Exception("Failed to create upload directory.");
         }
      }
      if (!rename($filepath, GLPI_DOC_DIR . $uploadfile)) {
         throw new Exception("Failed to move uploaded file.");
      }
      return self::get_upload_path($format, false) . '/' . $filename;
   }

   static function addFileToDb($file) {
      $doc = new Document();
      $newDoc = ItsmngUploadHandler::generateBaseDocumentFromPost($_POST);
      $newDoc['filename'] = $file['name'];
      $newDoc['filepath'] = ItsmngUploadHandler::uploadFiles(
         $file['path'],
         $file['format'],
         $file['name']
      );
      $newDoc['mime'] = $file['format'];
      $doc->add($newDoc);
      return $doc;
   }

   static function linkDocToItem($id, $entity, $isRecursive, $itemType, $itemId, $userId) {
      $docItem = new Document_Item();
      $docItem->add([
         'documents_id' => $id,
         'entities_id'  => $entity,
         'is_recursive' => $isRecursive,
         'itemtype'     => $itemType,
         'items_id'     => $itemId,
         'users_id'     => $userId,
      ]);
   }

   static function removeTmpFiles($files) {
      foreach ($files as $file) {
         unlink($file['path']);
      }
   }
}
