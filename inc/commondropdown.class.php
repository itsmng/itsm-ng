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

/// CommonDropdown class - generic dropdown
abstract class CommonDropdown extends CommonDBTM
{
    // From CommonDBTM
    public $dohistory                   = true;

    // For delete operation (entity will overload this value)
    public $must_be_replace = false;

    //Menu & navigation
    public $first_level_menu  = "config";
    public $second_level_menu = "commondropdown";
    public $third_level_menu  = "";

    public $display_dropdowntitle  = true;

    //This dropdown can be translated
    public $can_be_translated = true;

    public static $rightname = 'dropdown';


    /**
     * @since 0.85
     *
     * @param $nb
    **/
    public static function getTypeName($nb = 0)
    {
        return _n('Dropdown', 'Dropdowns', $nb);
    }


    /**
     * Is translation enabled for this itemtype
     *
     * @since 0.85
     *
     * @return true if translation is available, false otherwise
    **/
    public function maybeTranslated()
    {
        return $this->can_be_translated;
    }


    /**
     * @see CommonGLPI::getMenuShorcut()
     *
     * @since 0.85
    **/
    public static function getMenuShorcut()
    {
        return 'n';
    }


    /**
     *  @see CommonGLPI::getMenuContent()
     *
     *  @since 0.85
    **/
    public static function getMenuContent()
    {

        $menu = [];
        if (get_called_class() == 'CommonDropdown') {
            $menu['title']             = static::getTypeName(Session::getPluralNumber());
            $menu['shortcut']          = 'n';
            $menu['page']              = '/front/dropdown.php';
            $menu['icon']              = self::getIcon();
            $menu['config']['default'] = '/front/dropdown.php';

            $dps = Dropdown::getStandardDropdownItemTypes();
            $menu['options'] = [];

            foreach ($dps as $tab) {
                foreach ($tab as $key => $val) {
                    if ($tmp = getItemForItemtype($key)) {
                        $menu['options'][$key]['title']           = $val;
                        $menu['options'][$key]['page']            = $tmp->getSearchURL(false);
                        $menu['options'][$key]['icon']            = $tmp->getIcon();
                        $menu['options'][$key]['links']['search'] = $tmp->getSearchURL(false);
                        if ($tmp->canCreate()) {
                            $menu['options'][$key]['links']['add'] = $tmp->getFormURL(false);
                        }
                    }
                }
            }
            if (count($menu['options'])) {
                return $menu;
            }
        } else {
            return parent::getMenuContent();
        }
        return false;
    }


    /**
     * Return Additional Fields for this type
     *
     * @return array
    **/
    public function getAdditionalFields()
    {
        global $DB;

        if ($DB->fieldExists($this->getTable(), 'product_number')) {
            return [
               __('Product Number') => [
                  'name' => 'product_number',
                  'type' => 'text',
                  'value' => $this->fields['product_number'],
               ]
            ];
        } else {
            return [];
        }
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        if ($this->dohistory) {
            $this->addStandardTab('Log', $ong, $options);
        }

        if (DropdownTranslation::canBeTranslated($this)) {
            $this->addStandardTab('DropdownTranslation', $ong, $options);
        }

        return $ong;
    }


    /**
     * Display title above search engine
     *
     * @return void HTML display if needed
    **/
    public function title()
    {

        if ($this->display_dropdowntitle) {
            Dropdown::showItemTypeMenu(
                _n('Dropdown', 'Dropdowns', Session::getPluralNumber()),
                Dropdown::getStandardDropdownItemTypes(),
                $this->getSearchURL()
            );
        }
    }


    public function displayHeader()
    {

        if (empty($this->third_level_menu)) {
            $this->third_level_menu = $this->getType();
        }
        Html::header(
            $this->getTypeName(Session::getPluralNumber()),
            '',
            $this->first_level_menu,
            $this->second_level_menu,
            $this->third_level_menu
        );
    }


    /**
     * @since 0.83.3
     *
     * @see CommonDBTM::prepareInputForAdd()
    **/
    public function prepareInputForAdd($input)
    {
        global $DB;

        // if item based on location, create item in the same entity as location
        if (isset($input['locations_id']) && !isset($input['_is_update'])) {
            $iterator = $DB->request([
               'SELECT' => ['entities_id'],
               'FROM'   => 'glpi_locations',
               'WHERE'  => [
                  'id' => $input['locations_id']
               ]
            ]);
            while ($data = $iterator->next()) {
                $input['entities_id'] = $data['entities_id'];
            }
        }

        if (isset($input['name'])) {
            // leading/ending space will break findID/import
            $input['name'] = trim($input['name']);
        }
        if (isset($input['_is_update'])) {
            unset($input['_is_update']);
        }
        return $input;
    }


    /**
     * @since 0.83.3
     *
     * @see CommonDBTM::prepareInputForUpdate()
    **/
    public function prepareInputForUpdate($input)
    {
        //add a "metadata to find if we're on an update or a add
        $input['_is_update'] = true;
        return self::prepareInputForAdd($input);
    }


    public function showForm($ID, $options = [])
    {
        if (!$this->isNewID($ID)) {
            $this->check($ID, READ);
        } else {
            // Create item
            $this->check(-1, CREATE);
        }

        $form = [
          'action' => $this->getFormURL(),
          'itemtype' => $this::class,
          'content' => [
          __('New item') . ' - ' . $this->getTypeName() => [
              'visible' => true,
              'inputs' => [
                !$this->isNewID($ID) ? [
                  'type' => 'hidden',
                  'name' => 'id',
                  'value' => $ID,
                ] : [],
                __('Name') => [
                  'type' => 'text',
                  'name' => isset($this->fields['name']) ? 'name' : 'designation',
                  'value' => $this->fields[isset($this->fields['name']) ? 'name' : 'designation'],
                  'col_lg' => 6,
                ],
                __('Comments') => [
                  'type' => 'textarea',
                  'name' => 'comment',
                  'value' => $this->fields['comment'],
                  'col_lg' => 6,
                ],
              ]
            ]
          ]
        ];

        $fields = $this->getAdditionalFields();
        $form['content'][__('New item') . ' - ' . $this->getTypeName()]['inputs'] = array_merge(
            $form['content'][__('New item') . ' - ' . $this->getTypeName()]['inputs'],
            $fields
        );
        renderTwigForm($form, '', $this->fields);

        if (isset($this->fields['is_protected']) && $this->fields['is_protected']) {
            $options['candel'] = false;
        }

        return true;
    }


    public function displaySpecificTypeField($ID, $field = [])
    {
    }


    public function pre_deleteItem()
    {

        if (isset($this->fields['is_protected']) && $this->fields['is_protected']) {
            return false;
        }
        return true;
    }


    public function rawSearchOptions()
    {
        global $DB;
        $tab = [];

        $tab[] = [
           'id'   => 'common',
           'name' => __('Characteristics')
        ];

        $tab[] = [
           'id'                => '1',
           'table'             => $this->getTable(),
           'field'             => 'name',
           'name'              => __('Name'),
           'datatype'          => 'itemlink',
           'massiveaction'     => false,
           'autocomplete'      => true,
        ];

        $tab[] = [
           'id'                => '2',
           'table'             => $this->getTable(),
           'field'             => 'id',
           'name'              => __('ID'),
           'massiveaction'     => false,
           'datatype'          => 'number'
        ];

        if ($DB->fieldExists($this->getTable(), 'product_number')) {
            $tab[] = [
               'id'  => '3',
               'table'  => $this->getTable(),
               'field'  => 'product_number',
               'name'   => __('Product number'),
               'autocomplete' => true,
            ];
        }

        $tab[] = [
           'id'                => '16',
           'table'             => $this->getTable(),
           'field'             => 'comment',
           'name'              => __('Comments'),
           'datatype'          => 'text'
        ];

        if ($this->isEntityAssign()) {
            $tab[] = [
               'id'             => '80',
               'table'          => 'glpi_entities',
               'field'          => 'completename',
               'name'           => Entity::getTypeName(1),
               'massiveaction'  => false,
               'datatype'       => 'dropdown'
            ];
        }

        if ($this->maybeRecursive()) {
            $tab[] = [
               'id'             => '86',
               'table'          => $this->getTable(),
               'field'          => 'is_recursive',
               'name'           => __('Child entities'),
               'datatype'       => 'bool'
            ];
        }

        if ($this->isField('date_mod')) {
            $tab[] = [
               'id'             => '19',
               'table'          => $this->getTable(),
               'field'          => 'date_mod',
               'name'           => __('Last update'),
               'datatype'       => 'datetime',
               'massiveaction'  => false
            ];
        }

        if ($this->isField('date_creation')) {
            $tab[] = [
               'id'             => '121',
               'table'          => $this->getTable(),
               'field'          => 'date_creation',
               'name'           => __('Creation date'),
               'datatype'       => 'datetime',
               'massiveaction'  => false
            ];
        }

        // add objectlock search options
        $tab = array_merge($tab, ObjectLock::rawSearchOptionsToAdd(get_class($this)));

        return $tab;
    }


    /** Check if the dropdown $ID is used into item tables
     *
     * @return boolean : is the value used ?
    **/
    public function isUsed()
    {
        global $DB;

        $ID = $this->fields['id'];

        $RELATION = getDbRelations();
        if (isset($RELATION[$this->getTable()])) {
            foreach ($RELATION[$this->getTable()] as $tablename => $field) {
                if ($tablename[0] != '_') {
                    if (!is_array($field)) {
                        $row = $DB->request([
                           'FROM'   => $tablename,
                           'COUNT'  => 'cpt',
                           'WHERE'  => [$field => $ID]
                        ])->next();
                        if ($row['cpt'] > 0) {
                            return true;
                        }
                    } else {
                        foreach ($field as $f) {
                            $row = $DB->request([
                               'FROM'   => $tablename,
                               'COUNT'  => 'cpt',
                               'WHERE'  => [$f => $ID]
                            ])->next();
                            if ($row['cpt'] > 0) {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }


    /**
     * Report if a dropdown have Child
     * Used to (dis)allow delete action
    **/
    public function haveChildren()
    {
        return false;
    }


    /**
     * Show a dialog to Confirm delete action
     * And propose a value to replace
     *
     * @param $target string URL
    **/
    public function showDeleteConfirmForm($target)
    {

        if ($this->haveChildren()) {
            echo "<div class='center'><p class='red'>" .
                  __("You can't delete that item, because it has sub-items") . "</p></div>";
            return false;
        }

        $ID = $this->fields['id'];

        echo "<div class='center'><p class='red'>";
        echo __("Caution: you're about to remove a heading used for one or more items.");
        echo "</p>";

        if (!$this->must_be_replace) {
            // Delete form (set to 0)
            echo "<p>" . __('If you confirm the deletion, all uses of this dropdown will be blanked.') .
                 "</p>";
            echo "<form aria-label='Delete Confirm' action='$target' method='post'>";
            echo "<table class='tab_cadre' aria-label='Delete Confirm Form'><tr>";
            echo "<td><input type='hidden' name='id' value='$ID'>";
            echo "<input type='hidden' name='itemtype' value='" . $this->getType() . "' />";
            echo "<input type='hidden' name='forcepurge' value='1'>";
            echo "<input class='submit' type='submit' name='purge'
                value=\"" . _sx('button', 'Confirm') . "\">";
            echo "</td>";
            echo "<td><input class='submit' type='submit' name='annuler'
                    value=\"" . _sx('button', 'Cancel') . "\">";
            echo "</td></tr></table>\n";
            Html::closeForm();
        }

        // Replace form (set to new value)
        echo "<p>" . __('You can also replace all uses of this dropdown by another.') . "</p>";
        echo "<form aria-label='replace' action='$target' method='post'>";
        echo "<table class='tab_cadre' aria-label='Replace Form'><tr><td>";

        if ($this instanceof CommonTreeDropdown) {
            // TreeDropdown => default replacement is parent
            $fk = $this->getForeignKeyField();
            Dropdown::show(
                getItemTypeForTable($this->getTable()),
                ['name'   => '_replace_by',
                                 'value'  => $this->fields[$fk],
                                 'entity' => $this->getEntityID(),
                                 'used'   => getSonsOf($this->getTable(), $ID),
                                 'width'   => '100%']
            );
        } else {
            Dropdown::show(
                getItemTypeForTable($this->getTable()),
                ['name'   => '_replace_by',
                                 'entity' => $this->getEntityID(),
                                 'used'   => [$ID]]
            );
        }
        echo "<input type='hidden' name='id' value='$ID' />";
        echo "<input type='hidden' name='itemtype' value='" . $this->getType() . "' />";
        echo "</td><td>";
        echo "<input class='submit' type='submit' name='replace' value=\"" . _sx('button', 'Replace') . "\">";
        echo "</td><td>";
        echo "<input class='submit' type='submit' name='annuler' value=\"" . _sx('button', 'Cancel') . "\">";
        echo "</td></tr></table>\n";
        Html::closeForm();
        echo "</div>";
    }


    /**
     * check if a dropdown already exists (before import)
     *
     * @param &$input  array of value to import (name)
     *
     * @return integer the ID of the new (or -1 if not found)
    **/
    public function findID(array &$input)
    {
        global $DB;

        if (!empty($input["name"])) {
            $crit = [
               'SELECT' => 'id',
               'FROM'   => $this->getTable(),
               'WHERE'  => [
                  'name'   => $input['name']
               ],
               'LIMIT'  => 1
            ];

            if ($this->isEntityAssign()) {
                $crit['WHERE'] += getEntitiesRestrictCriteria(
                    $this->getTable(),
                    '',
                    $input['entities_id'],
                    $this->maybeRecursive()
                );
            }

            $iterator = $DB->request($crit);

            // Check twin :
            if (count($iterator) > 0) {
                $result = $iterator->next();
                return $result['id'];
            }
        }
        return -1;
    }


    /**
     * Import a dropdown - check if already exists
     *
     * @param $input  array of value to import (name, ...)
     *
     * @return integer|boolean the ID of the new or existing dropdown (-1 or false on failure)
    **/
    public function import(array $input)
    {

        if (!isset($input['name'])) {
            return -1;
        }
        // Clean datas
        $input['name'] = trim($input['name']);

        if (empty($input['name'])) {
            return -1;
        }

        // Check twin :
        if ($ID = $this->findID($input)) {
            if ($ID > 0) {
                return $ID;
            }
        }

        return $this->add($input);
    }


    /**
     * Import a value in a dropdown table.
     *
     * This import a new dropdown if it doesn't exist - Play dictionnary if needed
     *
     * @param string  $value           Value of the new dropdown (need to be addslashes)
     * @param integer $entities_id     Entity in case of specific dropdown (default -1)
     * @param array   $external_params (manufacturer) (need to be addslashes)
     * @param string  $comment         Comment (need to be addslashes)
     * @param boolean $add             if true, add it if not found. if false,
     *                                 just check if exists (true by default)
     *
     * @return integer Dropdown id
    **/
    public function importExternal(
        $value,
        $entities_id = -1,
        $external_params = [],
        $comment = "",
        $add = true
    ) {

        $value = trim($value);
        if (strlen($value) == 0) {
            return 0;
        }

        $ruleinput      = ["name" => stripslashes($value)];
        $rulecollection = RuleCollection::getClassByType($this->getType(), true);

        foreach ($this->additional_fields_for_dictionnary as $field) {
            if (isset($external_params[$field])) {
                $ruleinput[$field] = $external_params[$field];
            } else {
                $ruleinput[$field] = '';
            }
        }
        /*
        switch ($this->getTable()) {
           case "glpi_computermodels" :
           case "glpi_monitormodels" :
           case "glpi_printermodels" :
           case "glpi_peripheralmodels" :
           case "glpi_phonemodels" :
           case "glpi_networkequipmentmodels" :
              $ruleinput["manufacturer"] = $external_params["manufacturer"];
              break;
        }*/

        $input = [
           'name'        => $value,
           'comment'     => $comment,
           'entities_id' => $entities_id,
        ];

        if ($rulecollection) {
            $res_rule = $rulecollection->processAllRules(Toolbox::stripslashes_deep($ruleinput), [], []);
            if (isset($res_rule["name"])) {
                $input["name"] = $res_rule["name"];
            }
        }
        return ($add ? $this->import($input) : $this->findID($input));
    }


    /**
     * @see CommonDBTM::getSpecificMassiveActions()
     **/
    public function getSpecificMassiveActions($checkitem = null)
    {

        $isadmin = static::canUpdate();
        $actions = parent::getSpecificMassiveActions($checkitem);

        // Manage forbidden actions
        $forbidden_actions = $this->getForbiddenStandardMassiveAction();

        if (
            $isadmin
            &&  $this->maybeRecursive()
            && (count($_SESSION['glpiactiveentities']) > 1)
            && !in_array('merge', $forbidden_actions)
        ) {
            $actions[__CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'merge'] = __('Merge and assign to current entity');
        }

        return $actions;
    }


    /**
     * @since 0.85
     *
     * @see CommonDBTM::showMassiveActionsSubForm()
    **/
    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {

        switch ($ma->getAction()) {
            case 'merge':
                echo $_SESSION['glpiactive_entity_shortname'] . '<br>';
                echo Html::submit(_x('button', 'Merge'), ['name' => 'massiveaction', 'class' => 'btn btn-secondary']);
                return true;
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
            case 'merge':
                $fk = $item->getForeignKeyField();
                foreach ($ids as $key) {
                    if ($item->can($key, UPDATE)) {
                        if ($item->getEntityID() == $_SESSION['glpiactive_entity']) {
                            if (
                                $item->update(['id'           => $key,
                                                    'is_recursive' => 1])
                            ) {
                                $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                            } else {
                                $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                                $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                            }
                        } else {
                            $input2 = $item->fields;
                            // Remove keys (and name, tree dropdown will use completename)
                            if ($item instanceof CommonTreeDropdown) {
                                unset($input2['id'], $input2['name'], $input2[$fk]);
                            } else {
                                unset($input2['id']);
                            }
                            // Change entity
                            $input2['entities_id']  = $_SESSION['glpiactive_entity'];
                            $input2['is_recursive'] = 1;
                            $input2 = Toolbox::addslashes_deep($input2);
                            // Import new
                            if ($newid = $item->import($input2)) {
                                // Delete old
                                if ($newid > 0 && $key != $newid) {
                                    // delete with purge for dropdown with trashbin (Budget)
                                    $item->delete(['id'          => $key,
                                                        '_replace_by' => $newid], 1);
                                } elseif ($newid > 0 && $key == $newid) {
                                    $input2['id'] = $newid;
                                    $item->update($input2);
                                }
                                $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                            } else {
                                $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                                $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                            }
                        }
                    } else {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
                        $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
                    }
                }
                return;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    /**
     * Get links to Faq
     *
     * @param $withname  boolean  also display name ? (false by default)
    **/
    public function getLinks($withname = false)
    {
        global $CFG_GLPI;

        $ret = '';

        if ($withname) {
            $ret .= $this->fields["name"];
            $ret .= "&nbsp;&nbsp;";
        }

        if (
            $this->isField('knowbaseitemcategories_id')
            && $this->fields['knowbaseitemcategories_id']
        ) {
            $title = __s('FAQ');

            if (Session::getCurrentInterface() == 'central') {
                $title = __s('Knowledge base');
            }

            $rand = mt_rand();
            $kbitem = new KnowbaseItem();
            $found_kbitem = $kbitem->find([
               KnowbaseItem::getTable() . '.id'  => KnowbaseItem::getForCategory($this->fields['knowbaseitemcategories_id'])
            ]);

            if (count($found_kbitem)) {
                $kbitem->getFromDB(reset($found_kbitem)['id']);
                $ret .= "<div class='faqadd_block'>";
                $ret .= "<label for='display_faq_chkbox$rand'>";
                $ret .= "<img src='" . $CFG_GLPI["root_doc"] . "/pics/faqadd.png' class='middle pointer'
                      alt=\"$title\" title=\"$title\">";
                $ret .= "</label>";
                $ret .= "<input type='checkbox'  class='display_faq_chkbox' id='display_faq_chkbox$rand'>";
                $ret .= "<div class='faqadd_entries'>";
                if (count($found_kbitem) == 1) {
                    $ret .= "<div class='faqadd_block_content' id='faqadd_block_content$rand'>";
                    $ret .= $kbitem->showFull(['display' => false]);
                    $ret .= "</div>"; // .faqadd_block_content
                } else {
                    $ret .= Html::scriptBlock("
                  var getKnowbaseItemAnswer$rand = function() {
                     var knowbaseitems_id = $('#dropdown_knowbaseitems_id$rand').val();
                     $('#faqadd_block_content$rand').load(
                        '" . $CFG_GLPI['root_doc'] . "/ajax/getKnowbaseItemAnswer.php',
                        {
                           'knowbaseitems_id': knowbaseitems_id
                        }
                     );
                  };
               ");
                    $ret .= "<label for='dropdown_knowbaseitems_id$rand'>" .
                           KnowbaseItem::getTypeName() . "</label>&nbsp;";
                    $ret .= KnowbaseItem::dropdown([
                       'value'     => reset($found_kbitem)['id'],
                       'display'   => false,
                       'rand'      => $rand,
                       'condition' => [
                          KnowbaseItem::getTable() . '.id' => KnowbaseItem::getForCategory($this->fields['knowbaseitemcategories_id'])
                       ],
                       'on_change' => "getKnowbaseItemAnswer$rand()"
                    ]);
                    $ret .= "<div class='faqadd_block_content' id='faqadd_block_content$rand'>";
                    $ret .= $kbitem->showFull(['display' => false]);
                    $ret .= "</div>"; // .faqadd_block_content
                }
                $ret .= "</div>"; // .faqadd_entries
                $ret .= "</div>"; // .faqadd_block
            }
        }
        return $ret;
    }

    public function getForbiddenSingleMassiveActions()
    {
        $excluded = parent::getForbiddenSingleMassiveActions();
        $excluded[] = '*:merge';
        return $excluded;
    }


    public static function getIcon()
    {
        return "fas fa-edit";
    }
}
