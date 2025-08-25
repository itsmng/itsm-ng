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
class ItsmngUploadHandler
{
    public const UPLOAD = GLPI_UPLOAD_DIR;
    public const TMP = GLPI_TMP_DIR;
    public const PICTURE = GLPI_PICTURE_DIR;
    public const PLUGIN = GLPI_PLUGIN_DOC_DIR;
    public const DUMP = GLPI_DUMP_DIR;

    public const TYPES = [self::UPLOAD, self::TMP, self::PICTURE, self::PLUGIN, self::DUMP];

    public static function getUploadPath($type, $filename, $withDir = true)
    {
        if (in_array($type, self::TYPES)) {
            $extension = strtoupper(pathinfo($filename, PATHINFO_EXTENSION)) . '/';
        } else {
            $extension = '';
        }
        if (!empty($type) && !str_ends_with($type, '/')) {
            $type .= '/';
        }
        if (!file_exists($type . $extension)) {
            if (!mkdir($type . $extension, 0777, true)) {
                return false;
            }
        }
        if ($withDir) {
            return $type . $extension;
        }
        $relativePath = str_replace(GLPI_DOC_DIR, '', $type) . $extension;
        return $relativePath;
    }

    public static function generateBaseDocumentFromPost($POST)
    {
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

    public static function uploadFile($filepath, $filename, $type = self::UPLOAD, $name = null)
    {
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
        return self::getUploadPath($type, $filename, false) . '/' . $filename;
    }

    private static function getValidExtPatterns()
    {
        $request = config::getAdapter()->request([
            'FROM'   => 'glpi_documenttypes',
            'WHERE'  => [
                'is_uploadable'   => 1
            ]
        ]);
        $valid_type_iterator = $request->fetchAllAssociative();
        $valid_ext_patterns = [];
        foreach ($valid_type_iterator as $valid_type) {
            $valid_ext = $valid_type['ext'];
            if (preg_match('/\/.+\//', $valid_ext)) {
                // Filename matches pattern
                // Remove surrounding '/' as it will be included in a larger pattern
                // and protect by surrounding parenthesis to prevent conflict with other patterns
                $valid_ext_patterns[] = '(' . substr($valid_ext, 1, -1) . ')';
            } else {
                // Filename ends with allowed ext
                $valid_ext_patterns[] = '\.' . preg_quote($valid_type['ext'], '/') . '$';
            }
        }
        return $valid_ext_patterns;
    }

    public static function storeTmpFiles($files)
    {
        $tmpFiles = [];

        foreach ($files as $file) {
            $valid_regex = '/(' . implode('|', self::getValidExtPatterns()) . ')$/';
            if (!preg_match($valid_regex, $file['name'])) {
                Session::addMessageAfterRedirect(
                    __('Invalid file extension', 'itsmng'),
                    false,
                    ERROR
                );
                continue;
            }

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


    public static function addFileToDb($file, $name = null)
    {
        $newDoc = ItsmngUploadHandler::generateBaseDocumentFromPost($_POST);
        if (!$name) {
            $name = $file['name'];
        }
        $doc = new Document();
        $newDoc['filepath'] = ItsmngUploadHandler::uploadFile(
            $file['path'],
            $file['name'],
            ItsmngUploadHandler::UPLOAD
        );
        $newDoc['filename'] = $file['name'];
        $newDoc['name'] = $name;
        $newDoc['mime'] = $file['format'];
        $doc->add($newDoc);
        return $doc;
    }

    public static function linkDocToItem($id, $entity, $isRecursive, $itemType, $itemId, $userId)
    {
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

    public static function removeFiles($files)
    {
        foreach ($files as $file) {
            unlink($file['path']);
        }
    }
}
