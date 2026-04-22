<?php

namespace tests\units\itsmng\Database\Schema\Dialect;

class PostgreSqlDialect extends \GLPITestCase
{
    private function dialect(): \itsmng\Database\Schema\Dialect\PostgreSqlDialect
    {
        return new \itsmng\Database\Schema\Dialect\PostgreSqlDialect();
    }

    public function testName()
    {
        $this->string($this->dialect()->name())->isIdenticalTo('pgsql');
    }

    public function testSupportsTransactionalDdl()
    {
        $this->boolean($this->dialect()->supportsTransactionalDdl())->isTrue();
    }

    public function testCreateTableWithIntegerColumns()
    {
        $table = [
            'name'    => 'glpi_items',
            'columns' => [
                ['name' => 'id', 'type' => 'int32', 'nullable' => false, 'autoIncrement' => true],
                ['name' => 'entities_id', 'type' => 'int32', 'nullable' => false, 'unsigned' => true, 'default' => 0],
                ['name' => 'tickets_id', 'type' => 'int32', 'nullable' => false, 'default' => 0],
            ],
            'indexes' => [
                ['name' => 'PRIMARY', 'type' => 'primary', 'columns' => [['name' => 'id']]],
            ],
        ];

        $statements = $this->dialect()->createTableStatements($table);
        $this->array($statements)->hasSize(1);

        $ddl = $statements[0];
        // SERIAL for auto-increment column
        $this->string($ddl)->contains('"id" SERIAL NOT NULL');
        // unsigned int32 maps to BIGINT on PG
        $this->string($ddl)->contains('"entities_id" BIGINT NOT NULL DEFAULT 0');
        // signed int32 maps to INTEGER on PG
        $this->string($ddl)->contains('"tickets_id" INTEGER NOT NULL DEFAULT 0');
        // Primary key
        $this->string($ddl)->contains('PRIMARY KEY ("id")');
        // Double-quote identifiers, not backticks
        $this->string($ddl)->notContains('`');
    }

    public function testCreateTableWithStringAndTextColumns()
    {
        $table = [
            'name'    => 'glpi_configs',
            'columns' => [
                ['name' => 'id', 'type' => 'int32', 'nullable' => false, 'autoIncrement' => true],
                ['name' => 'name', 'type' => 'string', 'length' => 255, 'nullable' => true, 'default' => null],
                ['name' => 'value', 'type' => 'text', 'nullable' => true, 'default' => null],
                ['name' => 'longval', 'type' => 'longtext', 'nullable' => true, 'default' => null],
                ['name' => 'code', 'type' => 'char', 'length' => 3, 'nullable' => false, 'default' => ''],
            ],
            'indexes' => [
                ['name' => 'PRIMARY', 'type' => 'primary', 'columns' => [['name' => 'id']]],
            ],
        ];

        $statements = $this->dialect()->createTableStatements($table);
        $ddl = $statements[0];

        // string with length maps to VARCHAR on PG
        $this->string($ddl)->contains('"name" VARCHAR(255) NULL DEFAULT NULL');
        // text and longtext both map to TEXT on PG
        $this->string($ddl)->contains('"value" TEXT NULL DEFAULT NULL');
        $this->string($ddl)->contains('"longval" TEXT NULL DEFAULT NULL');
        // char
        $this->string($ddl)->contains('"code" CHAR(3) NOT NULL');
        // No COLLATE in PostgreSQL DDL
        $this->string($ddl)->notContains('COLLATE');
        // No ENGINE/CHARSET in PostgreSQL DDL
        $this->string($ddl)->notContains('ENGINE');
        $this->string($ddl)->notContains('CHARSET');
    }

    public function testCreateTableWithBooleanColumn()
    {
        $table = [
            'name'    => 'glpi_flags',
            'columns' => [
                ['name' => 'id', 'type' => 'int32', 'nullable' => false, 'autoIncrement' => true],
                ['name' => 'is_active', 'type' => 'boolean', 'nullable' => false, 'default' => false],
                ['name' => 'is_deleted', 'type' => 'boolean', 'nullable' => false, 'default' => true],
            ],
            'indexes' => [
                ['name' => 'PRIMARY', 'type' => 'primary', 'columns' => [['name' => 'id']]],
            ],
        ];

        $statements = $this->dialect()->createTableStatements($table);
        $ddl = $statements[0];

        $this->string($ddl)->contains('"is_active" BOOLEAN NOT NULL DEFAULT FALSE');
        $this->string($ddl)->contains('"is_deleted" BOOLEAN NOT NULL DEFAULT TRUE');
    }

    public function testCreateTableWithDateTimeColumns()
    {
        $table = [
            'name'    => 'glpi_events',
            'columns' => [
                ['name' => 'id', 'type' => 'int32', 'nullable' => false, 'autoIncrement' => true],
                ['name' => 'event_date', 'type' => 'date', 'nullable' => true, 'default' => null],
                ['name' => 'created_at', 'type' => 'timestamp', 'nullable' => true, 'default' => null],
                ['name' => 'updated_at', 'type' => 'timestamp', 'nullable' => false, 'default' => ['kind' => 'expression', 'value' => 'CURRENT_TIMESTAMP']],
            ],
            'indexes' => [
                ['name' => 'PRIMARY', 'type' => 'primary', 'columns' => [['name' => 'id']]],
            ],
        ];

        $statements = $this->dialect()->createTableStatements($table);
        $ddl = $statements[0];

        $this->string($ddl)->contains('"event_date" DATE NULL DEFAULT NULL');
        $this->string($ddl)->contains('"created_at" TIMESTAMP WITH TIME ZONE NULL DEFAULT NULL');
        $this->string($ddl)->contains('"updated_at" TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function testCreateTableWithJsonAndBinaryColumns()
    {
        $table = [
            'name'    => 'glpi_data',
            'columns' => [
                ['name' => 'id', 'type' => 'int32', 'nullable' => false, 'autoIncrement' => true],
                ['name' => 'payload', 'type' => 'json', 'nullable' => true, 'default' => null],
                ['name' => 'content', 'type' => 'binary', 'nullable' => true, 'default' => null],
            ],
            'indexes' => [
                ['name' => 'PRIMARY', 'type' => 'primary', 'columns' => [['name' => 'id']]],
            ],
        ];

        $statements = $this->dialect()->createTableStatements($table);
        $ddl = $statements[0];

        $this->string($ddl)->contains('"payload" JSONB NULL DEFAULT NULL');
        $this->string($ddl)->contains('"content" BYTEA NULL DEFAULT NULL');
    }

    public function testCreateTableWithIndexes()
    {
        $table = [
            'name'    => 'glpi_tickets',
            'columns' => [
                ['name' => 'id', 'type' => 'int32', 'nullable' => false, 'autoIncrement' => true],
                ['name' => 'name', 'type' => 'string', 'length' => 255, 'nullable' => true],
                ['name' => 'entities_id', 'type' => 'int32', 'nullable' => false, 'unsigned' => true, 'default' => 0],
                ['name' => 'status', 'type' => 'int32', 'nullable' => false, 'default' => 1],
            ],
            'indexes' => [
                ['name' => 'PRIMARY', 'type' => 'primary', 'columns' => [['name' => 'id']]],
                ['name' => 'entities_id', 'type' => 'index', 'columns' => [['name' => 'entities_id']]],
                ['name' => 'status', 'type' => 'unique', 'columns' => [['name' => 'status'], ['name' => 'entities_id']]],
            ],
        ];

        $statements = $this->dialect()->createTableStatements($table);

        // CREATE TABLE + 2 indexes (primary is inline)
        $this->array($statements)->hasSize(3);

        // Index names are prefixed with table name on PG
        $this->string($statements[1])->contains('CREATE INDEX "glpi_tickets_entities_id" ON "glpi_tickets"');
        $this->string($statements[2])->contains('CREATE UNIQUE INDEX "glpi_tickets_status" ON "glpi_tickets"');
    }

    public function testCreateTableWithColumnComment()
    {
        $table = [
            'name'    => 'glpi_configs',
            'columns' => [
                ['name' => 'id', 'type' => 'int32', 'nullable' => false, 'autoIncrement' => true],
                ['name' => 'context', 'type' => 'string', 'length' => 150, 'nullable' => true, 'comment' => 'Configuration context'],
            ],
            'indexes' => [
                ['name' => 'PRIMARY', 'type' => 'primary', 'columns' => [['name' => 'id']]],
            ],
        ];

        $statements = $this->dialect()->createTableStatements($table);

        // PG puts comments as separate statements
        $this->array($statements)->hasSize(2);
        $this->string($statements[1])->contains('COMMENT ON COLUMN "glpi_configs"."context" IS');
    }

    public function testAlterTableAddColumn()
    {
        $operation = [
            'kind'          => 'alter_table',
            'table'         => 'glpi_tickets',
            'add_columns'   => [
                ['name' => 'priority', 'type' => 'int32', 'nullable' => false, 'default' => 3],
            ],
            'alter_columns' => [],
            'drop_columns'  => [],
            'indexes'       => [],
            'foreign_keys'  => [],
        ];

        $statements = $this->dialect()->renderOperation($operation);
        $this->array($statements)->hasSize(1);
        $this->string($statements[0])->isIdenticalTo(
            'ALTER TABLE "glpi_tickets" ADD "priority" INTEGER NOT NULL DEFAULT 3'
        );
    }

    public function testAlterTableModifyColumn()
    {
        $operation = [
            'kind'          => 'alter_table',
            'table'         => 'glpi_tickets',
            'add_columns'   => [],
            'alter_columns' => [
                ['name' => 'name', 'type' => 'string', 'length' => 500, 'nullable' => true, 'default' => null],
            ],
            'drop_columns'  => [],
            'indexes'       => [],
            'foreign_keys'  => [],
        ];

        $statements = $this->dialect()->renderOperation($operation);

        // PostgreSQL ALTER COLUMN generates separate TYPE/NULL/DEFAULT statements
        $this->array($statements)->hasSize(3);
        $this->string($statements[0])->contains('ALTER COLUMN "name" TYPE VARCHAR(500)');
        $this->string($statements[1])->contains('ALTER COLUMN "name" DROP NOT NULL');
        $this->string($statements[2])->contains('ALTER COLUMN "name" SET DEFAULT NULL');
    }

    public function testAlterTableDropColumn()
    {
        $operation = [
            'kind'          => 'alter_table',
            'table'         => 'glpi_tickets',
            'add_columns'   => [],
            'alter_columns' => [],
            'drop_columns'  => ['legacy_field'],
            'indexes'       => [],
            'foreign_keys'  => [],
        ];

        $statements = $this->dialect()->renderOperation($operation);
        $this->array($statements)->hasSize(1);
        $this->string($statements[0])->isIdenticalTo(
            'ALTER TABLE "glpi_tickets" DROP COLUMN "legacy_field"'
        );
    }

    public function testAlterTableAddForeignKey()
    {
        $operation = [
            'kind'          => 'alter_table',
            'table'         => 'glpi_tickets',
            'add_columns'   => [],
            'alter_columns' => [],
            'drop_columns'  => [],
            'indexes'       => [],
            'foreign_keys'  => [
                [
                    'action'             => 'add',
                    'name'               => 'fk_tickets_users',
                    'columns'            => ['users_id'],
                    'referenced_table'   => 'glpi_users',
                    'referenced_columns' => ['id'],
                ],
            ],
        ];

        $statements = $this->dialect()->renderOperation($operation);
        $this->array($statements)->hasSize(1);
        $this->string($statements[0])->contains('ADD CONSTRAINT "fk_tickets_users"');
        $this->string($statements[0])->contains('FOREIGN KEY ("users_id")');
        $this->string($statements[0])->contains('REFERENCES "glpi_users" ("id")');
    }

    public function testAlterTableDropForeignKey()
    {
        $operation = [
            'kind'          => 'alter_table',
            'table'         => 'glpi_tickets',
            'add_columns'   => [],
            'alter_columns' => [],
            'drop_columns'  => [],
            'indexes'       => [],
            'foreign_keys'  => [
                ['action' => 'drop', 'name' => 'fk_tickets_users'],
            ],
        ];

        $statements = $this->dialect()->renderOperation($operation);
        $this->array($statements)->hasSize(1);
        $this->string($statements[0])->contains('DROP CONSTRAINT IF EXISTS "fk_tickets_users"');
    }

    public function testDropIndex()
    {
        $operation = [
            'kind'  => 'delete_index',
            'table' => 'glpi_tickets',
            'name'  => 'status',
        ];

        $statements = $this->dialect()->renderOperation($operation);
        $this->array($statements)->hasSize(1);
        // PG uses DROP INDEX (not ALTER TABLE DROP INDEX like MySQL)
        $this->string($statements[0])->contains('DROP INDEX IF EXISTS');
    }

    public function testDeleteTable()
    {
        $statements = $this->dialect()->renderOperation([
            'kind'  => 'delete_table',
            'table' => 'glpi_obsolete',
        ]);

        $this->array($statements)->hasSize(1);
        $this->string($statements[0])->isIdenticalTo('DROP TABLE IF EXISTS "glpi_obsolete"');
    }

    public function testRenameColumn()
    {
        $statements = $this->dialect()->renderOperation([
            'kind'  => 'rename_column',
            'table' => 'glpi_tickets',
            'from'  => 'old_name',
            'to'    => 'new_name',
        ]);

        $this->array($statements)->hasSize(1);
        $this->string($statements[0])->isIdenticalTo(
            'ALTER TABLE "glpi_tickets" RENAME COLUMN "old_name" TO "new_name"'
        );
    }

    public function testAutoIncrementInt64UsesBigserial()
    {
        $table = [
            'name'    => 'glpi_big',
            'columns' => [
                ['name' => 'id', 'type' => 'int64', 'nullable' => false, 'autoIncrement' => true],
            ],
            'indexes' => [
                ['name' => 'PRIMARY', 'type' => 'primary', 'columns' => [['name' => 'id']]],
            ],
        ];

        $statements = $this->dialect()->createTableStatements($table);
        $this->string($statements[0])->contains('"id" BIGSERIAL NOT NULL');
    }

    public function testCustomTypeStripsCollation()
    {
        $table = [
            'name'    => 'glpi_custom',
            'columns' => [
                ['name' => 'id', 'type' => 'int32', 'nullable' => false, 'autoIncrement' => true],
                ['name' => 'data', 'type' => 'custom', 'custom' => "TEXT COLLATE 'utf8_unicode_ci'", 'nullable' => true],
            ],
            'indexes' => [
                ['name' => 'PRIMARY', 'type' => 'primary', 'columns' => [['name' => 'id']]],
            ],
        ];

        $statements = $this->dialect()->createTableStatements($table);
        $ddl = $statements[0];

        // Custom type should have COLLATE stripped on PG
        $this->string($ddl)->notContains('COLLATE');
        $this->string($ddl)->contains('"data" TEXT NULL');
    }

    public function testLongtextCustomTypeMapsToText()
    {
        $table = [
            'name'    => 'glpi_custom',
            'columns' => [
                ['name' => 'id', 'type' => 'int32', 'nullable' => false, 'autoIncrement' => true],
                ['name' => 'body', 'type' => 'custom', 'custom' => 'LONGTEXT', 'nullable' => true],
            ],
            'indexes' => [
                ['name' => 'PRIMARY', 'type' => 'primary', 'columns' => [['name' => 'id']]],
            ],
        ];

        $statements = $this->dialect()->createTableStatements($table);
        $ddl = $statements[0];

        // LONGTEXT custom type should map to TEXT on PG
        $this->string($ddl)->contains('"body" TEXT NULL');
        $this->string($ddl)->notContains('LONGTEXT');
    }
}
