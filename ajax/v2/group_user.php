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

include('../../inc/includes.php');

header('Content-Type: application/json; charset=UTF-8');
Html::header_nocache();

Session::checkLoginUser();

$groups_id = isset($_GET['groups_id']) ? (int)$_GET['groups_id'] : 0;
if ($groups_id <= 0) {
    echo json_encode([
       'total' => 0,
       'rows'  => []
    ]);
    exit;
}

$group = new Group();
if (
    !$group->getFromDB($groups_id)
    || !Session::haveRight(Group::$rightname, READ)
    || !User::canView()
    || !$group->can($groups_id, READ)
) {
    echo json_encode([
       'total' => 0,
       'rows'  => []
    ]);
    exit;
}

$criterion = $_GET['criterion'] ?? '';
if (!in_array($criterion, ['', 'is_manager', 'is_userdelegate'], true)) {
    $criterion = '';
}

$tree   = isset($_GET['tree']) ? (int)$_GET['tree'] : 0;
$offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
$limit  = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : (int)$_SESSION['glpilist_limit'];
$sort   = $_GET['sort'] ?? 'group';
$order  = $_GET['order'] ?? 'asc';

echo json_encode(
    Group_User::getPaginatedMembersForGroup($group, $criterion, $tree, $offset, $limit, $sort, $order)
);
