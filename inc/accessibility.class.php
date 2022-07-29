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

        $classes = [];
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, "CommonGLPI")) {
                $reflector = new ReflectionMethod($class, 'defineTabs');
                if ($reflector->getDeclaringClass()->getName() !== $class) continue;
                $item = @getItemForItemtype($class);

                if ($item) {
                    try {
                        $tabs = @$item->defineTabs(["id" => 1]);
                    } catch (Exception $e) {
                        Toolbox::logWarning("($item) Caught exception: $e"); // :)
                        continue;
                    }
                    $classes = array_merge($classes, $tabs);
                }
            }
        }

        unset($classes["no_all_tab"]); // Remove superfluous element

        ksort($classes); // Order things around

        echo "<tr><th colspan='4'>" . __('Shortcuts') . " <a style='position: absolute; right: 25px; cursor: pointer; user-select: none;' onclick='\$(\".togshortcuts\").toggle(400);'>[toggle view]</a></th></tr>";

        $shortcuts = json_decode($data["access_custom_shortcuts"], true);

        foreach ($classes as $tab => $display) {
            $shortcut = $shortcuts[$tab];

            if (!$shortcut) {
                $shortcut = __("Not set");
            }
            echo "<tr class='togshortcuts' style='display: none;'>";
            echo "<input type='hidden' name='$tab' value='".json_encode($shortcut)."'>";
            echo "<td><label for='$tab$rand'>" . $display . "</label></td>";
            echo "<td>";
            if (!is_array($shortcut)) {
                $shortcutHtml = "<kbd>$shortcut</kbd>";
            } else {
                $shortcutHtml = "<kbd>".implode("</kbd>+<kbd>", $shortcut)."</kbd>";
            }
            echo "<span style='cursor: pointer'>$shortcutHtml</span>"; // Clicking this should edit the value in the hidden input for the HTML form.
            echo "</td></tr>";
        }

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
