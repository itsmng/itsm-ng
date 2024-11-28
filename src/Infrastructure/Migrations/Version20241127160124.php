<?php

declare(strict_types=1);

namespace Itsmng\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241127160124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    private function switchToDatetime($table, $column)
    {
        // add temp column
        $this->addSql('ALTER TABLE '.$table.' ADD temp_'.$column.' DATETIME DEFAULT NULL');
        // set temp column value with old column value
        $this->addSql('UPDATE '.$table.' SET temp_'.$column.' = '.$column.';');
        // drop old column
        $this->addSql('ALTER TABLE '.$table.' DROP COLUMN '.$column.';');
        // rename temp column to old column
        $this->addSql('ALTER TABLE '.$table.' CHANGE temp_'.$column.' '.$column.' DATETIME DEFAULT NULL');
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE glpi_apiclients CHANGE dolog_method dolog_method SMALLINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_appliances CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE glpi_authmails CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_blacklistedmailcontents', 'date_creation');
        $this->addSql('ALTER TABLE glpi_blacklistedmailcontents CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_blacklists', 'date_creation');
        $this->addSql('ALTER TABLE glpi_blacklists CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE glpi_budgets CHANGE value value NUMERIC(20, 4) DEFAULT \'0\' NOT NULL');
        $this->switchToDatetime('glpi_budgettypes', 'date_creation');
        $this->addSql('ALTER TABLE glpi_budgettypes CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_calendars', 'date_creation');
        $this->addSql('ALTER TABLE glpi_calendars CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE glpi_calendarsegments CHANGE day day TINYINT(1) DEFAULT 1 NOT NULL COMMENT \'number of the day based on date(w)\'');
        $this->switchToDatetime('glpi_certificates_items', 'date_creation');
        $this->addSql('ALTER TABLE glpi_certificates_items CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE glpi_changecosts CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_changes_items RENAME INDEX item TO itemt');
        $this->addSql('ALTER TABLE glpi_changevalidations CHANGE submission_date submission_date DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE validation_date validation_date DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE glpi_clusters RENAME INDEX group_id_tech TO groups_id_tech');
        $this->switchToDatetime('glpi_clustertypes', 'date_creation');
        $this->addSql('ALTER TABLE glpi_clustertypes CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_computermodels', 'date_creation');
        $this->addSql('ALTER TABLE glpi_computermodels CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE glpi_computers CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->switchToDatetime('glpi_computertypes', 'date_creation');
        $this->addSql('ALTER TABLE glpi_computertypes CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_computervirtualmachines', 'date_creation');
        $this->addSql('ALTER TABLE glpi_computervirtualmachines CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_consumableitems', 'date_creation');
        $this->addSql('ALTER TABLE glpi_consumableitems CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_consumableitemtypes', 'date_creation');
        $this->addSql('ALTER TABLE glpi_consumableitemtypes CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_consumables', 'date_creation');
        $this->addSql('ALTER TABLE glpi_consumables CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_contacts', 'date_creation');
        $this->addSql('ALTER TABLE glpi_contacts CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_contacttypes', 'date_creation');
        $this->addSql('ALTER TABLE glpi_contacttypes CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_contracts', 'date_creation');
        $this->addSql('ALTER TABLE glpi_contracts CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_contracttypes', 'date_creation');
        $this->addSql('ALTER TABLE glpi_contracttypes CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE glpi_crontasklogs CHANGE crontasklogs_id crontasklogs_id INT NOT NULL COMMENT \'id of "start" event\'');
        $this->switchToDatetime('glpi_crontasks', 'date_creation');
        $this->switchToDatetime('glpi_crontasks', 'lastrun');
        $this->addSql('ALTER TABLE glpi_crontasks CHANGE date_mod date_mod DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE glpi_dashboards MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX id ON glpi_dashboards');
        $this->addSql('DROP INDEX `primary` ON glpi_dashboards');
        $this->addSql('ALTER TABLE glpi_dashboards CHANGE name name VARCHAR(100) NOT NULL, CHANGE content content LONGTEXT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX profileId_userId ON glpi_dashboards (profileId, userId)');
        $this->addSql('ALTER TABLE glpi_dashboards ADD PRIMARY KEY (id)');
        $this->switchToDatetime('glpi_datacenters', 'date_creation');
        $this->addSql('ALTER TABLE glpi_datacenters CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_dcrooms', 'date_creation');
        $this->addSql('ALTER TABLE glpi_dcrooms CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_devicebatteries', 'date_creation');
        $this->addSql('ALTER TABLE glpi_devicebatteries CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_devicecasetypes', 'date_creation');
        $this->addSql('ALTER TABLE glpi_devicecasetypes CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_devicegenerics', 'date_creation');
        $this->addSql('ALTER TABLE glpi_devicegenerics CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_devicegraphiccards', 'date_creation');
        $this->addSql('ALTER TABLE glpi_devicegraphiccards CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_devicenetworkcards', 'date_creation');
        $this->addSql('ALTER TABLE glpi_devicenetworkcards CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_devicepowersupplies', 'date_creation');
        $this->addSql('ALTER TABLE glpi_devicepowersupplies CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_devicesensors', 'date_creation');
        $this->addSql('ALTER TABLE glpi_devicesensors CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_devicesimcards', 'date_creation');
        $this->addSql('ALTER TABLE glpi_devicesimcards CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_devicesoundcards', 'date_creation');
        $this->addSql('ALTER TABLE glpi_devicesoundcards CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_documentcategories', 'date_creation');
        $this->addSql('ALTER TABLE glpi_documentcategories CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_documents_items', 'date_creation');
        $this->switchToDatetime('glpi_documents_items', 'date');
        $this->addSql('ALTER TABLE glpi_documents_items CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_documenttypes', 'date_creation');
        $this->addSql('ALTER TABLE glpi_documenttypes CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_domainrecords', 'date_creation');
        $this->addSql('ALTER TABLE glpi_domainrecords CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->switchToDatetime('glpi_enclosuremodels', 'date_creation');
        $this->addSql('ALTER TABLE glpi_enclosuremodels CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE glpi_enclosures CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE glpi_enclosures RENAME INDEX group_id_tech TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_entities CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE glpi_events RENAME INDEX date TO name');
        $this->addSql('ALTER TABLE glpi_fieldunicities CHANGE fields fields LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX ldap_value ON glpi_groups');
        $this->addSql('DROP INDEX ldap_group_dn ON glpi_groups');
        $this->addSql('CREATE FULLTEXT INDEX ldap_value ON glpi_groups (ldap_value)');
        $this->addSql('CREATE FULLTEXT INDEX ldap_group_dn ON glpi_groups (ldap_group_dn)');
        $this->addSql('DROP INDEX source ON glpi_impactitems');
        $this->addSql('ALTER TABLE glpi_infocoms CHANGE value value NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE warranty_value warranty_value NUMERIC(20, 4) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_ipaddresses CHANGE version version SMALLINT UNSIGNED DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_ipnetworks CHANGE version version SMALLINT UNSIGNED DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_items_devicebatteries RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_devicecases RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_devicecontrols RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_devicedrives RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_devicefirmwares RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_devicegenerics RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_devicegraphiccards RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_deviceharddrives RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_devicememories RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_devicemotherboards RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_devicenetworkcards RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_devicepcis RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_devicepowersupplies RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_deviceprocessors RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_devicesensors RENAME INDEX computers_id TO items_id');
        $this->addSql('ALTER TABLE glpi_items_devicesoundcards RENAME INDEX computers_id TO items_id');
        $this->switchToDatetime('glpi_items_disks', 'date_creation');
        $this->addSql('ALTER TABLE glpi_items_disks CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE glpi_itilsolutions RENAME INDEX item_id TO items_id');
        $this->switchToDatetime('glpi_links', 'date_creation');
        $this->addSql('ALTER TABLE glpi_links CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE glpi_links_itemtypes CHANGE itemtype itemtype VARCHAR(100) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_monitors CHANGE size size NUMERIC(5, 2) DEFAULT \'0\' NOT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_networkequipments CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_oidc_config ADD logout VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_olalevels CHANGE `match` `match` VARCHAR(10) DEFAULT NULL COMMENT \'see define.php *_MATCHING constant\'');
        $this->switchToDatetime('glpi_pdutypes', 'date_creation');
        $this->addSql('ALTER TABLE glpi_pdutypes CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE glpi_peripherals CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_phones CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_printers CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_problemcosts CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_projectcosts CHANGE cost cost NUMERIC(20, 4) DEFAULT \'0\' NOT NULL');
        $this->switchToDatetime('glpi_projects', 'date_creation');
        $this->addSql('ALTER TABLE glpi_projects CHANGE date_mod date_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE glpi_racks RENAME INDEX group_id_tech TO groups_id_tech');
        $this->addSql('DROP INDEX field_value ON glpi_ruleactions');
        $this->addSql('CREATE INDEX field_value ON glpi_ruleactions (field, value)');
        $this->addSql('ALTER TABLE glpi_rules CHANGE `match` `match` VARCHAR(10) DEFAULT NULL COMMENT \'see define.php *_MATCHING constant\'');
        $this->addSql('ALTER TABLE glpi_slalevels CHANGE `match` `match` VARCHAR(10) DEFAULT NULL COMMENT \'see define.php *_MATCHING constant\'');
        $this->addSql('ALTER TABLE glpi_softwares CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_ticketcosts CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_ticketsatisfactions CHANGE satisfaction satisfaction INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_users CHANGE language language VARCHAR(10) DEFAULT NULL COMMENT \'see define.php CFG_GLPI[language] array\', CHANGE csv_delimiter csv_delimiter VARCHAR(1) DEFAULT NULL, CHANGE priority_1 priority_1 VARCHAR(20) DEFAULT NULL, CHANGE priority_2 priority_2 VARCHAR(20) DEFAULT NULL, CHANGE priority_3 priority_3 VARCHAR(20) DEFAULT NULL, CHANGE priority_4 priority_4 VARCHAR(20) DEFAULT NULL, CHANGE priority_5 priority_5 VARCHAR(20) DEFAULT NULL, CHANGE priority_6 priority_6 VARCHAR(20) DEFAULT NULL, CHANGE password_forget_token password_forget_token VARCHAR(40) DEFAULT NULL, CHANGE layout layout VARCHAR(20) DEFAULT NULL, CHANGE palette palette VARCHAR(20) DEFAULT NULL, CHANGE access_custom_shortcuts access_custom_shortcuts LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX item ON glpi_vobjects');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE glpi_plugin_workflows_configs (id INT AUTO_INCREMENT NOT NULL COMMENT \'RELATION to glpi_profiles (id)\', name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, value TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE glpi_plugin_workflows_profiles (id INT AUTO_INCREMENT NOT NULL COMMENT \'RELATION to glpi_profiles (id)\', name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, value TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE glpi_plugin_workflows_workflows (id INT AUTO_INCREMENT NOT NULL COMMENT \'RELATION to glpi_profiles (id)\', name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, description TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE glpi_apiclients CHANGE dolog_method dolog_method TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_appliances CHANGE date_mod date_mod DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_authmails CHANGE date_mod date_mod DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_blacklistedmailcontents CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_blacklists CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_budgets CHANGE value value NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_budgettypes CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_calendars CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_calendarsegments CHANGE day day TINYINT(1) DEFAULT 1 NOT NULL COMMENT \'numer of the day based on date(w)\'');
        $this->addSql('ALTER TABLE glpi_certificates_items CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE date_mod date_mod DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_changecosts CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_changes_items RENAME INDEX itemt TO item');
        $this->addSql('ALTER TABLE glpi_changevalidations CHANGE submission_date submission_date DATETIME DEFAULT NULL, CHANGE validation_date validation_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_clusters RENAME INDEX groups_id_tech TO group_id_tech');
        $this->addSql('ALTER TABLE glpi_clustertypes CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE date_mod date_mod DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_computermodels CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_computers CHANGE date_mod date_mod DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_computertypes CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_computervirtualmachines CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_consumableitems CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_consumableitemtypes CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_consumables CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_contacts CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_contacttypes CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_contracts CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_contracttypes CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_crontasklogs CHANGE crontasklogs_id crontasklogs_id INT NOT NULL COMMENT \'id of \'\'start\'\' event\'');
        $this->addSql('ALTER TABLE glpi_crontasks CHANGE lastrun lastrun DATETIME DEFAULT NULL COMMENT \'last run date\', CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_dashboards MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX profileId_userId ON glpi_dashboards');
        $this->addSql('DROP INDEX `PRIMARY` ON glpi_dashboards');
        $this->addSql('ALTER TABLE glpi_dashboards CHANGE name name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE content content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE UNIQUE INDEX id ON glpi_dashboards (id)');
        $this->addSql('ALTER TABLE glpi_dashboards ADD PRIMARY KEY (profileId, userId)');
        $this->addSql('ALTER TABLE glpi_datacenters CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_dcrooms CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_devicebatteries CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_devicecasetypes CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_devicegenerics CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_devicegraphiccards CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_devicenetworkcards CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_devicepowersupplies CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_devicesensors CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_devicesimcards CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_devicesoundcards CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_documentcategories CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_documents_items CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE date date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_documenttypes CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_domainrecords CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_enclosuremodels CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_enclosures CHANGE date_mod date_mod DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_enclosures RENAME INDEX groups_id_tech TO group_id_tech');
        $this->addSql('ALTER TABLE glpi_entities CHANGE date_mod date_mod DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_events RENAME INDEX name TO date');
        $this->addSql('ALTER TABLE glpi_fieldunicities CHANGE fields fields TEXT DEFAULT NULL');
        $this->addSql('DROP INDEX ldap_value ON glpi_groups');
        $this->addSql('DROP INDEX ldap_group_dn ON glpi_groups');
        $this->addSql('CREATE INDEX ldap_value ON glpi_groups (ldap_value(200))');
        $this->addSql('CREATE INDEX ldap_group_dn ON glpi_groups (ldap_group_dn(200))');
        $this->addSql('CREATE INDEX source ON glpi_impactitems (itemtype, items_id)');
        $this->addSql('ALTER TABLE glpi_infocoms CHANGE value value NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE warranty_value warranty_value NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_ipaddresses CHANGE version version TINYINT(1) DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_ipnetworks CHANGE version version TINYINT(1) DEFAULT 0');
        $this->addSql('ALTER TABLE glpi_items_devicebatteries RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_devicecases RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_devicecontrols RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_devicedrives RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_devicefirmwares RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_devicegenerics RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_devicegraphiccards RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_deviceharddrives RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_devicememories RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_devicemotherboards RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_devicenetworkcards RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_devicepcis RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_devicepowersupplies RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_deviceprocessors RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_devicesensors RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_devicesoundcards RENAME INDEX items_id TO computers_id');
        $this->addSql('ALTER TABLE glpi_items_disks CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_itilsolutions RENAME INDEX items_id TO item_id');
        $this->addSql('ALTER TABLE glpi_links CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_links_itemtypes CHANGE itemtype itemtype VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE glpi_monitors CHANGE size size NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_networkequipments CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_oidc_config DROP logout');
        $this->addSql('ALTER TABLE glpi_olalevels CHANGE `match` `match` CHAR(10) DEFAULT NULL COMMENT \'see define.php *_MATCHING constant\'');
        $this->addSql('ALTER TABLE glpi_pdutypes CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE date_mod date_mod DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_peripherals CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_phones CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_printers CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_problemcosts CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_projectcosts CHANGE cost cost NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_projects CHANGE date_mod date_mod DATETIME DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_racks RENAME INDEX groups_id_tech TO group_id_tech');
        $this->addSql('DROP INDEX field_value ON glpi_ruleactions');
        $this->addSql('CREATE INDEX field_value ON glpi_ruleactions (field(50), value(50))');
        $this->addSql('ALTER TABLE glpi_rules CHANGE `match` `match` CHAR(10) DEFAULT NULL COMMENT \'see define.php *_MATCHING constant\'');
        $this->addSql('ALTER TABLE glpi_slalevels CHANGE `match` `match` CHAR(10) DEFAULT NULL COMMENT \'see define.php *_MATCHING constant\'');
        $this->addSql('ALTER TABLE glpi_softwares CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_ticketcosts CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_ticketsatisfactions CHANGE satisfaction satisfaction INT DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_users CHANGE language language CHAR(10) DEFAULT NULL COMMENT \'see define.php CFG_GLPI[language] array\', CHANGE csv_delimiter csv_delimiter CHAR(1) DEFAULT NULL, CHANGE priority_1 priority_1 CHAR(20) DEFAULT NULL, CHANGE priority_2 priority_2 CHAR(20) DEFAULT NULL, CHANGE priority_3 priority_3 CHAR(20) DEFAULT NULL, CHANGE priority_4 priority_4 CHAR(20) DEFAULT NULL, CHANGE priority_5 priority_5 CHAR(20) DEFAULT NULL, CHANGE priority_6 priority_6 CHAR(20) DEFAULT NULL, CHANGE password_forget_token password_forget_token CHAR(40) DEFAULT NULL, CHANGE layout layout CHAR(20) DEFAULT NULL, CHANGE palette palette CHAR(20) DEFAULT NULL, CHANGE access_custom_shortcuts access_custom_shortcuts JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('CREATE INDEX item ON glpi_vobjects (itemtype, items_id)');
    }
}
