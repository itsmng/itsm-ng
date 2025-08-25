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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Profile class
 *
 * @since 0.85
**/
class ProfileRight extends CommonDBChild
{
    // From CommonDBChild:
    public static $itemtype = 'Profile';
    public static $items_id = 'profiles_id'; // Field name
    public $dohistory       = true;


    /**
     * Get possible rights
     *
     * @return array
     */
    public static function getAllPossibleRights()
    {
        global $GLPI_CACHE;

        $rights = [];

        if (
            !$GLPI_CACHE->has('all_possible_rights')
            || count($GLPI_CACHE->get('all_possible_rights')) == 0
        ) {
            $request = self::getAdapter()->request([
               'SELECT'          => 'name',
               'DISTINCT'        => true,
               'FROM'            => self::getTable()
            ]);
            while ($right = $request->fetchAssociative()) {
                // By default, all rights are NULL ...
                $rights[$right['name']] = '';
            }
            $GLPI_CACHE->set('all_possible_rights', $rights);
        } else {
            $rights = $GLPI_CACHE->get('all_possible_rights');
        }
        return $rights;
    }


    public static function cleanAllPossibleRights()
    {
        global $GLPI_CACHE;
        $GLPI_CACHE->delete('all_possible_rights');
    }

    /**
     * @param $profiles_id
     * @param $rights         array
    **/
    public static function getProfileRights($profiles_id, array $rights = [])
    {
        if (!version_compare(Config::getCurrentDBVersion(), '0.84', '>=')) {
            //table does not exists.
            return [];
        }

        $query = [
           'FROM'   => 'glpi_profilerights',
           'WHERE'  => ['profiles_id' => $profiles_id]
        ];
        if (count($rights) > 0) {
            $query['WHERE']['name'] = $rights;
        }
        $request = self::getAdapter()->request($query);
        $rights = [];
        while ($right = $request->fetchAssociative()) {
            $rights[$right['name']] = $right['rights'];
        }
        return $rights;
    }


    /**
     * @param $rights   array
     *
     * @return boolean
    **/
    public static function addProfileRights(array $rights)
    {
        global $DB, $GLPI_CACHE;

        $ok = true;
        $GLPI_CACHE->set('all_possible_rights', []);

        $request = self::getAdapter()->request([
            'SELECT'   => ['id'],
            'FROM'     => Profile::getTable()
        ]);

        while ($profile = $request->fetchAssociative()) {
            $profiles_id = $profile['id'];
            foreach ($rights as $name) {
                $profileRight = new self();

                $profileRight->fields = [
                    'profiles_id'  => $profiles_id,
                    'name'         => $name
                ];

                if (!$profileRight->addToDB()) {
                    $ok = false;
                }
            }
        }
        return $ok;
    }


    /**
     * @param $rights   array
     *
     * @return boolean
    **/
    public static function deleteProfileRights(array $rights)
    {
        global $GLPI_CACHE;

        $GLPI_CACHE->set('all_possible_rights', []);
        $ok = true;
        foreach ($rights as $name) {
            $adapter = self::getAdapter();
            $items = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => self::getTable(),
                'WHERE'  => [
                    'name' => $name
                ]
            ]);

            foreach ($items->fetchAllAssociative() as $data) {
                $profileRight = new self();
                if ($profileRight->getFromDB($data['id'])) {
                    if (!$profileRight->deleteFromDB()) {
                        $ok = false;
                    }
                }
            }
        }
        return $ok;
    }


    /**
     * @param $right
     * @param $value
     * @param $condition
     *
     * @return boolean
    **/
    public static function updateProfileRightAsOtherRight($right, $value, $condition)
    {
        $profiles = [];
        $ok       = true;
        $request = Profile::getAdapter()->request([
            'SELECT' => ['profiles_id'],
            'FROM'   => 'glpi_profilerights',
            'WHERE'  => $condition
        ]);

        $results = $request->fetchAllAssociative();
        $profiles = array_column($results, 'profiles_id');

        if (count($profiles)) {
            foreach ($profiles as $profiles_id) {
                $profileRight = new self();
                if ($profileRight->getFromDBByCrit([
                    'profiles_id' => $profiles_id,
                    'name'        => $right
                ])) {
                    $new_rights = $profileRight->fields['rights'] | (int)$value;

                    if (!$profileRight->update([
                        'id'     => $profileRight->getID(),
                        'rights' => $new_rights
                    ])) {
                        $ok = false;
                    }
                }
            }
        }
        return $ok;
    }


    /**
     * @since 0.85
     *
     * @param $newright      string   new right name
     * @param $initialright  string   right name to check
     * @param $condition              (default '')
     *
     * @return boolean
    **/
    public static function updateProfileRightsAsOtherRights($newright, $initialright, array $condition = [])
    {
        $profiles = [];
        $ok       = true;

        $criteria = [
           'FROM'   => self::getTable(),
           'WHERE'  => ['name' => $initialright] + $condition
        ];
        $request = self::getAdapter()->request($criteria);

        while ($data = $request->fetchAssociative()) {
            $profiles[$data['profiles_id']] = $data['rights'];
        }
        if (count($profiles)) {
            foreach ($profiles as $profiles_id => $rights_value) {
                $profileRight = new self();
                if ($profileRight->getFromDBByCrit([
                    'profiles_id'  => $profiles_id,
                    'name'         => $newright
                ])) {
                    if (!$profileRight->update([
                        'id'     => $profileRight->getID(),
                        'rights' => $rights_value
                    ])) {
                        $ok = false;
                    }
                }
            }
        }

        return $ok;
    }

    /**
     * @param $profiles_id
    **/
    public static function fillProfileRights($profiles_id)
    {
        global $DB;

        $subq = new \QuerySubQuery([
           'FROM'   => 'glpi_profilerights AS CURRENT',
           'WHERE'  => [
              'CURRENT.profiles_id'   => $profiles_id,
              'CURRENT.NAME'          => new \QueryExpression('POSSIBLE.NAME')
           ]
        ]);

        $expr = 'NOT EXISTS ' . $subq->getQuery();
        $request = self::getAdapter()->request([
           'SELECT'          => 'POSSIBLE.name AS NAME',
           'DISTINCT'        => true,
           'FROM'            => 'glpi_profilerights AS POSSIBLE',
           'WHERE'           => [
              new \QueryExpression($expr)
           ]
        ]);
        $results = $request->fetchAllAssociative();
        if (count($results) === 0) {
            return;
        }

        $query = $DB->buildInsert(
            self::getTable(),
            [
              'profiles_id' => new QueryParam(),
              'name'        => new QueryParam(),
            ]
        );
        $stmt = $DB->prepare($query);
        foreach ($results as $right) {
            $stmt->bind_param('ss', $profiles_id, $right['NAME']);
            $stmt->execute();
        }
    }


    /**
     * Update the rights of a profile (static since 0.90.1)
     *
     * @param $profiles_id
     * @param $rights         array
     */
    public static function updateProfileRights($profiles_id, array $rights = [])
    {
        $me = new self();
        foreach ($rights as $name => $right) {
            if (isset($right)) {
                if (
                    $me->getFromDBByCrit(['id'   => $profiles_id,
                                          'name'          => $name])
                ) {
                    $input = ['id'          => $me->getID(),
                              'rights'      => $right];
                    $me->update($input);
                } else {
                    $input = ['id' => $profiles_id,
                              'name'        => $name,
                              'rights'      => $right];
                    $me->add($input);
                }
            }
        }

        // Don't forget to complete the profile rights ...
        self::fillProfileRights($profiles_id);
    }


    /**
     * To avoid log out and login when rights change (very useful in debug mode)
     *
     * @see CommonDBChild::post_updateItem()
    **/
    public function post_updateItem($history = 1)
    {

        // update current profile
        if (
            isset($_SESSION['glpiactiveprofile']['id'])
            && $_SESSION['glpiactiveprofile']['id'] == $this->fields['profiles_id']
            && (!isset($_SESSION['glpiactiveprofile'][$this->fields['name']])
                || $_SESSION['glpiactiveprofile'][$this->fields['name']] != $this->fields['rights'])
        ) {
            $_SESSION['glpiactiveprofile'][$this->fields['name']] = $this->fields['rights'];
            unset($_SESSION['glpimenu']);
        }
    }


    /**
     * @since 085
     *
     * @param $field
     * @param $values
     * @param $options   array
    **/
    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        $itemtype = $options['searchopt']['rightclass'];
        $item     = new $itemtype();
        $rights   = '';
        $prem     = true;
        foreach ($item->getRights() as $val => $name) {
            if ((is_numeric($values['rights']) && $values['rights']) & $val) {
                if ($prem) {
                    $prem = false;
                } else {
                    $rights .= ", ";
                }
                if (is_array($name)) {
                    $rights .= $name['long'];
                } else {
                    $rights .= $name;
                }
            }
        }
        return ($rights ? $rights : __('None'));
    }


    /**
     * @since 0.85
     *
     * @see CommonDBTM::getLogTypeID()
    **/
    public function getLogTypeID()
    {
        return ['Profile', $this->fields['profiles_id']];
    }
}
