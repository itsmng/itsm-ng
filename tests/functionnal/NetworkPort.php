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

namespace tests\units;

use DbTestCase;

/* Test for inc/networkport.class.php */

class NetworkPort extends DbTestCase
{
    public function testAddSimpleNetworkPort()
    {
        $this->login();

        $computer1 = getItemByTypeName('Computer', '_test_pc01');
        $networkport = new \NetworkPort();

        // Be sure added
        $nb_log = (int)countElementsInTable('glpi_logs');
        $new_id = $networkport->add([
           'items_id'           => $computer1->getID(),
           'itemtype'           => 'Computer',
           'entities_id'        => $computer1->fields['entities_id'],
           'is_recursive'       => 0,
           'logical_number'     => 1,
           'mac'                => '00:24:81:eb:c6:d0',
           'instantiation_type' => 'NetworkPortEthernet',
           'name'               => 'eth1',
        ]);
        $this->integer((int)$new_id)->isGreaterThan(0);
        $this->integer((int)countElementsInTable('glpi_logs'))->isGreaterThan($nb_log);

        // check data in db
        $all_netports = getAllDataFromTable('glpi_networkports', ['ORDER' => 'id']);
        $current_networkport = end($all_netports);
        unset($current_networkport['id']);
        unset($current_networkport['date_mod']);
        unset($current_networkport['date_creation']);
        $expected = [
            'items_id'           => $computer1->getID(),
            'itemtype'           => 'Computer',
            'entities_id'        => $computer1->fields['entities_id'],
            'is_recursive'       => 0,
            'logical_number'     => 1,
            'name'               => 'eth1',
            'instantiation_type' => 'NetworkPortEthernet',
            'mac'                => '00:24:81:eb:c6:d0',
            'comment'            => null,
            'is_deleted'         => 0,
            'is_dynamic'         => 0,
        ];
        $this->array($current_networkport)->isIdenticalTo($expected);

        $all_netportethernets = getAllDataFromTable('glpi_networkportethernets', ['ORDER' => 'id']);
        $networkportethernet = end($all_netportethernets);
        $this->boolean($networkportethernet)->isFalse();

        // be sure added and have no logs
        $nb_log = (int)countElementsInTable('glpi_logs');
        $new_id = $networkport->add([
           'items_id'           => $computer1->getID(),
           'itemtype'           => 'Computer',
           'entities_id'        => $computer1->fields['entities_id'],
           'logical_number'     => 2,
           'mac'                => '00:24:81:eb:c6:d1',
           'instantiation_type' => 'NetworkPortEthernet',
        ], [], false);
        $this->integer((int)$new_id)->isGreaterThan(0);
        $this->integer((int)countElementsInTable('glpi_logs'))->isIdenticalTo($nb_log);
    }

    public function testAddCompleteNetworkPort()
    {
        $this->login();

        $computer1 = getItemByTypeName('Computer', '_test_pc01');

        // Do some installations
        $networkport = new \NetworkPort();

        // Be sure added
        $nb_log = (int)countElementsInTable('glpi_logs');
        $new_id = $networkport->add([
           'items_id'                    => $computer1->getID(),
           'itemtype'                    => 'Computer',
           'entities_id'                 => $computer1->fields['entities_id'],
           'is_recursive'                => 0,
           'logical_number'              => 3,
           'mac'                         => '00:24:81:eb:c6:d2',
           'instantiation_type'          => 'NetworkPortEthernet',
           'name'                        => 'em3',
           'comment'                     => 'Comment me!',
           'netpoints_id'                => 0,
           'items_devicenetworkcards_id' => 0,
           'type'                        => 'T',
           'speed'                       => 1000,
           'speed_other_value'           => '',
           'NetworkName_name'            => 'test1',
           'NetworkName_comment'         => 'test1 comment',
           'NetworkName_fqdns_id'        => 0,
           'NetworkName__ipaddresses'    => ['-1' => '192.168.20.1'],
           '_create_children'            => true // automatically add instancation, networkname and ipadresses
        ]);
        $this->integer($new_id)->isGreaterThan(0);
        $this->integer((int)countElementsInTable('glpi_logs'))->isGreaterThan($nb_log);

        // check data in db
        // 1 -> NetworkPortEthernet
        $all_netportethernets = getAllDataFromTable('glpi_networkportethernets', ['ORDER' => 'id']);
        $networkportethernet = end($all_netportethernets);
        unset($networkportethernet['id']);
        unset($networkportethernet['date_mod']);
        unset($networkportethernet['date_creation']);
        $expected = [
            'networkports_id'             => $new_id,
            'items_devicenetworkcards_id' => 0,
            'netpoints_id'                => 0,
            'type'                        => 'T',
            'speed'                       => 1000,
        ];
        $this->array($networkportethernet)->isIdenticalTo($expected);

        // 2 -> NetworkName
        $all_networknames = getAllDataFromTable('glpi_networknames', ['ORDER' => 'id']);
        $networkname = end($all_networknames);
        $networknames_id = $networkname['id'];
        unset($networkname['id']);
        unset($networkname['date_mod']);
        unset($networkname['date_creation']);
        $expected = [
            'entities_id' => $computer1->fields['entities_id'],
            'items_id'    => $new_id,
            'itemtype'    => 'NetworkPort',
            'name'        => 'test1',
            'comment'     => 'test1 comment',
            'fqdns_id'    => 0,
            'is_deleted'  => 0,
            'is_dynamic'  => 0,
        ];
        $this->array($networkname)->isIdenticalTo($expected);

        // 3 -> IPAddress
        $all_ipadresses = getAllDataFromTable('glpi_ipaddresses', ['ORDER' => 'id']);
        $ipadress = end($all_ipadresses);
        unset($ipadress['id']);
        unset($ipadress['date_mod']);
        unset($ipadress['date_creation']);
        $expected = [
            'entities_id'  => $computer1->fields['entities_id'],
            'items_id'     => $networknames_id,
            'itemtype'     => 'NetworkName',
            'version'      => 4,
            'name'         => '192.168.20.1',
            'binary_0'     => 0,
            'binary_1'     => 0,
            'binary_2'     => 65535,
            'binary_3'     => 3232240641,
            'is_deleted'   => 0,
            'is_dynamic'   => 0,
            'mainitems_id' => $computer1->getID(),
            'mainitemtype' => 'Computer',
        ];
        $this->array($ipadress)->isIdenticalTo($expected);

        // be sure added and have no logs
        $nb_log = (int)countElementsInTable('glpi_logs');
        $new_id = $networkport->add([
           'items_id'                    => $computer1->getID(),
           'itemtype'                    => 'Computer',
           'entities_id'                 => $computer1->fields['entities_id'],
           'is_recursive'                => 0,
           'logical_number'              => 4,
           'mac'                         => '00:24:81:eb:c6:d4',
           'instantiation_type'          => 'NetworkPortEthernet',
           'name'                        => 'em4',
           'comment'                     => 'Comment me!',
           'netpoints_id'                => 0,
           'items_devicenetworkcards_id' => 0,
           'type'                        => 'T',
           'speed'                       => 1000,
           'speed_other_value'           => '',
           'NetworkName_name'            => 'test2',
           'NetworkName_fqdns_id'        => 0,
           'NetworkName__ipaddresses'    => ['-1' => '192.168.20.2']
        ], [], false);
        $this->integer((int)$new_id)->isGreaterThan(0);
        $this->integer((int)countElementsInTable('glpi_logs'))->isIdenticalTo($nb_log);
    }

    public function testClone()
    {
        $this->login();

        $date = date('Y-m-d H:i:s');
        $_SESSION['glpi_currenttime'] = $date;

        $computer1 = getItemByTypeName('Computer', '_test_pc01');

        // Do some installations
        $networkport = new \NetworkPort();

        // Be sure added
        $nb_log = (int)countElementsInTable('glpi_logs');
        $new_id = $networkport->add([
           'items_id'                    => $computer1->getID(),
           'itemtype'                    => 'Computer',
           'entities_id'                 => $computer1->fields['entities_id'],
           'is_recursive'                => 0,
           'logical_number'              => 3,
           'mac'                         => '00:24:81:eb:c6:d2',
           'instantiation_type'          => 'NetworkPortEthernet',
           'name'                        => 'em3',
           'comment'                     => 'Comment me!',
           'netpoints_id'                => 0,
           'items_devicenetworkcards_id' => 0,
           'type'                        => 'T',
           'speed'                       => 1000,
           'speed_other_value'           => '',
           'NetworkName_name'            => 'test1',
           'NetworkName_comment'         => 'test1 comment',
           'NetworkName_fqdns_id'        => 0,
           'NetworkName__ipaddresses'    => ['-1' => '192.168.20.1'],
           '_create_children'            => true // automatically add instancation, networkname and ipadresses
        ]);
        $this->integer($new_id)->isGreaterThan(0);
        $this->integer((int)countElementsInTable('glpi_logs'))->isGreaterThan($nb_log);

        // Test item cloning
        $added = $networkport->clone();
        $this->integer((int)$added)->isGreaterThan(0);

        $clonedNetworkport = new \NetworkPort();
        $this->boolean($clonedNetworkport->getFromDB($added))->isTrue();

        $fields = $networkport->fields;

        // Check the networkport values. Id and dates must be different, everything else must be equal
        foreach ($fields as $k => $v) {
            switch ($k) {
                case 'id':
                    $this->variable($clonedNetworkport->getField($k))->isNotEqualTo($networkport->getField($k));
                    break;
                case 'date_mod':
                case 'date_creation':
                    $dateClone = new \DateTime($clonedNetworkport->getField($k));
                    $expectedDate = new \DateTime($date);
                    $this->dateTime($dateClone)->isEqualTo($expectedDate);
                    break;
                case 'name':
                    $this->variable($clonedNetworkport->getField($k))->isEqualTo("{$networkport->getField($k)} (copy)");
                    break;
                default:
                    $this->variable($clonedNetworkport->getField($k))->isEqualTo($networkport->getField($k));
            }
        }

        $instantiation = $networkport->getInstantiation();
        $clonedInstantiation = $clonedNetworkport->getInstantiation();
        $instantiationFields = $networkport->fields;

        // Check the networkport instantiation values. Id, networkports_id and dates must be different, everything else must be equal
        foreach ($fields as $k => $v) {
            switch ($k) {
                case 'id':
                    $this->variable($clonedInstantiation->getField($k))->isNotEqualTo($instantiation->getField($k));
                    break;
                case 'networkports_id':
                    $this->variable($clonedInstantiation->getField($k))->isNotEqualTo($instantiation->getField($k));
                    $this->variable($clonedInstantiation->getField($k))->isEqualTo($clonedNetworkport->getID());
                    break;
                case 'date_mod':
                case 'date_creation':
                    $dateClone = new \DateTime($clonedInstantiation->getField($k));
                    $expectedDate = new \DateTime($date);
                    $this->dateTime($dateClone)->isEqualTo($expectedDate);
                    break;
                default:
                    $this->variable($clonedInstantiation->getField($k))->isEqualTo($instantiation->getField($k));
            }
        }
    }

    public function testAliasCopiesMacFromOriginPort()
    {
        $this->login();

        $computer = getItemByTypeName('Computer', '_test_pc01');
        $networkport = new \NetworkPort();

        $origin_port_id = $networkport->add([
           'items_id'           => $computer->getID(),
           'itemtype'           => 'Computer',
           'entities_id'        => $computer->fields['entities_id'],
           'is_recursive'       => 0,
           'logical_number'     => 10,
           'mac'                => '00:24:81:eb:c7:10',
           'instantiation_type' => 'NetworkPortEthernet',
           'name'               => 'origin-port',
        ]);
        $this->integer($origin_port_id)->isGreaterThan(0);

        $alias_port_id = $networkport->add([
           'items_id'           => $computer->getID(),
           'itemtype'           => 'Computer',
           'entities_id'        => $computer->fields['entities_id'],
           'is_recursive'       => 0,
           'logical_number'     => 11,
           'instantiation_type' => 'NetworkPortAlias',
           'name'               => 'alias-port',
        ]);
        $this->integer($alias_port_id)->isGreaterThan(0);

        $alias = new \NetworkPortAlias();
        $alias_id = $alias->add([
           'networkports_id'       => $alias_port_id,
           'networkports_id_alias' => $origin_port_id,
        ]);
        $this->integer($alias_id)->isGreaterThan(0);
        
        // Check that the alias port's MAC is updated to origin port's MAC
        $aliasNetworkPort = new \NetworkPort();
        $this->boolean($aliasNetworkPort->getFromDB($alias_port_id))->isTrue();
        $this->string($aliasNetworkPort->fields['mac'])->isEqualTo('00:24:81:eb:c7:10');
    }

    public function testAggregateStoresPortList()
    {
        $this->login();

        $networkequipment = getItemByTypeName('NetworkEquipment', '_test_networkequipment_1');
        $networkport = new \NetworkPort();

        $port1 = (int)$networkport->add([
           'name'         => 'agg-if1',
           'items_id'     => $networkequipment->getID(),
           'itemtype'     => 'NetworkEquipment',
           'entities_id'  => $networkequipment->fields['entities_id'],
        ]);
        $port2 = (int)$networkport->add([
           'name'         => 'agg-if2',
           'items_id'     => $networkequipment->getID(),
           'itemtype'     => 'NetworkEquipment',
           'entities_id'  => $networkequipment->fields['entities_id'],
        ]);
        $port3 = (int)$networkport->add([
           'name'         => 'agg-if3',
           'items_id'     => $networkequipment->getID(),
           'itemtype'     => 'NetworkEquipment',
           'entities_id'  => $networkequipment->fields['entities_id'],
        ]);
        $agg_parent_port = (int)$networkport->add([
           'name'         => 'agg-parent',
           'items_id'     => $networkequipment->getID(),
           'itemtype'     => 'NetworkEquipment',
           'entities_id'  => $networkequipment->fields['entities_id'],
        ]);

        $this->integer($port1)->isGreaterThan(0);
        $this->integer($port2)->isGreaterThan(0);
        $this->integer($port3)->isGreaterThan(0);
        $this->integer($agg_parent_port)->isGreaterThan(0);

        $aggregate = new \NetworkPortAggregate();
        $aggregate_id = $aggregate->add([
           'networkports_id'      => $agg_parent_port,
           'networkports_id_list' => [$port1, $port2],
        ]);
        $this->integer($aggregate_id)->isGreaterThan(0);
        $this->array(importArrayFromDB($aggregate->fields['networkports_id_list']))
           ->isIdenticalTo([$port1, $port2]);

        $this->boolean($aggregate->update([
           'id'                   => $aggregate_id,
           'networkports_id'      => $agg_parent_port,
           'networkports_id_list' => [$port2, $port3],
        ]))->isTrue();
        $this->array(importArrayFromDB($aggregate->fields['networkports_id_list']))
           ->isIdenticalTo([$port2, $port3]);
    }

    public function testConnectTwoPorts()
    {
        $this->login();

        $computer = getItemByTypeName('Computer', '_test_pc01');
        $networkport = new \NetworkPort();

        $port_1_id = $networkport->add([
           'items_id'           => $computer->getID(),
           'itemtype'           => 'Computer',
           'entities_id'        => $computer->fields['entities_id'],
           'is_recursive'       => 0,
           'logical_number'     => 20,
           'instantiation_type' => 'NetworkPortEthernet',
           'name'               => 'wire-port-1',
        ]);
        $this->integer($port_1_id)->isGreaterThan(0);

        $port_2_id = $networkport->add([
           'items_id'           => $computer->getID(),
           'itemtype'           => 'Computer',
           'entities_id'        => $computer->fields['entities_id'],
           'is_recursive'       => 0,
           'logical_number'     => 21,
           'instantiation_type' => 'NetworkPortEthernet',
           'name'               => 'wire-port-2',
        ]);
        $this->integer($port_2_id)->isGreaterThan(0);

        $wire = new \NetworkPort_NetworkPort();
        $wire_id = $wire->add([
           'networkports_id_1' => $port_1_id,
           'networkports_id_2' => $port_2_id,
        ]);
        $this->integer($wire_id)->isGreaterThan(0);
        $this->boolean($wire->getFromDBForNetworkPort($port_1_id))->isTrue();
        $this->integer((int)$wire->getOppositeContact($port_1_id))->isEqualTo($port_2_id);
        $this->integer((int)$wire->getOppositeContact($port_2_id))->isEqualTo($port_1_id);
    }

    public function testVlanAssignAndUnassign()
    {
        $this->login();

        $computer = getItemByTypeName('Computer', '_test_pc01');
        $networkport = new \NetworkPort();
        $port_id = $networkport->add([
           'items_id'           => $computer->getID(),
           'itemtype'           => 'Computer',
           'entities_id'        => $computer->fields['entities_id'],
           'is_recursive'       => 0,
           'logical_number'     => 12,
           'mac'                => '00:24:81:eb:c7:12',
           'instantiation_type' => 'NetworkPortEthernet',
           'name'               => 'vlan-port',
        ]);
        $this->integer($port_id)->isGreaterThan(0);

        $vlan = new \Vlan();
        $vlan_id = $vlan->add([
           'name' => 'Functional VLAN',
           'tag'  => 120,
        ]);
        $this->integer($vlan_id)->isGreaterThan(0);

        $networkport_vlan = new \NetworkPort_Vlan();
        $relation_id = $networkport_vlan->assignVlan($port_id, $vlan_id, 1);
        $this->integer($relation_id)->isGreaterThan(0);
        $this->boolean($networkport_vlan->getFromDB($relation_id))->isTrue();
        $this->integer((int)$networkport_vlan->fields['tagged'])->isEqualTo(1);

        $this->boolean($networkport_vlan->unassignVlan($port_id, $vlan_id))->isTrue();
        $this->integer(countElementsInTable(
            \NetworkPort_Vlan::getTable(),
            [
                'networkports_id' => $port_id,
                'vlans_id'        => $vlan_id,
            ]
        ))->isEqualTo(0);
    }
}
