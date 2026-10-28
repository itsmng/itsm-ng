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

use Glpi\Event;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}


/**
 * ReminderTranslation Class
 *
 * @since 9.5
**/
class ReminderTranslation extends CommonDBChild
{
    public static $itemtype = 'Reminder';
    public static $items_id = 'reminders_id';
    public $dohistory       = true;
    public static $logs_for_parent = false;

    public static $rightname       = 'reminder_public';



    public static function getTypeName($nb = 0)
    {
        return _n('Translation', 'Translations', $nb);
    }


    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    /**
     * @param \CommonGLPI $item
     * @param int         $withtemplate
     *
     * @return array|string
     * @see CommonGLPI::getTabNameForItem()
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (
            self::canBeTranslated($item)
            && Session::getCurrentInterface() != "helpdesk"
        ) {
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
     * @param $tabnum (default 1)
     * @param $withtemplate (default 0)
     **
     *
     * @return bool
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if (
            $item->getType() == "Reminder"
            && self::canBeTranslated($item)
        ) {
            self::showTranslations($item);
        }
        return true;
    }


    /**
     * Display all translated field for an Reminder
     *
     * @param $item a Reminder item
     *
     * @return true;
    **/
    public static function showTranslations(Reminder $item)
    {
        $canedit = $item->can($item->getID(), UPDATE);
        $id = 'ReminderTranslation_' . $item->getID();
        if ($canedit) {
            (new self())->showForm(-1, ['parent' => $item]);
        }
        $obj = new self();
        $found = $obj->find(['reminders_id' => $item->getID()], 'language ASC');
        Session::initNavigateListItems(self::class, __('Entry translations list'));
        $values = $selection = [];
        foreach ($found as $data) {
            $name = htmlspecialchars($data['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
            if ($canedit) {
                $name = "<a href='" . static::getFormURLWithID($data['id']) . "'>$name</a>";
                $selection[$data['id']] = 'item[' . self::class . '][' . $data['id'] . ']';
            }
            if (!empty($data['text'])) {
                ob_start();
                Html::showToolTip(Toolbox::unclean_html_cross_side_scripting_deep($data['text']));
                $name .= ' ' . ob_get_clean();
            }
            $values[$data['id']] = ['language' => Dropdown::getLanguageName($data['language']), 'name' => $name];
            Session::addToNavigateListItems(self::class, $data['id']);
        }
        if ($canedit && $values) {
            Html::showMassiveActions(['container' => $id, 'display_arrow' => false,
                'num_displayed' => count($values)]);
        }
        renderTwigTemplate('table.twig', [
            'id' => $id, 'state_key' => $id,
            'fields' => ['language' => __('Language'), 'name' => __('Subject')],
            'values' => $values, 'pageSize' => max(1, (int) ($_SESSION['glpilist_limit'] ?? 15)),
        ] + ($canedit ? ['massive_action' => $selection] : []));
        return true;
    }


    /**
     * Display translation form
     *
     * @param integer $ID
     * @param array   $options
     */
    public function showForm($ID = -1, $options = [])
    {

        if ($ID > 0) {
            $this->check($ID, READ);
        } else {
            // Create item
            $item                = $options['parent'];
            $options['itemtype'] = get_class($item);
            $options['reminders_id'] = $item->getID();
            $this->check(-1, CREATE, $options);
        }
        ob_start();
        if ($ID > 0) {
            echo Dropdown::getLanguageName($this->fields['language']);
        } else {
            Dropdown::showLanguages('language', [
                'display_none' => false, 'value' => $_SESSION['glpilanguage'],
                'used' => self::getAlreadyTranslatedForItem($item),
            ]);
        }
        $language = ob_get_clean();
        ob_start();
        Html::textarea(['name' => 'text', 'value' => $this->fields['text'] ?? '',
            'enable_richtext' => true, 'enable_fileupload' => false]);
        $content = ob_get_clean();
        $buttons = [];
        if ($ID <= 0 || $this->can($ID, UPDATE)) {
            $buttons[] = ['type' => 'submit', 'name' => $ID > 0 ? 'update' : 'add',
                'value' => $ID > 0 ? _x('button', 'Update') : _x('button', 'Add'),
                'class' => 'btn btn-primary', 'icon' => $ID > 0 ? 'fas fa-save' : 'fas fa-plus'];
        }
        if ($ID > 0 && $this->can($ID, PURGE)) {
            $buttons[] = ['type' => 'submit', 'name' => 'purge',
                'value' => _x('button', 'Delete permanently'), 'class' => 'btn btn-danger'];
        }
        renderTwigForm([
            'action' => static::getFormURL(), 'buttons' => $buttons ?: [[]],
            'content' => [($ID > 0 ? static::getTypeName(1) : __('Add a new translation')) => [
                'visible' => true, 'inputs' => [
                    ['type' => 'hidden', 'name' => 'id', 'value' => $ID],
                    ['type' => 'hidden', 'name' => 'users_id', 'value' => Session::getLoginUserID()],
                    ['type' => 'hidden', 'name' => 'reminders_id', 'value' => $this->fields['reminders_id']],
                    __('Language') => ['content' => $language],
                    __('Name') => ['type' => 'text', 'name' => 'name', 'value' => $this->fields['name'] ?? '',
                        'col_lg' => 12, 'col_md' => 12],
                    __('Description') => ['content' => $content, 'col_lg' => 12, 'col_md' => 12],
                ],
            ]],
        ]);

        return true;
    }


    /**
     * Get a translation for a value
     *
     * @param Reminder $item   item to translate
     * @param string       $field  field to return (default 'name')
     *
     * @return string  the field translated if a translation is available, or the original field if not
    **/
    public static function getTranslatedValue(Reminder $item, $field = "name")
    {
        $obj   = new self();
        $found = $obj->find([
           'reminders_id'   => $item->getID(),
           'language'           => $_SESSION['glpilanguage']
        ]);

        if (
            (count($found) > 0)
            && in_array($field, ['name', 'text'])
        ) {
            $first = array_shift($found);
            return $first[$field];
        }
        return $item->fields[$field];
    }


    /**
     * Is kb item translation functionality active
     *
     * @return boolean
    **/
    public static function isReminderTranslationActive()
    {
        global $CFG_GLPI;

        return $CFG_GLPI['translate_reminders'];
    }


    /**
     * Check if an item can be translated
     * It be translated if translation if globally on and item is an instance of CommonDropdown
     * or CommonTreeDropdown and if translation is enabled for this class
     *
     * @param item the item to check
     *
     * @return true if item can be translated, false otherwise
    **/
    public static function canBeTranslated(CommonGLPI $item)
    {

        return (self::isReminderTranslationActive()
                && $item instanceof Reminder);
    }


    /**
     * Return the number of translations for an item
     *
     * @param Reminder $item
     *
     * @return integer  the number of translations for this item
    **/
    public static function getNumberOfTranslationsForItem($item)
    {

        return countElementsInTable(
            getTableForItemType(__CLASS__),
            ['reminders_id' => $item->getID()]
        );
    }


    /**
     * Get already translated languages for item
     *
     * @param item
     *
     * @return array of already translated languages
    **/
    public static function getAlreadyTranslatedForItem($item)
    {
        global $DB;

        $tab = [];

        $iterator = $DB->request([
           'FROM'   => getTableForItemType(__CLASS__),
           'WHERE'  => ['reminders_id' => $item->getID()]
        ]);

        while ($data = $iterator->next()) {
            $tab[$data['language']] = $data['language'];
        }
        return $tab;
    }
}
