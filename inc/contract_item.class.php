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
 * Contract_Item Class
 *
 * Relation between Contracts and Items
**/
class Contract_Item extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1 = 'Contract';
    public static $items_id_1 = 'contracts_id';

    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';


    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    public function canCreateItem()
    {

        // Try to load the contract
        $contract = $this->getConnexityItem(static::$itemtype_1, static::$items_id_1);
        if ($contract === false) {
            return false;
        }

        // Don't create a Contract_Item on contract that is alreay max used
        // Was previously done (until 0.83.*) by Contract_Item::can()
        if (
            ($contract->fields['max_links_allowed'] > 0)
            && (countElementsInTable(
                $this->getTable(),
                ['contracts_id' => $this->input['contracts_id']]
            )
                  >= $contract->fields['max_links_allowed'])
        ) {
            return false;
        }

        return parent::canCreateItem();
    }


    public static function getTypeName($nb = 0)
    {
        return _n('Link Contract/Item', 'Links Contract/Item', $nb);
    }

    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'items_id':
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
                }
                break;
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }


    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'massiveaction'      => false,
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '3',
           'table'              => $this->getTable(),
           'field'              => 'items_id',
           'name'               => __('Associated item ID'),
           'massiveaction'      => false,
           'datatype'           => 'specific',
           'additionalfields'   => ['itemtype']
        ];

        $tab[] = [
           'id'                 => '4',
           'table'              => $this->getTable(),
           'field'              => 'itemtype',
           'name'               => _n('Type', 'Types', 1),
           'massiveaction'      => false,
           'datatype'           => 'itemtypename',
           'itemtype_list'      => 'contract_types'
        ];

        return $tab;
    }


    /**
     * @since 0.84
     *
     * @param $contract_id   contract ID
     * @param $entities_id   entity ID
     *
     * @return array of items linked to contracts
    **/
    public static function getItemsForContract($contract_id, $entities_id)
    {

        $items = [];

        $types_iterator = self::getDistinctTypes($contract_id);

        while ($type_row = $types_iterator->next()) {
            $itemtype = $type_row['itemtype'];
            if (!getItemForItemtype($itemtype)) {
                continue;
            }

            $iterator = self::getTypeItems($contract_id, $itemtype);
            while ($objdata = $iterator->next()) {
                $items[$itemtype][$objdata['id']] = $objdata;
            }
        }

        return $items;
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        global $CFG_GLPI;

        // Can exists on template
        if (Contract::canView()) {
            $nb = 0;
            switch ($item->getType()) {
                case 'Contract':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = self::countForMainItem($item);
                    }
                    return self::createTabEntry(_n('Item', 'Items', Session::getPluralNumber()), $nb);

                default:
                    if (
                        $_SESSION['glpishow_count_on_tabs']
                        && in_array($item->getType(), $CFG_GLPI["contract_types"])
                    ) {
                        $nb = self::countForItem($item);
                    }
                    return self::createTabEntry(Contract::getTypeName(Session::getPluralNumber()), $nb);
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        global $CFG_GLPI;

        switch ($item->getType()) {
            case 'Contract':
                self::showForContract($item, $withtemplate);

                // no break
            default:
                if (in_array($item->getType(), $CFG_GLPI["contract_types"])) {
                    self::showForItem($item, $withtemplate);
                }
        }
        return true;
    }


    /**
     * Duplicate contracts from an item template to its clone
     *
     * @deprecated 9.5
     * @since 0.84
     *
     * @param string  $itemtype     itemtype of the item
     * @param integer $oldid        ID of the item to clone
     * @param integer $newid        ID of the item cloned
     * @param string  $newitemtype  itemtype of the new item (= $itemtype if empty) (default '')
     *
     * @return void
    **/
    public static function cloneItem($itemtype, $oldid, $newid, $newitemtype = '')
    {
        global $DB;

        Toolbox::deprecated('Use clone');
        if (empty($newitemtype)) {
            $newitemtype = $itemtype;
        }

        $result = self::getAdapter()->request(
            [
              'SELECT' => 'contracts_id',
              'FROM'   => self::getTable(),
              'WHERE'  => [
                 'items_id' => $oldid,
                 'itemtype' => $itemtype,
              ],
            ]
        );
        foreach ($result as $data) {
            $contractitem = new self();
            $contractitem->add(['contracts_id' => $data["contracts_id"],
                                     'itemtype'     => $newitemtype,
                                     'items_id'     => $newid]);
        }
    }


    /**
     * Print an HTML array of contract associated to an object
     *
     * @since 0.84
     *
     * @param CommonDBTM $item         CommonDBTM object wanted
     * @param integer    $withtemplate
     *
     * @return void
    **/
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {
        global $DB;

        $itemtype = $item->getType();
        $ID       = $item->fields['id'];

        if (
            !Contract::canView()
            || !$item->can($ID, READ)
        ) {
            return;
        }

        $canedit = $item->can($ID, UPDATE);
        $iterator = self::getListForItem($item);
        $number = count($iterator);
        $adapter = Config::getAdapter();
        $date_expr = $adapter->getDateAdd('begin_date', 'duration', 'month');


        $contracts = [];
        $used      = [];
        foreach ($iterator as $data) {
            $contracts[$data['id']] = $data;
            $used[$data['id']]      = $data['id'];
        }
        if ($canedit && ($withtemplate != 2)) {
            if (!count($used)) {
                $usedCondition = [];
            } else {
                $usedCondition = ['NOT' => [Contract::getTable() . '.id' => $used]];
            };
            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  [
                     'type' => 'submit',
                     'name' => 'add',
                     'value' => _sx('button', 'Add'),
                     'class' => 'btn btn-secondary',
                  ]
               ],
               'content' => [
                  '' => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'itemtype',
                           'value' => $itemtype
                        ],
                        [
                           'type' => 'hidden',
                           'name' => 'items_id',
                           'value' => $ID
                        ],
                        __('Add a contract') => [
                           'type' => 'select',
                           'name' => 'contracts_id',
                           'values' => getOptionForItems('Contract', array_merge([
                              'OR' => [
                                 'renewal' => 1,
                                new \QueryExpression("($date_expr) > CURRENT_DATE"),
                                 'begin_date'   => null,
                              ],
                              'is_deleted' => 0,
                           ], $usedCondition)),
                        ]
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        if ($withtemplate != 2) {
            if ($canedit && $number) {
                $massiveactionparams = [
                   'num_displayed' => min($_SESSION['glpilist_limit'], $number),
                   'container'     => 'TableFor' . __CLASS__,
                   'display_arrow' => false,
                   'specific_actions' => [
                      'purge' => __('Delete permanently the relation with selected elements')
                   ]
                ];
                Html::showMassiveActions($massiveactionparams);
            }
        }
        $fields = [
           __('Name'),
           Entity::getTypeName(1),
           _x('phone', 'Number'),
           ContractType::getTypeName(1),
           Supplier::getTypeName(1),
           __('Start date'),
           __('Initial contract period'),
        ];
        $values = [];
        $massive_action_values = [];
        foreach ($contracts as $data) {
            $cID         = $data["id"];
            $assocID     = $data["linkid"];
            $con         = new Contract();
            $con->getFromResultSet($data);
            $name = $con->fields["name"];
            if (
                $_SESSION["glpiis_ids_visible"]
                || empty($con->fields["name"])
            ) {
                $name = sprintf(__('%1$s (%2$s)'), $name, $con->fields["id"]);
            }
            $newValue = [
               "<a href='" . Contract::getFormURLWithID($cID) . "'>" . $name . "</a>",
               Dropdown::getDropdownName("glpi_entities", $con->fields["entities_id"]),
               $con->fields["num"],
               Dropdown::getDropdownName("glpi_contracttypes", $con->fields["contracttypes_id"]),
               $con->getSuppliersNames(),
               Html::convDate($con->fields["begin_date"]),
               sprintf(
                   __('%1$s %2$s'),
                   $con->fields["duration"],
                   _n('month', 'months', $con->fields["duration"])
               ),
            ];
            if (
                ($con->fields["begin_date"] != '')
                && !empty($con->fields["begin_date"])
            ) {
                $newValue[] = Infocom::getWarrantyExpir(
                    $con->fields["begin_date"],
                    $con->fields["duration"],
                    0,
                    true
                );
            }
            $massive_action_values[] = 'item[' . __CLASS__ . '][' . $assocID . ']';
            $values[] = $newValue;
        }
        renderTwigTemplate('table.twig', [
           'id' => 'TableFor' . __CLASS__,
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action_values,
        ]);
    }


    /**
     * Print the HTML array for Items linked to current contract
     *
     * @since 0.84
     *
     * @param Contract $contract     Contract object
     * @param integer  $withtemplate (default 0)
     *
     * @return void|boolean (display) Returns false if there is a rights error.
    **/
    public static function showForContract(Contract $contract, $withtemplate = 0)
    {
        global $CFG_GLPI;

        $instID = $contract->fields['id'];

        if (!$contract->can($instID, READ)) {
            return false;
        }
        $canedit = $contract->can($instID, UPDATE);
        $rand    = mt_rand();

        $types_iterator = self::getDistinctTypes($instID);
        $number = count($types_iterator);

        $data    = [];
        $totalnb = 0;
        $used    = [];
        foreach ($types_iterator as $type_row) {
            $itemtype = $type_row['itemtype'];
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }
            if ($item->canView()) {
                $itemtable = getTableForItemType($itemtype);
                $itemtype_2 = null;
                $itemtable_2 = null;

                $params = [
                   'SELECT' => [
                      $itemtable . '.*',
                      self::getTable() . '.id AS linkid',
                      'glpi_entities.id AS entity'
                   ],
                   'FROM'   => 'glpi_contracts_items',
                   'WHERE'  => [
                      'glpi_contracts_items.itemtype'     => $itemtype,
                      'glpi_contracts_items.contracts_id' => $instID
                   ]
                ];

                if ($item instanceof Item_Devices) {
                    $itemtype_2 = $itemtype::$itemtype_2;
                    $itemtable_2 = $itemtype_2::getTable();
                    $namefield = 'name_device';
                    $params['SELECT'][] = $itemtable_2 . '.designation AS ' . $namefield;
                } else {
                    $namefield = $item->getNameField();
                    $namefield = "$itemtable.$namefield";
                }

                $params['LEFT JOIN'][$itemtable] = [
                   'FKEY' => [
                      $itemtable        => 'id',
                      self::getTable()  => 'items_id'
                   ]
                ];
                if ($itemtype != 'Entity') {
                    $params['LEFT JOIN']['glpi_entities'] = [
                       'FKEY' => [
                          $itemtable        => 'entities_id',
                          'glpi_entities'   => 'id'
                       ]
                    ];
                }

                if ($item instanceof Item_Devices) {
                    $id_2 = $itemtype_2::getIndexName();
                    $fid_2 = $itemtype::$items_id_2;

                    $params['LEFT JOIN'][$itemtable_2] = [
                       'FKEY' => [
                          $itemtable     => $fid_2,
                          $itemtable_2   => $id_2
                       ]
                    ];
                }

                if ($item->maybeTemplate()) {
                    $params['WHERE'][] = [$itemtable . '.is_template' => 0];
                }
                $params['WHERE'] += getEntitiesRestrictCriteria($itemtable, '', '', $item->maybeRecursive());
                $params['ORDER'] = "glpi_entities.completename, $namefield";

                $request = self::getAdapter()->request($params);
                $results = $request->fetchAllAssociative();
                $nb = count($results);

                if ($nb > $_SESSION['glpilist_limit']) {
                    $opt = ['order'      => 'ASC',
                                 'is_deleted' => 0,
                                 'reset'      => 'reset',
                                 'start'      => 0,
                                 'sort'       => 80,
                                 'criteria'   => [0 => ['value'      => '$$$$' . $instID,
                                                                  'searchtype' => 'contains',
                                                                  'field'      => 29]]];

                    $url  = $item::getSearchURL();
                    $url .= (strpos($url, '?') ? '&' : '?');
                    $url .= Toolbox::append_params($opt);
                    $link = "<a href='$url'>" . __('Device list') . "</a>";

                    $data[$itemtype] = ['longlist' => true,
                                             'name'     => sprintf(
                                                 __('%1$s: %2$s'),
                                                 $item->getTypeName($nb),
                                                 $nb
                                             ),
                                             'link'     => $link];
                } elseif ($nb > 0) {
                    $data[$itemtype] = [];
                    foreach ($results as $objdata) {
                        $data[$itemtype][$objdata['id']] = $objdata;
                        $used[$itemtype][$objdata['id']] = $objdata['id'];
                    }
                }
                $totalnb += $nb;
            }
        }

        if (
            $canedit
            && (($contract->fields['max_links_allowed'] == 0)
                || ($contract->fields['max_links_allowed'] > $totalnb))
            && ($withtemplate != 2)
        ) {
            $itemtypes = $CFG_GLPI['contract_types'];
            $options = [];
            foreach ($itemtypes as $itemtype) {
                $options[$itemtype] = $itemtype::getTypeName(1);
            };

            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  [
                     'type' => 'submit',
                     'name' => 'add',
                     'value' => _sx('button', 'Add an item'),
                     'class' => 'btn btn-secondary'
                  ]
               ],
               'content' => [
                  __('Add an item') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'contracts_id',
                           'value' => $instID
                        ],
                        __('Type') => [
                           'type' => 'select',
                           'id' => 'dropdown_itemtype',
                           'name' => 'itemtype',
                           'values' => [Dropdown::EMPTY_VALUE] + array_unique($options),
                           'col_lg' => 6,
                           'hooks' => [
                              'change' => <<<JS
                              $.ajax({
                                    method: "POST",
                                    url: "$CFG_GLPI[root_doc]/ajax/getDropdownValue.php",
                                    data: {
                                       itemtype: this.value,
                                       display_emptychoice: 1,
                                    },
                                    success: function(response) {
                                       const data = response.results;
                                       $('#dropdown_items_id').empty();
                                       for (let i = 0; i < data.length; i++) {
                                          if (data[i].children) {
                                             const group = $('#dropdown_items_id')
                                                .append("<optgroup label='" + data[i].text + "'></optgroup>");
                                             for (let j = 0; j < data[i].children.length; j++) {
                                                group.append("<option value='" + data[i].children[j].id + "'>" + data[i].children[j].text + "</option>");
                                             }
                                          } else {
                                             $('#dropdown_items_id').append("<option value='" + data[i].id + "'>" + data[i].text + "</option>");
                                          }
                                       }
                                    }
                                 });
                           JS,
                           ]
                        ],
                        __('Item') => [
                           'type' => 'select',
                           'id' => 'dropdown_items_id',
                           'name' => 'items_id',
                           'values' => [],
                           'col_lg' => 6,
                        ],
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        if ($canedit && $totalnb) {
            $massiveactionparams = [
               'container' => 'tableForContractItem',
               'specific_actions' => [
                  'MassiveAction:purge' => _x('button', 'Delete permanently the relation with selected elements'),
               ],
               'display_arrow' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }

        $fields = [
           _n('Type', 'Types', 1),
           Entity::getTypeName(1),
           __('Name'),
           __('Serial number'),
           __('Inventory number'),
           __('Status')
        ];
        $values = [];
        $massiveactionValues = [];
        foreach ($data as $itemtype => $datas) {
            $item = new $itemtype();
            $typename = $item->getTypeName($nb);
            foreach ($datas as $sub_item) {
                if ($item instanceof Item_Devices) {
                    $name = $sub_item["name_device"];
                } else {
                    $name = $sub_item["name"];
                }
                if (
                    $_SESSION["glpiis_ids_visible"]
                    || empty($data["name"])
                ) {
                    $name = sprintf(__('%1$s (%2$s)'), $name, $sub_item["id"]);
                }

                if ($item->can($sub_item['id'], READ)) {
                    $link     = $itemtype::getFormURLWithID($sub_item['id']);
                    $namelink = "<a href=\"" . $link . "\">" . $name . "</a>";
                } else {
                    $namelink = $name;
                }

                $newValue = [
                   ($nb  > 1 ? sprintf(__('%1$s: %2$s'), $typename, $nb) : $typename),
                   Dropdown::getDropdownName("glpi_entities", $sub_item['entity']),
                   $namelink,
                   (isset($sub_item["serial"]) ? "" . $sub_item["serial"] . "" : "-"),
                   (isset($sub_item["otherserial"]) ? "" . $sub_item["otherserial"] . "" : "-"),
                   isset($sub_item["states_id"]) ? Dropdown::getDropdownName("glpi_states", $sub_item['states_id']) : ''
                ];
                $values[] = $newValue;
                $massiveactionValues[] = sprintf('item[%s][%s]', $item::class, $sub_item['id']);
            }
        }
        renderTwigTemplate('table.twig', [
           'id' => 'tableForContractItem',
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massiveactionValues,
        ]);
    }


    public static function getRelationMassiveActionsSpecificities()
    {
        global $CFG_GLPI;

        $specificities              = parent::getRelationMassiveActionsSpecificities();
        $specificities['itemtypes'] = $CFG_GLPI['contract_types'];

        return $specificities;
    }
}
