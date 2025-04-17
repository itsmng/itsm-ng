<?php

declare(strict_types=1);

namespace Itsmng\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250107095445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE glpi_appliances RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_cartridgeitems RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_certificates RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_changes RENAME COLUMN users_id_recipient TO recipient_users_id, RENAME COLUMN users_id_lastupdater TO lastupdater_users_id');
        $this->addSql('ALTER TABLE glpi_changetasks RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id, RENAME COLUMN users_id_editor TO editor_users_id');
        $this->addSql('ALTER TABLE glpi_changevalidations RENAME COLUMN users_id_validate TO validate_users_id');
        $this->addSql('ALTER TABLE glpi_clusters RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_computers RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_consumableitems RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_domainrecords RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_domains RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_enclosures RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_itilfollowups RENAME COLUMN users_id_editor TO editor_users_id');
        $this->addSql('ALTER TABLE glpi_itilsolutions RENAME COLUMN users_id_editor TO editor_users_id, RENAME COLUMN users_id_approval TO approval_users_id');
        $this->addSql('ALTER TABLE glpi_monitors RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_networkequipments RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_notepads RENAME COLUMN users_id_lastupdater TO lastupdater_users_id');
        $this->addSql('ALTER TABLE glpi_passivedcequipments RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_pdus RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_peripherals RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_phones RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_planningexternalevents RENAME COLUMN users_id_guests TO guests_users_id');
        $this->addSql('ALTER TABLE glpi_printers RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_problems RENAME COLUMN users_id_recipient TO recipient_users_id, RENAME COLUMN users_id_lastupdater TO lastupdater_users_id');
        $this->addSql('ALTER TABLE glpi_problemtasks RENAME COLUMN users_id_editor TO editor_users_id, RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_racks RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_softwarelicenses RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_softwares RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_tasktemplates RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_tickets RENAME COLUMN users_id_lastupdater TO lastupdater_users_id, RENAME COLUMN users_id_recipient TO recipient_users_id');
        $this->addSql('ALTER TABLE glpi_tickettasks RENAME COLUMN users_id_editor TO editor_users_id, RENAME COLUMN users_id_tech TO tech_users_id, RENAME COLUMN groups_id_tech TO tech_groups_id');
        $this->addSql('ALTER TABLE glpi_ticketvalidations RENAME COLUMN users_id_validate TO validate_users_id');
        $this->addSql('UPDATE glpi_entities SET entities_id = NULL WHERE entities_id = -1'); 
        $this->addSql('ALTER TABLE glpi_authldaps RENAME COLUMN condition TO conditions');
        $this->addSql('ALTER TABLE glpi_olalevelcriterias RENAME COLUMN condition TO conditions');
        $this->addSql('ALTER TABLE glpi_rules RENAME COLUMN condition TO conditions');
        $this->addSql('ALTER TABLE glpi_rulecriterias RENAME COLUMN condition TO conditions');
        $this->addSql('ALTER TABLE glpi_slalevelcriterias RENAME COLUMN condition TO conditions');


    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE glpi_appliances RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_cartridgeitems RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_certificates RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_changes RENAME COLUMN recipient_users_id TO users_id_recipient, RENAME COLUMN lastupdater_groups_id TO users_id_lastupdater');
        $this->addSql('ALTER TABLE glpi_changetasks RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech, RENAME COLUMN editor_users_id TO users_id_editor');
        $this->addSql('ALTER TABLE glpi_changevalidations RENAME COLUMN validate_users_id TO users_id_validate');
        $this->addSql('ALTER TABLE glpi_clusters RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_computers RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_consumableitems RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_domainrecords RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_domains RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_enclosures RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_itilfollowups RENAME COLUMN editor_users_id TO users_id_editor');
        $this->addSql('ALTER TABLE glpi_itilsolutions RENAME COLUMN editor_users_id TO users_id_editor, RENAME COLUMN approval_users_id TO users_id_approval');
        $this->addSql('ALTER TABLE glpi_monitors RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_networkequipments RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_passivedcequipments RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_pdus RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_peripherals RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_phones RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_planningexternalevents RENAME COLUMN guest_users_id TO users_id_guest');
        $this->addSql('ALTER TABLE glpi_printers RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_problems RENAME COLUMN recipient_users_id TO users_id_recipient, RENAME COLUMN lastupdater_groups_id TO users_id_lastupdater');
        $this->addSql('ALTER TABLE glpi_problemtasks RENAME COLUMN editor_users_id TO users_id_editor, RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_racks RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_softwarelicenses RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_softwares RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_tasktemplates RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        $this->addSql('ALTER TABLE glpi_tickettasks RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
    }
}
