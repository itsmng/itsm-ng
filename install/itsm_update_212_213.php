<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2025 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ITSM-NG.
 *
 * ITSM-NG is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ITSM-NG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ITSM-NG. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

/**
 * Update ITSM-NG from 2.1.2 to 2.1.3
 *
 * @return bool for success (will die for most error)
 **/
function update212to213(): bool
{
    /** @global Migration $migration */
    global $DB, $migration;

    $migration->displayMessage("Add new rights for assigned tickets");

    $default_assigns = [
        'followup' => 16384,
        'task'     => 16384
    ];

    $profiles = $DB->request([
        'SELECT' => ['id', 'name'],
        'FROM'   => 'glpi_profiles'
    ]);

    foreach ($profiles as $profile) {
        foreach ($default_assigns as $rightname => $rightvalue) {
            $rights_table = 'glpi_profilerights';

            $current_rights = $DB->request([
                'SELECT' => ['rights'],
                'FROM'   => $rights_table,
                'WHERE'  => [
                    'profiles_id' => $profile['id'],
                    'name'       => $rightname
                ]
            ]);

            if ($row = $current_rights->next()) {
                $new_rights = $row['rights'] | $rightvalue;
                $DB->update(
                    $rights_table,
                    [
                        'rights' => $new_rights
                    ],
                    [
                        'profiles_id' => $profile['id'],
                        'name'       => $rightname
                    ]
                );
            }
        }
    }

    $migration->executeMigration();
    return true;
}
