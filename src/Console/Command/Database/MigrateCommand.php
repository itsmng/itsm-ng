<?php

namespace itsmng\Console\Command\Database;

use Glpi\Console\AbstractCommand;
use itsmng\Database\Migrations\MigrationHistoryRepository;
use itsmng\Database\Migrations\MigrationRepository;
use itsmng\Database\Migrations\MigrationRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('itsmng:database:migrate');
        $this->setDescription(__('Run PSR-4 schema migrations.'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $DB;

        $update = new \Update($DB);
        $currents = $update->getCurrents();
        $current_db_version = $currents['dbversion'];
        $itsm_current_db_version = $currents['itsmdbversion'] ?? '1.0.0';

        if (
            version_compare($current_db_version, ITSM_SCHEMA_VERSION, 'ne')
            || version_compare($itsm_current_db_version, ITSM_SCHEMA_VERSION, 'ne')
        ) {
            $output->writeln('<error>' . __('Run itsmng:database:update before PSR-4 schema migrations.') . '</error>');
            return 1;
        }

        $history = new MigrationHistoryRepository($DB);
        $history->ensureBaseline(ITSM_SCHEMA_VERSION);

        $runner = new MigrationRunner(
            $DB,
            new MigrationRepository(GLPI_ROOT . '/src/Database/Migrations/Core'),
            $history
        );

        $versions = $runner->migrate();
        if ($versions === []) {
            $output->writeln('<info>' . __('No schema migration needed.') . '</info>');
            return 0;
        }

        foreach ($versions as $version) {
            $output->writeln('<info>' . sprintf(__('Applied schema migration %s.'), $version) . '</info>');
        }

        return 0;
    }
}
