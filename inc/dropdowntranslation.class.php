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
 * DropdownTranslation Class
 *
 *@since 0.85
**/
class DropdownTranslation extends CommonDBChild
{
    public static $itemtype = 'itemtype';
    public static $items_id = 'items_id';
    public $dohistory       = true;
    public static $rightname       = 'dropdown';


    public static function getTypeName($nb = 0)
    {
        return _n('Translation', 'Translations', $nb);
    }


    /**
     * Forbidden massives actions
    **/
    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (self::canBeTranslated($item)) {
            $nb = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
                $nb = self::getNumberOfTranslationsForItem($item);
            }
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
        }
        return '';
    }


    /**
     * @param $item            CommonGLPI object
     * @param $tabnum          (default 1)
     * @param $withtemplate    (default 0)
    **/
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if (DropdownTranslation::canBeTranslated($item)) {
            DropdownTranslation::showTranslations($item);
        }
        return true;
    }


    public function prepareInputForAdd($input)
    {

        if ($this->checkBeforeAddorUpdate($input, true)) {
            return $input;
        }
        Session::addMessageAfterRedirect(
            __("There's already a translation for this field in this language"),
            true,
            ERROR
        );
        return false;
    }


    public function prepareInputForUpdate($input)
    {

        if ($this->checkBeforeAddorUpdate($input, false)) {
            return $input;
        }
        Session::addMessageAfterRedirect(
            __("There's already a translation for this field in this language"),
            true,
            ERROR
        );
        return false;
    }


    public function post_purgeItem()
    {
        if ($this->fields['field'] == 'name') {
            $translation = new self();
            //If last translated field is deleted, then delete also completename record
            if (
                $this->getNumberOfTranslations(
                    $this->fields['itemtype'],
                    $this->fields['items_id'],
                    $this->fields['field'],
                    $this->fields['language']
                ) == 0
            ) {
                if (
                    $completenames_id = self::getTranslationID(
                        $this->fields['items_id'],
                        $this->fields['itemtype'],
                        'completename',
                        $this->fields['language']
                    )
                ) {
                    $translation->delete(['id' => $completenames_id]);
                }
            }
            // If only completename for sons : drop
            // foreach (getSonsOf(getTableForItemType($this->fields['itemtype']),
            //                                        $this->fields['items_id']) as $son) {

            //    if ($this->getNumberOfTranslations($this->fields['itemtype'], $son,
            //                                      'name', $this->fields['language']) == 0) {

            //       $completenames_id = self::getTranslationID($son, $this->fields['itemtype'],
            //                                                      'completename',
            //                                                      $this->fields['language']);
            //       if ($completenames_id) {
            //          $translation = new self();
            //          $translation->delete(array('id' => $completenames_id));
            //       }
            //    }
            // }
            // Then update all sons records
            if (!isset($this->input['_no_completename'])) {
                $translation->generateCompletename($this->fields, false);
            }
        }
        return true;
    }


    public function post_updateItem($history = 1)
    {

        if (!isset($this->input['_no_completename'])) {
            $translation = new self();
            $translation->generateCompletename($this->fields, false);
        }
    }


    public function post_addItem()
    {

        // Add to session
        $_SESSION['glpi_dropdowntranslations'][$this->fields['itemtype']][$this->fields['field']]
              = $this->fields['field'];

        if (!isset($this->input['_no_completename'])) {
            $translation = new self();
            $translation->generateCompletename($this->fields, true);
        }
    }


    /**
     * Return the number of translations for a field in a language
     *
     * @param itemtype
     * @param items_id
     * @param field
     * @param language
     *
     * @return integer the number of translations for this field
    **/
    public static function getNumberOfTranslations($itemtype, $items_id, $field, $language)
    {

        return countElementsInTable(
            getTableForItemType(__CLASS__),
            ['itemtype' => $itemtype,
                                     'items_id' => $items_id,
                                     'field'    => $field,
                                     'language' => $language]
        );
    }


    /**
     * Return the number of translations for an item
     *
     * @param item
     *
     * @return integer the number of translations for this item
    **/
    public static function getNumberOfTranslationsForItem($item)
    {

        return countElementsInTable(
            getTableForItemType(__CLASS__),
            ['itemtype' => $item->getType(),
                                     'items_id' => $item->getID(),
                                     'NOT'      => ['field' => 'completename' ]]
        );
    }


    /**
     * Check if a field's translation can be added or updated
     *
     * @param $input          translation's fields
     * @param $add    boolean true if a transaltion must be added, false if updated (true by default)
     *
     * @return true if translation can be added/update, false otherwise
    **/
    public function checkBeforeAddorUpdate($input, $add = true)
    {
        $number = $this->getNumberOfTranslations(
            $input['itemtype'],
            $input['items_id'],
            $input['field'],
            $input['language']
        );
        if ($add) {
            return ($number == 0);
        }
        return ($number > 0);
    }


    /**
     * Generate completename associated with a tree dropdown
     *
     * @param $input array    of user values
     * @param $add   boolean  true if translation is added, false if update (tgrue by default)
     *
     * @return void
    **/
    public function generateCompletename($input, $add = true)
    {
        // Force completename translated : used for the first translation
        $_SESSION['glpi_dropdowntranslations'][$input['itemtype']]['completename'] = 'completename';

        //If there's already a completename for this language, get it's ID, otherwise 0
        $completenames_id = self::getTranslationID(
            $input['items_id'],
            $input['itemtype'],
            'completename',
            $input['language']
        );
        $item = new $input['itemtype']();
        //Completename is used only for tree dropdowns !
        if (
            $item instanceof CommonTreeDropdown
            && isset($input['language'])
        ) {
            $item->getFromDB($input['items_id']);
            $foreignKey = $item->getForeignKeyField();

            //Regenerate completename : look for item's ancestors
            $completename = "";

            //Get ancestors as an array

            if ($item->fields[$foreignKey] != 0) {
                $completename = self::getTranslatedValue(
                    $item->fields[$foreignKey],
                    $input['itemtype'],
                    'completename',
                    $input['language']
                );
            }

            if ($completename != '') {
                $completename .= " > ";
            }
            $completename .= self::getTranslatedValue(
                $item->getID(),
                $input['itemtype'],
                'name',
                $input['language']
            );

            //Add or update completename for this language
            $translation              = new self();
            $tmp                      = [];
            $tmp['items_id']          = $input['items_id'];
            $tmp['itemtype']          = $input['itemtype'];
            $tmp['field']             = 'completename';
            $tmp['value']             = addslashes($completename);
            $tmp['language']          = $input['language'];
            $tmp['_no_completename']  = true;
            if ($completenames_id) {
                $tmp['id']    = $completenames_id;
                if ($completename === $item->fields['completename']) {
                    $translation->delete(['id' => $completenames_id]);
                } else {
                    $translation->update($tmp);
                }
            } else {
                if ($completename != $item->fields['completename']) {
                    $translation->add($tmp);
                }
            }

            $result = $this::getAdapter()->request([
               'SELECT' => ['id'],
               'FROM'   => $item->getTable(),
               'WHERE'  => [
                  $foreignKey => $item->getID()
               ]
            ]);

            while ($tmp = $result->fetchAssociative()) {
                $input2 = $input;
                $input2['items_id'] = $tmp['id'];
                $this->generateCompletename($input2, $add);
            }
        }
    }


    /**
     * Display all translated field for a dropdown
     *
     * @param CommonDropdown $item  A Dropdown item
     *
     * @return true;
    **/
    public static function showTranslations(CommonDropdown $item)
    {
        global $CFG_GLPI;

        $rand    = mt_rand();
        $canedit = $item->can($item->getID(), UPDATE);

        if ($canedit) {
            echo "<div id='viewtranslation" . $item->getType() . $item->getID() . "$rand'></div>\n";

            echo "<script type='text/javascript' >\n";
            echo "function addTranslation" . $item->getType() . $item->getID() . "$rand() {\n";
            $params = ['type'                       => __CLASS__,
                            'parenttype'                 => get_class($item),
                            $item->getForeignKeyField()  => $item->getID(),
                            'id'                         => -1];
            Ajax::updateItemJsCode(
                "viewtranslation" . $item->getType() . $item->getID() . "$rand",
                $CFG_GLPI["root_doc"] . "/ajax/viewsubitem.php",
                $params
            );
            echo "};";
            echo "</script>\n";
            echo "<div class='center'>" .
                 "<a class='vsubmit' href='javascript:addTranslation" .
                   $item->getType() . $item->getID() . "$rand();'>" . __('Add a new translation') .
                 "</a></div><br>";
        }

        $request = self::getAdapter()->request([
           'FROM'   => getTableForItemType(__CLASS__),
           'WHERE'  => [
              'itemtype'  => $item->getType(),
              'items_id'  => $item->getID(),
              'field'     => ['<>', 'completename']
           ],
           'ORDER'  => ['language ASC']
        ]);
        $result = $request->fetchAllAssociative();
        if (count($result)) {
            if ($canedit) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = ['container' => 'mass' . __CLASS__ . $rand];
                Html::showMassiveActions($massiveactionparams);
            }
            echo "<div class='center'>";
            echo "<table class='tab_cadre_fixehov' aria-label='List of translations'><tr class='tab_bg_2'>";
            echo "<th colspan='4'>" . __("List of translations") . "</th></tr><tr>";
            if ($canedit) {
                echo "<th width='10'>";
                echo Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                echo "</th>";
            }
            echo "<th>" . __("Language") . "</th>";
            echo "<th>" . _n('Field', 'Fields', 1) . "</th>";
            echo "<th>" . __("Value") . "</th></tr>";
            foreach ($result as $data) {
                $onhover = '';
                if ($canedit) {
                    $onhover = "style='cursor:pointer'
                           onClick=\"viewEditTranslation" . $data['itemtype'] . $data['id'] . "$rand();\"";
                }
                echo "<tr class='tab_bg_1'>";
                if ($canedit) {
                    echo "<td class='center'>";
                    Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
                    echo "</td>";
                }

                echo "<td $onhover>";
                if ($canedit) {
                    echo "\n<script type='text/javascript' >\n";
                    echo "function viewEditTranslation" . $data['itemtype'] . $data['id'] . "$rand() {\n";
                    $params = ['type'                     => __CLASS__,
                                   'parenttype'                => get_class($item),
                                   $item->getForeignKeyField() => $item->getID(),
                                   'id'                        => $data["id"]];
                    Ajax::updateItemJsCode(
                        "viewtranslation" . $item->getType() . $item->getID() . "$rand",
                        $CFG_GLPI["root_doc"] . "/ajax/viewsubitem.php",
                        $params
                    );
                    echo "};";
                    echo "</script>\n";
                }
                echo Dropdown::getLanguageName($data['language']);
                echo "</td><td $onhover>";
                $searchOption = $item->getSearchOptionByField('field', $data['field']);
                echo $searchOption['name'] . "</td>";
                echo "<td $onhover>" . $data['value'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            if ($canedit) {
                $massiveactionparams['ontop'] = false;
                Html::showMassiveActions($massiveactionparams);
                Html::closeForm();
            }
        } else {
            echo "<table class='tab_cadre_fixe' aria-label='No translation found''><tr class='tab_bg_2'>";
            echo "<th class='b'>" . __("No translation found") . "</th></tr></table>";
        }
        return true;
    }


    /**
     * Display translation form
     *
     * @param integer $ID       field (default -1)
     * @param array   $options
     */
    public function showForm($ID = -1, $options = [])
    {
        global $CFG_GLPI;

        if (isset($options['parent']) && !empty($options['parent'])) {
            $item = $options['parent'];
        }
        if ($ID > 0) {
            $this->check($ID, READ);
        } else {
            $options['itemtype'] = get_class($item);
            $options['items_id'] = $item->getID();

            // Create item
            $this->check(-1, CREATE, $options);
        }
        $rand = mt_rand();
        $this->showFormHeader($options);
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Language') . "</td>";
        echo "<td>";
        echo "<input type='hidden' name='items_id' value='" . $item->getID() . "'>";
        echo "<input type='hidden' name='itemtype' value='" . get_class($item) . "'>";
        if ($ID > 0) {
            echo "<input type='hidden' name='language' value='" . $this->fields['language'] . "'>";
            echo Dropdown::getLanguageName($this->fields['language']);
        } else {
            $rand   = Dropdown::showLanguages(
                "language",
                ['display_none' => false,
                                                    'value'        => $_SESSION['glpilanguage']]
            );
            $params = ['language' => '__VALUE__',
                            'itemtype' => get_class($item),
                            'items_id' => $item->getID()];
            Ajax::updateItemOnSelectEvent(
                "dropdown_language$rand",
                "span_fields",
                $CFG_GLPI["root_doc"] . "/ajax/updateTranslationFields.php",
                $params
            );
        }
        echo "</td><td colspan='2'>&nbsp;</td></tr>";

        echo "<tr class='tab_bg_1'><td>" . _n('Field', 'Fields', 1) . "</td>";
        echo "<td>";
        if ($ID > 0) {
            echo "<input type='hidden' name='field' value='" . $this->fields['field'] . "'>";
            $searchOption = $item->getSearchOptionByField('field', $this->fields['field']);
            echo $searchOption['name'];
        } else {
            echo "<span id='span_fields' name='span_fields'>";
            self::dropdownFields($item, $_SESSION['glpilanguage']);
            echo "</span>";
        }
        echo "</td>";
        echo "<td>" . __('Value') . "</td>";
        echo "<td><input type='text' name='value' value=\"" . $this->fields['value'] . "\" size='50'>";
        echo "</td>";
        echo "</tr>\n";
        $this->showFormButtons($options);
        return true;
    }


    /**
     * Display a dropdown with fields that can be translated for an itemtype
     *
     * @param CommonDBTM $item      a Dropdown item
     * @param string     $language  language to look for translations (default '')
     * @param string     $value     field which must be selected by default (default '')
     *
     * @return integer the dropdown's random identifier
    **/
    public static function dropdownFields(CommonDBTM $item, $language = '', $value = '')
    {
        $options = [];
        foreach (Search::getOptions(get_class($item)) as $field) {
            //Can only translate name, and fields whose datatype is text or string
            if (
                isset($field['field'])
                && ($field['field'] == 'name')
                && ($field['table'] == getTableForItemType(get_class($item)))
                || (isset($field['datatype'])
                    && in_array($field['datatype'], ['text', 'string']))
            ) {
                $options[$field['field']] = $field['name'];
            }
        }

        $used = [];
        if (!empty($options)) {
            $request = $item::getAdapter()->request([
               'SELECT' => 'field',
               'FROM'   => self::getTable(),
               'WHERE'  => [
                  'itemtype'  => $item->getType(),
                  'items_id'  => $item->getID(),
                  'language'  => $language
               ]
            ]);
            $results = $request->fetchAllAssociative();
            if (count($results) > 0) {
                foreach ($results as $data) {
                    $used[$data['field']] = $data['field'];
                }
            }
        }
        //$used = array();
        return Dropdown::showFromArray('field', $options, ['value' => $value,
                                                           'used'  => $used]);
    }


    /**
     * Get translated value for a field in a particular language
     *
     * @param integer $ID        dropdown item's id
     * @param string  $itemtype  dropdown itemtype
     * @param string  $field     the field to look for (default 'name')
     * @param string  $language  get translation for this language
     * @param string  $value     default value for the field (default '')
     *
     * @return string the translated value of the value in the default language
    **/
    public static function getTranslatedValue($ID, $itemtype, $field = 'name', $language = '', $value = '')
    {
        if ($language == '') {
            $language = $_SESSION['glpilanguage'];
        }

        //If dropdown translation is globally off, or if this itemtype cannot be translated,
        //then original value should be returned
        $item = new $itemtype();
        if (
            !$ID
            || !Session::haveTranslations($itemtype, $field)
        ) {
            return $value;
        }
        //ID > 0 : dropdown item might be translated !
        if ($ID > 0) {
            //There's at least one translation for this itemtype
            if (self::hasItemtypeATranslation($itemtype)) {
                $request = self::getAdapter()->request([
                   'SELECT' => ['value'],
                   'FROM'   => self::getTable(),
                   'WHERE'  => [
                      'itemtype'  => $itemtype,
                      'items_id'  => $ID,
                      'field'     => $field,
                      'language'  => $language
                   ]
                ]);
                $results = $request->fetchAllAssociative();
                if (count($results) > 0) {
                    return $results[0]['value'];
                }
            }
            //Get the value coming from the dropdown table
            $request = self::getAdapter()->request([
               'SELECT' => $field,
               'FROM'   => getTableForItemType($itemtype),
               'WHERE'  => ['id' => $ID]
            ]);
            $result = $request->fetchAllAssociative();
            if (count($result)) {
                return $result[0][$field];
            }
        }

        return "";
    }


    /**
     * Get the id of a translated string
     *
     * @param integer $ID          item id
     * @param string  $itemtype    item type
     * @param string  $field       the field for which the translation is needed
     * @param string  $language    the target language
     *
     * @return integer the row id or 0 if not translation found
    **/
    public static function getTranslationID($ID, $itemtype, $field, $language)
    {
        $request = self::getAdapter()->request([
           'SELECT' => ['id'],
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'itemtype'  => $itemtype,
              'items_id'  => $ID,
              'language'  => $language,
              'field'     => $field
           ]
        ]);
        $results = $request->fetchAllAssociative();
        if (count($results)) {
            $current = $results[0];
            return $current['id'];
        }
        return 0;
    }


    /**
     * Check if an item can be translated
     * It be translated if translation if globally on and item is an instance of CommonDropdown
     * or CommonTreeDropdown and if translation is enabled for this class
     *
     * @param CommonGLPI $item the item to check
     *
     * @return boolean true if item can be translated, false otherwise
    **/
    public static function canBeTranslated(CommonGLPI $item)
    {

        return (self::isDropdownTranslationActive()
                && (($item instanceof CommonDropdown)
                    && $item->maybeTranslated()));
    }


    /**
     * Is dropdown item translation functionnality active
     *
     * @return true if active, false if not
    **/
    public static function isDropdownTranslationActive()
    {
        global $CFG_GLPI;

        return $CFG_GLPI['translate_dropdowns'];
    }


    /**
     * Get a translation for a value
     *
     * @param string $itemtype  itemtype
     * @param string $field     field to query
     * @param string $value     value to translate
     *
     * @return string the value translated if a translation is available, or the same value if not
    **/
    public static function getTranslationByName($itemtype, $field, $value)
    {
        $request = self::getAdapter()->request([
           'SELECT' => ['id'],
           'FROM'   => getTableForItemType($itemtype),
           'WHERE'  => [
              $field   => Toolbox::addslashes_deep($value)
           ]
        ]);
        $results = $request->fetchAllAssociative();
        if (count($results) > 0) {
            $current = $results[0];
            return self::getTranslatedValue(
                $current['id'],
                $itemtype,
                $field,
                $_SESSION['glpilanguage'],
                $value
            );
        }
        return $value;
    }

    /**
     * Get translations for an item
     *
     * @param string  $itemtype  itemtype
     * @param integer $items_id  item ID
     * @param string  $field     the field for which the translation is needed
     *
     * @return string the value translated if a translation is available, or the same value if not
    **/
    public static function getTranslationsForAnItem($itemtype, $items_id, $field)
    {
        $request = self::getAdapter()->request([
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'itemtype'  => $itemtype,
              'items_id'  => $items_id,
              'field'     => $field
           ]
        ]);
        $data = [];
        while ($tmp = $request->fetchAssociative()) {
            $data[$tmp['id']] = $tmp;
        }

        return $data;
    }
    /**
     * Regenerate all completename translations for an item
     *
     * @param string  $itemtype    itemtype
     * @param integer $items_id    item ID
     *
     * @return string the value translated if a translation is available, or the same value if not
    **/
    public static function regenerateAllCompletenameTranslationsFor($itemtype, $items_id)
    {
        foreach (self::getTranslationsForAnItem($itemtype, $items_id, 'completename') as $data) {
            $dt = new DropdownTranslation();
            $dt->generateCompletename($data, false);
        }
    }

    /**
     * Check if there's at least one translation for this itemtype
     *
     * @param string $itemtype itemtype to check
     *
     * @return boolean true if there's at least one translation, otherwise false
    **/
    public static function hasItemtypeATranslation($itemtype)
    {
        return countElementsInTable(self::getTable(), ['itemtype' => $itemtype ]);
    }


    /**
     * Get available translations for a language
     *
     * @param string $language language
     *
     * @return array of table / field translated item
    **/
    public static function getAvailableTranslations($language)
    {
        $tab = [];
        if (self::isDropdownTranslationActive()) {
            $request = self::getAdapter()->request([
               'SELECT'          => [
                  'itemtype',
                  'field'
               ],
               'DISTINCT'        => true,
               'FROM'            => self::getTable(),
               'WHERE'           => ['language' => $language]
            ]);
            while ($data = $request->fetchAssociative()) {
                $tab[$data['itemtype']][$data['field']] = $data['field'];
            }
        }
        return $tab;
    }
}
