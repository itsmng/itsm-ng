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

include ('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkCentralAccess();

// Make a select box
if ($_POST["idtable"] && class_exists($_POST["idtable"])) {
   if (isset($_POST['entity_restrict'])) {
      $entity_restrict = $_POST['entity_restrict'];
   }
   if (isset($_POST['condition'])) {
      $condition = $_POST['condition'];
   }

   $isDevice  = strpos($_POST["idtable"], "Device") === 0;
   $values = getOptionForItems($_POST['idtable'], ($condition ?? []) + (isset($entity_restrict)
      ? ['entities_id' => $_POST['entity_restrict']] : []), true, $isDevice);

   if (isset($_POST['used'])) {
      $_POST['used'] = Toolbox::jsonDecode($_POST['used'], true);
   }
   if (isset($_POST['used'][$_POST['idtable']])) {
      $used = $_POST['used'][$_POST['idtable']];
      if (isset($used)) {
         foreach($used as $usedId) {
            foreach($values as $key => $value) {
                if (gettype($value) == 'array') {
                    foreach($value as $subKey => $subValue) {
                        if ($usedId == $subKey) {
                            unset($values[$key][$subKey]);
                        }
                    }
                } else {
                    if ($usedId == $key) {
                        unset($values[$key]);
                    }
                }
            }
         }
      }
   }

   echo json_encode($values);
}
