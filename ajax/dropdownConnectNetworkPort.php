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
 * @since 0.84
 */

$AJAX_INCLUDE = 1;

include('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkRight("networking", UPDATE);

// Make a select box
$itemtype           = filter_input(INPUT_POST, 'itemtype', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$item               = filter_input(INPUT_POST, 'item', FILTER_VALIDATE_INT);
$instantiation_type = filter_input(INPUT_POST, 'instantiation_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$networkports_id    = filter_input(INPUT_POST, 'networkports_id', FILTER_VALIDATE_INT);


    $entityClass = 'Itsmng\\Domain\\Entities\\' . $itemtype;
    $em = config::getAdapter()->getEntityManager();
    $qb = $em->createQueryBuilder();

    // Sélection des champs
    $qb->select('DISTINCT np2.id AS wid, np.id AS did, d.name AS cname, np.name AS nname');

    if ($instantiation_type === 'NetworkPortEthernet') {
        $qb->addSelect('npnt.name AS npname');
    }

    $qb->from($entityClass, 'd')
       ->leftJoin('d.networkports', 'np', 'WITH', 'np.items_id = d.id AND np.itemtype = :itemtype AND np.instantiation_type = :insttype')
       ->leftJoin('Itsmng\\Domain\\Entities\\NetworkPortsNetworkPort', 'np2np', 'WITH', 'np2np.networkports_id_1 = np.id OR np2np.networkports_id_2 = np.id')
       ->leftJoin('Itsmng\\Domain\\Entities\\NetworkPort', 'np2', 'WITH', 'np2.id = np2np.networkports_id_1 OR np2.id = np2np.networkports_id_2');

    if ($instantiation_type === 'NetworkPortEthernet') {
        $qb->leftJoin('Itsmng\\Domain\\Entities\\NetworkPortEthernet', 'npe', 'WITH', 'npe.id = np.id')
           ->leftJoin('npe.netpoint', 'npnt');
    }

    $qb->where('np2np.id IS NULL')
       ->andWhere('np.id IS NOT NULL')
       ->andWhere('np.id <> :networkports_id')
       ->andWhere('d.is_deleted = 0')
       ->andWhere('d.is_template = 0')
       ->setParameter('itemtype', $itemtype)
       ->setParameter('insttype', $instantiation_type)
       ->setParameter('networkports_id', $networkports_id);

    $results = $qb->getQuery()->getArrayResult();

    $values = [];
    foreach ($results as $data) {
        // Device name + port name
        $output = $output_long = $data['cname'];

        if (!empty($data['nname'])) {
            $output      = sprintf(__('%1$s - %2$s'), $output, $data['nname']);
            //TRANS: %1$s is device name, %2$s is port name
            $output_long = sprintf(__('%1$s - The port %2$s'), $output_long, $data['nname']);
        }

        // display netpoint (which will be copied)
        if (!empty($data['npname'])) {
            $output      = sprintf(__('%1$s - %2$s'), $output, $data['npname']);
            //TRANS: %1$s is a string (device name - port name...), %2$s is network outlet name
            $output_long = sprintf(__('%1$s - Network outlet %2$s'), $output_long, $data['npname']);
        }
        $ID = $data['did'];

        if ($_SESSION["glpiis_ids_visible"] || empty($output) || empty($output_long)) {
            $output      = sprintf(__('%1$s (%2$s)'), $output, $ID);
            $output_long = sprintf(__('%1$s (%2$s)'), $output_long, $ID);
        }
        $values[$ID] = $output_long;
    }
    echo json_encode($values);

