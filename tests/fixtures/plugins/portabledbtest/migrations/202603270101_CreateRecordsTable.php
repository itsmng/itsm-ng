<?php

namespace tests\fixtures\plugins\portabledbtest\migrations;

use itsmng\Database\Migrations\Attribute\SchemaMigration;
use itsmng\Database\Migrations\Migration;

#[SchemaMigration(
    version: 'portabledbtest_202603270101_create_records_table',
    description: 'Create the portable DB test records table.'
)]
final class Migration202603270101CreateRecordsTable extends Migration
{
    public function up(): void
    {
        $this->create()
            ->table('glpi_plugin_portabledbtest_records')
            ->withColumn('id')->asInt32(true)->identity()->primaryKey()->end()
            ->withColumn('code')->asString(64)->notNullable()->unique('portabledbtest_code')->end()
            ->withColumn('payload')->asString(255)->notNullable()->end();
    }

    public function down(): void
    {
        $this->delete()->table('glpi_plugin_portabledbtest_records');
    }
}
