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
        global $CFG_GLPI;

        $userpref = array_key_exists('last_login', $data);
        $url = Toolbox::getItemTypeFormURL(__CLASS__);
        if ($userpref) {
            $url = $data['id'] === Session::getLoginUserID()
                ? $CFG_GLPI['root_doc'] . '/front/accessibility.form.php' : Accessibility::getFormURL();
        }
        $canedit = $userpref ? Session::haveRight('accessibility', UPDATE) : Config::canUpdate();
        $zooms = [];
        for ($zoom = 100; $zoom <= 200; $zoom += 10) {
            $zooms[$zoom] = $zoom . '%';
        }
        $inputs = [
            __('UI Scale') => ['type' => 'select', 'name' => 'access_zoom_level', 'value' => $data['access_zoom_level'], 'values' => $zooms],
            __('UI Font') => ['type' => 'select', 'name' => 'access_font', 'value' => $data['access_font'], 'values' => [
                '' => 'Default', 'OpenDyslexic' => 'Open Dyslexic Regular', 'OpenDyslexicAlta' => 'Open Dyslexic Alta', 'Tiresias Infofont' => 'Tiresias Infofont',
            ]],
            __('Enable shortcuts') => ['type' => 'checkbox', 'name' => 'access_shortcuts', 'value' => $data['access_shortcuts']],
        ];
        if (!$canedit) {
            foreach ($inputs as &$input) {
                $input['disabled'] = true;
            }
            unset($input);
        }
        if ($userpref) {
            $inputs[] = ['type' => 'hidden', 'name' => 'id', 'value' => $data['id']];
        }
        $content = [__('Interface') => ['visible' => true, 'inputs' => $inputs]];
        $shortcut_values = [];
        if ($displayShortcut) {
            $shortcuts = json_decode($data['access_custom_shortcuts'] ?? '', true) ?? [];
            $classes = [];
            foreach (get_declared_classes() as $class) {
                if (is_subclass_of($class, 'CommonGLPI')) {
                    $classes[$class] = $class::getTypeName();
                }
            }
            unset($classes['no_all_tab']);
            ksort($classes);
            $shortcut_inputs = [];
            $cpt = 1;
            foreach ($classes as $class => $label) {
                $shortcut = $shortcuts[$class] ?? __('shift+alt+' . $cpt);
                $shortcut = is_array($shortcut) ? implode('+', $shortcut) : $shortcut;
                $shortcut_values[$class] = $shortcut;
                $escaped_class = htmlspecialchars($class, ENT_QUOTES, 'UTF-8', false);
                $escaped_shortcut = htmlspecialchars($shortcut, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
                ob_start();
                renderTwigTemplate('macros/wrappedInput.twig', [
                    'title' => htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false),
                    'input' => ['content' => '<input type="hidden" id="shortcut_' . $escaped_class . '" name="' . $escaped_class . '" value="' . $escaped_shortcut . '">'
                        . '<button type="button" class="btn btn-outline-primary" data-shortcut-target="' . $escaped_class . '"' . ($canedit ? '' : ' disabled') . '><kbd>' . $escaped_shortcut . '</kbd></button>',
                        'col_lg' => 6, 'col_md' => 6],
                ]);
                $shortcut_inputs[] = ob_get_clean();
                $cpt++;
            }
            $content[__('Shortcuts')] = ['visible' => true, 'inputs' => [
                ['content' => '<button type="button" class="btn btn-outline-secondary mb-3" onclick="$(\'.togshortcuts\').toggle();">' . __('Toggle view') . '</button>'
                    . '<span id="alert_save" class="text-danger ms-3" hidden>' . __("Don't forget to save") . '</span>'
                    . '<div class="row togshortcuts" style="display:none">' . implode('', $shortcut_inputs) . '</div>', 'col_lg' => 12, 'col_md' => 12],
            ]];
        }
        renderTwigForm([
            'action' => $url, 'attributes' => ['name' => 'form', 'data-track-changes' => 'true'],
            'buttons' => $canedit ? [['type' => 'submit', 'name' => 'update', 'value' => _x('button', 'Save'), 'class' => 'btn btn-primary']] : [[]],
            'content' => $content,
        ]);
        if (!$displayShortcut || !$canedit) {
            return;
        }
        echo '<div id="modalForm" class="modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="shortcut_title" style="display:none">'
            . '<div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header">'
            . '<h2 id="shortcut_title" class="modal-title h5">' . __('Shortcuts') . '</h2>'
            . '<button type="button" id="btnClose" class="btn-close" aria-label="' . __('Close') . '"></button></div>'
            . '<div class="modal-body"><p id="shortcut_added" class="text-break"></p><p id="shortcut_existant" class="text-danger"></p></div>'
            . '<div class="modal-footer"><button type="button" id="submit_in_modal" class="btn btn-primary">' . _x('button', 'Update') . '</button></div>'
            . '</div></div></div>';
        echo Html::scriptBlock('$(function () {
            const shortcuts = ' . json_encode($shortcut_values, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';
            const modal = document.getElementById("modalForm");
            let target = null, keys = [];
            function closeShortcut() {
                modal.style.display = "none";
                document.removeEventListener("keydown", getShortcut);
                if (target) target.focus();
            }
            function getShortcut(event) {
                event.preventDefault();
                keys.push(event.key);
                document.getElementById("shortcut_added").textContent = keys.join("+");
                const duplicate = Object.values(shortcuts).includes(keys.join("+"));
                document.getElementById("shortcut_existant").textContent = duplicate ? ' . json_encode(__('Already exist enter another shortcut')) . ' : "";
                document.getElementById("submit_in_modal").disabled = duplicate;
                if (duplicate) keys = [];
            }
            document.querySelectorAll("[data-shortcut-target]").forEach(button => {
                button.addEventListener("click", function () {
                    target = button; keys = [];
                    document.getElementById("shortcut_added").textContent = "";
                    document.getElementById("shortcut_existant").textContent = "";
                    document.getElementById("submit_in_modal").disabled = true;
                    modal.style.display = "block";
                    document.addEventListener("keydown", getShortcut);
                    document.getElementById("btnClose").focus();
                });
            });
            document.getElementById("btnClose").addEventListener("click", closeShortcut);
            document.getElementById("submit_in_modal").addEventListener("click", function () {
                if (!target || !keys.length) return;
                const name = target.dataset.shortcutTarget, value = keys.join("+");
                document.getElementById("shortcut_" + name).value = value;
                target.querySelector("kbd").textContent = value;
                shortcuts[name] = value;
                document.getElementById("alert_save").hidden = false;
                closeShortcut();
            });
        });');
    }
}
