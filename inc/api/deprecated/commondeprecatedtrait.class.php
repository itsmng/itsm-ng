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

namespace Glpi\Api\Deprecated;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * @since 9.5
 */

trait CommonDeprecatedTrait
{
    abstract public function getType(): string;

    /**
     * Get the class short name for the deprecated itemtpe
     *
     * @return string
     */
    private function getDeprecatedClass(): string
    {
        return (new \ReflectionClass(static::class))->getShortName();
    }

    /**
     * For each hateoas, update the href ref to match the deprecated type
     *
     * @param array $hateoas Current hateoas
     * @return array Updated hateoas
     */
    public function replaceCurrentHateoasRefByDeprecated(array $hateoas): array
    {
        foreach ($hateoas as $key => $value) {
            if (isset($value["href"])) {
                $hateoas[$key]["href"] = str_replace(
                    $this->getType(),
                    $this->getDeprecatedClass(),
                    $value["href"]
                );
            }
        }

        return $hateoas;
    }

    /**
     * For each searchoption, update the UID ref to match the deprecated type
     *
     * @param array $soptions
     * @return CommonDeprecatedTrait Return self to allow method chaining
     */
    public function updateSearchOptionsUids(array &$soptions)
    {
        $soptions = array_map(function ($soption) {
            if (isset($soption['uid'])) {
                $new_uid = str_replace(
                    $this->getType(),
                    $this->getDeprecatedClass(),
                    $soption['uid']
                );
                $soption['uid'] = $new_uid;
            }

            return $soption;
        }, $soptions);

        return $this;
    }

    /**
     * For each searchoption, update the table ref to match the deprecated type
     *
     * @param array $soptions
     * @return CommonDeprecatedTrait Return self to allow method chaining
     */
    public function updateSearchOptionsTables(array &$soptions)
    {
        $soptions = array_map(function ($soption) {
            if (isset($soption['table'])) {
                $new_table = str_replace(
                    getTableForItemType($this->getType()),
                    getTableForItemType($this->getDeprecatedClass()),
                    $soption['table']
                );
                $soption['table'] = $new_table;
            }

            return $soption;
        }, $soptions);

        return $this;
    }

    /**
     * Add a field in an array or an object
     *
     * @param array|object $fields
     * @param string $name
     * @param string $value
     * @return CommonDeprecatedTrait Return self to allow method chaining
     */
    public function addField(&$fields, string $name, string $value)
    {
        if (is_object($fields)) {
            if (!isset($fields->$name)) {
                $fields->$name = $value;
            }
        } elseif (is_array($fields)) {
            if (!isset($fields[$name])) {
                $fields[$name] = $value;
            }
        }

        return $this;
    }

    /**
     * Rename a field in an array or an object
     *
     * @param array|object $fields
     * @param string $old
     * @param string $new
     * @return CommonDeprecatedTrait Return self to allow method chaining
     */
    public function renameField(&$fields, string $old, string $new)
    {
        if (is_object($fields)) {
            if (isset($fields->$old)) {
                $fields->$new = $fields->$old;
                unset($fields->$old);
            }
        } elseif (is_array($fields)) {
            if (isset($fields[$old])) {
                $fields[$new] = $fields[$old];
                unset($fields[$old]);
            }
        }

        return $this;
    }

    /**
     * Delete a field in an array or an object
     *
     * @param array|object $fields
     * @param string $name
     * @return CommonDeprecatedTrait Return self to allow method chaining
     */
    public function deleteField(&$fields, string $name)
    {
        if (is_object($fields)) {
            if (isset($fields->$name)) {
                unset($fields->$name);
            }
        } elseif (is_array($fields)) {
            if (isset($fields[$name])) {
                unset($fields[$name]);
            }
        }

        return $this;
    }

    /**
     * Add a searchoption
     *
     * @param array $fields
     * @param string $key
     * @param array $values
     * @return CommonDeprecatedTrait Return self to allow method chaining
     */
    public function addSearchOption(
        array &$soptions,
        string $key,
        array $values
    ) {
        $soptions[$key] = $values;

        return $this;
    }

    /**
     * Edit an existing searchoption
     *
     * @param array $fields
     * @param string $key
     * @param array $values
     * @return CommonDeprecatedTrait Return self to allow method chaining
     */
    public function alterSearchOption(
        array &$soptions,
        string $key,
        array $values
    ) {
        foreach ($values as $v_key => $v_value) {
            $soptions[$key][$v_key] = $v_value;
        }

        return $this;
    }

    /**
     * Delete an existing searchoption
     *
     * @param array $fields
     * @param string $key
     * @param array $values
     * @return CommonDeprecatedTrait Return self to allow method chaining
     */
    public function deleteSearchOption(array &$soptions, string $key)
    {
        if (isset($soptions[$key])) {
            unset($soptions[$key]);
        }
        return $this;
    }
}
