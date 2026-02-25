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
 * DeviceSoundCard Class
**/
class DeviceSoundCard extends CommonDevice
{
    protected static $forward_entity_to = ['Item_DeviceSoundCard', 'Infocom'];

    public static function getTypeName($nb = 0)
    {
        return _n('Soundcard', 'Soundcards', $nb);
    }


    public function getAdditionalFields()
    {

        return array_merge(
            parent::getAdditionalFields(),
            [
              _n('Type', 'Types', 1) => [
                 'name'  => 'type',
                 'type'  => 'text',
                 'value' => $this->fields['type'],
              ],
              _n('Model', 'Models', 1) => [
                 'name'  => 'devicesoundcardmodels_id',
                 'type'  => 'select',
                 'values' => getOptionForItems('DeviceSoundCardModel'),
                 'value' => $this->fields['devicesoundcardmodels_id'],
                 'actions' => getItemActionButtons(['info', 'add'], 'DeviceSoundCardModel'),
              ],
              RegisteredID::getTypeName(Session::getPluralNumber()) => [
                 'name'  => 'none',
                 'type'  => 'multiSelect',
                 'inputs' => [
                    [
                       'name' => 'current_registeredID_type',
                       'type' => 'select',
                       'values' => array_merge([ Dropdown::EMPTY_VALUE ], RegisteredID::getRegisteredIDTypes()),
                    ],
                    [
                       'name' => 'current_registeredID',
                       'type' => 'text',
                       'size' => 30,
                    ],
                 ],
                 'getInputAdd' => <<<JS
                  function () {
                     if (!$('input[name="current_registeredID"]').val()) {
                        return;
                     }
                     var values = {
                        _registeredID_type: $('select[name="current_registeredID_type"]').val(),
                        _registeredID: $('input[name="current_registeredID').val()
                     };
                     var title = $('select[name="current_registeredID_type"] option:selected').text() + ' ' + $('input[name="current_registeredID"').val();
                     return {values, title};
                  }
               JS,
                 'values' => getOptionsWithNameForItem(
                     'RegisteredID',
                     ['itemtype' => $this::class, 'items_id' => $this->getID()],
                     ['_registeredID_type' => 'device_type', '_registeredID' => 'name']
                 ),
                 'col_lg' => 12,
                 'col_md' => 12,
              ],
            ]
        );
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '12',
           'table'              => $this->getTable(),
           'field'              => 'type',
           'name'               => _n('Type', 'Types', 1),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '13',
           'table'              => 'glpi_devicesoundcardmodels',
           'field'              => 'name',
           'name'               => _n('Model', 'Models', 1),
           'datatype'           => 'dropdown'
        ];

        return $tab;
    }


    public static function getHTMLTableHeader(
        $itemtype,
        HTMLTableBase $base,
        ?HTMLTableSuperHeader $super = null,
        ?HTMLTableHeader $father = null,
        array $options = []
    ) {

        $column = parent::getHTMLTableHeader($itemtype, $base, $super, $father, $options);

        if ($column == $father) {
            return $father;
        }

        switch ($itemtype) {
            case 'Computer':
                Manufacturer::getHTMLTableHeader(__CLASS__, $base, $super, $father, $options);
                $base->addHeader('devicesoundcard_type', _n('Type', 'Types', 1), $super, $father);
                break;
        }
    }


    public function getHTMLTableCellForItem(
        ?HTMLTableRow $row = null,
        ?CommonDBTM $item = null,
        ?HTMLTableCell $father = null,
        array $options = []
    ) {

        $column = parent::getHTMLTableCellForItem($row, $item, $father, $options);

        if ($column == $father) {
            return $father;
        }

        switch ($item->getType()) {
            case 'Computer':
                Manufacturer::getHTMLTableCellsForItem($row, $this, null, $options);
                if ($this->fields["type"]) {
                    $row->addCell(
                        $row->getHeaderByName('devicesoundcard_type'),
                        $this->fields["type"],
                        $father
                    );
                }
        }
    }

    public static function rawSearchOptionsToAdd($itemtype, $main_joinparams)
    {
        $tab = [];

        $tab[] = [
           'id'                 => '12',
           'table'              => 'glpi_devicesoundcards',
           'field'              => 'designation',
           'name'               => static::getTypeName(1),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'string',
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_items_devicesoundcards',
                 'joinparams'         => $main_joinparams
              ]
           ]
        ];

        return $tab;
    }


    public static function getIcon()
    {
        return "fas fa-volume-down";
    }
}
