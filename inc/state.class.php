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
 * State Class
**/
class State extends CommonTreeDropdown
{
    public $can_be_translated       = true;

    public static $rightname               = 'state';

    public static function getTypeName($nb = 0)
    {
        return _n('Status of items', 'Statuses of items', $nb);
    }


    public static function getFieldLabel()
    {
        return __('Status');
    }


    /**
     * @since 0.85
     *
     * @see CommonTreeDropdown::getAdditionalFields()
    **/
    public function getAdditionalFields()
    {

        $fields   = parent::getAdditionalFields();
        $fields[__('As child of')] = [
           'name'  => 'states_id',
           'type' => 'select',
           'values' => getOptionForItems('State', ['NOT' => ['id' => $this->getId()]]),
           'value' => $this->fields['states_id'],
        ];

        foreach ($this->getvisibilityFields() as $type => $field) {
            $fields[$type::getTypeName(Session::getPluralNumber())] = [
               'name'  => $field,
               'type'  => 'checkbox',
               'value' => $this->fields[$field],
            ];
        }
        return $fields;
    }


    /**
     * Dropdown of states for behaviour config
     *
     * @param $name            select name
     * @param $lib    string   to add for -1 value (default '')
     * @param $value           default value (default 0)
    **/
    public static function dropdownBehaviour($name, $lib = "", $value = 0)
    {
        global $DB;

        $elements = ["0" => __('Keep status')];

        if ($lib) {
            $elements["-1"] = $lib;
        }

        $iterator = $DB->request([
           'SELECT' => ['id', 'name'],
           'FROM'   => 'glpi_states',
           'ORDER'  => 'name'
        ]);

        while ($data = $iterator->next()) {
            $elements[$data["id"]] = sprintf(__('Set status: %s'), $data["name"]);
        }
        Dropdown::showFromArray($name, $elements, ['value' => $value]);
    }


    public static function showSummary()
    {
        global $DB, $CFG_GLPI;

        $state_type = $CFG_GLPI["state_types"];
        $states     = [];

        foreach ($state_type as $key => $itemtype) {
            if ($item = getItemForItemtype($itemtype)) {
                if (!$item->canView()) {
                    unset($state_type[$key]);
                } else {
                    $table = getTableForItemType($itemtype);
                    $WHERE = [];
                    if ($item->maybeDeleted()) {
                        $WHERE["$table.is_deleted"] = 0;
                    }
                    if ($item->maybeTemplate()) {
                        $WHERE["$table.is_template"] = 0;
                    }
                    $WHERE += getEntitiesRestrictCriteria($table);
                    $iterator = $DB->request([
                       'SELECT' => [
                          'states_id',
                          'COUNT'  => '* AS cpt'
                       ],
                       'FROM'   => $table,
                       'WHERE'  => $WHERE,
                       'GROUP'  => 'states_id'
                    ]);

                    while ($data = $iterator->next()) {
                        $states[$data["states_id"]][$itemtype] = $data["cpt"];
                    }
                }
            }
        }

        if (count($states)) {
            // Produce headline
            echo "<div class='center'><table class='tab_cadrehov' aria-label='Status'><tr>";

            // Type
            echo "<th>" . __('Status') . "</th>";

            foreach ($state_type as $key => $itemtype) {
                if ($item = getItemForItemtype($itemtype)) {
                    echo "<th>" . $item->getTypeName(Session::getPluralNumber()) . "</th>";
                    $total[$itemtype] = 0;
                } else {
                    unset($state_type[$key]);
                }
            }

            echo "<th>" . __('Total') . "</th>";
            echo "</tr>";

            $iterator = $DB->request([
               'FROM'   => 'glpi_states',
               'WHERE'  => getEntitiesRestrictCriteria('glpi_states', '', '', true),
               'ORDER'  => 'completename'
            ]);

            // No state
            $tot = 0;
            echo "<tr class='tab_bg_2'><td>---</td>";
            foreach ($state_type as $itemtype) {
                echo "<td class='numeric'>";

                if (isset($states[0][$itemtype])) {
                    echo $states[0][$itemtype];
                    $total[$itemtype] += $states[0][$itemtype];
                    $tot              += $states[0][$itemtype];
                } else {
                    echo "&nbsp;";
                }

                echo "</td>";
            }
            echo "<td class='numeric b'>$tot</td></tr>";

            while ($data = $iterator->next()) {
                $tot = 0;
                echo "<tr class='tab_bg_2'><td class='b'>";

                $opt = ['reset'    => 'reset',
                            'sort'     => 1,
                            'start'    => 0,
                            'criteria' => ['0' => ['value' => '$$$$' . $data['id'],
                                                             'searchtype' => 'contains',
                                                             'field' => 31]]];
                echo "<a href='" . $CFG_GLPI['root_doc'] . "/front/allassets.php?" . Toolbox::append_params($opt, '&amp;') . "'>" . $data["completename"] . "</a></td>";

                foreach ($state_type as $itemtype) {
                    echo "<td class='numeric'>";

                    if (isset($states[$data["id"]][$itemtype])) {
                        echo $states[$data["id"]][$itemtype];
                        $total[$itemtype] += $states[$data["id"]][$itemtype];
                        $tot              += $states[$data["id"]][$itemtype];
                    } else {
                        echo "&nbsp;";
                    }

                    echo "</td>";
                }
                echo "<td class='numeric b'>$tot</td>";
                echo "</tr>";
            }
            echo "<tr class='tab_bg_2'><td class='center b'>" . __('Total') . "</td>";
            $tot = 0;

            foreach ($state_type as $itemtype) {
                echo "<td class='numeric b'>" . $total[$itemtype] . "</td>";
                $tot += $total[$itemtype];
            }

            echo "<td class='numeric b'>$tot</td></tr>";
            echo "</table></div>";
        } else {
            echo "<div class='center b'>" . __('No item found') . "</div>";
        }
    }


    /**
     * @since 0.85
     *
     * @see CommonDBTM::getEmpty()
    **/
    public function getEmpty()
    {

        parent::getEmpty();
        //initialize is_visible_* fields at true to keep the same behavior as in older versions
        foreach ($this->getvisibilityFields() as $field) {
            $this->fields[$field] = 1;
        }
    }


    public function cleanDBonPurge()
    {
        Rule::cleanForItemCriteria($this);
        Rule::cleanForItemCriteria($this, '_states_id%');
    }


    /**
     * @since 0.85
     *
     * @see CommonTreeDropdown::prepareInputForAdd()
    **/
    public function prepareInputForAdd($input)
    {
        if (!isset($input['states_id'])) {
            $input['states_id'] = 0;
        }
        if (!$this->isUnique($input)) {
            Session::addMessageAfterRedirect(
                sprintf(__('%1$s must be unique!'), $this->getType(1)),
                false,
                ERROR
            );
            return false;
        }

        $input = parent::prepareInputForAdd($input);

        $state = new self();
        // Get visibility information from parent if not set
        if (isset($input['states_id']) && $state->getFromDB($input['states_id'])) {
            foreach ($this->getvisibilityFields() as $field) {
                if (!isset($input[$field]) && isset($state->fields[$field])) {
                    $input[$field] = $state->fields[$field];
                }
            }
        }
        return $input;
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '21',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_computer',
           'name'               => sprintf(__('%1$s - %2$s'), __('Visibility'), Computer::getTypeName(Session::getPluralNumber())),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '22',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_softwareversion',
           'name'               => sprintf(
               __('%1$s - %2$s'),
               __('Visibility'),
               SoftwareVersion::getTypeName(Session::getPluralNumber())
           ),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '23',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_monitor',
           'name'               => sprintf(__('%1$s - %2$s'), __('Visibility'), Monitor::getTypeName(Session::getPluralNumber())),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '24',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_printer',
           'name'               => sprintf(__('%1$s - %2$s'), __('Visibility'), Printer::getTypeName(Session::getPluralNumber())),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '25',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_peripheral',
           'name'               => sprintf(__('%1$s - %2$s'), __('Visibility'), Peripheral::getTypeName(Session::getPluralNumber())),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '26',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_phone',
           'name'               => sprintf(__('%1$s - %2$s'), __('Visibility'), Phone::getTypeName(Session::getPluralNumber())),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '27',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_networkequipment',
           'name'               => sprintf(
               __('%1$s - %2$s'),
               __('Visibility'),
               NetworkEquipment::getTypeName(Session::getPluralNumber())
           ),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '28',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_softwarelicense',
           'name'               => sprintf(
               __('%1$s - %2$s'),
               __('Visibility'),
               SoftwareLicense::getTypeName(Session::getPluralNumber())
           ),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '29',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_certificate',
           'name'               => sprintf(
               __('%1$s - %2$s'),
               __('Visibility'),
               Certificate::getTypeName(Session::getPluralNumber())
           ),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '30',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_rack',
           'name'               => sprintf(
               __('%1$s - %2$s'),
               __('Visibility'),
               Rack::getTypeName(Session::getPluralNumber())
           ),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '31',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_line',
           'name'               => sprintf(
               __('%1$s - %2$s'),
               __('Visibility'),
               Line::getTypeName(Session::getPluralNumber())
           ),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '32',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_enclosure',
           'name'               => sprintf(
               __('%1$s - %2$s'),
               __('Visibility'),
               Enclosure::getTypeName(Session::getPluralNumber())
           ),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '33',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_pdu',
           'name'               => sprintf(
               __('%1$s - %2$s'),
               __('Visibility'),
               PDU::getTypeName(Session::getPluralNumber())
           ),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '34',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_cluster',
           'name'               => sprintf(
               __('%1$s - %2$s'),
               __('Visibility'),
               Cluster::getTypeName(Session::getPluralNumber())
           ),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '35',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_passivedcequipment',
           'name'               => sprintf(
               __('%1$s - %2$s'),
               __('Visibility'),
               PassiveDCEquipment::getTypeName(Session::getPluralNumber())
           ),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '36',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_contract',
           'name'               => sprintf(
               __('%1$s - %2$s'),
               __('Visibility'),
               Contract::getTypeName(Session::getPluralNumber())
           ),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '37',
           'table'              => $this->getTable(),
           'field'              => 'is_visible_appliance',
           'name'               => sprintf(
               __('%1$s - %2$s'),
               __('Visibility'),
               Appliance::getTypeName(Session::getPluralNumber())
           ),
           'datatype'           => 'bool'
        ];

        return $tab;
    }

    public function prepareInputForUpdate($input)
    {
        if (!$this->isUnique($input)) {
            Session::addMessageAfterRedirect(
                sprintf(__('%1$s must be unique per level!'), $this->getType(1)),
                false,
                ERROR
            );
            return false;
        }
        return parent::prepareInputForUpdate($input);
    }

    /**
     * Checks that this state is unique given the new field values.
     *    Unique fields checked:
     *       - states_id
     *       - name
     * @param array $input Array of field names and values
     * @return boolean True if the new/updated record will be unique
     */
    public function isUnique($input)
    {
        global $DB;

        $unicity_fields = ['states_id', 'name'];

        $has_changed = false;
        $where = [];
        foreach ($unicity_fields as $unicity_field) {
            if (
                isset($input[$unicity_field]) &&
                  (!isset($this->fields[$unicity_field]) || $input[$unicity_field] != $this->fields[$unicity_field])
            ) {
                $has_changed = true;
            }
            if (isset($input[$unicity_field])) {
                $where[$unicity_field] = $input[$unicity_field];
            }
        }
        if (!$has_changed) {
            //state has not changed; this is OK.
            return true;
        }

        $query = [
           'FROM'   => $this->getTable(),
           'COUNT'  => 'cpt',
           'WHERE'  => $where
        ];
        $row = $DB->request($query)->next();
        return (int)$row['cpt'] == 0;
    }

    /**
     * Get visibility fields from conf
     */
    protected function getvisibilityFields(): array
    {
        global $CFG_GLPI;
        $fields = [];
        foreach ($CFG_GLPI['state_types'] as $type) {
            $fields[$type] = 'is_visible_' . strtolower($type);
        }
        return $fields;
    }
}
