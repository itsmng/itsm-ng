<?php

namespace tests\units;

use DbTestCase;

class RuleImportComputer extends DbTestCase
{
    public function testRuleActionValues()
    {
        $this->array(\RuleImportComputer::getRuleActionValues())->isIdenticalTo([
           \RuleImportComputer::RULE_ACTION_LINK_OR_IMPORT    => 'Link if possible',
           \RuleImportComputer::RULE_ACTION_LINK_OR_NO_IMPORT => 'Link if possible, otherwise imports declined',
        ]);
    }

    public function testProcessAllRulesMatchesImportRule()
    {
        $this->login();

        $rule = new \Rule();
        $criteria = new \RuleCriteria();
        $action = new \RuleAction();
        $collection = new \RuleImportComputerCollection();

        $name = 'import-computer-' . $this->getUniqueString();
        $rules_id = (int)$rule->add([
           'name'        => 'Import computer rule ' . $name,
           'is_active'   => 1,
           'entities_id' => 0,
           'sub_type'    => 'RuleImportComputer',
           'match'       => \Rule::AND_MATCHING,
           'condition'   => 0,
           'description' => '',
        ]);
        $this->integer($rules_id)->isGreaterThan(0);

        $criteria_id = (int)$criteria->add([
           'rules_id'  => $rules_id,
           'criteria'  => 'name',
           'condition' => \Rule::PATTERN_IS,
           'pattern'   => $name,
        ]);
        $this->integer($criteria_id)->isGreaterThan(0);

        $action_id = (int)$action->add([
           'rules_id'    => $rules_id,
           'action_type' => 'assign',
           'field'       => '_ignore_import',
           'value'       => '1',
        ]);
        $this->integer($action_id)->isGreaterThan(0);

        $collection->RuleList = new \stdClass();
        $collection->RuleList->load = true;

        $result = $collection->processAllRules([
           'name'       => $name,
           'entities_id' => 0,
        ]);
        $this->array($result)->hasKey('_ruleid');
        $this->string($result['_ruleid'])->isIdenticalTo((string)$rules_id);
        if (isset($result['_ignore_import'])) {
            $this->string($result['_ignore_import'])->isIdenticalTo('1');
        }
    }
}
