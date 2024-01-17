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
use Glpi\Console\Command\ForceNoPluginsOptionCommandInterface;
use Migration;
use Session;
use Update;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class UpdateCommand extends AbstractCommand implements ForceNoPluginsOptionCommandInterface {

   /**
    * Error code returned when trying to update from an unstable version.
    *
    * @var integer
    */
   const ERROR_NO_UNSTABLE_UPDATE = 1;

   /**
    * Error code returned when security key file is missing.
    *
    * @var integer
    */
   const ERROR_MISSING_SECURITY_KEY_FILE = 2;

   protected $requires_db_up_to_date = false;

   protected function configure() {
      parent::configure();

      $this->setName('itsmng:database:update');
      $this->setAliases(['db:update']);
      $this->setDescription(__('Update database schema to new version'));

      $this->addOption(
         'allow-unstable',
         'u',
         InputOption::VALUE_NONE,
         __('Allow update to an unstable version')
      );

      $this->addOption(
         'force',
         'f',
         InputOption::VALUE_NONE,
         __('Force execution of update from v-1 version of ITSM-ng even if schema did not changed')
      );
   }

   protected function initialize(InputInterface $input, OutputInterface $output) {

      parent::initialize($input, $output);

      $this->outputWarningOnMissingOptionnalRequirements();

      $this->db->disableTableCaching();
   }

   protected function execute(InputInterface $input, OutputInterface $output) {

      $allow_unstable = $input->getOption('allow-unstable');
      $force          = $input->getOption('force');
      $no_interaction = $input->getOption('no-interaction'); // Base symfony/console option

      $update = new Update($this->db);

      // Initialize entities
      $_SESSION['glpidefault_entity'] = 0;
      Session::initEntityProfiles(2);
      Session::changeProfile(4);

      // Display current/future state informations
      $currents                  = $update->getCurrents();
      $current_version           = $currents['version'];
      $current_db_version        = $currents['dbversion'];
      $itsm_current_version      = $currents['itsmversion'] ?? '1.0.0';
      $itsm_current_db_version   = $currents['itsmdbversion'] ?? '1.0.0';

      global $migration; // Migration scripts are using global migrations
      $migration = new Migration(ITSM_SCHEMA_VERSION);
      $migration->setOutputHandler($output);
      $update->setMigration($migration);

      $informations = new Table($output);
      $informations->setHeaders(['', __('Current'), _n('Target', 'Targets', 1)]);
      $informations->addRow([__('Database host'), $this->db->dbhost, '']);
      $informations->addRow([__('Database name'), $this->db->dbdefault, '']);
      $informations->addRow([__('Database user'), $this->db->dbuser, '']);
      $informations->addRow([__('ITSM version'), $itsm_current_version, ITSM_VERSION]);
      $informations->addRow([__('ITSM database version'), $itsm_current_db_version, ITSM_SCHEMA_VERSION]);
      $informations->render();

      if (defined('ITSM_PREVER')) {
         // Prevent unstable update unless explicitly asked
         if (!$allow_unstable && version_compare($current_db_version, ITSM_SCHEMA_VERSION, 'ne')) {
            $output->writeln(
               sprintf(
                  '<error>' . __('%s is not a stable release. Please upgrade manually or add --allow-unstable option.') . '</error>',
                  ITSM_SCHEMA_VERSION
               ),
               OutputInterface::VERBOSITY_QUIET
            );
            return self::ERROR_NO_UNSTABLE_UPDATE;
         }
      }

      if (version_compare($current_db_version, ITSM_SCHEMA_VERSION, 'eq') && !$force && version_compare($itsm_current_db_version, ITSM_SCHEMA_VERSION, 'eq')) {
         $output->writeln('<info>' . __('No migration needed.') . '</info>');
         return 0;
      }

      if ($update->isExpectedSecurityKeyFileMissing()) {
         $output->writeln(
            sprintf(
               '<error>' . __('The key file "%s" used to encrypt/decrypt sensitive data is missing. You should retrieve it from your previous installation or encrypted data will be unreadable.') . '</error>',
               $update->getExpectedSecurityKeyFilePath()
            ),
            OutputInterface::VERBOSITY_QUIET
         );

         if ($no_interaction) {
            return self::ERROR_MISSING_SECURITY_KEY_FILE;
         }
      }

      if (!$no_interaction) {
         // Ask for confirmation (unless --no-interaction)
         /** @var \Symfony\Component\Console\Helper\QuestionHelper $question_helper */
         $question_helper = $this->getHelper('question');
         $run = $question_helper->ask(
            $input,
            $output,
            new ConfirmationQuestion(__('Do you want to continue ?') . ' [Yes/no]', true)
         );
         if (!$run) {
            $output->writeln(
               '<comment>' . __('Update aborted.') . '</comment>',
               OutputInterface::VERBOSITY_VERBOSE
            );
            return 0;
         }
      }

      if (version_compare($current_db_version, ITSM_SCHEMA_VERSION, 'ne')) {
         $update->doUpdates($current_version);
      }

      if (version_compare($current_db_version, ITSM_SCHEMA_VERSION, 'ne') && version_compare($itsm_current_db_version, ITSM_SCHEMA_VERSION, 'ne')) {
         // Migration is considered as done as Update class has the responsibility
         // to run updates if schema has changed (even for "pre-versions".
         $output->writeln('<info>' . __('Migration done.') . '</info>');
      } else if ($force) {
         // Replay last update script even if there is no schema change.
         // It can be used in dev environment when update script has been updated/fixed.
         include_once(GLPI_ROOT . '/install/itsm_update/update_101_110.php');
         update101to110();

         $output->writeln('<info>' . __('Last migration replayed.') . '</info>');
      }

      return 0; // Success
   }

   public function getNoPluginsOptionValue() {

      return true;
   }
}
