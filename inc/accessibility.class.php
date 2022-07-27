<?php

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
