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

include("../inc/includes.php");

Session::checkRight("reports", READ);

Html::header(Report::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "tools", "report");

Report::title();

$items = $CFG_GLPI["report_types"];

// Titre
echo "<div class='center b spaced'><big>" . __('Device list') . "</big></div>";

// Request All

$itemTypes = filter_input(INPUT_POST, 'item_type', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$years = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);

if (empty($itemTypes) || (isset($itemTypes[0]) && $itemTypes[0] == 0)) {
    $itemTypes = $items;
}

if (is_array($years)) {
    $years = array_filter($years, fn($y) => $y !== false);
}

$em = config::getAdapter()->getEntityManager();
$qb = $em->createQueryBuilder();

foreach ($itemTypes as $itemtype) {

    $entityClass = 'Itsmng\\Domain\\Entities\\' . $itemtype;

    if (!class_exists($entityClass)) {
        continue;
    }

    $qb = $em->createQueryBuilder();

    // Selection of common fields
    $qb->select(
            'c.name AS itemname',
            'ct.name AS type',
            'i.buyDate',
            'i.warrantyDuration',
            'co.beginDate',
            'co.duration',
            'e.completename AS entname',
            'e.id AS entID',
        )
        ->from($entityClass, 'c')
        ->leftJoin(
            'Itsmng\\Domain\\Entities\\Infocom',
            'i',
            'WITH',
            'i.items_id = c.id AND i.itemtype = :itemtype'
        )
    ->leftJoin(
        'Itsmng\\Domain\\Entities\\ContractItem',
        'ci',
        'WITH',
        'ci.items_id = c.id AND ci.itemtype = :itemtype'
    )
        ->leftJoin('ci.contract', 'co')
        ->leftJoin('co.contractType', 'ct')
        ->leftJoin('c.entity', 'e')
        ->setParameter('itemtype', $itemtype)
        ->orderBy('e.completename', 'ASC')
        ->addOrderBy('c.isDeleted', 'DESC')
        ->addOrderBy('c.name', 'ASC');

    // Dynamic handling of location
    $reflection = new \ReflectionClass($entityClass);
    if ($reflection->hasProperty('location')) {
        $qb->leftJoin('c.location', 'l')
           ->addSelect('l.completename AS location');
    } else {
        $qb->addSelect("'' AS location");
    }
    // Specific handling according to the item
    if ($itemtype === 'SoftwareLicense') {
        $qb->leftJoin('c.software', 's')
           ->addSelect('s.isDeleted AS itemdeleted')
           ->andWhere('s.isTemplate = 0');
    } else {
        $qb->addSelect('c.isDeleted AS itemdeleted')
           ->andWhere('c.isTemplate = 0');
    }

    // Add dynamic conditions on years if present
    if (!empty($years)) {
    $orX = $qb->expr()->orX();

    foreach ($years as $idx => $year) {
        $start = new \DateTime("$year-01-01");
        $end   = new \DateTime("$year-12-31");

        // Add a condition for buyDate OR beginDate
        $orX->add(
            '(i.buyDate BETWEEN :start'.$idx.' AND :end'.$idx.' OR co.beginDate BETWEEN :start'.$idx.' AND :end'.$idx.')'
        );

        $qb->setParameter('start'.$idx, $start);
        $qb->setParameter('end'.$idx, $end);
    }
        $qb->andWhere($orX);
    }

    $results = $qb->getQuery()->getArrayResult();

    $display_entity = Session::isMultiEntitiesMode();

    if (!empty($results)) {
        $item = new $entityClass();

            echo "<div class='center b'>" .  $itemtype . "</div>";
            echo "<table class='tab_cadre_fixehov' aria-label='Device list' >";
            echo "<tr><th>" . __('Name') . "</th>";
            echo "<th>" . __('Deleted') . "</th>";
            if ($display_entity) {
                echo "<th>" . Entity::getTypeName(1) . "</th>";
            }
            echo "<th>" . Location::getTypeName(1) . "</th>";
            echo "<th>" . __('Date of purchase') . "</th>";
            echo "<th>" . __('Warranty expiration date') . "</th>";
            echo "<th>" . ContractType::getTypeName(1) . "</th>";
            echo "<th>" . __('Start date') . "</th>";
            echo "<th>" . __('End date') . "</th></tr>";

            foreach ($results as $data) {
                echo "<tr class='tab_bg_1'>";
                if ($data['itemname']) {
                    echo "<td> " . $data['itemname'] . "</td>";
                } else {
                    echo "<td>" . NOT_AVAILABLE . "</td>";
                }
                echo "<td class='center'>" . Dropdown::getYesNo($data['itemdeleted']) . "</td>";

                if ($display_entity) {
                    echo "<td>" . $data['entname'] . "</td>";
                }

                if ($data['location']) {
                    echo "<td>" . $data['location'] . "</td>";
                } else {
                    echo "<td>" . NOT_AVAILABLE . "</td>";
                }

                if ($data['buyDate']) {
                    echo "<td class='center'>" . Html::convDate($data['buyDate']) . "</td>";
                    if ($data["warrantyDuration"]) {
                        echo "<td class='center'>" . Infocom::getWarrantyExpir(
                            $data["buyDate"],
                            $data["warrantyDuration"]
                        ) .
                             "</td>";
                    } else {
                        echo "<td class='center'>" . NOT_AVAILABLE . "</td>";
                    }
                } else {
                    echo "<td class='center'>" . NOT_AVAILABLE . "</td>";
                    echo "<td class='center'>" . NOT_AVAILABLE . "</td>";
                }

                if ($data['type']) {
                    echo "<td>" . $data['type'] . "</td>";
                } else {
                    echo "<td>" . NOT_AVAILABLE . "</td>";
                }

                if ($data['beginDate']) {
                    echo "<td class='center'>" . Html::convDate($data['beginDate']) . "</td>";
                    if ($data["duration"]) {
                        echo "<td class='center'>" . Infocom::getWarrantyExpir(
                            $data["beginDate"],
                            $data["duration"]
                        ) . "</td>";
                    } else {
                        echo "<td class='center'>" . NOT_AVAILABLE . "</td>";
                    }
                } else {
                    echo "<td class='center'>" . NOT_AVAILABLE . "</td>";
                    echo "<td class='center'>" . NOT_AVAILABLE . "</td>";
                }

                echo "</tr>\n";
            }
            echo "</table><br><hr><br>";
        }
    }

Html::footer();
