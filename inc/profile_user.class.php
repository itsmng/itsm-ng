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
 * Profile_User Class
**/
class Profile_User extends CommonDBRelation
{
    // From CommonDBTM
    public $auto_message_on_action               = false;

    // From CommonDBRelation
    public static $itemtype_1                    = 'User';
    public static $items_id_1                    = 'users_id';

    public static $itemtype_2                    = 'Profile';
    public static $items_id_2                    = 'profiles_id';
    public static $checkItem_2_Rights            = self::DONT_CHECK_ITEM_RIGHTS;

    // Specific log system
    public static $logs_for_item_2               = false;
    public static $logs_for_item_1               = true;
    public static $log_history_1_add             = Log::HISTORY_ADD_SUBITEM;
    public static $log_history_1_delete          = Log::HISTORY_DELETE_SUBITEM;

    // Manage Entity properties forwarding
    public static $disableAutoEntityForwarding   = true;


    /**
     * @since 0.84
     *
     * @see CommonDBTM::getForbiddenStandardMassiveAction()
    **/
    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    public function maybeRecursive()
    {
        // Store is_recursive fields but not really recursive object
        return false;
    }


    // TODO CommonDBConnexity : check in details if we can replace canCreateItem by canRelationItem ...
    public function canCreateItem()
    {

        $user = new User();
        return $user->can($this->fields['users_id'], READ)
               && Profile::currentUserHaveMoreRightThan([$this->fields['profiles_id']
                                                                 => $this->fields['profiles_id']])
               && Session::haveAccessToEntity($this->fields['entities_id']);
    }

    public function prepareInputForAdd($input)
    {

        // TODO: check if the entities should not be inherited from the profile or the user
        if (
            !isset($input['entities_id'])
            || ($input['entities_id'] < 0)
        ) {
            Session::addMessageAfterRedirect(
                __('No selected element or badly defined operation'),
                false,
                ERROR
            );
            return false;
        }

        return parent::prepareInputForAdd($input);
    }


    /**
     * Show rights of a user
     *
     * @param $user User object
    **/
    public static function showForUser(User $user)
    {
        $ID = $user->getField('id');
        if (!$user->can($ID, READ)) {
            return false;
        }

        $canedit = $user->canEdit($ID);

        $strict_entities = self::getUserEntities($ID, false);
        if (
            !Session::haveAccessToOneOfEntities($strict_entities)
            && !Session::canViewAllEntities()
        ) {
            $canedit = false;
        }

        $canshowentity = Entity::canView();
        $rand          = mt_rand();

        if ($canedit) {
            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  [
                     'type' => 'submit',
                     'name' => 'add',
                     'value' => _sx('button', 'Add'),
                     'class' => 'btn btn-secondary',
                  ]
               ],
               'content' => [
                  __('Add an authorization to a user') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'users_id',
                           'value' => $ID,
                        ],
                        self::getTypeName() => [
                           'type'    => 'select',
                           'name'    => 'profiles_id',
                           'itemtype' => Profile::class,
                           'value' => Profile::getDefault(),
                           'actions' => getItemActionButtons(['info', 'add'], 'Profile'),
                           'col_lg' => 12,
                           'col_md' => 12,
                        ],
                        Entity::getTypeName() => [
                           'type'    => 'select',
                           'name'    => 'entities_id',
                           'values'  => getOptionForItems('Entity'),
                           'value' => $_SESSION['glpiactiveentities'],
                           'actions' => getItemActionButtons(['info', 'add'], 'Entity'),
                           'col_lg' => 8,
                        ],
                        __('Recursive') => [
                           'type'    => 'checkbox',
                           'name'    => 'is_recursive',
                           'value' => 0,
                           'col_lg' => 4,
                        ],
                     ]
                  ],
               ],
            ];
            renderTwigForm($form);
        }

        $iterator = self::getListForItem($user);
        $num = count($iterator);

        if ($canedit && $num) {
            $massiveactionparams = [
               'num_displayed' => min($_SESSION['glpilist_limit'], $num),
               'container'     => 'TableForProfileUser',
               'display_arrow' => false,
               'is_deleted' => 0,
               'add_actions' => [
                  'MassiveAction:purge' => __('Delete permanently the relation with selected elements'),
               ],
            ];
            Html::showMassiveActions($massiveactionparams);
        }

        $fields = [];
        if ($canshowentity) {
            $fields[] = Entity::getTypeName(1);
        }
        $fields[] = sprintf(
            __('%1$s (%2$s)'),
            self::getTypeName(Session::getPluralNumber()),
            __('D=Dynamic, R=Recursive')
        );

        $values = [];
        $massiveActionValues = [];
        while ($data = $iterator->next()) {
            $newValue = [];

            $link = $data["completename"];
            if ($_SESSION["glpiis_ids_visible"]) {
                $link = sprintf(__('%1$s (%2$s)'), $link, $data["entities_id"]);
            }

            if ($canshowentity) {
                $newValue[] = "<a href='" . Toolbox::getItemTypeFormURL('Entity') . "?id=" .
                       $data["entities_id"] . "'>$link</a>";
            }

            if (Profile::canView()) {
                $entname = "<a href='" . Toolbox::getItemTypeFormURL('Profile') . "?id=" . $data["id"] . "'>" .
                             $data["name"] . "</a>";
            } else {
                $entname =  $data["name"];
            }

            if ($data["is_dynamic"] || $data["is_recursive"]) {
                $entname = sprintf(__('%1$s %2$s'), $entname, "<span class='b'>(");
                if ($data["is_dynamic"]) {
                    //TRANS: letter 'D' for Dynamic
                    $entname = sprintf(__('%1$s%2$s'), $entname, __('D'));
                }
                if ($data["is_dynamic"] && $data["is_recursive"]) {
                    $entname = sprintf(__('%1$s%2$s'), $entname, ", ");
                }
                if ($data["is_recursive"]) {
                    //TRANS: letter 'R' for Recursive
                    $entname = sprintf(__('%1$s%2$s'), $entname, __('R'));
                }
                $entname = sprintf(__('%1$s%2$s'), $entname, ")</span>");
            }
            $massiveActionValues[] = 'item[' . self::class . ']' . '[' . $data['linkid'] . ']';
            $newValue[] = $entname;
            $values[] = $newValue;
        }
        renderTwigTemplate('table.twig', [
           'id' => 'TableForProfileUser',
           'fields' => $fields,
           'values' => $values,
           $canedit ? 'massive_action' : '' => $massiveActionValues
        ]);
        if ($num > 0) {
            echo "<table class='tab_cadre_fixehov' aria-label='Data Rows'>";

            while ($data = $iterator->next()) {
                echo "<tr class='tab_bg_1'>";
                if ($canedit) {
                    echo "<td width='10'>";
                    if (in_array($data["entities_id"], $_SESSION['glpiactiveentities'])) {
                        Html::showMassiveActionCheckBox(__CLASS__, $data["linkid"]);
                    } else {
                        echo "&nbsp;";
                    }
                    echo "</td>";
                }
                echo "<td>";

                $link = $data["completename"];
                if ($_SESSION["glpiis_ids_visible"]) {
                    $link = sprintf(__('%1$s (%2$s)'), $link, $data["entities_id"]);
                }

                if ($canshowentity) {
                    echo "<a href='" . Toolbox::getItemTypeFormURL('Entity') . "?id=" .
                           $data["entities_id"] . "'>";
                }
                echo $link . ($canshowentity ? "</a>" : '');
                echo "</td>";

                if (Profile::canView()) {
                    $entname = "<a href='" . Toolbox::getItemTypeFormURL('Profile') . "?id=" . $data["id"] . "'>" .
                                 $data["name"] . "</a>";
                } else {
                    $entname =  $data["name"];
                }

                if ($data["is_dynamic"] || $data["is_recursive"]) {
                    $entname = sprintf(__('%1$s %2$s'), $entname, "<span class='b'>(");
                    if ($data["is_dynamic"]) {
                        //TRANS: letter 'D' for Dynamic
                        $entname = sprintf(__('%1$s%2$s'), $entname, __('D'));
                    }
                    if ($data["is_dynamic"] && $data["is_recursive"]) {
                        $entname = sprintf(__('%1$s%2$s'), $entname, ", ");
                    }
                    if ($data["is_recursive"]) {
                        //TRANS: letter 'R' for Recursive
                        $entname = sprintf(__('%1$s%2$s'), $entname, __('R'));
                    }
                    $entname = sprintf(__('%1$s%2$s'), $entname, ")</span>");
                }
                echo "<td>" . $entname . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<table class='tab_cadre_fixe' aria-label='No item Found'>";
            echo "<tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>\n";
        }

        if ($canedit && $num) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
        }
    }


    /**
     * Show users of an entity
     *
     * @param $entity Entity object
    **/
    public static function showForEntity(Entity $entity)
    {
        global $DB;

        $ID = $entity->getField('id');
        if (!$entity->can($ID, READ)) {
            return false;
        }

        $canedit     = $entity->canEdit($ID);
        $canshowuser = User::canView();
        $nb_per_line = 3;
        $rand        = mt_rand();

        if ($canedit) {
            $headerspan = $nb_per_line * 2;
        } else {
            $headerspan = $nb_per_line;
        }

        if ($canedit) {
            echo "<div class='firstbloc'>";
            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  [
                     'type' => 'submit',
                     'name' => 'add',
                     'value' => _sx('button', 'Add'),
                     'class' => 'btn btn-secondary',
                  ]
               ],
               'content' => [
                  __('Add an authorization to a user') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'entities_id',
                           'value' => $ID,
                        ],
                        User::getTypeName() => [
                           'type'    => 'select',
                           'name'    => 'users_id',
                           'itemtype' => User::class,
                           'value' => $_SESSION['glpiactive_entity'],
                           'actions' => getItemActionButtons(['info'], 'User'),
                        ],
                        self::getTypeName() => [
                           'type'    => 'select',
                           'name'    => 'profiles_id',
                           'values'   => getOptionForItems('Profile'),
                           'value' => Profile::getDefault(),
                        ],
                        __('Recursive') => [
                           'type'    => 'checkbox',
                           'name'    => 'is_recursive',
                           'value' => 0,
                        ],
                     ]
                  ],
               ],
            ];
            renderTwigForm($form);
            echo "</div>";
        }

        $putable = Profile_User::getTable();
        $ptable = Profile::getTable();
        $utable = User::getTable();

        $iterator = $DB->request([
           'SELECT'       => [
              "glpi_users.*",
              "$putable.id AS linkid",
              "$putable.is_recursive",
              "$putable.is_dynamic",
              "$ptable.id AS pid",
              "$ptable.name AS pname"
           ],
           'FROM'         => $putable,
           'INNER JOIN'   => [
              $utable => [
                 'ON' => [
                    $putable => 'users_id',
                    $utable  => 'id'
                 ]
              ],
              $ptable  => [
                 'ON' => [
                    $putable => 'profiles_id',
                    $ptable  => 'id'
                 ]
              ]
           ],
           'WHERE'        => [
              "$utable.is_deleted"    => 0,
              "$putable.entities_id"  => $ID
           ],
           'ORDERBY'      => [
              "$putable.profiles_id",
              "$utable.name",
              "$utable.realname",
              "$utable.firstname"
           ]
        ]);

        $nb = count($iterator);

        echo "<div class='spaced'>";
        if ($canedit && $nb) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = [
               'container' => 'mass' . __CLASS__ . $rand,
               'specific_actions' => [
                  'purge' => _x('button', 'Delete permanently')
               ],
               'deprecated' => true
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        echo "<table class='tab_cadre_fixehov' aria-label='User Managment'>";
        echo "<thead><tr>";

        echo "<th class='noHover' colspan='$headerspan'>";
        printf(__('%1$s (%2$s)'), User::getTypeName(Session::getPluralNumber()), __('D=Dynamic, R=Recursive'));
        echo "</th></tr></thead>";

        if ($nb) {
            Session::initNavigateListItems(
                'User',
                //TRANS : %1$s is the itemtype name, %2$s is the name of the item (used for headings of a list)
                sprintf(
                    __('%1$s = %2$s'),
                    Entity::getTypeName(1),
                    $entity->getName()
                )
            );

            $current_pid = null;
            while ($data = $iterator->next()) {
                if ($data['pid'] != $current_pid) {
                    echo "<tbody><tr class='noHover'>";
                    $reduce_header = 0;
                    if ($canedit && $nb) {
                        echo "<th width='10'>";
                        echo Html::getCheckAllAsCheckbox("profile" . $data['pid'] . "_$rand");
                        echo "</th>";
                        $reduce_header++;
                    }
                    echo "<th colspan='" . ($headerspan - $reduce_header) . "'>";
                    printf(__('%1$s: %2$s'), Profile::getTypeName(1), $data["pname"]);
                    echo "</th></tr></tbody>";
                    echo "<tbody id='profile" . $data['pid'] . "_$rand'>";
                    $i = 0;
                }

                Session::addToNavigateListItems('User', $data["id"]);

                if (($i % $nb_per_line) == 0) {
                    if ($i  != 0) {
                        echo "</tr>";
                    }
                    echo "<tr class='tab_bg_1'>";
                }
                if ($canedit) {
                    echo "<td width='10'>";
                    Html::showMassiveActionCheckBox(__CLASS__, $data["linkid"]);
                    echo "</td>";
                }

                $username = formatUserName(
                    $data["id"],
                    $data["name"],
                    $data["realname"],
                    $data["firstname"],
                    $canshowuser
                );

                if ($data["is_dynamic"] || $data["is_recursive"]) {
                    $username = sprintf(__('%1$s %2$s'), $username, "<span class='b'>(");
                    if ($data["is_dynamic"]) {
                        $username = sprintf(__('%1$s%2$s'), $username, __('D'));
                    }
                    if ($data["is_dynamic"] && $data["is_recursive"]) {
                        $username = sprintf(__('%1$s%2$s'), $username, ", ");
                    }
                    if ($data["is_recursive"]) {
                        $username = sprintf(__('%1$s%2$s'), $username, __('R'));
                    }
                    $username = sprintf(__('%1$s%2$s'), $username, ")</span>");
                }
                echo "<td>" . $username . "</td>";
                $i++;

                $current_pid = $data['pid'];
                if ($data['pid'] != $current_pid) {
                    echo "</tr>";
                    echo "</tbody>";
                }
            }
        }
        echo "</table>";
        if ($canedit && $nb) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
        }
        echo "</div>";
    }


    /**
     * Show the User having a profile, in allowed Entity
     *
     * @param $prof Profile object
    **/
    public static function showForProfile(Profile $prof)
    {
        global $DB;

        $ID      = $prof->fields['id'];
        $canedit = Session::haveRightsOr("user", [CREATE, UPDATE, DELETE, PURGE]);
        $rand = mt_rand();
        if (!$prof->can($ID, READ)) {
            return false;
        }

        $utable = User::getTable();
        $putable = Profile_User::getTable();
        $etable = Entity::getTable();
        $iterator = $DB->request([
           'SELECT'          => [
              "$utable.*",
              "$putable.entities_id AS entity",
              "$putable.id AS linkid",
              "$putable.is_dynamic",
              "$putable.is_recursive"
           ],
           'DISTINCT'        => true,
           'FROM'            => $putable,
           'LEFT JOIN'       => [
              $etable  => [
                 'ON' => [
                    $putable => 'entities_id',
                    $etable  => 'id'
                 ]
              ],
              $utable  => [
                 'ON' => [
                    $putable => 'users_id',
                    $utable  => 'id'
                 ]
              ]
           ],
           'WHERE'           => [
              "$putable.profiles_id"  => $ID,
              "$utable.is_deleted"    => 0
           ] + getEntitiesRestrictCriteria($putable, 'entities_id', $_SESSION['glpiactiveentities'], true),
           'ORDERBY'         => "$etable.completename"
        ]);

        $nb = count($iterator);

        echo "<div class='spaced'>";

        if ($canedit && $nb) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = [
               'num_displayed' => min($_SESSION['glpilist_limit'], $nb),
               'container'     => 'mass' . __CLASS__ . $rand,
               'deprecated'    => true
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        echo "<h2>" . sprintf(__('%1$s: %2$s'), Profile::getTypeName(1), $prof->fields["name"]) . "</h2>\n";
        echo "<p>" . sprintf(
            __('%1$s (%2$s)'),
            User::getTypeName(Session::getPluralNumber()),
            __('D=Dynamic, R=Recursive')
        ) . "</p>";
        echo "<table class='tab_cadre_fixe' aria-label='Entity Data'>";

        $i              = 0;
        $nb_per_line    = 3;
        $rand           = mt_rand(); // Just to avoid IDE warning
        $canedit_entity = false;

        if ($nb) {
            $temp = -1;

            while ($data = $iterator->next()) {
                if ($data["entity"] != $temp) {
                    while (($i % $nb_per_line) != 0) {
                        if ($canedit_entity) {
                            echo "<td width='10'>&nbsp;</td>";
                        }
                        echo "<td class='tab_bg_1'>&nbsp;</td>\n";
                        $i++;
                    }

                    if ($i != 0) {
                        echo "</table>";
                        echo "</div>";
                        echo "</td></tr>\n";
                    }

                    // New entity
                    $i              = 0;
                    $temp           = $data["entity"];
                    $canedit_entity = $canedit && in_array($temp, $_SESSION['glpiactiveentities']);
                    $rand           = mt_rand();
                    echo "<tr class='tab_bg_2'>";
                    echo "<td>";
                    echo "<a href=\"javascript:showHideDiv('entity$temp$rand','imgcat$temp', '" .
                             "fa-folder','fa-folder-open');\">";
                    echo "<i id='imgcat$temp' class='fa fa-folder' aria-hidden='true'></i>&nbsp;";
                    echo "<span class='b'>" . Dropdown::getDropdownName('glpi_entities', $data["entity"]) .
                          "</span>";
                    echo "</a>";

                    echo "</td></tr>\n";

                    echo "<tr class='tab_bg_2'><td>";
                    echo "<div class='center' id='entity$temp$rand' style='display:none;'>\n";
                    echo Html::getCheckAllAsCheckbox("entity$temp$rand") . __('All');

                    echo "<table class='tab_cadre_fixe' aria-label='User Detail'>\n";
                }

                if (($i % $nb_per_line) == 0) {
                    if ($i != 0) {
                        echo "</tr>\n";
                    }
                    echo "<tr class='tab_bg_1'>\n";
                    $i = 0;
                }

                if ($canedit_entity) {
                    echo "<td width='10'>";
                    Html::showMassiveActionCheckBox(__CLASS__, $data["linkid"]);
                    echo "</td>";
                }

                $username = formatUserName(
                    $data["id"],
                    $data["name"],
                    $data["realname"],
                    $data["firstname"],
                    1
                );

                if ($data["is_dynamic"] || $data["is_recursive"]) {
                    $username = sprintf(__('%1$s %2$s'), $username, "<span class='b'>(");
                    if ($data["is_dynamic"]) {
                        $username = sprintf(__('%1$s%2$s'), $username, __('D'));
                    }
                    if ($data["is_dynamic"] && $data["is_recursive"]) {
                        $username = sprintf(__('%1$s%2$s'), $username, ", ");
                    }
                    if ($data["is_recursive"]) {
                        $username = sprintf(__('%1$s%2$s'), $username, __('R'));
                    }
                    $username = sprintf(__('%1$s%2$s'), $username, ")</span>");
                }
                echo "<td class='tab_bg_1'>" . $username . "</td>\n";
                $i++;
            }

            if (($i % $nb_per_line) != 0) {
                while (($i % $nb_per_line) != 0) {
                    if ($canedit_entity) {
                        echo "<td width='10'>&nbsp;</td>";
                    }
                    echo "<td class='tab_bg_1'>&nbsp;</td>";
                    $i++;
                }
            }

            if ($i != 0) {
                echo "</table>";
                echo "</div>";
                echo "</td></tr>\n";
            }
        } else {
            echo "<tr class='tab_bg_2'><td class='tab_bg_1 center'>" . __('No user found') .
                  "</td></tr>\n";
        }
        echo "</table>";
        if ($canedit && $nb) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
        }
        echo "</div>\n";
    }


    /**
     * Get entities for which a user have a right
     *
     * @param $user_ID         user ID
     * @param $is_recursive    check also using recursive rights (true by default)
     * @param $default_first   user default entity first (false by default)
     *
     * @return array of entities ID
    **/
    public static function getUserEntities($user_ID, $is_recursive = true, $default_first = false)
    {
        global $DB;

        $iterator = $DB->request([
           'SELECT'          => [
              'entities_id',
              'is_recursive'
           ],
           'DISTINCT'        => true,
           'FROM'            => 'glpi_profiles_users',
           'WHERE'           => ['users_id' => $user_ID]
        ]);
        $entities = [];

        while ($data = $iterator->next()) {
            if ($data['is_recursive'] && $is_recursive) {
                $tab      = getSonsOf('glpi_entities', $data['entities_id']);
                $entities = array_merge($tab, $entities);
            } else {
                $entities[] = $data['entities_id'];
            }
        }

        // Set default user entity at the begin
        if ($default_first) {
            $user = new User();
            if ($user->getFromDB($user_ID)) {
                $ent = $user->getField('entities_id');
                if (in_array($ent, $entities)) {
                    array_unshift($entities, $ent);
                }
            }
        }

        return array_unique($entities);
    }


    /**
     * Get entities for which a user have a right
     *
     * @since 0.84
     * @since 9.2  Add $rightname parameter
     *
     * @param integer $user_ID      user ID
     * @param string  $rightname    name of the rights to check (CommonDBTM::$rightname)
     * @param integer $rights       rights to check (may be a OR combinaison of several rights)
     *                              (exp: CommonDBTM::READ | CommonDBTM::UPDATE ...)
     * @param boolean $is_recursive check also using recursive rights (true by default)
     *
     * @return array of entities ID
    **/
    public static function getUserEntitiesForRight($user_ID, $rightname, $rights, $is_recursive = true)
    {
        global $DB;

        $putable = Profile_User::getTable();
        $ptable = Profile::getTable();
        $prtable = ProfileRight::getTable();
        $iterator = $DB->request([
           'SELECT'          => [
              "$putable.entities_id",
              "$putable.is_recursive"
           ],
           'DISTINCT'        => true,
           'FROM'            => $putable,
           'INNER JOIN'      => [
              $ptable  => [
                 'ON' => [
                    $putable => 'profiles_id',
                    $ptable  => 'id'
                 ]
              ],
              $prtable => [
                 'ON' => [
                    $prtable => 'profiles_id',
                    $ptable  => 'id'
                 ]
              ]
           ],
           'WHERE'           => [
              "$putable.users_id"  => $user_ID,
              "$prtable.name"      => $rightname,
              "$prtable.rights"    => ['&', $rights]
           ]
        ]);

        if (count($iterator) > 0) {
            $entities = [];

            while ($data = $iterator->next()) {
                if ($data['is_recursive'] && $is_recursive) {
                    $tab      = getSonsOf('glpi_entities', $data['entities_id']);
                    $entities = array_merge($tab, $entities);
                } else {
                    $entities[] = $data['entities_id'];
                }
            }

            return array_unique($entities);
        }

        return [];
    }


    /**
     * Get user profiles (no entity association, use sqlfilter if needed)
     *
     * @since 9.3 can pass sqlfilter as a parameter
     *
     * @param $user_ID            user ID
     * @param $sqlfilter  string  additional filter (default [])
     *
     * @return array of the IDs of the profiles
    **/
    public static function getUserProfiles($user_ID, $sqlfilter = [])
    {
        global $DB;

        $profiles = [];

        $where = ['users_id' => $user_ID];
        if (count($sqlfilter) > 0) {
            $where = $where + $sqlfilter;
        }

        $iterator = $DB->request([
           'SELECT'          => 'profiles_id',
           'DISTINCT'        => true,
           'FROM'            => 'glpi_profiles_users',
           'WHERE'           => $where
        ]);

        while ($data = $iterator->next()) {
            $profiles[$data['profiles_id']] = $data['profiles_id'];
        }

        return $profiles;
    }


    /**
     * retrieve the entities allowed to a user for a profile
     *
     * @param $users_id     Integer  ID of the user
     * @param $profiles_id  Integer  ID of the profile
     * @param $child        Boolean  when true, include child entity when recursive right
     *                               (false by default)
     *
     * @return Array of entity ID
    **/
    public static function getEntitiesForProfileByUser($users_id, $profiles_id, $child = false)
    {
        global $DB;

        $iterator = $DB->request([
           'SELECT' => ['entities_id', 'is_recursive'],
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'users_id'     => $users_id,
              'profiles_id'  => $profiles_id
           ]
        ]);

        $entities = [];
        while ($data = $iterator->next()) {
            if (
                $child
                && $data['is_recursive']
            ) {
                foreach (getSonsOf('glpi_entities', $data['entities_id']) as $id) {
                    $entities[$id] = $id;
                }
            } else {
                $entities[$data['entities_id']] = $data['entities_id'];
            }
        }
        return $entities;
    }


    /**
     * retrieve the entities associated to a user
     *
     * @param $users_id     Integer  ID of the user
     * @param $child        Boolean  when true, include child entity when recursive right
     *                               (false by default)
     *
     * @since 0.85
     *
     * @return Array of entity ID
    **/
    public static function getEntitiesForUser($users_id, $child = false)
    {
        global $DB;

        $iterator = $DB->request([
           'SELECT' => ['entities_id', 'is_recursive'],
           'FROM'   => 'glpi_profiles_users',
           'WHERE'  => ['users_id' => $users_id]
        ]);

        $entities = [];
        while ($data = $iterator->next()) {
            if (
                $child
                && $data['is_recursive']
            ) {
                foreach (getSonsOf('glpi_entities', $data['entities_id']) as $id) {
                    $entities[$id] = $id;
                }
            } else {
                $entities[$data['entities_id']] = $data['entities_id'];
            }
        }
        return $entities;
    }


    /**
     * Get entities for which a user have a right
     *
     * @param $user_ID         user ID
     * @param $only_dynamic    get only recursive rights (false by default)
     *
     * @return array of entities ID
    **/
    public static function getForUser($user_ID, $only_dynamic = false)
    {
        $condition = ['users_id' => $user_ID];

        if ($only_dynamic) {
            $condition['is_dynamic'] = 1;
        }

        return getAllDataFromTable('glpi_profiles_users', $condition);
    }


    /**
     * @param $user_ID
     * @param $profile_id
    **/
    public static function haveUniqueRight($user_ID, $profile_id)
    {
        global $DB;

        $result = $DB->request([
           'COUNT'  => 'cpt',
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'users_id'     => $user_ID,
              'profiles_id'  => $profile_id
           ]
        ])->next();
        return $result['cpt'];
    }


    /**
     * @param $user_ID
     * @param $only_dynamic    (false by default)
    **/
    public static function deleteRights($user_ID, $only_dynamic = false)
    {

        $crit = [
           'users_id' => $user_ID,
        ];

        if ($only_dynamic) {
            $crit['is_dynamic'] = '1';
        }

        $obj = new self();
        $obj->deleteByCriteria($crit);
    }


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
           'table'              => 'glpi_profiles',
           'field'              => 'name',
           'name'               => self::getTypeName(1),
           'datatype'           => 'dropdown',
           'massiveaction'      => false
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
           'id'                 => '80',
           'table'              => 'glpi_entities',
           'field'              => 'completename',
           'name'               => Entity::getTypeName(1),
           'massiveaction'      => true,
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '86',
           'table'              => $this->getTable(),
           'field'              => 'is_recursive',
           'name'               => __('Child entities'),
           'datatype'           => 'bool',
           'massiveaction'      => false
        ];

        return $tab;
    }


    public static function getTypeName($nb = 0)
    {
        return _n('Profile', 'Profiles', $nb);
    }

    protected function computeFriendlyName()
    {

        $name = sprintf(
            __('%1$s, %2$s'),
            Dropdown::getDropdownName('glpi_profiles', $this->fields['profiles_id']),
            Dropdown::getDropdownName('glpi_entities', $this->fields['entities_id'])
        );

        if (isset($this->fields['is_dynamic']) && $this->fields['is_dynamic']) {
            //TRANS: D for Dynamic
            $dyn  = __('D');
            $name = sprintf(__('%1$s, %2$s'), $name, $dyn);
        }
        if (isset($this->fields['is_recursive']) && $this->fields['is_recursive']) {
            //TRANS: R for Recursive
            $rec  = __('R');
            $name = sprintf(__('%1$s, %2$s'), $name, $rec);
        }
        return $name;
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        global $DB;

        if (!$withtemplate) {
            $nb = 0;
            switch ($item->getType()) {
                case 'Entity':
                    if (Session::haveRight('user', READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            $count = $DB->request([
                               'COUNT'     => 'cpt',
                               'FROM'      => $this->getTable(),
                               'LEFT JOIN' => [
                                  User::getTable() => [
                                     'FKEY' => [
                                        $this->getTable() => 'users_id',
                                        User::getTable()  => 'id'
                                     ]
                                  ]
                               ],
                               'WHERE'     => [
                                  User::getTable() . '.is_deleted'    => 0,
                                  $this->getTable() . '.entities_id'  => $item->getID()
                               ]
                            ])->next();
                            $nb        = $count['cpt'];
                        }
                        return self::createTabEntry(User::getTypeName(Session::getPluralNumber()), $nb);
                    }
                    break;

                case 'Profile':
                    if (Session::haveRight('user', READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            $nb = self::countForItem($item);
                        }
                        return self::createTabEntry(User::getTypeName(Session::getPluralNumber()), $nb);
                    }
                    break;

                case 'User':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = self::countForItem($item);
                    }
                    return self::createTabEntry(_n(
                        'Authorization',
                        'Authorizations',
                        Session::getPluralNumber()
                    ), $nb);
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'Entity':
                self::showForEntity($item);
                break;

            case 'Profile':
                self::showForProfile($item);
                break;

            case 'User':
                self::showForUser($item);
                break;
        }
        return true;
    }


    /**
     * @since 0.85
     *
     * @see CommonDBRelation::getRelationMassiveActionsSpecificities()
    **/
    public static function getRelationMassiveActionsSpecificities()
    {
        $specificities                            = parent::getRelationMassiveActionsSpecificities();

        $specificities['dropdown_method_2']       = 'dropdownUnder';
        $specificities['can_remove_all_at_once']  = false;

        return $specificities;
    }


    /**
     * @since 0.85
     *
     * @see CommonDBRelation::showRelationMassiveActionsSubForm()
    **/
    public static function showRelationMassiveActionsSubForm(MassiveAction $ma, $peer_number)
    {

        if (
            ($ma->getAction() == 'add')
            && ($peer_number == 2)
        ) {
            echo "<br><br>" . sprintf(__('%1$s: %2$s'), Entity::getTypeName(1), '');
            Entity::dropdown(['entity' => $_SESSION['glpiactiveentities']]);
            echo "<br><br>" . sprintf(__('%1$s: %2$s'), __('Recursive'), '');
            Html::showCheckbox(['name' => 'is_recursive']);
        }
    }


    /**
     * @since 0.85
     *
     * @see CommonDBRelation::getRelationInputForProcessingOfMassiveActions()
    **/
    public static function getRelationInputForProcessingOfMassiveActions(
        $action,
        CommonDBTM $item,
        array $ids,
        array $input
    ) {
        $result = [];
        if (isset($input['entities_id'])) {
            $result['entities_id'] = $input['entities_id'];
        }
        if (isset($input['is_recursive'])) {
            $result['is_recursive'] = $input['is_recursive'];
        }

        return $result;
    }

    /**
     * Get linked items list for specified item
     *
     * @since 9.3.1
     *
     * @param CommonDBTM $item  Item instance
     * @param boolean    $noent Flag to not compute entity information (see Document_Item::getListForItemParams)
     *
     * @return array
     */
    protected static function getListForItemParams(CommonDBTM $item, $noent = false)
    {
        $params = parent::getListForItemParams($item, $noent);
        $params['SELECT'][] = self::getTable() . '.entities_id';
        $params['SELECT'][] = self::getTable() . '.is_recursive';
        $params['SELECT'][] = 'glpi_entities.completename AS completename';
        $params['LEFT JOIN']['glpi_entities'] = [
           'FKEY'   => [
              self::getTable()  => 'entities_id',
              'glpi_entities'   => 'id'
           ]
        ];
        return $params;
    }
}
