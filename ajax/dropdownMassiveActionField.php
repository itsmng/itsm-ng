<?php

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

include('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!isset($_POST["itemtype"]) || !($item = getItemForItemtype($_POST['itemtype']))) {
    exit();
}

if (Infocom::canApplyOn($_POST["itemtype"])) {
    Session::checkSeveralRightsOr([$_POST["itemtype"] => UPDATE,
                                        "infocom"          => UPDATE]);
} else {
    $item->checkGlobal(UPDATE);
}

$inline = false;
if (isset($_POST['inline']) && $_POST['inline']) {
    $inline = true;
}
$submitname = _sx('button', 'Post');
if (isset($_POST['submitname']) && $_POST['submitname']) {
    $submitname = stripslashes($_POST['submitname']);
}


if (
    isset($_POST["itemtype"])
    && isset($_POST["id_field"]) && $_POST["id_field"]
) {
    $search = Search::getOptions($_POST["itemtype"]);
    if (!isset($search[$_POST["id_field"]])) {
        exit();
    }

    $search            = $search[$_POST["id_field"]];

    echo "<table class='tab_glpi' width='100%' aria-label='Massive action table'><tr><td>";

    $plugdisplay = false;
    // Specific plugin Type case
    if (
        ($plug = isPluginItemType($_POST["itemtype"]))
        // Specific for plugin which add link to core object
        || ($plug = isPluginItemType(getItemTypeForTable($search['table'])))
    ) {
        $plugdisplay = Plugin::doOneHook(
            $plug['plugin'],
            'MassiveActionsFieldsDisplay',
            ['itemtype' => $_POST["itemtype"],
                                               'options'  => $search]
        );
    }

    $fieldname = '';

    if (
        empty($search["linkfield"])
        || ($search['table'] == 'glpi_infocoms')
    ) {
        $fieldname = $search["field"];
    } else {
        $fieldname = $search["linkfield"];
    }
    if (!$plugdisplay) {
        $options = [];
        $values  = [];
        // For ticket template or aditional options of massive actions
        if (isset($_POST['options'])) {
            $options = $_POST['options'];
        }
        if (isset($_POST['additionalvalues'])) {
            $values = $_POST['additionalvalues'];
        }
        $values[$search["field"]] = '';
        
        function getValueToSelectFixed($item, $search, $fieldname, $values, $options) {
            $field = $search['field'];
            $value = is_array($values) ? ($values[$field] ?? '') : $values;
            
            switch ($search['datatype'] ?? 'specific') {
                case "dropdown":
                    $itemtype = getItemTypeForTable($search['table']);
                    if ($itemtype && class_exists($itemtype)) {
                        $params = [
                            'name' => $fieldname,
                            'value' => $value,
                            'display' => false
                        ];
                        
                        if (isset($options['entity'])) {
                            $params['entity'] = $options['entity'];
                        }
                        if ($itemtype == 'User' && isset($search['right'])) {
                            $params['right'] = $search['right'];
                        }
                        
                        return $itemtype::dropdown($params);
                    }
                    break;
                    
                case "specific":
                    $itemtype = getItemTypeForTable($search['table']);
                    if ($itemtype && ($itemObj = getItemForItemtype($itemtype))) {
                        return $itemObj->getSpecificValueToSelect($search['field'], $fieldname, $values, $options);
                    }
                    break;
                    
                case "text":
                    return "<textarea name='$fieldname' class='form-control'>" . htmlspecialchars($value) . "</textarea>";
                    
                case "bool":
                    return Dropdown::showYesNo($fieldname, $value, -1, ['display' => false]);
                    
                case "date":
                case "datetime":
                    return Html::showDateTimeField($fieldname, ['value' => $value, 'maybeempty' => true]);
                    
                default:
                    return "<input type='text' name='$fieldname' value='" . htmlspecialchars($value) . "' class='form-control' />";
            }
            
            return "<input type='text' name='$fieldname' value='" . htmlspecialchars($value) . "' class='form-control' />";
        }
        
        $result = getValueToSelectFixed($item, $search, $fieldname, $values, $options);
        echo $result;
    }

    echo "<input type='hidden' name='field' value='$fieldname'>";
    echo "</td>";
    if ($inline) {
        echo "<td><input type='submit' name='massiveaction' class='submit' value='$submitname'></td>";
    }
    echo "</tr></table>";

    if (!$inline) {
        echo "<br><input type='submit' name='massiveaction' class='submit' value='$submitname'>";
    }
}
