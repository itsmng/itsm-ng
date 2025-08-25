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

/**
 * @since 9.2
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class LineOperator extends CommonDropdown
{
    public static $rightname = 'lineoperator';

    public $can_be_translated = false;

    public static function getTypeName($nb = 0)
    {
        return _n('Line operator', 'Line operators', $nb);
    }

    public function getAdditionalFields()
    {
        return [
           __('Mobile Country Code') => [
              'name'  => 'mcc',
              'type'  => 'number',
              'value' => $this->fields['mcc'],
           ],
           __('Mobile Network Code') => [
              'name'  => 'mnc',
              'type'  => 'number',
              'value' => $this->fields['mnc'],
           ],
        ];
    }

    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
              'id'                 => '11',
              'table'              => $this->getTable(),
              'field'              => 'mcc',
              'name'               => __('Mobile Country Code'),
              'datatype'           => 'text',
              'autocomplete'       => true,
        ];

        $tab[] = [
              'id'                 => '12',
              'table'              => $this->getTable(),
              'field'              => 'mnc',
              'name'               => __('Mobile Network Code'),
              'datatype'           => 'text',
              'autocomplete'       => true,
        ];

        return $tab;
    }

    public function prepareInputForAdd($input)
    {
        $input = parent::prepareInputForAdd($input);

        if (!isset($input['mcc'])) {
            $input['mcc'] = 0;
        }
        if (!isset($input['mnc'])) {
            $input['mnc'] = 0;
        }

        //check for mcc/mnc unicity
        $request = $this::getAdapter()->request([
           'SELECT' => ['COUNT(*) AS cpt'],
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'mcc' => $input['mcc'],
              'mnc' => $input['mnc']
           ]
        ])->fetchAssociative();

        if ($request['cpt'] > 0) {
            Session::addMessageAfterRedirect(
                __('Mobile country code and network code combination must be unique!'),
                ERROR,
                true
            );
            return false;
        }

        return $input;
    }
}
