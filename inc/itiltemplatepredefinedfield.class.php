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
 * ITILTemplatePredefinedField Class
 *
 * Predefined fields for ITIL template class
 *
 * @since 9.5.0
**/
abstract class ITILTemplatePredefinedField extends ITILTemplateField
{
    public static function getTypeName($nb = 0)
    {
        return _n('Predefined field', 'Predefined fields', $nb);
    }


    protected function computeFriendlyName()
    {

        $tt_class = static::$itemtype;
        $tt     = new $tt_class();
        $fields = $tt->getAllowedFieldsNames(true, true);

        if (isset($fields[$this->fields["num"]])) {
            return $fields[$this->fields["num"]];
        }
        return '';
    }


    public function prepareInputForAdd($input)
    {
        // Normalize document dropdown submission (Document::dropdown uses peer_documents_id)
        if (!isset($input['value']) && isset($input['peer_documents_id']) && $input['peer_documents_id'] !== '') {
            $input['value'] = (int)$input['peer_documents_id'];
            $input['field'] = 'documents_id';
            unset($input['peer_documents_id']);
            unset($input['_rubdoc']);
        }

        // Use massiveaction system to manage add system.
        // Need to update data : value not set but
        if (!isset($input['value'])) {
            if (isset($input['field']) && isset($input[$input['field']])) {
                $input['value'] = $input[$input['field']];
                unset($input[$input['field']]);
                unset($input['field']);
            }
        }

        return parent::prepareInputForAdd($input);
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
                  static::$items_id => $this->fields[static::$items_id],
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


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        self::showForITILTemplate($item, $withtemplate);
        return true;
    }


    /**
     * Get predefined fields for a template
     *
     * @since 0.83
     *
     * @param integer $ID                   the template ID
     * @param boolean $withtypeandcategory  with type and category (false by default)
     *
     * @return array of predefined fields
    **/
    public function getPredefinedFields($ID, $withtypeandcategory = false)
    {
        global $DB;

        $iterator = $DB->request([
           'FROM'   => $this->getTable(),
           'WHERE'  => [static::$items_id => $ID],
           'ORDER'  => 'id'
        ]);

        $tt_class       = static::$itemtype;
        $tt             = new $tt_class();
        $allowed_fields = $tt->getAllowedFields($withtypeandcategory, true);
        $fields         = [];
        $multiple       = self::getMultiplePredefinedValues();
        while ($rule = $iterator->next()) {
            if (isset($allowed_fields[$rule['num']])) {
                if (in_array($rule['num'], $multiple)) {
                    if ($allowed_fields[$rule['num']] == 'items_id') {
                        $item_itemtype = explode("_", (string) $rule['value']);
                        $fields[$allowed_fields[$rule['num']]][$item_itemtype[0]][$item_itemtype[1]] = $item_itemtype[1];
                    } else {
                        $fields[$allowed_fields[$rule['num']]][] = $rule['value'];
                    }
                } else {
                    $fields[$allowed_fields[$rule['num']]] = $rule['value'];
                }
            }
        }
        return $fields;
    }


    /**
     * @since 0.85
    **/
    public static function getMultiplePredefinedValues()
    {

        $itil_class = static::$itiltype;
        $itil_object = new $itil_class();

        $itemstable = null;
        switch ($itil_class) {
            case 'Change':
                $itemstable = 'glpi_changes_items';
                break;
            case 'Problem':
                $itemstable = 'glpi_items_problems';
                break;
            case 'Ticket':
                $itemstable = 'glpi_items_tickets';
                break;
            default:
                throw new \RuntimeException('Unknown ITIL type ' . $itil_class);
        }

        $fields = [
           $itil_object->getSearchOptionIDByField('field', 'name', 'glpi_documents'),
           $itil_object->getSearchOptionIDByField('field', 'items_id', $itemstable),
           $itil_object->getSearchOptionIDByField('field', 'name', 'glpi_tasktemplates')
        ];

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
           -2 => -2, // validation request
        ];
    }


    /**
     * Print the predefined fields
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
        global $DB, $CFG_GLPI;

        $ID = $tt->fields['id'];

        if (!$tt->getFromDB($ID) || !$tt->can($ID, READ)) {
            return false;
        }

        $canedit       = $tt->canEdit($ID);

        $fields        = $tt->getAllowedFieldsNames(true, true);
        $fields        = array_diff_key($fields, self::getExcludedFields());

        $itil_class    = static::$itiltype;
        $searchOption  = Search::getOptions($itil_class);
        $itil_object   = new $itil_class();
        $rand          = mt_rand();

        $iterator = $DB->request([
           'FROM'   => static::getTable(),
           'WHERE'  => [static::$items_id => $ID],
           'ORDER'  => 'id'
        ]);

        $display_options = [
           'relative_dates' => true,
           'comments'       => true,
           'html'           => true
        ];

        $predeffields = [];
        $used         = [];
        $numrows      = count($iterator);
        while ($data = $iterator->next()) {
            $predeffields[$data['id']] = $data;
            $used[$data['num']] = $data['num'];
        }

        if ($canedit) {
            echo "<div class='firstbloc'>";
            echo "<form aria-label='Predefined Field' name='changeproblem_form$rand' id='changeproblem_form$rand' method='post'
               action='" . static::getFormURL() . "'>";

            echo "<div class='form-section mb-3'>";
            echo "<h2 class='form-section-header'>" . __('Add a predefined field') . "</h2>";
            echo "<div class='form-section-content'>";
            echo "<div class='row g-3 align-items-start'>";
            echo "<input type='hidden' name='" . static::$items_id . "' value='$ID'>";

            $display_fields[-1] = Dropdown::EMPTY_VALUE;
            $display_fields    += $fields;

            // Unset multiple items
            $multiple = self::getMultiplePredefinedValues();
            foreach ($multiple as $val) {
                if (isset($used[$val])) {
                    unset($used[$val]);
                }
            }

            echo "<div class='col-md-6 col-sm-12'>";
            $rand_dp  = Dropdown::showFromArray('num', $display_fields, [
               'used'  => $used,
                    'toadd' => null
            ]);
            echo "</div>";

            echo "<div class='col-md-6 col-sm-12'>";
            $paramsmassaction = [
               'id_field'   => '__VALUE__',
               'itemtype'   => static::$itiltype,
               'inline'     => true,
               'submitname' => _sx('button', 'Add'),
               'options'    => [
                  'relative_dates'     => 1,
                  'with_time'          => 1,
                  'with_days'          => 0,
                  'with_specific_date' => 0,
                  'itemlink_as_string' => 1,
                  'entity'             => $tt->getEntityID()
               ]
            ];

            Ajax::updateItemOnSelectEvent(
                "dropdown_num" . $rand_dp,
                "show_massiveaction_field",
                $CFG_GLPI["root_doc"] . "/ajax/dropdownMassiveActionField.php",
                $paramsmassaction
            );
            echo "<span id='show_massiveaction_field' class='d-block'>&nbsp;</span>\n";
            echo "</div>";

            echo "</div>";
            echo "</div>";
            echo "</div>";
            Html::closeForm();
            echo "</div>";
        }

        echo "<div class='spaced'>";
        $table_fields = [
           'name'           => __('Name'),
           'display_value'  => __('Value')
        ];

        $table_values = [];
        $massive_action_values = [];

        foreach ($predeffields as $data) {
            if (!isset($fields[$data['num']])) {
                // could happen when itemtype removed and items_id present
                continue;
            }

            $display_datas = [
               $searchOption[$data['num']]['field'] => $data['value']
            ];

            $row = [
               'name'          => $fields[$data['num']],
               'display_value' => $itil_object->getValueToDisplay(
                   $searchOption[$data['num']],
                   $display_datas,
                   $display_options
               )
            ];

            if ($canedit) {
                $selection_value = sprintf('item[%s][%d]', static::class, $data['id']);
                $row['value'] = $selection_value; // used by table twig for massive actions
                $massive_action_values[$data['id']] = $selection_value;
            }

            $table_values[$data['id']] = $row;
        }

        $table_id = 'TablePredefinedFields' . $rand;

        if ($canedit && $numrows) {
            $massiveactionparams = [
               'num_displayed' => min($_SESSION['glpilist_limit'], $numrows),
               'container'     => $table_id,
               'display_arrow' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }

        $table_params = [
           'id'     => $table_id,
           'fields' => $table_fields,
           'values' => $table_values,
        ];

        if ($canedit && $numrows) {
            $table_params['massive_action'] = $massive_action_values;
        } else {
            $table_params['noToolBar'] = true;
        }

        if (function_exists('renderTwigTemplate')) {
            renderTwigTemplate('table.twig', $table_params);
        } else {
            // Fallback to legacy table if twig rendering is unavailable
            echo "<table class='tab_cadre_fixehov' aria-label='ITIL Template'>";
            echo "<tr class='noHover'><th colspan='3'>";
            echo self::getTypeName($numrows);
            echo "</th></tr>";

            if (count($table_values)) {
                echo "<tr><th>" . __('Name') . "</th><th>" . __('Value') . "</th></tr>";
                foreach ($table_values as $row) {
                    echo "<tr class='tab_bg_2'>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td>" . $row['value'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><th colspan='3'>" . __('No item found') . "</th></tr>";
            }

            echo "</table>";
        }

        echo "</div>";
    }
}
