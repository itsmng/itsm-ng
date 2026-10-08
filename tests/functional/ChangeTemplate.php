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

class ChangeTemplate extends DbTestCase
{
    private function createTemplate(array $hidden = [], array $mandatory = [], array $predefined = []): array
    {
        $template = new \ChangeTemplate();
        $id = $template->add(['name' => 'Change form regression template']);
        $this->integer((int)$id)->isGreaterThan(0);
        $fields = array_flip($template->getAllowedFields(true, true));
        foreach (['Hidden' => $hidden, 'Mandatory' => $mandatory, 'Predefined' => $predefined] as $kind => $values) {
            $class = '\\ChangeTemplate' . $kind . 'Field';
            foreach ($values as $key => $value) {
                $field = $kind === 'Predefined' ? $key : $value;
                $input = ['changetemplates_id' => $id, 'num' => $fields[$field]];
                if ($kind === 'Predefined') {
                    $input['value'] = $value;
                }
                $this->integer((int)(new $class())->add($input))->isGreaterThan(0);
            }
        }
        $category = new \ITILCategory();
        $category_id = $category->add(['name' => 'Change form regression category', 'changetemplates_id' => $id]);
        $this->integer((int)$category_id)->isGreaterThan(0);
        $this->boolean($template->getFromDB($id))->isTrue();
        return [$template, $category_id];
    }

    private function getXPath(string $html): \DOMXPath
    {
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">' . preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html));
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return new \DOMXPath($document);
    }

    private function renderChange(array $options = [], int $id = 0): string
    {
        $change = new \Change();
        if ($id > 0) {
            $change->getFromDB($id);
        } else {
            $change->getEmpty();
        }
        ob_start();
        $change->showForm($id, $options);
        return ob_get_clean();
    }

    protected function newChangeIdProvider(): array
    {
        return [[0], [-1]];
    }

    /**
     * @dataProvider newChangeIdProvider
     */
    public function testHiddenPredefinedValuesSurviveSubmission(int $new_id)
    {
        $this->login('itsm', 'itsm');
        [$template, $category] = $this->createTemplate(
            ['name', 'urgency', 'rolloutplancontent'],
            ['controlistcontent'],
            ['name' => 'Hidden predefined title', 'urgency' => 4, 'rolloutplancontent' => 'Deploy quietly']
        );
        $xpath = $this->getXPath($this->renderChange(['itilcategories_id' => $category], $new_id));
        foreach (['name' => 'Hidden predefined title', 'urgency' => '4', 'rolloutplancontent' => 'Deploy quietly'] as $name => $value) {
            $nodes = $xpath->query('//input[@type="hidden" and @name="' . $name . '"]');
            $this->integer($nodes->length)->isEqualTo(1);
            $this->string($nodes->item(0)->getAttribute('value'))->isEqualTo($value);
            $this->integer($xpath->query('//select[@name="' . $name . '"] | //textarea[@name="' . $name . '"]')->length)->isEqualTo(0);
        }
        $this->integer($xpath->query('//textarea[@name="controlistcontent" and @required]')->length)->isEqualTo(1);
        $this->integer($xpath->query('//textarea[@name="checklistcontent" and @required]')->length)->isEqualTo(0);
        $this->integer($xpath->query('//input[@name="_changetemplate"]')->length)->isEqualTo(1);

        $change = new \Change();
        $input = ['entities_id' => 0, 'itilcategories_id' => $category, '_changetemplate' => $template->getID(), 'content' => 'A change'];
        foreach (['name', 'urgency', 'rolloutplancontent'] as $name) {
            $input[$name] = $xpath->query('//input[@name="' . $name . '"]')->item(0)->getAttribute('value');
        }
        $this->boolean($change->add($input + ['controlistcontent' => '']))->isFalse();
        $this->hasSessionMessages(ERROR, ['Mandatory fields are not filled. Please correct: Control list']);
        $id = $change->add($input + ['controlistcontent' => 'Verified']);
        $this->integer((int)$id)->isGreaterThan(0);
        $this->boolean($change->getFromDB($id))->isTrue();
        $this->string($change->fields['name'])->isEqualTo('Hidden predefined title');
        $this->integer((int)$change->fields['urgency'])->isEqualTo(4);
        $this->string($change->fields['rolloutplancontent'])->isEqualTo('Deploy quietly');

        ob_start();
        $change->showAnalysisForm($id);
        $analysis = $this->getXPath(ob_get_clean());
        $this->integer($analysis->query('//textarea[@name="controlistcontent" and @required]')->length)->isEqualTo(1);
        ob_start();
        $change->showPlanForm($id);
        $plan = $this->getXPath(ob_get_clean());
        $this->integer($plan->query('//textarea[@name="rolloutplancontent"]')->length)->isEqualTo(0);
        $this->string($plan->query('//input[@name="rolloutplancontent"]')->item(0)->getAttribute('value'))->isEqualTo('Deploy quietly');
    }

    public function testCategorySwitchPreservesEditsAndResetsOldDefaults()
    {
        $this->login('itsm', 'itsm');
        [$first, $first_category] = $this->createTemplate([], [], ['name' => 'First title', 'urgency' => 4, 'impactcontent' => 'Old impact']);
        [$second, $second_category] = $this->createTemplate([], [], ['name' => 'Second title', 'urgency' => 5]);
        $old_defaults = \Toolbox::prepareArrayForInput(['name' => 'First title', 'urgency' => 4, 'impactcontent' => 'Old impact']);
        $options = ['itilcategories_id' => $second_category, '_changetemplate' => $first->getID(), '_predefined_fields' => $old_defaults,
            'name' => 'My edited title', 'urgency' => 4, 'impactcontent' => 'Old impact', 'content' => 'My edited description'];
        $html = $this->renderChange($options);
        $xpath = $this->getXPath($html);
        $this->string($xpath->query('//input[@name="name"]')->item(0)->getAttribute('value'))->isEqualTo('My edited title');
        $this->string(trim($xpath->query('//textarea[@name="content"]')->item(0)->textContent))->isEqualTo('My edited description');
        $this->string($xpath->query('//select[@name="urgency"]//option[@selected]')->item(0)->getAttribute('value'))->isEqualTo('5');
        $this->string(trim($xpath->query('//textarea[@name="impactcontent"]')->item(0)->textContent))->isEmpty();
        $this->string($xpath->query('//input[@name="_changetemplate"]')->item(0)->getAttribute('value'))->isEqualTo((string)$second->getID());
        $this->string($html)->contains('this.form.submit();');
        $options['itilcategories_id'] = 0;
        $xpath = $this->getXPath($this->renderChange($options));
        $this->string($xpath->query('//select[@name="urgency"]//option[@selected]')->item(0)->getAttribute('value'))->isEqualTo('3');
    }

    public function testPredefinedDocumentsTasksAndItemsAreSubmitted()
    {
        $this->login('itsm', 'itsm');
        $document_id = (new \Document())->add(['name' => 'Template document']);
        $task_id = (new \TaskTemplate())->add(['name' => 'Template task', 'content' => 'Task from template']);
        $computer_id = (int)getItemByTypeName('Computer', '_test_pc01', true);
        [$template, $category] = $this->createTemplate([], [], [
            '_documents_id' => $document_id,
            '_tasktemplates_id' => $task_id,
            'items_id' => 'Computer_' . $computer_id,
        ]);
        $xpath = $this->getXPath($this->renderChange(['itilcategories_id' => $category]));
        $input = ['name' => 'Change with relations', 'content' => 'Template relations', 'entities_id' => 0,
            'itilcategories_id' => $category, '_changetemplate' => $template->getID()];
        foreach (['_documents_id' => $document_id, '_tasktemplates_id' => $task_id] as $field => $value) {
            $node = $xpath->query('//input[@name="' . $field . '[]"]')->item(0);
            $this->object($node)->isInstanceOf(\DOMElement::class);
            $this->integer((int)$node->getAttribute('value'))->isEqualTo((int)$value);
            $input[$field] = [$node->getAttribute('value')];
        }
        $node = $xpath->query('//input[@name="items_id[Computer][' . $computer_id . ']"]')->item(0);
        $this->object($node)->isInstanceOf(\DOMElement::class);
        $input['items_id'] = ['Computer' => [$computer_id => $node->getAttribute('value')]];
        $change = new \Change();
        $id = $change->add($input);
        $this->integer((int)$id)->isGreaterThan(0);
        $this->integer(countElementsInTable('glpi_changes_items', ['changes_id' => $id, 'items_id' => $computer_id]))->isEqualTo(1);
        $this->integer(countElementsInTable('glpi_documents_items', ['itemtype' => 'Change', 'items_id' => $id, 'documents_id' => $document_id]))->isEqualTo(1);
        $this->integer(countElementsInTable('glpi_changetasks', ['changes_id' => $id]))->isEqualTo(1);
    }

    public function testHiddenPredefinedActorsAreKeptWithoutVisibleRows()
    {
        $this->login('itsm', 'itsm');
        $user_id = (int)getItemByTypeName('User', 'tech', true);
        [$template, $category] = $this->createTemplate(
            ['_users_id_assign', '_groups_id_assign', '_suppliers_id_assign'],
            ['_users_id_assign'],
            ['_users_id_assign' => $user_id]
        );
        $xpath = $this->getXPath($this->renderChange(['itilcategories_id' => $category]));
        $hidden = $xpath->query('//input[@name="_users_id_assign[]"]');
        $this->integer($hidden->length)->isEqualTo(1);
        $this->integer((int)$hidden->item(0)->getAttribute('value'))->isEqualTo($user_id);
        $panel = '//section[@data-actor-role="assign"]';
        $this->integer($xpath->query($panel . '/parent::*[contains(@class, "d-none")]')->length)->isEqualTo(1);
        $this->integer($xpath->query($panel . '//div[@data-actor-entry and not(contains(@class, "d-none"))]')->length)->isEqualTo(0);
        $this->integer($xpath->query($panel . '//select')->length)->isEqualTo(0);
        $change = new \Change();
        $id = $change->add(['name' => 'Hidden technician', 'content' => 'Hidden technician from template',
            'entities_id' => 0, 'itilcategories_id' => $category, '_changetemplate' => $template->getID(),
            '_users_id_assign' => [$hidden->item(0)->getAttribute('value')]]);
        $this->integer((int)$id)->isGreaterThan(0);
        $this->integer(countElementsInTable('glpi_changes_users', ['changes_id' => $id, 'type' => \CommonITILActor::ASSIGN, 'users_id' => $user_id]))->isEqualTo(1);
        $xpath = $this->getXPath($this->renderChange([], $id));
        $this->integer($xpath->query($panel . '//div[@data-actor-entry and not(contains(@class, "d-none"))]')->length)->isEqualTo(0);
    }

    public function testConfigurationTablesUseTwigAndAssociationIds()
    {
        $this->login('itsm', 'itsm');
        [$template] = $this->createTemplate(['urgency'], ['name'], ['content' => 'Predefined content']);
        foreach (['Hidden', 'Mandatory', 'Predefined'] as $kind) {
            $class = '\\ChangeTemplate' . $kind . 'Field';
            ob_start();
            $class::showForITILTemplate($template);
            $html = ob_get_clean();
            preg_match('/<script type="application\/json"[^>]*>(.*?)<\/script>/s', $html, $match);
            $config = json_decode($match[1], true, 512, JSON_THROW_ON_ERROR);
            $this->string($config['selection']['type'])->isEqualTo('massive-action');
            $this->array($config['dataSource']['rows'])->hasSize(1);
            $relation = new $class();
            $relation->getFromDBByCrit(['changetemplates_id' => $template->getID()]);
            $id = $relation->getID();
            $this->string($config['selection']['values'][$id])->isEqualTo('item[' . ltrim($class, '\\') . '][' . $id . ']');
            $this->string($html)->notContains('tab_cadre');
            $_SESSION['glpiactiveprofile']['itiltemplate'] = READ;
            ob_start();
            $class::showForITILTemplate($template);
            $readonly_html = ob_get_clean();
            preg_match('/<script type="application\/json"[^>]*>(.*?)<\/script>/s', $readonly_html, $readonly_match);
            $readonly_config = json_decode($readonly_match[1], true, 512, JSON_THROW_ON_ERROR);
            $this->string($readonly_config['selection']['type'])->isEqualTo('none');
            $this->string($readonly_html)->notContains('name="add"');
            $_SESSION['glpiactiveprofile']['itiltemplate'] = ALLSTANDARDRIGHT;
        }
    }
}
