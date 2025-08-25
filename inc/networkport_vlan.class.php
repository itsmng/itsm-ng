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


class NetworkPort_Vlan extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1          = 'NetworkPort';
    public static $items_id_1          = 'networkports_id';

    public static $itemtype_2          = 'Vlan';
    public static $items_id_2          = 'vlans_id';
    public static $checkItem_2_Rights  = self::HAVE_VIEW_RIGHT_ON_ITEM;


    /**
     * @since 0.84
    **/
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
           'networkports_id' => $portID,
           'vlans_id'        => $vlanID
        ]);

        return $this->delete($this->fields);
    }


    /**
     * @param $port
     * @param $vlan
     * @param $tagged
    **/
    public function assignVlan($port, $vlan, $tagged)
    {
        $input = ['networkports_id' => $port,
                       'vlans_id'        => $vlan,
                       'tagged'          => $tagged];

        return $this->add($input);
    }

    /**
     * @param $port   NetworkPort object
    **/
    public static function showForNetworkPort(NetworkPort $port)
    {
        global $CFG_GLPI;

        $ID = $port->getID();
        if (!$port->can($ID, READ)) {
            return false;
        }

        $canedit = $port->canEdit($ID);
        $rand    = mt_rand();

        $request = self::getAdapter()->request([
           'SELECT'    => [
              'glpi_networkports_vlans.id as assocID',
              'glpi_networkports_vlans.tagged',
              'glpi_vlans.*'
           ],
           'FROM'      => 'glpi_networkports_vlans',
           'LEFT JOIN' => [
              'glpi_vlans'   => [
                 'ON' => [
                    'glpi_networkports_vlans'  => 'vlans_id',
                    'glpi_vlans'               => 'id'
                 ]
              ]
           ],
           'WHERE'     => ['networkports_id' => $ID]
        ]);
        $results = $request->fetchAllAssociative();
        $number = count($results);

        $vlans  = [];
        $used   = [];
        foreach ($results as $line) {
            $used[$line["id"]]       = $line["id"];
            $vlans[$line["assocID"]] = $line;
        }

        if ($canedit) {
            echo "<div class='firstbloc'>\n";
            echo "<form aria-label='VLAN' method='post' action='" . static::getFormURL() . "'>\n";
            echo "<table class='tab_cadre_fixe' aria-label='VLAN'>\n";
            echo "<tr><th colspan='4'>" . __('Associate a VLAN') . "</th></tr>";

            echo "<tr class='tab_bg_1'><td class='right'>";
            echo "<input type='hidden' name='networkports_id' value='$ID'>";
            Vlan::dropdown(['used' => $used]);
            echo "</td>";
            echo "<td class='right'>" . __('Tagged') . "</td>";
            echo "<td class='left'><input type='checkbox' name='tagged' value='1'></td>";
            echo "<td><input type='submit' name='add' value='" . _sx('button', 'Associate') .
                       "' class='submit'>";
            echo "</td></tr>\n";

            echo "</table>\n";
            Html::closeForm();
            echo "</div>\n";
        }

        echo "<div class='spaced'>";
        if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['num_displayed' => min($_SESSION['glpilist_limit'], $number),
                                         'container'     => 'mass' . __CLASS__ . $rand];
            Html::showMassiveActions($massiveactionparams);
        }
        echo "<table class='tab_cadre_fixehov' aria-label='VLAN Detail'>";

        $header_begin  = "<tr>";
        $header_top    = '';
        $header_bottom = '';
        $header_end    = '';
        if ($canedit && $number) {
            $header_top    .= "<th width='10'>";
            $header_top    .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
            $header_bottom .= "<th width='10'>";
            $header_bottom .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
        }
        $header_end .= "<th>" . __('Name') . "</th>";
        $header_end .= "<th>" . Entity::getTypeName(1) . "</th>";
        $header_end .= "<th>" . __('Tagged') . "</th>";
        $header_end .= "<th>" . __('ID TAG') . "</th>";
        $header_end .= "</tr>";
        echo $header_begin . $header_top . $header_end;

        $used = [];
        foreach ($vlans as $data) {
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
                echo "<td>";
                Html::showMassiveActionCheckBox(__CLASS__, $data["assocID"]);
                echo "</td>";
            }
            $name = $data["name"];
            if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                $name = sprintf(__('%1$s (%2$s)'), $name, $data["id"]);
            }
            echo "<td class='b'>
               <a href='" . Vlan::getFormURLWithID($data['id']) . "'>" . $name .
                 "</a>";
            echo "</td>";
            echo "<td>" . Dropdown::getDropdownName("glpi_entities", $data["entities_id"]);
            echo "</td><td>" . Dropdown::getYesNo($data["tagged"]) . "</td>";
            echo "<td>" . $data["tag"] . "</td>";
            echo "</tr>";
        }
        if ($number) {
            echo $header_begin . $header_top . $header_end;
        }
        echo "</table>";
        if ($canedit && $number) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
        }
        echo "</div>";
    }


    public static function showForVlan(Vlan $vlan)
    {
        global $CFG_GLPI;

        $ID = $vlan->getID();
        if (!$vlan->can($ID, READ)) {
            return false;
        }

        $canedit = $vlan->canEdit($ID);
        $rand    = mt_rand();

        $request = self::getAdapter()->request([
           'SELECT'    => [
              'glpi_networkports_vlans.id as assocID',
              'glpi_networkports_vlans.tagged',
              'glpi_networkports.*'
           ],
           'FROM'      => 'glpi_networkports_vlans',
           'LEFT JOIN' => [
              'glpi_networkports'   => [
                 'ON' => [
                    'glpi_networkports_vlans'  => 'networkports_id',
                    'glpi_networkports'        => 'id'
                 ]
              ]
           ],
           'WHERE'     => ['vlans_id' => $ID]
        ]);
        $results = $request->fetchAllAssociative();
        $number = count($results);

        $vlans  = [];
        $used   = [];
        // while ($line = $iterator->next()) {
        foreach ($results as $line) {
            $used[$line["id"]]       = $line["id"];
            $vlans[$line["assocID"]] = $line;
        }

        echo "<div class='spaced'>";
        if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['num_displayed' => min($_SESSION['glpilist_limit'], $number),
                                         'container'     => 'mass' . __CLASS__ . $rand];
            Html::showMassiveActions($massiveactionparams);
        }
        echo "<table class='tab_cadre_fixehov' aria-label='VLAN Detail'>";

        $header_begin  = "<tr>";
        $header_top    = '';
        $header_bottom = '';
        $header_end    = '';
        if ($canedit && $number) {
            $header_top    .= "<th width='10'>";
            $header_top    .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
            $header_bottom .= "<th width='10'>";
            $header_bottom .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
        }
        $header_end .= "<th>" . __('Name') . "</th>";
        $header_end .= "<th>" . Entity::getTypeName(1) . "</th>";
        $header_end .= "</tr>";
        echo $header_begin . $header_top . $header_end;

        $used = [];
        foreach ($vlans as $data) {
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
                echo "<td>";
                Html::showMassiveActionCheckBox(__CLASS__, $data["assocID"]);
                echo "</td>";
            }
            $name = $data["name"];
            if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                $name = sprintf(__('%1$s (%2$s)'), $name, $data["id"]);
            }
            echo "<td class='b'>
               <a href='" . NetworkPort::getFormURLWithID($data['id']) . "'>" . $name .
                 "</a>";
            echo "</td>";
            echo "<td>" . Dropdown::getDropdownName("glpi_entities", $data["entities_id"]);
            echo "</tr>";
        }
        if ($number) {
            echo $header_begin . $header_top . $header_end;
        }
        echo "</table>";
        if ($canedit && $number) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
        }
        echo "</div>";
    }
    /**
     * @param $portID
    **/
    public static function getVlansForNetworkPort($portID)
    {
        $vlans = [];
        $request = self::getAdapter()->request([
           'SELECT' => 'vlans_id',
           'FROM'   => 'glpi_networkports_vlans',
           'WHERE'  => ['networkports_id' => $portID]
        ]);
        $results = $request->fetchAllAssociative();
        foreach ($results as $data) {
            $vlans[$data['vlans_id']] = $data['vlans_id'];
        }

        return $vlans;
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            $nb = 0;
            switch ($item->getType()) {
                case 'NetworkPort':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable(
                            $this->getTable(),
                            ["networkports_id" => $item->getID()]
                        );
                    }
                    return self::createTabEntry(Vlan::getTypeName(), $nb);
                case 'Vlan':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable(
                            $this->getTable(),
                            ["vlans_id" => $item->getID()]
                        );
                    }
                    return self::createTabEntry(NetworkPort::getTypeName(), $nb);
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'NetworkPort':
                return self::showForNetworkPort($item);
            case 'Vlan':
                return self::showForVlan($item);
        }
        return true;
    }


    /**
     * @since 0.85
     *
     * @see CommonDBRelation::getRelationMassiveActionsSpecificities()
    **/
    public static function getRelationMassiveActionsSpecificities()
    {
        $specificities = parent::getRelationMassiveActionsSpecificities();

        // Set the labels for add_item and remove_item
        $specificities['button_labels']['add']    = _sx('button', 'Associate');
        $specificities['button_labels']['remove'] = _sx('button', 'Dissociate');

        return $specificities;
    }


    public static function showRelationMassiveActionsSubForm(MassiveAction $ma, $peer_number)
    {

        if ($ma->getAction() == 'add') {
            echo "<br><br>" . __('Tagged') . Html::getCheckbox(['name' => 'tagged']);
        }
    }


    public static function getRelationInputForProcessingOfMassiveActions(
        $action,
        CommonDBTM $item,
        array $ids,
        array $input
    ) {
        if ($action == 'add') {
            return ['tagged' => $input['tagged']];
        }
        return [];
    }
}
