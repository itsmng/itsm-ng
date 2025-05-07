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

/// NetworkPortEthernet class : Ethernet instantiation of NetworkPort
/// @since 0.84
class NetworkPortEthernet extends NetworkPortInstantiation
{
    public static function getTypeName($nb = 0)
    {
        return __('Ethernet port');
    }


    public function getNetworkCardInterestingFields()
    {
        return ['link.mac' => 'mac'];
    }


    public function prepareInput($input)
    {

        if (isset($input['speed']) && ($input['speed'] == 'speed_other_value')) {
            if (!isset($input['speed_other_value'])) {
                unset($input['speed']);
            } else {
                $speed = self::transformPortSpeed($input['speed_other_value'], false);
                if ($speed === false) {
                    unset($input['speed']);
                } else {
                    $input['speed'] = $speed;
                }
            }
        }

        return $input;
    }


    public function prepareInputForAdd($input)
    {
        return parent::prepareInputForAdd($this->prepareInput($input));
    }


    public function prepareInputForUpdate($input)
    {
        return parent::prepareInputForUpdate($this->prepareInput($input));
    }


    public function showInstantiationForm(NetworkPort $netport, $options, $recursiveItems)
    {
        global $CFG_GLPI;

        $returnValue = [
              $this->getTypeName() => [
                 'visible' => true,
                 'inputs' => [],
              ],
        ];
        if (!$options['several']) {
            $returnValue[$this->getTypeName()]['inputs'] = [
               _n('Network outlet', 'Network outlets', 1) => !$options['several'] ? [
                  'type' => 'select',
                  'name' => 'netpoints_id',
                  'itemtype' => Netpoint::class,
                  'value' => $this->fields['netpoints_id']?? null,
                  'actions' => getItemActionButtons(['info', 'add'], Netpoint::class),
               ] : [],
               DeviceNetworkCard::getTypeName(1) => !$options['several'] ? [
                  'type' => 'select',
                  'name' => 'items_devicenetworkcards_id',
                  'itemtype' => DeviceNetworkCard::class,
                  'value' => $this->fields['items_devicenetworkcards_id']?? null,
                  'actions' => getItemActionButtons(['info', 'add'], DeviceNetworkCard::class),
               ] : [],
            ];
        }
        $oppositePort = NetworkPort_NetworkPort::getOpposite($netport, $relations_id);
        $types = $CFG_GLPI["networkport_types"];
        $values = [];
        if (count($types)) {
            foreach ($types as $type) {
                if ($item = getItemForItemtype($type)) {
                    $values[$type] = $item->getTypeName(1);
                }
            }
        }
        asort($values);
        $entity_restrict = $options['entity_restrict'] ?? 0;

        $returnValue[$this->getTypeName()]['inputs'] += [
           __('Ethernet port type') => [
              'type' => 'select',
              'name' => 'type',
              'values' => self::getPortTypeName(),
              'value' => $this->fields['type']?? null,
           ],
           __('Ethernet port speed') => [
              'type' => 'select',
              'name' => 'speed',
              'values' => array_merge(
                  self::getPortSpeed(),
                  (!isset($this->fields['speed']) && !isset($standard_speeds[$this->fields['speed']]) && !empty($this->fields['speed'])) ?
                    ['speed_other_value' => self::transformPortSpeed($this->fields['speed'], true)] :
                       ['speed_other_value' => __('Other')]
              ),
              'value' => $this->fields['speed']?? null,
           ],
           __('MAC') => [
              'type' => 'text',
              'name' => 'mac',
              'value' => $netport->fields['mac'],
           ],
           !$oppositePort ? [
              'type' => 'hidden',
              'name' => 'NetworkPortConnect_networkports_id_1',
              'values' => $netport->getID(),
           ] : [],
           __('Connected to') => !$oppositePort ? [
              'type' => 'select',
              'id' => 'NetworkPortConnect_itemtype',
              'name' => 'NetworkPortConnect_itemtype',
              'values' => [Dropdown::EMPTY_VALUE] + $values,
              'hooks' => [
                 'change' => <<<JS
                  $.ajax({
                     url: '{$CFG_GLPI['root_doc']}/ajax/dropdownConnectNetworkPortDeviceType.php',
                     type: 'POST',
                     data: {
                        itemtype: $(this).val(),
                        entity_restrict: $entity_restrict,
                        networkports_id: '{$netport->getID()}',
                        instantiation_type: '{$this->getType()}',
                        with_empty: true
                     },
                     success: function(data) {
                        const jsonData = JSON.parse(data);

                        $('#NetworkPortConnect_items_id').empty();
                        for (const key in jsonData) {
                           $('#NetworkPortConnect_items_id').append('<option value="' + key + '">' + jsonData[key] + '</option>');
                        }
                     }
                  });
               JS,
              ]
           ] : [],
           __('Itemtype') => !$oppositePort ? [
              'type' => 'select',
              'id' => 'NetworkPortConnect_items_id',
              'name' => 'items',
              'hooks' => [
                 'change' => <<<JS
                  $.ajax({
                     url: '{$CFG_GLPI['root_doc']}/ajax/dropdownConnectNetworkPort.php',
                     type: 'POST',
                     data: {
                        item: $(this).val(),
                        networkports_id: '{$netport->getID()}',
                        itemtype: $('#NetworkPortConnect_itemtype').val(),
                        instantiation_type: '{$this->getType()}',
                     },
                     success: function(data) {
                        const jsonData = JSON.parse(data);

                        $('#NetworkPortConnect_networkports_id_2').empty();
                        for (const key in jsonData) {
                           $('#NetworkPortConnect_networkports_id_2').append('<option value="' + key + '">' + jsonData[key] + '</option>');
                        }
                     }
                  });
               JS,
              ]
           ] : [],
           __('Network port') => !$oppositePort ? [
              'type' => 'select',
              'name' => 'NetworkPortConnect_networkports_id_2',
           ] : [],
           __('Action') => $oppositePort ? [
              'content' => Html::showSimpleForm(
                  $oppositePort->getFormURL(),
                  'disconnect',
                  _x('button', 'Disconnect'),
                  ['id' => $relations_id]
              )
           ] : [],
        ];
        return $returnValue;
    }


    public function getInstantiationHTMLTableHeaders(
        HTMLTableGroup $group,
        HTMLTableSuperHeader $super,
        HTMLTableSuperHeader $internet_super = null,
        HTMLTableHeader $father = null,
        array $options = []
    ) {

        $display_options = &$options['display_options'];
        $header          = $group->addHeader('Connected', __('Connected to'), $super);

        DeviceNetworkCard::getHTMLTableHeader(
            'NetworkPortEthernet',
            $group,
            $super,
            $header,
            $options
        );

        $group->addHeader('speed', __('Ethernet port speed'), $super, $header);
        $group->addHeader('type', __('Ethernet port type'), $super, $header);

        Netpoint::getHTMLTableHeader('NetworkPortEthernet', $group, $super, $header, $options);

        $group->addHeader('Outlet', _n('Network outlet', 'Network outlets', 1), $super, $header);

        parent::getInstantiationHTMLTableHeaders($group, $super, $internet_super, $header, $options);
        return $header;
    }


    protected function getPeerInstantiationHTMLTable(
        NetworkPort $netport,
        HTMLTableRow $row,
        HTMLTableCell $father = null,
        array $options = []
    ) {

        DeviceNetworkCard::getHTMLTableCellsForItem($row, $this, $father, $options);

        if (!empty($this->fields['speed'])) {
            $row->addCell(
                $row->getHeaderByName('Instantiation', 'speed'),
                self::getPortSpeed($this->fields["speed"]),
                $father
            );
        }

        if (!empty($this->fields['type'])) {
            $row->addCell(
                $row->getHeaderByName('Instantiation', 'type'),
                self::getPortTypeName($this->fields["type"]),
                $father
            );
        }

        parent::getInstantiationHTMLTable($netport, $row, $father, $options);
        Netpoint::getHTMLTableCellsForItem($row, $this, $father, $options);
    }


    public function getInstantiationHTMLTable(
        NetworkPort $netport,
        HTMLTableRow $row,
        HTMLTableCell $father = null,
        array $options = []
    ) {

        return $this->getInstantiationHTMLTableWithPeer($netport, $row, $father, $options);
    }


    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
           'id'                 => 'common',
           'name'               => __('Characteristics')
        ];

        $tab[] = [
           'id'                 => '10',
           'table'              => NetworkPort::getTable(),
           'field'              => 'mac',
           'datatype'           => 'mac',
           'name'               => __('MAC'),
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'empty'
           ]
        ];

        $tab[] = [
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'type',
           'name'               => __('Ethernet port type'),
           'massiveaction'      => false,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '12',
           'table'              => $this->getTable(),
           'field'              => 'speed',
           'name'               => __('Ethernet port speed'),
           'massiveaction'      => false,
           'datatype'           => 'specific'
        ];

        return $tab;
    }


    /**
     * Get the possible value for Ethernet port type
     *
     * @param string|null $val  if not set, ask for all values, else for 1 value (default NULL)
     *
     * @return array|string
    **/
    public static function getPortTypeName($val = null)
    {

        $tmp['']   = Dropdown::EMPTY_VALUE;
        $tmp['T']  = __('Twisted pair (RJ-45)');
        $tmp['SX'] = __('Multimode fiber');
        $tmp['LX'] = __('Single mode fiber');

        if (is_null($val)) {
            return $tmp;
        }
        if (isset($tmp[$val])) {
            return $tmp[$val];
        }
        return NOT_AVAILABLE;
    }


    /**
     * Transform a port speed from string to integerer and vice-versa
     *
     * @param integer|string $val        port speed
     * @param boolean        $to_string  true if we must transform the speed to string
     *
     * @return integer|string (regarding what is requested)
    **/
    public static function transformPortSpeed($val, $to_string)
    {

        if ($to_string) {
            if (($val % 1000) == 0) {
                //TRANS: %d is the speed
                return sprintf(__('%d Gbit/s'), $val / 1000);
            }

            if ((($val % 100) == 0) && ($val > 1000)) {
                $val /= 100;
                //TRANS: %f is the speed
                return sprintf(__('%.1f Gbit/s'), $val / 10);
            }

            //TRANS: %d is the speed
            return sprintf(__('%d Mbit/s'), $val);
        } else {
            $val = preg_replace('/\s+/', '', strtolower($val));

            $number = sscanf($val, "%f%s", $speed, $unit);
            if ($number != 2) {
                return false;
            }

            if (($unit == 'mbit/s') || ($unit == 'mb/s')) {
                return (int)$speed;
            }

            if (($unit == 'gbit/s') || ($unit == 'gb/s')) {
                return (int)($speed * 1000);
            }

            return false;
        }
    }


    /**
     * Get the possible value for Ethernet port speed
     *
     * @param integer|null $val  if not set, ask for all values, else for 1 value (default NULL)
     *
     * @return array|string
    **/
    public static function getPortSpeed($val = null)
    {

        $tmp = [0     => '',
                     //TRANS: %d is the speed
                     10    => sprintf(__('%d Mbit/s'), 10),
                     100   => sprintf(__('%d Mbit/s'), 100),
                     //TRANS: %d is the speed
                     1000  => sprintf(__('%d Gbit/s'), 1),
                     10000 => sprintf(__('%d Gbit/s'), 10)];

        if (is_null($val)) {
            return $tmp;
        }
        if (isset($tmp[$val])) {
            return $tmp[$val];
        }
        return self::transformPortSpeed($val, true);
    }


    /**
     * @param $field
     * @param $values
     * @param $options   array
    **/
    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'type':
                return self::getPortTypeName($values[$field]);

            case 'speed':
                return self::getPortSpeed($values[$field]);
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }


    /**
     * @param $field
     * @param $name            (default '')
     * @param $values          (defaul '')
     * @param $options   array
     */
    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;

        switch ($field) {
            case 'type':
                $options['value'] = $values[$field];
                return Dropdown::showFromArray($name, self::getPortTypeName(), $options);

            case 'speed':
                $options['value'] = $values[$field];
                return Dropdown::showFromArray($name, self::getPortSpeed(), $options);
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }


    /**
     * @param $tab         array
     * @param $joinparams  array
    **/
    public static function getSearchOptionsToAddForInstantiation(array &$tab, array $joinparams)
    {
        $tab[] = [
           'id'                 => '22',
           'table'              => 'glpi_netpoints',
           'field'              => 'name',
           'datatype'           => 'dropdown',
            'name'              => __('Ethernet outlet'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'standard',
              'beforejoin'         => [
                 'table'              => 'glpi_networkportethernets',
                 'joinparams'         => $joinparams
              ]
           ]
        ];
    }
}
