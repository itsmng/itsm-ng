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

class User extends \DbTestCase
{
    private function insertFixture($table, array $values)
    {
        global $DB;
        $this->boolean($DB->insert($table, $values))->isTrue();
        return (int)$DB->insertId();
    }

    protected function privateItemProvider()
    {
        $cases = [];
        foreach (['Reminder', 'RSSFeed'] as $type) {
            foreach (['none', 'self', 'user', 'group', 'profile', 'entity'] as $visibility) {
                $cases[$type . ' ' . $visibility] = [$type, $visibility];
            }
        }
        return $cases;
    }

    /**
     * @dataProvider privateItemProvider
     */
    public function testPurgeNotesAndFeeds($type, $visibility)
    {
        $this->login();
        $owner = $this->insertFixture('glpi_users', ['name' => 'private-data-owner']);
        $other = (int)\Session::getLoginUserID();
        $table = $type::getTable();
        $id = $this->insertFixture($table, ['name' => 'Private data', 'users_id' => $owner]);
        $other_id = $this->insertFixture($table, ['name' => 'Other user data', 'users_id' => $other]);
        $relations = $type === 'Reminder'
            ? ['glpi_reminders_users', 'glpi_groups_reminders', 'glpi_profiles_reminders', 'glpi_entities_reminders']
            : ['glpi_rssfeeds_users', 'glpi_groups_rssfeeds', 'glpi_profiles_rssfeeds', 'glpi_entities_rssfeeds'];
        $fk = $type::getForeignKeyField();
        if ($visibility !== 'none') {
            $index = ['self' => 0, 'user' => 0, 'group' => 1, 'profile' => 2, 'entity' => 3][$visibility];
            $fields = [
                'self' => ['users_id' => $owner],
                'user' => ['users_id' => $other],
                'group' => ['groups_id' => $this->insertFixture('glpi_groups', ['name' => 'Private data group']), 'entities_id' => 0],
                'profile' => ['profiles_id' => $_SESSION['glpiactiveprofile']['id'], 'entities_id' => 0],
                'entity' => ['entities_id' => 0],
            ];
            $this->insertFixture($relations[$index], [$fk => $id] + $fields[$visibility]);
        }

        $dependents = [];
        if ($type === 'Reminder') {
            $dependents['glpi_remindertranslations'] = $this->insertFixture('glpi_remindertranslations', [
                'reminders_id' => $id, 'language' => 'fr_FR', 'name' => 'Traduction',
            ]);
            $dependents['glpi_vobjects'] = $this->insertFixture('glpi_vobjects', [
                'itemtype' => $type, 'items_id' => $id, 'data' => 'calendar data',
            ]);
            $dependents['glpi_planningrecalls'] = $this->insertFixture('glpi_planningrecalls', [
                'itemtype' => $type, 'items_id' => $id, 'users_id' => $other,
            ]);
            $dependents['glpi_alerts'] = $this->insertFixture('glpi_alerts', [
                'itemtype' => 'PlanningRecall', 'items_id' => $dependents['glpi_planningrecalls'],
            ]);
        }

        $user = new \User();
        $this->boolean($user->delete(['id' => $owner], 1))->isTrue();
        $shared = !in_array($visibility, ['none', 'self']);
        $item = new $type();
        $this->boolean($item->getFromDB($id))->isEqualTo($shared);
        if ($shared) {
            $this->integer((int)$item->fields['users_id'])->isEqualTo(0);
            $this->integer($item->countVisibilities())->isEqualTo(1);
        }
        $this->boolean($item->getFromDB($other_id))->isTrue();
        $this->integer((int)$item->fields['users_id'])->isEqualTo($other);
        foreach ($dependents as $dependent_table => $dependent_id) {
            $this->integer(countElementsInTable($dependent_table, ['id' => $dependent_id]))->isEqualTo($shared ? 1 : 0);
        }
        $this->integer(countElementsInTable($relations[0], ['users_id' => $owner]))->isEqualTo(0);
    }

    public function testPurgeSearchesAndPersonalPreferences()
    {
        $this->login();
        $owner = $this->insertFixture('glpi_users', ['name' => 'private-data-owner']);
        $other = (int)\Session::getLoginUserID();
        $private = $this->insertFixture('glpi_savedsearches', [
            'name' => 'Private search', 'itemtype' => 'Computer', 'users_id' => $owner, 'is_private' => 1,
        ]);
        $public = $this->insertFixture('glpi_savedsearches', [
            'name' => 'Public search', 'itemtype' => 'Computer', 'users_id' => $owner, 'is_private' => 0,
        ]);
        $other_search = $this->insertFixture('glpi_savedsearches', [
            'name' => 'Other private search', 'itemtype' => 'Computer', 'users_id' => $other, 'is_private' => 1,
        ]);
        $alert = $this->insertFixture('glpi_savedsearches_alerts', [
            'savedsearches_id' => $private, 'operator' => 0, 'value' => 1,
        ]);
        $default = $this->insertFixture('glpi_savedsearches_users', [
            'savedsearches_id' => $public, 'itemtype' => 'Computer', 'users_id' => $owner,
        ]);
        $other_default = $this->insertFixture('glpi_savedsearches_users', [
            'savedsearches_id' => $public, 'itemtype' => 'Computer', 'users_id' => $other,
        ]);
        $computer = (int)getItemByTypeName('Computer', '_test_pc01', true);
        $reminder = $this->insertFixture('glpi_reminders', ['name' => 'Other reminder', 'users_id' => $other]);
        $recall = $this->insertFixture('glpi_planningrecalls', [
            'itemtype' => 'Reminder', 'items_id' => $reminder, 'users_id' => $owner,
        ]);
        $recall_alert = $this->insertFixture('glpi_alerts', ['itemtype' => 'PlanningRecall', 'items_id' => $recall]);
        $kanban = $this->insertFixture('glpi_items_kanbans', [
            'itemtype' => 'Computer', 'items_id' => $computer, 'users_id' => $owner, 'state' => '{}',
        ]);
        $global_kanban = $this->insertFixture('glpi_items_kanbans', [
            'itemtype' => 'Computer', 'items_id' => $computer, 'users_id' => 0, 'state' => '{}',
        ]);
        $preference = $this->insertFixture('glpi_displaypreferences', [
            'itemtype' => 'Computer', 'users_id' => $owner, 'num' => 1, 'rank' => 1,
        ]);
        $note = $this->insertFixture('glpi_notepads', [
            'itemtype' => 'Computer', 'items_id' => $computer, 'users_id' => $owner,
            'users_id_lastupdater' => $owner, 'content' => 'Shared asset note',
        ]);
        $kb = $this->insertFixture('glpi_knowbaseitems', ['name' => 'Knowledge article', 'users_id' => $owner]);

        $user = new \User();
        $this->boolean($user->delete(['id' => $owner], 1))->isTrue();
        foreach ([
            'glpi_users' => $owner,
            'glpi_savedsearches' => $private,
            'glpi_savedsearches_alerts' => $alert,
            'glpi_savedsearches_users' => $default,
            'glpi_planningrecalls' => $recall,
            'glpi_alerts' => $recall_alert,
            'glpi_items_kanbans' => $kanban,
            'glpi_displaypreferences' => $preference,
        ] as $table => $id) {
            $this->integer(countElementsInTable($table, ['id' => $id]))->isEqualTo(0);
        }
        foreach ([
            'glpi_savedsearches_users' => $other_default,
            'glpi_reminders' => $reminder,
            'glpi_items_kanbans' => $global_kanban,
            'glpi_notepads' => $note,
            'glpi_knowbaseitems' => $kb,
        ] as $table => $id) {
            $this->integer(countElementsInTable($table, ['id' => $id]))->isEqualTo(1);
        }
        $search = new \SavedSearch();
        $this->boolean($search->getFromDB($public))->isTrue();
        $this->integer((int)$search->fields['users_id'])->isEqualTo(0);
        $this->boolean($search->getFromDB($other_search))->isTrue();
        $this->integer((int)$search->fields['users_id'])->isEqualTo($other);
    }

    public function testTrashAndRestoreKeepPrivateData()
    {
        $this->login();
        $owner = $this->insertFixture('glpi_users', ['name' => 'private-data-owner']);
        $records = [];
        foreach (['glpi_reminders', 'glpi_rssfeeds', 'glpi_savedsearches'] as $table) {
            $fields = ['name' => 'Private data', 'users_id' => $owner];
            if ($table === 'glpi_savedsearches') {
                $fields += ['itemtype' => 'Computer', 'is_private' => 1];
            }
            $records[$table] = $this->insertFixture($table, $fields);
        }
        $records['glpi_planningrecalls'] = $this->insertFixture('glpi_planningrecalls', [
            'itemtype' => 'Reminder', 'items_id' => $records['glpi_reminders'], 'users_id' => $owner,
        ]);
        $user = new \User();
        $this->boolean($user->delete(['id' => $owner]))->isTrue();
        foreach ($records as $table => $id) {
            $this->integer(countElementsInTable($table, ['id' => $id, 'users_id' => $owner]))->isEqualTo(1);
        }
        $this->boolean($user->restore(['id' => $owner]))->isTrue();
        foreach ($records as $table => $id) {
            $this->integer(countElementsInTable($table, ['id' => $id, 'users_id' => $owner]))->isEqualTo(1);
        }
    }

    protected function dashboardAudienceProvider()
    {
        return [['none'], ['User'], ['Group'], ['Profile'], ['Entity']];
    }

    /**
     * @dataProvider dashboardAudienceProvider
     */
    public function testPurgePrivateDashboards($audience)
    {
        $this->login();
        $owner = $this->insertFixture('glpi_users', ['name' => 'private-dashboard-owner']);
        $id = $this->insertFixture('glpi_dashboards_dashboards', ['key' => 'private-dashboard-' . $audience, 'name' => 'Personal dashboard']);
        $right = $this->insertFixture('glpi_dashboards_rights', [
            'dashboards_dashboards_id' => $id, 'itemtype' => 'User', 'items_id' => $owner,
        ]);
        $card = $this->insertFixture('glpi_dashboards_items', [
            'dashboards_dashboards_id' => $id, 'gridstack_id' => 'private-card', 'card_id' => 'test-card',
        ]);
        if ($audience !== 'none') {
            $target = [
                'User' => (int)\Session::getLoginUserID(),
                'Group' => $this->insertFixture('glpi_groups', ['name' => 'Dashboard audience']),
                'Profile' => (int)$_SESSION['glpiactiveprofile']['id'],
                'Entity' => 0,
            ][$audience];
            $this->insertFixture('glpi_dashboards_rights', [
                'dashboards_dashboards_id' => $id, 'itemtype' => $audience, 'items_id' => $target,
            ]);
        }
        $global = $this->insertFixture('glpi_dashboards_dashboards', ['key' => 'global-dashboard-' . $audience, 'name' => 'Global dashboard']);
        $this->insertFixture('glpi_dashboards_rights', [
            'dashboards_dashboards_id' => $global, 'itemtype' => 'Entity', 'items_id' => 0,
        ]);
        $user = new \User();
        $this->boolean($user->delete(['id' => $owner], 1))->isTrue();
        $remaining = $audience === 'none' ? 0 : 1;
        $this->integer(countElementsInTable('glpi_dashboards_dashboards', ['id' => $id]))->isEqualTo($remaining);
        $this->integer(countElementsInTable('glpi_dashboards_items', ['id' => $card]))->isEqualTo($remaining);
        $this->integer(countElementsInTable('glpi_dashboards_rights', ['dashboards_dashboards_id' => $id]))->isEqualTo($remaining);
        $this->integer(countElementsInTable('glpi_dashboards_rights', ['id' => $right]))->isEqualTo(0);
        $this->integer(countElementsInTable('glpi_dashboards_dashboards', ['id' => $global]))->isEqualTo(1);
    }
}
