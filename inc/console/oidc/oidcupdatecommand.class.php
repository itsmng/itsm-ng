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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OidcUpdateCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('itsmng:oidc:update');
        $this->setAliases(['oidc:update']);
        $this->addOption('provider', null, InputOption::VALUE_REQUIRED, __('Only synchronize this OIDC source'));
        $this->setDescription(__('Mark OIDC identities for synchronization on their next login'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        global $DB;

        $provider = $input->getOption('provider');
        $where = [1 => 1];
        if ($provider !== null) {
            if (!ctype_digit((string) $provider) || (int) $provider < 1) {
                throw new \InvalidArgumentException('Invalid OIDC source ID.');
            }
            \OidcProvider::load((int) $provider, false);
            $where = ['oidcproviders_id' => (int) $provider];
        }
        $DB->updateOrDie('glpi_oidcidentities', ['needs_sync' => 1], $where);

        return 0; // Success
    }

    public function mustCheckMandatoryRequirements(): bool
    {

        return false;
    }
}
