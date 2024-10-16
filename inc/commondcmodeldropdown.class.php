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

/// CommonDCModelDropdown class - dropdown for datacenter items models
abstract class CommonDCModelDropdown extends CommonDropdown
{
    public $additional_fields_for_dictionnary = ['manufacturer'];


    public static function getFieldLabel()
    {
        return _n('Model', 'Models', 1);
    }

    /**
     * Return Additional Fields for this type
     *
     * @return array
    **/
    public function getAdditionalFields()
    {
        global $DB;

        $fields = [];
        if ($DB->fieldExists($this->getTable(), 'product_number')) {
            $fields[__('Product Number')] = [
               'name'   => 'product_number',
               'type'   => 'text',
               'value' => $this->fields['product_number'],
            ];
        }

        if ($DB->fieldExists($this->getTable(), 'weight')) {
            $fields[__('Weight')] = [
               'name'   => 'weight',
               'type'   => 'number',
               'min' => 0,
               'max' => 1000,
               'value' => $this->fields['weight'],
            ];
        }

        if ($DB->fieldExists($this->getTable(), 'required_units')) {
            $fields[__('Required units')] = [
               'name'   => 'required_units',
               'type'   => 'number',
               'min'    => 1,
               'value'  => $this->fields['required_units'],
            ];
        }

        if ($DB->fieldExists($this->getTable(), 'depth')) {
            $fields[__('Depth')] = [
               'name'   => 'depth',
               'type'   => 'select',
               'values' => [
                  '1'      => __('1'),
                  '0.5'    => __('1/2'),
                  '0.33'   => __('1/3'),
                  '0.25'   => __('1/4')
               ],
               'value' => $this->fields['depth'],
            ];
        }

        if ($DB->fieldExists($this->getTable(), 'power_connections')) {
            $fields[__('Power connections')] = [
               'name'   => 'power_connections',
               'type'   => 'number',
               'min' => 0,
               'max' => 1000,
               'value' => $this->fields['power_connections'],
            ];
        }

        if ($DB->fieldExists($this->getTable(), 'power_consumption')) {
            $fields[__('Power consumption')] = [
               'name'   => 'power_consumption',
               'type'   => 'number',
               'after'   => __('watts'),
               'min' => 0,
               'html'   => true
            ];
        }

        if ($DB->fieldExists($this->getTable(), 'max_power')) {
            $fields[__('Max. power (in watts)')] = [
               'name'   => 'max_power',
               'type'   => 'number',
               'after'   => __('watts'),
               'min' => 0,
               'html'   => true
            ];
        }

        if ($DB->fieldExists($this->getTable(), 'is_half_rack')) {
            $fields[__('Is half rack')] = [
               'name'   => 'is_half_rack',
               'type'   => 'checkbox',
            ];
        }

        if ($DB->fieldExists($this->getTable(), 'picture_front')) {
            $fields[__('Front picture')] = [
               'name'   => 'picture_front',
               'type'   => 'imageUpload',
               'id'     => rand(),
               'accept' => 'image/*',
               'value'  => $this->fields['picture_front']
            ];
        }

        if ($DB->fieldExists($this->getTable(), 'picture_rear')) {
            $fields[__('Rear picture')] = [
               'name'   => 'picture_rear',
               'type'   => 'imageUpload',
               'id'     => rand(),
               'accept' => 'image/*',
               'value'  => $this->fields['picture_rear']
            ];
        }

        return $fields;
    }

    public function rawSearchOptions()
    {
        global $DB;
        $options = parent::rawSearchOptions();
        $table   = $this->getTable();

        if ($DB->fieldExists($table, 'product_number')) {
            $options[] = [
               'id'    => '130',
               'table' => $table,
               'field' => 'product_number',
               'name'  => __('Product Number'),
               'autocomplete' => true,
            ];
        }

        if ($DB->fieldExists($table, 'weight')) {
            $options[] = [
               'id'       => '131',
               'table'    => $table,
               'field'    => 'weight',
               'name'     => __('Weight'),
               'datatype' => 'decimal'
            ];
        }

        if ($DB->fieldExists($table, 'required_units')) {
            $options[] = [
               'id'       => '132',
               'table'    => $table,
               'field'    => 'required_units',
               'name'     => __('Required units'),
               'datatype' => 'number'
            ];
        }

        if ($DB->fieldExists($table, 'depth')) {
            $options[] = [
               'id'       => '133',
               'table'    => $table,
               'field'    => 'depth',
               'name'     => __('Depth'),
            ];
        }

        if ($DB->fieldExists($table, 'power_connections')) {
            $options[] = [
               'id'       => '134',
               'table'    => $table,
               'field'    => 'power_connections',
               'name'     => __('Power connections'),
               'datatype' => 'number'
            ];
        }

        if ($DB->fieldExists($table, 'power_consumption')) {
            $options[] = [
               'id'       => '135',
               'table'    => $table,
               'field'    => 'power_consumption',
               'name'     => __('Power consumption'),
               'datatype' => 'decimal'
            ];
        }

        if ($DB->fieldExists($table, 'is_half_rack')) {
            $options[] = [
               'id'       => '136',
               'table'    => $table,
               'field'    => 'is_half_rack',
               'name'     => __('Is half rack'),
               'datatype' => 'bool'
            ];
        }

        if ($DB->fieldExists($table, 'picture_front')) {
            $options[] = [
               'id'            => '137',
               'table'         => $table,
               'field'         => 'picture_front',
               'name'          => __('Front picture'),
               'datatype'      => 'specific',
               'nosearch'      => true,
               'massiveaction' => true,
               'nosort'        => true,
            ];
        }

        if ($DB->fieldExists($table, 'picture_rear')) {
            $options[] = [
               'id'            => '138',
               'table'         => $table,
               'field'         => 'picture_rear',
               'name'          => __('Rear picture'),
               'datatype'      => 'specific',
               'nosearch'      => true,
               'massiveaction' => true,
               'nosort'        => true,
            ];
        }

        return $options;
    }

    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'picture_front':
            case 'picture_rear':
                if (isset($options['html']) && $options['html']) {
                    return Html::image(Toolbox::getPictureUrl($values[$field]), [
                       'alt'   => $options['searchopt']['name'],
                       'style' => 'height: 30px;',
                    ]);
                }
        }

        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    public function prepareInputForAdd($input)
    {
        return $this->managePictures($input);
    }

    public function prepareInputForUpdate($input)
    {
        return $this->managePictures($input);
    }

    public function cleanDBonPurge()
    {
        Toolbox::deletePicture($this->fields['picture_front']);
        Toolbox::deletePicture($this->fields['picture_rear']);
    }

    /**
     * Add/remove front and rear pictures for models
     * @param  array $input the form input
     * @return array        the altered input
     */
    public function managePictures($input)
    {
        foreach (['picture_front', 'picture_rear'] as $name) {
            if (isset($input["_blank_$name"])
                && $input["_blank_$name"]) {
                $input[$name] = '';

                if (array_key_exists($name, $this->fields)) {
                    Toolbox::deletePicture($this->fields[$name]);
                }
            }

            if (isset($input["_$name"])) {
                $filename = array_shift($input["_$name"]);
                $src      = GLPI_TMP_DIR . '/' . $filename;

                $prefix   = null;
                if (isset($input["_prefix_$name"])) {
                    $prefix = array_shift($input["_prefix_$name"]);
                }

                if ($dest = Toolbox::savePicture($src, $prefix)) {
                    $input[$name] = $dest;
                } else {
                    Session::addMessageAfterRedirect(__('Unable to save picture file.'), true, ERROR);
                }

                if (array_key_exists($name, $this->fields)) {
                    Toolbox::deletePicture($this->fields[$name]);
                }
            }
        }

        return $input;
    }

    public function displaySpecificTypeField($ID, $field = [])
    {
        switch ($field['type']) {
            case 'depth':
                Dropdown::showFromArray(
                    $field['name'],
                    [
                      '1'      => __('1'),
                      '0.5'    => __('1/2'),
                      '0.33'   => __('1/3'),
                      '0.25'   => __('1/4')
               ],
                    [
                      'value'                 => $this->fields[$field['name']]
               ]
                );
                break;
            default:
                throw new \RuntimeException("Unknown {$field['type']}");
        }
    }
}
