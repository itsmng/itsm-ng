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

namespace tests\units;

use DbTestCase;

class ContractCost extends DbTestCase
{
    public function testBeginDateForcesEndDateWhenInvalid()
    {
        $this->login();

        $contract = new \Contract();
        $contract_id = $contract->add([
           'name'        => 'contract-cost-' . $this->getUniqueString(),
           'entities_id' => 0,
        ]);
        $this->integer((int)$contract_id)->isGreaterThan(0);

        $obj = new \ContractCost();
        $id = $obj->add([
           'contracts_id' => $contract_id,
           'name'         => 'cost-' . $this->getUniqueString(),
           'begin_date'   => '2025-01-10',
           'end_date'     => '2025-01-01',
           'cost'         => 50,
        ]);
        $this->integer((int)$id)->isGreaterThan(0);
        $this->boolean($obj->getFromDB($id))->isTrue();
        $this->string($obj->getField('end_date'))->isEqualTo('2025-01-10');

        $this->boolean($obj->update([
           'id'           => $id,
           'begin_date'   => '2025-02-12',
           'end_date'     => '2025-02-01',
        ]))->isTrue();
        $this->boolean($obj->getFromDB($id))->isTrue();
        $this->string($obj->getField('end_date'))->isEqualTo('2025-02-12');
    }

    public function testInitBasedOnPreviousCost()
    {
        $this->login();

        $contract = new \Contract();
        $contract_id = $contract->add([
           'name'        => 'contract-cost-prev-' . $this->getUniqueString(),
           'entities_id' => 0,
        ]);
        $this->integer((int)$contract_id)->isGreaterThan(0);

        $previous = new \ContractCost();
        $previous_id = $previous->add([
           'contracts_id' => $contract_id,
           'name'         => 'previous-cost-' . $this->getUniqueString(),
           'begin_date'   => '2025-05-01',
           'end_date'     => '2025-05-20',
           'cost'         => 210,
        ]);
        $this->integer((int)$previous_id)->isGreaterThan(0);

        $obj = new \ContractCost();
        $obj->fields['contracts_id'] = $contract_id;
        $obj->initBasedOnPrevious();

        $this->string((string)$obj->fields['begin_date'])->isEqualTo('2025-05-20');
        $this->string((string)$obj->fields['name'])->isEqualTo((string)$previous->fields['name']);
        $this->integer((int)$obj->fields['cost'])->isEqualTo(210);
    }
}
