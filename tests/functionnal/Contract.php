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

/* Test for inc/contract.class.php */

class Contract extends DbTestCase
{
    public function testClone()
    {
        $this->login();
        $this->setEntity('_test_root_entity', true);

        $contract = new \Contract();
        $input = [
           'name' => 'A test contract',
           'entities_id'  => 0
        ];
        $cid = $contract->add($input);
        $this->integer($cid)->isGreaterThan(0);

        $cost = new \ContractCost();
        $cost_id = $cost->add([
           'contracts_id' => $cid,
           'name'         => 'Test cost'
        ]);
        $this->integer($cost_id)->isGreaterThan(0);

        $suppliers_id = getItemByTypeName('Supplier', '_suplier01_name', true);
        $this->integer($suppliers_id)->isGreaterThan(0);

        $link_supplier = new \Contract_Supplier();
        $link_id = $link_supplier->add([
           'suppliers_id' => $suppliers_id,
           'contracts_id' => $cid
        ]);
        $this->integer($link_id)->isGreaterThan(0);

        $this->boolean($link_supplier->getFromDB($link_id))->isTrue();
        $relation_items = $link_supplier->getItemsAssociatedTo($contract->getType(), $cid);
        $this->array($relation_items)->hasSize(1, 'Original Contract_Supplier not found!');

        $citem = new \Contract_Item();
        $citems_id = $citem->add([
           'contracts_id' => $cid,
           'itemtype'     => 'Computer',
           'items_id'     => getItemByTypeName('Computer', '_test_pc01', true)
        ]);
        $this->integer($citems_id)->isGreaterThan(0);

        $this->boolean($citem->getFromDB($citems_id))->isTrue();
        $relation_items = $citem->getItemsAssociatedTo($contract->getType(), $cid);
        $this->array($relation_items)->hasSize(1, 'Original Contract_Item not found!');

        $cloned = $contract->clone();
        $this->integer($cloned)->isGreaterThan($cid);

        foreach ($contract->getCloneRelations() as $rel_class) {
            $this->integer(
                countElementsInTable(
                    $rel_class::getTable(),
                    ['contracts_id' => $cloned]
                )
            )->isIdenticalTo(1, 'Missing relation with ' . $rel_class);
        }
    }

    public function testContractSupplierLinkUnlink()
    {
        $this->login();
        $this->setEntity('_test_root_entity', true);

        $contract = new \Contract();
        $contract_id = $contract->add([
           'name'        => 'contract-supplier-link',
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

    public function testUpdateClearsOutdatedAlerts()
    {
        $this->login();
        $this->setEntity('_test_root_entity', true);

        $contract = new \Contract();
        $contract_id = $contract->add([
           'name'        => 'contract-alert-clear-' . $this->getUniqueString(),
           'entities_id' => 0,
           'begin_date'  => '2025-01-01',
           'duration'    => 12,
           'notice'      => 2,
        ]);
        $this->integer((int)$contract_id)->isGreaterThan(0);

        $alert = new \Alert();
        $end_alert_id = $alert->add([
           'itemtype' => 'Contract',
           'items_id' => $contract_id,
           'type'     => \Alert::END,
           'date'     => '2025-12-31 00:00:00',
        ]);
        $notice_alert_id = $alert->add([
           'itemtype' => 'Contract',
           'items_id' => $contract_id,
           'type'     => \Alert::NOTICE,
           'date'     => '2025-11-30 00:00:00',
        ]);
        $this->integer((int)$end_alert_id)->isGreaterThan(0);
        $this->integer((int)$notice_alert_id)->isGreaterThan(0);

        $this->boolean($contract->update([
           'id'         => $contract_id,
           'begin_date' => '2025-02-01',
           'duration'   => 14,
           'notice'     => 1,
        ]))->isTrue();

        $this->boolean((bool)\Alert::alertExists('Contract', $contract_id, \Alert::END))->isFalse();
        $this->boolean((bool)\Alert::alertExists('Contract', $contract_id, \Alert::NOTICE))->isFalse();
    }
}
