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
 * Budget class
 */
class Budget extends CommonDropdown
{
    use Glpi\Features\Clonable;

    // From CommonDBTM
    public $dohistory           = true;

    public static $rightname           = 'budget';
    protected $usenotepad       = true;

    public $can_be_translated = false;

    public function getCloneRelations(): array
    {
        return [
            Document_Item::class
        ];
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Budget', 'Budgets', $nb);
    }

    public function title()
    {
        return '';
    }

    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab(__CLASS__, $ong, $options);
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab('KnowbaseItem_Item', $ong, $options);
        $this->addStandardTab('Link', $ong, $options);
        $this->addStandardTab('Notepad', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            switch ($item->getType()) {
                case __CLASS__:
                    return [1 => __('Main'),
                        2 => _n('Item', 'Items', Session::getPluralNumber())];
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == __CLASS__) {
            switch ($tabnum) {
                case 1:
                    $item->showValuesByEntity();
                    break;

                case 2:
                    $item->showItems();
                    break;
            }
        }
        return true;
    }


    /**
     * Print the contact form
     *
     * @param integer $ID      Integer ID of the item
     * @param array  $options  Array of possible options:
     *     - target for the Form
     *     - withtemplate : template or basic item
     *
     * @return void|boolean (display) Returns false if there is a rights error.
     **/
    public function showForm($ID, $options = [])
    {

        $rowspan = 3;
        if ($ID > 0) {
            $rowspan++;
        }
        $form = [
            'action' => Toolbox::getItemTypeFormURL('budget'),
            'itemtype' => self::class,
            'content' => [
                '' => [
                    'visible' => true,
                    'inputs' => [
                        $this->isNewID($ID) ? [] : [
                            'type' => 'hidden',
                            'name' => 'id',
                            'value' => $ID
                        ],
                        __('Name') => [
                            'name' => 'name',
                            'type' => 'text',
                            'value' => $this->fields['name'],
                        ],
                        _x('price', 'Value') => [
                            'name' => 'value',
                            'type' => 'number',
                            'value' => $this->fields['value'],
                        ],
                        __('Type') => [
                            'name' => 'budgettypes_id',
                            'type' => 'select',
                            'values' => getOptionForItems(BudgetType::class),
                            'value' => $this->fields['budgettypes_id'],
                            'actions' => getItemActionButtons(['info', 'add'], "budgettype"),
                        ],
                        __('Start date') => [
                            'name' => 'begin_date',
                            'type'  => 'date',
                            'value' => $this->fields['begin_date'],
                        ],
                        __('End date') => [
                            'name' => 'end_date',
                            'type'  => 'date',
                            'value' => $this->fields['end_date'],
                        ],
                        __('Location') => [
                            'name' => 'locations_id',
                            'type' => 'select',
                            'values' => getOptionForItems("Location"),
                            'value' => $this->fields['locations_id'],
                            'actions' => getItemActionButtons(['info', 'add'], "location"),
                        ],
                        __('Comments') => [
                            'name' => 'comment',
                            'type' => 'textarea',
                            'value' => $this->fields['comment'],
                        ]
                    ]
                ]
            ]
        ];

        renderTwigForm($form, '', $this->fields);

        return true;
    }


    public function prepareInputForAdd($input)
    {

        if (isset($input["id"]) && ($input["id"] > 0)) {
            $input["_oldID"] = $input["id"];
        }
        unset($input['id']);
        unset($input['withtemplate']);

        return $input;
    }


    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id'                 => 'common',
            'name'               => __('Characteristics')
        ];

        $tab[] = [
            'id'                 => '1',
            'table'              => $this->getTable(),
            'field'              => 'name',
            'name'               => __('Name'),
            'datatype'           => 'itemlink',
            'massiveaction'      => false,
            'autocomplete'       => true,
        ];

        $tab[] = [
            'id'                 => '2',
            'table'              => $this->getTable(),
            'field'              => 'id',
            'name'               => __('ID'),
            'massiveaction'      => false,
            'datatype'           => 'number'
        ];

        $tab[] = [
            'id'                 => '19',
            'table'              => $this->getTable(),
            'field'              => 'date_mod',
            'name'               => __('Last update'),
            'datatype'           => 'datetime',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '121',
            'table'              => $this->getTable(),
            'field'              => 'date_creation',
            'name'               => __('Creation date'),
            'datatype'           => 'datetime',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '4',
            'table'              => 'glpi_budgettypes',
            'field'              => 'name',
            'name'               => _n('Type', 'Types', 1),
            'datatype'           => 'dropdown'
        ];

        $tab[] = [
            'id'                 => '5',
            'table'              => $this->getTable(),
            'field'              => 'begin_date',
            'name'               => __('Start date'),
            'datatype'           => 'date'
        ];

        $tab[] = [
            'id'                 => '6',
            'table'              => $this->getTable(),
            'field'              => 'end_date',
            'name'               => __('End date'),
            'datatype'           => 'date'
        ];

        $tab[] = [
            'id'                 => '7',
            'table'              => $this->getTable(),
            'field'              => 'value',
            'name'               => _x('price', 'Value'),
            'datatype'           => 'decimal'
        ];

        $tab[] = [
            'id'                 => '16',
            'table'              => $this->getTable(),
            'field'              => 'comment',
            'name'               => __('Comments'),
            'datatype'           => 'text'
        ];

        $tab[] = [
            'id'                 => '50',
            'table'              => $this->getTable(),
            'field'              => 'template_name',
            'name'               => __('Template name'),
            'datatype'           => 'text',
            'massiveaction'      => false,
            'nosearch'           => true,
            'nodisplay'          => true,
            'autocomplete'       => true,
        ];

        $tab[] = [
            'id'                 => '80',
            'table'              => 'glpi_entities',
            'field'              => 'completename',
            'name'               => Entity::getTypeName(1),
            'massiveaction'      => false,
            'datatype'           => 'dropdown'
        ];

        $tab[] = [
            'id'                 => '86',
            'table'              => $this->getTable(),
            'field'              => 'is_recursive',
            'name'               => __('Child entities'),
            'datatype'           => 'bool'
        ];

        // add objectlock search options
        $tab = array_merge($tab, ObjectLock::rawSearchOptionsToAdd(get_class($this)));
        $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        return $tab;
    }


    /**
     * Print the HTML array of Items on a budget
     *
     * @return void
     **/
    public function showItems()
    {
        global $DB;

        $budgets_id = $this->fields['id'];

        if (!$this->can($budgets_id, READ)) {
            return false;
        }

        // $iterator = $DB->request([
            $iterator = $this::getAdapter()->request([
            'SELECT'          => 'itemtype',
            'DISTINCT'        => true,
            'FROM'            => 'glpi_infocoms',
            'WHERE'           => [
                'budgets_id'   => $budgets_id,
                'NOT'          => ['itemtype' => ['ConsumableItem', 'CartridgeItem', 'Software']]
            ],
            'ORDER'           => 'itemtype'
        ]);

        // $number = count($iterator);
        $number = count(iterator_to_array($iterator));
        //

        echo "<div class='spaced'><table class='tab_cadre_fixe' aria-label='Associated Items Table'>";
        echo "<tr><th colspan='2'>";
        Html::printPagerForm();
        echo "</th><th colspan='4'>";
        if ($number == 0) {
            echo __('No associated item');
        } else {
            echo _n('Associated item', 'Associated items', $number);
        }
        echo "</th></tr>";

        echo "<tr><th>" . _n('Type', 'Types', 1) . "</th>";
        echo "<th>" . Entity::getTypeName(1) . "</th>";
        echo "<th>" . __('Name') . "</th>";
        echo "<th>" . __('Serial number') . "</th>";
        echo "<th>" . __('Inventory number') . "</th>";
        echo "<th>" . _x('price', 'Value') . "</th>";
        echo "</tr>";

        $num       = 0;
        $itemtypes = [];
        while ($row = $iterator->next()) {
            $itemtypes[] = $row['itemtype'];
        }
        $itemtypes[] = 'Contract';
        $itemtypes[] = 'Ticket';
        $itemtypes[] = 'Problem';
        $itemtypes[] = 'Change';
        $itemtypes[] = 'Project';

        foreach ($itemtypes as $itemtype) {
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }

            if ($item->canView()) {
                switch ($itemtype) {
                    case 'Contract':
                        $criteria = [
                            'SELECT'       => [
                                $item->getTable() . '.id',
                                $item->getTable() . '.entities_id',
                                'SUM' => 'glpi_contractcosts.cost AS value'
                            ],
                            'FROM'         => 'glpi_contractcosts',
                            'INNER JOIN'   => [
                                $item->getTable() => [
                                    'ON' => [
                                        $item->getTable()    => 'id',
                                        'glpi_contractcosts' => 'contracts_id'
                                    ]
                                ]
                            ],
                            'WHERE'        => [
                                'glpi_contractcosts.budgets_id'     => $budgets_id,
                                $item->getTable() . '.is_template'  => 0
                            ] + getEntitiesRestrictCriteria($item->getTable()),
                            'GROUPBY'      => [
                                $item->getTable() . '.id',
                                $item->getTable() . '.entities_id'
                            ],
                            'ORDERBY'      => [
                                $item->getTable() . '.entities_id',
                                $item->getTable() . '.name'
                            ]
                        ];
                        break;

                    case 'Ticket':
                    case 'Problem':
                    case 'Change':
                        $costtable = getTableForItemType($item->getType() . 'Cost');

                        $sum = new QueryExpression(
                            "SUM(" . $DB->quoteName("$costtable.actiontime") . " * " . $DB->quoteName("$costtable.cost_time") . "/" . HOUR_TIMESTAMP . "
                        + " . $DB->quoteName("$costtable.cost_fixed") . "
                        + " . $DB->quoteName("$costtable.cost_material") . ") AS " . $DB->quoteName('value')
                        );
                        $criteria = [
                            'SELECT'       => [
                                $item->getTable() . '.id',
                                $item->getTable() . '.entities_id',
                                $sum
                            ],
                            'FROM'         => $costtable,
                            'INNER JOIN'   => [
                                $item->getTable() => [
                                    'ON' => [
                                        $item->getTable()    => 'id',
                                        $costtable           => $item->getForeignKeyField()
                                    ]
                                ]
                            ],
                            'WHERE'        => [
                                $costtable . '.budgets_id' => $budgets_id
                            ] + getEntitiesRestrictCriteria($item->getTable()),
                            'GROUPBY'      => [
                                $item->getTable() . '.id',
                                $item->getTable() . '.entities_id'
                            ],
                            'ORDERBY'      => [
                                $item->getTable() . '.entities_id',
                                $item->getTable() . '.name'
                            ]
                        ];
                        break;

                    case 'Project':
                        $criteria = [
                            'SELECT'       => [
                                $item->getTable() . '.id',
                                $item->getTable() . '.entities_id',
                                'SUM' => 'glpi_projectcosts.cost AS value'
                            ],
                            'FROM'         => 'glpi_projectcosts',
                            'INNER JOIN'   => [
                                $item->getTable() => [
                                    'ON' => [
                                        $item->getTable()    => 'id',
                                        'glpi_projectcosts'  => 'projects_id'
                                    ]
                                ]
                            ],
                            'WHERE'        => [
                                'glpi_projectcosts.budgets_id'  => $budgets_id
                            ] + getEntitiesRestrictCriteria($item->getTable()),
                            'GROUPBY'      => [
                                $item->getTable() . '.id',
                                $item->getTable() . '.entities_id'
                            ],
                            'ORDERBY'      => [
                                $item->getTable() . '.entities_id',
                                $item->getTable() . '.name'
                            ]
                        ];
                        break;

                    case 'Cartridge':
                        $criteria = [
                            'SELECT'       => [
                                $item->getTable() . '.*',
                                'glpi_cartridgeitems.name',
                                'glpi_infocoms.value'
                            ],
                            'FROM'         => 'glpi_infocoms',
                            'INNER JOIN'   => [
                                $item->getTable() => [
                                    'ON' => [
                                        $item->getTable() => 'id',
                                        'glpi_infocoms'   => 'items_id'
                                    ]
                                ],
                                'glpi_cartridgeitems'   => [
                                    'ON' => [
                                        $item->getTable()       => 'cartridgeitems_id',
                                        'glpi_cartridgeitems'   => 'id'
                                    ]
                                ]
                            ],
                            'WHERE'        => [
                                'glpi_infocoms.itemtype'   => $itemtype,
                                'glpi_infocoms.budgets_id' => $budgets_id
                            ] + getEntitiesRestrictCriteria($item->getTable()),
                            'ORDERBY'      => [
                                'entities_id',
                                'glpi_cartridgeitems.name'
                            ]
                        ];
                        break;

                    case 'Consumable':
                        $criteria = [
                            'SELECT'       => [
                                $item->getTable() . '.*',
                                'glpi_consumableitems.name',
                                'glpi_infocoms.value'
                            ],
                            'FROM'         => 'glpi_infocoms',
                            'INNER JOIN'   => [
                                $item->getTable() => [
                                    'ON' => [
                                        $item->getTable() => 'id',
                                        'glpi_infocoms'   => 'items_id'
                                    ]
                                ],
                                'glpi_consumableitems'   => [
                                    'ON' => [
                                        $item->getTable()       => 'consumableitems_id',
                                        'glpi_consumableitems'  => 'id'
                                    ]
                                ]
                            ],
                            'WHERE'        => [
                                'glpi_infocoms.itemtype'   => $itemtype,
                                'glpi_infocoms.budgets_id' => $budgets_id
                            ] + getEntitiesRestrictCriteria($item->getTable()),
                            'ORDERBY'      => [
                                'entities_id',
                                'glpi_consumableitems.name'
                            ]
                        ];
                        break;

                    default:
                        $criteria = [
                            'SELECT'       => [
                                $item->getTable() . '.*',
                                'glpi_infocoms.value',
                            ],
                            'FROM'         => 'glpi_infocoms',
                            'INNER JOIN'   => [
                                $item->getTable() => [
                                    'ON' => [
                                        $item->getTable() => 'id',
                                        'glpi_infocoms'   => 'items_id'
                                    ]
                                ]
                            ],
                            'WHERE'        => [
                                'glpi_infocoms.itemtype'            => $itemtype,
                                'glpi_infocoms.budgets_id'          => $budgets_id
                            ] + getEntitiesRestrictCriteria($item->getTable()),
                            'ORDERBY'      => [
                                $item->getTable() . '.entities_id'
                            ]
                        ];
                        if ($item->maybeTemplate()) {
                            $criteria['WHERE'][$item->getTable() . '.is_template'] = 0;
                        }

                        if ($item instanceof Item_Devices) {
                            $criteria['ORDERBY'][] = $item->getTable() . '.itemtype';
                        } else {
                            $criteria['ORDERBY'][] = $item->getTable() . '.name';
                        }
                        break;
                }

                // $iterator = $DB->request($criteria);
                $iterator = $this::getAdapter()->request($criteria);
                // $nb = count($iterator);
                $nb = count(iterator_to_array($iterator));
                if ($nb > $_SESSION['glpilist_limit']) {
                    echo "<tr class='tab_bg_1'>";
                    $name = $item->getTypeName($nb);
                    //TRANS: %1$s is a name, %2$s is a number
                    echo "<td class='center'>" . sprintf(__('%1$s: %2$s'), $name, $nb) . "</td>";
                    echo "<td class='center' colspan='2'>";

                    $opt = ['order'      => 'ASC',
                        'is_deleted' => 0,
                        'reset'      => 'reset',
                        'start'      => 0,
                        'sort'       => 80,
                        'criteria'   => [0 => ['value'      => '$$$$' . $budgets_id,
                        'searchtype' => 'contains',
                        'field'      => 50]]];

                    echo "<a href='" . $item->getSearchURL() . "?" . Toolbox::append_params($opt) . "'>" .
                        __('Device list') . "</a></td>";
                    echo "<td class='center'>-</td><td class='center'>-</td><td class='center'>-" .
                        "</td></tr>";
                } elseif ($nb) {
                    for ($prem = true; $data = $iterator->next(); $prem = false) {
                        $name = NOT_AVAILABLE;
                        if ($item->getFromDB($data["id"])) {
                            if ($item instanceof Item_Devices) {
                                $tmpitem = new $item::$itemtype_2();
                                if ($tmpitem->getFromDB($data[$item::$items_id_2])) {
                                    $name = $tmpitem->getLink(['additional' => true]);
                                }
                            } else {
                                $name = $item->getLink(['additional' => true]);
                            }
                        }
                        echo "<tr class='tab_bg_1'>";
                        if ($prem) {
                            $typename = $item->getTypeName($nb);
                            echo "<td class='center top' rowspan='$nb'>" .
                                ($nb > 1 ? sprintf(__('%1$s: %2$s'), $typename, $nb) : $typename) . "</td>";
                        }
                        echo "<td class='center'>" . Dropdown::getDropdownName(
                            "glpi_entities",
                            $data["entities_id"]
                        );
                        echo "</td><td class='center";
                        echo(isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                        echo ">" . $name . "</td>";
                        echo "<td class='center'>" . (isset($data["serial"]) ? "" . $data["serial"] . "" : "-");
                        echo "</td>";
                        echo "<td class='center'>" .
                            (isset($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-") . "</td>";
                        echo "<td class='center'>" .
                            (isset($data["value"]) ? "" . Html::formatNumber($data["value"], true) . ""
                            : "-");

                        echo "</td></tr>";
                    }
                }
                $num += $nb;
            }
        }

        if ($num > 0) {
            echo "<tr class='tab_bg_2'>";
            echo "<td class='center b'>" . sprintf(__('%1$s = %2$s'), __('Total'), $num) . "</td>";
            echo "<td colspan='5'>&nbsp;</td></tr> ";
        }
        echo "</table></div>";
    }


    /**
     * Print the HTML array of value consumed for a budget
     *
     * @return void
     **/
    public function showValuesByEntity()
    {
        global $DB;

        $budgets_id = $this->fields['id'];

        if (!$this->can($budgets_id, READ)) {
            return false;
        }

        $types_iterator = Infocom::getTypes(
            [
                'budgets_id' => $budgets_id
            ] + getEntitiesRestrictCriteria('glpi_infocoms', 'entities_id')
        );

        $total               = 0;
        $totalbytypes        = [];

        $itemtypes           = [];

        $entities_values     = [];
        $entitiestype_values = [];
        $found_types         = [];

        while ($types = $types_iterator->next()) {
            $itemtypes[] = $types['itemtype'];
        }

        $itemtypes[] = 'Contract';
        $itemtypes[] = 'Ticket';
        $itemtypes[] = 'Problem';
        $itemtypes[] = 'Project';
        $itemtypes[] = 'Change';

        foreach ($itemtypes as $itemtype) {
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }

            $table = getTableForItemType($itemtype);
            switch ($itemtype) {
                case 'Contract':
                    $criteria = [
                        'SELECT'       => [
                            $table . '.entities_id',
                            'SUM' => 'glpi_contractcosts.cost AS sumvalue'
                        ],
                        'FROM'         => 'glpi_contractcosts',
                        'INNER JOIN'   => [
                            $table => [
                                'ON' => [
                                    $table               => 'id',
                                    'glpi_contractcosts' => 'contracts_id'
                                ]
                            ]
                        ],
                        'WHERE'        => [
                            'glpi_contractcosts.budgets_id'     => $budgets_id
                        ] + getEntitiesRestrictCriteria($table, 'entities_id'),
                        'GROUPBY'      => [
                            $table . '.entities_id'
                        ]
                    ];
                    break;

                case 'Project':
                    $costtable   = getTableForItemType($item->getType() . 'Cost');
                    $criteria = [
                        'SELECT'       => [
                            $table . '.entities_id',
                            'SUM' => 'glpi_projectcosts.cost AS sumvalue'
                        ],
                        'FROM'         => 'glpi_projectcosts',
                        'INNER JOIN'   => [
                            $table => [
                                'ON' => [
                                    $table               => 'id',
                                    'glpi_projectcosts'  => 'projects_id'
                                ]
                            ]
                        ],
                        'WHERE'        => [
                            'glpi_projectcosts.budgets_id'  => $budgets_id
                        ] + getEntitiesRestrictCriteria($table, 'entities_id'),
                        'GROUPBY'      => [
                            $item->getTable() . '.entities_id'
                        ]
                    ];
                    break;

                case 'Ticket':
                case 'Problem':
                case 'Change':
                    $costtable   = getTableForItemType($item->getType() . 'Cost');
                    $sum = new QueryExpression(
                        "SUM(" . $DB->quoteName("$costtable.actiontime") . " * " . $DB->quoteName("$costtable.cost_time") . "/" . HOUR_TIMESTAMP . "
                    + " . $DB->quoteName("$costtable.cost_fixed") . "
                    + " . $DB->quoteName("$costtable.cost_material") . ") AS " . $DB->quoteName('sumvalue')
                    );
                    $criteria = [
                        'SELECT'       => [
                            $item->getTable() . '.entities_id',
                            $sum
                        ],
                        'FROM'         => $costtable,
                        'INNER JOIN'   => [
                            $table => [
                                'ON' => [
                                    $table      => 'id',
                                    $costtable  => $item->getForeignKeyField()
                                ]
                            ]
                        ],
                        'WHERE'        => [
                            $costtable . '.budgets_id' => $budgets_id
                        ] + getEntitiesRestrictCriteria($table, 'entities_id'),
                        'GROUPBY'      => [
                            $item->getTable() . '.entities_id'
                        ]
                    ];
                    break;

                default:
                    $criteria = [
                        'SELECT'       => [
                            $table . '.entities_id',
                            'SUM' => 'glpi_infocoms.value AS sumvalue',
                        ],
                        'FROM'         => $table,
                        'INNER JOIN'   => [
                            'glpi_infocoms' => [
                                'ON' => [
                                    $table            => 'id',
                                    'glpi_infocoms'   => 'items_id'
                                ]
                            ]
                        ],
                        'WHERE'        => [
                            'glpi_infocoms.itemtype'            => $itemtype,
                            'glpi_infocoms.budgets_id'          => $budgets_id
                        ] + getEntitiesRestrictCriteria($table, 'entities_id'),
                        'GROUPBY'      => [
                            $table . '.entities_id'
                        ]
                    ];
                    if ($item->maybeTemplate()) {
                        $criteria['WHERE'][$table . '.is_template'] = 0;
                    }
                    break;
            }

            // $iterator = $DB->request($criteria);
            $iterator = $this::getAdapter()->request($criteria);

            // $nb = count($iterator);
            $nb = count(iterator_to_array($iterator));
            if ($nb) {
                $found_types[$itemtype]  = $item->getTypeName(1);
                $totalbytypes[$itemtype] = 0;
                //Store, for each entity, the budget spent
                while ($values = $iterator->next()) {
                    if (!isset($entities_values[$values['entities_id']])) {
                        $entities_values[$values['entities_id']] = 0;
                    }
                    if (!isset($entitiestype_values[$values['entities_id']][$itemtype])) {
                        $entitiestype_values[$values['entities_id']][$itemtype] = 0;
                    }
                    $entities_values[$values['entities_id']]                 += $values['sumvalue'];
                    $entitiestype_values[$values['entities_id']][$itemtype]  += $values['sumvalue'];
                    $total                                                   += $values['sumvalue'];
                    $totalbytypes[$itemtype]                                 += $values['sumvalue'];
                }
            }
        }

        $budget = new self();
        $budget->getFromDB($budgets_id);

        $colspan = count($found_types) + 2;
        echo "<div class='spaced'><table class='tab_cadre_fixehov' aria-label='Total Spent on the Budget Table'>";
        echo "<tr class='noHover'><th colspan='$colspan'>" . __('Total spent on the budget') . "</th></tr>";
        echo "<tr><th>" . Entity::getTypeName(1) . "</th>";
        if (count($found_types)) {
            foreach ($found_types as $type => $typename) {
                echo "<th>$typename</th>";
            }
        }
        echo "<th>" . __('Total') . "</th>";
        echo "</tr>";

        // get all entities ordered by names
        $allentities = getAllDataFromTable('glpi_entities', ['ORDER' => 'completename'], true);

        foreach (array_keys($allentities) as $entity) {
            if (isset($entities_values[$entity])) {
                echo "<tr class='tab_bg_1'>";
                echo "<td class='b'>" . Dropdown::getDropdownName('glpi_entities', $entity) . "</td>";
                if (count($found_types)) {
                    foreach ($found_types as $type => $typename) {
                        echo "<td class='numeric'>";
                        $typevalue = 0;
                        if (isset($entitiestype_values[$entity][$type])) {
                            $typevalue = $entitiestype_values[$entity][$type];
                        }
                        echo Html::formatNumber($typevalue);
                        echo "</td>";
                    }
                }

                echo "<td class='right b'>" . Html::formatNumber($entities_values[$entity]) . "</td>";
                echo "</tr>";
            }
        }
        if (count($found_types)) {
            echo "<tr class='tab_bg_1'>";
            echo "<td class='right b'>" . __('Total') . "</td>";
            foreach ($found_types as $type => $typename) {
                echo "<td class='numeric b'>";
                echo Html::formatNumber($totalbytypes[$type]);
                echo "</td>";
            }
            echo "<td class='numeric b'>" . Html::formatNumber($total) . "</td>";
            echo "</tr>";
        }
        echo "<tr class='tab_bg_1 noHover'><th colspan='$colspan'><br></th></tr>";
        echo "<tr class='tab_bg_1 noHover'>";
        echo "<td class='right' colspan='" . ($colspan - 1) . "'>" . __('Total spent on the budget') . "</td>";
        echo "<td class='numeric b'>" . Html::formatNumber($total) . "</td></tr>";
        if ($_SESSION['glpiactive_entity'] == $budget->fields['entities_id']) {
            echo "<tr class='tab_bg_1 noHover'>";
            echo "<td class='right' colspan='" . ($colspan - 1) . "'>" . __('Total remaining on the budget') .
                "</td>";
            echo "<td class='numeric b'>" . Html::formatNumber($budget->fields['value'] - $total) .
                "</td></tr>";
        }
        echo "</table></div>";
    }


    public static function getIcon()
    {
        return "fas fa-calculator";
    }
}
