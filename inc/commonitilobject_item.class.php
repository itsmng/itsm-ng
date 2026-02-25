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
 * CommonItilObject_Item Class
 *
 * Relation between CommonItilObject_Item and Items
 */
abstract class CommonItilObject_Item extends CommonDBRelation
{
    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'items_id':
                if (strpos((string) $values[$field], "_") !== false) {
                    $item_itemtype      = explode("_", (string) $values[$field]);
                    $values['itemtype'] = $item_itemtype[0];
                    $values[$field]     = $item_itemtype[1];
                }

                if (isset($values['itemtype'])) {
                    if (isset($options['comments']) && $options['comments']) {
                        $tmp = Dropdown::getDropdownName(
                            getTableForItemType($values['itemtype']),
                            $values[$field],
                            1
                        );
                        return sprintf(
                            __('%1$s %2$s'),
                            $tmp['name'],
                            Html::showToolTip($tmp['comment'], ['display' => false])
                        );
                    }
                    return Dropdown::getDropdownName(
                        getTableForItemType($values['itemtype']),
                        $values[$field]
                    );
                }
                break;
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;
        switch ($field) {
            case 'items_id':
                if (isset($values['itemtype']) && !empty($values['itemtype'])) {
                    $options['name']  = $name;
                    $options['value'] = $values[$field];
                    return Dropdown::show($values['itemtype'], $options);
                } else {
                    static::dropdownAllDevices($name, 0, 0);
                    return ' ';
                }
                break;
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }

    public static function dropdownAllDevices(
        $myname,
        $itemtype,
        $items_id = 0,
        $admin = 0,
        $users_id = 0,
        $entity_restrict = -1,
        $options = []
    ) {
        global $CFG_GLPI;

        $params = [static::$items_id_1 => 0,
                        'used'       => [],
                        'multiple'   => 0,
                        'rand'       => mt_rand()];

        foreach ($options as $key => $val) {
            $params[$key] = $val;
        }

        $rand = $params['rand'];

        if ($_SESSION["glpiactiveprofile"]["helpdesk_hardware"] == 0) {
            echo "<input type='hidden' name='$myname' value=''>";
            echo "<input type='hidden' name='items_id' value='0'>";
        } else {
            echo "<div id='tracking_all_devices$rand'>";
            if (
                $_SESSION["glpiactiveprofile"]["helpdesk_hardware"] & pow(
                    2,
                    Ticket::HELPDESK_ALL_HARDWARE
                )
            ) {
                // Display a message if view my hardware
                if (
                    $users_id
                    && ($_SESSION["glpiactiveprofile"]["helpdesk_hardware"] & pow(
                        2,
                        Ticket::HELPDESK_MY_HARDWARE
                    ))
                ) {
                    echo __('Or complete search') . "&nbsp;";
                }

                $types = static::$itemtype_1::getAllTypesForHelpdesk();
                $used = json_encode($params['used']);
                $inputs = [
                    [
                       'type' => 'select',
                       'id' => "dropdown_itemtype$rand",
                       'name' => 'itemtype',
                       'noLib' => true,
                       'values' => [($params[static::$items_id_1] > 0) ? Dropdown::EMPTY_VALUE : __('General')] + $types,
                       'col_lg' => 12,
                       'col_md' => 12,
                       'hooks' => [
                          'change' => <<<JS
                          const val = this.value;
                          $.ajax({
                             url: "{$CFG_GLPI['root_doc']}/ajax/dropdownTrackingDeviceType.php",
                             type: "POST",
                             data: {
                                itemtype: val,
                                entity_restrict: $entity_restrict,
                                admin: $admin,
                                used: {$used},
                                multiple: {$params['multiple']},
                             },
                             success: function(data) {
                                const jsonData = JSON.parse(data);
                                
                                const dropdown = document.getElementById(`dropdown_add_items_id$rand`);
                                dropdown.innerHTML = '';
                                
                                const defaultOption = document.createElement('option');
                                defaultOption.value = '';
                                defaultOption.text = '-- Sélectionner --';
                                dropdown.appendChild(defaultOption);
                                
                                for (const key in jsonData.results) {
                                   if (jsonData.results[key].children) {
                                      // add optgroup with options
                                      const optgroup = document.createElement('optgroup');
                                      optgroup.label = jsonData.results[key].label;
                                      dropdown.appendChild(optgroup);
                                      for (const child of jsonData.results[key].children) {
                                         const option = document.createElement('option');
                                         option.value = child.id;
                                         option.text = child.text;
                                         optgroup.appendChild(option);
                                      }                                 
                                   } else {
                                      const option = document.createElement('option');
                                      option.value = jsonData.results[key].id;
                                      option.text = jsonData.results[key].text;
                                      dropdown.appendChild(option);
                                   }
                                }
                             }
                          });
                          JS,
                       ]
                    ],
                    [
                       'type' => 'select',
                       'id' => "dropdown_add_items_id$rand",
                       'name' => 'items_id',
                       'noLib' => true,
                       'col_lg' => 12,
                       'col_md' => 12,
                    ]
                 ];
                foreach ($inputs as $input) {
                    renderTwigTemplate('macros/wrappedInput.twig', ['title' => '', 'input' => $input]);
                }
                echo "</span>\n";
            }
            echo "</div>";
        }
        return $rand;
    }
}
