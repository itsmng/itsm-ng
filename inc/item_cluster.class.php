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
class Item_Cluster extends CommonDBRelation
{
    public static $itemtype_1 = 'Cluster';
    public static $items_id_1 = 'clusters_id';
    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';
    public static $checkItem_1_Rights = self::DONT_CHECK_ITEM_RIGHTS;
    public static $mustBeAttached_1      = false;
    public static $mustBeAttached_2      = false;

    public static function getTypeName($nb = 0)
    {
        return _n('Item', 'Item', $nb);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $nb = 0;
        if ($_SESSION['glpishow_count_on_tabs']) {
            $nb = self::countForMainItem($item);
        }
        return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showItems($item, $withtemplate);
    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'MassiveAction:update';
        $forbidden[] = 'CommonDBConnexity:affect';
        $forbidden[] = 'CommonDBConnexity:unaffect';

        return $forbidden;
    }

    /**
     * Print enclosure items
     *
     * @return void
    **/
    public static function showItems(Cluster $cluster)
    {
        $ID = $cluster->fields['id'];
        $rand = mt_rand();

        if (
            !$cluster->getFromDB($ID)
            || !$cluster->can($ID, READ)
        ) {
            return false;
        }
        $canedit = $cluster->canEdit($ID);

        $itemsResult = self::getAdapter()->request([
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'clusters_id' => $ID
           ]
        ]);

        Session::initNavigateListItems(
            self::getType(),
            //TRANS : %1$s is the itemtype name,
            //        %2$s is the name of the item (used for headings of a list)
            sprintf(
                __('%1$s = %2$s'),
                $cluster->getTypeName(1),
                $cluster->getName()
            )
        );

        if ($cluster->canAddItem('itemtype')) {
            echo "<div class='firstbloc'>";
            Html::showSimpleForm(
                self::getFormURL(),
                '_add_fromitem',
                __('Add new item to this cluster...'),
                [
                  'cluster'   => $ID,
                  'position'  => 1
                ]
            );
            echo "</div>";
        }
        $items = $itemsResult->fetchAllAssociative();

        if (!count($items)) {
            echo "<table class='tab_cadre_fixe' aria-label='No item Found'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
            if ($canedit) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = [
                   'num_displayed'   => min($_SESSION['glpilist_limit'], count($items)),
                   'container'       => 'mass' . __CLASS__ . $rand
                ];
                Html::showMassiveActions($massiveactionparams);
            }

            echo "<table class='tab_cadre_fixehov' aria-label='Selectable Item'>";
            $header = "<tr>";
            if ($canedit) {
                $header .= "<th width='10'>";
                $header .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                $header .= "</th>";
            }
            $header .= "<th>" . _n('Item', 'Items', 1) . "</th>";
            $header .= "</tr>";

            echo $header;
            foreach ($items as $row) {
                $item = new $row['itemtype']();
                $item->getFromDB($row['items_id']);
                echo "<tr lass='tab_bg_1'>";
                if ($canedit) {
                    echo "<td>";
                    Html::showMassiveActionCheckBox(__CLASS__, $row["id"]);
                    echo "</td>";
                }
                echo "<td>" . $item->getLink() . "</td>";
                echo "</tr>";
            }
            echo $header;
            echo "</table>";

            if ($canedit && count($items)) {
                $massiveactionparams['ontop'] = false;
                Html::showMassiveActions($massiveactionparams);
            }
            if ($canedit) {
                Html::closeForm();
            }
        }
    }

    public function showForm($ID, $options = [])
    {
        global $CFG_GLPI;

        //get all used items
        $used = [];
        $request = $this::getAdapter()->request([
           'FROM'   => $this->getTable()
        ]);
        while ($row = $request->fetchAssociative()) {
            $used [$row['itemtype']][] = $row['items_id'];
        }
        $jsUsed = json_encode($used);

        $loadItemDropdownScript = <<<JS
        $.ajax({
            url: '{$CFG_GLPI["root_doc"]}/ajax/dropdownAllItems.php',
            data: {
                idtable: $('#dropdown_itemtype').val(),
                name: 'items_id',
                value: $('#dropdown_items_id').val(),
                rand: $('#dropdown_items_id').attr('rand'),
                used: JSON.stringify($jsUsed)
            },
            type: 'POST',
            success: function(data) {
                const jsonData = JSON.parse(data);
                $('#dropdown_items_id').empty();
                for (const [key, value] of Object.entries(jsonData)) {
                    if (typeof value === 'object') {
                        // add optgroup
                        let group = $('<optgroup>', {
                            label: key
                        });
                        $('#dropdown_items_id').append(group);
                        for (const [key2, value2] of Object.entries(value)) {
                            group.append($('<option>', {
                                value: key2,
                                text: value2
                            }));
                        }
                    } else {
                        $('#dropdown_items_id').append($('<option>', {
                            value: key,
                            text: value
                        }));
                    }
                }
            }
        });
      JS;

        $itemtypes = $CFG_GLPI['cluster_types'];
        $itemtypesValues = [];
        foreach ($itemtypes as $itemtype) {
            $itemtypesValues[$itemtype] = $itemtype::getTypeName(1);
        }

        $form = [
          'action' => $this->getFormURL(),
          'itemtype' => $this::class,
          'content' => [
            $this->getTypeName() => [
              'visible' => true,
              'inputs' => [
                $this->isNewID($ID) ? [] : [
                  'type' => 'hidden',
                  'name' => 'id',
                  'value' => $ID
                ],
                __('Item type') => [
                  'type' => 'select',
                  'id' => 'dropdown_itemtype',
                  'name' => 'itemtype',
                  'value' => $this->fields["itemtype"] ?? $options['itemtype'] ?? '',
                  'values' => $itemtypesValues,
                  'hooks' => [
                      'change' => $loadItemDropdownScript
                  ],
                  'init' => $loadItemDropdownScript
                ],
                __('Item') => [
                  'type' => 'select',
                  'id' => 'dropdown_items_id',
                  'name' => 'items_id',
                  'value' => $this->fields["items_id"] ?? $options['items_id'] ?? 0,
                ],
                Cluster::getTypeName(1) => [
                  'type' => 'select',
                  'id' => 'dropdown_clusters_id',
                  'itemtype' => Cluster::class,
                  'name' => 'clusters_id',
                  'value' => $this->fields["clusters_id"] ?? $options['clusters_id'] ?? 0,
                ],
              ]
            ]
          ]
        ];
        renderTwigForm($form, '', $this->fields);
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
            ($this->isNewItem() && (!isset($input['clusters_id']) || empty($input['clusters_id'])))
            || (isset($input['clusters_id']) && empty($input['clusters_id']))
        ) {
            $error_detected[] = __('A cluster is required');
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
}
