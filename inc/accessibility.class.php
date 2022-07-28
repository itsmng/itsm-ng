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

    static function editTabShortcutForm(string $tab) {
        global $CFG_GLPI, $DB;

        $user = new User();
        $user->getFromDB(Session::getLoginUserID());
        $data = $user->fields;

        $split = explode("$", $tab);

        $url       = Toolbox::getItemTypeFormURL(__CLASS__);
        $rand      = mt_rand();

        $canedit = Config::canUpdate();
        $canedituser = Session::haveRight('accessibility', UPDATE);

        $form = "<form name='form' action='".$CFG_GLPI['root_doc']."/front/preference.php' method='post' data-track-changes='true'>";

        $form .= "<div class='center' id='tabsbody'>";
        $form .= "<table class='tab_cadre_fixe'>";

        $form .= "<tr><th colspan='4' style='text-align: center'>" . __('Edit shortcut') . "</th></tr>";
        $form .= "<td><label for='$rand' style='text-align: center'>" . $split[1] . "</label></td>";
        $form .= "<td>";
        $form .= "<textarea></textarea>";
        $form .= "</td></tr>";

        if (Session::haveRight("accessibility", 2)) {
            $form .= "<tr class='tab_bg_2'>";
            $form .= "<td colspan='4' class='center'>";
            $form .= "<input type='submit' name='update' class='submit' value='"._sx('button', 'Save')."'>";
            $form .= "</td></tr>";
        }

        $form .= "</table></div>";
        $form .= str_replace('"', "'", Html::closeForm(false));

        return $form;
    }

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
                $url  = $CFG_GLPI['root_doc']."/front/preference.php";
            } else {
                $url  = User::getFormURL();
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
