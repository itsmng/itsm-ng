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

use function PHPSTORM_META\map;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Fieldblacklist Class
**/
class Fieldblacklist extends CommonDropdown
{
    public static $rightname         = 'config';

    public $can_be_translated = false;


    public static function getTypeName($nb = 0)
    {
        return _n('Ignored value for the unicity', 'Ignored values for the unicity', $nb);
    }


    public static function canCreate()
    {
        return static::canUpdate();
    }

    /**
     * @since 0.85
    **/
    public static function canPurge()
    {
        return static::canUpdate();
    }



    public function getAdditionalFields()
    {
        global $CFG_GLPI;

        $loadFields = <<<JS
         $.ajax({
            url: "{$CFG_GLPI['root_doc']}/ajax/dropdownFieldsBlacklist.php",
            type: 'POST',
            data: {
               itemtype: $('#ItemTypeDropdown').val(),
               id: '{$this->getID()}'
            },
            success: function(data) {
               const jsonData = JSON.parse(data);
               const currentvalue = $('#FieldDropdown').val();
               $('#FieldDropdown').empty();
               for (const [key, value] of Object.entries(jsonData)) {
                  $('#FieldDropdown').append($('<option>', {
                     value: key,
                     text: value
                  }));
               }
               if (Object.keys(jsonData).length > 0) {
                  $('#FieldDropdown').removeAttr('disabled');
                  $('#ValueInput').removeAttr('disabled');
               } else {
                  $('#FieldDropdown').attr('disabled', 'disabled');
                  $('#ValueInput').attr('disabled', 'disabled');
               }
               setTimeout(() => {
                  $('#FieldDropdown').val(currentvalue);
               }, 400);
            }
         });
      JS;

        $loadValueInput = <<<JS
         $.ajax({
            url: "{$CFG_GLPI['root_doc']}/ajax/dropdownValuesBlacklist.php",
            type: 'POST',
            data: {
               itemtype: $('#ItemTypeDropdown').val(),
               id_field: $('#FieldDropdown').val() ?? '{$this->fields['field']}',
               id: '{$this->getID()}'
            },
            success: function(data) {
               console.table({
               itemtype: $('#ItemTypeDropdown').val(),
               id_field: $('#FieldDropdown').val(),
               id: '{$this->getID()}'
            });
               const htmlObject = document.createElement('div');
               htmlObject.innerHTML = data.trim();
               $('#ValueInput').html(htmlObject);
            }
         });
      JS;
        return [
           _n('Type', 'Types', 1) => [
              'name'  => 'itemtype',
              'type'  => 'select',
              'id'    => 'ItemTypeDropdown',
              'values' => array_merge([ Dropdown::EMPTY_VALUE ], array_combine($CFG_GLPI['unicity_types'], $CFG_GLPI['unicity_types'])),
              'value' => isset($this->fields['itemtype']) ? $this->fields['itemtype'] : '',
              isset($this->fields['itemtype']) ? 'disabled' : '' => '',
              'hooks' => [
                 'change' => $loadFields
              ]
           ],
           _n('Field', 'Fields', 1) => [
              'name'  => 'field',
              'type'  => 'select',
              'id' => 'FieldDropdown',
              'values' => $this->selectCriterias(),
              'value' => isset($this->fields['field']) ? $this->fields['field'] : '',
              !isset($this->fields['itemtype']) ? 'disabled' : '',
              'hooks' => [
                 'change' => $loadValueInput,
              ],
           ],
           __('Value') => [
              'content' => '<div id="ValueInput"></div>',
              'init' => $loadValueInput,
           ]
        ];
    }


    /**
     * Get search function for the class
     *
     * @return array of search option
    **/
    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '4',
           'table'              => $this->getTable(),
           'field'              => 'itemtype',
           'name'               => _n('Type', 'Types', 1),
           'massiveaction'      => false,
           'datatype'           => 'itemtypename',
           'forcegroupby'       => true
        ];

        $tab[] = [
           'id'                 => '6',
           'table'              => $this->getTable(),
           'field'              => 'field',
           'name'               => _n('Field', 'Fields', 1),
           'massiveaction'      => false,
           'datatype'           => 'specific',
           'additionalfields'   => [
              '0'                  => 'itemtype'
           ]
        ];

        $tab[] = [
           'id'                 => '7',
           'table'              => $this->getTable(),
           'field'              => 'value',
           'name'               => __('Value'),
           'datatype'           => 'specific',
           'additionalfields'   => [
              '0'                  => 'itemtype',
              '1'                  => 'field'
           ],
           'massiveaction'      => false
        ];

        return $tab;
    }


    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'field':
                if (isset($values['itemtype']) && !empty($values['itemtype'])) {
                    $target       = getItemForItemtype($values['itemtype']);
                    $searchOption = $target->getSearchOptionByField('field', $values[$field]);
                    return $searchOption['name'];
                }
                break;

            case 'value':
                if (isset($values['itemtype']) && !empty($values['itemtype'])) {
                    $target = getItemForItemtype($values['itemtype']);
                    if (isset($values['field']) && !empty($values['field'])) {
                        $searchOption = $target->getSearchOptionByField('field', $values['field']);
                        return $target->getValueToDisplay($searchOption, $values[$field]);
                    }
                }
                break;
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }


    /**
     * @since 0.84
     *
     * @param $field
     * @param $name               (default '')
     * @param $values             (default '')
     * @param $options      array
    **/
    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;
        switch ($field) {
            case 'field':
                if (
                    isset($values['itemtype'])
                    && !empty($values['itemtype'])
                ) {
                    $options['value'] = $values[$field];
                    $options['name']  = $name;
                    return self::dropdownField($values['itemtype'], $options);
                }
                break;

            case 'value':
                if (
                    isset($values['itemtype'])
                    && !empty($values['itemtype'])
                ) {
                    if ($item = getItemForItemtype($values['itemtype'])) {
                        if (isset($values['field']) && !empty($values['field'])) {
                            $searchOption = $item->getSearchOptionByField('field', $values['field']);
                            return $item->getValueToSelect($searchOption, $name, $values[$field], $options);
                        }
                    }
                }
                break;
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }


    public function prepareInputForAdd($input)
    {

        $input = parent::prepareInputForAdd($input);
        return $input;
    }


    public function prepareInputForUpdate($input)
    {

        $input = parent::prepareInputForUpdate($input);
        return $input;
    }


    /**
     * Display specific fields for FieldUnicity
     *
     * @param integer $ID     Unused
     * @param array   $field  Array of fields
    **/
    public function displaySpecificTypeField($ID, $field = [])
    {

        switch ($field['type']) {
            case 'blacklist_itemtype':
                $this->showItemtype();
                break;

            case 'blacklist_field':
                $this->selectCriterias();
                break;

            case 'blacklist_value':
                $this->selectValues();
                break;
        }
    }


    /**
     * Display a dropdown which contains all the available itemtypes
     *
     * @return void
    **/
    public function showItemtype()
    {
        global $CFG_GLPI;

        if ($this->fields['id'] > 0) {
            if ($item = getItemForItemtype($this->fields['itemtype'])) {
                echo $item->getTypeName(1);
            }
            echo "<input type='hidden' name='itemtype' value='" . $this->fields['itemtype'] . "'>";
        } else {
            //Add criteria : display dropdown
            foreach ($CFG_GLPI['unicity_types'] as $itemtype) {
                if ($item = getItemForItemtype($itemtype)) {
                    if ($item->can(-1, READ)) {
                        $options[$itemtype] = $item->getTypeName(1);
                    }
                }
            }
            asort($options);
            $rand = Dropdown::showFromArray(
                'itemtype',
                $options,
                ['value'               => $this->fields['value'],
                                                  'display_emptychoice' => true]
            );

            $params = ['itemtype' => '__VALUE__',
                            'id'       => $this->fields['id']];
            Ajax::updateItemOnSelectEvent(
                "dropdown_itemtype$rand",
                "span_fields",
                $CFG_GLPI["root_doc"] . "/ajax/dropdownFieldsBlacklist.php",
                $params
            );
        }
    }


    public function selectCriterias()
    {
        global $CFG_GLPI, $DB;

        if (!isset($this->fields['itemtype']) || !$this->fields['itemtype']) {
            echo "</span>";
            return;
        }

        if (!isset($this->fields['entities_id'])) {
            $this->fields['entities_id'] = $_SESSION['glpiactive_entity'];
        }

        if ($target = getItemForItemtype($this->fields['itemtype'])) {
            $criteria = [];
            foreach ($DB->listFields($target->getTable()) as $field) {
                $searchOption = $target->getSearchOptionByField('field', $field['Field']);

                if (
                    !empty($searchOption)
                      && !in_array($field['Type'], $target->getUnallowedFieldsForUnicity())
                      && !in_array($field['Field'], $target->getUnallowedFieldsForUnicity())
                ) {
                    $criteria[$field['Field']] = $searchOption['name'];
                }
            }
            return $criteria;
        }
    }


    /** Dropdown fields for a specific itemtype
     *
     * @since 0.84
     *
     * @param string $itemtype
     * @param array  $options
    **/
    public static function dropdownField($itemtype, $options = [])
    {
        global $DB;

        $p['name']    = 'field';
        $p['display'] = true;
        $p['value']   = '';

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $p[$key] = $val;
            }
        }

        if ($target = getItemForItemtype($itemtype)) {
            $criteria = [];
            foreach ($DB->listFields($target->getTable()) as $field) {
                $searchOption = $target->getSearchOptionByField('field', $field['Field']);

                if (
                    !empty($searchOption)
                    && !in_array($field['Type'], $target->getUnallowedFieldsForUnicity())
                    && !in_array($field['Field'], $target->getUnallowedFieldsForUnicity())
                ) {
                    $criteria[$field['Field']] = $searchOption['name'];
                }
            }
            return Dropdown::showFromArray($p['name'], $criteria, $p);
        }
        return false;
    }


    /**
     * @param $field  (default '')
    **/
    public function selectValues($field = '')
    {
        if ($field == '') {
            $field = $this->fields['field'];
        }
        if ($this->fields['itemtype'] != '') {
            if ($item = getItemForItemtype($this->fields['itemtype'])) {
                $searchOption = $item->getSearchOptionByField('field', $field);
                $options      = [];
                if (isset($this->fields['entity'])) {
                    $options['entity']      = $this->fields['entity'];
                    $options['entity_sons'] = $this->fields['is_recursive'];
                }
                echo $item->getValueToSelect($searchOption, 'value', $this->fields['value'], $options);
            }
        }
    }


    /**
     * Check if a field & value are blacklisted or not
     *
     * @param itemtype      itemtype of the blacklisted field
     * @param entities_id   the entity in which the field must be saved
     * @param field         the field to check
     * @param value         the field's value
     *
     * @return true is value if blacklisted, false otherwise
    **/
    public static function isFieldBlacklisted($itemtype, $entities_id, $field, $value)
    {
        global $DB;

        $result = self::getAdapter()->request([
           'COUNT'  => 'cpt',
           'FROM'   => 'glpi_fieldblacklists',
           'WHERE'  => [
              'itemtype'  => $itemtype,
              'field'     => $field,
              'value'     => $value
           ] + getEntitiesRestrictCriteria('glpi_fieldblacklists', 'entities_id', $entities_id, true)
        ])->fetchAssociative();
        return $result['cpt'] > 0;
    }
}
