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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * DevicePci Class
**/
class DevicePci extends CommonDevice {

   static protected $forward_entity_to = ['Item_DevicePci', 'Infocom'];

   static function getTypeName($nb = 0) {
      return _n('PCI device', 'PCI devices', $nb);
   }
   
   
   /**
    * @see CommonDevice::getAdditionalFields()
    * @since 0.85
    */
    function getAdditionalFields() {
       
       return array_merge(
          parent::getAdditionalFields(),
          [
             _n('Model', 'Models', 1) => [
               'name'  => 'devicepcimodels_id',
               'type'  => 'select',
               'values' => getOptionForItems('DevicePciModel'),
               'value' => $this->fields['devicepcimodels_id'],
               'actions' => getItemActionButtons(['info', 'add'], 'DevicePciModel'),
               'col_lg' => 8,
            ],
            RegisteredID::getTypeName(Session::getPluralNumber()) => [
               'name'  => 'none',
               'type'  => 'multiSelect',
               'inputs' => [
                  [
                     'name' => '_registeredID_type[-1]',
                     'type' => 'select',
                     'values' => array_merge([ Dropdown::EMPTY_VALUE ], RegisteredID::getRegisteredIDTypes()),
                  ],
                  [
                     'name' => '_registeredID[-1]',
                     'type' => 'text',
                     'size' => 30,
                  ],
               ],
               'getInputAdd' => <<<JS
                  function () {
                     if ($('select[name="_registeredID_type[-1]"]').val() == 0) {
                        return;
                     }
                     var values = {
                        _registeredID_type: $('select[name="_registeredID_type[-1]"]').val(),
                        _registeredID: $('input[name="_registeredID[-1]').val()
                     };
                     var title = $('select[name="_registeredID_type[-1]"] option:selected').text() + ' ' + $('input[name="_registeredID[-1]"').val();
                     return {values, title};
                  }
               JS,
               'values' => getOptionsWithNameForItem('RegisteredID',
                  ['itemtype' => $this::class, 'items_id' => $this->getID()],
                  ['_registeredID_type' => 'device_type', '_registeredID' => 'name']
               ),
               'col_lg' => 12,
               'col_md' => 12,
            ],
         ]
      );
   }

   function rawSearchOptions() {

      $tab                 = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '17',
         'table'              => 'glpi_devicepcimodels',
         'field'              => 'name',
         'name'               => _n('Model', 'Models', 1),
         'datatype'           => 'dropdown'
      ];

      return $tab;
   }

   public static function rawSearchOptionsToAdd($itemtype, $main_joinparams) {
      $tab = [];

      $tab[] = [
         'id'                 => '95',
         'table'              => 'glpi_devicepcis',
         'field'              => 'designation',
         'name'               => __('Other component'),
         'forcegroupby'       => true,
         'usehaving'          => true,
         'massiveaction'      => false,
         'datatype'           => 'string',
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_items_devicepcis',
               'joinparams'         => $main_joinparams
            ]
         ]
      ];

      return $tab;
   }
}
