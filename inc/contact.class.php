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

use Sabre\VObject;

/**
 * Contact class
**/
class Contact extends CommonDBTM
{
    // From CommonDBTM
    public $dohistory           = true;

    public static $rightname           = 'contact_enterprise';
    protected $usenotepad       = true;



    public static function getTypeName($nb = 0)
    {
        return _n('Contact', 'Contacts', $nb);
    }


    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
              Contact_Supplier::class,
              ProjectTaskTeam::class,
              ProjectTeam::class,
            ]
        );
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Contact_Supplier', $ong, $options);
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab('Link', $ong, $options);
        $this->addStandardTab('Notepad', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    /**
     * Get address of the contact (company one)
     *
     *@return string containing the address
    **/
    public function getAddress()
    {
        global $DB;

        $iterator = $DB->request([
           'SELECT' => [
              'glpi_suppliers.name',
              'glpi_suppliers.address',
              'glpi_suppliers.postcode',
              'glpi_suppliers.town',
              'glpi_suppliers.state',
              'glpi_suppliers.country'
           ],
           'FROM'         => 'glpi_suppliers',
           'INNER JOIN'   => [
              'glpi_contacts_suppliers'  => [
                 'ON' => [
                    'glpi_contacts_suppliers'  => 'suppliers_id',
                    'glpi_suppliers'           => 'id'
                 ]
              ]
           ],
           'WHERE'        => ['contacts_id' => $this->fields['id']]
        ]);

        if ($data = $iterator->next()) {
            return $data;
        }
    }


    /**
     * Get website of the contact (company one)
     *
     *@return string containing the website
    **/
    public function getWebsite()
    {
        global $DB;

        $iterator = $DB->request([
           'SELECT' => [
              'glpi_suppliers.website AS website'
           ],
           'FROM'         => 'glpi_suppliers',
           'INNER JOIN'   => [
              'glpi_contacts_suppliers'  => [
                 'ON' => [
                    'glpi_contacts_suppliers'  => 'suppliers_id',
                    'glpi_suppliers'           => 'id'
                 ]
              ]
           ],
           'WHERE'        => ['contacts_id' => $this->fields['id']]
        ]);

        if ($data = $iterator->next()) {
            return $data['website'];
        }
        return '';
    }


    /**
     * Print the contact form
     *
     * @param $ID        integer ID of the item
     * @param $options   array
     *     - target filename : where to go when done.
     *     - withtemplate boolean : template or basic item
     *
     * @return true
    **/
    public function showForm($ID)
    {
        $form = [
              'action' => Toolbox::getItemTypeFormURL('contact'),
              'itemtype' => self::class,
              'content' => [
                  __('Contact') => [
                      'visible' => true,
                      'inputs' => [
                          $this->isNewID($ID) ? [] : [
                              'type' => 'hidden',
                              'name' => 'id',
                              'value' => $ID
                          ],
                          __('Surname') => [
                              'name' => 'name',
                              'type' => 'text',
                              'value' => $this->fields['name'],
                          ],
                          __('First name') => [
                              'name' => 'firstname',
                              'type' => 'text',
                              'value' => $this->fields['firstname'],
                          ],
                          __('Phone') => [
                              'name' => 'phone',
                              'type' => 'text',
                              'value' => $this->fields['phone'],
                          ],
                          __('Phone 2') => [
                              'name' => 'phone2',
                              'type' => 'text',
                              'value' => $this->fields['phone2'],
                          ],
                          __('Mobile phone') => [
                              'name' => 'mobile',
                              'type' => 'text',
                              'value' => $this->fields['mobile'],
                          ],
                          __('Fax') => [
                              'name' => 'fax',
                              'type' => 'text',
                              'value' => $this->fields['fax'],
                          ],
                          __('Email') => [
                              'name' => 'email',
                              'type' => 'text',
                              'value' => $this->fields['email'],
                          ],
                          __('Type') => [
                              'name' => 'contacttypes_id',
                              'type' => 'select',
                              'values' => getOptionForItems("contacttype"),
                              'value' => $this->fields['contacttypes_id'],
                              'actions' => getItemActionButtons(['info', 'add'], "contacttype"),
                          ],
                          __('Title') => [
                              'name' => 'usertitles_id',
                              'type' => 'select',
                              'values' => getOptionForItems("usertitle"),
                              'value' => $this->fields['usertitles_id'],
                              'actions' => getItemActionButtons(['info', 'add'], "usertitle"),
                          ],
                          __('Comments') => [
                              'name' => 'comment',
                              'type' => 'textarea',
                              'value' => $this->fields['comment'],
                          ],
                          __('Address') => [
                              'name' => 'address',
                              'type' => 'textarea',
                              'value' => $this->fields["address"],
                          ],
                          __('Postal code') => [
                          'name' => 'postcode',
                          'type' => 'text',
                          'value' => $this->fields['postcode'],
                          ],
                          __('City') => [
                          'name' => 'town',
                          'type' => 'text',
                          'value' => $this->fields['town'],
                          ],
                          __('State') => [
                          'name' => 'state',
                          'type' => 'text',
                          'value' => $this->fields['state'],
                          ],
                          __('Country') => [
                          'name' => 'country',
                          'type' => 'text',
                          'value' => $this->fields['country'],
                          ]
                      ]
                  ]
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
            $actions['Contact_Supplier' . MassiveAction::CLASS_ACTION_SEPARATOR . 'add']
                  = _x('button', 'Add a supplier');
        }

        return $actions;
    }


    protected function computeFriendlyName()
    {

        if (isset($this->fields["id"]) && ($this->fields["id"] > 0)) {
            return formatUserName(
                '',
                '',
                (isset($this->fields["name"]) ? $this->fields["name"] : ''),
                (isset($this->fields["firstname"]) ? $this->fields["firstname"] : '')
            );
        }
        return '';
    }


    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
           'id'                 => 'common',
           'name'               => __('Characteristics')
        ];

        $tab[] = [
           'id'                 => '1',
           'table'              => $this->getTable(),
           'field'              => 'name',
           'name'               => __('Last name'),
           'datatype'           => 'itemlink',
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'firstname',
           'name'               => __('First name'),
           'datatype'           => 'string',
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
           'field'              => 'phone',
           'name'               => Phone::getTypeName(1),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '4',
           'table'              => $this->getTable(),
           'field'              => 'phone2',
           'name'               => __('Phone 2'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '10',
           'table'              => $this->getTable(),
           'field'              => 'mobile',
           'name'               => __('Mobile phone'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => $this->getTable(),
           'field'              => 'fax',
           'name'               => __('Fax'),
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
           'id'                 => '82',
           'table'              => $this->getTable(),
           'field'              => 'address',
           'name'               => __('Address')
        ];

        $tab[] = [
           'id'                 => '83',
           'datatype'           => 'string',
           'table'              => $this->getTable(),
           'field'              => 'postcode',
           'name'               => __('Postal code'),
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '84',
           'table'              => $this->getTable(),
           'field'              => 'town',
           'name'               => __('City'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '85',
           'table'              => $this->getTable(),
           'field'              => 'state',
           'name'               => _x('location', 'State'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '87',
           'table'              => $this->getTable(),
           'field'              => 'country',
           'name'               => __('Country'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '9',
           'table'              => 'glpi_contacttypes',
           'field'              => 'name',
           'name'               => _n('Type', 'Types', 1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '81',
           'table'              => 'glpi_usertitles',
           'field'              => 'name',
           'name'               => __('Title'),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '8',
           'table'              => 'glpi_suppliers',
           'field'              => 'name',
           'name'               => _n('Associated supplier', 'Associated suppliers', Session::getPluralNumber()),
           'forcegroupby'       => true,
           'datatype'           => 'itemlink',
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

        // add objectlock search options
        $tab = array_merge($tab, ObjectLock::rawSearchOptionsToAdd(get_class($this)));

        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        return $tab;
    }


    /**
     * Generate the Vcard for the current Contact
     *
     * @return void
     */
    public function generateVcard()
    {

        if (!$this->can($this->fields['id'], READ)) {
            return;
        }

        // build the Vcard
        $vcard = new VObject\Component\VCard([
           'N'     => [$this->fields["name"], $this->fields["firstname"]],
           'EMAIL' => $this->fields["email"],
           'NOTE'  => $this->fields["comment"],
        ]);

        $vcard->add('TEL', $this->fields["phone"], ['type' => 'PREF;WORK;VOICE']);
        $vcard->add('TEL', $this->fields["phone2"], ['type' => 'HOME;VOICE']);
        $vcard->add('TEL', $this->fields["mobile"], ['type' => 'WORK;CELL']);
        $vcard->add('URL', $this->GetWebsite(), ['type' => 'WORK']);

        $addr = $this->GetAddress();
        if (is_array($addr)) {
            $addr_string = implode(";", array_filter($addr));
            $vcard->add('ADR', $addr_string, ['type' => 'WORK;POSTAL']);
        }

        // send the  VCard
        $output   = $vcard->serialize();
        $filename = $this->fields["name"] . "_" . $this->fields["firstname"] . ".vcf";

        @header("Content-Disposition: attachment; filename=\"$filename\"");
        @header("Content-Length: " . Toolbox::strlen($output));
        @header("Connection: close");
        @header("content-type: text/x-vcard; charset=UTF-8");

        echo $output;
    }


    public static function getIcon()
    {
        return "fas fa-user-tie";
    }
}
