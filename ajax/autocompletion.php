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

// Include plugin if it is a plugin table
if (!strstr($_GET['itemtype'], "Plugin")) {
    $AJAX_INCLUDE = 1;
}
include('../inc/includes.php');
header("Content-Type: application/json; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

// Security
$field = $_GET['field'] ?? null;
$itemtype = $_GET['itemtype'] ?? null;
$term = $_GET['term'] ?? '';

$item = getItemForItemtype($itemtype);

// Sécurity check
if (!$item || !isset($item->fields[$field]) || !$item->canView()) {
    exit();
}

$field_so = $item->getSearchOptionByField('field', $field, $item->getTable());
$can_autocomplete = array_key_exists('autocomplete', $field_so) && $field_so['autocomplete'];
if (!$can_autocomplete) {
    exit();
}

$where = [];
$params = [];
if (isset($_GET['entity_restrict']) && $_GET['entity_restrict'] >= 0 && $item->isEntityAssign()) {
    $where[] = 'c.entity = :entity';
    $params['entity'] = $_GET['entity_restrict'];
}
if (isset($_GET['user_restrict']) && $_GET['user_restrict'] > 0) {
    $where[] = 'c.user = :user';
    $params['user'] = $_GET['user_restrict'];
}

if (!empty($term)) {
    $where[] = "c.$field LIKE :term";
    $params['term'] = $term . '%';
}

$em = config::getAdapter()->getEntityManager();
$qb = $em->createQueryBuilder();

$qb->select("DISTINCT c.$field")
   ->from(get_class($item), 'c');

if (!empty($where)) {
    $qb->where(implode(' AND ', $where));
}

foreach ($params as $k => $v) {
    $qb->setParameter($k, $v);
}

$results = $qb->getQuery()->getArrayResult();

$values = array_map(fn ($row) => Html::entity_decode_deep($row[$field]), $results);

echo json_encode($values);
