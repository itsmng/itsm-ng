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

use Glpi\Toolbox\URL;

/**
 * Supplier class (suppliers)
 **/
class Supplier extends CommonDBTM
{
    // From CommonDBTM
    public $dohistory           = true;

    public static $rightname           = 'contact_enterprise';
    protected $usenotepad       = true;



    /**
     * Name of the type
     *
     * @param $nb : number of item in the type
     **/
    public static function getTypeName($nb = 0)
    {
        return _n('Supplier', 'Suppliers', $nb);
    }


    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
              Change_Supplier::class,
              Contact_Supplier::class,
              Contract_Supplier::class,
              Problem_Supplier::class,
              ProjectTaskTeam::class,
              ProjectTeam::class,
              Supplier_Ticket::class,
            ]
        );

        // Ticket rules use suppliers_id_assign
        Rule::cleanForItemAction($this, 'suppliers_id%');
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Contact_Supplier', $ong, $options);
        $this->addStandardTab('Contract_Supplier', $ong, $options);
        $this->addStandardTab('Infocom', $ong, $options);
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab('Ticket', $ong, $options);
        $this->addStandardTab('Item_Problem', $ong, $options);
        $this->addStandardTab('Change_Item', $ong, $options);
        $this->addStandardTab('Link', $ong, $options);
        $this->addStandardTab('Notepad', $ong, $options);
        $this->addStandardTab('KnowbaseItem_Item', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    /**
     * Print the enterprise form
     *
     * @param $ID Integer : Id of the computer or the template to print
     * @param $options array
     *     - target form target
     *     - withtemplate boolean : template or basic item
     *
     *@return void
     **/
    public function showForm($ID)
    {

        $form = [
           'action' => Toolbox::getItemTypeFormURL('supplier'),
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
              __('Supplier') => [
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
                       'value' => $this->fields['name'],
                    ],
                    __('Phone') => [
                       'name' => 'phonenumber',
                       'type' => 'text',
                       'value' => $this->fields['phonenumber'],
                    ],
                    __('Fax') => [
                       'name' => 'fax',
                       'type' => 'text',
                       'value' => $this->fields['fax'],
                    ],
                    __('Website') => [
                       'name' => 'website',
                       'type' => 'text',
                       'value' => $this->fields['website'],
                    ],
                    __('Email') => [
                       'name' => 'email',
                       'type' => 'text',
                       'value' => $this->fields['email'],
                    ],
                    __('Address') => [
                       'name' => 'address',
                       'type' => 'textarea',
                       'value' => $this->fields['address'],
                    ],
                    __('Postal code') => [
                       'name' => 'postcode',
                       'type' => 'text',
                       'value' => $this->fields['postcode'],
                    ],
                    __('Town') => [
                       'name' => 'town',
                       'type' => 'text',
                       'value' => $this->fields['town']
                    ],
                    __('Country') => [
                       'name' => 'country',
                       'type' => 'text',
                       'value' => $this->fields['country']
                    ],
                    __('Third party type') => [
                       'name' => 'suppliertypes_id',
                       'type' => 'select',
                       ...getAjaxDropdownOptions(SupplierType::class),
                       'actions' => getItemActionButtons(['info', 'add'], "SupplierType"),
                       'value' => $this->fields['suppliertypes_id']
                    ],
                    __('Comment') => [
                       'name' => 'comment',
                       'type' => 'textarea',
                       'value' => $this->fields['comment']
                    ],
                    __('Active') => [
                       'name' => 'is_active',
                       'type' => 'checkbox',
                       'value' => $this->fields['is_active']
                    ]
                 ]
              ]
           ]
        ];
        renderTwigForm($form);

        return true;
    }

    public static function dropdown($options = [])
    {
        $condition = ['is_active' => true];
        $options['condition'] = (isset($options['condition']) ? $options['condition'] + $condition : $condition);
        return Dropdown::show(get_called_class(), $options);
    }

    /**
     * @see CommonDBTM::getSpecificMassiveActions()
     **/
    public function getSpecificMassiveActions($checkitem = null)
    {

        $isadmin = static::canUpdate();
        $actions = parent::getSpecificMassiveActions($checkitem);
        if ($isadmin) {
            $actions['Contact_Supplier' . MassiveAction::CLASS_ACTION_SEPARATOR . 'add']
               = _x('button', 'Add a contact');
        }
        return $actions;
    }

    public function rawSearchOptions()
    {
        global $DB;

        $tab = [];

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
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'massiveaction'      => false,
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '3',
           'table'              => $this->getTable(),
           'field'              => 'address',
           'name'               => __('Address'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '10',
           'table'              => $this->getTable(),
           'field'              => 'fax',
           'name'               => __('Fax'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'town',
           'name'               => __('City'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '14',
           'table'              => $this->getTable(),
           'field'              => 'postcode',
           'name'               => __('Postal code'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '12',
           'table'              => $this->getTable(),
           'field'              => 'state',
           'name'               => _x('location', 'State'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '13',
           'table'              => $this->getTable(),
           'field'              => 'country',
           'name'               => __('Country'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '4',
           'table'              => $this->getTable(),
           'field'              => 'website',
           'name'               => __('Website'),
           'datatype'           => 'weblink',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => $this->getTable(),
           'field'              => 'phonenumber',
           'name'               => Phone::getTypeName(1),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '6',
           'table'              => $this->getTable(),
           'field'              => 'email',
           'name'               => _n('Email', 'Emails', 1),
           'datatype'           => 'email',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '9',
           'table'              => 'glpi_suppliertypes',
           'field'              => 'name',
           'name'               => SupplierType::getTypeName(1),
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

        if ($_SESSION["glpinames_format"] == User::FIRSTNAME_BEFORE) {
            $name1 = 'firstname';
            $name2 = 'name';
        } else {
            $name1 = 'name';
            $name2 = 'firstname';
        }

        $tab[] = [
           'id'                 => '8',
           'table'              => 'glpi_contacts',
           'field'              => 'completename',
           'name'               => _n('Associated contact', 'Associated contacts', Session::getPluralNumber()),
           'forcegroupby'       => true,
           'datatype'           => 'itemlink',
           'massiveaction'      => false,
           'computation'        => "CONCAT(" . $DB->quoteName("TABLE.$name1") . ", ' ', " . $DB->quoteName("TABLE.$name2") . ")",
           'computationgroupby' => true,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_contacts_suppliers',
                 'joinparams'         => [
                    'jointype'           => 'child'
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '16',
           'table'              => $this->getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text'
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
           'id'                 => '86',
           'table'              => $this->getTable(),
           'field'              => 'is_recursive',
           'name'               => __('Child entities'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '29',
           'table'              => 'glpi_contracts',
           'field'              => 'name',
           'name'               => _n('Associated contract', 'Associated contracts', Session::getPluralNumber()),
           'forcegroupby'       => true,
           'datatype'           => 'itemlink',
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_contracts_suppliers',
                 'joinparams'         => [
                    'jointype'           => 'child'
                 ]
              ]
           ]
        ];

        // add objectlock search options
        $tab = array_merge($tab, ObjectLock::rawSearchOptionsToAdd(get_class($this)));

        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        return $tab;
    }


    /**
     * Get links for an enterprise (website / edit)
     *
     * @param $withname boolean : also display name ? (false by default)
     **/
    public function getLinks($withname = false)
    {
        global $CFG_GLPI;

        $ret = '&nbsp;&nbsp;&nbsp;&nbsp;';

        if ($withname) {
            $ret .= htmlspecialchars((string) $this->fields["name"], ENT_QUOTES, 'UTF-8');
            $ret .= "&nbsp;&nbsp;";
        }

        if (!empty($this->fields['website'])) {
            $website_url = URL::sanitizeURL(Toolbox::formatOutputWebLink($this->fields['website']));
            if ($website_url !== '') {
                $ret .= "<a href='" . htmlspecialchars($website_url, ENT_QUOTES, 'UTF-8') . "' target='_blank'>
                      <img src='" . $CFG_GLPI["root_doc"] . "/pics/web.png' class='middle' alt=\"" .
                   __s('Web') . "\" title=\"" . __s('Web') . "\"></a>&nbsp;&nbsp;";
            }
        }

        if ($this->can($this->fields['id'], READ)) {
            $ret .= "<a href='" . Supplier::getFormURLWithID($this->fields['id']) . "'>
                  <img src='" . $CFG_GLPI["root_doc"] . "/pics/edit.png' class='middle' alt=\"" .
               __s('Update') . "\" title=\"" . __s('Update') . "\"></a>";
        }
        return $ret;
    }


    /**
     * Print the HTML array for infocoms linked
     *
     *@return void
     *
     **/
    public function showInfocoms()
    {

        global $DB;

        $instID = $this->fields['id'];
        if (!$this->can($instID, READ)) {
            return false;
        }

        $types_iterator = Infocom::getTypes(['suppliers_id' => $instID]);
        $number = count($types_iterator);

        $num = 0;
        $values = [];
        while ($row = $types_iterator->next()) {
            $itemtype = $row['itemtype'];

            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }

            if ($item->canView()) {
                $linktype  = $itemtype;
                $linkfield = 'id';
                $itemtable = getTableForItemType($itemtype);

                $criteria = [
                   'SELECT'       => [],
                   'FROM'         => 'glpi_infocoms',
                   'INNER JOIN'   => [
                      $itemtable  => [
                         'ON' => [
                            'glpi_infocoms'   => 'items_id',
                            $itemtable        => 'id'
                         ]
                      ]
                   ]
                ];

                // Set $linktype for entity restriction AND link to search engine
                if ($itemtype == 'Cartridge') {
                    $criteria['INNER JOIN']['glpi_cartridgeitems'] = [
                       'ON' => [
                          'glpi_cartridgeitems'   => 'id',
                          'glpi_cartridges'       => 'cartridgeitems_id'
                       ]
                    ];

                    $linktype  = 'CartridgeItem';
                    $linkfield = 'cartridgeitems_id';
                }

                if ($itemtype == 'Consumable') {
                    $criteria['INNER JOIN']['glpi_consumableitems'] = [
                       'ON' => [
                          'glpi_consumableitems'  => 'id',
                          'glpi_consumables'      => 'consumableitems_id'
                       ]
                    ];

                    $linktype  = 'ConsumableItem';
                    $linkfield = 'consumableitems_id';
                }

                if ($itemtype == 'Item_DeviceControl') {
                    $criteria['INNER JOIN']['glpi_devicecontrols'] = [
                       'ON' => [
                          'glpi_items_devicecontrols'   => 'devicecontrols_id',
                          'glpi_devicecontrols'         => 'id'
                       ]
                    ];

                    $linktype = 'DeviceControl';
                    $linkfield = 'devicecontrols_id';
                }

                $linktable = getTableForItemType($linktype);

                $criteria['SELECT'] = [
                   'glpi_infocoms.entities_id',
                   $linktype::getNameField(),
                   "$itemtable.*"
                ];

                $criteria['WHERE'] = [
                   'glpi_infocoms.itemtype'      => $itemtype,
                   'glpi_infocoms.suppliers_id'  => $instID,
                ] + getEntitiesRestrictCriteria($linktable);

                $criteria['ORDERBY'] = [
                   'glpi_infocoms.entities_id',
                   "$linktable." . $linktype::getNameField()
                ];

                $iterator = $DB->request($criteria);
                $nb = count($iterator);

                foreach ($iterator as $data) {
                    $name = $data[$linktype::getNameField()];
                    if ($_SESSION['glpiis_ids_visible'] || $name === '') {
                        $name = sprintf(__('%1$s (%2$s)'), $name, $data['id']);
                    }
                    $name = '<a href="' . htmlspecialchars($linktype::getFormURLWithID($data[$linkfield]), ENT_QUOTES, 'UTF-8') . '">'
                        . htmlspecialchars($name, ENT_QUOTES, 'UTF-8', false) . '</a>';
                    if (!empty($data['is_deleted'])) {
                        $name .= ' <span class="badge text-bg-warning">' . __('Deleted') . '</span>';
                    }
                    if (!empty($data['is_template'])) {
                        $name .= ' <span class="badge text-bg-secondary">' . __('Template') . '</span>';
                    }
                    $values[] = [$item->getTypeName(1),
                        htmlspecialchars(Dropdown::getDropdownName('glpi_entities', $data['entities_id']), ENT_QUOTES, 'UTF-8', false),
                        $name, htmlspecialchars($data['serial'] ?? '-', ENT_QUOTES, 'UTF-8', false),
                        htmlspecialchars($data['otherserial'] ?? '-', ENT_QUOTES, 'UTF-8', false)];
                }
                $num += $nb;
            }
        }
        renderTwigTemplate('table.twig', ['id' => 'supplierFinancialItems_' . $instID, 'state_key' => 'Supplier_financial_items',
            'pageSize' => (int) ($_SESSION['glpilist_limit'] ?? 15),
            'fields' => [_n('Type', 'Types', 1), Entity::getTypeName(1), __('Name'), __('Serial number'), __('Inventory number')],
            'values' => $values]);
        echo '<p class="text-end fw-bold mt-3">' . __('Total') . ': ' . $num . '</p>';
    }

    /**
     * Get suppliers matching a given email
     *
     * @since 9.5
     *
     * @param $email boolean : also display name ? (false by default)
     **/
    public static function getSuppliersByEmail($email)
    {
        global $DB;

        $suppliers = $DB->request([
           'SELECT' => ["id"],
           'FROM' => 'glpi_suppliers',
           'WHERE' => ['email' => $email]
        ]);

        return $suppliers;
    }


    public static function getIcon()
    {
        return "fas fa-dolly";
    }
}
