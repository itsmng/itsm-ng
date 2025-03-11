<?php

declare(strict_types=1);

namespace Itsmng\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241128091426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    private function switchToDatetime($table, $column, $nullable = true)
    {
        $this->addSql('ALTER TABLE '.$table.' ADD temp_'.$column.' DATETIME DEFAULT NULL');
        $this->addSql('UPDATE '.$table.' SET temp_'.$column.' = '.$column.';');
        $this->addSql('UPDATE '.$table.' SET temp_'.$column.' = "0000-00-00 00:00:00" WHERE temp_'.$column.' IS NULL;');
        $this->addSql('ALTER TABLE '.$table.' DROP COLUMN '.$column.';');
        $this->addSql('ALTER TABLE '.$table.' CHANGE temp_'.$column.' '.$column.' DATETIME' . ($nullable ? '' : ' NOT NULL'));
        $this->addSql('CREATE INDEX '.$column.' ON '.$table.' ('.$column.')');
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->switchToDatetime('glpi_alerts', 'date');
        $this->addSql('ALTER TABLE glpi_apiclients CHANGE dolog_method dolog_method SMALLINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE glpi_appliances ADD date_creation DATETIME NOT NULL');
        $this->addSql('ALTER TABLE glpi_authmails ADD date_creation DATETIME NOT NULL');
        $this->addSql('ALTER TABLE glpi_calendarsegments CHANGE day day TINYINT(1) DEFAULT 1 NOT NULL COMMENT \'number of the day based on date(w)\'');
        $this->addSql('ALTER TABLE glpi_clusters RENAME INDEX group_id_tech TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_crontasklogs CHANGE crontasklogs_id crontasklogs_id INT NOT NULL COMMENT \'id of "start" event\'');
        $this->switchToDatetime('glpi_crontasklogs', 'date');
        $this->switchToDatetime('glpi_crontasks', 'lastrun');
        $this->switchToDatetime('glpi_changevalidations', 'submission_date');
        $this->switchToDatetime('glpi_changevalidations', 'validation_date');
        $this->addSql('ALTER TABLE glpi_dashboards DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE glpi_dashboards CHANGE id id INT AUTO_INCREMENT NOT NULL PRIMARY KEY');
        $this->addSql('DROP INDEX id ON glpi_dashboards');
        $this->addSql('CREATE UNIQUE INDEX profileId_userId ON glpi_dashboards (profileId, userId)');
        $this->addSql('ALTER TABLE glpi_dashboards CHANGE name name VARCHAR(100) NOT NULL, CHANGE content content LONGTEXT NOT NULL');
        $this->switchToDatetime('glpi_documents_items', 'date');
        $this->addSql('ALTER TABLE glpi_enclosures RENAME INDEX group_id_tech TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_events RENAME INDEX date TO name');
        $this->addSql('ALTER TABLE glpi_fieldunicities CHANGE fields fields LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX ldap_value ON glpi_groups');
        $this->addSql('DROP INDEX ldap_group_dn ON glpi_groups');
        $this->addSql('CREATE FULLTEXT INDEX ldap_value ON glpi_groups (ldap_value)');
        $this->addSql('CREATE FULLTEXT INDEX ldap_group_dn ON glpi_groups (ldap_group_dn)');
        $this->addSql('DROP INDEX source ON glpi_impactitems');
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
        $this->addSql('ALTER TABLE glpi_itilsolutions RENAME INDEX item_id TO items_id');
        $this->addSql('ALTER TABLE glpi_links_itemtypes CHANGE itemtype itemtype VARCHAR(100) DEFAULT \'\' NOT NULL');
        $this->switchToDatetime('glpi_notimportedemails', 'date');
        $this->addSql('ALTER TABLE glpi_oidc_config ADD logout VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE glpi_olalevels CHANGE `match` `match` VARCHAR(10) DEFAULT NULL COMMENT \'see define.php *_MATCHING constant\'');
        $this->addSql('ALTER TABLE glpi_racks RENAME INDEX group_id_tech TO groups_id_tech');
        $this->addSql('DROP INDEX field_value ON glpi_ruleactions');
        $this->addSql('CREATE INDEX field_value ON glpi_ruleactions (field, value)');
        $this->addSql('ALTER TABLE glpi_rules CHANGE `match` `match` VARCHAR(10) DEFAULT NULL COMMENT \'see define.php *_MATCHING constant\'');
        $this->addSql('ALTER TABLE glpi_slalevels CHANGE `match` `match` VARCHAR(10) DEFAULT NULL COMMENT \'see define.php *_MATCHING constant\'');
        $this->switchToDatetime('glpi_tickets', 'date');
        $this->switchToDatetime('glpi_tickets', 'closedate');
        $this->switchToDatetime('glpi_tickets', 'solvedate');
        $this->switchToDatetime('glpi_tickets', 'time_to_resolve');
        $this->switchToDatetime('glpi_tickets', 'time_to_own');
        $this->switchToDatetime('glpi_tickets', 'begin_waiting_date');
        $this->switchToDatetime('glpi_tickets', 'ola_ttr_begin_date');
        $this->switchToDatetime('glpi_tickets', 'internal_time_to_resolve');
        $this->switchToDatetime('glpi_tickets', 'internal_time_to_own');
        $this->addSql('ALTER TABLE glpi_users CHANGE language language VARCHAR(10) DEFAULT NULL COMMENT \'see define.php CFG_GLPI[language] array\', CHANGE csv_delimiter csv_delimiter VARCHAR(1) DEFAULT NULL, CHANGE priority_1 priority_1 VARCHAR(20) DEFAULT NULL, CHANGE priority_2 priority_2 VARCHAR(20) DEFAULT NULL, CHANGE priority_3 priority_3 VARCHAR(20) DEFAULT NULL, CHANGE priority_4 priority_4 VARCHAR(20) DEFAULT NULL, CHANGE priority_5 priority_5 VARCHAR(20) DEFAULT NULL, CHANGE priority_6 priority_6 VARCHAR(20) DEFAULT NULL, CHANGE password_forget_token password_forget_token VARCHAR(40) DEFAULT NULL, CHANGE layout layout VARCHAR(20) DEFAULT NULL, CHANGE palette palette VARCHAR(20) DEFAULT NULL, CHANGE access_custom_shortcuts access_custom_shortcuts LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX item ON glpi_vobjects');
        $tableToSwitchToDatetime = [
            'glpi_appliances', 'glpi_authmails',
            'glpi_blacklistedmailcontents', 'glpi_blacklists', 'glpi_budgettypes', 'glpi_calendars',
            'glpi_certificates_items', 'glpi_clustertypes', 'glpi_computers', 'glpi_computermodels',
            'glpi_computertypes', 'glpi_computervirtualmachines', 'glpi_consumableitems', 'glpi_consumableitemtypes',
            'glpi_consumables', 'glpi_contacts', 'glpi_contacttypes', 'glpi_contracts', 'glpi_contracttypes',
            'glpi_crontasks', 'glpi_datacenters', 'glpi_dcrooms', 'glpi_devicebatteries', 'glpi_devicecasetypes',
            'glpi_devicegenerics', 'glpi_devicegraphiccards', 'glpi_devicenetworkcards', 'glpi_devicesensors',
            'glpi_devicesimcards', 'glpi_devicesoundcards', 'glpi_documentcategories', 'glpi_documents_items',
            'glpi_documenttypes', 'glpi_domainrecords', 'glpi_enclosuremodels', 'glpi_enclosures', 'glpi_entities',
            'glpi_devicepowersupplies', 'glpi_items_disks', 'glpi_links', 'glpi_pdutypes', 'glpi_projects', 'glpi_tickets',
        ];
        foreach ($tableToSwitchToDatetime as $table) {
            $this->switchToDatetime($table, 'date_creation', false);
            $this->switchToDatetime($table, 'date_mod', false);
        }
    }

    public function down(Schema $schema): void
    {
        // this is the initial version
    }
}
