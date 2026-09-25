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

class Contact_Supplier extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1 = 'Contact';
    public static $items_id_1 = 'contacts_id';

    public static $itemtype_2 = 'Supplier';
    public static $items_id_2 = 'suppliers_id';



    public static function getTypeName($nb = 0)
    {
        return _n('Link Contact/Supplier', 'Links Contact/Supplier', $nb);
    }

    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate && Session::haveRight("contact_enterprise", READ)) {
            $nb = 0;
            switch ($item->getType()) {
                case 'Supplier':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb =  self::countForItem($item);
                    }
                    return self::createTabEntry(Contact::getTypeName(Session::getPluralNumber()), $nb);

                case 'Contact':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = self::countForItem($item);
                    }
                    return self::createTabEntry(Supplier::getTypeName(Session::getPluralNumber()), $nb);
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'Supplier':
                self::showForSupplier($item);
                break;

            case 'Contact':
                self::showForContact($item);
                break;
        }
        return true;
    }


    /**
     * Print the HTML array for entreprises on the current contact
     *
     * @return void
     */
    public static function showForContact(Contact $contact)
    {

        $instID = $contact->fields['id'];

        if (!$contact->can($instID, READ)) {
            return;
        }

        $canedit = $contact->can($instID, UPDATE);

        $iterator = self::getListForItem($contact);
        $number = count($iterator);

        $suppliers = [];
        $used = [];
        while ($data = $iterator->next()) {
            $suppliers[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($canedit) {
            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  [
                     'type' => 'submit',
                     'name' => 'add',
                     'value' => _sx('button', 'Add'),
                     'class' => 'btn btn-secondary'
                  ]
               ],
               'content' => [
                  __('Add a supplier') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'contacts_id',
                           'value' => $instID
                        ],
                        '' => [
                           'type' => 'select',
                           'name' => 'suppliers_id',
                           ...getAjaxDropdownOptions('Supplier', [
                              'is_active' => true
                           ], true, false, $used),
                           'actions' => getItemActionButtons(['info'], 'Supplier'),
                           'col_lg' => 12,
                           'col_md' => 12,
                        ]
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        if ($canedit && $number) {
            $massiveactionparams = [
               'num_displayed' => min($_SESSION['glpilist_limit'], $number),
               'container'     => 'tableForContactSupplier',
               'is_deleted'    => false,
               'display_arrow' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }

        $fields = [
           'supplier' => Supplier::getTypeName(1),
           'entity' => Entity::getTypeName(1),
           'suppliertypes_id' => SupplierType::getTypeName(1),
           'phonenumber' => Phone::getTypeName(1),
           'fax' => __('Fax'),
           'website' => __('Website'),
        ];

        $values = [];
        $massiveactionValues = [];

        if ($number > 0) {
            Session::initNavigateListItems(
                'Supplier',
                //TRANS : %1$s is the itemtype name,
                //        %2$s is the name of the item (used for headings of a list)
                sprintf(
                    __('%1$s = %2$s'),
                    Contact::getTypeName(1),
                    $contact->getName()
                )
            );

            foreach ($suppliers as $data) {
                $assocID = $data["linkid"];
                Session::addToNavigateListItems('Supplier', $data["id"]);
                $website           = $data["website"];

                if (!empty($website)) {
                    $website = $data["website"];

                    if (!preg_match("?https*://?", $website)) {
                        $website = "http://" . $website;
                    }
                    $website = "<a target=_blank href='$website'>" . $data["website"] . "</a>";
                }

                $supplierName = Dropdown::getDropdownName("glpi_suppliers", $data["id"]);
                if (
                    $_SESSION["glpiis_ids_visible"]
                    || empty($supplierName)
                ) {
                    $supplierName = sprintf(__('%1$s (%2$s)'), $supplierName, $data["id"]);
                }

                $values[] = [
                   'supplier' => "<a href='" . Supplier::getFormURLWithID($data["id"]) . "'>" . $supplierName . "</a>",
                   'entity' => Dropdown::getDropdownName("glpi_entities", $data["entity"]),
                   'suppliertypes_id' => Dropdown::getDropdownName("glpi_suppliertypes", $data["suppliertypes_id"]),
                   'phonenumber' => $data["phonenumber"],
                   'fax' => $data["fax"],
                   'website' => $website,
                ];
                $massiveactionValues[] = sprintf('item[%s][%s]', __CLASS__, $assocID);
            }
        }

        renderTwigTemplate('table.twig', [
           'id' => 'tableForContactSupplier',
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massiveactionValues,
        ]);
    }

    /**
     * Show contacts associated to an enterprise
     *
     * @param Supplier $supplier
     *
     * @return void
     */
    public static function showForSupplier(Supplier $supplier)
    {

        $instID = $supplier->fields['id'];
        if (!$supplier->can($instID, READ)) {
            return;
        }
        $canedit = $supplier->can($instID, UPDATE);
        $rand = mt_rand();

        $iterator = self::getListForItem($supplier);
        $number = count($iterator);

        $contacts = [];
        $options = getAjaxDropdownOptionsByEntity(Contact::class, $supplier->fields['entities_id']);
        while ($data = $iterator->next()) {
            $options['used'][] = $data['id'];
            $contacts[$data['linkid']] = $data;
        };

        if ($canedit) {
            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  [
                     'type' => 'submit',
                     'name' => 'add',
                     'value' => _sx('button', 'Add'),
                     'class' => 'btn btn-secondary'
                  ]
               ],
               'content' => [
                  __('Add a contact') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'suppliers_id',
                           'value' => $instID
                        ],
                        '' => [
                           'type' => 'select',
                           'name' => 'contacts_id',
                           ...$options,
                           'actions' => getItemActionButtons(['info'], 'Contact'),
                           'col_lg' => 12,
                           'col_md' => 12,
                        ]
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        if ($canedit && $number) {
            $massiveactionparams = [
               'num_displayed' => min($_SESSION['glpilist_limit'], $number),
               'container'     => 'tableForContactSupplier',
               'is_deleted'    => false,
               'display_arrow' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        $fields = [
           __('Name'),
           Entity::getTypeName(1),
           Phone::getTypeName(1),
           __('Phone 2'),
           __('Mobile phone'),
           __('Fax'),
           _n('Email', 'Emails', 1),
           _n('Type', 'Types', 1)
        ];

        $values = [];
        $massiveactionValues = [];
        foreach ($contacts as $data) {
            $values[] = [
               '<a href="' . Contact::getFormURLWithID($data["id"]) . '">' .
                           sprintf(__('%1$s %2$s'), $data["name"], $data["firstname"]) . '</a>',
               Dropdown::getDropdownName("glpi_entities", $data["entity"]),
               $data["phone"],
               $data["phone2"],
               $data["mobile"],
               $data["fax"],
               "<a href='mailto:" . $data["email"] . "'>" . $data["email"] . "</a>",
               Dropdown::getDropdownName("glpi_contacttypes", $data["contacttypes_id"])
            ];
            $massiveactionValues[] = sprintf('item[%s][%s]', __CLASS__, $data['linkid']);
        }

        renderTwigTemplate('table.twig', [
           'id' => 'tableForContactSupplier',
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massiveactionValues,
        ]);
    }
}
