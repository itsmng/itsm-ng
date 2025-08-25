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
 * Enclosure Class
**/
class Enclosure extends CommonDBTM
{
    use Glpi\Features\DCBreadcrumb;
    use Glpi\Features\Clonable;

    // From CommonDBTM
    public $dohistory                   = true;
    public static $rightname                   = 'datacenter';

    public function getCloneRelations(): array
    {
        return [
           Item_Enclosure::class,
           Item_Devices::class,
           NetworkPort::class
        ];
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Enclosure', 'Enclosures', $nb);
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong)
           ->addImpactTab($ong, $options)
           ->addStandardTab('Item_Enclosure', $ong, $options)
           ->addStandardTab('Item_Devices', $ong, $options)
           ->addStandardTab('NetworkPort', $ong, $options)
           ->addStandardTab('Infocom', $ong, $options)
           ->addStandardTab('Contract_Item', $ong, $options)
           ->addStandardTab('Document_Item', $ong, $options)
           ->addStandardTab('Ticket', $ong, $options)
           ->addStandardTab('Item_Problem', $ong, $options)
           ->addStandardTab('Change_Item', $ong, $options)
           ->addStandardTab('Log', $ong, $options);
        return $ong;
    }

    public function showForm($ID, $options = [])
    {
        $title = __('New item') . ' - ' . self::getTypeName(1);
        $isNew = $this->isNewID($ID) || (isset($options['withtemplate']) && $options['withtemplate'] == 2);

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
                       'itemtype' => State::class,
                       'conditions' => ['is_visible_enclosure' => 1],
                       'value' => $this->fields['states_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "State"),
                    ],
                    __('Power supplies') => [
                       'name' => 'power_supplies',
                       'type' => 'number',
                       'value' => $this->fields['power_supplies'] ?: 1,
                       'min' => 1,
                       'max' => 6,
                    ],
                    Manufacturer::getTypeName(1) => [
                       'name' => 'manufacturers_id',
                       'type' => 'select',
                       'values' => getOptionForItems('Manufacturer'),
                       'value' => $this->fields['manufacturers_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "Manufacturer"),
                    ],
                    __('Location') => [
                       'name' => 'locations_id',
                       'type' => 'select',
                       'itemtype' => Location::class,
                       'value' => $this->fields['locations_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "Location"),
                    ],
                    _n('Model', 'Models', 1) => [
                       'name' => 'enclosuremodels_id',
                       'type' => 'select',
                       'values' => getOptionForItems('EnclosureModel'),
                       'value' => $this->fields['enclosuremodels_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "EnclosureModel"),
                    ],
                    __("Technician in charge of the hardware") => [
                       'name' => 'tech_users_id',
                       'type' => 'select',
                       'values' => getOptionsForUsers('own_ticket', ['entities_id' => $this->fields['entities_id']]),
                       'value' => $this->fields['tech_users_id'],
                       'actions' => getItemActionButtons(['info'], "User"),
                    ],
                    __('Group in charge of the hardware') => [
                       'name' => 'tech_groups_id',
                       'type' => 'select',
                       'itemtype' => Group::class,
                       'conditions' => [ 'is_assign' => 1 ],
                       'value' => $this->fields['tech_groups_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "Group"),
                    ],
                    __('Serial number') => [
                       'name' => 'serial',
                       'type' => 'text',
                       'value' => $this->fields['serial'],
                    ],
                    __('Inventory number') => [
                       'name' => 'otherserial',
                       'type' => 'text',
                       'value' => $this->fields['otherserial'],
                    ],
                    __('Comments') => [
                       'name' => 'comment',
                       'type' => 'textarea',
                       'value' => $this->fields['comment'],
                    ],
                 ]
              ]
           ]
        ];

        ob_start();

        renderTwigForm($form, '', $this->fields);
        return true;
    }

    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'massiveaction'      => false, // implicit field is id
           'datatype'           => 'number'
        ];

        $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

        $tab[] = [
           'id'                 => '40',
           'table'              => 'glpi_enclosuremodels',
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
           'condition'          => ['is_visible_computer' => 1]
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
           'id'                 => '16',
           'table'              => $this->getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text'
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
           'datatype'           => 'dropdown'
        ];

        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        $tab = array_merge($tab, Datacenter::rawSearchOptionsToAdd(get_class($this)));

        return $tab;
    }

    /**
     * Get already filled places
     *
     * @param string  $itemtype  The item type
     * @param integer $items_id  The item's ID
     *
     * @return array [x => ['depth' => 1, 'orientation' => 0, 'width' => 1, 'hpos' =>0]]
     *               orientation will not be available if depth is > 0.5; hpos will not be available
     *               if width is = 1
     */
    public function getFilled($itemtype = null, $items_id = null)
    {
        $request = $this::getAdapter()->request([
           'FROM'   => Item_Enclosure::getTable(),
           'WHERE'  => [
              'enclosures_id' => $this->getID()
           ]
        ]);

        $filled = [];
        while ($row = $request->fetchAssociative()) {
            if (
                empty($itemtype) || empty($items_id)
                || $itemtype != $row['itemtype'] || $items_id != $row['items_id']
            ) {
                $filled[$row['position']] = $row['position'];
            }
        }
        return $filled;
    }

    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
              Item_Enclosure::class,
            ]
        );
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


    public static function getIcon()
    {
        return "fas fa-th";
    }
}
