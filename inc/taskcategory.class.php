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
 * TaskCategory class
**/
class TaskCategory extends CommonTreeDropdown
{
    // From CommonDBTM
    public $dohistory          = true;
    public $can_be_translated  = true;

    public static $rightname          = 'taskcategory';

    public function getAdditionalFields()
    {

        $tab = parent::getAdditionalFields();

        $tab[__('As child of')] = [
           'name'  => 'taskcategories_id',
           'type'  => 'select',
           'values' => getOptionForItems('TaskCategory', ['NOT' => ['id' => $this->getID()]]),
           'value' => $this->fields['taskcategories_id']
        ];

        $tab[__('Active')] = [
           'name'  => 'is_active',
           'type'  => 'checkbox',
           'value' => $this->fields['is_active']
        ];

        $tab[KnowbaseItemCategory::getTypeName()] = [
           'name'  => 'knowbaseitemcategories_id',
           'type'  => 'select',
           'values' => getOptionForItems('KnowbaseItemCategory'),
           'value' => $this->fields['knowbaseitemcategories_id']
        ];

        return $tab;
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '8',
           'table'              => $this->getTable(),
           'field'              => 'is_active',
           'name'               => __('Active'),
           'datatype'           => 'bool'
        ];

        return $tab;
    }


    public static function getTypeName($nb = 0)
    {
        return _n('Task category', 'Task categories', $nb);
    }
}
