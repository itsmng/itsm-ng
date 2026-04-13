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

class ITILCategory extends DbTestCase
{
    public function testPrepareInputForAdd()
    {
        $this->login();

        $category = new \ITILCategory();

        $input = [
           'name'    => '_test_itilcategory_1',
           'comment' => '_test_itilcategory_1',
        ];
        $expected = [
           'name'              => '_test_itilcategory_1',
           'comment'           => '_test_itilcategory_1',
           'itilcategories_id' => 0,
           'level'             => 1,
           'completename'      => '_test_itilcategory_1',
           'code'              => '',
        ];
        $this->array($category->prepareInputForAdd($input))->isIdenticalTo($expected);

        $input = [
           'name'    => '_test_itilcategory_2',
           'comment' => '_test_itilcategory_2',
           'code'    => 'code2',
        ];
        $expected = [
           'name'              => '_test_itilcategory_2',
           'comment'           => '_test_itilcategory_2',
           'code'              => 'code2',
           'itilcategories_id' => 0,
           'level'             => 1,
           'completename'      => '_test_itilcategory_2',
        ];
        $this->array($category->prepareInputForAdd($input))->isIdenticalTo($expected);

        $input = [
           'name'    => '_test_itilcategory_3',
           'comment' => '_test_itilcategory_3',
           'code'    => ' code 3 ',
        ];
        $expected = [
           'name'              => '_test_itilcategory_3',
           'comment'           => '_test_itilcategory_3',
           'code'              => 'code 3',
           'itilcategories_id' => 0,
           'level'             => 1,
           'completename'      => '_test_itilcategory_3',
        ];
        $this->array($category->prepareInputForAdd($input))->isIdenticalTo($expected);
    }

    public function testPrepareInputForUpdate()
    {
        $this->login();

        $category = new \ITILCategory();
        $category_id = (int)$category->add([
           'name'    => '_test_itilcategory_1',
           'comment' => '_test_itilcategory_1',
        ]);
        $this->integer($category_id)->isGreaterThan(0);

        $this->boolean($category->update([
           'id'   => $category_id,
           'code' => ' code 1 ',
        ]))->isTrue();
        $this->boolean($category->getFromDB($category_id))->isTrue();
        $this->string($category->fields['name'])->isIdenticalTo('_test_itilcategory_1');
        $this->string($category->fields['comment'])->isIdenticalTo('_test_itilcategory_1');
        $this->string($category->fields['code'])->isIdenticalTo('code 1');

        $this->boolean($category->update([
           'id'      => $category_id,
           'comment' => 'new comment',
        ]))->isTrue();
        $this->boolean($category->getFromDB($category_id))->isTrue();
        $this->string($category->fields['name'])->isIdenticalTo('_test_itilcategory_1');
        $this->string($category->fields['comment'])->isIdenticalTo('new comment');
        $this->string($category->fields['code'])->isIdenticalTo('code 1');

        $this->boolean($category->update([
           'id'   => $category_id,
           'code' => '',
        ]))->isTrue();
        $this->boolean($category->getFromDB($category_id))->isTrue();
        $this->string($category->fields['name'])->isIdenticalTo('_test_itilcategory_1');
        $this->string($category->fields['comment'])->isIdenticalTo('new comment');
        $this->string($category->fields['code'])->isIdenticalTo('');
    }

    public function testRecursiveITILCategoryRights()
    {
        $this->login();

        $root_entity_id = getItemByTypeName('Entity', '_test_root_entity', true);
        $child_entity_id = getItemByTypeName('Entity', '_test_child_1', true);

        $category = new \ITILCategory();

        $category_id = (int)$category->add([
           'name'         => 'Recursive Category Test',
           'entities_id'  => $root_entity_id,
           'is_recursive' => 1,
        ]);
        $this->integer($category_id)->isGreaterThan(0);

        $non_recursive_category_id = (int)$category->add([
           'name'         => 'Non-Recursive Category Test',
           'entities_id'  => $root_entity_id,
           'is_recursive' => 0,
        ]);
        $this->integer($non_recursive_category_id)->isGreaterThan(0);

        $child_category_id = (int)$category->add([
           'name'         => 'Child Category Test',
           'entities_id'  => $child_entity_id,
           'is_recursive' => 0,
        ]);
        $this->integer($child_category_id)->isGreaterThan(0);

        $this->boolean(\Session::changeActiveEntities($child_entity_id))->isTrue();

        $this->boolean($category->can($category_id, READ))->isTrue();
        $this->boolean($category->can($non_recursive_category_id, READ))->isFalse();

        $this->boolean($category->getFromDB($category_id))->isTrue();
        $this->boolean($category->canUpdateItem())->isFalse();
        $this->boolean($category->canDeleteItem())->isFalse();
        $this->boolean($category->canPurgeItem())->isFalse();

        $this->boolean($category->getFromDB($child_category_id))->isTrue();
        $this->boolean($category->canUpdateItem())->isTrue();
        $this->boolean($category->canDeleteItem())->isTrue();
        $this->boolean($category->canPurgeItem())->isTrue();

        $this->boolean(\Session::changeActiveEntities($root_entity_id))->isTrue();

        $this->boolean($category->getFromDB($category_id))->isTrue();
        $this->boolean($category->canUpdateItem())->isTrue();
        $this->boolean($category->canDeleteItem())->isTrue();
        $this->boolean($category->canPurgeItem())->isTrue();
    }
}
