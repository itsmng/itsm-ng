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

// Relation between Contracts and Suppliers
class Contract_Supplier extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1 = 'Contract';
    public static $items_id_1 = 'contracts_id';

    public static $itemtype_2 = 'Supplier';
    public static $items_id_2 = 'suppliers_id';


    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            $nb = 0;
            switch ($item->getType()) {
                case 'Supplier':
                    if (Contract::canView()) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            $nb =  self::countForItem($item);
                        }
                        return self::createTabEntry(
                            Contract::getTypeName(Session::getPluralNumber()),
                            $nb
                        );
                    }
                    break;

                case 'Contract':
                    if (Session::haveRight("contact_enterprise", READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            $nb = self::countForItem($item);
                        }
                        return self::createTabEntry(Supplier::getTypeName(Session::getPluralNumber()), $nb);
                    }
                    break;
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

            case 'Contract':
                self::showForContract($item);
                break;
        }
        return true;
    }


    /**
     * Print an HTML array with contracts associated to the enterprise
     *
     * @since 0.84
     *
     * @param Supplier $supplier
     *
     * @return void
    **/
    public static function showForSupplier(Supplier $supplier)
    {

        $ID = $supplier->fields['id'];
        if (
            !Contract::canView()
            || !$supplier->can($ID, READ)
        ) {
            return;
        }
        $canedit = $supplier->can($ID, UPDATE);
        $rand    = mt_rand();

        $iterator = self::getListForItem($supplier);
        $number = count($iterator);

        $contracts = [];
        $options = getOptionForItems('Contract', [
           'entities_id' => $supplier->fields["entities_id"],
           'is_recursive' => $supplier->fields["is_recursive"]
        ]);
        foreach ($iterator as $data) {
            unset($options[$data['id']]);
            $contracts[$data['linkid']] = $data;
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
                  __('Add a contract') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'suppliers_id',
                           'value' => $ID
                        ],
                        '' => [
                           'type' => 'select',
                           'name' => 'contracts_id',
                           'values' => $options,
                           'actions' => getItemActionButtons(['info'], 'Contract'),
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
               'container'     => 'tableForContractSupplier',
               'num_displayed' => min($_SESSION['glpilist_limit'], $number),
               'display_arrow' => false,
               'is_deleted' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        $fields = [
           'name' => __('Name'),
           'entities_id' => Entity::getTypeName(1),
           'num' => _x('phone', 'Number'),
           'contracttypes_id' => ContractType::getTypeName(1),
           'begin_date' => __('Start date'),
           'duration' => __('Initial contract period')
        ];
        $values = [];
        $massiveactionValues = [];
        foreach ($contracts as $data) {
            $cID        = $data["id"];
            $assocID    = $data["linkid"];
            $name = $data["name"];
            if (
                $_SESSION["glpiis_ids_visible"]
                || empty($data["name"])
            ) {
                $name = sprintf(__('%1$s (%2$s)'), $name, $data["id"]);
            }
            $values[] = [
               'checkbox' => $canedit ? Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) : '',
               'name' => "<a href='" . Contract::getFormURLWithID($cID) . "'>" . $name . "</a>",
               'entities_id' => Dropdown::getDropdownName("glpi_entities", $data["entity"]),
               'num' => $data["num"],
               'contracttypes_id' => Dropdown::getDropdownName("glpi_contracttypes", $data["contracttypes_id"]),
               'begin_date' => Html::convDate($data["begin_date"]),
               'duration' => sprintf(_n('%d month', '%d months', $data["duration"]), $data["duration"])
            ];
            $massiveactionValues[] = sprintf('item[%s][%s]', __CLASS__, $assocID);
        }
        renderTwigTemplate('table.twig', [
           'id' => 'tableForContractSupplier',
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massiveactionValues,
        ]);
    }


    /**
     * Print the HTML array of suppliers for this contract
     *
     * @since 0.84
     *
     * @param $contract Contract object
     *
     * @return void
     **/
    public static function showForContract(Contract $contract)
    {

        $instID = $contract->fields['id'];

        if (
            !$contract->can($instID, READ)
            || !Session::haveRight("contact_enterprise", READ)
        ) {
            return;
        }
        $canedit = $contract->can($instID, UPDATE);
        $rand    = mt_rand();

        $iterator = self::getListForItem($contract);
        $number = count($iterator);

        $suppliers = [];
        $used      = [];
        // while ($data = $iterator->next()) {
        foreach ($iterator as $data) {
            $suppliers[$data['linkid']]   = $data;
            $used[$data['id']]            = $data['id'];
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
                    '' => [
                        'visible' => true,
                        'inputs' => [
                            [
                                'type' => 'hidden',
                                'name' => 'contracts_id',
                                'value' => $instID
                            ],
                            __('Add a supplier') => [
                                'type' => 'select',
                                'name' => 'suppliers_id',
                                'values' => getOptionForItems('Supplier', [], true, false, $used),
                                'actions' => getItemActionButtons(['info'], 'Supplier'),
                                'col_lg' => 12,
                                'col_md' => 12,
                            ],
                        ]
                    ]
                ]
            ];
            renderTwigForm($form);
        }

        $headers = [
          'supplier' => Supplier::getTypeName(1),
          'entity' => Entity::getTypeName(1),
          'suppliertypes_id' => SupplierType::getTypeName(1),
          'phonenumber' => _x('phone', 'Number'),
          'website' => __('Website')
        ];
        $values = [];
        $massiveactionValues = [];
        Html::showMassiveActions([
          'container'     => 'tableForContractSupplier',
          'display_arrow' => false,
          'is_deleted' => false,
        ]);
        foreach ($suppliers as $data) {
            $assocID = $data['linkid'];
            $website = $data['website'];
            if (!empty($website)) {
                if (!preg_match("?https*://?", $website)) {
                    $website = "http://" . $website;
                }
                $website = "<a target=_blank href='$website'>" . $data['website'] . "</a>";
            }
            $entID         = $data['id'];
            $entity        = $data['entity'];
            $entname       = Dropdown::getDropdownName("glpi_suppliers", $entID);
            if (
                $_SESSION["glpiis_ids_visible"]
                || empty($entname)
            ) {
                $entname = sprintf(__('%1$s (%2$s)'), $entname, $entID);
            }
            $values[] = [
               'supplier' => "<a href='" . Supplier::getFormURLWithID($entID) . "'>" . $entname . "</a>",
               'entity' => Dropdown::getDropdownName("glpi_entities", $entity),
               'suppliertypes_id' => Dropdown::getDropdownName("glpi_suppliertypes", $data['suppliertypes_id']),
               'phonenumber' => $data['phonenumber'],
               'website' => $website
            ];
            $massiveactionValues[] = sprintf('item[%s][%s]', __CLASS__, $assocID);
        }
        renderTwigTemplate('table.twig', [
           'id' => 'tableForContractSupplier',
           'fields' => $headers,
           'values' => $values,
           'massive_action' => $massiveactionValues,
        ]);
    }
}
