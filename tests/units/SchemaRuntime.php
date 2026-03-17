<?php

namespace tests\units;

use itsmng\Database\Migrations\Migration;
use itsmng\Database\Schema\Dialect\MySqlDialect;
use itsmng\Database\Schema\Dialect\PostgreSqlDialect;
use itsmng\Database\Schema\SchemaFingerprint as RuntimeSchemaFingerprint;

class_alias(RuntimeSchemaFingerprint::class, \SchemaFingerprint::class);

class SchemaFingerprint extends \GLPITestCase
{
    public function testSchemaFingerprintIsDeterministic()
    {
        $schema = [
            'tables' => [
                [
                    'name' => 'glpi_demo',
                    'columns' => [
                        ['name' => 'name', 'type' => 'string', 'length' => 255, 'nullable' => true],
                        ['name' => 'id', 'type' => 'int32', 'nullable' => false, 'autoIncrement' => true],
                    ],
                    'indexes' => [
                        ['name' => 'PRIMARY', 'type' => 'primary', 'columns' => [['name' => 'id']]],
                    ],
                ],
            ],
        ];

        $reordered = [
            'tables' => [
                [
                    'indexes' => [
                        ['columns' => [['name' => 'id']], 'type' => 'primary', 'name' => 'PRIMARY'],
                    ],
                    'columns' => [
                        ['length' => 255, 'nullable' => true, 'type' => 'string', 'name' => 'name'],
                        ['autoIncrement' => true, 'nullable' => false, 'type' => 'int32', 'name' => 'id'],
                    ],
                    'name' => 'glpi_demo',
                ],
            ],
        ];

        $fingerprint = new RuntimeSchemaFingerprint();

        $this->string($fingerprint->hash($schema))
            ->isIdenticalTo($fingerprint->hash($reordered));
    }

    public function testMySqlDialectRendersCreateTable()
    {
        $dialect = new MySqlDialect();
        $statements = $dialect->createTableStatements([
            'name' => 'glpi_demo',
            'columns' => [
                ['name' => 'id', 'type' => 'int32', 'nullable' => false, 'autoIncrement' => true],
                ['name' => 'name', 'type' => 'string', 'length' => 50, 'nullable' => false],
                ['name' => 'date_creation', 'type' => 'timestamp', 'nullable' => false, 'default' => ['kind' => 'expression', 'value' => 'CURRENT_TIMESTAMP']],
            ],
            'indexes' => [
                ['name' => 'PRIMARY', 'type' => 'primary', 'columns' => [['name' => 'id']]],
                ['name' => 'name', 'type' => 'index', 'columns' => [['name' => 'name']]],
            ],
        ]);

        $this->array($statements)->hasSize(1);
        $this->string($statements[0])->contains('CREATE TABLE `glpi_demo`');
        $this->string($statements[0])->contains('`id` INT(11) NOT NULL AUTO_INCREMENT');
        $this->string($statements[0])->contains('PRIMARY KEY (`id`)');
        $this->string($statements[0])->contains('KEY `name` (`name`)');
    }

    public function testPostgreSqlDialectRendersCreateTable()
    {
        $dialect = new PostgreSqlDialect();
        $statements = $dialect->createTableStatements([
            'name' => 'glpi_demo',
            'columns' => [
                ['name' => 'id', 'type' => 'int32', 'nullable' => false, 'autoIncrement' => true],
                ['name' => 'payload', 'type' => 'json', 'nullable' => true],
            ],
            'indexes' => [
                ['name' => 'PRIMARY', 'type' => 'primary', 'columns' => [['name' => 'id']]],
            ],
        ]);

        $this->array($statements)->hasSize(1);
        $this->string($statements[0])->contains('CREATE TABLE "glpi_demo"');
        $this->string($statements[0])->contains('"id" SERIAL NOT NULL');
        $this->string($statements[0])->contains('"payload" JSONB NULL');
        $this->string($statements[0])->contains('PRIMARY KEY ("id")');
    }

    public function testMigrationBuilderProducesExpectedOperations()
    {
        $migration = new class () extends Migration {
            public function up(): void
            {
                $this->create()->table('glpi_demo')
                    ->withColumn('id')->asInt32()->notNullable()->primaryKey()->identity()
                    ->withColumn('name')->asString(50)->notNullable();
                $this->alter()->table('glpi_demo')
                    ->addColumn('is_active')->asBoolean()->notNullable()->withDefaultValue(true);
                $this->rename()->table('glpi_demo')->to('glpi_demo_new');
            }

            public function down(): void
            {
                $this->delete()->table('glpi_demo_new');
            }
        };

        $operations = $migration->buildOperations('up');

        $this->array($operations)->hasSize(3);
        $this->string($operations[0]['kind'])->isIdenticalTo('create_table');
        $this->string($operations[1]['kind'])->isIdenticalTo('alter_table');
        $this->string($operations[2]['kind'])->isIdenticalTo('rename_table');
        $this->string($operations[2]['to'])->isIdenticalTo('glpi_demo_new');
    }
}
