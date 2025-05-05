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

/// NetworkPortAggregate class : aggregate instantiation of NetworkPort. Aggregate can represent a
/// trunk on switch, specific port under that regroup several ethernet ports to manage Ethernet
/// Bridging.
/// @since 0.84
class NetworkPortAggregate extends NetworkPortInstantiation
{
    public static function getTypeName($nb = 0)
    {
        return __('Aggregation port');
    }


    public function prepareInputForAdd($input)
    {

        if ((isset($input['networkports_id_list'])) && is_array($input['networkports_id_list'])) {
            $input['networkports_id_list'] = exportArrayToDB($input['networkports_id_list']);
        } else {
            $input['networkports_id_list'] = exportArrayToDB([]);
        }
        return parent::prepareInputForAdd($input);
    }


    public function prepareInputForUpdate($input)
    {

        if ((isset($input['networkports_id_list'])) && is_array($input['networkports_id_list'])) {
            $input['networkports_id_list'] = exportArrayToDB($input['networkports_id_list']);
        } else {
            $input['networkports_id_list'] = exportArrayToDB([]);
        }
        return parent::prepareInputForAdd($input);
    }


    public function showInstantiationForm(NetworkPort $netport, $options, $recursiveItems)
    {
        if (
            isset($this->fields['networkports_id_list'])
            && is_string($this->fields['networkports_id_list'])
        ) {
            $this->fields['networkports_id_list']
                           = importArrayFromDB($this->fields['networkports_id_list']);
        }

        $lastItem = $recursiveItems[count($recursiveItems) - 1];
        $netport_types = ['NetworkPortEthernet', 'NetworkPortWifi'];
        foreach ($netport_types as $netport_type) {
            $request = $this::getAdapter()->request([
               'SELECT' => [
                  'port.id',
                  'port.name',
                  'port.mac'
               ],
               'FROM'   => 'glpi_networkports AS port',
               'WHERE'  => [
                  'items_id'           => $lastItem->getID(),
                  'itemtype'           => $lastItem->getType(),
                  'instantiation_type' => $netport_type
               ],
               'ORDER'  => ['logical_number', 'name']
            ]);
            $results = $request->fetchAllAssociative();
            if (count($results)) {
                $array_element_name = call_user_func(
                    [$netport_type, 'getTypeName'],
                    count($results)
                );
                $possible_ports[$array_element_name] = [];

                foreach ($results as $portEntry) {
                    $macAddresses[$portEntry['id']] = $portEntry['mac'];
                    if (!empty($portEntry['mac'])) {
                        $portEntry['name'] = sprintf(
                            __('%1$s - %2$s'),
                            $portEntry['name'],
                            $portEntry['mac']
                        );
                    }
                    $possible_ports[$array_element_name][$portEntry['id']] = $portEntry['name'];
                }
            }
        }
        $checklistOptions = [];
        foreach ($possible_ports as $value) {
            $checklistOptions = array_merge($checklistOptions, $value);
        }

        return [
           $this->getTypeName() => [
              'visible' => true,
              'inputs' => [
                 __('MAC') => [
                    'type' => 'text',
                    'name' => 'mac',
                    'value' => $netport->fields['mac'],
                 ],
                 __('Origin port') => [
                    'type' => 'checklist',
                    'name' => 'networkports_id_list',
                    'options' => $checklistOptions,
                    'values' => $this->fields['networkports_id_list'],
                 ]
              ]
           ]
        ];
    }


    public function getInstantiationHTMLTableHeaders(
        HTMLTableGroup $group,
        HTMLTableSuperHeader $super,
        HTMLTableSuperHeader $internet_super = null,
        HTMLTableHeader $father = null,
        array $options = []
    ) {

        $group->addHeader('Origin', __('Origin port'), $super);

        parent::getInstantiationHTMLTableHeaders($group, $super, $internet_super, $father, $options);
        return null;
    }


    public function getInstantiationHTMLTable(
        NetworkPort $netport,
        HTMLTableRow $row,
        HTMLTableCell $father = null,
        array $options = []
    ) {

        if (
            isset($this->fields['networkports_id_list'])
            && is_string($this->fields['networkports_id_list'])
        ) {
            $this->fields['networkports_id_list']
                           = importArrayFromDB($this->fields['networkports_id_list']);
        }

        $row->addCell(
            $row->getHeaderByName('Instantiation', 'Origin'),
            $this->getInstantiationNetworkPortHTMLTable()
        );

        parent::getInstantiationHTMLTable($netport, $row, $father, $options);
        return null;
    }
}
