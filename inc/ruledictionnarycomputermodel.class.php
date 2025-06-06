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

class RuleDictionnaryComputerModel extends RuleDictionnaryDropdown
{
    /**
     * Constructor
    **/
    public function __construct()
    {
        parent::__construct('RuleDictionnaryComputerModel');
    }


    /**
     * @see Rule::getCriterias()
    **/
    public function getCriterias()
    {

        static $criterias = [];

        if (count($criterias)) {
            return $criterias;
        }

        $criterias['name']['field']         = 'name';
        $criterias['name']['name']          =  _n('Model', 'Models', 1);
        $criterias['name']['table']         = 'glpi_computermodels';

        $criterias['manufacturer']['field'] = 'name';
        $criterias['manufacturer']['name']  = Manufacturer::getTypeName(1);
        $criterias['manufacturer']['table'] = 'glpi_manufacturers';

        return $criterias;
    }


    /**
     * @see Rule::getActions()
    **/
    public function getActions()
    {

        $actions                          = [];
        $actions['name']['name']          = _n('Model', 'Models', 1);
        $actions['name']['force_actions'] = ['append_regex_result', 'assign', 'regex_result'];

        return $actions;
    }
}
