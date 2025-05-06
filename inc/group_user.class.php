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
 * Group_User Class
 *
 *  Relation between Group and User
**/
class Group_User extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1                 = 'User';
    public static $items_id_1                 = 'users_id';

    public static $itemtype_2                 = 'Group';
    public static $items_id_2                 = 'groups_id';

    /**
    * Check if a user belongs to a group
    *
    * @since 9.4
    *
    * @param integer $users_id  the user ID
    * @param integer $groups_id the group ID
    *
    * @return boolean true if the user belongs to the group
    */
    public static function isUserInGroup($users_id, $groups_id)
    {
        return countElementsInTable(
            'glpi_groups_users',
            [
              'users_id' => $users_id,
              'groups_id' => $groups_id
            ]
        ) > 0;
    }

    /**
     * Get groups for a user
     *
     * @param integer $users_id  User id
     * @param array   $condition Query extra condition (default [])
     *
     * @return array
    **/
    public static function getUserGroups($users_id, $condition = [])
    {
        global $DB;

        $groups = [];
        $result = self::getAdapter()->request([
           'SELECT' => [
              'glpi_groups.*',
              'glpi_groups_users.id AS IDD',
              'glpi_groups_users.id AS linkid',
              'glpi_groups_users.is_dynamic AS is_dynamic',
              'glpi_groups_users.is_manager AS is_manager',
              'glpi_groups_users.is_userdelegate AS is_userdelegate'
           ],
           'FROM'   => self::getTable(),
           'LEFT JOIN'    => [
              Group::getTable() => [
                 'FKEY' => [
                    Group::getTable() => 'id',
                    self::getTable()  => 'groups_id'
                 ]
              ]
           ],
           'WHERE'        => [
              'glpi_groups_users.users_id' => $users_id
           ] + $condition,
           'ORDER'        => 'glpi_groups.name'
        ]);
        while ($row = $result->fetchAssociative()) {
            $groups[] = $row;
        }

        return $groups;
    }


    /**
     * Get users for a group
     *
     * @since 0.84
     *
     * @param integer $groups_id Group ID
     * @param array   $condition Query extra condition (default [])
     *
     * @return array
    **/
    public static function getGroupUsers($groups_id, $condition = [])
    {
        global $DB;

        $users = [];

        $result = self::getAdapter()->request([
           'SELECT' => [
              'glpi_users.*',
              'glpi_groups_users.id AS IDD',
              'glpi_groups_users.id AS linkid',
              'glpi_groups_users.is_dynamic AS is_dynamic',
              'glpi_groups_users.is_manager AS is_manager',
              'glpi_groups_users.is_userdelegate AS is_userdelegate'
           ],
           'FROM'   => self::getTable(),
           'LEFT JOIN'    => [
              User::getTable() => [
                 'FKEY' => [
                    User::getTable() => 'id',
                    self::getTable()  => 'users_id'
                 ]
              ]
           ],
           'WHERE'        => [
              'glpi_groups_users.groups_id' => $groups_id
           ] + $condition,
           'ORDER'        => 'glpi_users.name'
        ]);
        while ($row = $result->fetchAssociative()) {
            $users[] = $row;
        }

        return $users;
    }


    /**  Show groups of a user
     *
     * @param $user   User object
    **/
    public static function showForUser(User $user)
    {
        global $CFG_GLPI;

        $ID = $user->fields['id'];
        if (
            !Group::canView()
            || !$user->can($ID, READ)
        ) {
            return false;
        }

        $canedit = $user->can($ID, UPDATE);

        $rand    = mt_rand();

        $iterator = self::getListForItem($user);
        $groups = [];
        //$groups  = self::getUserGroups($ID);
        $used    = [];
        foreach ($iterator as $data) {
            $used[$data["id"]] = $data["id"];
            $groups[] = $data;
        }

        $options = getItemByEntity('Group', Session::getActiveEntity(), [ 'is_usergroup' => 1, ]
           + getEntitiesRestrictCriteria(Group::getTable(), '', '', true));

        foreach ($used as $id) {
            unset($options[$id]);
        }

        if ($canedit) {
            $form = [
               'action' => User::getFormURL(),
               'buttons' => [
                  [
                     'type'  => 'submit',
                     'name'  => 'addgroup',
                     'value' => _sx('button', 'Add'),
                     'class' => 'btn btn-secondary'
                  ]
               ],
               'content' => [
                  __('Associate to a group') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'users_id',
                           'value' => $ID
                        ],
                        __('Group') => [
                           'type' => 'select',
                           'name' => 'groups_id',
                           'values' => $options,
                           'actions' => getItemActionButtons(['info', 'add'], 'Group')
                        ],
                        __('Manager') => [
                           'type' => 'checkbox',
                           'name' => 'is_manager',
                           'value' => 1
                        ],
                        __('Delegatee') => [
                           'type' => 'checkbox',
                           'name' => 'is_userdelegate',
                           'value' => 1
                        ]
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        if ($canedit && count($used)) {
            $massiveactionparams = [
               'container'     => 'tab_group_user',
               'display_arrow' => false
            ];
            Html::showMassiveActions($massiveactionparams);
        }

        $fields = [
           Group::getTypeName(1),
           __('Dynamic'),
           __('Manager'),
           __('Delegatee')
        ];
        $values = [];
        $massiveactionparams = [];

        $group = new Group();
        foreach ($groups as $data) {
            if (!$group->getFromDB($data["id"])) {
                continue;
            }
            $newValue = [$group->getLink()];
            if ($data['is_dynamic']) {
                $newValue[] = "<img src='" . $CFG_GLPI["root_doc"] . "/pics/ok.png' width='14' height='14' alt=\"" .
                __('Dynamic') . "\">";
            }
            if ($data['is_manager']) {
                $newValue[] = "<img src='" . $CFG_GLPI["root_doc"] . "/pics/ok.png' width='14' height='14' alt=\"" .
                __('Manager') . "\">";
            }
            if ($data['is_userdelegate']) {
                $newValue[] = "<img src='" . $CFG_GLPI["root_doc"] . "/pics/ok.png' width='14' height='14' alt=\"" .
                __('Delegatee') . "\">";
            }
            $values[] = $newValue;
            $massiveactionparams[] = sprintf('item[%s][%s]', self::class, $data['linkid']);
        }

        renderTwigTemplate('table.twig', [
           'id' => 'tab_group_user',
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massiveactionparams
        ]);
    }


    /**
     * Show form to add a user in current group
     *
     * @since 0.83
     *
     * @param $group                    Group object
     * @param $used_ids        Array    of already add users
     * @param $entityrestrict  Array    of entities
     * @param $crit            String   for criteria (for default dropdown)
    **/
    private static function showAddUserForm(Group $group, $used_ids, $entityrestrict, $crit)
    {
        $rand = mt_rand();
        $res  = iterator_to_array(User::getSqlSearchResult(true, "all", $entityrestrict, 0, $used_ids, '', 0, -1, 0, 1));

        $nb = count($res);

        if ($nb) {
            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  [
                     'type'  => 'submit',
                     'name'  => 'add',
                     'value' => _sx('button', 'Add'),
                     'class' => 'btn btn-secondary'
                  ]
               ],
               'content' => [
                  __('Add a user') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'groups_id',
                           'value' => $group->fields['id']
                        ],
                        [
                           'type' => 'hidden',
                           'name' => 'is_dynamic',
                           'value' => 0
                        ],
                        __('User') => [
                           'type' => 'select',
                           'name' => 'users_id',
                           'values' => getOptionsForUsers('all'),
                           'condition' => ['entities_id' => $entityrestrict],
                           'actions' => getItemActionButtons(['info', 'add'], 'User')
                        ],
                        __('Manager') => [
                           'type' => 'checkbox',
                           'name' => 'is_manager',
                           'value' => 1
                        ],
                        __('Delegatee') => [
                           'type' => 'checkbox',
                           'name' => 'is_userdelegate',
                           'value' => 1
                        ]
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }
    }


    /**
     * Retrieve list of member of a Group
     *
     * @since 0.83
     *
     * @param Group           $group    Group object
     * @param array           $members  Array filled on output of member (filtered)
     * @param array           $ids      Array of ids (not filtered)
     * @param string          $crit     Filter (is_manager, is_userdelegate) (default '')
     * @param boolean|integer $tree     True to include member of sub-group (default 0)
     *
     * @return String tab of entity for restriction
    **/
    public static function getDataForGroup(Group $group, &$members, &$ids, $crit = '', $tree = 0)
    {
        global $DB;

        // Entity restriction for this group, according to user allowed entities
        if ($group->fields['is_recursive']) {
            $entityrestrict = getSonsOf('glpi_entities', $group->fields['entities_id']);

            // active entity could be a child of object entity
            if (
                ($_SESSION['glpiactive_entity'] != $group->fields['entities_id'])
                && in_array($_SESSION['glpiactive_entity'], $entityrestrict)
            ) {
                $entityrestrict = getSonsOf('glpi_entities', $_SESSION['glpiactive_entity']);
            }
        } else {
            $entityrestrict = $group->fields['entities_id'];
        }

        if ($tree) {
            $restrict = getSonsOf('glpi_groups', $group->getID());
        } else {
            $restrict = $group->getID();
        }

        // All group members
        $pu_table = Profile_User::getTable();
        $request = self::getAdapter()->request([
           'SELECT' => [
              'glpi_users.id',
              'glpi_groups_users.id AS linkid',
              'glpi_groups_users.groups_id',
              'glpi_groups_users.is_dynamic AS is_dynamic',
              'glpi_groups_users.is_manager AS is_manager',
              'glpi_groups_users.is_userdelegate AS is_userdelegate'
           ],
           'DISTINCT'  => true,
           'FROM'      => self::getTable(),
           'LEFT JOIN' => [
              User::getTable() => [
                 'ON' => [
                    self::getTable() => 'users_id',
                    User::getTable() => 'id'
                 ]
              ],
              $pu_table => [
                 'ON' => [
                    $pu_table        => 'users_id',
                    User::getTable() => 'id'
                 ]
              ]
           ],
           'WHERE' => [
              self::getTable() . '.groups_id'  => $restrict,
              'OR' => [
                 "$pu_table.entities_id" => null
              ] + getEntitiesRestrictCriteria($pu_table, '', $entityrestrict, 1)
           ],
           'ORDERBY' => [
              User::getTable() . '.realname',
              User::getTable() . '.firstname',
              User::getTable() . '.name'
           ]
        ]);
        
        while ($data = $request->fetchAssociative()) {
            // Add to display list, according to criterion
            if (empty($crit) || $data[$crit]) {
                $members[] = $data;
            }
            // Add to member list (member of sub-group are not member)
            if ($data['groups_id'] == $group->getID()) {
                $ids[]  = $data['id'];
            }
        }

        return $entityrestrict;
    }


    /**
     * Show users of a group
     *
     * @since 0.83
     *
     * @param $group  Group object: the group
    **/
    public static function showForGroup(Group $group)
    {
        global $CFG_GLPI;

        $ID = $group->getID();
        if (
            !User::canView()
            || !$group->can($ID, READ)
        ) {
            return false;
        }

        // Have right to manage members
        $canedit = self::canUpdate();
        $rand    = mt_rand();
        $user    = new User();
        $crit    = Session::getSavedOption(__CLASS__, 'criterion', '');
        $tree    = Session::getSavedOption(__CLASS__, 'tree', 0);
        $used    = [];
        $ids     = [];

        // Retrieve member list
        // TODO: migrate to use CommonDBRelation::getListForItem()
        $entityrestrict = self::getDataForGroup($group, $used, $ids, $crit, $tree);

        if ($canedit) {
            self::showAddUserForm($group, $ids, $entityrestrict, $crit);
        }

        $number = count($used);
        $start  = (isset($_GET['start']) ? intval($_GET['start']) : 0);
        if ($start >= $number) {
            $start = 0;
        }

        // Display results
        if ($number) {
            $fields = [
               'group' => $tree ? Group::getTypeName(1) : User::getTypeName(1),
               'parent' => __('Parent'),
               'dynamic' => __('Dynamic'),
               'manager' => __('Manager'),
               'delegatee' => __('Delegatee'),
            ];
            $values = [];
            $massiveactionValues = [];
            $massiveactionparams = [
               'num_displayed' => min($number - $start, $_SESSION['glpilist_limit']),
               'container' => 'mass' . __CLASS__ . $rand,
               'display_arrow' => false
            ];

            if ($canedit) {
                Html::showMassiveActions($massiveactionparams);
            }

            $tmpgrp = new Group();

            for ($i = $start, $j = 0; ($i < $number) && ($j < $_SESSION['glpilist_limit']); $i++, $j++) {
                $data = $used[$i];
                $user->getFromDB($data["id"]);
                Session::addToNavigateListItems('User', $data["id"]);

                $newValue = ['group' => $user->getLink()];
                if ($tree) {
                    if ($tmpgrp->getFromDB($data['groups_id'])) {
                        $newValue['group'] = $tmpgrp->getLink(['comments' => true]);
                    }
                }
                $parent = new Group();
                if ($parent->getFromDB($data['groups_id'])) {
                    $newValue['parent'] = $parent->getLink(['comments' => true]);
                } else {
                    $newValue['parent'] = __('Root');
                }
                if ($data['is_dynamic']) {
                    $newValue['dynamic'] = "<img src='" . $CFG_GLPI["root_doc"] . "/pics/ok.png' width='14' height='14' alt=\"" .
                       __('Dynamic') . "\">";
                }
                if ($data['is_manager']) {
                    $newValue['manager'] = "<img src='" . $CFG_GLPI["root_doc"] . "/pics/ok.png' width='14' height='14' alt=\"" .
                    __('Manager') . "\">";
                }
                if ($data['is_userdelegate']) {
                    $newValue['delegatee'] = "<img src='" . $CFG_GLPI["root_doc"] . "/pics/ok.png' width='14' height='14' alt=\"" .
                    __('Delegatee') . "\">";
                }
                if ($user->fields['is_active']) {
                    $newValue['active'] = "<img src='" . $CFG_GLPI["root_doc"] . "/pics/ok.png' width='14' height='14' alt=\"" .
                    __('Active') . "\">";
                }
                if ($canedit) {
                    $massiveactionValues[] = sprintf('item[%s][%s]', self::class, $data['linkid']);
                }
                $values[] = $newValue;
            }
            renderTwigTemplate('table.twig', [
               'id' => 'mass' . __CLASS__ . $rand,
               'fields' => $fields,
               'values' => $values,
               'massive_action' => $massiveactionValues,
            ]);
        } else {
            echo "<p class='center b'>" . __('No item found') . "</p>";
        }
    }


    /**
     * @since 0.85
     *
     * @see CommonDBRelation::getRelationMassiveActionsSpecificities()
    **/
    public static function getRelationMassiveActionsSpecificities()
    {
        $specificities                           = parent::getRelationMassiveActionsSpecificities();

        $specificities['select_items_options_1'] = ['right'     => 'all'];
        $specificities['select_items_options_2'] = [
           'condition' => [
              'is_usergroup' => 1,
           ] + getEntitiesRestrictCriteria(Group::getTable(), '', '', true)
        ];

        // Define normalized action for add_item and remove_item
        $specificities['normalized']['add'][]    = 'add_supervisor';
        $specificities['normalized']['add'][]    = 'add_delegatee';

        $specificities['button_labels']['add_supervisor'] = $specificities['button_labels']['add'];
        $specificities['button_labels']['add_delegatee']  = $specificities['button_labels']['add'];

        $specificities['update_if_different'] = true;

        return $specificities;
    }


    public static function getRelationInputForProcessingOfMassiveActions(
        $action,
        CommonDBTM $item,
        array $ids,
        array $input
    ) {
        switch ($action) {
            case 'add_supervisor':
                return ['is_manager' => 1];

            case 'add_delegatee':
                return ['is_userdelegate' => 1];
        }

        return [];
    }


    /**
     * Get search function for the class
     *
     * @return array of search option
    **/
    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
           'id'                 => 'common',
           'name'               => __('Characteristics')
        ];

        $tab[] = [
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'massiveaction'      => false,
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '3',
           'table'              => $this->getTable(),
           'field'              => 'is_dynamic',
           'name'               => __('Dynamic'),
           'datatype'           => 'bool',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '4',
           'table'              => 'glpi_groups',
           'field'              => 'completename',
           'name'               => Group::getTypeName(1),
           'massiveaction'      => false,
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => 'glpi_users',
           'field'              => 'name',
           'name'               => User::getTypeName(1),
           'massiveaction'      => false,
           'datatype'           => 'dropdown',
           'right'              => 'all'
        ];

        $tab[] = [
           'id'                 => '6',
           'table'              => $this->getTable(),
           'field'              => 'is_manager',
           'name'               => __('Manager'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '7',
           'table'              => $this->getTable(),
           'field'              => 'is_userdelegate',
           'name'               => __('Delegatee'),
           'datatype'           => 'bool'
        ];

        return $tab;
    }


    /**
     * @param $user_ID
     * @param $only_dynamic (false by default
    **/
    public static function deleteGroups($user_ID, $only_dynamic = false)
    {
        $crit['users_id'] = $user_ID;
        if ($only_dynamic) {
            $crit['is_dynamic'] = '1';
        }
        $obj = new self();
        $obj->deleteByCriteria($crit);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            $nb = 0;
            switch ($item->getType()) {
                case 'User':
                    if (Group::canView()) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            $nb = self::countForItem($item);
                        }
                        return self::createTabEntry(Group::getTypeName(Session::getPluralNumber()), $nb);
                    }
                    break;

                case 'Group':
                    if (User::canView()) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            $nb = self::countForItem($item);
                        }
                        return self::createTabEntry(User::getTypeName(Session::getPluralNumber()), $nb);
                    }
                    break;
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'User':
                self::showForUser($item);
                break;

            case 'Group':
                self::showForGroup($item);
                break;
        }
        return true;
    }

    /**
     * Get linked items list for specified item
     *
     * @since 9.3.1
     *
     * @param CommonDBTM $item  Item instance
     * @param boolean    $noent Flag to not compute entity informations (see Document_Item::getListForItemParams)
     *
     * @return array
     */
    protected static function getListForItemParams(CommonDBTM $item, $noent = false)
    {
        $params = parent::getListForItemParams($item, $noent);
        $params['SELECT'][] = self::getTable() . '.is_manager';
        $params['SELECT'][] = self::getTable() . '.is_userdelegate';
        return $params;
    }


    public function post_addItem()
    {
        global $DB;

        // add new user to plannings
        $groups_id  = $this->fields['groups_id'];
        $planning_k = 'group_' . $groups_id . '_users';

        // find users with the current group in their plannings
        $user_inst = new User();
        $users = $user_inst->find([
           'plannings' => ['LIKE', "%$planning_k%"]
        ]);

        // add the new user to found plannings
        $query = $DB->buildUpdate(
            User::getTable(),
            [
              'plannings' => new QueryParam(),
            ],
            [
              'id'        => new QueryParam()
            ]
        );
        $stmt = $DB->prepare($query);
        $in_transaction = $DB->inTransaction();
        if (!$in_transaction) {
            $DB->beginTransaction();
        }
        foreach ($users as $user) {
            $users_id  = $user['id'];
            $plannings = importArrayFromDB($user['plannings']);
            $nb_users  = count($plannings['plannings'][$planning_k]['users']);

            // add the planning for the user
            $plannings['plannings'][$planning_k]['users']['user_' . $this->fields['users_id']] = [
               'color'   => Planning::getPaletteColor('bg', $nb_users),
               'display' => true,
               'type'    => 'user'
            ];

            // if current user logged, append also to its session
            if ($users_id == Session::getLoginUserID()) {
                $_SESSION['glpi_plannings'] = $plannings;
            }

            // save the planning completed to db
            $json_plannings = exportArrayToDB($plannings);
            $stmt->bind_param('si', $json_plannings, $users_id);
            $stmt->execute();
        }

        if (!$in_transaction) {
            $DB->commit();
        }
        $stmt->close();
    }


    public function post_purgeItem()
    {
        global $DB;

        // remove user from plannings
        $groups_id  = $this->fields['groups_id'];
        $planning_k = 'group_' . $groups_id . '_users';

        // find users with the current group in their plannings
        $user_inst = new User();
        $users = $user_inst->find([
           'plannings' => ['LIKE', "%$planning_k%"]
        ]);

        // remove the deleted user to found plannings
        $query = $DB->buildUpdate(
            User::getTable(),
            [
              'plannings' => new QueryParam(),
            ],
            [
              'id'        => new QueryParam()
            ]
        );
        $stmt = $DB->prepare($query);
        $in_transaction = $DB->inTransaction();
        if (!$in_transaction) {
            $DB->beginTransaction();
        }
        foreach ($users as $user) {
            $users_id  = $user['id'];
            $plannings = importArrayFromDB($user['plannings']);

            // delete planning for the user
            unset($plannings['plannings'][$planning_k]['users']['user_' . $this->fields['users_id']]);

            // if current user logged, append also to its session
            if ($users_id == Session::getLoginUserID()) {
                $_SESSION['glpi_plannings'] = $plannings;
            }

            // save the planning completed to db
            $json_plannings = exportArrayToDB($plannings);
            $stmt->bind_param('si', $json_plannings, $users_id);
            $stmt->execute();
        }

        if (!$in_transaction) {
            $DB->commit();
        }
        $stmt->close();
    }
}
