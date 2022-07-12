/**
 * ---------------------------------------------------------------------
 * ITSM-NG 
 * Copyright (C) 2022 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org/
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ITSM-NG.
 *
 * ITSM-NG is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ITSM-NG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ITSM-NG. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

var modalWindow;
var modalTask;

$(function() {
   modalWindow = $("<div></div>").dialog({
      resizable: true,
      width: '300',
      autoOpen: false,
      height: '150',
      modal: true,
      position: {
         my: 'center'
      },
      open: function( event, ui ) {
         //remove existing tinymce when reopen modal (without this, tinymce don't load on 2nd opening of dialog)
         modalWindow.find('.mce-container').remove();
      }
   });
});

var specialstatus = new function() {
 
    this.showStatusModal = function (idint) {
       var RegexUrl = /^(.*)front\/.*\.php/;
       var RegexUrlRes = RegexUrl.exec(window.location.pathname);
         id = idint.toString();
       modalWindow.load(
          RegexUrlRes[1]+'ajax/specialstatus.php?status=delete&id='+id
       ).dialog('open');
    }
} 