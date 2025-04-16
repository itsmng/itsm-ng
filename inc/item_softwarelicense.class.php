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
 * Manage link between items and software licenses.
 */
class Item_SoftwareLicense extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1 = 'itemtype';
    public static $items_id_1 = 'items_id';

    public static $itemtype_2 = 'SoftwareLicense';
    public static $items_id_2 = 'softwarelicenses_id';


    public function post_addItem()
    {

        SoftwareLicense::updateValidityIndicator($this->fields['softwarelicenses_id']);

        parent::post_addItem();
    }


    public function post_deleteFromDB()
    {

        SoftwareLicense::updateValidityIndicator($this->fields['softwarelicenses_id']);

        parent::post_deleteFromDB();
    }


    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
           'id'                 => 'common',
           'name'               => __('Characteristics')
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
           'id'                 => '4',
           'table'              => 'glpi_softwarelicenses',
           'field'              => 'name',
           'name'               => _n('License', 'Licenses', 1),
           'datatype'           => 'dropdown',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => $this->getTable(),
           'field'              => 'items_id',
           'name'               => _n('Associated element', 'Associated elements', Session::getPluralNumber()),
           'datatype'           => 'specific',
           'comments'           => true,
           'nosort'             => true,
           'massiveaction'      => false,
           'additionalfields'   => ['itemtype']
        ];

        $tab[] = [
           'id'                 => '6',
           'table'              => $this->getTable(),
           'field'              => 'itemtype',
           'name'               => _x('software', 'Request source'),
           'datatype'           => 'dropdown'
        ];

        return $tab;
    }


    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {

        global $CFG_GLPI;
        $input = $ma->getInput();
        switch ($ma->getAction()) {
            case 'move_license':
                if (isset($input['options'])) {
                    if (isset($input['options']['move'])) {
                        SoftwareLicense::dropdown([
                           'condition' => [
                              'glpi_softwarelicenses.softwares_id' => $input['options']['move']['softwares_id']
                           ],
                           'used'      => $input['options']['move']['used']
                        ]);
                        echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                        return true;
                    }
                }
                return false;

            case 'add':
                $inputs = [
                   Software::getTypeName() => [
                      'type' => 'select',
                      'name' => Software::getForeignKeyField(),
                      'itemtype' => Software::class,
                      'conditions' => [
                            'entities_id' => $_SESSION["glpiactive_entity"],
                            'is_template' => 0
                      ],
                      'actions' => getItemActionButtons(['info'], Software::class),
                      'col_lg' => 12,
                      'col_md' => 12,
                      'hooks' => [
                         'change' => <<<JS
                     const val = this.value;
                     const select = document.querySelector('select[name="peer_softwarelicenses_id"]');
                     select.disabled = !val;
                     select.innerHTML = '';
                     if (val != 0) {
                        $.ajax({
                           url: "{$CFG_GLPI['root_doc']}/ajax/dropdownSoftwareLicense.php",
                           method: 'POST',
                           data: {
                              softwares_id: val,
                              entity_restrict: {$_SESSION["glpiactive_entity"]}
                           },
                           success: function(data) {
                              const jsonData = JSON.parse(data);
                              jsonData[0] = '-----';
                              for (const key in jsonData) {
                                 const option = document.createElement('option');
                                 option.value = key;
                                 option.text = jsonData[key];
                                 select.appendChild(option);
                              }
                           }
                        });
                     };
                     JS,
                      ],
                   ],
                   SoftwareVersion::getTypeName() => [
                      'type' => 'select',
                      'name' => 'peer_softwarelicenses_id',
                      'values' => [],
                      'disabled' => '',
                      'col_lg' => 12,
                      'col_md' => 12,
                   ]
                ];
                foreach ($inputs as $title => $input) {
                    renderTwigTemplate('macros/wrappedInput.twig', [
                       'title' => $title,
                       'input' => $input,
                    ]);
                };
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction', 'class' => 'btn btn-secondary']);
                return true;
            case 'add_item':
                global $CFG_GLPI;
                echo "<table class='tab_cadre_fixe' aria-label='Item detail'>";
                echo "<tr class='tab_bg_2 center'>";
                echo "<td>";
                $rand = Dropdown::showItemTypes('itemtype', $CFG_GLPI['software_types'], [
                   'width'                 => 'unset'
                ]);

                $p = ['idtable'            => '__VALUE__',
                   'rand'                  => $rand,
                   'name'                  => "items_id",
                   'width'                 => 'unset'
                ];

                Ajax::updateItemOnSelectEvent(
                    "dropdown_itemtype$rand",
                    "results_itemtype$rand",
                    $CFG_GLPI["root_doc"] . "/ajax/dropdownAllItems.php",
                    $p
                );

                echo "<span id='results_itemtype$rand'>\n";
                echo "</td><td>";
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']) . "</span>";
                echo "</td></tr>";

                return true;
        }
        return parent::showMassiveActionsSubForm($ma);
    }


    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {

        switch ($ma->getAction()) {
            case 'move_license':
                $input = $ma->getInput();
                if (isset($input['softwarelicenses_id'])) {
                    foreach ($ids as $id) {
                        if ($item->can($id, UPDATE)) {
                            //Process rules
                            if (
                                $item->update(['id'  => $id,
                                                    'softwarelicenses_id'
                                                    => $input['softwarelicenses_id']])
                            ) {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                            } else {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                                $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                            }
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
                            $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
                        }
                    }
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                }
                return;

            case 'install':
                $csl = new self();
                $csv = new Item_SoftwareVersion();
                foreach ($ids as $id) {
                    if ($csl->getFromDB($id)) {
                        $sl = new SoftwareLicense();

                        if ($sl->getFromDB($csl->fields["softwarelicenses_id"])) {
                            $version = 0;
                            if ($sl->fields["softwareversions_id_use"] > 0) {
                                $version = $sl->fields["softwareversions_id_use"];
                            } else {
                                $version = $sl->fields["softwareversions_id_buy"];
                            }
                            if ($version > 0) {
                                $params = [
                                   'items_id'  => $csl->fields['items_id'],
                                   'itemtype'  => $csl->fields['itemtype'],
                                   'softwareversions_id' => $version];
                                //Get software name and manufacturer
                                if ($csv->can(-1, CREATE, $params)) {
                                    //Process rules
                                    if ($csv->add($params)) {
                                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                                    } else {
                                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                                    }
                                } else {
                                    $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
                                }
                            } else {
                                Session::addMessageAfterRedirect(__('A version is required!'), false, ERROR);
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                            }
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        }
                    }
                }
                return;

            case 'add_item':
                $item_licence = new Item_SoftwareLicense();
                $input = $ma->getInput();
                foreach ($ids as $id) {
                    $input = [
                       'softwarelicenses_id'   => $id,
                       'items_id'        => $input['items_id'],
                       'itemtype'        => $input['itemtype']
                    ];
                    if ($item_licence->can(-1, UPDATE, $input)) {
                        if ($item_licence->add($input)) {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        }
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                    }
                }
                return;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }


    /**
     * Get number of installed licenses of a license
     *
     * @param integer $softwarelicenses_id license ID
     * @param integer $entity              to search for item in (default = all entities)
     *                                     (default '') -1 means no entity restriction
     * @param string $itemtype             Item type to filter on. Use null for all itemtypes
     *
     * @return integer number of installations
    **/
    public static function countForLicense($softwarelicenses_id, $entity = '', $itemtype = null)
    {
        $request = self::getAdapter()->request([
           'SELECT'    => ['itemtype'],
           'DISTINCT'  => true,
           'FROM'      => self::getTable(__CLASS__),
           'WHERE'     => [
              'softwarelicenses_id'   => $softwarelicenses_id
           ]
        ]);

        $target_types = [];
        if ($itemtype !== null) {
            $target_types = [$itemtype];
        } else {
            while ($data = $request->fetchAssociative()) {
                $target_types[] = $data['itemtype'];
            }
        }

        $count = 0;
        foreach ($target_types as $itemtype) {
            $itemtable = $itemtype::getTable();
            $request = [
               'FROM'         => 'glpi_items_softwarelicenses',
               'COUNT'        => 'cpt',
               'INNER JOIN'   => [
                  $itemtable  => [
                     'FKEY'   => [
                        $itemtable                    => 'id',
                        'glpi_items_softwarelicenses' => 'items_id', [
                           'AND' => [
                              'glpi_items_softwarelicenses.itemtype' => $itemtype
                           ]
                        ]
                     ]
                  ]
               ],
               'WHERE'        => [
                  'glpi_items_softwarelicenses.softwarelicenses_id'     => $softwarelicenses_id,
                  'glpi_items_softwarelicenses.is_deleted'              => 0
               ]
            ];
            if ($entity !== -1) {
                $request['WHERE'] += getEntitiesRestrictCriteria($itemtable, '', $entity);
            }
            $item = new $itemtype();
            if ($item->maybeDeleted()) {
                $request['WHERE']["$itemtable.is_deleted"] = 0;
            }
            if ($item->maybeTemplate()) {
                $request['WHERE']["$itemtable.is_template"] = 0;
            }
            $count += self::getAdapter()->request($request)->fetchAssociative()['cpt'];
        }
        return $count;
    }


    /**
     * Get number of installed licenses of a software
     *
     * @param integer $softwares_id software ID
     *
     * @return integer number of installations
    **/
    public static function countForSoftware($softwares_id)
    {
        $license_table = SoftwareLicense::getTable();
        $item_license_table = self::getTable(__CLASS__);

        $request = self::getAdapter()->request([
           'SELECT'    => ['itemtype'],
           'DISTINCT'  => true,
           'FROM'      => $item_license_table,
           'LEFT JOIN' => [
              $license_table => [
                 'FKEY'   => [
                    $license_table       => 'id',
                    $item_license_table  => 'softwarelicenses_id'
                 ]
              ]
           ],
           'WHERE'     => [
              'softwares_id'   => $softwares_id
           ]
        ]);

        $target_types = [];
        while ($data = $request->fetchAssociative()) {
            $target_types[] = $data['itemtype'];
        }

        $count = 0;
        foreach ($target_types as $itemtype) {
            $itemtable = $itemtype::getTable();
            $request = [
               'FROM'         => 'glpi_softwarelicenses',
               'COUNT'        => 'cpt',
               'INNER JOIN'   => [
                  'glpi_items_softwarelicenses' => [
                     'FKEY'   => [
                        'glpi_softwarelicenses'          => 'id',
                        'glpi_items_softwarelicenses'    => 'softwarelicenses_id'
                     ]
                  ],
                  $itemtable  => [
                     'FKEY'   => [
                        $itemtable                    => 'id',
                        'glpi_items_softwarelicenses' => 'items_id', [
                           'AND' => [
                              'glpi_items_softwarelicenses.itemtype' => $itemtype
                           ]
                        ]
                     ]
                  ]
               ],
               'WHERE'        => [
                  'glpi_softwarelicenses.softwares_id'      => $softwares_id,
                  'glpi_items_softwarelicenses.is_deleted'  => 0
               ] + getEntitiesRestrictCriteria($itemtable)
            ];
            $item = new $itemtype();
            if ($item->maybeDeleted()) {
                $request['WHERE']["$itemtable.is_deleted"] = 0;
            }
            if ($item->maybeTemplate()) {
                $request['WHERE']["$itemtable.is_template"] = 0;
            }
            $count += self::getAdapter()->request($request)->fetchAssociative()['cpt'];
        }
        return $count;
    }


    /**
     * Show number of installation per entity
     *
     * @param SoftwareLicense $license SoftwareLicense instance
     *
     * @return void
    **/
    public static function showForLicenseByEntity(SoftwareLicense $license)
    {
        $softwarelicense_id = $license->getField('id');
        $license_table = SoftwareLicense::getTable();
        $item_license_table = self::getTable(__CLASS__);

        if (!Software::canView() || !$softwarelicense_id) {
            return false;
        }

        echo "<div class='center'>";
        echo "<table class='tab_cadre' aria-label='Number of affected items'><tr>";
        echo "<th>" . Entity::getTypeName(1) . "</th>";
        echo "<th>" . __('Number of affected items') . "</th>";
        echo "</tr>\n";

        $tot = 0;

        $request = self::getAdapter()->request([
           'SELECT' => ['id', 'completename'],
           'FROM'   => 'glpi_entities',
           'WHERE'  => getEntitiesRestrictCriteria('glpi_entities'),
           'ORDER'  => ['completename']
        ]);

        $tab = "&nbsp;&nbsp;&nbsp;&nbsp;";
        while ($data = $request->fetchAssociative()) {
            $itemtype_request = self::getAdapter()->request([
               'SELECT'    => ['itemtype'],
               'DISTINCT'  => true,
               'FROM'      => $item_license_table,
               'LEFT JOIN' => [
                  $license_table => [
                     'FKEY'   => [
                        $license_table       => 'id',
                        $item_license_table  => 'softwarelicenses_id'
                     ]
                  ]
               ],
               'WHERE'     => [
                  $item_license_table . '.softwarelicenses_id'   => $softwarelicense_id
               ] + getEntitiesRestrictCriteria($license_table, '', $data['id'])
            ]);

            $target_types = [];
            while ($type = $itemtype_request->fetchAssociative()) {
                $target_types[] = $type['itemtype'];
            }

            if (count($target_types)) {
                echo "<tr class='tab_bg_2'><td colspan='2'>{$data["completename"]}</td></tr>";
                foreach ($target_types as $itemtype) {
                    $nb = self::countForLicense($softwarelicense_id, $data['id'], $itemtype);
                    echo "<tr class='tab_bg_2'><td>$tab$tab{$itemtype::getTypeName()}</td>";
                    echo "<td class='numeric'>{$nb}</td></tr>\n";
                    $tot += $nb;
                }
            }
        }

        if ($tot > 0) {
            echo "<tr class='tab_bg_1'><td class='center b'>" . __('Total') . "</td>";
            echo "<td class='numeric b '>" . $tot . "</td></tr>\n";
        } else {
            echo "<tr class='tab_bg_1'><td colspan='2 b'>" . __('No item found') . "</td></tr>\n";
        }
        echo "</table></div>";
    }


    /**
     * Show items linked to a License
     *
     * @param SoftwareLicense $license SoftwareLicense instance
     *
     * @return void
    **/
    public static function showForLicense(SoftwareLicense $license)
    {
        global $DB, $CFG_GLPI;

        $searchID = $license->getField('id');

        if (!Software::canView() || !$searchID) {
            return false;
        }

        $canedit         = Session::haveRightsOr("software", [CREATE, UPDATE, DELETE, PURGE]);
        $canshowitems  = [];
        $item_license_table = self::getTable(__CLASS__);

        if (isset($_GET["start"])) {
            $start = $_GET["start"];
        } else {
            $start = 0;
        }

        if (isset($_GET["order"]) && ($_GET["order"] == "DESC")) {
            $order = "DESC";
        } else {
            $order = "ASC";
        }

        if (isset($_GET["sort"]) && !empty($_GET["sort"])) {
            // manage several param like location,compname : order first
            $tmp  = explode(",", $_GET["sort"]);
            $sort = "`" . implode("` $order,`", $tmp) . "`";
        } else {
            $sort = "`entity` $order, `itemname`";
        }

        //SoftwareLicense ID
        $number = self::countForLicense($searchID);

        if (
            $canedit
            && ($license->getField('number') == -1 || $number < $license->getField('number')
            || $license->getField('allow_overquota'))
        ) {
            $values = [];
            $types = $CFG_GLPI['software_types'];
            if (count($types)) {
                foreach ($types as $type) {
                    if ($item = getItemForItemtype($type)) {
                        $values[$type] = $item->getTypeName(1);
                    }
                }
            }
            asort($values);
            $form = [
               'action' => self::getFormURL(),
               'buttons' => [
                  [
                     'type' => 'submit',
                     'name' => 'add',
                     'value' => _sx('button', 'Add'),
                     'class' => 'btn btn-secondary'
                  ]
               ],
               'content' => [
                  '' => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'softwarelicenses_id',
                           'value' => $searchID
                        ],
                        __('Item type') => [
                           'type' => 'select',
                           'name' => 'itemtype',
                           'id' => 'dropdown_itemtype',
                           'values' => $values,
                           'value' => 'Computer',
                           'col_lg' => 6,
                           'hooks' => [
                              'change' => <<<JS
                                 $.ajax({
                                    method: "POST",
                                    url: "$CFG_GLPI[root_doc]/ajax/getDropdownValue.php",
                                    data: {
                                       itemtype: this.value,
                                    },
                                    success: function(response) {
                                       const data = response.results;
                                       $('#dropdown_items_id').empty();
                                       $('#dropdown_items_id').append("<option value='" + data[0].id + "'>" + data[0].text + "</option>");
                                       delete data[0];
                                       for (let i = 0; i < data.length; i++) {
                                          const group = $('#dropdown_items_id')
                                             .append("<optgroup label='" + data[i].text + "'></optgroup>");
                                          for (let j = 0; j < data[i].children.length; j++) {
                                             group.append("<option value='" + data[i].children[j].id + "'>" + data[i].children[j].text + "</option>");
                                          }
                                       }
                                    }
                                 });
                              JS,
                           ]
                        ],
                        __('Item') => [
                           'type' => 'select',
                           'name' => 'items_id',
                           'id' => 'dropdown_items_id',
                           'values' => [],
                           'value' => '',
                           'col_lg' => 6,
                           'init' => <<<JS
                              $.ajax({
                                 method: "POST",
                                 url: "$CFG_GLPI[root_doc]/ajax/getDropdownValue.php",
                                 data: {
                                    itemtype: $('#dropdown_itemtype').val(),
                                 },
                                 success: function(response) {
                                    const data = response.results;
                                    console.log(data)
                                    $('#dropdown_items_id').empty();
                                    $('#dropdown_items_id').append("<option value='" + data[0].id + "'>" + data[0].text + "</option>");
                                    delete data[0];
                                    for (let i = 1; i < data.length; i++) {
                                       const group = $('#dropdown_items_id')
                                          .append("<optgroup label='" + data[i].text + "'></optgroup>");
                                       for (let j = 0; j < data[i].children.length; j++) {
                                          group.append("<option value='" + data[i].children[j].id + "'>" + data[i].children[j].text + "</option>");
                                       }
                                    }
                                 }
                              });
                           JS,
                        ]
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        $queries = [];
        foreach ($CFG_GLPI['software_types'] as $itemtype) {
            $canshowitems[$itemtype] = $itemtype::canView();
            $itemtable = $itemtype::getTable();
            $query = [
               'SELECT' => [
                  $item_license_table . '.*',
                  'glpi_softwarelicenses.name AS license',
                  'glpi_softwarelicenses.id AS vID',
                  'glpi_softwarelicenses.softwares_id AS softid',
                  "{$itemtable}.name AS itemname",
                  "{$itemtable}.id AS iID",
                  new QueryExpression($DB->quoteValue($itemtype) . " AS " . $DB::quoteName('item_type')),
               ],
               'FROM'   => $item_license_table,
               'INNER JOIN' => [
                  'glpi_softwarelicenses' => [
                     'FKEY'   => [
                        $item_license_table     => 'softwarelicenses_id',
                        'glpi_softwarelicenses' => 'id'
                     ]
                  ]
               ],
               'LEFT JOIN' => [
                  $itemtable => [
                     'FKEY'   => [
                        $item_license_table     => 'items_id',
                        $itemtable        => 'id', [
                           'AND' => [
                              $item_license_table . '.itemtype'  => $itemtype
                           ]
                        ]
                     ]
                  ]
               ],
               'WHERE'     => [
                  'glpi_softwarelicenses.id'                   => $searchID,
                  'glpi_items_softwarelicenses.is_deleted'     => 0
               ]
            ];
            if ($DB->fieldExists($itemtable, 'serial')) {
                $query['SELECT'][] = $itemtable . '.serial';
            } else {
                $query['SELECT'][] = new QueryExpression(
                    $DB->quoteValue('') . " AS " . $DB->quoteName($itemtable . ".serial")
                );
            }
            if ($DB->fieldExists($itemtable, 'otherserial')) {
                $query['SELECT'][] = $itemtable . '.otherserial';
            } else {
                $query['SELECT'][] = new QueryExpression(
                    $DB->quoteValue('') . " AS " . $DB->quoteName($itemtable . ".otherserial")
                );
            }
            if ($DB->fieldExists($itemtable, 'users_id')) {
                $query['SELECT'][] = 'glpi_users.name AS username';
                $query['SELECT'][] = 'glpi_users.id AS userid';
                $query['SELECT'][] = 'glpi_users.realname AS userrealname';
                $query['SELECT'][] = 'glpi_users.firstname AS userfirstname';
                $query['LEFT JOIN']['glpi_users'] = [
                   'FKEY'   => [
                      $itemtable     => 'users_id',
                      'glpi_users'   => 'id'
                   ]
                ];
            } else {
                $query['SELECT'][] = new QueryExpression(
                    $DB->quoteValue('') . " AS " . $DB->quoteName($itemtable . ".username")
                );
                $query['SELECT'][] = new QueryExpression(
                    $DB->quoteValue('-1') . " AS " . $DB->quoteName($itemtable . ".userid")
                );
                $query['SELECT'][] = new QueryExpression(
                    $DB->quoteValue('') . " AS " . $DB->quoteName($itemtable . ".userrealname")
                );
                $query['SELECT'][] = new QueryExpression(
                    $DB->quoteValue('') . " AS " . $DB->quoteName($itemtable . ".userfirstname")
                );
            }
            if ($DB->fieldExists($itemtable, 'entities_id')) {
                $query['SELECT'][] = 'glpi_entities.completename AS entity';
                $query['LEFT JOIN']['glpi_entities'] = [
                   'FKEY'   => [
                      $itemtable     => 'entities_id',
                      'glpi_users'   => 'id'
                   ]
                ];
                $query['WHERE'] += getEntitiesRestrictCriteria($itemtable, '', '', true);
            } else {
                $query['SELECT'][] = new QueryExpression(
                    $DB->quoteValue('') . " AS " . $DB->quoteName('entity')
                );
            }
            if ($DB->fieldExists($itemtable, 'locations_id')) {
                $query['SELECT'][] = 'glpi_locations.completename AS location';
                $query['LEFT JOIN']['glpi_locations'] = [
                   'FKEY'   => [
                      $itemtable     => 'locations_id',
                      'glpi_users'   => 'id'
                   ]
                ];
            } else {
                $query['SELECT'][] = new QueryExpression(
                    $DB->quoteValue('') . " AS " . $DB->quoteName('location')
                );
            }
            if ($DB->fieldExists($itemtable, 'states_id')) {
                $query['SELECT'][] = 'glpi_states.name AS state';
                $query['LEFT JOIN']['glpi_states'] = [
                   'FKEY'   => [
                      $itemtable     => 'states_id',
                      'glpi_users'   => 'id'
                   ]
                ];
            } else {
                $query['SELECT'][] = new QueryExpression(
                    $DB->quoteValue('') . " AS " . $DB->quoteName('state')
                );
            }
            if ($DB->fieldExists($itemtable, 'groups_id')) {
                $query['SELECT'][] = 'glpi_groups.name AS groupe';
                $query['LEFT JOIN']['glpi_groups'] = [
                   'FKEY'   => [
                      $itemtable     => 'groups_id',
                      'glpi_users'   => 'id'
                   ]
                ];
            } else {
                $query['SELECT'][] = new QueryExpression(
                    $DB->quoteValue('') . " AS " . $DB->quoteName('groupe')
                );
            }
            if ($DB->fieldExists($itemtable, 'is_deleted')) {
                $query['WHERE']["{$itemtable}.is_deleted"] = 0;
            }
            if ($DB->fieldExists($itemtable, 'is_template')) {
                $query['WHERE']["{$itemtable}.is_template"] = 0;
            }
            $queries[] = $query;
        }
        $union = new QueryUnion($queries, true);
        $criteria = [
           'SELECT' => [],
           'FROM'   => $union,
           'ORDER'        => "$sort $order",
           'LIMIT'        => $_SESSION['glpilist_limit'],
           'START'        => $start
        ];
        $request = self::getAdapter()->request($criteria);
        $dataList = $request->fetchAllAssociative(); 

        if (!empty($dataList)) {
            if ($canedit) {
                $massiveactionparams = [
                   'num_displayed'    => min($_SESSION['glpilist_limit'], count($dataList)),
                   'container'        => 'tableForSoftwareLicenceItem',
                   'specific_actions' => [
                      'MassiveAction:purge' => _x('button', 'Delete permanently the relation with selected elements'),
                   ],
                   'is_deleted' => false,
                   'display_arrow' => false
                ];

                // show transfer only if multi licenses for this software
                if (self::countLicenses($data['softid']) > 1) {
                    $massiveactionparams['specific_actions'][__CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'move_license'] = _x('button', 'Move');
                }

                // Options to update license
                $massiveactionparams['extraparams']['options']['move']['used'] = [$searchID];
                $massiveactionparams['extraparams']['options']['move']['softwares_id']
                                                                      = $license->fields['softwares_id'];

                Html::showMassiveActions($massiveactionparams);
            }

            $soft       = new Software();
            $soft->getFromDB($license->fields['softwares_id']);
            $showEntity = ($license->isRecursive());
            $linkUser   = User::canView();

            $text = sprintf(__('%1$s = %2$s'), Software::getTypeName(1), $soft->fields["name"]);
            $text = sprintf(__('%1$s - %2$s'), $text, $data["license"]);

            $fields = [
               __('Item type'),
               __('Name'),
            ];
            if ($showEntity) {
                $fields[] = Entity::getTypeName(1);
            }
            $fields[] = __('Serial number');
            $fields[] = __('Inventory number');
            $fields[] = Location::getTypeName(1);
            $fields[] = __('Status');
            $fields[] = Group::getTypeName(1);
            $fields[] = User::getTypeName();
            $values = [];
            $massiveactionValues = [];
            do {
                $newValue = [];
                $newValue[] = $data['itemtype'];
                if ($canshowitems[$data['item_type']]) {
                    $newValue[] = "<a href='" . $data['item_type']::getFormURLWithID($data['iID']) . "'>"
                                            . $data['itemname'] . "</a>";
                } else {
                    $newValue[] = $data['itemname'];
                }

                if ($showEntity) {
                    $newValue[] = $data['entity'];
                }
                $newValue = array_merge($newValue, [
                   $data['serial'],
                   $data['otherserial'],
                   $data['location'],
                   $data['state'],
                   $data['groupe'],
                   formatUserName(
                       $data['userid'],
                       $data['username'],
                       $data['userrealname'],
                       $data['userfirstname'],
                       $linkUser
                   )
                ]);

                $values[] = $newValue;
                $massiveactionValues[] = sprintf('item[%s][%s]', $data['itemtype'], $data['items_id']);
            } while ($data = $iterator->next());
            renderTwigTemplate('table.twig', [
               'id' => 'tableForSoftwareLicenceItem',
               'fields' => $fields,
               'values' => $values,
               'massive_action' => $massiveactionValues
            ]);
        } else { // Not found
            echo __('No item found');
        }
    }


    /**
     * Update license associated on a computer
     *
     * @param integer $licID               ID of the install software lienk
     * @param integer $softwarelicenses_id ID of the new license
     *
     * @return void
    **/
    public function upgrade($licID, $softwarelicenses_id)
    {

        if ($this->getFromDB($licID)) {
            $items_id = $this->fields['items_id'];
            $itemtype = $this->fields['itemtype'];
            $this->delete(['id' => $licID]);
            $this->add([
               'items_id'              => $items_id,
               'itemtype'              => $itemtype,
               'softwarelicenses_id'   => $softwarelicenses_id]);
        }
    }


    /**
     * Get licenses list corresponding to an installation
     *
     * @param string $itemtype          Type of item
     * @param integer $items_id         ID of the item
     * @param integer $softwareversions_id ID of the version
     *
     * @return void
    **/
    public static function getLicenseForInstallation($itemtype, $items_id, $softwareversions_id)
    {
        $lic = [];
        $item_license_table = self::getTable(__CLASS__);

        $request = self::getAdapter()->request([
           'SELECT'       => [
              'glpi_softwarelicenses.*',
              'glpi_softwarelicensetypes.name AS type'
           ],
           'FROM'         => 'glpi_softwarelicenses',
           'INNER JOIN'   => [
              $item_license_table  => [
                 'FKEY'   => [
                    $item_license_table     => 'softwarelicenses_id',
                    'glpi_softwarelicenses' => 'id'
                 ]
              ]
           ],
           'LEFT JOIN'    => [
              'glpi_softwarelicensetypes'   => [
                 'FKEY'   => [
                    'glpi_softwarelicenses'       => 'softwarelicensetypes_id',
                    'glpi_softwarelicensetypes'   => 'id'
                 ]
              ]
           ],
           'WHERE'        => [
              $item_license_table . '.itemtype'  => $itemtype,
              $item_license_table . '.items_id'  => $items_id,
              'OR'                                => [
                 'glpi_softwarelicenses.softwareversions_id_use' => $softwareversions_id,
                 'glpi_softwarelicenses.softwareversions_id_buy' => $softwareversions_id
              ]
           ]
        ]);

        while ($data = $request->fetchAssociative()) {
            $lic[$data['id']] = $data;
        }
        return $lic;
    }


    /**
     * Duplicate all software licenses from a computer template to its clone
     *
     * @deprecated 9.5
     *
     * @param integer $oldid ID of the computer to clone
     * @param integer $newid ID of the computer cloned
     *
     * @return void
    **/
    public static function cloneComputer($oldid, $newid)
    {
        Toolbox::deprecated('Use clone');
        self::cloneItem('Computer', $oldid, $newid);
    }


    /**
     * Duplicate all software licenses from an item template to its clone
     *
     * @deprecated 9.5
     *
     * @param string  $itemtype Type of the item
     * @param integer $oldid ID of the item to clone
     * @param integer $newid ID of the item cloned
     *
     * @return void
     **/
    public static function cloneItem($itemtype, $oldid, $newid)
    {
        Toolbox::deprecated('Use clone');
        $request = self::getAdapter()->request([
           'FROM' => 'glpi_items_softwarelicenses',
           'WHERE' => [
              'items_id' => $oldid,
              'itemtype' => $itemtype
           ]
        ]);

        while ($data = $request->fetchAssociative()) {
            $csl = new self();
            unset($data['id']);
            $data['items_id'] = $newid;
            $data['itemtype'] = $itemtype;
            $data['_no_history'] = true;

            $csl->add($data);
        }
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        $nb = 0;
        switch ($item->getType()) {
            case 'SoftwareLicense':
                if (!$withtemplate) {
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = self::countForLicense($item->getID());
                    }
                    return [1 => __('Summary'),
                                 2 => self::createTabEntry(
                                     _n('Item', 'Items', Session::getPluralNumber()),
                                     $nb
                                 )];
                }
                break;
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == 'SoftwareLicense') {
            switch ($tabnum) {
                case 1:
                    self::showForLicenseByEntity($item);
                    break;

                case 2:
                    self::showForLicense($item);
                    break;
            }
        }
        return true;
    }


    /**
     * Count number of licenses for a software
     *
     * @since 0.85
     *
     * @param integer $softwares_id Software ID
     *
     * @return void
     **/
    public static function countLicenses($softwares_id)
    {
        $result = self::getAdapter()->request([
           'FROM'   => 'glpi_softwarelicenses',
           'COUNT'  => 'cpt',
           'WHERE'  => [
              'softwares_id' => $softwares_id
           ] + getEntitiesRestrictCriteria('glpi_softwarelicenses')
        ])->fetchAssociative();
        return $result['cpt'];
    }
}
