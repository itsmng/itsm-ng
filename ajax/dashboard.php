<?php
/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ITSM-NG.
 *
 * ITSM-NG is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ITSM-NG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ITSM-NG. If not, see <http://www.gnu.org/licenses/>.
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
   $comparison = $_REQUEST['comparison'] ?? '';
   
   $format = $_REQUEST['format'] ?? 'count';
   $url = Dashboard::getWidgetUrl($format, $statType, $statSelection, 'model');

   $data = json_decode(file_get_contents($url));
   $widget = [
      'type' => $format,
      'value' => $data,
      'title' => $_REQUEST['title'] ?? $_REQUEST['statType'],
      'icon' => $_REQUEST['icon'] ?? '',
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
      $_REQUEST['format'] ?? 'count',
      json_decode($_REQUEST['coords']),
      $_REQUEST['title'],
      $_REQUEST['statType'],
      $_REQUEST['statSelection'],
      $_REQUEST['icon'],
      $_REQUEST['comparison'] ?? 'model'
   )) {
      echo json_encode(["status" => "success"]);
   } else {
      echo json_encode(["status" => "error"]);
   }
   exit;
}