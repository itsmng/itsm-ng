<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
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

class Location extends DbTestCase
{
    public function testImportExternal()
    {
        $locations_id = \Dropdown::importExternal(
            'Location',
            'testImportExternal_1',
            getItemByTypeName('Entity', '_test_root_entity', true)
        );

        $this->integer((int)$locations_id)->isGreaterThan(0);

        $location = new \Location();
        $this->boolean($location->getFromDB($locations_id))->isTrue();
        $this->string($location->fields['name'])->isIdenticalTo('testImportExternal_1');
    }

    public function testFindIDByName()
    {
        $entities_id = getItemByTypeName('Entity', '_test_root_entity', true);

        $location = new \Location();
        $location_id = $location->add([
           'name'        => 'testFindIDByName_1',
           'entities_id' => $entities_id,
        ]);
        $this->integer((int)$location_id)->isGreaterThan(0);

        $params = [
           'name'        => 'testFindIDByName_1',
           'entities_id' => $entities_id,
        ];
        $this->integer((int)$location->findID($params))->isIdenticalTo((int)$location_id);

        $location_id_2 = $location->add([
           'locations_id' => $location_id,
           'name'         => 'testFindIDByName_2',
           'entities_id'  => $entities_id,
        ]);
        $this->integer((int)$location_id_2)->isGreaterThan(0);

        $params = [
           'name'         => 'testFindIDByName_2',
           'locations_id' => $location_id,
           'entities_id'  => $entities_id,
        ];
        $this->integer((int)$location->findID($params))->isIdenticalTo((int)$location_id_2);

        $params = [
           'name'        => 'testFindIDByName_2',
           'entities_id' => $entities_id,
        ];
        $this->integer((int)$location->findID($params))->isIdenticalTo(-1);
    }

    public function testFindIDByCompleteName()
    {
        $entities_id = getItemByTypeName('Entity', '_test_root_entity', true);

        $location = new \Location();
        $location_id = $location->add([
           'name'        => 'testFindIDByCompleteName_1',
           'entities_id' => $entities_id,
        ]);
        $this->integer((int)$location_id)->isGreaterThan(0);

        $params = [
           'completename' => 'testFindIDByCompleteName_1',
           'entities_id'  => $entities_id,
        ];
        $this->integer((int)$location->findID($params))->isIdenticalTo((int)$location_id);

        $location_id_2 = $location->add([
           'locations_id' => $location_id,
           'name'         => 'testFindIDByCompleteName_2',
           'entities_id'  => $entities_id,
        ]);
        $this->integer((int)$location_id_2)->isGreaterThan(0);

        $params = [
           'completename' => 'testFindIDByCompleteName_1 > testFindIDByCompleteName_2',
           'entities_id'  => $entities_id,
        ];
        $this->integer((int)$location->findID($params))->isIdenticalTo((int)$location_id_2);
    }

    public function testUnicity()
    {
        $location_1 = new \Location();
        $location_1_id = $location_1->add([
           'name' => 'Unique location',
        ]);
        $this->integer((int)$location_1_id)->isGreaterThan(0);
        $this->boolean($location_1->getFromDB($location_1_id))->isTrue();
        $this->string($location_1->fields['completename'])->isIdenticalTo('Unique location');

        $location_2 = new \Location();
        $location_2_id = $location_2->add([
           'name' => 'Non unique location',
        ]);
        $this->integer((int)$location_2_id)->isGreaterThan(0);
        $this->boolean($location_2->getFromDB($location_2_id))->isTrue();
        $this->string($location_2->fields['completename'])->isIdenticalTo('Non unique location');

        $this->exception(
            function () use ($location_2, $location_2_id) {
                $location_2->update([
                   'id'   => $location_2_id,
                   'name' => 'Unique location',
                ]);
            }
        )
           ->isInstanceOf('GlpitestSQLError')
           ->message
              ->matches("#Duplicate entry '.+' for key '(" . $location_2->getTable() . "\\.)?unicity'#");

        $this->boolean($location_2->getFromDB($location_2_id))->isTrue();
        $this->string($location_2->fields['name'])->isIdenticalTo('Non unique location');
        $this->string($location_2->fields['completename'])->isIdenticalTo('Non unique location');
    }

    protected function importProvider()
    {
        $root_entity_id = getItemByTypeName('Entity', '_test_root_entity', true);
        $sub_entity_id  = getItemByTypeName('Entity', '_test_child_1', true);

        return [
           [
              'input'    => [
                 'entities_id' => $root_entity_id,
                 'name'        => 'Import by name',
              ],
              'imported' => [
                 [
                    'entities_id' => $root_entity_id,
                    'name'        => 'Import by name',
                 ],
              ],
           ],
           [
              'input'    => [
                 'entities_id' => $sub_entity_id,
                 'name'        => 'Import by name',
              ],
              'imported' => [
                 [
                    'entities_id' => $sub_entity_id,
                    'name'        => 'Import by name',
                 ],
              ],
           ],
        ];
    }

    /**
     * @dataProvider importProvider
     */
    public function testImport(array $input, array $imported)
    {
        $instance = new \Location();
        $count_before_import = countElementsInTable(\Location::getTable());

        $this->integer((int)$instance->import($input))->isGreaterThan(0);
        $this->integer(countElementsInTable(\Location::getTable()) - $count_before_import)
           ->isIdenticalTo(count($imported));

        foreach ($imported as $location_data) {
            $this->integer(countElementsInTable(\Location::getTable(), $location_data))->isIdenticalTo(1);
        }
    }

    public function testImportTree()
    {
        $instance = new \Location();
        $imported_id = $instance->import([
           'entities_id'  => 0,
           'completename' => 'location 1 > sub location A',
        ]);

        $this->integer((int)$imported_id)->isGreaterThan(0);

        $imported = new \Location();
        $this->boolean($imported->getFromDB($imported_id))->isTrue();
        $this->string($imported->fields['name'])->isIdenticalTo('sub location A');
        $this->integer((int)$imported->fields['locations_id'])->isGreaterThan(0);

        $imported_parent = new \Location();
        $this->boolean($imported_parent->getFromDB($imported->fields['locations_id']))->isTrue();
        $this->string($imported_parent->fields['name'])->isIdenticalTo('location 1');
        $this->integer((int)$imported_parent->fields['locations_id'])->isIdenticalTo(0);

        $imported_id = $instance->import([
           'entities_id'  => 0,
           'completename' => '_location01 > sub location B',
        ]);

        $this->integer((int)$imported_id)->isGreaterThan(0);

        $imported = new \Location();
        $this->boolean($imported->getFromDB($imported_id))->isTrue();
        $this->string($imported->fields['name'])->isIdenticalTo('sub location B');
        $this->integer((int)$imported->fields['locations_id'])
           ->isIdenticalTo(getItemByTypeName('Location', '_location01', true));
    }

    public function testImportSeparator()
    {
        $instance = new \Location();
        $imported_id = $instance->import([
           'entities_id'  => 0,
           'completename' => '_location01 > _sublocation01',
        ]);

        $this->integer((int)$imported_id)->isIdenticalTo(getItemByTypeName('Location', '_sublocation01', true));

        $imported_id = $instance->import([
           'entities_id'  => 0,
           'completename' => '_location02>_sublocation02',
        ]);

        $this->integer((int)$imported_id)->isGreaterThan(0);

        $location = new \Location();
        $this->boolean($location->getFromDB($imported_id))->isTrue();
        $this->string($location->fields['name'])->isIdenticalTo('_sublocation02');
        $this->integer((int)$location->fields['locations_id'])
           ->isIdenticalTo(getItemByTypeName('Location', '_location02', true));
    }

    public function testImportParentVisibleEntity()
    {
        $instance = new \Location();

        $root_entity_id = getItemByTypeName('Entity', '_test_root_entity', true);
        $sub_entity_id  = getItemByTypeName('Entity', '_test_child_1', true);

        $parent_location = $this->createItem(
            \Location::class,
            [
               'entities_id'  => $root_entity_id,
               'is_recursive' => 1,
               'name'         => 'Parent location',
            ]
        );

        $imported_id = $instance->import([
           'entities_id'  => $sub_entity_id,
           'completename' => 'Parent location > Child name',
        ]);

        $this->integer((int)$imported_id)->isGreaterThan(0);

        $imported = new \Location();
        $this->boolean($imported->getFromDB($imported_id))->isTrue();
        $this->string($imported->fields['name'])->isIdenticalTo('Child name');
        $this->integer((int)$imported->fields['entities_id'])->isIdenticalTo($sub_entity_id);
        $this->integer((int)$imported->fields['locations_id'])->isIdenticalTo($parent_location->getID());
    }

    public function testImportParentNotVisibleEntity()
    {
        $instance = new \Location();

        $root_entity_id = getItemByTypeName('Entity', '_test_root_entity', true);
        $sub_entity_id  = getItemByTypeName('Entity', '_test_child_1', true);

        $root_level_1 = $this->createItem(
            \Location::class,
            [
               'entities_id'  => $root_entity_id,
               'is_recursive' => 0,
               'name'         => 'Location level 1',
            ]
        );
        $root_level_2 = $this->createItem(
            \Location::class,
            [
               'entities_id'  => $root_entity_id,
               'is_recursive' => 0,
               'name'         => 'Location level 2',
            ]
        );

        $imported_id = $instance->import([
           'entities_id'  => $sub_entity_id,
           'completename' => 'Location level 1 > Location level 2 > Location level 3',
        ]);

        $this->integer((int)$imported_id)->isGreaterThan(0);

        $level_3 = new \Location();
        $this->boolean($level_3->getFromDB($imported_id))->isTrue();
        $this->string($level_3->fields['name'])->isIdenticalTo('Location level 3');
        $this->integer((int)$level_3->fields['entities_id'])->isIdenticalTo($sub_entity_id);

        $level_2 = new \Location();
        $this->boolean($level_2->getFromDB($level_3->fields['locations_id']))->isTrue();
        $this->string($level_2->fields['name'])->isIdenticalTo('Location level 2');
        $this->integer((int)$level_2->fields['entities_id'])->isIdenticalTo($sub_entity_id);
        $this->integer((int)$level_2->getID())->isNotIdenticalTo($root_level_2->getID());

        $level_1 = new \Location();
        $this->boolean($level_1->getFromDB($level_2->fields['locations_id']))->isTrue();
        $this->string($level_1->fields['name'])->isIdenticalTo('Location level 1');
        $this->integer((int)$level_1->fields['entities_id'])->isIdenticalTo($sub_entity_id);
        $this->integer((int)$level_1->getID())->isNotIdenticalTo($root_level_1->getID());
        $this->integer((int)$level_1->fields['locations_id'])->isIdenticalTo(0);
    }

    public function testMaybeLocated()
    {
        global $CFG_GLPI;

        foreach ($CFG_GLPI['location_types'] as $type) {
            $item = new $type();
            $this->boolean($item->maybeLocated())->isTrue($type . ' cannot be located!');
        }
    }

    public function testTabs()
    {
        $this->login();

        $location = $this->createItem(\Location::class, [
           'name'        => 'testTabs',
           'entities_id' => getItemByTypeName('Entity', '_test_root_entity', true),
        ]);

        $tabs = $location->defineTabs();

        $this->array($tabs)->hasKeys([
           'Location$main',
           'Log$1',
           'Netpoint$1',
           'Document_Item$1',
           'Location$1',
           'Location$2',
        ]);
        $this->string($tabs[\Location::class . '$1'])->contains('Locations');
        $this->string($tabs[\Location::class . '$2'])->contains('Items');
    }
}
