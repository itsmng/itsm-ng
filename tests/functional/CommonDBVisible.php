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

class CommonDBVisible extends \DbTestCase
{
    public function testTargetsRequireParentUpdatePermission()
    {
        $this->login();
        $group_id = (new \Group())->add([
           'name' => 'Visibility security group',
           'entities_id' => $_SESSION['glpiactive_entity'],
        ]);
        $this->integer((int) $group_id)->isGreaterThan(0);
        $parents = [];
        foreach ([
           \KnowbaseItem::class => ['name' => 'Security article', 'answer' => 'Private content'],
           \Reminder::class => ['name' => 'Security reminder', 'text' => 'Private content'],
           \RSSFeed::class => ['name' => 'Security feed', 'url' => 'https://example.invalid/feed'],
        ] as $type => $input) {
            $parent = new $type();
            $id = $parent->add($input + ['users_id' => \Session::getLoginUserID()]);
            $this->integer((int) $id)->isGreaterThan(0);
            $relation = 'Entity_' . $type;
            $this->integer((int) (new $relation())->add([
               $type::getForeignKeyField() => $id,
               'entities_id' => 0,
               'is_recursive' => 1,
            ]))->isGreaterThan(0);
            $parents[$type] = $id;
        }

        foreach ([false, true] as $can_update) {
            if ($can_update) {
                $this->login();
            } else {
                $this->login('tech', 'tech');
                foreach (array_keys($parents) as $type) {
                    $_SESSION['glpiactiveprofile'][$type::$rightname] = READ;
                }
            }
            foreach ($parents as $type => $id) {
                $parent = new $type();
                $this->boolean($parent->can($id, READ))->isTrue();
                $this->boolean($parent->can($id, UPDATE))->isEqualTo($can_update);
                foreach ([
                   'User' => ['users_id' => \Session::getLoginUserID()],
                   'Group' => ['groups_id' => $group_id, 'entities_id' => 0],
                   'Profile' => ['profiles_id' => 1, 'entities_id' => 0],
                   'Entity' => ['entities_id' => 0, 'is_recursive' => 1],
                ] as $target => $input) {
                    $relation = $target === 'User' ? $type . '_User' : $target . '_' . $type;
                    if ($type === \KnowbaseItem::class && $target === 'Profile') {
                        $relation = \KnowbaseItem_Profile::class;
                    }
                    $input[$type::getForeignKeyField()] = $id;
                    $this->boolean((new $relation())->can(-1, CREATE, $input))->isEqualTo($can_update);
                }
            }
        }
    }
}
