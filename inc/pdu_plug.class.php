<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

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
**/

class Pdu_Plug extends CommonDBRelation
{
    public static $itemtype_1 = 'PDU';
    public static $items_id_1 = 'pdus_id';
    public static $itemtype_2 = 'Plug';
    public static $items_id_2 = 'plugs_id';
    public static $checkItem_1_Rights = self::DONT_CHECK_ITEM_RIGHTS;
    public static $mustBeAttached_1      = false;
    public static $mustBeAttached_2      = false;

    public static function getTypeName($nb = 0)
    {
        return _n('PDU plug', 'PDU plugs', $nb);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $nb = 0;
        switch ($item->getType()) {
            default:
                $field = $item->getType() == PDU::getType() ? 'pdus_id' : 'plugs_id';
                if ($_SESSION['glpishow_count_on_tabs']) {
                    $nb = countElementsInTable(
                        self::getTable(),
                        [$field  => $item->getID()]
                    );
                }
                return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showItems($item, $withtemplate);
    }

    /**
     * Print items
     *
     * @param  PDU $pdu PDU instance
     *
     * @return void
     */
    public static function showItems(PDU $pdu)
    {
        $ID = $pdu->getID();
        $rand = mt_rand();

        if (
            !$pdu->getFromDB($ID)
            || !$pdu->can($ID, READ)
        ) {
            return false;
        }
        $canedit = $pdu->canEdit($ID);

        $request = self::getAdapter()->request([
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'pdus_id' => $pdu->getID()
           ]
        ]);
        $items = $request->fetchAllAssociative();
        $link = new self();

        Session::initNavigateListItems(
            self::getType(),
            //TRANS : %1$s is the itemtype name,
            //        %2$s is the name of the item (used for headings of a list)
            sprintf(
                __('%1$s = %2$s'),
                $pdu->getTypeName(1),
                $pdu->getName()
            )
        );


        if ($canedit) {
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
                  __('Add an item') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'pdus_id',
                           'value' => $ID
                        ],
                        __('Add a new plug') => [
                           'type' => 'select',
                           'name' => 'plugs_id',
                           'values' => getOptionForItems(Plug::class),
                           'actions' => getItemActionButtons(['info', 'add'], Plug::class),
                        ],
                        __('Number') => [
                           'type' => 'number',
                           'name' => 'number_plugs',
                           'col_lg' => 6,
                        ],
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        if ($canedit) {
            $massiveactionparams = [
               'container'       => 'tableForPDUPlug',
               'display_arrow' => false,
               'specific_actions' => [
                  'MassiveAction:purge' => _x('button', 'Delete permanently the relation with selected elements'),
               ],
            ];
            Html::showMassiveActions($massiveactionparams);
        }

        $fields = [
           __('Name'),
           __('Number')
        ];
        $values = [];
        $massive_action = [];
        foreach ($items as $row) {
            $item = new Plug();
            $item->getFromDB($row['plugs_id']);
            $values[] = [
               $item->getLink(),
               $row['number_plugs']
            ];
            $massive_action[] = sprintf('item[%s][%s]', self::class, $row['id']);
        }
        renderTwigTemplate('table.twig', [
           'id' => 'tableForPDUPlug',
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);
    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'CommonDBConnexity:affect';
        $forbidden[] = 'CommonDBConnexity:unaffect';
        return $forbidden;
    }
}
