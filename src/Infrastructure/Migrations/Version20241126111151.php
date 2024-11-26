<?php

declare(strict_types=1);

namespace Itsmng\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241126111151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE glpi_budgets CHANGE value value NUMERIC(20, 4) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_changecosts CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_computers CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_dashboards CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('DROP INDEX IDX_7286AF61FF7A3925 ON glpi_groups');
        $this->addSql('DROP INDEX IDX_7286AF61B49CA1EA ON glpi_groups');
        $this->addSql('CREATE FULLTEXT INDEX IDX_7286AF61FF7A3925 ON glpi_groups (ldap_value)');
        $this->addSql('CREATE FULLTEXT INDEX IDX_7286AF61B49CA1EA ON glpi_groups (ldap_group_dn)');
        $this->addSql('ALTER TABLE glpi_infocoms CHANGE value value NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE warranty_value warranty_value NUMERIC(20, 4) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_monitors CHANGE size size NUMERIC(5, 2) DEFAULT \'0\' NOT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_networkequipments CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_peripherals CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_phones CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_printers CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_problemcosts CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_projectcosts CHANGE cost cost NUMERIC(20, 4) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_softwares CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE glpi_ticketcosts CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE glpi_budgets CHANGE value value NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_changecosts CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_computers CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_dashboards CHANGE id id INT NOT NULL');
        $this->addSql('DROP INDEX IDX_7286AF61FF7A3925 ON glpi_groups');
        $this->addSql('DROP INDEX IDX_7286AF61B49CA1EA ON glpi_groups');
        $this->addSql('CREATE INDEX IDX_7286AF61FF7A3925 ON glpi_groups (ldap_value(1024))');
        $this->addSql('CREATE INDEX IDX_7286AF61B49CA1EA ON glpi_groups (ldap_group_dn(1024))');
        $this->addSql('ALTER TABLE glpi_infocoms CHANGE value value NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE warranty_value warranty_value NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_monitors CHANGE size size NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL, CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_networkequipments CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_peripherals CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_phones CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_printers CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_problemcosts CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_projectcosts CHANGE cost cost NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
        $this->addSql('ALTER TABLE glpi_softwares CHANGE ticket_tco ticket_tco NUMERIC(20, 4) DEFAULT \'0.0000\'');
        $this->addSql('ALTER TABLE glpi_ticketcosts CHANGE cost_time cost_time NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_fixed cost_fixed NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL, CHANGE cost_material cost_material NUMERIC(20, 4) DEFAULT \'0.0000\' NOT NULL');
    }
}
