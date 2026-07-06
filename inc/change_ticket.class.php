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
 * Change_Ticket Class
 *
 * Relation between Changes and Tickets
**/
class Change_Ticket extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1   = 'Change';
    public static $items_id_1   = 'changes_id';

    public static $itemtype_2   = 'Ticket';
    public static $items_id_2   = 'tickets_id';



    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    public static function getTypeName($nb = 0)
    {
        return _n('Link Ticket/Change', 'Links Ticket/Change', $nb);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (static::canView()) {
            $nb = 0;
            switch ($item->getType()) {
                case 'Change':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable(
                            'glpi_changes_tickets',
                            ['changes_id' => $item->getID()]
                        );
                    }
                    return self::createTabEntry(Ticket::getTypeName(Session::getPluralNumber()), $nb);

                case 'Ticket':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable(
                            'glpi_changes_tickets',
                            ['tickets_id' => $item->getID()]
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

            case 'Ticket':
                self::showForTicket($item);
                break;
        }
        return true;
    }


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
                $change = new Change();
                $input = $ma->getInput();
                if (isset($input['changes_id']) && $change->getFromDB($input['changes_id'])) {
                    $change->showMassiveSolutionForm($change);
                    echo "<br>";
                    echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                    return true;
                }
                return false;
        }
        return parent::showMassiveActionsSubForm($ma);
    }


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
     * Show tickets for a change
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

        $iterator = $DB->request([
           'SELECT' => [
              'glpi_changes_tickets.id AS linkid',
              'glpi_tickets.*'
           ],
           'DISTINCT'        => true,
           'FROM'            => 'glpi_changes_tickets',
           'LEFT JOIN'       => [
              'glpi_tickets' => [
                 'ON' => [
                    'glpi_changes_tickets'  => 'tickets_id',
                    'glpi_tickets'          => 'id'
                 ]
              ]
           ],
           'WHERE'           => [
              'glpi_changes_tickets.changes_id'   => $ID
           ],
           'ORDERBY'          => [
              'glpi_tickets.name'
           ]
        ]);

        $tickets = [];
        $used    = [];
        $numrows = count($iterator);

        while ($data = $iterator->next()) {
            $tickets[$data['id']] = $data;
            $used[$data['id']]    = $data['id'];
        }

        if ($canedit) {
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
                  __('Add a ticket') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'changes_id',
                           'value' => $ID,
                        ],
                        Ticket::getTypeName(1) => [
                           'type' => 'select',
                           'name' => 'tickets_id',
                           'itemtype' => Ticket::class,
                           'used' => $used,
                           'condition' => Ticket::getOpenCriteria() + [
                              'entities_id' => $change->getEntityID(),
                              'is_recursive' => $change->isRecursive(),
                           ],
                           'col_lg' => 12,
                           'col_md' => 12,
                           'actions' => getItemActionButtons(['info'], Ticket::class),
                        ],
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        $massiveActionContainerID = 'TableFor' . __CLASS__ . $rand;
        if ($canedit && $numrows) {
            $massiveactionparams = [
               'container'        => $massiveActionContainerID,
               'display_arrow'    => false,
               'specific_actions' => [
                  'MassiveAction:purge' => _x('button', 'Delete permanently the relation with selected elements'),
                  __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'solveticket' => __('Solve tickets'),
                  __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'add_task' => __('Add a new task'),
               ],
               'extraparams'      => ['changes_id' => $change->getID()],
               'width'            => 1000,
               'height'           => 500,
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
        foreach ($tickets as $data) {
            $ticket = new Ticket();
            $ticket->getFromDB($data['id']);

            $newValue = [];
            $newValue[] = sprintf(__('%1$s: %2$s'), __('ID'), $data["id"])
               . "&nbsp;" . CommonITILObject::getStatusIcon($data["status"]);

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

            $cell = '';
            foreach ($ticket->getUsers(CommonITILActor::REQUESTER) as $d) {
                $userdata = getUserName($d["users_id"], 2);
                $cell .= sprintf(
                    __('%1$s %2$s'),
                    "<span class='b'>" . $userdata['name'] . "</span>",
                    Html::showToolTip(
                        $userdata["comment"],
                        [
                           'link' => $userdata["link"],
                           'display' => false,
                        ]
                    )
                );
                $cell .= "<br>";
            }
            foreach ($ticket->getGroups(CommonITILActor::REQUESTER) as $d) {
                $cell .= Dropdown::getDropdownName("glpi_groups", $d["groups_id"]);
                $cell .= "<br>";
            }
            $newValue[] = $cell;

            $cell = '';
            $entity = $ticket->getEntityID();
            $anonymize_helpdesk = Entity::getUsedConfig('anonymize_support_agents', $entity)
               && Session::getCurrentInterface() == 'helpdesk';
            foreach ($ticket->getUsers(CommonITILActor::ASSIGN) as $d) {
                if ($anonymize_helpdesk) {
                    $cell .= __("Helpdesk");
                } else {
                    $userdata = getUserName($d["users_id"], 2);
                    $cell .= sprintf(
                        __('%1$s %2$s'),
                        "<span class='b'>" . $userdata['name'] . "</span>",
                        Html::showToolTip(
                            $userdata["comment"],
                            [
                               'link' => $userdata["link"],
                               'display' => false,
                            ]
                        )
                    );
                }
                $cell .= "<br>";
            }
            foreach ($ticket->getGroups(CommonITILActor::ASSIGN) as $d) {
                if ($anonymize_helpdesk) {
                    $cell .= __("Helpdesk group");
                } else {
                    $cell .= Dropdown::getDropdownName("glpi_groups", $d["groups_id"]);
                }
                $cell .= "<br>";
            }
            foreach ($ticket->getSuppliers(CommonITILActor::ASSIGN) as $d) {
                $cell .= Dropdown::getDropdownName("glpi_suppliers", $d["suppliers_id"]);
                $cell .= "<br>";
            }
            $newValue[] = $cell;

            $newValue[] = Dropdown::getDropdownName('glpi_itilcategories', $ticket->fields["itilcategories_id"]);
            $newValue[] = ($ticket->canViewItem())
               ? "<a id='" . $ticket->getType() . $ticket->fields["id"] . "$rand' href=\"" . $ticket->getLinkURL()
                  . "\">" . $ticket->getName() . "</a>"
               : $ticket->getName();

            $cell = '';
            $planned_infos = '';
            $tasktype = $ticket->getType() . "Task";
            $plan = new $tasktype();
            $items = [];
            $result = $DB->request([
               'FROM'  => $plan->getTable(),
               'WHERE' => [
                  $ticket->getForeignKeyField() => $ticket->fields['id'],
               ],
            ]);
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
                              id='" . $ticket->getType() . $ticket->fields["id"] . "planning$rand'>" .
                                  $cell . '</span>';
                $cell = sprintf(
                    __('%1$s %2$s'),
                    $cell,
                    Html::showToolTip(
                        $planned_infos,
                        [
                           'display' => false,
                           'applyto' => $ticket->getType() .
                              $ticket->fields["id"] .
                              "planning" . $rand
                        ]
                    )
                );
            }
            $newValue[] = $cell;

            if (count($_SESSION["glpiactiveentities"]) > 1) {
                $newValue[] = Dropdown::getDropdownName('glpi_entities', $data['entities_id']);
            }

            $values[] = $newValue;
            $massive_action[] = sprintf('item[%s][%s]', self::class, $data['linkid']);
        }

        renderTwigTemplate('table.twig', [
           'id' => $massiveActionContainerID,
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);

        if ($numrows) {
            Session::initNavigateListItems(
                'Ticket',
                //TRANS : %1$s is the itemtype name,
                //        %2$s is the name of the item (used for headings of a list)
                sprintf(
                    __('%1$s = %2$s'),
                    Change::getTypeName(1),
                    $change->fields["name"]
                )
            );
            foreach ($tickets as $data) {
                Session::addToNavigateListItems('Ticket', $data["id"]);
            }
        }
    }


    /**
     * Show changes for a ticket
     *
     * @param $ticket Ticket object
    **/
    public static function showForTicket(Ticket $ticket)
    {
        global $DB;

        $ID = $ticket->getField('id');
        if (!$ticket->can($ID, READ)) {
            return false;
        }

        $canedit = $ticket->canEdit($ID);
        $rand    = mt_rand();

        $iterator = $DB->request([
           'SELECT'          => [
              'glpi_changes_tickets.id AS linkid',
              'glpi_changes.*'
           ],
           'DISTINCT'        => true,
           'FROM'            => 'glpi_changes_tickets',
           'LEFT JOIN'       => [
              'glpi_changes' => [
                 'ON' => [
                    'glpi_changes_tickets'  => 'changes_id',
                    'glpi_changes'          => 'id'
                 ]
              ]
           ],
           'WHERE'           => [
              'glpi_changes_tickets.tickets_id'   => $ID
           ],
           'ORDERBY'          => [
              'glpi_changes.name'
           ]
        ]);

        $changes = [];
        $used    = [];
        $numrows = count($iterator);

        while ($data = $iterator->next()) {
            $changes[$data['id']] = $data;
            $used[$data['id']]    = $data['id'];
        }

        if ($canedit) {
            $options = getOptionForItems(Change::class, Change::getOpenCriteria());
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
                   __('Add a change') => [
                      'visible' => true,
                      'inputs' => [
                         [
                            'type' => 'hidden',
                            'name' => 'tickets_id',
                            'value' => $ID,
                         ],
                         Change::getTypeName() => [
                            'type' => 'select',
                            'name' => 'changes_id',
                            'values' => $options,
                            'actions' => getItemActionButtons(['info'], Change::class)
                         ],
                         '' => Session::haveRight('change', CREATE) ? [
                            'content' => (function () use ($ID) {
                                $ticket = new Ticket();
                                if ($ticket->getFromDB($ID)) {
                                    return "<a href='" . Toolbox::getItemTypeFormURL('Change') .
                                           "?tickets_id={$ID}" .
                                           "&name=" . urlencode((string) $ticket->fields['name']) .
                                           "&content=" . urlencode((string) $ticket->fields['content']) .
                                           "&entities_id=" . $ticket->fields['entities_id'] . "'>" .
                                           __('Create a change from this ticket') . '</a>';
                                }
                                return "<a href='" . Toolbox::getItemTypeFormURL('Change') . "?tickets_id={$ID}'>" .
                                       __('Create a change from this ticket') . '</a>';
                            })(),
                         ] : []
                      ]
                   ]
                ]
             ];
            renderTwigForm($form);
        }

        $massiveActionContainerID = 'TableFor' . __CLASS__ . $rand;
        if ($canedit && $numrows) {
            $massiveactionparams = [
               'container'     => $massiveActionContainerID,
               'display_arrow' => false,
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
        $i = 0;
        foreach ($changes as $data) {
            $change = new Change();
            $change->getFromDB($data['id']);

            $newValue = [
               CommonITILObject::getStatusIcon($data['status']),
               Html::convDateTime($data['date']),
               Html::convDateTime($data['date_mod']),
               Dropdown::getDropdownName('glpi_entities', $data['entities_id']),
               $data['priority'],
            ];
            $newCell = '';
            foreach ($change->getUsers(CommonITILActor::REQUESTER) as $d) {
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

            foreach ($change->getGroups(CommonITILActor::REQUESTER) as $d) {
                $newCell .= Dropdown::getDropdownName("glpi_groups", $d["groups_id"]);
                $newCell .= "<br>";
            }
            $newValue[] = $newCell;

            $newCell = "";

            $entity = $change->getEntityID();
            $anonymize_helpdesk = Entity::getUsedConfig('anonymize_support_agents', $entity)
               && Session::getCurrentInterface() == 'helpdesk';

            foreach ($change->getUsers(CommonITILActor::ASSIGN) as $d) {
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

            foreach ($change->getGroups(CommonITILActor::ASSIGN) as $d) {
                if ($anonymize_helpdesk) {
                    $newCell .= __("Helpdesk group");
                } else {
                    $newCell .= Dropdown::getDropdownName("glpi_groups", $d["groups_id"]);
                }
                $newCell .= "<br>";
            }

            foreach ($change->getSuppliers(CommonITILActor::ASSIGN) as $d) {
                $newCell .= Dropdown::getDropdownName("glpi_suppliers", $d["suppliers_id"]);
                $newCell .= "<br>";
            }
            $newValue[] = $newCell;
            $newValue[] = Dropdown::getDropdownName('glpi_itilcategories', $data['itilcategories_id']);
            $newValue[] = $change->getLink($data['id'], $data['name']);

            $newCell  = '';
            $planned_infos = '';

            $tasktype      = $change->getType() . "Task";
            $plan          = new $tasktype();
            $items         = [];

            $result = $DB->request(
                [
                  'FROM'  => $plan->getTable(),
                  'WHERE' => [
                     $change->getForeignKeyField() => $change->fields['id'],
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
           'id' => $massiveActionContainerID,
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);
    }
}
