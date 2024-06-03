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

include ('../../inc/includes.php');

if (!isset($_REQUEST["action"])) {
   exit;
}

global $CFG_GLPI;

if ($_REQUEST['action'] == 'preview' && isset($_REQUEST['dataFilters'])) {
   Session::checkRight("dashboard", READ);
   $dataFilters = json_decode(stripslashes($_REQUEST['dataFilters'] ?? '[]'), true);
   echo json_encode(Search::getDatas($dataFilters['itemtype'], $dataFilters + ['list_limit' => 0]));
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
} else if (($_REQUEST['action'] == 'add') && isset($_REQUEST['widget']) && isset($_REQUEST['id'])) {
   Session::checkRight("dashboard", UPDATE);
   
   $dashboard = new Dashboard();
   $dashboard->getFromDB($_REQUEST['id']);
   $widget = json_decode(stripslashes($_REQUEST['widget']), true);
   $options = $_REQUEST['options'] ?? [];

   $format = $widget['format'];
   $coords = $widget['coords'];
   $title = $widget['title'];
   $filters = $widget['filters'];
   $options = $widget['options'];
   if ($dashboard->addWidget($format, $coords, $title, $filters, $options)) {
      echo json_encode(["status" => "success"]);
   } else {
      echo json_encode(["status" => "error"]);
   }
   exit;
} else if (($_REQUEST['action'] == 'getSearch')  && isset($_REQUEST['itemtype'])) {
   Session::checkRight("dashboard", READ);
   Search::showGenericSearch($_REQUEST['itemtype'], ['hide' => false, 'showbookmark' => false]);
   exit;
}
