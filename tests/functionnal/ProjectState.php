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

class ProjectState extends DbTestCase
{
    public function testCrudPersistsColorAndFinishedState()
    {
        $this->login();

        $project_state = new \ProjectState();
        $id = $project_state->add([
           'name'        => 'Project state test',
           'color'       => '#123456',
           'is_finished' => 1,
        ]);
        $this->integer((int)$id)->isGreaterThan(0);
        $this->boolean($project_state->getFromDB($id))->isTrue();
        $this->string($project_state->getField('color'))->isEqualTo('#123456');
        $this->integer((int)$project_state->getField('is_finished'))->isEqualTo(1);

        $this->boolean($project_state->update([
           'id'          => $id,
           'color'       => '#abcdef',
           'is_finished' => 0,
        ]))->isTrue();
        $this->boolean($project_state->getFromDB($id))->isTrue();
        $this->string($project_state->getField('color'))->isEqualTo('#abcdef');
        $this->integer((int)$project_state->getField('is_finished'))->isEqualTo(0);

        $this->boolean($project_state->delete(['id' => $id]))->isTrue();
    }
}
