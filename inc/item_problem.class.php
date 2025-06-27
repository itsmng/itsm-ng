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
 * Item_Problem Class
 *
 *  Relation between Problems and Items
**/
class Item_Problem extends CommonItilObject_Item
{
    // From CommonDBRelation
    public static $itemtype_1          = 'Problem';
    public static $items_id_1          = 'problems_id';

    public static $itemtype_2          = 'itemtype';
    public static $items_id_2          = 'items_id';
    public static $checkItem_2_Rights  = self::HAVE_VIEW_RIGHT_ON_ITEM;



    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    public function prepareInputForAdd($input)
    {

        // Avoid duplicate entry
        if (
            countElementsInTable($this->getTable(), ['problems_id' => $input['problems_id'],
                                                    'itemtype'    => $input['itemtype'],
                                                    'items_id'    => $input['items_id']]) > 0
        ) {
            return false;
        }
        return parent::prepareInputForAdd($input);
    }


    /**
     * Print the HTML array for Items linked to a problem
     *
     * @param $problem Problem object
     *
     * @return void
    **/
    public static function showForProblem(Problem $problem)
    {
        global $CFG_GLPI;

        $instID = $problem->fields['id'];

        if (!$problem->can($instID, READ)) {
            return false;
        }
        $canedit = $problem->canEdit($instID);
        $rand    = mt_rand();

        $types_iterator = self::getDistinctTypes($instID);
        $number = count($types_iterator);

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
                           'name' => 'problems_id',
                           'value' => $instID
                        ],
                        __('Type') => [
                           'type' => 'select',
                           'id' => 'dropdown_itemtype',
                           'name' => 'itemtype',
                           'values' => [Dropdown::EMPTY_VALUE] + array_unique($problem->getAllTypesForHelpdesk()),
                           'col_lg' => 6,
                           'hooks' => [
                              'change' => <<<JS
                              $.ajax({
                                    method: "POST",
                                    url: "$CFG_GLPI[root_doc]/ajax/getDropdownValue.php",
                                    data: {
                                       itemtype: this.value,
                                       display_emptychoice: 1,
                                    },
                                    success: function(response) {
                                       const data = response.results;
                                       $('#dropdown_items_id').empty();
                                       for (let i = 0; i < data.length; i++) {
                                          if (data[i].children) {
                                             const group = $('#dropdown_items_id')
                                                .append("<optgroup label='" + data[i].text + "'></optgroup>");
                                             for (let j = 0; j < data[i].children.length; j++) {
                                                group.append("<option value='" + data[i].children[j].id + "'>" + data[i].children[j].text + "</option>");
                                             }
                                          } else {
                                             $('#dropdown_items_id').append("<option value='" + data[i].id + "'>" + data[i].text + "</option>");
                                          }
                                       }
                                    }
                                 });
                           JS,
                           ]
                        ],
                        __('Item') => [
                           'type' => 'select',
                           'id' => 'dropdown_items_id',
                           'name' => 'items_id',
                           'values' => [],
                           'col_lg' => 6,
                        ],
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        if ($canedit && $number) {
            $massiveactionparams = [
               'container' => 'tableForProblemItem',
               'specific_actions' => [
                  'MassiveAction:purge' => _x('button', 'Delete permanently the relation with selected elements'),
               ],
               'display_arrow' => false
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        $fields = [
           _n('Type', 'Types', 1),
           Entity::getTypeName(1),
           __('Name'),
           __('Serial number'),
           __('Inventory number'),
        ];
        $values = [];
        $massive_action = [];
        foreach ($types_iterator as $row) {
            $itemtype = $row['itemtype'];
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }

            if ($item->canView()) {
                $iterator = self::getTypeItems($instID, $itemtype);
                $nb = count($iterator);

                $prem = true;
                while ($data = $iterator->next()) {
                    $name = $data["name"];
                    if (
                        $_SESSION["glpiis_ids_visible"]
                        || empty($data["name"])
                    ) {
                        $name = sprintf(__('%1$s (%2$s)'), $name, $data["id"]);
                    }
                    $link = $itemtype::getFormURLWithID($data['id']);
                    $namelink = "<a href=\"" . $link . "\">" . $name . "</a>";
                    $typename = $item->getTypeName($nb);

                    $values[] = [
                       (($nb > 1) ? sprintf(__('%1$s: %2$s'), $typename, $nb) : $typename),
                       Dropdown::getDropdownName("glpi_entities", $data['entity']),
                       $namelink,
                       (isset($data["serial"]) ? "" . $data["serial"] . "" : "-"),
                       (isset($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-"),
                    ];
                    $massive_action[] = sprintf('item[%s][%s]', self::class, $data['linkid']);
                }
            }
        }
        renderTwigTemplate('table.twig', [
           'id' => 'tableForProblemItem',
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action
        ]);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        global $DB;

        if (!$withtemplate) {
            $nb = 0;
            switch ($item->getType()) {
                case 'Problem':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = self::countForMainItem($item);
                    }
                    return self::createTabEntry(_n('Item', 'Items', Session::getPluralNumber()), $nb);

                case 'User':
                case 'Group':
                case 'Supplier':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $from = $item->getType() == 'Group' ? 'glpi_groups_problems' : 'glpi_problems_' . strtolower($item->getType() . 's');
                        $result = $this::getAdapter()->request([
                           'COUNT'  => 'cpt',
                           'FROM'   => $from,
                           'WHERE'  => [
                              $item->getForeignKeyField()   => $item->fields['id']
                           ]
                        ])->fetchAssociative();
                        $nb = $result['cpt'];
                    }
                    return self::createTabEntry(Problem::getTypeName(Session::getPluralNumber()), $nb);

                default:
                    if (Session::haveRight("problem", Problem::READALL)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            // Direct one
                            $nb = self::countForItem($item);
                            // Linked items
                            $linkeditems = $item->getLinkedItems();

                            if (count($linkeditems)) {
                                foreach ($linkeditems as $type => $tab) {
                                    $typeitem = new $type();
                                    foreach ($tab as $ID) {
                                        if ($typeitem->getFromDB($ID)) {
                                            $nb += self::countForItem($typeitem);
                                        }
                                    }
                                }
                            }
                        }
                        return self::createTabEntry(Problem::getTypeName(Session::getPluralNumber()), $nb);
                    }
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'Problem':
                self::showForProblem($item);
                break;

            default:
                Problem::showListForItem($item, $withtemplate);
        }
        return true;
    }
}
