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
 * Update ITSM-NG from 1.2.0 to 1.3.0
 *
 * @return bool for success (will die for most error)
 **/
function update120to130()
{
    /** @global Migration $migration */
    global $DB, $migration;

    $current_config   = Config::getConfigurationValues('core');
    $updateresult     = true;

    $migration->displayTitle(sprintf(__('Update to %s'), '1.3.0'));
    $migration->setVersion('1.3.0');


    /** Create new table for chat config */
    if (!$DB->tableExists("glpi_notificationchatconfigs")) {
        $config = "CREATE TABLE `glpi_notificationchatconfigs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `hookurl` varchar(255) DEFAULT NULL,
            `chat` varchar(255) DEFAULT NULL,
            `type` varchar(255) DEFAULT NULL,
            `value` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $DB->queryOrDie($config, "erreur lors de la création de la table de configuration ".$DB->error());
    }

    /** Create new table for notification chat queue */
    if (!$DB->tableExists("glpi_queuedchats")) {
        $config = "CREATE TABLE `glpi_queuedchats` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `itemtype` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
            `items_id` int(11) NOT NULL DEFAULT '0',
            `notificationtemplates_id` int(11) NOT NULL DEFAULT '0',
            `entities_id` int(11) NOT NULL DEFAULT '0',
            `locations_id` int(11) NOT NULL DEFAULT '0',
            `groups_id` int(11) NOT NULL DEFAULT '0',
            `itilcategories_id` int(11) NOT NULL DEFAULT '0',
            `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
            `sent_try` int(11) NOT NULL DEFAULT '0',
            `create_time` timestamp NULL DEFAULT NULL,
            `send_time` timestamp NULL DEFAULT NULL,
            `sent_time` timestamp NULL DEFAULT NULL,
            `entName` text COLLATE utf8_unicode_ci,
            `ticketTitle` text COLLATE utf8_unicode_ci,
            `completName` text COLLATE utf8_unicode_ci,
            `serverName` text COLLATE utf8_unicode_ci,
            `hookurl` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
            `mode` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'See Notification_NotificationTemplate::MODE_* constants',
            PRIMARY KEY (`id`),
            KEY `item` (`itemtype`,`items_id`,`notificationtemplates_id`),
            KEY `is_deleted` (`is_deleted`),
            KEY `entities_id` (`entities_id`),
            KEY `sent_try` (`sent_try`),
            KEY `create_time` (`create_time`),
            KEY `send_time` (`send_time`),
            KEY `sent_time` (`sent_time`),
            KEY `mode` (`mode`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $DB->queryOrDie($config, "erreur lors de la création de la table de configuration ".$DB->error());
    }

    // Add default notifications_chat in config
    if (!isset($current_config['notifications_chat'])) {
        $migration->addConfig([
            'notifications_chat' => '0',
        ]);
    }

    /** Create new table for Open ID connect's config */
    if (!$DB->tableExists("glpi_queuednotifications")) {
        $config = "CREATE TABLE `glpi_queuednotifications` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `itemtype` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
            `items_id` int(11) NOT NULL DEFAULT '0',
            `notificationtemplates_id` int(11) NOT NULL DEFAULT '0',
            `entities_id` int(11) NOT NULL DEFAULT '0',
            `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
            `sent_try` int(11) NOT NULL DEFAULT '0',
            `create_time` timestamp NULL DEFAULT NULL,
            `send_time` timestamp NULL DEFAULT NULL,
            `sent_time` timestamp NULL DEFAULT NULL,
            `name` text COLLATE utf8_unicode_ci,
            `sender` text COLLATE utf8_unicode_ci,
            `sendername` text COLLATE utf8_unicode_ci,
            `recipient` text COLLATE utf8_unicode_ci,
            `recipientname` text COLLATE utf8_unicode_ci,
            `replyto` text COLLATE utf8_unicode_ci,
            `replytoname` text COLLATE utf8_unicode_ci,
            `headers` text COLLATE utf8_unicode_ci,
            `body_html` longtext COLLATE utf8_unicode_ci,
            `body_text` longtext COLLATE utf8_unicode_ci,
            `messageid` text COLLATE utf8_unicode_ci,
            `documents` text COLLATE utf8_unicode_ci,
            `mode` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'See Notification_NotificationTemplate::MODE_* constants',
            PRIMARY KEY (`id`),
            KEY `item` (`itemtype`,`items_id`,`notificationtemplates_id`),
            KEY `is_deleted` (`is_deleted`),
            KEY `entities_id` (`entities_id`),
            KEY `sent_try` (`sent_try`),
            KEY `create_time` (`create_time`),
            KEY `send_time` (`send_time`),
            KEY `sent_time` (`sent_time`),
            KEY `mode` (`mode`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $DB->queryOrDie($config, "erreur lors de la création de la table de configuration ".$DB->error());
    }

    // Update OIDC config table
    if (!$DB->fieldExists('glpi_oidc_config', 'scope')) {
        $config = "ALTER TABLE `glpi_oidc_config` ADD COLUMN `scope` varchar(255) DEFAULT NULL";
        $DB->queryOrDie($config, "erreur lors de la mise a jour de la table de configuration oidc".$DB->error());
    }

    // Update users table to add accessibility columns
    if (!$DB->fieldExists('glpi_users', 'access_zoom_level')) {
        $users = "ALTER TABLE `glpi_users` ADD COLUMN `access_zoom_level` smallint(1) DEFAULT 100";
        $DB->queryOrDie($users, "erreur lors de la mise a jour de la table de glpi_users".$DB->error());
    }

    if (!$DB->fieldExists('glpi_users', 'access_font')) {
        $users = "ALTER TABLE `glpi_users` ADD COLUMN `access_font` varchar(100) DEFAULT NULL";
        $DB->queryOrDie($users, "erreur lors de la mise a jour de la table de glpi_users".$DB->error());
    }

    if (!$DB->fieldExists('glpi_users', 'access_shortcuts')) {
        $users = "ALTER TABLE `glpi_users` ADD COLUMN `access_shortcuts` tinyint(1) DEFAULT 0";
        $DB->queryOrDie($users, "erreur lors de la mise a jour de la table de glpi_users".$DB->error());
    }

    if (!$DB->fieldExists('glpi_users', 'access_custom_shortcuts')) {
        $users = "ALTER TABLE `glpi_users` ADD COLUMN `access_custom_shortcuts` JSON DEFAULT NULL";
        $DB->queryOrDie($users, "erreur lors de la mise a jour de la table de glpi_users".$DB->error());
    }

    // ************ Keep it at the end **************
    $migration->executeMigration();
    return $updateresult;
}
