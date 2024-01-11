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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class Dashboard extends \CommonDBTM {
   static $rightname = 'dashboard';

   /**
    * Return the title of the current dasbhoard
    *
    * @return string
    */
   function getTitle(): string {
      return $this->fields['name'] ?? "";
   }

   static function getMenuName() {
      return __('Dashboard');
   }

   static function getIcon() {
      return 'fas fa-tachometer-alt';
   }

   static function getMenuContent() {
      $menu = [];

      if (static::canView()) {

         $menu['title'] = self::getMenuName();
         $menu['page']  = '/src/dashboard/dashboard.php';
         $menu['icon']  = self::getIcon();
         $menu['links'] = [
            'add' => '/src/dashboard/dashboard.form.php'
         ];
      }
      if (count($menu)) {
         return $menu;
      }
      return false;
   }

   static function getFormUrl($full = true) {
      global $CFG_GLPI;
      if ($full) {
         return $CFG_GLPI['root_doc'] . "/src/dashboard/dashboard.form.php";
      }
      return "/src/dashboard/dashboard.form.php";
   }

   /**
    * @param $name
   **/
   static function cronInfo() {
      return ['description' => __('Update the dashboard tables')];
   }

   /**
    * Cron action on dashboard : populate databases for dashboard
    *
    * @param CronTask $task CronTask for log, display information if NULL? (default NULL)
    *
    * @return void
   **/
   static function crondashboard($task = null) {
      global $DB;

      $scriptPath = GLPI_ROOT . '/src/dashboard/dashboardPopulation.sql';
      if (!$DB->runFile($scriptPath)) {
         $task->log('[ERROR] Dashboard tables could not be populated');
         return;
      };
      $task->log('Dashboard tables populated');
      $task->addVolume(1);
   }

   /**
    * Show the form to create or edit a dashboard
    *
    * @param $ID: [profileId, userId]
    *
    * @return void
    */
   function showForm($ID) {
      if ($ID) {
         $this->getFromDB($ID);
      }
      Html::requireJs('charts');

      $form = [
         'action' => $this->getFormURL(),
         'content' => [
            __('General') => [
               'visible' => true,
               'inputs' => [
                  __('Name') => [
                     'type' => 'text',
                     'name' => 'name',
                     'value' => $this->fields['name'] ?? '',
                     'required' => true,
                  ],
                  __('Profile') => [
                     'type' => 'select',
                     'id' => 'ProfileDropdownForDashboard',
                     'searchable' => true,
                     'name' => 'profileId',
                     'value' => $this->fields['profileId'] ?? '',
                     'values' => getOptionForItems(Profile::class),
                     'required' => true,
                  ],
                  __('User') => [
                     'type' => 'select',
                     'id' => 'UserDropdownForDashboard',
                     'searchable' => true,
                     'name' => 'userId',
                     'value' => $this->fields['userId'] ?? '',
                     'values' => getOptionForItems(User::class),
                     'required' => true,
                  ],
               ]
            ],
            "Hidden" => [
               'visible' => false,
               'inputs' => [
                  [
                     'type' => 'hidden',
                     'name' => isset($ID) ? 'update' : 'add',
                     'value' => 'true',
                  ],
                  [
                     'type' => 'hidden',
                     'name' => 'id',
                     'value' => $ID,
                  ],
                  [
                     'type' => 'hidden',
                     'name' => '_glpi_csrf_token',
                     'value' => Session::getNewCSRFToken()
                 ],
               ]
            ]
         ]
      ];
      renderTwigForm($form);
      if ($ID) {
         $this->show($ID, true);
      }
   }

   static public function getDashboardData($uri) {
      global $CFG_GLPI;

      $opts = [
         'http' => [
            'method' => 'GET',
            'header' => 
               "Accept: application/json\r\n".
               "api-key: ". $CFG_GLPI["dashboard_api_token"] ."\r\n"
         ]
      ];
      $context = stream_context_create($opts);
      try {
         if ($CFG_GLPI['url_dashboard_api'] == '') {
            throw new Exception(__("Dashboard API URL is not set"));
         }
         $encoded_data = @file_get_contents($CFG_GLPI['url_dashboard_api'] . $uri, false, $context);
         if ($encoded_data === FALSE) {
            throw new Exception(__("Could not fetch data from dashboard API"));
         }
         return json_decode($encoded_data, true);
      } catch (Exception $e) {
         throw new Exception($e->getMessage());
      }
   }

  private function generateListFromColumns($columns, $assetType = null) {
    global $DB;
    $output = [];
    foreach ($columns as $column) {
      if ($column['name'] == 'assetType') continue;
      $query = "SELECT name FROM `{$column['type']}`";
      $columnExists = count(iterator_to_array($DB->query("SHOW COLUMNS FROM `{$column['type']}` LIKE 'assetTypeId'"))) > 0;
      if ($columnExists && $assetType) {
        $query .= " WHERE assetTypeId = $assetType";
      }
      $values = array_column(iterator_to_array($DB->query($query)), 'name');
      $mappedValues = array_combine($values, array_map(function($value) {
         return ['name' => $value, 'value' => $value];
      }, $values));
      $output[$column['name']] = [
         'value' => $column['name'],
         'content' => $mappedValues,
      ];
    }
    return $output;
  }

   private function getCategories() {
      global $DB;
      $dashboard_assetTypes = iterator_to_array($DB->query("SELECT DISTINCT id, name FROM `Dashboard_AssetType`"));
      $assetTypes = [];
      $comparisons = self::getDashboardData("/dashboard/comparisons/Asset");
      foreach ($dashboard_assetTypes as $value) {
         $assetTypes[$value['name']] = [
            'value' => $value['name'],
            'content' => $this->generateListFromColumns($comparisons, $value['id']),
         ];
      }
      return [
         'Asset' => $assetTypes,
         'Ticket' => $this->generateListFromColumns(self::getDashboardData("/dashboard/comparisons/Ticket")),
         'Entity' => [],
         'Group' => [],
         'User' => [],
      ];
   }

   function getForUser() {
      global $DB;

      $profileId = $_SESSION['glpiactiveprofile']['id'];
      $userId = Session::getLoginUserID();

      $dashboardId = iterator_to_array(
         $DB->query("SELECT id FROM `".self::getTable()."` WHERE profileId = $profileId AND userId = $userId")
      );
      if (!$dashboardId) {
         $dashboardId = iterator_to_array(
            $DB->query("SELECT id FROM `".self::getTable()."` WHERE profileId = $profileId AND userId = 0")
         );
      }
      if (!$dashboardId)
         return false;
      $this->getFromDB($dashboardId[0]['id']);
      return true;
   }

   static function parseOptions($format, $options, $data) {
      if (isset($options['total']) && $format == 'pie') {
         $options['total'] = array_sum($data['1']) * 2;
         $options['startAngle'] = intval($options['startAngle']);
      }
      return $options;
   }

   function getGridContent($content) {
      foreach ($content as $rowIdx => $row) {
         foreach ($row as $colIdx => $widget) {
            $content[$rowIdx][$colIdx] = array_merge(
               $content[$rowIdx][$colIdx],
               ['value' => self::getDashboardData($widget['url'])],
            );
            $content[$rowIdx][$colIdx]['options'] = $this::parseOptions(
               $content[$rowIdx][$colIdx]['type'] ,
               $widget['options'] ?? [],
               $content[$rowIdx][$colIdx]['value']);
            unset ($content[$rowIdx][$colIdx]['url']);
         }
      }
      return $content;
   }

   function show($ID = null, $edit = false) {
      global $CFG_GLPI;

      Html::requireJs('charts');
      $twig_vars = [];
      
      try {
         if ($edit) {
            $twig_vars['dataSet'] = [];
            $twig_vars['dataGroups'] = $this->getCategories();
         };
         $twig_vars['dashboardApiUrl'] = $CFG_GLPI["url_dashboard_api"] . "/dashboard";
         $twig_vars['ajaxUrl'] = $CFG_GLPI['root_doc'] . "/src/dashboard/dashboard.ajax.php";
         $twig_vars['edit'] = $edit;
         $twig_vars['dashboardId'] = $ID ?? $this->fields['id'];
         $twig_vars['widgetGrid'] = $this->getGridContent(json_decode($this->fields['content'] ?? '[]', true) ?? []);
         $twig_vars['base'] = $CFG_GLPI['root_doc'];
         renderTwigTemplate('dashboard/dashboard.twig', $twig_vars);
      } catch (Exception $e) {
         echo <<<HTML
         <div class="center b">
            {$e->getMessage()}
         </div>
         HTML;
      }
   }

   private function placeWidgetAtCoord(&$content, $widget, $coords) {
      [$x, $y] = $coords;
      if ($x == -1) {
         array_unshift($content, [$widget]);
      } else if ($x > count($content)) {
         array_push($content, [$widget]);
      } else {
         if ($y == -1) {
            array_unshift($content[$x], $widget);
         } else if ($y > count($content[$x])) {
            array_push($content[$x], $widget);
         } else { // add the widget in between
            array_splice($content[$x], $y, 0, [$widget]);
         }
      }
   }
   
   static function getWidgetUrl($type, $statType, $statSelection, $options = []) {
      $encodedSelection = urlencode($statSelection);
      $url = "/dashboard/$type?statType=$statType&statSelection=$encodedSelection";
      if ($type != 'count') {
         $comparison = $options['comparison'] ?? 'id';
         $url .= "&comparison={$comparison}";
      }
      return $url;
   }

   function addWidget($format = 'count', $coords = [0, 0], $title = '', $statType = '', $statSelection = '', $options = []) {
      $dashboard = json_decode($this->fields['content'], true) ?? [];
      $urlStatSelection = stripslashes($statSelection);
      $widget = [
         'type' => $format,
         'title' => $title ?? $statType,
         'icon' => $options['icon'] ?? '',
         'url' => Dashboard::getWidgetUrl($format, $statType, $urlStatSelection, $options),
         'options' => $options,
      ];

      $this->placeWidgetAtCoord($dashboard, $widget, $coords);
      
      $content = str_replace("\\", "\\\\", json_encode($dashboard, JSON_UNESCAPED_UNICODE));
      if ($widget && $this->update(['id' => $this->fields['id'], 'content' => $content]))
         Session::addMessageAfterRedirect(__("Widget added successfuly"));
      else
         Session::addMessageAfterRedirect(__("Widget could not be added"), false, ERROR);
      Html::back();
   }

   function deleteWidget($coords) {
      [$x, $y] = $coords;
      $dashboard = json_decode($this->fields['content'], true);
      array_splice($dashboard[$x], $y, 1);
      if (empty($dashboard[$x])) {
         array_splice($dashboard, $x, 1);
      }
      $content = str_replace("\\", "\\\\", json_encode($dashboard, JSON_UNESCAPED_UNICODE));
      
      return ($this->update(['id' => $this->fields['id'], 'content' => $content]));
   }
}
