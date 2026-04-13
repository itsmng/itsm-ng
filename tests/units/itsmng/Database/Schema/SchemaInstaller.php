<?php

namespace tests\units\itsmng\Database\Schema;

class SchemaInstaller extends \GLPITestCase
{
    public function testExecuteOperationsConvertsMyIsamTablesBeforeAddingForeignKeys()
    {
        $this->mockGenerator->orphanize('__construct');

        $queries = [];
        $db = new \mock\DBmysql();
        $this->calling($db)->getDbType = 'mysql';
        $this->calling($db)->listTables = static function (string $table, array $where = []) {
            return new class ($where) {
                public function __construct(private array $where)
                {
                }

                public function count(): int
                {
                    return ($this->where['engine'] ?? null) === 'MyIsam' ? 1 : 0;
                }
            };
        };
        $this->calling($db)->queryOrDie = static function (string $sql, string $message = '') use (&$queries) {
            $queries[] = [$sql, $message];
            return true;
        };

        $installer = new \itsmng\Database\Schema\SchemaInstaller();
        $installer->executeOperations([
            [
                'kind'          => 'alter_table',
                'table'         => 'glpi_appliances',
                'add_columns'   => [],
                'alter_columns' => [],
                'drop_columns'  => [],
                'indexes'       => [],
                'foreign_keys'  => [
                    [
                        'action'             => 'add',
                        'name'               => 'fk_glpi_appliances_entities_id',
                        'columns'            => ['entities_id'],
                        'referenced_table'   => 'glpi_entities',
                        'referenced_columns' => ['id'],
                    ],
                ],
            ],
        ], $db);

        $this->array($queries)->isIdenticalTo([
            [
                'ALTER TABLE `glpi_appliances` ENGINE = InnoDB',
                'Prepare table engine for foreign key migration',
            ],
            [
                'ALTER TABLE `glpi_entities` ENGINE = InnoDB',
                'Prepare table engine for foreign key migration',
            ],
            [
                'ALTER TABLE `glpi_appliances` ADD CONSTRAINT `fk_glpi_appliances_entities_id` FOREIGN KEY (`entities_id`) REFERENCES `glpi_entities` (`id`)',
                'Schema migration',
            ],
        ]);
    }

    public function testExecuteOperationsSkipsExistingMySqlIndexes()
    {
        $this->mockGenerator->orphanize('__construct');

        $queries = [];
        $index_rows = [
            ['Key_name' => 'entities_id'],
        ];

        $db = new \mock\DBmysql();
        $this->calling($db)->getDbType = 'mysql';
        $this->calling($db)->listIndexes = new \ArrayIterator($index_rows);
        $this->calling($db)->fetchAssoc = static function (\ArrayIterator $iterator) {
            if (!$iterator->valid()) {
                return null;
            }

            $current = $iterator->current();
            $iterator->next();

            return $current;
        };
        $this->calling($db)->queryOrDie = static function (string $sql, string $message = '') use (&$queries) {
            $queries[] = [$sql, $message];
            return true;
        };

        $installer = new \itsmng\Database\Schema\SchemaInstaller();
        $installer->executeOperations([
            [
                'kind'          => 'alter_table',
                'table'         => 'glpi_apiclients',
                'add_columns'   => [],
                'alter_columns' => [],
                'drop_columns'  => [],
                'indexes'       => [
                    [
                        'name'    => 'entities_id',
                        'type'    => 'index',
                        'columns' => [
                            ['name' => 'entities_id'],
                        ],
                    ],
                ],
                'foreign_keys'  => [],
            ],
        ], $db);

        $this->array($queries)->isEmpty();
    }

    public function testExecuteOperationsSkipsExistingForeignKeys()
    {
        $this->mockGenerator->orphanize('__construct');

        $queries = [];

        $db = new \mock\DBmysql();
        $this->calling($db)->getDbType = 'mysql';
        $this->calling($db)->constraintExists = static fn (string $table, string $constraint): bool => $table === 'glpi_apiclients'
            && $constraint === 'fk_glpi_apiclients_entities_id';
        $this->calling($db)->queryOrDie = static function (string $sql, string $message = '') use (&$queries) {
            $queries[] = [$sql, $message];
            return true;
        };

        $installer = new \itsmng\Database\Schema\SchemaInstaller();
        $installer->executeOperations([
            [
                'kind'          => 'alter_table',
                'table'         => 'glpi_apiclients',
                'add_columns'   => [],
                'alter_columns' => [],
                'drop_columns'  => [],
                'indexes'       => [],
                'foreign_keys'  => [
                    [
                        'action'             => 'add',
                        'name'               => 'fk_glpi_apiclients_entities_id',
                        'columns'            => ['entities_id'],
                        'referenced_table'   => 'glpi_entities',
                        'referenced_columns' => ['id'],
                    ],
                ],
            ],
        ], $db);

        $this->array($queries)->isEmpty();
    }
}
