<?php

namespace tests\units;

use DbTestCase;

class RuleDictionnaryDropdownCollection extends DbTestCase
{
    public function testManufacturerPagination()
    {
        $this->login();
        $collection = new \RuleDictionnaryManufacturerCollection();
        $before = $collection->getPaginatedRules(['limit' => 15]);
        $created = [];
        for ($i = 0; $i < 31; $i++) {
            $rule = new \RuleDictionnaryManufacturer();
            $id = (int) $rule->add([
                'name' => 'pagination-' . $this->getUniqueString(),
                'description' => 'Pagination regression',
                'is_active' => $i % 2,
                'match' => \Rule::AND_MATCHING,
            ]);
            $this->integer($id)->isGreaterThan(0);
            $created[] = sprintf('item[RuleDictionnaryManufacturer][%s]', $id);
        }

        $seen = [];
        $total = $before['total'] + 31;
        for ($offset = 0; $offset < $total; $offset += 15) {
            $page = $collection->getPaginatedRules(['offset' => $offset, 'limit' => 15]);
            $this->integer($page['total'])->isEqualTo($total);
            $this->array($page['rows'])->hasSize(min(15, $total - $offset));
            foreach ($page['rows'] as $row) {
                $seen[] = $row['value'];
            }
        }
        $this->array(array_unique($seen))->hasSize($total);
        $this->array(array_diff($created, $seen))->isEmpty();
        $this->array($collection->getPaginatedRules(['offset' => $total])['rows'])->isEmpty();

        $ascending = $collection->getPaginatedRules(['sort' => '0', 'order' => 'asc', 'limit' => 1]);
        $descending = $collection->getPaginatedRules([
            'sort' => '0', 'order' => 'desc', 'offset' => $total - 1, 'limit' => 1,
        ]);
        $this->string($ascending['rows'][0]['value'])->isEqualTo($descending['rows'][0]['value']);
        $invalid_sort = $collection->getPaginatedRules(['sort' => 'invalid', 'limit' => 15]);
        $default_sort = $collection->getPaginatedRules(['limit' => 15]);
        $this->array(array_column($invalid_sort['rows'], 'value'))
            ->isEqualTo(array_column($default_sort['rows'], 'value'));
    }

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
