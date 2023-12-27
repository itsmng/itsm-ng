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
    $ADDTODISPLAYPREF = [];

    $migration->displayTitle(sprintf(__('Update to %s'), '2.0.0'));
    $migration->setVersion('2.0.0');

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

    if(!$DB->tableExists('glpi_dashboards')) {
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

    if (!$DB->tableExists('Dashboard_Entity')) {
        $query = "
        CREATE TABLE `Dashboard_Entity` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
            `parentId` INTEGER NULL,
        
            INDEX `Dashboard_Entity_parentId_key`(`parentId`),
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;        
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
        $DB->queryOrDie("
        ALTER TABLE `Dashboard_Entity` ADD CONSTRAINT `Dashboard_Entity_parentId_fkey` FOREIGN KEY (`parentId`) REFERENCES `Dashboard_Entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
        ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->tableExists('Dashboard_Profile')) {
        $query = "
        CREATE TABLE `Dashboard_Profile` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
        
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
    }

    if (!$DB->tableExists('Dashboard_Group')) {
        $query = "
        CREATE TABLE `Dashboard_Group` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
            `entityId` INTEGER NOT NULL,
        
            INDEX `Dashboard_Group_entityId_key`(`entityId`),
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;        
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
        $DB->queryOrDie("
        ALTER TABLE `Dashboard_Group` ADD CONSTRAINT `Dashboard_Group_entityId_fkey` FOREIGN KEY (`entityId`) REFERENCES `Dashboard_Entity`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
        ", "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
}

    if (!$DB->tableExists('Dashboard_User')) {
        $query = "
        CREATE TABLE `Dashboard_User` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
            `groupId` INTEGER NULL,
            `profileId` INTEGER NULL,
        
            INDEX `Dashboard_User_groupId_key`(`groupId`),
            INDEX `Dashboard_User_profileId_key`(`profileId`),
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;        
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
        $DB->queryOrDie("
        ALTER TABLE `Dashboard_User` ADD CONSTRAINT `Dashboard_User_groupId_fkey` FOREIGN KEY (`groupId`) REFERENCES `Dashboard_Group`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
        ", "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
        $DB->queryOrDie("
        ALTER TABLE `Dashboard_User` ADD CONSTRAINT `Dashboard_User_profileId_fkey` FOREIGN KEY (`profileId`) REFERENCES `Dashboard_Profile`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
        ", "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());

    }

    if (!$DB->tableExists('Dashboard_Location')) {
        $query = "
        CREATE TABLE `Dashboard_Location` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
        
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;        
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
    }

    if (!$DB->tableExists('Dashboard_AssetType')) {
        $query = "
        CREATE TABLE `Dashboard_AssetType` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
        
            UNIQUE INDEX `Dashboard_AssetType_name_key`(`name`),
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;        
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
    }

    if (!$DB->tableExists('Dashboard_Type')) {
        $query = "
        CREATE TABLE `Dashboard_Type` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
            `assetTypeId` INTEGER NOT NULL,
        
            PRIMARY KEY (`id`, `assetTypeId`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
        $DB->queryOrDie("
        ALTER TABLE `Dashboard_Type` ADD CONSTRAINT `Dashboard_Type_assetTypeId_fkey` FOREIGN KEY (`assetTypeId`) REFERENCES `Dashboard_AssetType`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
        ", "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
    }

    if (!$DB->tableExists('Dashboard_Model')) {
        $query = "
        CREATE TABLE `Dashboard_Model` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
            `assetTypeId` INTEGER NOT NULL,
        
            PRIMARY KEY (`id`, `assetTypeId`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
        $DB->queryOrDie("
        ALTER TABLE `Dashboard_Model` ADD CONSTRAINT `Dashboard_Model_assetTypeId_fkey` FOREIGN KEY (`assetTypeId`) REFERENCES `Dashboard_AssetType`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
        ", "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
    }

    if (!$DB->tableExists('Dashboard_Asset')) {
        $query = "
        CREATE TABLE `Dashboard_Asset` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
            `entityId` INTEGER NOT NULL,
            `assetTypeId` INTEGER NOT NULL,
            `locationId` INTEGER NULL,
            `modelId` INTEGER NULL,
            `typeId` INTEGER NULL,
        
            PRIMARY KEY (`id`, `assetTypeId`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;        
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
        $DB->queryOrDie("
        ALTER TABLE `Dashboard_Asset` ADD CONSTRAINT `Dashboard_Asset_entityId_fkey` FOREIGN KEY (`entityId`) REFERENCES `Dashboard_Entity`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
        ", "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
        $DB->queryOrDie("
        ALTER TABLE `Dashboard_Asset` ADD CONSTRAINT `Dashboard_Asset_assetTypeId_fkey` FOREIGN KEY (`assetTypeId`) REFERENCES `Dashboard_AssetType`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
        ", "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
        $DB->queryOrDie("
        ALTER TABLE `Dashboard_Asset` ADD CONSTRAINT `Dashboard_Asset_locationId_fkey` FOREIGN KEY (`locationId`) REFERENCES `Dashboard_Location`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
        ", "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
        $DB->queryOrDie("
        ALTER TABLE `Dashboard_Asset` ADD CONSTRAINT `Dashboard_Asset_modelId_fkey` FOREIGN KEY (`modelId`) REFERENCES `Dashboard_Model`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
        ", "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
        $DB->queryOrDie("
        ALTER TABLE `Dashboard_Asset` ADD CONSTRAINT `Dashboard_Asset_typeId_fkey` FOREIGN KEY (`typeId`) REFERENCES `Dashboard_Type`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
        ", "erreur lors de la mise a jour de la table de Dashboard_Profile".$DB->error());
    }

    if ($DB)

    // ************ Keep it at the end **************
    $migration->executeMigration();
    return $updateresult;
}
