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
 * @since 0.84
**/
class IPNetwork_Vlan extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1          = 'IPNetwork';
    public static $items_id_1          = 'ipnetworks_id';

    public static $itemtype_2          = 'Vlan';
    public static $items_id_2          = 'vlans_id';
    public static $checkItem_2_Rights  = self::HAVE_VIEW_RIGHT_ON_ITEM;


    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    /**
     * @param $portID
     * @param $vlanID
    **/
    public function unassignVlan($portID, $vlanID)
    {

        $this->getFromDBByCrit([
           'ipnetworks_id'   => $portID,
           'vlans_id'        => $vlanID
        ]);

        return $this->delete($this->fields);
    }


    /**
     * @param $port
     * @param $vlan
    **/
    public function assignVlan($port, $vlan)
    {

        $input = ['ipnetworks_id' => $port,
                       'vlans_id'      => $vlan];

        return $this->add($input);
    }


    /**
     * @param $port   IPNetwork object
    **/
    public static function showForIPNetwork(IPNetwork $port)
    {
        global $DB, $CFG_GLPI;

        $ID = $port->getID();
        if (!$port->can($ID, READ)) {
            return false;
        }

        $canedit = $port->canEdit($ID);
        $rand    = mt_rand();

        $iterator = $DB->request([
           'SELECT'    => [
              self::getTable() . '.id AS assocID',
              'glpi_vlans.*'
           ],
           'FROM'      => self::getTable(),
           'LEFT JOIN' => [
              'glpi_vlans'   => [
                 'ON' => [
                    self::getTable()  => 'vlans_id',
                    'glpi_vlans'      => 'id'
                 ]
              ]
           ],
           'WHERE'     => ['ipnetworks_id' => $ID]
        ]);

        $vlans  = [];
        $used   = [];
        $number = count($iterator);
        while ($line = $iterator->next()) {
            $used[$line["id"]]       = $line["id"];
            $vlans[$line["assocID"]] = $line;
        }

        if ($canedit) {
            renderTwigForm([
                'action' => self::getFormURL(),
                'buttons' => [[
                    'type' => 'submit', 'name' => 'add', 'value' => _x('button', 'Associate'),
                    'class' => 'btn btn-primary', 'icon' => 'fas fa-plus',
                ]],
                'content' => [__('Associate a VLAN') => [
                    'visible' => true,
                    'inputs' => [
                        ['type' => 'hidden', 'name' => 'ipnetworks_id', 'value' => $ID],
                        Vlan::getTypeName(1) => [
                            'type' => 'select', 'name' => 'vlans_id',
                            ...getAjaxDropdownOptions(Vlan::class, [], true, false, $used),
                        ],
                    ],
                ]],
            ]);
        }
        $values = [];
        $selection = [];
        $object = new Vlan();
        foreach ($vlans as $data) {
            $object->getFromResultSet($data);
            $values[] = [$object->getLink(), htmlspecialchars(Dropdown::getDropdownName('glpi_entities', $data['entities_id']), ENT_QUOTES, 'UTF-8', false), (int) $data['tag']];
            $selection[] = sprintf('item[%s][%s]', self::class, $data['assocID']);
        }
        $table_id = self::class . '_ipnetworks_id_' . $ID;
        $table = [
            'id' => $table_id,
            'state_key' => $table_id,
            'fields' => [__('Name'), Entity::getTypeName(1), __('ID TAG')],
            'values' => $values,
            'pageSize' => (int) ($_SESSION['glpilist_limit'] ?? 15),
        ];
        if ($canedit) {
            Html::showMassiveActions([
                'container' => $table_id,
                'num_displayed' => min((int) ($_SESSION['glpilist_limit'] ?? 15), count($values)),
                'itemtype' => self::class,
                'display_arrow' => false,
            ]);
            $table['massive_action'] = $selection;
        }
        renderTwigTemplate('table.twig', $table);
    }


    /**
     * @param $portID
    **/
    public static function getVlansForIPNetwork($portID)
    {
        global $DB;

        $vlans = [];
        $iterator = $DB->request([
           'SELECT' => 'vlans_id',
           'FROM'   => self::getTable(),
           'WHERE'  => ['ipnetworks_id' => $portID]
        ]);
        while ($data = $iterator->next()) {
            $vlans[$data['vlans_id']] = $data['vlans_id'];
        }

        return $vlans;
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            $nb = 0;
            switch ($item->getType()) {
                case 'IPNetwork':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb =  countElementsInTable(
                            $this->getTable(),
                            ['ipnetworks_id' => $item->getID()]
                        );
                    }
                    return self::createTabEntry(Vlan::getTypeName(), $nb);
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == 'IPNetwork') {
            self::showForIPNetwork($item);
        }
        return true;
    }
}
