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

class DomainRecord extends CommonDBChild
{
    public const DEFAULT_TTL = 3600;

    public static $rightname              = 'domain';
    // From CommonDBChild
    public static $itemtype        = 'Domain';
    public static $items_id        = 'domains_id';
    public $dohistory              = true;

    public static function getTypeName($nb = 0)
    {
        return _n('Domain record', 'Domains records', $nb);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$withtemplate) {
            if ($item->getType() == 'Domain') {
                if ($_SESSION['glpishow_count_on_tabs']) {
                    return self::createTabEntry(_n('Record', 'Records', Session::getPluralNumber()), self::countForDomain($item));
                }
                return _n('Record', 'Records', Session::getPluralNumber());
            }
        }
        return '';
    }

    public static function countForDomain(Domain $item)
    {
        return countElementsInTable(
            self::getTable(),
            [
              "domains_id"   => $item->getID(),
            ]
        );
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'Domain') {
            self::showForDomain($item);
        }
        return true;
    }

    public function rawSearchOptions()
    {
        $tab = [];

        $tab = array_merge($tab, parent::rawSearchOptions());

        $tab[] = [
           'id'                 => '2',
           'table'              => 'glpi_domains',
           'field'              => 'name',
           'name'               => Domain::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '3',
           'table'              => DomainRecordType::getTable(),
           'field'              => 'name',
           'name'               => DomainRecordType::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '4',
           'table'              => $this->getTable(),
           'field'              => 'ttl',
           'name'               => __('TTL')
        ];

        $tab[] = [
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'data',
           'name'               => __('Data'),
        ];

        $tab[] = [
           'id'                 => '6',
           'table'              => 'glpi_users',
           'field'              => 'name',
           'linkfield'          => 'users_id_tech',
           'name'               => __('Technician in charge'),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '7',
           'table'              => $this->getTable(),
           'field'              => 'date_creation',
           'name'               => __('Creation date'),
           'datatype'           => 'date'
        ];

        $tab[] = [
           'id'                 => '8',
           'table'              => $this->getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '9',
           'table'              => 'glpi_groups',
           'field'              => 'name',
           'linkfield'          => 'groups_id_tech',
           'name'               => __('Group in charge'),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '10',
           'table'              => $this->getTable(),
           'field'              => 'date_mod',
           'massiveaction'      => false,
           'name'               => __('Last update'),
           'datatype'           => 'datetime'
        ];

        $tab[] = [
           'id'                 => '80',
           'table'              => 'glpi_entities',
           'field'              => 'completename',
           'name'               => Entity::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        return $tab;
    }

    public static function canCreate()
    {
        if (count($_SESSION['glpiactiveprofile']['managed_domainrecordtypes'])) {
            return true;
        }
        return parent::canCreate();
    }

    public static function canUpdate()
    {
        if (count($_SESSION['glpiactiveprofile']['managed_domainrecordtypes'])) {
            return true;
        }
        return parent::canUpdate();
    }


    public static function canDelete()
    {
        if (count($_SESSION['glpiactiveprofile']['managed_domainrecordtypes'])) {
            return true;
        }
        return parent::canDelete();
    }


    public static function canPurge()
    {
        if (count($_SESSION['glpiactiveprofile']['managed_domainrecordtypes'])) {
            return true;
        }
        return parent::canPurge();
    }


    public function canCreateItem()
    {
        return count($_SESSION['glpiactiveprofile']['managed_domainrecordtypes']);
    }


    public function canUpdateItem()
    {
        return parent::canUpdateItem()
           && (
               $_SESSION['glpiactiveprofile']['managed_domainrecordtypes'] == [-1]
         || in_array($this->fields['domainrecordtypes_id'], $_SESSION['glpiactiveprofile']['managed_domainrecordtypes'])
           );
    }

    public function canDeleteItem()
    {
        return parent::canDeleteItem()
           && (
               $_SESSION['glpiactiveprofile']['managed_domainrecordtypes'] == [-1]
         || in_array($this->fields['domainrecordtypes_id'], $_SESSION['glpiactiveprofile']['managed_domainrecordtypes'])
           );
    }


    public function canPurgeItem()
    {
        return parent::canPurgeItem()
           && (
               $_SESSION['glpiactiveprofile']['managed_domainrecordtypes'] == [-1]
         || in_array($this->fields['domainrecordtypes_id'], $_SESSION['glpiactiveprofile']['managed_domainrecordtypes'])
           );
    }


    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Ticket', $ong, $options);
        $this->addStandardTab('Item_Problem', $ong, $options);
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab('Link', $ong, $options);
        $this->addStandardTab('Notepad', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }

    /**
     * Prepare input for add and update
     *
     * @param array   $input Input values
     * @param boolean $add   True when we're adding a record
     *
     * @return aray|false
     */
    private function prepareInput($input, $add = false)
    {

        if ($add) {
            if (isset($input['date_creation']) && empty($input['date_creation'])) {
                $input['date_creation'] = 'NULL';
            }

            if (!isset($input['ttl']) || empty($input['ttl'])) {
                $input['ttl'] = self::DEFAULT_TTL;
            }
        }

        //search entity
        if ($add && !isset($input['entities_id'])) {
            $input['entities_id'] = $_SESSION['glpiactive_entity'] ?? 0;
            $input['is_recursive'] = $_SESSION['glpiactive_entity_recursive'] ?? 0;
            $domain = new Domain();
            if (isset($input['domains_id']) && $domain->getFromDB($input['domains_id'])) {
                $input['entities_id'] = $domain->fields['entities_id'];
                $input['is_recursive'] = $domain->fields['is_recursive'];
            }
        }

        if (!Session::isCron() && (isset($input['domainrecordtypes_id']) || isset($this->fields['domainrecordtypes_id']))) {
            if (!($_SESSION['glpiactiveprofile']['managed_domainrecordtypes'] == [-1])) {
                if (isset($input['domainrecordtypes_id']) && !(in_array($input['domainrecordtypes_id'], $_SESSION['glpiactiveprofile']['managed_domainrecordtypes']))) {
                    //no right to use selected type
                    Session::addMessageAfterRedirect(
                        __('You are not allowed to use this type of records'),
                        true,
                        ERROR
                    );
                    return false;
                }
                if ($add === false && !(in_array($this->fields['domainrecordtypes_id'], $_SESSION['glpiactiveprofile']['managed_domainrecordtypes']))) {
                    //no right to change existing type
                    Session::addMessageAfterRedirect(
                        __('You are not allowed to edit this type of records'),
                        true,
                        ERROR
                    );
                    return false;
                }
            }
        }

        return $input;
    }

    public function prepareInputForAdd($input)
    {
        return $this->prepareInput($input, true);
    }

    public function prepareInputForUpdate($input)
    {
        return $this->prepareInput($input);
    }

    public function showForm($ID, $options = [])
    {
        $form = [
           'action' => $this->getFormURL(),
           'itemtype' => $this::class,
           'content' => [
              '' => [
                 'visible' => true,
                 'inputs' => [
                    Domain::getTypeName(1) => [
                       'type' => 'select',
                       'name' => 'domains_id',
                       'values' => getOptionForItems(Domain::class),
                       'value' => $this->fields['domains_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], Domain::class)
                    ],
                    __('Name') => [
                       'type' => 'text',
                       'name' => 'name',
                       'value' => $this->fields['name'] ?? '',
                    ],
                    DomainRecordType::getTypeName(1) => [
                       'type' => 'select',
                       'name' => 'domainrecordtypes_id',
                       'values' => getOptionForItems(DomainRecordType::class),
                       'value' => $this->fields['domainrecordtypes_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], DomainRecordType::class)
                    ],
                    __('Creation date') => [
                       'type' => 'datetime-local',
                       'name' => 'date_creation',
                       'value' => $this->fields["date_creation"] ?? '',
                    ],
                    __('Data') => [
                       'type' => 'text',
                       'name' => 'data',
                       'value' => $this->fields['data'] ?? '',
                    ],
                    __('Technician in charge') => [
                       'type' => 'select',
                       'name' => "users_id_tech",
                       'values' => getOptionsForUsers('interface', ['entities_id' => Session::getActiveEntity()]),
                       'value' => $this->fields["users_id_tech"] ?? '',
                       'actions' => getItemActionButtons(['info'], User::class),
                    ],
                    __('Group in charge') => [
                       'type' => 'select',
                       'name' => "groups_id_tech",
                       'values' => getOptionForItems(Group::class, ['entities_id' => Session::getActiveEntity()]),
                       'value' => $this->fields["groups_id_tech"] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], Group::class),
                    ],
                    __('TTL') => [
                       'type' => 'number',
                       'name' => 'ttl',
                       'value' => $this->fields['ttl'] ?? '',
                    ],
                    __('Comments') => [
                       'type' => 'textarea',
                       'name' => 'comment',
                       'value' => $this->fields['comment'] ?? '',
                    ]
                 ]
              ]
           ]
        ];
        renderTwigForm($form, '', $this->fields);

        return true;
    }

    /**
     * Show records for a domain
     *
     * @param Domain $domain Domain object
     *
     * @return void|boolean (display) Returns false if there is a rights error.
     **/
    public static function showForDomain(Domain $domain)
    {
        $instID = $domain->fields['id'];
        if (!$domain->can($instID, READ)) {
            return false;
        }
        $canedit = $domain->can($instID, UPDATE)
                   || count($_SESSION['glpiactiveprofile']['managed_domainrecordtypes']);
        $rand    = mt_rand();

        $request = self::getAdapter()->request([
           'SELECT'    => 'record.*',
           'FROM'      => self::getTable() . ' AS record',
           'WHERE'     => ['domains_id' => $instID],
           'LEFT JOIN' => [
              DomainRecordType::getTable() . ' AS rtype'  => [
                 'ON'  => [
                    'rtype'  => 'id',
                    'record' => 'domainrecordtypes_id'
                 ]
              ]
           ],
           'ORDER'     => ['rtype.name ASC', 'record.name ASC']
        ]);
        $results = $request->fetchAllAssociative();
        $number = count($results);

        if ($canedit) {
            $form = [
               'action' => Toolbox::getItemTypeFormURL("Domain"),
               'buttons' => [
                  [
                     'name' => 'addrecord',
                     'value' => _x('button', 'Add'),
                     'class' => 'btn btn-secondary',
                  ]
               ],
               'content' => [
                  __('Link a record') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'domains_id',
                           'value' => $instID,
                        ],
                        '' => [
                           'type' => 'select',
                           'name' => 'domainrecords_id',
                           'itemtype' => DomainRecord::class,
                           'condition' => ['domains_id' => 0],
                           'actions' => getItemActionButtons(['info', 'add'], DomainRecord::class),
                           'col_lg' => 12,
                           'col_md' => 12,
                        ]
                     ]
                  ]
               ]

            ];
            renderTwigForm($form);
        }

        if ($canedit && $number) {
            $massiveactionparams = [
               'container' => 'tableForDomainRecordsDomain',
               'display_arrow' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        $fields = [
           _n('Type', 'Types', 1),
           __('Name'),
           __('TTL'),
           _n('Target', 'Targets', 1),
        ];
        $values = [];
        $massive_action = [];
        foreach ($results as $data) {
            $ID = "";

            if ($_SESSION["glpiis_ids_visible"] || empty(self::getDisplayName($domain, $data['name']))) {
                $ID = " (" . $data["id"] . ")";
            }

            $link = Toolbox::getItemTypeFormURL('DomainRecord');
            $name = "<a href=\"" . $link . "?id=" . $data["id"] . "\">"
                     . self::getDisplayName($domain, $data['name']) . "$ID</a>";
            $values[] = [
               Dropdown::getDropdownName(DomainRecordType::getTable(), $data['domainrecordtypes_id']),
               $name,
               $data['ttl'],
               $data['data'],
            ];
            $massive_action[] = sprintf('item[%s][%s]', DomainRecord::class, $data['id']);
        }
        renderTwigTemplate('table.twig', [
           'id' => 'tableForDomainRecordsDomain',
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);
    }

    public static function getDisplayName(Domain $domain, $name)
    {
        $name_txt = rtrim(
            str_replace(
                rtrim($domain->getCanonicalName(), '.'),
                '',
                $name
            ),
            '.'
        );
        if (empty($name_txt)) {
            //dns root
            $name_txt = '@';
        }
        return $name_txt;
    }
}
