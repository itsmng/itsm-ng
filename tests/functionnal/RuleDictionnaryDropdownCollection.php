<?php

namespace tests\units;

use DbTestCase;

class RuleDictionnaryDropdownCollection extends DbTestCase
{
    protected function nonSoftwareCollectionProvider()
    {
        return [
           ['RuleDictionnaryPrinterCollection', 'RuleDictionnaryPrinter', ['manufacturer' => 'Acme', 'comment' => 'Printer']],
           ['RuleDictionnaryOperatingSystemCollection', 'RuleDictionnaryOperatingSystem', []],
           ['RuleDictionnaryNetworkEquipmentModelCollection', 'RuleDictionnaryNetworkEquipmentModel', ['manufacturer' => 'Acme']],
        ];
    }

    /**
     * @dataProvider nonSoftwareCollectionProvider
     */
    public function testCollectionAppliesAssignRule($collection_class, $rule_type, array $extra_input)
    {
        $this->login();

        $rule = new \Rule();
        $criteria = new \RuleCriteria();
        $action = new \RuleAction();
        $collection_fqcn = '\\' . $collection_class;
        $collection = new $collection_fqcn();

        $name = 'dictionnary-' . $this->getUniqueString();
        $target_name = 'mapped-' . $name;
        $rules_id = (int)$rule->add([
           'name'        => 'Dictionnary rule ' . $name,
           'is_active'   => 1,
           'entities_id' => 0,
           'sub_type'    => $rule_type,
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
           'field'       => 'name',
           'value'       => $target_name,
        ]);
        $this->integer($action_id)->isGreaterThan(0);

        $collection->RuleList = new \stdClass();
        $collection->RuleList->load = true;

        $input = array_merge(['name' => $name], $extra_input);
        $result = $collection->processAllRules($input);
        $this->array($result)->isIdenticalTo([
           'name'    => $target_name,
           '_ruleid' => (string)$rules_id,
        ]);
    }
}
