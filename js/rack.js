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

/* global grid_link_url, grid_rack_add_tip, grid_rack_id, grid_rack_units */
var x_before_drag = 0;
var y_before_drag = 0;
var dirty = false;

var initRack = function() {
   // grid events
   $(document)
      .on("click", "#sviewlist", function() {
         $('#viewlist').show();
         $('#viewgraph').hide();
         $(this).addClass('selected');
         $('#sviewgraph').removeClass('selected');
      })
      .on("click", "#sviewgraph", function() {
         $('#viewlist').hide();
         $('#viewgraph').show();
         $(this).addClass('selected');
         $('#sviewlist').removeClass('selected');
      })
      .on("click", "#toggle_images", function() {
         $('#toggle_text').toggle();
         $(this).toggleClass('active');
         $('#viewgraph').toggleClass('clear_picture');
      })
      .on("click", "#toggle_text", function() {
         $(this).toggleClass('active');
         $('#viewgraph').toggleClass('clear_text');
      })
      .on("click", ".cell_add", function() {
         var index = grid_rack_units - $(this).index();
         var side = $(this).parents('.rack_side').hasClass('rack_front')
            ? 0  // front
            : 1; // rear

         $.ajax({
            url : grid_link_url,
            data: {
               racks_id: grid_rack_id,
               orientation: side,
               position: index,
               ajax: true
            },
            success: function(data) {
               $('#grid-dialog')
                  .html(data)
                  .dialog({
                     modal: true,
                     width: 'auto'
                  });
            }
         });
      });

   // use each to re-init options for each grid
   GridStack.initAll({
      cellHeight: 20,
      float: true,
      columnOpts: { breakpoints: [{w: 768, c:1}] },
      animate: true,
      disableResize: true,
      minRow: grid_rack_units,
      maxRow: grid_rack_units,
      draggable: {
         containment: '.grid-stack',
         scroll: true
      }
   });

   $('.grid-stack')
      .on('dragstart', function(event) {
         var element = $(event.target);
         var node    = element.data('_gridstack_node');

         // store position before drag
         x_before_drag = Number(node.x);
         y_before_drag = Number(node.y);

         // disable qtip
         element.qtip('hide', true);
      })
      .on('click', '.grid-stack-item', function(event) {
         var element = $(event.target);
         var el_url  = element.find('.itemrack_name').attr('href');

         if (el_url) {
            window.location = el_url;
         }
      });

   for (var i = grid_rack_units; i >= 1; i--) {
      // add index number front of each rows
      $('.indexes').append('<li>' + i + '</li>');

      // append cells for adding new items
      $('.racks_add').append(
         '<div class="cell_add"><span class="tipcontent">'+grid_rack_add_tip+'</span></div>'
      );
   }

   // lock all item (prevent pushing down elements)
  $('.grid-stack').each(function (idx, gsEl) {
    const gridstack = $(gsEl).data('gridstack');
    if (gridstack) {
      gridstack.disableDragAndDrop('.grid-stack-item');
    }
  });
};


var getHpos = function(x, is_half_rack, is_rack_rear) {
   if (!is_half_rack) {
      return 0;
   } else if (x == 0) {
      return is_rack_rear ? 2 : 1;
   } else if (x == 1) {
      return is_rack_rear ? 1 : 2;
   }
};
