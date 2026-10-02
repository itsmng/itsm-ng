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
 * ITILTemplateHiddenField Class
 *
 * Predefined fields for ITIL template class
 *
 * @since 9.5.0
**/
abstract class ITILTemplateHiddenField extends ITILTemplateField
{
    public static function getTypeName($nb = 0)
    {
        return _n('Hidden field', 'Hidden fields', $nb);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        // can exists for template
        if (
            $item instanceof ITILTemplate
            && Session::haveRight("itiltemplate", READ)
        ) {
            $nb = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
                $nb = countElementsInTable(
                    $this->getTable(),
                    [static::$items_id => $item->getID()]
                );
            }
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
        }
        return '';
    }


    public function post_purgeItem()
    {
        global $DB;

        parent::post_purgeItem();

        $itil_class = static::$itiltype;
        $itil_object = new $itil_class();
        $itemtype_id = $itil_object->getSearchOptionIDByField('field', 'itemtype', $itil_object->getTable());
        $items_id_id = $itil_object->getSearchOptionIDByField('field', 'items_id', $itil_object->getTable());

        // Try to delete itemtype -> delete items_id
        if ($this->fields['num'] == $itemtype_id) {
            $iterator = $DB->request([
               'SELECT' => 'id',
               'FROM'   => $this->getTable(),
               'WHERE'  => [
                  static::$items_id => $this->fields[static::$itiltype],
                  'num'             => $items_id_id
               ]
            ]);
            if (count($iterator)) {
                $result = $iterator->next();
                $a = new static();
                $a->delete(['id' => $result['id']]);
            }
        }
    }


    /**
     * Get hidden fields for a template
     *
     * @since 0.83
     *
     * @param $ID                    integer  the template ID
     * @param $withtypeandcategory   boolean  with type and category (false by default)
     *
     * @return array of hidden fields
    **/
    public function getHiddenFields($ID, $withtypeandcategory = false)
    {
        global $DB;

        $iterator = $DB->request([
           'FROM'   => $this->getTable(),
           'WHERE'  => [static::$items_id => $ID],
           'ORDER'  => 'id'
        ]);

        $tt_class       = static::$itemtype;
        $tt             = new $tt_class();
        $allowed_fields = $tt->getAllowedFields($withtypeandcategory);
        $fields         = [];

        while ($rule = $iterator->next()) {
            if (isset($allowed_fields[$rule['num']])) {
                $fields[$allowed_fields[$rule['num']]] = $rule['num'];
            }
        }
        return $fields;
    }


    /**
     * Return fields who doesn't need to be used for this part of template
     *
     * @since 9.2
     *
     * @return array the excluded fields (keys and values are equals)
     */
    public static function getExcludedFields()
    {
        return [
           175 => 175, // ticket's tasks (template)
        ];
    }


    /**
     * Print the hidden fields
     *
     * @since 0.83
     *
     * @param ITILTemplate $tt            ITIL Template
     * @param boolean      $withtemplate  Template or basic item (default 0)
     *
     * @return void
    **/
    public static function showForITILTemplate(ITILTemplate $tt, $withtemplate = 0)
    {
        global $DB;

        $ID = $tt->fields['id'];

        if (!$tt->getFromDB($ID) || !$tt->can($ID, READ)) {
            return false;
        }
        $canedit = $tt->canEdit($ID);
        $fields  = $tt->getAllowedFieldsNames(false);
        $fields  = array_diff_key($fields, self::getExcludedFields());
        $rand    = mt_rand();

        $iterator = $DB->request([
           'FROM'   => static::getTable(),
           'WHERE'  => [static::$items_id => $ID]
        ]);

        $hiddenfields = [];
        $used         = [];
        while ($data = $iterator->next()) {
            $hiddenfields[$data['id']] = $data;
            $used[$data['num']]        = $data['num'];
        }

        if ($canedit) {
            static::showAddFieldForm($tt, __('Add a hidden field'), $fields, $used);
        }

        $rows = [];
        foreach ($hiddenfields as $data) {
            if (isset($fields[$data['num']])) {
                $rows[$data['id']] = ['name' => $fields[$data['num']]];
            }
        }
        static::showFieldsTable(['name' => __('Name')], $rows, $canedit, 'TableHiddenFields' . $rand);
    }
}
