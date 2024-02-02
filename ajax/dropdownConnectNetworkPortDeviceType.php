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

include ('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkRight("networking", UPDATE);

// Make a select box
if (class_exists($_POST["itemtype"])) {

   $params   = [
      'entity'    => $_POST["entity_restrict"],
      'condition' => [
         'id' => new \QuerySubQuery([
            'SELECT' => 'items_id',
            'FROM'   => 'glpi_networkports',
            'WHERE'  => [
               'itemtype'           => $_POST['itemtype'],
               'instantiation_type' => $_POST['instantiation_type']
            ]
         ])
      ],
   ];

   echo json_encode(getOptionForItems($_POST["itemtype"], ['entities_id' => $_POST["entity_restrict"] ?? Session::getActiveEntity()]));

   // Dropdown::show($_POST['itemtype'], $params);
}
