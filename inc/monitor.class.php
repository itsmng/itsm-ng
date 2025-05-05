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
 * Monitor Class
**/
class Monitor extends CommonDBTM
{
    use Glpi\Features\DCBreadcrumb;
    use Glpi\Features\Clonable;

    // From CommonDBTM
    public $dohistory                   = true;
    protected static $forward_entity_to = ['Infocom', 'ReservationItem', 'Item_OperatingSystem', 'NetworkPort',
                                           'Item_SoftwareVersion'];

    public static $rightname                   = 'monitor';
    protected $usenotepad               = true;

    public function getCloneRelations(): array
    {
        return [
           Item_OperatingSystem::class,
           Item_Devices::class,
           Infocom::class,
           Contract_Item::class,
           Document_Item::class,
           Computer_Item::class,
           KnowbaseItem_Item::class
        ];
    }

    /**
     * Name of the type
     *
     * @param $nb  string   number of item in the type
    **/
    public static function getTypeName($nb = 0)
    {
        return _n('Monitor', 'Monitors', $nb);
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
        $this->addStandardTab('Item_Devices', $ong, $options);
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
        $this->addStandardTab('Domain_Item', $ong, $options);
        $this->addStandardTab('Appliance_Item', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }

    public function prepareInputForAdd($input)
    {

        if (isset($input["id"]) && ($input["id"] > 0)) {
            $input["_oldID"] = $input["id"];
        }
        if (isset($input["size"]) && ($input["size"] == '')) {
            unset($input["size"]);
        }
        unset($input['id']);
        unset($input['withtemplate']);

        return $input;
    }


    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
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
     * Print the monitor form
     *
     * @param $ID        integer  ID of the item
     * @param $options   array
     *     - target filename : where to go when done.
     *     - withtemplate boolean : template or basic item
     *
     * @return boolean item found
     **/
    public function showForm($ID, $options = [])
    {
        global $CFG_GLPI;
        $isNew = $this->isNewID($ID) || (isset($options['withtemplate']) && $options['withtemplate'] == 2);

        $form = [
           'action' => $this->getFormURL(),
           'itemtype' => $this::class,
           'content' => [
              __('General') => [
                 'visible' => true,
                 'inputs' => [
                    __("Name") => [
                       'name' => 'name',
                       'type' => 'text',
                       'value' => $this->fields['name'],
                       'placeholder' => ''
                    ],
                    __("Status") => [
                       'name' => 'states_id',
                       'type' => 'select',
                       'values' => getOptionForItems('State', ['is_visible_monitor' => 1, 'entities_id' => $this->fields['entities_id']]),
                       'value' => $this->fields['states_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "State"),
                    ],
                    __("Location") => [
                       'name' => 'locations_id',
                       'id' => 'locations_id_dropdown',
                       'type' => 'select',
                       'values' => getOptionForItems('Location', ['entities_id' => $this->fields['entities_id']]),
                       'value' => $this->fields['locations_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "Location"),
                    ],
                    __("Type") => [
                       'name' => 'monitortypes_id',
                       'type' => 'select',
                       'values' => getOptionForItems('MonitorType'),
                       'value' => $this->fields['monitortypes_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "MonitorType"),
                    ],
                    __("Technician in charge of the hardware") => [
                       'name' => 'tech_users_id',
                       'type' => 'select',
                       'values' => getOptionsForUsers('own_ticket', ['entities_id' => $this->fields['entities_id']]),
                       'value' => $this->fields['tech_users_id'],
                       'actions' => getItemActionButtons(['info'], "User"),
                    ],
                    __("Manufacturer") => [
                       'name' => 'manufacturers_id',
                       'type' => 'select',
                       'values' => getOptionForItems('Manufacturer'),
                       'value' => $this->fields['manufacturers_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "Manufacturer"),
                    ],
                    __("Group in charge of the hardware") => [
                       'name' => 'tech_groups_id',
                       'type' => 'select',
                       'values' => getOptionForItems('Group', ['is_assign' => 1, 'entities_id' => $this->fields['entities_id']]),
                       'value' => $this->fields['tech_groups_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "Group"),
                    ],
                    __("Model") => [
                       'name' => 'monitormodels_id',
                       'type' => 'select',
                       'values' => getOptionForItems('MonitorModel'),
                       'value' => $this->fields['monitormodels_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "MonitorModel"),
                    ],
                    __("Serial number") => [
                       'name' => 'serial',
                       'type' => 'text',
                       'value' => $this->fields['serial'],
                    ], // DOES NOT TAKE INTO ACCOUNT AUTOCOMPLETION FIELD
                    __("Inventory/Asset number") => [
                       'name' => 'otherserial',
                       'type' => 'text',
                       'value' => $this->fields['otherserial'],
                    ], // DOES NOT TAKE INTO ACCOUNT AUTOCOMPLETION FIELD
                      User::getTypeName(1) => [
                       'type' => 'select',
                       'name' => 'users_id',
                       'values' => getOptionsForUsers('all', ['entities_id' => $this->fields['entities_id']]),
                       'value' => $this->fields['users_id'],
                       'actions' => getItemActionButtons(['info'], "User"),
                    ],
                    Group::getTypeName() => [
                       'name' => 'groups_id',
                       'type' => 'select',
                       'values' => getOptionForItems('Group', ['is_itemgroup' => 1, 'entities_id' => $this->fields['entities_id']]),
                       'value' => $this->fields['groups_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "Group"),
                    ],
                    __('Comments') => [
                       'name' => 'comment',
                       'type' => 'textarea',
                       'value' => $this->fields['comment'],
                    ],
                    __('Size') => [
                       'name' => 'size',
                       'type' => 'text',
                       'value' => $this->fields['size'],
                       'after' => '"',
                    ],
                    __('Management Type') => [
                       'name' => 'is_global',
                       'type' => 'select',
                       'values' => [
                             0 => __('Unit Management'),
                             1 => __('Global Management'),
                       ],
                       'disabled' => $CFG_GLPI['monitors_management_restrict'] != 2 ,
                       'value' => $this->fields['is_global'],
                    ],
                 ],
              ],
              __('Flags') => [
                 'visible' => true,
                 'inputs' => [
                    __('Microphone') => [
                       'name' => 'have_micro',
                       'type' => 'checkbox',
                       'value' => $this->fields['have_micro'],
                    ],
                    __('Speakers') => [
                       'name' => 'have_speaker',
                       'type' => 'checkbox',
                       'value' => $this->fields['have_speaker'],
                    ],
                    __('Sub-D') => [
                       'name' => 'have_subd',
                       'type' => 'checkbox',
                       'value' => $this->fields['have_subd'],
                    ],
                    __('BNC') => [
                       'name' => 'have_bnc',
                       'type' => 'checkbox',
                       'value' => $this->fields['have_bnc'],
                    ],
                    __('DVI') => [
                       'name' => 'have_dvi',
                       'type' => 'checkbox',
                       'value' => $this->fields['have_dvi'],
                    ],
                    __('Pivot') => [
                       'name' => 'have_pivot',
                       'type' => 'checkbox',
                       'value' => $this->fields['have_pivot'],
                    ],
                    __('HDMI') => [
                       'name' => 'have_hdmi',
                       'type' => 'checkbox',
                       'value' => $this->fields['have_hdmi'],
                    ],
                    __('DisplayPort') => [
                       'name' => 'have_displayport',
                       'type' => 'checkbox',
                       'value' => $this->fields['have_displayport'],
                    ],
                 ],
              ],
           ]
        ];

        renderTwigForm($form, '', $this->fields);
        return true;
    }


    /**
     * Return the linked items (in computers_items)
     *
     * @return array of linked items  like array('Computer' => array(1,2), 'Printer' => array(5,6))
     * @since 0.84.4
    **/
    public function getLinkedItems()
    {
        $request = $this::getAdapter()->request([
           'SELECT' => 'computers_id',
           'FROM'   => 'glpi_computers_items',
           'WHERE'  => [
              'itemtype'  => $this->getType(),
              'items_id'  => $this->fields['id']
           ]
        ]);
        $tab = [];
        while ($data = $request->fetchAssociative()) {
            $tab['Computer'][$data['computers_id']] = $data['computers_id'];
        }
        return $tab;
    }


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
           'table'              => 'glpi_monitortypes',
           'field'              => 'name',
           'name'               => _n('Type', 'Types', 1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '40',
           'table'              => 'glpi_monitormodels',
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
           'condition'          => ['is_visible_monitor' => 1]
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
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'size',
           'name'               => __('Size'),
           'datatype'           => 'decimal',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '41',
           'table'              => $this->getTable(),
           'field'              => 'have_micro',
           'name'               => __('Microphone'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '42',
           'table'              => $this->getTable(),
           'field'              => 'have_speaker',
           'name'               => __('Speakers'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '43',
           'table'              => $this->getTable(),
           'field'              => 'have_subd',
           'name'               => __('Sub-D'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '44',
           'table'              => $this->getTable(),
           'field'              => 'have_bnc',
           'name'               => __('BNC'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '45',
           'table'              => $this->getTable(),
           'field'              => 'have_dvi',
           'name'               => __('DVI'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '46',
           'table'              => $this->getTable(),
           'field'              => 'have_pivot',
           'name'               => __('Pivot'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '47',
           'table'              => $this->getTable(),
           'field'              => 'have_hdmi',
           'name'               => __('HDMI'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '48',
           'table'              => $this->getTable(),
           'field'              => 'have_displayport',
           'name'               => __('DisplayPort'),
           'datatype'           => 'bool'
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
           'linkfield'          => 'tech_users_id',
           'name'               => __('Technician in charge of the hardware'),
           'datatype'           => 'dropdown',
           'right'              => 'own_ticket'
        ];

        $tab[] = [
           'id'                 => '49',
           'table'              => 'glpi_groups',
           'field'              => 'completename',
           'linkfield'          => 'tech_groups_id',
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

        $tab = array_merge($tab, Datacenter::rawSearchOptionsToAdd(get_class($this)));

        return $tab;
    }


    public static function getIcon()
    {
        return "fas fa-desktop";
    }
}
