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

class Item_SoftwareVersion extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1 = 'itemtype';
    public static $items_id_1 = 'items_id';
    public static $itemtype_2 = 'SoftwareVersion';
    public static $items_id_2 = 'softwareversions_id';


    public static $log_history_1_add    = Log::HISTORY_INSTALL_SOFTWARE;
    public static $log_history_1_delete = Log::HISTORY_UNINSTALL_SOFTWARE;

    public static $log_history_2_add    = Log::HISTORY_INSTALL_SOFTWARE;
    public static $log_history_2_delete = Log::HISTORY_UNINSTALL_SOFTWARE;

    public function useDeletedToLockIfDynamic()
    {
        return false;
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Installation', 'Installations', $nb);
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
           'id'                 => '3',
           'table'              => $this->getTable(),
           'field'              => 'items_id',
           'name'               => _n('Associated element', 'Associated elements', Session::getPluralNumber()),
           'massiveaction'      => false,
           'nosort'             => true,
           'datatype'           => 'specific',
           'additionalfields'   => ['itemtype']
        ];

        $tab[] = [
           'id'                 => '4',
           'table'              => 'glpi_softwareversions',
           'field'              => 'name',
           'name'               => _n('Version', 'Versions', 1),
           'datatype'           => 'dropdown',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => $this->getTable(),
           'field'              => 'itemtype',
           'name'               => _x('software', 'Request source'),
           'datatype'           => 'dropdown'
        ];

        return $tab;
    }

    public function prepareInputForAdd($input)
    {

        if (!isset($input['itemtype']) || !isset($input['items_id'])) {
            return false;
        }
        $itemtype = $input['itemtype'];
        $item = new $itemtype();
        if (
            (!isset($input['is_template_item']) && $item->maybeTemplate())
            || (!isset($input['is_deleted_item']) && $item->maybeDeleted())
        ) {
            if ($item->getFromDB($input['items_id'])) {
                if ($item->maybeTemplate()) {
                    $input['is_template_item'] = $item->getField('is_template');
                }
                if ($item->maybeDeleted()) {
                    $input['is_deleted_item']  = $item->getField('is_deleted');
                }
            } else {
                return false;
            }
        }

        return parent::prepareInputForAdd($input);
    }


    public function prepareInputForUpdate($input)
    {

        if (isset($input['itemtype']) && isset($input['items_id'])) {
            $itemtype = $input['itemtype'];
            $item = new $itemtype();
            if (
                (!isset($input['is_template_item']) && $item->maybeTemplate())
                || (!isset($input['is_deleted_item']) && $item->maybeDeleted())
            ) {
                if ($item->getFromDB($input['items_id'])) {
                    if ($item->maybeTemplate()) {
                        $input['is_template_item'] = $item->getField('is_template');
                    }
                    if ($item->maybeDeleted()) {
                        $input['is_deleted_item'] = $item->getField('is_deleted');
                    }
                } else {
                    return false;
                }
            }
        }

        return parent::prepareInputForUpdate($input);
    }


    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        global $CFG_GLPI;

        switch ($ma->getAction()) {
            case 'add':
                $inputs = [
                   Software::getTypeName() => [
                      'type' => 'select',
                      'name' => Software::getForeignKeyField(),
                      'values' => getOptionForItems(
                          Software::class,
                          [
                            'is_deleted' => 0,
                            'entities_id' => $_SESSION["glpiactive_entity"],
                            'is_template' => 0
                          ]
                      ),
                      'actions' => getItemActionButtons(['info'], Software::class),
                      'col_lg' => 12,
                      'col_md' => 12,
                      'hooks' => [
                         'change' => <<<JS
                        const val = this.value;
                        const select = document.querySelector('select[name="peer_softwareversions_id"]');
                        select.disabled = !val;
                        select.innerHTML = '';
                        if (val != 0) {
                           $.ajax({
                              url: "{$CFG_GLPI['root_doc']}/ajax/dropdownInstallVersion.php",
                              method: 'POST',
                              data: {
                                 softwares_id: val,
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
                        }
                     JS,
                      ]
                   ],
                   SoftwareVersion::getTypeName() => [
                      'type' => 'select',
                      'name' => 'peer_softwareversions_id',
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
                }
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction', 'class' => 'btn btn-secondary']);
                return true;

            case 'move_version':
                $input = $ma->getInput();
                if (isset($input['options'])) {
                    if (isset($input['options']['move'])) {
                        $options = ['softwares_id' => $input['options']['move']['softwares_id']];
                        if (isset($input['options']['move']['used'])) {
                            $options['used'] = $input['options']['move']['used'];
                        }
                        SoftwareVersion::dropdownForOneSoftware($options);
                        echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                        return true;
                    }
                }
                return false;
        }
        return parent::showMassiveActionsSubForm($ma);
    }


    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {

        switch ($ma->getAction()) {
            case 'move_version':
                $input = $ma->getInput();
                if (isset($input['softwareversions_id'])) {
                    foreach ($ids as $id) {
                        if ($item->can($id, UPDATE)) {
                            //Process rules
                            if (
                                $item->update([
                                'id' => $id,
                                'softwareversions_id'
                                => $input['softwareversions_id']
                                ])
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

            case 'add':
                $itemtoadd = new Item_SoftwareVersion();
                if (isset($_POST['peer_softwareversions_id'])) {
                    foreach ($ids as $id) {
                        if ($item->can($id, UPDATE)) {
                            //Process rules
                            if (
                                $itemtoadd->add([
                                'items_id'              => $id,
                                'itemtype'              => $item::getType(),
                                'softwareversions_id'   => $_POST['peer_softwareversions_id']
                                ])
                            ) {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                            } else {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                                $ma->addMessage($itemtoadd->getErrorMessage(ERROR_ON_ACTION));
                            }
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
                            $ma->addMessage($itemtoadd->getErrorMessage(ERROR_RIGHT));
                        }
                    }
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                }
                return;
        }

        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }


    /**
     * @param $computers_id
     **/
    public function updateDatasForComputer($computers_id)
    {

        Toolbox::deprecated('Use updateDatasForItem()');
        return $this->updateDatasForItem('Computer', $computers_id);
    }


    public function updateDatasForItem($itemtype, $items_id)
    {
        global $DB;

        $item = new $itemtype();
        if ($item->getFromDB($items_id)) {
            $result = $DB->update(
                $this->getTable(),
                [
                  'is_template_item'  => $item->maybeTemplate() ? $item->getField('is_template') : 0,
                  'is_deleted_item'   => $item->maybeDeleted() ? $item->getField('is_deleted') : 0
                ],
                [
                  'items_id' => $items_id,
                  'itemtype' => $itemtype
                ]
            );
            return $result;
        }
        return false;
    }

    /**
     * Get number of installed licenses of a version
     *
     * @param integer          $softwareversions_id version ID
     * @param string|integer[] $entity              to search for item in ('' = all active entities)
     *
     * @return integer number of installations
     **/
    public static function countForVersion($softwareversions_id, $entity = '')
    {
        $item_version_table = self::getTable(__CLASS__);
        $request = self::getAdapter()->request([
           'SELECT'    => ['itemtype'],
           'DISTINCT'  => true,
           'FROM'      => $item_version_table,
           'WHERE'     => [
              'softwareversions_id'   => $softwareversions_id
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
               'FROM'         => 'glpi_items_softwareversions',
               'COUNT'        => 'cpt',
               'INNER JOIN'   => [
                  $itemtable  => [
                     'FKEY'   => [
                        $itemtable                    => 'id',
                        'glpi_items_softwareversions' => 'items_id', [
                           'AND' => [
                              'glpi_items_softwareversions.itemtype' => $itemtype
                           ]
                        ]
                     ]
                  ]
               ],
               'WHERE'        => [
                  'glpi_items_softwareversions.softwareversions_id'     => $softwareversions_id,
                  'glpi_items_softwareversions.is_deleted'              => 0
               ] + getEntitiesRestrictCriteria($itemtable, '', $entity)
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
     * Get number of installed versions of a software
     *
     * @param $softwares_id software ID
     *
     * @return number of installations
     **/
    public static function countForSoftware($softwares_id)
    {
        $request = self::getAdapter()->request([
           'SELECT'    => ['itemtype'],
           'DISTINCT'  => true,
           'FROM'      => 'glpi_softwareversions',
           'INNER JOIN'   => [
              'glpi_items_softwareversions'   => [
                 'FKEY'   => [
                    'glpi_items_softwareversions' => 'softwareversions_id',
                    'glpi_softwareversions'       => 'id'
                 ]
              ],
           ],
           'WHERE'     => [
              'softwares_id' => $softwares_id
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
               'FROM'         => 'glpi_softwareversions',
               'COUNT'        => 'cpt',
               'INNER JOIN'   => [
                  'glpi_items_softwareversions'   => [
                     'FKEY'   => [
                        'glpi_items_softwareversions' => 'softwareversions_id',
                        'glpi_softwareversions'       => 'id'
                     ]
                  ],
                  $itemtable  => [
                     'FKEY'   => [
                        $itemtable                    => 'id',
                        'glpi_items_softwareversions' => 'items_id', [
                           'AND' => [
                              'glpi_items_softwareversions.itemtype' => $itemtype
                           ]
                        ]
                     ]
                  ]
               ],
               'WHERE'        => [
                  'glpi_softwareversions.softwares_id'      => $softwares_id,
                  'glpi_items_softwareversions.is_deleted'  => 0
               ] + getEntitiesRestrictCriteria($itemtable, '', '', true)
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
     * Show installation of a Software
     *
     * @param $software Software object
     *
     * @return void
     **/
    public static function showForSoftware(Software $software)
    {
        self::showInstallations($software->getField('id'), 'softwares_id');
    }


    /**
     * Show installation of a Version
     *
     * @param $version SoftwareVersion object
     *
     * @return void
     **/
    public static function showForVersion(SoftwareVersion $version)
    {
        self::showInstallations($version->getField('id'), 'id');
    }


    /**
     * Show installations of a software
     *
     * @param integer $searchID  value of the ID to search
     * @param string  $crit      to search : softwares_id (software) or id (version)
     *
     * @return void
     **/
    private static function showInstallations($searchID, $crit)
    {
        global $DB, $CFG_GLPI;

        if (!Software::canView() || !$searchID) {
            return;
        }

        $canedit       = Session::haveRightsOr("software", [CREATE, UPDATE, DELETE, PURGE]);
        $canshowitems  = [];
        $item_version_table = self::getTable(__CLASS__);

        $refcolumns = [
           'vername'           => _n('Version', 'Versions', Session::getPluralNumber()),
           'item_type'          => __('Item type'),
           'itemname'          => __('Name'),
           'entity'            => Entity::getTypeName(1),
           'serial'            => __('Serial number'),
           'otherserial'       => __('Inventory number'),
           'location,itemname' => Location::getTypeName(1),
           'state,itemname'    => __('Status'),
           'groupe,itemname'   => Group::getTypeName(1),
           'username,itemname' => User::getTypeName(1),
           'lname'             => SoftwareLicense::getTypeName(Session::getPluralNumber()),
           'date_install'      => __('Installation date')
        ];
        if ($crit != "softwares_id") {
            unset($refcolumns['vername']);
        }

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

        if (isset($_GET["sort"]) && !empty($_GET["sort"]) && isset($refcolumns[$_GET["sort"]])) {
            // manage several param like location,compname :  order first
            $tmp  = explode(",", $_GET["sort"]);
            $sort = "`" . implode("` $order,`", $tmp) . "`";
        } else {
            if ($crit == "softwares_id") {
                $sort = "`entity` $order, `version`, `itemname`";
            } else {
                $sort = "`entity` $order, `itemname`";
            }
        }

        // Total Number of events
        if ($crit == "softwares_id") {
            // Software ID
            $number = self::countForSoftware($searchID);
        } else {
            //SoftwareVersion ID
            $number = self::countForVersion($searchID);
        }

        echo "<div class='center'>";
        if ($number < 1) {
            echo "<table class='tab_cadre_fixe' aria-label='No item Found'>";
            echo "<tr><th>" . __('No item found') . "</th></tr>";
            echo "</table></div>\n";
            return;
        }

        // Display the pager
        Html::printAjaxPager(self::getTypeName(Session::getPluralNumber()), $start, $number);

        $queries = [];
        foreach ($CFG_GLPI['software_types'] as $itemtype) {
            $canshowitems[$itemtype] = $itemtype::canView();
            $itemtable = $itemtype::getTable();
            $query = [
               'SELECT' => [
                  $item_version_table . '.*',
                  'glpi_softwareversions.name AS version',
                  'glpi_softwareversions.softwares_id AS sID',
                  'glpi_softwareversions.id AS vID',
                  "{$itemtable}.name AS itemname",
                  "{$itemtable}.id AS iID",
                  new QueryExpression($DB->quoteValue($itemtype) . " AS " . $DB::quoteName('item_type')),
               ],
               'FROM'   => $item_version_table,
               'INNER JOIN' => [
                  'glpi_softwareversions' => [
                     'FKEY'   => [
                        $item_version_table     => 'softwareversions_id',
                        'glpi_softwareversions' => 'id'
                     ]
                  ]
               ],
               'LEFT JOIN' => [
                  $itemtable => [
                     'FKEY'   => [
                        $item_version_table  => 'items_id',
                        $itemtable        => 'id', [
                           'AND' => [
                              $item_version_table . '.itemtype'  => $itemtype
                           ]
                        ]
                     ]
                  ]
               ],
               'WHERE'     => [
                  "glpi_softwareversions.$crit"                => $searchID,
                  'glpi_items_softwareversions.is_deleted'     => 0
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
                      'glpi_entities'   => 'id'
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
                      'glpi_locations'   => 'id'
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
                      'glpi_states'   => 'id'
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
                      'glpi_groups'   => 'id'
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
        
        $rand = mt_rand();

        if ($data = $request->fetchAssociative()) {
            $softwares_id  = $data['sID'];
            $soft          = new Software();
            $showEntity    = ($soft->getFromDB($softwares_id) && $soft->isRecursive());
            $linkUser      = User::canView();
            $title         = $soft->fields["name"];

            if ($crit == "id") {
                $title = sprintf(__('%1$s - %2$s'), $title, $data["version"]);
            }

            Session::initNavigateListItems(
                $data['item_type'],
                //TRANS : %1$s is the itemtype name,
                //        %2$s is the name of the item (used for headings of a list)
                sprintf(
                    __('%1$s = %2$s'),
                    Software::getTypeName(1),
                    $title
                )
            );

            if ($canedit) {
                $rand = mt_rand();
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams
                   = [
                      'num_displayed'
                      => min($_SESSION['glpilist_limit'], $number),
                      'container'
                      => 'mass' . __CLASS__ . $rand,
                      'specific_actions'
                      => [
                         __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'move_version'
                         => _x('button', 'Move'),
                         'purge' => _x('button', 'Delete permanently')
                      ]
                   ];
                // Options to update version
                $massiveactionparams['extraparams']['options']['move']['softwares_id'] = $softwares_id;
                if ($crit == 'softwares_id') {
                    $massiveactionparams['extraparams']['options']['move']['used'] = [];
                } else {
                    $massiveactionparams['extraparams']['options']['move']['used'] = [$searchID];
                }

                Html::showMassiveActions($massiveactionparams);
            }

            echo "<table class='tab_cadre_fixehov' aria-label='Item detail'>";

            $header_begin  = "<tr>";
            $header_top    = '';
            $header_bottom = '';
            $header_end    = '';
            if ($canedit) {
                $header_begin  .= "<th width='10'>";
                $header_top    .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                $header_end    .= "</th>";
            }
            $columns = $refcolumns;
            if (!$showEntity) {
                unset($columns['entity']);
            }

            foreach ($columns as $key => $val) {
                // Non order column
                if ($key[0] == '_') {
                    $header_end .= "<th>$val</th>";
                } else {
                    $header_end .= "<th" . ($sort == "`$key`" ? " class='order_$order'" : '') . ">" .
                       "<a href='javascript:reloadTab(\"sort=$key&amp;order=" .
                       (($order == "ASC") ? "DESC" : "ASC") . "&amp;start=0\");'>$val</a></th>";
                }
            }

            $header_end .= "</tr>\n";
            echo $header_begin . $header_top . $header_end;

            do {
                Session::addToNavigateListItems($data['item_type'], $data["iID"]);

                echo "<tr class='tab_bg_2'>";
                if ($canedit) {
                    echo "<td>";
                    Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
                    echo "</td>";
                }

                if ($crit == "softwares_id") {
                    echo "<td><a href='" . SoftwareVersion::getFormURLWithID($data['vID']) . "'>" .
                       $data['version'] . "</a></td>";
                }

                $itemname = $data['itemname'];
                if (empty($itemname) || $_SESSION['glpiis_ids_visible']) {
                    $itemname = sprintf(__('%1$s (%2$s)'), $itemname, $data['iID']);
                }

                echo "<td>{$data['item_type']}</td>";

                if ($canshowitems[$data['item_type']]) {
                    echo "<td><a href='" . $data['item_type']::getFormURLWithID($data['iID']) . "'>$itemname</a></td>";
                } else {
                    echo "<td>" . $itemname . "</td>";
                }

                if ($showEntity) {
                    echo "<td>" . $data['entity'] . "</td>";
                }
                echo "<td>" . $data['serial'] . "</td>";
                echo "<td>" . $data['otherserial'] . "</td>";
                echo "<td>" . $data['location'] . "</td>";
                echo "<td>" . $data['state'] . "</td>";
                echo "<td>" . $data['groupe'] . "</td>";
                echo "<td>" . formatUserName(
                    $data['userid'],
                    $data['username'],
                    $data['userrealname'],
                    $data['userfirstname'],
                    $linkUser
                ) . "</td>";

                $lics = Item_SoftwareLicense::getLicenseForInstallation(
                    $data['item_type'],
                    $data['iID'],
                    $data['vID']
                );
                echo "<td>";

                if (count($lics)) {
                    foreach ($lics as $lic) {
                        $serial = $lic['serial'];

                        if (!empty($lic['type'])) {
                            $serial = sprintf(__('%1$s (%2$s)'), $serial, $lic['type']);
                        }

                        echo "<a href='" . SoftwareLicense::getFormURLWithID($lic['id']) . "'>" . $lic['name'];
                        echo "</a> - " . $serial;

                        echo "<br>";
                    }
                }
                echo "</td>";

                echo "<td>" . Html::convDate($data['date_install']) . "</td>";
                echo "</tr>\n";
            } while ($data = $iterator->next());

            echo $header_begin . $header_bottom . $header_end;

            echo "</table>\n";
            if ($canedit) {
                $massiveactionparams['ontop'] = false;
                Html::showMassiveActions($massiveactionparams);
                Html::closeForm();
            }
        } else { // Not found
            echo __('No item found');
        }
        Html::printAjaxPager(self::getTypeName(Session::getPluralNumber()), $start, $number);

        echo "</div>\n";
    }


    /**
     * Show number of installations per entity
     *
     * @param $version SoftwareVersion object
     *
     * @return void
     **/
    public static function showForVersionByEntity(SoftwareVersion $version)
    {

        $softwareversions_id = $version->getField('id');

        if (!Software::canView() || !$softwareversions_id) {
            return;
        }

        echo "<div class='center'>";
        echo "<table class='tab_cadre' aria-label='software Version'><tr>";
        echo "<th>" . Entity::getTypeName(1) . "</th>";
        echo "<th>" . self::getTypeName(Session::getPluralNumber()) . "</th>";
        echo "</tr>\n";

        $tot = 0;

        $request = self::getAdapter()->request([
           'SELECT' => ['id', 'completename'],
           'FROM'   => 'glpi_entities',
           'WHERE'  => getEntitiesRestrictCriteria('glpi_entities'),
           'ORDER'  => ['completename']
        ]);

        while ($data = $request->fetchAssociative()) {
            $nb = self::countForVersion($softwareversions_id, $data['id']);
            if ($nb > 0) {
                echo "<tr class='tab_bg_2'><td>" . $data["completename"] . "</td>";
                echo "<td class='numeric'>" . $nb . "</td></tr>\n";
                $tot += $nb;
            }
        }

        if ($tot > 0) {
            echo "<tr class='tab_bg_1'><td class='center b'>" . __('Total') . "</td>";
            echo "<td class='numeric b'>" . $tot . "</td></tr>\n";
        } else {
            echo "<tr class='tab_bg_1'><td colspan='2 b'>" . __('No item found') . "</td></tr>\n";
        }
        echo "</table></div>";
    }


    /**
     * Show software installed on a computer
     *
     * @param Computer $comp         Computer object
     * @param boolean  $withtemplate template case of the view process
     *
     * @return void
     **/
    public static function showForComputer(Computer $comp, $withtemplate = 0)
    {

        Toolbox::deprecated('Use showForItem()');
        self::showForItem($comp, $withtemplate);
    }


    /**
     * Get softwares related to a given item
     *
     * @param CommonDBTM $item  Item instance
     * @param string     $sort  Field to sort on
     * @param string     $order Sort order
     *
     * @return Doctrine\DBAL\Result
     */
    public static function getFromItem(CommonDBTM $item, $sort = null, $order = null): Doctrine\DBAL\Result
    {
        $selftable     = self::getTable(__CLASS__);

        $select = [
           'glpi_softwares.softwarecategories_id',
           'glpi_softwares.name AS softname',
           "glpi_items_softwareversions.id",
           'glpi_states.name as state',
           'glpi_softwareversions.id AS verid',
           'glpi_softwareversions.softwares_id',
           'glpi_softwareversions.name AS version',
           'glpi_softwares.is_valid AS softvalid',
           'glpi_items_softwareversions.date_install AS dateinstall'
        ];

        if (Plugin::haveImport()) {
            $select[] = "{$selftable}.is_dynamic";
        }

        $request = [
           'SELECT'    => $select,
           'FROM'      => $selftable,
           'LEFT JOIN' => [
              'glpi_softwareversions' => [
                 'FKEY'   => [
                    $selftable              => 'softwareversions_id',
                    'glpi_softwareversions' => 'id'
                 ]
              ],
              'glpi_states'  => [
                 'FKEY'   => [
                    'glpi_softwareversions' => 'states_id',
                    'glpi_states'           => 'id'
                 ]
              ],
              'glpi_softwares'  => [
                 'FKEY'   => [
                    'glpi_softwareversions' => 'softwares_id',
                    'glpi_softwares'        => 'id'
                 ]
              ]
           ],
           'WHERE'     => [
              "{$selftable}.items_id"  => $item->getField('id'),
              "{$selftable}.itemtype"    => $item->getType()
           ] + getEntitiesRestrictCriteria('glpi_softwares', '', '', true),
           'ORDER'     => ['softname', 'version']
        ];

        if ($item->maybeDeleted()) {
            $request['WHERE']["{$selftable}.is_deleted"] = 0;
        }

        $crit = Session::getSavedOption(__CLASS__, 'criterion', -1);
        if ($crit > -1) {
            $request['WHERE']['glpi_softwares.softwarecategories_id'] = (int)$crit;
        }

        $request = self::getAdapter()->request($request);
        return $request;
    }

    /**
     * Show software installed on a computer
     *
     * @param Computer $comp         Computer object
     * @param boolean  $withtemplate template case of the view process
     *
     * @return void
     **/
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {
        global $CFG_GLPI;

        if (!Software::canView()) {
            return;
        }

        $items_id      = $item->getField('id');
        $itemtype      = $item->getType();
        $rand          = mt_rand();
        $canedit       = Session::haveRightsOr("software", [CREATE, UPDATE, DELETE, PURGE]);
        $entities_id   = $item->fields["entities_id"];

        $crit         = Session::getSavedOption(__CLASS__, 'criterion', -1);

        $resultRequest = self::getFromItem($item);

        if (
            (empty($withtemplate) || ($withtemplate != 2))
            && $canedit
        ) {
            $form = [
               'action' => Item_SoftwareVersion::getFormURL(),
               'buttons' => [
                  'Install' => [
                     'type' => 'submit',
                     'name' => 'add',
                     'value' => _x('button', 'Install'),
                     'class' => 'btn btn-primary mb-3'
                  ]
               ],
               'content' => [
                  __("Software") => [
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
                           'value' => $items_id
                        ],
                        _n('Software', 'Software', Session::getPluralNumber()) => [
                           'type' => 'select',
                           'id' => 'dropdownForSoftware',
                           'name' => 'softwareversions_id',
                           'itemtype' => 'Software',
                           'col_lg' => 6,
                           'hooks' => [
                              'change' => <<<JS
                              var softwareversions_id = $(this).val();
                              var url = '{$CFG_GLPI["root_doc"]}/ajax/dropdownInstallVersion.php';

                              if (softwareversions_id == 0) {
                                 $('#dropdownForSoftwareVersion').empty();
                                 $('#dropdownForSoftwareVersion').prop('disabled', true);
                                 return;
                              }
                              $.ajax({
                                 url: url,
                                 type: 'POST',
                                 data: {
                                    softwares_id: softwareversions_id,
                                 },
                                 dataType: 'json',
                                 success: function(data) {

                                    $('#dropdownForSoftwareVersion').prop('disabled', false);
                                    $('#dropdownForSoftwareVersion').empty();
                                    for (const [key, value] of Object.entries(data)) {
                                       $('#dropdownForSoftwareVersion').append(
                                          $('<option></option>').val(key).html(value)
                                       )
                                    }
                                 },
                              });
                           JS,
                           ]
                        ],
                        __("Version") => [
                           'type' => 'select',
                           'id' => 'dropdownForSoftwareVersion',
                           'name' => 'softwareversions_id',
                           'values' => getOptionForItems('Software', ['entities_id' => $entities_id]),
                           'col_lg' => 6,
                           'disabled' => true,
                        ],
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }
        $results = $resultRequest->fetchAllAssociative();
        $number = count($results);
        $start  = (isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0);
        if ($start >= $number) {
            $start = 0;
        }

        $installed = [];
        $installedSoftware = [];

        $massActionContainerSoftware = 'mass' . __CLASS__ . $rand;
        if ($canedit) {
            $massiveactionparams = [
               'container' => $massActionContainerSoftware,
               'specific_actions' => [
                  'purge' => _x('button', 'Delete permanently')
               ],
               'display_arrow' => false,
            ];

            Html::showMassiveActions($massiveactionparams);
        }
        $fields = [
           __('Name'),
           __('Status'),
           _n('Version', 'Versions', 1),
           SoftwareLicense::getTypeName(1),
           __('Installation date')
        ];
        if (Plugin::haveImport()) {
            $fields[] = __('Automatic inventory');
        }
        $fields[] = SoftwareCategory::getTypeName(1);
        $fields[] = __('Valid license');

        $values = [];
        $massive_action = [];
        $datas = iterator_to_array($iterator);
        foreach ($datas as $data) {
            $licids = self::softwareByCategory(
                $data,
                $itemtype,
                $items_id,
                $withtemplate,
                $canedit,
                false
            );

            $category = new SoftwareCategory();
            $category->getFromDB($data['softwarecategories_id']);
            $soft = new Software();
            $soft->getFromDB($data['softwares_id']);
            $version = new SoftwareVersion();
            $version->getFromDB($data['verid']);
            $newValue = [
               $soft->getLink(),
               $data['state'],
               $version->getLink(),
               implode('<br>', $licids),
               Html::convDate($data['dateinstall']),
            ];
            if (Plugin::haveImport()) {
                $newValue[] = $data['is_dynamic'] ? __('Yes') : __('No');
            }
            $newValue[] = $category->getLink();
            $newValue[] = $data['softvalid'] ? __('Yes') : __('No');
            $values[] = $newValue;
            $installedSoftware[] = $data['softwares_id'];
            foreach ($licids as $licid) {
                Session::addToNavigateListItems('SoftwareLicense', $licid);
                $installed[] = $licid;
            }
            $massive_action[] = sprintf('item[%s][%s]', self::class, $data['id']);
        }
        renderTwigTemplate('table.twig', [
           'id' => $massActionContainerSoftware,
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);

        if (
            (empty($withtemplate) || ($withtemplate != 2))
            && $canedit
        ) {
            $allOptions = getOptionForItems(Software::class, ['entities_id' => $entities_id, 'is_template' => 0]);
            $options = [];
            foreach ($installedSoftware as $id) {
                if (isset($allOptions[$id])) {
                    $options[$id] = $allOptions[$id];
                }
            }
            $form = [
               'action' => Item_SoftwareLicense::getFormURL(),
               'buttons' => [
                  'Install' => [
                     'type' => 'submit',
                     'name' => 'add',
                     'value' => _x('button', 'Install'),
                     'class' => 'btn btn-primary mb-3'
                  ]
               ],
               'content' => [
                  SoftwareLicense::getTypeName(Session::getPluralNumber()) => [
                     'visible' => 'true',
                     'inputs' => [
                        SoftwareLicense::getTypeName(Session::getPluralNumber()) => [
                           'type' => 'select',
                           'id' => 'licenceDropdown',
                           'name' => 'softwarelicenses_id',
                           'itemtype' => 'SoftwareLicense',
                           'conditions' => ['entities_id' => $entities_id],
                           'disabled' => true,
                           'col_lg' => 6,
                        ],
                        [
                           'type' => 'hidden',
                           'name' => 'itemtype',
                           'value' => $itemtype
                        ],
                        [
                           'type' => 'hidden',
                           'name' => 'items_id',
                           'value' => $items_id
                        ],
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }
        $lic_where = [];
        if (count($installed)) {
            $lic_where['NOT'] = ['glpi_softwarelicenses.id' => $installed];
        }

        $lic_request = [
           'SELECT'       => [
              'glpi_softwarelicenses.*',
              'glpi_items_softwarelicenses.id AS linkid',
              'glpi_softwares.name AS softname',
              'glpi_softwareversions.name AS version',
              'glpi_states.name AS state'
           ],
           'FROM'         => SoftwareLicense::getTable(),
           'INNER JOIN'   => [
              'glpi_softwares'  => [
                 'FKEY'   => [
                    'glpi_softwarelicenses' => 'softwares_id',
                    'glpi_softwares'        => 'id'
                 ]
              ]
           ],
           'LEFT JOIN'    => [
              'glpi_items_softwarelicenses'   => [
                 'FKEY'   => [
                    'glpi_items_softwarelicenses' => 'softwarelicenses_id',
                    'glpi_softwarelicenses'       => 'id'
                 ]
              ],
              'glpi_softwareversions'   => [
                 'FKEY'   => [
                    'glpi_softwareversions' => 'id',
                    'glpi_softwarelicenses' => 'softwareversions_id_use',
                    [
                       'AND' => [
                          'glpi_softwarelicenses.softwareversions_id_use' => 0,
                          'glpi_softwarelicenses.softwareversions_id_buy' => new \QueryExpression(DBmysql::quoteName('glpi_softwareversions.id')),
                       ]
                    ]
                 ]
              ],
              'glpi_states'  => [
                 'FKEY'   => [
                    'glpi_softwareversions' => 'states_id',
                    'glpi_states'           => 'id'
                 ]
              ]
           ],
           'WHERE'     => [
              'glpi_items_softwarelicenses.items_id'  => $items_id,
              'glpi_items_softwarelicenses.itemtype'  => $itemtype,
           ] + $lic_where,
           'ORDER'     => ['softname', 'version']
        ];
        if ($item->maybeDeleted()) {
            $lic_request['WHERE']['glpi_items_softwarelicenses.is_deleted'] = 0;
        }
        $lic_request = self::getAdapter()->request($lic_request);
        
        $massActionContainerSoftwareLicense = 'massSoftwareLicense' . $rand;
        if ($canedit) {
            $actions = [
               'Item_SoftwareLicense' . MassiveAction::CLASS_ACTION_SEPARATOR .
               'install' => _x('button', 'Install')
            ];
            if (SoftwareLicense::canUpdate()) {
                $actions['purge'] = _x('button', 'Delete permanently');
            }

            $massiveactionparams = [
               'display_arrow' => false,
               'container'        => $massActionContainerSoftwareLicense,
               'specific_actions' => $actions,
               'is_deleted' => 0,
            ];

            Html::showMassiveActions($massiveactionparams);
        }
        $fields = [
           __('Name'),
           __('Status'),
           _n('Version', 'Versions', 1),
           SoftwareLicense::getTypeName(1),
        ];
        $values = [];
        $massive_action = [];
        $data = $lic_request->fetchAllAssociative();
        foreach ($datas as $data) {
            $newValue = [
               $data['softname'],
               $data['state'],
               $data['version'],
               $data['name'],
            ];
            $values[] = $newValue;
            $massive_action[] = sprintf('item[%s][%s]', Item_SoftwareLicense::class, $data['linkid']);
        }
        renderTwigTemplate('table.twig', [
           'id' => $massActionContainerSoftwareLicense,
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);
    }


    /**
     * Display a installed software for a category
     *
     * @param array   $data         data used to display
     * @param integer $computers_id ID of the computer
     * @param boolean $withtemplate template case of the view process
     * @param boolean $canedit      user can edit software ?
     * @param boolean $display      display and calculte if true or juste calculate
     *
     * @return integer[] Found licenses ids
     **/
    private static function softsByCategory(
        $data,
        $computers_id,
        $withtemplate,
        $canedit,
        $display
    ) {
        Toolbox::deprecated('Use softwareByCategory()');
        return self::softwareByCategory($data, 'Computer', $computers_id, $withtemplate, $canedit, $display);
    }


    /**
     * Display a installed software for a category
     *
     * @param array   $data         data used to display
     * @param string  $itemtype     Type of the item
     * @param integer $items_id     ID of the item
     * @param boolean $withtemplate template case of the view process
     * @param boolean $canedit      user can edit software ?
     * @param boolean $display      display and calculate if true or just calculate
     *
     * @return integer[] Found licenses ids
     **/
    private static function softwareByCategory(
        $data,
        $itemtype,
        $items_id,
        $withtemplate,
        $canedit,
        $display
    ) {

        $ID    = $data["id"];
        $verid = $data["verid"];

        if ($display) {
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
                echo "<td>";
                Html::showMassiveActionCheckBox(__CLASS__, $ID);
                echo "</td>";
            }
            echo "<td class='b'>";
            echo "<a href='" . Software::getFormURLWithID($data['softwares_id']) . "'>";
            echo($_SESSION["glpiis_ids_visible"] ? sprintf(
                __('%1$s (%2$s)'),
                $data["softname"],
                $data['softwares_id']
            )
               : $data["softname"]);
            echo "</a></td>";
            echo "<td>" . $data["state"] . "</td>";

            echo "<td>" . $data["version"];
            echo "</td><td>";
        }

        $request = self::getAdapter()->request([
           'SELECT'       => [
              'glpi_softwarelicenses.*',
              'glpi_softwarelicensetypes.name AS type'
           ],
           'FROM'         => 'glpi_items_softwarelicenses',
           'INNER JOIN'   => [
              'glpi_softwarelicenses' => [
                 'FKEY'   => [
                    'glpi_items_softwarelicenses'   => 'softwarelicenses_id',
                    'glpi_softwarelicenses'             => 'id'
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
              "glpi_items_softwarelicenses.items_id"    => $items_id,
              'glpi_items_softwarelicenses.itemtype'    => $itemtype,
              'OR'                                            => [
                 'glpi_softwarelicenses.softwareversions_id_use' => $verid,
                 [
                    'glpi_softwarelicenses.softwareversions_id_use' => 0,
                    'glpi_softwarelicenses.softwareversions_id_buy' => $verid
                 ]
              ]
           ]
        ]);

        $licids = [];
        while ($licdata = $request->fetchAssociative()) {
            $licids[]  = $licdata['id'];
            $licserial = $licdata['serial'];

            if (!empty($licdata['type'])) {
                $licserial = sprintf(__('%1$s (%2$s)'), $licserial, $licdata['type']);
            }

            if ($display) {
                echo "<span class='b'>" . $licdata['name'] . "</span> - " . $licserial;

                $link_item = Toolbox::getItemTypeFormURL('SoftwareLicense');
                $link      = $link_item . "?id=" . $licdata['id'];
                $comment   = "<table aria-label='Software License'><tr><td>" . __('Name') . "</td><td>" . $licdata['name'] . "</td></tr>" .
                   "<tr><td>" . __('Serial number') . "</td><td>" . $licdata['serial'] . "</td></tr>" .
                   "<tr><td>" . __('Comments') . '</td><td>' . $licdata['comment'] . "</td></tr>" .
                   "</table>";

                Html::showToolTip($comment, ['link' => $link]);
                echo "<br>";
            }
        }

        if ($display) {
            if (!count($licids)) {
                echo "&nbsp;";
            }

            echo "</td>";

            echo "<td>" . Html::convDate($data['dateinstall']) . "</td>";

            if (isset($data['is_dynamic'])) {
                echo "<td>" . Dropdown::getYesNo($data['is_dynamic']) . "</td>";
            }

            echo "<td>" . Dropdown::getDropdownName(
                "glpi_softwarecategories",
                $data['softwarecategories_id']
            );
            echo "</td>";
            echo "<td>" . Dropdown::getYesNo($data["softvalid"]) . "</td>";
            echo "</tr>\n";
        }

        return $licids;
    }


    /**
     * Display a software for a License (not installed)
     *
     * @param array   $data         data used to display
     * @param integer $computers_id ID of the computer
     * @param boolean $withtemplate template case of the view process
     * @param boolean $canedit      user can edit software ?
     *
     * @return void
     */
    private static function displaySoftsByLicense($data, $computers_id, $withtemplate, $canedit)
    {

        Toolbox::deprecated('Use displaySoftwareByLicense()');
        return self::displaySoftwareByLicense($data, $withtemplate, $canedit);
    }


    /**
     * Display a software for a License (not installed)
     *
     * @param array   $data         data used to display
     * @param boolean $withtemplate template case of the view process
     * @param boolean $canedit      user can edit software ?
     *
     * @return void
     */
    private static function displaySoftwareByLicense($data, $withtemplate, $canedit)
    {

        $ID = $data['linkid'];

        $link_item = Toolbox::getItemTypeFormURL('SoftwareLicense');
        $link      = $link_item . "?id=" . $data['id'];

        echo "<tr class='tab_bg_1'>";
        if ($canedit) {
            echo "<td>";
            if (empty($withtemplate) || ($withtemplate != 2)) {
                Html::showMassiveActionCheckBox('Item_SoftwareLicense', $ID);
            }
            echo "</td>";
        }

        echo "<td class='center b'>";
        echo "<a href='" . Software::getFormURLWithID($data['softwares_id']) . "'>";
        echo($_SESSION["glpiis_ids_visible"] ? sprintf(
            __('%1$s (%2$s)'),
            $data["softname"],
            $data['softwares_id']
        )
           : $data["softname"]);
        echo "</a></td>";
        echo "<td>" . $data["state"] . "</td>";

        echo "<td>" . $data["version"];

        $serial = $data["serial"];

        if ($data["softwarelicensetypes_id"]) {
            $serial = sprintf(
                __('%1$s (%2$s)'),
                $serial,
                Dropdown::getDropdownName(
                    "glpi_softwarelicensetypes",
                    $data["softwarelicensetypes_id"]
                )
            );
        }
        echo "</td><td class='b'>" . $data["name"] . " - " . $serial;

        $comment = "<table aria-label='Software License ID'><tr><td>" . __('Name') . "</td>" . "<td>" . $data['name'] . "</td></tr>" .
           "<tr><td>" . __('Serial number') . "</td><td>" . $data['serial'] . "</td></tr>" .
           "<tr><td>" . __('Comments') . "</td><td>" . $data['comment'] . "</td></tr></table>";

        Html::showToolTip($comment, ['link' => $link]);
        echo "</td></tr>\n";
    }


    /**
     * Update version installed on a item
     *
     * @param integer $instID              ID of the installed software link
     * @param integer $softwareversions_id ID of the new version
     * @param boolean $dohistory           Do history ? (default 1)
     *
     * @return void
     **/
    public function upgrade($instID, $softwareversions_id, $dohistory = 1)
    {

        if ($this->getFromDB($instID)) {
            $items_id = $this->fields['items_id'];
            $itemtype = $this->fields['itemtype'];
            $this->delete(['id' => $instID]);
            $this->add([
               'itemtype'              => $itemtype,
               'items_id'              => $items_id,
               'softwareversions_id'   => $softwareversions_id
            ]);
        }
    }


    /**
     * Duplicate all software from a computer template to its clone
     *
     * @deprecated 9.5
     *
     * @param integer $oldid ID of the computer to clone
     * @param integer $newid ID of the computer cloned
     **/
    public static function cloneComputer($oldid, $newid)
    {

        Toolbox::deprecated('Use clone');
        return self::cloneItem('Computer', $oldid, $newid);
    }


    /**
     * Duplicate all software from a item template to its clone
     *
     * @deprecated 9.5
     *
     * @param string  $itemtype Itemtype of the item to clone
     * @param integer $oldid ID of the item to clone
     * @param integer $newid ID of the item cloned
     **/
    public static function cloneItem($itemtype, $oldid, $newid)
    {
        Toolbox::deprecated('Use clone');
        $request = self::getAdapter()->request([
           'FROM'   => 'glpi_items_softwareversions',
           'WHERE'  => [
              'items_id' => $oldid,
              'itemtype' => $itemtype
           ]
        ]);

        while ($data = $request->fetchAssociative()) {
            $csv                  = new self();
            unset($data['id']);
            $data['itemtype'] = $itemtype;
            $data['items_id'] = $newid;
            $data['_no_history']  = true;

            $csv->add($data);
        }
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        $nb = 0;
        switch ($item->getType()) {
            case 'Software':
                if (!$withtemplate) {
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = self::countForSoftware($item->getID());
                    }
                    return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
                }
                break;

            case 'SoftwareVersion':
                if (!$withtemplate) {
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = self::countForVersion($item->getID());
                    }
                    return [
                       1 => __('Summary'),
                       2 => self::createTabEntry(
                           self::getTypeName(Session::getPluralNumber()),
                           $nb
                       )
                    ];
                }
                break;

            default:
                // Installation allowed for template
                if (Software::canView()) {
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = self::countForItem($item);
                    }
                    return self::createTabEntry(Software::getTypeName(Session::getPluralNumber()), $nb);
                }
                break;
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == 'Software') {
            self::showForSoftware($item);
        } elseif ($item->getType() == 'SoftwareVersion') {
            switch ($tabnum) {
                case 1:
                    self::showForVersionByEntity($item);
                    break;

                case 2:
                    self::showForVersion($item);
                    break;
            }
        } else {
            self::showForItem($item, $withtemplate);
        }
        return true;
    }


    protected static function getListForItemParams(CommonDBTM $item, $noent = false)
    {
        $table = self::getTable(__CLASS__);

        $params = parent::getListForItemParams($item);
        unset($params['SELECT'], $params['ORDER']);
        $params['WHERE'] = [
           $table . '.items_id'   => $item->getID(),
           $table . '.itemtype'   => $item::getType(),
           $table . '.is_deleted' => 0
        ];
        if ($noent === false) {
            $params['WHERE'] += getEntitiesRestrictCriteria($table, '', '', 'auto');
        }
        return $params;
    }

    public static function countForItem(CommonDBTM $item)
    {
        $params = self::getListForItemParams($item);
        unset($params['SELECT'], $params['ORDER']);
        $params['COUNT'] = 'cpt';
        $request = self::getAdapter()->request($params);
        return $request->fetchAssociative()['cpt'];
    }
}
