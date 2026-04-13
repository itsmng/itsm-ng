<?php

namespace tests\units\itsmng\Database\Migrations;

class MigrationHistoryRepository extends \GLPITestCase
{
    public function testRecordPersistsMigrationClassNameVerbatim()
    {
        $this->mockGenerator->orphanize('__construct');

        $db = new \mock\DBmysql();
        $written = null;

        $this->calling($db)->tableExists = true;
        $this->calling($db)->getField = ['Type' => 'varchar(255)'];
        $this->calling($db)->insertOrDie = function (string $table, array $values, string $message) use (&$written) {
            $written = [
                'table'   => $table,
                'values'  => $values,
                'message' => $message,
            ];

            return true;
        };

        $repository = new \itsmng\Database\Migrations\MigrationHistoryRepository($db);
        $repository->record('202603270101', 'Foo\\Bar', 2);

        $this->array($written)->isIdenticalTo([
            'table'   => \itsmng\Database\Migrations\MigrationHistoryRepository::TABLE,
            'values'  => [
                'version'   => '202603270101',
                'migration' => 'Foo\\Bar',
                'batch'     => 2,
            ],
            'message' => 'Record applied schema migration',
        ]);
    }
}
