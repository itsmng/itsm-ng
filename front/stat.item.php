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

Html::header(__('Statistics'), '', "helpdesk", "stat");

Session::checkRight("statistic", READ);

if (isset($_GET["date1"])) {
    $_POST["date1"] = $_GET["date1"];
}
if (isset($_GET["date2"])) {
    $_POST["date2"] = $_GET["date2"];
}

if (empty($_POST["date1"]) && empty($_POST["date2"])) {
    $year           = date("Y") - 1;
    $_POST["date1"] = date("Y-m-d", mktime(1, 0, 0, date("m"), date("d"), $year));
    $_POST["date2"] = date("Y-m-d");
}

if (
    !empty($_POST["date1"])
    && !empty($_POST["date2"])
    && (strcmp((string) $_POST["date2"], (string) $_POST["date1"]) < 0)
) {
    $tmp            = $_POST["date1"];
    $_POST["date1"] = $_POST["date2"];
    $_POST["date2"] = $tmp;
}

if (!isset($_GET["start"])) {
    $_GET["start"] = 0;
}

Stat::title();

renderTwigForm(['action' => 'stat.item.php', 'method' => 'post',
    'buttons' => [['type' => 'submit', 'name' => 'submit', 'value' => __('Display report'), 'class' => 'btn btn-primary']],
    'content' => [['visible' => false, 'inputs' => [
        __('Start date') => ['type' => 'date', 'name' => 'date1', 'value' => $_POST['date1'], 'col_lg' => 6],
        __('End date') => ['type' => 'date', 'name' => 'date2', 'value' => $_POST['date2'], 'col_lg' => 6],
    ]]]]);

if (isset($_POST['submit'])) {
    Stat::showItems($_SERVER['PHP_SELF'], $_POST["date1"], $_POST["date2"], $_GET['start']);
}

Html::footer();
