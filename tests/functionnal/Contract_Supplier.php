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

class Contract_Supplier extends DbTestCase
{
    public function testLinkAndUnlink()
    {
        $this->login();

        $contract = new \Contract();
        $contract_id = $contract->add([
           'name'        => 'contract-supplier-' . $this->getUniqueString(),
           'entities_id' => 0,
        ]);
        $this->integer((int)$contract_id)->isGreaterThan(0);

        $supplier_id = getItemByTypeName('Supplier', '_suplier01_name', true);
        $this->integer((int)$supplier_id)->isGreaterThan(0);

        $relation = new \Contract_Supplier();
        $relation_id = $relation->add([
           'contracts_id' => $contract_id,
           'suppliers_id' => $supplier_id,
        ]);
        $this->integer((int)$relation_id)->isGreaterThan(0);
        $this->boolean($relation->getFromDB($relation_id))->isTrue();

        $this->boolean($relation->delete(['id' => $relation_id]))->isTrue();
        $this->integer((int)countElementsInTable(
            \Contract_Supplier::getTable(),
            [
                'contracts_id' => $contract_id,
                'suppliers_id' => $supplier_id,
            ]
        ))->isEqualTo(0);
    }
}
