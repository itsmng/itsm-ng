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
        $platform = $this->connection->getDatabasePlatform()->getName();

        if (str_contains($platform, 'postgres')) {
            // Use guarded, idempotent renames for Postgres to handle already-renamed schemas
            $renames = [
                // tech owner/group
                ['glpi_appliances', 'users_id_tech', 'tech_users_id'],
                ['glpi_appliances', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_cartridgeitems', 'users_id_tech', 'tech_users_id'],
                ['glpi_cartridgeitems', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_certificates', 'users_id_tech', 'tech_users_id'],
                ['glpi_certificates', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_changetasks', 'users_id_tech', 'tech_users_id'],
                ['glpi_changetasks', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_changetasks', 'users_id_editor', 'editor_users_id'],
                ['glpi_changevalidations', 'users_id_validate', 'validate_users_id'],
                ['glpi_clusters', 'users_id_tech', 'tech_users_id'],
                ['glpi_clusters', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_computers', 'users_id_tech', 'tech_users_id'],
                ['glpi_computers', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_consumableitems', 'users_id_tech', 'tech_users_id'],
                ['glpi_consumableitems', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_domainrecords', 'users_id_tech', 'tech_users_id'],
                ['glpi_domainrecords', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_domains', 'users_id_tech', 'tech_users_id'],
                ['glpi_domains', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_enclosures', 'users_id_tech', 'tech_users_id'],
                ['glpi_enclosures', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_itilfollowups', 'users_id_editor', 'editor_users_id'],
                ['glpi_itilsolutions', 'users_id_editor', 'editor_users_id'],
                ['glpi_itilsolutions', 'users_id_approval', 'approval_users_id'],
                ['glpi_monitors', 'users_id_tech', 'tech_users_id'],
                ['glpi_monitors', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_networkequipments', 'users_id_tech', 'tech_users_id'],
                ['glpi_networkequipments', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_notepads', 'users_id_lastupdater', 'lastupdater_users_id'],
                ['glpi_passivedcequipments', 'users_id_tech', 'tech_users_id'],
                ['glpi_passivedcequipments', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_pdus', 'users_id_tech', 'tech_users_id'],
                ['glpi_pdus', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_peripherals', 'users_id_tech', 'tech_users_id'],
                ['glpi_peripherals', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_phones', 'users_id_tech', 'tech_users_id'],
                ['glpi_phones', 'groups_id_tech', 'tech_groups_id'],
                // keep PlanningExternalEvent as users_id_guests (entity expects that)
                // problems/changes recipients and updaters
                ['glpi_changes', 'users_id_recipient', 'recipient_users_id'],
                ['glpi_changes', 'users_id_lastupdater', 'lastupdater_users_id'],
                ['glpi_printers', 'users_id_tech', 'tech_users_id'],
                ['glpi_printers', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_problems', 'users_id_recipient', 'recipient_users_id'],
                ['glpi_problems', 'users_id_lastupdater', 'lastupdater_users_id'],
                ['glpi_problemtasks', 'users_id_editor', 'editor_users_id'],
                ['glpi_problemtasks', 'users_id_tech', 'tech_users_id'],
                ['glpi_problemtasks', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_racks', 'users_id_tech', 'tech_users_id'],
                ['glpi_racks', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_softwarelicenses', 'users_id_tech', 'tech_users_id'],
                ['glpi_softwarelicenses', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_softwares', 'users_id_tech', 'tech_users_id'],
                ['glpi_softwares', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_tasktemplates', 'users_id_tech', 'tech_users_id'],
                ['glpi_tasktemplates', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_tickets', 'users_id_lastupdater', 'lastupdater_users_id'],
                ['glpi_tickets', 'users_id_recipient', 'recipient_users_id'],
                ['glpi_tickettasks', 'users_id_editor', 'editor_users_id'],
                ['glpi_tickettasks', 'users_id_tech', 'tech_users_id'],
                ['glpi_tickettasks', 'groups_id_tech', 'tech_groups_id'],
                ['glpi_ticketvalidations', 'users_id_validate', 'validate_users_id'],
            ];

            foreach ($renames as [$table, $from, $to]) {
                $this->addSql(
                    "DO $$\n" .
                    "BEGIN\n" .
                    "    IF EXISTS (\n" .
                    "        SELECT 1 FROM information_schema.columns\n" .
                    "        WHERE table_schema = 'public'\n" .
                    "          AND table_name = '" . $table . "'\n" .
                    "          AND column_name = '" . $from . "'\n" .
                    "    ) THEN\n" .
                    "        EXECUTE format('ALTER TABLE %I RENAME COLUMN %I TO %I', '" . $table . "', '" . $from . "', '" . $to . "');\n" .
                    "    END IF;\n" .
                    "END $$;"
                );
            }

            // entities cleanup
            $this->addSql("UPDATE glpi_entities SET entities_id = NULL WHERE entities_id = -1");

            // reserved/renamed keywords columns; guard with existence checks
            $keywordRenames = [
                ['glpi_authldaps', 'condition', 'conditions'],
                ['glpi_olalevelcriterias', 'condition', 'conditions'],
                ['glpi_rules', 'condition', 'conditions'],
                ['glpi_rulecriterias', 'condition', 'conditions'],
                ['glpi_slalevelcriterias', 'condition', 'conditions'],
            ];
            foreach ($keywordRenames as [$table, $from, $to]) {
                $this->addSql(
                    "DO $$\n" .
                    "BEGIN\n" .
                    "    IF EXISTS (\n" .
                    "        SELECT 1 FROM information_schema.columns\n" .
                    "        WHERE table_schema = 'public'\n" .
                    "          AND table_name = '" . $table . "'\n" .
                    "          AND column_name = '" . $from . "'\n" .
                    "    ) THEN\n" .
                    "        EXECUTE format('ALTER TABLE %I RENAME COLUMN %I TO %I', '" . $table . "', '" . $from . "', '" . $to . "');\n" .
                    "    END IF;\n" .
                    "END $$;"
                );
            }

            // glpi_rules.match -> matching (match must be double-quoted); guard existence
            $this->addSql(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'glpi_rules' AND column_name = 'match'
    ) THEN
        EXECUTE 'ALTER TABLE glpi_rules RENAME COLUMN "match" TO matching';
    END IF;
END $$;
SQL);
        } else {
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
            $this->addSql('ALTER TABLE glpi_authldaps RENAME COLUMN `condition` TO conditions');
            $this->addSql('ALTER TABLE glpi_olalevelcriterias RENAME COLUMN `condition` TO conditions');
            $this->addSql('ALTER TABLE glpi_rules RENAME COLUMN `condition` TO conditions, RENAME COLUMN `match` TO matching');
            $this->addSql('ALTER TABLE glpi_rulecriterias RENAME COLUMN `condition` TO conditions');
            $this->addSql('ALTER TABLE glpi_slalevelcriterias RENAME COLUMN `condition` TO conditions');
        }


    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();

        if (str_contains($platform, 'postgres')) {
            // Guarded reverse renames for Postgres
            $reverseRenames = [
                ['glpi_appliances', 'tech_users_id', 'users_id_tech'],
                ['glpi_appliances', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_cartridgeitems', 'tech_users_id', 'users_id_tech'],
                ['glpi_cartridgeitems', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_certificates', 'tech_users_id', 'users_id_tech'],
                ['glpi_certificates', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_changes', 'recipient_users_id', 'users_id_recipient'],
                ['glpi_changes', 'lastupdater_users_id', 'users_id_lastupdater'],
                ['glpi_changetasks', 'tech_users_id', 'users_id_tech'],
                ['glpi_changetasks', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_changetasks', 'editor_users_id', 'users_id_editor'],
                ['glpi_changevalidations', 'validate_users_id', 'users_id_validate'],
                ['glpi_clusters', 'tech_users_id', 'users_id_tech'],
                ['glpi_clusters', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_computers', 'tech_users_id', 'users_id_tech'],
                ['glpi_computers', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_consumableitems', 'tech_users_id', 'users_id_tech'],
                ['glpi_consumableitems', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_domainrecords', 'tech_users_id', 'users_id_tech'],
                ['glpi_domainrecords', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_domains', 'tech_users_id', 'users_id_tech'],
                ['glpi_domains', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_enclosures', 'tech_users_id', 'users_id_tech'],
                ['glpi_enclosures', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_itilfollowups', 'editor_users_id', 'users_id_editor'],
                ['glpi_itilsolutions', 'editor_users_id', 'users_id_editor'],
                ['glpi_itilsolutions', 'approval_users_id', 'users_id_approval'],
                ['glpi_monitors', 'tech_users_id', 'users_id_tech'],
                ['glpi_monitors', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_networkequipments', 'tech_users_id', 'users_id_tech'],
                ['glpi_networkequipments', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_notepads', 'lastupdater_users_id', 'users_id_lastupdater'],
                ['glpi_passivedcequipments', 'tech_users_id', 'users_id_tech'],
                ['glpi_passivedcequipments', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_pdus', 'tech_users_id', 'users_id_tech'],
                ['glpi_pdus', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_peripherals', 'tech_users_id', 'users_id_tech'],
                ['glpi_peripherals', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_phones', 'tech_users_id', 'users_id_tech'],
                ['glpi_phones', 'tech_groups_id', 'groups_id_tech'],
                // planningexternalevents left untouched in up(); nothing to revert here
                ['glpi_printers', 'tech_users_id', 'users_id_tech'],
                ['glpi_printers', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_problems', 'recipient_users_id', 'users_id_recipient'],
                ['glpi_problems', 'lastupdater_users_id', 'users_id_lastupdater'],
                ['glpi_problemtasks', 'editor_users_id', 'users_id_editor'],
                ['glpi_problemtasks', 'tech_users_id', 'users_id_tech'],
                ['glpi_problemtasks', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_racks', 'tech_users_id', 'users_id_tech'],
                ['glpi_racks', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_softwarelicenses', 'tech_users_id', 'users_id_tech'],
                ['glpi_softwarelicenses', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_softwares', 'tech_users_id', 'users_id_tech'],
                ['glpi_softwares', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_tasktemplates', 'tech_users_id', 'users_id_tech'],
                ['glpi_tasktemplates', 'tech_groups_id', 'groups_id_tech'],
                ['glpi_tickettasks', 'tech_users_id', 'users_id_tech'],
                ['glpi_tickettasks', 'tech_groups_id', 'groups_id_tech'],
            ];

            foreach ($reverseRenames as [$table, $from, $to]) {
                $this->addSql(
                    "DO $$\n" .
                    "BEGIN\n" .
                    "    IF EXISTS (\n" .
                    "        SELECT 1 FROM information_schema.columns\n" .
                    "        WHERE table_schema = 'public'\n" .
                    "          AND table_name = '" . $table . "'\n" .
                    "          AND column_name = '" . $from . "'\n" .
                    "    ) THEN\n" .
                    "        EXECUTE format('ALTER TABLE %I RENAME COLUMN %I TO %I', '" . $table . "', '" . $from . "', '" . $to . "');\n" .
                    "    END IF;\n" .
                    "END $$;"
                );
            }
        } else {
            $this->addSql('ALTER TABLE glpi_appliances RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
            $this->addSql('ALTER TABLE glpi_cartridgeitems RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
            $this->addSql('ALTER TABLE glpi_certificates RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
            $this->addSql('ALTER TABLE glpi_changes RENAME COLUMN recipient_users_id TO users_id_recipient, RENAME COLUMN lastupdater_users_id TO users_id_lastupdater');
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
            // No rename for planningexternalevents (kept as users_id_guests in up())
            $this->addSql('ALTER TABLE glpi_printers RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
            $this->addSql('ALTER TABLE glpi_problems RENAME COLUMN recipient_users_id TO users_id_recipient, RENAME COLUMN lastupdater_users_id TO users_id_lastupdater');
            $this->addSql('ALTER TABLE glpi_problemtasks RENAME COLUMN editor_users_id TO users_id_editor, RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
            $this->addSql('ALTER TABLE glpi_racks RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
            $this->addSql('ALTER TABLE glpi_softwarelicenses RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
            $this->addSql('ALTER TABLE glpi_softwares RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
            $this->addSql('ALTER TABLE glpi_tasktemplates RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
            $this->addSql('ALTER TABLE glpi_tickettasks RENAME COLUMN tech_users_id TO users_id_tech, RENAME COLUMN tech_groups_id TO groups_id_tech');
        }
    }
}
