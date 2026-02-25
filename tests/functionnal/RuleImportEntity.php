<?php

namespace tests\units;

use DbTestCase;

class RuleImportEntity extends DbTestCase
{
    public function testExecuteActionsAssignsEntity()
    {
        $entities_id = (int)getItemByTypeName('Entity', '_test_child_1', true);
        $rule = new \RuleImportEntity();
        $action = new \RuleAction();
        $action->fields = [
           'action_type' => 'assign',
           'field'       => 'entities_id',
           'value'       => $entities_id,
        ];
        $rule->actions = [$action];

        $result = $rule->executeActions([], []);
        $this->array($result)->isIdenticalTo([
           'entities_id' => $entities_id,
        ]);
    }
}
