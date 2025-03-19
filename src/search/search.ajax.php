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

include_once '../../inc/includes.php';
Session::checkLoginUser();

if (!isset($_GET['itemtype'])) {
    return '{}';
}

$itemtype = $_GET['itemtype'];
$params = [];
if (isset($_GET['limit'])) {
    $params['list_limit'] = $_GET['limit'];
}
if (isset($_GET['offset'])) {
    $params['start'] = $_GET['offset'];
}
if (isset($_GET['deleted'])) {
    $params['is_deleted'] = $_GET['deleted'];
}
if (isset($_GET['sort'])) {
    $params['sort'] = $_GET['sort'];
}
if (isset($_GET['order'])) {
    $params['order'] = strtoupper($_GET['order']);
}
if (isset($_GET['search'])) {
    $params['criteria'] = [
        [
            'link' => 'AND',
            'field' => 1,
            'searchtype' => 'contains',
            'value' => $_GET['search']
        ]
    ];
} else {
    $params['criteria'] = [];
}
if (isset($_GET['criteria'])) {
    $criterias = json_decode(stripslashes($_GET['criteria']), true);
    if ($params['criteria'] == null) {
        $params['criteria'] = $criterias;
    } else {
        $params['criteria'] = array_merge($params['criteria'], $criterias);
    }
}
$formattedDatas = [];
$params['as_map'] = '0';

$datas = Search::getDatas($itemtype, $params);

foreach ($datas['data']['rows'] as $row) {
    $newData = [$row['id'] => $row['id']];
    if (
        !isset($row['entities_id'])
        || in_array($row['entities_id'], $_SESSION['glpiactiveentities'])
    ) {
        $newData['value'] = 'item[' . $itemtype . '][' . $row['id'] . ']';
    } else {
        $newData['value'] = null;
    }
    foreach ($datas['data']['cols'] as $col) {
        $newCol = $row[$itemtype . '_' . $col['id']];
        if (isset($newCol['displayname'])) {
            $field = $newCol['displayname'];
        } elseif (isset($newCol[0]['name'])) {
            $field = $newCol[0][0]['name'];
        } elseif (isset($newCol[0]['id'])) {
            $field = $newCol[0][0]['id'];
        } else {
            $field = '';
        }
        $newData[$col['id']] = $field;
    }
    $formattedDatas[] = $newData;
}
$return = [
    'total' => $datas['data']['totalcount'],
    'rows' => $formattedDatas
];

array_walk_recursive($return, function (&$value) {
    if (is_string($value)) {
        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
});

$json = json_encode($return, JSON_UNESCAPED_UNICODE);
if ($json === false) {
    echo "JSON encode error: " . json_last_error_msg();
} else {
    echo $json;
}

