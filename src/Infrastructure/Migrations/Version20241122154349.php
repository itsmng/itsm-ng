<?php

declare(strict_types=1);

namespace Itsmng\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241122154349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_alerts (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, type INT DEFAULT 0 NOT NULL COMMENT \'see define.php ALERT_* constant\', date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX type (type), UNIQUE INDEX unicity (itemtype, items_id, type), INDEX date (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_apiclients (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, is_active TINYINT(1) DEFAULT 0 NOT NULL, ipv4_range_start BIGINT DEFAULT NULL, ipv4_range_end BIGINT DEFAULT NULL, ipv6 VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, app_token VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, app_token_date DATETIME DEFAULT NULL, dolog_method TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX date_mod (date_mod), INDEX is_active (is_active), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_applianceenvironments (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_appliances (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, appliancetypes_id INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, manufacturers_id INT DEFAULT 0 NOT NULL, applianceenvironments_id INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, users_id_tech INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, states_id INT DEFAULT 0 NOT NULL, externalidentifier VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_helpdesk_visible TINYINT(1) DEFAULT 1 NOT NULL, INDEX applianceenvironments_id (applianceenvironments_id), INDEX appliancetypes_id (appliancetypes_id), INDEX entities_id (entities_id), INDEX otherserial (otherserial), INDEX groups_id_tech (groups_id_tech), INDEX users_id (users_id), INDEX locations_id (locations_id), INDEX name (name), INDEX is_helpdesk_visible (is_helpdesk_visible), INDEX states_id (states_id), INDEX users_id_tech (users_id_tech), INDEX manufacturers_id (manufacturers_id), INDEX is_deleted (is_deleted), UNIQUE INDEX unicity (externalidentifier), INDEX serial (serial), INDEX groups_id (groups_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_appliances_items (id INT AUTO_INCREMENT NOT NULL, appliances_id INT DEFAULT 0 NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, INDEX appliances_id (appliances_id), UNIQUE INDEX unicity (appliances_id, items_id, itemtype), INDEX item (itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_appliances_items_relations (id INT AUTO_INCREMENT NOT NULL, appliances_items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, INDEX item (itemtype, items_id), INDEX appliances_items_id (appliances_items_id), INDEX itemtype (itemtype), INDEX items_id (items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_appliancetypes (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, externalidentifier VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, UNIQUE INDEX externalidentifier (externalidentifier), INDEX name (name), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_authldapreplicates (id INT AUTO_INCREMENT NOT NULL, authldaps_id INT DEFAULT 0 NOT NULL, host VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, port INT DEFAULT 389 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX authldaps_id (authldaps_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_authldaps (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, host VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, basedn VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, rootdn VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, port INT DEFAULT 389 NOT NULL, `condition` TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, login_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'uid\' COLLATE `utf8mb3_unicode_ci`, sync_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, use_tls TINYINT(1) DEFAULT 0 NOT NULL, group_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, group_condition TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, group_search_type INT DEFAULT 0 NOT NULL, group_member_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, email1_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, realname_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, firstname_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, phone_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, phone2_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, mobile_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, use_dn TINYINT(1) DEFAULT 1 NOT NULL, time_offset INT DEFAULT 0 NOT NULL COMMENT \'in seconds\', deref_option INT DEFAULT 0 NOT NULL, title_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, category_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, language_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entity_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entity_condition TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_default TINYINT(1) DEFAULT 0 NOT NULL, is_active TINYINT(1) DEFAULT 0 NOT NULL, rootdn_passwd VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, registration_number_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, email2_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, email3_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, email4_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, location_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, responsible_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, pagesize INT DEFAULT 0 NOT NULL, ldap_maxlimit INT DEFAULT 0 NOT NULL, can_support_pagesize TINYINT(1) DEFAULT 0 NOT NULL, picture_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_creation DATETIME DEFAULT NULL, inventory_domain VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX date_creation (date_creation), INDEX date_mod (date_mod), INDEX sync_field (sync_field), INDEX is_default (is_default), INDEX is_active (is_active), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_authmails (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, connect_string VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, host VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_active TINYINT(1) DEFAULT 0 NOT NULL, INDEX is_active (is_active), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_autoupdatesystems (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_blacklistedmailcontents (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, content TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_blacklists (id INT AUTO_INCREMENT NOT NULL, type INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, value VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX type (type), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_budgets (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, begin_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, value NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, locations_id INT DEFAULT 0 NOT NULL, budgettypes_id INT DEFAULT 0 NOT NULL, INDEX name (name), INDEX budgettypes_id (budgettypes_id), INDEX date_mod (date_mod), INDEX begin_date (begin_date), INDEX is_recursive (is_recursive), INDEX date_creation (date_creation), INDEX end_date (end_date), INDEX entities_id (entities_id), INDEX locations_id (locations_id), INDEX is_template (is_template), INDEX is_deleted (is_deleted), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_budgettypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_businesscriticities (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, businesscriticities_id INT DEFAULT 0 NOT NULL, completename TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, level INT DEFAULT 0 NOT NULL, ancestors_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, sons_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, UNIQUE INDEX unicity (businesscriticities_id, name), INDEX date_creation (date_creation), INDEX name (name), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_calendars (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, cache_duration TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX entities_id (entities_id), INDEX is_recursive (is_recursive), INDEX date_mod (date_mod), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_calendars_holidays (id INT AUTO_INCREMENT NOT NULL, calendars_id INT DEFAULT 0 NOT NULL, holidays_id INT DEFAULT 0 NOT NULL, INDEX holidays_id (holidays_id), UNIQUE INDEX unicity (calendars_id, holidays_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_calendarsegments (id INT AUTO_INCREMENT NOT NULL, calendars_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, day TINYINT(1) DEFAULT 1 NOT NULL COMMENT \'numer of the day based on date(w)\', begin TIME DEFAULT NULL, end TIME DEFAULT NULL, INDEX calendars_id (calendars_id), INDEX day (day), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_cartridgeitems (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ref VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, cartridgeitemtypes_id INT DEFAULT 0 NOT NULL, manufacturers_id INT DEFAULT 0 NOT NULL, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, alarm_threshold INT DEFAULT 10 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX alarm_threshold (alarm_threshold), INDEX users_id_tech (users_id_tech), INDEX entities_id (entities_id), INDEX groups_id_tech (groups_id_tech), INDEX cartridgeitemtypes_id (cartridgeitemtypes_id), INDEX manufacturers_id (manufacturers_id), INDEX date_mod (date_mod), INDEX is_deleted (is_deleted), INDEX locations_id (locations_id), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_cartridgeitems_printermodels (id INT AUTO_INCREMENT NOT NULL, cartridgeitems_id INT DEFAULT 0 NOT NULL, printermodels_id INT DEFAULT 0 NOT NULL, INDEX cartridgeitems_id (cartridgeitems_id), UNIQUE INDEX unicity (printermodels_id, cartridgeitems_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_cartridgeitemtypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_cartridges (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, cartridgeitems_id INT DEFAULT 0 NOT NULL, printers_id INT DEFAULT 0 NOT NULL, date_in DATE DEFAULT NULL, date_use DATE DEFAULT NULL, date_out DATE DEFAULT NULL, pages INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX cartridgeitems_id (cartridgeitems_id), INDEX date_creation (date_creation), INDEX printers_id (printers_id), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_certificates (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, certificatetypes_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to glpi_certificatetypes (id)\', dns_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, dns_suffix VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id_tech INT DEFAULT 0 NOT NULL COMMENT \'RELATION to glpi_users (id)\', groups_id_tech INT DEFAULT 0 NOT NULL COMMENT \'RELATION to glpi_groups (id)\', locations_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to glpi_locations (id)\', manufacturers_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to glpi_manufacturers (id)\', contact VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, contact_num VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, is_autosign TINYINT(1) DEFAULT 0 NOT NULL, date_expiration DATE DEFAULT NULL, states_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to states (id)\', command TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, certificate_request TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, certificate_item TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_creation DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, INDEX is_deleted (is_deleted), INDEX name (name), INDEX date_mod (date_mod), INDEX manufacturers_id (manufacturers_id), INDEX groups_id (groups_id), INDEX certificatetypes_id (certificatetypes_id), INDEX entities_id (entities_id), INDEX states_id (states_id), INDEX users_id (users_id), INDEX users_id_tech (users_id_tech), INDEX is_template (is_template), INDEX date_creation (date_creation), INDEX locations_id (locations_id), INDEX groups_id_tech (groups_id_tech), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_certificates_items (id INT AUTO_INCREMENT NOT NULL, certificates_id INT DEFAULT 0 NOT NULL, items_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to various tables, according to itemtype (id)\', itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'see .class.php file\', date_creation DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, INDEX item (itemtype, items_id), INDEX date_creation (date_creation), INDEX date_mod (date_mod), INDEX device (items_id, itemtype), UNIQUE INDEX unicity (certificates_id, itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_certificatetypes (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_creation DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX entities_id (entities_id), INDEX date_mod (date_mod), INDEX is_recursive (is_recursive), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_changecosts (id INT AUTO_INCREMENT NOT NULL, changes_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, begin_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, actiontime INT DEFAULT 0 NOT NULL, cost_time NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, cost_fixed NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, cost_material NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, budgets_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX budgets_id (budgets_id), INDEX end_date (end_date), INDEX name (name), INDEX entities_id (entities_id), INDEX changes_id (changes_id), INDEX is_recursive (is_recursive), INDEX begin_date (begin_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_changes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, status INT DEFAULT 1 NOT NULL, content LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date DATETIME DEFAULT NULL, solvedate DATETIME DEFAULT NULL, closedate DATETIME DEFAULT NULL, time_to_resolve DATETIME DEFAULT NULL, users_id_recipient INT DEFAULT 0 NOT NULL, users_id_lastupdater INT DEFAULT 0 NOT NULL, urgency INT DEFAULT 1 NOT NULL, impact INT DEFAULT 1 NOT NULL, priority INT DEFAULT 1 NOT NULL, itilcategories_id INT DEFAULT 0 NOT NULL, impactcontent LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, controlistcontent LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, rolloutplancontent LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, backoutplancontent LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, checklistcontent LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, global_validation INT DEFAULT 1 NOT NULL, validation_percent INT DEFAULT 0 NOT NULL, actiontime INT DEFAULT 0 NOT NULL, begin_waiting_date DATETIME DEFAULT NULL, waiting_duration INT DEFAULT 0 NOT NULL, close_delay_stat INT DEFAULT 0 NOT NULL, solve_delay_stat INT DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, INDEX time_to_resolve (time_to_resolve), INDEX solvedate (solvedate), INDEX date_mod (date_mod), INDEX closedate (closedate), INDEX is_recursive (is_recursive), INDEX global_validation (global_validation), INDEX urgency (urgency), INDEX itilcategories_id (itilcategories_id), INDEX status (status), INDEX is_deleted (is_deleted), INDEX name (name), INDEX users_id_lastupdater (users_id_lastupdater), INDEX impact (impact), INDEX users_id_recipient (users_id_recipient), INDEX priority (priority), INDEX date (date), INDEX entities_id (entities_id), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_changes_groups (id INT AUTO_INCREMENT NOT NULL, changes_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, type INT DEFAULT 1 NOT NULL, INDEX `group` (groups_id, type), UNIQUE INDEX unicity (changes_id, type, groups_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_changes_items (id INT AUTO_INCREMENT NOT NULL, changes_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, INDEX item (itemtype, items_id), UNIQUE INDEX unicity (changes_id, itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_changes_problems (id INT AUTO_INCREMENT NOT NULL, changes_id INT DEFAULT 0 NOT NULL, problems_id INT DEFAULT 0 NOT NULL, UNIQUE INDEX unicity (changes_id, problems_id), INDEX problems_id (problems_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_changes_suppliers (id INT AUTO_INCREMENT NOT NULL, changes_id INT DEFAULT 0 NOT NULL, suppliers_id INT DEFAULT 0 NOT NULL, type INT DEFAULT 1 NOT NULL, use_notification TINYINT(1) DEFAULT 0 NOT NULL, alternative_email VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX `group` (suppliers_id, type), UNIQUE INDEX unicity (changes_id, type, suppliers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_changes_tickets (id INT AUTO_INCREMENT NOT NULL, changes_id INT DEFAULT 0 NOT NULL, tickets_id INT DEFAULT 0 NOT NULL, INDEX tickets_id (tickets_id), UNIQUE INDEX unicity (changes_id, tickets_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_changes_users (id INT AUTO_INCREMENT NOT NULL, changes_id INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, type INT DEFAULT 1 NOT NULL, use_notification TINYINT(1) DEFAULT 0 NOT NULL, alternative_email VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, UNIQUE INDEX unicity (changes_id, type, users_id, alternative_email), INDEX user (users_id, type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_changetasks (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, changes_id INT DEFAULT 0 NOT NULL, taskcategories_id INT DEFAULT 0 NOT NULL, state INT DEFAULT 0 NOT NULL, date DATETIME DEFAULT NULL, begin DATETIME DEFAULT NULL, end DATETIME DEFAULT NULL, users_id INT DEFAULT 0 NOT NULL, users_id_editor INT DEFAULT 0 NOT NULL, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, content LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, actiontime INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, tasktemplates_id INT DEFAULT 0 NOT NULL, timeline_position TINYINT(1) DEFAULT 0 NOT NULL, is_private TINYINT(1) DEFAULT 0 NOT NULL, INDEX begin (begin), INDEX date (date), INDEX users_id_editor (users_id_editor), INDEX changes_id (changes_id), INDEX is_private (is_private), INDEX end (end), INDEX date_mod (date_mod), INDEX users_id_tech (users_id_tech), INDEX state (state), INDEX taskcategories_id (taskcategories_id), INDEX date_creation (date_creation), INDEX groups_id_tech (groups_id_tech), INDEX users_id (users_id), UNIQUE INDEX uuid (uuid), INDEX tasktemplates_id (tasktemplates_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_changetemplatehiddenfields (id INT AUTO_INCREMENT NOT NULL, changetemplates_id INT DEFAULT 0 NOT NULL, num INT DEFAULT 0 NOT NULL, INDEX changetemplates_id (changetemplates_id), UNIQUE INDEX unicity (changetemplates_id, num), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_changetemplatemandatoryfields (id INT AUTO_INCREMENT NOT NULL, changetemplates_id INT DEFAULT 0 NOT NULL, num INT DEFAULT 0 NOT NULL, UNIQUE INDEX unicity (changetemplates_id, num), INDEX changetemplates_id (changetemplates_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_changetemplatepredefinedfields (id INT AUTO_INCREMENT NOT NULL, changetemplates_id INT DEFAULT 0 NOT NULL, num INT DEFAULT 0 NOT NULL, value TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX changetemplates_id (changetemplates_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_changetemplates (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), INDEX entities_id (entities_id), INDEX is_recursive (is_recursive), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_changevalidations (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, changes_id INT DEFAULT 0 NOT NULL, users_id_validate INT DEFAULT 0 NOT NULL, comment_submission TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment_validation TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, status INT DEFAULT 2 NOT NULL, submission_date DATETIME DEFAULT NULL, validation_date DATETIME DEFAULT NULL, timeline_position TINYINT(1) DEFAULT 0 NOT NULL, INDEX validation_date (validation_date), INDEX users_id_validate (users_id_validate), INDEX entities_id (entities_id), INDEX status (status), INDEX changes_id (changes_id), INDEX is_recursive (is_recursive), INDEX submission_date (submission_date), INDEX users_id (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_clusters (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, uuid VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, version VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to states (id)\', comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, clustertypes_id INT DEFAULT 0 NOT NULL, autoupdatesystems_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX states_id (states_id), INDEX users_id_tech (users_id_tech), INDEX is_recursive (is_recursive), INDEX clustertypes_id (clustertypes_id), INDEX group_id_tech (groups_id_tech), INDEX autoupdatesystems_id (autoupdatesystems_id), INDEX is_deleted (is_deleted), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_clustertypes (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_creation DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX entities_id (entities_id), INDEX is_recursive (is_recursive), INDEX date_creation (date_creation), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_computerantiviruses (id INT AUTO_INCREMENT NOT NULL, computers_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, antivirus_version VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, signature_version VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_active TINYINT(1) DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_uptodate TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, date_expiration DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX is_uptodate (is_uptodate), INDEX antivirus_version (antivirus_version), INDEX date_expiration (date_expiration), INDEX is_dynamic (is_dynamic), INDEX signature_version (signature_version), INDEX date_mod (date_mod), INDEX is_deleted (is_deleted), INDEX is_active (is_active), INDEX name (name), INDEX date_creation (date_creation), INDEX computers_id (computers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_computermodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, weight INT DEFAULT 0 NOT NULL, required_units INT DEFAULT 1 NOT NULL, depth DOUBLE PRECISION DEFAULT \'1\' NOT NULL, power_connections INT DEFAULT 0 NOT NULL, power_consumption INT DEFAULT 0 NOT NULL, is_half_rack TINYINT(1) DEFAULT 0 NOT NULL, picture_front TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, picture_rear TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX product_number (product_number), INDEX name (name), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_computers (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, contact VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, contact_num VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, autoupdatesystems_id INT DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, networks_id INT DEFAULT 0 NOT NULL, computermodels_id INT DEFAULT 0 NOT NULL, computertypes_id INT DEFAULT 0 NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\', uuid VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_creation DATETIME DEFAULT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX date_creation (date_creation), INDEX autoupdatesystems_id (autoupdatesystems_id), INDEX serial (serial), INDEX date_mod (date_mod), INDEX is_deleted (is_deleted), INDEX states_id (states_id), INDEX users_id (users_id), INDEX is_recursive (is_recursive), INDEX entities_id (entities_id), INDEX otherserial (otherserial), INDEX name (name), INDEX groups_id_tech (groups_id_tech), INDEX users_id_tech (users_id_tech), INDEX locations_id (locations_id), INDEX manufacturers_id (manufacturers_id), INDEX uuid (uuid), INDEX is_template (is_template), INDEX is_dynamic (is_dynamic), INDEX computertypes_id (computertypes_id), INDEX networks_id (networks_id), INDEX computermodels_id (computermodels_id), INDEX groups_id (groups_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_computers_items (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to various table, according to itemtype (ID)\', computers_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, INDEX is_dynamic (is_dynamic), INDEX item (itemtype, items_id), INDEX is_deleted (is_deleted), INDEX computers_id (computers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_computertypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_computervirtualmachines (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, computers_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, virtualmachinestates_id INT DEFAULT 0 NOT NULL, virtualmachinesystems_id INT DEFAULT 0 NOT NULL, virtualmachinetypes_id INT DEFAULT 0 NOT NULL, uuid VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, vcpu INT DEFAULT 0 NOT NULL, ram VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX virtualmachinestates_id (virtualmachinestates_id), INDEX computers_id (computers_id), INDEX date_mod (date_mod), INDEX is_deleted (is_deleted), INDEX virtualmachinesystems_id (virtualmachinesystems_id), INDEX entities_id (entities_id), INDEX date_creation (date_creation), INDEX is_dynamic (is_dynamic), INDEX vcpu (vcpu), INDEX name (name), INDEX uuid (uuid), INDEX ram (ram), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_configs (id INT AUTO_INCREMENT NOT NULL, context VARCHAR(150) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, name VARCHAR(150) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, value TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, UNIQUE INDEX unicity (context, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_consumableitems (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ref VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, consumableitemtypes_id INT DEFAULT 0 NOT NULL, manufacturers_id INT DEFAULT 0 NOT NULL, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, alarm_threshold INT DEFAULT 10 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX locations_id (locations_id), INDEX name (name), INDEX date_creation (date_creation), INDEX alarm_threshold (alarm_threshold), INDEX users_id_tech (users_id_tech), INDEX entities_id (entities_id), INDEX otherserial (otherserial), INDEX groups_id_tech (groups_id_tech), INDEX consumableitemtypes_id (consumableitemtypes_id), INDEX manufacturers_id (manufacturers_id), INDEX date_mod (date_mod), INDEX is_deleted (is_deleted), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_consumableitemtypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_consumables (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, consumableitems_id INT DEFAULT 0 NOT NULL, date_in DATE DEFAULT NULL, date_out DATE DEFAULT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX consumableitems_id (consumableitems_id), INDEX date_mod (date_mod), INDEX entities_id (entities_id), INDEX date_in (date_in), INDEX date_creation (date_creation), INDEX item (itemtype, items_id), INDEX date_out (date_out), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_contacts (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, firstname VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, phone VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, phone2 VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, mobile VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, fax VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, contacttypes_id INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, usertitles_id INT DEFAULT 0 NOT NULL, address TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, postcode VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, town VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, state VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, country VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX is_deleted (is_deleted), INDEX name (name), INDEX usertitles_id (usertitles_id), INDEX entities_id (entities_id), INDEX date_mod (date_mod), INDEX contacttypes_id (contacttypes_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_contacts_suppliers (id INT AUTO_INCREMENT NOT NULL, suppliers_id INT DEFAULT 0 NOT NULL, contacts_id INT DEFAULT 0 NOT NULL, INDEX contacts_id (contacts_id), UNIQUE INDEX unicity (suppliers_id, contacts_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_contacttypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_contractcosts (id INT AUTO_INCREMENT NOT NULL, contracts_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, begin_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, cost NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, budgets_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX budgets_id (budgets_id), INDEX end_date (end_date), INDEX name (name), INDEX entities_id (entities_id), INDEX contracts_id (contracts_id), INDEX is_recursive (is_recursive), INDEX begin_date (begin_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_contracts (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, num VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, contracttypes_id INT DEFAULT 0 NOT NULL, begin_date DATE DEFAULT NULL, duration INT DEFAULT 0 NOT NULL, notice INT DEFAULT 0 NOT NULL, periodicity INT DEFAULT 0 NOT NULL, billing INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, accounting_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, week_begin_hour TIME DEFAULT \'00:00:00\' NOT NULL, week_end_hour TIME DEFAULT \'00:00:00\' NOT NULL, saturday_begin_hour TIME DEFAULT \'00:00:00\' NOT NULL, saturday_end_hour TIME DEFAULT \'00:00:00\' NOT NULL, use_saturday TINYINT(1) DEFAULT 0 NOT NULL, monday_begin_hour TIME DEFAULT \'00:00:00\' NOT NULL, monday_end_hour TIME DEFAULT \'00:00:00\' NOT NULL, use_monday TINYINT(1) DEFAULT 0 NOT NULL, max_links_allowed INT DEFAULT 0 NOT NULL, alert INT DEFAULT 0 NOT NULL, renewal INT DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_template TINYINT(1) DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX states_id (states_id), INDEX use_monday (use_monday), INDEX contracttypes_id (contracttypes_id), INDEX date_mod (date_mod), INDEX use_saturday (use_saturday), INDEX entities_id (entities_id), INDEX begin_date (begin_date), INDEX date_creation (date_creation), INDEX alert (alert), INDEX is_deleted (is_deleted), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_contracts_items (id INT AUTO_INCREMENT NOT NULL, contracts_id INT DEFAULT 0 NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, INDEX item (itemtype, items_id), INDEX FK_device (items_id, itemtype), UNIQUE INDEX unicity (contracts_id, itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_contracts_suppliers (id INT AUTO_INCREMENT NOT NULL, suppliers_id INT DEFAULT 0 NOT NULL, contracts_id INT DEFAULT 0 NOT NULL, INDEX contracts_id (contracts_id), UNIQUE INDEX unicity (suppliers_id, contracts_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_contracttypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_crontasklogs (id INT AUTO_INCREMENT NOT NULL, crontasks_id INT NOT NULL, crontasklogs_id INT NOT NULL COMMENT \'id of \'\'start\'\' event\', date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, state INT NOT NULL COMMENT \'0:start, 1:run, 2:stop\', elapsed DOUBLE PRECISION NOT NULL COMMENT \'time elapsed since start\', volume INT NOT NULL COMMENT \'for statistics\', content VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'message\', INDEX crontasklogs_id_state (crontasklogs_id, state), INDEX date (date), INDEX crontasks_id (crontasks_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_crontasks (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, name VARCHAR(150) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'task name\', frequency INT NOT NULL COMMENT \'second between launch\', param INT DEFAULT NULL COMMENT \'task specify parameter\', state INT DEFAULT 1 NOT NULL COMMENT \'0:disabled, 1:waiting, 2:running\', mode INT DEFAULT 1 NOT NULL COMMENT \'1:internal, 2:external\', allowmode INT DEFAULT 3 NOT NULL COMMENT \'1:internal, 2:external, 3:both\', hourmin INT DEFAULT 0 NOT NULL, hourmax INT DEFAULT 24 NOT NULL, logs_lifetime INT DEFAULT 30 NOT NULL COMMENT \'number of days\', lastrun DATETIME DEFAULT NULL COMMENT \'last run date\', lastcode INT DEFAULT NULL COMMENT \'last run return code\', comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, UNIQUE INDEX unicity (itemtype, name), INDEX date_creation (date_creation), INDEX mode (mode), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'Task run by internal / external cron.\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_dashboards (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, profileId INT DEFAULT 0 NOT NULL, userId INT DEFAULT 0 NOT NULL, UNIQUE INDEX id (id), PRIMARY KEY(profileId, userId)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_datacenters (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX is_recursive (is_recursive), INDEX locations_id (locations_id), INDEX is_deleted (is_deleted), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_dcrooms (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, vis_cols INT DEFAULT NULL, vis_rows INT DEFAULT NULL, blueprint TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, datacenters_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX locations_id (locations_id), INDEX datacenters_id (datacenters_id), INDEX entities_id (entities_id), INDEX is_deleted (is_deleted), INDEX is_recursive (is_recursive), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicebatteries (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, voltage INT DEFAULT NULL, capacity INT DEFAULT NULL, devicebatterytypes_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, devicebatterymodels_id INT DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX devicebatterymodels_id (devicebatterymodels_id), INDEX is_recursive (is_recursive), INDEX designation (designation), INDEX devicebatterytypes_id (devicebatterytypes_id), INDEX date_mod (date_mod), INDEX manufacturers_id (manufacturers_id), INDEX date_creation (date_creation), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicebatterymodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), INDEX product_number (product_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicebatterytypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicecasemodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), INDEX product_number (product_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicecases (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicecasetypes_id INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, devicecasemodels_id INT DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX entities_id (entities_id), INDEX designation (designation), INDEX devicecasemodels_id (devicecasemodels_id), INDEX is_recursive (is_recursive), INDEX manufacturers_id (manufacturers_id), INDEX date_mod (date_mod), INDEX devicecasetypes_id (devicecasetypes_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicecasetypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicecontrolmodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), INDEX product_number (product_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicecontrols (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_raid TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, interfacetypes_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, devicecontrolmodels_id INT DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX entities_id (entities_id), INDEX designation (designation), INDEX devicecontrolmodels_id (devicecontrolmodels_id), INDEX is_recursive (is_recursive), INDEX manufacturers_id (manufacturers_id), INDEX date_mod (date_mod), INDEX interfacetypes_id (interfacetypes_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicedrivemodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), INDEX product_number (product_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicedrives (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_writer TINYINT(1) DEFAULT 1 NOT NULL, speed VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, interfacetypes_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, devicedrivemodels_id INT DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX entities_id (entities_id), INDEX designation (designation), INDEX devicedrivemodels_id (devicedrivemodels_id), INDEX is_recursive (is_recursive), INDEX manufacturers_id (manufacturers_id), INDEX date_mod (date_mod), INDEX interfacetypes_id (interfacetypes_id), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicefirmwaremodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX product_number (product_number), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicefirmwares (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, date DATE DEFAULT NULL, version VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicefirmwaretypes_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, devicefirmwaremodels_id INT DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX devicefirmwaretypes_id (devicefirmwaretypes_id), INDEX date_mod (date_mod), INDEX manufacturers_id (manufacturers_id), INDEX date_creation (date_creation), INDEX entities_id (entities_id), INDEX devicefirmwaremodels_id (devicefirmwaremodels_id), INDEX is_recursive (is_recursive), INDEX designation (designation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicefirmwaretypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX name (name), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicegenericmodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX product_number (product_number), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicegenerics (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicegenerictypes_id INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, devicegenericmodels_id INT DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX is_recursive (is_recursive), INDEX manufacturers_id (manufacturers_id), INDEX date_creation (date_creation), INDEX locations_id (locations_id), INDEX devicegenerictypes_id (devicegenerictypes_id), INDEX devicegenericmodels_id (devicegenericmodels_id), INDEX states_id (states_id), INDEX entities_id (entities_id), INDEX designation (designation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicegenerictypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicegraphiccardmodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX product_number (product_number), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicegraphiccards (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, interfacetypes_id INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, memory_default INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, devicegraphiccardmodels_id INT DEFAULT NULL, chipset VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX is_recursive (is_recursive), INDEX manufacturers_id (manufacturers_id), INDEX devicegraphiccardmodels_id (devicegraphiccardmodels_id), INDEX chipset (chipset), INDEX interfacetypes_id (interfacetypes_id), INDEX date_mod (date_mod), INDEX entities_id (entities_id), INDEX designation (designation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_deviceharddrivemodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX product_number (product_number), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_deviceharddrives (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, rpm VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, interfacetypes_id INT DEFAULT 0 NOT NULL, cache VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, capacity_default INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, deviceharddrivemodels_id INT DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX deviceharddrivemodels_id (deviceharddrivemodels_id), INDEX is_recursive (is_recursive), INDEX manufacturers_id (manufacturers_id), INDEX date_mod (date_mod), INDEX interfacetypes_id (interfacetypes_id), INDEX date_creation (date_creation), INDEX entities_id (entities_id), INDEX designation (designation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicememories (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, frequence VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, size_default INT DEFAULT 0 NOT NULL, devicememorytypes_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, devicememorymodels_id INT DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX manufacturers_id (manufacturers_id), INDEX date_mod (date_mod), INDEX devicememorytypes_id (devicememorytypes_id), INDEX date_creation (date_creation), INDEX entities_id (entities_id), INDEX designation (designation), INDEX devicememorymodels_id (devicememorymodels_id), INDEX is_recursive (is_recursive), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicememorymodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), INDEX product_number (product_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicememorytypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX name (name), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicemotherboardmodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX product_number (product_number), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicemotherboards (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, chipset VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, devicemotherboardmodels_id INT DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX entities_id (entities_id), INDEX devicemotherboardmodels_id (devicemotherboardmodels_id), INDEX is_recursive (is_recursive), INDEX designation (designation), INDEX date_mod (date_mod), INDEX manufacturers_id (manufacturers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicenetworkcardmodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), INDEX product_number (product_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicenetworkcards (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, bandwidth VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, mac_default VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, devicenetworkcardmodels_id INT DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX is_recursive (is_recursive), INDEX designation (designation), INDEX date_mod (date_mod), INDEX manufacturers_id (manufacturers_id), INDEX date_creation (date_creation), INDEX entities_id (entities_id), INDEX devicenetworkcardmodels_id (devicenetworkcardmodels_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicepcimodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), INDEX product_number (product_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicepcis (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, devicenetworkcardmodels_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, devicepcimodels_id INT DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX devicenetworkcardmodels_id (devicenetworkcardmodels_id), INDEX devicepcimodels_id (devicepcimodels_id), INDEX is_recursive (is_recursive), INDEX entities_id (entities_id), INDEX designation (designation), INDEX date_mod (date_mod), INDEX manufacturers_id (manufacturers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicepowersupplies (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, power VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_atx TINYINT(1) DEFAULT 1 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, devicepowersupplymodels_id INT DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX manufacturers_id (manufacturers_id), INDEX date_creation (date_creation), INDEX entities_id (entities_id), INDEX devicepowersupplymodels_id (devicepowersupplymodels_id), INDEX is_recursive (is_recursive), INDEX designation (designation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicepowersupplymodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), INDEX product_number (product_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_deviceprocessormodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), INDEX product_number (product_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_deviceprocessors (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, frequence INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, frequency_default INT DEFAULT 0 NOT NULL, nbcores_default INT DEFAULT NULL, nbthreads_default INT DEFAULT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, deviceprocessormodels_id INT DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX deviceprocessormodels_id (deviceprocessormodels_id), INDEX is_recursive (is_recursive), INDEX designation (designation), INDEX date_mod (date_mod), INDEX manufacturers_id (manufacturers_id), INDEX date_creation (date_creation), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicesensormodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX product_number (product_number), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicesensors (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicesensortypes_id INT DEFAULT 0 NOT NULL, devicesensormodels_id INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX is_recursive (is_recursive), INDEX manufacturers_id (manufacturers_id), INDEX date_creation (date_creation), INDEX locations_id (locations_id), INDEX devicesensortypes_id (devicesensortypes_id), INDEX states_id (states_id), INDEX entities_id (entities_id), INDEX designation (designation), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicesensortypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicesimcards (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, manufacturers_id INT DEFAULT 0 NOT NULL, voltage INT DEFAULT NULL, devicesimcardtypes_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, allow_voip TINYINT(1) DEFAULT 0 NOT NULL, INDEX date_creation (date_creation), INDEX is_recursive (is_recursive), INDEX manufacturers_id (manufacturers_id), INDEX devicesimcardtypes_id (devicesimcardtypes_id), INDEX designation (designation), INDEX date_mod (date_mod), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicesimcardtypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicesoundcardmodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), INDEX product_number (product_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_devicesoundcards (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, type VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, devicesoundcardmodels_id INT DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX devicesoundcardmodels_id (devicesoundcardmodels_id), INDEX is_recursive (is_recursive), INDEX designation (designation), INDEX date_mod (date_mod), INDEX manufacturers_id (manufacturers_id), INDEX date_creation (date_creation), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_displaypreferences (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, num INT DEFAULT 0 NOT NULL, rank INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, INDEX itemtype (itemtype), INDEX rank (rank), UNIQUE INDEX unicity (users_id, itemtype, num), INDEX num (num), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_documentcategories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, documentcategories_id INT DEFAULT 0 NOT NULL, completename TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, level INT DEFAULT 0 NOT NULL, ancestors_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, sons_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX name (name), INDEX date_mod (date_mod), UNIQUE INDEX unicity (documentcategories_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_documents (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, filename VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'for display and transfert\', filepath VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'file storage path\', documentcategories_id INT DEFAULT 0 NOT NULL, mime VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, link VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id INT DEFAULT 0 NOT NULL, tickets_id INT DEFAULT 0 NOT NULL, sha1sum CHAR(40) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_blacklisted TINYINT(1) DEFAULT 0 NOT NULL, tag VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_creation DATETIME DEFAULT NULL, INDEX users_id (users_id), INDEX name (name), INDEX tag (tag), INDEX documentcategories_id (documentcategories_id), INDEX entities_id (entities_id), INDEX date_creation (date_creation), INDEX is_deleted (is_deleted), INDEX tickets_id (tickets_id), INDEX date_mod (date_mod), INDEX sha1sum (sha1sum), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_documents_items (id INT AUTO_INCREMENT NOT NULL, documents_id INT DEFAULT 0 NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, users_id INT DEFAULT 0, timeline_position TINYINT(1) DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, date DATETIME DEFAULT NULL, INDEX date_creation (date_creation), UNIQUE INDEX unicity (documents_id, itemtype, items_id, timeline_position), INDEX date (date), INDEX item (itemtype, items_id, entities_id, is_recursive), INDEX users_id (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_documenttypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ext VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, icon VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, mime VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_uploadable TINYINT(1) DEFAULT 1 NOT NULL, date_mod DATETIME DEFAULT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX name (name), INDEX is_uploadable (is_uploadable), INDEX date_mod (date_mod), UNIQUE INDEX unicity (ext), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_domainrecords (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, data TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, domains_id INT DEFAULT 0 NOT NULL, domainrecordtypes_id INT DEFAULT 0 NOT NULL, ttl INT NOT NULL, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX is_deleted (is_deleted), INDEX users_id_tech (users_id_tech), INDEX entities_id (entities_id), INDEX date_creation (date_creation), INDEX groups_id_tech (groups_id_tech), INDEX domains_id (domains_id), INDEX date_mod (date_mod), INDEX domainrecordtypes_id (domainrecordtypes_id), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_domainrecordtypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_domainrelations (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_domains (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, domaintypes_id INT DEFAULT 0 NOT NULL, date_expiration DATETIME DEFAULT NULL, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, others VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX is_deleted (is_deleted), INDEX users_id_tech (users_id_tech), INDEX name (name), INDEX date_expiration (date_expiration), INDEX groups_id_tech (groups_id_tech), INDEX entities_id (entities_id), INDEX date_creation (date_creation), INDEX date_mod (date_mod), INDEX domaintypes_id (domaintypes_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_domains_items (id INT AUTO_INCREMENT NOT NULL, domains_id INT DEFAULT 0 NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, domainrelations_id INT DEFAULT 0 NOT NULL, INDEX domains_id (domains_id), UNIQUE INDEX unicity (domains_id, itemtype, items_id), INDEX item (itemtype, items_id), INDEX domainrelations_id (domainrelations_id), INDEX FK_device (items_id, itemtype), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_domaintypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_dropdowntranslations (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, language VARCHAR(10) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, field VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, value TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, UNIQUE INDEX unicity (itemtype, items_id, language, field), INDEX typeid (itemtype, items_id), INDEX field (field), INDEX language (language), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_enclosuremodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, weight INT DEFAULT 0 NOT NULL, required_units INT DEFAULT 1 NOT NULL, depth DOUBLE PRECISION DEFAULT \'1\' NOT NULL, power_connections INT DEFAULT 0 NOT NULL, power_consumption INT DEFAULT 0 NOT NULL, is_half_rack TINYINT(1) DEFAULT 0 NOT NULL, picture_front TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, picture_rear TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX product_number (product_number), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_enclosures (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, enclosuremodels_id INT DEFAULT NULL, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, orientation TINYINT(1) DEFAULT NULL, power_supplies TINYINT(1) DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to states (id)\', comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX states_id (states_id), INDEX group_id_tech (groups_id_tech), INDEX locations_id (locations_id), INDEX manufacturers_id (manufacturers_id), INDEX is_template (is_template), INDEX enclosuremodels_id (enclosuremodels_id), INDEX entities_id (entities_id), INDEX is_deleted (is_deleted), INDEX users_id_tech (users_id_tech), INDEX is_recursive (is_recursive), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_entities (id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, completename TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, level INT DEFAULT 0 NOT NULL, sons_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ancestors_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, address TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, postcode VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, town VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, state VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, country VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, website VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, phonenumber VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, fax VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, admin_email VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, admin_email_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, admin_reply VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, admin_reply_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, notification_subject_tag VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ldap_dn VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, tag VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, authldaps_id INT DEFAULT 0 NOT NULL, mail_domain VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entity_ldapfilter TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, mailing_signature TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, cartridges_alert_repeat INT DEFAULT -2 NOT NULL, consumables_alert_repeat INT DEFAULT -2 NOT NULL, use_licenses_alert INT DEFAULT -2 NOT NULL, send_licenses_alert_before_delay INT DEFAULT -2 NOT NULL, use_certificates_alert INT DEFAULT -2 NOT NULL, send_certificates_alert_before_delay INT DEFAULT -2 NOT NULL, use_contracts_alert INT DEFAULT -2 NOT NULL, send_contracts_alert_before_delay INT DEFAULT -2 NOT NULL, use_infocoms_alert INT DEFAULT -2 NOT NULL, send_infocoms_alert_before_delay INT DEFAULT -2 NOT NULL, use_reservations_alert INT DEFAULT -2 NOT NULL, use_domains_alert INT DEFAULT -2 NOT NULL, send_domains_alert_close_expiries_delay INT DEFAULT -2 NOT NULL, send_domains_alert_expired_delay INT DEFAULT -2 NOT NULL, autoclose_delay INT DEFAULT -2 NOT NULL, autopurge_delay INT DEFAULT -10 NOT NULL, notclosed_delay INT DEFAULT -2 NOT NULL, calendars_id INT DEFAULT -2 NOT NULL, auto_assign_mode INT DEFAULT -2 NOT NULL, tickettype INT DEFAULT -2 NOT NULL, max_closedate DATETIME DEFAULT NULL, inquest_config INT DEFAULT -2 NOT NULL, inquest_rate INT DEFAULT 0 NOT NULL, inquest_delay INT DEFAULT -10 NOT NULL, inquest_URL VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, autofill_warranty_date VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'-2\' NOT NULL COLLATE `utf8mb3_unicode_ci`, autofill_use_date VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'-2\' NOT NULL COLLATE `utf8mb3_unicode_ci`, autofill_buy_date VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'-2\' NOT NULL COLLATE `utf8mb3_unicode_ci`, autofill_delivery_date VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'-2\' NOT NULL COLLATE `utf8mb3_unicode_ci`, autofill_order_date VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'-2\' NOT NULL COLLATE `utf8mb3_unicode_ci`, tickettemplates_id INT DEFAULT -2 NOT NULL, changetemplates_id INT DEFAULT -2 NOT NULL, problemtemplates_id INT DEFAULT -2 NOT NULL, entities_id_software INT DEFAULT -2 NOT NULL, default_contract_alert INT DEFAULT -2 NOT NULL, default_infocom_alert INT DEFAULT -2 NOT NULL, default_cartridges_alarm_threshold INT DEFAULT -2 NOT NULL, default_consumables_alarm_threshold INT DEFAULT -2 NOT NULL, delay_send_emails INT DEFAULT -2 NOT NULL, is_notif_enable_default INT DEFAULT -2 NOT NULL, inquest_duration INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, autofill_decommission_date VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'-2\' NOT NULL COLLATE `utf8mb3_unicode_ci`, suppliers_as_private INT DEFAULT -2 NOT NULL, anonymize_support_agents INT DEFAULT -2 NOT NULL, enable_custom_css INT DEFAULT -2 NOT NULL, custom_css_code TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, latitude VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, longitude VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, altitude VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX problemtemplates_id (problemtemplates_id), INDEX date_creation (date_creation), INDEX tickettemplates_id (tickettemplates_id), INDEX entities_id (entities_id), INDEX changetemplates_id (changetemplates_id), INDEX date_mod (date_mod), UNIQUE INDEX unicity (entities_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_entities_knowbaseitems (id INT AUTO_INCREMENT NOT NULL, knowbaseitems_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX entities_id (entities_id), INDEX is_recursive (is_recursive), INDEX knowbaseitems_id (knowbaseitems_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_entities_reminders (id INT AUTO_INCREMENT NOT NULL, reminders_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX reminders_id (reminders_id), INDEX entities_id (entities_id), INDEX is_recursive (is_recursive), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_entities_rssfeeds (id INT AUTO_INCREMENT NOT NULL, rssfeeds_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX rssfeeds_id (rssfeeds_id), INDEX entities_id (entities_id), INDEX is_recursive (is_recursive), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_events (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, type VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date DATETIME DEFAULT NULL, service VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, level INT DEFAULT 0 NOT NULL, message TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX item (type, items_id), INDEX date (date), INDEX level (level), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_fieldblacklists (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, value VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_fieldunicities (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT -1 NOT NULL, fields TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_active TINYINT(1) DEFAULT 0 NOT NULL, action_refuse TINYINT(1) DEFAULT 0 NOT NULL, action_notify TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'Stores field unicity criterias\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_filesystems (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_fqdns (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, fqdn VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX fqdn (fqdn), INDEX is_recursive (is_recursive), INDEX entities_id (entities_id), INDEX date_mod (date_mod), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_groups (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ldap_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ldap_value TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ldap_group_dn TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, groups_id INT DEFAULT 0 NOT NULL, completename TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, level INT DEFAULT 0 NOT NULL, ancestors_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, sons_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_requester TINYINT(1) DEFAULT 1 NOT NULL, is_watcher TINYINT(1) DEFAULT 1 NOT NULL, is_assign TINYINT(1) DEFAULT 1 NOT NULL, is_task TINYINT(1) DEFAULT 1 NOT NULL, is_notify TINYINT(1) DEFAULT 1 NOT NULL, is_itemgroup TINYINT(1) DEFAULT 1 NOT NULL, is_usergroup TINYINT(1) DEFAULT 1 NOT NULL, is_manager TINYINT(1) DEFAULT 1 NOT NULL, date_creation DATETIME DEFAULT NULL, INDEX ldap_field (ldap_field), INDEX date_creation (date_creation), INDEX is_itemgroup (is_itemgroup), INDEX is_watcher (is_watcher), INDEX ldap_group_dn (ldap_group_dn(200)), INDEX entities_id (entities_id), INDEX is_usergroup (is_usergroup), INDEX is_assign (is_assign), INDEX groups_id (groups_id), INDEX date_mod (date_mod), INDEX name (name), INDEX is_manager (is_manager), INDEX is_notify (is_notify), INDEX is_requester (is_requester), INDEX ldap_value (ldap_value(200)), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_groups_knowbaseitems (id INT AUTO_INCREMENT NOT NULL, knowbaseitems_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT -1 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX groups_id (groups_id), INDEX entities_id (entities_id), INDEX is_recursive (is_recursive), INDEX knowbaseitems_id (knowbaseitems_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_groups_problems (id INT AUTO_INCREMENT NOT NULL, problems_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, type INT DEFAULT 1 NOT NULL, INDEX `group` (groups_id, type), UNIQUE INDEX unicity (problems_id, type, groups_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_groups_reminders (id INT AUTO_INCREMENT NOT NULL, reminders_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT -1 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX is_recursive (is_recursive), INDEX reminders_id (reminders_id), INDEX groups_id (groups_id), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_groups_rssfeeds (id INT AUTO_INCREMENT NOT NULL, rssfeeds_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT -1 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX groups_id (groups_id), INDEX entities_id (entities_id), INDEX is_recursive (is_recursive), INDEX rssfeeds_id (rssfeeds_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_groups_tickets (id INT AUTO_INCREMENT NOT NULL, tickets_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, type INT DEFAULT 1 NOT NULL, INDEX `group` (groups_id, type), UNIQUE INDEX unicity (tickets_id, type, groups_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_groups_users (id INT AUTO_INCREMENT NOT NULL, users_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, is_manager TINYINT(1) DEFAULT 0 NOT NULL, is_userdelegate TINYINT(1) DEFAULT 0 NOT NULL, INDEX is_manager (is_manager), UNIQUE INDEX unicity (users_id, groups_id), INDEX is_userdelegate (is_userdelegate), INDEX groups_id (groups_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_holidays (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, begin_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, is_perpetual TINYINT(1) DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX begin_date (begin_date), INDEX date_creation (date_creation), INDEX end_date (end_date), INDEX is_perpetual (is_perpetual), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_impactcompounds (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' COLLATE `utf8mb3_unicode_ci`, color VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_impactcontexts (id INT AUTO_INCREMENT NOT NULL, positions TEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, zoom DOUBLE PRECISION DEFAULT \'0\' NOT NULL, pan_x DOUBLE PRECISION DEFAULT \'0\' NOT NULL, pan_y DOUBLE PRECISION DEFAULT \'0\' NOT NULL, impact_color VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, depends_color VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, impact_and_depends_color VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, show_depends TINYINT(1) DEFAULT 1 NOT NULL, show_impact TINYINT(1) DEFAULT 1 NOT NULL, max_depth INT DEFAULT 5 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_impactitems (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, parent_id INT DEFAULT 0 NOT NULL, impactcontexts_id INT DEFAULT 0 NOT NULL, is_slave TINYINT(1) DEFAULT 1 NOT NULL, INDEX parent_id (parent_id), INDEX impactcontexts_id (impactcontexts_id), INDEX source (itemtype, items_id), UNIQUE INDEX unicity (itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_impactrelations (id INT AUTO_INCREMENT NOT NULL, itemtype_source VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, items_id_source INT DEFAULT 0 NOT NULL, itemtype_impacted VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, items_id_impacted INT DEFAULT 0 NOT NULL, UNIQUE INDEX unicity (itemtype_source, items_id_source, itemtype_impacted, items_id_impacted), INDEX source_asset (itemtype_source, items_id_source), INDEX impacted_asset (itemtype_impacted, items_id_impacted), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_infocoms (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, buy_date DATE DEFAULT NULL, use_date DATE DEFAULT NULL, warranty_duration INT DEFAULT 0 NOT NULL, warranty_info VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, suppliers_id INT DEFAULT 0 NOT NULL, order_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, delivery_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, immo_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, value NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, warranty_value NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, sink_time INT DEFAULT 0 NOT NULL, sink_type INT DEFAULT 0 NOT NULL, sink_coeff DOUBLE PRECISION DEFAULT \'0\' NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, bill VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, budgets_id INT DEFAULT 0 NOT NULL, alert INT DEFAULT 0 NOT NULL, order_date DATE DEFAULT NULL, delivery_date DATE DEFAULT NULL, inventory_date DATE DEFAULT NULL, warranty_date DATE DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, decommission_date DATETIME DEFAULT NULL, businesscriticities_id INT DEFAULT 0 NOT NULL, INDEX is_recursive (is_recursive), INDEX budgets_id (budgets_id), INDEX date_mod (date_mod), INDEX suppliers_id (suppliers_id), INDEX buy_date (buy_date), INDEX date_creation (date_creation), INDEX entities_id (entities_id), INDEX alert (alert), UNIQUE INDEX unicity (itemtype, items_id), INDEX businesscriticities_id (businesscriticities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_interfacetypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX name (name), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_ipaddresses (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, version TINYINT(1) DEFAULT 0, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, binary_0 INT UNSIGNED DEFAULT 0 NOT NULL, binary_1 INT UNSIGNED DEFAULT 0 NOT NULL, binary_2 INT UNSIGNED DEFAULT 0 NOT NULL, binary_3 INT UNSIGNED DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, mainitems_id INT DEFAULT 0 NOT NULL, mainitemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX is_dynamic (is_dynamic), INDEX textual (name), INDEX mainitem (mainitemtype, mainitems_id, is_deleted), INDEX item (itemtype, items_id, is_deleted), INDEX `binary` (binary_0, binary_1, binary_2, binary_3), INDEX is_deleted (is_deleted), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_ipaddresses_ipnetworks (id INT AUTO_INCREMENT NOT NULL, ipaddresses_id INT DEFAULT 0 NOT NULL, ipnetworks_id INT DEFAULT 0 NOT NULL, INDEX ipnetworks_id (ipnetworks_id), INDEX ipaddresses_id (ipaddresses_id), UNIQUE INDEX unicity (ipaddresses_id, ipnetworks_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_ipnetworks (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, ipnetworks_id INT DEFAULT 0 NOT NULL, completename TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, level INT DEFAULT 0 NOT NULL, ancestors_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, sons_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, addressable TINYINT(1) DEFAULT 0 NOT NULL, version TINYINT(1) DEFAULT 0, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, address VARCHAR(40) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, address_0 INT UNSIGNED DEFAULT 0 NOT NULL, address_1 INT UNSIGNED DEFAULT 0 NOT NULL, address_2 INT UNSIGNED DEFAULT 0 NOT NULL, address_3 INT UNSIGNED DEFAULT 0 NOT NULL, netmask VARCHAR(40) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, netmask_0 INT UNSIGNED DEFAULT 0 NOT NULL, netmask_1 INT UNSIGNED DEFAULT 0 NOT NULL, netmask_2 INT UNSIGNED DEFAULT 0 NOT NULL, netmask_3 INT UNSIGNED DEFAULT 0 NOT NULL, gateway VARCHAR(40) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, gateway_0 INT UNSIGNED DEFAULT 0 NOT NULL, gateway_1 INT UNSIGNED DEFAULT 0 NOT NULL, gateway_2 INT UNSIGNED DEFAULT 0 NOT NULL, gateway_3 INT UNSIGNED DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX address (address_0, address_1, address_2, address_3), INDEX network_definition (entities_id, address, netmask), INDEX date_mod (date_mod), INDEX netmask (netmask_0, netmask_1, netmask_2, netmask_3), INDEX date_creation (date_creation), INDEX gateway (gateway_0, gateway_1, gateway_2, gateway_3), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_ipnetworks_vlans (id INT AUTO_INCREMENT NOT NULL, ipnetworks_id INT DEFAULT 0 NOT NULL, vlans_id INT DEFAULT 0 NOT NULL, UNIQUE INDEX link (ipnetworks_id, vlans_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_clusters (id INT AUTO_INCREMENT NOT NULL, clusters_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, UNIQUE INDEX unicity (clusters_id, itemtype, items_id), INDEX item (itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicebatteries (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicebatteries_id INT DEFAULT 0 NOT NULL, manufacturing_date DATE DEFAULT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX item (itemtype, items_id), INDEX entities_id (entities_id), INDEX devicebatteries_id (devicebatteries_id), INDEX is_recursive (is_recursive), INDEX is_deleted (is_deleted), INDEX otherserial (otherserial), INDEX serial (serial), INDEX is_dynamic (is_dynamic), INDEX computers_id (items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicecases (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicecases_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX otherserial (otherserial), INDEX serial (serial), INDEX is_dynamic (is_dynamic), INDEX computers_id (items_id), INDEX locations_id (locations_id), INDEX item (itemtype, items_id), INDEX entities_id (entities_id), INDEX devicecases_id (devicecases_id), INDEX states_id (states_id), INDEX is_recursive (is_recursive), INDEX is_deleted (is_deleted), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicecontrols (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicecontrols_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, busID VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX is_deleted (is_deleted), INDEX states_id (states_id), INDEX serial (serial), INDEX is_dynamic (is_dynamic), INDEX computers_id (items_id), INDEX otherserial (otherserial), INDEX busID (busID), INDEX entities_id (entities_id), INDEX devicecontrols_id (devicecontrols_id), INDEX locations_id (locations_id), INDEX item (itemtype, items_id), INDEX is_recursive (is_recursive), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicedrives (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicedrives_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, busID VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX entities_id (entities_id), INDEX devicedrives_id (devicedrives_id), INDEX locations_id (locations_id), INDEX item (itemtype, items_id), INDEX is_recursive (is_recursive), INDEX is_deleted (is_deleted), INDEX states_id (states_id), INDEX serial (serial), INDEX is_dynamic (is_dynamic), INDEX computers_id (items_id), INDEX otherserial (otherserial), INDEX busID (busID), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicefirmwares (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicefirmwares_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX serial (serial), INDEX is_dynamic (is_dynamic), INDEX computers_id (items_id), INDEX item (itemtype, items_id), INDEX entities_id (entities_id), INDEX devicefirmwares_id (devicefirmwares_id), INDEX is_recursive (is_recursive), INDEX is_deleted (is_deleted), INDEX otherserial (otherserial), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicegenerics (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicegenerics_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX is_recursive (is_recursive), INDEX is_deleted (is_deleted), INDEX otherserial (otherserial), INDEX serial (serial), INDEX is_dynamic (is_dynamic), INDEX computers_id (items_id), INDEX item (itemtype, items_id), INDEX entities_id (entities_id), INDEX devicegenerics_id (devicegenerics_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicegraphiccards (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicegraphiccards_id INT DEFAULT 0 NOT NULL, memory INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, busID VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX computers_id (items_id), INDEX states_id (states_id), INDEX serial (serial), INDEX is_dynamic (is_dynamic), INDEX devicegraphiccards_id (devicegraphiccards_id), INDEX otherserial (otherserial), INDEX busID (busID), INDEX entities_id (entities_id), INDEX specificity (memory), INDEX locations_id (locations_id), INDEX item (itemtype, items_id), INDEX is_recursive (is_recursive), INDEX is_deleted (is_deleted), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_deviceharddrives (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, deviceharddrives_id INT DEFAULT 0 NOT NULL, capacity INT DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, busID VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX deviceharddrives_id (deviceharddrives_id), INDEX otherserial (otherserial), INDEX busID (busID), INDEX serial (serial), INDEX specificity (capacity), INDEX locations_id (locations_id), INDEX item (itemtype, items_id), INDEX entities_id (entities_id), INDEX is_deleted (is_deleted), INDEX states_id (states_id), INDEX computers_id (items_id), INDEX is_recursive (is_recursive), INDEX is_dynamic (is_dynamic), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicememories (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicememories_id INT DEFAULT 0 NOT NULL, size INT DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, busID VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX locations_id (locations_id), INDEX item (itemtype, items_id), INDEX entities_id (entities_id), INDEX is_deleted (is_deleted), INDEX computers_id (items_id), INDEX states_id (states_id), INDEX is_recursive (is_recursive), INDEX is_dynamic (is_dynamic), INDEX devicememories_id (devicememories_id), INDEX otherserial (otherserial), INDEX busID (busID), INDEX serial (serial), INDEX specificity (size), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicemotherboards (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicemotherboards_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX computers_id (items_id), INDEX locations_id (locations_id), INDEX item (itemtype, items_id), INDEX entities_id (entities_id), INDEX devicemotherboards_id (devicemotherboards_id), INDEX states_id (states_id), INDEX is_recursive (is_recursive), INDEX is_deleted (is_deleted), INDEX otherserial (otherserial), INDEX serial (serial), INDEX is_dynamic (is_dynamic), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicenetworkcards (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicenetworkcards_id INT DEFAULT 0 NOT NULL, mac VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, busID VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX is_deleted (is_deleted), INDEX computers_id (items_id), INDEX states_id (states_id), INDEX serial (serial), INDEX is_dynamic (is_dynamic), INDEX devicenetworkcards_id (devicenetworkcards_id), INDEX otherserial (otherserial), INDEX busID (busID), INDEX entities_id (entities_id), INDEX specificity (mac), INDEX locations_id (locations_id), INDEX item (itemtype, items_id), INDEX is_recursive (is_recursive), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicepcis (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicepcis_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, busID VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX entities_id (entities_id), INDEX devicepcis_id (devicepcis_id), INDEX locations_id (locations_id), INDEX item (itemtype, items_id), INDEX is_recursive (is_recursive), INDEX is_deleted (is_deleted), INDEX states_id (states_id), INDEX serial (serial), INDEX is_dynamic (is_dynamic), INDEX computers_id (items_id), INDEX otherserial (otherserial), INDEX busID (busID), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicepowersupplies (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicepowersupplies_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX serial (serial), INDEX is_dynamic (is_dynamic), INDEX computers_id (items_id), INDEX locations_id (locations_id), INDEX item (itemtype, items_id), INDEX entities_id (entities_id), INDEX devicepowersupplies_id (devicepowersupplies_id), INDEX states_id (states_id), INDEX is_recursive (is_recursive), INDEX is_deleted (is_deleted), INDEX otherserial (otherserial), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_deviceprocessors (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, deviceprocessors_id INT DEFAULT 0 NOT NULL, frequency INT DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, nbcores INT DEFAULT NULL, nbthreads INT DEFAULT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, busID VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX is_recursive (is_recursive), INDEX nbcores (nbcores), INDEX is_deleted (is_deleted), INDEX computers_id (items_id), INDEX otherserial (otherserial), INDEX busID (busID), INDEX nbthreads (nbthreads), INDEX is_dynamic (is_dynamic), INDEX deviceprocessors_id (deviceprocessors_id), INDEX locations_id (locations_id), INDEX item (itemtype, items_id), INDEX entities_id (entities_id), INDEX serial (serial), INDEX specificity (frequency), INDEX states_id (states_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicesensors (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicesensors_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX is_recursive (is_recursive), INDEX is_deleted (is_deleted), INDEX otherserial (otherserial), INDEX serial (serial), INDEX is_dynamic (is_dynamic), INDEX computers_id (items_id), INDEX item (itemtype, items_id), INDEX entities_id (entities_id), INDEX devicesensors_id (devicesensors_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicesimcards (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to various table, according to itemtype (id)\', itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, devicesimcards_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, states_id INT DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, lines_id INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, pin VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, pin2 VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, puk VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, puk2 VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, msin VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, INDEX groups_id (groups_id), INDEX locations_id (locations_id), INDEX serial (serial), INDEX is_dynamic (is_dynamic), INDEX lines_id (lines_id), INDEX otherserial (otherserial), INDEX entities_id (entities_id), INDEX devicesimcards_id (devicesimcards_id), INDEX users_id (users_id), INDEX states_id (states_id), INDEX is_recursive (is_recursive), INDEX is_deleted (is_deleted), INDEX item (itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_devicesoundcards (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, devicesoundcards_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, busID VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, INDEX locations_id (locations_id), INDEX item (itemtype, items_id), INDEX is_recursive (is_recursive), INDEX is_deleted (is_deleted), INDEX states_id (states_id), INDEX serial (serial), INDEX is_dynamic (is_dynamic), INDEX computers_id (items_id), INDEX otherserial (otherserial), INDEX busID (busID), INDEX entities_id (entities_id), INDEX devicesoundcards_id (devicesoundcards_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_disks (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, device VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, mountpoint VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, filesystems_id INT DEFAULT 0 NOT NULL, totalsize INT DEFAULT 0 NOT NULL, freesize INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, encryption_status INT DEFAULT 0 NOT NULL, encryption_tool VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, encryption_algorithm VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, encryption_type VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX entities_id (entities_id), INDEX item (itemtype, items_id), INDEX freesize (freesize), INDEX device (device), INDEX date_creation (date_creation), INDEX is_deleted (is_deleted), INDEX itemtype (itemtype), INDEX mountpoint (mountpoint), INDEX is_dynamic (is_dynamic), INDEX filesystems_id (filesystems_id), INDEX items_id (items_id), INDEX totalsize (totalsize), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_enclosures (id INT AUTO_INCREMENT NOT NULL, enclosures_id INT NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT NOT NULL, position INT NOT NULL, UNIQUE INDEX item (itemtype, items_id), INDEX relation (enclosures_id, itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_kanbans (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT NULL, users_id INT NOT NULL, state TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, UNIQUE INDEX unicity (itemtype, items_id, users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_operatingsystems (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, operatingsystems_id INT DEFAULT 0 NOT NULL, operatingsystemversions_id INT DEFAULT 0 NOT NULL, operatingsystemservicepacks_id INT DEFAULT 0 NOT NULL, operatingsystemarchitectures_id INT DEFAULT 0 NOT NULL, operatingsystemkernelversions_id INT DEFAULT 0 NOT NULL, license_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, licenseid VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, operatingsystemeditions_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX operatingsystemeditions_id (operatingsystemeditions_id), INDEX operatingsystemversions_id (operatingsystemversions_id), UNIQUE INDEX unicity (items_id, itemtype, operatingsystems_id, operatingsystemarchitectures_id), INDEX is_recursive (is_recursive), INDEX is_deleted (is_deleted), INDEX operatingsystemarchitectures_id (operatingsystemarchitectures_id), INDEX operatingsystems_id (operatingsystems_id), INDEX items_id (items_id), INDEX is_dynamic (is_dynamic), INDEX operatingsystemkernelversions_id (operatingsystemkernelversions_id), INDEX operatingsystemservicepacks_id (operatingsystemservicepacks_id), INDEX item (itemtype, items_id), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_problems (id INT AUTO_INCREMENT NOT NULL, problems_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, INDEX item (itemtype, items_id), UNIQUE INDEX unicity (problems_id, itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_projects (id INT AUTO_INCREMENT NOT NULL, projects_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, INDEX item (itemtype, items_id), UNIQUE INDEX unicity (projects_id, itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_racks (id INT AUTO_INCREMENT NOT NULL, racks_id INT NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT NOT NULL, position INT NOT NULL, orientation TINYINT(1) DEFAULT NULL, bgcolor VARCHAR(7) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, hpos TINYINT(1) DEFAULT 0 NOT NULL, is_reserved TINYINT(1) DEFAULT 0 NOT NULL, INDEX relation (racks_id, itemtype, items_id), UNIQUE INDEX item (itemtype, items_id, is_reserved), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_softwarelicenses (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, softwarelicenses_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, INDEX softwarelicenses_id (softwarelicenses_id), INDEX itemtype (itemtype), INDEX is_deleted (is_deleted), INDEX item (itemtype, items_id), INDEX is_dynamic (is_dynamic), INDEX items_id (items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_softwareversions (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, softwareversions_id INT DEFAULT 0 NOT NULL, is_deleted_item TINYINT(1) DEFAULT 0 NOT NULL, is_template_item TINYINT(1) DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, date_install DATE DEFAULT NULL, INDEX is_deleted (is_deleted_item), INDEX items_id (items_id), UNIQUE INDEX unicity (itemtype, items_id, softwareversions_id), INDEX is_dynamic (is_dynamic), INDEX softwareversions_id (softwareversions_id), INDEX itemtype (itemtype), INDEX date_install (date_install), INDEX is_template (is_template_item), INDEX computers_info (entities_id, is_template_item, is_deleted_item), INDEX item (itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_items_tickets (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, tickets_id INT DEFAULT 0 NOT NULL, INDEX tickets_id (tickets_id), UNIQUE INDEX unicity (itemtype, items_id, tickets_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_itilcategories (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, itilcategories_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, completename TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, level INT DEFAULT 0 NOT NULL, knowbaseitemcategories_id INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, code VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ancestors_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, sons_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_helpdeskvisible TINYINT(1) DEFAULT 1 NOT NULL, tickettemplates_id_incident INT DEFAULT 0 NOT NULL, tickettemplates_id_demand INT DEFAULT 0 NOT NULL, changetemplates_id INT DEFAULT 0 NOT NULL, problemtemplates_id INT DEFAULT 0 NOT NULL, is_incident INT DEFAULT 1 NOT NULL, is_request INT DEFAULT 1 NOT NULL, is_problem INT DEFAULT 1 NOT NULL, is_change TINYINT(1) DEFAULT 1 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX changetemplates_id (changetemplates_id), INDEX itilcategories_id (itilcategories_id), INDEX users_id (users_id), INDEX date_creation (date_creation), INDEX entities_id (entities_id), INDEX is_problem (is_problem), INDEX problemtemplates_id (problemtemplates_id), INDEX tickettemplates_id_incident (tickettemplates_id_incident), INDEX groups_id (groups_id), INDEX is_recursive (is_recursive), INDEX is_change (is_change), INDEX is_incident (is_incident), INDEX tickettemplates_id_demand (tickettemplates_id_demand), INDEX is_helpdeskvisible (is_helpdeskvisible), INDEX knowbaseitemcategories_id (knowbaseitemcategories_id), INDEX date_mod (date_mod), INDEX name (name), INDEX is_request (is_request), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_itilfollowups (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, date DATETIME DEFAULT NULL, users_id INT DEFAULT 0 NOT NULL, users_id_editor INT DEFAULT 0 NOT NULL, content LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_private TINYINT(1) DEFAULT 0 NOT NULL, requesttypes_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, timeline_position TINYINT(1) DEFAULT 0 NOT NULL, sourceitems_id INT DEFAULT 0 NOT NULL, sourceof_items_id INT DEFAULT 0 NOT NULL, INDEX users_id (users_id), INDEX date (date), INDEX item_id (items_id), INDEX sourceitems_id (sourceitems_id), INDEX users_id_editor (users_id_editor), INDEX date_mod (date_mod), INDEX item (itemtype, items_id), INDEX sourceof_items_id (sourceof_items_id), INDEX is_private (is_private), INDEX date_creation (date_creation), INDEX itemtype (itemtype), INDEX requesttypes_id (requesttypes_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_itilfollowuptemplates (id INT AUTO_INCREMENT NOT NULL, date_creation DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, content TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, requesttypes_id INT DEFAULT 0 NOT NULL, is_private TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX is_private (is_private), INDEX entities_id (entities_id), INDEX name (name), INDEX date_mod (date_mod), INDEX is_recursive (is_recursive), INDEX date_creation (date_creation), INDEX requesttypes_id (requesttypes_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_itils_projects (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, projects_id INT DEFAULT 0 NOT NULL, INDEX projects_id (projects_id), UNIQUE INDEX unicity (itemtype, items_id, projects_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_itilsolutions (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, solutiontypes_id INT DEFAULT 0 NOT NULL, solutiontype_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_creation DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_approval DATETIME DEFAULT NULL, users_id INT DEFAULT 0 NOT NULL, user_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id_editor INT DEFAULT 0 NOT NULL, users_id_approval INT DEFAULT 0 NOT NULL, user_name_approval VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, status INT DEFAULT 1 NOT NULL, itilfollowups_id INT DEFAULT NULL COMMENT \'Followup reference on reject or approve a solution\', INDEX itilfollowups_id (itilfollowups_id), INDEX users_id_editor (users_id_editor), INDEX itemtype (itemtype), INDEX users_id_approval (users_id_approval), INDEX solutiontypes_id (solutiontypes_id), INDEX item_id (items_id), INDEX status (status), INDEX users_id (users_id), INDEX item (itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_knowbaseitemcategories (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, knowbaseitemcategories_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, completename TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, level INT DEFAULT 0 NOT NULL, sons_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ancestors_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX is_recursive (is_recursive), INDEX date_mod (date_mod), INDEX name (name), UNIQUE INDEX unicity (entities_id, knowbaseitemcategories_id, name), INDEX date_creation (date_creation), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_knowbaseitems (id INT AUTO_INCREMENT NOT NULL, knowbaseitemcategories_id INT DEFAULT 0 NOT NULL, name TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, answer LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_faq TINYINT(1) DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, view INT DEFAULT 0 NOT NULL, date DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, begin_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, FULLTEXT INDEX name (name), INDEX end_date (end_date), INDEX is_faq (is_faq), FULLTEXT INDEX answer (answer), FULLTEXT INDEX `fulltext` (name, answer), INDEX date_mod (date_mod), INDEX users_id (users_id), INDEX begin_date (begin_date), INDEX knowbaseitemcategories_id (knowbaseitemcategories_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_knowbaseitems_comments (id INT AUTO_INCREMENT NOT NULL, knowbaseitems_id INT NOT NULL, users_id INT DEFAULT 0 NOT NULL, language VARCHAR(10) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, parent_comment_id INT DEFAULT NULL, date_creation DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_knowbaseitems_items (id INT AUTO_INCREMENT NOT NULL, knowbaseitems_id INT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, INDEX itemtype (itemtype), UNIQUE INDEX unicity (itemtype, items_id, knowbaseitems_id), INDEX item_id (items_id), INDEX item (itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_knowbaseitems_profiles (id INT AUTO_INCREMENT NOT NULL, knowbaseitems_id INT DEFAULT 0 NOT NULL, profiles_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT -1 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX profiles_id (profiles_id), INDEX entities_id (entities_id), INDEX is_recursive (is_recursive), INDEX knowbaseitems_id (knowbaseitems_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_knowbaseitems_revisions (id INT AUTO_INCREMENT NOT NULL, knowbaseitems_id INT NOT NULL, revision INT NOT NULL, name TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, answer LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, language VARCHAR(10) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id INT DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, INDEX revision (revision), UNIQUE INDEX unicity (knowbaseitems_id, revision, language), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_knowbaseitems_users (id INT AUTO_INCREMENT NOT NULL, knowbaseitems_id INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, INDEX users_id (users_id), INDEX knowbaseitems_id (knowbaseitems_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_knowbaseitemtranslations (id INT AUTO_INCREMENT NOT NULL, knowbaseitems_id INT DEFAULT 0 NOT NULL, language VARCHAR(10) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, name TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, answer LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, FULLTEXT INDEX name (name), INDEX users_id (users_id), FULLTEXT INDEX answer (answer), FULLTEXT INDEX `fulltext` (name, answer), INDEX item (knowbaseitems_id, language), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_lineoperators (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, mcc INT DEFAULT NULL, mnc INT DEFAULT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_creation (date_creation), INDEX entities_id (entities_id), UNIQUE INDEX unicity (mcc, mnc), INDEX is_recursive (is_recursive), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_lines (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, caller_num VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, caller_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, users_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, lineoperators_id INT DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, linetypes_id INT DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX is_recursive (is_recursive), INDEX users_id (users_id), INDEX lineoperators_id (lineoperators_id), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_linetypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX name (name), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_links (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 1 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, link VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, data TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, open_window TINYINT(1) DEFAULT 1 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_links_itemtypes (id INT AUTO_INCREMENT NOT NULL, links_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, INDEX links_id (links_id), UNIQUE INDEX unicity (itemtype, links_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_locations (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, completename TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, level INT DEFAULT 0 NOT NULL, ancestors_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, sons_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, address TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, postcode VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, town VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, state VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, country VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, building VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, room VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, latitude VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, longitude VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, altitude VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX locations_id (locations_id), UNIQUE INDEX unicity (entities_id, locations_id, name), INDEX date_creation (date_creation), INDEX name (name), INDEX is_recursive (is_recursive), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_logs (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, itemtype_link VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, linked_action INT DEFAULT 0 NOT NULL COMMENT \'see define.php HISTORY_* constant\', user_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, id_search_option INT DEFAULT 0 NOT NULL COMMENT \'see search.constant.php for value\', old_value VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, new_value VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX date_mod (date_mod), INDEX id_search_option (id_search_option), INDEX itemtype_link (itemtype_link), INDEX item (itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_mailcollectors (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, host VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, login VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, filesize_max INT DEFAULT 2097152 NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, date_mod DATETIME DEFAULT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, passwd VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, accepted VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, refused VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, errors INT DEFAULT 0 NOT NULL, use_mail_date TINYINT(1) DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, requester_field INT DEFAULT 0 NOT NULL, add_cc_to_observer TINYINT(1) DEFAULT 0 NOT NULL, collect_only_unread TINYINT(1) DEFAULT 0 NOT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX is_active (is_active), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_manufacturers (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_monitormodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, weight INT DEFAULT 0 NOT NULL, required_units INT DEFAULT 1 NOT NULL, depth DOUBLE PRECISION DEFAULT \'1\' NOT NULL, power_connections INT DEFAULT 0 NOT NULL, power_consumption INT DEFAULT 0 NOT NULL, is_half_rack TINYINT(1) DEFAULT 0 NOT NULL, picture_front TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, picture_rear TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX product_number (product_number), INDEX name (name), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_monitors (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, contact VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, contact_num VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, size NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL, have_micro TINYINT(1) DEFAULT 0 NOT NULL, have_speaker TINYINT(1) DEFAULT 0 NOT NULL, have_subd TINYINT(1) DEFAULT 0 NOT NULL, have_bnc TINYINT(1) DEFAULT 0 NOT NULL, have_dvi TINYINT(1) DEFAULT 0 NOT NULL, have_pivot TINYINT(1) DEFAULT 0 NOT NULL, have_hdmi TINYINT(1) DEFAULT 0 NOT NULL, have_displayport TINYINT(1) DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, monitortypes_id INT DEFAULT 0 NOT NULL, monitormodels_id INT DEFAULT 0 NOT NULL, manufacturers_id INT DEFAULT 0 NOT NULL, is_global TINYINT(1) DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\', is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX groups_id_tech (groups_id_tech), INDEX users_id_tech (users_id_tech), INDEX locations_id (locations_id), INDEX manufacturers_id (manufacturers_id), INDEX is_template (is_template), INDEX date_creation (date_creation), INDEX is_dynamic (is_dynamic), INDEX monitortypes_id (monitortypes_id), INDEX monitormodels_id (monitormodels_id), INDEX groups_id (groups_id), INDEX is_global (is_global), INDEX is_recursive (is_recursive), INDEX serial (serial), INDEX is_deleted (is_deleted), INDEX states_id (states_id), INDEX users_id (users_id), INDEX entities_id (entities_id), INDEX name (name), INDEX otherserial (otherserial), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_monitortypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_netpoints (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX name (name), INDEX date_creation (date_creation), INDEX location_name (locations_id, name), INDEX complete (entities_id, locations_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkaliases (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, networknames_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, fqdns_id INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX entities_id (entities_id), INDEX name (name), INDEX networknames_id (networknames_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkequipmentmodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, weight INT DEFAULT 0 NOT NULL, required_units INT DEFAULT 1 NOT NULL, depth DOUBLE PRECISION DEFAULT \'1\' NOT NULL, power_connections INT DEFAULT 0 NOT NULL, power_consumption INT DEFAULT 0 NOT NULL, is_half_rack TINYINT(1) DEFAULT 0 NOT NULL, picture_front TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, picture_rear TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX product_number (product_number), INDEX name (name), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkequipments (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ram VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, contact VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, contact_num VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, networks_id INT DEFAULT 0 NOT NULL, networkequipmenttypes_id INT DEFAULT 0 NOT NULL, networkequipmentmodels_id INT DEFAULT 0 NOT NULL, manufacturers_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\', is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, INDEX manufacturers_id (manufacturers_id), INDEX name (name), INDEX serial (serial), INDEX date_mod (date_mod), INDEX users_id_tech (users_id_tech), INDEX networkequipmentmodels_id (networkequipmentmodels_id), INDEX groups_id (groups_id), INDEX is_template (is_template), INDEX otherserial (otherserial), INDEX groups_id_tech (groups_id_tech), INDEX networkequipmenttypes_id (networkequipmenttypes_id), INDEX networks_id (networks_id), INDEX users_id (users_id), INDEX entities_id (entities_id), INDEX date_creation (date_creation), INDEX is_dynamic (is_dynamic), INDEX is_deleted (is_deleted), INDEX states_id (states_id), INDEX locations_id (locations_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkequipmenttypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkinterfaces (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networknames (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, fqdns_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX is_dynamic (is_dynamic), INDEX name (name), INDEX entities_id (entities_id), INDEX date_mod (date_mod), INDEX item (itemtype, items_id, is_deleted), INDEX fqdns_id (fqdns_id), INDEX FQDN (name, fqdns_id), INDEX date_creation (date_creation), INDEX is_deleted (is_deleted), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkportaggregates (id INT AUTO_INCREMENT NOT NULL, networkports_id INT DEFAULT 0 NOT NULL, networkports_id_list TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'array of associated networkports_id\', date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), UNIQUE INDEX networkports_id (networkports_id), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkportaliases (id INT AUTO_INCREMENT NOT NULL, networkports_id INT DEFAULT 0 NOT NULL, networkports_id_alias INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), UNIQUE INDEX networkports_id (networkports_id), INDEX networkports_id_alias (networkports_id_alias), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkportdialups (id INT AUTO_INCREMENT NOT NULL, networkports_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, UNIQUE INDEX networkports_id (networkports_id), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkportethernets (id INT AUTO_INCREMENT NOT NULL, networkports_id INT DEFAULT 0 NOT NULL, items_devicenetworkcards_id INT DEFAULT 0 NOT NULL, netpoints_id INT DEFAULT 0 NOT NULL, type VARCHAR(10) CHARACTER SET utf8mb3 DEFAULT \'\' COLLATE `utf8mb3_unicode_ci` COMMENT \'T, LX, SX\', speed INT DEFAULT 10 NOT NULL COMMENT \'Mbit/s: 10, 100, 1000, 10000\', date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX netpoint (netpoints_id), INDEX date_creation (date_creation), INDEX type (type), UNIQUE INDEX networkports_id (networkports_id), INDEX speed (speed), INDEX card (items_devicenetworkcards_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkportfiberchannels (id INT AUTO_INCREMENT NOT NULL, networkports_id INT DEFAULT 0 NOT NULL, items_devicenetworkcards_id INT DEFAULT 0 NOT NULL, netpoints_id INT DEFAULT 0 NOT NULL, wwn VARCHAR(16) CHARACTER SET utf8mb3 DEFAULT \'\' COLLATE `utf8mb3_unicode_ci`, speed INT DEFAULT 10 NOT NULL COMMENT \'Mbit/s: 10, 100, 1000, 10000\', date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX speed (speed), INDEX card (items_devicenetworkcards_id), INDEX date_mod (date_mod), INDEX netpoint (netpoints_id), INDEX date_creation (date_creation), INDEX wwn (wwn), UNIQUE INDEX networkports_id (networkports_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkportlocals (id INT AUTO_INCREMENT NOT NULL, networkports_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), UNIQUE INDEX networkports_id (networkports_id), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkports (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, logical_number INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, instantiation_type VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, mac VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX is_dynamic (is_dynamic), INDEX is_recursive (is_recursive), INDEX item (itemtype, items_id), INDEX date_mod (date_mod), INDEX mac (mac), INDEX on_device (items_id, itemtype), INDEX date_creation (date_creation), INDEX is_deleted (is_deleted), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkports_networkports (id INT AUTO_INCREMENT NOT NULL, networkports_id_1 INT DEFAULT 0 NOT NULL, networkports_id_2 INT DEFAULT 0 NOT NULL, INDEX networkports_id_2 (networkports_id_2), UNIQUE INDEX unicity (networkports_id_1, networkports_id_2), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkports_vlans (id INT AUTO_INCREMENT NOT NULL, networkports_id INT DEFAULT 0 NOT NULL, vlans_id INT DEFAULT 0 NOT NULL, tagged TINYINT(1) DEFAULT 0 NOT NULL, INDEX vlans_id (vlans_id), UNIQUE INDEX unicity (networkports_id, vlans_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networkportwifis (id INT AUTO_INCREMENT NOT NULL, networkports_id INT DEFAULT 0 NOT NULL, items_devicenetworkcards_id INT DEFAULT 0 NOT NULL, wifinetworks_id INT DEFAULT 0 NOT NULL, networkportwifis_id INT DEFAULT 0 NOT NULL COMMENT \'only useful in case of Managed node\', version VARCHAR(20) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'a, a/b, a/b/g, a/b/g/n, a/b/g/n/y\', mode VARCHAR(20) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'ad-hoc, managed, master, repeater, secondary, monitor, auto\', date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX mode (mode), INDEX card (items_devicenetworkcards_id), INDEX date_mod (date_mod), INDEX essid (wifinetworks_id), INDEX date_creation (date_creation), INDEX version (version), UNIQUE INDEX networkports_id (networkports_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_networks (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX name (name), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_notepads (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, date DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, users_id INT DEFAULT 0 NOT NULL, users_id_lastupdater INT DEFAULT 0 NOT NULL, content LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX users_id (users_id), INDEX date_mod (date_mod), INDEX date (date), INDEX item (itemtype, items_id), INDEX users_id_lastupdater (users_id_lastupdater), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_notificationchatconfigs (id INT AUTO_INCREMENT NOT NULL, hookurl VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, chat VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, type VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, value VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_notifications (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, event VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, is_active TINYINT(1) DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, allow_response TINYINT(1) DEFAULT 1 NOT NULL, INDEX date_creation (date_creation), INDEX is_active (is_active), INDEX name (name), INDEX date_mod (date_mod), INDEX itemtype (itemtype), INDEX is_recursive (is_recursive), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_notifications_notificationtemplates (id INT AUTO_INCREMENT NOT NULL, notifications_id INT DEFAULT 0 NOT NULL, mode VARCHAR(20) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'See Notification_NotificationTemplate::MODE_* constants\', notificationtemplates_id INT DEFAULT 0 NOT NULL, INDEX mode (mode), INDEX notifications_id (notifications_id), UNIQUE INDEX unicity (notifications_id, mode, notificationtemplates_id), INDEX notificationtemplates_id (notificationtemplates_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_notificationtargets (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, type INT DEFAULT 0 NOT NULL, notifications_id INT DEFAULT 0 NOT NULL, INDEX notifications_id (notifications_id), INDEX items (type, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_notificationtemplates (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, css TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX itemtype (itemtype), INDEX date_mod (date_mod), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_notificationtemplatetranslations (id INT AUTO_INCREMENT NOT NULL, notificationtemplates_id INT DEFAULT 0 NOT NULL, language VARCHAR(10) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, subject VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, content_text TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, content_html TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX notificationtemplates_id (notificationtemplates_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_notimportedemails (id INT AUTO_INCREMENT NOT NULL, `from` VARCHAR(255) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, `to` VARCHAR(255) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, mailcollectors_id INT DEFAULT 0 NOT NULL, date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, subject TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, messageid VARCHAR(255) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, reason INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, INDEX users_id (users_id), INDEX mailcollectors_id (mailcollectors_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET latin1 COLLATE `latin1_swedish_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_objectlocks (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'Type of locked object\', items_id INT NOT NULL COMMENT \'RELATION to various tables, according to itemtype (ID)\', users_id INT NOT NULL COMMENT \'id of the locker\', date_mod DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'Timestamp of the lock\', UNIQUE INDEX item (itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_oidc_config (id INT DEFAULT 0 NOT NULL, Provider VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ClientID VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ClientSecret VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_activate TINYINT(1) DEFAULT 0 NOT NULL, is_forced TINYINT(1) DEFAULT 0 NOT NULL, scope VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, proxy VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, cert VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, logout VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_oidc_mapping (id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' COLLATE `utf8mb3_unicode_ci`, given_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' COLLATE `utf8mb3_unicode_ci`, family_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' COLLATE `utf8mb3_unicode_ci`, picture VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' COLLATE `utf8mb3_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' COLLATE `utf8mb3_unicode_ci`, locale VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' COLLATE `utf8mb3_unicode_ci`, phone_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' COLLATE `utf8mb3_unicode_ci`, `group` VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_oidc_users (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT 0 NOT NULL, `update` TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_olalevelactions (id INT AUTO_INCREMENT NOT NULL, olalevels_id INT DEFAULT 0 NOT NULL, action_type VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, value VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX olalevels_id (olalevels_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_olalevelcriterias (id INT AUTO_INCREMENT NOT NULL, olalevels_id INT DEFAULT 0 NOT NULL, criteria VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, `condition` INT DEFAULT 0 NOT NULL COMMENT \'see define.php PATTERN_* and REGEX_* constant\', pattern VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX `condition` (`condition`), INDEX olalevels_id (olalevels_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_olalevels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, olas_id INT DEFAULT 0 NOT NULL, execution_time INT NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, `match` CHAR(10) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'see define.php *_MATCHING constant\', uuid VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX is_active (is_active), INDEX olas_id (olas_id), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_olalevels_tickets (id INT AUTO_INCREMENT NOT NULL, tickets_id INT DEFAULT 0 NOT NULL, olalevels_id INT DEFAULT 0 NOT NULL, date DATETIME DEFAULT NULL, INDEX tickets_id (tickets_id), INDEX olalevels_id (olalevels_id), UNIQUE INDEX unicity (tickets_id, olalevels_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_olas (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, type INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, number_time INT NOT NULL, calendars_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, definition_time VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, end_of_working_day TINYINT(1) DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, slms_id INT DEFAULT 0 NOT NULL, INDEX date_creation (date_creation), INDEX calendars_id (calendars_id), INDEX name (name), INDEX slms_id (slms_id), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_operatingsystemarchitectures (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX name (name), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_operatingsystemeditions (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_operatingsystemkernels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_operatingsystemkernelversions (id INT AUTO_INCREMENT NOT NULL, operatingsystemkernels_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX operatingsystemkernels_id (operatingsystemkernels_id), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_operatingsystems (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_operatingsystemservicepacks (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_operatingsystemversions (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_passivedcequipmentmodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, weight INT DEFAULT 0 NOT NULL, required_units INT DEFAULT 1 NOT NULL, depth DOUBLE PRECISION DEFAULT \'1\' NOT NULL, power_connections INT DEFAULT 0 NOT NULL, power_consumption INT DEFAULT 0 NOT NULL, is_half_rack TINYINT(1) DEFAULT 0 NOT NULL, picture_front TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, picture_rear TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX product_number (product_number), INDEX name (name), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_passivedcequipments (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, passivedcequipmentmodels_id INT DEFAULT NULL, passivedcequipmenttypes_id INT DEFAULT 0 NOT NULL, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to states (id)\', comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX entities_id (entities_id), INDEX manufacturers_id (manufacturers_id), INDEX is_template (is_template), INDEX passivedcequipmenttypes_id (passivedcequipmenttypes_id), INDEX is_recursive (is_recursive), INDEX is_deleted (is_deleted), INDEX users_id_tech (users_id_tech), INDEX locations_id (locations_id), INDEX states_id (states_id), INDEX group_id_tech (groups_id_tech), INDEX passivedcequipmentmodels_id (passivedcequipmentmodels_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_passivedcequipmenttypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_pdumodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, weight INT DEFAULT 0 NOT NULL, required_units INT DEFAULT 1 NOT NULL, depth DOUBLE PRECISION DEFAULT \'1\' NOT NULL, power_connections INT DEFAULT 0 NOT NULL, max_power INT DEFAULT 0 NOT NULL, is_half_rack TINYINT(1) DEFAULT 0 NOT NULL, picture_front TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, picture_rear TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_rackable TINYINT(1) DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX is_rackable (is_rackable), INDEX product_number (product_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_pdus (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, pdumodels_id INT DEFAULT NULL, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL COMMENT \'RELATION to states (id)\', comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, pdutypes_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX manufacturers_id (manufacturers_id), INDEX is_template (is_template), INDEX pdumodels_id (pdumodels_id), INDEX entities_id (entities_id), INDEX pdutypes_id (pdutypes_id), INDEX is_deleted (is_deleted), INDEX users_id_tech (users_id_tech), INDEX is_recursive (is_recursive), INDEX states_id (states_id), INDEX group_id_tech (groups_id_tech), INDEX locations_id (locations_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_pdus_plugs (id INT AUTO_INCREMENT NOT NULL, plugs_id INT DEFAULT 0 NOT NULL, pdus_id INT DEFAULT 0 NOT NULL, number_plugs INT DEFAULT 0, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX plugs_id (plugs_id), INDEX pdus_id (pdus_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_pdus_racks (id INT AUTO_INCREMENT NOT NULL, racks_id INT DEFAULT 0 NOT NULL, pdus_id INT DEFAULT 0 NOT NULL, side INT DEFAULT 0, position INT NOT NULL, bgcolor VARCHAR(7) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX pdus_id (pdus_id), INDEX racks_id (racks_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_pdutypes (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_creation DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX is_recursive (is_recursive), INDEX name (name), INDEX date_creation (date_creation), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_peripheralmodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, weight INT DEFAULT 0 NOT NULL, required_units INT DEFAULT 1 NOT NULL, depth DOUBLE PRECISION DEFAULT \'1\' NOT NULL, power_connections INT DEFAULT 0 NOT NULL, power_consumption INT DEFAULT 0 NOT NULL, is_half_rack TINYINT(1) DEFAULT 0 NOT NULL, picture_front TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, picture_rear TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX product_number (product_number), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_peripherals (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, contact VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, contact_num VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, peripheraltypes_id INT DEFAULT 0 NOT NULL, peripheralmodels_id INT DEFAULT 0 NOT NULL, brand VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, manufacturers_id INT DEFAULT 0 NOT NULL, is_global TINYINT(1) DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\', is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX is_dynamic (is_dynamic), INDEX is_deleted (is_deleted), INDEX states_id (states_id), INDEX users_id (users_id), INDEX entities_id (entities_id), INDEX is_recursive (is_recursive), INDEX serial (serial), INDEX name (name), INDEX date_mod (date_mod), INDEX users_id_tech (users_id_tech), INDEX locations_id (locations_id), INDEX manufacturers_id (manufacturers_id), INDEX otherserial (otherserial), INDEX is_template (is_template), INDEX groups_id_tech (groups_id_tech), INDEX peripheraltypes_id (peripheraltypes_id), INDEX peripheralmodels_id (peripheralmodels_id), INDEX groups_id (groups_id), INDEX date_creation (date_creation), INDEX is_global (is_global), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_peripheraltypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_phonemodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX product_number (product_number), INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_phonepowersupplies (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_phones (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, contact VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, contact_num VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, phonetypes_id INT DEFAULT 0 NOT NULL, phonemodels_id INT DEFAULT 0 NOT NULL, brand VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, phonepowersupplies_id INT DEFAULT 0 NOT NULL, number_line VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, have_headset TINYINT(1) DEFAULT 0 NOT NULL, have_hp TINYINT(1) DEFAULT 0 NOT NULL, manufacturers_id INT DEFAULT 0 NOT NULL, is_global TINYINT(1) DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\', is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX is_deleted (is_deleted), INDEX states_id (states_id), INDEX locations_id (locations_id), INDEX manufacturers_id (manufacturers_id), INDEX is_recursive (is_recursive), INDEX is_template (is_template), INDEX serial (serial), INDEX date_mod (date_mod), INDEX users_id_tech (users_id_tech), INDEX phonemodels_id (phonemodels_id), INDEX groups_id (groups_id), INDEX is_global (is_global), INDEX otherserial (otherserial), INDEX groups_id_tech (groups_id_tech), INDEX phonetypes_id (phonetypes_id), INDEX phonepowersupplies_id (phonepowersupplies_id), INDEX users_id (users_id), INDEX entities_id (entities_id), INDEX date_creation (date_creation), INDEX name (name), INDEX is_dynamic (is_dynamic), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_phonetypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_planningeventcategories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, color VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_planningexternalevents (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, planningexternaleventtemplates_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 1 NOT NULL, date DATETIME DEFAULT NULL, users_id INT DEFAULT 0 NOT NULL, users_id_guests TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, groups_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, text TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, begin DATETIME DEFAULT NULL, end DATETIME DEFAULT NULL, rrule TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, state INT DEFAULT 0 NOT NULL, planningeventcategories_id INT DEFAULT 0 NOT NULL, background TINYINT(1) DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX state (state), INDEX end (end), INDEX is_recursive (is_recursive), UNIQUE INDEX uuid (uuid), INDEX planningeventcategories_id (planningeventcategories_id), INDEX users_id (users_id), INDEX date (date), INDEX planningexternaleventtemplates_id (planningexternaleventtemplates_id), INDEX date_mod (date_mod), INDEX groups_id (groups_id), INDEX begin (begin), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_planningexternaleventtemplates (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, text TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, duration INT DEFAULT 0 NOT NULL, before_time INT DEFAULT 0 NOT NULL, rrule TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, state INT DEFAULT 0 NOT NULL, planningeventcategories_id INT DEFAULT 0 NOT NULL, background TINYINT(1) DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX state (state), INDEX date_mod (date_mod), INDEX planningeventcategories_id (planningeventcategories_id), INDEX date_creation (date_creation), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_planningrecalls (id INT AUTO_INCREMENT NOT NULL, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, users_id INT DEFAULT 0 NOT NULL, before_time INT DEFAULT -10 NOT NULL, `when` DATETIME DEFAULT NULL, INDEX users_id (users_id), UNIQUE INDEX unicity (itemtype, items_id, users_id), INDEX before_time (before_time), INDEX `when` (`when`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_plugins (id INT AUTO_INCREMENT NOT NULL, directory VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, version VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, state INT DEFAULT 0 NOT NULL COMMENT \'see define.php PLUGIN_* constant\', author VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, homepage VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, license VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, UNIQUE INDEX unicity (directory), INDEX state (state), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_plugs (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_printermodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX product_number (product_number), INDEX name (name), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_printers (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, contact VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, contact_num VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, have_serial TINYINT(1) DEFAULT 0 NOT NULL, have_parallel TINYINT(1) DEFAULT 0 NOT NULL, have_usb TINYINT(1) DEFAULT 0 NOT NULL, have_wifi TINYINT(1) DEFAULT 0 NOT NULL, have_ethernet TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, memory_size VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, networks_id INT DEFAULT 0 NOT NULL, printertypes_id INT DEFAULT 0 NOT NULL, printermodels_id INT DEFAULT 0 NOT NULL, manufacturers_id INT DEFAULT 0 NOT NULL, is_global TINYINT(1) DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, init_pages_counter INT DEFAULT 0 NOT NULL, last_pages_counter INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\', is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, INDEX last_pages_counter (last_pages_counter), INDEX name (name), INDEX is_deleted (is_deleted), INDEX states_id (states_id), INDEX locations_id (locations_id), INDEX date_creation (date_creation), INDEX manufacturers_id (manufacturers_id), INDEX is_dynamic (is_dynamic), INDEX is_template (is_template), INDEX date_mod (date_mod), INDEX users_id_tech (users_id_tech), INDEX printermodels_id (printermodels_id), INDEX groups_id (groups_id), INDEX serial (serial), INDEX is_global (is_global), INDEX groups_id_tech (groups_id_tech), INDEX printertypes_id (printertypes_id), INDEX networks_id (networks_id), INDEX users_id (users_id), INDEX otherserial (otherserial), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_printertypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_problemcosts (id INT AUTO_INCREMENT NOT NULL, problems_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, begin_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, actiontime INT DEFAULT 0 NOT NULL, cost_time NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, cost_fixed NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, cost_material NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, budgets_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, INDEX end_date (end_date), INDEX name (name), INDEX entities_id (entities_id), INDEX problems_id (problems_id), INDEX budgets_id (budgets_id), INDEX begin_date (begin_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_problems (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, status INT DEFAULT 1 NOT NULL, content LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date DATETIME DEFAULT NULL, solvedate DATETIME DEFAULT NULL, closedate DATETIME DEFAULT NULL, time_to_resolve DATETIME DEFAULT NULL, users_id_recipient INT DEFAULT 0 NOT NULL, users_id_lastupdater INT DEFAULT 0 NOT NULL, urgency INT DEFAULT 1 NOT NULL, impact INT DEFAULT 1 NOT NULL, priority INT DEFAULT 1 NOT NULL, itilcategories_id INT DEFAULT 0 NOT NULL, impactcontent LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, causecontent LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, symptomcontent LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, actiontime INT DEFAULT 0 NOT NULL, begin_waiting_date DATETIME DEFAULT NULL, waiting_duration INT DEFAULT 0 NOT NULL, close_delay_stat INT DEFAULT 0 NOT NULL, solve_delay_stat INT DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, INDEX users_id_lastupdater (users_id_lastupdater), INDEX urgency (urgency), INDEX itilcategories_id (itilcategories_id), INDEX status (status), INDEX is_deleted (is_deleted), INDEX name (name), INDEX date_creation (date_creation), INDEX impact (impact), INDEX users_id_recipient (users_id_recipient), INDEX priority (priority), INDEX date (date), INDEX entities_id (entities_id), INDEX time_to_resolve (time_to_resolve), INDEX solvedate (solvedate), INDEX date_mod (date_mod), INDEX closedate (closedate), INDEX is_recursive (is_recursive), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_problems_suppliers (id INT AUTO_INCREMENT NOT NULL, problems_id INT DEFAULT 0 NOT NULL, suppliers_id INT DEFAULT 0 NOT NULL, type INT DEFAULT 1 NOT NULL, use_notification TINYINT(1) DEFAULT 0 NOT NULL, alternative_email VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX `group` (suppliers_id, type), UNIQUE INDEX unicity (problems_id, type, suppliers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_problems_tickets (id INT AUTO_INCREMENT NOT NULL, problems_id INT DEFAULT 0 NOT NULL, tickets_id INT DEFAULT 0 NOT NULL, INDEX tickets_id (tickets_id), UNIQUE INDEX unicity (problems_id, tickets_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_problems_users (id INT AUTO_INCREMENT NOT NULL, problems_id INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, type INT DEFAULT 1 NOT NULL, use_notification TINYINT(1) DEFAULT 0 NOT NULL, alternative_email VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, UNIQUE INDEX unicity (problems_id, type, users_id, alternative_email), INDEX user (users_id, type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_problemtasks (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, problems_id INT DEFAULT 0 NOT NULL, taskcategories_id INT DEFAULT 0 NOT NULL, date DATETIME DEFAULT NULL, begin DATETIME DEFAULT NULL, end DATETIME DEFAULT NULL, users_id INT DEFAULT 0 NOT NULL, users_id_editor INT DEFAULT 0 NOT NULL, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, content LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, actiontime INT DEFAULT 0 NOT NULL, state INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, tasktemplates_id INT DEFAULT 0 NOT NULL, timeline_position TINYINT(1) DEFAULT 0 NOT NULL, is_private TINYINT(1) DEFAULT 0 NOT NULL, INDEX taskcategories_id (taskcategories_id), INDEX begin (begin), INDEX date (date), INDEX users_id_editor (users_id_editor), UNIQUE INDEX uuid (uuid), INDEX tasktemplates_id (tasktemplates_id), INDEX end (end), INDEX date_mod (date_mod), INDEX users_id_tech (users_id_tech), INDEX problems_id (problems_id), INDEX is_private (is_private), INDEX state (state), INDEX date_creation (date_creation), INDEX groups_id_tech (groups_id_tech), INDEX users_id (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_problemtemplatehiddenfields (id INT AUTO_INCREMENT NOT NULL, problemtemplates_id INT DEFAULT 0 NOT NULL, num INT DEFAULT 0 NOT NULL, UNIQUE INDEX unicity (problemtemplates_id, num), INDEX problemtemplates_id (problemtemplates_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_problemtemplatemandatoryfields (id INT AUTO_INCREMENT NOT NULL, problemtemplates_id INT DEFAULT 0 NOT NULL, num INT DEFAULT 0 NOT NULL, INDEX problemtemplates_id (problemtemplates_id), UNIQUE INDEX unicity (problemtemplates_id, num), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_problemtemplatepredefinedfields (id INT AUTO_INCREMENT NOT NULL, problemtemplates_id INT DEFAULT 0 NOT NULL, num INT DEFAULT 0 NOT NULL, value TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX problemtemplates_id (problemtemplates_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_problemtemplates (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX is_recursive (is_recursive), INDEX name (name), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_profilerights (id INT AUTO_INCREMENT NOT NULL, profiles_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, rights INT DEFAULT 0 NOT NULL, UNIQUE INDEX unicity (profiles_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_profiles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, interface VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'helpdesk\' COLLATE `utf8mb3_unicode_ci`, is_default TINYINT(1) DEFAULT 0 NOT NULL, helpdesk_hardware INT DEFAULT 0 NOT NULL, helpdesk_item_type TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ticket_status TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'json encoded array of from/dest allowed status change\', date_mod DATETIME DEFAULT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, problem_status TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'json encoded array of from/dest allowed status change\', create_ticket_on_login TINYINT(1) DEFAULT 0 NOT NULL, tickettemplates_id INT DEFAULT 0 NOT NULL, changetemplates_id INT DEFAULT 0 NOT NULL, problemtemplates_id INT DEFAULT 0 NOT NULL, change_status TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'json encoded array of from/dest allowed status change\', managed_domainrecordtypes TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_creation DATETIME DEFAULT NULL, INDEX changetemplates_id (changetemplates_id), INDEX date_mod (date_mod), INDEX problemtemplates_id (problemtemplates_id), INDEX date_creation (date_creation), INDEX interface (interface), INDEX tickettemplates_id (tickettemplates_id), INDEX is_default (is_default), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_profiles_reminders (id INT AUTO_INCREMENT NOT NULL, reminders_id INT DEFAULT 0 NOT NULL, profiles_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT -1 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX is_recursive (is_recursive), INDEX reminders_id (reminders_id), INDEX profiles_id (profiles_id), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_profiles_rssfeeds (id INT AUTO_INCREMENT NOT NULL, rssfeeds_id INT DEFAULT 0 NOT NULL, profiles_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT -1 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX entities_id (entities_id), INDEX is_recursive (is_recursive), INDEX rssfeeds_id (rssfeeds_id), INDEX profiles_id (profiles_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_profiles_users (id INT AUTO_INCREMENT NOT NULL, users_id INT DEFAULT 0 NOT NULL, profiles_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 1 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, is_default_profile TINYINT(1) DEFAULT 0 NOT NULL, INDEX is_recursive (is_recursive), INDEX entities_id (entities_id), INDEX is_dynamic (is_dynamic), INDEX profiles_id (profiles_id), INDEX users_id (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_projectcosts (id INT AUTO_INCREMENT NOT NULL, projects_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, begin_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, cost NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, budgets_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, INDEX name (name), INDEX entities_id (entities_id), INDEX projects_id (projects_id), INDEX is_recursive (is_recursive), INDEX begin_date (begin_date), INDEX budgets_id (budgets_id), INDEX end_date (end_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_projects (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, code VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, priority INT DEFAULT 1 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, projects_id INT DEFAULT 0 NOT NULL, projectstates_id INT DEFAULT 0 NOT NULL, projecttypes_id INT DEFAULT 0 NOT NULL, date DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, users_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, plan_start_date DATETIME DEFAULT NULL, plan_end_date DATETIME DEFAULT NULL, real_start_date DATETIME DEFAULT NULL, real_end_date DATETIME DEFAULT NULL, percent_done INT DEFAULT 0 NOT NULL, auto_percent_done TINYINT(1) DEFAULT 0 NOT NULL, show_on_global_gantt TINYINT(1) DEFAULT 0 NOT NULL, content LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, projecttemplates_id INT DEFAULT 0 NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX real_end_date (real_end_date), INDEX plan_start_date (plan_start_date), INDEX date_mod (date_mod), INDEX projecttypes_id (projecttypes_id), INDEX projecttemplates_id (projecttemplates_id), INDEX is_recursive (is_recursive), INDEX percent_done (percent_done), INDEX name (name), INDEX plan_end_date (plan_end_date), INDEX users_id (users_id), INDEX priority (priority), INDEX is_template (is_template), INDEX projects_id (projects_id), INDEX show_on_global_gantt (show_on_global_gantt), INDEX code (code), INDEX real_start_date (real_start_date), INDEX groups_id (groups_id), INDEX date (date), INDEX projectstates_id (projectstates_id), INDEX date_creation (date_creation), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_projectstates (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, color VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_finished TINYINT(1) DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX name (name), INDEX is_finished (is_finished), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_projecttasks (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, projects_id INT DEFAULT 0 NOT NULL, projecttasks_id INT DEFAULT 0 NOT NULL, date DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, plan_start_date DATETIME DEFAULT NULL, plan_end_date DATETIME DEFAULT NULL, real_start_date DATETIME DEFAULT NULL, real_end_date DATETIME DEFAULT NULL, planned_duration INT DEFAULT 0 NOT NULL, effective_duration INT DEFAULT 0 NOT NULL, projectstates_id INT DEFAULT 0 NOT NULL, projecttasktypes_id INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, percent_done INT DEFAULT 0 NOT NULL, auto_percent_done TINYINT(1) DEFAULT 0 NOT NULL, is_milestone TINYINT(1) DEFAULT 0 NOT NULL, projecttasktemplates_id INT DEFAULT 0 NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX projects_id (projects_id), INDEX name (name), INDEX is_template (is_template), INDEX projectstates_id (projectstates_id), INDEX real_start_date (real_start_date), INDEX users_id (users_id), INDEX projecttasks_id (projecttasks_id), INDEX entities_id (entities_id), INDEX is_milestone (is_milestone), INDEX projecttasktypes_id (projecttasktypes_id), INDEX real_end_date (real_end_date), INDEX plan_start_date (plan_start_date), INDEX date (date), INDEX is_recursive (is_recursive), UNIQUE INDEX uuid (uuid), INDEX projecttasktemplates_id (projecttasktemplates_id), INDEX percent_done (percent_done), INDEX plan_end_date (plan_end_date), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_projecttasks_tickets (id INT AUTO_INCREMENT NOT NULL, tickets_id INT DEFAULT 0 NOT NULL, projecttasks_id INT DEFAULT 0 NOT NULL, UNIQUE INDEX unicity (tickets_id, projecttasks_id), INDEX projects_id (projecttasks_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_projecttaskteams (id INT AUTO_INCREMENT NOT NULL, projecttasks_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, INDEX item (itemtype, items_id), UNIQUE INDEX unicity (projecttasks_id, itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_projecttasktemplates (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, projects_id INT DEFAULT 0 NOT NULL, projecttasks_id INT DEFAULT 0 NOT NULL, plan_start_date DATETIME DEFAULT NULL, plan_end_date DATETIME DEFAULT NULL, real_start_date DATETIME DEFAULT NULL, real_end_date DATETIME DEFAULT NULL, planned_duration INT DEFAULT 0 NOT NULL, effective_duration INT DEFAULT 0 NOT NULL, projectstates_id INT DEFAULT 0 NOT NULL, projecttasktypes_id INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, percent_done INT DEFAULT 0 NOT NULL, is_milestone TINYINT(1) DEFAULT 0 NOT NULL, comments TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX projects_id (projects_id), INDEX name (name), INDEX projectstates_id (projectstates_id), INDEX real_start_date (real_start_date), INDEX users_id (users_id), INDEX projecttasks_id (projecttasks_id), INDEX entities_id (entities_id), INDEX projecttasktypes_id (projecttasktypes_id), INDEX real_end_date (real_end_date), INDEX plan_start_date (plan_start_date), INDEX date_creation (date_creation), INDEX is_recursive (is_recursive), INDEX is_milestone (is_milestone), INDEX percent_done (percent_done), INDEX plan_end_date (plan_end_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_projecttasktypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX name (name), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_projectteams (id INT AUTO_INCREMENT NOT NULL, projects_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, INDEX item (itemtype, items_id), UNIQUE INDEX unicity (projects_id, itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_projecttypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX name (name), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_queuedchats (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, notificationtemplates_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, itilcategories_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, sent_try INT DEFAULT 0 NOT NULL, create_time DATETIME DEFAULT NULL, send_time DATETIME DEFAULT NULL, sent_time DATETIME DEFAULT NULL, entName TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, ticketTitle TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, completName TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, serverName TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, hookurl VARCHAR(250) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, mode VARCHAR(20) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'See Notification_NotificationTemplate::MODE_* constants\', INDEX send_time (send_time), INDEX entities_id (entities_id), INDEX sent_time (sent_time), INDEX sent_try (sent_try), INDEX mode (mode), INDEX create_time (create_time), INDEX is_deleted (is_deleted), INDEX item (itemtype, items_id, notificationtemplates_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_queuednotifications (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, notificationtemplates_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, sent_try INT DEFAULT 0 NOT NULL, create_time DATETIME DEFAULT NULL, send_time DATETIME DEFAULT NULL, sent_time DATETIME DEFAULT NULL, name TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, sender TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, sendername TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, recipient TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, recipientname TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, replyto TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, replytoname TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, headers TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, body_html LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, body_text LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, messageid TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, documents TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, mode VARCHAR(20) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'See Notification_NotificationTemplate::MODE_* constants\', INDEX mode (mode), INDEX create_time (create_time), INDEX is_deleted (is_deleted), INDEX item (itemtype, items_id, notificationtemplates_id), INDEX send_time (send_time), INDEX entities_id (entities_id), INDEX sent_time (sent_time), INDEX sent_try (sent_try), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_rackmodels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, product_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX product_number (product_number), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_racks (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, rackmodels_id INT DEFAULT NULL, manufacturers_id INT DEFAULT 0 NOT NULL, racktypes_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, depth INT DEFAULT NULL, number_units INT DEFAULT 0, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, dcrooms_id INT DEFAULT 0 NOT NULL, room_orientation INT DEFAULT 0 NOT NULL, position VARCHAR(50) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, bgcolor VARCHAR(7) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, max_power INT DEFAULT 0 NOT NULL, mesured_power INT DEFAULT 0 NOT NULL, max_weight INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX dcrooms_id (dcrooms_id), INDEX group_id_tech (groups_id_tech), INDEX racktypes_id (racktypes_id), INDEX locations_id (locations_id), INDEX is_template (is_template), INDEX states_id (states_id), INDEX rackmodels_id (rackmodels_id), INDEX entities_id (entities_id), INDEX is_deleted (is_deleted), INDEX users_id_tech (users_id_tech), INDEX manufacturers_id (manufacturers_id), INDEX is_recursive (is_recursive), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_racktypes (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_creation DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, INDEX is_recursive (is_recursive), INDEX name (name), INDEX date_creation (date_creation), INDEX entities_id (entities_id), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_registeredids (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, device_type VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'USB, PCI ...\', INDEX device_type (device_type), INDEX name (name), INDEX item (items_id, itemtype), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_reminders (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date DATETIME DEFAULT NULL, users_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, text TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, begin DATETIME DEFAULT NULL, end DATETIME DEFAULT NULL, is_planned TINYINT(1) DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, state INT DEFAULT 0 NOT NULL, begin_view_date DATETIME DEFAULT NULL, end_view_date DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX state (state), INDEX end (end), UNIQUE INDEX uuid (uuid), INDEX date_mod (date_mod), INDEX users_id (users_id), INDEX date (date), INDEX date_creation (date_creation), INDEX is_planned (is_planned), INDEX begin (begin), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_reminders_users (id INT AUTO_INCREMENT NOT NULL, reminders_id INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, INDEX reminders_id (reminders_id), INDEX users_id (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_remindertranslations (id INT AUTO_INCREMENT NOT NULL, reminders_id INT DEFAULT 0 NOT NULL, language VARCHAR(5) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, name TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, text LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX item (reminders_id, language), INDEX users_id (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_requesttypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_helpdesk_default TINYINT(1) DEFAULT 0 NOT NULL, is_followup_default TINYINT(1) DEFAULT 0 NOT NULL, is_mail_default TINYINT(1) DEFAULT 0 NOT NULL, is_mailfollowup_default TINYINT(1) DEFAULT 0 NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, is_ticketheader TINYINT(1) DEFAULT 1 NOT NULL, is_itilfollowup TINYINT(1) DEFAULT 1 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX is_ticketheader (is_ticketheader), INDEX date_mod (date_mod), INDEX is_followup_default (is_followup_default), INDEX is_itilfollowup (is_itilfollowup), INDEX date_creation (date_creation), INDEX is_mail_default (is_mail_default), INDEX name (name), INDEX is_active (is_active), INDEX is_mailfollowup_default (is_mailfollowup_default), INDEX is_helpdesk_default (is_helpdesk_default), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_reservationitems (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, items_id INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_active TINYINT(1) DEFAULT 1 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, INDEX is_recursive (is_recursive), INDEX item (itemtype, items_id), INDEX is_deleted (is_deleted), INDEX entities_id (entities_id), INDEX is_active (is_active), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_reservations (id INT AUTO_INCREMENT NOT NULL, reservationitems_id INT DEFAULT 0 NOT NULL, begin DATETIME DEFAULT NULL, end DATETIME DEFAULT NULL, users_id INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, `group` INT DEFAULT 0 NOT NULL, INDEX users_id (users_id), INDEX begin (begin), INDEX resagroup (reservationitems_id, `group`), INDEX end (end), INDEX reservationitems_id (reservationitems_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_rssfeeds (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, users_id INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, url TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, refresh_rate INT DEFAULT 86400 NOT NULL, max_items INT DEFAULT 20 NOT NULL, have_error TINYINT(1) DEFAULT 0 NOT NULL, is_active TINYINT(1) DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX have_error (have_error), INDEX name (name), INDEX is_active (is_active), INDEX users_id (users_id), INDEX date_creation (date_creation), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_rssfeeds_users (id INT AUTO_INCREMENT NOT NULL, rssfeeds_id INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, INDEX rssfeeds_id (rssfeeds_id), INDEX users_id (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_ruleactions (id INT AUTO_INCREMENT NOT NULL, rules_id INT DEFAULT 0 NOT NULL, action_type VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'VALUE IN (assign, regex_result, append_regex_result, affectbyip, affectbyfqdn, affectbymac)\', field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, value VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX rules_id (rules_id), INDEX field_value (field(50), value(50)), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_rulecriterias (id INT AUTO_INCREMENT NOT NULL, rules_id INT DEFAULT 0 NOT NULL, criteria VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, `condition` INT DEFAULT 0 NOT NULL COMMENT \'see define.php PATTERN_* and REGEX_* constant\', pattern TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX `condition` (`condition`), INDEX rules_id (rules_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_rulerightparameters (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, value VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_rules (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, sub_type VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, ranking INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, description TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, `match` CHAR(10) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'see define.php *_MATCHING constant\', is_active TINYINT(1) DEFAULT 1 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, uuid VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, `condition` INT DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, INDEX `condition` (`condition`), INDEX sub_type (sub_type), INDEX date_creation (date_creation), INDEX date_mod (date_mod), INDEX entities_id (entities_id), INDEX is_recursive (is_recursive), INDEX is_active (is_active), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_savedsearches (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, type INT DEFAULT 0 NOT NULL COMMENT \'see SavedSearch:: constants\', itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, users_id INT DEFAULT 0 NOT NULL, is_private TINYINT(1) DEFAULT 1 NOT NULL, entities_id INT DEFAULT -1 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, path VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, query TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, last_execution_time INT DEFAULT NULL, do_count TINYINT(1) DEFAULT 2 NOT NULL COMMENT \'Do or do not count results on list display see SavedSearch::COUNT_* constants\', last_execution_date DATETIME DEFAULT NULL, counter INT DEFAULT 0 NOT NULL, INDEX users_id (users_id), INDEX type (type), INDEX last_execution_date (last_execution_date), INDEX is_private (is_private), INDEX itemtype (itemtype), INDEX do_count (do_count), INDEX is_recursive (is_recursive), INDEX entities_id (entities_id), INDEX last_execution_time (last_execution_time), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_savedsearches_alerts (id INT AUTO_INCREMENT NOT NULL, savedsearches_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_active TINYINT(1) DEFAULT 0 NOT NULL, operator TINYINT(1) NOT NULL, value INT NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX name (name), UNIQUE INDEX unicity (savedsearches_id, operator, value), INDEX is_active (is_active), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_savedsearches_users (id INT AUTO_INCREMENT NOT NULL, users_id INT DEFAULT 0 NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, savedsearches_id INT DEFAULT 0 NOT NULL, INDEX savedsearches_id (savedsearches_id), UNIQUE INDEX unicity (users_id, itemtype), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_slalevelactions (id INT AUTO_INCREMENT NOT NULL, slalevels_id INT DEFAULT 0 NOT NULL, action_type VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, value VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX slalevels_id (slalevels_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_slalevelcriterias (id INT AUTO_INCREMENT NOT NULL, slalevels_id INT DEFAULT 0 NOT NULL, criteria VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, `condition` INT DEFAULT 0 NOT NULL COMMENT \'see define.php PATTERN_* and REGEX_* constant\', pattern VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX slalevels_id (slalevels_id), INDEX `condition` (`condition`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_slalevels (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, slas_id INT DEFAULT 0 NOT NULL, execution_time INT NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, `match` CHAR(10) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'see define.php *_MATCHING constant\', uuid VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX slas_id (slas_id), INDEX name (name), INDEX is_active (is_active), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_slalevels_tickets (id INT AUTO_INCREMENT NOT NULL, tickets_id INT DEFAULT 0 NOT NULL, slalevels_id INT DEFAULT 0 NOT NULL, date DATETIME DEFAULT NULL, INDEX tickets_id (tickets_id), INDEX slalevels_id (slalevels_id), UNIQUE INDEX unicity (tickets_id, slalevels_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_slas (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, type INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, number_time INT NOT NULL, calendars_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, definition_time VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, end_of_working_day TINYINT(1) DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, slms_id INT DEFAULT 0 NOT NULL, INDEX calendars_id (calendars_id), INDEX name (name), INDEX slms_id (slms_id), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_slms (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, calendars_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX calendars_id (calendars_id), INDEX name (name), INDEX date_mod (date_mod), INDEX entities_id (entities_id), INDEX date_creation (date_creation), INDEX is_recursive (is_recursive), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_softwarecategories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, softwarecategories_id INT DEFAULT 0 NOT NULL, completename TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, level INT DEFAULT 0 NOT NULL, ancestors_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, sons_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX softwarecategories_id (softwarecategories_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_softwarelicenses (id INT AUTO_INCREMENT NOT NULL, softwares_id INT DEFAULT 0 NOT NULL, softwarelicenses_id INT DEFAULT 0 NOT NULL, completename TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, level INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, number INT DEFAULT 0 NOT NULL, softwarelicensetypes_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, serial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, otherserial VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, softwareversions_id_buy INT DEFAULT 0 NOT NULL, softwareversions_id_use INT DEFAULT 0 NOT NULL, expire DATE DEFAULT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, is_valid TINYINT(1) DEFAULT 1 NOT NULL, date_creation DATETIME DEFAULT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, users_id_tech INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, is_helpdesk_visible TINYINT(1) DEFAULT 0 NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, states_id INT DEFAULT 0 NOT NULL, manufacturers_id INT DEFAULT 0 NOT NULL, contact VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, contact_num VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, allow_overquota TINYINT(1) DEFAULT 0 NOT NULL, INDEX groups_id_tech (groups_id_tech), INDEX name (name), INDEX locations_id (locations_id), INDEX softwares_id_expire_number (softwares_id, expire, number), INDEX allow_overquota (allow_overquota), INDEX softwarelicensetypes_id (softwarelicensetypes_id), INDEX date_creation (date_creation), INDEX expire (expire), INDEX groups_id (groups_id), INDEX is_template (is_template), INDEX users_id_tech (users_id_tech), INDEX softwareversions_id_use (softwareversions_id_use), INDEX manufacturers_id (manufacturers_id), INDEX softwareversions_id_buy (softwareversions_id_buy), INDEX is_helpdesk_visible (is_helpdesk_visible), INDEX serial (serial), INDEX users_id (users_id), INDEX date_mod (date_mod), INDEX states_id (states_id), INDEX entities_id (entities_id), INDEX is_deleted (is_deleted), INDEX otherserial (otherserial), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_softwarelicensetypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, softwarelicensetypes_id INT DEFAULT 0 NOT NULL, level INT DEFAULT 0 NOT NULL, ancestors_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, sons_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, completename TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX softwarelicensetypes_id (softwarelicensetypes_id), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_softwares (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, is_update TINYINT(1) DEFAULT 0 NOT NULL, softwares_id INT DEFAULT 0 NOT NULL, manufacturers_id INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, is_template TINYINT(1) DEFAULT 0 NOT NULL, template_name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, users_id INT DEFAULT 0 NOT NULL, groups_id INT DEFAULT 0 NOT NULL, ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\', is_helpdesk_visible TINYINT(1) DEFAULT 1 NOT NULL, softwarecategories_id INT DEFAULT 0 NOT NULL, is_valid TINYINT(1) DEFAULT 1 NOT NULL, date_creation DATETIME DEFAULT NULL, INDEX is_template (is_template), INDEX is_deleted (is_deleted), INDEX locations_id (locations_id), INDEX manufacturers_id (manufacturers_id), INDEX is_update (is_update), INDEX is_helpdesk_visible (is_helpdesk_visible), INDEX date_mod (date_mod), INDEX users_id_tech (users_id_tech), INDEX groups_id (groups_id), INDEX softwarecategories_id (softwarecategories_id), INDEX groups_id_tech (groups_id_tech), INDEX name (name), INDEX softwares_id (softwares_id), INDEX users_id (users_id), INDEX entities_id (entities_id), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_softwareversions (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, softwares_id INT DEFAULT 0 NOT NULL, states_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, operatingsystems_id INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX operatingsystems_id (operatingsystems_id), INDEX states_id (states_id), INDEX date_mod (date_mod), INDEX entities_id (entities_id), INDEX name (name), INDEX date_creation (date_creation), INDEX is_recursive (is_recursive), INDEX softwares_id (softwares_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_solutiontemplates (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, content TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, solutiontypes_id INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX solutiontypes_id (solutiontypes_id), INDEX entities_id (entities_id), INDEX name (name), INDEX date_mod (date_mod), INDEX is_recursive (is_recursive), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_solutiontypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 1 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX is_recursive (is_recursive), INDEX date_mod (date_mod), INDEX name (name), INDEX date_creation (date_creation), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_specialstatuses (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, weight INT DEFAULT 1 NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, color VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_ssovariables (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_creation (date_creation), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_states (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, states_id INT DEFAULT 0 NOT NULL, completename TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, level INT DEFAULT 0 NOT NULL, ancestors_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, sons_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_visible_computer TINYINT(1) DEFAULT 1 NOT NULL, is_visible_monitor TINYINT(1) DEFAULT 1 NOT NULL, is_visible_networkequipment TINYINT(1) DEFAULT 1 NOT NULL, is_visible_peripheral TINYINT(1) DEFAULT 1 NOT NULL, is_visible_phone TINYINT(1) DEFAULT 1 NOT NULL, is_visible_printer TINYINT(1) DEFAULT 1 NOT NULL, is_visible_softwareversion TINYINT(1) DEFAULT 1 NOT NULL, is_visible_softwarelicense TINYINT(1) DEFAULT 1 NOT NULL, is_visible_line TINYINT(1) DEFAULT 1 NOT NULL, is_visible_certificate TINYINT(1) DEFAULT 1 NOT NULL, is_visible_rack TINYINT(1) DEFAULT 1 NOT NULL, is_visible_passivedcequipment TINYINT(1) DEFAULT 1 NOT NULL, is_visible_enclosure TINYINT(1) DEFAULT 1 NOT NULL, is_visible_pdu TINYINT(1) DEFAULT 1 NOT NULL, is_visible_cluster TINYINT(1) DEFAULT 1 NOT NULL, is_visible_contract TINYINT(1) DEFAULT 1 NOT NULL, is_visible_appliance TINYINT(1) DEFAULT 1 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX is_visible_cluster (is_visible_cluster), INDEX is_visible_passivedcequipment (is_visible_passivedcequipment), INDEX is_visible_line (is_visible_line), INDEX is_visible_printer (is_visible_printer), INDEX date_creation (date_creation), INDEX is_visible_networkequipment (is_visible_networkequipment), INDEX name (name), INDEX is_visible_contract (is_visible_contract), INDEX is_visible_enclosure (is_visible_enclosure), INDEX is_visible_certificate (is_visible_certificate), INDEX is_visible_softwareversion (is_visible_softwareversion), INDEX is_visible_peripheral (is_visible_peripheral), INDEX is_visible_computer (is_visible_computer), INDEX is_visible_appliance (is_visible_appliance), UNIQUE INDEX unicity (states_id, name), INDEX is_visible_pdu (is_visible_pdu), INDEX is_visible_rack (is_visible_rack), INDEX is_visible_softwarelicense (is_visible_softwarelicense), INDEX is_visible_phone (is_visible_phone), INDEX is_visible_monitor (is_visible_monitor), INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_suppliers (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, suppliertypes_id INT DEFAULT 0 NOT NULL, address TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, postcode VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, town VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, state VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, country VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, website VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, phonenumber VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, fax VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, is_active TINYINT(1) DEFAULT 0 NOT NULL, INDEX date_mod (date_mod), INDEX entities_id (entities_id), INDEX date_creation (date_creation), INDEX suppliertypes_id (suppliertypes_id), INDEX is_active (is_active), INDEX is_deleted (is_deleted), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_suppliers_tickets (id INT AUTO_INCREMENT NOT NULL, tickets_id INT DEFAULT 0 NOT NULL, suppliers_id INT DEFAULT 0 NOT NULL, type INT DEFAULT 1 NOT NULL, use_notification TINYINT(1) DEFAULT 1 NOT NULL, alternative_email VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX `group` (suppliers_id, type), UNIQUE INDEX unicity (tickets_id, type, suppliers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_suppliertypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_taskcategories (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, taskcategories_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, completename TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, level INT DEFAULT 0 NOT NULL, ancestors_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, sons_cache LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_active TINYINT(1) DEFAULT 1 NOT NULL, is_helpdeskvisible TINYINT(1) DEFAULT 1 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, knowbaseitemcategories_id INT DEFAULT 0 NOT NULL, INDEX knowbaseitemcategories_id (knowbaseitemcategories_id), INDEX is_helpdeskvisible (is_helpdeskvisible), INDEX entities_id (entities_id), INDEX date_mod (date_mod), INDEX is_recursive (is_recursive), INDEX name (name), INDEX date_creation (date_creation), INDEX is_active (is_active), INDEX taskcategories_id (taskcategories_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_tasktemplates (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, content TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, taskcategories_id INT DEFAULT 0 NOT NULL, actiontime INT DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, state INT DEFAULT 0 NOT NULL, is_private TINYINT(1) DEFAULT 0 NOT NULL, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, INDEX groups_id_tech (groups_id_tech), INDEX date_creation (date_creation), INDEX taskcategories_id (taskcategories_id), INDEX is_private (is_private), INDEX entities_id (entities_id), INDEX name (name), INDEX users_id_tech (users_id_tech), INDEX date_mod (date_mod), INDEX is_recursive (is_recursive), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_ticketcosts (id INT AUTO_INCREMENT NOT NULL, tickets_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, begin_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, actiontime INT DEFAULT 0 NOT NULL, cost_time NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, cost_fixed NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, cost_material NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, budgets_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, INDEX entities_id (entities_id), INDEX tickets_id (tickets_id), INDEX budgets_id (budgets_id), INDEX begin_date (begin_date), INDEX end_date (end_date), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_ticketrecurrents (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, is_active TINYINT(1) DEFAULT 0 NOT NULL, tickettemplates_id INT DEFAULT 0 NOT NULL, begin_date DATETIME DEFAULT NULL, periodicity VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, create_before INT DEFAULT 0 NOT NULL, next_creation_date DATETIME DEFAULT NULL, calendars_id INT DEFAULT 0 NOT NULL, end_date DATETIME DEFAULT NULL, INDEX next_creation_date (next_creation_date), INDEX is_recursive (is_recursive), INDEX is_active (is_active), INDEX tickettemplates_id (tickettemplates_id), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_tickets (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date DATETIME DEFAULT NULL, closedate DATETIME DEFAULT NULL, solvedate DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, users_id_lastupdater INT DEFAULT 0 NOT NULL, status INT DEFAULT 1 NOT NULL, users_id_recipient INT DEFAULT 0 NOT NULL, requesttypes_id INT DEFAULT 0 NOT NULL, content LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, urgency INT DEFAULT 1 NOT NULL, impact INT DEFAULT 1 NOT NULL, priority INT DEFAULT 1 NOT NULL, itilcategories_id INT DEFAULT 0 NOT NULL, type INT DEFAULT 1 NOT NULL, global_validation INT DEFAULT 1 NOT NULL, slas_id_ttr INT DEFAULT 0 NOT NULL, slas_id_tto INT DEFAULT 0 NOT NULL, slalevels_id_ttr INT DEFAULT 0 NOT NULL, time_to_resolve DATETIME DEFAULT NULL, time_to_own DATETIME DEFAULT NULL, begin_waiting_date DATETIME DEFAULT NULL, sla_waiting_duration INT DEFAULT 0 NOT NULL, ola_waiting_duration INT DEFAULT 0 NOT NULL, olas_id_tto INT DEFAULT 0 NOT NULL, olas_id_ttr INT DEFAULT 0 NOT NULL, olalevels_id_ttr INT DEFAULT 0 NOT NULL, ola_ttr_begin_date DATETIME DEFAULT NULL, internal_time_to_resolve DATETIME DEFAULT NULL, internal_time_to_own DATETIME DEFAULT NULL, waiting_duration INT DEFAULT 0 NOT NULL, close_delay_stat INT DEFAULT 0 NOT NULL, solve_delay_stat INT DEFAULT 0 NOT NULL, takeintoaccount_delay_stat INT DEFAULT 0 NOT NULL, actiontime INT DEFAULT 0 NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, locations_id INT DEFAULT 0 NOT NULL, validation_percent INT DEFAULT 0 NOT NULL, date_creation DATETIME DEFAULT NULL, INDEX users_id_recipient (users_id_recipient), INDEX itilcategories_id (itilcategories_id), INDEX request_type (requesttypes_id), INDEX internal_time_to_own (internal_time_to_own), INDEX closedate (closedate), INDEX olas_id_ttr (olas_id_ttr), INDEX time_to_resolve (time_to_resolve), INDEX date_creation (date_creation), INDEX global_validation (global_validation), INDEX is_deleted (is_deleted), INDEX solvedate (solvedate), INDEX date_mod (date_mod), INDEX users_id_lastupdater (users_id_lastupdater), INDEX status (status), INDEX slalevels_id_ttr (slalevels_id_ttr), INDEX time_to_own (time_to_own), INDEX ola_waiting_duration (ola_waiting_duration), INDEX slas_id_tto (slas_id_tto), INDEX name (name), INDEX urgency (urgency), INDEX entities_id (entities_id), INDEX type (type), INDEX priority (priority), INDEX internal_time_to_resolve (internal_time_to_resolve), INDEX date (date), INDEX olas_id_tto (olas_id_tto), INDEX olalevels_id_ttr (olalevels_id_ttr), INDEX slas_id_ttr (slas_id_ttr), INDEX locations_id (locations_id), INDEX impact (impact), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_tickets_tickets (id INT AUTO_INCREMENT NOT NULL, tickets_id_1 INT DEFAULT 0 NOT NULL, tickets_id_2 INT DEFAULT 0 NOT NULL, link INT DEFAULT 1 NOT NULL, UNIQUE INDEX unicity (tickets_id_1, tickets_id_2), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_tickets_users (id INT AUTO_INCREMENT NOT NULL, tickets_id INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, type INT DEFAULT 1 NOT NULL, use_notification TINYINT(1) DEFAULT 1 NOT NULL, alternative_email VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, UNIQUE INDEX unicity (tickets_id, type, users_id, alternative_email), INDEX user (users_id, type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_ticketsatisfactions (id INT AUTO_INCREMENT NOT NULL, tickets_id INT DEFAULT 0 NOT NULL, type INT DEFAULT 1 NOT NULL, date_begin DATETIME DEFAULT NULL, date_answered DATETIME DEFAULT NULL, satisfaction INT DEFAULT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, UNIQUE INDEX tickets_id (tickets_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_tickettasks (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, tickets_id INT DEFAULT 0 NOT NULL, taskcategories_id INT DEFAULT 0 NOT NULL, date DATETIME DEFAULT NULL, users_id INT DEFAULT 0 NOT NULL, users_id_editor INT DEFAULT 0 NOT NULL, content LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_private TINYINT(1) DEFAULT 0 NOT NULL, actiontime INT DEFAULT 0 NOT NULL, begin DATETIME DEFAULT NULL, end DATETIME DEFAULT NULL, state INT DEFAULT 1 NOT NULL, users_id_tech INT DEFAULT 0 NOT NULL, groups_id_tech INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, tasktemplates_id INT DEFAULT 0 NOT NULL, timeline_position TINYINT(1) DEFAULT 0 NOT NULL, sourceitems_id INT DEFAULT 0 NOT NULL, INDEX tickets_id (tickets_id), INDEX date_creation (date_creation), UNIQUE INDEX uuid (uuid), INDEX end (end), INDEX users_id_tech (users_id_tech), INDEX is_private (is_private), INDEX users_id (users_id), INDEX date (date), INDEX tasktemplates_id (tasktemplates_id), INDEX groups_id_tech (groups_id_tech), INDEX taskcategories_id (taskcategories_id), INDEX users_id_editor (users_id_editor), INDEX date_mod (date_mod), INDEX sourceitems_id (sourceitems_id), INDEX begin (begin), INDEX state (state), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_tickettemplatehiddenfields (id INT AUTO_INCREMENT NOT NULL, tickettemplates_id INT DEFAULT 0 NOT NULL, num INT DEFAULT 0 NOT NULL, INDEX tickettemplates_id (tickettemplates_id), UNIQUE INDEX unicity (tickettemplates_id, num), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_tickettemplatemandatoryfields (id INT AUTO_INCREMENT NOT NULL, tickettemplates_id INT DEFAULT 0 NOT NULL, num INT DEFAULT 0 NOT NULL, INDEX tickettemplates_id (tickettemplates_id), UNIQUE INDEX unicity (tickettemplates_id, num), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_tickettemplatepredefinedfields (id INT AUTO_INCREMENT NOT NULL, tickettemplates_id INT DEFAULT 0 NOT NULL, num INT DEFAULT 0 NOT NULL, value TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX tickettemplates_id (tickettemplates_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_tickettemplates (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX is_recursive (is_recursive), INDEX name (name), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_ticketvalidations (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, users_id INT DEFAULT 0 NOT NULL, tickets_id INT DEFAULT 0 NOT NULL, users_id_validate INT DEFAULT 0 NOT NULL, comment_submission TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment_validation TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, status INT DEFAULT 2 NOT NULL, submission_date DATETIME DEFAULT NULL, validation_date DATETIME DEFAULT NULL, timeline_position TINYINT(1) DEFAULT 0 NOT NULL, INDEX submission_date (submission_date), INDEX users_id (users_id), INDEX validation_date (validation_date), INDEX users_id_validate (users_id_validate), INDEX status (status), INDEX tickets_id (tickets_id), INDEX entities_id (entities_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_transfers (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, keep_ticket INT DEFAULT 0 NOT NULL, keep_networklink INT DEFAULT 0 NOT NULL, keep_reservation INT DEFAULT 0 NOT NULL, keep_history INT DEFAULT 0 NOT NULL, keep_device INT DEFAULT 0 NOT NULL, keep_infocom INT DEFAULT 0 NOT NULL, keep_dc_monitor INT DEFAULT 0 NOT NULL, clean_dc_monitor INT DEFAULT 0 NOT NULL, keep_dc_phone INT DEFAULT 0 NOT NULL, clean_dc_phone INT DEFAULT 0 NOT NULL, keep_dc_peripheral INT DEFAULT 0 NOT NULL, clean_dc_peripheral INT DEFAULT 0 NOT NULL, keep_dc_printer INT DEFAULT 0 NOT NULL, clean_dc_printer INT DEFAULT 0 NOT NULL, keep_supplier INT DEFAULT 0 NOT NULL, clean_supplier INT DEFAULT 0 NOT NULL, keep_contact INT DEFAULT 0 NOT NULL, clean_contact INT DEFAULT 0 NOT NULL, keep_contract INT DEFAULT 0 NOT NULL, clean_contract INT DEFAULT 0 NOT NULL, keep_software INT DEFAULT 0 NOT NULL, clean_software INT DEFAULT 0 NOT NULL, keep_document INT DEFAULT 0 NOT NULL, clean_document INT DEFAULT 0 NOT NULL, keep_cartridgeitem INT DEFAULT 0 NOT NULL, clean_cartridgeitem INT DEFAULT 0 NOT NULL, keep_cartridge INT DEFAULT 0 NOT NULL, keep_consumable INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, keep_disk INT DEFAULT 0 NOT NULL, INDEX date_mod (date_mod), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_usercategories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_useremails (id INT AUTO_INCREMENT NOT NULL, users_id INT DEFAULT 0 NOT NULL, is_default TINYINT(1) DEFAULT 0 NOT NULL, is_dynamic TINYINT(1) DEFAULT 0 NOT NULL, email VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX is_default (is_default), UNIQUE INDEX unicity (users_id, email), INDEX is_dynamic (is_dynamic), INDEX email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_users (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, password VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, password_last_update DATETIME DEFAULT NULL, phone VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, phone2 VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, mobile VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, realname VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, firstname VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, locations_id INT DEFAULT 0 NOT NULL, language CHAR(10) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'see define.php CFG_GLPI[language] array\', use_mode INT DEFAULT 0 NOT NULL, list_limit INT DEFAULT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, auths_id INT DEFAULT 0 NOT NULL, authtype INT DEFAULT 0 NOT NULL, last_login DATETIME DEFAULT NULL, date_mod DATETIME DEFAULT NULL, date_sync DATETIME DEFAULT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, profiles_id INT DEFAULT 0 NOT NULL, entities_id INT DEFAULT 0 NOT NULL, usertitles_id INT DEFAULT 0 NOT NULL, usercategories_id INT DEFAULT 0 NOT NULL, date_format INT DEFAULT NULL, number_format INT DEFAULT NULL, names_format INT DEFAULT NULL, csv_delimiter CHAR(1) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_ids_visible TINYINT(1) DEFAULT NULL, use_flat_dropdowntree TINYINT(1) DEFAULT NULL, show_jobs_at_login TINYINT(1) DEFAULT NULL, priority_1 CHAR(20) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, priority_2 CHAR(20) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, priority_3 CHAR(20) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, priority_4 CHAR(20) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, priority_5 CHAR(20) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, priority_6 CHAR(20) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, followup_private TINYINT(1) DEFAULT NULL, task_private TINYINT(1) DEFAULT NULL, default_requesttypes_id INT DEFAULT NULL, password_forget_token CHAR(40) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, password_forget_token_date DATETIME DEFAULT NULL, user_dn TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, registration_number VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, show_count_on_tabs TINYINT(1) DEFAULT NULL, refresh_views INT DEFAULT NULL, set_default_tech TINYINT(1) DEFAULT NULL, personal_token VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, personal_token_date DATETIME DEFAULT NULL, api_token VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, api_token_date DATETIME DEFAULT NULL, cookie_token VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, cookie_token_date DATETIME DEFAULT NULL, display_count_on_home INT DEFAULT NULL, notification_to_myself TINYINT(1) DEFAULT NULL, duedateok_color VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, duedatewarning_color VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, duedatecritical_color VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, duedatewarning_less INT DEFAULT NULL, duedatecritical_less INT DEFAULT NULL, duedatewarning_unit VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, duedatecritical_unit VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, display_options TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, is_deleted_ldap TINYINT(1) DEFAULT 0 NOT NULL, pdffont VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, picture VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, begin_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, keep_devices_when_purging_item TINYINT(1) DEFAULT NULL, privatebookmarkorder LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, backcreated TINYINT(1) DEFAULT NULL, task_state INT DEFAULT NULL, layout CHAR(20) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, palette CHAR(20) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, set_default_requester TINYINT(1) DEFAULT NULL, lock_autolock_mode TINYINT(1) DEFAULT NULL, lock_directunlock_notification TINYINT(1) DEFAULT NULL, date_creation DATETIME DEFAULT NULL, highcontrast_css TINYINT(1) DEFAULT 0, plannings TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, sync_field VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, groups_id INT DEFAULT 0 NOT NULL, users_id_supervisor INT DEFAULT 0 NOT NULL, timezone VARCHAR(50) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, default_dashboard_central VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, default_dashboard_assets VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, default_dashboard_helpdesk VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, default_dashboard_mini_ticket VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, access_zoom_level SMALLINT DEFAULT 100, access_font VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, access_shortcuts TINYINT(1) DEFAULT 0, access_custom_shortcuts JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', menu_favorite LONGTEXT CHARACTER SET utf8mb3 DEFAULT \'{}\' COLLATE `utf8mb3_unicode_ci`, menu_favorite_on TEXT CHARACTER SET utf8mb3 DEFAULT \'1\' COLLATE `utf8mb3_unicode_ci`, menu_position TEXT CHARACTER SET utf8mb3 DEFAULT \'menu-left\' COLLATE `utf8mb3_unicode_ci`, menu_small TEXT CHARACTER SET utf8mb3 DEFAULT \'false\' COLLATE `utf8mb3_unicode_ci`, menu_open LONGTEXT CHARACTER SET utf8mb3 DEFAULT \'[]\' COLLATE `utf8mb3_unicode_ci`, INDEX firstname (firstname), INDEX sync_field (sync_field), UNIQUE INDEX unicityloginauth (name, authtype, auths_id), INDEX date_creation (date_creation), INDEX authitem (authtype, auths_id), INDEX is_deleted (is_deleted), INDEX locations_id (locations_id), INDEX realname (realname), INDEX groups_id (groups_id), INDEX begin_date (begin_date), INDEX is_active (is_active), INDEX usertitles_id (usertitles_id), INDEX entities_id (entities_id), INDEX users_id_supervisor (users_id_supervisor), INDEX end_date (end_date), INDEX is_deleted_ldap (is_deleted_ldap), INDEX date_mod (date_mod), INDEX usercategories_id (usercategories_id), INDEX profiles_id (profiles_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_usertitles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_virtualmachinestates (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_virtualmachinesystems (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_virtualmachinetypes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT \'\' NOT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX date_creation (date_creation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_vlans (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, tag INT DEFAULT 0 NOT NULL, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX date_mod (date_mod), INDEX name (name), INDEX date_creation (date_creation), INDEX entities_id (entities_id), INDEX tag (tag), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_vobjects (id INT AUTO_INCREMENT NOT NULL, itemtype VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, items_id INT DEFAULT 0 NOT NULL, data TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, UNIQUE INDEX unicity (itemtype, items_id), INDEX date_mod (date_mod), INDEX date_creation (date_creation), INDEX item (itemtype, items_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('CREATE TABLE glpi_wifinetworks (id INT AUTO_INCREMENT NOT NULL, entities_id INT DEFAULT 0 NOT NULL, is_recursive TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, essid VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, mode VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'ad-hoc, access_point\', comment TEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, date_mod DATETIME DEFAULT NULL, date_creation DATETIME DEFAULT NULL, INDEX name (name), INDEX date_mod (date_mod), INDEX entities_id (entities_id), INDEX date_creation (date_creation), INDEX essid (essid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_alerts');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_apiclients');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_applianceenvironments');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_appliances');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_appliances_items');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_appliances_items_relations');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_appliancetypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_authldapreplicates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_authldaps');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_authmails');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_autoupdatesystems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_blacklistedmailcontents');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_blacklists');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_budgets');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_budgettypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_businesscriticities');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_calendars');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_calendars_holidays');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_calendarsegments');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_cartridgeitems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_cartridgeitems_printermodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_cartridgeitemtypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_cartridges');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_certificates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_certificates_items');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_certificatetypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_changecosts');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_changes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_changes_groups');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_changes_items');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_changes_problems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_changes_suppliers');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_changes_tickets');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_changes_users');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_changetasks');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_changetemplatehiddenfields');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_changetemplatemandatoryfields');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_changetemplatepredefinedfields');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_changetemplates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_changevalidations');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_clusters');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_clustertypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_computerantiviruses');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_computermodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_computers');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_computers_items');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_computertypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_computervirtualmachines');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_configs');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_consumableitems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_consumableitemtypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_consumables');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_contacts');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_contacts_suppliers');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_contacttypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_contractcosts');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_contracts');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_contracts_items');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_contracts_suppliers');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_contracttypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_crontasklogs');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_crontasks');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_dashboards');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_datacenters');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_dcrooms');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicebatteries');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicebatterymodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicebatterytypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicecasemodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicecases');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicecasetypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicecontrolmodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicecontrols');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicedrivemodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicedrives');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicefirmwaremodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicefirmwares');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicefirmwaretypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicegenericmodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicegenerics');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicegenerictypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicegraphiccardmodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicegraphiccards');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_deviceharddrivemodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_deviceharddrives');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicememories');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicememorymodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicememorytypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicemotherboardmodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicemotherboards');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicenetworkcardmodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicenetworkcards');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicepcimodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicepcis');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicepowersupplies');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicepowersupplymodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_deviceprocessormodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_deviceprocessors');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicesensormodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicesensors');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicesensortypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicesimcards');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicesimcardtypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicesoundcardmodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_devicesoundcards');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_displaypreferences');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_documentcategories');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_documents');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_documents_items');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_documenttypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_domainrecords');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_domainrecordtypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_domainrelations');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_domains');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_domains_items');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_domaintypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_dropdowntranslations');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_enclosuremodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_enclosures');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_entities');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_entities_knowbaseitems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_entities_reminders');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_entities_rssfeeds');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_events');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_fieldblacklists');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_fieldunicities');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_filesystems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_fqdns');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_groups');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_groups_knowbaseitems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_groups_problems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_groups_reminders');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_groups_rssfeeds');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_groups_tickets');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_groups_users');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_holidays');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_impactcompounds');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_impactcontexts');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_impactitems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_impactrelations');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_infocoms');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_interfacetypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_ipaddresses');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_ipaddresses_ipnetworks');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_ipnetworks');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_ipnetworks_vlans');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_clusters');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicebatteries');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicecases');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicecontrols');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicedrives');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicefirmwares');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicegenerics');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicegraphiccards');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_deviceharddrives');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicememories');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicemotherboards');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicenetworkcards');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicepcis');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicepowersupplies');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_deviceprocessors');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicesensors');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicesimcards');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_devicesoundcards');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_disks');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_enclosures');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_kanbans');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_operatingsystems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_problems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_projects');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_racks');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_softwarelicenses');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_softwareversions');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_items_tickets');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_itilcategories');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_itilfollowups');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_itilfollowuptemplates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_itils_projects');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_itilsolutions');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_knowbaseitemcategories');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_knowbaseitems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_knowbaseitems_comments');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_knowbaseitems_items');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_knowbaseitems_profiles');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_knowbaseitems_revisions');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_knowbaseitems_users');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_knowbaseitemtranslations');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_lineoperators');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_lines');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_linetypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_links');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_links_itemtypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_locations');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_logs');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_mailcollectors');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_manufacturers');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_monitormodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_monitors');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_monitortypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_netpoints');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkaliases');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkequipmentmodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkequipments');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkequipmenttypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkinterfaces');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networknames');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkportaggregates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkportaliases');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkportdialups');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkportethernets');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkportfiberchannels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkportlocals');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkports');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkports_networkports');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkports_vlans');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networkportwifis');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_networks');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_notepads');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_notificationchatconfigs');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_notifications');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_notifications_notificationtemplates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_notificationtargets');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_notificationtemplates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_notificationtemplatetranslations');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_notimportedemails');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_objectlocks');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_oidc_config');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_oidc_mapping');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_oidc_users');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_olalevelactions');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_olalevelcriterias');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_olalevels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_olalevels_tickets');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_olas');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_operatingsystemarchitectures');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_operatingsystemeditions');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_operatingsystemkernels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_operatingsystemkernelversions');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_operatingsystems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_operatingsystemservicepacks');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_operatingsystemversions');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_passivedcequipmentmodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_passivedcequipments');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_passivedcequipmenttypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_pdumodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_pdus');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_pdus_plugs');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_pdus_racks');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_pdutypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_peripheralmodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_peripherals');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_peripheraltypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_phonemodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_phonepowersupplies');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_phones');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_phonetypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_planningeventcategories');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_planningexternalevents');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_planningexternaleventtemplates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_planningrecalls');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_plugins');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_plugs');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_printermodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_printers');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_printertypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_problemcosts');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_problems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_problems_suppliers');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_problems_tickets');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_problems_users');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_problemtasks');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_problemtemplatehiddenfields');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_problemtemplatemandatoryfields');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_problemtemplatepredefinedfields');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_problemtemplates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_profilerights');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_profiles');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_profiles_reminders');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_profiles_rssfeeds');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_profiles_users');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_projectcosts');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_projects');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_projectstates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_projecttasks');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_projecttasks_tickets');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_projecttaskteams');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_projecttasktemplates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_projecttasktypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_projectteams');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_projecttypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_queuedchats');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_queuednotifications');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_rackmodels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_racks');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_racktypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_registeredids');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_reminders');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_reminders_users');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_remindertranslations');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_requesttypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_reservationitems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_reservations');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_rssfeeds');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_rssfeeds_users');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_ruleactions');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_rulecriterias');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_rulerightparameters');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_rules');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_savedsearches');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_savedsearches_alerts');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_savedsearches_users');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_slalevelactions');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_slalevelcriterias');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_slalevels');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_slalevels_tickets');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_slas');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_slms');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_softwarecategories');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_softwarelicenses');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_softwarelicensetypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_softwares');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_softwareversions');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_solutiontemplates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_solutiontypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_specialstatuses');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_ssovariables');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_states');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_suppliers');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_suppliers_tickets');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_suppliertypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_taskcategories');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_tasktemplates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_ticketcosts');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_ticketrecurrents');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_tickets');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_tickets_tickets');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_tickets_users');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_ticketsatisfactions');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_tickettasks');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_tickettemplatehiddenfields');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_tickettemplatemandatoryfields');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_tickettemplatepredefinedfields');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_tickettemplates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_ticketvalidations');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_transfers');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_usercategories');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_useremails');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_users');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_usertitles');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_virtualmachinestates');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_virtualmachinesystems');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_virtualmachinetypes');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_vlans');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_vobjects');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql('DROP TABLE glpi_wifinetworks');
    }
}