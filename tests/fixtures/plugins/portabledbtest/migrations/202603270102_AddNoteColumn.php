<?php

namespace tests\fixtures\plugins\portabledbtest\migrations;

use itsmng\Database\Migrations\Attribute\SchemaMigration;
use itsmng\Database\Migrations\Migration;

#[SchemaMigration(
    version: 'portabledbtest_202603270102_add_note_column',
    description: 'Add a note column to the portable DB test records table.'
)]
final class Migration202603270102AddNoteColumn extends Migration
{
    public function up(): void
    {
        $this->alter()
            ->table('glpi_plugin_portabledbtest_records')
            ->addColumn('note')->asString(255)->nullable()->end();
    }

    public function down(): void
    {
        $this->delete()->column('note')->fromTable('glpi_plugin_portabledbtest_records');
    }
}
