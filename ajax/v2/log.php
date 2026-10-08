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

header('Content-Type: application/json; charset=UTF-8');
Html::header_nocache();

Session::checkLoginUser();

if (!Session::haveRight(Log::$rightname, READ)) {
    echo json_encode(['total' => 0, 'rows' => []]);
    exit;
}

$itemtype = $_GET['itemtype'] ?? '';
$items_id = isset($_GET['items_id']) ? (int) $_GET['items_id'] : -1;

if (!Toolbox::isCommonDBTM($itemtype)) {
    echo json_encode(['total' => 0, 'rows' => []]);
    exit;
}

$item = getItemForItemtype($itemtype);
if (!$item || $item->isNewID($items_id) || !$item->getFromDB($items_id) || !$item->can($items_id, READ)) {
    echo json_encode(['total' => 0, 'rows' => []]);
    exit;
}

$limit = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : $_SESSION['glpilist_limit'];
$offset = isset($_GET['offset']) ? max(0, (int) $_GET['offset']) : 0;

$sort = $_GET['sort'] ?? '';
$order = $_GET['order'] ?? '';

$filters = [];
if (isset($_GET['filters']) && $_GET['filters'] !== '') {
    $decoded = json_decode((string) $_GET['filters'], true);
    if (is_array($decoded)) {
        $filters = $decoded;
    }
}

$sql_filters = Log::convertFiltersValuesToSqlCriteria($filters);
$total = countElementsInTable('glpi_logs', ['items_id' => $items_id, 'itemtype' => $itemtype] + $sql_filters);

$rows = [];
if ($total > 0) {
    $options = [
        'sort' => $sort,
        'order' => $order,
    ];
    foreach (Log::getHistoryData($item, $offset, $limit, $sql_filters, $options) as $data) {
        if (!$data['display_history']) {
            continue;
        }
        $rows[] = [
            'id' => $data['id'],
            'date_mod' => $data['date_mod'],
            'user_name' => $data['user_name'],
            'field' => $data['field'],
            'change' => Html::entities_deep($data['change']),
        ];
    }
}

echo json_encode([
    'total' => $total,
    'rows' => $rows,
]);
