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

/// Class Manufacturer
/// @todo study if we should integrate getHTMLTableHeader and getHTMLTableCellsForItem ...
class Manufacturer extends CommonDropdown
{
    public $can_be_translated = false;


    public static function getTypeName($nb = 0)
    {
        return _n('Manufacturer', 'Manufacturers', $nb);
    }


    public function displaySpecificTypeField($ID, $field = [])
    {

        switch ($field['type']) {
            case 'registeredIDChooser':
                RegisteredID::showChildsForItemForm($this, '_registeredID');
                break;
        }
    }


    public function getAdditionalFields()
    {

        return [
           RegisteredID::getTypeName(Session::getPluralNumber()) => [
              'name'  => 'none',
              'type'  => 'multiSelect',
              'inputs' => [
                 [
                    'name' => 'current_registeredID_type',
                    'type' => 'select',
                    'values' => array_merge([ Dropdown::EMPTY_VALUE ], RegisteredID::getRegisteredIDTypes()),
                 ],
                 [
                    'name' => 'current_registeredID',
                    'type' => 'text',
                    'size' => 30,
                 ],
              ],
              'getInputAdd' => <<<JS
               function () {
                  if (!$('input[name="current_registeredID"]').val()) {
                     return;
                  }
                  var values = {
                     _registeredID_type: $('select[name="current_registeredID_type"]').val(),
                     _registeredID: $('input[name="current_registeredID').val()
                  };
                  var title = $('select[name="current_registeredID_type"] option:selected').text() + ' ' + $('input[name="current_registeredID"').val();
                  return {values, title};
               }
            JS,
              'values' => getOptionsWithNameForItem(
                  'RegisteredID',
                  ['itemtype' => $this::class, 'items_id' => $this->getID()],
                  ['_registeredID_type' => 'device_type', '_registeredID' => 'name']
              ),
              'col_lg' => 12,
              'col_md' => 12,
           ],
        ];
    }


    /**
     * @since 0.85
    **/
    public function post_workOnItem()
    {

        if (
            (isset($this->input['_registeredID']))
            && (is_array($this->input['_registeredID']))
        ) {
            $input = ['itemtype' => $this->getType(),
                           'items_id' => $this->getID()];

            foreach ($this->input['_registeredID'] as $id => $registered_id) {
                $id_object     = new RegisteredID();
                $input['name'] = $registered_id;

                if (isset($this->input['_registeredID_type'][$id])) {
                    $input['device_type'] = $this->input['_registeredID_type'][$id];
                } else {
                    $input['device_type'] = '';
                }
                //$input['device_type'] = '';
                if ($id < 0) {
                    if (!empty($registered_id)) {
                        $id_object->add($input);
                    }
                } else {
                    if (!empty($registered_id)) {
                        $input['id'] = $id;
                        $id_object->update($input);
                        unset($input['id']);
                    } else {
                        $id_object->delete(['id' => $id]);
                    }
                }
            }
            unset($this->input['_registeredID']);
        }
    }


    public function post_addItem()
    {

        $this->post_workOnItem();
        parent::post_addItem();
    }


    public function post_updateItem($history = 1)
    {

        $this->post_workOnItem();
        parent::post_updateItem($history);
    }


    /**
     * @param null|string $old_name  Old name (need to be addslashes)
     *
     * @return null|string new addslashes name
    **/
    public static function processName($old_name)
    {

        if ($old_name == null) {
            return $old_name;
        }

        $rulecollection = new RuleDictionnaryManufacturerCollection();
        $output         = [];
        $output         = $rulecollection->processAllRules(
            ["name" => stripslashes($old_name)],
            $output,
            []
        );
        if (isset($output["name"])) {
            return $output["name"];
        }
        return $old_name;
    }


    public function cleanDBonPurge()
    {
        // Rules use manufacturer intread of manufacturers_id
        Rule::cleanForItemAction($this, 'manufacturer');
    }


    /**
     * @since 0.84
     *
     * @param $itemtype
     * @param $base                  HTMLTableBase object
     * @param $super                 HTMLTableSuperHeader object (default NULL)
     * @param $father                HTMLTableHeader object (default NULL)
     * @param $options      array
    **/
    public static function getHTMLTableHeader(
        $itemtype,
        HTMLTableBase $base,
        ?HTMLTableSuperHeader $super = null,
        ?HTMLTableHeader $father = null,
        array $options = []
    ) {

        $column_name = __CLASS__;

        if (isset($options['dont_display'][$column_name])) {
            return;
        }

        $base->addHeader($column_name, Manufacturer::getTypeName(1), $super, $father);
    }


    /**
     * @since 0.84
     *
     * @param $row                HTMLTableRow object (default NULL)
     * @param $item               CommonDBTM object (default NULL)
     * @param $father             HTMLTableCell object (default NULL)
     * @param $options   array
    **/
    public static function getHTMLTableCellsForItem(
        ?HTMLTableRow $row = null,
        ?CommonDBTM $item = null,
        ?HTMLTableCell $father = null,
        array $options = []
    ) {

        $column_name = __CLASS__;

        if (isset($options['dont_display'][$column_name])) {
            return;
        }

        if (!empty($item->fields["manufacturers_id"])) {
            $row->addCell(
                $row->getHeaderByName($column_name),
                Dropdown::getDropdownName(
                    "glpi_manufacturers",
                    $item->fields["manufacturers_id"]
                ),
                $father
            );
        }
    }
}
