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

use itsmng\Timezone;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * Template for task
 * @since 9.2
**/
class ProjectTaskTemplate extends CommonDropdown {

   // From CommonDBTM
   public $dohistory          = true;
   public $can_be_translated  = true;

   static $rightname          = 'project';

   static function getTypeName($nb = 0) {
      return _n('Project task template', 'Project task templates', $nb);
   }


   function getAdditionalFields() {

      return [
         _x('item', 'State') => [
            'name'  => 'projectstates_id',
            'type'  => 'select',
            'values' => getOptionForItems('ProjectState'),
            'value' => $this->fields['projectstates_id'],
         ],
         _n('Type', 'Types', 1) => [
            'name'  => 'projecttasktypes_id',
            'type'  => 'select',
            'values' => getOptionForItems('ProjectTaskType'),
            'value' => $this->fields['projecttasktypes_id'],
         ],
         __('As child of') => [
            'name'  => 'projecttasks_id',
            'type'  => 'select',
            'values' => getOptionForItems('ProjectTask', ['NOT' => ['id' => $this->getID()]]),
            'value' => $this->fields['projecttasks_id'],
         ],
         __('Percent done') => [
            'name'  => 'percent_done',
            'type'  => 'number',
            'min' => 0,
            'max' => 100,
            'step' => 1,
            'value' => $this->fields['percent_done'],
            'after' => '%'
         ],
         __('Milestone') => [
            'name'  => 'is_milestone',
            'type'  => 'checkbox',
            'value' => $this->fields['is_milestone'],
         ],
         __('Planned start date') => [
            'name'  => 'plan_start_date',
            'type'  => 'datetime-local',
            'value' => $this->fields['plan_start_date'],
            'col_lg' => 6
         ],
         __('Real start date') => [
            'name'  => 'real_start_date',
            'type'  => 'datetime-local',
            'value' => $this->fields['real_start_date'],
            'col_lg' => 6
         ],
         __('Planned end date') => [
            'name'  => 'plan_end_date',
            'type' => 'datetime-local',
            'value' => $this->fields['plan_end_date'],            
            'col_lg' => 6
         ],
         __('Real end date') => [
            'name'  => 'real_end_date',
            'type'  => 'datetime-local',
            'value' => $this->fields['real_end_date'],
            'col_lg' => 6
         ],
         __('Planned duration') => [
            'name'  => 'planned_duration',
            'type'  => 'select',
            'values' => Timezone::GetTimeStamp([
               'value' => $this->fields['planned_duration'],
               'min'   => 0,
               'max'   => 100 * HOUR_TIMESTAMP,
               'step'  => HOUR_TIMESTAMP,
               'addfirstminutes' => true,
               'inhours'         => true
            ]),
            'value' => $this->fields['planned_duration'],
            'col_lg' => 6
         ],
         __('Effective duration') => [
            'name'  => 'effective_duration',
            'type'  => 'select',
            'values' => Timezone::GetTimeStamp([
               'value' => $this->fields['effective_duration'],
               'min'   => 0,
               'max'   => 100 * HOUR_TIMESTAMP,
               'step'  => HOUR_TIMESTAMP,
               'addfirstminutes' => true,
               'inhours'         => true
            ]),
            'value' => $this->fields['effective_duration'],
            'col_lg' => 6
         ],
         __('Description') => [
            'name'  => 'description',
            'type'  => 'richtextarea',
            'value' => $this->fields['description'],
            'col_lg' => 12,
            'col_md' => 12,
         ],
      ];
   }


   function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'       => '4',
         'name'     => _x('item', 'State'),
         'field'    => 'name',
         'table'    => 'glpi_projectstates',
         'datatype' => 'dropdown',
      ];

      $tab[] = [
         'id'       => '5',
         'name'     => _n('Type', 'Types', 1),
         'field'    => 'name',
         'table'    => 'glpi_projecttasktypes',
         'datatype' => 'dropdown',
      ];

      $tab[] = [
         'id'       => '6',
         'name'     => __('As child of'),
         'field'    => 'name',
         'table'    => 'glpi_projects',
         'datatype' => 'itemlink',
      ];

      $tab[] = [
         'id'       => '7',
         'name'     => __('Percent done'),
         'field'    => 'percent_done',
         'table'    => $this->getTable(),
         'datatype' => 'percent',
      ];

      $tab[] = [
         'id'       => '8',
         'name'     => __('Milestone'),
         'field'    => 'is_milestone',
         'table'    => $this->getTable(),
         'datatype' => 'bool',
      ];

      $tab[] = [
         'id'       => '9',
         'name'     => __('Planned start date'),
         'field'    => 'plan_start_date',
         'table'    => $this->getTable(),
         'datatype' => 'datetime',
      ];

      $tab[] = [
         'id'       => '10',
         'name'     => __('Real start date'),
         'field'    => 'real_start_date',
         'table'    => $this->getTable(),
         'datatype' => 'datetime',
      ];

      $tab[] = [
         'id'       => '11',
         'name'     => __('Planned end date'),
         'field'    => 'plan_end_date',
         'table'    => $this->getTable(),
         'datatype' => 'datetime',
      ];

      $tab[] = [
         'id'       => '12',
         'name'     => __('Real end date'),
         'field'    => 'real_end_date',
         'table'    => $this->getTable(),
         'datatype' => 'datetime',
      ];

      $tab[] = [
         'id'       => '13',
         'name'     => __('Planned duration'),
         'field'    => 'planned_duration',
         'table'    => $this->getTable(),
         'datatype' => 'actiontime',
      ];

      $tab[] = [
         'id'       => '14',
         'name'     => __('Effective duration'),
         'field'    => 'effective_duration',
         'table'    => $this->getTable(),
         'datatype' => 'actiontime',
      ];

      $tab[] = [
         'id'       => '15',
         'name'     => __('Description'),
         'field'    => 'description',
         'table'    => $this->getTable(),
         'datatype' => 'textarea',
      ];

      return $tab;
   }


   function displaySpecificTypeField($ID, $field = []) {

      switch ($field['type']) {
         case 'percent_done' :
            Dropdown::showNumber("percent_done", ['value' => $this->fields['percent_done'],
                                                  'min'   => 0,
                                                  'max'   => 100,
                                                  'step'  => 5,
                                                  'unit'  => '%']);
            break;
         case 'actiontime' :
            Dropdown::showTimeStamp($field["name"],
                                    ['min'             => 0,
                                     'max'             => 100 * HOUR_TIMESTAMP,
                                     'step'            => HOUR_TIMESTAMP,
                                     'value'           => $this->fields[$field["name"]],
                                     'addfirstminutes' => true,
                                     'inhours'         => true]);
            break;
      }
   }


   static function getSpecificValueToDisplay($field, $values, array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'type' :
            $types = self::getTypes();
            return $types[$values[$field]];
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   function defineTabs($options = []) {

      $ong = parent::defineTabs($options);
      $this->addStandardTab('Document_Item', $ong, $options);

      return $ong;
   }

}
