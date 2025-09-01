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

    private function switchToDatetime(string $table, string $column, bool $nullable = true): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();

        if (str_contains($platform, 'postgres')) {
            // Postgres: use TIMESTAMP, no zero-datetime sentinel; enforce NOT NULL with a safe default when requested
            $this->addSql('ALTER TABLE ' . $table . ' ADD COLUMN temp_' . $column . ' TIMESTAMP NULL');
            $this->addSql('UPDATE ' . $table . ' SET temp_' . $column . ' = ' . $column);
            if ($nullable) {
                // keep NULLs as NULL
            } else {
                // replace NULLs with epoch to satisfy NOT NULL constraint later
                $this->addSql("UPDATE $table SET temp_$column = '1970-01-01 00:00:00' WHERE temp_$column IS NULL");
            }
            $this->addSql('ALTER TABLE ' . $table . ' DROP COLUMN ' . $column);
            $this->addSql('ALTER TABLE ' . $table . ' RENAME COLUMN temp_' . $column . ' TO ' . $column);
            if ($nullable) {
                $this->addSql('ALTER TABLE ' . $table . ' ALTER COLUMN ' . $column . ' DROP NOT NULL');
            } else {
                $this->addSql('ALTER TABLE ' . $table . ' ALTER COLUMN ' . $column . ' SET NOT NULL');
            }
            $this->addSql('CREATE INDEX IF NOT EXISTS ' . $column . ' ON ' . $table . ' (' . $column . ')');
        } else {
            // MySQL: keep DATETIME and original zero-datetime sentinel behavior
            // Restartable sequence:
            // 1) Ensure temp column exists
            $this->addSql('ALTER TABLE ' . $table . ' ADD COLUMN IF NOT EXISTS temp_' . $column . ' DATETIME DEFAULT NULL');
            // 2) Ensure source column exists (in case a previous attempt already dropped it)
            $this->addSql('ALTER TABLE ' . $table . ' ADD COLUMN IF NOT EXISTS ' . $column . ' DATETIME DEFAULT NULL');
            // 3) Copy data from source when available; keep any previously copied data
            $this->addSql('UPDATE ' . $table . ' SET temp_' . $column . ' = COALESCE(' . $column . ', temp_' . $column . ')');
            // 4) Apply zero-datetime sentinel for NOT NULL emulation if needed
            $this->addSql('UPDATE ' . $table . ' SET temp_' . $column . ' = "0000-00-00 00:00:00" WHERE temp_' . $column . ' IS NULL');
            // 5) Drop source and rename temp -> source
            $this->addSql('ALTER TABLE ' . $table . ' DROP COLUMN ' . $column);
            $this->addSql('ALTER TABLE ' . $table . ' CHANGE temp_' . $column . ' ' . $column . ' DATETIME' . ($nullable ? '' : ' NOT NULL'));
            // 6) Recreate simple index safely
            $this->addSql('DROP INDEX IF EXISTS ' . $column . ' ON ' . $table);
            $this->addSql('CREATE INDEX ' . $column . ' ON ' . $table . ' (' . $column . ')');
        }
    }

    public function up(Schema $schema): void
    {
        // Cross-DB: branch per platform for dialect differences
        $platform = $this->connection->getDatabasePlatform()->getName();

        // Common: switch many tables' date columns to TIMESTAMP/DATETIME via helper
        $this->switchToDatetime('glpi_alerts', 'date');
        $this->switchToDatetime('glpi_crontasklogs', 'date');
        $this->switchToDatetime('glpi_crontasks', 'lastrun');
        $this->switchToDatetime('glpi_changevalidations', 'submission_date');
        $this->switchToDatetime('glpi_changevalidations', 'validation_date');
        $this->switchToDatetime('glpi_documents_items', 'date');
        $this->switchToDatetime('glpi_notimportedemails', 'date');
        $this->switchToDatetime('glpi_tickets', 'date');
        $this->switchToDatetime('glpi_tickets', 'closedate');
        $this->switchToDatetime('glpi_tickets', 'solvedate');
        $this->switchToDatetime('glpi_tickets', 'time_to_resolve');
        $this->switchToDatetime('glpi_tickets', 'time_to_own');
        $this->switchToDatetime('glpi_tickets', 'begin_waiting_date');
        $this->switchToDatetime('glpi_tickets', 'ola_ttr_begin_date');
        $this->switchToDatetime('glpi_tickets', 'internal_time_to_resolve');
        $this->switchToDatetime('glpi_tickets', 'internal_time_to_own');

        if (str_contains($platform, 'postgres')) {
            // Postgres equivalents
            $this->addSql('ALTER TABLE glpi_apiclients ALTER COLUMN dolog_method TYPE SMALLINT USING dolog_method::smallint');
            $this->addSql('ALTER TABLE glpi_apiclients ALTER COLUMN dolog_method SET DEFAULT 0');
            $this->addSql('ALTER TABLE glpi_apiclients ALTER COLUMN dolog_method SET NOT NULL');

            // Normalize root entity parent: allow NULL then convert -1 to NULL
            $this->addSql("ALTER TABLE glpi_entities ALTER COLUMN entities_id DROP NOT NULL");
            $this->addSql("UPDATE glpi_entities SET entities_id = NULL WHERE entities_id = -1");

            $this->addSql('ALTER TABLE glpi_appliances ADD COLUMN IF NOT EXISTS date_creation TIMESTAMP NOT NULL');
            $this->addSql('ALTER TABLE glpi_authmails ADD COLUMN IF NOT EXISTS date_creation TIMESTAMP NOT NULL');

            $this->addSql(
                "DO $$
                BEGIN
                    IF EXISTS (
                        SELECT 1
                        FROM information_schema.columns
                        WHERE table_name = 'glpi_calendarsegments'
                          AND column_name = 'day'
                          AND data_type = 'boolean'
                    ) THEN
                        EXECUTE 'ALTER TABLE glpi_calendarsegments ALTER COLUMN day DROP DEFAULT';
                        EXECUTE 'ALTER TABLE glpi_calendarsegments ALTER COLUMN day TYPE SMALLINT USING (CASE WHEN day THEN 1 ELSE 0 END)';
                    ELSE
                        EXECUTE 'ALTER TABLE glpi_calendarsegments ALTER COLUMN day DROP DEFAULT';
                        EXECUTE 'ALTER TABLE glpi_calendarsegments ALTER COLUMN day TYPE SMALLINT USING (day::smallint)';
                    END IF;
                END
                $$"
            );
            $this->addSql('ALTER TABLE glpi_calendarsegments ALTER COLUMN day SET DEFAULT 1');
            $this->addSql('ALTER TABLE glpi_calendarsegments ALTER COLUMN day SET NOT NULL');

            $this->addSql('ALTER INDEX IF EXISTS group_id_tech RENAME TO groups_id_tech');
            $this->addSql('COMMENT ON COLUMN glpi_crontasklogs.crontasklogs_id IS ' . $this->connection->quote("id of \"start\" event"));
            $this->addSql('ALTER TABLE glpi_crontasklogs ALTER COLUMN crontasklogs_id TYPE INTEGER USING crontasklogs_id::integer');
            // Ensure no NULLs remain before enforcing NOT NULL (fallback to self id)
            $this->addSql('UPDATE glpi_crontasklogs SET crontasklogs_id = id WHERE crontasklogs_id IS NULL');
            $this->addSql('ALTER TABLE glpi_crontasklogs ALTER COLUMN crontasklogs_id SET NOT NULL');

            // Dashboards: add identity only if not already identity and has no default (serial)
            $this->addSql("DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM pg_attribute a
                    WHERE a.attrelid = 'glpi_dashboards'::regclass
                      AND a.attname = 'id'
                      AND a.attidentity <> ''
                ) AND NOT EXISTS (
                    SELECT 1 FROM information_schema.columns c
                    WHERE c.table_name = 'glpi_dashboards'
                      AND c.column_name = 'id'
                      AND c.column_default IS NOT NULL
                ) THEN
                    EXECUTE 'ALTER TABLE glpi_dashboards ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY';
                END IF;
            END
            $$");
            $this->addSql('DO $$ BEGIN IF NOT EXISTS (
                    SELECT 1 FROM pg_constraint WHERE conrelid = \'glpi_dashboards\'::regclass AND contype = \'p\'
            ) THEN ALTER TABLE glpi_dashboards ADD PRIMARY KEY (id); END IF; END $$');
            $this->addSql('DROP INDEX IF EXISTS id');
            // Create unique index on (profileId,userId), handling possible case differences
            $this->addSql("DO $$
            DECLARE c1 text; c2 text; idx text := 'profileid_userid';
            BEGIN
                SELECT column_name INTO c1 FROM information_schema.columns WHERE table_name='glpi_dashboards' AND column_name IN ('profileId','profileid') LIMIT 1;
                SELECT column_name INTO c2 FROM information_schema.columns WHERE table_name='glpi_dashboards' AND column_name IN ('userId','userid') LIMIT 1;
                IF c1 IS NOT NULL AND c2 IS NOT NULL THEN
                    EXECUTE 'CREATE UNIQUE INDEX IF NOT EXISTS ' || quote_ident(idx) || ' ON glpi_dashboards (' || quote_ident(c1) || ', ' || quote_ident(c2) || ')';
                END IF;
            END $$");
            $this->addSql('ALTER TABLE glpi_dashboards ALTER COLUMN name TYPE VARCHAR(100)');
            $this->addSql('ALTER TABLE glpi_dashboards ALTER COLUMN name SET NOT NULL');
            $this->addSql('ALTER TABLE glpi_dashboards ALTER COLUMN content TYPE TEXT');
            $this->addSql('ALTER TABLE glpi_dashboards ALTER COLUMN content SET NOT NULL');

            $this->addSql('ALTER INDEX IF EXISTS group_id_tech RENAME TO groups_id_tech');
            $this->addSql('ALTER INDEX IF EXISTS date RENAME TO name');

            // LONGTEXT -> TEXT
            $this->addSql('ALTER TABLE glpi_fieldunicities ALTER COLUMN fields TYPE TEXT');

            $this->addSql("DROP INDEX IF EXISTS ldap_value");
            $this->addSql("DROP INDEX IF EXISTS ldap_group_dn");
            $this->addSql("CREATE INDEX IF NOT EXISTS glpi_groups_ldap_value_fts ON glpi_groups USING GIN (to_tsvector('simple', coalesce(ldap_value, '')))");
            $this->addSql("CREATE INDEX IF NOT EXISTS glpi_groups_ldap_group_dn_fts ON glpi_groups USING GIN (to_tsvector('simple', coalesce(ldap_group_dn, '')))");

            $this->addSql('DROP INDEX IF EXISTS source');

            // UNSIGNED -> SMALLINT, set default
            $this->addSql('ALTER TABLE glpi_ipaddresses ALTER COLUMN version TYPE SMALLINT USING version::smallint');
            $this->addSql('ALTER TABLE glpi_ipaddresses ALTER COLUMN version SET DEFAULT 0');
            $this->addSql('ALTER TABLE glpi_ipnetworks ALTER COLUMN version TYPE SMALLINT USING version::smallint');
            $this->addSql('ALTER TABLE glpi_ipnetworks ALTER COLUMN version SET DEFAULT 0');

            // Bulk index renames from computers_id -> items_id
            $indexRenameTables = [
                'glpi_items_devicebatteries',
                'glpi_items_devicecases',
                'glpi_items_devicecontrols',
                'glpi_items_devicedrives',
                'glpi_items_devicefirmwares',
                'glpi_items_devicegenerics',
                'glpi_items_devicegraphiccards',
                'glpi_items_deviceharddrives',
                'glpi_items_devicememories',
                'glpi_items_devicemotherboards',
                'glpi_items_devicenetworkcards',
                'glpi_items_devicepcis',
                'glpi_items_devicepowersupplies',
                'glpi_items_deviceprocessors',
                'glpi_items_devicesensors',
                'glpi_items_devicesoundcards'
            ];
            foreach ($indexRenameTables as $t) {
                $this->addSql('ALTER INDEX IF EXISTS ' . $t . '_computers_id RENAME TO ' . $t . '_items_id');
                $this->addSql('ALTER INDEX IF EXISTS computers_id RENAME TO items_id');
            }
            $this->addSql('ALTER INDEX IF EXISTS item_id RENAME TO items_id');

            $this->addSql("ALTER TABLE glpi_links_itemtypes ALTER COLUMN itemtype TYPE VARCHAR(100)");
            $this->addSql("ALTER TABLE glpi_links_itemtypes ALTER COLUMN itemtype SET DEFAULT ''");
            $this->addSql('ALTER TABLE glpi_oidc_config ADD COLUMN IF NOT EXISTS logout VARCHAR(255)');

            $this->addSql('ALTER TABLE glpi_olalevels ALTER COLUMN "match" TYPE VARCHAR(10)');
            $this->addSql('ALTER TABLE glpi_rules ALTER COLUMN "match" TYPE VARCHAR(10)');
            $this->addSql('ALTER TABLE glpi_slalevels ALTER COLUMN "match" TYPE VARCHAR(10)');

            $this->addSql('DROP INDEX IF EXISTS field_value');
            $this->addSql('CREATE INDEX IF NOT EXISTS field_value ON glpi_ruleactions (field, value)');

            // Users column adjustments; LONGTEXT -> TEXT
            $this->addSql("ALTER TABLE glpi_users ALTER COLUMN language TYPE VARCHAR(10)");
            $this->addSql("ALTER TABLE glpi_users ALTER COLUMN csv_delimiter TYPE VARCHAR(1)");
            foreach (['priority_1', 'priority_2', 'priority_3', 'priority_4', 'priority_5', 'priority_6', 'password_forget_token', 'layout', 'palette'] as $col) {
                $this->addSql('ALTER TABLE glpi_users ALTER COLUMN ' . $col . ' TYPE VARCHAR(20)');
            }
            $this->addSql('ALTER TABLE glpi_users ALTER COLUMN access_custom_shortcuts TYPE TEXT');

            // Drop index on vobjects if present
            $this->addSql('DROP INDEX IF EXISTS item');
        } else {
            // MySQL branch (original SQL)
            $this->addSql('ALTER TABLE glpi_apiclients CHANGE dolog_method dolog_method SMALLINT DEFAULT 0 NOT NULL');
            $this->addSql('ALTER TABLE glpi_calendarsegments CHANGE day day TINYINT(1) DEFAULT 1 NOT NULL COMMENT \'number of the day based on date(w)\'');
            $this->addSql('ALTER TABLE glpi_crontasklogs CHANGE crontasklogs_id crontasklogs_id INT NOT NULL COMMENT \'id of "start" event\'');
            // Ensure missing columns exist before cross-table datetime switch
            $this->addSql('ALTER TABLE glpi_appliances ADD COLUMN IF NOT EXISTS date_creation DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00"');
            $this->addSql('ALTER TABLE glpi_authmails ADD COLUMN IF NOT EXISTS date_creation DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00"');
            // glpi_dashboards is already correctly defined (PK, AUTO_INCREMENT, unique(profileId,userId)) in MySQL sample DB
            $this->addSql('ALTER TABLE glpi_dashboards CHANGE name name VARCHAR(100) NOT NULL, CHANGE content content LONGTEXT NOT NULL');
            // Index rename already applied in enclosures in current schema
            $this->addSql('ALTER TABLE glpi_fieldunicities CHANGE fields fields LONGTEXT DEFAULT NULL');
            $this->addSql('DROP INDEX ldap_value ON glpi_groups');
            $this->addSql('DROP INDEX ldap_group_dn ON glpi_groups');
            $this->addSql('CREATE FULLTEXT INDEX ldap_value ON glpi_groups (ldap_value)');
            $this->addSql('CREATE FULLTEXT INDEX ldap_group_dn ON glpi_groups (ldap_group_dn)');
            $this->addSql('ALTER TABLE glpi_ipaddresses CHANGE version version SMALLINT UNSIGNED DEFAULT 0');
            $this->addSql('ALTER TABLE glpi_ipnetworks CHANGE version version SMALLINT UNSIGNED DEFAULT 0');
            // Index renames from computers_id -> items_id already applied in current schema
            $this->addSql('ALTER TABLE glpi_links_itemtypes CHANGE itemtype itemtype VARCHAR(100) DEFAULT \'\' NOT NULL');
            $this->addSql('ALTER TABLE glpi_olalevels CHANGE `match` `match` VARCHAR(10) DEFAULT NULL COMMENT \'see define.php *_MATCHING constant\'');
            // Only racks still has index named group_id_tech in sample DB, rename it
            $this->addSql('ALTER TABLE glpi_racks RENAME INDEX IF EXISTS group_id_tech TO groups_id_tech');
            $this->addSql('DROP INDEX field_value ON glpi_ruleactions');
            $this->addSql('CREATE INDEX field_value ON glpi_ruleactions (field, value)');
            $this->addSql('ALTER TABLE glpi_rules CHANGE `match` `match` VARCHAR(10) DEFAULT NULL COMMENT \'see define.php *_MATCHING constant\'');
            $this->addSql('ALTER TABLE glpi_slalevels CHANGE `match` `match` VARCHAR(10) DEFAULT NULL COMMENT \'see define.php *_MATCHING constant\'');
            $this->addSql('ALTER TABLE glpi_users CHANGE language language VARCHAR(10) DEFAULT NULL COMMENT \'see define.php CFG_GLPI[language] array\', CHANGE csv_delimiter csv_delimiter VARCHAR(1) DEFAULT NULL, CHANGE priority_1 priority_1 VARCHAR(20) DEFAULT NULL, CHANGE priority_2 priority_2 VARCHAR(20) DEFAULT NULL, CHANGE priority_3 priority_3 VARCHAR(20) DEFAULT NULL, CHANGE priority_4 priority_4 VARCHAR(20) DEFAULT NULL, CHANGE priority_5 priority_5 VARCHAR(20) DEFAULT NULL, CHANGE priority_6 priority_6 VARCHAR(20) DEFAULT NULL, CHANGE password_forget_token password_forget_token VARCHAR(40) DEFAULT NULL, CHANGE layout layout VARCHAR(20) DEFAULT NULL, CHANGE palette palette VARCHAR(20) DEFAULT NULL, CHANGE access_custom_shortcuts access_custom_shortcuts LONGTEXT DEFAULT NULL');
            $this->addSql('DROP INDEX IF EXISTS item ON glpi_vobjects');
        }

        $tableToSwitchToDatetime = [
            'glpi_appliances',
            'glpi_authmails',
            'glpi_blacklistedmailcontents',
            'glpi_blacklists',
            'glpi_budgettypes',
            'glpi_calendars',
            'glpi_certificates_items',
            'glpi_clustertypes',
            'glpi_computers',
            'glpi_computermodels',
            'glpi_computertypes',
            'glpi_computervirtualmachines',
            'glpi_consumableitems',
            'glpi_consumableitemtypes',
            'glpi_consumables',
            'glpi_contacts',
            'glpi_contacttypes',
            'glpi_contracts',
            'glpi_contracttypes',
            'glpi_crontasks',
            'glpi_datacenters',
            'glpi_dcrooms',
            'glpi_devicebatteries',
            'glpi_devicecasetypes',
            'glpi_devicegenerics',
            'glpi_devicegraphiccards',
            'glpi_devicenetworkcards',
            'glpi_devicesensors',
            'glpi_devicesimcards',
            'glpi_devicesoundcards',
            'glpi_documentcategories',
            'glpi_documents_items',
            'glpi_documenttypes',
            'glpi_domainrecords',
            'glpi_enclosuremodels',
            'glpi_enclosures',
            'glpi_entities',
            'glpi_devicepowersupplies',
            'glpi_items_disks',
            'glpi_links',
            'glpi_pdutypes',
            'glpi_projects',
            'glpi_tickets',
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
