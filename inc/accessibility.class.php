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

class Accessibility extends CommonDBTM
{
    public static function getTypeName($nb = 0)
    {
        return __("Accessibility");
    }

    public function getRights($interface = 'central')
    {
        $values[READ] = ["short" => __("Read"),
            "long" => __("See this parameter in your settings")];
        $values[UPDATE] = ["short" => __("Edit"),
            "long" => __("Edit this parameter in your settings")];

        if ($interface == 'helpdesk') {
            unset($values[UPDATE], $values[READ]);
        }

        return $values;
    }

    /*************************************** TABS ********************************************/

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'Preference':
                return __('Accessibility');

            case 'User':
                if (
                    User::canUpdate()
                    && $item->currentUserHaveMoreRightThan($item->getID())
                ) {
                    return __('Accessibility');
                }
                break;
            case Impact::getType():
                return Impact::getTypeName();
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        global $CFG_GLPI;

        if ($item->getType() == 'Preference') {
            $config = new self();
            $user   = new User();
            if ($user->getFromDB(Session::getLoginUserID())) {
                $user->computePreferences();
                $config->showAccessForm($user->fields);
            }
        } elseif ($item->getType() == 'User') {
            $config = new self();
            $user   = new User();
            $user->computePreferences();
            $config->showAccessForm($item->fields, true);
        }
        return true;
    }

    /*************************************** FORM ********************************************/

    public function showAccessForm($data = [], $displayShortcut = true)
    {
        global $CFG_GLPI, $DB;

        $user = new User();
        $user->getFromDB(session::getLoginUserID());

        $userpref  = false;
        $url       = Toolbox::getItemTypeFormURL(__CLASS__);
        $rand      = mt_rand();

        $canedit = Config::canUpdate();
        $canedituser = Session::haveRight('accessibility', UPDATE);

        if (array_key_exists('last_login', $data)) {
            $userpref = true;
            if ($data["id"] === Session::getLoginUserID()) {
                $url  = $CFG_GLPI['root_doc'] . "/front/accessibility.form.php";
            } else {
                $url  = Accessibility::getFormURL();
            }
        }

        if ((!$userpref && $canedit) || ($userpref && $canedituser)) {
            echo "<form aria-label='form' name='form' action='$url' method='post' data-track-changes='true'>";
        }

        if ($userpref) {
            echo "<input type='hidden' name='id' value='" . $data['id'] . "'>";
        }

        echo "<div class='center' id='tabsbody'>";
        echo "<table class='tab_cadre_fixe' style='position: relative' aria-label='Accessibility settings'>";
        echo "<tr><th colspan='4'>" . __('Interface') . "</th></tr>";
        echo "<tr class='tab_bg_1' >";
        echo "<td width='40%'><label for='access_zoom_level_drop$rand'>" . __('UI Scale') . "</label></td>";

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

        echo "<td width='40%'>";
        Dropdown::showFromArray('access_zoom_level', $zooms, ['value' => $data["access_zoom_level"], 'rand' => $rand]);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1' >";
        echo "<td width='40%'><label for='access_font_drop$rand'>" . __('UI Font') . "</label></td>";

        $fonts = [
            ""                  => "Default",
            "OpenDyslexic"      => "Open Dyslexic Regular",
            "OpenDyslexicAlta"  => "Open Dyslexic Alta",
            "Tiresias Infofont" => "Tiresias Infofont"
        ];

        echo "<td width='40%'>";
        Dropdown::showFromArray('access_font', $fonts, ['value' => $data["access_font"], 'rand' => $rand]);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1' >";
        echo "<td width='40%'><label for='access_shortcuts_drop$rand'>" . __('Enable shortcuts') . "</label></td>";
        echo "<td width='40%'>";
        Dropdown::showYesNo('access_shortcuts', $data["access_shortcuts"], -1, ['rand' => $rand]);
        echo "</td></tr>";

        if ($displayShortcut) {
            echo "<tr><th colspan='4'>" . __('Shortcuts') . "<span id ='alert_save' style='display:none; position: absolute; left: 50.5%; color: #ae0c2a; '><i>" . __("Don't forget to save") . "</i></span>" . " <a style='position: absolute; right: 25px; cursor: pointer; user-select: none;' onclick='\$(\".togshortcuts\").toggle(400);'>[" . __("Toggle view") . "]</a></th></tr>";

            if (is_null($data["access_custom_shortcuts"])) {
                $shortcuts = [];
            } else {
                $shortcuts = json_decode($data["access_custom_shortcuts"], true);
            }
            $font = $user->fields["access_font"];
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

            foreach ($classes as $tab => $display) {
                $shortcut = $shortcuts[$tab] ?? __("shift+alt+" . $cpt);

                echo "<tr class='togshortcuts' style='display: none;' enctype='application/json'>";
                echo "<input type='hidden' id='$tab' name='$tab' value='$shortcut' >";
                echo "<td width='40%'><label for='$tab$rand'>" . $display . "</label></td>";
                echo "<td width='40%'>";

                if (!is_array($shortcut)) {
                    $shortcutHtml = "<kbd style='font-family:$font'>$shortcut</kbd>";
                } else {
                    $shortcutHtml = "<kbd style='font-family:$font'>" . implode("</kbd>+<kbd>", $shortcut) . "</kbd>";
                }

                Html::accessibilityHeader();
                echo "<span class='$tab' name='$tab' style='cursor: pointer;' onclick='myFunction($tab)'>$shortcutHtml</span>"; // Clicking this should edit the value in the hidden input for the HTML form.
                echo "&nbsp;";
                echo "<span id='infoBulle_$tab' style='background: orange; position: absolute;  height: 14.7px; margin-top: 0.2px;color: orange; border-radius: 3px;'></span>";
                echo "</td></tr>";
                $cpt++;
            }


            $currentShortcut = json_decode((string) $user->fields["access_custom_shortcuts"], true);

            unset($currentShortcut["DCRoom"]);
            unset($currentShortcut["update"]);
            $all_shotcuts = array();
            $all_classes = array();

            if (!is_null($currentShortcut)) {
                foreach ($currentShortcut as $name => $shortcut) {
                    if (is_subclass_of($name, "CommonGLPI")) {
                        $url = Toolbox::getItemTypeFormURL($name);
                        array_push($all_shotcuts, $shortcut);
                        array_push($all_classes, $name);
                    }
                }
            }

            echo Html::scriptBlock('
            
            function myFunction(rack) {
                    var entity_element = $(this);
                    let all_shotcuts = ' . json_encode($all_shotcuts) . '; // Retrieve all shortcuts
                    let all_classes  = ' . json_encode($all_classes) . ';  // Retrieve all classes
                    
                    let id_span = document.getElementsByClassName(rack.id)[0]; // the span 
                    let id_input_hidden  = document.getElementById(rack.name); // the input hidden
               
                    
                    let alertSave = document.getElementById("alert_save"); // message do not forget to save the form
                    
                    modal = document.getElementById("modalForm"); //The modal
                    if(modal.style.display === "none"){
                        modal.style.display = "block";
                        document.addEventListener(' . "'keydown'" . ', getShortcut); // Instanciation get short 
                          
                    } else {
                        modal.style.display = "none";
     
                    }
                
                   
                    let keyPressed="";
                    let btnClose = document.getElementById("btnClose"); // Close boutton in the modal
                    btnClose.addEventListener("click", function() {
                        modal.style.display = "none";
                        // Remove all text in the modal
                        keyPressed="";
                        document.getElementById("shortcut_added").innerHTML =""; //
                        document.getElementById("shortcut_existant").innerHTML ="";
                        document.removeEventListener(' . "'keydown'" . ', getShortcut);
                    });
                    
                    
                    
                    let id_infoBulle ="infoBulle_"+rack.name;
                    let btn_infoBulle = document.getElementById(id_infoBulle); // info bulle
                    
                    function getShortcut(event){
                        event.preventDefault();                 
                        const btn_submit_in_modal = document.getElementById("submit_in_modal");
                        keyPressed += event.key;
                        document.getElementById("shortcut_added").innerHTML = keyPressed;
                        document.getElementById("shortcut_existant").innerHTML ="";
                        keyPressed +="+";
                        var testExistShortcut = 0;

                        if(all_shotcuts.includes(keyPressed.slice(0, -1))){
                            testExistShortcut++;
                        }

                        if(testExistShortcut == 0){
                            // Set the custom shortcut in currents fields
                            btn_submit_in_modal.addEventListener("click", function() {
                                if(keyPressed != ""){
                                    id_input_hidden.value=keyPressed.slice(0 , -1);   //Remove(slice) the last + before updating
                                    id_span.innerHTML = "<kbd>"+keyPressed.slice(0 , -1)+"</kbd>";
            
                                    modal.style.display = "none";
                                    document.removeEventListener(' . "'keydown'" . ', getShortcut);
                                    document.getElementById("shortcut_added").innerHTML ="";
                                    btn_infoBulle.innerHTML ="&nbsp;&nbsp;&nbsp;";
                                    alertSave.style.display = "inline";
                                }
                                keyPressed="";
                        
                            });
                        } else {
                            document.getElementById("shortcut_existant").innerHTML ="Already exist enter another shortcut";
                            keyPressed="";
                        }  
                    }
                };
            ');
        }

        if ((!$userpref && $canedit) || ($userpref && $canedituser)) {
            echo "<tr class='tab_bg_2'>";
            echo "<td colspan='4' class='center'>";
            echo "<input type='submit' name='update' class='submit' value=\"" . _sx('button', 'Save') . "\">";
            echo "</td></tr>";
        }

        echo "</table></div>";
        Html::closeForm();

        if ($displayShortcut) {
            echo "<div id='modalForm'  tabindex='-1' role='dialog' class='ui-dialog ui-corner-all ui-widget ui-widget-content ui-front ui-draggable ui-resizable' style='position: fixed; height: 150px; width: 300px; top: 30%; left: 40%; display:none;' >";

            echo "<div class='ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix ui-draggable-handle'>";
            echo "<span id='ui-id-8' class='ui-dialog-title'>Enter your shortcut &nbsp;</span>";
            echo "<button type='button' id='btnClose'class='ui-button ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close' title='Close'>";
            echo "<span class='ui-button-icon ui-icon ui-icon-closethick'></span>";
            echo "<span class='ui-button-icon-space'> </span>";
            echo "Close";
            echo "</button>";
            echo "</div>";

            echo "<div class='spaced'>";
            echo "<table class='tab_cadre_fixe' style=' height: 120px; overflow-y: scroll;' aria-label='Shortcuts'>";
            echo "<tr >";
            echo "<td width='100%' style='position: absolute;  left: 50%; transform: translate(-50%, 0%);'>";
            echo "<p style='width: 100%; overflow-wrap: break-word;' class='center' id='shortcut_added'></p>";
            echo "</td></tr>";

            echo "<tr >";
            echo "<td width='100%' style='position: absolute; left: 50%; transform: translate(-50%, 0%);'>
                <p  id='shortcut_existant' class='center' style='color: red; width: 100%;'></p>
            </td>";
            echo "</tr>";

            echo "<tr >";
            echo "<td  style='position: absolute; margin: 0; left: 50%; transform: translate(-50%, 0%); '>";
            echo "<input type='submit'  id='submit_in_modal'  name='submit_in_modal' class='vsubmit' value=\"" . _sx('button', 'Update') . "\">";
            echo "</td>";
            echo "</tr>";

            echo "</table>";
            echo "</div>";
            echo "</div>";
        }
    }
}
