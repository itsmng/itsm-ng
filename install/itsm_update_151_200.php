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

    if(!isset($current_config['dashboard_api_token'])) {
        $migration->addConfig([
            'dashboard_api_token' => Toolbox::getRandomString(32)
        ]);
    }

    if (!isset($current_config['url_dashboard_api'])) {
        $migration->addConfig([
            'url_dashboard_api' => 'localhost:3000'
        ]);
    }

    if (!$DB->tableExists('Dashboard_Entity')){
        $query = "CREATE TABLE `Dashboard_Entity` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
            `parentId` INTEGER NULL,
        
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->tableExists('Dashboard_Profile')){
        $query = "CREATE TABLE `Dashboard_Profile` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
        
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->tableExists('Dashboard_ProfileEntity')){

        $query = "CREATE TABLE `Dashboard_ProfileEntity` (
            `id` INTEGER NOT NULL,
            `profileId` INTEGER NOT NULL,
            `entityId` INTEGER NOT NULL,
        
            UNIQUE INDEX `Dashboard_ProfileEntity_profileId_entityId_key`(`profileId`, `entityId`),
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->tableExists('Dashboard_Group')){
        $query = "CREATE TABLE `Dashboard_Group` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
            `entityId` INTEGER NOT NULL,
        
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->tableExists('Dashboard_User')){
        $query = "CREATE TABLE `Dashboard_User` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
        
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;    
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->tableExists('Dashboard_Location')){
        $query = "CREATE TABLE `Dashboard_Location` (
            `id` INTEGER NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(191) NOT NULL,
        
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->tableExists('Dashboard_AssetType')){
        $query = "CREATE TABLE `Dashboard_AssetType` (
            `id` INTEGER NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(191) NOT NULL,
        
            UNIQUE INDEX `Dashboard_AssetType_name_key`(`name`),
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->tableExists('Dashboard_Type')){
        $query = "CREATE TABLE `Dashboard_Type` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
            `assetTypeId` INTEGER NOT NULL,
        
            PRIMARY KEY (`id`, `assetTypeId`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }
    
    if (!$DB->tableExists('Dashboard_Model')){
        $query = "CREATE TABLE `Dashboard_Model` (
            `id` INTEGER NOT NULL,
            `name` VARCHAR(191) NOT NULL,
            `assetTypeId` INTEGER NOT NULL,
        
            PRIMARY KEY (`id`, `assetTypeId`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->tableExists('Dashboard_Asset')){
        $query = "CREATE TABLE `Dashboard_Asset` (
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
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->tableExists('_Dashboard_ProfileEntityToDashboard_User')){
        $query = "CREATE TABLE `_Dashboard_ProfileEntityToDashboard_User` (
            `A` INTEGER NOT NULL,
            `B` INTEGER NOT NULL,
        
            UNIQUE INDEX `_Dashboard_ProfileEntityToDashboard_User_AB_unique`(`A`, `B`),
            INDEX `_Dashboard_ProfileEntityToDashboard_User_B_index`(`B`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->tableExists('_Dashboard_GroupToDashboard_User')){
        $query = "CREATE TABLE `_Dashboard_GroupToDashboard_User` (
            `A` INTEGER NOT NULL,
            `B` INTEGER NOT NULL,
        
            UNIQUE INDEX `_Dashboard_GroupToDashboard_User_AB_unique`(`A`, `B`),
            INDEX `_Dashboard_GroupToDashboard_User_B_index`(`B`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ";
        $DB->queryOrDie($query, "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->fieldExists('Dashboard_Entity', 'parentId')){
        $DB->queryOrDie(
            "ALTER TAd_fkey` FOREIGN KEY (`parentId`) REFERENCES `Dashboard_Entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
            ", "erreur lorsBLE `Dashboard_Entity` ADD CONSTRAINT `Dashboard_Entity_parentI de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->fieldExists('Dashboard_ProfileEntity', 'profileId')){
        $DB->queryOrDie(
            "ALTER TABLE `Dashboard_ProfileEntity` ADD CONSTRAINT `Dashboard_ProfileEntity_profileId_fkey` FOREIGN KEY (`profileId`) REFERENCES `Dashboard_Profile`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
            ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->fieldExists('Dashboard_ProfileEntity', 'entityId')){
        $DB->queryOrDie(
            "ALTER TABLE `Dashboard_ProfileEntity` ADD CONSTRAINT `Dashboard_ProfileEntity_entityId_fkey` FOREIGN KEY (`entityId`) REFERENCES `Dashboard_Entity`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
            ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }
    
    if (!$DB->fieldExists('Dashboard_Group', 'entityId')){
        $DB->queryOrDie(
            "ALTER TABLE `Dashboard_Group` ADD CONSTRAINT `Dashboard_Group_entityId_fkey` FOREIGN KEY (`entityId`) REFERENCES `Dashboard_Entity`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
            ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->fieldExists('Dashboard_Type', 'assetTypeId')){
        $DB->queryOrDie(
            "ALTER TABLE `Dashboard_Type` ADD CONSTRAINT `Dashboard_Type_assetTypeId_fkey` FOREIGN KEY (`assetTypeId`) REFERENCES `Dashboard_AssetType`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
            ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }
    
    if (!$DB->fieldExists('Dashboard_Model', 'assetTypeId')){
        $DB->queryOrDie(
            "ALTER TABLE `Dashboard_Model` ADD CONSTRAINT `Dashboard_Model_assetTypeId_fkey` FOREIGN KEY (`assetTypeId`) REFERENCES `Dashboard_AssetType`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
            ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->fieldExists('Dashboard_Asset', 'entityId')){
        $DB->queryOrDie(
            "ALTER TABLE `Dashboard_Asset` ADD CONSTRAINT `Dashboard_Asset_entityId_fkey` FOREIGN KEY (`entityId`) REFERENCES `Dashboard_Entity`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
            ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    if (!$DB->fieldExists('Dashboard_Asset', 'assetTypeId')){
        $DB->queryOrDie(
            "ALTER TABLE `Dashboard_Asset` ADD CONSTRAINT `Dashboard_Asset_assetTypeId_fkey` FOREIGN KEY (`assetTypeId`) REFERENCES `Dashboard_AssetType`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
            ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }
    
    if (!$DB->fieldExists('Dashboard_Asset', 'locationId')){
        $DB->queryOrDie(
            "ALTER TABLE `Dashboard_Asset` ADD CONSTRAINT `Dashboard_Asset_locationId_fkey` FOREIGN KEY (`locationId`) REFERENCES `Dashboard_Location`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
            ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }
    
    if (!$DB->fieldExists('Dashboard_Asset', 'assetTypeId')){
        $DB->queryOrDie(
            "ALTER TABLE `Dashboard_Asset` ADD CONSTRAINT `Dashboard_Asset_modelId_assetTypeId_fkey` FOREIGN KEY (`modelId`, `assetTypeId`) REFERENCES `Dashboard_Model`(`id`, `assetTypeId`) ON DELETE RESTRICT ON UPDATE CASCADE;
            ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }
    
    if (!$DB->fieldExists('Dashboard_Asset', 'assetTypeId')){
        $DB->queryOrDie(
            "ALTER TABLE `Dashboard_Asset` ADD CONSTRAINT `Dashboard_Asset_typeId_assetTypeId_fkey` FOREIGN KEY (`typeId`, `assetTypeId`) REFERENCES `Dashboard_Type`(`id`, `assetTypeId`) ON DELETE RESTRICT ON UPDATE CASCADE;
            ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }
    
    if (!$DB->fieldExists('_Dashboard_ProfileEntityToDashboard_User', 'A')){
        $DB->queryOrDie(
            "ALTER TABLE `_Dashboard_ProfileEntityToDashboard_User` ADD CONSTRAINT `_Dashboard_ProfileEntityToDashboard_User_A_fkey` FOREIGN KEY (`A`) REFERENCES `Dashboard_ProfileEntity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
            ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }
    
    if (!$DB->fieldExists('_Dashboard_ProfileEntityToDashboard_User', 'B')){
        $DB->queryOrDie(
            "ALTER TABLE `_Dashboard_ProfileEntityToDashboard_User` ADD CONSTRAINT `_Dashboard_ProfileEntityToDashboard_User_B_fkey` FOREIGN KEY (`B`) REFERENCES `Dashboard_User`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
            ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }
    
    if (!$DB->fieldExists('_Dashboard_GroupToDashboard_User', 'A')){
        $DB->queryOrDie(
            "ALTER TABLE `_Dashboard_GroupToDashboard_User` ADD CONSTRAINT `_Dashboard_GroupToDashboard_User_A_fkey` FOREIGN KEY (`A`) REFERENCES `Dashboard_Group`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
            ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }
    
    if (!$DB->fieldExists('_Dashboard_GroupToDashboard_User', 'B')){
        $DB->queryOrDie(
            "ALTER TABLE `_Dashboard_GroupToDashboard_User` ADD CONSTRAINT `_Dashboard_GroupToDashboard_User_B_fkey` FOREIGN KEY (`B`) REFERENCES `Dashboard_User`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
            ", "erreur lors de la mise a jour de la table de Dashboard_Entity".$DB->error());
    }

    // FIX OIDC
    if ($DB->fieldExists('glpi_oidc_config', 'proxy')) {
        $DB->queryOrDie("ALTER TABLE `glpi_oidc_config` ADD `proxy` VARCHAR(255) NULL AFTER `scope`;");
    }

    if ($DB->fieldExists('glpi_oidc_config', 'cert')) {
        $DB->queryOrDie("ALTER TABLE `glpi_oidc_config` ADD `cert` VARCHAR(255) NULL AFTER `proxy`;");
    }

    // add the dashboard table population
    CronTask::register('Dashboard', 'dashboard', DAY_TIMESTAMP / 2, [
        'comment' => __('Update dashboard'),
        'mode'    => CronTask::MODE_INTERNAL,
    ]);

    // ************ Keep it at the end **************
    $migration->executeMigration();
    return $updateresult;
}
