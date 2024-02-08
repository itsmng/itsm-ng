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

/** @file
 * @brief
 */

include ('../inc/includes.php');
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (isset($_POST["projects_id"])) {
   $condition = ['glpi_projecttasks.projectstates_id' => ['<>', 3]];

   if ($_POST["projects_id"] > 0) {
      $condition['glpi_projecttasks.projects_id'] = $_POST['projects_id'];
   }

   if (isset($_POST['entity_restrict']) && $_POST['entity_restrict'] > 0) {
      $condition['glpi_projecttasks.entities_id'] = $_POST['entity_restrict'];
   }

   $values = getOptionForItems(ProjectTask::class, $condition);
   if (isset($_POST["used"]) && !empty($_POST["used"])) {
      $used = $_POST["used"];
      foreach ($used as $key => $value) {
         if (isset($values[$key])) {
            unset($values[$key]);
         }
      }
   }

   echo json_encode($values);
}
