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

if (!isset($_REQUEST["action"])) {
   exit;
}
if ($_REQUEST['action'] == 'preview' && isset($_REQUEST['statType']) && isset($_REQUEST['statSelection'])) {
   Session::checkRight("dashboard", READ);
   $statType = $_REQUEST['statType'];
   $statSelection = stripslashes($_REQUEST['statSelection']);

   $data = 
      file_get_contents(
         "http://localhost:3000/dashboard/count?statType=$statType&statSelection=$statSelection"
      );
   $widget = [
      'type' => 'number',
      'value' => $data,
      'title' => $_REQUEST['title'] ?? $_REQUEST['statType'],
      'icon' => 'fas fa-chart-pie',
   ];
   require_once GLPI_ROOT . "/ng/twig.class.php";
   $twig = Twig::load(GLPI_ROOT . "/templates", false);
   try {
      echo $twig->render('dashboard/widget.twig', [
         'widget' => $widget,
      ]);
   } catch (Exception $e) {
      echo $e->getMessage();
   }
} else if (($_REQUEST['action'] == 'delete') && isset($_REQUEST['coords']) && isset($_REQUEST['id'])) {
   Session::checkRight("dashboard", UPDATE);
   $dashboard = new Dashboard();
   $dashboard->getFromDB($_REQUEST['id']);
   if ($dashboard->deleteWidget(json_decode($_REQUEST['coords']))) {
      echo json_encode(["status" => "success"]);
   } else {
      echo json_encode(["status" => "error"]);
   }
   exit;
} else if (($_REQUEST['action'] == 'add') && isset($_REQUEST['coords']) && isset($_REQUEST['id'])) {
   Session::checkRight("dashboard", UPDATE);
   $dashboard = new Dashboard();
   $dashboard->getFromDB($_REQUEST['id']);
   if ($dashboard->addWidget(
      $_REQUEST['dataType'] ?? 'number',
      json_decode($_REQUEST['coords']),
      $_REQUEST['title'],
      $_REQUEST['statType'],
      $_REQUEST['statSelection'],
      $_REQUEST['icon']
   )) {
      echo json_encode(["status" => "success"]);
   } else {
      echo json_encode(["status" => "error"]);
   }
   exit;
}