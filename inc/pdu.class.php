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

use Itsmng\Domain\Entities\Pdu as EntitiesPdu;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * PDU Class
**/
class PDU extends CommonDBTM
{
    use Glpi\Features\DCBreadcrumb;
    use Glpi\Features\Clonable;

    // From CommonDBTM
    public $dohistory                   = true;
    public static $rightname                   = 'datacenter';

    public $entity = EntitiesPdu::class;

    public function getCloneRelations(): array
    {
        return [
           Pdu_Plug::class,
           Item_Devices::class,
           NetworkPort::class
        ];
    }

    public static function getTypeName($nb = 0)
    {
        return _n('PDU', 'PDUs', $nb);
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong)
           ->addImpactTab($ong, $options)
           ->addStandardTab('Pdu_Plug', $ong, $options)
           ->addStandardTab('Item_Devices', $ong, $options)
           ->addStandardTab('NetworkPort', $ong, $options)
           ->addStandardTab('Infocom', $ong, $options)
           ->addStandardTab('Contract_Item', $ong, $options)
           ->addStandardTab('Document_Item', $ong, $options)
           ->addStandardTab('Ticket', $ong, $options)
           ->addStandardTab('Item_Problem', $ong, $options)
           ->addStandardTab('Change_Item', $ong, $options)
           ->addStandardTab('Log', $ong, $options);
        ;
        return $ong;
    }

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
                       'values' => getOptionForItems('State', ['is_visible_pdu' => 1]),
                       'value' => $this->fields['states_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "State"),
                    ],
                    __('Location') => [
                       'name' => 'locations_id',
                       'type' => 'select',
                       'values' => getOptionForItems('Location', ['entities_id' => $this->fields['entities_id'],]),
                       'value' => $this->fields['locations_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "Location"),
                    ],
                    __('Type') => [
                       'name' => 'pdutypes_id',
                       'type' => 'select',
                       'values' => getOptionForItems('PDUType'),
                       'value' => $this->fields['pdutypes_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "PDUType"),
                    ],
                    __("Technician in charge of the hardware") => [
                       'name' => 'users_id_tech',
                       'type' => 'select',
                       'values' => getOptionsForUsers('own_ticket', ['entities_id' => $this->fields['entities_id']]),
                       'value' => $this->fields['users_id_tech'],
                       'actions' => getItemActionButtons(['info'], "User"),
                    ],
                    Manufacturer::getTypeName(1) => [
                       'name' => 'manufacturers_id',
                       'type' => 'select',
                       'values' => getOptionForItems('Manufacturer'),
                       'value' => $this->fields['manufacturers_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "Manufacturer"),
                    ],
                    __('Group in charge of the hardware') => [
                       'name' => 'groups_id_tech',
                       'type' => 'select',
                       'itemtype' => 'Group',
                       'condition' => ['is_assign' => 1, 'entities_id' => $this->fields['entities_id']],
                       'value' => $this->fields['groups_id_tech'],
                       'actions' => getItemActionButtons(['info', 'add'], "Group"),
                    ],
                    _n('Model', 'Models', 1) => [
                       'name' => 'pdumodels_id',
                       'type' => 'select',
                       'values' => getOptionForItems('PDUModel'),
                       'value' => $this->fields['pdumodels_id'],
                       'actions' => getItemActionButtons(['info', 'add'], "PDUModel"),
                    ],
                    __('Serial number') => [
                       'name' => 'serial',
                       'type' => 'text',
                       'value' => $this->fields['serial'],
                    ],
                    __('Inventory number') => [
                       'name' => 'otherserial',
                       'type' => 'text',
                       'value' => $this->fields['serial'],
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

        $tab[] = [
           'id'                 => '4',
           'table'              => 'glpi_pdutypes',
           'field'              => 'name',
           'name'               => _n('Type', 'Types', 1),
           'datatype'           => 'dropdown'
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

        $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

        $tab[] = [
           'id'                 => '19',
           'table'              => $this->getTable(),
           'field'              => 'date_mod',
           'name'               => __('Last update'),
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
           'id'                 => '31',
           'table'              => 'glpi_states',
           'field'              => 'completename',
           'name'               => __('Status'),
           'datatype'           => 'dropdown',
           'condition'          => ['is_visible_pdu' => 1]
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
           'id'                 => '40',
           'table'              => 'glpi_pdumodels',
           'field'              => 'name',
           'name'               => _n('Model', 'Models', 1),
           'datatype'           => 'dropdown'
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
           'id'                 => '121',
           'table'              => $this->getTable(),
           'field'              => 'date_creation',
           'name'               => __('Creation date'),
           'datatype'           => 'datetime',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '80',
           'table'              => 'glpi_entities',
           'field'              => 'completename',
           'name'               => Entity::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        $tab = array_merge($tab, Datacenter::rawSearchOptionsToAdd(get_class($this)));

        return $tab;
    }

    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
              Pdu_Plug::class,
              PDU_Rack::class,
            ]
        );
    }


    public static function getIcon()
    {
        return "fas fa-plug";
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
}
