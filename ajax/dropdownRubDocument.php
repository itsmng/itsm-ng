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

if (strpos($_SERVER['PHP_SELF'], "dropdownRubDocument.php")) {
    $AJAX_INCLUDE = 1;
    include('../inc/includes.php');
    header("Content-Type: text/html; charset=UTF-8");
    Html::header_nocache();
}

Session::checkCentralAccess();

// Make a select box
if (isset($_POST["rubdoc"])) {
    $used = [];

    // Clean used array
    if (isset($_POST['used']) && is_array($_POST['used']) && (count($_POST['used']) > 0)) {
        $entityManager = config::getAdapter()->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
            ->select('d.id')
            ->from(\Itsmng\Domain\Entities\Document::class, 'd')
            ->where('d.id = :used')
            ->andWhere('d.documentCategory = :rubdoc')
            ->setParameter('used', $_POST['used'])
            ->setParameter('rubdoc', (int)$_POST['rubdoc']);

        $result = $queryBuilder->getQuery()->getResult();

        $ids  = array_column($results, 'id');
        $used = array_combine($ids, $ids);
    }

    if (!isset($_POST['entity']) || $_POST['entity'] === '') {
        $_POST['entity'] = $_SESSION['glpiactive_entity'];
    }
    $values = getItemByEntity(
        Document::class,
        intval($_POST['entity']),
        ['glpi_documents.documentcategories_id' => (int)$_POST["rubdoc"]]
    );
    foreach ($used as $id) {
        unset($values[$id]);
    }

    echo json_encode($values);
}
