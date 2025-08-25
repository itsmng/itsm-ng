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
class Item_Rack extends CommonDBRelation
{
    public static $itemtype_1 = 'Rack';
    public static $items_id_1 = 'racks_id';
    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';
    public static $checkItem_2_Rights = self::DONT_CHECK_ITEM_RIGHTS;
    public static $mustBeAttached_1      = false;
    public static $mustBeAttached_2      = false;

    public static function getTypeName($nb = 0)
    {
        return _n('Item', 'Item', $nb);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $nb = 0;
        switch ($item->getType()) {
            default:
                if ($_SESSION['glpishow_count_on_tabs']) {
                    $nb = countElementsInTable(
                        self::getTable(),
                        ['racks_id'  => $item->getID()]
                    );
                    $nb += countElementsInTable(
                        PDU_Rack::getTable(),
                        ['racks_id'  => $item->getID()]
                    );
                }
                return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
        }
        return '';
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
     * Print racks items
     * @param  Rack   $rack the current rack instance
     * @return void
     */
    public static function showItems(Rack $rack)
    {
        global $CFG_GLPI;

        $ID = $rack->getID();
        $rand = mt_rand();

        if (
            !$rack->getFromDB($ID)
            || !$rack->can($ID, READ)
        ) {
            return false;
        }
        $canedit = $rack->canEdit($ID);

        $itemsRequest = self::getAdapter()->request([
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'racks_id' => $rack->getID()
           ],
           'ORDER' => 'position DESC'
        ]);
        $items = $itemsRequest->fetchAllAssociative();
        $link = new self();

        if ($canedit) {
            Session::initNavigateListItems(
                self::getType(),
                //TRANS : %1$s is the itemtype name,
                //        %2$s is the name of the item (used for headings of a list)
                sprintf(
                    __('%1$s = %2$s'),
                    $rack->getTypeName(1),
                    $rack->getName()
                )
            );
        }

        echo "<div id='switchview'>";
        echo "<i id='sviewlist' class='pointer fa fa-list-alt' title='" . __('View as list') . "'></i>";
        echo "<i id='sviewgraph' class='pointer fa fa-th-large selected' title='" . __('View graphical representation') . "'></i>";
        echo "</div>";

        echo "<div id='viewlist'>";

        echo "<h2>" . __("Racked items") . "</h2>";
        if (!count($items)) {
            echo "<table class='tab_cadre_fixe' aria-label='No Item Found'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
            $massiveActionId = 'mass' . __CLASS__ . $rand;

            $massiveactionparams = [
               'num_displayed'   => min($_SESSION['glpilist_limit'], count($items)),
               'container'       => $massiveActionId,
               'display_arrow'   => false,
            ];
            Html::showMassiveActions($massiveactionparams);
            $fields = [
              'item' => _n('Item', 'Items', 1),
              'position' => __('Position'),
              'orientation' => __('Orientation')
            ];
            $values = [];
            $massiveActionValues = [];
            foreach ($items as $row) {
                $item = new $row['itemtype']();
                $item->getFromDB($row['items_id']);
                $values[] = [
                    'item' => $item->getLink(),
                    'position' => $row['position'],
                    'orientation' => $row['orientation'] == Rack::FRONT ? __('Front') : __('Rear')
                ];
                $massiveActionValues[] = sprintf("item[%s][%s]", $item->getType(), $item->getID());
            }
            renderTwigTemplate('table.twig', [
              'id' => $massiveActionId,
              'fields' => $fields,
              'values' => $values,
              'massive_action' => $massiveActionValues,
            ]);
        }

        PDU_Rack::showListForRack($rack);

        echo "</div>";
        echo "<div id='viewgraph'>";

        $data = [];
        //all rows; empty
        for ($i = (int)$rack->fields['number_units']; $i > 0; --$i) {
            $data[Rack::FRONT][$i] = false;
            $data[Rack::REAR][$i] = false;
        }

        //fill rows
        $outbound = [];
        foreach ($items as $row) {
            $rel  = new self();
            $rel->getFromDB($row['id']);
            $item = new $row['itemtype']();
            if (!$item->getFromDB($row['items_id'])) {
                continue;
            }

            $position = $row['position'];

            $gs_item = [
               'id'        => $row['id'],
               'name'      => $item->getName(),
               'x'         => $row['hpos'] >= 2 ? 1 : 0,
               'y'         => $rack->fields['number_units'] - $row['position'],
               'height'    => 1,
               'width'     => 2,
               'bgcolor'   => $row['bgcolor'],
               'picture_f' => null,
               'picture_r' => null,
               'url'       => $item->getLinkURL(),
               'rel_url'   => $rel->getLinkURL(),
               'rear'      => false,
               'half_rack' => false,
               'reserved'  => (bool) $row['is_reserved'],
            ];

            $model_class = $item->getType() . 'Model';
            $modelsfield = strtolower($item->getType()) . 'models_id';
            $model = new $model_class();
            if ($model->getFromDB($item->fields[$modelsfield])) {
                $item->model = $model;

                if ($item->model->fields['required_units'] > 1) {
                    $gs_item['height'] = $item->model->fields['required_units'];
                    $gs_item['y']      = $rack->fields['number_units'] + 1
                                         - $row['position']
                                         - $item->model->fields['required_units'];
                }

                if ($item->model->fields['is_half_rack'] == 1) {
                    $gs_item['half_rack'] = true;
                    $gs_item['width'] = 1;
                    $row['position'] .= "_" . $gs_item['x'];
                    if ($row['orientation'] == Rack::REAR) {
                        $gs_item['x'] = $row['hpos'] == 2 ? 0 : 1;
                    }
                }

                if (!empty($item->model->fields['picture_front'])) {
                    $gs_item['picture_f'] = Toolbox::getPictureUrl($item->model->fields['picture_front']);
                }
                if (!empty($item->model->fields['picture_rear'])) {
                    $gs_item['picture_r'] = Toolbox::getPictureUrl($item->model->fields['picture_rear']);
                }
            } else {
                $item->model = null;
            }

            if (isset($data[$row['orientation']][$position])) {
                $data[$row['orientation']][$row['position']] = [
                   'row'     => $row,
                   'item'    => $item,
                   'gs_item' => $gs_item
                ];

                //add to other side if needed
                if (
                    $item->model == null
                    || $item->model->fields['depth'] >= 1
                ) {
                    $gs_item['rear'] = true;
                    $flip_orientation = (int) !((bool) $row['orientation']);
                    if ($gs_item['half_rack']) {
                        $gs_item['x'] = (int) !((bool) $gs_item['x']);
                        //$row['position'] = substr($row['position'], 0, -2)."_".$gs_item['x'];
                    }
                    $data[$flip_orientation][$row['position']] = [
                       'row'     => $row,
                       'item'    => $item,
                       'gs_item' => $gs_item
                    ];
                }
            } else {
                $outbound[] = ['row' => $row, 'item' => $item, 'gs_item' => $gs_item];
            }
        }

        if (count($outbound)) {
            echo "<table class='outbound' aria-label='Following elements are out of rack bounds'><thead><th>";
            echo __('Following elements are out of rack bounds');
            echo "</th></thead><tbody>";
            foreach ($outbound as $out) {
                echo "<tr><td>" . self::getCell($out, !$canedit) . "</td></tr>";
            }
            echo "</tbody></table>";
        }
        $nb_top_pdu = count(PDU_Rack::getForRackSide($rack, PDU_Rack::SIDE_TOP));
        $nb_bot_pdu = count(PDU_Rack::getForRackSide($rack, PDU_Rack::SIDE_BOTTOM));

        echo '
      <div class="racks_row">
         <span class="racks_view_controls">
            <span class="mini_toggle active"
                  id="toggle_images">' . __('images') . '</span>
            <span class="mini_toggle active"
                  id="toggle_text">' . __('texts') . '</span>
            <div class="sep"></div>
         </span>
         <div class="racks_col">
         <h2>' . __('Front') . '</h2>
         <div class="rack_side rack_front">';
        // append some spaces on top for having symetrical view between front and rear
        for ($i = 0; $i < $nb_top_pdu; $i++) {
            echo "<div class='virtual_pdu_space'></div>";
        }
        echo '<ul class="indexes"></ul>
            <div class="grid-stack grid-rack"
                 id="grid-front"
                 gs-column="2"
                 gs-max-row="' . ($rack->fields['number_units'] + 1) . '">';

        if ($link->canCreate()) {
            echo '<div class="racks_add"></div>';
        }

        foreach ($data[Rack::FRONT] as $current_item) {
            echo self::getCell($current_item, !$canedit);
        }
        echo '</div>
            <ul class="indexes"></ul>';
        // append some spaces on bottom for having symetrical view between front and rear
        for ($i = 0; $i < $nb_bot_pdu; $i++) {
            echo "<div class='virtual_pdu_space'></div>";
        }
        echo '</div>
         </div>
         <div class="racks_col">
            <h2>' . __('Rear') . '</h2>';
        echo '<div class="rack_side rack_rear">';
        PDU_Rack::showVizForRack($rack, PDU_Rack::SIDE_TOP);
        PDU_Rack::showVizForRack($rack, PDU_Rack::SIDE_LEFT);
        echo '<ul class="indexes"></ul>
            <div class="grid-stack grid-rack"
                 id="grid2-rear"
                 gs-column="2"
                 gs-max-row="' . ($rack->fields['number_units'] + 1) . '">';

        if ($link->canCreate()) {
            echo '<div class="racks_add"></div>';
        }

        foreach ($data[Rack::REAR] as $current_item) {
            echo self::getCell($current_item, !$canedit);
        }
        echo '</div>
            <ul class="indexes"></ul>';
        PDU_Rack::showVizForRack($rack, PDU_Rack::SIDE_RIGHT);
        PDU_Rack::showVizForRack($rack, PDU_Rack::SIDE_BOTTOM);
        echo '</div>';
        echo '
         </div>
         <div class="racks_col">';
        self::showStats($rack);
        PDU_Rack::showStatsForRack($rack);
        echo '</div>'; // .racks_col
        echo '</div>'; // .racks_row
        echo '<div class="sep"></div>';
        echo "<div id='grid-dialog'></div>";
        echo "</div>"; // #viewgraph

        $rack_add_tip = __s('Insert an item here');
        $ajax_url     = $CFG_GLPI['root_doc'] . "/ajax/rack.php";

        echo Html::script('js/common.js');
        echo Html::script("vendor/wenzhixin/bootstrap-table/dist/bootstrap-table.min.js");
        $js = <<<JAVASCRIPT
      // init variables to pass to js/rack.js
      var grid_link_url      = "{$link->getFormURL()}";
      var grid_item_ajax_url = "{$ajax_url}";
      var grid_rack_id       = $ID;
      var grid_rack_units    = {$rack->fields['number_units']};
      var grid_rack_add_tip  = "{$rack_add_tip}";

      $(function() {
         // initialize grid with function defined in js/rack.js
         initRack();

         // drag&drop scenario for item_rack:
         // - we start by storing position before drag
         // - we send position to db by ajax after drag stop event
         // - if ajax answer return a fail, we restore item to the old position
         //   and we display a message explaning the failure
         // - else we move the other side of asset (if exists)
         $('.grid-stack.grid-rack')
            .on('change', function(event, items) {
               if (dirty) {
                  return;
               }
               var grid = event.target.gridstack;
               var is_rack_rear = $(grid.el).parents('.racks_col').hasClass('rack_rear');
               $.each(grid.getGridItems(), function(index, item) {
                  item = $(item);
                  const node = item[0].gridstackNode;
                  var is_half_rack = item.hasClass('half_rack');
                  var is_el_rear   = item.hasClass('rear');
                  var new_pos      = grid_rack_units - node.y - node.h + 1;
                  $.post(grid_item_ajax_url, {
                     id: node.id,
                     action: 'move_item',
                     position: new_pos,
                     hpos: getHpos(node.x, is_half_rack, is_rack_rear),
                  }, function(answer) {
                     // revert to old position
                     if (!answer.status) {
                        dirty = true;
                        grid.update(item.el, {x: x_before_drag, y: y_before_drag});
                        dirty = false;
                        displayAjaxMessageAfterRedirect();
                     } else {
                        // move other side if needed
                        var other_side_cls = $(item.el).hasClass('item_rear')
                           ? "item_front"
                           : "item_rear";
                        var other_side_el = $('.grid-stack-item.'+other_side_cls+'[gs-id='+node.id+']');

                        if (other_side_el.length) {
                           var other_side_grid = $(other_side_el).parent().get(0).gridstack;
                           new_x = node.x;
                           new_y = node.y;
                           if (node.w == 1) {
                              new_x = (node.x == 0 ? 1 : 0);
                           }
                           dirty = true;
                           other_side_grid.update(other_side_el, {x: new_x, y: new_y});
                           dirty = false;
                           document.location.reload();
                        }
                     }
                  }).fail(function() {
                     dirty = true;
                     grid.update(item.el, {x: x_before_drag, y: y_before_drag});
                     dirty = false;
                     displayAjaxMessageAfterRedirect();
                  });
               });
            })
      });
JAVASCRIPT;
        echo Html::scriptBlock($js);
    }

    /**
     * Display a mini stats block (wiehgt, power, etc) for the current rack instance
     * @param  Rack   $rack the current rack instance
     * @return void
     */
    public static function showStats(Rack $rack)
    {
        $items = self::getAdapter()->request([
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'racks_id' => $rack->getID()
           ]
        ]);

        $weight = 0;
        $power  = 0;
        $units  = [
           Rack::FRONT => array_fill(0, $rack->fields['number_units'], 0),
           Rack::REAR  => array_fill(0, $rack->fields['number_units'], 0),
        ];

        $rel = new self();
        while ($row = $items->fetchAssociative()) {
            $rel->getFromDB($row['id']);

            $item = new $row['itemtype']();
            $item->getFromDB($row['items_id']);

            $model_class = $item->getType() . 'Model';
            $modelsfield = strtolower($item->getType()) . 'models_id';
            $model = new $model_class();

            if ($model->getFromDB($item->fields[$modelsfield])) {
                $required_units = $model->fields['required_units'];

                for ($i = 0; $i < $model->fields['required_units']; $i++) {
                    $units[$row['orientation']][$row['position'] + $i] = 1;
                    if ($model->fields['depth'] == 1) {
                        $other_side = (int) !(bool) $row['orientation'];
                        $units[$other_side][$row['position'] + $i] = 1;
                    }
                }

                if (array_key_exists('power_consumption', $model->fields)) { // PDU does not consume energy
                    $power += $model->fields['power_consumption'];
                }

                $weight += $model->fields['weight'];
            } else {
                $units[Rack::FRONT][$row['position']] = 1;
                $units[Rack::REAR][$row['position']]  = 1;
            }
        }

        $nb_units = max(
            array_sum($units[Rack::FRONT]),
            array_sum($units[Rack::REAR])
        );

        $space_prct  = round(100 * $nb_units / max($rack->fields['number_units'], 1));
        $weight_prct = round(100 * $weight / max($rack->fields['max_weight'], 1));
        $power_prct  = round(100 * $power / max($rack->fields['max_power'], 1));

        echo "<div id='rack_stats' class='rack_side_block'>";

        echo "<h2>" . __("Rack stats") . "</h2>";

        echo "<div class='rack_side_block_content'>";
        echo "<h3>" . __("Space") . "</h3>";
        Html::progressBar('rack_space', [
           'create' => true,
           'percent' => $space_prct,
           'message' => $space_prct . "%",
        ]);

        echo "<h3>" . __("Weight") . "</h3>";
        Html::progressBar('rack_weight', [
           'create' => true,
           'percent' => $weight_prct,
           'message' => $weight . " / " . $rack->fields['max_weight']
        ]);

        echo "<h3>" . __("Power") . "</h3>";
        Html::progressBar('rack_power', [
           'create' => true,
           'percent' => $power_prct,
           'message' => $power . " / " . $rack->fields['max_power']
        ]);
        echo "</div>";
        echo "</div>";
    }

    public function showForm($ID, $options = [])
    {
        global $DB, $CFG_GLPI;

        $rack = new Rack();
        $rack->getFromDB($options['racks_id'] ?? $this->fields['racks_id']);

        $types = array_combine($CFG_GLPI['rackable_types'], $CFG_GLPI['rackable_types']);
        foreach ($types as $type => &$text) {
            $text = $type::getTypeName(1);
        }

        $used = $used_reserved = [];
        $request = $this::getAdapter()->request([
           'FROM' => $this->getTable()
        ]);
        while ($row = $request->fetchAssociative()) {
            $used[$row['itemtype']][] = $row['items_id'];
        }
        // find used pdu (not racked)
        foreach (PDU_Rack::getUsed() as $used_pdu) {
            $used['PDU'][] = $used_pdu['pdus_id'];
        }
        // get all reserved items
        $request = $this::getAdapter()->request([
           'FROM'  => $this->getTable(),
           'WHERE' => [
              'is_reserved' => true
           ]
        ]);
        while ($row = $request->fetchAssociative()) {
            $used_reserved[$row['itemtype']][] = $row['items_id'];
        }

        //items part of an enclosure should not be listed
        $request = $this::getAdapter()->request([
           'FROM'   => Item_Enclosure::getTable()
        ]);
        while ($row = $request->fetchAssociative()) {
            $used[$row['itemtype']][] = $row['items_id'];
        }

        $form = [
          'action' => $this->getFormURL(),
          'itemtype' => $this::class,
         'content' => [
              $this->getTypeName(1) => [
                  'visible' => true,
                  'inputs' => [
                      (isset($options['_onlypdu']) && $options['_onlypdu']) ? [
                          'type' => 'hidden',
                          'name' => 'itemtype',
                          'value' => 'PDU'
                      ] : [],
                      [
                          'type' => 'hidden',
                          'id' => 'used_input',
                          'name' => 'used',
                          'value' => json_encode($used)
                      ],
                      __('Item type') => (isset($options['_onlypdu']) && $options['_onlypdu']) ? [
                          'content' => PDU::getTypeName(1)
                      ] : [
                          'type' => 'select',
                          'id' => 'dropdown_itemtype',
                          'name' => 'itemtype',
                          'values' => [Dropdown::EMPTY_VALUE] + $types,
                          'value' => $this->fields["itemtype"] ?? 0,
                          'hooks' => [
                              'change' => <<<JS
                                const val = $('#dropdown_itemtype').val();
                                $('#dropdown_items_id').empty();
                                if (val == 0) {
                                    $('#dropdown_items_id').prop('disabled', true);
                                    return;
                                }
                                $.post({
                                    url: "{$CFG_GLPI["root_doc"]}/ajax/dropdownAllItems.php",
                                    data: {
                                        idtable: val,
                                        is_reserved: $('#dropdown_is_reserved').val(),
                                        used: $('#used_input').val(),
                                        entity_restrict: {$rack->fields['entities_id']}
                                    },
                                    success: function(data) {
                                        const json = JSON.parse(data);
                                        for (const [key, value] of Object.entries(json)) {
                                            if (typeof value === 'object') {
                                                const group = $('#dropdown_items_id').append($('<optgroup>').attr('label', key));
                                                for (const [k, v] of Object.entries(value)) {
                                                    group.append($('<option>').val(k).text(v));
                                                }
                                            } else {
                                                $('#dropdown_items_id').append($('<option>').val(key).text(value));
                                            }
                                        }
                                        $('#dropdown_items_id').prop('disabled', false);
                                    }
                                });
                            JS,
                          ]
                      ],
                      _n('Item', 'Items', 1) => [
                          'type' => 'select',
                          'id' => 'dropdown_items_id',
                          'name' => 'items_id',
                          'value' => $this->fields["items_id"] ?? 0,
                          'values' => isset($this->fields['itemtype']) && !empty($this->fields['itemtype'])
                              ? getItemByEntity(new $this->fields['itemtype'](), $this->fields['entities_id'])
                              : []
                      ],
                      Rack::getTypeName(1) => [
                          'type' => 'select',
                          'itemtype' => Rack::class,
                          'name' => 'racks_id',
                          'value' => $options["racks_id"] ?? $this->fields["racks_id"] ?? 0,
                      ],
                      __('Position') => [
                          'type' => 'number',
                          'name' => 'position',
                          'min' => 1,
                          'max' => $rack->fields['number_units'] ?? 1,
                          'step' => 1,
                          'value' => $options["position"] ?? $this->fields["position"] ?? 0,
                      ],
                      __('Orientation (front rack point of view)') => [
                          'type' => 'select',
                          'name' => 'orientation',
                          'values' => [
                              Rack::FRONT => __('Front'),
                              Rack::REAR  => __('Rear')
                          ],
                          'value' => $options["orientation"] ?? $this->fields["orientation"] ?? 0,
                      ],
                      __('Background color') => [
                          'type' => 'color',
                          'name' => 'bgcolor',
                          'value' => $options["bgcolor"] ?? $this->fields["bgcolor"] ?? '#69CEBA',
                      ],
                      __('Horizontal position (from rack point of view)') => [
                          'type' => 'select',
                          'name' => 'hpos',
                          'values' => [
                              Rack::POS_NONE  => __('None'),
                              Rack::POS_LEFT  => __('Left'),
                              Rack::POS_RIGHT => __('Right')
                          ],
                          'value' => $options["hpos"] ?? $this->fields["hpos"] ?? 0,
                      ],
                      __('Reserved position ?') => [
                          'type' => 'checkbox',
                          'name' => 'is_reserved',
                          'value' => $options["is_reserved"] ?? $this->fields["is_reserved"] ?? 0,
                      ]
                  ]
              ]
          ]
        ];
        renderTwigForm($form, '', $this->fields);

        echo "<tr class='tab_bg_1'>";
        echo "<td>";

        echo Html::scriptBlock("
         var toggleUsed = function(reserved) {
            if (reserved == 1) {
               $('#used_').val('" . json_encode($used_reserved) . "');
            } else {
               $('#used_').val('" . json_encode($used) . "');
            }
            // force change of itemtype dropdown to have a correct (with empty/filled used input)
            // filtered items list
            $('#dropdown_itemtype').trigger('change');
         }
      ");
        $entities = $rack->fields['entities_id'];
        if ($rack->fields['is_recursive']) {
            $entities = getSonsOf('glpi_entities', $entities);
        }
    }

    public function post_getEmpty()
    {
        $this->fields['bgcolor'] = '#69CEBA';
    }

    /**
     * Get cell content
     *
     * @param mixed $cell Rack cell (array or false)
     *
     * @return string
     */
    private static function getCell($cell, $readonly = false)
    {
        if ($cell) {
            $item        = $cell['item'];
            $gs_item     = $cell['gs_item'];
            $name        = $gs_item['name'];
            $typename    = is_object($item)
                            ? $item->getTypeName()
                            : "";
            $serial      = is_object($item)
                            ? $item->fields['serial']
                            : "";
            $otherserial = is_object($item)
                            ? $item->fields['otherserial']
                            : "";
            $model       = is_object($item)
                           && is_object($item->model)
                           && isset($item->model->fields['name'])
                            ? $item->model->fields['name']
                            : '';
            $rear        = $gs_item['rear'];
            $back_class  = $rear
                            ? "item_rear"
                            : "item_front";
            $half_class  = $gs_item['half_rack']
                            ? "half_rack"
                            : "";
            $reserved    = $gs_item['reserved'];
            $reserved_cl = $reserved
                            ? "reserved"
                            : "";
            $icon        = $reserved
                            ? self::getItemIcon("Reserved")
                            : self::getItemIcon(get_class($item));
            $bg_color    = $gs_item['bgcolor'];
            if ($item->maybeDeleted() && $item->isDeleted()) {
                $bg_color = '#ff0000'; //red for deleted items
            }
            $fg_color    = !empty($bg_color)
                            ? Html::getInvertedColor($gs_item['bgcolor'])
                            : "";
            $fg_color_s  = "color: $fg_color;";
            $img_class   = "";
            $img_s       = "";
            if ($gs_item['picture_f'] && !$rear && !$reserved) {
                $img_s = "background: $bg_color url(\"" . $gs_item['picture_f'] . "\")  no-repeat top left/100% 100%;";
                $img_class = 'with_picture';
            }
            if ($gs_item['picture_r'] && $rear && !$reserved) {
                $img_s = "background: $bg_color url(\"" . $gs_item['picture_r'] . "\")  no-repeat top left/100% 100%;";
                $img_class = 'with_picture';
            }

            $tip = "<span class='tipcontent'>";
            $tip .= "<span>
                  <label>" .
                     ($rear
                        ? __("asset rear side")
                        : __("asset front side")) . "
                  </label>
               </span>";
            if (!empty($typename)) {
                $tip .= "<span>
                     <label>" . _n('Type', 'Types', 1) . ":</label>
                     $typename
                  </span>";
            }
            if (!empty($name)) {
                $tip .= "<span>
                     <label>" . __('name') . ":</label>
                     $name
                  </span>";
            }
            if (!empty($serial)) {
                $tip .= "<span>
                     <label>" . __('serial') . ":</label>
                     $serial
                  </span>";
            }
            if (!empty($otherserial)) {
                $tip .= "<span>
                     <label>" . __('Inventory number') . ":</label>
                     $otherserial
                  </span>";
            }
            if (!empty($model)) {
                $tip .= "<span>
                     <label>" . __('model') . ":</label>
                     $model
                  </span>";
            }

            $tip .= "</span>";

            $readonly_attr = $readonly ? 'gs-no-move="true"' : '';
            return "
         <div class='grid-stack-item $back_class $half_class $reserved_cl $img_class'
               gs-width='{$gs_item['width']}' gs-h='{$gs_item['height']}'
               gs-x='{$gs_item['x']}' gs-y='{$gs_item['y']}'
               gs-id='{$gs_item['id']}' {$readonly_attr}
               style='background-color: $bg_color; color: $fg_color;'>
            <div class='grid-stack-item-content' style='$fg_color_s $img_s'>
               $icon" .
                  (!empty($gs_item['url'])
                     ? "<a href='{$gs_item['url']}' class='itemrack_name' style='$fg_color_s'>{$gs_item['name']}</a>"
                     : "<span class='itemrack_name'>" . $gs_item['name'] . "</span>") . "
               <a href='{$gs_item['rel_url']}'>
                  <i class='fa fa-pencil-alt rel-link'
                     style='$fg_color_s'
                     title='" . __("Edit rack relation") . "'></i>
               </a>
               $tip
            </div>
         </div>";
        }

        return false;
    }


    /**
     * Return an i html tag with a dedicated icon for the itemtype
     * @param  string $itemtype  A rackable itemtype
     * @return string           The i html tag
     */
    private static function getItemIcon($itemtype = "")
    {
        $icon = "";
        switch ($itemtype) {
            case "Computer":
                $icon = "fas fa-server";
                break;
            case "Reserved":
                $icon = "fas fa-lock";
                break;

            default:
                $icon = $itemtype::getIcon();
                break;
        }

        if (!empty($icon)) {
            $icon = "<i class='item_rack_icon $icon' aria-hidden='true'></i>";
        }

        return $icon;
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

        $itemtype    = !$this->isNewItem() ? $this->fields['itemtype'] : null;
        $items_id    = !$this->isNewItem() ? $this->fields['items_id'] : null;
        $racks_id    = !$this->isNewItem() ? $this->fields['racks_id'] : null;
        $position    = !$this->isNewItem() ? $this->fields['position'] : null;
        $hpos        = !$this->isNewItem() ? $this->fields['hpos'] : null;
        $orientation = !$this->isNewItem() ? $this->fields['orientation'] : null;

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
            ($this->isNewItem() && (!isset($input['racks_id']) || empty($input['racks_id'])))
            || (isset($input['racks_id']) && empty($input['racks_id']))
        ) {
            $error_detected[] = __('A rack is required');
        }
        if (
            ($this->isNewItem() && (!isset($input['position']) || empty($input['position'])))
            || (isset($input['position']) && empty($input['position']))
        ) {
            $error_detected[] = __('A position is required');
        }

        if (isset($input['itemtype'])) {
            $itemtype = $input['itemtype'];
        }
        if (isset($input['items_id'])) {
            $items_id = $input['items_id'];
        }
        if (isset($input['racks_id'])) {
            $racks_id = $input['racks_id'];
        }
        if (isset($input['position'])) {
            $position = $input['position'];
        }
        if (isset($input['hpos'])) {
            $hpos = $input['hpos'];
        }
        if (isset($input['orientation'])) {
            $orientation = $input['orientation'];
        }

        if (!count($error_detected)) {
            //check if required U are available at position
            $rack = new Rack();
            $rack->getFromDB($racks_id);

            if ($this->isNewItem()) {
                $filled = $rack->getFilled();
            } else {
                // If object is existing, exclude current state from used positions
                $filled = $rack->getFilled($this->fields['itemtype'], $this->fields['items_id']);
            }

            $item = new $itemtype();
            $item->getFromDB($items_id);
            $model_class = $item->getType() . 'Model';
            $modelsfield = strtolower($item->getType()) . 'models_id';
            $model = new $model_class();
            if ($model->getFromDB($item->fields[$modelsfield])) {
                $item->model = $model;
            } else {
                $item->model = null;
            }

            $required_units = 1;
            $width          = 1;
            $depth          = 1;
            if ($item->model != null) {
                if ($item->model->fields['required_units'] > 1) {
                    $required_units = $item->model->fields['required_units'];
                }
                if ($item->model->fields['is_half_rack'] == 1) {
                    if ($this->isNewItem() && !isset($input['hpos']) || $input['hpos'] == 0) {
                        $error_detected[] = __('You must define an horizontal position for this item');
                    }
                    $width = 0.5;
                }
                if ($item->model->fields['depth'] != 1) {
                    if ($this->isNewItem() && !isset($input['orientation'])) {
                        $error_detected[] = __('You must define an orientation for this item');
                    }
                    $depth = $item->model->fields['depth'];
                }
            }

            if (
                $position > $rack->fields['number_units'] ||
                $position + $required_units  > $rack->fields['number_units'] + 1
            ) {
                $error_detected[] = __('Item is out of rack bounds');
            } elseif (!count($error_detected)) {
                $i = 0;
                while ($i < $required_units) {
                    $current_position = $position + $i;
                    if (isset($filled[$current_position])) {
                        $content_filled = $filled[$current_position];

                        if ($hpos == Rack::POS_NONE || $hpos == Rack::POS_LEFT) {
                            $d = 0;
                            while ($d / 4 < $depth) {
                                $pos = ($orientation == Rack::REAR) ? 3 - $d : $d;
                                $val = 1;
                                if (isset($content_filled[Rack::POS_LEFT][$pos]) && $content_filled[Rack::POS_LEFT][$pos] != 0) {
                                    $error_detected[] = __('Not enough space available to place item');
                                    break 2;
                                }
                                ++$d;
                            }
                        }

                        if ($hpos == Rack::POS_NONE || $hpos == Rack::POS_RIGHT) {
                            $d = 0;
                            while ($d / 4 < $depth) {
                                $pos = ($orientation == Rack::REAR) ? 3 - $d : $d;
                                $val = 1;
                                if (isset($content_filled[Rack::POS_RIGHT][$pos]) && $content_filled[Rack::POS_RIGHT][$pos] != 0) {
                                    $error_detected[] = __('Not enough space available to place item');
                                    break 2;
                                }
                                ++$d;
                            }
                        }
                    }
                    ++$i;
                }
            }
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

    protected function computeFriendlyName()
    {
        $rack = new Rack();
        $rack->getFromDB($this->fields['racks_id']);
        $name = sprintf(
            __('Item for rack "%1$s"'),
            $rack->getName()
        );

        return $name;
    }
}
