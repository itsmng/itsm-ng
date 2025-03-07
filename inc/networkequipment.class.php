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

use Itsmng\Domain\Entities\Networkequipment as EntitiesNetworkequipment;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}


/**
 * Network equipment Class
**/
class NetworkEquipment extends CommonDBTM
{
    use Glpi\Features\DCBreadcrumb;
    use Glpi\Features\Clonable;

    // From CommonDBTM
    public $dohistory                   = true;
    protected static $forward_entity_to = ['Infocom', 'NetworkPort', 'ReservationItem',
                                           'Item_OperatingSystem', 'Item_Disk', 'Item_SoftwareVersion'];

    public static $rightname                   = 'networking';
    protected $usenotepad               = true;

    public $entity = EntitiesNetworkequipment::class;

    /** RELATIONS */
    public function getCloneRelations(): array
    {
        return [
           Item_OperatingSystem::class,
           Item_Devices::class,
           Infocom::class,
           NetworkPort::class,
           Contract_Item::class,
           Document_Item::class,
           KnowbaseItem_Item::class
        ];
    }
    /** /RELATIONS */


    /**
     * Name of the type
     *
     * @param $nb  integer  number of item in the type (default 0)
    **/
    public static function getTypeName($nb = 0)
    {
        return _n('Network device', 'Network devices', $nb);
    }


    /**
     * @see CommonGLPI::getAdditionalMenuOptions()
     *
     * @since 0.85
    **/
    public static function getAdditionalMenuOptions()
    {

        if (static::canView()) {
            $options = [
               'networkport' => [
                  'title' => NetworkPort::getTypeName(Session::getPluralNumber()),
                  'page'  => NetworkPort::getFormURL(false),
               ],
            ];
            return $options;
        }
        return false;
    }


    /**
     * @see CommonGLPI::getMenuName()
     *
     * @since 0.85
    **/
    // bug in translation: https://github.com/glpi-project/glpi/issues/1970
    /*static function getMenuName() {
       return _n('Network', 'Networks', Session::getPluralNumber());
    }*/


    /**
     * @since 0.84
     *
     * @see CommonDBTM::cleanDBonPurge()
    **/
    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
              Certificate_Item::class,
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
        $this->addDefaultFormTab($ong)
           ->addImpactTab($ong, $options)
           ->addStandardTab('Item_OperatingSystem', $ong, $options)
           ->addStandardTab('Item_SoftwareVersion', $ong, $options)
           ->addStandardTab('Item_Devices', $ong, $options)
           ->addStandardTab('Item_Disk', $ong, $options)
           ->addStandardTab('NetworkPort', $ong, $options)
           ->addStandardTab('NetworkName', $ong, $options)
           ->addStandardTab('Infocom', $ong, $options)
           ->addStandardTab('Contract_Item', $ong, $options)
           ->addStandardTab('Document_Item', $ong, $options)
           ->addStandardTab('KnowbaseItem_Item', $ong, $options)
           ->addStandardTab('Ticket', $ong, $options)
           ->addStandardTab('Item_Problem', $ong, $options)
           ->addStandardTab('Change_Item', $ong, $options)
           ->addStandardTab('Link', $ong, $options)
           ->addStandardTab('Notepad', $ong, $options)
           ->addStandardTab('Reservation', $ong, $options)
           ->addStandardTab('Certificate_Item', $ong, $options)
           ->addStandardTab('Domain_Item', $ong, $options)
           ->addStandardTab('Appliance_Item', $ong, $options)
           ->addStandardTab('Log', $ong, $options);

        return $ong;
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


    /**
     * Can I change recursive flag to false
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

        // RELATION : networking -> _port -> _wire -> _port -> device

        // Evaluate connection in the 2 ways
        foreach (
            ["networkports_id_1" => "networkports_id_2",
                  "networkports_id_2" => "networkports_id_1"] as $enda => $endb
        ) {
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

            $res = $DB->request($criteria);
            if ($res) {
                while ($data = $res->next()) {
                    $itemtable = getTableForItemType($data["itemtype"]);
                    if ($item = getItemForItemtype($data["itemtype"])) {
                        // For each itemtype which are entity dependant
                        if ($item->isEntityAssign()) {
                            if (
                                countElementsInTable($itemtable, ['id' => $data["ids"],
                                                     'NOT' => ['entities_id' => $entities ]]) > 0
                            ) {
                                return false;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }


    /**
     * Print the networking form
     *
     * @param $ID        integer ID of the item
     * @param $options   array
     *     - target filename : where to go when done.
     *     - withtemplate boolean : template or basic item
     *
     *@return boolean item found
    **/
    public function showForm($ID, $options = [])
    {
        $title = self::getTypeName(1);

        $form = [
           'action' => $this->getFormURL(),
           'itemtype' => $this::class,
           'content' => [
              $title => [
                 'visible' => true,
                 'inputs' => [
                    __('Name') => [
                       'name' => 'name',
                       'type' => 'text',
                       'value' => $this->fields['name'],
                    ],
                    __('Status') => [
                       'name' => 'states_id',
                       'type' => 'select',
                       'value' => $this->fields['states_id'],
                       'itemtype' => State::class,
                       'actions' => getItemActionButtons(['info', 'add'], "State"),
                    ],
                    __('Location') => [
                       'name' => 'locations_id',
                       'type' => 'select',
                       'value' => $this->fields['locations_id'],
                       'itemtype' => Location::class,
                       'actions' => getItemActionButtons(['info', 'add'], "Location"),
                    ],
                    __('Type') => [
                       'name' => 'networkequipmenttypes_id',
                       'type' => 'select',
                       'value' => $this->fields['networkequipmenttypes_id'],
                       'values' => getOptionForItems("NetworkEquipmentType"),
                       'actions' => getItemActionButtons(['info', 'add'], "NetworkEquipmentType"),
                    ],
                    __("Technician in charge of the software") => [
                       'name' => 'users_id_tech',
                       'type' => 'select',
                       'value' => $this->fields['users_id_tech'],
                       'values' => getOptionsForUsers('own_ticket', ['entities_id' => $this->fields['entities_id']]),
                       'actions' => getItemActionButtons(['info'], "User"),
                    ],
                    __("Manufacturer") => [
                       'name' => 'manufacturers_id',
                       'type' => 'select',
                       'values' => getOptionForItems("Manufacturer"),
                       'value' => $this->fields['manufacturers_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "Manufacturer"),
                    ],
                    __("Group in charge of the software") => [
                       'name' => 'groups_id_tech',
                       'type' => 'select',
                       'value' => $this->fields['groups_id_tech'],
                       'itemtype' => Group::class,
                       'actions' => getItemActionButtons(['info', 'add'], "Group"),
                    ],
                    __("Model") => [
                       'name' => 'networkequipmentmodels_id',
                       'type' => 'select',
                       'value' => $this->fields['networkequipmentmodels_id'],
                       'values' => getOptionForItems("NetworkEquipmentModel"),
                       'actions' => getItemActionButtons(['info', 'add'], "NetworkEquipmentModel"),
                    ],
                    __("Alternate username number") => [
                       'name' => 'contact_num',
                       'type' => 'text',
                       'value' => $this->fields['contact_num'],
                    ],
                    __("Serial number") => [
                       'name' => 'serial',
                       'type' => 'text',
                       'value' => $this->fields['serial'],
                    ],
                    __("Alternate username") => [
                       'name' => 'contact',
                       'type' => 'text',
                       'value' => $this->fields['contact'],
                    ],
                    __("Inventory number") => [
                       'name' => 'otherserial',
                       'type' => 'text',
                       'value' => $this->fields['otherserial'],
                    ],
                    __("User") => [
                       'name' => 'users_id',
                       'type' => 'select',
                       'value' => $this->fields['users_id'],
                       'itemtype' => User::class,
                       'actions' => getItemActionButtons(['info'], "User"),
                    ],
                    __("Network") => [
                       'name' => 'networks_id',
                       'type' => 'select',
                       'value' => $this->fields['networks_id'],
                       'values' => getOptionForItems("Network"),
                       'actions' => getItemActionButtons(['info', 'add'], "Network"),
                    ],
                    __("Group") => [
                       'name' => 'groups_id',
                       'type' => 'select',
                       'value' => $this->fields['groups_id'],
                       'itemtype' => Group::class,
                       'actions' => getItemActionButtons(['info', 'add'], "Group"),
                    ],
                    __("Memory") => [
                       'name' => 'ram',
                       'type' => 'text',
                       'value' => $this->fields['ram'],
                       'after' => __('Mio'),
                    ],
                    __("Comment") => [
                       'name' => 'comment',
                       'type' => 'textarea',
                       'value' => $this->fields['comment'],
                    ],
                 ]
              ],
           ]
        ];

        renderTwigForm($form, '', $this->fields);
        return true;
    }


    public function getSpecificMassiveActions($checkitem = null)
    {

        $isadmin = static::canUpdate();
        $actions = parent::getSpecificMassiveActions($checkitem);

        if ($isadmin) {
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
           'table'              => 'glpi_networkequipmenttypes',
           'field'              => 'name',
           'name'               => _n('Type', 'Types', 1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '40',
           'table'              => 'glpi_networkequipmentmodels',
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
           'condition'          => ['is_visible_networkequipment' => 1]
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
           'datatype'           => 'dropdown',
           'condition'          => ['is_itemgroup' => 1]
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
           'id'                 => '11',
           'table'              => 'glpi_devicefirmwares',
           'field'              => 'version',
           'name'               => _n('Firmware', 'Firmware', 1),
           'forcegroupby'       => true,
           'usehaving'          => true,
           'massiveaction'      => false,
           'datatype'           => 'dropdown',
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_items_devicefirmwares',
                 'joinparams'         => [
                    'jointype'           => 'itemtype_item',
                    'specific_itemtype'  => 'NetworkEquipment'
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '14',
           'table'              => $this->getTable(),
           'field'              => 'ram',
           'name'               => sprintf(__('%1$s (%2$s)'), _n('Memory', 'Memories', 1), __('Mio')),
           'datatype'           => 'number',
           'autocomplete'       => true,
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
           'id'                 => '65',
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

        // add operating system search options
        $tab = array_merge($tab, Item_OperatingSystem::rawSearchOptionsToAdd(get_class($this)));

        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        $tab = array_merge($tab, Item_Devices::rawSearchOptionsToAdd(get_class($this)));

        $tab = array_merge($tab, Datacenter::rawSearchOptionsToAdd(get_class($this)));

        return $tab;
    }

    public static function getIcon()
    {
        return "fas fa-network-wired";
    }
}
