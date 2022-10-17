<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org
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

class Accessibility extends CommonDBTM {
    static function getTypeName($nb = 0) {
        return __("Accessibility");
    }

    function getRights($interface = 'central') {
        $values[READ] = ["short" => __("Read"),
            "long" => __("See this parameter in your settings")];
        $values[UPDATE] = ["short" => __("Edit"),
            "long" => __("Edit this parameter in your settings")];

        return $values;
    }

    /*************************************** TABS ********************************************/

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

        switch ($item->getType()) {
            case 'Preference' :
                return __('Accessibility');

            case 'User' :
                if (User::canUpdate()
                    && $item->currentUserHaveMoreRightThan($item->getID())) {
                    return __('Accessibility');
                }
                break;
            case Impact::getType():
                return Impact::getTypeName();
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        global $CFG_GLPI;

        if ($item->getType() == 'Preference') {
            $config = new self();
            $user   = new User();
            if ($user->getFromDB(Session::getLoginUserID())) {
                $user->computePreferences();
                $config->showAccessForm($user->fields);
            }

        } else if ($item->getType() == 'User') {
            $config = new self();
            $item->computePreferences();
            $config->showAccessForm($item->fields);
        }
        return true;
    }

    /*************************************** FORM ********************************************/

    function showAccessForm($data = []) {
        global $CFG_GLPI, $DB;

        $userpref  = false;
        $url       = Toolbox::getItemTypeFormURL(__CLASS__);
        $rand      = mt_rand();

        $canedit = Config::canUpdate();
        $canedituser = Session::haveRight('accessibility', UPDATE);
        if (array_key_exists('last_login', $data)) {
            $userpref = true;
            if ($data["id"] === Session::getLoginUserID()) {
                $url  = $CFG_GLPI['root_doc']."/front/accessibility.form.php";
            } else {
                $url  = Accessibility::getFormURL();
            }
        }

        if ((!$userpref && $canedit) || ($userpref && $canedituser)) {
            echo "<form name='form' action='$url' method='post' data-track-changes='true'>";
        }

        if ($userpref) {
            echo "<input type='hidden' name='id' value='".$data['id']."'>";
        }
        echo "<div class='center' id='tabsbody'>";
        echo "<table class='tab_cadre_fixe'>";

        echo "<tr><th colspan='4'>" . __('Interface') . "</th></tr>";
        echo "<td><label for='access_zoom_level_drop$rand'>" .__('UI Scale') . "</label></td>";
        $zooms = [
            100 => '100%',
            110 => '110%',
            120 => '120%',
            130 => '130%',
            140 => '140%',
            150 => '150%',
            160 => '160%',
            170 => '170%',
            180 => '180%',
            190 => '190%',
            200 => '200%'
        ];
        echo "<td>";
        Dropdown::showFromArray('access_zoom_level', $zooms, ['value' => $data["access_zoom_level"], 'rand' => $rand]);
        echo "</td></tr>";

        echo "<td><label for='access_font_drop$rand'>" .__('UI Font') . "</label></td>";
        $fonts = [
            ""                  => "Default",
            "OpenDyslexic"      => "Open Dyslexic Regular",
            "OpenDyslexicAlta"  => "Open Dyslexic Alta",
            "Tiresias Infofont" => "Tiresias Infofont"
        ];
        echo "<td>";
        Dropdown::showFromArray('access_font', $fonts, ['value' => $data["access_font"], 'rand' => $rand]);
        echo "</td></tr>";

        echo "<td><label for='access_shortcuts_drop$rand'>" .__('Enable shortcuts') . "</label></td>";
        echo "<td>";
        Dropdown::showYesNo('access_shortcuts', $data["access_shortcuts"], -1,['rand' => $rand]);
        echo "</td></tr>";

        echo "<tr><th colspan='4'>" . __('Shortcuts') . " <a style='position: absolute; right: 25px; cursor: pointer; user-select: none;' onclick='\$(\".togshortcuts\").toggle(400);'>[toggle view]</a></th></tr>";

        $shortcuts = json_decode($data["access_custom_shortcuts"], true);
        $cpt = 1;
        $classes = [];
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, "CommonGLPI")) {
                $tabs = array($class => $class::getTypeName());
                $classes = array_merge($classes, $tabs);
                
            }
        }
        unset($classes["no_all_tab"]); // Remove superfluous element
        ksort($classes);
        foreach($classes as $tab => $display){
            $shortcut = $shortcuts[$tab];
            if (!$shortcut) {
                $shortcut = __("shif+alt+" .$cpt);
            
            }

            echo "<tr class='togshortcuts' style='display: none;' enctype='application/json'>";
            echo "<input type='hidden' id='$tab' name='$tab' value='$shortcut' >";
            echo "<td><label for='$tabs$rand'>" . $display . "</label></td>";
            echo "<td>";
            if (!is_array($shortcut)) {
                $shortcutHtml = "<kbd>$shortcut</kbd>";
            } else {
                $shortcutHtml = "<kbd>".implode("</kbd>+<kbd>", $shortcut)."</kbd>";
            }
           
            echo "<span class='$tab' name='$tab' style='cursor: pointer' onclick='myFunction($tab)'>$shortcutHtml</span>"; // Clicking this should edit the value in the hidden input for the HTML form.           
            echo "</td></tr>";
            $cpt++;

        }
                $js=<<<JAVASCRIPT
            function myFunction(rack) {
                var entity_element = $(this);

                let id_span = document.getElementsByClassName(rack.id)[0]; //the input hidden
                let id_input_hidden  = document.getElementById(rack.name); //the span 
                id_input_hidden.value = "";
                
                x = document.getElementById("popupForm"); //The popup
                if(x.style.display === "none"){
                    x.style.display = "block";
                    document.addEventListener('keydown', getShortcut); // Instanciation get short 
                } else {
                    x.style.display = "none";
 
                }
                
                let log="";
                //Function to manage events
                function getShortcut(event){
                    event.preventDefault();                 
                    const element = document.getElementById("saveShortcut");
                    log += event.key;
                    document.getElementById("shortcut_added").innerHTML = log;
                    log +="+";
                    // Set the custom shortcut in currents fields
                    element.addEventListener("click", function() {
                        id_input_hidden.value=log.slice(0 , -1); //Remove(slice) the last + before updating
                        id_span.innerHTML = "<kbd>"+log.slice(0 , -1)+"<kbd>";
  
                        x.style.display = "none";
                        document.removeEventListener('keydown', getShortcut);
                        document.getElementById("shortcut_added").innerHTML ="";
                
                    });
                }
            }; 
        JAVASCRIPT;
        echo Html::scriptBlock($js);

        echo "<div id='popupForm' style='display: none;position: fixed;left: 50%;top: 50%;transform: translate(-45%, 5%);border: 2px solid #666;z-index: 9;padding: 50px;background-color: rgb(228, 216, 216);opacity: 0.8;width: 20%;height: 10%;text-align: center;'>
        <H3> Enter your shortcut</H3>
        <p id='shortcut_added'></p>
        <span style='cursor: pointer; background-color: rgb(131, 77, 77); color: white' id='saveShortcut'>Update</span>
       
        </div>";
 

        if ((!$userpref && $canedit) || ($userpref && $canedituser)) {
            echo "<tr class='tab_bg_2'>";
            echo "<td colspan='4' class='center'>";
            echo "<input type='submit' name='update' class='submit' value=\""._sx('button', 'Save')."\">";
            echo "</td></tr>";
        }

        echo "</table></div>";
        Html::closeForm();
    }
}
