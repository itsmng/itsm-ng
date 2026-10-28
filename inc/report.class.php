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
        echo '<div class="mb-4"><label class="form-label d-block">' . __('Select the report you want to generate') . '</label>';

        $selected = -1;
        $values   = [$CFG_GLPI["root_doc"] . '/front/report.php' => Dropdown::EMPTY_VALUE];

        foreach ($report_list as $val => $data) {
            $name          = $data['name'];
            $file          = $data['file'];
            $key           = $CFG_GLPI["root_doc"] . "/front/" . $file;
            $values[$key]  = $name;
            if (stripos((string) $_SERVER['REQUEST_URI'], $key) !== false) {
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
                    if (stripos((string) $_SERVER['REQUEST_URI'], $file) !== false) {
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
        echo '</div>';
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
        $rows = [];

        // 1. Get counts of itemtype
        $items     = $CFG_GLPI["asset_types"];

        $linkitems = $CFG_GLPI['directconnect_types'];



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

            $result = $DB->request($criteria)->next();
            $number = (int)$result['cpt'];

            $rows[] = [$itemtype::getTypeName(Session::getPluralNumber()), __('Total'), $number];
        }



        // 2. Get some more number data (operating systems per computer)
        $iterator = $DB->request([
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

        while ($data = $iterator->next()) {
            if (empty($data['name'])) {
                $data['name'] = Dropdown::EMPTY_VALUE;
            }
            $rows[] = [OperatingSystem::getTypeName(1), htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8', false), $data['count']];
        }

        // Get counts of types

        $val   = array_flip($items);
        $items = array_flip($val);

        foreach ($items as $itemtype) {


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

            $iterator = $DB->request($criteria);
            while ($data = $iterator->next()) {
                if (empty($data['name'])) {
                    $data['name'] = Dropdown::EMPTY_VALUE;
                }
                $rows[] = [$itemtype::getTypeName(Session::getPluralNumber()), htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8', false), $data['count']];
            }
        }
        renderTwigTemplate('table.twig', ['id' => 'defaultReport', 'state_key' => 'Report_default',
            'pageSize' => (int) ($_SESSION['glpilist_limit'] ?? 15),
            'fields' => [_n('Type', 'Types', 1), __('Name'), _x('quantity', 'Number')], 'values' => $rows]);
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

        $iterator = $DB->request($criteria);

        $fields = [];
        if (!empty($extra)) {
            $fields[] = $extra;
        }
        foreach ([_n('Device type', 'Device types', 1), __('Device name'), __('Port Number'), NetworkPort::getTypeName(1), __('MAC address'), IPAddress::getTypeName(0)] as $label) {
            $fields[] = __('Device 1') . ' - ' . $label;
        }
        foreach ([NetworkPort::getTypeName(1), __('MAC address'), IPAddress::getTypeName(0), _n('Device type', 'Device types', 1), __('Device name')] as $label) {
            $fields[] = __('Device 2') . ' - ' . $label;
        }
        $rows = [];
        $escape = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8', false);
        foreach ($iterator as $line) {
            $first = $line['itemtype_2'] === 'NetworkEquipment' ? 2 : 1;
            $second = $first === 1 ? 2 : 1;
            $row = [];
            if (!empty($extra)) {
                $row[] = $escape(($line['extra'] ?? '') ?: NOT_AVAILABLE);
            }
            foreach ([$first, $second] as $side) {
                $type = $line['itemtype_' . $side];
                $name = NOT_AVAILABLE;
                $typeName = NOT_AVAILABLE;
                if ($type && ($item = getItemForItemtype($type))) {
                    $typeName = $item->getTypeName(1);
                    if ($item->getFromDB($line['items_id_' . $side])) {
                        $name = $item->getName() ?: NOT_AVAILABLE;
                    }
                }
                if ($side === $first) {
                    $row[] = $escape($typeName);
                    $row[] = $escape($name);
                    $row[] = $escape($line['logical_' . $side] ?: NOT_AVAILABLE);
                }
                foreach (['port_', 'mac_', 'ip_'] as $field) {
                    $row[] = $escape($line[$field . $side] ?: NOT_AVAILABLE);
                }
                if ($side === $second) {
                    $row[] = $escape($typeName);
                    $row[] = $escape($name);
                }
            }
            $rows[] = $row;
        }
        $tableId = 'networkReport_' . md5(serialize([$from, $joincrit, $where, $extra]));
        renderTwigTemplate('table.twig', ['id' => $tableId, 'state_key' => $tableId,
            'pageSize' => (int) ($_SESSION['glpilist_limit'] ?? 15), 'fields' => $fields, 'values' => $rows]);

    }


    /**
     * @since 0.85
     *
     * @see commonDBTM::getRights()
    **/
    public static function showInventoryRows($iterator, $itemtype, $context)
    {
        $multi = Session::isMultiEntitiesMode();
        $fields = [_n('Type', 'Types', 1), __('Name'), __('Deleted')];
        if ($multi) {
            $fields[] = Entity::getTypeName(1);
        }
        $fields = array_merge($fields, [Location::getTypeName(1), __('Date of purchase'), __('Warranty expiration date'),
            ContractType::getTypeName(1), __('Start date'), __('End date')]);
        $rows = [];
        foreach ($iterator as $data) {
            $row = [$itemtype::getTypeName(1), htmlspecialchars($data['itemname'] ?: NOT_AVAILABLE, ENT_QUOTES, 'UTF-8', false),
                Dropdown::getYesNo($data['itemdeleted'] ?? 0)];
            if ($multi) {
                $row[] = htmlspecialchars($data['entname'] ?? '', ENT_QUOTES, 'UTF-8', false);
            }
            $row[] = htmlspecialchars(($data['location'] ?? '') ?: NOT_AVAILABLE, ENT_QUOTES, 'UTF-8', false);
            $row[] = !empty($data['buy_date']) ? Html::convDate($data['buy_date']) : NOT_AVAILABLE;
            $row[] = !empty($data['buy_date']) && !empty($data['warranty_duration'])
                ? Infocom::getWarrantyExpir($data['buy_date'], $data['warranty_duration']) : NOT_AVAILABLE;
            $row[] = htmlspecialchars(($data['type'] ?? '') ?: NOT_AVAILABLE, ENT_QUOTES, 'UTF-8', false);
            $row[] = !empty($data['begin_date']) ? Html::convDate($data['begin_date']) : NOT_AVAILABLE;
            $row[] = !empty($data['begin_date']) && !empty($data['duration'])
                ? Infocom::getWarrantyExpir($data['begin_date'], $data['duration']) : NOT_AVAILABLE;
            $rows[] = $row;
        }
        renderTwigTemplate('table.twig', ['id' => $context . '_' . $itemtype, 'state_key' => $context . '_' . $itemtype,
            'pageSize' => (int) ($_SESSION['glpilist_limit'] ?? 15), 'fields' => $fields, 'values' => $rows]);
    }


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
