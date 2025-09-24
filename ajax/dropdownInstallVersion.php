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

if (strpos($_SERVER['PHP_SELF'], "dropdownInstallVersion.php")) {
    $AJAX_INCLUDE = 1;
    include('../inc/includes.php');
    header("Content-Type: text/html; charset=UTF-8");
    Html::header_nocache();
}

Session::checkRight("software", UPDATE);

$softwares_id = filter_input(INPUT_POST, 'softwares_id', FILTER_VALIDATE_INT);
if (!$softwares_id) {
    exit;
}
$used = [];
if (isset($_POST['used']) && is_array($_POST['used'])) {
    $used = array_filter($_POST['used'], 'is_numeric');
}
    $em = config::getAdapter()->getEntityManager();

    $qb = $em->createQueryBuilder();
    $qb->select('DISTINCT v, s.name AS sname')
    ->from('App\Entity\SoftwareVersion', 'v')
    ->leftJoin('v.state', 's')
    ->where('v.software = :softid')
    ->setParameter('softid', $softwares_id);

    if (count($used)) {
        $qb->andWhere($qb->expr()->notIn('v.id', ':used'))
        ->setParameter('used', $used);
    }

    $results = $qb->getQuery()->getResult();
    $number = count($results);

    $values = [];
    foreach ($results as $data) {
        $ID = $data['id'];
        $output = $data['name'];

        if (empty($output) || $_SESSION['glpiis_ids_visible']) {
            $output = sprintf(__('%1$s (%2$s)'), $output, $ID);
        }
        if (!empty($data['sname'])) {
            $output = sprintf(__('%1$s - %2$s'), $output, $data['sname']);
        }
        $values[$ID] = $output;
    }

    echo json_encode($values);
    // Dropdown::showFromArray($_POST['myname'], $values, ['display_emptychoice' => true]);

