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

class Domain_Item extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1 = "Domain";
    public static $items_id_1 = 'domains_id';

    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';

    public static $rightname = 'domain';

    public static function cleanForItem(CommonDBTM $item)
    {
        $temp = new self();
        $temp->deleteByCriteria(
            ['itemtype' => $item->getType(),
                 'items_id' => $item->getField('id')]
        );
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$withtemplate) {
            if (
                $item->getType() == 'Domain'
                && count(Domain::getTypes(false))
            ) {
                if ($_SESSION['glpishow_count_on_tabs']) {
                    return self::createTabEntry(_n('Associated item', 'Associated items', Session::getPluralNumber()), self::countForDomain($item));
                }
                return _n('Associated item', 'Associated items', Session::getPluralNumber());
            } elseif (
                $item->getType() == 'DomainRelation' || in_array($item->getType(), Domain::getTypes(true))
                       && Session::haveRight('domain', READ)
            ) {
                if ($_SESSION['glpishow_count_on_tabs']) {
                    return self::createTabEntry(Domain::getTypeName(Session::getPluralNumber()), self::countForItem($item));
                }
                return Domain::getTypeName(2);
            }
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'Domain') {
            self::showForDomain($item);
        } elseif (
            in_array($item->getType(), Domain::getTypes(true))
            || $item->getType() == DomainRelation::getType()
        ) {
            self::showForItem($item);
        }
        return true;
    }

    public static function countForDomain(Domain $item)
    {
        $types = $item->getTypes();
        if (count($types) == 0) {
            return 0;
        }
        return countElementsInTable(
            'glpi_domains_items',
            [
              "domains_id"   => $item->getID(),
              "itemtype"     => $types
            ]
        );
    }

    public static function countForItem(CommonDBTM $item)
    {
        $criteria = [];
        if ($item instanceof DomainRelation) {
            $criteria = ['domainrelations_id' => $item->fields['id']];
        } else {
            $criteria = [
               'itemtype'  => $item->getType(),
               'items_id'  => $item->fields['id']
            ];
        }

        return countElementsInTable(
            self::getTable(),
            $criteria
        );
    }

    public function getFromDBbyDomainsAndItem($domains_id, $items_id, $itemtype)
    {
        $criteria = ['domains_id' => $domains_id];
        $item = new $itemtype();
        if ($item instanceof DomainRelation) {
            $criteria += ['domainrelations_id' => $items_id];
        } else {
            $criteria += [
               'itemtype'  => $itemtype,
               'items_id'  => $items_id
            ];
        }

        return $this->getFromDBByCrit($criteria);
    }

    public function addItem($values)
    {
        $this->add([
           'domains_id'         => $values['domains_id'],
           'items_id'           => $values['items_id'],
           'itemtype'           => $values['itemtype'],
           'domainrelations_id' => $values['domainrelations_id']
        ]);
    }

    public function deleteItemByDomainsAndItem($domains_id, $items_id, $itemtype)
    {
        if ($this->getFromDBbyDomainsAndItem($domains_id, $items_id, $itemtype)) {
            $this->delete(['id' => $this->fields["id"]]);
        }
    }

    /**
     * Show items linked to a domain
     *
     * @param Domain $domain Domain object
     *
     * @return void|boolean (display) Returns false if there is a rights error.
     **/
    public static function showForDomain(Domain $domain)
    {
        global $CFG_GLPI;

        $instID = $domain->fields['id'];
        if (!$domain->can($instID, READ)) {
            return false;
        }
        $canedit = $domain->can($instID, UPDATE);
        $rand    = mt_rand();

        $request = self::getAdapter()->request([
           'SELECT'    => 'itemtype',
           'DISTINCT'  => true,
           'FROM'      => self::getTable(),
           'WHERE'     => ['domains_id' => $instID],
           'ORDER'     => 'itemtype',
           'LIMIT'     => count(Domain::getTypes(true))
        ]);

        $results = $request->fetchAllAssociative();
        $number = count($results);

        if (Session::isMultiEntitiesMode()) {
            $colsup = 1;
        } else {
            $colsup = 0;
        }

        if ($canedit) {
            $itemtypes = $CFG_GLPI['domain_types'];
            $options = [];
            foreach ($itemtypes as $itemtype) {
                $options[$itemtype] = $itemtype::getTypeName(1);
            };

            $form = [
               'action' => Toolbox::getItemTypeFormURL("Domain"),
               'buttons' => [
                  [
                     'name' => 'additem',
                     'value' => _x('button', 'Add'),
                     'class' => 'btn btn-secondary',
                  ]
                  ],
                  'content' => [
                     __('Add an item') => [
                        'visible' => true,
                        'inputs' => [
                           [
                              'type' => 'hidden',
                              'name' => 'domains_id',
                              'value' => $instID,
                           ],
                           __('Type') => [
                              'type' => 'select',
                              'id' => 'dropdown_itemtype',
                              'name' => 'itemtype',
                              'values' => [Dropdown::EMPTY_VALUE] + array_unique($options),
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
                           ],
                           __('Relation') => [
                              'type' => 'select',
                              'name' => 'domainrelations_id',
                              'itemtype' => DomainRelation::class,
                              'value' => DomainRelation::BELONGS,
                              'actions' => getItemActionButtons(['info', 'add'], DomainRelation::class)
                           ]
                        ]
                     ]
                  ]
            ];
            renderTwigForm($form);
        }

        if ($canedit && $number) {
            $massiveactionparams = [
               'container' => 'tableForDomainItem',
               'display_arrow' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        $fields = [
           _n('Type', 'Types', 1),
           __('Name'),
           _n('Type', 'Types', 1),
           DomainRelation::getTypeName(1),
           __('Serial number'),
           __('Inventory number'),
        ];
        if (Session::isMultiEntitiesMode()) {
            $fields[] = Entity::getTypeName(1);
        }
        $values = [];
        $massive_action = [];
        foreach ($results as $data) {
            $itemtype = $data['itemtype'];
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }

            if ($item->canView()) {
                $itemTable = getTableForItemType($itemtype);
                $linked_criteria = [
                   'SELECT' => [
                      "$itemTable.*",
                      'glpi_domains_items.id AS items_id',
                      'glpi_domains_items.domainrelations_id',
                      'glpi_entities.id AS entity'
                   ],
                   'FROM'   => self::getTable(),
                   'INNER JOIN'   => [
                      $itemTable  => [
                         'ON'  => [
                            $itemTable  => 'id',
                            self::getTable()  => 'items_id'
                         ]
                      ]
                   ],
                   'LEFT JOIN'    => [
                      'glpi_entities'   => [
                         'ON'  => [
                            'glpi_entities'   => 'id',
                            $itemTable        => 'entities_id'
                         ]
                      ]
                   ],
                   'WHERE'        => [
                      self::getTable() . '.itemtype'   => $itemtype,
                      self::getTable() . '.domains_id' => $instID
                   ] + getEntitiesRestrictCriteria($itemTable, '', '', $item->maybeRecursive())
                ];

                if ($item->maybeTemplate()) {
                    $linked_criteria['WHERE']["$itemTable.is_template"] = 0;
                }

                $results = self::getAdapter()->request($linked_criteria);
                $linked_result = $results->fetchAllAssociative();
                if (count($linked_result)) {
                    Session::initNavigateListItems($itemtype, Domain::getTypeName(2) . " = " . $domain->fields['name']);

                    // while ($data = $linked_iterator->next()) {
                    foreach ($linked_result as $data) {
                        $item->getFromDB($data["id"]);

                        $ID = "";

                        if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                            $ID = " (" . $data["id"] . ")";
                        }

                        $link = Toolbox::getItemTypeFormURL($itemtype);
                        $name = "<a href=\"" . $link . "?id=" . $data["id"] . "\">"
                                 . $data["name"] . "$ID</a>";

                        $newValue = [
                           $item->getTypeName(1),
                           $name,
                           Dropdown::getDropdownName("glpi_domainrelations", $data['domainrelations_id']),
                           (isset($data["serial"]) ? "" . $data["serial"] . "" : "-"),
                           (isset($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-"),

                        ];
                        if (Session::isMultiEntitiesMode()) {
                            $newValue[] = Dropdown::getDropdownName("glpi_entities", $data['entity']);
                        }
                        $values[] = $newValue;
                        $massive_action[] = sprintf('item[%s][%s]', self::class, $data['id']);
                    }
                }
            }
        }
        renderTwigTemplate('table.twig', [
           'id' => 'tableForDomainItem',
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);
        echo "<div class='spaced'>";
        echo "<table class='tab_cadre_fixe' aria-label='Show Domain'>";
        echo "<tr>";

        foreach ($results as $data) {
            $itemtype = $data['itemtype'];
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }

            if ($item->canView()) {
                $itemTable = getTableForItemType($itemtype);
                $linked_criteria = [
                   'SELECT' => [
                      "$itemTable.*",
                      'glpi_domains_items.id AS items_id',
                      'glpi_domains_items.domainrelations_id',
                      'glpi_entities.id AS entity'
                   ],
                   'FROM'   => self::getTable(),
                   'INNER JOIN'   => [
                      $itemTable  => [
                         'ON'  => [
                            $itemTable  => 'id',
                            self::getTable()  => 'items_id'
                         ]
                      ]
                   ],
                   'LEFT JOIN'    => [
                      'glpi_entities'   => [
                         'ON'  => [
                            'glpi_entities'   => 'id',
                            $itemTable        => 'entities_id'
                         ]
                      ]
                   ],
                   'WHERE'        => [
                      self::getTable() . '.itemtype'   => $itemtype,
                      self::getTable() . '.domains_id' => $instID
                   ] + getEntitiesRestrictCriteria($itemTable, '', '', $item->maybeRecursive())
                ];

                if ($item->maybeTemplate()) {
                    $linked_criteria['WHERE']["$itemTable.is_template"] = 0;
                }

                $results = self::getAdapter()->request($linked_criteria);
                $linked_result = $results->fetchAllAssociative();
                if (count($linked_result)) {
                    Session::initNavigateListItems($itemtype, Domain::getTypeName(2) . " = " . $domain->fields['name']);

                    foreach ($linked_result as $data) {
                        Session::addToNavigateListItems($itemtype, $data["id"]);
                        $item->getFromDB($data["id"]);

                        $ID = "";

                        if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                            $ID = " (" . $data["id"] . ")";
                        }

                        $link = Toolbox::getItemTypeFormURL($itemtype);
                        $name = "<a href=\"" . $link . "?id=" . $data["id"] . "\">"
                                 . $data["name"] . "$ID</a>";

                        echo "<tr class='tab_bg_1'>";

                        if ($canedit) {
                            echo "<td width='10'>";
                            Html::showMassiveActionCheckBox(__CLASS__, $data["items_id"]);
                            echo "</td>";
                        }
                        echo "<td class='center'>" . $item->getTypeName(1) . "</td>";

                        echo "<td class='center' " . (isset($data['is_deleted']) && $data['is_deleted'] ? "class='tab_bg_2_2'" : "") .
                              ">" . $name . "</td>";
                        if (Session::isMultiEntitiesMode()) {
                            echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $data['entity']) . "</td>";
                        }
                        echo "<td class='center'>" . Dropdown::getDropdownName("glpi_domainrelations", $data['domainrelations_id']) . "</td>";
                        echo "<td class='center'>" . (isset($data["serial"]) ? "" . $data["serial"] . "" : "-") . "</td>";
                        echo "<td class='center'>" . (isset($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-") . "</td>";

                        echo "</tr>";
                    }
                }
            }
        }
        echo "</table>";

        if ($canedit && $number) {
            Html::closeForm();
        }
        echo "</div>";
    }

    /**
     * Show domains associated to an item
     *
     * @param $item            CommonDBTM object for which associated domains must be displayed
     * @param $withtemplate (default '')
     *
     * @return bool
     */
    public static function showForItem(CommonDBTM $item, $withtemplate = '')
    {
        global $DB;

        $ID = $item->getField('id');

        if ($item->isNewID($ID)) {
            return false;
        }
        if (!Session::haveRight('domain', READ)) {
            return false;
        }

        if (!$item->can($item->fields['id'], READ)) {
            return false;
        }

        if (empty($withtemplate)) {
            $withtemplate = 0;
        }

        $canedit      = $item->canAddItem('Domain');
        $rand         = mt_rand();
        $is_recursive = $item->isRecursive();

        $criteria = [
           'SELECT'    => [
              'glpi_domains_items.id AS assocID',
              'glpi_domains_items.domainrelations_id',
              'glpi_entities.id AS entity',
              'glpi_domains.name AS assocName',
              'glpi_domains.*'

           ],
           'FROM'      => self::getTable(),
           'LEFT JOIN' => [
              Domain::getTable()   => [
                 'ON'  => [
                    Domain::getTable()   => 'id',
                    self::getTable()     => 'domains_id'
                 ]
              ],
              Entity::getTable()   => [
                 'ON'  => [
                    Domain::getTable()   => 'entities_id',
                    Entity::getTable()   => 'id'
                 ]
              ]
           ],
           'WHERE'     => [],//to be filled
           'ORDER'     => 'assocName'
        ];

        if ($item instanceof DomainRelation) {
            $criteria['WHERE'] = ['glpi_domains_items.domainrelations_id' => $ID];
        } else {
            $criteria['WHERE'] = [
               'glpi_domains_items.itemtype' => $item->getType(),
               'glpi_domains_items.items_id' => $ID
            ];
        }
        $criteria['WHERE'] += getEntitiesRestrictCriteria(Domain::getTable(), '', '', true);

        $request = self::getAdapter()->request($criteria);
        $results = $request->fetchAllAssociative();
        $number = count($results);
        $i      = 0;

        $domains = [];
        $domain  = new Domain();
        $used    = [];
        foreach ($results as $data) {
            $domains[$data['assocID']?? null] = $data;
            $used[$data['id']]         = $data['id'];
        }

        if (!($item instanceof DomainRelation) && $canedit && $withtemplate < 2) {
            // Restrict entity for knowbase
            $entities = "";
            $entity   = $_SESSION["glpiactive_entity"];

            if ($item->isEntityAssign()) {
                /// Case of personal items : entity = -1 : create on active entity (Reminder case))
                if ($item->getEntityID() >= 0) {
                    $entity = $item->getEntityID();
                }

                if ($item->isRecursive()) {
                    $entities = getSonsOf('glpi_entities', $entity);
                } else {
                    $entities = $entity;
                }
            }

            $domain_result = self::getAdapter()->request([
               'COUNT'  => 'cpt',
               'FROM'   => Domain::getTable(),
               'WHERE'  => ['is_deleted' => 0] + getEntitiesRestrictCriteria(Domain::getTable(), '', $entities, true)
            ]);
            // $result = $domain_iterator->next();
            $result = $domain_result->fetchAssociative();
            $nb     = $result['cpt'];

            if (
                Session::haveRight('domain', READ)
                && ($nb > count($used))
            ) {
                $form = [
                    'action' => Toolbox::getItemTypeFormURL('Domain'),
                    'buttons' => [
                       [
                          'name' => 'additem',
                          'value' => __('Associate a domain'),
                          'class' => 'btn btn-secondary',
                       ]
                    ],
                    'content' => [
                        '' => [
                            'visible' => true,
                            'inputs' => [
                                DomainRelation::getTypeName() => [
                                    'type' => 'select',
                                    'name' => 'domainrelations_id',
                                    'itemtype' => DomainRelation::class,
                                    'value' => DomainRelation::BELONGS,
                                    'actions' => getItemActionButtons(['info', 'add'], DomainRelation::class),
                                    'col_lg' => 6,
                                ],
                                __('Domain') => [
                                    'type' => 'select',
                                    'name' => 'domains_id',
                                    'itemtype' => Domain::class,
                                    'used' => $used,
                                    'col_lg' => 6,
                                ],
                                [
                                    'type' => 'hidden',
                                    'name' => 'items_id',
                                    'value' => $ID,
                                ],
                                [
                                    'type' => 'hidden',
                                    'name' => 'itemtype',
                                    'value' => $item->getType(),
                                ],
                                [
                                    'type' => 'hidden',
                                    'name' => 'entities_id',
                                    'value' => $entity,
                                ],
                                [
                                    'type' => 'hidden',
                                    'name' => 'is_recursive',
                                    'value' => $is_recursive,
                                ],
                            ]
                        ]
                    ]
                ];
                renderTwigForm($form);
            }
        }

        $massivactionId = 'mass' . __CLASS__ . $rand;
        $fields = [
          'name' => __('Name'),
          'tech_groups_id' => __('Group in charge'),
          'tech_users_id' => __('Technician in charge'),
          'domaintypes_id' => _n('Type', 'Types', 1),
          'date_creation' => __('Creation date'),
          'expiration' => __('Expiration date'),
        ];
        if (Session::isMultiEntitiesMode()) {
            $fields['entities_id'] = Entity::getTypeName(1);
        }
        $values = [];
        $massive_action = [];
        if ($canedit && $number && ($withtemplate < 2)) {
            $massiveactionparams = [
               'container' => $massivactionId,
               'num_displayed' => $number,
               'display_arrow' => false,
               'specific_actions' => [
                   'MassiveAction:update' => __('Modify'),
                   'MassiveAction:purge' => __('Delete'),
               ]
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        $used = [];

        $i = 0;
        if ($number) {
            foreach ($domains as $data) {
                $domainID = $data["id"];
                $link     = NOT_AVAILABLE;

                if ($domain->getFromDB($domainID)) {
                    $link = $domain->getLink();
                }

                Session::addToNavigateListItems('Domain', $domainID);

                $used[$domainID] = $domainID;

                if ($canedit && ($withtemplate < 2)) {
                    $massive_action[$i] = sprintf('item[%s][%s]', self::class, $domainID);
                }
                $newValue = [
                    'name' => $link,
                    'tech_groups_id' => Dropdown::getDropdownName("glpi_groups", $data["tech_groups_id"]),
                    'tech_users_id' => getUserName($data["tech_users_id"]),
                    'domaintypes_id' => Dropdown::getDropdownName("glpi_domaintypes", $data["domaintypes_id"]),
                    'date_creation' => Html::convDate($data["date_creation"]),
                    'expiration' => $data["date_expiration"] <= date('Y-m-d') && !empty($data["date_expiration"]) ?
                        "<div class='deleted'>" . Html::convDate($data["date_expiration"]) . "</div>" :
                        (empty($data["date_expiration"]) ? __('Does not expire') : Html::convDate($data["date_expiration"])),
                ];
                if (Session::isMultiEntitiesMode()) {
                    $newValue['entities_id'] = Dropdown::getDropdownName("glpi_entities", $data['entity']);
                }
                $values[] = $newValue;
                $i++;
            }
        }
        renderTwigTemplate('table.twig', [
           'id' => $massivactionId,
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);
    }

    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
           'id'                 => '2',
           'table'              => DomainRelation::getTable(),
           'field'              => 'name',
           'name'               => DomainRelation::getTypeName(),
           'datatype'           => 'itemlink',
           'itemlink_type'      => $this->getType(),
        ];

        return $tab;
    }
}
