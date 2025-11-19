<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

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
 **/
class Appliance_Item extends CommonDBRelation
{
    use Glpi\Features\Clonable;

    public static $itemtype_1 = 'Appliance';
    public static $items_id_1 = 'appliances_id';
    public static $take_entity_1 = false;

    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';
    public static $take_entity_2 = true;

    public function getCloneRelations(): array
    {
        return [
            Appliance_Item_Relation::class
        ];
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Item', 'Items', $nb);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!Appliance::canView()) {
            return '';
        }

        $nb = 0;
        if ($item->getType() == Appliance::class) {
            if ($_SESSION['glpishow_count_on_tabs']) {
                if (!$item->isNewItem()) {
                    $nb = self::countForMainItem($item);
                }
            }
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
        } elseif (in_array($item->getType(), Appliance::getTypes(true))) {
            if ($_SESSION['glpishow_count_on_tabs']) {
                $nb = self::countForItem($item);
            }
            return self::createTabEntry(Appliance::getTypeName(Session::getPluralNumber()), $nb);
        }
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case Appliance::class:
                self::showItems($item);
                break;
            default:
                if (in_array($item->getType(), Appliance::getTypes())) {
                    self::showForItem($item, $withtemplate);
                }
        }
        return true;
    }

    /**
     * Print enclosure items
     *
     * @param Appliance $appliance  Appliance object wanted
     *
     * @return void|boolean (display) Returns false if there is a rights error.
     **/
    public static function showItems(Appliance $appliance)
    {
        global $DB, $CFG_GLPI;

        $ID = $appliance->fields['id'];
        $rand = mt_rand();

        if (
            !$appliance->getFromDB($ID)
            || !$appliance->can($ID, READ)
        ) {
            return false;
        }
        $canedit = $appliance->canEdit($ID);
        $entity_restrict = [(int) $appliance->getEntityID()];
        if ((int) ($appliance->fields['is_recursive'] ?? 0)) {
            $entity_restrict = getSonsOf('glpi_entities', $appliance->getEntityID());
        }
        $entity_restrict = array_unique(array_map('intval', (array) $entity_restrict));
        if (empty($entity_restrict)) {
            $entity_restrict = [(int) $appliance->getEntityID()];
        }
        $entity_restrict_js = json_encode(array_values($entity_restrict));

        $items = $DB->request([
            'FROM' => self::getTable(),
            'WHERE' => [
                self::$items_id_1 => $ID
            ]
        ]);

        Session::initNavigateListItems(
            self::getType(),
            //TRANS : %1$s is the itemtype name,
            //        %2$s is the name of the item (used for headings of a list)
            sprintf(
                __('%1$s = %2$s'),
                $appliance->getTypeName(1),
                $appliance->getName()
            )
        );

        if ($appliance->canAddItem('itemtype')) {
            $itemtypes = $CFG_GLPI['appliance_types'];
            $options = [];
            foreach ($itemtypes as $itemtype) {
                $options[$itemtype] = $itemtype::getTypeName(1);
            }

            $form = [
                'action' => Toolbox::getItemTypeFormURL(__CLASS__),
                'buttons' => [
                    [
                        'type' => 'submit',
                        'name' => 'add',
                        'value' => _sx('button', 'Add an item'),
                        'class' => 'btn btn-secondary'
                    ]
                ],
                'content' => [
                    __('Add an item') => [
                        'visible' => true,
                        'inputs' => [
                            [
                                'type' => 'hidden',
                                'name' => 'appliances_id',
                                'value' => $ID
                            ],
                            __('Type') => [
                                'type' => 'select',
                                'id' => 'dropdown_itemtype',
                                'name' => 'itemtype',
                                'values' => [Dropdown::EMPTY_VALUE] + array_unique($options),
                                'col_lg' => 6,
                                'hooks' => [
                                    'change' => <<<JS
                                        const entityRestrict = $entity_restrict_js;
                                        const target = $('#dropdown_items_id');
                                        const selectedType = this.value;
                                        target.empty();
                                        if (!selectedType) {
                                            return;
                                        }
                              $.ajax({
                                    method: "POST",
                                    url: "$CFG_GLPI[root_doc]/ajax/getDropdownValue.php",
                                    data: {
                                                    itemtype: selectedType,
                                       display_emptychoice: 1,
                                                    entity_restrict: entityRestrict,
                                    },
                                    success: function(response) {
                                       const data = response.results;
                                       for (let i = 0; i < data.length; i++) {
                                          if (data[i].children) {
                                                            const group = target
                                                .append("<optgroup label='" + data[i].text + "'></optgroup>");
                                             for (let j = 0; j < data[i].children.length; j++) {
                                                group.append("<option value='" + data[i].children[j].id + "'>" + data[i].children[j].text + "</option>");
                                             }
                                          } else {
                                                            target.append("<option value='" + data[i].id + "'>" + data[i].text + "</option>");
                                          }
                                       }
                                    }
                                 });
                           JS,
                                ]
                            ],
                            __('Item') => [
                                'type' => 'select',
                                'id' => 'dropdown_items_id',
                                'name' => 'items_id',
                                'values' => [],
                                'col_lg' => 6,
                            ],
                        ]
                    ]
                ]
            ];
            renderTwigForm($form);
        }

        $items = iterator_to_array($items);

        $fields = [
            __('Itemtype'),
            _n('Item', 'Items', 1),
            __("Serial"),
            __("Inventory number"),
            Appliance_Item_Relation::getTypeName(Session::getPluralNumber()),
        ];

        if ($canedit) {
            $massiveactionparams = [
                'container' => 'tableForApplianceItem',
                'specific_actions' => [
                    'MassiveAction:purge' => _x('button', 'Delete permanently the relation with selected elements'),
                ],
                'is_deleted' => 0,
                'display_arrow' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }

        $values = [];
        $massive_action = [];
        foreach ($items as $row) {
            $item = new $row['itemtype']();
            $item->getFromDB($row['items_id']);
            $values[] = [
                $item->getTypeName(1),
                $item->getLink(),
                ($item->fields['serial'] ?? ""),
                ($item->fields['otherserial'] ?? ""),
            ];
            $massive_action[] = sprintf('item[%s][%s]', self::class, $row['id']);
        }
        renderTwigTemplate('table.twig', [
            'id' => 'tableForApplianceItem',
            'fields' => $fields,
            'values' => $values,
            'massive_action' => $massive_action,
        ]);
    }

    /**
     * Print an HTML array of appliances associated to an object
     *
     * @since 9.5.2
     *
     * @param CommonDBTM $item         CommonDBTM object wanted
     * @param boolean    $withtemplate not used (to be deleted)
     *
     * @return void
     **/
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {

        $itemtype = $item->getType();
        $ID = $item->fields['id'];

        if (
            !Appliance::canView()
            || !$item->can($ID, READ)
        ) {
            return;
        }

        $canedit = $item->can($ID, UPDATE);
        $rand = mt_rand();

        $iterator = self::getListForItem($item);
        $number = count($iterator);

        $appliances = [];
        $used = [];
        while ($data = $iterator->next()) {
            $appliances[$data['id']] = $data;
            $used[$data['id']] = $data['id'];
        }
        if ($canedit && ($withtemplate != 2)) {
            $form = [
                'action' => Toolbox::getItemTypeFormURL(__CLASS__),
                'buttons' => [
                    [
                        'name' => 'add',
                        'value' => _x('button', 'Associate'),
                        'class' => 'btn btn-secondary',
                    ]
                ],
                'content' => [
                    '' => [
                        'visible' => true,
                        'inputs' => [
                            [
                                'type' => 'hidden',
                                'name' => 'items_id',
                                'value' => $ID,
                            ],
                            [
                                'type' => 'hidden',
                                'name' => 'itemtype',
                                'value' => $itemtype,
                            ],
                            __('Add to an appliance') => [
                                'type' => 'select',
                                'name' => 'appliances_id',
                                'itemtype' => Appliance::class,
                                'actions' => getItemActionButtons(['info'], Appliance::class),
                                'col_lg' => 12,
                                'col_md' => 12,
                            ]
                        ]
                    ]
                ]
            ];
            renderTwigForm($form);
        }

        if ($withtemplate != 2) {
            if ($canedit && $number) {
                $massiveactionparams = [
                    'container' => 'tableForApplianceItem',
                    'display_arrow' => false,
                    'specific_actions' => [
                        'MassiveAction:purge' => _x('button', 'Delete permanently the relation with selected elements'),
                    ],
                ];
                Html::showMassiveActions($massiveactionparams);
            }
        }

        $fields = [
            __('Name'),
            Appliance_Item_Relation::getTypeName(Session::getPluralNumber()),
        ];
        $values = [];
        $massive_action = [];
        foreach ($appliances as $data) {
            $cID = $data["id"];
            Session::addToNavigateListItems(__CLASS__, $cID);
            $assocID = $data["linkid"];
            $app = new Appliance();
            $app->getFromResultSet($data);
            $name = $app->fields["name"];
            if (
                $_SESSION["glpiis_ids_visible"]
                || empty($app->fields["name"])
            ) {
                $name = sprintf(__('%1$s (%2$s)'), $name, $app->fields["id"]);
            }
            $values[] = [
                $name,
                Appliance_Item_Relation::showListForApplianceItem($assocID, $canedit),
            ];
            $massive_action[] = sprintf('item[%s][%s]', self::class, $data['linkid']);
        }

        renderTwigTemplate('table.twig', [
            'id' => 'tableForApplianceItem',
            'fields' => $fields,
            'values' => $values,
            'massive_action' => $massive_action,
        ]);
    }


    public function prepareInputForAdd($input)
    {
        return $this->prepareInput($input);
    }

    public function prepareInputForUpdate($input)
    {
        return $this->prepareInput($input);
    }

    /**
     * Prepares input (for update and add)
     *
     * @param array $input Input data
     *
     * @return array
     */
    private function prepareInput($input)
    {
        $error_detected = [];

        //check for requirements
        if (
            ($this->isNewItem() && (!isset($input['itemtype']) || empty($input['itemtype'])))
            || (isset($input['itemtype']) && empty($input['itemtype']))
        ) {
            $error_detected[] = __('An item type is required');
        }
        if (
            ($this->isNewItem() && (!isset($input['items_id']) || empty($input['items_id'])))
            || (isset($input['items_id']) && empty($input['items_id']))
        ) {
            $error_detected[] = __('An item is required');
        }
        if (
            ($this->isNewItem() && (!isset($input[self::$items_id_1]) || empty($input[self::$items_id_1])))
            || (isset($input[self::$items_id_1]) && empty($input[self::$items_id_1]))
        ) {
            $error_detected[] = __('An appliance is required');
        }

        if (count($error_detected)) {
            foreach ($error_detected as $error) {
                Session::addMessageAfterRedirect(
                    $error,
                    true,
                    ERROR
                );
            }
            return false;
        }

        return $input;
    }

    public static function countForMainItem(CommonDBTM $item, $extra_types_where = [])
    {
        $types = Appliance::getTypes();
        $clause = [];
        if (count($types)) {
            $clause = ['itemtype' => $types];
        } else {
            $clause = [new \QueryExpression('true = false')];
        }
        $extra_types_where = array_merge(
            $extra_types_where,
            $clause
        );
        return parent::countForMainItem($item, $extra_types_where);
    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        $forbidden[] = 'CommonDBConnexity:unaffect';
        $forbidden[] = 'CommonDBConnexity:affect';
        return $forbidden;
    }

    public static function getRelationMassiveActionsSpecificities()
    {
        global $CFG_GLPI;

        $specificities = parent::getRelationMassiveActionsSpecificities();
        $specificities['itemtypes'] = Appliance::getTypes();

        return $specificities;
    }

    public function cleanDBonPurge()
    {
        $this->deleteChildrenAndRelationsFromDb(
            [
                Appliance_Item_Relation::class,
            ]
        );
    }
}
