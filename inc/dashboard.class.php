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

use Ramsey\Uuid\Uuid;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class Dashboard extends \CommonDBTM {
   static $rightname = 'dashboard';


   function __construct(string $dashboard_key = "") {
      $this->key = $dashboard_key;
   }

   function getFromDB($ID) {
      global $DB;

      $iterator = $DB->request([
         'FROM'  => self::getTable(),
         'WHERE' => [
            'id' => $ID
         ],
         'LIMIT' => 1
      ]);
      if (count($iterator) == 1) {
         $this->fields = $iterator->next();
         $this->key    = $ID;
         $this->post_getFromDB();
         return true;
      } else if (count($iterator) > 1) {
         \Toolbox::logWarning(
            sprintf(
               'getFromDB expects to get one result, %1$s found!',
               count($iterator)
            )
         );
      }

      return false;
   }


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
      $menu = parent::getMenuContent() ?: [];

      if (static::canView()) {

         $menu['title'] = self::getMenuName();
         $menu['page']  = '/front/dashboard.php';
         $menu['icon']  = self::getIcon();
      }
      if (count($menu)) {
         return $menu;
      }
      return false;
   }


   function showForm($ID, $options = []) {
      include_once GLPI_ROOT . '/ng/form.utils.php';
      $this->getFromDB($ID);
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
            ]
         ]
      ];
      renderTwigForm($form);
      $this->show(true);
   }

   private function parseDbResponseForChecklist($reponse) {
      $result = [];
      foreach ($reponse as $key => $value) {
         $result[$value['name']] = ['value' => $value['name']];
      }
      return $result;
   }

   private function getCategories() {
      global $DB;
      $dashboard_assetTypes = iterator_to_array($DB->query("SELECT DISTINCT id, name FROM `Dashboard_AssetType`"));
      $assetTypes = [];
      foreach ($dashboard_assetTypes as $key => $value) {
         $models = iterator_to_array($DB->query("
            SELECT DISTINCT name FROM `Dashboard_Model`
            WHERE assetId = '".$value['id']."'
         "));
         $types = iterator_to_array($DB->query("
            SELECT DISTINCT name FROM `Dashboard_Type`
            WHERE assetId = '".$value["id"]."'
         "));
         $assetTypes[$value['name']] = [
            'value' => $value['name'],
            'content' => [
               'Model' => [
                  'value' => 'Model',
                  'content' => $this->parseDbResponseForChecklist($models),
               ],
               'Type' => [
                  'value' => 'Type',
                  'content' => $this->parseDbResponseForChecklist($types),
               ],
            ]
         ];
      }
      return [
         'Asset' => iterator_to_array($assetTypes),
      ];
   }

   function show($edit = false) {
      Html::requireJs('charts');
      $twig = Twig::load(GLPI_ROOT . "/templates", false);
      $twig_vars = [];

      if ($edit) {
         $twig_vars['dataSet'] = [];
         $twig_vars['dataGroups'] = $this->getCategories();
      };
      $twig_vars['edit'] = $edit;
      try {
         echo $twig->render('dashboard/dashboard.twig', $twig_vars);
      } catch (Exception $e) {
         echo $e->getMessage();
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

   function addWidget($data) {
      $grid = new Grid();
      $card = [];
      $dashboard = json_decode($this->fields['content'], true) ?? [];
      $provider = $card['provider'];
      $widget = [
         'type' => $card['widgettype'][0],
         'provider' => $provider,
      ];

      $this->placeWidgetAtCoord($dashboard, $widget, json_decode($data['coords']));
      
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
