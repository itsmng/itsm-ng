<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
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

include('../inc/includes.php');

use function __;

header('Content-Type: application/json; charset=UTF-8');
Html::header_nocache();

$itemtype = $_POST['itemtype'] ?? 'Ticket';
$items_id = isset($_POST['items_id']) ? (int)$_POST['items_id'] : (int)($_POST['ticketId'] ?? 0);
$linkId = $_POST['linkId'] ?? '';
$objectTypeId = isset($_POST['objectTypeId']) ? (int)$_POST['objectTypeId'] : 0;
$objectId = isset($_POST['objectId']) ? (int)$_POST['objectId'] : 0;

if (
    !$items_id
    || !$linkId
    || !$objectTypeId
    || !in_array($linkId, ['user', 'group', 'supplier'], true)
    || !is_a($itemtype, CommonITILObject::class, true)
) {
    echo json_encode([
        'success' => false,
        'message' => __('Error while deleting actor')
    ]);
    return;
}

$item = new $itemtype();
if (!$item->getFromDB($items_id)) {
    echo json_encode([
        'success' => false,
        'message' => __('Error while deleting actor')
    ]);
    return;
}

$canDelete = $objectTypeId === CommonITILActor::ASSIGN
    ? $item->canAssign()
    : $item->canAdminActors();
if (!$canDelete) {
    echo json_encode([
        'success' => false,
        'message' => __('Error while deleting actor')
    ]);
    return;
}

$criteria = [
    $item->getForeignKeyField() => $item->getID(),
    'type'                      => $objectTypeId,
];

switch ($linkId) {
    case 'user':
        $linkClass = $item->userlinkclass;
        $criteria['users_id'] = $objectId;
        break;

    case 'group':
        $linkClass = $item->grouplinkclass;
        $criteria['groups_id'] = $objectId;
        break;

    case 'supplier':
        $linkClass = $item->supplierlinkclass;
        $criteria['suppliers_id'] = $objectId;
        break;
}

$link = new $linkClass();

if (!$link->getFromDBByCrit($criteria)) {
    echo json_encode([
        'success' => false,
        'message' => __('Error while deleting actor')
    ]);
    return;
}

$link->delete(['id' => $link->getID()]);

echo json_encode([
    'success' => true,
    'message' => __('Actor deleted successfully')
]);
return;
