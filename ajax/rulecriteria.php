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

// Direct access to file
if (strpos($_SERVER['PHP_SELF'], "rulecriteria.php")) {
    include('../inc/includes.php');
    header("Content-Type: text/html; charset=UTF-8");
    Html::header_nocache();
} elseif (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

Session::checkLoginUser();

if (isset($_POST["sub_type"]) && ($rule = getItemForItemtype($_POST["sub_type"]))) {
    $criterias = $rule->getAllCriteria();

    if (count($criterias)) {
        // First include -> first of the predefined array
        if (!isset($_POST["criteria"])) {
            $_POST["criteria"] = key($criterias);
        }

        if (isset($criterias[$_POST["criteria"]]['allow_condition'])) {
            $allow_condition = $criterias[$_POST["criteria"]]['allow_condition'];
        } else {
            $allow_condition = [];
        }

        $condparam = ['criterion'        => $_POST["criteria"],
                           'allow_conditions' => $allow_condition];
        if (isset($_POST['condition'])) {
            $condparam['value'] = $_POST['condition'];
        }

        $elements = [];
        foreach (RuleCriteria::getConditions($_POST['sub_type'], '') as $pattern => $label) {
            if (
                empty($p['allow_conditions'])
                || (!empty($p['allow_conditions']) && in_array($pattern, $p['allow_conditions']))
            ) {
                $elements[$pattern] = $label;
            }
        }

        $updateScript = <<<JS
          var condition = $('#DropdownForConditionCriterias').val();
          var condition_span = $('#condition_span');
          var url = "{$CFG_GLPI['root_doc']}/ajax/rulecriteriavalue.php";

          condition_span.load(url, {
              condition: condition,
              criteria: "{$_POST['criteria']}",
              sub_type: "{$_POST['sub_type']}"
          });
      JS;

        renderTwigTemplate('macros/input.twig', [
            'name' => 'condition',
            'id' => 'DropdownForConditionCriterias',
            'type' => 'select',
            'values' => $elements,
            'hooks' => [
                'change' => $updateScript
            ],
            'init' => $updateScript
        ]);
        echo "<span id='condition_span'></span>\n";
    }
}
