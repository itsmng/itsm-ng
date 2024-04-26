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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/** GLPIUploadHandler class
 *
 * @since 9.2
 **/
class ItsmngUploadHandler {

    const UPLOAD = '_upload';
    const TMP = '_tmp';
    const PICTURE = '_picture';
    const PLUGIN = '_plugin';
    const DUMP = '_dump';

    static function getUploadPath($type, $filename, $withDir = true) {
        $extension = strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
        switch ($type) {
            case self::UPLOAD:
                $upload_path = '/_uploads';
                break;
            case self::TMP:
                $upload_path = '/_tmp';
                break;
            case self::PICTURE:
                $upload_path = '/_pictures';
                break;
            case self::PLUGIN:
                $upload_path = '/_plugins';
                break;
            case self::DUMP:
                $upload_path = '/_dumps';
                break;
            default:
                return $type;
                break;
        }
        if (!file_exists(GLPI_DOC_DIR . $upload_path . '/' . $extension)) {
            if (!mkdir(GLPI_DOC_DIR . $upload_path . '/' . $extension, 0777, true)) {
                return false;
            }
        }
        if ($withDir) {
            return GLPI_DOC_DIR . $upload_path . '/' . $extension;
        }
        return $extension;

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

   static function uploadFile($filepath, $filename, $type = self::UPLOAD, $name = null) {
      $uploadPath = self::getUploadPath($type, $filename);
      $uniqid = $name ?? uniqid();
      $filename = $uniqid . '.' . pathinfo($filename, PATHINFO_EXTENSION);
      $uploadfile = $uploadPath . '/' . $filename;
      if (!rename($filepath, $uploadfile)) {
          return false;
      }
      if ($type == self::PICTURE) {
          $thumb = $uploadPath . '/' .
            $uniqid . '_min' . '.' . pathinfo($filename, PATHINFO_EXTENSION);
          Toolbox::resizePicture($uploadfile, $thumb);
      }
      return $uploadfile;
   }

   static function storeTmpFiles($files) {
      $tmpFiles = [];
      foreach ($files as $file) {
        $uploadPath = self::getUploadPath(self::TMP, $file['name']);
        $filename = uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $uploadfile = $uploadPath . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $uploadfile)) {
            return false;
        }
        $tmpFiles[] = [
            'path'   => $uploadfile,
            'name'   => $file['name'],
            'format' => $file['type'],
        ];
      }
      return $tmpFiles;
   }


   static function addFileToDb($file) {
       $doc = new Document();
       $newDoc = ItsmngUploadHandler::generateBaseDocumentFromPost($_POST);
       $newDoc['filename'] = $file['name'];
       $newDoc['filepath'] = ItsmngUploadHandler::uploadFile(
           $file['path'],
           $file['name'],
           ItsmngUploadHandler::UPLOAD
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

   static function removeFiles($files) {
       foreach ($files as $file) {
           unlink($file['path']);
      }
   }
}
