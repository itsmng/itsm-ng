<?php

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2022 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

namespace Glpi\Console\Database;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

use Glpi\Console\AbstractCommand;
use itsmng\Database\Schema\CoreSchema;
use itsmng\Database\Schema\Dialect\DialectResolver;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder;

class CheckCommand extends AbstractCommand
{
    /**
     * Error code returned when failed to read empty SQL file.
     *
     * @var integer
     */
    public const ERROR_UNABLE_TO_READ_EMPTYSQL = 1;

    protected function configure()
    {
        parent::configure();

        $this->setName('itsmng:database:check');
        $this->setAliases(['db:check']);
        $this->setDescription(__('Check for schema differences between current database and installation file.'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $differ = new Differ(new DiffOnlyOutputBuilder(''));
        $dialect = $this->resolveDialect();
        foreach ($this->getSchemaTables() as $table) {
            $table_name = $table['name'];

            $output->writeln(
                sprintf(__('Processing table "%s"...'), $table_name),
                OutputInterface::VERBOSITY_VERY_VERBOSE
            );

            $statements = $dialect->createTableStatements($table);
            $base_table_struct = $this->db->getTableSchema($table_name, $statements);
            $existing_table_struct = $this->db->getTableSchema($table_name);

            if ($existing_table_struct != $base_table_struct) {
                $message = sprintf(__('Table schema differs for table "%s".'), $table_name);
                $output->writeln(
                    '<info>' . $message . '</info>',
                    OutputInterface::VERBOSITY_QUIET
                );
                if ($existing_table_struct['schema'] != $base_table_struct['schema']) {
                    $output->write(
                        $differ->diff($base_table_struct['schema'], $existing_table_struct['schema'])
                    );
                }

                if ($existing_table_struct['index'] != $base_table_struct['index']) {
                    $output->write(
                        $differ->diff(
                            implode("\n", $base_table_struct['index']),
                            implode("\n", $existing_table_struct['index'])
                        )
                    );
                }
            }
        }

        return 0; // Success
    }

    protected function getSchemaTables(): array
    {
        return CoreSchema::definition()['tables'];
    }

    protected function resolveDialect()
    {
        return (new DialectResolver())->resolve($this->db);
    }
}
