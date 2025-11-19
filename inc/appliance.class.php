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

use function Termwind\render;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Appliances Class
 **/
class Appliance extends CommonDBTM
{
    use Glpi\Features\Clonable;

    // From CommonDBTM
    public $dohistory = true;
    public static $rightname = 'appliance';
    protected $usenotepad = true;

    public function getCloneRelations(): array
    {
        return [
            Appliance_Item::class,
            Contract_Item::class,
            Document_Item::class,
            Infocom::class,
            Notepad::class,
            KnowbaseItem_Item::class
        ];
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Appliance', 'Appliances', $nb);
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong)
            ->addImpactTab($ong, $options)
            ->addStandardTab('Appliance_Item', $ong, $options)
            ->addStandardTab('Contract_Item', $ong, $options)
            ->addStandardTab('Document_Item', $ong, $options)
            ->addStandardTab('Infocom', $ong, $options)
            ->addStandardTab('Certificate_Item', $ong, $options)
            ->addStandardTab('Domain_Item', $ong, $options)
            ->addStandardTab('KnowbaseItem_Item', $ong, $options)
            ->addStandardTab('Ticket', $ong, $options)
            ->addStandardTab('Item_Problem', $ong, $options)
            ->addStandardTab('Change_Item', $ong, $options)
            ->addStandardTab('Link', $ong, $options)
            ->addStandardTab('Notepad', $ong, $options)
            ->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    public function showForm($ID)
    {
        $form = [
            'action' => Toolbox::getItemTypeFormURL('appliance'),
            'itemtype' => $this::class,
            'buttons' => [
                [
                    'type' => 'submit',
                    'name' => $this->isNewID($ID) ? 'add' : 'update',
                    'value' => $this->isNewID($ID) ? __('Add') : __('Update'),
                    'class' => 'btn btn-secondary',
                ],
                $this->isNewID($ID) ? [] : [
                    'type' => 'submit',
                    'name' => 'delete',
                    'value' => __('Put in trashbin'),
                    'class' => 'btn btn-secondary'
                ]
            ],
            'content' => [
                __('Appliance') => [
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
                            'value' => $this->fields['name'] ?? '',
                        ],
                        __('Location') => [
                            'name' => 'locations_id',
                            'type' => 'select',
                            'values' => getOptionForItems("Location"),
                            'value' => $this->fields['locations_id'] ?? '',
                            'actions' => getItemActionButtons(['info', 'add'], "Location"),
                        ],
                        __('Technician in charge of the appliance') => [
                            'name' => 'users_id_tech',
                            'type' => 'select',
                            'values' => getOptionsForUsers('own_ticket', ['entities_id' => $this->fields['entities_id'] ?? '']),
                            'value' => $this->fields['users_id_tech'] ?? '',
                            'actions' => getItemActionButtons(['info', 'add'], "User"),
                        ],
                        __('Group in charge of the appliance') => [
                            'name' => 'groups_id_tech',
                            'type' => 'select',
                            'values' => getOptionForItems("Group"),
                            'value' => $this->fields['groups_id_tech'] ?? '',
                            'actions' => getItemActionButtons(['info', 'add'], "Group"),
                        ],
                        __('Serial number') => [
                            'name' => 'serial',
                            'type' => 'text',
                            'value' => $this->fields['serial'] ?? '',
                        ],
                        __('User') => [
                            'name' => 'users_id',
                            'type' => 'select',
                            'values' => getOptionsForUsers('all', ['entities_id' => $this->fields['entities_id'] ?? '']),
                            'value' => $this->fields['users_id'] ?? '',
                            'actions' => getItemActionButtons(['info', 'add'], "User"),
                        ],
                        __('Group') => [
                            'name' => 'groups_id',
                            'type' => 'select',
                            'values' => getOptionForItems("Group"),
                            'value' => $this->fields['groups_id'] ?? '',
                            'actions' => getItemActionButtons(['info', 'add'], "Group"),
                        ],
                        __('State') => [
                            'name' => 'states_id',
                            'type' => 'select',
                            'values' => getOptionForItems("State"),
                            'value' => $this->fields['states_id'] ?? '',
                            'actions' => getItemActionButtons(['info', 'add'], "State"),
                        ],
                        __('Appliance type') => [
                            'name' => 'appliancetypes_id',
                            'type' => 'select',
                            'values' => getOptionForItems("ApplianceType"),
                            'value' => $this->fields['appliancetypes_id'] ?? '',
                            'actions' => getItemActionButtons(['info', 'add'], "Appliancetype"),
                        ],
                        __('Manufacturer') => [
                            'name' => 'manufacturers_id',
                            'type' => 'select',
                            'values' => getOptionForItems("Manufacturer"),
                            'value' => $this->fields['manufacturers_id'] ?? '',
                            'actions' => getItemActionButtons(['info', 'add'], "Manufacturer"),
                        ],
                        __('Appliance environment') => [
                            'name' => 'applianceenvironments_id',
                            'type' => 'select',
                            'values' => getOptionForItems("ApplianceEnvironment"),
                            'value' => $this->fields['applianceenvironments_id'] ?? '',
                            'actions' => getItemActionButtons(['info', 'add'], "ApplianceEnvironment"),
                        ],
                        __('Inventory number') => [
                            'name' => 'otherserial',
                            'type' => 'text',
                            'value' => $this->fields['otherserial'] ?? '',
                        ],
                        __('Comments') => [
                            'name' => 'comment',
                            'type' => 'text',
                            'value' => $this->fields['comment'] ?? '',
                        ]
                    ]
                ]
            ]
        ];

        renderTwigForm($form, '', $this->fields);
        return true;
    }

    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
            'id' => '4',
            'table' => self::getTable(),
            'field' => 'comment',
            'name' => __('Comments'),
            'datatype' => 'text'
        ];

        $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

        $tab[] = [
            'id' => '5',
            'table' => Appliance_Item::getTable(),
            'field' => 'items_id',
            'name' => _n('Associated item', 'Associated items', 2),
            'nosearch' => true,
            'massiveaction' => false,
            'forcegroupby' => true,
            'additionalfields' => ['itemtype'],
            'joinparams' => ['jointype' => 'child']
        ];

        $tab[] = [
            'id' => '6',
            'table' => User::getTable(),
            'field' => 'name',
            'name' => User::getTypeName(1),
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '8',
            'table' => Group::getTable(),
            'field' => 'completename',
            'name' => Group::getTypeName(1),
            'condition' => ['is_itemgroup' => 1],
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '23',
            'table' => 'glpi_manufacturers',
            'field' => 'name',
            'name' => Manufacturer::getTypeName(1),
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '24',
            'table' => User::getTable(),
            'field' => 'name',
            'linkfield' => 'users_id_tech',
            'name' => __('Technician in charge'),
            'datatype' => 'dropdown',
            'right' => 'own_ticket'
        ];

        $tab[] = [
            'id' => '49',
            'table' => Group::getTable(),
            'field' => 'completename',
            'linkfield' => 'groups_id_tech',
            'name' => __('Group in charge'),
            'condition' => ['is_assign' => 1],
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '9',
            'table' => self::getTable(),
            'field' => 'date_mod',
            'name' => __('Last update'),
            'massiveaction' => false,
            'datatype' => 'datetime'
        ];

        $tab[] = [
            'id' => '10',
            'table' => ApplianceEnvironment::getTable(),
            'field' => 'name',
            'name' => __('Environment'),
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '11',
            'table' => ApplianceType::getTable(),
            'field' => 'name',
            'name' => _n('Type', 'Types', 1),
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '12',
            'table' => self::getTable(),
            'field' => 'serial',
            'name' => __('Serial number'),
            'autocomplete' => true
        ];

        $tab[] = [
            'id' => '13',
            'table' => self::getTable(),
            'field' => 'otherserial',
            'name' => __('Inventory number'),
            'autocomplete' => true
        ];

        $tab[] = [
            'id' => '31',
            'table' => self::getTable(),
            'field' => 'id',
            'name' => __('ID'),
            'datatype' => 'number',
            'massiveaction' => false
        ];

        $tab[] = [
            'id' => '80',
            'table' => 'glpi_entities',
            'field' => 'completename',
            'name' => Entity::getTypeName(1),
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '7',
            'table' => self::getTable(),
            'field' => 'is_recursive',
            'name' => __('Child entities'),
            'massiveaction' => false,
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => '81',
            'table' => Entity::getTable(),
            'field' => 'entities_id',
            'name' => sprintf('%s-%s', Entity::getTypeName(1), __('ID'))
        ];

        $tab[] = [
            'id' => '61',
            'table' => $this->getTable(),
            'field' => 'is_helpdesk_visible',
            'name' => __('Associable to a ticket'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => '32',
            'table' => 'glpi_states',
            'field' => 'completename',
            'name' => __('Status'),
            'datatype' => 'dropdown',
            'condition' => ['is_visible_appliance' => 1]
        ];

        $tab = array_merge($tab, Certificate::rawSearchOptionsToAdd());

        return $tab;
    }


    public static function rawSearchOptionsToAdd(string $itemtype)
    {
        $tab = [];

        $tab[] = [
            'id' => 'appliance',
            'name' => self::getTypeName(Session::getPluralNumber())
        ];

        $tab[] = [
            'id' => '1210',
            'table' => self::getTable(),
            'field' => 'name',
            'name' => __('Name'),
            'forcegroupby' => true,
            'datatype' => 'itemlink',
            'itemlink_type' => 'Appliance',
            'massiveaction' => false,
            'joinparams' => [
                'beforejoin' => [
                    'table' => Appliance_Item::getTable(),
                    'joinparams' => ['jointype' => 'itemtype_item']
                ]
            ]
        ];

        $tab[] = [
            'id' => '1211',
            'table' => ApplianceType::getTable(),
            'field' => 'name',
            'name' => ApplianceType::getTypeName(1),
            'forcegroupby' => true,
            'massiveaction' => false,
            'joinparams' => [
                'beforejoin' => [
                    'table' => Appliance::getTable(),
                    'joinparams' => [
                        'beforejoin' => [
                            'table' => Appliance_Item::getTable(),
                            'joinparams' => ['jointype' => 'itemtype_item']
                        ]
                    ]
                ]
            ]
        ];

        $tab[] = [
            'id' => '1212',
            'table' => User::getTable(),
            'field' => 'name',
            'name' => User::getTypeName(1),
            'forcegroupby' => true,
            'massiveaction' => false,
            'datatype' => 'dropdown',
            'joinparams' => [
                'beforejoin' => [
                    'table' => self::getTable(),
                    'joinparams' => [
                        'beforejoin' => [
                            'table' => Appliance_Item::getTable(),
                            'joinparams' => ['jointype' => 'itemtype_item']
                        ]
                    ]
                ]
            ]
        ];

        $tab[] = [
            'id' => '1213',
            'table' => Group::getTable(),
            'field' => 'name',
            'name' => Group::getTypeName(1),
            'forcegroupby' => true,
            'massiveaction' => false,
            'datatype' => 'dropdown',
            'joinparams' => [
                'beforejoin' => [
                    'table' => self::getTable(),
                    'joinparams' => [
                        'beforejoin' => [
                            'table' => Appliance_Item::getTable(),
                            'joinparams' => ['jointype' => 'itemtype_item']
                        ]
                    ]
                ]
            ]
        ];

        return $tab;
    }


    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
                Appliance_Item::class,
            ]
        );
    }


    public static function getIcon()
    {
        return "fas fa-cubes";
    }

    /**
     * Get item types that can be linked to an appliance
     *
     * @param boolean $all Get all possible types or only allowed ones
     *
     * @return array
     */
    public static function getTypes($all = false): array
    {
        global $CFG_GLPI;

        $types = $CFG_GLPI['appliance_types'];

        foreach ($types as $key => $type) {
            if (!class_exists($type)) {
                continue;
            }

            if ($all === false && !$type::canView()) {
                unset($types[$key]);
            }
        }
        return $types;
    }

    public function getSpecificMassiveActions($checkitem = null)
    {

        $isadmin = static::canUpdate();
        $actions = parent::getSpecificMassiveActions($checkitem);

        if ($isadmin) {
            $prefix = 'Appliance_Item' . MassiveAction::CLASS_ACTION_SEPARATOR;
            $actions[$prefix . 'add'] = _x('button', 'Add an item');
            $actions[$prefix . 'remove'] = _x('button', 'Remove an item');
        }

        KnowbaseItem_Item::getMassiveActionsForItemtype($actions, __CLASS__, 0, $checkitem);

        return $actions;
    }

    public static function getMassiveActionsForItemtype(
        array &$actions,
        $itemtype,
        $is_deleted = 0,
        CommonDBTM $checkitem = null
    ) {
        if (in_array($itemtype, self::getTypes())) {
            if (self::canUpdate()) {
                $action_prefix = 'Appliance_Item' . MassiveAction::CLASS_ACTION_SEPARATOR;
                $actions[$action_prefix . 'add'] = "<i class='ma-icon fas fa-file-contract' aria-hidden='true'></i>" .
                    _x('button', 'Add to an appliance');
                $actions[$action_prefix . 'remove'] = _x('button', 'Remove from an appliance');
            }
        }
    }

    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {

        switch ($ma->getAction()) {
            case 'add_item':
                renderTwigTemplate('macros/wrappedInput.twig', [
                    'title' => Appliance::getTypeName(),
                    'input' => [
                        'name' => 'appliances_id',
                        'type' => 'select',
                        'values' => getOptionForItems(Appliance::class, isset($_POST['entity_restrict']) ? ['entities_id' => $_POST['entity_restrict']] : []),
                        'value' => $_POST['appliances_id'] ?? '',
                        'col_lg' => 12,
                        'col_md' => 12,
                        'actions' => getItemActionButtons(['info', 'add'], Appliance::class)
                    ],
                ]);
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction', "class" => "btn btn-secondary mt-3"]);
                return true;
        }
        return parent::showMassiveActionsSubForm($ma);
    }

    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {
        $appli_item = new Appliance_Item();

        switch ($ma->getAction()) {
            case 'add_item':
                $input = $ma->getInput();
                foreach ($ids as $id) {
                    $input = [
                        'appliances_id' => $input['appliances_id'],
                        'items_id' => $id,
                        'itemtype' => $item->getType()
                    ];
                    if ($appli_item->can(-1, UPDATE, $input)) {
                        if ($appli_item->add($input)) {
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
}
