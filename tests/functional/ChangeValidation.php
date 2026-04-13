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

class ChangeValidation extends DbTestCase
{
    private function getNewChange(): int
    {
        $change = new \Change();
        $changes_id = $change->add([
           'name'        => 'change validation reference',
           'content'     => 'reference change for validation tests',
           'entities_id' => getItemByTypeName('Entity', '_test_root_entity', true),
        ]);

        $this->integer((int)$changes_id)->isGreaterThan(0);

        return (int)$changes_id;
    }

    public function testAddSplitsMultipleValidators()
    {
        $this->login();

        $changes_id = $this->getNewChange();
        $users_id_itsm = (int)getItemByTypeName('User', 'itsm', true);
        $users_id_tech = (int)getItemByTypeName('User', 'tech', true);

        $validation = new \ChangeValidation();
        $validation_id = $validation->add([
           'changes_id'         => $changes_id,
           'users_id_validate'  => [$users_id_itsm, $users_id_tech],
           'comment_submission' => 'Please validate this change',
        ]);

        $this->integer((int)$validation_id)->isGreaterThan(0);
        $this->integer(
            countElementsInTable(
                \ChangeValidation::getTable(),
                ['changes_id' => $changes_id]
            )
        )->isEqualTo(2);

        $change = new \Change();
        $this->boolean($change->getFromDB($changes_id))->isTrue();
        $this->integer((int)$change->fields['global_validation'])->isEqualTo(\CommonITILValidation::WAITING);
    }

    public function testRefusalRequiresComment()
    {
        $this->login();

        $changes_id = $this->getNewChange();
        $users_id_validate = (int)getItemByTypeName('User', 'itsm', true);

        $validation = new \ChangeValidation();
        $validation_id = $validation->add([
           'changes_id'         => $changes_id,
           'users_id_validate'  => $users_id_validate,
           'comment_submission' => 'Please validate this change',
        ]);

        $this->integer((int)$validation_id)->isGreaterThan(0);

        $this->login('itsm', 'itsm');

        $validation = new \ChangeValidation();
        $this->boolean(
            $validation->getFromDBByCrit([
               'changes_id'        => $changes_id,
               'users_id_validate' => $users_id_validate,
            ])
        )->isTrue();

        $this->boolean(
            $validation->update([
               'id'         => $validation->fields['id'],
               'changes_id' => $changes_id,
               'status'     => \CommonITILValidation::REFUSED,
            ])
        )->isFalse();
        $this->hasSessionMessages(ERROR, ['If approval is denied, specify a reason.']);

        $this->boolean(
            $validation->update([
               'id'                 => $validation->fields['id'],
               'changes_id'         => $changes_id,
               'status'             => \CommonITILValidation::REFUSED,
               'comment_validation' => 'Needs rework',
            ])
        )->isTrue();

        $change = new \Change();
        $this->boolean($change->getFromDB($changes_id))->isTrue();
        $this->integer((int)$change->fields['global_validation'])->isEqualTo(\CommonITILValidation::REFUSED);
    }
}
