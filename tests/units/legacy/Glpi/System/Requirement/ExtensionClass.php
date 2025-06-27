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

class ExtensionClass extends \GLPITestCase
{
    public function testCheckOnExistingExtensionByClass()
    {

        $this->newTestedInstance('psr-log', 'Psr\\Log\\NullLogger');
        $this->boolean($this->testedInstance->isValidated())->isEqualTo(true);
        $this->array($this->testedInstance->getValidationMessages())
           ->isEqualTo(['psr-log extension is installed']);
    }

    public function testCheckOnExistingExtensionByInterface()
    {

        $this->newTestedInstance('psr-simplecache', 'Psr\\SimpleCache\\CacheInterface');
        $this->boolean($this->testedInstance->isValidated())->isEqualTo(true);
        $this->array($this->testedInstance->getValidationMessages())
           ->isEqualTo(['psr-simplecache extension is installed']);
    }

    public function testCheckOnMissingMandatoryExtension()
    {

        $this->newTestedInstance('fake_ext', 'Fake\\FakeExtension');
        $this->boolean($this->testedInstance->isValidated())->isEqualTo(false);
        $this->array($this->testedInstance->getValidationMessages())
           ->isEqualTo(['fake_ext extension is missing']);
    }

    public function testCheckOnMissingOptionalExtension()
    {

        $this->newTestedInstance('fake_ext', 'Fake\\FakeExtension', true);
        $this->boolean($this->testedInstance->isValidated())->isEqualTo(false);
        $this->array($this->testedInstance->getValidationMessages())
           ->isEqualTo(['fake_ext extension is not present']);
    }
}
