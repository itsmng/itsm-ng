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
function update151to200() : bool {
    /** @global Migration $migration */
    global $DB, $migration;

    $current_config   = Config::getConfigurationValues('core');
    $updateresult     = true;

    $migration->displayTitle(sprintf(__('Update to %s'), '2.0.0_rc3'));
    $migration->setVersion('2.0.0_rc3');

    if(!$DB->fieldExists('glpi_users', 'menu_favorite')) {
        $query = "alter table glpi_users add column menu_favorite longtext default '{}';";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de glpi_users".$DB->error());
    }

    if(!$DB->fieldExists('glpi_users', 'menu_favorite_on')) {
        $query = "alter table glpi_users add column menu_favorite_on text default '1';";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de glpi_users".$DB->error());
    }

    if(!$DB->fieldExists('glpi_users', 'menu_position')) {
        $query = "alter table glpi_users add column menu_position text default 'menu-left';";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de glpi_users".$DB->error());
    }

    if(!$DB->fieldExists('glpi_users', 'menu_small')) {
        $query = "alter table glpi_users add column menu_small text default 'false';";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de glpi_users".$DB->error());
    }

    if(!$DB->fieldExists('glpi_users', 'menu_width')) {
        $query = "alter table glpi_users add column menu_width text default 'null';";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de glpi_users".$DB->error());
    }

    if(!$DB->fieldExists('glpi_users', 'menu_open')) {
        $query = "alter table glpi_users add column menu_open longtext default '[]';";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de glpi_users".$DB->error());
    }

    if(!$DB->fieldExists('glpi_users', 'bubble_pos')) {
        $query = "alter table glpi_users add column bubble_pos text default NULL;";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de glpi_users".$DB->error());
    }

    if (!$DB->tableExists('glpi_user_menu')) {
        $query = "
        CREATE TABLE IF NOT EXISTS `glpi_user_menu` (
            `name` VARCHAR(255) NOT NULL,
            `user_id` int(11) NOT NULL,
            `content` text COLLATE utf8_unicode_ci,
            PRIMARY KEY (`name`, `user_id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de glpi_user_menu".$DB->error());
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

    if (!$DB->fieldExists('glpi_oidc_config', 'logout')) {
        $migration->addField('glpi_oidc_config', 'logout', 'VARCHAR(255) NULL');
    }

    // ************ Keep it at the end **************
    $migration->executeMigration();
    return $updateresult;
}
