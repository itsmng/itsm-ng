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


if (empty($_GET["showgraph"])) {
    $_GET["showgraph"] = 0;
}

//sanitize dates
foreach (['date1', 'date2'] as $key) {
    if (array_key_exists($key, $_GET) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$_GET[$key]) !== 1) {
        unset($_GET[$key]);
    }
}

if (empty($_GET["date1"]) && empty($_GET["date2"])) {
    $year          = date("Y") - 1;
    $_GET["date1"] = date("Y-m-d", mktime(1, 0, 0, date("m"), date("d"), $year));
    $_GET["date2"] = date("Y-m-d");
}

if (
    !empty($_GET["date1"])
    && !empty($_GET["date2"])
    && (strcmp($_GET["date2"], $_GET["date1"]) < 0)
) {
    $tmp           = $_GET["date1"];
    $_GET["date1"] = $_GET["date2"];
    $_GET["date2"] = $tmp;
}

if (!isset($_GET["start"])) {
    $_GET["start"] = 0;
}
// Why this test ?? For me it's doing nothing
if (isset($_GET["dropdown"])) {
    $_GET["dropdown"] = $_GET["dropdown"];
}

if (empty($_GET["dropdown"])) {
    $_GET["dropdown"] = "ComputerType";
}

if (!isset($_GET['itemtype'])) {
    $_GET['itemtype'] = 'Ticket';
}

$stat = new Stat();
Stat::title();

echo "<form aria-label='Location Statistics' method='get' name='form' action='stat.location.php'>";
// keep it first param
echo "<input type='hidden' name='itemtype' value='" . $_GET['itemtype'] . "'>";

echo "<table class='tab_cadre_fixe' aria-label='Statistics report filter'><tr class='tab_bg_2'><td rowspan='2' width='30%'>";
$values = [_n('Dropdown', 'Dropdowns', Session::getPluralNumber()) => ['ComputerType'    => _n('Type', 'Types', 1),
                                                       'ComputerModel'   => _n('Model', 'Models', 1),
                                                       'OperatingSystem' => OperatingSystem::getTypeName(1),
                                                       'Location'        => Location::getTypeName(1)],
               ];
$devices = Dropdown::getDeviceItemTypes();
foreach ($devices as $label => $dp) {
    foreach ($dp as $i => $name) {
        $values[$label][$i] = $name;
    }
}

Dropdown::showFromArray('dropdown', $values, ['value' => $_GET["dropdown"]]);

echo "</td>";

echo "<td class='right'>" . __('Start date') . "</td><td>";
Html::showDateField("date1", ['value' => $_GET["date1"]]);
echo "</td>";
echo "<td class='right'>" . __('Show graphics') . "</td>";
echo "<td rowspan='2' class='center'>";
echo "<input type='submit' class='submit' name='submit' value='" . __s('Display report') . "'></td></tr>";

echo "<tr class='tab_bg_2'><td class='right'>" . __('End date') . "</td><td>";
Html::showDateField("date2", ['value' => $_GET["date2"]]);
echo "</td><td class='center'>";
Dropdown::showYesNo('showgraph', $_GET['showgraph']);
echo "</td>";
echo "</tr>";
echo "</table>";
// form using GET method : CRSF not needed
echo "</form>";

if (
    empty($_GET["dropdown"])
    || !($item = getItemForItemtype($_GET["dropdown"]))
) {
    // Do nothing
    Html::footer();
    exit();
}


if (!($item instanceof CommonDevice)) {
    // echo "Dropdown";
    $type = "comp_champ";

    $val = Stat::getItems($_GET['itemtype'], $_GET["date1"], $_GET["date2"], $_GET["dropdown"]);
    $params = ['type'     => $type,
                    'dropdown' => $_GET["dropdown"],
                    'date1'    => $_GET["date1"],
                    'date2'    => $_GET["date2"],
                    'start'    => $_GET["start"]];
} else {
    //   echo "Device";
    $type  = "device";

    $val = Stat::getItems($_GET['itemtype'], $_GET["date1"], $_GET["date2"], $_GET["dropdown"]);
    $params = ['type'     => $type,
                    'dropdown' => $_GET["dropdown"],
                    'date1'    => $_GET["date1"],
                    'date2'    => $_GET["date2"],
                    'start'    => $_GET["start"]];
}

Html::printPager(
    $_GET['start'],
    count($val),
    $CFG_GLPI['root_doc'] . '/front/stat.location.php',
    "date1=" . $_GET["date1"] . "&amp;date2=" . $_GET["date2"] .
                     "&amp;itemtype=" . $_GET['itemtype'] . "&amp;dropdown=" . $_GET["dropdown"],
    'Stat',
    $params
);

if (!$_GET['showgraph']) {
    Stat::showTable(
        $_GET['itemtype'],
        $type,
        $_GET["date1"],
        $_GET["date2"],
        $_GET['start'],
        $val,
        $_GET["dropdown"]
    );
} else {
    $data = Stat::getData(
        $_GET['itemtype'],
        $type,
        $_GET["date1"],
        $_GET["date2"],
        $_GET['start'],
        $val,
        $_GET["dropdown"]
    );

    if (isset($data['opened']) && is_array($data['opened'])) {
        $count = 0;
        $labels = [];
        $series = [];
        foreach ($data['opened'] as $key => $val) {
            if ($val > 0) {
                $newkey = Toolbox::unclean_cross_side_scripting_deep(Html::clean($key));
                $labels[] = $newkey;
                $series[] = ['name' => $newkey, 'data' => $val];
                $count += $val;
            }
        }

        if (count($series)) {
            $stat->displayPieGraph(
                sprintf(
                    __('Opened %1$s (%2$s)'),
                    Ticket::getTypeName(Session::getPluralNumber()),
                    $count
                ),
                $labels,
                $series
            );
        }
    }

    if (isset($data['solved']) && is_array($data['solved'])) {
        $count = 0;
        $labels = [];
        $series = [];
        foreach ($data['solved'] as $key => $val) {
            if ($val > 0) {
                $labels[] = $newkey;
                $series[] = ['name' => $newkey, 'data' => $val];
                $count += $val;
            }
        }

        if (count($series)) {
            $stat->displayPieGraph(
                sprintf(
                    __('Solved %1$s (%2$s)'),
                    Ticket::getTypeName(Session::getPluralNumber()),
                    $count
                ),
                $labels,
                $series
            );
        }
    }

    if (isset($data['late']) && is_array($data['late'])) {
        $count = 0;
        $labels = [];
        $series = [];
        foreach ($data['late'] as $key => $val) {
            if ($val > 0) {
                $labels[] = $newkey;
                $series[] = ['name' => $newkey, 'data' => $val];
                $count += $val;
            }
        }

        if (count($series)) {
            $stat->displayPieGraph(
                sprintf(
                    __('Late solved %1$s (%2$s)'),
                    Ticket::getTypeName(Session::getPluralNumber()),
                    $count
                ),
                $labels,
                $series
            );
        }
    }

    if (isset($data['closed']) && is_array($data['closed'])) {
        $count = 0;
        $labels = [];
        $series = [];
        foreach ($data['closed'] as $key => $val) {
            if ($val > 0) {
                $labels[] = $newkey;
                $series[] = ['name' => $newkey, 'data' => $val];
                $count += $val;
            }
        }

        if (count($series)) {
            $stat->displayPieGraph(
                sprintf(
                    __('Closed %1$s (%2$s)'),
                    Ticket::getTypeName(Session::getPluralNumber()),
                    $count
                ),
                $labels,
                $series
            );
        }
    }

    if (isset($data['opensatisfaction']) && is_array($data['opensatisfaction'])) {
        $count = 0;
        $labels = [];
        $series = [];
        foreach ($data['opensatisfaction'] as $key => $val) {
            if ($val > 0) {
                $labels[] = $newkey;
                $series[] = ['name' => $newkey, 'data' => $val];
                $count += $val;
            }
        }

        if (count($series)) {
            $stat->displayPieGraph(
                sprintf(
                    __('%1$s satisfaction survey (%2$s)'),
                    Ticket::getTypeName(Session::getPluralNumber()),
                    $count
                ),
                $labels,
                $series
            );
        }
    }
}

Html::footer();
