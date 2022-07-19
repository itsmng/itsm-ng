<?php
/**
 * ---------------------------------------------------------------------
 * ITSM-NG 
 * Copyright (C) 2022 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org/
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ITSM-NG.
 *
 * ITSM-NG is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ITSM-NG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ITSM-NG. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

namespace Glpi\Console\Oidc;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

use Glpi\Console\AbstractCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OidcUpdateCommand extends AbstractCommand {

   protected function configure() {
      parent::configure();

      $this->setName('itsmng:oidc:update');
      $this->setAliases(['oidc:update']);
      $this->setDescription(__('Each ITSM-NG user using openID connect must log in again to update their personal information'));
   }

   protected function execute(InputInterface $input, OutputInterface $output) {

      global $DB;

      $querry = "UPDATE glpi_oidc_users SET `update` = 0;";
      $DB->queryOrDie($querry);

      return 0; // Success
   }

   public function mustCheckMandatoryRequirements(): bool {

      return false;
   }
}