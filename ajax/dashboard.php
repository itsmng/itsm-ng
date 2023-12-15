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

use Http\Client\Exception\HttpException;

include ('../inc/includes.php');

if (!isset($_REQUEST["action"])) {
   exit;
}

global $CFG_GLPI;

if ($_REQUEST['action'] == 'preview' && isset($_REQUEST['statType']) && isset($_REQUEST['statSelection'])) {
   Session::checkRight("dashboard", READ);
   require_once GLPI_ROOT . "/ng/twig.class.php";
   try {
      $statType = $_REQUEST['statType'];
      $statSelection = stripslashes($_REQUEST['statSelection']);
      
      $format = $_REQUEST['format'] ?? 'count';
      $url = $CFG_GLPI["url_dashboard_api"] . Dashboard::getWidgetUrl($format, $statType, $statSelection, $_REQUEST['options']);
      $encoded_data = @file_get_contents($url);
      $data = json_decode($encoded_data);
      $widget = [
         'type' => $format,
         'value' => $data,
         'title' => $_REQUEST['title'] ?? $_REQUEST['statType'],
         'icon' => $_REQUEST['icon'] ?? '',
      ];
      
      $twig = Twig::load(GLPI_ROOT . "/templates", false);
      echo $twig->render('dashboard/widget.twig', [
         'widget' => $widget,
      ]);
   } catch (HttpException $e) {
      echo json_encode(["status" => "error", "message" => $e->getMessage()]);
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
   
   $format = $_REQUEST['format'] ?? 'count';
   $coords = $_REQUEST['coords'];
   $title = $_REQUEST['title'] ?? $_REQUEST['statType'];
   $statType = $_REQUEST['statType'];
   $statSelection = stripslashes($_REQUEST['statSelection']);
   $options = [
      'icon' => $_REQUEST['icon'] ?? '',
      'comparison' => $_REQUEST['comparison'] ?? 'id',
      'direction' => $_REQUEST['direction'] ?? 'vertical',
   ];
   
   if ($dashboard->addWidget($format, $coords, $title, $statType, $statSelection, $options)) {
         echo json_encode(["status" => "success"]);
      } else {
         echo json_encode(["status" => "error"]);
   }
   exit;
} else if (($_REQUEST['action'] == 'getColumns')  && isset($_REQUEST['statType'])) {
   Session::checkRight("dashboard", READ);
   $statType = $_REQUEST['statType'];
   $url = $CFG_GLPI["url_dashboard_api"] . "/dashboard/comparisons/" . $statType;
   $data = json_decode(file_get_contents($url));
   echo json_encode($data);
   exit;
}