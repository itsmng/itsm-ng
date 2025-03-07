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

use Itsmng\Domain\Entities\Printer as EntitiesPrinter;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}


/**
 * Printer Class
**/
class Printer extends CommonDBTM
{
    use Glpi\Features\Clonable;

    // From CommonDBTM
    public $dohistory                   = true;

    protected static $forward_entity_to = ['Infocom', 'NetworkPort', 'ReservationItem',
                                           'Item_OperatingSystem', 'Item_Disk', 'Item_SoftwareVersion'];

    public static $rightname                   = 'printer';
    protected $usenotepad               = true;

    public $entity = EntitiesPrinter::class;

    public function getCloneRelations(): array
    {
        return [
           Item_OperatingSystem::class,
           Item_Devices::class,
           Infocom::class,
           NetworkPort::class,
           Contract_Item::class,
           Document_Item::class,
           Computer_Item::class,
           KnowbaseItem_Item::class,
           Cartridge::class
        ];
    }

    /**
     * Name of the type
     *
     * @param $nb : number of item in the type
    **/
    public static function getTypeName($nb = 0)
    {
        return _n('Printer', 'Printers', $nb);
    }


    /**
     * @see CommonDBTM::useDeletedToLockIfDynamic()
     *
     * @since 0.84
    **/
    public function useDeletedToLockIfDynamic()
    {
        return false;
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addImpactTab($ong, $options);
        $this->addStandardTab('Item_OperatingSystem', $ong, $options);
        $this->addStandardTab('Item_SoftwareVersion', $ong, $options);
        $this->addStandardTab('Cartridge', $ong, $options);
        $this->addStandardTab('Item_Devices', $ong, $options);
        $this->addStandardTab('Item_Disk', $ong, $options);
        $this->addStandardTab('Computer_Item', $ong, $options);
        $this->addStandardTab('NetworkPort', $ong, $options);
        $this->addStandardTab('Infocom', $ong, $options);
        $this->addStandardTab('Contract_Item', $ong, $options);
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab('KnowbaseItem_Item', $ong, $options);
        $this->addStandardTab('Ticket', $ong, $options);
        $this->addStandardTab('Item_Problem', $ong, $options);
        $this->addStandardTab('Change_Item', $ong, $options);
        $this->addStandardTab('Link', $ong, $options);
        $this->addStandardTab('Notepad', $ong, $options);
        $this->addStandardTab('Reservation', $ong, $options);
        $this->addStandardTab('Certificate_Item', $ong, $options);
        $this->addStandardTab('Domain_Item', $ong, $options);
        $this->addStandardTab('Appliance_Item', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    /**
     * Can I change recusvive flag to false
     * check if there is "linked" object in another entity
     *
     * Overloaded from CommonDBTM
     *
     * @return boolean
    **/
    public function canUnrecurs()
    {
        global $DB;

        $ID = $this->fields['id'];

        if (
            ($ID < 0)
            || !$this->fields['is_recursive']
        ) {
            return true;
        }

        if (!parent::canUnrecurs()) {
            return false;
        }

        $entities = getAncestorsOf("glpi_entities", $this->fields['entities_id']);
        $entities[] = $this->fields['entities_id'];

        // RELATION : printers -> _port -> _wire -> _port -> device

        // Evaluate connection in the 2 ways
        $tabend = ['networkports_id_1' => 'networkports_id_2',
                   'networkports_id_2' => 'networkports_id_1'];
        foreach ($tabend as $enda => $endb) {
            $criteria = [
               'SELECT'       => [
                  'itemtype',
                  new QueryExpression('GROUP_CONCAT(DISTINCT ' . $DB->quoteName('items_id') . ') AS ' . $DB->quoteName('ids'))
               ],
               'FROM'         => 'glpi_networkports_networkports',
               'INNER JOIN'   => [
                  'glpi_networkports'  => [
                     'ON'  => [
                        'glpi_networkports_networkports' => $endb,
                        'glpi_networkports'              => 'id'
                     ]
                  ]
               ],
               'WHERE'        => [
                  'glpi_networkports_networkports.' . $enda   => new QuerySubQuery([
                     'SELECT' => 'id',
                     'FROM'   => 'glpi_networkports',
                     'WHERE'  => [
                        'itemtype'  => $this->getType(),
                        'items_id'  => $ID
                     ]
                  ])
               ],
               'GROUPBY'      => 'itemtype'
            ];

            $iterator = $DB->request($criteria);
            while ($data = $iterator->next()) {
                $itemtable = getTableForItemType($data["itemtype"]);
                if ($item = getItemForItemtype($data["itemtype"])) {
                    // For each itemtype which are entity dependant
                    if ($item->isEntityAssign()) {
                        if (
                            countElementsInTable($itemtable, ['id' => $data["ids"],
                                                   'NOT' => [ 'entities_id' => $entities]]) > 0
                        ) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }


    public function prepareInputForAdd($input)
    {

        if (isset($input["id"]) && ($input["id"] > 0)) {
            $input["_oldID"] = $input["id"];
        }
        unset($input['id']);
        unset($input['withtemplate']);

        if (isset($input['init_pages_counter'])) {
            $input['init_pages_counter'] = intval($input['init_pages_counter']);
        } else {
            $input['init_pages_counter'] = 0;
        }
        if (isset($input['last_pages_counter'])) {
            $input['last_pages_counter'] = intval($input['last_pages_counter']);
        } else {
            $input['last_pages_counter'] = $input['init_pages_counter'];
        }

        return $input;
    }


    public function prepareInputForUpdate($input)
    {

        if (isset($input['init_pages_counter'])) {
            $input['init_pages_counter'] = intval($input['init_pages_counter']);
        }
        if (isset($input['last_pages_counter'])) {
            $input['last_pages_counter'] = intval($input['last_pages_counter']);
        }

        return $input;
    }


    public function cleanDBonPurge()
    {
        global $DB;

        $DB->update(
            'glpi_cartridges',
            [
              'printers_id' => 'NULL'
            ],
            [
              'printers_id' => $this->fields['id']
            ]
        );

        $this->deleteChildrenAndRelationsFromDb(
            [
              Certificate_Item::class,
              Computer_Item::class,
              Item_Project::class,
            ]
        );

        Item_Devices::cleanItemDeviceDBOnItemDelete(
            $this->getType(),
            $this->fields['id'],
            (!empty($this->input['keep_devices']))
        );
    }


    /**
     * Print the printer form
     *
     * @param $ID        integer ID of the item
     * @param $options   array of possible options:
     *     - target filename : where to go when done.
     *     - withtemplate boolean : template or basic item
     *
      *@return boolean item found
     **/
    public function showForm($ID, $options = [])
    {
        global $CFG_GLPI;

        $isNew = $this->isNewID($ID) || (isset($options['withtemplate']) && $options['withtemplate'] == 2);

        $form = [
           'action' => $this->getFormURL(),
           'itemtype' => $this::class,
           'content' => [
              'General' => [
                 'inputs' => [
                    __('Name') => [
                       'name' => 'name',
                       'type' => 'text',
                       'value' => $this->fields["name"],
                    ],
                    __('State') => [
                       'name' => 'states_id',
                       'type' => 'select',
                       'itemtype' => State::class,
                       'conditions' => ['is_visible_printer' => 1],
                       'value' => $this->fields["states_id"],
                       'actions' => getItemActionButtons(['info', 'add'], "State"),
                    ],
                    Location::getTypeName(1) => [
                       'name' => 'locations_id',
                       'type' => 'select',
                       'itemtype' => Location::class,
                       'value' => $this->fields["locations_id"],
                       'actions' => getItemActionButtons(['info', 'add'], "Location"),
                    ],
                    _n('Type', 'Types', 1) => [
                       'name' => 'printertypes_id',
                       'type' => 'select',
                       'values' => getOptionForItems('PrinterType'),
                       'value' => $this->fields["printertypes_id"],
                       'actions' => getItemActionButtons(['info', 'add'], "PrinterType"),
                    ],
                    __('Technician in charge of the hardware') => [
                       'name' => 'users_id_tech',
                       'type' => 'select',
                       'values' => getOptionsForUsers('own_ticket', ['entities_id' => $this->fields['entities_id']]),
                       'value' => $this->fields["users_id_tech"],
                       'actions' => getItemActionButtons(['info'], "User"),
                    ],
                    Manufacturer::getTypeName(1) => [
                       'name' => 'manufacturers_id',
                       'type' => 'select',
                       'values' => getOptionForItems('Manufacturer'),
                       'value' => $this->fields["manufacturers_id"],
                       'actions' => getItemActionButtons(['info', 'add'], "Manufacturer"),
                    ],
                    __('Group in charge of the hardware') => [
                       'name' => 'groups_id_tech',
                       'type' => 'select',
                       'itemtype' => Group::class,
                       'conditions' => ['is_assign' => 1],
                       'value' => $this->fields["groups_id_tech"],
                       'actions' => getItemActionButtons(['info', 'add'], "Group"),
                    ],
                    _n('Model', 'Models', 1) => [
                       'name' => 'printermodels_id',
                       'type' => 'select',
                       'values' => getOptionForItems('PrinterModel'),
                       'value' => $this->fields["printermodels_id"],
                       'actions' => getItemActionButtons(['info', 'add'], "PrinterModel"),
                    ],
                    __('Alternate username number') => [
                       'name' => 'contact_num',
                       'type' => 'text',
                       'value' => $this->fields["contact_num"],
                    ],
                    __('Serial number') => [
                       'name' => 'serial',
                       'type' => 'text',
                       'value' => $this->fields["serial"],
                    ],
                    __('Alternate username') => [
                       'name' => 'contact',
                       'type' => 'text',
                       'value' => $this->fields["contact"],
                    ],
                    __('Inventory number') => [
                       'name' => 'otherserial',
                       'type' => 'text',
                       'value' => $this->fields["otherserial"],
                    ],
                    User::getTypeName(1) => [
                       'name' => 'users_id',
                       'type' => 'select',
                       'values' => getOptionForItems('User', ['entities_id' => $this->fields["entities_id"]]),
                       'value' => $this->fields["users_id"],
                       'actions' => getItemActionButtons(['info'], "User"),
                    ],
                    __('Management Type') => [
                       'name' => 'is_global',
                       'type' => 'select',
                       'values' => [
                             0 => __('Unit Management'),
                             1 => __('Global Management'),
                       ],
                       'disabled' => $CFG_GLPI['printers_management_restrict'] != 2 ,
                       'value' => $this->fields['is_global'],
                    ],
                    Group::getTypeName(1) => [
                       'name' => 'groups_id',
                       'type' => 'select',
                       'itemtype' => 'Group',
                       'conditions' => ['is_assign' => 1],
                       'value' => $this->fields["groups_id"],
                       'actions' => getItemActionButtons(['info', 'add'], "Group"),
                    ],
                    _n('Network', 'Networks', 1) => [
                       'name' => 'networks_id',
                       'type' => 'select',
                       'values' => getOptionForItems('Network'),
                       'value' => $this->fields["networks_id"],
                       'actions' => getItemActionButtons(['info', 'add'], "Network"),
                    ],
                 ]
              ],
              __("Ports") => [
                 'visible' => true,
                 'inputs' => [
                    __("Serial") => [
                       'name' => 'have_serial',
                       'type' => 'checkbox',
                       'value' => $this->fields["have_serial"],
                    ],
                    __("Parallel") => [
                       'name' => 'have_parallel',
                       'type' => 'checkbox',
                       'value' => $this->fields["have_parallel"],
                    ],
                    __("USB") => [
                       'name' => 'have_usb',
                       'type' => 'checkbox',
                       'value' => $this->fields["have_usb"],
                    ],
                    __("Ethernet") => [
                       'name' => 'have_ethernet',
                       'type' => 'checkbox',
                       'value' => $this->fields["have_ethernet"],
                    ],
                    __("Wifi") => [
                       'name' => 'have_wifi',
                       'type' => 'checkbox',
                       'value' => $this->fields["have_wifi"],
                    ],
                 ]
              ]
           ]
        ];

        renderTwigForm($form, '', $this->fields);
        return true;
    }


    /**
     * Return the linked items (in computers_items)
     *
     * @return an array of linked items  like array('Computer' => array(1,2), 'Printer' => array(5,6))
     * @since 0.84.4
    **/
    public function getLinkedItems()
    {
        global $DB;

        $iterator = $DB->request([
           'SELECT' => 'computers_id',
           'FROM'   => 'glpi_computers_items',
           'WHERE'  => [
              'itemtype'  => $this->getType(),
              'items_id'  => $this->fields['id']
           ]
        ]);
        $tab = [];
        while ($data = $iterator->next()) {
            $tab['Computer'][$data['computers_id']] = $data['computers_id'];
        }
        return $tab;
    }


    /**
     * @see CommonDBTM::getSpecificMassiveActions()
    **/
    public function getSpecificMassiveActions($checkitem = null)
    {

        $actions = parent::getSpecificMassiveActions($checkitem);
        if (static::canUpdate()) {
            Computer_Item::getMassiveActionsForItemtype($actions, __CLASS__, 0, $checkitem);
            $actions += [
               'Item_SoftwareLicense' . MassiveAction::CLASS_ACTION_SEPARATOR . 'add'
                  => "<i class='ma-icon fas fa-key' aria-hidden='true'></i>" .
                     _x('button', 'Add a license')
            ];
            KnowbaseItem_Item::getMassiveActionsForItemtype($actions, __CLASS__, 0, $checkitem);
        }

        return $actions;
    }

    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'massiveaction'      => false,
           'datatype'           => 'number'
        ];

        $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

        $tab[] = [
           'id'                 => '4',
           'table'              => 'glpi_printertypes',
           'field'              => 'name',
           'name'               => _n('Type', 'Types', 1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '40',
           'table'              => 'glpi_printermodels',
           'field'              => 'name',
           'name'               => _n('Model', 'Models', 1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '31',
           'table'              => 'glpi_states',
           'field'              => 'completename',
           'name'               => __('Status'),
           'datatype'           => 'dropdown',
           'condition'          => ['is_visible_printer' => 1]
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => $this->getTable(),
           'field'              => 'serial',
           'name'               => __('Serial number'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '6',
           'table'              => $this->getTable(),
           'field'              => 'otherserial',
           'name'               => __('Inventory number'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '7',
           'table'              => $this->getTable(),
           'field'              => 'contact',
           'name'               => __('Alternate username'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '8',
           'table'              => $this->getTable(),
           'field'              => 'contact_num',
           'name'               => __('Alternate username number'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '70',
           'table'              => 'glpi_users',
           'field'              => 'name',
           'name'               => User::getTypeName(1),
           'datatype'           => 'dropdown',
           'right'              => 'all'
        ];

        $tab[] = [
           'id'                 => '71',
           'table'              => 'glpi_groups',
           'field'              => 'completename',
           'name'               => Group::getTypeName(1),
           'condition'          => ['is_itemgroup' => 1],
           'datatype'           => 'dropdown'
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
           'id'                 => '16',
           'table'              => $this->getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '42',
           'table'              => $this->getTable(),
           'field'              => 'have_serial',
           'name'               => __('Serial'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '43',
           'table'              => $this->getTable(),
           'field'              => 'have_parallel',
           'name'               => __('Parallel'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '44',
           'table'              => $this->getTable(),
           'field'              => 'have_usb',
           'name'               => __('USB'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '45',
           'table'              => $this->getTable(),
           'field'              => 'have_ethernet',
           'name'               => __('Ethernet'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '46',
           'table'              => $this->getTable(),
           'field'              => 'have_wifi',
           'name'               => __('Wifi'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '13',
           'table'              => $this->getTable(),
           'field'              => 'memory_size',
           'name'               => _n('Memory', 'Memories', 1),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'init_pages_counter',
           'name'               => __('Initial page counter'),
           'datatype'           => 'number',
           'nosearch'           => true,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '12',
           'table'              => $this->getTable(),
           'field'              => 'last_pages_counter',
           'name'               => __('Current counter of pages'),
           'datatype'           => 'number',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '9',
           'table'              => $this->getTable(),
           'field'              => '_virtual',
           'linkfield'          => '_virtual',
           'name'               => _n('Cartridge', 'Cartridges', Session::getPluralNumber()),
           'datatype'           => 'specific',
           'massiveaction'      => false,
           'nosearch'           => true,
           'nosort'             => true
        ];

        $tab[] = [
           'id'                 => '17',
           'table'              => 'glpi_cartridges',
           'field'              => 'id',
           'name'               => __('Number of used cartridges'),
           'datatype'           => 'count',
           'forcegroupby'       => true,
           'usehaving'          => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child',
              'condition'          => 'AND NEWTABLE.`date_use` IS NOT NULL
                                     AND NEWTABLE.`date_out` IS NULL'
           ]
        ];

        $tab[] = [
           'id'                 => '18',
           'table'              => 'glpi_cartridges',
           'field'              => 'id',
           'name'               => __('Number of worn cartridges'),
           'datatype'           => 'count',
           'forcegroupby'       => true,
           'usehaving'          => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child',
              'condition'          => 'AND NEWTABLE.`date_out` IS NOT NULL'
           ]
        ];

        $tab[] = [
           'id'                 => '32',
           'table'              => 'glpi_networks',
           'field'              => 'name',
           'name'               => _n('Network', 'Networks', 1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '23',
           'table'              => 'glpi_manufacturers',
           'field'              => 'name',
           'name'               => Manufacturer::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '24',
           'table'              => 'glpi_users',
           'field'              => 'name',
           'linkfield'          => 'users_id_tech',
           'name'               => __('Technician in charge of the hardware'),
           'datatype'           => 'dropdown',
           'right'              => 'own_ticket'
        ];

        $tab[] = [
           'id'                 => '49',
           'table'              => 'glpi_groups',
           'field'              => 'completename',
           'linkfield'          => 'groups_id_tech',
           'name'               => __('Group in charge of the hardware'),
           'condition'          => ['is_assign' => 1],
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '61',
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
           'id'                 => '82',
           'table'              => $this->getTable(),
           'field'              => 'is_global',
           'name'               => __('Global management'),
           'datatype'           => 'bool',
           'massiveaction'      => false
        ];

        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        $tab = array_merge($tab, Item_Devices::rawSearchOptionsToAdd(get_class($this)));

        return $tab;
    }


    /**
     * Add a printer. If already exist in trashbin restore it
     *
     * @param $name          the printer's name (need to be addslashes)
     * @param $manufacturer  the software's manufacturer (need to be addslashes)
     * @param $entity        the entity in which the software must be added
     * @param $comment       comment (default '')
    **/
    public function addOrRestoreFromTrash($name, $manufacturer, $entity, $comment = '')
    {
        global $DB;

        //Look for the software by his name in GLPI for a specific entity
        $iterator = $DB->request([
           'SELECT' => ['id', 'is_deleted'],
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'name'         => $name,
              'is_template'  => 0,
              'entities_id'  => $entity
           ]
        ]);

        if (count($iterator) > 0) {
            //Printer already exists for this entity, get its ID
            $data = $iterator->next();
            $ID   = $data["id"];

            // restore software
            if ($data['is_deleted']) {
                $this->removeFromTrash($ID);
            }
        } else {
            $ID = 0;
        }

        if (!$ID) {
            $ID = $this->addPrinter($name, $manufacturer, $entity, $comment);
        }
        return $ID;
    }


    /**
     * Create a new printer
     *
     * @param $name         the printer's name (need to be addslashes)
     * @param $manufacturer the printer's manufacturer (need to be addslashes)
     * @param $entity       the entity in which the printer must be added
     * @param $comment      (default '')
     *
     * @return the printer's ID
    **/
    public function addPrinter($name, $manufacturer, $entity, $comment = '')
    {
        global $DB;

        $manufacturer_id = 0;
        if ($manufacturer != '') {
            $manufacturer_id = Dropdown::importExternal('Manufacturer', $manufacturer);
        }

        //If there's a printer in a parent entity with the same name and manufacturer
        $iterator = $DB->request([
           'SELECT' => 'id',
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'manufacturers_id'   => $manufacturer_id,
              'name'               => $name,
           ] + getEntitiesRestrictCriteria(self::getTable, 'entities_id', $entity, true)
        ]);

        if ($printer = $iterator->next()) {
            $id = $printer["id"];
        } else {
            $input["name"]             = $name;
            $input["manufacturers_id"] = $manufacturer_id;
            $input["entities_id"]      = $entity;

            $id = $this->add($input);
        }
        return $id;
    }


    /**
     * Restore a software from trashbin
     *
     * @param $ID  the ID of the software to put in trashbin
     *
     * @return boolean (success)
    **/
    public function removeFromTrash($ID)
    {
        return $this->restore(["id" => $ID]);
    }


    public static function getIcon()
    {
        return "fas fa-print";
    }
}
