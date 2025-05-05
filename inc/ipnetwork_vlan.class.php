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
        global $CFG_GLPI;

        $ID = $port->getID();
        if (!$port->can($ID, READ)) {
            return false;
        }

        $canedit = $port->canEdit($ID);
        $rand    = mt_rand();

        $request = self::getAdapter()->request([
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
        $results = $request->fetchAllAssociative();
        $number = count($results);
        foreach ($results as $line) {
            $used[$line["id"]]       = $line["id"];
            $vlans[$line["assocID"]] = $line;
        }

        if ($canedit) {
            echo "<div class='firstbloc'>\n";
            echo "<form aria-label='VLAN' method='post' action='" . static::getFormURL() . "'>\n";
            echo "<table class='tab_cadre_fixe' aria-label='Associate a VLAN'>\n";
            echo "<tr><th>" . __('Associate a VLAN') . "</th></tr>";

            echo "<tr class='tab_bg_1'><td class='center'>";
            echo "<input type='hidden' name='ipnetworks_id' value='$ID'>";
            Vlan::dropdown(['used' => $used]);
            echo "&nbsp;<input type='submit' name='add' value='" . _sx('button', 'Associate') .
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
        echo "<table class='tab_cadre_fixehov' aria-label='editable entity table'>";

        $header_begin  = "<tr>";
        $header_top    = '';
        $header_bottom = '';
        $header_end    = '';
        if ($canedit && $number) {
            $header_top    .= "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            $header_top    .= "</th>";
            $header_bottom .= "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            $header_bottom .= "</th>";
        }
        $header_end .= "<th>" . __('Name') . "</th>";
        $header_end .= "<th>" . Entity::getTypeName(1) . "</th>";
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
            echo "<td class='center b'>
               <a href='" . $CFG_GLPI["root_doc"] . "/front/vlan.form.php?id=" . $data["id"] . "'>" . $name .
                 "</a>";
            echo "</td>";
            echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $data["entities_id"]);
            echo "<td class='numeric'>" . $data["tag"] . "</td>";
            echo "</tr>";
        }
        if ($number) {
            echo $header_begin . $header_bottom . $header_end;
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
    public static function getVlansForIPNetwork($portID)
    {
        $vlans = [];
        $request = self::getAdapter()->request([
           'SELECT' => 'vlans_id',
           'FROM'   => self::getTable(),
           'WHERE'  => ['ipnetworks_id' => $portID]
        ]);
        while ($data = $request->fetchAssociative()) {
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
