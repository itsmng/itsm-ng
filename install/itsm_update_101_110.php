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
 * Update ITSM-NG from 1.0.1 to 1.1.0
 *
 * @return bool for success (will die for most error)
 **/
function update101to110()
{
    /** @global Migration $migration */
    global $DB, $migration;

    $updateresult     = true;

    $migration->displayTitle(sprintf(__('Update to %s'), '1.1.0'));
    $migration->setVersion('1.1.0');


    /** Create new table for Open ID connect's config */
    if (!$DB->tableExists("glpi_oidc_config")) {
        $config = "CREATE TABLE `glpi_oidc_config` (
        `id` INT(11) NOT NULL DEFAULT 0,
        `Provider` varchar(255) DEFAULT NULL,
        `ClientID` varchar(255) DEFAULT NULL,
        `ClientSecret` varchar(255) DEFAULT NULL,
        `is_activate`   TINYINT(1) NOT NULL DEFAULT 0,
        `is_forced`   TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $DB->queryOrDie($config, "erreur lors de la création de la table de configuration ".$DB->error());
    }

    $oidc_config = [
        "is_activate" => 0,
        "is_forced" => 0
    ];

    // Update or insert OIDC config
    $DB->updateOrInsert("glpi_oidc_config", $oidc_config, ['id' => 0]);

    /** /Create new table for Open ID connect's config */

    // ************ Keep it at the end **************
    $migration->executeMigration();
    return $updateresult;
}
