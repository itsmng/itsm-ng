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
 * SoftwareLicense Class
**/
class SoftwareLicense extends CommonTreeDropdown
{
    use Glpi\Features\Clonable;

    /// TODO move to CommonDBChild ?
    // From CommonDBTM
    public $dohistory                   = true;

    protected static $forward_entity_to = ['Infocom'];

    public static $rightname                   = 'license';
    protected $usenotepad               = true;

    public function title()
    {
        return '';
    }

    public function getCloneRelations(): array
    {
        return [];
    }

    public static function getTypeName($nb = 0)
    {
        return _n('License', 'Licenses', $nb);
    }


    public function pre_updateInDB()
    {

        // Clean end alert if expire is after old one
        if (
            isset($this->oldvalues['expire'])
            && ($this->oldvalues['expire'] < $this->fields['expire'])
        ) {
            $alert = new Alert();
            $alert->clear($this->getType(), $this->fields['id'], Alert::END);
        }
    }


    /**
     * @see CommonDBTM::prepareInputForAdd()
    **/
    public function prepareInputForAdd($input)
    {

        $input = parent::prepareInputForAdd($input);

        if (!isset($this->input['softwares_id']) || !$this->input['softwares_id']) {
            Session::addMessageAfterRedirect(
                __("Please select a software for this license"),
                true,
                ERROR,
                true
            );
            return false;
        }

        if (isset($input["id"]) && ($input["id"] > 0)) {
            $input["_oldID"] = $input["id"];
        }
        unset($input['id']);
        unset($input['withtemplate']);

        // Unset to set to default using mysql default value
        if (empty($input['expire'])) {
            unset($input['expire']);
        }

        return $input;
    }

    /**
     * @since 0.85
     * @see CommonDBTM::prepareInputForUpdate()
    **/
    public function prepareInputForUpdate($input)
    {

        $input = parent::prepareInputForUpdate($input);

        // Update number : compute validity indicator
        if (isset($input['number'])) {
            $input['is_valid'] = self::computeValidityIndicator($input['id'], $input['number']);
        }

        return $input;
    }


    /**
     * Compute licence validity indicator.
     *
     * @param $ID        ID of the licence
     * @param $number    licence count to check (default -1)
     *
     * @since 0.85
     *
     * @return int validity indicator
    **/
    public static function computeValidityIndicator($ID, $number = -1)
    {

        if (
            ($number >= 0)
            && ($number < Item_SoftwareLicense::countForLicense($ID, -1))
        ) {
            return 0;
        }
        // Default return 1
        return 1;
    }


    /**
     * Update validity indicator of a specific license
     * @param $ID ID of the licence
     *
     * @since 0.85
     *
     * @return void
    **/
    public static function updateValidityIndicator($ID)
    {

        $lic = new self();
        if ($lic->getFromDB($ID)) {
            $valid = self::computeValidityIndicator($ID, $lic->fields['number']);
            if ($valid != $lic->fields['is_valid']) {
                $lic->update(['id'       => $ID,
                                   'is_valid' => $valid]);
            }
        }
    }


    /**
     * @since 0.84
    **/
    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
              Certificate_Item::class,
              Item_SoftwareLicense::class,
            ]
        );

        // Alert does not extends CommonDBConnexity
        $alert = new Alert();
        $alert->cleanDBonItemDelete($this->getType(), $this->fields['id']);
    }


    public function post_addItem()
    {
        $itemtype = 'Software';
        $dupid    = $this->fields["softwares_id"];

        if (isset($this->input["_duplicate_license"])) {
            $itemtype = 'SoftwareLicense';
            $dupid    = $this->input["_duplicate_license"];
        }

        // Add infocoms if exists for the licence
        $infocoms = Infocom::getItemsAssociatedTo($this->getType(), $this->fields['id']);
        if (!empty($infocoms)) {
            $override_input['items_id'] = $this->getID();
            $infocoms[0]->clone($override_input);
        }
        Software::updateValidityIndicator($this->fields["softwares_id"]);
    }

    /**
     * @since 0.85
     * @see CommonDBTM::post_updateItem()
    **/
    public function post_updateItem($history = 1)
    {

        if (in_array("is_valid", $this->updates)) {
            Software::updateValidityIndicator($this->fields["softwares_id"]);
        }
    }


    /**
     * @since 0.85
     * @see CommonDBTM::post_deleteFromDB()
    **/
    public function post_deleteFromDB()
    {
        Software::updateValidityIndicator($this->fields["softwares_id"]);
    }


    /**
     * @since 0.84
     *
     * @see CommonDBTM::getPreAdditionalInfosForName
    **/
    public function getPreAdditionalInfosForName()
    {

        $soft = new Software();
        if ($soft->getFromDB($this->fields['softwares_id'])) {
            return $soft->getName();
        }
        return '';
    }

    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addImpactTab($ong, $options);
        $this->addStandardTab('SoftwareLicense', $ong, $options);
        $this->addStandardTab('Item_SoftwareLicense', $ong, $options);
        $this->addStandardTab('Infocom', $ong, $options);
        $this->addStandardTab('Contract_Item', $ong, $options);
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab('KnowbaseItem_Item', $ong, $options);
        $this->addStandardTab('Ticket', $ong, $options);
        $this->addStandardTab('Item_Problem', $ong, $options);
        $this->addStandardTab('Change_Item', $ong, $options);
        $this->addStandardTab('Notepad', $ong, $options);
        $this->addStandardTab('Certificate_Item', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);
        return $ong;
    }


    /**
     * Print the Software / license form
     *
     * @param $ID        integer  Id of the version or the template to print
     * @param $options   array    of possible options:
     *     - target form target
     *     - softwares_id ID of the software for add process
     *
     * @return true if displayed  false if item not found or not right to display
    **/
    public function showForm($ID, $options = [])
    {
        $softwares_id = -1;
        if (isset($options['softwares_id'])) {
            $softwares_id = $options['softwares_id'];
        }

        if ($ID < 0) {
            // Create item
            $this->fields['softwares_id'] = $softwares_id;
            $this->fields['number']       = 1;
            $soft                         = new Software();
            if (
                $soft->getFromDB($softwares_id)
                && in_array($_SESSION['glpiactive_entity'], getAncestorsOf(
                    'glpi_entities',
                    $soft->getEntityID()
                ))
            ) {
                $options['entities_id'] = $soft->getEntityID();
            }
        }

        $form = [
           'action' => $this->getFormURL(),
           'itemtype' => $this::class,
           'content' => [
              __('New item') . ' - ' . self::getTypeName(1) => [
                 'visible' => true,
                 'inputs' => [
                    [
                       'type' => 'hidden',
                       'name' => 'withtemplate',
                       'value' => $options['withtemplate']
                    ],
                    Software::getTypeName(1) => $ID > 0 ? [
                       'content' => "<a href='" . Software::getFormURLWithID($this->fields["softwares_id"]) . "'>" .
                       Dropdown::getDropdownName("glpi_softwares", $this->fields["softwares_id"]) . "</a>"
                    ] : [
                       'type' => 'select',
                       'name' => 'softwares_id',
                       'itemtype' => Software::class,
                       'conditions' => ['is_template' => 0, 'is_deleted' => 0],
                       'value' => $this->fields["softwares_id"],
                       'actions' => getItemActionButtons(['info'], 'Software')
                    ],
                    __('Name') => [
                       'type' => 'text',
                       'name' => 'name',
                       'value' => $this->fields["name"],
                    ],
                    __('Status') => [
                       'type' => 'select',
                       'name' => 'states_id',
                       'itemtype' => State::class,
                       'conditions' => ['is_visible_softwarelicense' => 1],
                       'value' => $this->fields["states_id"],
                    ],
                    __('As child of') => [
                       'type' => 'select',
                       'name' => 'softwarelicenses_id',
                       'itemtype' => SoftwareLicense::class,
                       'conditions' => ['AND' => [
                          'NOT' => ['id' => $ID],
                          'softwares_id' => $this->fields['softwares_id']
                       ]],
                       'actions' => getItemActionButtons(['info', 'add'], 'SoftwareLicense')
                    ],
                    Location::getTypeName(1) => [
                       'type' => 'select',
                       'name' => 'locations_id',
                       'itemtype' => Location::class,
                       'value' => $this->fields["locations_id"],
                       'actions' => getItemActionButtons(['info', 'add'], 'Location')
                    ],
                    _n('Type', 'Types', 1) => [
                       'type' => 'select',
                       'name' => 'softwarelicensetypes_id',
                       'itemtype' => SoftwareLicenseType::class,
                       'value' => $this->fields["softwarelicensetypes_id"],
                       'actions' => getItemActionButtons(['info', 'add'], 'SoftwareLicenseType')
                    ],
                    __('Technician in charge of the license') => [
                       'type' => 'select',
                       'name' => 'users_id_tech',
                       'values' => getOptionsForUsers('own_ticket', ['entities_id' => $this->fields['entities_id']]),
                       'value' => $this->fields["users_id_tech"],
                       'actions' => getItemActionButtons(['info'], 'User')
                    ],
                    __('Publisher') => [
                       'type' => 'select',
                       'name' => 'manufacturers_id',
                       'values' => getOptionForItems('Manufacturer'),
                       'value' => $this->fields["manufacturers_id"],
                       'actions' => getItemActionButtons(['info', 'add'], 'Manufacturer')
                    ],
                    __('Group in charge of the license') => [
                       'type' => 'select',
                       'name' => 'groups_id_tech',
                       'itemtype' => Group::class,
                       'copnditions' => ['is_assign' => 1],
                       'value' => $this->fields["groups_id_tech"],
                       'actions' => getItemActionButtons(['info', 'add'], 'Group')
                    ],
                    __('Serial number') => [
                       'type' => 'text',
                       'name' => 'serial',
                       'value' => $this->fields["serial"],
                    ],
                    User::getTypeName(1) => [
                       'type' => 'select',
                       'name' => 'users_id',
                       'values' => getOptionsForUsers('all', ['entities_id' => $this->fields['entities_id']]),
                       'value' => $this->fields["users_id"],
                       'actions' => getItemActionButtons(['info'], 'User')
                    ],
                    __('Inventory number') => [
                       'type' => 'text',
                       'name' => 'otherserial',
                       'value' => $this->fields["otherserial"],
                    ],
                    Group::getTypeName(1) => [
                       'type' => 'select',
                       'name' => 'groups_id',
                       'itemtype' => Group::class,
                       'conditions' => ['is_itemgroup' => 1],
                       'value' => $this->fields["groups_id"],
                       'actions' => getItemActionButtons(['info', 'add'], 'Group')
                    ],
                    __('Comments') => [
                       'type' => 'textarea',
                       'name' => 'comment',
                       'value' => $this->fields["comment"],
                    ],
                    __('Version in use') => [
                       'type' => 'select',
                       'name' => 'softwareversions_id_use',
                       'itemtype' => SoftwareVersion::class,
                       'conditions' => ['softwares_id' => $this->fields['softwares_id']],
                       'value' => $this->fields["softwareversions_id_use"],
                    ],
                    __('Purchase version') => [
                       'type' => 'select',
                       'name' => 'softwareversions_id_buy',
                       'itemtype' => SoftwareVersion::class,
                       'conditions' => ['softwares_id' => $this->fields['softwares_id']],
                       'value' => $this->fields["softwareversions_id_buy"],
                    ],
                    _x('quantity', 'Number') . ' (0 = ' . __('Unlimited') . ')' => [
                       'type' => 'number',
                       'name' => 'number',
                       'value' => $this->fields["number"],
                       'min' => 0,
                       'max' => 10000,
                       'step' => 1,
                    ],
                    __('Quota Status') => $ID > 0 ?
                       ($this->fields['is_valid'] ? [
                          'content' => "<span class='green'>" . _x('adjective', 'Valid') . '<span>'
                       ]
                       : ((!$this->fields['is_valid'] && $this->fields['allow_overquota']) ? [
                          'content' => "<span class='green'>" . _x('adjective', 'Valid (Over Quota)') . '<span>'
                       ] : [
                          'content' => "<span class='red'>" . _x('adjective', 'Invalid') . '<span>'
                       ]))
                    : [],
                    __('Allow Over-Quota') => [
                       'type' => 'checkbox',
                       'name' => 'allow_overquota',
                       'value' => $this->fields["allow_overquota"],
                    ],
                    __('Expiration') => [
                       'type' => 'date',
                       'name' => 'expire',
                       'value' => $this->fields["expire"],
                       'after' => $ID && is_null($this->fields["expire"]) ?
                          __('Never expire') : ''
                    ],
                    $ID > 0 ? [
                       'type' => 'hidden',
                       'name' => 'id',
                       'value' => $ID
                    ] : [],
                 ]
              ]
           ]
        ];
        renderTwigForm($form, '', $this->fields);

        return true;
    }

    /**
     * Is the license may be recursive
     *
     * @return boolean
    **/
    public function maybeRecursive()
    {

        $soft = new Software();
        if (
            isset($this->fields["softwares_id"])
            && $soft->getFromDB($this->fields["softwares_id"])
        ) {
            return $soft->isRecursive();
        }

        return true;
    }


    public function rawSearchOptions()
    {
        $tab = [];

        // Only use for History (not by search Engine)
        $tab[] = [
           'id'                 => 'common',
           'name'               => __('Characteristics')
        ];

        $tab[] = [
           'id'                 => '1',
           'table'              => $this->getTable(),
           'field'              => 'name',
           'name'               => __('Name'),
           'datatype'           => 'itemlink',
           'massiveaction'      => false,
           'forcegroupby'       => true,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'massiveaction'      => false,
           'datatype'           => 'number',
           'forcegroupby'       => true
        ];

        $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

        $tab[] = [
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'serial',
           'name'               => __('Serial number'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '4',
           'table'              => $this->getTable(),
           'field'              => 'number',
           'name'               => __('Number'),
           'datatype'           => 'number',
           'max'                => 100,
           'toadd'              => [
              '-1'                 => 'Unlimited'
           ]
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => 'glpi_softwarelicensetypes',
           'field'              => 'name',
           'name'               => _n('Type', 'Types', 1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '6',
           'table'              => 'glpi_softwareversions',
           'field'              => 'name',
           'linkfield'          => 'softwareversions_id_buy',
           'name'               => __('Purchase version'),
           'datatype'           => 'dropdown',
           'displaywith'        => [
              '0'                  => __('states_id')
           ]
        ];

        $tab[] = [
           'id'                 => '7',
           'table'              => 'glpi_softwareversions',
           'field'              => 'name',
           'linkfield'          => 'softwareversions_id_use',
           'name'               => __('Version in use'),
           'datatype'           => 'dropdown',
           'displaywith'        => [
              '0'                  => __('states_id')
           ]
        ];

        $tab[] = [
           'id'                 => '8',
           'table'              => $this->getTable(),
           'field'              => 'expire',
           'name'               => __('Expiration'),
           'datatype'           => 'date'
        ];

        $tab[] = [
           'id'                 => '9',
           'table'              => $this->getTable(),
           'field'              => 'is_valid',
           'name'               => __('Valid'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '10',
           'table'              => 'glpi_softwares',
           'field'              => 'name',
           'name'               => Software::getTypeName(1),
           'datatype'           => 'itemlink'
        ];

        $tab[] = [
           'id'                 => '168',
           'table'              => $this->getTable(),
           'field'              => 'allow_overquota',
           'name'               => __('Allow Over-Quota'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '13',
           'table'              => $this->getTable(),
           'field'              => 'completename',
           'name'               => __('Father'),
           'datatype'           => 'itemlink',
           'forcegroupby'       => true,
           'joinparams'        => ['condition' => "AND 1=1"]
        ];

        $tab[] = [
           'id'                 => '16',
           'table'              => $this->getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '24',
           'table'              => 'glpi_users',
           'field'              => 'name',
           'linkfield'          => 'users_id_tech',
           'name'               => __('Technician in charge of the license'),
           'datatype'           => 'dropdown',
           'right'              => 'own_ticket'
        ];

        $tab[] = [
           'id'                 => '31',
           'table'              => 'glpi_states',
           'field'              => 'completename',
           'name'               => __('Status'),
           'datatype'           => 'dropdown',
           'condition'          => ['is_visible_softwarelicense' => 1]
        ];

        $tab[] = [
           'id'                 => '49',
           'table'              => 'glpi_groups',
           'field'              => 'completename',
           'linkfield'          => 'groups_id_tech',
           'name'               => __('Group in charge of the license'),
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
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '86',
           'table'              => $this->getTable(),
           'field'              => 'is_recursive',
           'name'               => __('Child entities'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '162',
           'table'              => $this->getTable(),
           'field'              => 'otherserial',
           'name'               => __('Inventory number'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        // add objectlock search options
        $tab = array_merge($tab, ObjectLock::rawSearchOptionsToAdd(get_class($this)));
        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        return $tab;
    }


    public static function rawSearchOptionsToAdd()
    {
        $tab = [];
        $name = static::getTypeName(Session::getPluralNumber());

        if (!self::canView()) {
            return $tab;
        }

        $licjoinexpire = ['jointype'  => 'child',
                                'condition' => getEntitiesRestrictRequest(
                                    ' AND',
                                    "NEWTABLE",
                                    '',
                                    '',
                                    true
                                ) .
                                               " AND NEWTABLE.`is_template` = 0
                                               AND (NEWTABLE.`expire` IS NULL
                                                   OR NEWTABLE.`expire` > NOW())"];

        $tab[] = [
           'id'                 => 'license',
           'name'               => $name
        ];

        $tab[] = [
           'id'                 => '160',
           'table'              => 'glpi_softwarelicenses',
           'field'              => 'name',
           'name'               => __('Name'),
           'datatype'           => 'dropdown',
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => $licjoinexpire
        ];

        $tab[] = [
           'id'                 => '161',
           'table'              => 'glpi_softwarelicenses',
           'field'              => 'serial',
           'datatype'           => 'string',
           'name'               => __('Serial number'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => $licjoinexpire
        ];

        $tab[] = [
           'id'                 => '162',
           'table'              => 'glpi_softwarelicenses',
           'field'              => 'otherserial',
           'datatype'           => 'string',
           'name'               => __('Inventory number'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => $licjoinexpire
        ];

        $tab[] = [
           'id'                 => '163',
           'table'              => 'glpi_softwarelicenses',
           'field'              => 'number',
           'name'               => __('Number of licenses'),
           'forcegroupby'       => true,
           'usehaving'          => true,
           'datatype'           => 'number',
           'massiveaction'      => false,
           'joinparams'         => $licjoinexpire
        ];

        $tab[] = [
           'id'                 => '164',
           'table'              => 'glpi_softwarelicensetypes',
           'field'              => 'name',
           'datatype'           => 'dropdown',
           'name'               => _n('Type', 'Types', 1),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_softwarelicenses',
                 'joinparams'         => $licjoinexpire
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '165',
           'table'              => 'glpi_softwarelicenses',
           'field'              => 'comment',
           'name'               => __('Comments'),
           'forcegroupby'       => true,
           'datatype'           => 'text',
           'massiveaction'      => false,
           'joinparams'         => $licjoinexpire
        ];

        $tab[] = [
           'id'                 => '166',
           'table'              => 'glpi_softwarelicenses',
           'field'              => 'expire',
           'name'               => __('Expiration'),
           'forcegroupby'       => true,
           'datatype'           => 'date',
           'emptylabel'         => __('Never expire'),
           'massiveaction'      => false,
           'joinparams'         => $licjoinexpire
        ];

        $tab[] = [
           'id'                 => '167',
           'table'              => 'glpi_softwarelicenses',
           'field'              => 'is_valid',
           'name'               => __('Valid'),
           'forcegroupby'       => true,
           'datatype'           => 'bool',
           'massiveaction'      => false,
           'joinparams'         => $licjoinexpire
        ];

        return $tab;
    }


    /**
     * Give cron information
     *
     * @param $name : task's name
     *
     * @return array of information
    **/
    public static function cronInfo($name)
    {
        return ['description' => __('Send alarms on expired licenses')];
    }


    /**
     * Cron action on softwares : alert on expired licences
     *
     * @param $task to log, if NULL display (default NULL)
     *
     * @return 0 : nothing to do 1 : done with success
    **/
    public static function cronSoftware($task = null)
    {
        global $DB, $CFG_GLPI;

        $cron_status = 1;

        if (!$CFG_GLPI['use_notifications']) {
            return 0;
        }

        $message      = [];
        $items_notice = [];
        $items_end    = [];

        $tonotify = Entity::getEntitiesToNotify('use_licenses_alert');
        foreach (array_keys($tonotify) as $entity) {
            $before = Entity::getUsedConfig('send_licenses_alert_before_delay', $entity);
            // Check licenses
            $criteria = [
               'SELECT' => [
                  'glpi_softwarelicenses.*',
                  'glpi_softwares.name AS softname'
               ],
               'FROM'   => 'glpi_softwarelicenses',
               'INNER JOIN'   => [
                  'glpi_softwares'  => [
                     'ON'  => [
                        'glpi_softwarelicenses' => 'softwares_id',
                        'glpi_softwares'        => 'id'
                     ]
                  ]
               ],
               'LEFT JOIN'    => [
                  'glpi_alerts'  => [
                     'ON'  => [
                        'glpi_softwarelicenses' => 'id',
                        'glpi_alerts'           => 'items_id', [
                           'AND' => [
                              'glpi_alerts.itemtype'  => 'SoftwareLicense'
                           ]
                        ]
                     ]
                  ]
               ],
               'WHERE'        => [
                  'glpi_alerts.date'   => null,
                  'NOT'                => ['glpi_softwarelicenses.expire' => null],
                  new QueryExpression('DATEDIFF(' . $DB->quoteName('glpi_softwarelicenses.expire') . ', CURDATE()) < ' . $before),
                  'glpi_softwares.is_template'  => 0,
                  'glpi_softwares.is_deleted'   => 0,
                  'glpi_softwares.entities_id'  => $entity
               ]
            ];
            $iterator = $DB->request($criteria);

            $message = "";
            $items   = [];

            while ($license = $iterator->next()) {
                $name     = $license['softname'] . ' - ' . $license['name'] . ' - ' . $license['serial'];
                //TRANS: %1$s the license name, %2$s is the expiration date
                $message .= sprintf(
                    __('License %1$s expired on %2$s'),
                    Html::convDate($license["expire"]),
                    $name
                ) . "<br>\n";
                $items[$license['id']] = $license;
            }

            if (!empty($items)) {
                $alert                  = new Alert();
                $options['entities_id'] = $entity;
                $options['licenses']    = $items;

                if (NotificationEvent::raiseEvent('alert', new self(), $options)) {
                    $entityname = Dropdown::getDropdownName("glpi_entities", $entity);
                    if ($task) {
                        //TRANS: %1$s is the entity, %2$s is the message
                        $task->log(sprintf(__('%1$s: %2$s') . "\n", $entityname, $message));
                        $task->addVolume(1);
                    } else {
                        Session::addMessageAfterRedirect(sprintf(
                            __('%1$s: %2$s'),
                            $entityname,
                            $message
                        ));
                    }

                    $input["type"]     = Alert::END;
                    $input["itemtype"] = 'SoftwareLicense';

                    // add alerts
                    foreach ($items as $ID => $consumable) {
                        $input["items_id"] = $ID;
                        $alert->add($input);
                        unset($alert->fields['id']);
                    }
                } else {
                    $entityname = Dropdown::getDropdownName('glpi_entities', $entity);
                    //TRANS: %s is entity name
                    $msg = sprintf(__('%1$s: %2$s'), $entityname, __('Send licenses alert failed'));
                    if ($task) {
                        $task->log($msg);
                    } else {
                        Session::addMessageAfterRedirect($msg, false, ERROR);
                    }
                }
            }
        }
        return $cron_status;
    }


    /**
     * Get number of bought licenses of a version
     *
     * @param $softwareversions_id   version ID
     * @param $entity                to search for licenses in (default = all active entities)
     *                               (default '')
     *
     * @return number of installations
    */
    public static function countForVersion($softwareversions_id, $entity = '')
    {
        global $DB;

        $result = $DB->request([
           'COUNT'  => 'cpt',
           'FROM'   => 'glpi_softwarelicenses',
           'WHERE'  => [
              'softwareversions_id_buy'  => $softwareversions_id
           ] + getEntitiesRestrictCriteria('glpi_softwarelicenses', '', $entity)
        ])->next();

        return $result['cpt'];
    }


    /**
     * Get number of licensesof a software
     *
     * @param $softwares_id software ID
     *
     * @return number of licenses
    **/
    public static function countForSoftware($softwares_id)
    {
        global $DB;

        $iterator = $DB->request([
           'COUNT'  => 'cpt',
           'FROM'   => 'glpi_softwarelicenses',
           'WHERE'  => [
              'softwares_id' => $softwares_id,
              'is_template'  => 0,
              'number'       => -1
           ] + getEntitiesRestrictCriteria('glpi_softwarelicenses', '', '', true)
        ]);

        if ($line = $iterator->next()) {
            if ($line['cpt'] > 0) {
                // At least 1 unlimited license, means unlimited
                return -1;
            }
        }

        $result = $DB->request([
           'SELECT' => ['SUM' => 'number AS numsum'],
           'FROM'   => 'glpi_softwarelicenses',
           'WHERE'  => [
              'softwares_id' => $softwares_id,
              'is_template'  => 0,
              'number'       => ['>', 0]
           ] + getEntitiesRestrictCriteria('glpi_softwarelicenses', '', '', true)
        ])->next();
        return ($result['numsum'] ? $result['numsum'] : 0);
    }


    public function getSpecificMassiveActions($checkitem = null)
    {

        $actions = parent::getSpecificMassiveActions($checkitem);
        if (static::canUpdate()) {
            $prefix                       = 'Item_SoftwareLicense' . MassiveAction::CLASS_ACTION_SEPARATOR;
            $actions[$prefix . 'add_item']  = _x('button', 'Add an item');
        }

        return $actions;
    }


    /**
     * Show Licenses of a software
     *
     * @param $software Software object
     *
     * @return void
    **/
    public static function showForSoftware(Software $software)
    {
        global $DB;

        $softwares_id  = $software->getField('id');
        $license       = new self();

        if (!$software->can($softwares_id, READ)) {
            return false;
        }

        $columns = ['name'      => __('Name'),
                    'entity'    => Entity::getTypeName(1),
                    'serial'    => __('Serial number'),
                    'number'    => _x('quantity', 'Number'),
                    '_affected' => __('Affected items'),
                    'typename'  => _n('Type', 'Types', 1),
                    'buyname'   => __('Purchase version'),
                    'usename'   => __('Version in use'),
                    'expire'    => __('Expiration'),
                    'statename' => __('Status')];
        if (!$software->isRecursive()) {
            unset($columns['entity']);
        }

        if (isset($_GET["start"])) {
            $start = $_GET["start"];
        } else {
            $start = 0;
        }

        if (isset($_GET["order"]) && ($_GET["order"] == "DESC")) {
            $order = "DESC";
        } else {
            $order = "ASC";
        }

        if (isset($_GET["sort"]) && !empty($_GET["sort"]) && isset($columns[$_GET["sort"]])) {
            $sort = $_GET["sort"];
        } else {
            $sort = ["entity $order", "name $order"];
        }

        // Righ type is enough. Can add a License on a software we have Read access
        $canedit             = Software::canUpdate();
        $showmassiveactions  = $canedit;

        // Total Number of events
        $number = countElementsInTable(
            "glpi_softwarelicenses",
            [
              'glpi_softwarelicenses.softwares_id' => $softwares_id,
              'glpi_softwarelicenses.is_template'  => 0,
            ] + getEntitiesRestrictCriteria('glpi_softwarelicenses', '', '', true)
        );
        echo "<div class='spaced'>";

        Session::initNavigateListItems(
            'SoftwareLicense',
            //TRANS : %1$s is the itemtype name, %2$s is the name of the item (used for headings of a list)
            sprintf(
                __('%1$s = %2$s'),
                Software::getTypeName(1),
                $software->getName()
            )
        );

        if ($canedit) {
            echo "<div class='center firstbloc'>";
            echo "<a class='btn btn-secondary' href='" . SoftwareLicense::getFormURL() . "?softwares_id=$softwares_id'>" .
                   _x('button', 'Add a license') . "</a>";
            echo "</div>";
        }

        $rand  = mt_rand();
        $iterator = $DB->request([
           'SELECT'    => [
              'glpi_softwarelicenses.*',
              'buyvers.name AS buyname',
              'usevers.name AS usename',
              'glpi_entities.completename AS entity',
              'glpi_softwarelicensetypes.name AS typename',
              'glpi_states.name AS statename'
           ],
           'FROM'      => 'glpi_softwarelicenses',
           'LEFT JOIN' => [
              'glpi_softwareversions AS buyvers'  => [
                 'ON' => [
                    'glpi_softwarelicenses' => 'softwareversions_id_buy',
                    'buyvers'               => 'id'
                 ]
              ],
              'glpi_softwareversions AS usevers'  => [
                 'ON' => [
                    'glpi_softwarelicenses' => 'softwareversions_id_use',
                    'usevers'               => 'id'
                 ]
              ],
              'glpi_entities'                     => [
                 'ON' => [
                    'glpi_entities'         => 'id',
                    'glpi_softwarelicenses' => 'entities_id'
                 ]
              ],
              'glpi_softwarelicensetypes'         => [
                 'ON' => [
                    'glpi_softwarelicensetypes'   => 'id',
                    'glpi_softwarelicenses'       => 'softwarelicensetypes_id'
                 ]
              ],
              'glpi_states'                       => [
                 'ON' => [
                    'glpi_softwarelicenses' => 'states_id',
                    'glpi_states'           => 'id'
                 ]
              ]
           ],
           'WHERE'     => [
              'glpi_softwarelicenses.softwares_id'   => $softwares_id,
              'glpi_softwarelicenses.is_template'    => 0
           ] + getEntitiesRestrictCriteria('glpi_softwarelicenses', '', '', true),
           'ORDERBY'   => $sort,
           'START'     => (int)$start,
           'LIMIT'     => (int)$_SESSION['glpilist_limit']
        ]);
        $num_displayed = count($iterator);

        if ($num_displayed) {
            // Display the pager
            Html::printAjaxPager(self::getTypeName(Session::getPluralNumber()), $start, $number);
            if ($showmassiveactions) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams
                   = ['num_displayed'
                            => min($_SESSION['glpilist_limit'], $num_displayed),
                            'container'
                            => 'mass' . __CLASS__ . $rand,
                            'extraparams'
                            => ['options'
                                        => ['glpi_softwareversions.name'
                                                 => ['condition'
                                                          => $DB->quoteName("glpi_softwareversions.softwares_id") . "
                                                               = $softwares_id"],
                                                 'glpi_softwarelicenses.name'
                                                 => ['itemlink_as_string' => true]]]];

                Html::showMassiveActions($massiveactionparams);
            }

            echo "<table class='tab_cadre_fixehov' aria-label='List of Items'>";

            $header_begin  = "<tr><th>";
            $header_top    = Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            $header_end    = '';

            foreach ($columns as $key => $val) {
                // Non order column
                if ($key[0] == '_') {
                    $header_end .= "<th>$val</th>";
                } else {
                    $header_end .= "<th" . (!is_array($sort) && $sort == "$key" ? " class='order_$order'" : '') . ">" .
                          "<a href='javascript:reloadTab(\"sort=$key&amp;order=" .
                             (($order == "ASC") ? "DESC" : "ASC") . "&amp;start=0\");'>$val</a></th>";
                }
            }

            $header_end .= "</tr>\n";
            echo $header_begin . $header_top . $header_end;

            $tot_assoc = 0;
            $tot       = 0;
            while ($data = $iterator->next()) {
                Session::addToNavigateListItems('SoftwareLicense', $data['id']);
                $expired = true;
                if (
                    is_null($data['expire'])
                    || ($data['expire'] > date('Y-m-d'))
                ) {
                    $expired = false;
                }
                echo "<tr class='tab_bg_2" . ($expired ? '_2' : '') . "'>";

                if ($license->canEdit($data['id'])) {
                    echo "<td>" . Html::getMassiveActionCheckBox(__CLASS__, $data["id"]) . "</td>";
                } else {
                    echo "<td>&nbsp;</td>";
                }

                echo "<td>";
                echo $license->getLink(['complete' => true, 'comments' => true]);
                echo "</td>";

                if (isset($columns['entity'])) {
                    echo "<td>";
                    echo $data['entity'];
                    echo "</td>";
                }
                echo "<td>" . $data['serial'] . "</td>";
                echo "<td class='numeric'>" .
                         (($data['number'] > 0) ? $data['number'] : __('Unlimited')) . "</td>";
                $nb_assoc   = Item_SoftwareLicense::countForLicense($data['id']);
                $tot_assoc += $nb_assoc;
                $color = ($data['is_valid'] ? 'green' : 'red');

                echo "<td class='numeric $color'>" . $nb_assoc . "</td>";
                echo "<td>" . $data['typename'] . "</td>";
                echo "<td>" . $data['buyname'] . "</td>";
                echo "<td>" . $data['usename'] . "</td>";
                echo "<td class='center'>" . Html::convDate($data['expire']) . "</td>";
                echo "<td>" . $data['statename'] . "</td>";
                echo "</tr>";

                if ($data['number'] < 0) {
                    // One illimited license, total is illimited
                    $tot = -1;
                } elseif ($tot >= 0) {
                    // Expire license not count
                    if (!$expired) {
                        // Not illimited, add the current number
                        $tot += $data['number'];
                    }
                }
            }
            echo "<tr class='tab_bg_1 noHover'>";
            echo "<td colspan='" .
                     ($software->isRecursive() ? 4 : 3) . "' class='right b'>" . __('Total') . "</td>";
            echo "<td class='numeric'>" . (($tot > 0) ? $tot . "" : __('Unlimited')) .
                  "</td>";
            $color = ($software->fields['is_valid'] ? 'green' : 'red');
            echo "<td class='numeric $color'>" . $tot_assoc . "</td><td></td><td></td><td></td><td></td><td></td>";
            echo "</tr>";
            echo "</table>\n";

            if ($showmassiveactions) {
                $massiveactionparams['ontop'] = false;
                Html::showMassiveActions($massiveactionparams);

                Html::closeForm();
            }
            Html::printAjaxPager(self::getTypeName(Session::getPluralNumber()), $start, $number);
        } else {
            echo "<table class='tab_cadre_fixe' aria-label='No item found'><tr><th>" . __('No item found') . "</th></tr></table>";
        }

        echo "</div>";
    }


    /**
     * Display debug information for current object
    **/
    public function showDebug()
    {

        $license = ['softname' => '',
                         'name'     => '',
                         'serial'   => '',
                         'expire'   => ''];

        $options['entities_id'] = $this->getEntityID();
        $options['licenses']    = [$license];
        NotificationEvent::debugEvent($this, $options);
    }


    /**
     * Get fields to display in the unicity error message
     *
     * @return an array which contains field => label
    */
    public function getUnicityFieldsToDisplayInErrorMessage()
    {

        return ['id'           => __('ID'),
                     'serial'       => __('Serial number'),
                     'entities_id'  => Entity::getTypeName(1),
                     'softwares_id' => _n('Software', 'Software', 1)];
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            $nb = 0;
            switch ($item->getType()) {
                case 'Software':
                    if (!self::canView()) {
                        return '';
                    }
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = self::countForSoftware($item->getID());
                    }
                    return self::createTabEntry(
                        self::getTypeName(Session::getPluralNumber()),
                        (($nb >= 0) ? $nb : '&infin;')
                    );
                    break;
                case 'SoftwareLicense':
                    if (!self::canView()) {
                        return '';
                    }
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable(
                            $this->getTable(),
                            ['softwarelicenses_id' => $item->getID()]
                        );
                    }
                    return self::createTabEntry(
                        self::getTypeName(Session::getPluralNumber()),
                        (($nb >= 0) ? $nb : '&infin;')
                    );
                    break;
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == 'Software' && self::canView()) {
            self::showForSoftware($item);
        } else {
            if ($item->getType() == 'SoftwareLicense' && self::canView()) {
                self::getSonsOf($item);
                return true;
            }
        }
        return true;
    }


    public static function getSonsOf($item)
    {
        global $DB;
        $entity_assign = $item->isEntityAssign();
        $nb            = 0;
        $ID            = $item->getID();

        echo "<div class='spaced'>";
        echo "<table class='tab_cadre_fixehov' aria-label='Sons of items'>";
        echo "<tr class='noHover'><th colspan='" . ($nb + 3) . "'>" . sprintf(
            __('Sons of %s'),
            $item->getTreeLink()
        );
        echo "</th></tr>";

        $header = "<tr><th>" . __('Name') . "</th>";
        if ($entity_assign) {
            $header .= "<th>" . Entity::getTypeName(1) . "</th>";
        }

        $header .= "<th>" . __('Comments') . "</th>";
        $header .= "</tr>\n";
        echo $header;

        $fk   = $item->getForeignKeyField();
        $crit = [$fk     => $ID,
                      'ORDER' => 'name'];

        if ($entity_assign) {
            if ($fk == 'entities_id') {
                $crit['id']  = $_SESSION['glpiactiveentities'];
                $crit['id'] += $_SESSION['glpiparententities'];
            } else {
                foreach ($_SESSION['glpiactiveentities'] as $key => $value) {
                    $crit['entities_id'][$key] = (string)$value;
                }
            }
        }
        $nb = 0;

        foreach ($DB->request($item->getTable(), $crit) as $data) {
            $nb++;
            echo "<tr class='tab_bg_1'>";
            echo "<td><a href='" . $item->getFormURL();
            echo '?id=' . $data['id'] . "'>" . $data['name'] . "</a></td>";
            if ($entity_assign) {
                echo "<td>" . Dropdown::getDropdownName("glpi_entities", $data["entities_id"]) . "</td>";
            }

            echo "<td>" . $data['comment'] . "</td>";
            echo "</tr>\n";
        }
        if ($nb) {
            echo $header;
        }
        echo "</table></div>\n";
    }

    public static function getIcon()
    {
        return "fas fa-key";
    }
}
