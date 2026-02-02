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

class DisplayPreference extends CommonDBTM
{
    // From CommonGLPI
    public $taborientation          = 'horizontal';
    public $get_item_to_display_tab = false;

    // From CommonDBTM
    public $auto_message_on_action  = false;

    protected $displaylist          = false;


    public static $rightname = 'search_config';

    public const PERSONAL = 1024;
    public const GENERAL  = 2048;



    public function prepareInputForAdd($input)
    {
        global $DB;

        $result = $DB->request([
           'SELECT' => ['MAX' => 'rank AS maxrank'],
           'FROM'   => $this->getTable(),
           'WHERE'  => [
              'itemtype'  => $input['itemtype'],
              'users_id'  => $input['users_id']
           ]
        ])->next();
        $input['rank'] = $result['maxrank'] + 1;
        return $input;
    }


    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {

        switch ($ma->getAction()) {
            case 'delete_for_user':
                $input = $ma->getInput();
                if (isset($input['users_id'])) {
                    $user = new User();
                    $user->getFromDB($input['users_id']);
                    foreach ($ids as $id) {
                        if ($input['users_id'] == Session::getLoginUserID()) {
                            if (
                                $item->deleteByCriteria(['users_id' => $input['users_id'],
                                                              'itemtype' => $id])
                            ) {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                            } else {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                                $ma->addMessage($user->getErrorMessage(ERROR_ON_ACTION));
                            }
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
                            $ma->addMessage($user->getErrorMessage(ERROR_RIGHT));
                        }
                    }
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                }
                return;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }


    /**
     * Get display preference for a user for an itemtype
     *
     * @param string  $itemtype  itemtype
     * @param integer $user_id   user ID
     *
     * @return array
    **/
    public static function getForTypeUser($itemtype, $user_id)
    {
        global $DB;

        $iterator = $DB->request([
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'itemtype'  => $itemtype,
              'OR'        => [
                 ['users_id' => $user_id],
                 ['users_id' => 0]
              ]
           ],
           'ORDER'  => ['users_id', 'rank']
        ]);

        $default_prefs = [];
        $user_prefs = [];

        while ($data = $iterator->next()) {
            if ($data["users_id"] != 0) {
                $user_prefs[] = $data["num"];
            } else {
                $default_prefs[] = $data["num"];
            }
        }

        return count($user_prefs) ? $user_prefs : $default_prefs;
    }


    /**
     * Active personal config based on global one
     *
     * @param $input  array parameter (itemtype,users_id)
    **/
    public function activatePerso(array $input)
    {
        global $DB;

        if (!Session::haveRight(self::$rightname, self::PERSONAL)) {
            return false;
        }

        $iterator = $DB->request([
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'itemtype'  => $input['itemtype'],
              'users_id'  => 0
           ]
        ]);

        if (count($iterator)) {
            while ($data = $iterator->next()) {
                unset($data["id"]);
                $data["users_id"] = $input["users_id"];
                $this->fields     = $data;
                $this->addToDB();
            }
        } else {
            // No items in the global config
            $searchopt = Search::getOptions($input["itemtype"]);
            if (count($searchopt) > 1) {
                $done = false;

                foreach ($searchopt as $key => $val) {
                    if (
                        is_array($val)
                        && ($key != 1)
                        && !$done
                    ) {
                        $data["users_id"] = $input["users_id"];
                        $data["itemtype"] = $input["itemtype"];
                        $data["rank"]     = 1;
                        $data["num"]      = $key;
                        $this->fields     = $data;
                        $this->addToDB();
                        $done = true;
                    }
                }
            }
        }
    }


    /**
     * Order to move an item
     *
     * @param array  $input  array parameter (id,itemtype,users_id)
     * @param string $action       up or down
    **/
    public function orderItem(array $input, $action)
    {
        global $DB;

        // Get current item
        $result = $DB->request([
           'SELECT' => 'rank',
           'FROM'   => $this->getTable(),
           'WHERE'  => ['id' => $input['id']]
        ])->next();
        $rank1  = $result['rank'];

        // Get previous or next item
        $where = [];
        $order = 'rank ';
        switch ($action) {
            case "up":
                $where['rank'] = ['<', $rank1];
                $order .= 'DESC';
                break;

            case "down":
                $where['rank'] = ['>', $rank1];
                $order .= 'ASC';
                break;

            default:
                return false;
        }

        $result = $DB->request([
           'SELECT' => ['id', 'rank'],
           'FROM'   => $this->getTable(),
           'WHERE'  => [
              'itemtype'  => $input['itemtype'],
              'users_id'  => $input["users_id"]
           ] + $where,
           'ORDER'  => $order,
           'LIMIT'  => 1
        ])->next();

        $rank2  = $result['rank'];
        $ID2    = $result['id'];

        // Update items
        $DB->update(
            $this->getTable(),
            ['rank' => $rank2],
            ['id' => $input['id']]
        );

        $DB->update(
            $this->getTable(),
            ['rank' => $rank1],
            ['id' => $ID2]
        );
    }


    /**
     * Print the search config form
     *
     * @param string $target    form target
     * @param string $itemtype  item type
     *
     * @return void|boolean (display) Returns false if there is a rights error.
    **/
    public function showFormPerso($target, $itemtype)
    {
        global $CFG_GLPI, $DB;

        $searchopt = Search::getCleanedOptions($itemtype);
        if (!is_array($searchopt)) {
            return false;
        }

        $item = null;
        if ($itemtype != 'AllAssets') {
            $item = getItemForItemtype($itemtype);
        }

        $IDuser = Session::getLoginUserID();
        $personal_write = Session::haveRight(self::$rightname, self::PERSONAL);
        // Defined items
        $iterator = $DB->request([
           'FROM'   => $this->getTable(),
           'WHERE'  => [
              'itemtype'  => $itemtype,
              'users_id'  => $IDuser
           ],
           'ORDER'  => 'rank'
        ]);
        $numrows = count($iterator);

        echo '<h2>' . __('Personal View') . '</h2>';

        if ($numrows == 0) {
            if (!$personal_write) {
                echo '<p class="alert alert-info">' . __('No personal criteria.') . '</p>';
                return;
            }

            $buttonLabel = __('Create');
            $form = [
                'action' => $target,
                'buttons' => [[]],
                'content' => [
                    __('Personal settings') => [
                        'visible' => true,
                        'inputs' => [
                            [
                                'type' => 'hidden',
                                'name' => 'itemtype',
                                'value' => $itemtype,
                            ],
                            [
                                'type' => 'hidden',
                                'name' => 'users_id',
                                'value' => $IDuser,
                            ],
                            __('No personal criteria. Create personal parameters?') => [
                                'content' => '<div class="d-flex flex-wrap gap-2 align-items-center">'
                                    . '<button type="submit" name="activate" value="1" class="btn btn-sm btn-secondary">'
                                    . $buttonLabel
                                    . '</button>'
                                    . '</div>',
                                'col_lg' => 12,
                                'col_md' => 12,
                            ],
                        ],
                    ],
                ],
            ];
            renderTwigForm($form);
            return;
        }

        $already_added = self::getForTypeUser($itemtype, $IDuser);
        $group  = '';
        $values = [];
        foreach ($searchopt as $key => $val) {
            if (!is_array($val)) {
                $group = $val;
            } elseif (count($val) === 1) {
                $group = $val['name'];
            } elseif (
                $key != 1
                       && !in_array($key, $already_added)
                       && (!isset($val['nodisplay']) || !$val['nodisplay'])
            ) {
                $values[$group][$key] = $val["name"];
            }
        }

        if ($personal_write) {
            $buttonLabel = _sx('button', 'Add');
            $form = [
                'action' => $target,
                'buttons' => [[]],
                'content' => [
                    __('Personal settings') => [
                        'visible' => true,
                        'inputs' => [
                            [
                                'type' => 'hidden',
                                'name' => 'itemtype',
                                'value' => $itemtype
                            ],
                            [
                                'type' => 'hidden',
                                'name' => 'users_id',
                                'value' => $IDuser
                            ],
                            '' => $values ? [
                                'type' => 'select',
                                'name' => 'num',
                                'style' => 'width: 100%;',
                                'values' => $values,
                                'after' => <<<HTML
                                <button type="submit" name="add" value="1" class="btn btn-sm btn-secondary">
                                    $buttonLabel
                                </button>
                            HTML,
                                'col_lg' => 12,
                                'col_md' => 12,
                            ] : [],
                        ]
                    ]
                ]
            ];
            renderTwigForm($form);
        }

        if ($personal_write) {
            $form = [
                'action' => $target,
                'buttons' => [[]],
                'content' => [
                    '' => [
                        'visible' => false,
                        'inputs' => [
                            [
                                'type' => 'hidden',
                                'name' => 'itemtype',
                                'value' => $itemtype
                            ],
                            [
                                'type' => 'hidden',
                                'name' => 'users_id',
                                'value' => $IDuser
                            ],
                            '' => [
                                'content' => '<div class="d-flex align-items-center justify-content-end gap-2">'
                                    . '<span class="text-muted">' . __('Select default items to show') . '</span>'
                                    . '<button type="submit" name="disable" value="1" class="btn btn-sm btn-outline-secondary">'
                                    . __('Delete')
                                    . '</button>'
                                    . '</div>',
                                'col_lg' => 12,
                                'col_md' => 12,
                            ]
                        ]
                    ]
                ]
            ];
            renderTwigForm($form);
        }

        $fields = [
            'name' => __('Name'),
            'up' => '<i class="fa fa-arrow-up" aria-hidden="true"></i>',
            'down' => '<i class="fa fa-arrow-down" aria-hidden="true"></i>',
            'close' => '<i class="fa fa-times" aria-hidden="true"></i>'
        ];

        $values = [
            ['name' => $searchopt[1]["name"],]
        ];
        if (
            Session::isMultiEntitiesMode()
            && (isset($CFG_GLPI["union_search_type"][$itemtype])
                || ($item && $item->maybeRecursive())
                || (count($_SESSION["glpiactiveentities"]) > 1))
            && isset($searchopt[80])
        ) {
            $values[] = ['name' => $searchopt[80]["name"]];
        }

        $i = 0;
        while ($data = $iterator->next()) {
            $newValue = [];
            if (($data["num"] != 1) && isset($searchopt[$data["num"]])) {
                $newValue['name'] = $searchopt[$data["num"]]["name"];

                if ($personal_write) {
                    if ($i != 0) {
                        $newValue['up'] = <<<HTML
                        <form aria-label="Informations" method="post" action="$target">
                            <input type="hidden" name="id" value="{$data['id']}">
                            <input type="hidden" name="users_id" value="$IDuser">
                            <input type="hidden" name="itemtype" value="$itemtype">
                            <button type="submit" name="up" title="Bring up" class="btn btn-sm fs-6" aria-label="Bring Up">
                                <i class="fa fa-arrow-up" aria-hidden="true"></i>
                            </button>
                            <input type="hidden" name="_glpi_csrf_token" value="$_SESSION[_glpi_csrf_token]">
                        </form>
                      HTML;
                    }

                    if ($i != ($numrows - 1)) {
                        $newValue['down'] = <<<HTML
                        <form aria-label="Informations" method="post" action="$target">
                            <input type="hidden" name="id" value="{$data['id']}">
                            <input type="hidden" name="users_id" value="$IDuser">
                            <input type="hidden" name="itemtype" value="$itemtype">
                            <button type="submit" name="down" title="Bring down" class="btn btn-sm fs-6" aria-label="Bring Down">
                                <i class="fa fa-arrow-down" aria-hidden="true"></i>
                            </button>
                            <input type="hidden" name="_glpi_csrf_token" value="$_SESSION[_glpi_csrf_token]">
                        </form>
                      HTML;
                    }

                    if (!isset($searchopt[$data["num"]]["noremove"]) || $searchopt[$data["num"]]["noremove"] !== true) {
                        $newValue['close'] = <<<HTML
                        <form aria-label="Item Information" method="post" action="$target">
                            <input type="hidden" name="id" value="{$data['id']}">
                            <input type="hidden" name="users_id" value="$IDuser">
                            <input type="hidden" name="itemtype" value="$itemtype">
                            <button type="submit" name="purge" title="Delete permanently" class="btn btn-sm fs-6" aria-label="Delete">
                                <i class="fa fa-times-circle" aria-hidden="true"></i>
                            </button>
                            <input type="hidden" name="_glpi_csrf_token" value="$_SESSION[_glpi_csrf_token]">
                        </form>
                      HTML;
                    }
                }
            }
            $values[] = $newValue;
            $i++;
        }

        renderTwigTemplate('table.twig', [
            'fields' => $fields,
            'values' => $values,
            'minimal' => true,
        ]);
    }


    /**
     * Print the search config form
     *
     * @param string $target    form target
     * @param string $itemtype  item type
     *
     * @return void|boolean (display) Returns false if there is a rights error.
    **/
    public function showFormGlobal($target, $itemtype)
    {
        global $CFG_GLPI, $DB;

        $searchopt = Search::getCleanedOptions($itemtype);
        if (!is_array($searchopt)) {
            return false;
        }
        $IDuser = 0;

        $item = null;
        if ($itemtype != 'AllAssets') {
            $item = getItemForItemtype($itemtype);
        }

        $global_write = Session::haveRight(self::$rightname, self::GENERAL);

        // Defined items
        $iterator = $DB->request([
           'FROM'   => $this->getTable(),
           'WHERE'  => [
              'itemtype'  => $itemtype,
              'users_id'  => $IDuser
           ],
           'ORDER'  => 'rank'
        ]);
        $numrows = count($iterator);

        echo '<h2>' . __('Select default items to show') . '</h2>';

        if ($global_write) {
            $already_added = self::getForTypeUser($itemtype, $IDuser);
            $group  = '';
            $values = [];
            foreach ($searchopt as $key => $val) {
                if (!is_array($val)) {
                    $group = $val;
                } elseif (count($val) === 1) {
                    $group = $val['name'];
                } elseif (
                    $key != 1
                           && !in_array($key, $already_added)
                           && (!isset($val['nodisplay']) || !$val['nodisplay'])
                ) {
                    $values[$group][$key] = $val["name"];
                }
            }
            $buttonLabel = _sx('button', 'Add');

            $form = [
               'action' => $target,
               'buttons' => [[]],
               'content' => [
                  '' => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'itemtype',
                           'value' => $itemtype
                        ],
                        [
                           'type' => 'hidden',
                           'name' => 'users_id',
                           'value' => $IDuser
                        ],
                        '' => $values ? [
                           'type' => 'select',
                           'name' => 'num',
                           'style' => 'width: 100%;',
                           'values' => $values,
                           'after' => <<<HTML
                           <button type="submit" name="add" value="1" class="btn btn-sm btn-secondary">
                               $buttonLabel
                           </button>
                        HTML,
                           'col_lg' => 12,
                           'col_md' => 12,
                        ] : [],
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        };

        $fields = [
           'name' => __('Name'),
           'up' => '<i class="fa fa-arrow-up" aria-hidden="true"></i>',
           'down' => '<i class="fa fa-arrow-down" aria-hidden="true"></i>',
           'close' => '<i class="fa fa-times" aria-hidden="true"></i>'
        ];
        $values = [
           ['name' => $searchopt[1]["name"],]
        ];
        if (Session::isMultiEntitiesMode()) {
            $values[] = ['name' => $searchopt[80]["name"]];
        }
        $i = 0;
        while ($data = $iterator->next()) {
            $newValue = [];
            if (
                ($data["num"] != 1)
                && isset($searchopt[$data["num"]])
            ) {
                $newValue['name'] = $searchopt[$data["num"]]["name"];

                if ($global_write) {
                    if ($i != 0) {
                        $newValue['up'] = <<<HTML
                        <form aria-label="Informations" method="post" action="$target">
                           <input type="hidden" name="id" value="{$data['id']}">
                           <input type="hidden" name="users_id" value="$IDuser">
                           <input type="hidden" name="itemtype" value="$itemtype">
                           <button type="submit" name="up" title="Bring up" class="btn btn-sm text-sm fs-6" aria-label='Bring Up'>
                              <i class="fa fa-arrow-up"></i>
                           </button>
                           <input type="hidden" name="_glpi_csrf_token" value="$_SESSION[_glpi_csrf_token]">
                        </form>
                     HTML;
                    }

                    if ($i != ($numrows - 1)) {
                        $newValue['down'] = <<<HTML
                        <form aria-label="Informations" method="post" action="$target">
                           <input type="hidden" name="id" value="{$data['id']}">
                           <input type="hidden" name="users_id" value="$IDuser">
                           <input type="hidden" name="itemtype" value="$itemtype">
                           <button type="submit" name="down" title="Bring down" class="btn btn-sm fs-6" aria-label='Bring Down'>
                              <i class="fa fa-arrow-down" aria-hidden='true'></i>
                           </button>
                           <input type="hidden" name="_glpi_csrf_token" value="$_SESSION[_glpi_csrf_token]">
                        </form>
                     HTML;
                    }

                    if (!isset($searchopt[$data["num"]]["noremove"]) || $searchopt[$data["num"]]["noremove"] !== true) {
                        $newValue['close'] = <<<HTML
                        <form aria-label='Informations' method="post" action="$target">
                           <input type="hidden" name="id" value="{$data['id']}">
                           <input type="hidden" name="users_id" value="$IDuser">
                           <input type="hidden" name="itemtype" value="$itemtype">
                           <button type="submit" name="purge" title="Delete permanently" class="btn btn-xs fs-6" aria-label='Delete'>
                              <i class="fa fa-times-circle" aria-hidden='true'></i>
                           </button>
                           <input type="hidden" name="_glpi_csrf_token" value="$_SESSION[_glpi_csrf_token]">
                        </form>
                     HTML;
                    }
                }
            }
            $values[] = $newValue;
            $i++;
        }
        renderTwigTemplate('table.twig', [
           'fields' => $fields,
           'values' => $values,
           'minimal' => true,
        ]);
    }


    /**
     * show defined display preferences for a user
     *
     * @param $users_id integer user ID
    **/
    public static function showForUser($users_id)
    {
        global $DB;

        $url = Toolbox::getItemTypeFormURL(__CLASS__);

        $iterator = $DB->request([
           'SELECT'  => ['itemtype'],
           'COUNT'   => 'nb',
           'FROM'    => self::getTable(),
           'WHERE'   => [
              'users_id'  => $users_id
           ],
           'GROUPBY' => 'itemtype'
        ]);

        if (count($iterator) > 0) {
            $rand = mt_rand();
            echo "<div class='spaced'>";
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['width'            => 400,
                              'height'           => 200,
                              'container'        => 'mass' . __CLASS__ . $rand,
                              'specific_actions' => [__CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'delete_for_user'
                                                          => _x('button', 'Delete permanently')],
                              'extraparams'      => ['massive_action_fields' => ['users_id']]];

            Html::showMassiveActions($massiveactionparams);

            echo Html::hidden('users_id', ['value'                 => $users_id,
                                                'data-glpicore-ma-tags' => 'common']);
            echo "<table class='tab_cadre_fixe' aria-label='List of Items with Actions'>";
            echo "<tr>";
            echo "<th width='10'>";
            echo Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            echo "</th>";
            echo "<th colspan='2'>" . _n('Type', 'Types', 1) . "</th></tr>";
            while ($data = $iterator->next()) {
                echo "<tr class='tab_bg_1'><td width='10'>";
                Html::showMassiveActionCheckBox(__CLASS__, $data["itemtype"]);
                echo "</td>";
                if ($item = getItemForItemtype($data["itemtype"])) {
                    $name = $item->getTypeName(1);
                } else {
                    $name = $data["itemtype"];
                }
                echo "<td>$name</td><td class='numeric'>" . $data['nb'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
            echo "</div>";
        } else {
            echo "<table class='tab_cadre_fixe' aria-label='No item found'>";
            echo "<tr class='tab_bg_2'><td class='b center'>" . __('No item found') . "</td></tr>";
            echo "</table>";
        }
    }


    /**
     * For tab management : force isNewItem
     *
     * @since 0.83
    **/
    public function isNewItem()
    {
        return false;
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addStandardTab(__CLASS__, $ong, $options);
        $ong['no_all_tab'] = true;
        return $ong;
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case __CLASS__:
                $ong = [];
                $ong[1] = __('Global View');
                if (Session::haveRight(self::$rightname, self::PERSONAL)) {
                    $ong[2] = __('Personal View');
                }
                return $ong;
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'Preference':
                self::showForUser(Session::getLoginUserID());
                return true;

            case __CLASS__:
                if (!($item instanceof self)) {
                    return false;
                }
                $display = $item;
                switch ($tabnum) {
                    case 1:
                        $display->showFormGlobal(Toolbox::cleanTarget($_GET['_target']), $_GET["displaytype"]);
                        return true;

                    case 2:
                        Session::checkRight(self::$rightname, self::PERSONAL);
                        $display->showFormPerso(Toolbox::cleanTarget($_GET['_target']), $_GET["displaytype"]);
                        return true;
                }
        }
        return false;
    }


    public function getRights($interface = 'central')
    {

        //TRANS: short for : Search result user display
        $values[self::PERSONAL]  = ['short' => __('User display'),
                                         'long'  => __('Search result user display')];
        //TRANS: short for : Search result default display
        $values[self::GENERAL]  =  ['short' => __('Default display'),
                                         'long'  => __('Search result default display')];

        return $values;
    }
}
