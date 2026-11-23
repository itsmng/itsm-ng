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

namespace tests\units;

use DbTestCase;

/* Test for inc/savedsearch.class.php */

class SavedSearch extends DbTestCase
{
    public function testNewSearchDefaultsToPrivate()
    {
        $this->login('normal', 'normal');
        $this->boolean((bool) \SavedSearch::canCreate())->isFalse();

        $savedSearch = new \SavedSearch();
        $input = [
            'name' => 'Private search',
            'itemtype' => 'Ticket',
            'type' => \SavedSearch::SEARCH,
            'url' => '/front/ticket.php',
        ];
        $this->boolean($savedSearch->can(-1, CREATE, $input))->isTrue();
        $this->checkInput($savedSearch, $savedSearch->add($input), [
            'is_private' => 1,
            'users_id' => \Session::getLoginUserID(),
        ]);
    }

    public function testAddVisibilityRestrict()
    {
        //first, as a super-admin
        $this->login();
        $this->string(\SavedSearch::addVisibilityRestrict())
           ->isIdenticalTo('');

        $this->login('normal', 'normal');
        $this->string(\SavedSearch::addVisibilityRestrict())
           ->isIdenticalTo("`glpi_savedsearches`.`is_private` = '1' AND `glpi_savedsearches`.`users_id` = '5'");

        //add public saved searches read right for normal profile
        global $DB;
        $DB->update(
            'glpi_profilerights',
            ['rights' => 1],
            [
              'profiles_id'  => 2,
              'name'         => 'bookmark_public'
         ]
        );

        //ACLs have changed: login again.
        $this->login('normal', 'normal');

        //reset rights. Done here so ACLs are reset even if tests fails.
        $DB->update(
            'glpi_profilerights',
            ['rights' => 0],
            [
              'profiles_id'  => 2,
              'name'         => 'bookmark_public'
         ]
        );

        $this->string(\SavedSearch::addVisibilityRestrict())
           ->isIdenticalTo("((`glpi_savedsearches`.`is_private` = '1' AND `glpi_savedsearches`.`users_id` = '5') OR `glpi_savedsearches`.`is_private` = '0')");
    }
}
