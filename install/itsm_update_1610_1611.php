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
 * Update ITSM-NG from 1.6.10 to 1.6.11
 *
 * @return bool for success (will die for most error)
 **/
function update1610to1611()
{
    /** @global Migration $migration */
    global $DB, $migration;

    $updateresult     = true;

    if (!$DB->fieldExists('glpi_oidc_config', 'sso_link_users')) {
        $query = "ALTER TABLE `glpi_oidc_config` ADD COLUMN (`sso_link_users` TINYINT(1) NOT NULL DEFAULT 1)";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de glpi_oidc_config".$DB->error());
    }

    $taskExists = $DB->queryOrDie("SELECT id AS nu FROM `glpi_crontasks` WHERE `name` = 'cleantransferorphans'");
    if ($DB->numrows($taskExists) == 0) {
        $query = 'INSERT INTO `glpi_crontasks` (`itemtype`, `name`, `frequency`, `state`, `mode`, `allowmode`, `hourmin`, `hourmax`, `logs_lifetime`) VALUES
                ("Transfer", "cleantransferorphans", "3600", "1", "1", "3", "0", "24", "30")';
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de glpi_crontasks".$DB->error());
    }

    // ************ Keep it at the end **************
    $migration->executeMigration();
    return $updateresult;
}
