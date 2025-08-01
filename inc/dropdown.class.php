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

class Dropdown
{
    //Empty value displayed in a dropdown
    public const EMPTY_VALUE = '-----';

    /**
     * Print out an HTML "<select>" for a dropdown with preselected value
     *
     * @param string $itemtype  itemtype used for create dropdown
     * @param array  $options   array of possible options:
     *    - name                 : string / name of the select (default is depending itemtype)
     *    - value                : integer / preselected value (default -1)
     *    - comments             : boolean / is the comments displayed near the dropdown (default true)
     *    - toadd                : array / array of specific values to add at the begining
     *    - entity               : integer or array / restrict to a defined entity or array of entities
     *                                                (default -1 : no restriction)
     *    - entity_sons          : boolean / if entity restrict specified auto select its sons
     *                                       only available if entity is a single value not an array
     *                                       (default false)
     *    - toupdate             : array / Update a specific item on select change on dropdown
     *                                     (need value_fieldname, to_update,
     *                                      url (see Ajax::updateItemOnSelectEvent for information)
     *                                      and may have moreparams)
     *    - used                 : array / Already used items ID: not to display in dropdown
     *                                    (default empty)
     *    - on_change            : string / value to transmit to "onChange"
     *    - rand                 : integer / already computed rand value
     *    - condition            : array / aditional SQL condition to limit display
     *    - displaywith          : array / array of field to display with request
     *    - emptylabel           : Empty choice's label (default self::EMPTY_VALUE)
     *    - display_emptychoice  : Display emptychoice ? (default true)
     *    - display              : boolean / display or get string (default true)
     *    - width                : specific width needed (default auto adaptive)
     *    - permit_select_parent : boolean / for tree dropdown permit to see parent items
     *                                       not available by default (default false)
     *    - specific_tags        : array of HTML5 tags to add the the field
     *    - url                  : url of the ajax php code which should return the json data to show in
     *                                       the dropdown
     *
     * @return boolean : false if error and random id if OK
     *
     * @since 9.5.0 Usage of string in condition option is removed
    **/
    public static function show($itemtype, $options = [])
    {
        global $CFG_GLPI;

        if ($itemtype && !($item = getItemForItemtype($itemtype))) {
            return false;
        }

        $table = $item->getTable();

        $params['name']                 = $item->getForeignKeyField();
        $params['value']                = (($itemtype == 'Entity') ? $_SESSION['glpiactive_entity'] : '');
        $params['comments']             = true;
        $params['entity']               = -1;
        $params['entity_sons']          = false;
        $params['toupdate']             = '';
        $params['width']                = '';
        $params['used']                 = [];
        $params['toadd']                = [];
        $params['on_change']            = '';
        $params['condition']            = [];
        $params['rand']                 = mt_rand();
        $params['displaywith']          = [];
        //Parameters about choice 0
        //Empty choice's label
        $params['emptylabel']           = self::EMPTY_VALUE;
        //Display emptychoice ?
        $params['display_emptychoice']  = ($itemtype != 'Entity');
        $params['placeholder']          = '';
        $params['display']              = true;
        $params['permit_select_parent'] = false;
        $params['addicon']              = true;
        $params['specific_tags']        = [];
        $params['url']                  = $CFG_GLPI['root_doc'] . "/ajax/getDropdownValue.php";

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $params[$key] = $val;
            }
        }
        $output       = '';
        $name         = $params['emptylabel'];
        $comment      = "";

        // Check default value for dropdown : need to be a numeric (or null)
        if (
            $params['value'] !== null
            && ((strlen($params['value']) == 0) || !is_numeric($params['value']) && $params['value'] != 'mygroups')
        ) {
            $params['value'] = 0;
        }

        if (isset($params['toadd'][$params['value']])) {
            $name = $params['toadd'][$params['value']];
        } elseif (
            ($params['value'] > 0)
                   || (($itemtype == "Entity")
                       && ($params['value'] >= 0))
        ) {
            $tmpname = self::getDropdownName($table, $params['value'], 1);

            if ($tmpname["name"] != "&nbsp;") {
                $name    = $tmpname["name"];
                $comment = $tmpname["comment"];
            }
        }

        // Manage entity_sons
        if (
            !($params['entity'] < 0)
            && $params['entity_sons']
        ) {
            if (is_array($params['entity'])) {
                // translation not needed - only for debug
                $output .= "entity_sons options is not available with entity option as array";
            } else {
                $params['entity'] = getSonsOf('glpi_entities', $params['entity']);
            }
        }

        $field_id = Html::cleanId("dropdown_" . $params['name'] . $params['rand']);

        // Manage condition
        if (!empty($params['condition'])) {
            // Put condition in session and replace it by its key
            // This is made to prevent passing to many parameters when calling the ajax script
            $params['condition'] = static::addNewCondition($params['condition']);
        }

        $name = Toolbox::unclean_cross_side_scripting_deep($name);

        $p = ['value'                => $params['value'],
              'valuename'            => $name,
              'width'                => $params['width'],
              'itemtype'             => $itemtype,
              'display_emptychoice'  => $params['display_emptychoice'],
              'placeholder'          => $params['placeholder'],
              'displaywith'          => $params['displaywith'],
              'emptylabel'           => $params['emptylabel'],
              'condition'            => $params['condition'],
              'used'                 => $params['used'],
              'toadd'                => $params['toadd'],
              'entity_restrict'      => ($entity_restrict = (is_array($params['entity']) ? json_encode(array_values($params['entity'])) : $params['entity'])),
              'on_change'            => $params['on_change'],
              'permit_select_parent' => $params['permit_select_parent'],
              'specific_tags'        => $params['specific_tags'],
              '_idor_token'          => Session::getNewIDORToken($itemtype, [
                 'entity_restrict' => $entity_restrict,
              ]),
        ];

        $output = "<span class='no-wrap input-group'>";
        $output .= Html::jsAjaxDropdown(
            $params['name'],
            $field_id,
            $params['url'],
            $p
        );
        // Display comment
        if ($params['comments']) {
            $comment_id      = Html::cleanId("comment_" . $params['name'] . $params['rand']);
            $link_id         = Html::cleanId("comment_link_" . $params['name'] . $params['rand']);
            $kblink_id       = Html::cleanId("kb_link_" . $params['name'] . $params['rand']);
            $options_tooltip = ['contentid' => $comment_id,
                                     'linkid'    => $link_id,
                                     'display'   => false];

            if ($item->canView()) {
                if (
                    $params['value']
                     && $item->getFromDB($params['value'])
                     && $item->canViewItem()
                ) {
                    $options_tooltip['link']       = $item->getLinkURL();
                } else {
                    $options_tooltip['link']       = $item->getSearchURL();
                }
            }

            if (empty($comment)) {
                $comment = Toolbox::ucfirst(
                    sprintf(
                        __('Show %1$s'),
                        $item::getTypeName(Session::getPluralNumber())
                    )
                );
            }
            $output .= "<span class='input-group-text'>";
            $output .= Html::showToolTip($comment, $options_tooltip);
            $output .= "</span>";

            if (
                ($item instanceof CommonDropdown)
                && $item->canCreate()
                && !isset($_REQUEST['_in_modal'])
                && $params['addicon']
            ) {
                $output .= "<span class='fa fa-plus-circle pointer input-group-text' title=\"" . __s('Add') . "\"
                            onClick=\"" . Html::jsGetElementbyID('add_' . $field_id) . ".dialog('open');\"
                           ><span class='sr-only'>" . __s('Add') . "</span></span>";
                $output .= Ajax::createIframeModalWindow(
                    'add_' . $field_id,
                    $item->getFormURL(),
                    ['display' => false]
                );
            }

            // Display specific Links
            if ($itemtype == "Supplier") {
                if ($item->getFromDB($params['value'])) {
                    $output .= $item->getLinks();
                }
            }

            if ($itemtype == 'Location') {
                $output .= "<span class='fa fa-globe-americas pointer input-group-text' title='" . __s('Display on map') . "' onclick='showMapForLocation(this)' data-fid='$field_id'></span>";
            }

            $paramscomment = [
               'value'       => '__VALUE__',
               'itemtype'    => $itemtype,
               '_idor_token' => Session::getNewIDORToken($itemtype)
            ];
            if (
                $item->isField('knowbaseitemcategories_id')
                && Session::haveRight('knowbase', READ)
            ) {
                if (method_exists($item, 'getLinks')) {
                    $output .= "<span id='$kblink_id' class='input-group-text'>";
                    $output .= '&nbsp;' . $item->getLinks();
                    $output .= "</span>";
                    $paramscomment['withlink'] = $kblink_id;
                    $output .= Ajax::updateItemOnSelectEvent(
                        $field_id,
                        $kblink_id,
                        $CFG_GLPI["root_doc"] . "/ajax/kblink.php",
                        $paramscomment,
                        false
                    );
                }
            }

            if ($item->canView()) {
                $paramscomment['withlink'] = $link_id;
            }

            $output .= Ajax::updateItemOnSelectEvent(
                $field_id,
                $comment_id,
                $CFG_GLPI["root_doc"] . "/ajax/comments.php",
                $paramscomment,
                false
            );
        }
        $output .= Ajax::commonDropdownUpdateItem($params, false);
        $output .= "</span>";
        if ($params['display']) {
            echo $output;
            return $params['rand'];
        }
        return $output;
    }


    /**
     * Add new condition
     *
     * @todo should not use session to pass query parameters...
     *
     * @param array $condition Condition to add
     *
     * @return string
     */
    public static function addNewCondition(array $condition)
    {
        $sha1 = sha1(serialize($condition));
        $_SESSION['glpicondition'][$sha1] = $condition;
        return $sha1;
    }

    /**
     * Get the value of a dropdown
     *
     * Returns the value of the dropdown from $table with ID $id.
     *
     * @param string  $table        the dropdown table from witch we want values on the select
     * @param integer $id           id of the element to get
     * @param boolean $withcomment  give array with name and comment (default 0)
     * @param boolean $translate    (true by default)
     * @param boolean $tooltip      (true by default) returns a tooltip, else returns only 'comment'
     *
     * @return string the value of the dropdown or &nbsp; if not exists
    **/
    public static function getDropdownName($table, $id, $withcomment = 0, $translate = true, $tooltip = true)
    {
        global $DB;

        $dft_retval = "&nbsp;";

        $item = getItemForItemtype(getItemTypeForTable($table));

        if (!is_object($item)) {
            return $dft_retval;
        }

        if ($item instanceof CommonTreeDropdown) {
            return getTreeValueCompleteName($table, $id, $withcomment, $translate, $tooltip);
        }

        $name    = "";
        $comment = "";

        if ($id) {
            $SELECTNAME    = new \QueryExpression("'' AS " . $DB->quoteName('transname'));
            $SELECTCOMMENT = new \QueryExpression("'' AS " . $DB->quoteName('transcomment'));
            $JOIN          = [];
            $JOINS         = [];
            if ($translate) {
                if (Session::haveTranslations(getItemTypeForTable($table), 'name')) {
                    $SELECTNAME = 'namet.value AS transname';
                    $JOINS['glpi_dropdowntranslations AS namet'] = [
                       'ON' => [
                          'namet'  => 'items_id',
                          $table   => 'id', [
                             'AND' => [
                                'namet.itemtype'  => getItemTypeForTable($table),
                                'namet.language'  => $_SESSION['glpilanguage'],
                                'namet.field'     => 'name'
                             ]
                          ]
                       ]
                    ];
                }
                if (Session::haveTranslations(getItemTypeForTable($table), 'comment')) {
                    $SELECTCOMMENT = 'namec.value AS transcomment';
                    $JOINS['glpi_dropdowntranslations AS namec'] = [
                       'ON' => [
                          'namec'  => 'items_id',
                          $table   => 'id', [
                             'AND' => [
                                'namec.itemtype'  => getItemTypeForTable($table),
                                'namec.language'  => $_SESSION['glpilanguage'],
                                'namec.field'     => 'comment'
                             ]
                          ]
                       ]
                    ];
                }

                if (count($JOINS)) {
                    $JOIN = ['LEFT JOIN' => $JOINS];
                }
            }

            $criteria = [
               'SELECT' => [
                  "$table.*",
                  $SELECTNAME,
                  $SELECTCOMMENT
               ],
               'FROM'   => $table,
               'WHERE'  => ["$table.id" => $id]
            ] + $JOIN;
            $iterator = $DB->request($criteria);

            /// TODO review comment management...
            /// TODO getDropdownName need to return only name
            /// When needed to use comment use class instead : getComments function
            /// GetName of class already give Name !!
            /// TODO CommonDBTM : review getComments to be recursive and add informations from class hierarchy
            /// getUserName have the same system : clean it too
            /// Need to study the problem
            if (count($iterator)) {
                $data = $iterator->next();
                if ($translate && !empty($data['transname'])) {
                    $name = $data['transname'];
                } else {
                    $name = $data[$item->getNameField()];
                }
                if (isset($data["comment"])) {
                    if ($translate && !empty($data['transcomment'])) {
                        $comment = $data['transcomment'];
                    } else {
                        $comment = $data["comment"];
                    }
                }

                switch ($table) {
                    case "glpi_computers":
                        if (empty($name)) {
                            $name = "($id)";
                        }
                        break;

                    case "glpi_contacts":
                        //TRANS: %1$s is the name, %2$s is the firstname
                        $name = sprintf(__('%1$s %2$s'), $name, $data["firstname"]);
                        if ($tooltip) {
                            if (!empty($data["phone"])) {
                                $comment .= "<br>" . sprintf(
                                    __('%1$s: %2$s'),
                                    "<span class='b'>" . Phone::getTypeName(1),
                                    "</span>" . $data['phone']
                                );
                            }
                            if (!empty($data["phone2"])) {
                                $comment .= "<br>" . sprintf(
                                    __('%1$s: %2$s'),
                                    "<span class='b'>" . __('Phone 2'),
                                    "</span>" . $data['phone2']
                                );
                            }
                            if (!empty($data["mobile"])) {
                                $comment .= "<br>" . sprintf(
                                    __('%1$s: %2$s'),
                                    "<span class='b'>" . __('Mobile phone'),
                                    "</span>" . $data['mobile']
                                );
                            }
                            if (!empty($data["fax"])) {
                                $comment .= "<br>" . sprintf(
                                    __('%1$s: %2$s'),
                                    "<span class='b'>" . __('Fax'),
                                    "</span>" . $data['fax']
                                );
                            }
                            if (!empty($data["email"])) {
                                $comment .= "<br>" . sprintf(
                                    __('%1$s: %2$s'),
                                    "<span class='b'>" . _n('Email', 'Emails', 1),
                                    "</span>" . $data['email']
                                );
                            }
                        }
                        break;

                    case "glpi_suppliers":
                        if ($tooltip) {
                            if (!empty($data["phonenumber"])) {
                                $comment .= "<br>" . sprintf(
                                    __('%1$s: %2$s'),
                                    "<span class='b'>" . Phone::getTypeName(1),
                                    "</span>" . $data['phonenumber']
                                );
                            }
                            if (!empty($data["fax"])) {
                                $comment .= "<br>" . sprintf(
                                    __('%1$s: %2$s'),
                                    "<span class='b'>" . __('Fax'),
                                    "</span>" . $data['fax']
                                );
                            }
                            if (!empty($data["email"])) {
                                $comment .= "<br>" . sprintf(
                                    __('%1$s: %2$s'),
                                    "<span class='b'>" . _n('Email', 'Emails', 1),
                                    "</span>" . $data['email']
                                );
                            }
                        }
                        break;

                    case "glpi_netpoints":
                        $name = sprintf(
                            __('%1$s (%2$s)'),
                            $name,
                            self::getDropdownName(
                                "glpi_locations",
                                $data["locations_id"],
                                false,
                                $translate
                            )
                        );
                        break;

                    case "glpi_budgets":
                        if ($tooltip) {
                            if (!empty($data['locations_id'])) {
                                $comment .= "<br>" . sprintf(
                                    __('%1$s: %2$s'),
                                    "<span class='b'>" . Location::getTypeName(1) . "</span>",
                                    self::getDropdownName(
                                        "glpi_locations",
                                        $data["locations_id"],
                                        false,
                                        $translate
                                    )
                                );
                            }
                            if (!empty($data['budgettypes_id'])) {
                                $comment .= "<br>" . sprintf(
                                    __('%1$s: %2$s'),
                                    "<span class='b'>" . _n('Type', 'Types', 1) . "</span>",
                                    self::getDropdownName(
                                        "glpi_budgettypes",
                                        $data["budgettypes_id"],
                                        false,
                                        $translate
                                    )
                                );
                            }
                            if (!empty($data['begin_date'])) {
                                $comment .= "<br>" . sprintf(
                                    __('%1$s: %2$s'),
                                    "<span class='b'>" . __('Start date') . "</span>",
                                    Html::convDateTime($data["begin_date"])
                                );
                            }
                            if (!empty($data['end_date'])) {
                                $comment .= "<br>" . sprintf(
                                    __('%1$s: %2$s'),
                                    "<span class='b'>" . __('End date') . "</span>",
                                    Html::convDateTime($data["end_date"])
                                );
                            }
                        }
                }
            }
        }

        if (empty($name)) {
            $name = $dft_retval;
        }

        if ($withcomment) {
            return [
               'name'      => $name,
               'comment'   => $comment
            ];
        }

        return $name;
    }


    /**
     * Get values of a dropdown for a list of item
     *
     * @param string    $table  the dropdown table from witch we want values on the select
     * @param integer[] $ids    array containing the ids to get
     *
     * @return array containing the value of the dropdown or &nbsp; if not exists
    **/
    public static function getDropdownArrayNames($table, $ids)
    {
        global $DB;

        $tabs = [];

        if (count($ids)) {
            $itemtype = getItemTypeForTable($table);
            if ($item = getItemForItemtype($itemtype)) {
                $field    = 'name';
                if ($item instanceof CommonTreeDropdown) {
                    $field = 'completename';
                }

                $iterator = $DB->request([
                   'SELECT' => ['id', $field],
                   'FROM'   => $table,
                   'WHERE'  => ['id' => $ids]
                ]);

                while ($data = $iterator->next()) {
                    $tabs[$data['id']] = $data[$field];
                }
            }
        }
        return $tabs;
    }


    /**
     * Make a select box for device type
     *
     * @param string   $name     name of the select box
     * @param string[] $types    array of types to display
     * @param array    $options  Parameters which could be used in options array :
     *    - value               : integer / preselected value (default '')
     *    - used                : array / Already used items ID: not to display in dropdown (default empty)
     *    - emptylabel          : Empty choice's label (default self::EMPTY_VALUE)
     *    - display             : boolean if false get string
     *    - width               : specific width needed (default not set)
     *    - emptylabel          : empty label if empty displayed (default self::EMPTY_VALUE)
     *    - display_emptychoice : display empty choice (default false)
     *
     * @return integer|string
     *    integer if option display=true (random part of elements id)
     *    string if option display=false (HTML code)
    **/
    public static function showItemTypes($name, $types = [], $options = [])
    {
        $params['value']               = '';
        $params['used']                = [];
        $params['emptylabel']          = self::EMPTY_VALUE;
        $params['display']             = true;
        $params['width']               = '80%';
        $params['display_emptychoice'] = true;
        $params['rand']         = mt_rand();

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $params[$key] = $val;
            }
        }

        $values = [];
        if (count($types)) {
            foreach ($types as $type) {
                if ($item = getItemForItemtype($type)) {
                    $values[$type] = $item->getTypeName(1);
                }
            }
        }
        asort($values);
        return self::showFromArray(
            $name,
            $values,
            $params
        );
    }


    /**
     * Make a select box for device type
     *
     * @param string $name          name of the select box
     * @param string $itemtype_ref  itemtype reference where to search in itemtype field
     * @param array  $options       array of possible options:
     *        - may be value (default value) / field (used field to search itemtype)
     *
     * @return integer|string
     *    integer if option display=true (random part of elements id)
     *    string if option display=false (HTML code)
    **/
    public static function dropdownUsedItemTypes($name, $itemtype_ref, $options = [])
    {
        global $DB;

        $p['value'] = 0;
        $p['field'] = 'itemtype';

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $p[$key] = $val;
            }
        }

        $iterator = $DB->request([
           'SELECT'          => $p['field'],
           'DISTINCT'        => true,
           'FROM'            => getTableForItemType($itemtype_ref)
        ]);

        $tabs = [];
        while ($data = $iterator->next()) {
            $tabs[$data[$p['field']]] = $data[$p['field']];
        }
        return self::showItemTypes($name, $tabs, ['value' => $p['value']]);
    }


    /**
     * Make a select box for icons
     *
     * @param string  $myname      the name of the HTML select
     * @param mixed   $value       the preselected value we want
     * @param string  $store_path  path where icons are stored
     * @param boolean $display     display of get string ? (true by default)
     *
     *
     * @return void|string
     *    void if param display=true
     *    string if param display=false (HTML code)
    **/
    public static function dropdownIcons($myname, $value, $store_path, $display = true)
    {

        $output = '';
        if (is_dir($store_path)) {
            if ($dh = opendir($store_path)) {
                $files = [];

                while (($file = readdir($dh)) !== false) {
                    $files[] = $file;
                }

                closedir($dh);
                sort($files);

                foreach ($files as $file) {
                    if (preg_match("/\.png$/i", $file)) {
                        $values[$file] = $file;
                    }
                }
                Dropdown::showFromArray(
                    $myname,
                    $values,
                    ['value'               => $value,
                                              'display_emptychoice' => true]
                );
            } else {
                //TRANS: %s is the store path
                printf(__('Error reading directory %s'), $store_path);
            }
        } else {
            //TRANS: %s is the store path
            printf(__('Error: %s is not a directory'), $store_path);
        }
        if ($display) {
            echo $output;
        } else {
            return $output;
        }
    }


    /**
     * Dropdown for GMT selection
     *
     * @param string $name   select name
     * @param mixed  $value  default value (default '')
    **/
    public static function showGMT($name, $value = '')
    {

        $elements = [-12, -11, -10, -9, -8, -7, -6, -5, -4, -3.5, -3, -2, -1, 0,
                          '+1', '+2', '+3', '+3.5', '+4', '+4.5', '+5', '+5.5', '+6', '+6.5', '+7',
                          '+8', '+9', '+9.5', '+10', '+11', '+12', '+13'];

        $values = [];
        foreach ($elements as $element) {
            if ($element != 0) {
                $values[$element * HOUR_TIMESTAMP] = sprintf(
                    __('%1$s %2$s'),
                    __('GMT'),
                    sprintf(
                        _n('%s hour', '%s hours', $element),
                        $element
                    )
                );
            } else {
                $display_value                   = __('GMT');
                $values[$element * HOUR_TIMESTAMP] = __('GMT');
            }
        }
        Dropdown::showFromArray($name, $values, ['value' => $value]);
    }


    /**
     * Make a select box for a boolean choice (Yes/No) or display a checkbox. Add a
     * 'use_checkbox' = true to the $params array to display a checkbox instead a select box
     *
     * @param string  $name         select name
     * @param mixed   $value        preselected value. (default 0)
     * @param integer $restrict_to  allows to display only yes or no in the dropdown (default -1)
     * @param array   $params       Array of optional options (passed to showFromArray)
     *
     * @return integer|string
     *    integer if option display=true (random part of elements id)
     *    string if option display=false (HTML code)
    **/
    public static function showYesNo($name, $value = 0, $restrict_to = -1, $params = [])
    {

        if (!array_key_exists('use_checkbox', $params)) {
            // TODO: switch to true when Html::showCheckbox() is validated
            $params['use_checkbox'] = false;
        }
        if ($params['use_checkbox']) {
            if (!empty($params['rand'])) {
                $rand = $params['rand'];
            } else {
                $rand = mt_rand();
            }

            $options = ['name' => $name,
                             'id'   => Html::cleanId("dropdown_" . $name . $rand)];

            switch ($restrict_to) {
                case 0:
                    $options['checked']  = false;
                    $options['readonly'] = true;
                    break;

                case 1:
                    $options['checked']  = true;
                    $options['readonly'] = true;
                    break;

                default:
                    $options['checked']  = ($value ? 1 : 0);
                    $options['readonly'] = false;
                    break;
            }

            $output = Html::getCheckbox($options);
            if (!isset($params['display']) || $params['display'] == 'true') {
                echo $output;
                return $rand;
            } else {
                return $output;
            }
        }

        if ($restrict_to != 0) {
            $options[0] = __('No');
        }

        if ($restrict_to != 1) {
            $options[1] = __('Yes');
        }

        $params['value'] = $value;
        $params['width'] = "5rem";
        return self::showFromArray($name, $options, $params);
    }


    /**
     * Get Yes No string
     *
     * @param mixed $value Yes No value
     *
     * @return string
    **/
    public static function getYesNo($value)
    {

        if ($value) {
            return __('Yes');
        }
        return __('No');
    }


    /**
     * Get the Device list name the user is allowed to edit
     *
     * @return array (group of dropdown) of array (itemtype => localized name)
    **/
    public static function getDeviceItemTypes()
    {
        static $optgroup = null;

        if (!Session::haveRight('device', READ)) {
            return [];
        }

        if (is_null($optgroup)) {
            $devices = [];
            foreach (CommonDevice::getDeviceTypes() as $device_type) {
                $devices[$device_type] = $device_type::getTypeName(Session::getPluralNumber());
            }
            asort($devices);
            $optgroup = [_n('Component', 'Components', Session::getPluralNumber()) => $devices];
        }
        return $optgroup;
    }


    /**
     * Get the dropdown list name the user is allowed to edit
     *
     * @return array (group of dropdown) of array (itemtype => localized name)
    **/
    public static function getStandardDropdownItemTypes()
    {
        static $optgroup = null;

        if (is_null($optgroup)) {
            $optgroup = [
                __('Common') => [
                    'Location' => null,
                    'State' => null,
                    'Manufacturer' => null,
                    'Blacklist' => null,
                    'BlacklistedMailContent' => null
                ],

                __('Assistance') => [
                    'ITILCategory' => null,
                    'TaskCategory' => null,
                    'TaskTemplate' => null,
                    'SolutionType' => null,
                    'SolutionTemplate' => null,
                    'RequestType' => null,
                    'ITILFollowupTemplate' => null,
                    'ProjectState' => null,
                    'ProjectType' => null,
                    'ProjectTaskType' => null,
                    'ProjectTaskTemplate' => null,
                    'PlanningExternalEventTemplate' => null,
                    'PlanningEventCategory' => null,
                ],

                _n('Type', 'Types', Session::getPluralNumber()) => [
                    'ComputerType' => null,
                    'NetworkEquipmentType' => null,
                    'PrinterType' => null,
                    'MonitorType' => null,
                    'PeripheralType' => null,
                    'PhoneType' => null,
                    'SoftwareLicenseType' => null,
                    'CartridgeItemType' => null,
                    'ConsumableItemType' => null,
                    'ContractType' => null,
                    'ContactType' => null,
                    'DeviceGenericType' => null,
                    'DeviceSensorType' => null,
                    'DeviceMemoryType' => null,
                    'SupplierType' => null,
                    'InterfaceType' => null,
                    'DeviceCaseType' => null,
                    'PhonePowerSupply' => null,
                    'Filesystem' => null,
                    'CertificateType' => null,
                    'BudgetType' => null,
                    'DeviceSimcardType' => null,
                    'LineType' => null,
                    'RackType' => null,
                    'PDUType' => null,
                    'PassiveDCEquipmentType' => null,
                    'ClusterType' => null,
                ],

                _n('Model', 'Models', 1) => [
                    'ComputerModel' => null,
                    'NetworkEquipmentModel' => null,
                    'PrinterModel' => null,
                    'MonitorModel' => null,
                    'PeripheralModel' => null,
                    'PhoneModel' => null,

                     // Devices models :
                     'DeviceCaseModel' => null,
                     'DeviceControlModel' => null,
                     'DeviceDriveModel' => null,
                     'DeviceGenericModel' => null,
                     'DeviceGraphicCardModel' => null,
                     'DeviceHardDriveModel' => null,
                     'DeviceMemoryModel' => null,
                     'DeviceMotherBoardModel' => null,
                     'DeviceNetworkCardModel' => null,
                     'DevicePciModel' => null,
                     'DevicePowerSupplyModel' => null,
                     'DeviceProcessorModel' => null,
                     'DeviceSoundCardModel' => null,
                     'DeviceSensorModel' => null,
                     'RackModel' => null,
                     'EnclosureModel' => null,
                     'PDUModel' => null,
                     'PassiveDCEquipmentModel' => null,
                ],

                _n('Virtual machine', 'Virtual machines', Session::getPluralNumber()) => [
                    'VirtualMachineType' => null,
                    'VirtualMachineSystem' => null,
                    'VirtualMachineState' => null
                ],

                __('Management') => [
                    'DocumentCategory' => null,
                    'DocumentType' => null,
                    'BusinessCriticity' => null
                ],

                __('Tools') => [
                    'KnowbaseItemCategory' => null,
                    'SpecialStatus' => null
                ],

                _n('Calendar', 'Calendars', 1) => [
                    'Calendar' => null,
                    'Holiday' => null
                ],

                OperatingSystem::getTypeName(Session::getPluralNumber()) => [
                    'OperatingSystem' => null,
                    'OperatingSystemVersion' => null,
                    'OperatingSystemServicePack' => null,
                    'OperatingSystemArchitecture' => null,
                    'OperatingSystemEdition' => null,
                    'OperatingSystemKernel' => null,
                    'OperatingSystemKernelVersion' => null,
                    'AutoUpdateSystem' => null
                ],

                __('Networking') => [
                    'NetworkInterface' => null,
                    'Netpoint' => null,
                    'Network' => null,
                    'Vlan' => null,
                    'LineOperator' => null,
                    'DomainType' => null,
                    'DomainRelation' => null,
                    'DomainRecordType' => null
                ],

                __('Internet') => [
                    'IPNetwork' => null,
                    'FQDN' => null,
                    'WifiNetwork' => null,
                    'NetworkName' => null
                ],

                _n('Software', 'Software', 1) => [
                   'SoftwareCategory' => null
                ],

                User::getTypeName(1) => [
                    'UserTitle' => null,
                    'UserCategory' => null
                ],

                __('Authorizations assignment rules') => [
                   'RuleRightParameter' => null
                ],

                __('Fields unicity') => [
                   'Fieldblacklist' => null
                ],

                __('External authentications') => [
                   'SsoVariable' => null
                ],
                __('Power management') => [
                  'Plug' => null
                ],
                __('Appliances') => [
                  'ApplianceType' => null,
                  'ApplianceEnvironment' => null
                ]

            ]; //end $opt

            $plugdrop = Plugin::getDropdowns();

            if (count($plugdrop)) {
                $optgroup = array_merge($optgroup, $plugdrop);
            }

            foreach ($optgroup as $label => &$dp) {
                foreach ($dp as $key => &$val) {
                    if ($tmp = getItemForItemtype($key)) {
                        if (!$tmp->canView()) {
                            unset($optgroup[$label][$key]);
                        } elseif ($val === null) {
                            $val = $key::getTypeName(Session::getPluralNumber());
                        }
                    } else {
                        unset($optgroup[$label][$key]);
                    }
                }

                if (count($optgroup[$label]) == 0) {
                    unset($optgroup[$label]);
                }
            }
        }
        return $optgroup;
    }


    /**
     * Display a menu to select a itemtype which open the search form
     *
     * @param $title     string   title to display
     * @param $optgroup  array    (group of dropdown) of array (itemtype => localized name)
     * @param $value     string   URL of selected current value (default '')
    **/
    public static function showItemTypeMenu($title, $optgroup, $value = '')
    {

        echo "<table class='tab_cadre' width='50%' aria-label='Item Type Menu'>";
        echo "<tr class='tab_bg_1'><td class='b'>&nbsp;" . $title . "&nbsp; ";
        $selected = '';

        foreach ($optgroup as $label => $dp) {
            foreach ($dp as $key => $val) {
                $search = $key::getSearchURL();

                if (basename($search) == basename($value)) {
                    $selected = $search;
                }
                $values[$label][$search] = $val;
            }
        }
        Dropdown::showFromArray(
            'dpmenu',
            $values,
            ['on_change'
                                         => "var _value = this.options[this.selectedIndex].value; if (_value != 0) {window.location.href=_value;}",
                                      'value'               => $selected,
                                      'display_emptychoice' => true]
        );

        echo "</td></tr>";
        echo "</table><br>";
    }


    /**
     * Display a list to select a itemtype with link to search form
     *
     * @param $optgroup array (group of dropdown) of array (itemtype => localized name)
     */
    public static function showItemTypeList($optgroup)
    {

        echo "<div id='list_nav'>";
        $nb = 0;
        foreach ($optgroup as $label => $dp) {
            $nb += count($dp);
        }
        $step = ($nb > 15 ? ($nb / 3) : $nb);
        echo "<table class='tab_glpi'><tr class='top'><td width='33%' class='center' aria-label=' Item type List'>";
        echo "<table class='tab_cadre' aria-label=' Item type SubList'>";
        $i = 1;

        foreach ($optgroup as $label => $dp) {
            echo "<tr><th>$label</th></tr>\n";

            foreach ($dp as $key => $val) {
                $class = "class='tab_bg_4'";
                if (
                    ($itemtype = getItemForItemtype($key))
                    && $itemtype->isEntityAssign()
                ) {
                    $class = "class='tab_bg_2'";
                }
                echo "<tr $class><td><a href='" . $key::getSearchURL() . "'>";
                echo "$val</a></td></tr>\n";
                $i++;
            }

            if (($i >= $step) && ($i < $nb)) {
                echo "</table></td><td width='25'>&nbsp;</td><td><table class='tab_cadre' aria-label='Item Type Sublist Detail'>";
                $step += $step;
            }
        }
        echo "</table></td></tr></table></div>";
    }


    /**
     * Dropdown available languages
     * @deprecated since 2.0.0
     * use Language::getLanguages() instead
     * @param string $myname   select name
     * @param array  $options  array of additionnal options:
     *    - display_emptychoice : allow selection of no language
     *    - emptylabel          : specific string to empty label if display_emptychoice is true
    **/
    public static function showLanguages($myname, $options = [])
    {
        $values = [];
        if (isset($options['display_emptychoice']) && ($options['display_emptychoice'])) {
            if (isset($options['emptylabel'])) {
                $values[''] = $options['emptylabel'];
            } else {
                $values[''] = self::EMPTY_VALUE;
            }
            unset($options['display_emptychoice']);
        }

        $values = array_merge($values, Language::getLanguages());
        return self::showFromArray($myname, $values, $options);
    }

    /**
     * Get available languages
     *
     * @since 9.5.0
     * @deprecated since 2.0.0
     * use Language::getLanguages() instead
     * @return array
     *
     */
    public static function getLanguages()
    {
        global $CFG_GLPI;

        $languages = [];
        foreach ($CFG_GLPI["languages"] as $key => $val) {
            if (isset($val[1]) && is_file(GLPI_ROOT . "/locales/" . $val[1])) {
                $languages[$key] = $val[0];
            }
        }

        return $languages;
    }


    /**
     * @since 0.84
     *
     * @param $value
    **/
    public static function getLanguageName($value)
    {
        global $CFG_GLPI;

        if (isset($CFG_GLPI["languages"][$value][0])) {
            return $CFG_GLPI["languages"][$value][0];
        }
        return $value;
    }


    /**
     * Print a select with hours
     *
     * Print a select named $name with hours options and selected value $value
     *
     *@param $name             string   HTML select name
     *@param $options array of options :
     *     - value              default value (default '')
     *     - limit_planning     limit planning to the configuration range (default false)
     *     - display   boolean  if false get string
     *     - width              specific width needed (default auto adaptive)
     *     - step               step time (defaut config GLPI)
     *
     * @since 0.85 update prototype
     *
     * @return integer|string
     *    integer if option display=true (random part of elements id)
     *    string if option display=false (HTML code)
     **/
    public static function showHours($name, $options = [])
    {
        global $CFG_GLPI;

        $p['value']          = '';
        $p['limit_planning'] = false;
        $p['display']        = true;
        $p['width']          = '';
        $p['step']           = $CFG_GLPI["time_step"];

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $p[$key] = $val;
            }
        }

        $begin = 0;
        $end   = 24;
        // Check if the $step is Ok for the $value field
        $split = explode(":", $p['value']);

        // Valid value XX:YY ou XX:YY:ZZ
        if ((count($split) == 2) || (count($split) == 3)) {
            $min = $split[1];

            // Problem
            if (($min % $p['step']) != 0) {
                // set minimum step
                $p['step'] = 5;
            }
        }

        if ($p['limit_planning']) {
            $plan_begin = explode(":", $CFG_GLPI["planning_begin"]);
            $plan_end   = explode(":", $CFG_GLPI["planning_end"]);
            $begin      = (int) $plan_begin[0];
            $end        = (int) $plan_end[0];
        }

        $values   = [];
        $selected = '';

        for ($i = $begin; $i < $end; $i++) {
            if ($i < 10) {
                $tmp = "0" . $i;
            } else {
                $tmp = $i;
            }

            for ($j = 0; $j < 60; $j += $p['step']) {
                if ($j < 10) {
                    $val = $tmp . ":0$j";
                } else {
                    $val = $tmp . ":$j";
                }
                $values[$val] = $val;
                if (($p['value'] == $val . ":00") || ($p['value'] == $val)) {
                    $selected = $val;
                }
            }
        }
        // Last item
        $val = $end . ":00";
        $values[$val] = $val;
        if (($p['value'] == $val . ":00") || ($p['value'] == $val)) {
            $selected = $val;
        }
        $p['value'] = $selected;
        return Dropdown::showFromArray($name, $values, $p);
    }


    /**
     * show a dropdown to selec a type
     *
     * @since 0.83
     *
     * @param array|string $types    Types used (default "state_types") (default '')
     * @param array        $options  Array of optional options
     *        name, value, rand, emptylabel, display_emptychoice, on_change, plural, checkright
     *       - toupdate            : array / Update a specific item on select change on dropdown
     *                                    (need value_fieldname, to_update,
     *                                     url (see Ajax::updateItemOnSelectEvent for information)
     *                                     and may have moreparams)
     *
     * @return integer rand for select id
    **/
    public static function showItemType($types = '', $options = [])
    {
        global $CFG_GLPI;

        $params['name']                = 'itemtype';
        $params['value']               = '';
        $params['rand']                = mt_rand();
        $params['on_change']           = '';
        $params['plural']              = false;
        //Parameters about choice 0
        //Empty choice's label
        $params['emptylabel']          = self::EMPTY_VALUE;
        //Display emptychoice ?
        $params['display_emptychoice'] = true;
        $params['checkright']          = false;
        $params['toupdate']            = '';
        $params['display']             = true;

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $params[$key] = $val;
            }
        }

        if (!is_array($types)) {
            $types = $CFG_GLPI["state_types"];
        }
        $options = [];

        foreach ($types as $type) {
            if ($item = getItemForItemtype($type)) {
                if ($params['checkright'] && !$item->canView()) {
                    continue;
                }
                $options[$type] = $item->getTypeName($params['plural'] ? 2 : 1);
            }
        }
        asort($options);

        if (count($options)) {
            return Dropdown::showFromArray($params['name'], $options, [
               'value'               => $params['value'],
               'on_change'           => $params['on_change'],
               'toupdate'            => $params['toupdate'],
               'display_emptychoice' => $params['display_emptychoice'],
               'emptylabel'          => $params['emptylabel'],
               'display'             => $params['display'],
               'rand'                => $params['rand'],
            ]);
        }
        return 0;
    }


    /**
     * Make a select box for all items
     *
     * @since 0.85
     *
     * @param $options array:
     *   - itemtype_name        : the name of the field containing the itemtype (default 'itemtype')
     *   - items_id_name        : the name of the field containing the id of the selected item
     *                            (default 'items_id')
     *   - itemtypes            : all possible types to search for (default: $CFG_GLPI["state_types"])
     *   - default_itemtype     : the default itemtype to select (don't define if you don't
     *                            need a default) (defaut 0)
     *    - entity_restrict     : restrict entity in searching items (default -1)
     *    - onlyglobal          : don't match item that don't have `is_global` == 1 (false by default)
     *    - checkright          : check to see if we can "view" the itemtype (false by default)
     *    - showItemSpecificity : given an item, the AJAX file to open if there is special
     *                            treatment. For instance, select a Item_Device* for CommonDevice
     *    - emptylabel          : Empty choice's label (default self::EMPTY_VALUE)
     *    - used                : array / Already used items ID: not to display in dropdown (default empty)
     *    - display             : true : display directly, false return the html
     *
     * @return integer randomized value used to generate HTML IDs
    **/
    public static function showSelectItemFromItemtypes(array $options = [])
    {
        global $CFG_GLPI;

        $params = [];
        $params['itemtype_name']       = 'itemtype';
        $params['items_id_name']       = 'items_id';
        $params['itemtypes']           = '';
        $params['default_itemtype']    = 0;
        $params['entity_restrict']     = -1;
        $params['onlyglobal']          = false;
        $params['checkright']          = false;
        $params['showItemSpecificity'] = '';
        $params['emptylabel']          = self::EMPTY_VALUE;
        $params['used']                = [];
        $params['display']             = true;
        $params['rand']                = mt_rand();

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $params[$key] = $val;
            }
        }


        $dropdownValues = [Dropdown::EMPTY_VALUE];
        foreach ($CFG_GLPI["ticket_types"] as $itemtype) {
            $dropdownValues[$itemtype] = $itemtype::getTypeName(1);
        }
        asort($dropdownValues);
        $entity = Session::getActiveEntity();
        $inputs = [
           __('Itemtype') => [
              'type' => 'select',
              'id' => 'selectItemTypeForTicketMassiveAction',
              'name' => $params['itemtype_name'],
              'values' => $dropdownValues,
              'col_lg' => 6,
              'hooks' => [
                 'change' => <<<JS
                  const val = this.value;
                  $('#selectItemForTicketMassiveAction').empty();
                  if (val != 0) {
                     $.ajax({
                        url: '{$CFG_GLPI['root_doc']}/ajax/dropdownAllItems.php',
                        data: {
                           itemtype_name: 'devicetype',
                           items_id_name: 'devices_id',
                           idtable: val,
                           entity_restrict: $entity,
                        },
                        type: 'POST',
                        success: function(data) {
                           const jsonDatas = JSON.parse(data);
                           for (const key in jsonDatas) {
                             if (typeof jsonDatas[key] === 'object') {
                                for (const key2 in jsonDatas[key]) {
                                   $('#selectItemForTicketMassiveAction').append('<option value="' + key2 + '">' + jsonDatas[key][key2] + '</option>');
                                }
                             } else {
                                $('#selectItemForTicketMassiveAction').append('<option value="' + key + '">' + jsonDatas[key] + '</option>');
                             }
                           }
                        }
                     });
                  }
                  if (val == 0) {
                     $('#selectItemForTicketMassiveAction').prop('disabled', true);
                     $('#selectItemForTicketMassiveAction').empty();
                  } else {
                     $('#selectItemForTicketMassiveAction').prop('disabled', false);
                  }
               JS,
              ]
           ],
           __('Component') => [
              'type' => 'select',
              'name' => $params['items_id_name'],
              'id' => 'selectItemForTicketMassiveAction',
              'values' => [],
              'col_lg' => 6,
              'disabled' => '',
           ],
        ];
        ob_start();
        echo "<div class='center row'>";
        foreach ($inputs as $title => $input) {
            renderTwigTemplate('macros/wrappedInput.twig', [
               'title' => $title,
               'input' => $input,
            ]);
        }
        echo "</div>";
        $out = ob_get_clean();

        if ($params['display']) {
            echo $out;
        }
        return $out;
    }


    /**
     * Dropdown numbers
     *
     * @since 0.84
     *
     * @param string $myname   select name
     * @param array  $options  array of additionnal options :
     *     - value              default value (default 0)
     *     - rand               random value
     *     - min                min value (default 0)
     *     - max                max value (default 100)
     *     - step               step used (default 1)
     *     - toadd     array    of values to add at the beginning
     *     - unit      string   unit to used
     *     - display   boolean  if false get string
     *     - width              specific width needed (default 80%)
     *     - on_change string / value to transmit to "onChange"
     *     - used      array / Already used items ID: not to display in dropdown (default empty)
    **/
    public static function showNumber($myname, $options = [])
    {
        global $CFG_GLPI;

        $p = [
           'value'           => 0,
           'rand'            => mt_rand(),
           'min'             => 0,
           'max'             => 100,
           'step'            => 1,
           'toadd'           => [],
           'unit'            => '',
           'display'         => true,
           'width'           => '',
           'on_change'       => '',
           'used'            => [],
           'specific_tags'   => [],
        ];

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $p[$key] = $val;
            }
        }
        if (($p['value'] < $p['min']) && !isset($p['toadd'][$p['value']])) {
            $min = $p['min'];

            while (isset($p['used'][$min])) {
                ++$min;
            }
            $p['value'] = $min;
        }

        $field_id = Html::cleanId("dropdown_" . $myname . $p['rand']);
        if (!isset($p['toadd'][$p['value']])) {
            $decimals = Toolbox::isFloat($p['value']) ? Toolbox::getDecimalNumbers($p['step']) : 0;
            $valuename = self::getValueWithUnit($p['value'], $p['unit'], $decimals);
        } else {
            $valuename = $p['toadd'][$p['value']];
        }
        $param = ['value'               => $p['value'],
                       'valuename'           => $valuename,
                       'width'               => $p['width'],
                       'on_change'           => $p['on_change'],
                       'used'                => $p['used'],
                       'unit'                => $p['unit'],
                       'min'                 => $p['min'],
                       'max'                 => $p['max'],
                       'step'                => $p['step'],
                       'toadd'               => $p['toadd'],
                       'specific_tags'       => $p['specific_tags']];

        $out   = Html::jsAjaxDropdown(
            $myname,
            $field_id,
            $CFG_GLPI['root_doc'] . "/ajax/getDropdownNumber.php",
            $param
        );

        if ($p['display']) {
            echo $out;
            return $p['rand'];
        }
        return $out;
    }


    /**
     * Get value with unit / Automatic management of standar unit (year, month, %, ...)
     *
     * @since 0.84
     *
     * @param integer $value    numeric value
     * @param string  $unit     unit (maybe year, month, day, hour, % for standard management)
     * @param integer $decimals number of decimal
    **/
    public static function getValueWithUnit($value, $unit, $decimals = 0)
    {

        $formatted_number = is_numeric($value)
           ? Html::formatNumber($value, false, $decimals)
           : $value;

        if (strlen($unit) == 0) {
            return $formatted_number;
        }

        switch ($unit) {
            case 'year':
                //TRANS: %s is a number of years
                return sprintf(_n('%s year', '%s years', $value), $formatted_number);

            case 'month':
                //TRANS: %s is a number of months
                return sprintf(_n('%s month', '%s months', $value), $formatted_number);

            case 'day':
                //TRANS: %s is a number of days
                return sprintf(_n('%s day', '%s days', $value), $formatted_number);

            case 'hour':
                //TRANS: %s is a number of hours
                return sprintf(_n('%s hour', '%s hours', $value), $formatted_number);

            case 'minute':
                //TRANS: %s is a number of minutes
                return sprintf(_n('%s minute', '%s minutes', $value), $formatted_number);

            case 'second':
                //TRANS: %s is a number of seconds
                return sprintf(_n('%s second', '%s seconds', $value), $formatted_number);

            case 'millisecond':
                //TRANS: %s is a number of milliseconds
                return sprintf(_n('%s millisecond', '%s milliseconds', $value), $formatted_number);

            case 'auto':
                return Toolbox::getSize($value * 1024 * 1024);

            case '%':
                return sprintf(__('%s%%'), $formatted_number);

            default:
                return sprintf(__('%1$s %2$s'), $formatted_number, $unit);
        }
    }


    /**
     * Dropdown integers
     *
     * @since 0.83
     *
     * @param string $myname   select name
     * @param array  $options  array of options
     *    - value           : default value
     *    - min             : min value : default 0
     *    - max             : max value : default DAY_TIMESTAMP
     *    - value           : default value
     *    - addfirstminutes : add first minutes before first step (default false)
     *    - toadd           : array of values to add
     *    - inhours         : only show timestamp in hours not in days
     *    - display         : boolean / display or return string
     *    - width           : string / display width of the item
    **/
    public static function showTimeStamp($myname, $options = [])
    {
        global $CFG_GLPI;

        $params['value']               = 0;
        $params['rand']                = mt_rand();
        $params['min']                 = 0;
        $params['max']                 = DAY_TIMESTAMP;
        $params['step']                = $CFG_GLPI["time_step"] * MINUTE_TIMESTAMP;
        $params['emptylabel']          = self::EMPTY_VALUE;
        $params['addfirstminutes']     = false;
        $params['toadd']               = [];
        $params['inhours']             = false;
        $params['display']             = true;
        $params['display_emptychoice'] = true;
        $params['width']               = '80%';

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $params[$key] = $val;
            }
        }

        // Manage min :
        $params['min'] = floor($params['min'] / $params['step']) * $params['step'];

        if ($params['min'] == 0) {
            $params['min'] = $params['step'];
        }

        $params['max'] = max($params['value'], $params['max']);

        // Floor with MINUTE_TIMESTAMP for rounded purpose
        if (empty($params['value'])) {
            $params['value'] = 0;
        }
        if (
            ($params['value'] < max($params['min'], 10 * MINUTE_TIMESTAMP))
            && $params['addfirstminutes']
        ) {
            $params['value'] = floor(($params['value']) / MINUTE_TIMESTAMP) * MINUTE_TIMESTAMP;
        } elseif (!in_array($params['value'], $params['toadd'])) {
            // Round to a valid step except if value is already valid (defined in values to add)
            $params['value'] = floor(($params['value']) / $params['step']) * $params['step'];
        }

        $values = [];

        if ($params['value']) {
            $values[$params['value']] = '';
        }

        if ($params['addfirstminutes']) {
            $max = max($params['min'], 10 * MINUTE_TIMESTAMP);
            for ($i = MINUTE_TIMESTAMP; $i < $max; $i += MINUTE_TIMESTAMP) {
                $values[$i] = '';
            }
        }

        for ($i = $params['min']; $i <= $params['max']; $i += $params['step']) {
            $values[$i] = '';
        }

        if (count($params['toadd'])) {
            foreach ($params['toadd'] as $key) {
                $values[$key] = '';
            }
            ksort($values);
        }

        foreach ($values as $i => $val) {
            if (empty($val)) {
                if ($params['inhours']) {
                    $day  = 0;
                    $hour = floor($i / HOUR_TIMESTAMP);
                } else {
                    $day  = floor($i / DAY_TIMESTAMP);
                    $hour = floor(($i % DAY_TIMESTAMP) / HOUR_TIMESTAMP);
                }
                $minute     = floor(($i % HOUR_TIMESTAMP) / MINUTE_TIMESTAMP);
                if ($minute === '0') {
                    $minute = '00';
                }
                $values[$i] = '';
                if ($day > 0) {
                    if (($hour > 0) || ($minute > 0)) {
                        if ($minute < 10) {
                            $minute = '0' . $minute;
                        }

                        //TRANS: %1$d is the number of days, %2$d the number of hours,
                        //       %3$s the number of minutes : display 1 day 3h15
                        $values[$i] = sprintf(
                            _n('%1$d day %2$dh%3$s', '%1$d days %2$dh%3$s', $day),
                            $day,
                            $hour,
                            $minute
                        );
                    } else {
                        $values[$i] = sprintf(_n('%d day', '%d days', $day), $day);
                    }
                } elseif ($hour > 0 || $minute > 0) {
                    if ($minute < 10) {
                        $minute = '0' . $minute;
                    }

                    //TRANS: %1$d the number of hours, %2$s the number of minutes : display 3h15
                    $values[$i] = sprintf(__('%1$dh%2$s'), $hour, $minute);
                }
            }
        }
        return Dropdown::showFromArray(
            $myname,
            $values,
            ['value'                => $params['value'],
                                              'display'             => $params['display'],
                                              'width'               => $params['width'],
                                              'display_emptychoice' => $params['display_emptychoice'],
                                              'rand'                => $params['rand'],
                                              'emptylabel'          => $params['emptylabel']]
        );
    }


    /**
     * Toggle view in LDAP user import/synchro between no restriction and date restriction
     *
     * @param $enabled (default 0)
    **/
    public static function showAdvanceDateRestrictionSwitch($enabled = 0)
    {
        global $CFG_GLPI;

        $rand = mt_rand();
        $url  = $CFG_GLPI["root_doc"] . "/ajax/ldapdaterestriction.php";
        echo "<script type='text/javascript' >\n";
        echo "function activateRestriction() {\n";
        $params = ['enabled' => 1];
        Ajax::updateItemJsCode('date_restriction', $url, $params);
        echo "};";

        echo "function deactivateRestriction() {\n";
        $params = ['enabled' => 0];
        Ajax::updateItemJsCode('date_restriction', $url, $params);
        echo "};";
        echo "</script>";

        echo "</table>";
        echo "<span id='date_restriction'>";
        $_POST['enabled'] = $enabled;
        include(GLPI_ROOT . "/ajax/ldapdaterestriction.php");
        echo "</span>\n";
        return $rand;
    }


    /**
     * Dropdown of values in an array
     *
     * @param string $name      select name
     * @param array  $elements  array of elements to display
     * @param array  $options   array of possible options:
     *    - value               : integer / preselected value (default 0)
     *    - used                : array / Already used items ID: not to display in dropdown (default empty)
     *    - readonly            : boolean / used as a readonly item (default false)
     *    - on_change           : string / value to transmit to "onChange"
     *    - multiple            : boolean / can select several values (default false)
     *    - size                : integer / number of rows for the select (default = 1)
     *    - display             : boolean / display or return string
     *    - other               : boolean or string if not false, then we can use an "other" value
     *                            if it is a string, then the default value will be this string
     *    - rand                : specific rand if needed (default is generated one)
     *    - width               : specific width needed (default not set)
     *    - emptylabel          : empty label if empty displayed (default self::EMPTY_VALUE)
     *    - display_emptychoice : display empty choice, cannot be used when "multiple" option set to true (default false)
     *    - class               : class attributes to add
     *    - tooltip             : string / message to add as tooltip on the dropdown (default '')
     *    - option_tooltips     : array / message to add as tooltip on the dropdown options. Use the same keys as for the $elements parameter, but none is mandotary. Missing keys will just be ignored and no tooltip will be added. To add a tooltip on an option group, is the '__optgroup_label' key inside the array describing option tooltips : 'optgroupname1' => array('__optgroup_label' => 'tooltip for option group') (default empty)
     *    - noselect2           : if true, don't use select2 lib
     *
     * Permit to use optgroup defining items in arrays
     * array('optgroupname'  => array('key1' => 'val1',
     *                                'key2' => 'val2'),
     *       'optgroupname2' => array('key3' => 'val3',
     *                                'key4' => 'val4'))
     *
     * @return integer|string
     *    integer if option display=true (random part of elements id)
     *    string if option display=false (HTML code)
    **/
    public static function showFromArray($name, array $elements, $options = [])
    {

        $param['value']               = '';
        $param['values']              = [''];
        $param['class']               = '';
        $param['tooltip']             = '';
        $param['option_tooltips']     = [];
        $param['used']                = [];
        $param['readonly']            = false;
        $param['on_change']           = '';
        $param['width']               = '';
        $param['multiple']            = false;
        $param['size']                = 1;
        $param['display']             = true;
        $param['other']               = false;
        $param['rand']                = mt_rand();
        $param['emptylabel']          = self::EMPTY_VALUE;
        $param['display_emptychoice'] = false;
        $param['disabled']            = false;
        $param['noselect2']           = false;

        if (is_array($options) && count($options)) {
            if (isset($options['value']) && strlen($options['value'])) {
                $options['values'] = [$options['value']];
                unset($options['value']);
            }
            foreach ($options as $key => $val) {
                $param[$key] = $val;
            }
        }

        if ($param['other'] !== false) {
            $other_select_option = $name . '_other_value';
            $param['on_change'] .= "displayOtherSelectOptions(this, \"$other_select_option\");";

            // If $param['other'] is a string, then we must highlight "other" option
            if (is_string($param['other'])) {
                if (!$param["multiple"]) {
                    $param['values'] = [$other_select_option];
                } else {
                    $param['values'][] = $other_select_option;
                }
            }
        }

        $param['option_tooltips'] = Html::entities_deep($param['option_tooltips']);

        if ($param["display_emptychoice"] && !$param["multiple"]) {
            $elements = [ 0 => $param['emptylabel'] ] + $elements;
        }

        if ($param["multiple"]) {
            $field_name = $name . "[]";
        } else {
            $field_name = $name;
        }

        $output = '';
        // readonly mode
        $field_id = Html::cleanId("dropdown_" . $name . $param['rand']);
        if ($param['readonly']) {
            $to_display = [];
            foreach ($param['values'] as $value) {
                $output .= "<input type='hidden' name='$field_name' value='$value'>";
                if (isset($elements[$value])) {
                    $to_display[] = $elements[$value];
                }
            }
            $output .= implode('<br>', $to_display);
        } else {
            $input = [
               'type'        => 'select',
               'name'        => $name,
               'id'          => $field_id,
               'aria-label'  => $field_name,
               'title'       => Html::entities_deep($param['tooltip'] ?? $field_name),
               'noLib'       => true,
               'hooks'       => [
                  'change' => $param['on_change'] ?? '',
               ],
               'multiple'    => $param['multiple'] ?? false,
               ($param['disabled'] ? 'disabled' : '') => true,
               ($param['noselect2'] ? 'noLib' : '') => true,
               'style' => ($param['width'] ? ('width:' . $param['width']) : ''),
            ];

            $max_option_size = 0;
            foreach ($elements as $key => $val) {
                // optgroup management
                if (is_array($val)) {
                    $opt_group = Html::entities_deep($key);
                    if ($max_option_size < strlen($opt_group)) {
                        $max_option_size = strlen($opt_group);
                    }

                    $input['values'][$opt_group] = [];

                    foreach ($val as $key2 => $val2) {
                        if (!isset($param['used'][$key2])) {
                            foreach ($param['values'] as $value) {
                                if (strcmp($key2, $value) === 0) {
                                    $input['value'] = $key2;
                                    break;
                                }
                            }
                            $input['values'][$opt_group][$key2] = Html::entities_deep($val2);
                            if ($max_option_size < strlen($val2)) {
                                $max_option_size = strlen($val2);
                            }
                        }
                    }
                } else {
                    if (!isset($param['used'][$key])) {
                        foreach ($param['values'] as $value) {
                            if (strcmp($key, $value) === 0) {
                                $input['value'] = $key;
                                break;
                            }
                        }
                        if ($max_option_size < strlen($val)) {
                            $max_option_size = strlen($val);
                        }
                        $input['values'][Html::entities_deep($key)] = $val;
                    }
                }
            }

            if ($param['other'] !== false) {
                $input['values'][$other_select_option] = __('Other...');
                if (is_string($param['other'])) {
                    $input['value'] = $other_select_option;
                }
            }
        }

        if (!$param['noselect2']) {
            // Width set on select
            $output .= Html::jsAdaptDropdown($field_id, ['width' => $param["width"]]);
        }

        $output .= Ajax::commonDropdownUpdateItem($param, false);

        ob_start();
        renderTwigTemplate('macros/input.twig', $input);
        $output .= ob_get_clean();

        if ($param['display']) {
            echo $output;
            return $param['rand'];
        }
        return $output;
    }


    /**
     * Dropdown for global item management
     *
     * @param integer $ID           item ID
     * @param array   attrs   array which contains the extra paramters
     *
     * Parameters can be :
     * - target target for actions
     * - withtemplate template or basic computer
     * - value value of global state
     * - management_restrict global management restrict mode
    **/
    public static function showGlobalSwitch($ID, $attrs = [])
    {
        $params['management_restrict'] = 0;
        $params['value']               = 0;
        $params['name']                = 'is_global';
        $params['target']              = '';

        foreach ($attrs as $key => $value) {
            if ($value != '') {
                $params[$key] = $value;
            }
        }

        if (
            $params['value']
            && empty($params['withtemplate'])
        ) {
            echo __('Global management');

            if ($params['management_restrict'] == 2) {
                echo "&nbsp;";
                Html::showSimpleForm(
                    $params['target'],
                    'unglobalize',
                    __('Use unitary management'),
                    ['id' => $ID],
                    '',
                    '',
                    [__('Do you really want to use unitary management for this item?'),
                                           __('Duplicate the element as many times as there are connections')]
                );
                echo "&nbsp;";

                echo "<span class='fa fa-info pointer'" .
                     " title=\"" . __s('Duplicate the element as many times as there are connections') .
                     "\"><span class='sr-only'>" . __s('Duplicate the element as many times as there are connections') . "</span></span>";
            }
        } else {
            if ($params['management_restrict'] == 2) {
                $rand = mt_rand();
                $values = [MANAGEMENT_UNITARY => __('Unit management'),
                                MANAGEMENT_GLOBAL  => __('Global management')];
                Dropdown::showFromArray($params['name'], $values, ['value' => $params['value']]);
            } else {
                // Templates edition
                if (!empty($params['withtemplate'])) {
                    echo "<input type='hidden' name='is_global' value='" .
                           $params['management_restrict'] . "'>";
                    echo(!$params['management_restrict'] ? __('Unit management') : __('Global management'));
                } else {
                    echo(!$params['value'] ? __('Unit management') : __('Global management'));
                }
            }
        }
    }


    /**
     * Import a dropdown - check if already exists
     *
     * @param string $itemtype  name of the class
     * @param array  $input     of value to import
     *
     * @return boolean|integer ID of the new item or false on error
    **/
    public static function import($itemtype, $input)
    {

        if (!($item = getItemForItemtype($itemtype))) {
            return false;
        }
        return $item->import($input);
    }


    /**
     * Import a value in a dropdown table.
     *
     * This import a new dropdown if it doesn't exist - Play dictionnary if needed
     *
     * @param string  $itemtype         name of the class
     * @param string  $value            Value of the new dropdown.
     * @param integer $entities_id       entity in case of specific dropdown
     * @param array   $external_params
     * @param string  $comment
     * @param boolean $add              if true, add it if not found. if false, just check if exists
     *
     * @return integer : dropdown id.
    **/
    public static function importExternal(
        $itemtype,
        $value,
        $entities_id = -1,
        $external_params = [],
        $comment = '',
        $add = true
    ) {

        if (!($item = getItemForItemtype($itemtype))) {
            return false;
        }
        return $item->importExternal($value, $entities_id, $external_params, $comment, $add);
    }

    /**
     * Get the label associated with a management type
     *
     * @param integer value the type of management (default 0)
     *
     * @return string the label corresponding to it, or ""
    **/
    public static function getGlobalSwitch($value = 0)
    {

        switch ($value) {
            case 0:
                return __('Unit management');

            case 1:
                return __('Global management');

            default:
                return "";
        }
    }


    /**
     * show dropdown for output format
     *
     * @since 0.83
    **/
    public static function showOutputFormat()
    {
        $values[Search::PDF_OUTPUT_LANDSCAPE]     = __('Current page in landscape PDF');
        $values[Search::PDF_OUTPUT_PORTRAIT]      = __('Current page in portrait PDF');
        $values[Search::SYLK_OUTPUT]              = __('Current page in SLK');
        $values[Search::CSV_OUTPUT]               = __('Current page in CSV');
        $values['-' . Search::PDF_OUTPUT_LANDSCAPE] = __('All pages in landscape PDF');
        $values['-' . Search::PDF_OUTPUT_PORTRAIT]  = __('All pages in portrait PDF');
        $values['-' . Search::SYLK_OUTPUT]          = __('All pages in SLK');
        $values['-' . Search::CSV_OUTPUT]           = __('All pages in CSV');

        Dropdown::showFromArray('display_type', $values);
        echo "<button type='submit' aria-label='Export' name='export' class='unstyled pointer' " .
               " title=\"" . _sx('button', 'Export') . "\">" .
               "<i class='far fa-save' aria-hidden='true'></i><span class='sr-only'>" . _sx('button', 'Export') . "<span>";
    }


    /**
     * show dropdown to select list limit
     *
     * @since 0.83
     *
     * @param string $onchange  Optional, for ajax (default '')
    **/
    public static function showListLimit($onchange = '', $display = true)
    {
        global $CFG_GLPI;

        if (isset($_SESSION['glpilist_limit'])) {
            $list_limit = $_SESSION['glpilist_limit'];
        } else {
            $list_limit = $CFG_GLPI['list_limit'];
        }

        $values = [];

        for ($i = 5; $i < 20; $i += 5) {
            $values[$i] = $i;
        }
        for ($i = 20; $i < 50; $i += 10) {
            $values[$i] = $i;
        }
        for ($i = 50; $i < 250; $i += 50) {
            $values[$i] = $i;
        }
        for ($i = 250; $i < 1000; $i += 250) {
            $values[$i] = $i;
        }
        for ($i = 1000; $i < 5000; $i += 1000) {
            $values[$i] = $i;
        }
        for ($i = 5000; $i <= 10000; $i += 5000) {
            $values[$i] = $i;
        }
        $values[9999999] = 9999999;
        // Propose max input vars -10
        $max             = Toolbox::get_max_input_vars();
        if ($max > 10) {
            $values[$max - 10] = $max - 10;
        }
        ksort($values);
        return self::showFromArray(
            'glpilist_limit',
            $values,
            ['on_change' => $onchange,
                                         'value'     => $list_limit,
                                         'display'   => $display]
        );
    }

    /**
     * Get dropdown value
     *
     * @param array   $post Posted values
     * @param boolean $json Encode to JSON, default to true
     *
     * @return string|array
     */
    public static function getDropdownValue($post, $json = true)
    {
        global $DB, $CFG_GLPI;

        // check if asked itemtype is the one originaly requested by the form
        // if (!Session::validateIDOR($post)) {
        //    return;
        // }

        if (
            isset($post["entity_restrict"])
            && !is_array($post["entity_restrict"])
            && (substr($post["entity_restrict"], 0, 1) === '[')
            && (substr($post["entity_restrict"], -1) === ']')
        ) {
            $decoded = Toolbox::jsonDecode($post['entity_restrict']);
            $entities = [];
            if (is_array($decoded)) {
                foreach ($decoded as $value) {
                    $entities[] = (int)$value;
                }
            }
            $post["entity_restrict"] = $entities;
        }
        if (isset($post['entity_restrict']) && 'default' === $post['entity_restrict']) {
            $post['entity_restrict'] = $_SESSION['glpiactiveentities'];
        }

        // Security
        if (!($item = getItemForItemtype($post['itemtype']))) {
            return;
        }

        $table = $item->getTable();
        $datas = [];

        $displaywith = false;
        if (isset($post['displaywith'])) {
            if (is_array($post['displaywith']) && count($post['displaywith'])) {
                $table = getTableForItemType($post['itemtype']);
                foreach ($post['displaywith'] as $key => $value) {
                    if (!$DB->fieldExists($table, $value)) {
                        unset($post['displaywith'][$key]);
                    }
                }
                if (count($post['displaywith'])) {
                    $displaywith = true;
                }
            }
        }

        if (!isset($post['permit_select_parent'])) {
            $post['permit_select_parent'] = false;
        }

        if (isset($post['condition']) && !empty($post['condition']) && !is_array($post['condition'])) {
            // Retreive conditions from SESSION using its key
            $key = $post['condition'];
            if (isset($_SESSION['glpicondition']) && isset($_SESSION['glpicondition'][$key])) {
                $post['condition'] = $_SESSION['glpicondition'][$key];
            } else {
                $post['condition'] = [];
            }
        }

        if (!isset($post['emptylabel']) || ($post['emptylabel'] == '')) {
            $post['emptylabel'] = Dropdown::EMPTY_VALUE;
        }

        $where = [];

        if ($item->maybeDeleted()) {
            $where["$table.is_deleted"] = 0;
        }
        if ($item->maybeTemplate()) {
            $where["$table.is_template"] = 0;
        }

        if (!isset($post['page'])) {
            $post['page']       = 1;
            $post['page_limit'] = null;
        }

        $start = intval(($post['page'] - 1) * $post['page_limit']);
        $limit = intval($post['page_limit']);

        if (isset($post['used'])) {
            $used = $post['used'];

            if (count($used)) {
                $where['NOT'] = ["$table.id" => $used];
            }
        }

        if (isset($post['toadd'])) {
            $toadd = $post['toadd'];
        } else {
            $toadd = [];
        }

        if (isset($post['condition']) && ($post['condition'] != '')) {
            $where = array_merge($where, $post['condition']);
        }

        $one_item = -1;
        if (isset($post['_one_id'])) {
            $one_item = $post['_one_id'];
        }

        // Count real items returned
        $count = 0;

        if ($item instanceof CommonTreeDropdown) {
            if ($one_item >= 0) {
                $where["$table.id"] = $one_item;
            } else {
                if (!empty($post['searchText'])) {
                    $search = Search::makeTextSearchValue($post['searchText']);

                    $swhere = [
                       "$table.completename" => ['LIKE', $search],
                    ];
                    if (Session::haveTranslations($post['itemtype'], 'completename')) {
                        $swhere["namet.value"] = ['LIKE', $search];
                    }

                    if (
                        $_SESSION['glpiis_ids_visible']
                        && is_numeric($post['searchText']) && (int)$post['searchText'] == $post['searchText']
                    ) {
                        $swhere[$table . '.' . $item->getIndexName()] = ['LIKE', "%{$post['searchText']}%"];
                    }

                    // search also in displaywith columns
                    if ($displaywith && count($post['displaywith'])) {
                        foreach ($post['displaywith'] as $with) {
                            $swhere["$table.$with"] = ['LIKE', $search];
                        }
                    }

                    $where[] = ['OR' => $swhere];
                }
            }

            $multi = false;

            // Manage multiple Entities dropdowns
            $order = ["$table.completename"];

            // No multi if get one item
            if ($item->isEntityAssign()) {
                $recur = $item->maybeRecursive();

                // Entities are not really recursive : do not display parents
                if ($post['itemtype'] == 'Entity') {
                    $recur = false;
                }

                if (isset($post["entity_restrict"]) && !($post["entity_restrict"] < 0)) {
                    $where = $where + getEntitiesRestrictCriteria(
                        $table,
                        '',
                        $post["entity_restrict"],
                        $recur
                    );

                    if (is_array($post["entity_restrict"]) && (count($post["entity_restrict"]) > 1)) {
                        $multi = true;
                    }
                } else {
                    // If private item do not use entity
                    if (!$item->maybePrivate()) {
                        $where = $where + getEntitiesRestrictCriteria($table, '', '', $recur);

                        if (count($_SESSION['glpiactiveentities']) > 1) {
                            $multi = true;
                        }
                    } else {
                        $multi = false;
                    }
                }

                // Force recursive items to multi entity view
                if ($recur) {
                    $multi = true;
                }

                // no multi view for entitites
                if ($post['itemtype'] == "Entity") {
                    $multi = false;
                }

                if ($multi) {
                    array_unshift($order, "$table.entities_id");
                }
            }

            $addselect = [];
            $ljoin = [];
            if (Session::haveTranslations($post['itemtype'], 'completename')) {
                $addselect[] = "namet.value AS transcompletename";
                $ljoin['glpi_dropdowntranslations AS namet'] = [
                   'ON' => [
                      'namet'  => 'items_id',
                      $table   => 'id', [
                         'AND' => [
                            'namet.itemtype'  => $post['itemtype'],
                            'namet.language'  => $_SESSION['glpilanguage'],
                            'namet.field'     => 'completename'
                         ]
                      ]
                   ]
                ];
            }
            if (Session::haveTranslations($post['itemtype'], 'name')) {
                $addselect[] = "namet2.value AS transname";
                $ljoin['glpi_dropdowntranslations AS namet2'] = [
                   'ON' => [
                      'namet2' => 'items_id',
                      $table   => 'id', [
                         'AND' => [
                            'namet2.itemtype' => $post['itemtype'],
                            'namet2.language' => $_SESSION['glpilanguage'],
                            'namet2.field'    => 'name'
                         ]
                      ]
                   ]
                ];
            }
            if (Session::haveTranslations($post['itemtype'], 'comment')) {
                $addselect[] = "commentt.value AS transcomment";
                $ljoin['glpi_dropdowntranslations AS commentt'] = [
                   'ON' => [
                      'commentt'  => 'items_id',
                      $table      => 'id', [
                         'AND' => [
                            'commentt.itemtype'  => $post['itemtype'],
                            'commentt.language'  => $_SESSION['glpilanguage'],
                            'commentt.field'     => 'comment'
                         ]
                      ]
                   ]
                ];
            }

            if ($start > 0 && $multi) {
                //we want to load last entry of previous page
                //(and therefore one more result) to check if
                //entity name must be displayed again
                --$start;
                ++$limit;
            }

            $criteria = [
               'SELECT' => array_merge(["$table.*"], $addselect),
               'FROM'   => $table,
               'WHERE'  => $where,
               'ORDER'  => $order,
               'START'  => $start,
               'LIMIT'  => $limit
            ];
            if (count($ljoin)) {
                $criteria['LEFT JOIN'] = $ljoin;
            }
            $iterator = $DB->request($criteria);

            // Empty search text : display first
            if ($post['page'] == 1 && empty($post['searchText'])) {
                if (isset($post['display_emptychoice']) && $post['display_emptychoice']) {
                    $datas[] = [
                       'id' => 0,
                       'text' => $post['emptylabel']
                    ];
                }
            }

            if ($post['page'] == 1) {
                if (count($toadd)) {
                    foreach ($toadd as $key => $val) {
                        $datas[] = [
                           'id' => $key,
                           'text' => stripslashes($val)
                        ];
                    }
                }
            }
            $last_level_displayed = [];
            $datastoadd           = [];

            // Ignore first item for all pages except first page
            $firstitem = (($post['page'] > 1));
            if (count($iterator)) {
                $prev             = -1;
                $firstitem_entity = -1;

                while ($data = $iterator->next()) {
                    $ID    = $data['id'];
                    $level = $data['level'];

                    if (isset($data['transname']) && !empty($data['transname'])) {
                        $outputval = $data['transname'];
                    } else {
                        $outputval = $data['name'];
                    }
                    $outputval = str_repeat('&nbsp;', max(($level - 1) * 2, 0)) . ($level > 1 ? '>&nbsp;' : '') . $outputval;

                    if (
                        $multi
                        && ($data["entities_id"] != $prev)
                    ) {
                        // Do not do it for first item for next page load
                        if (!$firstitem) {
                            if ($prev >= 0) {
                                if (count($datastoadd)) {
                                    $datas[] = [
                                       'text' => Dropdown::getDropdownName("glpi_entities", $prev),
                                       'children' => $datastoadd
                                    ];
                                }
                            }
                        }
                        $prev = $data["entities_id"];
                        if ($firstitem) {
                            $firstitem_entity = $prev;
                        }
                        // Reset last level displayed :
                        $datastoadd = [];
                    }

                    if ($_SESSION['glpiuse_flat_dropdowntree']) {
                        if (isset($data['transcompletename']) && !empty($data['transcompletename'])) {
                            $outputval = $data['transcompletename'];
                        } else {
                            $outputval = $data['completename'];
                        }
                        $level = 0;
                    } else { // Need to check if parent is the good one
                        // Do not do if only get one item
                        if (($level > 1)) {
                            // Last parent is not the good one need to display arbo
                            if (
                                !isset($last_level_displayed[$level - 1])
                                || ($last_level_displayed[$level - 1] != $data[$item->getForeignKeyField()])
                            ) {
                                $work_level    = $level - 1;
                                $work_parentID = $data[$item->getForeignKeyField()];
                                $parent_datas  = [];
                                do {
                                    // Get parent
                                    if ($item->getFromDB($work_parentID)) {
                                        // Do not do for first item for next page load
                                        if (!$firstitem) {
                                            $title = $item->fields['completename'];

                                            $selection_text = $title;

                                            if (isset($item->fields["comment"])) {
                                                $addcomment
                                                = DropdownTranslation::getTranslatedValue(
                                                    $ID,
                                                    $post['itemtype'],
                                                    'comment',
                                                    $_SESSION['glpilanguage'],
                                                    $item->fields['comment']
                                                );
                                                $title = sprintf(__('%1$s - %2$s'), $title, $addcomment);
                                            }
                                            $output2 = DropdownTranslation::getTranslatedValue(
                                                $item->fields['id'],
                                                $post['itemtype'],
                                                'name',
                                                $_SESSION['glpilanguage'],
                                                $item->fields['name']
                                            );

                                            $temp = ['id'       => $work_parentID,
                                                        'text'     => $output2,
                                                        'level'    => (int)$work_level,
                                                        'disabled' => true];
                                            if ($post['permit_select_parent']) {
                                                $temp['title'] = $title;
                                                $temp['selection_text'] = $selection_text;
                                                unset($temp['disabled']);
                                            }
                                            array_unshift($parent_datas, $temp);
                                        }
                                        $last_level_displayed[$work_level] = $item->fields['id'];
                                        $work_level--;
                                        $work_parentID = $item->fields[$item->getForeignKeyField()];
                                    } else { // Error getting item : stop
                                        $work_level = -1;
                                    }
                                } while (
                                    ($work_level >= 1)
                                         && (!isset($last_level_displayed[$work_level])
                                            || ($last_level_displayed[$work_level] != $work_parentID))
                                );
                                // Add parents
                                foreach ($parent_datas as $val) {
                                    $datastoadd[] = $val;
                                }
                            }
                        }
                        $last_level_displayed[$level] = $data['id'];
                    }

                    // Do not do for first item for next page load
                    if (!$firstitem) {
                        if (
                            $_SESSION["glpiis_ids_visible"]
                            || (Toolbox::strlen($outputval) == 0)
                        ) {
                            $outputval = sprintf(__('%1$s (%2$s)'), $outputval, $ID);
                        }

                        if (isset($data['transcompletename']) && !empty($data['transcompletename'])) {
                            $title = $data['transcompletename'];
                        } else {
                            $title = $data['completename'];
                        }

                        $selection_text = $title;

                        if (isset($data["comment"])) {
                            if (isset($data['transcomment']) && !empty($data['transcomment'])) {
                                $addcomment = $data['transcomment'];
                            } else {
                                $addcomment = $data['comment'];
                            }
                            $title = sprintf(__('%1$s - %2$s'), $title, $addcomment);
                        }
                        $datastoadd[] = [
                           'id' => $ID,
                           'text' => $outputval,
                           'level' => (int)$level,
                           'title' => $title,
                           'selection_text' => $selection_text
                        ];
                        $count++;
                    }
                    $firstitem = false;
                }
            }

            if ($multi) {
                if (count($datastoadd)) {
                    // On paging mode do not add entity information each time
                    if ($prev == $firstitem_entity) {
                        $datas = array_merge($datas, $datastoadd);
                    } else {
                        $datas[] = [
                           'text' => Dropdown::getDropdownName("glpi_entities", $prev),
                           'children' => $datastoadd
                        ];
                    }
                }
            } else {
                if (count($datastoadd)) {
                    $datas = array_merge($datas, $datastoadd);
                }
            }
        } else { // Not a dropdowntree
            $multi = false;
            // No multi if get one item
            if ($item->isEntityAssign()) {
                $multi = $item->maybeRecursive();

                if (isset($post["entity_restrict"]) && !($post["entity_restrict"] < 0)) {
                    $where = $where + getEntitiesRestrictCriteria(
                        $table,
                        "entities_id",
                        $post["entity_restrict"],
                        $multi
                    );

                    if (is_array($post["entity_restrict"]) && (count($post["entity_restrict"]) > 1)) {
                        $multi = true;
                    }
                } else {
                    // Do not use entity if may be private
                    if (!$item->maybePrivate()) {
                        $where = $where + getEntitiesRestrictCriteria($table, '', '', $multi);

                        if (count($_SESSION['glpiactiveentities']) > 1) {
                            $multi = true;
                        }
                    } else {
                        $multi = false;
                    }
                }
            }

            $field = "name";
            if ($item instanceof CommonDevice) {
                $field = "designation";
            } elseif ($item instanceof Item_Devices) {
                $field = "itemtype";
            }

            if (!empty($post['searchText'])) {
                $search = Search::makeTextSearchValue($post['searchText']);
                $orwhere = ["$table.$field" => ['LIKE', $search]];

                if (
                    $_SESSION['glpiis_ids_visible']
                    && is_numeric($post['searchText']) && (int)$post['searchText'] == $post['searchText']
                ) {
                    $orwhere[$table . '.' . $item->getIndexName()] = ['LIKE', "%{$post['searchText']}%"];
                }

                if ($item instanceof CommonDCModelDropdown) {
                    $orwhere[$table . '.product_number'] = ['LIKE', $search];
                }

                if (Session::haveTranslations($post['itemtype'], $field)) {
                    $orwhere['namet.value'] = ['LIKE', $search];
                }
                if ($post['itemtype'] == "SoftwareLicense") {
                    $orwhere['glpi_softwares.name'] = ['LIKE', $search];
                }

                // search also in displaywith columns
                if ($displaywith && count($post['displaywith'])) {
                    foreach ($post['displaywith'] as $with) {
                        $orwhere["$table.$with"] = ['LIKE', $search];
                    }
                }

                $where[] = ['OR' => $orwhere];
            }
            $addselect = [];
            $ljoin = [];
            if (Session::haveTranslations($post['itemtype'], $field)) {
                $addselect[] = "namet.value AS transname";
                $ljoin['glpi_dropdowntranslations AS namet'] = [
                   'ON' => [
                      'namet'  => 'items_id',
                      $table   => 'id', [
                         'AND' => [
                            'namet.itemtype'  => $post['itemtype'],
                            'namet.language'  => $_SESSION['glpilanguage'],
                            'namet.field'     => $field
                         ]
                      ]
                   ]
                ];
            }
            if (Session::haveTranslations($post['itemtype'], 'comment')) {
                $addselect[] = "commentt.value AS transcomment";
                $ljoin['glpi_dropdowntranslations AS commentt'] = [
                   'ON' => [
                      'commentt'  => 'items_id',
                      $table      => 'id', [
                         'AND' => [
                            'commentt.itemtype'  => $post['itemtype'],
                            'commentt.language'  => $_SESSION['glpilanguage'],
                            'commentt.field'     => 'comment'
                         ]
                      ]
                   ]
                ];
            }

            $criteria = [];
            switch ($post['itemtype']) {
                case "Contact":
                    $criteria = [
                       'SELECT' => [
                          "$table.entities_id",
                          new \QueryExpression(
                              "CONCAT(IFNULL(" . $DB->quoteName('name') . ",''),' ',IFNULL(" .
                              $DB->quoteName('firstname') . ",'')) AS " . $DB->quoteName($field)
                          ),
                          "$table.comment",
                          "$table.id"
                       ],
                       'FROM'   => $table
                    ];
                    break;

                case "SoftwareLicense":
                    $criteria = [
                       'SELECT' => [
                          "$table.*",
                          new \QueryExpression("CONCAT(glpi_softwares.name,' - ',glpi_softwarelicenses.name) AS $field")
                       ],
                       'FROM'   => $table,
                       'LEFT JOIN' => [
                          'glpi_softwares'  => [
                             'ON' => [
                                'glpi_softwarelicenses' => 'softwares_id',
                                'glpi_softwares'        => 'id'
                             ]
                          ]
                       ]
                    ];
                    break;

                case "Profile":
                    $criteria = [
                       'SELECT'          => "$table.*",
                       'DISTINCT'        => true,
                       'FROM'            => $table,
                       'LEFT JOIN'       => [
                          'glpi_profilerights' => [
                             'ON' => [
                                'glpi_profilerights' => 'profiles_id',
                                $table               => 'id'
                             ]
                          ]
                       ]
                    ];
                    break;

                case KnowbaseItem::getType():
                    $criteria = [
                       'SELECT' => array_merge(["$table.*"], $addselect),
                       'DISTINCT'        => true,
                       'FROM'            => $table
                    ];
                    if (count($ljoin)) {
                        $criteria['LEFT JOIN'] = $ljoin;
                    }

                    $visibility = KnowbaseItem::getVisibilityCriteria();
                    if (count($visibility['LEFT JOIN'])) {
                        $criteria['LEFT JOIN'] = array_merge(
                            (isset($criteria['LEFT JOIN']) ? $criteria['LEFT JOIN'] : []),
                            $visibility['LEFT JOIN']
                        );
                        //Do not use where??
                        /*if (isset($visibility['WHERE'])) {
                           $where = $visibility['WHERE'];
                        }*/
                    }
                    break;

                case Project::getType():
                    $visibility = Project::getVisibilityCriteria();
                    if (count($visibility['LEFT JOIN'])) {
                        $ljoin = array_merge($ljoin, $visibility['LEFT JOIN']);
                        if (isset($visibility['WHERE'])) {
                            $where[] = $visibility['WHERE'];
                        }
                    }
                    //no break to reach default case.

                default:
                    $criteria = [
                       'SELECT' => array_merge(["$table.*"], $addselect),
                       'FROM'   => $table
                    ];
                    if (count($ljoin)) {
                        $criteria['LEFT JOIN'] = $ljoin;
                    }
            }

            $criteria = array_merge(
                $criteria,
                [
                  'WHERE'  => $where,
                  'START'  => $start,
                  'LIMIT'  => $limit
                ]
            );

            if ($multi) {
                $criteria['ORDERBY'] = ["$table.entities_id", "$table.$field"];
            } else {
                $criteria['ORDERBY'] = ["$table.$field"];
            }

            $iterator = $DB->request($criteria);

            // Display first if no search
            if ($post['page'] == 1 && empty($post['searchText'])) {
                if (!isset($post['display_emptychoice']) || $post['display_emptychoice']) {
                    $datas[] = [
                       'id' => 0,
                       'text' => $post["emptylabel"]
                    ];
                }
            }
            if ($post['page'] == 1) {
                if (count($toadd)) {
                    foreach ($toadd as $key => $val) {
                        $datas[] = [
                           'id' => $key,
                           'text' => stripslashes($val)
                        ];
                    }
                }
            }

            $datastoadd = [];

            if (count($iterator)) {
                $prev = -1;

                while ($data = $iterator->next()) {
                    if (
                        $multi
                        && ($data["entities_id"] != $prev)
                    ) {
                        if ($prev >= 0) {
                            if (count($datastoadd)) {
                                $datas[] = [
                                   'text' => Dropdown::getDropdownName("glpi_entities", $prev),
                                   'children' => $datastoadd
                                ];
                            }
                        }
                        $prev       = $data["entities_id"];
                        $datastoadd = [];
                    }

                    if (isset($data['transname']) && !empty($data['transname'])) {
                        $outputval = $data['transname'];
                    } elseif ($field == 'itemtype' && class_exists($data['itemtype'])) {
                        $tmpitem = new $data[$field]();
                        if ($tmpitem->getFromDB($data['items_id'])) {
                            $outputval = sprintf(__('%1$s - %2$s'), $tmpitem->getTypeName(), $tmpitem->getName());
                        } else {
                            $outputval = $tmpitem->getTypeName();
                        }
                    } elseif ($item instanceof CommonDCModelDropdown) {
                        $outputval = sprintf(__('%1$s - %2$s'), $data[$field], $data['product_number']);
                    } else {
                        $outputval = $data[$field];
                    }

                    $ID         = $data['id'];
                    $addcomment = "";
                    $title      = $outputval;
                    if (isset($data["comment"])) {
                        if (isset($data['transcomment']) && !empty($data['transcomment'])) {
                            $addcomment .= $data['transcomment'];
                        } else {
                            $addcomment .= $data["comment"];
                        }

                        $title = sprintf(__('%1$s - %2$s'), $title, $addcomment);
                    }
                    if (
                        $_SESSION["glpiis_ids_visible"]
                        || (strlen($outputval) == 0)
                    ) {
                        //TRANS: %1$s is the name, %2$s the ID
                        $outputval = sprintf(__('%1$s (%2$s)'), $outputval, $ID);
                    }
                    if ($displaywith) {
                        foreach ($post['displaywith'] as $key) {
                            if (isset($data[$key])) {
                                $withoutput = $data[$key];
                                if (isForeignKeyField($key)) {
                                    $withoutput = Dropdown::getDropdownName(
                                        getTableNameForForeignKeyField($key),
                                        $data[$key]
                                    );
                                }
                                if ((strlen($withoutput) > 0) && ($withoutput != '&nbsp;')) {
                                    $outputval = sprintf(__('%1$s - %2$s'), $outputval, $withoutput);
                                }
                            }
                        }
                    }
                    $datastoadd[] = [
                       'id' => $ID,
                       'text' => $outputval,
                       'title' => $title
                    ];
                    $count++;
                }
                if ($multi) {
                    if (count($datastoadd)) {
                        $datas[] = [
                           'text' => Dropdown::getDropdownName("glpi_entities", $prev),
                           'children' => $datastoadd
                        ];
                    }
                } else {
                    if (count($datastoadd)) {
                        $datas = array_merge($datas, $datastoadd);
                    }
                }
            }
        }

        $ret['results'] = Toolbox::unclean_cross_side_scripting_deep($datas);
        $ret['count']   = $count;
        $ret['pagination']['more']    = ($count >= $post['page_limit']);

        return ($json === true) ? json_encode($ret) : $ret;
    }

    /**
     * Get dropdown connect
     *
     * @param array   $post Posted values
     * @param boolean $json Encode to JSON, default to true
     *
     * @return string|array
     */
    public static function getDropdownConnect($post, $json = true)
    {
        global $DB, $CFG_GLPI;

        // check if asked itemtype is the one originaly requested by the form
        if (!Session::validateIDOR($post)) {
            return;
        }

        if (!isset($post['fromtype']) || !($fromitem = getItemForItemtype($post['fromtype']))) {
            return;
        }

        $fromitem->checkGlobal(UPDATE);
        $used = [];
        if (isset($post["used"])) {
            $used = $post["used"];

            if (isset($used[$post['itemtype']])) {
                $used = $used[$post['itemtype']];
            } else {
                $used = [];
            }
        }

        // Make a select box
        $table = getTableForItemType($post["itemtype"]);
        if (!$item = getItemForItemtype($post['itemtype'])) {
            return;
        }

        $where = [];

        if ($item->maybeDeleted()) {
            $where["$table.is_deleted"] = 0;
        }
        if ($item->maybeTemplate()) {
            $where["$table.is_template"] = 0;
        }

        if (isset($post['searchText']) && (strlen($post['searchText']) > 0)) {
            $search = Search::makeTextSearchValue($post['searchText']);
            $where['OR'] = [
               "$table.name"        => ['LIKE', $search],
               "$table.otherserial" => ['LIKE', $search],
               "$table.serial"      => ['LIKE', $search]
            ];
        }

        $multi = $item->maybeRecursive();

        if (isset($post["entity_restrict"]) && !($post["entity_restrict"] < 0)) {
            $where = $where + getEntitiesRestrictCriteria($table, '', $post["entity_restrict"], $multi);
            if (is_array($post["entity_restrict"]) && (count($post["entity_restrict"]) > 1)) {
                $multi = true;
            }
        } else {
            $where = $where + getEntitiesRestrictCriteria($table, '', $_SESSION['glpiactiveentities'], $multi);
            if (count($_SESSION['glpiactiveentities']) > 1) {
                $multi = true;
            }
        }

        if (!isset($post['page'])) {
            $post['page']       = 1;
            $post['page_limit'] = $CFG_GLPI['dropdown_max'];
        }

        $start = intval(($post['page'] - 1) * $post['page_limit']);
        $limit = intval($post['page_limit']);

        if (!isset($post['onlyglobal'])) {
            $post['onlyglobal'] = false;
        }

        if (
            $post["onlyglobal"]
            && ($post["itemtype"] != 'Computer')
        ) {
            $where["$table.is_global"] = 1;
        } else {
            $where_used = [];
            if (!empty($used)) {
                $where_used[] = ['NOT' => ["$table.id" => $used]];
            }

            if ($post["itemtype"] == 'Computer') {
                $where = $where + $where_used;
            } else {
                $where[] = [
                   'OR' => [
                      [
                         'glpi_computers_items.id'  => null
                      ] + $where_used,
                      "$table.is_global"            => 1
                   ]
                ];
            }
        }

        $criteria = [
           'SELECT'          => [
              "$table.id",
              "$table.name AS name",
              "$table.serial AS serial",
              "$table.otherserial AS otherserial",
              "$table.entities_id AS entities_id"
           ],
           'DISTINCT'        => true,
           'FROM'            => $table,
           'WHERE'           => $where,
           'ORDERBY'         => ['entities_id', 'name ASC'],
           'LIMIT'           => $limit,
           'START'           => $start
        ];

        if (($post["itemtype"] != 'Computer') && !$post["onlyglobal"]) {
            $criteria['LEFT JOIN'] = [
               'glpi_computers_items'  => [
                  'ON' => [
                     $table                  => 'id',
                     'glpi_computers_items'  => 'items_id', [
                        'AND' => [
                           'glpi_computers_items.itemtype'  => $post['itemtype']
                        ]
                     ]
                  ]
               ]
            ];
        }

        $iterator = $DB->request($criteria);

        $results = [];
        // Display first if no search
        if (empty($post['searchText'])) {
            $results[] = [
               'id' => 0,
               'text' => Dropdown::EMPTY_VALUE
            ];
        }
        if (count($iterator)) {
            $prev       = -1;
            $datatoadd = [];

            while ($data = $iterator->next()) {
                if ($multi && ($data["entities_id"] != $prev)) {
                    if (count($datatoadd)) {
                        $results[] = [
                           'text' => Dropdown::getDropdownName("glpi_entities", $prev),
                           'children' => $datatoadd
                        ];
                    }
                    $prev = $data["entities_id"];
                    // Reset last level displayed :
                    $datatoadd = [];
                }
                $output = $data['name'];
                $ID     = $data['id'];

                if (
                    $_SESSION["glpiis_ids_visible"]
                    || empty($output)
                ) {
                    $output = sprintf(__('%1$s (%2$s)'), $output, $ID);
                }
                if (!empty($data['serial'])) {
                    $output = sprintf(__('%1$s - %2$s'), $output, $data["serial"]);
                }
                if (!empty($data['otherserial'])) {
                    $output = sprintf(__('%1$s - %2$s'), $output, $data["otherserial"]);
                }
                $datatoadd[] = [
                   'id' => $ID,
                   'text' => $output
                ];
            }

            if ($multi) {
                if (count($datatoadd)) {
                    $results[] = [
                       'text' => Dropdown::getDropdownName("glpi_entities", $prev),
                       'children' => $datatoadd
                    ];
                }
            } else {
                if (count($datatoadd)) {
                    $results = array_merge($results, $datatoadd);
                }
            }
        }

        $ret['results'] = $results;
        return ($json === true) ? json_encode($ret) : $ret;
    }

    /**
     * Get dropdown find num
     *
     * @param array   $post Posted values
     * @param boolean $json Encode to JSON, default to true
     *
     * @return string|array
     */
    public static function getDropdownFindNum($post, $json = true)
    {
        global $DB, $CFG_GLPI;

        // Security
        if (!$DB->tableExists($post['table'])) {
            return;
        }

        $itemtypeisplugin = isPluginItemType($post['itemtype']);

        // check if asked itemtype is the one originaly requested by the form
        if (!Session::validateIDOR($post)) {
            return;
        }

        if (!$item = getItemForItemtype($post['itemtype'])) {
            return;
        }

        $where = [];
        if (isset($post['used']) && !empty($post['used'])) {
            $where['NOT'] = ['id' => $post['used']];
        }

        if ($item->maybeDeleted()) {
            $where['is_deleted'] = 0;
        }

        if ($item->maybeTemplate()) {
            $where['is_template'] = 0;
        }

        if (isset($_POST['searchText']) && (strlen($post['searchText']) > 0)) {
            $search = ['LIKE', Search::makeTextSearchValue($post['searchText'])];
            $orwhere = [
               'name'   => $search,
               'id'     => $post['searchText']
            ];

            if ($DB->fieldExists($post['table'], "contact")) {
                $orwhere['contact'] = $search;
            }
            if ($DB->fieldExists($post['table'], "serial")) {
                $orwhere['serial'] = $search;
            }
            if ($DB->fieldExists($post['table'], "otherserial")) {
                $orwhere['otherserial'] = $search;
            }
            $where[] = ['OR' => $orwhere];
        }

        // If software or plugins : filter to display only the objects that are allowed to be visible in Helpdesk
        $filterHelpdesk = in_array($post['itemtype'], $CFG_GLPI["helpdesk_visible_types"]);

        if (
            isset($post['context'])
            && $post['context'] == "impact"
            && Impact::isEnabled($post['itemtype'])
        ) {
            $filterHelpdesk = false;
        }

        if ($filterHelpdesk) {
            $where['is_helpdesk_visible'] = 1;
        }

        if ($item->isEntityAssign()) {
            if (isset($post["entity_restrict"]) && ($post["entity_restrict"] >= 0)) {
                $entity = $post["entity_restrict"];
            } else {
                $entity = '';
            }

            // allow opening ticket on recursive object (printer, software, ...)
            $recursive = $item->maybeRecursive();
            $where     = $where + getEntitiesRestrictCriteria($post['table'], '', $entity, $recursive);
        }

        if (!isset($post['page'])) {
            $post['page']       = 1;
            $post['page_limit'] = $CFG_GLPI['dropdown_max'];
        }

        $start = intval(($post['page'] - 1) * $post['page_limit']);
        $limit = intval($post['page_limit']);

        $iterator = $DB->request([
           'FROM'   => $post['table'],
           'WHERE'  => $where,
           'ORDER'  => $item->getNameField(),
           'LIMIT'  => $limit,
           'START'  => $start
        ]);

        $results = [];

        // Display first if no search
        if ($post['page'] == 1 && empty($post['searchText'])) {
            $results[] = [
               'id' => 0,
               'text' => Dropdown::EMPTY_VALUE
            ];
        }
        $count = 0;
        if (count($iterator)) {
            while ($data = $iterator->next()) {
                $output = $data[$item->getNameField()];

                if (isset($data['contact']) && !empty($data['contact'])) {
                    $output = sprintf(__('%1$s - %2$s'), $output, $data['contact']);
                }
                if (isset($data['serial']) && !empty($data['serial'])) {
                    $output = sprintf(__('%1$s - %2$s'), $output, $data['serial']);
                }
                if (isset($data['otherserial']) && !empty($data['otherserial'])) {
                    $output = sprintf(__('%1$s - %2$s'), $output, $data['otherserial']);
                }

                if (
                    empty($output)
                    || $_SESSION['glpiis_ids_visible']
                ) {
                    $output = sprintf(__('%1$s (%2$s)'), $output, $data['id']);
                }

                $results[] = [
                   'id' => $data['id'],
                   'text' => $output
                ];
                $count++;
            }
        }

        $ret['count']   = $count;
        $ret['results'] = $results;

        return ($json === true) ? json_encode($ret) : $ret;
    }

    /**
     * Get dropdown netpoint
     *
     * @param array   $post Posted values
     * @param boolean $json Encode to JSON, default to true
     *
     * @return string|array
     */
    public static function getDropdownNetpoint($post, $json = true)
    {
        global $DB, $CFG_GLPI;

        // Make a select box with preselected values
        $results           = [];
        $location_restrict = false;

        if (!isset($post['page'])) {
            $post['page']       = 1;
            $post['page_limit'] = $CFG_GLPI['dropdown_max'];
        }

        $start = intval(($post['page'] - 1) * $post['page_limit']);
        $limit = intval($post['page_limit']);

        $criteria = [
           'SELECT'    => [
              'glpi_netpoints.comment AS comment',
              'glpi_netpoints.id',
              'glpi_netpoints.name AS netpname',
              'glpi_locations.completename AS loc'
           ],
           'FROM'      => 'glpi_netpoints',
           'LEFT JOIN' => [
              'glpi_locations'  => [
                 'ON' => [
                    'glpi_netpoints'  => 'locations_id',
                    'glpi_locations'  => 'id'
                 ]
              ]
           ],
           'WHERE'     => [],
           'ORDERBY'   => [
              'glpi_locations.completename',
              'glpi_netpoints.name'
           ],
           'START'     => $start,
           'LIMIT'     => $limit
        ];

        if (
            !(isset($post["devtype"])
              && ($post["devtype"] != 'NetworkEquipment')
              && isset($post["locations_id"])
              && ($post["locations_id"] > 0))
        ) {
            if (isset($post["entity_restrict"]) && ($post["entity_restrict"] >= 0)) {
                $criteria['WHERE']['glpi_netpoints.entities_id'] = $post['entity_restrict'];
            } else {
                $criteria['WHERE'] = $criteria['WHERE'] + getEntitiesRestrictCriteria('glpi_locations');
            }
        }

        if (isset($post['searchText']) && strlen($post['searchText']) > 0) {
            $criteria['WHERE']['OR'] = [
               'glpi_netpoints.name'         => ['LIKE', Search::makeTextSearchValue($post['searchText'])],
               'glpi_locations.completename' => ['LIKE', Search::makeTextSearchValue($post['searchText'])]
            ];
        }

        if (isset($post["devtype"]) && !empty($post["devtype"])) {
            $criteria['LEFT JOIN']['glpi_networkportethernets'] = [
               'ON' => [
                  'glpi_networkportethernets'   => 'netpoints_id',
                  'glpi_netpoints'              => 'id'
               ]
            ];

            $extra_and = [];
            if ($post["devtype"] == 'NetworkEquipment') {
                $extra_and['glpi_networkports.itemtype'] = 'NetworkEquipment';
            } else {
                $extra_and['NOT'] = ['glpi_networkports.itemtype' => 'NetworkEquipment'];
                if (isset($post["locations_id"]) && ($post["locations_id"] >= 0)) {
                    $location_restrict = true;
                    $criteria['WHERE']['glpi_netpoints.locations_id'] = $post['locations_id'];
                }
            }

            $criteria['LEFT JOIN']['glpi_networkports'] = [
               'ON' => [
                  'glpi_networkportethernets'   => 'id',
                  'glpi_networkports'           => 'id', [
                     'AND' => [
                        'glpi_networkports.instantiation_type'    => 'NetworkPortEthernet',
                     ] + $extra_and
                  ]
               ]
            ];
            $criteria['WHERE']['glpi_networkportethernets.netpoints_id'] = null;
        } elseif (isset($post["locations_id"]) && ($post["locations_id"] >= 0)) {
            $location_restrict = true;
            $criteria['WHERE']['glpi_netpoints.locations_id'] = $post['locations_id'];
        }

        $iterator = $DB->request($criteria);

        // Display first if no search
        if (empty($post['searchText'])) {
            if ($post['page'] == 1) {
                $results[] = [
                   'id' => 0,
                   'text' => Dropdown::EMPTY_VALUE
                ];
            }
        }

        $count = 0;
        if (count($iterator)) {
            while ($data = $iterator->next()) {
                $output     = $data['netpname'];
                $loc        = $data['loc'];
                $ID         = $data['id'];
                $title      = $output;
                if (isset($data["comment"])) {
                    //TRANS: %1$s is the location, %2$s is the comment
                    $title = sprintf(__('%1$s - %2$s'), $title, $loc);
                    $title = sprintf(__('%1$s - %2$s'), $title, $data["comment"]);
                }
                if (!$location_restrict) {
                    $output = sprintf(__('%1$s (%2$s)'), $output, $loc);
                }

                $results[] = [
                   'id' => $ID,
                   'text' => $output,
                   'title' => $title
                ];
                $count++;
            }
        }

        $ret['count']   = $count;
        $ret['results'] = $results;

        return ($json === true) ? json_encode($ret) : $ret;
    }

    /**
     * Get dropdown number
     *
     * @param array   $post Posted values
     * @param boolean $json Encode to JSON, default to true
     *
     * @return string|array
     */
    public static function getDropdownNumber($post, $json = true)
    {
        global $CFG_GLPI;

        $used = [];

        if (isset($post['used'])) {
            $used = $post['used'];
        }

        if (!isset($post['value'])) {
            $post['value'] = 0;
        }

        if (!isset($post['page'])) {
            $post['page']       = 1;
            $post['page_limit'] = $CFG_GLPI['dropdown_max'];
        }

        if (isset($post['toadd'])) {
            $toadd = $post['toadd'];
        } else {
            $toadd = [];
        }

        $data = [];
        // Count real items returned
        $count = 0;

        if ($post['page'] == 1) {
            if (count($toadd)) {
                foreach ($toadd as $key => $val) {
                    $data[] = ['id' => $key,
                       'text' => (string)stripslashes($val)];
                }
            }
        }

        $values = [];

        if (!isset($post['min'])) {
            $post['min'] = 1;
        }

        if (!isset($post['step'])) {
            $post['step'] = 1;
        }

        if (!isset($post['max'])) {
            //limit max entries to avoid loop issues
            $post['max'] = $CFG_GLPI['dropdown_max'] * $post['step'];
        }

        for ($i = $post['min']; $i <= $post['max']; $i += $post['step']) {
            if (!empty($post['searchText']) && strstr($i, $post['searchText']) || empty($post['searchText'])) {
                if (!in_array($i, $used)) {
                    $values["$i"] = $i;
                }
            }
        }

        if (count($values)) {
            $start  = ($post['page'] - 1) * $post['page_limit'];
            $tosend = array_splice($values, $start, $post['page_limit']);
            foreach ($tosend as $i) {
                $txt = $i;
                if (isset($post['unit'])) {
                    $decimals = Toolbox::isFloat($i) ? Toolbox::getDecimalNumbers($post['step']) : 0;
                    $txt = Dropdown::getValueWithUnit($i, $post['unit'], $decimals);
                }
                $data[] = ['id' => $i,
                   'text' => (string)$txt];
                $count++;
            }
        } else {
            if (!isset($toadd[-1])) {
                $value = -1;
                if (isset($post['min']) && $value < $post['min']) {
                    $value = $post['min'];
                } elseif (isset($post['max']) && $value > $post['max']) {
                    $value = $post['max'];
                }

                if (isset($post['unit'])) {
                    $decimals = Toolbox::isFloat($value) ? Toolbox::getDecimalNumbers($post['step']) : 0;
                    $txt = Dropdown::getValueWithUnit($value, $post['unit'], $decimals);
                }
                $data[] = [
                   'id' => $value,
                   'text' => (string)stripslashes($txt)
                ];
                $count++;
            }
        }

        $ret['results'] = $data;
        $ret['count']   = $count;

        return ($json === true) ? json_encode($ret) : $ret;
    }

    /**
     * Get dropdown users
     *
     * @param array   $post Posted values
     * @param boolean $json Encode to JSON, default to true
     *
     * @return string|array
     */
    public static function getDropdownUsers($post, $json = true)
    {
        global $CFG_GLPI;

        // check if asked itemtype is the one originaly requested by the form
        if (!Session::validateIDOR($post + ['itemtype' => 'User', 'right' => ($post['right'] ?? "")])) {
            return;
        }

        if (!isset($post['right'])) {
            $post['right'] = "all";
        }

        // Default view : Nobody
        if (!isset($post['all'])) {
            $post['all'] = 0;
        }

        $used = [];

        if (isset($post['used'])) {
            $used = $post['used'];
        }

        if (!isset($post['value'])) {
            $post['value'] = 0;
        }

        if (!isset($post['page'])) {
            $post['page']       = 1;
            $post['page_limit'] = $CFG_GLPI['dropdown_max'];
        }

        $entity_restrict = -1;
        if (isset($post['entity_restrict'])) {
            $entity_restrict = Toolbox::jsonDecode($post['entity_restrict']);
        }

        $start  = intval(($post['page'] - 1) * $post['page_limit']);
        $searchText = (isset($post['searchText']) ? $post['searchText'] : null);
        $inactive_deleted = isset($post['inactive_deleted']) ? $post['inactive_deleted'] : 0;
        $with_no_right = isset($post['with_no_right']) ? $post['with_no_right'] : 0;
        $result = User::getSqlSearchResult(
            false,
            $post['right'],
            $entity_restrict,
            $post['value'],
            $used,
            $searchText,
            $start,
            (int)$post['page_limit'],
            $inactive_deleted,
            $with_no_right
        );

        $users = [];

        // Count real items returned
        $count = 0;
        if (count($result)) {
            while ($data = $result->next()) {
                $users[$data["id"]] = formatUserName(
                    $data["id"],
                    $data["name"],
                    $data["realname"],
                    $data["firstname"]
                );
                $logins[$data["id"]] = $data["name"];
            }
        }

        $results = [];

        // Display first if empty search
        if ($post['page'] == 1 && empty($post['searchText'])) {
            if ($post['all'] == 0) {
                $results[] = [
                   'id' => 0,
                   'text' => Dropdown::EMPTY_VALUE
                ];
            } elseif ($post['all'] == 1) {
                $results[] = [
                   'id' => 0,
                   'text' => __('All')
                ];
            }
        }

        if (count($users)) {
            foreach ($users as $ID => $output) {
                $title = sprintf(__('%1$s - %2$s'), $output, $logins[$ID]);

                $results[] = [
                   'id' => $ID,
                   'text' => $output,
                   'title' => $title
                ];
                $count++;
            }
        }

        $ret['results'] = $results;
        $ret['count']   = $count;

        return ($json === true) ? json_encode($ret) : $ret;
    }
}
