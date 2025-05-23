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

class Link_Itemtype extends CommonDBChild
{
    // From CommonDbChild
    public static $itemtype = 'Link';
    public static $items_id = 'links_id';


    /**
     * @since 0.84
    **/
    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    /**
     * Print the HTML array for device on link
     *
     * @param $link : Link
     *
     * @return void
    **/
    public static function showForLink($link)
    {
        global $DB,$CFG_GLPI;

        $links_id = $link->getField('id');

        $canedit  = $link->canEdit($links_id);
        $rand     = mt_rand();

        if (
            !Link::canView()
            || !$link->can($links_id, READ)
        ) {
            return false;
        }

        $request = self::getAdapter()->request([
           'FROM'   => 'glpi_links_itemtypes',
           'WHERE'  => ['links_id' => $links_id],
           'ORDER'  => 'itemtype'
        ]);
        $types  = [];
        $used   = [];
        $results = $request->fetchAllAssociative();
        $numrows = count($results);
        foreach ($results as $data) {
            $types[$data['id']]      = $data;
            $used[$data['itemtype']] = $data['itemtype'];
        }

        if ($canedit) {
            $values = [];
            if (count($CFG_GLPI["link_types"])) {
                foreach ($CFG_GLPI["link_types"] as $type) {
                    if ($item = getItemForItemtype($type)) {
                        $values[$type] = $item->getTypeName(1);
                    }
                }
            }
            asort($values);

            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  [
                     'type'  => 'submit',
                     'name' => 'add',
                     'value' => _sx('button', 'Add'),
                     'class' => 'btn btn-secondary',
                  ]
               ],
               'content' => [
                  '' => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'links_id',
                           'value' => $links_id,
                        ],
                        __('Add an item type') => [
                           'type' => 'select',
                           'name' => 'itemtype',
                           'values' => array_merge([Dropdown::EMPTY_VALUE], $values),
                           'col_lg' => 12,
                           'col_md' => 12,
                        ]
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        if ($canedit && $numrows) {
            $massiveactionparams = [
               'num_displayed'  => min($_SESSION['glpilist_limit'], $numrows),
               'container'      => 'tab_associated_itemtypes',
               'display_arrow'  => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        $fields = [_n('Type', 'Types', 1)];
        $values = [];
        $massiveactionparams = [];

        foreach ($types as $data) {
            $typename = NOT_AVAILABLE;
            if ($item = getItemForItemtype($data['itemtype'])) {
                $values[] = [$item->getTypeName(1)];
                $massiveactionparams[] = sprintf('item[%s][%s]', self::class, $data['id']);
            }
        }
        renderTwigTemplate('table.twig', [
           'id' => 'tab_associated_itemtypes',
           'fields' => $fields,
           'values' => $values,
           'itemtype' => self::class,
           'massive_action' => $massiveactionparams,
        ]);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            $nb = 0;
            switch ($item->getType()) {
                case 'Link':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable(
                            $this->getTable(),
                            ['links_id' => $item->getID()]
                        );
                    }
                    return self::createTabEntry(_n(
                        'Associated item type',
                        'Associated item types',
                        Session::getPluralNumber()
                    ), $nb);
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == 'Link') {
            self::showForLink($item);
        }
        return true;
    }


    /**
     *
     * Remove all associations for an itemtype
     *
     * @since 0.85
     *
     * @param string $itemtype  itemtype for which all link associations must be removed
     */
    public static function deleteForItemtype($itemtype)
    {
        $adapter = self::getAdapter();
        $items = $adapter->request([
            'SELECT' => ['id'],
            'FROM'   => self::getTable(),
            'WHERE'  => [
                'itemtype'  => ['LIKE', "%Plugin$itemtype%"]
            ]
        ]);
        
        foreach ($items->fetchAllAssociative() as $data) {
            $link_itemtype = new self();
            if ($link_itemtype->getFromDB($data['id'])) {
                $link_itemtype->deleteFromDB();
            }
        }
    }
}
