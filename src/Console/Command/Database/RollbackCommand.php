<?php

namespace itsmng\Console\Command\Database;

use Glpi\Console\AbstractCommand;
use itsmng\Database\Migrations\MigrationRepository;
use itsmng\Database\Migrations\MigrationRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RollbackCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('itsmng:database:rollback');
        $this->setDescription(__('Rollback the latest PSR-4 schema migration batch.'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $DB;

        $runner = new MigrationRunner(
            $DB,
            new MigrationRepository(GLPI_ROOT . '/src/Database/Migrations/Core')
        );

        $versions = $runner->rollbackLatestBatch();
        if ($versions === []) {
            $output->writeln('<info>' . __('No schema migration batch to rollback.') . '</info>');
            return 0;
        }

        foreach ($versions as $version) {
            $output->writeln('<info>' . sprintf(__('Rolled back schema migration %s.'), $version) . '</info>');
        }

        return 0;
    }
}
