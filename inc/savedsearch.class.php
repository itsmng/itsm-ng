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
 * Saved searches class
 *
 * @since 9.2
**/
class SavedSearch extends CommonDBTM implements ExtraVisibilityCriteria
{
    public static $rightname               = 'bookmark_public';

    public const SEARCH = 1; //SEARCH SYSTEM bookmark
    public const URI    = 2;
    public const ALERT  = 3; //SEARCH SYSTEM search alert

    public const COUNT_NO = 0;
    public const COUNT_YES = 1;
    public const COUNT_AUTO = 2;


    public static function getForbiddenActionsForMenu()
    {
        return ['add'];
    }


    public static function getTypeName($nb = 0)
    {
        return _n('Saved search', 'Saved searches', $nb);
    }


    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    public function getSpecificMassiveActions($checkitem = null)
    {

        $actions[get_called_class() . MassiveAction::CLASS_ACTION_SEPARATOR . 'unset_default']
                       = __('Unset as default');
        $actions[get_called_class() . MassiveAction::CLASS_ACTION_SEPARATOR . 'change_count_method']
                       = __('Change count method');
        if (Session::haveRight('transfer', READ)) {
            $actions[get_called_class() . MassiveAction::CLASS_ACTION_SEPARATOR . 'change_entity']
                        = __('Change visibility');
        }
        return $actions;
    }


    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {

        switch ($ma->getAction()) {
            case 'change_count_method':
                $values = [self::COUNT_AUTO  => __('Auto'),
                           self::COUNT_YES   => __('Yes'),
                           self::COUNT_NO    => __('No')];
                Dropdown::showFromArray('do_count', $values, ['width' => '20%']);
                break;

            case 'change_entity':
                Entity::dropdown(['entity' => $_SESSION['glpiactiveentities'],
                                  'value'  => $_SESSION['glpiactive_entity'],
                                  'name'   => 'entities_id']);
                echo '<br/>';
                echo __('Child entities');
                Dropdown::showYesNo('is_recursive');
                echo '<br/>';
                break;
        }
        return parent::showMassiveActionsSubForm($ma);
    }


    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {
        $input = $ma->getInput();
        switch ($ma->getAction()) {
            case 'unset_default':
                if ($item->unmarkDefaults($ids)) {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_OK);
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                }
                return;
                break;

            case 'change_count_method':
                if ($item->setDoCount($ids, $input['do_count'])) {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_OK);
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                }
                break;

            case 'change_entity':
                if ($item->setEntityRecur($ids, $input['entities_id'], $input['is_recursive'])) {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_OK);
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                }
                break;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }


    public function canCreateItem()
    {

        if ($this->fields['is_private'] == 1) {
            return (Session::haveRight('config', UPDATE)
                    || $this->fields['users_id'] == Session::getLoginUserID());
        }
        return parent::canCreateItem();
    }


    public function canViewItem()
    {

        if ($this->fields['is_private'] == 1) {
            return (Session::haveRight('config', READ)
                    || $this->fields['users_id'] == Session::getLoginUserID());
        }
        return parent::canViewItem();
    }


    public function isNewItem()
    {
        /// For tabs management : force isNewItem
        return false;
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong)
             ->addStandardTab('SavedSearch_Alert', $ong, $options);
        return $ong;
    }


    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = ['id'                 => 'common',
                  'name'               => __('Characteristics')
                 ];

        $tab[] = ['id'                 => '1',
                  'table'              => $this->getTable(),
                  'field'              => 'name',
                  'name'               => __('Name'),
                  'datatype'           => 'itemlink',
                  'massiveaction'      => false, // implicit key==1
                  'autocomplete'       => true,
                 ];

        $tab[] = ['id'                 => '2',
                  'table'              => $this->getTable(),
                  'field'              => 'id',
                  'name'               => __('ID'),
                  'massiveaction'      => false, // implicit field is id
                  'datatype'           => 'number'
                 ];

        $tab[] = ['id'                 => 3,
                  'table'              => User::getTable(),
                  'field'              => 'name',
                  'name'               => User::getTypeName(1),
                  'datatype'           => 'dropdown'
                 ];

        $tab[] = [
           'id'  => 4,
           'table'           => $this->getTable(),
           'field'           => 'is_private',
           'name'            => __('Is private'),
           'datatype'        => 'bool',
           'massiveaction'   => false,
        ];

        $tab[] = ['id'                 => '8',
                  'table'              => $this->getTable(),
                  'field'              => 'itemtype',
                  'name'               => __('Item type'),
                  'massiveaction'      => false,
                  'datatype'           => 'itemtypename',
                  'types'              => self::getUsedItemtypes()
                 ];

        $tab[] = ['id'                 => 9,
                  'table'              => $this->getTable(),
                  'field'              => 'last_execution_time',
                  'name'               => __('Last duration (ms)'),
                  'massiveaction'      => false,
                  'datatype'           => 'number'
                 ];

        $tab[] = ['id'                 => 10,
                  'table'              => $this->getTable(),
                  'field'              => 'do_count',
                  'name'               => __('Count'),
                  'massiveaction'      => true,
                  'datatype'           => 'specific',
                  'searchtype'         => 'equals'
                 ];

        $tab[] = [
           'id'            => 11,
           'table'         => SavedSearch_User::getTable(),
           'field'         => 'users_id',
           'name'          => __('Default'),
           'massiveaction' => false,
           'joinparams'    => [
              'jointype'  => 'child',
              'condition' => "AND NEWTABLE.users_id = " . Session::getLoginUserID()
           ],
           'datatype'      => 'specific',
           'searchtype'    => [
              0 => 'equals',
              1 => 'notequals'
           ],
        ];

        $tab[] = ['id'                 => 12,
                  'table'              => $this->getTable(),
                  'field'              => 'counter',
                  'name'               => __('Counter'),
                  'massiveaction'      => false,
                  'datatype'           => 'number'
                 ];

        $tab[] = ['id'                 => 13,
                  'table'              => $this->getTable(),
                  'field'              => 'last_execution_date',
                  'name'               => __('Last execution date'),
                  'massiveaction'      => false,
                  'datatype'           => 'datetime'
                 ];

        return $tab;
    }


    public function prepareInputForAdd($input)
    {

        if (!isset($input['url']) || !isset($input['type'])) {
            return false;
        }

        $taburl = parse_url(Toolbox::unclean_cross_side_scripting_deep(Toolbox::stripslashes_deep($input['url'])));

        $key = "plugins";

        $index  = strpos($taburl["path"], $key);
        if (!$index) {
            $index = strpos($taburl["path"], "front");
        }
        $input['path'] = Toolbox::substr(
            $taburl["path"],
            $index,
            Toolbox::strlen($taburl["path"]) - $index
        );

        $query_tab = [];

        if (isset($taburl["query"])) {
            parse_str($taburl["query"], $query_tab);
        }

        $input['query'] = Toolbox::append_params(
            $this->prepareQueryToStore(
                $input['type'],
                $query_tab
            )
        );

        return $input;
    }


    public function pre_updateInDB()
    {

        // Set new user if initial user have been deleted
        if (
            ($this->fields['users_id'] == 0)
            && ($uid = Session::getLoginUserID())
        ) {
            $this->input['users_id']  = $uid;
            $this->fields['users_id'] = $uid;
            $this->updates[]          = "users_id";
        }
    }


    public function post_getEmpty()
    {

        $this->fields["users_id"]     = Session::getLoginUserID();
        $this->fields["is_private"]   = 1;
        $this->fields["is_recursive"] = 1;
        $this->fields["entities_id"]  = $_SESSION["glpiactive_entity"];
    }


    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
              SavedSearch_Alert::class,
              SavedSearch_User::class,
            ]
        );
    }


    /**
     * Print the saved search form
     *
     * @param integer $ID      ID of the item
     * @param array   $options possible options:
     *                         - target for the Form
     *                         - type when adding
     *                         - url when adding
     *                         - itemtype when adding
     *
     * @return void
    **/
    public function showForm($ID, $options = [])
    {

        $ID = $this->getID();

        $form = [
           'action' => $this->getFormURL(),
           'itemtype' => $this::class,
           'content' => [
              '' => [
                 'visible' => true,
                 'inputs' => [
                    isset($options['itemtype']) ? [
                       'type' => 'hidden',
                       'name' => 'itemtype',
                       'value' => $options['itemtype'],
                    ] : [],
                    isset($options['type']) ? [
                       'type' => 'hidden',
                       'name' => 'type',
                       'value' => $options['type'],
                    ] : [],
                    isset($options['url']) ? [
                       'type' => 'hidden',
                       'name' => 'url',
                       'value' => $options['url'],
                    ] : [],
                    __('Name') => [
                       'type' => 'text',
                       'name' => 'name',
                            'value' => $this->fields['name'] ?? '',
                    ],
                    __('Do count') => (Session::haveRight("config", UPDATE)) ? [
                       'type' => 'select',
                       'name' => 'do_count',
                       'values' => [
                                self::COUNT_AUTO => __('Auto'),
                                self::COUNT_YES => __('Yes'),
                                self::COUNT_NO => __('No')
                       ],
                            'value' => $this->fields['do_count'] ?? self::COUNT_NO,
                    ] : [],
                    __('Visibility') => $this->canCreate() ? [
                       'type' => 'select',
                       'name' => 'is_private',
                       'values' => [__('Public'), __('Private')],
                            'value' => $this->fields['is_private'] ?? 0,
                    ] : [
                       'content' => $this->fields['is_private'] ? __('Private') : __('Public')
                    ],
                    Entity::getTypeName(1) => $this->canCreate() ? [
                       'type' => 'select',
                       'name' => 'entities_id',
                       'values' => getOptionForItems(Entity::class),
                            'value' => $this->fields['entities_id'] ?? 0,
                       'actions' => getItemActionButtons(['info', 'add'], Entity::class),
                       ] : [],
                    __('Child entities') => [
                       'type' => 'checkbox',
                       'name' => 'is_recursive',
                            'value' => $this->fields['is_recursive'] ?? 0,
                        ]
                 ]
              ]
           ]
        ];
        renderTwigForm($form, '', $this->fields);
    }


    /**
     * Prepare query to store depending of the type
     *
     * @param integer $type      Saved search type (self::SEARCH, self::URI or self::ALERT)
     * @param array   $query_tab Parameters
     *
     * @return clean query array
    **/
    protected function prepareQueryToStore($type, $query_tab)
    {

        switch ($type) {
            case self::SEARCH:
            case self::ALERT:
                $fields_toclean = ['add_search_count',
                                   'add_search_count2',
                                   'delete_search_count',
                                   'delete_search_count2',
                                   'start',
                                   '_glpi_csrf_token'
                                  ];
                foreach ($fields_toclean as $field) {
                    if (isset($query_tab[$field])) {
                        unset($query_tab[$field]);
                    }
                }
                break;
        }
        return $query_tab;
    }


    /**
     * Prepare query to use depending of the type
     *
     * @param integer $type      Saved search type (see SavedSearch constants)
     * @param array   $query_tab Parameters array
     *
     * @return prepared query array
    **/
    public function prepareQueryToUse($type, $query_tab)
    {

        switch ($type) {
            case self::SEARCH:
            case self::ALERT:
                // Check if all data are valid
                $opt            = Search::getCleanedOptions($this->fields['itemtype']);
                $query_tab_save = $query_tab;
                $partial_load   = false;
                // Standard search
                if (isset($query_tab_save['criteria']) && count($query_tab_save['criteria'])) {
                    unset($query_tab['criteria']);
                    $new_key = 0;
                    foreach ($query_tab_save['criteria'] as $key => $val) {
                        if (
                            isset($val['field'])
                            && $val['field'] != 'view'
                            && $val['field'] != 'all'
                            && (!isset($opt[$val['field']])
                                || (isset($opt[$val['field']]['nosearch'])
                                    && $opt[$val['field']]['nosearch']))
                        ) {
                            $partial_load = true;
                        } else {
                            $query_tab['criteria'][$new_key] = $val;
                            $new_key++;
                        }
                    }
                }
                // Meta search
                if (isset($query_tab_save['metacriteria']) && count($query_tab_save['metacriteria'])) {
                    $meta_ok = Search::getMetaItemtypeAvailable($query_tab['itemtype']);
                    unset($query_tab['metacriteria']);
                    $new_key = 0;
                    foreach ($query_tab_save['metacriteria'] as $key => $val) {
                        if (isset($val['itemtype'])) {
                            $opt = Search::getCleanedOptions($val['itemtype']);
                        }
                        // Use if meta type is valid and option available
                        if (
                            !isset($val['itemtype']) || !in_array($val['itemtype'], $meta_ok)
                            || !isset($opt[$val['field']])
                        ) {
                            $partial_load = true;
                        } else {
                            $query_tab['metacriteria'][$new_key] = $val;
                            $new_key++;
                        }
                    }
                }
                // Display message
                if ($partial_load) {
                    Session::addMessageAfterRedirect(__('Partial load of the saved search.'), false, ERROR);
                }
                // add reset value
                $query_tab['reset'] = 'reset';
                break;
        }
        return $query_tab;
    }


    /**
     * Load a saved search
     *
     * @param integer $ID ID of the saved search
     *
     * @return void
    **/
    public function load($ID)
    {
        global $CFG_GLPI;

        if ($params = $this->getParameters($ID)) {
            $url  = $CFG_GLPI['root_doc'] . "/" . rawurldecode($this->fields["path"]);
            $url .= "?" . Toolbox::append_params($params);

            Html::redirect($url);
        }
    }


    /**
     * Get saved search parameters
     *
     * @param integer $ID ID of the saved search
     *
     * @return array|false
    **/
    public function getParameters($ID)
    {

        if ($this->getFromDB($ID)) {
            $query_tab = [];
            parse_str($this->fields["query"], $query_tab);
            $query_tab['savedsearches_id'] = $ID;
            if (class_exists($this->fields['itemtype']) || $this->fields['itemtype'] == 'AllAssets') {
                return $this->prepareQueryToUse($this->fields["type"], $query_tab);
            }
        }
        return false;
    }


    /**
     * Mark saved search as default view for the currect user
     *
     * @param integer $ID ID of the saved search
     *
     * @return void
    **/
    public function markDefault($ID)
    {
        global $DB;

        if (
            $this->getFromDB($ID)
            && ($this->fields['type'] != self::URI)
        ) {
            $dd = new SavedSearch_User();
            // Is default view for this itemtype already exists ?
            $iterator = $DB->request([
               'SELECT' => 'id',
               'FROM'   => 'glpi_savedsearches_users',
               'WHERE'  => [
                  'users_id'  => Session::getLoginUserID(),
                  'itemtype'  => $this->fields['itemtype']
               ]
            ]);

            if ($result = $iterator->next()) {
                // already exists update it
                $updateID = $result['id'];
                $dd->update([
                   'id'                 => $updateID,
                   'savedsearches_id'   => $ID
                ]);
            } else {
                $dd->add([
                   'savedsearches_id'   => $ID,
                   'users_id'           => Session::getLoginUserID(),
                   'itemtype'           => $this->fields['itemtype']
                ]);
            }
        }
    }


    /**
     * Unmark savedsearch as default view for the current user
     *
     * @param integer $ID ID of the saved search
     *
     * @return void
    **/
    public function unmarkDefault($ID)
    {
        global $DB;

        if (
            $this->getFromDB($ID)
            && ($this->fields['type'] != self::URI)
        ) {
            $dd = new SavedSearch_User();
            // Is default view for this itemtype already exists ?
            $iterator = $DB->request([
               'SELECT' => 'id',
               'FROM'   => 'glpi_savedsearches_users',
               'WHERE'  => [
                  'users_id'           => Session::getLoginUserID(),
                  'savedsearches_id'   => $ID,
                  'itemtype'           => $this->fields['itemtype']
               ]
            ]);

            if ($result = $iterator->next()) {
                // already exists delete it
                $deleteID = $result['id'];
                $dd->delete(['id' => $deleteID]);
            }
        }
    }


    /**
     * Unmark savedsearch as default view
     *
     * @param array $ids IDs of the saved searches
     *
     * @return boolean
    **/
    public function unmarkDefaults(array $ids)
    {
        global $DB;

        if (Session::haveRight('config', UPDATE)) {
            return $DB->delete(
                'glpi_savedsearches_users',
                [
                  'savedsearches_id'   => $ids
                ]
            );
        }
    }


    /**
     * Show user searches list
     *
     * @return void
     */
    public function displayMine()
    {
        global $DB, $CFG_GLPI;

        $table = $this->getTable();
        $utable = 'glpi_savedsearches_users';
        $criteria = [
           'SELECT'    => [
              "$table.*",
              "$utable.id AS IS_DEFAULT"
           ],
           'FROM'      => $table,
           'LEFT JOIN' => [
              $utable => [
                 'ON' => [
                    $utable  => 'savedsearches_id',
                    $table   => 'id', [
                       'AND' => [
                          "$table.itemtype"    => new \QueryExpression("$utable.itemtype"),
                          "$utable.users_id"   => Session::getLoginUserID()
                       ]
                    ]
                 ]
              ]
           ],
           'WHERE'     => [],
           'ORDERBY'   => [
              'itemtype',
              'name'
           ]
        ];

        $public_criteria = $criteria;
        if ($this->canView()) {
            $public_criteria['WHERE'] = [
               "$table.is_private"  => 0,
            ] + getEntitiesRestrictCriteria($table, '', '', true);
        }
        $public_iterator = $DB->request($public_criteria);

        $private_criteria = $criteria;
        $private_criteria['WHERE'] = [
           "$table.is_private"  => 1,
           "$table.users_id"    => Session::getLoginUserID()
        ] + getEntitiesRestrictCriteria($table, '', '', true);
        $private_iterator = $DB->request($private_criteria);

        // get saved searches
        $searches = ['private'   => [],
                     'public'    => []];

        while ($data = $private_iterator->next()) {
            $searches['private'][$data['id']] = $data;
        }

        while ($data = $public_iterator->next()) {
            $searches['public'][$data['id']] = $data;
        }

        $ordered = [];

        // get personal order
        $user               = new User();
        $personalorderfield = $this->getPersonalOrderField();

        $personalorder = [];
        if ($user->getFromDB(Session::getLoginUserID())) {
            $personalorder = importArrayFromDB($user->fields[$personalorderfield]);
        }
        if (!is_array($personalorder)) {
            $personalorder = [];
        }

        // Add on personal order
        if (count($personalorder)) {
            foreach ($personalorder as $val) {
                if (isset($searches['private'][$val])) {
                    $ordered[$val] = $searches['private'][$val];
                    unset($searches['private'][$val]);
                }
            }
        }

        // Add unsaved in order
        if (count($searches['private'])) {
            foreach ($searches['private'] as $key => $val) {
                $ordered[$key] = $val;
            }
        }

        // New: save order
        $store = array_keys($ordered);
        $user->update(['id'                => Session::getLoginUserID(),
                       $personalorderfield => exportArrayToDB($store)]);
        $searches['private'] = $ordered;

        $rand    = mt_rand();

        echo "<div class='center' id='tabsbody' >";

        $colspan = 2;
        echo "<table class='tab_cadre_fixehov' aria-label='Filter List'>";
        echo "<thead><tr><th colspan='$colspan' class='search_header'>" .
                    "<input type='text' id='filter_savedsearch' placeholder='" . __('Filter list') . "' style='width: 95%; padding: 5px'></i>" .
             "</th></tr></thead>";
        echo "<thead><tr><th colspan='$colspan' class='private_header'>" .
                    sprintf(
                        _n('Private %1$s', 'Private %1$s', count($searches['private'])),
                        $this->getTypeName(count($searches['private']))
                    ) .
                    "<i class='toggle fa fa-chevron-circle-up' title='" . __('Hide/Show elements') . "'></i>" .
             "</th></tr></thead><tbody>";
        echo $this->displaySavedSearchType($searches['private']);
        echo "</tbody>";
        if ($this->canView()) {
            echo "<thead><tr><th colspan='$colspan'>" .
                        sprintf(
                            _n('Public %1$s', 'Public %1$s', count($searches['public'])),
                            $this->getTypeName(count($searches['public']))
                        ) .
                        "<i class='toggle fa fa-chevron-circle-up' title='" . __('Hide/Show elements') . "'></i>" .
                 "</th></tr></thead><tbody>";
            echo $this->displaySavedSearchType($searches['public']);
            echo "</tbody>";
        }
        echo "</table></div>";
        Html::closeForm();

        if (count($searches['private']) || count($searches['public'])) {
            $js = "$(function() {
            $('.countSearches').on('click', function(e) {
               e.preventDefault();
               var _this = $(this);
               var _dest = _this.closest('tr').find('span.count');
               $.ajax({
                  url: _this.attr('href'),
                  beforeSend: function() {
                     var _img = '<span id=\'loading\'><img src=\'{$CFG_GLPI["root_doc"]}/pics/spinner.gif\' alt=\'" . addslashes(__('Loading...')) . "\'/></span>';
                     _dest.append(_img);
                  },
                  success: function(res) {
                     _dest.html(' (' + res.count + ')');
                  },
                  complete: function() {
                     $('#loading').remove();
                  }
               });
            });\n

            $('.slidepanel .default').on('click', function(e) {
               e.preventDefault();
               var _this = $(this);
               var _currentclass = (_this.hasClass('bookmark_record') ? 'bookmark_record' : 'bookmark_default');
               $.ajax({
                  url: _this.attr('href').replace(/\/front\//, '/ajax/'),
                  beforeSend: function() {
                     _this
                        .removeClass(_currentclass)
                        .addClass('fa-spinner fa-spin')
                  },
                  success: function(res) {
                     $('#showSavedSearches .contents').html(res);
                  },
                  error: function() {
                     alert('" . addslashes(__('Default bookmark has not been changed!'))  . "');
                     _this.addClass(_currentclass);
                  },
                  complete: function() {
                     _this.removeClass('fa-spin').removeClass('fa-spinner');
                  }
               });
            });\n

            $('.slidepanel .toggle').on('click', function() {
               var _this = $(this);
               var _elt = _this.parents('thead').next('tbody');
               _elt.toggle();
               if (_elt.is(':visible')) {
                  _this.removeClass('fa-chevron-circle-down')
                     .addClass('fa-chevron-circle-up');
               } else {
                  _this.removeClass('fa-chevron-circle-up')
                     .addClass('fa-chevron-circle-down');
               }
            });
            $('#filter_savedsearch').on('keyup', function() {
               var _this = $(this);
               var searchtext = _this.val() + '';
               var searchparts = searchtext.toLowerCase().split(/\s+/);
               var _rows = _this.parents('table').find('tbody tr');
               _rows.each(function() {
                  var _row = $(this);
                  var rowtext = _row.text().toLowerCase();

                  var show = true;

                  for (var i=0; i < searchparts.length; i++) {
                     if (rowtext.indexOf(searchparts[i]) == -1) {
                        show = false;
                        break;
                     }
                  }

                  if (show) {
                     _row.show();
                  } else {
                     _row.hide();
                  }
               });
            });

         });";

            echo Html::scriptBlock($js);
        }
    }


    /**
     * Display saved searches from a type
     *
     * @param string $searches Search type
     *
     * @return void
    **/
    private function displaySavedSearchType($searches)
    {
        global $CFG_GLPI;

        if ($totalcount = count($searches)) {
            $current_type      = -1;
            $number            = 0;
            $current_type_name = NOT_AVAILABLE;
            $is_private        = null;

            foreach ($searches as $key => $this->fields) {
                $number++;
                if ($current_type != $this->fields['itemtype']) {
                    $current_type      = $this->fields['itemtype'];
                    $current_type_name = NOT_AVAILABLE;

                    if ($current_type == "AllAssets") {
                        $current_type_name = __('Global');
                    } elseif ($item = getItemForItemtype($current_type)) {
                        $current_type_name = $item->getTypeName(Session::getPluralNumber());
                    }
                }

                if ($_SESSION['glpishow_count_on_tabs']) {
                    $count = null;
                    try {
                        $data = $this->execute();
                    } catch (\RuntimeException $e) {
                        Toolbox::logError($e);
                        $data = false;
                    }
                    if (isset($data['data']['totalcount'])) {
                        $count = $data['data']['totalcount'];
                    } else {
                        $info_message = ($this->fields['do_count'] == self::COUNT_NO)
                                         ? __s('Count for this saved search has been disabled.')
                                         : __s('Counting this saved search would take too long, it has been skipped.');
                        if ($count === null) {
                            //no count, just inform the user
                            $count = "<span class='fa fa-info-circle' title='$info_message'></span>";
                        }
                    }
                }

                if ($is_private === null) {
                    $is_private = ($this->fields['is_private'] == 1);
                }

                echo "<tr class='tab_bg_1";
                if ($is_private) {
                    echo " private' data-position='$number' data-id='{$this->getID()}";
                }
                echo "'>";
                echo "<td class='small no-wrap'>";
                if (is_null($this->fields['IS_DEFAULT'])) {
                    echo "<a class='default fa fa-star bookmark_record' href=\"" .
                            $this->getSearchURL() . "?action=edit&amp; mark_default=1&amp;id=" .
                            $this->fields["id"] . "\" title=\"" . __s('Not default search') . "\">" .
                            "<span class='sr-only'>" . __('Not default search')  . "</span></a>";
                } else {
                    echo "<a class='default fa fa-star bookmark_default' href=\"" .
                            $this->getSearchURL() . "?action=edit&amp;mark_default=0&amp;id=" .
                            $this->fields["id"] . "\" title=\"" . __s('Default search') . "\">" .
                            "<span class='sr-only'>" . __('Default search') . "</span></a>";
                }
                echo "</td>";
                echo "<td>";
                $text = sprintf(__('%1$s on %2$s'), $this->fields['name'], $current_type_name);

                $title = ($is_private ? __s('Click to load or drag and drop to reorder')
                                      : __s('Click to load'));
                echo "<a class='savedsearchlink' href=\"" . $this->getSearchURL() . "?action=load&amp;id=" .
                         $this->fields["id"] . "\" title='" . $title . "'>" .
                         $text;
                if ($_SESSION['glpishow_count_on_tabs']) {
                    echo "<span class='primary-bg primary-fg count'>$count</span>";
                }
                echo "</a>";
                echo "</td>";
                echo "</tr>";
            }

            if ($is_private) {
                //private saved searches can be ordered
                $js = "$(function() {
               $('.slidepanel .contents table').sortable({
                  items: 'tr.private',
                  placeholder: 'ui-state-highlight',
                  create: function(event, ui) {
                     $('tr.private td:first-child').each(function() {
                        $(this).prepend('<span class=\'drag\'><img src=\'{$CFG_GLPI['root_doc']}/pics/drag.png\' alt=\'\'/></span>');
                     });
                  },
                  stop: function (event, ui) {
                     var _ids = $('tr.private').map(function(idx, ele) {
                        return $(ele).data('id');
                     }).get();

                     $.ajax({
                        url: '{$CFG_GLPI["root_doc"]}/ajax/savedsearch.php?action=reorder',
                        data: {
                           ids: _ids
                        },
                        beforeSend: function() {
                           var _img = '<span id=\'loading\'><img src=\'{$CFG_GLPI["root_doc"]}/pics/spinner.gif\' alt=\'" . addslashes(__('Loading...')) . "\'/></span>';
                           $('.private_header').prepend(_img);
                        },
                        error: function() {
                           alert('" . addslashes(__('Saved searches order cannot be saved!')) . "');
                        },
                        complete: function() {
                           $('#loading').remove();
                        }
                     });
                  }
               });
            });";

                echo Html::scriptBlock($js);
            }
        } else {
            echo "<tr class='tab_bg_1'><td colspan='3'>";
            echo sprintf(__('You have not recorded any %1$s yet'), mb_strtolower($this->getTypeName(1)));
            echo "</td></tr>";
        }
    }


    /**
     * Save order
     *
     * @param array $items Ordered ids
     *
     * @return boolean
     */
    public function saveOrder(array $items)
    {
        if (count($items)) {
            $user               = new User();
            $personalorderfield = $this->getPersonalOrderField();

            $user->update(['id'                 => Session::getLoginUserID(),
                           $personalorderfield  => exportArrayToDB($items)]);
            return true;
        }
        return false;
    }


    /**
     * Display buttons
     *
     * @param integer $type     SavedSearch type to use
     * @param integer $itemtype Device type of item where is the bookmark (default 0)
     *
     * @return void
    **/
    public static function showSaveButton($type, $itemtype = 0)
    {
        global $CFG_GLPI;

        echo "<a href='#' onClick=\"savesearch.dialog('open'); return false;\"
             class='fa fa-star bookmark_record save' title='" . __s('Save current search') . "'>";
        echo "<span class='sr-only'>" . __s('Save current search') . "</span>";
        echo "</a>";

        Ajax::createModalWindow(
            'savesearch',
            $CFG_GLPI['root_doc'] .
                                   "/ajax/savedsearch.php?action=create&itemtype=$itemtype&type=$type&url=" .
                                   rawurlencode($_SERVER["REQUEST_URI"]),
            ['title'       => __('Save current search')]
        );
    }


    /**
     * Get personal order field name
     *
     * @return string
    **/
    protected function getPersonalOrderField()
    {
        return 'privatebookmarkorder';
    }


    /**
     * Get all itemtypes used
     *
     * @return array of itemtypes
    **/
    public static function getUsedItemtypes()
    {
        global $DB;

        $types = [];
        $iterator = $DB->request([
           'SELECT'          => 'itemtype',
           'DISTINCT'        => true,
           'FROM'            => static::getTable()
        ]);
        while ($data = $iterator->next()) {
            $types[] = $data['itemtype'];
        }
        return $types;
    }


    /**
     * Update bookmark execution time after it has been loaded
     *
     * @param integer $id   Saved search ID
     * @param integer $time Execution time, in milliseconds
     *
     * @return void
    **/
    public static function updateExecutionTime($id, $time)
    {
        global $DB;

        if ($_SESSION['glpishow_count_on_tabs']) {
            $DB->update(
                static::getTable(),
                [
                  'last_execution_time'   => $time,
                  'last_execution_date'   => date('Y-m-d H:i:s'),
                  'counter'               => new \QueryExpression($DB->quoteName('counter') . ' + 1')
                ],
                [
                  'id' => $id
                ]
            );
        }
    }


    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'do_count':
                switch ($values[$field]) {
                    case SavedSearch::COUNT_NO:
                        return __('No');

                    case SavedSearch::COUNT_YES:
                        return __('Yes');

                    case SavedSearch::COUNT_AUTO:
                        return ('Auto');
                }
                break;
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }


    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;

        switch ($field) {
            case 'do_count':
                $options['name']  = $name;
                $options['value'] = $values[$field];
                return self::dropdownDoCount($options);
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }


    /**
     * Dropdown of do_count possible values
     *
     * @param array $options array of options:
     *                       - name     : select name (default is do_count)
     *                       - value    : default value (default self::COUNT_AUTO)
     *                       - display  : boolean if false get string
     *
     * @return void|string
    **/
    public static function dropdownDoCount(array $options = [])
    {

        $p['name']      = 'do_count';
        $p['value']     = self::COUNT_AUTO;
        $p['display']   = true;

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $p[$key] = $val;
            }
        }

        $tab = [self::COUNT_AUTO  => __('Auto'),
                self::COUNT_YES   => __('Yes'),
                self::COUNT_NO    => __('No')];

        return Dropdown::showFromArray($p['name'], $tab, $p);
    }


    /**
     * Set do_count from massive actions
     *
     * @param array   $ids      Items IDs
     * @param integer $do_count One of self::COUNT_*
     *
     * @return boolean
     */
    public function setDoCount(array $ids, $do_count)
    {
        global $DB;

        $result = $DB->update(
            $this->getTable(),
            [
              'do_count' => $do_count
            ],
            [
              'id' => $ids
            ]
        );
        return $result;
    }


    /**
     * Set entity and recursivity from massive actions
     *
     * @param array   $ids   Items IDs
     * @param integer $eid   Entityy ID
     * @param boolean $recur Recursivity
     *
     * @return boolean
     */
    public function setEntityRecur(array $ids, $eid, $recur)
    {
        global $DB;

        $result = $DB->update(
            $this->getTable(),
            [
              'entities_id'  => $eid,
              'is_recursive' => $recur
            ],
            [
              'id' => $ids
            ]
        );
        return $result;
    }


    public static function cronInfo($name)
    {

        switch ($name) {
            case 'countAll':
                return ['description' => __('Update all bookmarks execution time')];
        }
        return [];
    }


    /**
     * Update all bookmarks execution time
     *
     * @param CronTask $task CronTask instance
     *
     * @return void
    **/
    public static function croncountAll($task)
    {
        global $DB, $CFG_GLPI;

        $cron_status = 0;

        if ($CFG_GLPI['show_count_on_tabs'] != -1) {
            $lastdate = new \DateTime($task->getField('lastrun'));
            $lastdate->sub(new \DateInterval('P7D'));

            $iterator = $DB->request(['FROM'   => self::getTable(),
                                      'FIELDS' => ['id', 'query', 'itemtype', 'type'],
                                      'WHERE'  => ['last_execution_date'
                                                   => ['<' , $lastdate->format('Y-m-d H:i:s')]]]);

            if ($iterator->numrows()) {
                //prepare variables we'll use
                $self = new self();
                $now = date('Y-m-d H:i:s');

                $query = $DB->buildUpdate(
                    self::getTable(),
                    [
                      'last_execution_time'   => new QueryParam(),
                      'last_execution_date'   => new QueryParam()
                    ],
                    [
                      'id'                    => new QueryParam()
                    ]
                );
                $stmt = $DB->prepare($query);

                if (!isset($_SESSION['glpiname'])) {
                    //required from search class
                    $_SESSION['glpiname'] = 'crontab';
                }
                if (!isset($_SESSION['glpigroups'])) {
                    $_SESSION['glpigroups'] = [];
                }

                $in_transaction = $DB->inTransaction();
                if (!$in_transaction) {
                    $DB->beginTransaction();
                }
                while ($row = $iterator->next()) {
                    try {
                        $self->fields = $row;
                        if ($data = $self->execute(true)) {
                            $execution_time = $data['data']['execution_time'];

                            $stmt->bind_param('sss', $execution_time, $now, $row['id']);
                            $stmt->execute();
                        }
                    } catch (\Exception $e) {
                        Toolbox::logError($e);
                    }
                }

                $stmt->close();
                if (!$in_transaction) {
                    $DB->commit();
                }

                $cron_status = 1;
            }
        } else {
            Toolbox::logWarning('Count on tabs has been disabled; crontask is inefficient.');
        }

        return $cron_status;
    }


    /**
     * Execute current saved search and return results
     *
     * @param boolean $force Force query execution even if it should not be executed
     *                       (default false)
     *
     * @throws RuntimeException
     *
     * @return array
    **/
    public function execute($force = false)
    {
        global $CFG_GLPI;

        if (
            ($force === true)
            || (($this->fields['do_count'] == self::COUNT_YES)
                || ($this->fields['do_count'] == self::COUNT_AUTO)
                && ($this->getField('last_execution_time') != null)
                && ($this->fields['last_execution_time'] <= $CFG_GLPI['max_time_for_count']))
        ) {
            $search = new Search();
            //Do the same as self::getParameters() but getFromDB is useless
            $query_tab = [];
            parse_str($this->getField('query'), $query_tab);

            $params = null;
            if (
                class_exists($this->getField('itemtype'))
                || ($this->getField('itemtype') == 'AllAssets')
            ) {
                $params = $this->prepareQueryToUse($this->getField('type'), $query_tab);
            }

            if (!$params) {
                throw new \RuntimeException('Saved search #' . $this->getID() . ' seems to be broken!');
            } else {
                $data                   = $search->prepareDatasForSearch(
                    $this->getField('itemtype'),
                    $params
                );
                $data['search']['sort'] = null;
                $search->constructSQL($data);
                $search->constructData($data, true);
                return $data;
            }
        }
    }


    /**
     * Create specific notification for a public saved search
     *
     * @return void
     */
    public function createNotif()
    {

        $notif = new Notification();
        $notif->getFromDBByCrit(['event' => 'alert_' . $this->getID()]);

        if ($notif->isNewItem()) {
            $notif->check(-1, CREATE);
            $notif->add(['name'            => SavedSearch::getTypeName(1) . ' ' . addslashes($this->getName()),
                         'entities_id'     => $_SESSION["glpidefault_entity"],
                         'itemtype'        => SavedSearch_Alert::getType(),
                         'event'           => 'alert_' . $this->getID(),
                         'is_active'       => 0,
                         'date_creation' => date('Y-m-d H:i:s')
                        ]);

            Session::addMessageAfterRedirect(__('Notification has been created!'), INFO);
        }
    }

    /**
     * Return visibility SQL restriction to add
     *
     * @return string restrict to add
    **/
    public static function addVisibilityRestrict()
    {
        //not deprecated because used in Search

        if (Session::haveRight('config', UPDATE)) {
            return '';
        }

        //get and clean criteria
        $criteria = self::getVisibilityCriteria();
        unset($criteria['LEFT JOIN']);
        $criteria['FROM'] = self::getTable();

        $it = new \DBmysqlIterator(null);
        $it->buildQuery($criteria);
        $sql = $it->getSql();
        $sql = preg_replace('/.*WHERE /', '', $sql);

        return $sql;
    }

    /**
     * Return visibility joins to add to DBIterator parameters
     *
     * @since 9.4
     *
     * @param boolean $forceall force all joins (false by default)
     *
     * @return array
     */
    public static function getVisibilityCriteria(bool $forceall = false): array
    {
        $criteria = ['WHERE' => []];
        if (Session::haveRight('config', UPDATE)) {
            return $criteria;
        }

        $restrict = [
           self::getTable() . '.is_private' => 1,
           self::getTable() . '.users_id'    => Session::getLoginUserID()
        ];

        if (Session::haveRight(self::$rightname, READ)) {
            $restrict = [
               'OR' => [
                  $restrict,
                  self::getTable() . '.is_private' => 0
               ]
            ];
        }

        $criteria['WHERE'] = $restrict;
        return $criteria;
    }


    public static function getIcon()
    {
        return "far fa-bookmark";
    }
}
