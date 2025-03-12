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
 * Update ITSM-NG from 1.6.0 to 1.6.1
 *
 * @return bool for success (will die for most error)
 **/
function update160to161() {
    /** @global Migration $migration */
    global $DB, $migration;

    $updateresult     = true;

    $migration->displayTitle(sprintf(__('Update to %s'), '1.6.1'));
    $migration->setVersion('1.6.1');

    if(!$DB->fieldExists('glpi_oidc_config', 'logout')) {
        $query = "ALTER TABLE `glpi_oidc_config` ADD COLUMN (`logout` varchar(255) DEFAULT NULL)";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de glpi_oidc_config".$DB->error());
    }

    if(!$DB->fieldExists('glpi_oidc_config', 'proxy') && !$DB->fieldExists('glpi_oidc_config', 'cert')) {
        $query = "ALTER TABLE `glpi_oidc_config` ADD COLUMN (`cert` varchar(255) DEFAULT NULL, `proxy` varchar(255) DEFAULT NULL)";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de glpi_configs".$DB->error());
    }

    // ************ Keep it at the end **************
    $migration->executeMigration();
    return $updateresult;
}
