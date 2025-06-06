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

/// LDAP criteria class
class RuleRightParameter extends CommonDropdown
{
    public static $rightname         = 'rule_ldap';

    public $can_be_translated = false;

    /**
     * @see CommonDBTM::prepareInputForAdd()
    **/
    public function prepareInputForAdd($input)
    {

        //LDAP parameters MUST be in lower case
        //because the are retieved in lower case  from the directory
        $input["value"] = Toolbox::strtolower($input["value"]);
        return $input;
    }


    public function getAdditionalFields()
    {

        return [
           _n('Criterion', 'Criteria', 1) => [
              'name'  => 'value',
              'type'  => 'text',
              'value' => $this->fields['value'],
           ]
        ];
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'value',
           'name'               => _n('Criterion', 'Criteria', 1),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        return $tab;
    }


    public static function getTypeName($nb = 0)
    {
        return _n('LDAP criterion', 'LDAP criteria', $nb);
    }
}
