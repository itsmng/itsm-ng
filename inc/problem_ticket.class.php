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

class Problem_Ticket extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1   = 'Problem';
    public static $items_id_1   = 'problems_id';

    public static $itemtype_2   = 'Ticket';
    public static $items_id_2   = 'tickets_id';


    /**
     * @since 0.84
    **/
    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    public static function getTypeName($nb = 0)
    {
        return _n('Link Ticket/Problem', 'Links Ticket/Problem', $nb);
    }


    /**
     * @see CommonGLPI::getTabNameForItem()
    **/
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (static::canView()) {
            $nb = 0;
            switch ($item->getType()) {
                case 'Ticket':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $problems = self::getTicketProblemsData($item->getID());
                        $nb = count($problems);
                    }
                    return self::createTabEntry(Problem::getTypeName(Session::getPluralNumber()), $nb);

                case 'Problem':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $tickets = self::getProblemTicketsData($item->getID());
                        $nb = count($tickets);
                    }
                    return self::createTabEntry(Ticket::getTypeName(Session::getPluralNumber()), $nb);
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'Ticket':
                self::showForTicket($item);
                break;

            case 'Problem':
                self::showForProblem($item);
                break;
        }
        return true;
    }


    /**
     * @since 0.84
    **/
    public function post_addItem()
    {
        global $CFG_GLPI;

        $donotif = !isset($this->input['_disablenotif']) && $CFG_GLPI["use_notifications"];

        if ($donotif) {
            $problem = new Problem();
            if ($problem->getFromDB($this->input["problems_id"])) {
                $options = [];
                NotificationEvent::raiseEvent("new", $problem, $options);
            }
        }

        parent::post_addItem();
    }


    /**
     * @since 0.84
    **/
    public function post_deleteFromDB()
    {
        global $CFG_GLPI;

        $donotif = !isset($this->input['_disablenotif']) && $CFG_GLPI["use_notifications"];

        if ($donotif) {
            $problem = new Problem();
            if ($problem->getFromDB($this->fields["problems_id"])) {
                $options = [];
                NotificationEvent::raiseEvent("delete", $problem, $options);
            }
        }

        parent::post_deleteFromDB();
    }


    /**
     * @since 0.85
     *
     * @see CommonDBTM::showMassiveActionsSubForm()
    **/
    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        switch ($ma->getAction()) {
            case 'add_task':
                $tasktype = 'TicketTask';
                if ($ttype = getItemForItemtype($tasktype)) {
                    $ttype->showMassiveActionAddTaskForm();
                    return true;
                }
                return false;

            case "solveticket":
                $problem = new Problem();
                $input = $ma->getInput();
                if (isset($input['problems_id']) && $problem->getFromDB($input['problems_id'])) {
                    $problem->showMassiveSolutionForm($problem);
                    echo "<br>";
                    echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                    return true;
                }
                return false;
        }
        return parent::showMassiveActionsSubForm($ma);
    }


    /**
     * @since 0.85
     *
     * @see CommonDBTM::processMassiveActionsForOneItemtype()
    **/
    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {

        switch ($ma->getAction()) {
            case 'add_task':
                if (!($task = getItemForItemtype('TicketTask'))) {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                    break;
                }
                $ticket = new Ticket();
                $field = $ticket->getForeignKeyField();

                $input = $ma->getInput();

                foreach ($ids as $id) {
                    if ($item->can($id, READ)) {
                        if ($ticket->getFromDB($item->fields['tickets_id'])) {
                            $input2 = [$field              => $item->fields['tickets_id'],
                                         'taskcategories_id' => $input['taskcategories_id'],
                                         'actiontime'        => $input['actiontime'],
                                         'content'           => $input['content']];
                            if ($task->can(-1, CREATE, $input2)) {
                                if ($task->add($input2)) {
                                    $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                                } else {
                                    $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                                    $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                                }
                            } else {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
                                $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
                            }
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
                            $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
                        }
                    }
                }
                return;

            case 'solveticket':
                $input  = $ma->getInput();
                $ticket = new Ticket();
                foreach ($ids as $id) {
                    if ($item->can($id, READ)) {
                        if (
                            $ticket->getFromDB($item->fields['tickets_id'])
                            && $ticket->canSolve()
                        ) {
                            $solution = new ITILSolution();
                            $added = $solution->add([
                               'itemtype'  => $ticket->getType(),
                               'items_id'  => $ticket->getID(),
                               'solutiontypes_id'   => $input['solutiontypes_id'],
                               'content'            => $input['content']
                            ]);

                            if ($added) {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                            } else {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                                $ma->addMessage($ticket->getErrorMessage(ERROR_ON_ACTION));
                            }
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
                            $ma->addMessage($ticket->getErrorMessage(ERROR_RIGHT));
                        }
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
                        $ma->addMessage($ticket->getErrorMessage(ERROR_RIGHT));
                    }
                }
                return;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }


    /**
     * Show tickets for a problem
     *
     * @param $problem Problem object
    **/
    public static function showForProblem(Problem $problem)
    {

        $ID = $problem->getField('id');

        if (!static::canView() || !$problem->can($ID, READ)) {
            return false;
        }

        $canedit = $problem->canEdit($ID);

        $rand = mt_rand();

        $tickets = self::getProblemTicketsData($ID);
        $used    = [];
        $numrows = count($tickets);
        foreach ($tickets as $ticket) {
            $used[$ticket['id']] = $ticket['id'];
        }

        if ($canedit) {
            echo "<div class='firstbloc'>";
            echo "<form aria-label='Add a ticket' name='changeticket_form$rand' id='changeticket_form$rand' method='post'
               action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";

            echo "<table class='tab_cadre_fixe' aria-label='Add a ticket'>";
            echo "<tr class='tab_bg_2'><th colspan='2'>" . __('Add a ticket') . "</th></tr>";

            echo "<tr class='tab_bg_2'><td class='right'>";
            echo "<input type='hidden' name='problems_id' value='$ID'>";
            Ticket::dropdown([
               'used'        => $used,
               'entity'      => $problem->getEntityID(),
               'entity_sons' => $problem->isRecursive(),
               'displaywith' => ['id'],
            ]);
            echo "</td><td class='center'>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td></tr>";

            echo "</table>";
            Html::closeForm();
            echo "</div>";
        }

        echo "<div class='spaced'>";
        if ($canedit && $numrows) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['num_displayed'    => min($_SESSION['glpilist_limit'], $numrows),
                                         'container'        => 'mass' . __CLASS__ . $rand,
                                         'specific_actions' => ['purge'
                                                                       => _x(
                                                                           'button',
                                                                           'Delete permanently'
                                                                       ),
                                                                     __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'solveticket'
                                                                       => __('Solve tickets'),
                                                                     __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'add_task'
                                                                       => __('Add a new task')],
                                         'extraparams'      => ['problems_id' => $problem->getID()],
                                         'width'            => 1000,
                                         'height'           => 500];
            Html::showMassiveActions($massiveactionparams);
        }
        echo "<table class='tab_cadre_fixehov' aria-label='problem'>";
        echo "<tr class='noHover'><th colspan='12'>" . Ticket::getTypeName($numrows) . "</th>";
        echo "</tr>";
        if ($numrows) {
            Ticket::commonListHeader(Search::HTML_OUTPUT, 'mass' . __CLASS__ . $rand);
            Session::initNavigateListItems(
                'Ticket',
                //TRANS : %1$s is the itemtype name,
                //        %2$s is the name of the item (used for headings of a list)
                sprintf(
                    __('%1$s = %2$s'),
                    Problem::getTypeName(1),
                    $problem->fields["name"]
                )
            );

            $i = 0;
            foreach ($tickets as $data) {
                Session::addToNavigateListItems('Ticket', $data["id"]);
                Ticket::showShort(
                    $data['id'],
                    [
                      'row_num'                => $i,
                      'type_for_massiveaction' => __CLASS__,
                      'id_for_massiveaction'   => $data['linkid']
                    ]
                );
                $i++;
            }
            Ticket::commonListHeader(Search::HTML_OUTPUT, 'mass' . __CLASS__ . $rand);
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
     * Show problems for a ticket
     *
     * @param $ticket Ticket object
    **/
    public static function showForTicket(Ticket $ticket)
    {
        global $DB;

        $ID = $ticket->getField('id');

        if (!static::canView() || !$ticket->can($ID, READ)) {
            return false;
        }

        $canedit = $ticket->can($ID, UPDATE);

        $rand = mt_rand();

        $problems = self::getTicketProblemsData($ID);
        $used     = [];
        $numrows  = count($problems);
        foreach ($problems as $problem) {
            $used[$problem['id']] = $problem['id'];
        }
        if ($canedit) {
            $options = getOptionForItems(Problem::class, Problem::getOpenCriteria());
            foreach ($used as $id) {
                if (isset($options[$id])) {
                    unset($options[$id]);
                }
            }
            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  [
                     'name' => 'add',
                     'value' => _sx('button', 'Add'),
                     'class' => 'btn btn-secondary',
                  ]
               ],
               'content' => [
                  __('Add a Problem') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'tickets_id',
                           'value' => $ID,
                        ],
                        Problem::getTypeName() => [
                           'type' => 'select',
                           'name' => 'problems_id',
                           'values' => $options,
                           'actions' => getItemActionButtons(['info'], Problem::class)
                        ],
                        '' => Session::haveRight('problem', CREATE) ? [
                           'content' => "<a href='" . Toolbox::getItemTypeFormURL('Problem') . "?tickets_id={$ID}'>"
                              . __('Create a problem from this ticket') . '</a>',
                        ] : []
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        $massContainerId = 'TableFor' . __CLASS__ . $rand;
        if ($canedit && $numrows) {
            $massiveactionparams = [
               'container'      => $massContainerId,
               'display_arrow'  => false,
               'specific_actions' => [
                  'MassiveAction:purge' => _x('button', 'Delete permanently the relation with selected elements'),
               ],
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        $fields = [
           __('Status'),
           __('Date'),
           __('Last update'),
           __('Entities'),
           __('Priority'),
           __('Requester'),
           __('Assigned'),
           __('Category'),
           __('Title'),
           __('Planification'),
        ];
        $values = [];
        $massive_action = [];
        foreach ($problems as $data) {
            $problem = new Problem();
            $problem->getFromDB($data['id']);

            $newValue = [
               CommonITILObject::getStatusIcon($data['status']),
               Html::convDateTime($data['date']),
               Html::convDateTime($data['date_mod']),
               Dropdown::getDropdownName('glpi_entities', $data['entities_id']),
               Ticket::getPriorityName($data['priority']),
            ];
            $newCell = '';
            foreach ($problem->getUsers(CommonITILActor::REQUESTER) as $d) {
                $userdata    = getUserName($d["users_id"], 2);
                $newCell .= sprintf(
                    __('%1$s %2$s'),
                    "<span class='b'>" . $userdata['name'] . "</span>",
                    Html::showToolTip(
                        $userdata["comment"],
                        ['link'    => $userdata["link"],
                                            'display' => false]
                    )
                );
                $newCell .= "<br>";
            }

            foreach ($problem->getGroups(CommonITILActor::REQUESTER) as $d) {
                $newCell .= Dropdown::getDropdownName("glpi_groups", $d["groups_id"]);
                $newCell .= "<br>";
            }
            $newValue[] = $newCell;

            $newCell = "";

            $entity = $problem->getEntityID();
            $anonymize_helpdesk = Entity::getUsedConfig('anonymize_support_agents', $entity)
               && Session::getCurrentInterface() == 'helpdesk';

            foreach ($problem->getUsers(CommonITILActor::ASSIGN) as $d) {
                if ($anonymize_helpdesk) {
                    $newCell .= __("Helpdesk");
                } else {
                    $userdata   = getUserName($d["users_id"], 2);
                    $newCell .= sprintf(
                        __('%1$s %2$s'),
                        "<span class='b'>" . $userdata['name'] . "</span>",
                        Html::showToolTip(
                            $userdata["comment"],
                            ['link'    => $userdata["link"],
                                                'display' => false]
                        )
                    );
                }

                $newCell .= "<br>";
            }

            foreach ($problem->getGroups(CommonITILActor::ASSIGN) as $d) {
                if ($anonymize_helpdesk) {
                    $newCell .= __("Helpdesk group");
                } else {
                    $newCell .= Dropdown::getDropdownName("glpi_groups", $d["groups_id"]);
                }
                $newCell .= "<br>";
            }

            foreach ($problem->getSuppliers(CommonITILActor::ASSIGN) as $d) {
                $newCell .= Dropdown::getDropdownName("glpi_suppliers", $d["suppliers_id"]);
                $newCell .= "<br>";
            }
            $newValue[] = $newCell;
            $newValue[] = Dropdown::getDropdownName('glpi_itilcategories', $data['itilcategories_id']);
            $newValue[] = $problem->getLink($data['id'], $data['name']);

            $newCell  = '';
            $planned_infos = '';

            $tasktype      = $problem->getType() . "Task";
            $plan          = new $tasktype();
            $items         = [];

            $result = $DB->request(
                [
                  'FROM'  => $plan->getTable(),
                  'WHERE' => [
                     $problem->getForeignKeyField() => $problem->fields['id'],
                  ],
                ]
            );
            foreach ($result as $plan) {
                if (isset($plan['begin']) && $plan['begin']) {
                    $items[$plan['id']] = $plan['id'];
                    $planned_infos .= sprintf(__('From %s') . ('<br>'), Html::convDateTime($plan['begin']));
                    $planned_infos .= sprintf(__('To %s') . ('<br>'), Html::convDateTime($plan['end']));
                    if ($plan['users_id_tech']) {
                        $planned_infos .= sprintf(
                            __('By %s') . ('<br>'),
                            getUserName($plan['users_id_tech'])
                        );
                    }
                    $planned_infos .= "<br>";
                }
            }

            $newCell = count($items);
            if ($newCell) {
                $newCell = "<span class='pointer'
                              id='" . $change->getType() . $change->fields["id"] . "planning$rand'>" .
                                  $newCell . '</span>';
                $newCell = sprintf(
                    __('%1$s %2$s'),
                    $newCell,
                    Html::showToolTip(
                        $planned_infos,
                        [
                          'display' => false,
                          'applyto' => $change->getType() .
                          $change->fields["id"] .
                          "planning" . $rand
                        ]
                    )
                );
            }
            $newValue[] = $newCell;
            $values[] = $newValue;
            $massive_action[] = sprintf('item[%s][%s]', self::class, $data['linkid']);
        }

        renderTwigTemplate('table.twig', [
           'id' => $massContainerId,
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);
    }

    /**
     * Returns problems data for given ticket.
     * Returned data is usable by `Problem::showShort()` method.
     *
     * @param integer $tickets_id
     *
     * @return array
     */
    private static function getTicketProblemsData($tickets_id)
    {

        $ticket = new Ticket();
        $ticket->fields['id'] = $tickets_id;
        $iterator = self::getListForItem($ticket);

        $problems = [];
        foreach ($iterator as $data) {
            $problem = new Problem();
            $problem->getFromDB($data['id']);
            if ($problem->canViewItem()) {
                $problems[$data['id']] = $data;
            }
        }

        return $problems;
    }

    /**
     * Returns tickets data for given problem.
     * Returned data is usable by `Ticket::showShort()` method.
     *
     * @param integer $problems_id
     *
     * @return array
     */
    private static function getProblemTicketsData($problems_id)
    {

        $problem = new Problem();
        $problem->fields['id'] = $problems_id;
        $iterator = self::getListForItem($problem);

        $tickets = [];
        foreach ($iterator as $data) {
            $ticket = new Ticket();
            $ticket->getFromDB($data['id']);
            if ($ticket->canViewItem()) {
                $tickets[$data['id']] = $data;
            }
        }

        return $tickets;
    }
}
