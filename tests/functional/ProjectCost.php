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

class ProjectCost extends DbTestCase
{
    public function testBeginDateForcesEndDateWhenInvalid()
    {
        $this->login();

        $project = new \Project();
        $project_id = $project->add([
           'name' => 'project-cost-' . $this->getUniqueString(),
        ]);
        $this->integer((int)$project_id)->isGreaterThan(0);

        $obj = new \ProjectCost();
        $id = $obj->add([
           'projects_id' => $project_id,
           'name'        => 'cost-' . $this->getUniqueString(),
           'begin_date'  => '2025-03-10',
           'end_date'    => '2025-03-01',
           'cost'        => 75,
        ]);
        $this->integer((int)$id)->isGreaterThan(0);
        $this->boolean($obj->getFromDB($id))->isTrue();
        $this->string($obj->getField('end_date'))->isEqualTo('2025-03-10');

        $this->boolean($obj->update([
           'id'          => $id,
           'begin_date'  => '2025-04-12',
           'end_date'    => '2025-04-01',
        ]))->isTrue();
        $this->boolean($obj->getFromDB($id))->isTrue();
        $this->string($obj->getField('end_date'))->isEqualTo('2025-04-12');
    }

    public function testInitBasedOnPreviousCost()
    {
        $this->login();

        $project = new \Project();
        $project_id = $project->add([
           'name' => 'project-cost-prev-' . $this->getUniqueString(),
        ]);
        $this->integer((int)$project_id)->isGreaterThan(0);

        $previous = new \ProjectCost();
        $previous_id = $previous->add([
           'projects_id' => $project_id,
           'name'        => 'previous-cost-' . $this->getUniqueString(),
           'begin_date'  => '2025-01-01',
           'end_date'    => '2025-01-15',
           'cost'        => 120,
        ]);
        $this->integer((int)$previous_id)->isGreaterThan(0);

        $obj = new \ProjectCost();
        $obj->fields['projects_id'] = $project_id;
        $obj->initBasedOnPrevious();

        $this->string((string)$obj->fields['begin_date'])->isEqualTo('2025-01-15');
        $this->string((string)$obj->fields['name'])->isEqualTo((string)$previous->fields['name']);
        $this->integer((int)$obj->fields['cost'])->isEqualTo(120);
    }
}
