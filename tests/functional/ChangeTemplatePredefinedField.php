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

class ChangeTemplatePredefinedField extends DbTestCase
{
    private function getNewTemplate(): \ChangeTemplate
    {
        $template = new \ChangeTemplate();
        $templates_id = $template->add([
           'name' => 'Change template predefined field reference',
        ]);

        $this->integer((int)$templates_id)->isGreaterThan(0);
        $this->boolean($template->getFromDB($templates_id))->isTrue();

        return $template;
    }

    public function testPrepareInputForAddNormalizesDocumentDropdownPayload()
    {
        $this->login();

        $template = $this->getNewTemplate();
        $change = new \Change();
        $document = new \Document();
        $documents_id = $document->add([
           'name' => 'Change template predefined document',
        ]);

        $this->integer((int)$documents_id)->isGreaterThan(0);

        $predefined = new \ChangeTemplatePredefinedField();
        $input = $predefined->prepareInputForAdd([
           'changetemplates_id' => $template->getID(),
           'num'                => $change->getSearchOptionIDByField('field', 'name', 'glpi_documents'),
           'peer_documents_id'  => (string)$documents_id,
        ]);

        $this->array($input)->hasKey('value');
        $this->integer((int)$input['value'])->isEqualTo((int)$documents_id);
        $this->string((string)$input['field'])->isEqualTo('documents_id');
        $this->array($input)->notHasKey('peer_documents_id');
    }

    public function testDeletingItemtypePredefinedFieldAlsoDeletesItemsId()
    {
        $this->login();

        $template = $this->getNewTemplate();
        $change = new \Change();
        $computer_id = (int)getItemByTypeName('Computer', '_test_pc01', true);
        $items_table = $change->getItemsTable();
        $itemtype_num = $change->getSearchOptionIDByField('field', 'itemtype', $items_table);
        $items_id_num = $change->getSearchOptionIDByField('field', 'items_id', $items_table);

        $predefined = new \ChangeTemplatePredefinedField();
        $itemtype_id = $predefined->add([
           'changetemplates_id' => $template->getID(),
           'num'                => $itemtype_num,
           'value'              => 'Computer',
        ]);
        $this->integer((int)$itemtype_id)->isGreaterThan(0);

        $items_id = $predefined->add([
           'changetemplates_id' => $template->getID(),
           'num'                => $items_id_num,
           'value'              => 'Computer_' . $computer_id,
        ]);
        $this->integer((int)$items_id)->isGreaterThan(0);

        $this->integer(
            countElementsInTable(
                \ChangeTemplatePredefinedField::getTable(),
                ['changetemplates_id' => $template->getID()]
            )
        )->isEqualTo(2);

        $itemtype_predefined = new \ChangeTemplatePredefinedField();
        $this->boolean($itemtype_predefined->delete(['id' => $itemtype_id], true))->isTrue();
        $this->integer(
            countElementsInTable(
                \ChangeTemplatePredefinedField::getTable(),
                ['changetemplates_id' => $template->getID()]
            )
        )->isEqualTo(0);
    }
}
