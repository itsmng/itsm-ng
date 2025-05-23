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
 *  Report class
 *
 * @ since version 0.84
**/
class Report extends CommonGLPI
{
    protected static $notable = false;
    public static $rightname         = 'reports';


    public static function getTypeName($nb = 0)
    {
        return _n('Report', 'Reports', $nb);
    }


    /**
     * @see CommonGLPI::getMenuShorcut()
     *
     *  @since 0.85
    **/
    public static function getMenuShorcut()
    {
        return 'e';
    }


    /**
     * Show report title
    **/
    public static function title()
    {
        global $PLUGIN_HOOKS, $CFG_GLPI;

        // Report generation
        // Default Report included
        $report_list = [];
        $report_list["default"]["name"] = __('Default report');
        $report_list["default"]["file"] = "report.default.php";

        if (Contract::canView()) {
            // Rapport ajoute par GLPI V0.2
            $report_list["Contrats"]["name"] = __('By contract');
            $report_list["Contrats"]["file"] = "report.contract.php";
        }
        if (Infocom::canView()) {
            $report_list["Par_annee"]["name"] = __('By year');
            $report_list["Par_annee"]["file"] = "report.year.php";
            $report_list["Infocoms"]["name"]  = __('Hardware financial and administrative information');
            $report_list["Infocoms"]["file"]  = "report.infocom.php";
            $report_list["Infocoms2"]["name"] = __('Other financial and administrative information (licenses, cartridges, consumables)');
            $report_list["Infocoms2"]["file"] = "report.infocom.conso.php";
        }
        if (Session::haveRight("networking", READ)) {
            $report_list["Rapport prises reseau"]["name"] = __('Network report');
            $report_list["Rapport prises reseau"]["file"] = "report.networking.php";
        }
        if (Session::haveRight("reservation", READ)) {
            $report_list["reservation"]["name"] = __('Loan');
            $report_list["reservation"]["file"] = "report.reservation.php";
        }
        if (
            Computer::canView()
            || Monitor::canView()
            || Session::haveRight("networking", READ)
            || Peripheral::canView()
            || Printer::canView()
            || Phone::canView()
        ) {
            $report_list["state"]["name"] = __('Status');
            $report_list["state"]["file"] = "report.state.php";
        }
        //Affichage du tableau de presentation des stats
        echo "<table class='tab_cadre_fixe' aria-label='Statistics'>";
        echo "<tr><th colspan='2'>" . __('Select the report you want to generate') . "</th></tr>";
        echo "<tr class='tab_bg_1'><td class='center'>";

        $selected = -1;
        $values   = [$CFG_GLPI["root_doc"] . '/front/report.php' => Dropdown::EMPTY_VALUE];

        foreach ($report_list as $val => $data) {
            $name          = $data['name'];
            $file          = $data['file'];
            $key           = $CFG_GLPI["root_doc"] . "/front/" . $file;
            $values[$key]  = $name;
            if (stripos($_SERVER['REQUEST_URI'], $key) !== false) {
                $selected = $key;
            }
        }

        $names    = [];
        $optgroup = [];
        if (isset($PLUGIN_HOOKS["reports"]) && is_array($PLUGIN_HOOKS["reports"])) {
            foreach ($PLUGIN_HOOKS["reports"] as $plug => $pages) {
                if (!Plugin::isPluginActive($plug)) {
                    continue;
                }
                if (is_array($pages) && count($pages)) {
                    foreach ($pages as $page => $name) {
                        $names[$plug . '/' . $page] = ["name" => $name,
                                                        "plug" => $plug];
                        $optgroup[$plug] = Plugin::getInfo($plug, 'name');
                    }
                }
            }
            asort($names);
        }

        foreach ($optgroup as $opt => $title) {
            $group = $title;
            foreach ($names as $key => $val) {
                if ($opt == $val["plug"]) {
                    $file                  = $CFG_GLPI["root_doc"] . "/plugins/" . $key;
                    $values[$group][$file] = $val["name"];
                    if (stripos($_SERVER['REQUEST_URI'], $file) !== false) {
                        $selected = $file;
                    }
                }
            }
        }

        Dropdown::showFromArray(
            'statmenu',
            $values,
            ['on_change' => "window.location.href=this.options[this.selectedIndex].value",
                                      'value'     => $selected]
        );
        echo "</td>";
        echo "</tr>";
        echo "</table>";
    }


    /**
     * Show Default Report
     *
     * @since 0.84
    **/
    public static function showDefaultReport()
    {
        global $DB, $CFG_GLPI;

        // Title
        echo "<span class='big b'>GLPI " . Report::getTypeName(Session::getPluralNumber()) . "</span><br><br>";

        // 1. Get counts of itemtype
        $items     = $CFG_GLPI["asset_types"];

        $linkitems = $CFG_GLPI['directconnect_types'];

        echo "<table class='tab_cadrehov' aria-label='Show default report'>";

        foreach ($items as $itemtype) {
            $table_item = getTableForItemType($itemtype);
            $criteria = [
               'COUNT'  => 'cpt',
               'FROM'   => $table_item,
               'WHERE'  => [
                  "$table_item.is_deleted"   => 0,
                  "$table_item.is_template"  => 0
               ] + getEntitiesRestrictCriteria($table_item)
            ];

            if (in_array($itemtype, $linkitems)) {
                $criteria['LEFT JOIN'] = [
                   'glpi_computers_items' => [
                      'ON' => [
                         'glpi_computers_items'  => 'items_id',
                         $table_item             => 'id', [
                            'AND' => [
                               'glpi_computers_items.itemtype' => $itemtype
                            ]
                         ]
                      ]
                   ]
                ];
            }

            $result = config::getAdapter()->request($criteria)->fetchAssociative();
            $number = (int)$result['cpt'];

            echo "<tr class='tab_bg_2'><td>" . $itemtype::getTypeName(Session::getPluralNumber()) . "</td>";
            echo "<td class='numeric'>$number</td></tr>";
        }

        echo "<tr class='tab_bg_1'><td colspan='2' class='b'>" . OperatingSystem::getTypeName(1) . "</td></tr>";

        // 2. Get some more number data (operating systems per computer)
        $request = config::getAdapter()->request([
           'SELECT'    => [
              'COUNT' => '* AS count',
              'glpi_operatingsystems.name AS name'
           ],
           'FROM'      => 'glpi_items_operatingsystems',
           'LEFT JOIN' => [
              'glpi_operatingsystems' => [
                 'ON' => [
                    'glpi_items_operatingsystems' => 'operatingsystems_id',
                    'glpi_operatingsystems'       => 'id'
                 ]
              ]
           ],
           'WHERE'     => ['is_deleted' => 0],
           'GROUPBY'   => 'glpi_operatingsystems.name'
        ]);

        while ($data = $request->fetchAssociative()) {
            if (empty($data['name'])) {
                $data['name'] = Dropdown::EMPTY_VALUE;
            }
            echo "<tr class='tab_bg_2'><td>" . $data['name'] . "</td>";
            echo "<td class='numeric'>" . $data['count'] . "</td></tr>";
        }

        // Get counts of types

        $val   = array_flip($items);
        $items = array_flip($val);

        foreach ($items as $itemtype) {
            echo "<tr class='tab_bg_1'><td colspan='2' class='b'>" . $itemtype::getTypeName(Session::getPluralNumber()) .
                 "</td></tr>";

            $table_item = getTableForItemType($itemtype);
            $typeclass  = $itemtype . "Type";
            $type_table = getTableForItemType($typeclass);
            $typefield  = getForeignKeyFieldForTable(getTableForItemType($typeclass));

            $criteria = [
               'SELECT'    => [
                  'COUNT'  => '* AS count',
                  "$type_table.name AS name"
               ],
               'FROM'      => $table_item,
               'LEFT JOIN' => [
                  $type_table => [
                     'ON' => [
                        $table_item => $typefield,
                        $type_table => 'id'
                     ]
                  ]
               ],
               'WHERE'     => [
                  "$table_item.is_deleted"   => 0,
                  "$table_item.is_template"  => 0
               ] + getEntitiesRestrictCriteria($table_item),
               'GROUPBY'   => "$type_table.name"
            ];

            if (in_array($itemtype, $linkitems)) {
                $criteria['LEFT JOIN']['glpi_computers_items'] = [
                   'ON' => [
                      'glpi_computers_items'  => 'items_id',
                      $table_item             => 'id', [
                         'AND' => [
                            'glpi_computers_items.itemtype'  => $itemtype
                         ]
                      ]
                   ]
                ];
            }

            $request = config::getAdapter()->request($criteria);
            while ($data = $request->fetchAssociative()) {
                if (empty($data['name'])) {
                    $data['name'] = Dropdown::EMPTY_VALUE;
                }
                echo "<tr class='tab_bg_2'><td>" . $data['name'] . "</td>";
                echo "<td class='numeric'>" . $data['count'] . "</td></tr>";
            }
        }
        echo "</table>";
    }


    /**
     * Get report information
     *
     * @param string $from      From table
     * @param array  $joincrit  Join criteria
     * @param array  $where     Where clause
     * @param array  $select    Extra select clause
     * @param array  $leftjoin  Extra LEFT JOIN clause
     * @param array  $innerjoin Extra INNER JOIN clause
     * @param array  $order     Order clause
     * @param string $extra     ?
     *
     * @return void
     *
     * @since 10.0.0
    **/
    public static function reportForNetworkInformations(
        $from,
        array $joincrit,
        array $where = [],
        array $select = [],
        array $leftjoin = [],
        array $innerjoin = [],
        array $order = [],
        $extra = ''
    ) {
        global $DB;

        // This SQL request matches the NetworkPort, then its NetworkName and IPAddreses. It also
        //      match opposite NetworkPort, then its NetworkName and IPAddresses.
        // Results are groupes by NetworkPort. Then all IPs are concatenated by comma as separator.

        if (count($joincrit) === 3) {
            $andcrit = array_pop($joincrit);
            $andcrit['AND']['PORT_1.is_deleted'] = 0;
            $joincrit[] = $andcrit;
        } else {
            $joincrit[]['AND']['PORT_1.is_deleted'] = 0;
        }

        $criteria = [
           'SELECT'       => array_merge([
              'PORT_1.itemtype AS itemtype_1',
              'PORT_1.items_id AS items_id_1',
              'PORT_1.id AS id_1',
              'PORT_1.name AS port_1',
              'PORT_1.mac AS mac_1',
              'PORT_1.logical_number AS logical_1',
              new QueryExpression('GROUP_CONCAT(' . $DB->quoteName('ADDR_1.name') . ' SEPARATOR ' . $DB->quote(',') . ') AS ' . $DB->quoteName('ip_1')),
              'PORT_2.itemtype AS itemtype_2',
              'PORT_2.items_id AS items_id_2',
              'PORT_2.id AS id_2',
              'PORT_2.name AS port_2',
              'PORT_2.mac AS mac_2',
              new QueryExpression('GROUP_CONCAT(' . $DB->quoteName('ADDR_2.name') . ' SEPARATOR ' . $DB->quote(',') . ') AS ' . $DB->quoteName('ip_2'))
           ], $select),
           'FROM'         => $from,
           'INNER JOIN'   => $innerjoin + [
              'glpi_networkports AS PORT_1' => [
                 'ON' => $joincrit
              ]
           ],
           'LEFT JOIN'    => [
              'glpi_networknames AS NAME_1' => [
                 'ON'  => [
                    'PORT_1' => 'id',
                    'NAME_1' => 'items_id', [
                       'AND'    => [
                          'NAME_1.itemtype'    => 'NetworkPort',
                          'NAME_1.is_deleted'  => 0
                       ]
                    ]
                 ]
              ],
              'glpi_ipaddresses AS ADDR_1'  => [
                 'ON'  => [
                    'NAME_1' => 'id',
                    'ADDR_1' => 'items_id', [
                       'AND'    => [
                          'ADDR_1.itemtype'    => 'NetworkName',
                          'ADDR_1.is_deleted'  => 0
                       ]
                    ]
                 ]
              ],
              'glpi_networkports_networkports AS LINK'  => [
                 'ON'  => [
                    'LINK'   => 'networkports_id_1',
                    'PORT_1' => 'id', [
                       'OR'     => [
                          'LINK.networkports_id_2'   => new QueryExpression($DB->quoteName('PORT_1.id'))
                       ]
                    ]
                 ]
              ],
              'glpi_networkports AS PORT_2' => [
                 'ON'  => [
                    'PORT_2' => 'id',
                    new QueryExpression(
                        'IF(' . $DB->quoteName('LINK.networkports_id_1') . ' = ' . $DB->quoteName('PORT_1.id') . ', ' .
                          $DB->quoteName('LINK.networkports_id_2') . ', ' .
                          $DB->quoteName('LINK.networkports_id_1') . ')'
                    )
                 ]
              ],
              'glpi_networknames AS NAME_2' => [
               'ON'  => [
                    'PORT_2' => 'id',
                    'NAME_2' => 'items_id', [
                       'AND'    => [
                          'NAME_2.itemtype'     => 'NetworkPort',
                          'NAME_2.is_deleted'   => 0
                       ]
                    ]
               ]
              ],
              'glpi_ipaddresses AS ADDR_2'  => [
               'ON'  => [
                    'NAME_2' => 'id',
                    'ADDR_2' => 'items_id', [
                       'AND'    => [
                          'ADDR_2.itemtype'    => 'NetworkName',
                          'ADDR_2.is_deleted'  => 0
                       ]
                    ]
               ]
              ]
           ] + $leftjoin,
           'WHERE'        => $where,
           'GROUPBY'      => ['PORT_1.id']
        ];

        if (count($order)) {
            $criteria['ORDER'] = $order;
        }

        $request = config::getAdapter()->request($criteria);
        $results = $request->fetchAllAssociative();
        if (count($results)) {
            echo "<table class='tab_cadre_fixehov'aria-label='Devices'>";
            echo "<tr>";
            if (!empty($extra)) {
                echo "<td>&nbsp;</td>";
            }
            echo "<th colspan='5'>" . __('Device 1') . "</th>";
            echo "<th colspan='5'>" . __('Device 2') . "</th>";
            echo "</tr>\n";

            echo "<tr>";
            if (!empty($extra)) {
                echo "<th>$extra</th>";
            }
            echo "<th>" . _n('Device type', 'Device types', 1) . "</th>";
            echo "<th>" . __('Device name') . "</th>";
            echo "<th>" . __('Port Number') . "</th>";
            echo "<th>" . NetworkPort::getTypeName(1) . "</th>";
            echo "<th>" . __('MAC address') . "</th>";
            echo "<th>" . IPAddress::getTypeName(0) . "</th>";
            echo "<th>" . NetworkPort::getTypeName(1) . "</th>";
            echo "<th>" . __('MAC address') . "</th>";
            echo "<th>" . IPAddress::getTypeName(0) . "</th>";
            echo "<th>" . _n('Device type', 'Device types', 1) . "</th>";
            echo "<th>" . __('Device name') . "</th>";
            echo "</tr>\n";

            foreach ($results as $line) {
                echo "<tr class='tab_bg_1'>";

                // To ensure that the NetworkEquipment remain the first item, we test its type
                if ($line['itemtype_2'] == 'NetworkEquipment') {
                    $idx = 2;
                } else {
                    $idx = 1;
                }

                if (!empty($extra)) {
                    echo "<td>" . (empty($line['extra']) ? NOT_AVAILABLE : $line['extra']) . "</td>";
                }

                $itemtype = $line["itemtype_$idx"];
                if (!empty($itemtype)) {
                    echo "<td>" . $itemtype::getTypeName(1) . "</td>";
                    $item_name = '';
                    if ($item = getItemForItemtype($itemtype)) {
                        if ($item->getFromDB($line["items_id_$idx"])) {
                            $item_name = $item->getName();
                        }
                    }
                    echo "<td>" . (empty($item_name) ? NOT_AVAILABLE : $item_name) . "</td>";
                } else {
                    echo "<td> " . NOT_AVAILABLE . " </td>";
                    echo "<td> " . NOT_AVAILABLE . " </td>";
                }
                echo "<td>" . (empty($line["logical_$idx"]) ? NOT_AVAILABLE : $line["logical_$idx"]) . "</td>";
                echo "<td>" . (empty($line["port_$idx"]) ? NOT_AVAILABLE : $line["port_$idx"]) . "</td>";
                echo "<td>" . (empty($line["mac_$idx"]) ? NOT_AVAILABLE : $line["mac_$idx"]) . "</td>";
                echo "<td>" . (empty($line["ip_$idx"]) ? NOT_AVAILABLE : $line["ip_$idx"]) . "</td>";

                if ($idx == 1) {
                    $idx = 2;
                } else {
                    $idx = 1;
                }

                echo "<td>" . (empty($line["port_$idx"]) ? NOT_AVAILABLE : $line["port_$idx"]) . "</td>";
                echo "<td>" . (empty($line["mac_$idx"]) ? NOT_AVAILABLE : $line["mac_$idx"]) . "</td>";
                echo "<td>" . (empty($line["ip_$idx"]) ? NOT_AVAILABLE : $line["ip_$idx"]) . "</td>";
                $itemtype = $line["itemtype_$idx"];
                if (!empty($itemtype)) {
                    echo "<td>" . $itemtype::getTypeName(1) . "</td>";
                    $item_name = '';
                    if ($item = getItemForItemtype($itemtype)) {
                        if ($item->getFromDB($line["items_id_$idx"])) {
                            $item_name = $item->getName();
                        }
                    }
                    echo "<td>" . (empty($item_name) ? NOT_AVAILABLE : $item_name) . "</td>";
                } else {
                    echo "<td> " . NOT_AVAILABLE . " </td>";
                    echo "<td> " . NOT_AVAILABLE . " </td>";
                }

                echo "</tr>\n";
            }
            echo "</table><br><hr><br>";
        }
    }


    /**
     * @since 0.85
     *
     * @see commonDBTM::getRights()
    **/
    public function getRights($interface = 'central')
    {

        $values = [ READ => __('Read')];
        return $values;
    }


    public static function getIcon()
    {
        return "fas fa-file-medical-alt";
    }
}
