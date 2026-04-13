<?php

namespace tests\units\itsmng\Database\Migrations;

if (!class_exists(\itsmng\Database\Migrations\MigrationDsl::class)) {
    class_alias(
        \itsmng\Database\Migrations\Migration::class,
        \itsmng\Database\Migrations\MigrationDsl::class
    );
}

class MigrationDsl extends \GLPITestCase
{
    public function testBuildOperationsSupportsRawSqlAndForeignKeys()
    {
        $migration = new class extends \itsmng\Database\Migrations\Migration {
            public function up(): void
            {
                $this->execute('UPDATE glpi_tickets SET users_id_recipient = NULL WHERE users_id_recipient = 0');
                $this->alter()->table('glpi_tickets')->addForeignKey(
                    'fk_glpi_tickets_users_id_recipient',
                    'users_id_recipient',
                    'glpi_users'
                );
            }

            public function down(): void
            {
                $this->alter()->table('glpi_tickets')->dropForeignKey('fk_glpi_tickets_users_id_recipient');
            }
        };

        $this->array($migration->buildOperations('up'))->isEqualTo([
            [
                'kind' => 'raw_sql',
                'sql'  => 'UPDATE glpi_tickets SET users_id_recipient = NULL WHERE users_id_recipient = 0',
            ],
            [
                'kind'          => 'alter_table',
                'table'         => 'glpi_tickets',
                'add_columns'   => [],
                'alter_columns' => [],
                'drop_columns'  => [],
                'indexes'       => [],
                'foreign_keys'  => [
                    [
                        'action'             => 'add',
                        'name'               => 'fk_glpi_tickets_users_id_recipient',
                        'columns'            => ['users_id_recipient'],
                        'referenced_table'   => 'glpi_users',
                        'referenced_columns' => ['id'],
                    ],
                ],
            ],
        ]);

        $this->array($migration->buildOperations('down'))->isEqualTo([
            [
                'kind'          => 'alter_table',
                'table'         => 'glpi_tickets',
                'add_columns'   => [],
                'alter_columns' => [],
                'drop_columns'  => [],
                'indexes'       => [],
                'foreign_keys'  => [
                    [
                        'action' => 'drop',
                        'name'   => 'fk_glpi_tickets_users_id_recipient',
                    ],
                ],
            ],
        ]);
    }
}
