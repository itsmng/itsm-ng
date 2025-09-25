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

/**
 * @since 0.85
 */

include('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkCentralAccess();

// Make a select box
$items_id = filter_input(INPUT_POST, 'items_id', FILTER_VALIDATE_INT);
$itemtype = filter_input(INPUT_POST, 'itemtype', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (!$items_id || !$itemtype || !class_exists($itemtype)) {
    exit;
}

$devicetype = $itemtype;
$linktype   = $devicetype::getItem_DeviceType();
$specificities = $linktype::getSpecificities();

$em = config::getAdapter()->getEntityManager();
$qb = $em->createQueryBuilder();
$qb->select('d.id');

if (count($specificities)) {
    $concatFields = [];
    foreach (array_keys($specificities) as $field) {
        $concatFields[] = "d.$field";
    }

    $qb->addSelect(
        $qb->expr()->concat(
            ...array_map(fn ($f) => "COALESCE(d.$f, '')", array_keys($specificities))
        ) . " AS name"
    );
} else {
    $qb->addSelect('d.id AS name');
}

$qb->from($linktype::getEntityClass(), 'd')
   ->where('d.' . $devicetype::getForeignKeyField() . ' = :itemid')
   ->setParameter('itemid', $items_id)
   ->andWhere('d.itemtype = :itype')
   ->setParameter('itype', '');

$results = $qb->getQuery()->getArrayResult();
$devices = [];
foreach ($results as $row) {
    $name = $row['name'];
    if (empty($name)) {
        $name = $row['id'];
    }
    $devices[$row['id']] = $name;
}
echo json_encode(['name' => $devicetype::getForeignKeyField(), 'options' => $devices]);
