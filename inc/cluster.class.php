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

use Itsmng\Domain\Entities\Cluster as EntitiesCluster;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Cluster Class
 **/
class Cluster extends CommonDBTM
{
    use Glpi\Features\Clonable;

    // From CommonDBTM
    public $dohistory                   = true;
    public static $rightname                   = 'cluster';

    public $entity = EntitiesCluster::class;

    public function getCloneRelations(): array
    {
        return [
           NetworkPort::class
        ];
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Cluster', 'Clusters', $nb);
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong)
           ->addImpactTab($ong, $options)
           ->addStandardTab('Item_Cluster', $ong, $options)
           ->addStandardTab('NetworkPort', $ong, $options)
           ->addStandardTab('Contract_Item', $ong, $options)
           ->addStandardTab('Document_Item', $ong, $options)
           ->addStandardTab('Ticket', $ong, $options)
           ->addStandardTab('Item_Problem', $ong, $options)
           ->addStandardTab('Change_Item', $ong, $options)
           ->addStandardTab('Appliance_Item', $ong, $options)
           ->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    public function showForm($ID)
    {
        $form = [
           'action' => Toolbox::getItemTypeFormURL('cluster'),
           'itemtype' => $this::class,
           'content' => [
              __('Cluster') => [
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
                    __('Status') => [
                       'name' => 'states_id',
                       'type' => 'select',
                       'itemtype' => State::class,
                       'conditions' => ['is_visible_line' => 1],
                       'value' => $this->fields['states_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "State"),
                    ],
                    __('UUID') => [
                       'name' => 'uuid',
                       'type' => 'text',
                       'value' => $this->fields['uuid'] ?? '',
                    ],
                    __('Version') => [
                       'name' => 'version',
                       'type' => 'text',
                       'value' => $this->fields['version'] ?? '',
                    ],
                    __('Type') => [
                       'name' => 'clustertypes_id',
                       'type' => 'select',
                       'itemtype' => ClusterType::class,
                       'value' => $this->fields['clustertypes_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "ClusterType"),
                    ],
                    __('Auto update system') => [
                       'name' => 'autoupdatesystems_id',
                       'type' => 'select',
                       'values' => getOptionForItems('AutoUpdateSystem'),
                       'value' => $this->fields['autoupdatesystems_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "AutoUpdateSystem"),
                    ],
                    __('Technician in charge of the hardware') => [
                       'name' => 'users_id_tech',
                       'type' => 'select',
                       'values' => getOptionsForUsers('own_ticket', ['entities_id' => $this->fields['entities_id']  ?? '']),
                       'value' => $this->fields['users_id_tech'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "User"),
                    ],
                    __('Group in charge of the hardware') => [
                       'name' => 'groups_id_tech',
                       'type' => 'select',
                       'itemtype' => Group::class,
                       'conditions' => ['is_assign' => 1],
                       'value' => $this->fields['groups_id_tech'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "Group"),
                    ],
                    __('Comments') => [
                       'name' => 'comment',
                       'type' => 'textarea',
                       'value' => $this->fields['comment'] ?? '',
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
           'id'                 => '31',
           'table'              => 'glpi_states',
           'field'              => 'completename',
           'name'               => __('Status'),
           'datatype'           => 'dropdown',
           'condition'          => ['is_visible_cluster' => 1]
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => $this->getTable(),
           'field'              => 'uuid',
           'name'               => __('UUID'),
           'datatype'           => 'string'
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

        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        return $tab;
    }

    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
              Item_Cluster::class,
            ]
        );
    }


    public static function getIcon()
    {
        return "fas fa-project-diagram";
    }
}
