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
$(document).ready(function() {
  var url = window.location.href;

  if(!url.includes("front/login.php")) {
    $.get(CFG_GLPI.root_doc+'/ajax/hotkeys.php', {

    }, function(html) {
      $(document.body).append(html);
    });
  }
});
/**
*
* @param {String} shortcut to be handling
* @param {function} callback function to make the redirection
* @param {Array} opt
*/
function hotkeys(shortcut,callback,opt) {
  //Provide a set of default options
  var default_options = {
    'type':'keydown',
    'target':document
  };
  if(!opt) opt = default_options;
  else {
    for(var dfo in default_options) {
      if(typeof opt[dfo] == 'undefined') opt[dfo] = default_options[dfo];
    }
  }

  var ele = opt.target;
  if(typeof opt.target == 'string') ele = document.getElementById(opt.target);
  var ths = this;

  //The function to be called at keypress
  var func = function(e) {
    e = e || window.event;

    //Find Which key is pressed
    if (e.keyCode) code = e.keyCode;
    else if (e.which) code = e.which;
    var character = String.fromCharCode(code).toLowerCase();

    var keys = shortcut.toLowerCase().split("+");
    //Key Pressed - counts the number of valid keypresses - if it is same as the number of keys, the shortcut function is invoked
    var kp = 0;

    //Work around for stupid Shift key bug created by using lowercase - as a result the shift+num combination was broken
    var shift_nums = {
      "`":"~",
      "1":"!",
      "2":"@",
      "3":"#",
      "4":"$",
      "5":"%",
      "6":"^",
      "7":"&",
      "8":"*",
      "9":"(",
      "0":")",
      "-":"_",
      "=":"+",
      ";":":",
      "'":"\"",
      ",":"<",
      ".":">",
      "/":"?"
    };
    //Special Keys - and their codes
    var special_keys = {
      'esc':27,
      'escape':27,
      'tab':9,
      'space':32,
      'return':13,
      'enter':13,
      'backspace':8,

      'scrolllock':145,
      'scroll_lock':145,
      'scroll':145,
      'capslock':20,
      'caps_lock':20,
      'caps':20,
      'numlock':144,
      'num_lock':144,
      'num':144,

      'pause':19,
      'break':19,

      'insert':45,
      'home':36,
      'delete':46,
      'end':35,

      'pageup':33,
      'page_up':33,
      'pu':33,

      'pagedown':34,
      'page_down':34,
      'pd':34,

      'left':37,
      'up':38,
      'right':39,
      'down':40,

      'f1':112,
      'f2':113,
      'f3':114,
      'f4':115,
      'f5':116,
      'f6':117,
      'f7':118,
      'f8':119,
      'f9':120,
      'f10':121,
      'f11':122,
      'f12':123
    };


    for(var i=0; k=keys[i],i<keys.length; i++) {
      //Modifiers
      if(k == 'ctrl' || k == 'control') {
        if(e.ctrlKey) kp++;

      } else if(k ==  'shift') {
        if(e.shiftKey) kp++;

      } else if(k == 'alt') {
        if(e.altKey) kp++;

      } else if(k.length > 1) { //If it is a special key
        if(special_keys[k] == code) kp++;

      } else { //The special keys did not match
        if(character == k) kp++;
        else {
          if(shift_nums[character] && e.shiftKey) { //Stupid Shift key bug created by using lowercase
            character = shift_nums[character];
            if(character == k) kp++;
          }
        }
      }
    }

    if(kp == keys.length) {
      callback(e);
    }
  };

  //Attach the function with the event
  if(ele.addEventListener) ele.addEventListener(opt['type'], func, false);
  else if(ele.attachEvent) ele.attachEvent('on'+opt['type'], func);
  else ele['on'+opt['type']] = func;
}
