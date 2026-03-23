<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2025 ITSM-NG and contributors.
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

if (!defined('GLPI_ROOT')) {
    include('../../inc/includes.php');
}

use function __;

header('Content-Type: application/json; charset=UTF-8');
Html::header_nocache();

Session::checkCentralAccess();

$type = $_REQUEST['type'] ?? '';
$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

if (!in_array($type, ['user', 'supplier'], true)) {
    echo json_encode([
        'success' => false,
        'message' => __('Invalid request'),
    ]);
    exit;
}

if ($id <= 0) {
    echo json_encode([
        'success' => true,
        'email'   => '',
    ]);
    exit;
}

$email = '';

switch ($type) {
    case 'user':
        $user = new User();
        if (!$user->getFromDB($id)) {
            echo json_encode([
                'success' => false,
                'message' => __('Item not found'),
            ]);
            exit;
        }

        $email = (string)$user->getDefaultEmail();
        break;

    case 'supplier':
        $supplier = new Supplier();
        if (!$supplier->getFromDB($id)) {
            echo json_encode([
                'success' => false,
                'message' => __('Item not found'),
            ]);
            exit;
        }

        $email = (string)$supplier->fields['email'];
        break;
}

echo json_encode([
    'success' => true,
    'email'   => $email,
]);
