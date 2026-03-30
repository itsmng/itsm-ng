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

use CommonDBTM;
use DbTestCase;

/* Test for inc/search.class.php */

class Search extends DbTestCase
{
    private function createSearchEntityContext(): int
    {
        $this->login();
        $this->setEntity('_test_root_entity', true);

        return getItemByTypeName('Entity', '_test_root_entity', true);
    }

    private function searchRows(array $data): array
    {
        return $data['data']['rows'] ?? [];
    }

    private function getSearchFieldAliases(string $itemtype, int $field): array
    {
        return [
            'value' => 'ITEM_' . $itemtype . '_' . $field,
            'id' => 'ITEM_' . $itemtype . '_' . $field . '_id',
        ];
    }

    private function extractRowIds(array $data, string $itemtype, int $field = 1): array
    {
        $aliases = $this->getSearchFieldAliases($itemtype, $field);
        $parsed_key = $itemtype . '_' . $field;
        $ids = [];

        foreach ($this->searchRows($data) as $row) {
            if (isset($row[$parsed_key][0]['id']) && is_numeric($row[$parsed_key][0]['id'])) {
                $ids[] = (int) $row[$parsed_key][0]['id'];
                continue;
            }

            if (($data['itemtype'] ?? null) === $itemtype && isset($row['id']) && is_numeric($row['id'])) {
                $ids[] = (int) $row['id'];
                continue;
            }

            $raw = $row['raw'] ?? [];
            if (!array_key_exists($aliases['id'], $raw)) {
                continue;
            }
            $ids[] = (int) $raw[$aliases['id']];
        }

        sort($ids);

        return $ids;
    }

    private function extractRowLabels(array $data, string $itemtype, int $field = 1): array
    {
        $aliases = $this->getSearchFieldAliases($itemtype, $field);
        $parsed_key = $itemtype . '_' . $field;
        $labels = [];

        foreach ($this->searchRows($data) as $row) {
            if (isset($row[$parsed_key][0]['name']) && is_string($row[$parsed_key][0]['name']) && $row[$parsed_key][0]['name'] !== '') {
                $labels[] = $row[$parsed_key][0]['name'];
                continue;
            }

            if (isset($row[$parsed_key]['displayname']) && is_string($row[$parsed_key]['displayname']) && $row[$parsed_key]['displayname'] !== '') {
                $labels[] = $row[$parsed_key]['displayname'];
                continue;
            }

            $raw = $row['raw'] ?? [];
            if (!array_key_exists($aliases['value'], $raw)) {
                continue;
            }
            $labels[] = (string) $raw[$aliases['value']];
        }

        sort($labels);

        return $labels;
    }

    private function extractAllAssetsLabels(array $data): array
    {
        $labels = [];

        foreach ($this->searchRows($data) as $row) {
            $type = $row['TYPE'] ?? null;
            if (is_string($type)) {
                $parsed_key = $type . '_1';
                if (isset($row[$parsed_key][0]['name']) && is_string($row[$parsed_key][0]['name']) && $row[$parsed_key][0]['name'] !== '') {
                    $labels[] = $row[$parsed_key][0]['name'];
                    continue;
                }
                if (isset($row[$parsed_key]['displayname']) && is_string($row[$parsed_key]['displayname']) && $row[$parsed_key]['displayname'] !== '') {
                    $labels[] = $row[$parsed_key]['displayname'];
                    continue;
                }
            }

            $raw = $row['raw'] ?? [];
            foreach ($raw as $key => $value) {
                if (preg_match('/^ITEM_.+_1$/', $key) === 1 && !str_ends_with($key, '_id') && is_string($value) && $value !== '') {
                    $labels[] = $value;
                    break;
                }
            }
        }

        sort($labels);

        return $labels;
    }

    private function assertSearchReturnedIds(array $data, array $expectedIds, string $itemtype, int $field = 1): void
    {
        sort($expectedIds);
        $this->array($this->extractRowIds($data, $itemtype, $field))->isIdenticalTo($expectedIds);
    }

    private function assertSearchReturnedLabels(array $data, array $expectedLabels, string $itemtype, int $field = 1): void
    {
        sort($expectedLabels);
        $this->array($this->extractRowLabels($data, $itemtype, $field))->isIdenticalTo($expectedLabels);
    }

    private function assertSearchContainsText(array $data, string $expectedText): void
    {
        foreach ($this->searchRows($data) as $row) {
            $encoded = json_encode($row);
            if (is_string($encoded) && str_contains($encoded, $expectedText)) {
                $this->boolean(true)->isTrue();
                return;
            }
        }

        $this->array([])->contains($expectedText);
    }

    private function doSearch($itemtype, $params, array $forcedisplay = [])
    {
        global $DEBUG_SQL;

        // check param itemtype exists (to avoid search errors)
        if ($itemtype !== 'AllAssets') {
            $this->class($itemtype)->isSubClassof('CommonDBTM');
        }

        // login to glpi if needed
        if (!isset($_SESSION['glpiname'])) {
            $this->login();
        }

        // force session in debug mode (to store & retrieve sql errors)
        $glpi_use_mode = $_SESSION['glpi_use_mode'];
        $_SESSION['glpi_use_mode'] = \Session::DEBUG_MODE;

        // don't compute last request from session
        $params['reset'] = 'reset';

        // do search
        $params = \Search::manageParams($itemtype, $params);
        $data = \Search::getDatas($itemtype, $params, $forcedisplay);

        // append existing errors to returned data
        $data['last_errors'] = [];
        if (isset($DEBUG_SQL['errors'])) {
            $data['last_errors'] = implode(', ', $DEBUG_SQL['errors']);
            unset($DEBUG_SQL['errors']);
        }

        // restore glpi mode to previous
        $_SESSION['glpi_use_mode'] = $glpi_use_mode;

        // do not store this search from session
        \Search::resetSaveSearch();

        $this->checkSearchResult($data);

        return $data;
    }

    public function testMetaComputerOS()
    {
        // Create a small OS/computer graph with one matching relation.
        $entities_id = $this->createSearchEntityContext();
        $suffix = $this->getUniqueString();

        $matching_os = $this->createItem(\OperatingSystem::class, [
            'name' => 'windows-search-' . $suffix,
        ]);
        $other_os = $this->createItem(\OperatingSystem::class, [
            'name' => 'linux-search-' . $suffix,
        ]);
        $matching_computer = $this->createItem(\Computer::class, [
            'name' => 'meta-os-match-' . $suffix,
            'entities_id' => $entities_id,
        ]);
        $other_computer = $this->createItem(\Computer::class, [
            'name' => 'meta-os-other-' . $suffix,
            'entities_id' => $entities_id,
        ]);

        $this->createItem(\Item_OperatingSystem::class, [
            'itemtype' => \Computer::class,
            'items_id' => $matching_computer->getID(),
            'operatingsystems_id' => $matching_os->getID(),
        ]);
        $this->createItem(\Item_OperatingSystem::class, [
            'itemtype' => \Computer::class,
            'items_id' => $other_computer->getID(),
            'operatingsystems_id' => $other_os->getID(),
        ]);

        // Search computers through an operating system meta criterion.
        $search_params = [
            'is_deleted' => 0,
            'start' => 0,
            'criteria' => [
                0 => [
                    'field' => 1,
                    'searchtype' => 'contains',
                    'value' => 'meta-os-'
                ]
            ],
            'metacriteria' => [
                0 => [
                    'link' => 'AND',
                    'itemtype' => 'OperatingSystem',
                    'field' => 1, //name
                    'searchtype' => 'contains',
                    'value' => 'windows-search-' . $suffix
                ]
            ]
        ];

        $data = $this->doSearch('Computer', $search_params);

        // Only the computer linked to the matching operating system should be returned.
        $this->assertSearchReturnedIds($data, [$matching_computer->getID()], 'Computer');
    }


    public function testMetaComputerSoftwareLicense()
    {
        // Create one software/license chain linked to a single computer.
        $entities_id = $this->createSearchEntityContext();
        $suffix = $this->getUniqueString();

        $software = $this->createItem(\Software::class, [
            'name' => 'firefox-search-' . $suffix,
            'entities_id' => $entities_id,
            'is_recursive' => 1,
        ]);
        $license = $this->createItem(\SoftwareLicense::class, [
            'name' => 'license-search-' . $suffix,
            'completename' => 'license-search-' . $suffix,
            'softwares_id' => $software->getID(),
            'entities_id' => $entities_id,
            'is_recursive' => 1,
            'number' => 5,
        ]);
        $software_version = $this->createItem(\SoftwareVersion::class, [
            'name' => 'firefox-version-' . $suffix,
            'entities_id' => $entities_id,
            'is_recursive' => 1,
            'softwares_id' => $software->getID(),
        ]);
        $matching_computer = $this->createItem(\Computer::class, [
            'name' => 'meta-license-match-' . $suffix,
            'entities_id' => $entities_id,
        ]);
        $other_computer = $this->createItem(\Computer::class, [
            'name' => 'meta-license-other-' . $suffix,
            'entities_id' => $entities_id,
        ]);

        $this->createItem(\Item_SoftwareLicense::class, [
            'softwarelicenses_id' => $license->getID(),
            'items_id' => $matching_computer->getID(),
            'itemtype' => \Computer::class,
        ]);
        $this->createItem(\Item_SoftwareVersion::class, [
            'items_id' => $matching_computer->getID(),
            'itemtype' => \Computer::class,
            'softwareversions_id' => $software_version->getID(),
        ]);

        // Search computers through software-related meta criteria.
        $search_params = [
            'is_deleted' => 0,
            'start' => 0,
            'criteria' => [
                0 => [
                    'field' => 1,
                    'searchtype' => 'contains',
                    'value' => 'meta-license-'
                ]
            ],
            'metacriteria' => [
                0 => [
                    'link' => 'AND',
                    'itemtype' => 'Software',
                    'field' => 160,
                    'searchtype' => 'contains',
                    'value' => 'license-search-' . $suffix
                ]
            ]
        ];

        $data = $this->doSearch('Computer', $search_params);

        // Only the computer linked to the matching software/license chain should match.
        $this->integer($data['data']['totalcount'])->isIdenticalTo(1);
        $this->assertSearchContainsText($data, 'meta-license-match-' . $suffix);
        $this->array(json_decode(json_encode($data['data']['rows']), true))->notContains('meta-license-other-' . $suffix);
    }

    public function testSoftwareLinkedToAnyComputer()
    {
        // Create one linked software and one unlinked software.
        $entities_id = $this->createSearchEntityContext();
        $suffix = $this->getUniqueString();

        $linked_software = $this->createItem(\Software::class, [
            'name' => 'software-linked-' . $suffix,
            'entities_id' => $entities_id,
            'is_recursive' => 1,
        ]);
        $linked_version = $this->createItem(\SoftwareVersion::class, [
            'name' => 'software-linked-version-' . $suffix,
            'entities_id' => $entities_id,
            'is_recursive' => 1,
            'softwares_id' => $linked_software->getID(),
        ]);
        $linked_computer = $this->createItem(\Computer::class, [
            'name' => 'software-linked-computer-' . $suffix,
            'entities_id' => $entities_id,
        ]);
        $unlinked_software = $this->createItem(\Software::class, [
            'name' => 'software-unlinked-' . $suffix,
            'entities_id' => $entities_id,
            'is_recursive' => 1,
        ]);

        $this->createItem(\Item_SoftwareVersion::class, [
            'items_id' => $linked_computer->getID(),
            'itemtype' => \Computer::class,
            'softwareversions_id' => $linked_version->getID(),
        ]);

        // Search softwares that are linked to at least one computer.
        $search_params = [
            'is_deleted' => 0,
            'start' => 0,
            'criteria' => [
                [
                    'field' => 1,
                    'searchtype' => 'contains',
                    'value' => 'software-linked-' . $suffix,
                ],
            ],
            'metacriteria' => [
                [
                    'link' => 'AND NOT',
                    'itemtype' => 'Computer',
                    'field' => 2,
                    'searchtype' => 'contains',
                    'value' => '^$', // search for "null" id
                ],
            ],
        ];

        $data = $this->doSearch('Software', $search_params);

        // Only the software linked to the created computer should remain in the result set.
        $this->integer($data['data']['totalcount'])->isIdenticalTo(1);
        $this->assertSearchContainsText($data, 'software-linked-' . $suffix);
    }

    public function testMetaComputerUser()
    {
        $search_params = [
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            'criteria' => [
                0 => [
                    'field' => 'view',
                    'searchtype' => 'contains',
                    'value' => ''
                ]
            ],
            // user login
            'metacriteria' => [
                0 => [
                    'link' => 'AND',
                    'itemtype' => 'User',
                    'field' => 1,
                    'searchtype' => 'equals',
                    'value' => 2
                ],
                // user profile
                1 => [
                    'link' => 'AND',
                    'itemtype' => 'User',
                    'field' => 20,
                    'searchtype' => 'equals',
                    'value' => 4
                ],
                // user entity
                2 => [
                    'link' => 'AND',
                    'itemtype' => 'User',
                    'field' => 80,
                    'searchtype' => 'equals',
                    'value' => 0
                ],
                // user profile
                3 => [
                    'link' => 'AND',
                    'itemtype' => 'User',
                    'field' => 13,
                    'searchtype' => 'equals',
                    'value' => 1
                ]
            ]
        ];

        $this->doSearch('Computer', $search_params);
    }

    public function testSubMetaTicketComputer()
    {
        $search_params = [
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            'criteria' => [
                0 => [
                    'field' => 12,
                    'searchtype' => 'equals',
                    'value' => 'notold'
                ],
                1 => [
                    'link' => 'AND',
                    'criteria' => [
                        0 => [
                            'field' => 'view',
                            'searchtype' => 'contains',
                            'value' => 'test1'
                        ],
                        1 => [
                            'link' => 'OR',
                            'field' => 'view',
                            'searchtype' => 'contains',
                            'value' => 'test2'
                        ],
                        2 => [
                            'link' => 'OR',
                            'meta' => true,
                            'itemtype' => 'Computer',
                            'field' => 1,
                            'searchtype' => 'contains',
                            'value' => 'test3'
                        ],
                    ]
                ],
            ],
        ];

        $this->doSearch('Ticket', $search_params);
    }

    public function testFlagMetaComputerUser()
    {
        // Create computers linked to stable seeded users so the user search option is available.
        $entities_id = $this->createSearchEntityContext();
        $suffix = $this->getUniqueString();
        $user_tech_id = getItemByTypeName('User', 'tech', true);
        $other_user_id = getItemByTypeName('User', 'normal', true);
        $matching_computer = $this->createItem(\Computer::class, [
            'name' => 'flag-meta-match-' . $suffix,
            'entities_id' => $entities_id,
            'users_id' => $user_tech_id,
        ]);
        $other_computer = $this->createItem(\Computer::class, [
            'name' => 'flag-meta-other-' . $suffix,
            'entities_id' => $entities_id,
            'users_id' => $other_user_id,
        ]);

        // Search computers using the classic metacriteria syntax.
        $meta_search_params = [
            'reset' => 'reset',
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            'criteria' => [
                0 => [
                    'field' => 1,
                    'searchtype' => 'contains',
                    'value' => 'flag-meta-'
                ]
            ],
            'metacriteria' => [
                0 => [
                    'link' => 'AND',
                    'itemtype' => 'User',
                    'field' => 1,
                    'searchtype' => 'equals',
                    'value' => $user_tech_id,
                ]
            ]
        ];

        $meta_data = $this->doSearch('Computer', $meta_search_params);

        // Sanity-check the local dataset before comparing meta-search variants.
        $baseline_data = $this->doSearch('Computer', [
            'reset' => 'reset',
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            'criteria' => [
                0 => [
                    'field' => 1,
                    'searchtype' => 'contains',
                    'value' => 'flag-meta-'
                ]
            ]
        ]);

        // Search computers using flagged user criteria directly in the criteria tree.
        $flagged_search_params = [
            'reset' => 'reset',
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            'criteria' => [
                0 => [
                    'field' => 1,
                    'searchtype' => 'contains',
                    'value' => 'flag-meta-'
                ],
                1 => [
                    'link' => 'AND',
                    'itemtype' => 'User',
                    'field' => 1,
                    'meta' => 1,
                    'searchtype' => 'equals',
                    'value' => $user_tech_id,
                ]
            ]
        ];

        $data = $this->doSearch('Computer', $flagged_search_params);

        // Flagged meta criteria should behave like the equivalent metacriteria search.
        $this->integer($baseline_data['data']['totalcount'])->isIdenticalTo(2);
        $this->integer($data['data']['totalcount'])->isIdenticalTo($meta_data['data']['totalcount']);
        $this->array($this->extractRowLabels($data, 'Computer'))->isIdenticalTo($this->extractRowLabels($meta_data, 'Computer'));
    }

    public function testNestedAndMetaComputer()
    {
        // Build a small graph that exercises nested criteria, meta criteria, and exclusions.
        $entities_id = $this->createSearchEntityContext();
        $suffix = $this->getUniqueString();
        $primary_user_id = getItemByTypeName('User', TU_USER, true);
        $secondary_user_id = getItemByTypeName('User', 'normal', true);
        $location = $this->createItem(\Location::class, [
            'name' => 'nested-location-' . $suffix,
        ]);
        $software = $this->createItem(\Software::class, [
            'name' => 'nested-software-' . $suffix,
            'entities_id' => $entities_id,
            'is_recursive' => 1,
        ]);
        $software_version = $this->createItem(\SoftwareVersion::class, [
            'name' => 'nested-software-version-' . $suffix,
            'entities_id' => $entities_id,
            'is_recursive' => 1,
            'softwares_id' => $software->getID(),
        ]);
        $budget = $this->createItem(\Budget::class, [
            'name' => 'excluded-budget-' . $suffix,
            'entities_id' => $entities_id,
        ]);
        $printer = $this->createItem(\Printer::class, [
            'name' => 'HP blocked printer ' . $suffix,
            'entities_id' => $entities_id,
        ]);
        $matching_computer = $this->createItem(\Computer::class, [
            'name' => 'nested-test-' . $suffix,
            'entities_id' => $entities_id,
            'locations_id' => $location->getID(),
            'users_id' => $primary_user_id,
            'serial' => 'nested-serial-' . $suffix,
        ]);
        $budget_excluded = $this->createItem(\Computer::class, [
            'name' => 'nested-test-budget-' . $suffix,
            'entities_id' => $entities_id,
            'locations_id' => $location->getID(),
            'users_id' => $primary_user_id,
        ]);
        $printer_excluded = $this->createItem(\Computer::class, [
            'name' => 'nested-test-printer-' . $suffix,
            'entities_id' => $entities_id,
            'locations_id' => $location->getID(),
            'users_id' => $primary_user_id,
        ]);
        $criteria_excluded = $this->createItem(\Computer::class, [
            'name' => 'nested-miss-' . $suffix,
            'entities_id' => $entities_id,
            'locations_id' => $location->getID(),
            'users_id' => $primary_user_id,
        ]);

        foreach ([$matching_computer, $budget_excluded, $printer_excluded] as $computer) {
            $this->createItem(\Item_SoftwareVersion::class, [
                'items_id' => $computer->getID(),
                'itemtype' => \Computer::class,
                'softwareversions_id' => $software_version->getID(),
            ]);
        }

        $this->createItem(\Infocom::class, [
            'itemtype' => \Computer::class,
            'items_id' => $budget_excluded->getID(),
            'budgets_id' => $budget->getID(),
        ]);
        $this->createItem(\Computer_Item::class, [
            'computers_id' => $printer_excluded->getID(),
            'items_id' => $printer->getID(),
            'itemtype' => \Printer::class,
        ]);

        // Search using the full nested criteria set.
        $search_params = [
            'reset' => 'reset',
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            'criteria' => [
                [
                    'link' => 'AND',
                    'field' => 1,
                    'searchtype' => 'contains',
                    'value' => 'nested-test',
                ],
                [
                    'link' => 'AND',
                    'itemtype' => 'Software',
                    'meta' => 1,
                    'field' => 1,
                    'searchtype' => 'equals',
                    'value' => $software->getID(),
                ],
                [
                    'link' => 'OR',
                    'criteria' => [
                        [
                            'link' => 'AND',
                            'field' => 2,
                            'searchtype' => 'contains',
                            'value' => 'nested-serial-',
                        ],
                        [
                            'link' => 'OR',
                            'field' => 2,
                            'searchtype' => 'contains',
                            'value' => 'no-match-' . $suffix,
                        ],
                        [
                            'link' => 'AND',
                            'field' => 3,
                            'searchtype' => 'equals',
                            'value' => $location->getID(),
                        ],
                        [
                            'link' => 'AND',
                            'criteria' => [
                                [
                                    'field' => 70,
                                    'searchtype' => 'equals',
                                    'value' => $primary_user_id,
                                ],
                                [
                                    'link' => 'OR',
                                    'field' => 70,
                                    'searchtype' => 'equals',
                                    'value' => $secondary_user_id,
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'link' => 'AND NOT',
                    'itemtype' => 'Budget',
                    'meta' => 1,
                    'field' => 2,
                    'searchtype' => 'contains',
                    'value' => (string) $budget->getID(),
                ],
                [
                    'link' => 'AND NOT',
                    'itemtype' => 'Printer',
                    'meta' => 1,
                    'field' => 1,
                    'searchtype' => 'contains',
                    'value' => 'HP',
                ]
            ]
        ];

        $data = $this->doSearch('Computer', $search_params);

        // Only the fully matching computer should survive the positive and negative filters.
        $this->assertSearchReturnedIds($data, [$matching_computer->getID()], 'Computer');
        $returned_ids = $this->extractRowIds($data, 'Computer');
        $this->array($returned_ids)->notContains($budget_excluded->getID());
        $this->array($returned_ids)->notContains($printer_excluded->getID());
        $this->array($returned_ids)->notContains($criteria_excluded->getID());
    }

    public function testViewCriterion()
    {
        // Create one computer whose searchable view text is unique.
        $entities_id = $this->createSearchEntityContext();
        $suffix = $this->getUniqueString();
        $matching_computer = $this->createItem(\Computer::class, [
            'name' => 'view-search-' . $suffix,
            'entities_id' => $entities_id,
        ]);
        $other_computer = $this->createItem(\Computer::class, [
            'name' => 'view-control-' . $suffix,
            'entities_id' => $entities_id,
        ]);

        // Search using the generic view criterion.
        $data = $this->doSearch('Computer', [
            'reset' => 'reset',
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            'criteria' => [
                [
                    'link' => 'AND',
                    'field' => 'view',
                    'searchtype' => 'contains',
                    'value' => 'view-search-' . $suffix,
                ],
            ]
        ]);

        // The view criterion should resolve to the matching computer only.
        $this->assertSearchReturnedIds($data, [$matching_computer->getID()], 'Computer');
        $this->array($this->extractRowIds($data, 'Computer'))->notContains($other_computer->getID());
    }

    public function testSearchOnRelationTable()
    {
        // Create one matching change/ticket relation and one control relation.
        $entities_id = $this->createSearchEntityContext();
        $suffix = $this->getUniqueString();
        $change = $this->createItem(\Change::class, [
            'name' => 'relation-change-' . $suffix,
            'content' => 'relation-change-' . $suffix,
            'entities_id' => $entities_id,
        ]);
        $ticket = $this->createItem(\Ticket::class, [
            'name' => 'relation-ticket-' . $suffix,
            'content' => 'relation-ticket-' . $suffix,
            'entities_id' => $entities_id,
            'users_id_recipient' => \Session::getLoginUserID(),
        ]);
        $relation = $this->createItem(\Change_Ticket::class, [
            'changes_id' => $change->getID(),
            'tickets_id' => $ticket->getID(),
        ]);
        $other_change = $this->createItem(\Change::class, [
            'name' => 'relation-change-other-' . $suffix,
            'content' => 'relation-change-other-' . $suffix,
            'entities_id' => $entities_id,
        ]);
        $other_ticket = $this->createItem(\Ticket::class, [
            'name' => 'relation-ticket-other-' . $suffix,
            'content' => 'relation-ticket-other-' . $suffix,
            'entities_id' => $entities_id,
            'users_id_recipient' => \Session::getLoginUserID(),
        ]);
        $this->createItem(\Change_Ticket::class, [
            'changes_id' => $other_change->getID(),
            'tickets_id' => $other_ticket->getID(),
        ]);

        // Search the relation table through the linked change.
        $data = $this->doSearch(\Change_Ticket::class, [
            'reset' => 'reset',
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            'criteria' => [
                [
                    'link' => 'AND',
                    'field' => '3',
                    'searchtype' => 'equals',
                    'value' => (string) $change->getID(),
                ],
            ]
        ]);

        // Only the requested relation row should be returned.
        $this->assertSearchReturnedIds($data, [$relation->getID()], \Change_Ticket::class);
    }

    public function testUser()
    {
        $search_params = [
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            // profile
            'criteria' => [
                0 => [
                    'field' => '20',
                    'searchtype' => 'contains',
                    'value' => 'super-admin'
                ],
                // login
                1 => [
                    'link' => 'AND',
                    'field' => '1',
                    'searchtype' => 'contains',
                    'value' => 'itsm'
                ],
                // entity
                2 => [
                    'link' => 'AND',
                    'field' => '80',
                    'searchtype' => 'equals',
                    'value' => 0
                ],
                // is not not active
                3 => [
                    'link' => 'AND',
                    'field' => '8',
                    'searchtype' => 'notequals',
                    'value' => 0
                ]
            ]
        ];
        $data = $this->doSearch('User', $search_params);

        //expecting one result
        $this->integer($data['data']['totalcount'])->isIdenticalTo(1);
    }

    /**
     * This test will add all searchoptions in each itemtype and check if the
     * search give a SQL error
     *
     * @return void
     */
    public function testSearchOptions()
    {
        $classes = $this->getSearchableClasses();
        foreach ($classes as $class) {
            if (!in_array($class, ['Accessibility', 'Oidc', 'SpecialStatus'])) {
                $item = new $class();

                //load all options; so rawSearchOptionsToAdd to be tested
                $options = \Search::getCleanedOptions($item->getType());

                $multi_criteria = [];
                foreach ($options as $key => $data) {
                    if (!is_int($key) || ($criterion_params = $this->getCriterionParams($item, $key, $data)) === null) {
                        continue;
                    }

                    // do a search query based on current search option
                    $this->doSearch(
                        $class,
                        [
                            'is_deleted' => 0,
                            'start' => 0,
                            'criteria' => [$criterion_params],
                            'metacriteria' => []
                        ]
                    );

                    $multi_criteria[] = $criterion_params;

                    if (count($multi_criteria) > 50) {
                        // Limit criteria count to 50 to prevent performances issues
                        // and also prevent exceeding of MySQL join limit.
                        break;
                    }
                }

                // do a search query with all criteria at the same time
                $search_params = [
                    'is_deleted' => 0,
                    'start' => 0,
                    'criteria' => $multi_criteria,
                    'metacriteria' => []
                ];
                $this->doSearch($class, $search_params);
            }
        }
    }

    /**
     * Test search with all meta to not have SQL errors
     *
     * @return void
     */
    public function testSearchAllMeta()
    {

        $classes = $this->getSearchableClasses();

        // extract metacriteria
        $itemtype_criteria = [];
        foreach ($classes as $class) {
            $itemtype = $class::getType();
            $itemtype_criteria[$itemtype] = [];
            $metaList = \Search::getMetaItemtypeAvailable($itemtype);
            foreach ($metaList as $metaitemtype) {
                $item = getItemForItemtype($metaitemtype);
                foreach ($item->searchOptions() as $key => $data) {
                    if (is_array($data) && array_key_exists('nometa', $data) && $data['nometa'] === true) {
                        continue;
                    }
                    if (!is_int($key) || ($criterion_params = $this->getCriterionParams($item, $key, $data)) === null) {
                        continue;
                    }

                    $criterion_params['itemtype'] = $metaitemtype;
                    $criterion_params['link'] = 'AND';

                    $itemtype_criteria[$itemtype][] = $criterion_params;
                }
            }
        }

        foreach ($itemtype_criteria as $itemtype => $criteria) {
            if (empty($criteria)) {
                continue;
            }

            $first_criteria_by_metatype = [];

            // Search with each meta criteria independently.
            foreach ($criteria as $criterion_params) {
                if (!array_key_exists($criterion_params['itemtype'], $first_criteria_by_metatype)) {
                    $first_criteria_by_metatype[$criterion_params['itemtype']] = $criterion_params;
                }

                $search_params = [
                    'is_deleted' => 0,
                    'start' => 0,
                    'criteria' => [
                        0 => [
                            'field' => 'view',
                            'searchtype' => 'contains',
                            'value' => ''
                        ]
                    ],
                    'metacriteria' => [$criterion_params]
                ];
                $this->doSearch($itemtype, $search_params);
            }

            // Search with criteria related to multiple meta items.
            // Limit criteria count to 5 to prevent performances issues (mainly on MariaDB).
            // Test would take hours if done using too many criteria on each request.
            // Thus, using 5 different meta items on a request seems already more than a normal usage.
            foreach (array_chunk($first_criteria_by_metatype, 3) as $criteria_chunk) {
                $search_params = [
                    'is_deleted' => 0,
                    'start' => 0,
                    'criteria' => [
                        0 => [
                            'field' => 'view',
                            'searchtype' => 'contains',
                            'value' => ''
                        ]
                    ],
                    'metacriteria' => $criteria_chunk
                ];
                $this->doSearch($itemtype, $search_params);
            }
        }
    }

    /**
     * Get criterion params for corresponding SO.
     *
     * @param CommonDBTM $item
     * @param int $so_key
     * @param array $so_data
     * @return null|array
     */
    private function getCriterionParams(CommonDBTM $item, int $so_key, array $so_data): ?array
    {
        global $DB;

        if ((array_key_exists('nosearch', $so_data) && $so_data['nosearch'])) {
            return null;
        }
        $actions = \Search::getActionsFor($item->getType(), $so_key);
        $searchtype = array_keys($actions)[0];

        switch ($so_data['datatype'] ?? null) {
            case 'bool':
            case 'integer':
            case 'number':
                $val = 0;
                break;
            case 'date':
            case 'date_delay':
                $val = date('Y-m-d');
                break;
            case 'datetime':
                // Search class expects seconds to be ":00".
                $val = date('Y-m-d H:i:00');
                break;
            case 'right':
                $val = READ;
                break;
            default:
                if (array_key_exists('table', $so_data) && array_key_exists('field', $so_data)) {
                    $field = $DB->tableExists($so_data['table']) ? $DB->getField($so_data['table'], $so_data['field']) : null;
                    if (preg_match('/int(\(\d+\))?$/', $field['Type'] ?? '')) {
                        $val = 1;
                        break;
                    }
                }

                $val = 'val';
                break;
        }

        return [
            'field' => $so_key,
            'searchtype' => $searchtype,
            'value' => $val
        ];
    }

    public function testIsNotifyComputerGroup()
    {
        $search_params = [
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            'criteria' => [
                0 => [
                    'field' => 'view',
                    'searchtype' => 'contains',
                    'value' => ''
                ]
            ],
            // group is_notify
            'metacriteria' => [
                0 => [
                    'link' => 'AND',
                    'itemtype' => 'Group',
                    'field' => 20,
                    'searchtype' => 'equals',
                    'value' => 1
                ]
            ]
        ];
        $this->login();
        $this->setEntity('_test_root_entity', true);

        $data = $this->doSearch('Computer', $search_params);

        //expecting no result
        $this->integer($data['data']['totalcount'])->isIdenticalTo(0);

        $computer1 = getItemByTypeName('Computer', '_test_pc01');

        //create group that can be notified
        $group = new \Group();
        $gid = $group->add(
            [
                'name' => '_test_group01',
                'is_notify' => '1',
                'entities_id' => $computer1->fields['entities_id'],
                'is_recursive' => 1
            ]
        );
        $this->integer($gid)->isGreaterThan(0);

        //attach group to computer
        $updated = $computer1->update(
            [
                'id' => $computer1->getID(),
                'groups_id' => $gid
            ]
        );
        $this->boolean($updated)->isTrue();

        $data = $this->doSearch('Computer', $search_params);

        //reset computer
        $updated = $computer1->update(
            [
                'id' => $computer1->getID(),
                'groups_id' => 0
            ]
        );
        $this->boolean($updated)->isTrue();

        $this->integer($data['data']['totalcount'])->isIdenticalTo(1);
    }

    public function testDateBeforeOrNot()
    {
        //tickets created since one week
        $search_params = [
            'is_deleted' => 0,
            'start' => 0,
            'criteria' => [
                0 => [
                    'field' => 'view',
                    'searchtype' => 'contains',
                    'value' => ''
                ],
                // creation date
                1 => [
                    'link' => 'AND',
                    'field' => '15',
                    'searchtype' => 'morethan',
                    'value' => '-1WEEK'
                ]
            ]
        ];

        $data = $this->doSearch('Ticket', $search_params);

        $this->integer($data['data']['totalcount'])->isGreaterThan(1);

        //negate previous search
        $search_params['criteria'][1]['link'] = 'AND NOT';
        $data = $this->doSearch('Ticket', $search_params);

        $this->integer($data['data']['totalcount'])->isIdenticalTo(0);
    }

    /**
     * Test that searchOptions throws an exception when it finds a duplicate
     *
     * @return void
     */
    public function testGetSearchOptionsWException()
    {
        $error = 'Duplicate key 12 (One search option/Any option) in tests\units\DupSearchOpt searchOptions! ';

        $this->exception(
            function () {
                $item = new DupSearchOpt();
                $item->searchOptions();
            }
        )
            ->isInstanceOf('\RuntimeException')
            ->message->endWith($error);
    }

    public function testManageParams()
    {
        // let's use TU_USER
        $this->login();
        $uid = getItemByTypeName('User', TU_USER, true);

        $search = \Search::manageParams('Ticket', ['reset' => 1], false, false);
        $this->array(
            $search
        )->isEqualTo([
                    'reset' => 1,
                    'start' => 0,
                    'order' => 'DESC',
                    'sort' => 19,
                    'is_deleted' => 0,
                    'criteria' => [
                        0 => [
                            'field' => 12,
                            'searchtype' => 'equals',
                            'value' => 'notold'
                        ],
                    ],
                    'metacriteria' => [],
                    'as_map' => 0
                ]);

        // now add a bookmark on Ticket view
        $bk = new \SavedSearch();
        $this->boolean(
            (bool) $bk->add([
                'name' => 'All my tickets',
                'type' => 1,
                'itemtype' => 'Ticket',
                'users_id' => $uid,
                'is_private' => 1,
                'entities_id' => 0,
                'is_recursive' => 1,
                'url' => 'front/ticket.php?itemtype=Ticket&sort=2&order=DESC&start=0&criteria[0][field]=5&criteria[0][searchtype]=equals&criteria[0][value]=' . $uid
            ])
        )->isTrue();

        $bk_id = $bk->fields['id'];

        $bk_user = new \SavedSearch_User();
        $this->boolean(
            (bool) $bk_user->add([
                'users_id' => $uid,
                'itemtype' => 'Ticket',
                'savedsearches_id' => $bk_id
            ])
        )->isTrue();

        $search = \Search::manageParams('Ticket', ['reset' => 1], true, false);
        $this->array(
            $search
        )->isEqualTo([
                    'reset' => 1,
                    'start' => 0,
                    'order' => 'DESC',
                    'sort' => 2,
                    'is_deleted' => 0,
                    'criteria' => [
                        0 => [
                            'field' => '5',
                            'searchtype' => 'equals',
                            'value' => $uid
                        ],
                    ],
                    'metacriteria' => [],
                    'itemtype' => 'Ticket',
                    'savedsearches_id' => $bk_id,
                    'as_map' => 0
                ]);

        // let's test for Computers
        $search = \Search::manageParams('Computer', ['reset' => 1], false, false);
        $this->array(
            $search
        )->isEqualTo([
                    'reset' => 1,
                    'start' => 0,
                    'order' => 'ASC',
                    'sort' => 1,
                    'is_deleted' => 0,
                    'criteria' => [
                        0 => [
                            'field' => 'view',
                            'link' => 'contains',
                            'value' => '',
                        ]
                    ],
                    'metacriteria' => [],
                    'as_map' => 0
                ]);

        // now add a bookmark on Computer view
        $bk = new \SavedSearch();
        $this->boolean(
            (bool) $bk->add([
                'name' => 'Computer test',
                'type' => 1,
                'itemtype' => 'Computer',
                'users_id' => $uid,
                'is_private' => 1,
                'entities_id' => 0,
                'is_recursive' => 1,
                'url' => 'front/computer.php?itemtype=Computer&sort=31&order=DESC&criteria%5B0%5D%5Bfield%5D=view&criteria%5B0%5D%5Bsearchtype%5D=contains&criteria%5B0%5D%5Bvalue%5D=test'
            ])
        )->isTrue();

        $bk_id = $bk->fields['id'];

        $bk_user = new \SavedSearch_User();
        $this->boolean(
            (bool) $bk_user->add([
                'users_id' => $uid,
                'itemtype' => 'Computer',
                'savedsearches_id' => $bk_id
            ])
        )->isTrue();

        $search = \Search::manageParams('Computer', ['reset' => 1], true, false);
        $this->array(
            $search
        )->isEqualTo([
                    'reset' => 1,
                    'start' => 0,
                    'order' => 'DESC',
                    'sort' => 31,
                    'is_deleted' => 0,
                    'criteria' => [
                        0 => [
                            'field' => 'view',
                            'searchtype' => 'contains',
                            'value' => 'test'
                        ],
                    ],
                    'metacriteria' => [],
                    'itemtype' => 'Computer',
                    'savedsearches_id' => $bk_id,
                    'as_map' => 0
                ]);

    }

    public function addSelectProvider()
    {
        return [
            'special_fk' => [
                [
                    'itemtype' => 'Computer',
                    'ID' => 24, // users_id_tech
                    'sql' => '`glpi_users_users_id_tech`.`name` AS `ITEM_Computer_24`, `glpi_users_users_id_tech`.`realname` AS `ITEM_Computer_24_realname`,
                           `glpi_users_users_id_tech`.`id` AS `ITEM_Computer_24_id`, `glpi_users_users_id_tech`.`firstname` AS `ITEM_Computer_24_firstname`,'
                ]
            ],
            'regular_fk' => [
                [
                    'itemtype' => 'Computer',
                    'ID' => 70, // users_id
                    'sql' => '`glpi_users`.`name` AS `ITEM_Computer_70`, `glpi_users`.`realname` AS `ITEM_Computer_70_realname`,
                           `glpi_users`.`id` AS `ITEM_Computer_70_id`, `glpi_users`.`firstname` AS `ITEM_Computer_70_firstname`,'
                ]
            ],
        ];
    }

    /**
     * @dataProvider addSelectProvider
     */
    public function testAddSelect($provider)
    {
        $sql_select = \Search::addSelect($provider['itemtype'], $provider['ID']);

        $this->string($this->cleanSQL($sql_select))
            ->isEqualTo($this->cleanSQL($provider['sql']));
    }

    public function addLeftJoinProvider()
    {
        return [
            'itemtype_item_revert' => [
                [
                    'itemtype' => 'Project',
                    'table' => \Contact::getTable(),
                    'field' => 'name',
                    'linkfield' => 'id',
                    'meta' => false,
                    'meta_type' => null,
                    'joinparams' => [
                        'jointype' => 'itemtype_item_revert',
                        'specific_itemtype' => 'Contact',
                        'beforejoin' => [
                            'table' => \ProjectTeam::getTable(),
                            'joinparams' => [
                                'jointype' => 'child',
                            ]
                        ]
                    ],
                    'sql' => "LEFT JOIN `glpi_projectteams`
                        ON (`glpi_projects`.`id` = `glpi_projectteams`.`projects_id`
                            )
                      LEFT JOIN `glpi_contacts`  AS `glpi_contacts_id_d36f89b191ea44cf6f7c8414b12e1e50`
                        ON (`glpi_contacts_id_d36f89b191ea44cf6f7c8414b12e1e50`.`id` = `glpi_projectteams`.`items_id`
                        AND `glpi_projectteams`.`itemtype` = 'Contact'
                         )"
                ]
            ],
            'special_fk' => [
                [
                    'itemtype' => 'Computer',
                    'table' => \User::getTable(),
                    'field' => 'name',
                    'linkfield' => 'users_id_tech',
                    'meta' => false,
                    'meta_type' => null,
                    'joinparams' => [],
                    'sql' => "LEFT JOIN `glpi_users` AS `glpi_users_users_id_tech` ON (`glpi_computers`.`users_id_tech` = `glpi_users_users_id_tech`.`id` )"
                ]
            ],
            'regular_fk' => [
                [
                    'itemtype' => 'Computer',
                    'table' => \User::getTable(),
                    'field' => 'name',
                    'linkfield' => 'users_id',
                    'meta' => false,
                    'meta_type' => null,
                    'joinparams' => [],
                    'sql' => "LEFT JOIN `glpi_users` ON (`glpi_computers`.`users_id` = `glpi_users`.`id` )"
                ]
            ],
        ];
    }

    /**
     * @dataProvider addLeftJoinProvider
     */
    public function testAddLeftJoin($lj_provider)
    {
        $already_link_tables = [];

        $sql_join = \Search::addLeftJoin(
            $lj_provider['itemtype'],
            getTableForItemType($lj_provider['itemtype']),
            $already_link_tables,
            $lj_provider['table'],
            $lj_provider['linkfield'],
            $lj_provider['meta'],
            $lj_provider['meta_type'],
            $lj_provider['joinparams'],
            $lj_provider['field']
        );

        $this->string($this->cleanSQL($sql_join))
            ->isEqualTo($this->cleanSQL($lj_provider['sql']));
    }

    private function cleanSQL($sql)
    {
        global $DB;

        $sql = str_replace("\r\n", ' ', $sql);
        $sql = str_replace("\n", ' ', $sql);
        while (strpos($sql, '  ') !== false) {
            $sql = str_replace('  ', ' ', $sql);
        }

        if ($DB::getQuoteNameChar() !== '`') {
            $sql = str_replace('`', $DB::getQuoteNameChar(), $sql);
        }

        $sql = trim($sql);

        return $sql;
    }

    public function testAllAssetsFields()
    {
        global $CFG_GLPI, $DB;

        $needed_fields = [
            'id',
            'name',
            'states_id',
            'locations_id',
            'serial',
            'otherserial',
            'comment',
            'users_id',
            'contact',
            'contact_num',
            'groups_id',
            'date_mod',
            'manufacturers_id',
            'groups_id_tech',
            'entities_id',
        ];

        foreach ($CFG_GLPI["asset_types"] as $itemtype) {
            $table = getTableForItemType($itemtype);

            foreach ($needed_fields as $field) {
                $this->boolean($DB->fieldExists($table, $field))
                    ->isTrue("$table.$field is missing");
            }
        }
    }

    public function testProblems()
    {
        $tech_users_id = getItemByTypeName('User', "tech", true);

        // reduce the right of tech profile
        // to have only the right of display their own problems (created, assign)
        \ProfileRight::updateProfileRights(getItemByTypeName('Profile', "Technician", true), [
            'Problem' => (\Problem::READMY + READNOTE + UPDATENOTE)
        ]);

        // add a group for tech user
        $group = new \Group();
        $groups_id = $group->add([
            'name' => "test group for tech user"
        ]);
        $this->integer((int) $groups_id)->isGreaterThan(0);
        $group_user = new \Group_User();
        $this->integer(
            (int) $group_user->add([
                'groups_id' => $groups_id,
                'users_id' => $tech_users_id
            ])
        )->isGreaterThan(0);

        // create a problem and assign group with tech user
        $problem = new \Problem();
        $this->integer(
            (int) $problem->add([
                'name' => "test problem visibility for tech",
                'content' => "test problem visibility for tech",
                '_groups_id_assign' => $groups_id
            ])
        )->isGreaterThan(0);

        // let's use tech user
        $this->login('tech', 'tech');

        // do search and check presence of the created problem
        $data = \Search::prepareDatasForSearch('Problem', ['reset' => 'reset']);
        \Search::constructSQL($data);
        \Search::constructData($data);

        $this->integer($data['data']['totalcount'])->isEqualTo(1);
        $this->array($data)
            ->array['data']
            ->array['rows']
            ->array[0]
            ->array['raw']
            ->string['ITEM_Problem_1']->isEqualTo('test problem visibility for tech');

    }

    public function testChanges()
    {
        $tech_users_id = getItemByTypeName('User', "tech", true);

        // reduce the right of tech profile
        // to have only the right of display their own changes (created, assign)
        \ProfileRight::updateProfileRights(getItemByTypeName('Profile', "Technician", true), [
            'Change' => (\Change::READMY + READNOTE + UPDATENOTE)
        ]);

        // add a group for tech user
        $group = new \Group();
        $groups_id = $group->add([
            'name' => "test group for tech user"
        ]);
        $this->integer((int) $groups_id)->isGreaterThan(0);

        $group_user = new \Group_User();
        $this->integer(
            (int) $group_user->add([
                'groups_id' => $groups_id,
                'users_id' => $tech_users_id
            ])
        )->isGreaterThan(0);

        // create a Change and assign group with tech user
        $change = new \Change();
        $this->integer(
            (int) $change->add([
                'name' => "test Change visibility for tech",
                'content' => "test Change visibility for tech",
                '_groups_id_assign' => $groups_id
            ])
        )->isGreaterThan(0);

        // let's use tech user
        $this->login('tech', 'tech');

        // do search and check presence of the created Change
        $data = \Search::prepareDatasForSearch('Change', ['reset' => 'reset']);
        \Search::constructSQL($data);
        \Search::constructData($data);

        $this->integer($data['data']['totalcount'])->isEqualTo(1);
        $this->array($data)
            ->array['data']
            ->array['rows']
            ->array[0]
            ->array['raw']
            ->string['ITEM_Change_1']->isEqualTo('test Change visibility for tech');

    }

    public function testSearchDdTranslation()
    {
        global $CFG_GLPI;

        $this->login();
        $conf = new \Config();
        $conf->setConfigurationValues('core', ['translate_dropdowns' => 1]);
        $CFG_GLPI['translate_dropdowns'] = 1;

        $state = new \State();
        $this->boolean($state->maybeTranslated())->isTrue();

        $sid = $state->add([
            'name' => 'A test state',
            'is_recursive' => 1
        ]);
        $this->integer($sid)->isGreaterThan(0);

        $ddtrans = new \DropdownTranslation();
        $this->integer(
            $ddtrans->add([
                'itemtype' => $state->getType(),
                'items_id' => $state->fields['id'],
                'language' => 'fr_FR',
                'field' => 'completename',
                'value' => 'Un status de test'
            ])
        )->isGreaterThan(0);

        $_SESSION['glpi_dropdowntranslations'] = [$state->getType() => ['completename' => '']];

        $search_params = [
            'is_deleted' => 0,
            'start' => 0,
            'criteria' => [
                0 => [
                    'field' => 'view',
                    'searchtype' => 'contains',
                    'value' => 'test'
                ]
            ],
            'metacriteria' => []
        ];

        $data = $this->doSearch('State', $search_params);

        $this->integer($data['data']['totalcount'])->isIdenticalTo(1);

        $conf->setConfigurationValues('core', ['translate_dropdowns' => 0]);
        $CFG_GLPI['translate_dropdowns'] = 0;
        unset($_SESSION['glpi_dropdowntranslations']);
    }

    public function dataInfocomOptions()
    {
        return [
            [1, false],
            [2, false],
            [4, false],
            [40, false],
            [31, false],
            [80, false],
            [25, true],
            [26, true],
            [27, true],
            [28, true],
            [37, true],
            [38, true],
            [50, true],
            [51, true],
            [52, true],
            [53, true],
            [54, true],
            [55, true],
            [56, true],
            [57, true],
            [58, true],
            [59, true],
            [120, true],
            [122, true],
            [123, true],
            [124, true],
            [125, true],
            [142, true],
            [159, true],
            [173, true],
        ];
    }

    /**
     * @dataProvider dataInfocomOptions
     */
    public function testIsInfocomOption($index, $expected)
    {
        $this->boolean(\Search::isInfocomOption('Computer', $index))->isIdenticalTo($expected);
    }

    protected function makeTextSearchValueProvider()
    {
        return [
            ['NULL', null],
            ['null', null],
            ['', ''],
            ['^', '%'],
            ['$', ''],
            ['^$', ''],
            ['$^', '%$^%'], // inverted ^ and $
            ['looking for', '%looking for%'],
            ['^starts with', 'starts with%'],
            ['ends with$', '%ends with'],
            ['^exact string$', 'exact string'],
            ['a ^ in the middle$', '%a ^ in the middle'],
            ['^and $ not at the end', 'and $ not at the end%'],
            ['45$^ab5', '%45$^ab5%'],
            ['^ ltrim', 'ltrim%'],
            ['rtim this   $', '%rtim this'],
            ['  extra spaces ', '%extra spaces%'],
            ['^ exactval $', 'exactval'],
        ];
    }

    /**
     * @dataProvider makeTextSearchValueProvider
     */
    public function testMakeTextSearchValue($value, $expected)
    {
        $this->variable(\Search::makeTextSearchValue($value))->isIdenticalTo($expected);
    }

    public function providerAddWhere()
    {
        return [
            [
                'link' => ' ',
                'nott' => 0,
                'itemtype' => \User::class,
                'ID' => 99,
                'searchtype' => 'equals',
                'val' => '5',
                'meta' => false,
                'expected' => "   (`glpi_users_users_id_supervisor`.`id` = '5')",
            ],
            [
                'link' => ' AND ',
                'nott' => 0,
                'itemtype' => \CartridgeItem::class,
                'ID' => 24,
                'searchtype' => 'equals',
                'val' => '2',
                'meta' => false,
                'expected' => "  AND  (`glpi_users_users_id_tech`.`id` = '2') ",
            ],
        ];
    }

    /**
     * @dataProvider providerAddWhere
     */
    public function testAddWhere($link, $nott, $itemtype, $ID, $searchtype, $val, $meta, $expected)
    {
        $output = \Search::addWhere($link, $nott, $itemtype, $ID, $searchtype, $val, $meta);
        $this->string($this->cleanSQL($output))->isEqualTo($this->cleanSQL($expected));

        if ($meta) {
            return; // Do not know how to run search on meta here
        }

        $search_params = [
            'is_deleted' => 0,
            'start' => 0,
            'criteria' => [
                [
                    'field' => $ID,
                    'searchtype' => $searchtype,
                    'value' => $val
                ]
            ],
            'metacriteria' => []
        ];

        // Run a search to trigger a test failure if anything goes wrong.
        $this->doSearch($itemtype, $search_params);
    }

    public function testSearchWGroups()
    {
        $this->login();
        $this->setEntity('_test_root_entity', true);

        $search_params = [
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            'criteria' => [
                0 => [
                    'field' => 'view',
                    'searchtype' => 'contains',
                    'value' => 'pc'
                ]
            ]
        ];
        $data = $this->doSearch('Computer', $search_params);

        $this->integer($data['data']['totalcount'])->isIdenticalTo(8);

        $displaypref = new \DisplayPreference();
        $input = [
            'itemtype' => 'Computer',
            'users_id' => \Session::getLoginUserID(),
            'num' => 49, //Computer groups_id_tech SO
        ];
        $this->integer((int) $displaypref->add($input))->isGreaterThan(0);

        $data = $this->doSearch('Computer', $search_params);

        $this->integer($data['data']['totalcount'])->isIdenticalTo(8);
    }

    public function testSearchWithMultipleFkeysOnSameTable()
    {
        // Create tickets that exercise two distinct joins to the users table.
        $entities_id = $this->createSearchEntityContext();
        $suffix = $this->getUniqueString();

        $user_tech_id = getItemByTypeName('User', 'tech', true);
        $user_normal_id = getItemByTypeName('User', 'normal', true);
        $user_login = \Session::getLoginUserID();

        $matching_ticket = new \Ticket();
        $matching_ticket_id = $matching_ticket->add([
            'name' => 'multi-fk-match-' . $suffix,
            'content' => 'multi-fk-match-' . $suffix,
            'entities_id' => $entities_id,
            'users_id_recipient' => $user_login,
        ]);
        $this->integer((int) $matching_ticket_id)->isGreaterThan(0);
        $this->createItem(\Ticket_User::class, [
            'tickets_id' => $matching_ticket_id,
            'users_id' => $user_tech_id,
            'type' => \CommonITILActor::REQUESTER,
        ]);

        $requester_miss = new \Ticket();
        $requester_miss_id = $requester_miss->add([
            'name' => 'multi-fk-requester-miss-' . $suffix,
            'content' => 'multi-fk-requester-miss-' . $suffix,
            'entities_id' => $entities_id,
            'users_id_recipient' => $user_login,
        ]);
        $this->integer((int) $requester_miss_id)->isGreaterThan(0);
        $this->createItem(\Ticket_User::class, [
            'tickets_id' => $requester_miss_id,
            'users_id' => $user_normal_id,
            'type' => \CommonITILActor::REQUESTER,
        ]);

        // Search with recipient and requester criteria, which both join the users table.
        $search_params = [
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            'criteria' => [
                0 => [
                    'link' => 'AND',
                    'field' => '4', // Requester
                    'searchtype' => 'contains',
                    'value' => 'tech',
                ],
                1 => [
                    'link' => 'AND',
                    'field' => '22', // Recipient
                    'searchtype' => 'contains',
                    'value' => TU_USER,
                ]
            ]
        ];
        $data = $this->doSearch('Ticket', $search_params);

        // Only the ticket matching both user joins should remain.
        $this->integer($data['data']['totalcount'])->isIdenticalTo(1);
        $this->assertSearchContainsText($data, 'multi-fk-match-' . $suffix);
    }

    public function testSearchAllAssets()
    {
        // Create one uniquely named asset for each type covered by AllAssets.
        $entities_id = $this->createSearchEntityContext();
        $suffix = $this->getUniqueString();
        $expected_labels = [];

        foreach ([\Computer::class, \Monitor::class, \NetworkEquipment::class, \Peripheral::class, \Phone::class, \Printer::class] as $itemtype) {
            $name = 'allassets-match-' . strtolower($itemtype) . '-' . $suffix;
            $this->createItem($itemtype, [
                'name' => $name,
                'entities_id' => $entities_id,
            ]);
            $expected_labels[] = $name;
        }

        // Search across all asset types using a shared name prefix.
        $data = $this->doSearch('AllAssets', [
            'reset' => 'reset',
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            'criteria' => [
                [
                    'link' => 'AND',
                    'field' => 'view',
                    'searchtype' => 'contains',
                    'value' => $suffix,
                ],
            ]
        ]);

        // Each created asset should be visible in the aggregated result set.
        foreach ($expected_labels as $expected_label) {
            $this->assertSearchContainsText($data, $expected_label);
        }
    }

    public function testSearchWithNamespacedItem()
    {
        // Create one computer and search it through the namespaced item type alias.
        $entities_id = $this->createSearchEntityContext();
        $suffix = $this->getUniqueString();
        $matching_computer = $this->createItem(\Computer::class, [
            'name' => 'namespaced-search-' . $suffix,
            'entities_id' => $entities_id,
        ]);

        $search_params = [
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            'criteria' => [
                [
                    'field' => 1,
                    'searchtype' => 'contains',
                    'value' => 'namespaced-search-' . $suffix,
                ]
            ],
        ];

        // The namespaced search should behave like the underlying item type.
        $data = $this->doSearch('SearchTest\\Computer', $search_params);

        $this->assertSearchReturnedIds($data, [$matching_computer->getID()], 'SearchTest\\Computer');
    }

    public function testGroupParamAfterMeta()
    {
        // Try to run this query without warnings
        $this->doSearch('Ticket', [
            'reset' => 'reset',
            'is_deleted' => 0,
            'start' => 0,
            'search' => 'Search',
            'criteria' => [
                [
                    'link' => 'AND',
                    'field' => 12,
                    'searchtype' => 'equals',
                    'value' => 'notold',
                ],
                [
                    'link' => 'AND',
                    'itemtype' => 'Computer',
                    'meta' => true,
                    'field' => 1,
                    'searchtype' => 'contains',
                    'value' => 'ù',
                ],
                [
                    'link' => 'AND',
                    'criteria' => [
                        [
                            'link' => 'AND+NOT',
                            'field' => 'view',
                            'searchtype' => 'contains',
                            'value' => '233',
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * Check that search result is valid.
     *
     * @param array $result
     */
    private function checkSearchResult($result)
    {
        $this->array($result)->hasKey('data');
        $this->array($result['data'])->hasKeys(['count', 'begin', 'end', 'totalcount', 'cols', 'rows', 'items']);
        $this->integer((int) $result['data']['count']);
        $this->integer((int) $result['data']['begin']);
        $this->integer((int) $result['data']['end']);
        $this->integer((int) $result['data']['totalcount']);
        $this->array($result['data']['cols']);
        $this->array($result['data']['rows']);
        $this->array($result['data']['items']);

        // No errors
        $this->array($result)->hasKey('last_errors');
        $this->array($result['last_errors'])->isIdenticalTo([]);

        $this->array($result)->hasKey('sql');
        $this->array($result['sql'])->hasKey('search');
        $this->string($result['sql']['search']);
    }

    /**
     * Returns list of searchable classes.
     *
     * @return array
     */
    private function getSearchableClasses(): array
    {
        $classes = $this->getClasses(
            'searchOptions',
            [
                '/^Common.*/', // Should be abstract
                'NetworkPortInstantiation', // Should be abstract (or have $notable = true)
                'NetworkPortMigration', // Tables only exists in specific cases
                'NotificationSettingConfig', // Stores its data in glpi_configs, does not acts as a CommonDBTM
            ]
        );
        $searchable_classes = [];
        foreach ($classes as $class) {
            $item_class = new \ReflectionClass($class);
            if ($item_class->isAbstract() || $class::getTable() === '' || !is_a($class, CommonDBTM::class, true)) {
                // abstract class or class with "static protected $notable = true;" (which is a kind of abstract)
                continue;
            }

            $searchable_classes[] = $class;
        }
        sort($searchable_classes);

        return $searchable_classes;
    }
}

class DupSearchOpt extends \CommonDBTM
{
    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id' => '12',
            'name' => 'One search option'
        ];

        $tab[] = [
            'id' => '12',
            'name' => 'Any option'
        ];

        return $tab;
    }
}

namespace SearchTest;

class Computer extends \Computer
{
    public static function getTable($classname = null)
    {
        return 'glpi_computers';
    }
}
