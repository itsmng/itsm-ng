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
 * SolutionTemplate Class
**/
class SolutionTemplate extends CommonDropdown
{
    // From CommonDBTM
    public $dohistory = true;

    public static $rightname = 'solutiontemplate';

    public $can_be_translated = false;


    public static function getTypeName($nb = 0)
    {
        return _n('Solution template', 'Solution templates', $nb);
    }


    public function getAdditionalFields()
    {

        return [
           SolutionType::getTypeName(1) => [
              'name'  => 'solutiontypes_id',
              'type'  => 'select',
              'values' => getOptionForItems('SolutionType'),
              'value' => $this->fields['solutiontypes_id'],
              'actions' => getItemActionButtons(['info', 'add'], 'SolutionType')
           ],
           __('Content') => [
              'name'  => 'content',
              'id'    => 'TextAreaForSolutionContent',
              'type'  => 'richtextarea',
              'value' => $this->fields['content'],
              'col_lg' => 12,
              'col_md' => 12,
           ]
        ];
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '4',
           'name'               => __('Content'),
           'field'              => 'content',
           'table'              => $this->getTable(),
           'datatype'           => 'text',
           'htmltext'           => true
        ];

        $tab[] = [
           'id'                 => '3',
           'name'               => SolutionType::getTypeName(1),
           'field'              => 'name',
           'table'              => getTableForItemType('SolutionType'),
           'datatype'           => 'dropdown'
        ];

        return $tab;
    }
}
