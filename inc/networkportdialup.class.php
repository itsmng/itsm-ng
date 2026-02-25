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

/// NetworkPortDialup class : dialup instantiation of NetworkPort. A dialup connexion also known as
/// point-to-point protocol allows connexion between to sites through specific connexion
/// @since 0.84
class NetworkPortDialup extends NetworkPortInstantiation
{
    public static function getTypeName($nb = 0)
    {
        return __('Connection by dial line - Dialup Port');
    }


    public function getInstantiationHTMLTableHeaders(
        HTMLTableGroup $group,
        HTMLTableSuperHeader $super,
        ?HTMLTableSuperHeader $internet_super = null,
        ?HTMLTableHeader $father = null,
        array $options = []
    ) {

        $header = $group->addHeader('Connected', __('Connected to'), $super);

        parent::getInstantiationHTMLTableHeaders($group, $super, $internet_super, $header, $options);
        return null;
    }


    public function getInstantiationHTMLTable(
        NetworkPort $netport,
        HTMLTableRow $row,
        ?HTMLTableCell $father = null,
        array $options = []
    ) {

        return $this->getInstantiationHTMLTableWithPeer($netport, $row, $father, $options);
    }


    public function showInstantiationForm(NetworkPort $netport, $options, $recursiveItems)
    {
        global $CFG_GLPI;

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

        return [
           $this->getTypeName() => [
              'visible' => true,
              'inputs' => [
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
              ]
           ]
        ];

        echo "<tr class='tab_bg_1'>";
        $this->showMacField($netport, $options);

        echo "<td>" . __('Connected to') . '</td><td>';
        self::showConnection($netport, true);
        echo "</td>";

        echo "</tr>";
    }
}
