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

Session::checkCentralAccess();

// Make a select box
if ($_POST["idtable"] && class_exists($_POST["idtable"])) {
    if (isset($_POST['entity_restrict'])) {
        $entity_restrict = $_POST['entity_restrict'];
    }
    if (isset($_POST['condition'])) {
        $condition = $_POST['condition'];
    }

    $used = $_POST['used'] ?? [];
    if (is_string($used)) {
        $used = Toolbox::jsonDecode($used, true);
    }
    $select = getAjaxDropdownOptionsByEntity(
        $_POST['idtable'],
        $entity_restrict ?? -1,
        $condition ?? [],
        $used[$_POST['idtable']] ?? []
    );
    outputAjaxDropdownDefinition($select);
}
