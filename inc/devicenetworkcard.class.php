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
 * DeviceNetworkCard Class
**/
class DeviceNetworkCard extends CommonDevice
{
    protected static $forward_entity_to = ['Item_DeviceNetworkCard', 'Infocom'];

    public static function getTypeName($nb = 0)
    {
        return _n('Network card', 'Network cards', $nb);
    }


    /**
     * Criteria used for import function
     *
     * @since 0.84
    **/
    public function getImportCriteria()
    {

        return ['designation'      => 'equal',
                     'manufacturers_id' => 'equal',
                     'mac'              => 'equal'];
    }


    public function getAdditionalFields()
    {

        return array_merge(
            parent::getAdditionalFields(),
            [
              __('MAC address by default') => [
                 'name'  => 'mac_default',
                 'type'  => 'text',
                 'value' => $this->fields['mac_default'],
              ],
              __('Flow') => [
                 'name'  => 'bandwidth',
                 'type'  => 'text',
                 'value' => $this->fields['bandwidth'],
              ],
              _n('Model', 'Models', 1) => [
                 'name'  => 'devicenetworkcardmodels_id',
                 'type'  => 'select',
                 'values' => getOptionForItems('DeviceNetworkCardModel'),
                 'value' => $this->fields['devicenetworkcardmodels_id'],
                 'actions' => getItemActionButtons(['info', 'add'], 'DeviceNetworkCardModel'),
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
                 'col_lg' => 8,
                 'col_md' => 8,
              ],
            ]
        );
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'mac_default',
           'name'               => __('MAC address by default'),
           'datatype'           => 'mac',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '12',
           'table'              => $this->getTable(),
           'field'              => 'bandwidth',
           'name'               => __('Flow'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '13',
           'table'              => 'glpi_devicenetworkcardmodels',
           'field'              => 'name',
           'name'               => _n('Model', 'Models', 1),
           'datatype'           => 'dropdown'
        ];

        return $tab;
    }


    /**
     * Import a device if not exists
     *
     * @param $input array of datas
     *
     * @return integer ID of existing or new Device
    **/
    public function import(array $input)
    {

        if (!isset($input['designation']) || empty($input['designation'])) {
            return 0;
        }

        $criteria = [
           'SELECT' => 'id',
           'FROM'   => $this->getTable(),
           'WHERE'  => ['designation' => $input['designation']]
        ];

        if (isset($input["bandwidth"])) {
            $criteria['WHERE']['bandwidth'] = $input['bandwidth'];
        }

        $request = $this::getAdapter()->request($criteria);
        $results = $request->fetchAllAssociative();
        if (count($results) > 0) {
            $line = $results[0];
            return $line['id'];
        }
        return $this->add($input);
    }


    public static function getHTMLTableHeader(
        $itemtype,
        HTMLTableBase $base,
        HTMLTableSuperHeader $super = null,
        HTMLTableHeader $father = null,
        array $options = []
    ) {

        $column_name = __CLASS__;

        if (isset($options['dont_display'][$column_name])) {
            return;
        }

        if (in_array($itemtype, NetworkPort::getNetworkPortInstantiations())) {
            $base->addHeader($column_name, __('Interface'), $super, $father);
        } else {
            $column = parent::getHTMLTableHeader($itemtype, $base, $super, $father, $options);
            if ($column == $father) {
                return $father;
            }
            Manufacturer::getHTMLTableHeader(__CLASS__, $base, $super, $father, $options);
            $base->addHeader('devicenetworkcard_bandwidth', __('Flow'), $super, $father);
        }
    }


    public static function getHTMLTableCellsForItem(
        HTMLTableRow $row = null,
        CommonDBTM $item = null,
        HTMLTableCell $father = null,
        array $options = []
    ) {
        
        $column_name = __CLASS__;

        if (isset($options['dont_display'][$column_name])) {
            return;
        }

        if (empty($item)) {
            if (empty($father)) {
                return;
            }
            $item = $father->getItem();
        }    
        if (in_array($item->getType(), NetworkPort::getNetworkPortInstantiations())) {
            $link = new Item_DeviceNetworkCard();            
            if ($link->getFromDB($item->fields['items_devicenetworkcards_id'])) {
                $device = $link->getOnePeer(1);
                if ($device) {
                    $row->addCell($row->getHeaderByName($column_name), $device->getLink(), $father);
                }
            }
        }
    }


    public function getHTMLTableCellForItem(
        HTMLTableRow $row = null,
        CommonDBTM $item = null,
        HTMLTableCell $father = null,
        array $options = []
    ) {

        $column = parent::getHTMLTableCellForItem($row, $item, $father, $options);

        if ($column == $father) {
            return $father;
        }

        switch ($item->getType()) {
            case 'Computer':
                Manufacturer::getHTMLTableCellsForItem($row, $this, null, $options);
                if ($this->fields["bandwidth"]) {
                    $row->addCell(
                        $row->getHeaderByName('devicenetworkcard_bandwidth'),
                        $this->fields["bandwidth"],
                        $father
                    );
                }
                break;
        }
    }

    public static function rawSearchOptionsToAdd($itemtype, $main_joinparams)
    {
        $tab = [];

        $tab[] = [
           'id'                 => '112',
           'table'              => 'glpi_devicenetworkcards',
           'field'              => 'designation',
           'name'               => NetworkInterface::getTypeName(1),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'string',
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_items_devicenetworkcards',
                 'joinparams'         => $main_joinparams
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '113',
           'table'              => 'glpi_items_devicenetworkcards',
           'field'              => 'mac',
           'name'               => __('MAC address'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'string',
           'joinparams'         => $main_joinparams
        ];

        return $tab;
    }


    public static function getIcon()
    {
        return "fas fa-network-wired";
    }
}
