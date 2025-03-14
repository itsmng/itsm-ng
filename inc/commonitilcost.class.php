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

use itsmng\Timezone;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * CommonITILCost Class
 *
 * @since 0.85
**/
abstract class CommonITILCost extends CommonDBChild
{
    public $dohistory        = true;


    public static function getTypeName($nb = 0)
    {
        return _n('Cost', 'Costs', $nb);
    }


    public function getItilObjectItemType()
    {
        return str_replace('Cost', '', $this->getType());
    }


    /**
     * @see CommonGLPI::getTabNameForItem()
    **/
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        // can exists for template
        if (
            ($item->getType() == static::$itemtype)
            && static::canView()
        ) {
            $nb = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
                $nb = countElementsInTable(
                    $this->getTable(),
                    [$item->getForeignKeyField() => $item->getID()]
                );
            }
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
        }
        return '';
    }


    /**
     * @param $item            CommonGLPI object
     * @param $tabnum          (default 1)
     * @param $withtemplate    (default 0)
    **/
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        self::showForObject($item, $withtemplate);
        return true;
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
           'name'               => __('Title'),
           'searchtype'         => 'contains',
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
           'id'                 => '16',
           'table'              => $this->getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '12',
           'table'              => $this->getTable(),
           'field'              => 'begin_date',
           'name'               => __('Begin date'),
           'datatype'           => 'datetime'
        ];

        $tab[] = [
           'id'                 => '10',
           'table'              => $this->getTable(),
           'field'              => 'end_date',
           'name'               => __('End date'),
           'datatype'           => 'datetime'
        ];

        $tab[] = [
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'actiontime',
           'name'               => __('Duration'),
           'datatype'           => 'timestamp'
        ];

        $tab[] = [
           'id'                 => '14',
           'table'              => $this->getTable(),
           'field'              => 'cost_time',
           'name'               => __('Time cost'),
           'datatype'           => 'decimal'
        ];

        $tab[] = [
           'id'                 => '15',
           'table'              => $this->getTable(),
           'field'              => 'cost_fixed',
           'name'               => __('Fixed cost'),
           'datatype'           => 'decimal'
        ];

        $tab[] = [
           'id'                 => '19',
           'table'              => $this->getTable(),
           'field'              => 'cost_material',
           'name'               => __('Material cost'),
           'datatype'           => 'decimal'
        ];

        $tab[] = [
           'id'                 => '18',
           'table'              => 'glpi_budgets',
           'field'              => 'name',
           'name'               => Budget::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '80',
           'table'              => 'glpi_entities',
           'field'              => 'completename',
           'name'               => Entity::getTypeName(1),
           'massiveaction'      => false,
           'datatype'           => 'dropdown'
        ];

        return $tab;
    }


    public static function rawSearchOptionsToAdd()
    {
        global $DB;

        $tab = [];

        $tab[] = [
           'id'                 => 'cost',
           'name'               => _n('Cost', 'Costs', 1)
        ];

        $tab[] = [
           'id'                 => '48',
           'table'              => static::getTable(),
           'field'              => 'totalcost',
           'name'               => __('Total cost'),
           'datatype'           => 'decimal',
           'forcegroupby'       => true,
           'usehaving'          => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child'
           ],
           'computation'        =>
              '(SUM(' . $DB->quoteName('TABLE.actiontime') . ' * ' .
              $DB->quoteName('TABLE.cost_time') . '/' . HOUR_TIMESTAMP .
              ' + ' . $DB->quoteName('TABLE.cost_fixed') . ' + ' .
              $DB->quoteName('TABLE.cost_material') . ') / COUNT(' .
              $DB->quoteName('TABLE.id') . ')) * COUNT(DISTINCT ' .
              $DB->quoteName('TABLE.id') . ')',
           'nometa'             => true, // cannot GROUP_CONCAT a SUM
        ];

        $tab[] = [
           'id'                 => '42',
           'table'              => static::getTable(),
           'field'              => 'cost_time',
           'name'               => __('Time cost'),
           'datatype'           => 'decimal',
           'forcegroupby'       => true,
           'usehaving'          => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child'
           ],
           'computation'        =>
              '(SUM(' . $DB->quoteName('TABLE.actiontime') . ' * ' .
              $DB->quoteName('TABLE.cost_time') . '/' . HOUR_TIMESTAMP .
              ') / COUNT(' . $DB->quoteName('TABLE.id') . ')) * COUNT(DISTINCT ' .
              $DB->quoteName('TABLE.id') . ')',
           'nometa'             => true, // cannot GROUP_CONCAT a SUM
        ];

        $tab[] = [
           'id'                 => '49',
           'table'              => static::getTable(),
           'field'              => 'actiontime',
           'name'               => sprintf(__('%1$s - %2$s'), _n('Cost', 'Costs', 1), __('Duration')),
           'datatype'           => 'timestamp',
           'forcegroupby'       => true,
           'usehaving'          => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child'
           ]
        ];

        $tab[] = [
           'id'                 => '43',
           'table'              => static::getTable(),
           'field'              => 'cost_fixed',
           'name'               => __('Fixed cost'),
           'datatype'           => 'decimal',
           'forcegroupby'       => true,
           'usehaving'          => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child'
           ],
           'computation'        =>
              '(SUM(' . $DB->quoteName('TABLE.cost_fixed') . ') / COUNT(' .
              $DB->quoteName('TABLE.id') . '))
            * COUNT(DISTINCT ' . $DB->quoteName('TABLE.id') . ')',
           'nometa'             => true, // cannot GROUP_CONCAT a SUM
        ];

        $tab[] = [
           'id'                 => '44',
           'table'              => static::getTable(),
           'field'              => 'cost_material',
           'name'               => __('Material cost'),
           'datatype'           => 'decimal',
           'forcegroupby'       => true,
           'usehaving'          => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child'
           ],
           'computation'        =>
              '(SUM(' . $DB->quoteName('TABLE.cost_material') . ') / COUNT(' .
              $DB->quoteName('TABLE.id') . '))
            * COUNT(DISTINCT ' . $DB->quoteName('TABLE.id') . ')',
           'nometa'             => true, // cannot GROUP_CONCAT a SUM
        ];

        return $tab;
    }


    /**
     * Init cost for creation based on previous cost
    **/
    public function initBasedOnPrevious()
    {

        $item = new static::$itemtype();
        if (
            !isset($this->fields[static::$items_id])
            || !$item->getFromDB($this->fields[static::$items_id])
        ) {
            return false;
        }

        // Set actiontime to
        $this->fields['actiontime']
                      = max(
                          0,
                          $item->fields['actiontime']
                                - $this->getTotalActionTimeForItem($this->fields[static::$items_id])
                      );
        $lastdata     = $this->getLastCostForItem($this->fields[static::$items_id]);

        if (isset($lastdata['end_date'])) {
            $this->fields['begin_date'] = $lastdata['end_date'];
        }
        if (isset($lastdata['cost_time'])) {
            $this->fields['cost_time'] = $lastdata['cost_time'];
        }
        if (isset($lastdata['cost_fixed'])) {
            $this->fields['cost_fixed'] = $lastdata['cost_fixed'];
        }
        if (isset($lastdata['budgets_id'])) {
            $this->fields['budgets_id'] = $lastdata['budgets_id'];
        }
        if (isset($lastdata['name'])) {
            $this->fields['name'] = $lastdata['name'];
        }
    }


    /**
     * Get total actiNULL        11400   0.0000  0.0000  0.0000  on time used on costs for an item
     *
     * @param $items_id        integer  ID of the item
    **/
    public function getTotalActionTimeForItem($items_id)
    {
        global $DB;

      //   $result = $DB->request([
      //      'SELECT' => ['SUM' => 'actiontime AS sumtime'],
      //      'FROM'   => $this->getTable(),
      //      'WHERE'  => [static::$items_id => $items_id]
      //   ])->next();
      //   return $result['sumtime'];
      $dql = "SELECT SUM(e.actiontime) AS sumtime 
        FROM " . get_class($this) . " e 
        WHERE e." . static::$items_id . " = :items_id";
        $result = $this->getAdapter()->request($dql, ['items_id' => $items_id]);
         foreach ($result as $data) {
            return $data['sumtime'];
        }
    }


    /**
     * Get last datas for an item
     *
     * @param $items_id        integer  ID of the item
    **/
    public function getLastCostForItem($items_id)
    {
        global $DB;

        $result = $DB->request([
           'FROM'   => $this->getTable(),
           'WHERE'  => [
              static::$items_id => $items_id
           ],
           'ORDER'  => [
              'end_date DESC',
              'id DESC'
           ]
        ])->next();
        return $result;
    }


    /**
     * Print the item cost form
     *
     * @param $ID        integer  ID of the item
     * @param $options   array    options used
    **/
    public function showForm($ID, $options = [])
    {

        if (isset($options['parent']) && !empty($options['parent'])) {
            $item = $options['parent'];
        }

        if ($ID > 0) {
            $this->check($ID, READ);
        } else {
            // Create item
            $options[static::$items_id] = $item->getField('id');
            $this->check(-1, CREATE, $options);
            $this->initBasedOnPrevious();
        }

        if ($ID > 0) {
            $items_id = $this->fields[static::$items_id];
        } else {
            $items_id = $options['parent']->fields["id"];
        }

        $item = new static::$itemtype();
        if (!$item->getFromDB($items_id)) {
            return false;
        }

        $form = [
           'action' => $this->getFormURL(),
           'buttons' => [
              [
                 'name'  => $ID > 0 ? 'update' : 'add',
                 'type'  => 'submit',
                 'value' => $ID > 0 ? __('Update') : __('Add'),
                 'class' => 'btn btn-secondary',
              ],
           ],
           'content' => [
              $this->getTypeName() => [
                 'visible' => true,
                 'inputs' => [
                    $ID > 0 ?
                    [
                       'type' => 'hidden',
                       'name' => 'id',
                       'value' => $ID
                    ] : [],
                    [
                       'type' => 'hidden',
                       'name' => static::$items_id,
                       'value' => $item->fields['id']
                    ],
                    __('Name') => [
                       'type' => 'text',
                       'name' => 'name',
                       'value' => $this->fields['name'],
                    ],
                    __('Begin date') => [
                       'type' => 'date',
                       'name' => 'begin_date',
                       'value' => $this->fields['begin_date'],
                    ],
                    __('End date') => [
                       'type' => 'date',
                       'name' => 'end_date',
                       'value' => $this->fields['end_date'],
                    ],
                    __('Duration') => [
                       'type' => 'select',
                       'name' => 'actiontime',
                       'values' => Timezone::GetTimeStamp(['addfirstminutes' => true]),
                    ],
                    __('Time cost') => [
                       'type' => 'number',
                       'name' => 'cost_time',
                       'step' => '0.01',
                       'value' => Html::formatNumber($this->fields["cost_time"], true),
                    ],
                    __('Fixed cost') => [
                       'type' => 'number',
                       'name' => 'cost_fixed',
                       'step' => '0.01',
                       'value' => Html::formatNumber($this->fields["cost_fixed"], true),
                    ],
                    __('Material cost') => [
                       'type' => 'number',
                       'name' => 'cost_material',
                       'step' => '0.01',
                       'value' => Html::formatNumber($this->fields["cost_material"], true),
                    ],
                    Budget::getTypeName(1) => [
                       'type' => 'select',
                       'name' => 'budgets_id',
                       'value' => $this->fields["budgets_id"],
                       'condition' => [
                          'entities_id' => $this->fields['entities_id'],
                          'is_recursive' => 1,
                       ],
                       'itemtype' => Budget::class,
                    ],
                    __('Comments') => [
                       'type' => 'textarea',
                       'name' => 'comment',
                       'value' => $this->fields["comment"],
                    ],
                 ]
              ]
           ]
        ];
        renderTwigForm($form);

        return true;
    }


    /**
     * Print the item costs
     *
     * @param $item                  CommonITILObject object or Project
     * @param $withtemplate boolean  Template or basic item (default 0)
     *
     * @return number total cost
    **/
    public static function showForObject($item, $withtemplate = 0)
    {
        global $DB, $CFG_GLPI;

        $forproject = false;
        if (is_a($item, 'Project', true)) {
            $forproject = true;
        }

        $ID = $item->fields['id'];

        if (
            !$item->getFromDB($ID)
            || !$item->canViewItem()
            || !static::canView()
        ) {
            return false;
        }
        $canedit = false;
        if (!$forproject) {
            $canedit = $item->canAddItem(__CLASS__);
        }

        $items_ids = $ID;
        if ($forproject) {
            $alltickets = ProjectTask::getAllTicketsForProject($ID);
            $items_ids = (count($alltickets) ? $alltickets : 0);
        }
        $iterator = $DB->request([
           'FROM'   => static::getTable(),
           'WHERE'  => [
              static::$items_id   => $items_ids
           ],
           'ORDER'  => 'begin_date'
        ]);

        $rand   = mt_rand();

        if (
            $canedit
            && !in_array($item->fields['status'], array_merge(
                $item->getClosedStatusArray(),
                $item->getSolvedStatusArray()
            ))
        ) {
            echo "<div id='viewcost" . $ID . "_$rand'></div>\n";
            echo "<script type='text/javascript' >\n";
            echo "function viewAddCost" . $ID . "_$rand() {\n";
            $params = ['type'             => static::getType(),
                            'parenttype'       => static::$itemtype,
                            static::$items_id  => $ID,
                            'id'               => -1];
            Ajax::updateItemJsCode(
                "viewcost" . $ID . "_$rand",
                $CFG_GLPI["root_doc"] . "/ajax/viewsubitem.php",
                $params
            );
            echo "};";
            echo "</script>\n";
            if (static::canCreate()) {
                echo "<div class='center firstbloc'>" .
                       "<a class='btn btn-secondary' href='javascript:viewAddCost" . $ID . "_$rand();'>";
                echo __('Add a new cost') . "</a></div>\n";
            }
        }

        $total          = 0;
        $total_time     = 0;
        $total_costtime = 0;
        $total_fixed    = 0;
        $total_material = 0;

        $fields = [];
        if ($forproject) {
            $fields += [
               Ticket::getTypeName(1),
            ];
        } else {
            $fields += [
               __('Item duration'),
            ];
        }

        $fields = [];
        if ($forproject) {
            $fields += [
               Ticket::getTypeName(1),
            ];
        }
        $fields += [
           __('Name'),
           __('Begin date'),
           __('End date'),
           Budget::getTypeName(1),
           __('Duration'),
           __('Time cost'),
           __('Fixed cost'),
           __('Material cost'),
           __('Total cost'),
           __('Edit'),
        ];
        $values = [];
        $massive_action = [];
        while ($data = $iterator->next()) {
            $newValue = [];
            $name = (empty($data['name']) ? sprintf(
                __('%1$s (%2$s)'),
                $data['name'],
                $data['id']
            )
                                          : $data['name']);
            $ticket = new Ticket();
            if ($forproject) {
                $newValue[] = $ticket->getLink();
            }
            $newValue += [
               $name,
               Html::convDate($data['begin_date']),
               Html::convDate($data['end_date']),
               Dropdown::getDropdownName('glpi_budgets', $data['budgets_id']),
               CommonITILObject::getActionTime($data['actiontime']),
               Html::formatNumber($data['cost_time']),
               Html::formatNumber($data['cost_fixed']),
               Html::formatNumber($data['cost_material']),
               self::computeTotalCost(
                   $data['actiontime'],
                   $data['cost_time'],
                   $data['cost_fixed'],
                   $data['cost_material']
               ),
               '<a><i class="fas fa-pencil-alt" onclick="viewEditCost' . $data[static::$items_id] . "_" . $data["id"] . "_$rand()\" title='viewEditCost'></i></a>"
            ];
            $total_time += $data['actiontime'];
            $total_costtime += ($data['actiontime'] * $data['cost_time'] / HOUR_TIMESTAMP);
            $total_fixed += $data['cost_fixed'];
            $total_material += $data['cost_material'];
            $total += self::computeTotalCost(
                $data['actiontime'],
                $data['cost_time'],
                $data['cost_fixed'],
                $data['cost_material']
            );

            if ($canedit) {
                echo "\n<script type='text/javascript' >\n";
                echo "function viewEditCost" . $data[static::$items_id] . "_" . $data["id"] . "_$rand() {\n";
                $params = ['type'            => static::getType(),
                               'parenttype'       => static::$itemtype,
                               static::$items_id  => $data[static::$items_id],
                               'id'               => $data["id"]];
                Ajax::updateItemJsCode(
                    "viewcost" . $ID . "_$rand",
                    $CFG_GLPI["root_doc"] . "/ajax/viewsubitem.php",
                    $params
                );
                echo "};";
                echo "</script>\n";
            }
            $values[] = $newValue;
        }

        renderTwigTemplate('table.twig', [
           'fields'        => $fields,
           'values'        => $values,
           'massiveaction' => $massive_action,
        ]);

        $fields = [
           __('Item duration'),
           __('Total'),
           __('Time cost'),
           __('Fixed cost'),
           __('Material cost'),
           __('Total cost')
        ];
        $values = [[
           CommonITILObject::getActionTime($item->fields['actiontime']),
           CommonITILObject::getActionTime($total_time),
           Html::formatNumber($total_costtime),
           Html::formatNumber($total_fixed),
           Html::formatNumber($total_material),
           Html::formatNumber($total),
        ]];
        renderTwigTemplate('table.twig', [
           'minimal' => true,
           'fields' => $fields,
           'values' => $values,
        ]);

        return $total;
    }


    /**
     * Get costs summary values
     *
     * @param $type    string  type
     * @param $ID      integer ID of the ticket
     *
     * @return array of costs and actiontime
    **/
    public static function getCostsSummary($type, $ID)
    {
        global $DB;

        $result = $DB->request(
            [
              'FROM'      => getTableForItemType($type),
              'WHERE'     => [
                 static::$items_id      => $ID,
              ],
              'ORDER'     => [
                 'begin_date'
              ],
            ]
        );

        $tab = ['totalcost'   => 0,
                    'actiontime'   => 0,
                    'costfixed'    => 0,
                    'costtime'     => 0,
                    'costmaterial' => 0
               ];

        foreach ($result as $data) {
            $tab['actiontime']   += $data['actiontime'];
            $tab['costfixed']    += $data['cost_fixed'];
            $tab['costmaterial'] += $data['cost_material'];
            $tab['costtime']     += ($data['actiontime'] * $data['cost_time'] / HOUR_TIMESTAMP);
            $tab['totalcost']    +=  self::computeTotalCost(
                $data['actiontime'],
                $data['cost_time'],
                $data['cost_fixed'],
                $data['cost_material']
            );
        }
        foreach ($tab as $key => $val) {
            $tab[$key] = Html::formatNumber($val);
        }
        return $tab;
    }


    /**
     * Computer total cost of a item
     *
     * @param $actiontime      float    actiontime
     * @param $cost_time       float    time cost
     * @param $cost_fixed      float    fixed cost
     * @param $cost_material   float    material cost
     * @param $edit            boolean  used for edit of computation ? (true by default)
     *
     * @return string total cost formatted string
    **/
    public static function computeTotalCost(
        $actiontime,
        $cost_time,
        $cost_fixed,
        $cost_material,
        $edit = true
    ) {

        return Html::formatNumber(
            ($actiontime * $cost_time / HOUR_TIMESTAMP) + $cost_fixed + $cost_material,
            $edit
        );
    }
}
