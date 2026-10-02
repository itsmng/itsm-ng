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

class ProblemTemplate extends DbTestCase
{
    private function createTemplate(string $type, array $hidden = [], array $mandatory = [], array $predefined = []): array
    {
        $class = '\\' . $type . 'Template';
        $template = new $class();
        $id = $template->add(['name' => $type . ' form regression template']);
        $this->integer((int)$id)->isGreaterThan(0);
        $fields = array_flip($template->getAllowedFields(true, true));
        $foreign_key = strtolower($type) . 'templates_id';
        foreach (['Hidden' => $hidden, 'Mandatory' => $mandatory, 'Predefined' => $predefined] as $kind => $values) {
            $relation_class = $class . $kind . 'Field';
            foreach ($values as $key => $value) {
                $field = $kind === 'Predefined' ? $key : $value;
                $input = [$foreign_key => $id, 'num' => $fields[$field]];
                if ($kind === 'Predefined') {
                    $input['value'] = $value;
                }
                $this->integer((int)(new $relation_class())->add($input))->isGreaterThan(0);
            }
        }
        $category_input = ['name' => $type . ' form regression category', 'is_incident' => 1, 'is_request' => 1, 'is_helpdeskvisible' => 1];
        if ($type === 'Ticket') {
            $category_input['tickettemplates_id_incident'] = $id;
            $category_input['tickettemplates_id_demand'] = $id;
        } else {
            $category_input[$foreign_key] = $id;
        }
        $category = (new \ITILCategory())->add($category_input);
        $this->integer((int)$category)->isGreaterThan(0);
        $this->boolean($template->getFromDB($id))->isTrue();
        return [$template, $category];
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

    private function renderForm(string $type, array $options, int $id = 0, bool $helpdesk = false): string
    {
        $class = '\\' . $type;
        $item = new $class();
        $id > 0 ? $item->getFromDB($id) : $item->getEmpty();
        ob_start();
        if ($helpdesk) {
            $previous_post = $_POST;
            $_POST = $options;
            $item->showFormHelpdesk(\Session::getLoginUserID());
            $_POST = $previous_post;
        } else {
            $item->showForm($id, $options);
        }
        return ob_get_clean();
    }

    protected function objectTypeProvider(): array
    {
        return [['Problem'], ['Ticket']];
    }

    protected function newFormProvider(): array
    {
        return [['Problem', 0], ['Problem', -1], ['Ticket', 0], ['Ticket', -1]];
    }

    /**
     * @dataProvider newFormProvider
     */
    public function testHiddenMandatoryAndPredefinedFields(string $type, int $new_id)
    {
        $this->login('itsm', 'itsm');
        [$template, $category] = $this->createTemplate($type, ['urgency'], ['name'], ['urgency' => 4, 'date' => '2026-09-01 12:34:56']);
        $xpath = $this->getXPath($this->renderForm($type, ['itilcategories_id' => $category], $new_id));
        $this->integer($xpath->query('//select[@name="urgency"]')->length)->isEqualTo(0);
        $this->string($xpath->query('//input[@type="hidden" and @name="urgency"]')->item(0)->getAttribute('value'))->isEqualTo('4');
        $this->integer($xpath->query('//input[@name="name" and @required]')->length)->isEqualTo(1);
        $this->string($xpath->query('//input[@name="date"]')->item(0)->getAttribute('value'))->isEqualTo('2026-09-01 12:34:56');
        $key = '_' . strtolower($type) . 'template';
        $this->integer($xpath->query('//form//input[@name="' . $key . '"]')->length)->isEqualTo(1);
        $class = '\\' . $type;
        $item = new $class();
        $input = ['entities_id' => 0, 'itilcategories_id' => $category, $key => $template->getID(), 'content' => 'Template regression', 'urgency' => 4];
        $this->boolean($item->add($input + ['name' => '']))->isFalse();
        $this->hasSessionMessages(ERROR, ['Mandatory fields are not filled. Please correct: Title']);
        $id = $item->add($input + ['name' => 'Required title', 'date' => '2026-09-01 12:34:56']);
        $this->integer((int)$id)->isGreaterThan(0);
        $this->boolean($item->getFromDB($id))->isTrue();
        $this->integer((int)$item->fields['urgency'])->isEqualTo(4);
        $this->string($item->fields['date'])->isEqualTo('2026-09-01 12:34:56');
        $existing = $this->getXPath($this->renderForm($type, [], (int)$id));
        $this->integer($existing->query('//select[@name="urgency"]')->length)->isEqualTo(0);
        $this->integer($existing->query('//input[@name="name" and @required]')->length)->isEqualTo(1);
    }

    /**
     * @dataProvider objectTypeProvider
     */
    public function testCategorySwitchKeepsEditsAndResetsOldDefaults(string $type)
    {
        $this->login('itsm', 'itsm');
        [$first] = $this->createTemplate($type, [], [], ['name' => 'First title', 'urgency' => 4, 'impact' => 5]);
        [$second, $category] = $this->createTemplate($type, [], [], ['name' => 'Second title', 'urgency' => 5, 'priority' => 4]);
        $key = '_' . strtolower($type) . 'template';
        $options = ['itilcategories_id' => $category, 'name' => 'Edited title', 'content' => 'Edited description',
            'urgency' => 4, 'impact' => 5, $key => $first->getID(),
            '_predefined_fields' => \Toolbox::prepareArrayForInput(['name' => 'First title', 'urgency' => 4, 'impact' => 5])];
        foreach ($type === 'Ticket' ? [false, true] : [false] as $helpdesk) {
            $html = $this->renderForm($type, $options, -1, $helpdesk);
            $xpath = $this->getXPath($html);
            $this->string($xpath->query('//input[@name="name"]')->item(0)->getAttribute('value'))->isEqualTo('Edited title');
            $this->string(trim($xpath->query('//textarea[@name="content"]')->item(0)->textContent))->isEqualTo('Edited description');
            $this->integer((int)$xpath->query('//select[@name="urgency"]/option[@selected]')->item(0)->getAttribute('value'))->isEqualTo(5);
            if (!$helpdesk) {
                $this->integer((int)$xpath->query('//select[@name="impact"]/option[@selected]')->item(0)->getAttribute('value'))->isEqualTo(3);
                $this->integer((int)$xpath->query('//select[@name="priority"]/option[@selected]')->item(0)->getAttribute('value'))->isEqualTo(4);
            }
            $this->string($html)->contains("dispatchEvent(new Event('submit'");
            $this->string($xpath->query('//input[@name="' . $key . '"]')->item(0)->getAttribute('value'))->isEqualTo((string)$second->getID());
        }
    }

    /**
     * @dataProvider objectTypeProvider
     */
    public function testPredefinedRelationsAndHiddenActorsAreSubmitted(string $type)
    {
        $this->login('itsm', 'itsm');
        $document = (new \Document())->add(['name' => 'Template document']);
        $task = (new \TaskTemplate())->add(['name' => 'Template task', 'content' => 'Template task content']);
        $computer = (int)getItemByTypeName('Computer', '_test_pc01', true);
        $tech = (int)getItemByTypeName('User', 'tech', true);
        [$template, $category] = $this->createTemplate(
            $type,
            ['items_id', '_documents_id', '_users_id_assign', '_groups_id_assign', '_suppliers_id_assign'],
            ['_users_id_assign', '_documents_id'],
            ['_documents_id' => $document, '_tasktemplates_id' => $task, 'items_id' => 'Computer_' . $computer, '_users_id_assign' => $tech]
        );
        $key = '_' . strtolower($type) . 'template';
        $input = ['name' => 'Relations from template', 'content' => 'Template relations', 'entities_id' => 0, 'itilcategories_id' => $category, $key => $template->getID()];
        foreach ($type === 'Ticket' ? [false, true] : [false] as $helpdesk) {
            $xpath = $this->getXPath($this->renderForm($type, ['itilcategories_id' => $category], -1, $helpdesk));
            foreach (['_documents_id' => $document, '_tasktemplates_id' => $task] as $field => $value) {
                $nodes = $xpath->query('//form//input[@name="' . $field . '[]"]');
                $this->integer($nodes->length)->isEqualTo(1);
                $this->integer((int)$nodes->item(0)->getAttribute('value'))->isEqualTo((int)$value);
                $input[$field] = [$nodes->item(0)->getAttribute('value')];
            }
            $nodes = $xpath->query('//form//input[@name="items_id[Computer][' . $computer . ']"]');
            $this->integer($nodes->length)->isEqualTo(1);
            $input['items_id'] = ['Computer' => [$computer => $nodes->item(0)->getAttribute('value')]];
            $this->integer($xpath->query('//input[@type="file"]')->length)->isEqualTo(0);
            if (!$helpdesk) {
                $nodes = $xpath->query('//input[@name="_users_id_assign[]"]');
                $this->integer($nodes->length)->isEqualTo(1);
                $this->integer((int)$nodes->item(0)->getAttribute('value'))->isEqualTo($tech);
                $input['_users_id_assign'] = [$tech];
                $this->integer($xpath->query('//section[@data-actor-role="assign"]/parent::*[contains(@class, "d-none")]')->length)->isEqualTo(1);
            }
        }
        $class = '\\' . $type;
        $item = new $class();
        $id = $item->add($input);
        $this->integer((int)$id)->isGreaterThan(0);
        $foreign_key = strtolower($type) . 's_id';
        $this->integer(countElementsInTable($type === 'Ticket' ? 'glpi_items_tickets' : 'glpi_items_problems', [$foreign_key => $id, 'items_id' => $computer]))->isEqualTo(1);
        $this->integer(countElementsInTable('glpi_documents_items', ['itemtype' => $type, 'items_id' => $id, 'documents_id' => $document]))->isEqualTo(1);
        $this->integer(countElementsInTable('glpi_' . strtolower($type) . 'tasks', [$foreign_key => $id]))->isEqualTo(1);
    }

    /**
     * @dataProvider objectTypeProvider
     */
    public function testCategorySwitchRecognizesPostedActorAndItemArrays(string $type)
    {
        $this->login('itsm', 'itsm');
        $first_user = (int)getItemByTypeName('User', 'tech', true);
        $second_user = (int)\Session::getLoginUserID();
        $computer = (int)getItemByTypeName('Computer', '_test_pc01', true);
        [$first] = $this->createTemplate($type, ['_users_id_assign', 'items_id'], [], ['_users_id_assign' => $first_user, 'items_id' => 'Computer_' . $computer]);
        [$second, $category] = $this->createTemplate($type, ['_users_id_assign', 'items_id'], [], ['_users_id_assign' => $second_user]);
        $key = '_' . strtolower($type) . 'template';
        $options = ['itilcategories_id' => $category, '_users_id_requester' => [\Session::getLoginUserID()], '_users_id_assign' => [$first_user],
            'items_id' => ['Computer' => [$computer => $computer]], $key => $first->getID(),
            '_predefined_fields' => \Toolbox::prepareArrayForInput(['_users_id_assign' => $first_user, 'items_id' => ['Computer' => [$computer]]])];
        $xpath = $this->getXPath($this->renderForm($type, $options, -1));
        $nodes = $xpath->query('//form//input[@name="_users_id_assign[]"]');
        $this->integer($nodes->length)->isEqualTo(1);
        $this->integer((int)$nodes->item(0)->getAttribute('value'))->isEqualTo($second_user);
        $this->integer($xpath->query('//form//input[starts-with(@name, "items_id[")]')->length)->isEqualTo(0);
    }

    public function testProblemPromotionUsesSourceCategoryAndPreservesReloadEdits()
    {
        $this->login('itsm', 'itsm');
        [$template, $category] = $this->createTemplate('Problem', [], [], ['name' => 'Problem template title', 'urgency' => 4]);
        $ticket_id = (new \Ticket())->add(['name' => 'Source ticket', 'content' => 'Source description', 'entities_id' => 0, 'itilcategories_id' => $category]);
        $xpath = $this->getXPath($this->renderForm('Problem', ['tickets_id' => $ticket_id], -1));
        $this->string($xpath->query('//input[@name="name"]')->item(0)->getAttribute('value'))->isEqualTo('Problem template title');
        $this->string(trim($xpath->query('//textarea[@name="content"]')->item(0)->textContent))->isEqualTo('Source description');
        $this->string($xpath->query('//input[@name="_problemtemplate"]')->item(0)->getAttribute('value'))->isEqualTo((string)$template->getID());
        $xpath = $this->getXPath($this->renderForm('Problem', ['_tickets_id' => $ticket_id, 'itilcategories_id' => $category,
            'name' => 'Edited promoted problem', 'content' => 'Edited promoted description', 'urgency' => 4,
            '_predefined_fields' => \Toolbox::prepareArrayForInput(['name' => 'Problem template title', 'urgency' => 4])], -1));
        $this->string($xpath->query('//input[@name="name"]')->item(0)->getAttribute('value'))->isEqualTo('Edited promoted problem');
        $this->string(trim($xpath->query('//textarea[@name="content"]')->item(0)->textContent))->isEqualTo('Edited promoted description');
    }

    public function testTicketMandatoryApprovalCanBeSelected()
    {
        $this->login('itsm', 'itsm');
        [$template, $category] = $this->createTemplate('Ticket', [], ['_add_validation']);
        $xpath = $this->getXPath($this->renderForm('Ticket', ['itilcategories_id' => $category], -1));
        $this->integer($xpath->query('//form//select[@name="validatortype"]')->length)->isEqualTo(1);
        $this->integer($xpath->query('//form//input[@name="_add_validation"]')->length)->isEqualTo(1);
        $ticket = new \Ticket();
        $input = ['name' => 'Approval required', 'content' => 'Approval test', 'entities_id' => 0, 'itilcategories_id' => $category,
            '_tickettemplate' => $template->getID(), '_add_validation' => 0];
        $this->boolean($ticket->add($input))->isFalse();
        $this->hasSessionMessages(ERROR, ['Mandatory fields are not filled. Please correct: Approval request']);
        $tech = (int)getItemByTypeName('User', 'tech', true);
        $id = $ticket->add($input + ['users_id_validate' => [$tech]]);
        $this->integer((int)$id)->isGreaterThan(0);
        $this->integer(countElementsInTable('glpi_ticketvalidations', ['tickets_id' => $id, 'users_id_validate' => $tech]))->isEqualTo(1);
    }

    public function testHelpdeskMandatoryFieldsAreOptionalUnlessConfigured()
    {
        $this->login('itsm', 'itsm');
        [$template, $category] = $this->createTemplate('Ticket', [], ['name']);
        $xpath = $this->getXPath($this->renderForm('Ticket', ['itilcategories_id' => $category], 0, true));
        $this->integer($xpath->query('//input[@name="name" and @required]')->length)->isEqualTo(1);
        $this->integer($xpath->query('//textarea[@name="content" and @required]')->length)->isEqualTo(0);
        $this->integer($xpath->query('//label[contains(., "Title")]//span')->length)->isEqualTo(1);
    }

    /**
     * @dataProvider objectTypeProvider
     */
    public function testConfigurationTablesUseTwigAndAssociationIds(string $type)
    {
        $this->login('itsm', 'itsm');
        [$template] = $this->createTemplate($type, ['urgency'], ['name'], ['content' => 'Predefined content']);
        foreach (['Hidden', 'Mandatory', 'Predefined'] as $kind) {
            $class = '\\' . $type . 'Template' . $kind . 'Field';
            ob_start();
            $class::showForITILTemplate($template);
            $html = ob_get_clean();
            preg_match('/<script type="application\/json"[^>]*>(.*?)<\/script>/s', $html, $match);
            $config = json_decode($match[1], true, 512, JSON_THROW_ON_ERROR);
            $this->string($config['selection']['type'])->isEqualTo('massive-action');
            $this->array($config['dataSource']['rows'])->hasSize(1);
            $relation = new $class();
            $relation->getFromDBByCrit([strtolower($type) . 'templates_id' => $template->getID()]);
            $id = $relation->getID();
            $this->string($config['selection']['values'][$id])->isEqualTo('item[' . ltrim($class, '\\') . '][' . $id . ']');
            $this->string($html)->notContains('tab_cadre');
            $_SESSION['glpiactiveprofile']['itiltemplate'] = READ;
            ob_start();
            $class::showForITILTemplate($template);
            $readonly = ob_get_clean();
            preg_match('/<script type="application\/json"[^>]*>(.*?)<\/script>/s', $readonly, $match);
            $config = json_decode($match[1], true, 512, JSON_THROW_ON_ERROR);
            $this->string($config['selection']['type'])->isEqualTo('none');
            $this->string($readonly)->notContains('name="add"');
            $_SESSION['glpiactiveprofile']['itiltemplate'] = ALLSTANDARDRIGHT;
        }
    }
}
