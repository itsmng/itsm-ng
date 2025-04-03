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

// Relation between CartridgeItem and PrinterModel
// since version 0.84
class CartridgeItem_PrinterModel extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1          = 'CartridgeItem';
    public static $items_id_1          = 'cartridgeitems_id';

    public static $itemtype_2          = 'PrinterModel';
    public static $items_id_2          = 'printermodels_id';
    public static $checkItem_2_Rights  = self::DONT_CHECK_ITEM_RIGHTS;



    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'CartridgeItem':
                self::showForCartridgeItem($item);
                break;
        }
        return true;
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate && Printer::canView()) {
            $nb = 0;
            switch ($item->getType()) {
                case 'CartridgeItem':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = self::countForItem($item);
                    }
                    return self::createTabEntry(PrinterModel::getTypeName(Session::getPluralNumber()), $nb);
            }
        }
        return '';
    }


    /**
     * Show the printer types that are compatible with a cartridge type
     *
     * @param $item   CartridgeItem object
     *
     * @return boolean|void
    **/
    public static function showForCartridgeItem(CartridgeItem $item)
    {

        $instID = $item->getField('id');
        if (!$item->can($instID, READ)) {
            return false;
        }
        $canedit = $item->canEdit($instID);
        $rand    = mt_rand();

        $iterator = self::getListForItem($item);
        $number = count($iterator);

        $used  = [];
        $datas = [];
        foreach ($iterator as $data) {
            $used[$data["id"]] = $data["id"];
            $datas[$data["linkid"]]  = $data;
        }

        if ($canedit) {
            $options = getOptionForItems(PrinterModel::class);
            foreach ($used as $cartridge) {
                unset($options[$cartridge]);
            };
            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  [
                     'type' => 'submit',
                     'name' => 'add',
                     'value' => _sx('button', 'Add an item'),
                     'class' => 'btn btn-secondary'
                  ]
               ],
               'content' => [
                  __('Add a compatible printer model') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'cartridgeitems_id',
                           'value' => $instID
                        ],
                        '' => [
                           'type' => 'select',
                           'name' => 'printermodels_id',
                           'values' => $options,
                           'col_lg' => 12,
                           'col_md' => 12,
                           'actions' => getItemActionButtons(['info', 'add'], PrinterModel::class)
                        ],
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        if ($number) {
            if ($canedit) {
                $massiveactionparams = [
                   'num_displayed' => min($_SESSION['glpilist_limit'], count($used)),
                   'container'     => 'tableForCartidgeItemPrinterModel',
                   'display_arrow' => false,
                   'specific_actions' => [
                      'MassiveAction:purge' => _x('button', 'Delete permanently the relation with selected elements'),
                   ],
                ];
                Html::showMassiveActions($massiveactionparams);
            }
            $fields = [_n('Model', 'Models', 1)];
            $values = [];
            $massive_action = [];
            foreach ($datas as $data) {
                $opt = [
                   'is_deleted' => 0,
                   'criteria'   => [
                      [
                         'field'      => 40, // printer model
                         'searchtype' => 'equals',
                         'value'      => $data["id"],
                      ]
                   ]
                ];
                $url = Printer::getSearchURL() . "?" . Toolbox::append_params($opt, '&amp;');
                $values[] = ["<a href='" . $url . "'>" . $data["name"]];
                $massive_action[] = sprintf('item[%s][%s]', self::class, $data['linkid']);
            }
            renderTwigTemplate('table.twig', [
               'id' => 'tableForCartidgeItemPrinterModel',
               'fields' => $fields,
               'values' => $values,
               'massive_action' => $massive_action,
            ]);
        }
    }
}
