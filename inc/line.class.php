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

/**
 * @since 9.2
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class Line extends CommonDBTM {
	// From CommonDBTM
	public $dohistory                   = true;

	static $rightname                   = 'line';
	protected $usenotepad               = true;


	static function getTypeName($nb = 0) {
    	return _n('Line', 'Lines', $nb);
   	}


	/**
	* @see CommonDBTM::useDeletedToLockIfDynamic()
    *
    * @since 0.84
    **/
	function useDeletedToLockIfDynamic() {
    	return false;
	}

	function defineTabs($options = []) {

    	$ong = [];
    	$this->addDefaultFormTab($ong);
		$this->addImpactTab($ong, $options);
		$this->addStandardTab('Infocom', $ong, $options);
		$this->addStandardTab('Contract_Item', $ong, $options);
		$this->addStandardTab('Document_Item', $ong, $options);
		$this->addStandardTab('Notepad', $ong, $options);
		$this->addStandardTab('Log', $ong, $options);

		return $ong;
	}

	/**
	* Print the contact form
	*
	* @param $ID        integer ID of the item
	* @param $options   array of possible options:
	*     - target for the Form
	*     - withtemplate : template or basic item
	*
	* @return void
	**/
	function showForm($ID, $options = []) {
		require_once GLPI_ROOT . '/src/twig/twig.class.php';
		require_once GLPI_ROOT . '/src/twig/twig.utils.php';

		$form = [
			'action' => Toolbox::getItemTypeFormURL('line'),
			'buttons' => [
				[
					'type' => 'submit',
					'name' => 'add',
					'value' => __('Add'),
					'class' => 'submit-button btn btn-warning',
				]
			],
            'content' => [
				__('Line') => [
					'visible' => true,
					'inputs' => [
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
						],
						__('Caller number') => [
							'name' => 'caller_num',
							'type' => 'text',
							'value' => $this->fields['caller_num'] ?? '',
						],
						__('User') => [
							'name' => 'users_id',
							'type' => 'select',
							'values' => getOptionForItems("User"),
							'value' => $this->fields['users_id'] ?? ''
						],
						__('Group') => [
							'name' => 'groups_id',
							'type' => 'select',
							'values' => getOptionForItems("Group"),
							'value' => $this->fields['groups_id'] ?? ''
						],
						__('Line operator') => [
							'name' => 'lineoperators_id',
							'type' => 'select',
							'values' => getOptionForItems("LineOperators"),
							'value' => $this->fields['lineoperators_id'] ?? ''
						],
						__('Status') => [
							'name' => 'states_id',
							'type' => 'select',
							'values' => getOptionForItems('State', ['is_visible_line' => 1]),
							'value' => $this->fields['states_id'] ?? ''
						],
						__('Line type') => [
							'name' => 'linetypes_id',
							'type' => 'select',
							'values' => getOptionForItems("LineTypes"),
							'value' => $this->fields['linetypes_id'] ?? ''
						],
						__('Caller name') => [
							'name' => 'caller_name',
							'type' => 'text',
							'value' => $this->fields['caller_name'] ?? ''
						],
						__('Comment') => [
							'name' => 'comment',
							'type' => 'textarea',
							'value' => $this->fields['comment'] ?? ''
						],
                	]
            	]
        	]
		];

		if ($_GET['id'] != '') {
			$delete['Button'] = [
				'type' => 'submit',
				'name' => 'delete',
				'value' => __('Put in trashbin'),
				'class' => 'submit-button btn btn-warning'
			];

			array_push($form['buttons'], $delete['Button']);
		}	

    	renderTwigForm($form);

    	return true;
    }


	function rawSearchOptions() {
    	$tab = parent::rawSearchOptions();

    	$tab = array_merge($tab, Location::rawSearchOptionsToAdd());

    	$tab[] = [
            'id'                 => '2',
            'table'              => $this->getTable(),
            'field'              => 'id',
            'name'               => __('ID'),
            'massiveaction'      => false,
            'datatype'           => 'number'
    	];

    	$tab[] = [
            'id'                 => '4',
            'table'              => 'glpi_linetypes',
            'field'              => 'name',
            'name'               => LineType::getTypeName(1),
            'datatype'           => 'dropdown',
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
            'id'                 => '31',
            'table'              => 'glpi_states',
            'field'              => 'completename',
            'name'               => __('Status'),
            'datatype'           => 'dropdown',
            'condition'          => ['is_visible_line' => 1]
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
            'id'                 => '80',
            'table'              => 'glpi_entities',
            'field'              => 'completename',
            'name'               => Entity::getTypeName(1),
            'massiveaction'      => false,
            'datatype'           => 'dropdown'
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
            'id'                 => '184',
            'table'              => 'glpi_lineoperators',
            'field'              => 'name',
            'name'               => LineOperator::getTypeName(1),
            'massiveaction'      => true,
            'datatype'           => 'dropdown'
      ];

      $tab[] = [
            'id'                 => '185',
            'table'              => $this->getTable(),
            'field'              => 'caller_num',
            'name'               => __('Caller number'),
            'datatype'           => 'string',
            'autocomplete'       => true,
      ];

      $tab[] = [
            'id'                 => '186',
            'table'              => $this->getTable(),
            'field'              => 'caller_name',
            'name'               => __('Caller name'),
            'datatype'           => 'string',
            'autocomplete'       => true,
      ];

      return $tab;
   }


   static function getIcon() {
      return "fas fa-phone";
   }
}
