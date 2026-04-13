<?php

namespace tests\units;

class DisplayPreference extends \DbTestCase
{
    private function makeItemtype(string $prefix): string
    {
        return $prefix . '_' . substr($this->getUniqueString(), -16);
    }

    public function testGlobalPreferencesAreStoredWithNullUsersIdAndUsedAsFallback()
    {
        global $DB;

        $this->login();

        $itemtype = $this->makeItemtype('dp_null');
        $users_id = \Session::getLoginUserID();
        $displaypreference = new \DisplayPreference();

        $this->integer((int) $displaypreference->add([
            'itemtype' => $itemtype,
            'users_id' => null,
            'num'      => 11,
        ]))->isGreaterThan(0);
        $this->integer((int) $displaypreference->add([
            'itemtype' => $itemtype,
            'users_id' => null,
            'num'      => 22,
        ]))->isGreaterThan(0);

        $row = $DB->request([
            'FROM'  => \DisplayPreference::getTable(),
            'WHERE' => [
                'itemtype' => $itemtype,
                'num'      => 11,
                'users_id' => null,
            ],
        ])->next();
        $this->array($row)->hasKeys(['itemtype', 'num', 'users_id']);
        $this->variable($row['users_id'])->isNull();

        $this->array(\DisplayPreference::getForTypeUser($itemtype, $users_id))->isIdenticalTo([11, 22]);
    }

    public function testActivatePersoClonesNullGlobalPreferences()
    {
        global $DB;

        $this->login();

        $itemtype = $this->makeItemtype('dp_perso');
        $users_id = \Session::getLoginUserID();
        $displaypreference = new \DisplayPreference();

        $this->integer((int) $displaypreference->add([
            'itemtype' => $itemtype,
            'users_id' => null,
            'num'      => 31,
        ]))->isGreaterThan(0);
        $this->integer((int) $displaypreference->add([
            'itemtype' => $itemtype,
            'users_id' => null,
            'num'      => 32,
        ]))->isGreaterThan(0);

        $displaypreference->activatePerso([
            'itemtype' => $itemtype,
            'users_id' => $users_id,
        ]);

        $personal_rows = $DB->request([
            'FROM'  => \DisplayPreference::getTable(),
            'WHERE' => [
                'itemtype' => $itemtype,
                'users_id' => $users_id,
            ],
            'ORDER' => 'rank',
        ]);

        $this->integer(count($personal_rows))->isIdenticalTo(2);
        $this->array(\DisplayPreference::getForTypeUser($itemtype, $users_id))->isIdenticalTo([31, 32]);
    }

    public function testDuplicateGlobalPreferenceIsRejected()
    {
        global $DB;

        $this->login();

        $itemtype = $this->makeItemtype('dp_dup');
        $displaypreference = new \DisplayPreference();

        $this->integer((int) $displaypreference->add([
            'itemtype' => $itemtype,
            'users_id' => null,
            'num'      => 41,
        ]))->isGreaterThan(0);

        $this->boolean((bool) $displaypreference->add([
            'itemtype' => $itemtype,
            'users_id' => null,
            'num'      => 41,
        ]))->isFalse();
        $this->hasSessionMessages(ERROR, [__('This display preference already exists.')]);

        $rows = $DB->request([
            'FROM'  => \DisplayPreference::getTable(),
            'WHERE' => [
                'itemtype' => $itemtype,
                'num'      => 41,
            ],
        ]);

        $this->integer(count($rows))->isIdenticalTo(1);
    }
}
