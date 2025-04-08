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
 * Relation between Itil items and Projects
 *
 * @since 9.4.0
**/
class Itil_Project extends CommonDBRelation
{
    public static $itemtype_1 = 'itemtype';
    public static $items_id_1 = 'items_id';
    public static $itemtype_2 = 'Project';
    public static $items_id_2 = 'projects_id';

    public static function getTypeName($nb = 0)
    {

        return _n('Link Project/Itil', 'Links Project/Itil', $nb);
    }

    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        $label = '';

        if (static::canView()) {
            $nb = 0;
            switch ($item->getType()) {
                case Change::class:
                case Problem::class:
                case Ticket::class:
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable(
                            self::getTable(),
                            [
                              'itemtype' => $item->getType(),
                              'items_id' => $item->getID(),
                            ]
                        );
                    }
                    $label = self::createTabEntry(Project::getTypeName(Session::getPluralNumber()), $nb);
                    break;

                case Project::class:
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable(self::getTable(), ['projects_id' => $item->getID()]);
                    }
                    $label = self::createTabEntry(
                        _n('Itil item', 'Itil items', Session::getPluralNumber()),
                        $nb
                    );
                    break;
            }
        }

        return $label;
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case Change::class:
            case Problem::class:
            case Ticket::class:
                self::showForItil($item);
                break;

            case Project::class:
                self::showForProject($item);
                break;
        }
        return true;
    }


    /**
     * Show ITIL items for a project.
     *
     * @param Project $project
     * @return void
     **/
    public static function showForProject(Project $project)
    {
        global $DB;

        $ID = $project->getField('id');
        if (!$project->can($ID, READ)) {
            return false;
        }

        $canedit = $project->canEdit($ID);

        /** @var CommonITILObject $itemtype */
        foreach ([Change::class, Problem::class, Ticket::class] as $itemtype) {
            $rand    = mt_rand();

            $selfTable = self::getTable();
            $itemTable = $itemtype::getTable();

            $iterator = $DB->request([
               'SELECT'          => [
                  "$selfTable.id AS linkid",
                  "$itemTable.*"
               ],
               'DISTINCT'        => true,
               'FROM'            => $selfTable,
               'LEFT JOIN'       => [
                  $itemTable => [
                     'FKEY' => [
                        $selfTable => 'items_id',
                        $itemTable => 'id',
                     ],
                  ],
               ],
               'WHERE'           => [
                  "{$selfTable}.itemtype"    => $itemtype,
                  "{$selfTable}.projects_id" => $ID,
                  'NOT'                      => ["{$itemTable}.id" => null],
               ],
               'ORDER'  => "{$itemTable}.name",
            ]);

            $numrows = $iterator->count();

            $items = [];
            $used  = [];
            while ($data = $iterator->next()) {
                $items[$data['id']] = $data;
                $used[$data['id']]  = $data['id'];
            }
            if ($canedit) {
                $label = null;
                switch ($itemtype) {
                    case Change::class:
                        $label = __('Add a change');
                        break;
                    case Problem::class:
                        $label = __('Add a problem');
                        break;
                    case Ticket::class:
                        $label = __('Add a ticket');
                        break;
                }
                $values = getOptionForItems($itemtype::getType());
                foreach ($used as $usedValue) {
                    unset($values[$usedValue]);
                }

                $form = [
                   'action' => Toolbox::getItemTypeFormURL(__CLASS__),
                   'buttons' => [
                      [
                         'name' => 'add',
                         'value' => _x('button', 'Add'),
                         'class' => 'btn btn-secondary',
                      ]
                   ],
                   'content' => [
                      $label => [
                         'visible' => true,
                         'inputs' => [
                            [
                               'type' => 'hidden',
                               'name' => 'itemtype',
                               'value' => $itemtype,
                            ],
                            [
                               'type' => 'hidden',
                               'name' => 'projects_id',
                               'value' => $ID,
                            ],
                            '' => [
                               'type' => 'select',
                               'name' => 'items_id',
                               'values' => $values,
                               'col_lg' => 12,
                               'col_md' => 12,
                            ]
                         ]
                      ]
                   ]
                ];
                renderTwigForm($form);
            }

            $massContainerId = 'tableForitilProject' . $itemtype;
            if ($canedit && $numrows) {
                $massiveactionparams = [
                   'container'     => $massContainerId,
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
            if ($itemtype == 'Ticket') {
                $fields[] = _n('Associated element', 'Associated elements', Session::getPluralNumber());
            }
            if (count($_SESSION["glpiactiveentities"]) > 1) {
                $fields[] = Entity::getTypeName(Session::getPluralNumber());
            }

            $values = [];
            $massive_action = [];
            foreach ($items as $data) {
                $item = new $itemtype();
                $item->getFromDB($data['id']);
                $newValue = [
                   CommonITILObject::getStatusIcon($data['status']),
                ];
                if ($data['status'] == CommonITILObject::CLOSED) {
                    $newValue[] = sprintf(__('Closed on %s'), Html::convDateTime($data['closedate']));
                } elseif ($data['status'] == CommonITILObject::SOLVED) {
                    $newValue[] = sprintf(__('Solved on %s'), Html::convDateTime($data['solvedate']));
                } elseif ($data['begin_waiting_date']) {
                    $newValue[] = sprintf(__('Put on hold on %s'), Html::convDateTime($data['begin_waiting_date']));
                } elseif ($data['time_to_resolve']) {
                    $newValue[] = sprintf(__('%1$s: %2$s'), __('Time to resolve'), Html::convDateTime($data['time_to_resolve']));
                } else {
                    $newValue[] = sprintf(__('Opened on %s'), Html::convDateTime($data['date']));
                }
                $newValue[] = Html::convDateTime($data["date_mod"]);
                $newValue[] = CommonITILObject::getPriorityName($data["priority"]);

                $actors = '';
                foreach ($item->getUsers(CommonITILActor::REQUESTER) as $d) {
                    $userdata    = getUserName($d["users_id"], 2);
                    $actors .= sprintf(
                        __('%1$s %2$s'),
                        "<span class='b'>" . $userdata['name'] . "</span>",
                        Html::showToolTip(
                            $userdata["comment"],
                            ['link'    => $userdata["link"],
                            'display' => false]
                        )
                    );
                    $actors .= "<br>";
                }
                foreach ($item->getGroups(CommonITILActor::REQUESTER) as $d) {
                    $actors .= Dropdown::getDropdownName("glpi_groups", $d["groups_id"]);
                    $actors .= "<br>";
                }
                $newValue[] = $actors;

                $actors = '';
                foreach ($item->getUsers(CommonITILActor::ASSIGN) as $d) {
                    if (Entity::getUsedConfig('anonymize_support_agents', Session::getActiveEntity())) {
                        $actors .= __("Helpdesk");
                    } else {
                        $userdata   = getUserName($d["users_id"], 2);
                        $actors .= sprintf(
                            __('%1$s %2$s'),
                            "<span class='b'>" . $userdata['name'] . "</span>",
                            Html::showToolTip(
                                $userdata["comment"],
                                ['link'    => $userdata["link"],
                                                    'display' => false]
                            )
                        );
                    }

                    $actors .= "<br>";
                }

                foreach ($item->getGroups(CommonITILActor::ASSIGN) as $d) {
                    if (Entity::getUsedConfig('anonymize_support_agents', Session::getActiveEntity())) {
                        $actors .= __("Helpdesk group");
                    } else {
                        $actors .= Dropdown::getDropdownName("glpi_groups", $d["groups_id"]);
                    }
                    $actors .= "<br>";
                }

                foreach ($item->getSuppliers(CommonITILActor::ASSIGN) as $d) {
                    $actors .= Dropdown::getDropdownName("glpi_suppliers", $d["suppliers_id"]);
                    $actors .= "<br>";
                }
                $newValue[] = $actors;

                $newValue[] = Dropdown::getDropdownName('glpi_itilcategories', $data["itilcategories_id"]);

                $name = $item->getName();
                if ($item->canViewItem()) {
                    $name = "<a id='" . $item->getType() . $item->fields["id"] . "$rand' href=\"" . $item->getLinkURL()
                                   . "\">$name</a>";
                }
                $newValue[] = $name;

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
                        $planned_infos .= sprintf(
                            __('From %s') .
                                                   ($p['output_type'] == Search::HTML_OUTPUT ? '<br>' : ''),
                            Html::convDateTime($plan['begin'])
                        );
                        $planned_infos .= sprintf(
                            __('To %s') .
                                                   ($p['output_type'] == Search::HTML_OUTPUT ? '<br>' : ''),
                            Html::convDateTime($plan['end'])
                        );
                        if ($plan['tech_users_id']) {
                            $planned_infos .= sprintf(
                                __('By %s') .
                                                       ($p['output_type'] == Search::HTML_OUTPUT ? '<br>' : ''),
                                getUserName($plan['tech_users_id'])
                            );
                        }
                        $planned_infos .= "<br>";
                    }
                }

                $newValue[] = $planned_infos;
                $newValue[] = count($items);

                $values[] = $newValue;
                $massive_action[] = sprintf('item[%s][%s]', Itil_Project::class, $data['id']);
            }
            renderTwigTemplate('table.twig', [
               'id' => $massContainerId,
               'fields' => $fields,
               'values' => $values,
               'massive_action' => $massive_action,
            ]);
            echo '<div class="spaced">';
            echo '<table class="tab_cadre_fixehov" aria-label="Item Detail">';
            if ($numrows) {
                $i = 0;
                foreach ($items as $data) {
                    $itemtype::showShort(
                        $data['id'],
                        [
                          'row_num'                => $i,
                          'type_for_massiveaction' => __CLASS__,
                          'id_for_massiveaction'   => $data['linkid']
                        ]
                    );
                    $i++;
                }
            }
            echo '</table>';

            if ($canedit && $numrows) {
                $massiveactionparams['ontop'] = false;
                Html::showMassiveActions($massiveactionparams);
                Html::closeForm();
            }
            echo '</div>';
        }
    }

    /**
     * Show projects for an ITIL item.
     *
     * @param CommonITILObject $itil
     * @return void
    **/
    public static function showForItil(CommonITILObject $itil)
    {
        global $DB;

        $ID = $itil->getField('id');
        if (!$itil->can($ID, READ)) {
            return false;
        }

        $canedit = $itil->canEdit($ID);
        $rand    = mt_rand();

        $selfTable = self::getTable();
        $projectTable = Project::getTable();

        $iterator = $DB->request([
           'SELECT'          => [
              "$selfTable.id AS linkid",
              "$projectTable.*"
           ],
           'DISTINCT'        => true,
           'FROM'            => $selfTable,
           'LEFT JOIN'       => [
              $projectTable => [
                 'FKEY' => [
                    $selfTable    => 'projects_id',
                    $projectTable => 'id',
                 ],
              ],
           ],
           'WHERE'           => [
              "{$selfTable}.itemtype" => $itil->getType(),
              "{$selfTable}.items_id" => $ID,
              'NOT'                   => ["{$projectTable}.id" => null],
           ],
           'ORDER'  => "{$projectTable}.name",
        ]);

        $numrows = $iterator->count();

        $projects = [];
        $used     = [];
        while ($data = $iterator->next()) {
            $projects[$data['id']] = $data;
            $used[$data['id']]     = $data['id'];
        }

        if (
            $canedit
            && !in_array($itil->fields['status'], array_merge(
                $itil->getClosedStatusArray(),
                $itil->getSolvedStatusArray()
            ))
        ) {
            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  [
                     'name' => 'add',
                     'value' => _x('button', 'Add'),
                     'class' => 'btn btn-secondary',
                  ]
               ],
               'content' => [
                  __('Add a project') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'itemtype',
                           'value' => $itil->getType(),
                        ],
                        [
                           'type' => 'hidden',
                           'name' => 'items_id',
                           'value' => $ID,
                        ],
                        '' => [
                           'type' => 'select',
                           'name' => 'projects_id',
                           'values' => getOptionForItems(Project::class, [], true, false, $used),
                           'col_lg' => 12,
                           'col_md' => 12,
                        ]
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        $massContainerId = 'TableForProject' . $itil->getType();
        if ($canedit && $numrows) {
            $massiveactionparams = [
               'container'     => $massContainerId,
               'specific_actions' => [
                  'MassiveAction:purge' => _x('button', 'Delete permanently the relation with selected elements'),
               ],
               'display_arrow' => false,
               'is_deleted' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        $fields = [
           __('ID'),
           __('Status'),
           __('Date'),
           __('Last update'),
           __('Entity'),
           __('Priority'),
           __('Manager'),
           __('Manager Group'),
           __('Name'),
        ];
        $values = [];
        $massive_action = [];
        foreach ($projects as $data) {
            $project = new Project();
            $project->getFromDB($data['id']);
            $newValue = [
               $data['id'],
               Dropdown::getDropdownName('glpi_projectstates', $data["projectstates_id"]),
               Html::convDateTime($data['date']),
               Html::convDateTime($data['date_mod']),
               Dropdown::getDropdownName('glpi_entities', $data['entities_id']),
               $data['priority'],
               Dropdown::getDropdownName('glpi_users', $data['users_id']),
               Dropdown::getDropdownName('glpi_groups', $data['groups_id']),
               $project->getLink(),
            ];
            $values[] = $newValue;
            $massive_action[] = sprintf('item[%s][%s]', Itil_Project::class, $data['linkid']);
        }
        renderTwigTemplate('table.twig', [
           'id' => $massContainerId,
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);
    }

    /**
     * Duplicate all itil items from a project template to his clone.
     *
     * @deprecated 9.5
     *
     * @param integer $oldid  ID of the item to clone
     * @param integer $newid  ID of the item cloned
     *
     * @return void
     **/
    public static function cloneItilProject($oldid, $newid)
    {
        global $DB;

        Toolbox::deprecated('Use clone');
        $itil_items = $DB->request(self::getTable(), ['WHERE'  => ['projects_id' => $oldid]]);
        foreach ($itil_items as $data) {
            unset($data['id']);
            $data['projects_id'] = $newid;
            $data                = Toolbox::addslashes_deep($data);

            $itil_project = new Itil_Project();
            $itil_project->add($data);
        }
    }
}
