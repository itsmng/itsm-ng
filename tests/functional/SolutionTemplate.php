<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2025 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org
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

namespace tests\units;

use DbTestCase;

class SolutionTemplate extends DbTestCase
{
    public function testGroupRestriction()
    {
        $this->login();
        $this->ensureRestrictionTableExists();

        $allowed_group = new \Group();
        $allowed_group_id = $allowed_group->add([
           'name' => 'solution-template-allowed-group-' . $this->getUniqueString(),
        ]);
        $this->integer((int)$allowed_group_id)->isGreaterThan(0);

        $denied_group = new \Group();
        $denied_group_id = $denied_group->add([
           'name' => 'solution-template-denied-group-' . $this->getUniqueString(),
        ]);
        $this->integer((int)$denied_group_id)->isGreaterThan(0);

        $template = new \SolutionTemplate();
        $visible_to_all_id = $template->add([
           'name' => 'solution-template-all-' . $this->getUniqueString(),
        ]);
        $visible_to_allowed_id = $template->add([
           'name' => 'solution-template-allowed-' . $this->getUniqueString(),
        ]);
        $visible_to_denied_id = $template->add([
           'name' => 'solution-template-denied-' . $this->getUniqueString(),
        ]);

        $this->integer((int)$visible_to_all_id)->isGreaterThan(0);
        $this->integer((int)$visible_to_allowed_id)->isGreaterThan(0);
        $this->integer((int)$visible_to_denied_id)->isGreaterThan(0);

        $link = new \Group_SolutionTemplate();
        $this->integer((int)$link->add([
           'solutiontemplates_id' => $visible_to_allowed_id,
           'groups_id'            => $allowed_group_id,
        ]))->isGreaterThan(0);
        $this->integer((int)$link->add([
           'solutiontemplates_id' => $visible_to_denied_id,
           'groups_id'            => $denied_group_id,
        ]))->isGreaterThan(0);

        $this->boolean(\SolutionTemplate::isVisibleForCurrentUser($visible_to_all_id))->isTrue();
        $this->boolean(\SolutionTemplate::isVisibleForCurrentUser($visible_to_allowed_id))->isFalse();
        $this->boolean(\SolutionTemplate::isVisibleForCurrentUser($visible_to_denied_id))->isFalse();
        $this->boolean(\Group_SolutionTemplate::canAccessItem($visible_to_allowed_id, [$allowed_group_id]))->isTrue();
        $this->boolean(\Group_SolutionTemplate::canAccessItem($visible_to_denied_id, [$allowed_group_id]))->isFalse();

        $condition = \SolutionTemplate::getGroupVisibilityCondition([$allowed_group_id]);
        $this->array($condition)->hasKey('NOT');
        $this->array($condition['NOT'])->hasKey('glpi_solutiontemplates.id');
        $this->array($condition['NOT']['glpi_solutiontemplates.id'])->contains($visible_to_denied_id);
        $this->array($condition['NOT']['glpi_solutiontemplates.id'])->notContains($visible_to_all_id);
        $this->array($condition['NOT']['glpi_solutiontemplates.id'])->notContains($visible_to_allowed_id);
    }


    private function ensureRestrictionTableExists(): void
    {
        global $DB;

        $DB->queryOrDie(
            "CREATE TABLE IF NOT EXISTS `glpi_groups_solutiontemplates` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `solutiontemplates_id` int(11) NOT NULL DEFAULT '0',
                `groups_id` int(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                UNIQUE KEY `unicity` (`solutiontemplates_id`,`groups_id`),
                KEY `groups_id` (`groups_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
            $DB->error()
        );
    }
}
