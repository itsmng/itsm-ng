<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
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
 * Update ITSM-NG from 2.0.0 to 2.0.4
 *
 * @return bool for success (will die for most error)
 **/
function update200to204(): bool
{
    /** @global Migration $migration */
    global $DB, $migration;

    $criteria = "SELECT * FROM glpi_oidc_config";
    $iterators = $DB->request($criteria);
    if (count($iterators) > 0) {
        $oidc_db = $iterators->next();
        if (isset($oidc_db['ClientSecret']) && $oidc_db['ClientSecret'] != '') {
            $oidc_db['ClientSecret'] = Toolbox::sodiumEncrypt($oidc_db['ClientSecret']);
            $DB->updateOrInsert("glpi_oidc_config", $oidc_db, ['id'   => 0]);
        }
    }

    // ************ Keep it at the end **************
    $migration->executeMigration();
    return true;
}
