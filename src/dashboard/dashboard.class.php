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

class Dashboard extends \CommonDBTM
{
   static $rightname = 'dashboard';

   /**
    * Return the title of the current dasbhoard
    *
    * @return string
    */
   function getTitle(): string
   {
      return $this->fields['name'] ?? "";
   }

   static function getMenuName()
   {
      return __('Dashboard');
   }

   static function getIcon()
   {
      return 'fas fa-tachometer-alt';
   }

   static function getMenuContent()
   {
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

   static function getFormUrl($full = true)
   {
      global $CFG_GLPI;
      if ($full) {
         return $CFG_GLPI['root_doc'] . "/src/dashboard/dashboard.form.php";
      }
      return "/src/dashboard/dashboard.form.php";
   }

   /**
    * @param $name
    **/
   static function cronInfo()
   {
      return ['description' => __('Update the dashboard tables')];
   }

   /**
    * Cron action on dashboard : populate databases for dashboard
    *
    * @param CronTask $task CronTask for log, display information if NULL? (default NULL)
    *
    * @return void
    **/
   static function crondashboard($task = null)
   {
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
   function showForm($ID)
   {
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
                  $this->isNewID($ID) ? [] : [
                     'type' => 'hidden',
                     'name' => 'id',
                     'value' => $ID,
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

   function getForUser()
   {
      global $DB;

      $profileId = $_SESSION['glpiactiveprofile']['id'];
      $userId = Session::getLoginUserID();

      $dashboardId = iterator_to_array(
         $DB->query("SELECT id FROM `" . self::getTable() . "` WHERE profileId = $profileId AND userId = $userId")
      );
      if (!$dashboardId) {
         $dashboardId = iterator_to_array(
            $DB->query("SELECT id FROM `" . self::getTable() . "` WHERE profileId = $profileId AND userId = 0")
         );
      }
      if (!$dashboardId)
         return false;
      $this->getFromDB($dashboardId[0]['id']);
      return true;
   }

   static function parseOptions($format, $options, $data)
   {
      if (isset($options['total']) && $format == 'pie') {
         $options['total'] = array_sum($data['1']) * 2;
         $options['startAngle'] = intval($options['startAngle']);
      }
      return $options;
   }

   function show($ID = null, $edit = false)
   {
      global $CFG_GLPI;

      Html::requireJs('charts');
      $twig_vars = [];

      try {
         if ($edit) {
            $objects = $CFG_GLPI['globalsearch_types'];
            asort($objects);
            $values = [];
            foreach ($objects as $object) {
               $values[$object] = ((string) $object)::getTypeName();
            }
            $jsUpdate = <<<JS
               $.ajax({
                  url: "{$CFG_GLPI['root_doc']}/src/dashboard/dashboard.ajax.php",
                  data: {
                     action: "getSearch",
                     itemtype: $('#ItemTypeDropdownForDashboard').val(),
                  },
                  success: function(data) {
                     $('#data-selection-search-content').html(data);
                     $('#data-selection-search-content form').attr('action', "#");
                     fetchPreview("{$CFG_GLPI['root_doc']}/src/dashboard/dashboard.ajax.php");
                  }
               });
            JS;
            ob_start();
            renderTwigTemplate('macros/wrappedInput.twig', [
               'title' => __('Itemtype'),
               'input' => [
                  'type' => 'select',
                  'id' => 'ItemTypeDropdownForDashboard',
                  'values' => $values,
                  'value' => array_key_first($values),
                  'col_lg' => 12,
                  'col_md' => 12,
                  'init' => $jsUpdate,
                  'hooks' => [
                     'change' => $jsUpdate,
                  ]
               ]
            ]);
            $twig_vars['dataSelection'] = ob_get_clean();
         };
         $twig_vars['dashboardApiUrl'] = $CFG_GLPI["url_dashboard_api"] . "/dashboard";
         $twig_vars['ajaxUrl'] = $CFG_GLPI['root_doc'] . "/src/dashboard/dashboard.ajax.php";
         $twig_vars['edit'] = $edit;
         $twig_vars['dashboardId'] = $ID ?? $this->fields['id'];
         $twig_vars['widgetGrid'] = self::expandContent(json_decode($this->fields['content'] ?? '[]', true) ?? []);
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

   static private function expandContent($content) {
      // make series and labels from content
      foreach ($content as $rowKey => $row) {
         foreach ($row as $cellKey => $widget) {
            $res = Search::getDatas($widget['filters']['itemtype'], $widget['filters']);
            if ($widget['type'] == 'count') {
               $content[$rowKey][$cellKey]['value'] = $res['data']['totalcount'];
               continue;
            }
            $labels = array_map(function($row) use ($widget) {
               $name = $widget['filters']['itemtype'] . '_' . $widget['options']['comparison'];
               return $row[$name][0]['name'];
            }, $res['data']['rows']);
            $series = [];
            foreach ($labels as $label) {
               if (isset($series[$label])) {
                  $series[$label]++;
               } else {
                  $series[$label] = 1;
               }
            }
            $uniqueLabels = array_values(array_unique($labels));
            $seriesCounts = array_values($series);
            switch ($widget['type']) {
               case 'pie':
                  $content[$rowKey][$cellKey]['labels'] = $uniqueLabels;
                  $content[$rowKey][$cellKey]['series'] = $seriesCounts;
                  break;
               case 'bar':
               case 'line':
                  $content[$rowKey][$cellKey]['labels'] = $uniqueLabels;
                  $content[$rowKey][$cellKey]['series'] = [$seriesCounts];
                  break;
            }
         }
      }
      return $content;
   }

   private function placeWidgetAtCoord(&$content, $widget, $coords)
   {
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
         } else {
            array_splice($content[$x], $y, 0, [$widget]);
         }
      }
   }

   static function getWidgetUrl($type, $statType, $statSelection, $options = [])
   {
      $encodedSelection = urlencode($statSelection);
      $url = "/dashboard/$type?statType=$statType&statSelection=$encodedSelection";
      if ($type != 'count') {
         $comparison = $options['comparison'] ?? 'id';
         $url .= "&comparison={$comparison}";
      }
      return $url;
   }

   function addWidget(string $format = 'count', array $coords = [0, 0], string $title = '', array $filters = [], array $options = [])
   {
      $dashboard = json_decode($this->fields['content'], true) ?? [];
      $widget = [
         'type' => $format,
         'title' => $title,
         'filters' => $filters,
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

   function deleteWidget($coords)
   {
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
