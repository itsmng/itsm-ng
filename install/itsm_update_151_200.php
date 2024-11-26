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
 * Update ITSM-NG from 1.5.0 to 1.5.1
 *
 * @return bool for success (will die for most error)
 **/
function update151to200(): bool
{
    /** @global Migration $migration */
    global $DB, $migration;

    $current_config   = Config::getConfigurationValues('core');
    $updateresult     = true;

    $migration->displayTitle(sprintf(__('Update to %s'), '2.0.0'));
    $migration->setVersion('2.0.0');

    $userModifications = [
        'menu_favorite' => "longtext default '{}'",
        'menu_favorite_on' => "text default '1'",
        'menu_position' => "text default 'menu-left'",
        'menu_small' => "text default 'false'",
        'menu_open' => "longtext default '[]'",
    ];
    foreach ($userModifications as $field => $definition) {
        if (!$DB->fieldExists('glpi_users', $field)) {
            $migration->addField('glpi_users', $field, $definition);
        }
    }

    if (!$DB->fieldExists('glpi_oidc_config', $field)) {
        $migration->addField('glpi_oidc_config', 'logout', 'VARCHAR(255) NULL');
    }

    if (!$DB->tableExists('glpi_dashboards')) {
        $query = "
        CREATE TABLE `glpi_dashboards` (
            `id` int(11) NOT NULL UNIQUE AUTO_INCREMENT,
            `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
            `content` LONGTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
            `profileId` int(11) NOT NULL DEFAULT 0,
            `userId` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`profileId`, `userId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de glpi_dashboards".$DB->error());
    }

    // ************ Keep it at the end **************
    $migration->executeMigration();
    return $updateresult;
}
