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

/* Test for inc/reminder.class.php */

class Reminder extends DbTestCase
{
    public function testAddVisibilityRestrict()
    {
        //first, as a super-admin
        $this->login();
        $restrict = trim(preg_replace('/\s+/', ' ', \Reminder::addVisibilityRestrict()));
        $this->string($restrict)
           ->contains("`glpi_reminders`.`users_id` = '" . $_SESSION['glpiID'] . "'")
           ->contains("`glpi_profiles_reminders`.`profiles_id` = '" . $_SESSION['glpiactiveprofile']['id'] . "'");

        $this->login('normal', 'normal');
        $restrict = trim(preg_replace('/\s+/', ' ', \Reminder::addVisibilityRestrict()));
        $this->string($restrict)
           ->contains("`glpi_reminders`.`users_id` = '" . $_SESSION['glpiID'] . "'")
           ->contains("`glpi_profiles_reminders`.`profiles_id` = '" . $_SESSION['glpiactiveprofile']['id'] . "'");

        $this->login('tech', 'tech');
        $restrict = trim(preg_replace('/\s+/', ' ', \Reminder::addVisibilityRestrict()));
        $this->string($restrict)
           ->contains("`glpi_reminders`.`users_id` = '" . $_SESSION['glpiID'] . "'")
           ->contains("`glpi_profiles_reminders`.`profiles_id` = '" . $_SESSION['glpiactiveprofile']['id'] . "'");

        $bkp_groups = $_SESSION['glpigroups'];
        $_SESSION['glpigroups'] = [42, 1337];
        $str = \Reminder::addVisibilityRestrict();
        $_SESSION['glpigroups'] = $bkp_groups;
        $this->string(trim(preg_replace('/\s+/', ' ', $str)))
           ->contains("`glpi_groups_reminders`.`groups_id` IN ('42', '1337')");
    }
}
