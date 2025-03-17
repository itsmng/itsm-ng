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
 * @since 0.84
 *
 * Change_Problem Class
 *
 * Relation between Changes and Problems
**/
class Change_Problem extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1   = 'Change';
    public static $items_id_1   = 'changes_id';

    public static $itemtype_2   = 'Problem';
    public static $items_id_2   = 'problems_id';



    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    public static function getTypeName($nb = 0)
    {
        return _n('Link Problem/Change', 'Links Problem/Change', $nb);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (static::canView()) {
            $nb = 0;
            switch ($item->getType()) {
                case 'Change':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable(
                            'glpi_changes_problems',
                            ['changes_id' => $item->getID()]
                        );
                    }
                    return self::createTabEntry(Problem::getTypeName(Session::getPluralNumber()), $nb);

                case 'Problem':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable(
                            'glpi_changes_problems',
                            ['problems_id' => $item->getID()]
                        );
                    }
                    return self::createTabEntry(Change::getTypeName(Session::getPluralNumber()), $nb);
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'Change':
                self::showForChange($item);
                break;

            case 'Problem':
                self::showForProblem($item);
                break;
        }
        return true;
    }


    /**
     * Show tickets for a problem
     *
     * @param $problem Problem object
    **/
    public static function showForProblem(Problem $problem)
    {
        global $DB;

        $ID = $problem->getField('id');
        if (!$problem->can($ID, READ)) {
            return false;
        }

        $canedit = $problem->canEdit($ID);
        $rand    = mt_rand();

        // $iterator = $DB->request([
        //    'SELECT' => [
        //       'glpi_changes_problems.id AS linkid',
        //       'glpi_changes.*'
        //    ],
        //    'DISTINCT'        => true,
        //    'FROM'            => 'glpi_changes_problems',
        //    'LEFT JOIN'       => [
        //       'glpi_changes' => [
        //          'ON' => [
        //             'glpi_changes_problems' => 'changes_id',
        //             'glpi_changes'          => 'id'
        //          ]
        //       ]
        //    ],
        //    'WHERE'           => [
        //       'glpi_changes_problems.problems_id' => $ID
        //    ],
        //    'ORDERBY'         => 'glpi_changes.name'
        // ]);
        $dql = "SELECT DISTINCT cp.id AS linkid, c
        FROM Itsmng\\Domain\\Entities\\ChangeProblem cp
        LEFT JOIN Itsmng\\Domain\\Entities\\Change c WITH cp.change = c.id
        WHERE cp.problem = :problems_id
        ORDER BY c.name";
        $result = self::getAdapter()->request($dql, [
            'problems_id' => $ID
        ]);

        $changes = [];
        $used    = [];
        $numrows = count($result);
        // while ($data = $iterator->next()) {
        //     $changes[$data['id']] = $data;
        //     $used[$data['id']]    = $data['id'];
        // }
        foreach ($result as $data) {
            $changes[$data['id']] = $data;
            $used[$data['id']]    = $data['id'];
        }

        if ($canedit) {
            echo "<div class='firstbloc'>";

            echo "<form aria-label='Problem Changes' name='changeproblem_form$rand' id='changeproblem_form$rand' method='post'
                action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";

            echo "<table class='tab_cadre_fixe' aria-label='Problem Changes Table'>";
            echo "<tr class='tab_bg_2'><th colspan='3'>" . __('Add a change') . "</th></tr>";

            echo "<tr class='tab_bg_2'><td>";
            echo "<input type='hidden' name='problems_id' value='$ID'>";
            Change::dropdown([
               'used'        => $used,
               'entity'      => $problem->getEntityID(),
               'entity_sons' => $problem->isRecursive(),
            ]);
            echo "</td><td class='center'>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td><td>";
            if (Session::haveRight('change', CREATE)) {
                echo "<a href='" . Toolbox::getItemTypeFormURL('Change') . "?problems_id=$ID'>";
                echo __('Create a change from this problem');
                echo "</a>";
            }
            echo "</td></tr></table>";
            Html::closeForm();
            echo "</div>";
        }

        echo "<div class='spaced'>";
        if ($canedit && $numrows) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['num_displayed' => min($_SESSION['glpilist_limit'], $numrows),
                                         'container'     => 'mass' . __CLASS__ . $rand];
            Html::showMassiveActions($massiveactionparams);
        }

        echo "<table class='tab_cadre_fixehov' aria-label='Changes Table'>";
        echo "<tr class='noHover'><th colspan='12'>" . Change::getTypeName($numrows) . "</th>";
        echo "</tr>";
        if ($numrows) {
            Change::commonListHeader(Search::HTML_OUTPUT, 'mass' . __CLASS__ . $rand);
            Session::initNavigateListItems(
                'Change',
                //TRANS : %1$s is the itemtype name,
                //        %2$s is the name of the item (used for headings of a list)
                sprintf(
                    __('%1$s = %2$s'),
                    Problem::getTypeName(1),
                    $problem->fields["name"]
                )
            );

            $i = 0;
            foreach ($changes as $data) {
                Session::addToNavigateListItems('Change', $data["id"]);
                Change::showShort($data['id'], ['row_num'                => $i,
                                                     'type_for_massiveaction' => __CLASS__,
                                                     'id_for_massiveaction'   => $data['linkid']]);
                $i++;
            }
            Change::commonListHeader(Search::HTML_OUTPUT, 'mass' . __CLASS__ . $rand);
        }
        echo "</table>";

        if ($canedit && $numrows) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
        }
        echo "</div>";
    }


    /**
     * Show problems for a change
     *
     * @param $change Change object
    **/
    public static function showForChange(Change $change)
    {
        global $DB;

        $ID = $change->getField('id');
        if (!$change->can($ID, READ)) {
            return false;
        }

        $canedit = $change->canEdit($ID);
        $rand    = mt_rand();

        // $iterator = $DB->request([
        //    'SELECT' => [
        //       'glpi_changes_problems.id AS linkid',
        //       'glpi_problems.*'
        //    ],
        //    'DISTINCT'        => true,
        //    'FROM'            => 'glpi_changes_problems',
        //    'LEFT JOIN'       => [
        //       'glpi_problems' => [
        //          'ON' => [
        //             'glpi_changes_problems' => 'problems_id',
        //             'glpi_problems'         => 'id'
        //          ]
        //       ]
        //    ],
        //    'WHERE'           => [
        //       'glpi_changes_problems.changes_id' => $ID
        //    ],
        //    'ORDERBY'         => 'glpi_problems.name'
        // ]);
        $dql = "SELECT DISTINCT cp.id AS linkid, p
        FROM Itsmng\\Domain\\Entities\\ChangeProblem cp
        LEFT JOIN Itsmng\\Domain\\Entities\\Problem p WITH cp.problem = p.id
        WHERE cp.change = :changes_id
        ORDER BY p.name";

        $result = self::getAdapter()->request($dql, [
            'changes_id' => $ID
        ]);

        $problems = [];
        $used     = [];
        $numrows = count($result);
        // while ($data = $iterator->next()) {
        //     $problems[$data['id']] = $data;
        //     $used[$data['id']]     = $data['id'];
        // }
        foreach ($result as $data) {
            $problems[$data['id']] = $data;
            $used[$data['id']]     = $data['id'];
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
                  __('Add an item') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'changes_id',
                           'value' => $ID
                        ],
                        __('Problem') => [
                           'type' => 'select',
                           'name' => 'problems_id',
                           'itemtype' => Problem::class,
                           'col_lg' => 12,
                           'col_md' => 12,
                           'actions' => getItemActionButtons(['info'], Problem::class),
                        ]
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        if ($canedit && $numrows) {
            $massiveactionparams = [
               'container'     => 'tableForChangeProblem',
               'specific_actions' => [
                  'MassiveAction:purge' => _x('button', 'Delete permanently the relation with selected elements'),
               ],
               'display_arrow' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        $fields = [
           __('Status'),
           _n('Date', 'Dates', 1),
           __('Last update'),
           __('Priority'),
           _n('Requester', 'Requesters', 1),
           __('Assigned'),
           __('Category'),
           __('Title'),
           __('Planification'),
        ];
        if (count($_SESSION["glpiactiveentities"]) > 1) {
            $fields[] = Entity::getTypeName(Session::getPluralNumber());
        }
        $values = [];
        $massive_action = [];
        foreach ($problems as $data) {
            $newValue = [];

            $newValue[] = sprintf(__('%1$s: %2$s'), __('ID'), $data["id"]) . "&nbsp;" . CommonITILObject::getStatusIcon($data["status"]);

            if ($data['status'] == CommonITILObject::CLOSED) {
                $newValue[] = sprintf(__('Closed on %s'), '<br>') . Html::convDateTime($data['closedate']);
            } elseif ($data['status'] == CommonITILObject::SOLVED) {
                $newValue[] = sprintf(__('Solved on %s'), '<br>') . Html::convDateTime($data['solvedate']);
            } elseif ($data['begin_waiting_date']) {
                $newValue[] = sprintf(__('Put on hold on %s'), '<br>') . Html::convDateTime($data['begin_waiting_date']);
            } elseif ($data['time_to_resolve']) {
                $newValue[] = sprintf(__('%1$s: %2$s'), __('Time to resolve'), '<br>') . Html::convDateTime($data['time_to_resolve']);
            } else {
                $newValue[] = sprintf(__('Opened on %s'), '<br>') . Html::convDateTime($data['date']);
            }
            $newValue[] = Html::convDateTime($data["date_mod"]);
            $newValue[] = CommonITILObject::getPriorityName($data["priority"]);

            $cell = "";
            $item = new Problem();
            $item->getFromDB($data['id']);
            foreach ($item->getUsers(CommonITILActor::REQUESTER) as $d) {
                $userdata    = getUserName($d["users_id"], 2);
                $cell .= sprintf(
                    __('%1$s %2$s'),
                    "<span class='b'>" . $userdata['name'] . "</span>",
                    Html::showToolTip(
                        $userdata["comment"],
                        ['link'    => $userdata["link"],
                                            'display' => false]
                    )
                );
                $cell .= "<br>";
            }
            foreach ($item->getGroups(CommonITILActor::REQUESTER) as $d) {
                $cell .= Dropdown::getDropdownName("glpi_groups", $d["groups_id"]);
                $cell .= "<br>";
            }
            $newValue[] = $cell;

            $cell = "";
            $entity = $item->getEntityID();
            $anonymize_helpdesk = Entity::getUsedConfig('anonymize_support_agents', $entity)
               && Session::getCurrentInterface() == 'helpdesk';
            foreach ($item->getUsers(CommonITILActor::ASSIGN) as $d) {
                if ($anonymize_helpdesk) {
                    $cell .= __("Helpdesk");
                } else {
                    $userdata   = getUserName($d["users_id"], 2);
                    $cell .= sprintf(
                        __('%1$s %2$s'),
                        "<span class='b'>" . $userdata['name'] . "</span>",
                        Html::showToolTip(
                            $userdata["comment"],
                            ['link'    => $userdata["link"],
                                                'display' => false]
                        )
                    );
                }
                $cell .= "<br>";
            }
            foreach ($item->getGroups(CommonITILActor::ASSIGN) as $d) {
                if ($anonymize_helpdesk) {
                    $cell .= __("Helpdesk group");
                } else {
                    $cell .= Dropdown::getDropdownName("glpi_groups", $d["groups_id"]);
                }
                $cell .= "<br>";
            }
            foreach ($item->getSuppliers(CommonITILActor::ASSIGN) as $d) {
                $cell .= Dropdown::getDropdownName("glpi_suppliers", $d["suppliers_id"]);
                $cell .= "<br>";
            }
            $newValue[] = $cell;

            $newValue[] = Dropdown::getDropdownName(
                'glpi_itilcategories',
                $item->fields["itilcategories_id"]
            );

            $newValue[] = ($item->canViewItem()) ?
            "<a id='" . $item->getType() . $item->fields["id"] . "$rand' href=\"" . $item->getLinkURL()
            . "\">" . $item->getName() . "</a>" : $item->getName();

            $cell  = '';
            $planned_infos = '';
            $tasktype      = $item->getType() . "Task";
            $plan          = new $tasktype();
            $items         = [];
            $result = $DB->request(
                [
                  'FROM'  => $plan->getTable(),
                  'WHERE' => [
                     $item->getForeignKeyField() => $item->fields['id'],
                  ],
                ]
            );
            foreach ($result as $plan) {
                if (isset($plan['begin']) && $plan['begin']) {
                    $items[$plan['id']] = $plan['id'];
                    $planned_infos .= sprintf(__('From %s') . '<br>', Html::convDateTime($plan['begin']));
                    $planned_infos .= sprintf(__('To %s') . '<br>', Html::convDateTime($plan['end']));
                    if ($plan['users_id_tech']) {
                        $planned_infos .= sprintf(__('By %s') . '<br>', getUserName($plan['users_id_tech']));
                    }
                    $planned_infos .= "<br>";
                }
            }

            $cell = count($items);
            if ($cell) {
                $cell = "<span class='pointer'
                              id='" . $item->getType() . $item->fields["id"] . "planning$rand'>" .
                                  $cell . '</span>';
                $cell = sprintf(
                    __('%1$s %2$s'),
                    $cell,
                    Html::showToolTip(
                        $planned_infos,
                        ['display' => false,
                          'applyto' => $item->getType() .
                                         $item->fields["id"] .
                                         "planning" . $rand]
                    )
                );
            }
            $newValue[] = $cell;

            $values[] = $newValue;
            $massive_action[] = sprintf('item[%s][%s]', self::class, $data['linkid']);
        };
        renderTwigTemplate('table.twig', [
           'id' => 'tableForChangeProblem',
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);
    }
}
