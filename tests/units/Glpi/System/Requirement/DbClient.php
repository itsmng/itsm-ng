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

namespace tests\units\Glpi\System\Requirement;

class DbClient extends \GLPITestCase
{
    public function testCheckUsingPdoMysql()
    {
        $this->newTestedInstance();
        $this->function->extension_loaded = function (string $name): bool {
            return $name === 'pdo_mysql';
        };

        $this->boolean($this->testedInstance->isValidated())->isEqualTo(true);
        $this->array($this->testedInstance->getValidationMessages())
           ->isEqualTo(['pdo_mysql extension is installed']);
    }

    public function testCheckUsingPdoPgsql()
    {
        $this->newTestedInstance();
        $this->function->extension_loaded = function (string $name): bool {
            return $name === 'pdo_pgsql';
        };
        $this->boolean($this->testedInstance->isValidated())->isEqualTo(true);
        $this->array($this->testedInstance->getValidationMessages())
           ->isEqualTo(['pdo_pgsql extension is installed']);
    }

    public function testCheckOnMissingExtensions()
    {
        $this->newTestedInstance();
        $this->function->extension_loaded = false;

        $this->boolean($this->testedInstance->isValidated())->isEqualTo(false);
        $this->array($this->testedInstance->getValidationMessages())
           ->isEqualTo(['Neither pdo_mysql nor pdo_pgsql extensions are available']);
    }
}
