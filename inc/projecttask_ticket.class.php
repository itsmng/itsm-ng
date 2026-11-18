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
 * ProjectTask_Ticket Class
 *
 * Relation between ProjectTasks and Tickets
 *
 * @since 0.85
 **/
class ProjectTask_Ticket extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1   = 'ProjectTask';
    public static $items_id_1   = 'projecttasks_id';

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
        return _n('Link Ticket/Project task', 'Links Ticket/Project task', $nb);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (static::canView()) {
            $nb = 0;
            switch ($item->getType()) {
                case 'ProjectTask':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = self::countForItem($item);
                    }
                    return self::createTabEntry(Ticket::getTypeName(Session::getPluralNumber()), $nb);

                case 'Ticket':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = self::countForItem($item);
                    }
                    return self::createTabEntry(ProjectTask::getTypeName(Session::getPluralNumber()), $nb);
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'ProjectTask':
                self::showForProjectTask($item);
                break;

            case 'Ticket':
                self::showForTicket($item);
                break;
        }
        return true;
    }


    /**
     * Get total duration of tickets linked to a project task
     *
     * @param $projecttasks_id    integer    $projecttasks_id ID of the project task
     *
     * @return integer total actiontime
     **/
    public static function getTicketsTotalActionTime($projecttasks_id)
    {
        global $DB;

        $iterator = $DB->request([
           'SELECT'       => new QueryExpression('SUM(glpi_tickets.actiontime) AS duration'),
           'FROM'         => self::getTable(),
           'INNER JOIN'   => [
              'glpi_tickets' => [
                 'FKEY'   => [
                    self::getTable()  => 'tickets_id',
                    'glpi_tickets'    => 'id'
                 ]
              ]
           ],
           'WHERE'        => ['projecttasks_id' => $projecttasks_id]
        ]);

        if ($row = $iterator->next()) {
            return $row['duration'];
        }
        return 0;
    }


    /**
     * Show tickets for a projecttask
     *
     * @param $projecttask ProjectTask object
     **/
    public static function showForProjectTask(ProjectTask $projecttask)
    {
        $ID = $projecttask->getID();
        if (!$projecttask->can($ID, READ)) {
            return false;
        }
        $canedit = $projecttask->canEdit($ID);
        $tickets = [];
        $used = [];
        $selection = [];
        foreach (self::getListForItem($projecttask) as $data) {
            $tickets[$data['id']] = $data;
            $used[] = $data['id'];
            $selection[$data['id']] = 'item[ProjectTask_Ticket][' . $data['linkid'] . ']';
        }
        if ($canedit) {
            $condition = [
                'NOT' => ['glpi_tickets.status' => array_merge(Ticket::getSolvedStatusArray(), Ticket::getClosedStatusArray())],
                'is_recursive' => $projecttask->isRecursive(),
            ];
            renderTwigForm([
                'action' => Toolbox::getItemTypeFormURL(__CLASS__),
                'buttons' => [[
                    'type' => 'submit', 'name' => 'add', 'value' => _x('button', 'Add'),
                    'class' => 'btn btn-primary', 'icon' => 'fas fa-plus',
                ]],
                'content' => [__('Add a ticket') => [
                    'visible' => true,
                    'inputs' => [
                        ['type' => 'hidden', 'name' => 'projecttasks_id', 'value' => $ID],
                        Ticket::getTypeName(1) => [
                            'type' => 'select', 'name' => 'tickets_id', 'value' => 0,
                            ...getAjaxDropdownOptionsByEntity('Ticket', $projecttask->getEntityID(), $condition, $used),
                            'col_lg' => 8, 'col_md' => 8,
                        ],
                        '' => ['content' => '<a class="btn btn-outline-primary" href="'
                            . htmlspecialchars(Ticket::getFormURL() . '?_projecttasks_id=' . $ID, ENT_QUOTES, 'UTF-8', false)
                            . '"><i class="fas fa-plus me-2" aria-hidden="true"></i>' . __('Create a ticket from this task') . '</a>',
                            'col_lg' => 4, 'col_md' => 4],
                    ],
                ]],
            ]);
        }
        $canselect = $canedit && (Ticket::canDelete() || Session::haveRight(Ticket::$rightname, UPDATE));
        if ($canselect && $tickets) {
            Html::showMassiveActions([
                'container' => 'itilForProjectTask' . $ID . 'TicketDirect',
                'num_displayed' => count($tickets),
                'display_arrow' => false,
            ]);
        }
        Ticket::showItemListTable($tickets, $projecttask, false, $canselect ? $selection : null);
    }


    /**
     * Show projecttasks for a ticket
     *
     * @param $ticket Ticket object
     **/
    public static function showForTicket(Ticket $ticket)
    {
        global $DB, $CFG_GLPI;

        $ID = $ticket->getField('id');
        if (!$ticket->can($ID, READ)) {
            return false;
        }

        $canedit = $ticket->canEdit($ID);
        $rand    = mt_rand();

        $iterator = self::getListForItem($ticket);
        $numrows = count($iterator);

        $pjtasks = [];
        $used    = [];
        while ($data = $iterator->next()) {
            $pjtasks[$data['id']] = $data;
            $used[$data['id']]    = $data['id'];
        }

        if (
            $canedit
            && !in_array($ticket->fields['status'], array_merge(
                $ticket->getClosedStatusArray(),
                $ticket->getSolvedStatusArray()
            ))
        ) {
            $finished_states_it = $DB->request(
                [
                  'SELECT' => ['id'],
                  'FROM'   => ProjectState::getTable(),
                  'WHERE'  => [
                     'is_finished' => 1
                  ],
                ]
            );
            $finished_states_ids = [];
            foreach ($finished_states_it as $finished_state) {
                $finished_states_ids[] = $finished_state['id'];
            }

            $usedValues = json_encode($used);
            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  'add' => [
                     'name' => 'add',
                     'value' => _sx('button', 'Add'),
                     'class' => 'btn btn-secondary'
                  ]
               ],
               'content' => [
                  __('Add a project task') => [
                     'visible' => true,
                     'inputs' => [
                        'projecttasks_id' => [
                           'type' => 'hidden',
                           'name' => 'tickets_id',
                           'value' => $ID
                        ],
                        Project::getTypeName() => [
                           'type' => 'select',
                           'name' => 'projects_id',
                           'id' => 'DropdownForProjectIdProjectTask',
                           'itemtype' => Project::class,
                           'conditions' => [ 'NOT' => ['projectstates_id' => $finished_states_ids] ],
                           'entity' => $ticket->getEntityID(),
                           'col_lg' => 6,
                           'hooks' => [
                              'change' => <<<JS
                              var projects_id = $('#DropdownForProjectIdProjectTask').val();
                              $.ajax({
                              url: '{$CFG_GLPI['root_doc']}/ajax/dropdownProjectTaskTicket.php',
                              method: 'POST',
                              data: {
                                 projects_id: projects_id,
                                 entity_restrict: {$ticket->getEntityID()},
                                 used: $usedValues,
                              },
                              success: function(data) {
                                 setAjaxDropdownOptions('#DropdownForProjectTaskIdProjectTask', JSON.parse(data));
                                 $('#DropdownForProjectTaskIdProjectTask').prop('disabled', projects_id == 0);
                              }});
                           JS,
                           ],
                           'actions' => getItemActionButtons(['info'], Project::class),
                        ],
                        ProjectTask::getTypeName() => [
                           'type' => 'select',
                           'id' => 'DropdownForProjectTaskIdProjectTask',
                           'name' => 'projecttasks_id',
                           'disabled' => '',
                           'col_lg' => 6,
                           'actions' => getItemActionButtons(['info'], ProjectTask::class),
                        ]
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        $fields = [
           Project::getTypeName(Session::getPluralNumber()),
           ProjectTask::getTypeName(Session::getPluralNumber()),
           _n('Type', 'Types', 1),
           __('Status'),
           __('Percent done'),
           __('Planned start date'),
           __('Planned end date'),
           __('Planned duration'),
           __('Effective duration'),
           __('Father')
        ];
        $values = [];
        if ($numrows) {
            $iterator = $DB->request([
               'SELECT'    => [
                  'glpi_projecttasks.*',
                  'glpi_projecttasktypes.name AS tname',
                  'glpi_projectstates.name AS sname',
                  'glpi_projectstates.color',
                  'father.name AS fname',
                  'father.id AS fID',
                  'glpi_projects.name AS projectname',
                  'glpi_projects.content AS projectcontent'
               ],
               'FROM'      => 'glpi_projecttasks',
               'LEFT JOIN' => [
                  'glpi_projecttasktypes' => [
                     'ON' => [
                        'glpi_projecttasktypes' => 'id',
                        'glpi_projecttasks'     => 'projecttasktypes_id'
                     ]
                  ],
                  'glpi_projectstates'    => [
                     'ON' => [
                        'glpi_projectstates' => 'id',
                        'glpi_projecttasks'  => 'projectstates_id'
                     ]
                  ],
                  'glpi_projecttasks AS father' => [
                     'ON' => [
                        'father'             => 'id',
                        'glpi_projecttasks'  => 'projecttasks_id'
                     ]
                  ],
                  'glpi_projecttasks_tickets'   => [
                     'ON' => [
                        'glpi_projecttasks_tickets'   => 'projecttasks_id',
                        'glpi_projecttasks'           => 'id'
                     ]
                  ],
                  'glpi_projects'               => [
                     'ON' => [
                        'glpi_projecttasks'  => 'projects_id',
                        'glpi_projects'      => 'id'
                     ]
                  ]
               ],
               'WHERE'     => [
                  'glpi_projecttasks_tickets.tickets_id' => $ID
               ],
            ]);
            while ($data = $iterator->next()) {
                $newValue = [];
                $rand = mt_rand();
                $link = "<a id='Project" . $data["projects_id"] . $rand . "' href='" .
                   Project::getFormURLWithID($data['projects_id']) . "'>" . $data['projectname'] .
                   (empty($data['projectname']) ? "(" . $data['projects_id'] . ")" : "") . "</a>";
                $newValue[] = sprintf(
                    __('%1$s %2$s'),
                    $link,
                    Html::showToolTip(
                        $data['projectcontent'],
                        [
                          'display' => false,
                          'applyto' => "Project" . $data["projects_id"] . $rand
                        ]
                    )
                );
                $link = "<a id='ProjectTask" . $data["id"] . $rand . "' href='" .
                   ProjectTask::getFormURLWithID($data['id']) . "'>" . $data['name'] .
                   (empty($data['name']) ? "(" . $data['id'] . ")" : "") . "</a>";
                $newValue[] = sprintf(
                    __('%1$s %2$s'),
                    $link,
                    Html::showToolTip(
                        $data['content'],
                        [
                          'display' => false,
                          'applyto' => "ProjectTask" . $data["id"] . $rand
                        ]
                    )
                );
                $newValue[] = $data['tname'];
                $newValue[] = $data['sname'];
                $newValue[] = Dropdown::getValueWithUnit($data["percent_done"], "%");
                $newValue[] = Html::convDateTime($data['plan_start_date']);
                $newValue[] = Html::convDateTime($data['plan_end_date']);
                $newValue[] = Html::timestampToString($data['planned_duration'], false);
                $newValue[] = Html::timestampToString(
                    ProjectTask::getTotalEffectiveDuration($data['id']),
                    false
                );
                if ($data['projecttasks_id'] > 0) {
                    $father = Dropdown::getDropdownName('glpi_projecttasks', $data['projecttasks_id']);
                    $newValue[] = "<a id='ProjectTask" . $data["projecttasks_id"] . $rand . "' href='" .
                       ProjectTask::getFormURLWithID($data['projecttasks_id']) . "'>" . $father .
                       (empty($father) ? "(" . $data['projecttasks_id'] . ")" : "") . "</a>";
                }
                $values[] = $newValue;
            }
        };
        renderTwigTemplate('table.twig', [
           'fields' => $fields,
           'values' => $values
        ]);
    }
}
