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

Session::checkRight("reports", READ);

Html::header(Report::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "tools", "report");

if (empty($_POST["date1"]) && empty($_POST["date2"])) {
    $year           = date("Y") - 1;
    $_POST["date1"] = date("Y-m-d", mktime(1, 0, 0, date("m"), date("d"), $year));
    $_POST["date2"] = date("Y-m-d");
}

if (
    !empty($_POST["date1"])
    && !empty($_POST["date2"])
    && (strcmp($_POST["date2"], $_POST["date1"]) < 0)
) {
    $tmp            = $_POST["date1"];
    $_POST["date1"] = $_POST["date2"];
    $_POST["date2"] = $tmp;
}

$stat = new Stat();
$chart_opts =  [
   'width'  => '90%',
   'legend' => false,
   'title'  => __('Value'),
];

Report::title();

$form = [
   'action' => $_SERVER['PHP_SELF'],
   'buttons' => [
      [
         'value' => __s('Display report'),
         'class' => 'btn btn-secondary',
      ]
   ],
   'content' => [
      '' => [
         'visible' => true,
         'inputs' => [
            __('Start date') => [
               'type' => 'date',
               'name' => 'date1',
               'value' => $_POST["date1"],
               'col_lg' => 6,
            ],
            __('End date') => [
               'type' => 'date',
               'name' => 'date2',
               'value' => $_POST["date2"],
               'col_lg' => 6,
            ]
         ]
      ]
   ]
];
renderTwigForm($form);

$valeurtot           = 0;
$valeurnettetot      = 0;
$valeurnettegraphtot = [];
$valeurgraphtot      = [];


/** Display an infocom report
 *
 * @param string $itemtype  item type
 * @param string $begin     begin date
 * @param string $end       end date
**/
function display_infocoms_report($itemtype, $begin, $end)
{
    global $DB, $valeurtot, $valeurnettetot, $valeurnettegraphtot, $valeurgraphtot, $CFG_GLPI, $stat, $chart_opts;

    $em = config::getAdapter()->getEntityManager();

    $entityClass = 'Itsmng\\Domain\\Entities\\' . $itemtype;

    // Vérifier si l’entité existe et possède le champ ticketTco
    if (!class_exists($entityClass)) {
        return false;
    }
    $reflection = new \ReflectionClass($entityClass);
    if (!$reflection->hasProperty('ticketTco')) {
        return false;
    }

    $qb = $em->createQueryBuilder();

    $qb->select(
        'i',
        'i.value AS value',
        'i.sinkType AS sinkType',
        'i.sinkTime AS sinkTime',
        'i.sinkCoeff AS sinkCoeff',
        'i.buyDate AS buyDate',
        'i.useDate AS useDate',
        'i.warrantyDuration AS warrantyDuration',
        'c.ticketTco AS ticket_tco',
        'c.id AS items_id',
        'c.name AS name',
        'c.ticketTco',
        'e.completename AS entname',
        'e.id AS entID'
    )
        ->from(Itsmng\Domain\Entities\Infocom::class, 'i')
        ->innerJoin(
            $entityClass,
            'c',
            'WITH',
            'i.items_id = c.id AND i.itemtype = :itemtype'
        )
        ->leftJoin('c.entity', 'e')
        ->where('c.isTemplate = 0')
        ->setParameter('itemtype', $itemtype)
        ->orderBy('e.completename', 'ASC')
        ->addOrderBy('i.buyDate', 'ASC')
        ->addOrderBy('i.useDate', 'ASC');

    // Filtre sur la date de début
    if (!empty($begin)) {
        $qb->andWhere(
            $qb->expr()->orX(
                'i.buyDate >= :begin',
                'i.useDate >= :begin'
            )
        );
        $qb->setParameter('begin', new \DateTime($begin));
    }

    // Filtre sur la date de fin
    if (!empty($end)) {
        $qb->andWhere(
            $qb->expr()->orX(
                'i.buyDate <= :end',
                'i.useDate <= :end'
            )
        );
        $qb->setParameter('end', new \DateTime($end));
    }

    $results = $qb->getQuery()->getArrayResult();

    $display_entity = Session::isMultiEntitiesMode();

    if (!empty($results)) {
        echo "<h2>" . htmlspecialchars($itemtype) . "</h2>";

        echo "<table class='tab_cadre' aria-label='Report Form'><tr><th>" . __('Name') . "</th>";
        if ($display_entity) {
            echo "<th>" . Entity::getTypeName(1) . "</th>";
        }
        echo "<th>" . _x('price', 'Value') . "</th><th>" . __('ANV') . "</th>";
        echo "<th>" . __('TCO') . "</th><th>" . __('Date of purchase') . "</th>";
        echo "<th>" . __('Startup date') . "</th><th>" . __('Warranty expiration date') . "</th></tr>";

        $valeursoustot      = 0;
        $valeurnettesoustot = 0;
        $valeurnettegraph   = [];
        $valeurgraph        = [];

        foreach ($results as $line) {
            $entity = $em->find($entityClass, $line['items_id']);
            if (
                isset($line["is_global"]) && $line["is_global"]
               && $entity !== null
            ) {
                $line["value"] *= Computer_Item::countForItem($entity);
            }

            if ($line["value"] > 0) {
                $valeursoustot += $line["value"];
            }
            $valeurnette = Infocom::Amort(
                $line["sinkType"],
                $line["value"],
                $line["sinkTime"],
                $line["sinkCoeff"],
                $line["buyDate"],
                $line["useDate"],
                $CFG_GLPI["date_tax"],
                "n"
            );

            $tmp         = Infocom::Amort(
                $line["sinkType"],
                $line["value"],
                $line["sinkTime"],
                $line["sinkCoeff"],
                $line["buyDate"],
                $line["useDate"],
                $CFG_GLPI["date_tax"],
                "all"
            );

            if (is_array($tmp) && (count($tmp) > 0)) {
                foreach ($tmp["annee"] as $key => $val) {
                    if ($tmp["vcnetfin"][$key] > 0) {
                        if (!isset($valeurnettegraph[$val])) {
                            $valeurnettegraph[$val] = 0;
                        }
                        $valeurnettegraph[$val] += $tmp["vcnetdeb"][$key];
                    }
                }
            }

            if (!empty($line["buyDate"])) {
                $year = substr($line["buyDate"], 0, 4);
                if ($line["value"] > 0) {
                    if (!isset($valeurgraph[$year])) {
                        $valeurgraph[$year] = 0;
                    }
                    $valeurgraph[$year] += $line["value"];
                }
            }

            $valeurnette = str_replace([" ", "-"], ["", ""], $valeurnette);
            if (!empty($valeurnette)) {
                $valeurnettesoustot += $valeurnette;
            }

            echo "<tr class='tab_bg_1'><td>" . $line["name"] . "</td>";
            if ($display_entity) {
                echo "<td>" . $line['entname'] . "</td>";
            }

            echo "<td class='right'>" . Html::formatNumber($line["value"]) . "</td>" .
                 "<td class='right'>" . Html::formatNumber($valeurnette) . "</td>" .
                 "<td class='right'>" . Infocom::showTco($line["ticketTco"], $line["value"]) . "</td>" .
                 "<td>" . Html::convDate($line["buyDate"]) . "</td>" .
                 "<td>" . Html::convDate($line["useDate"]) . "</td>" .
                 "<td>" . Infocom::getWarrantyExpir($line["buyDate"], $line["warrantyDuration"]) .
                 "</td></tr>";
        }

        $valeurtot      += $valeursoustot;
        $valeurnettetot += $valeurnettesoustot;

        $tmpmsg = sprintf(
            __('Total: Value=%1$s - Account net value=%2$s'),
            Html::formatNumber($valeursoustot),
            Html::formatNumber($valeurnettesoustot)
        );
        echo "<tr><td colspan='6' class='center'><h3>$tmpmsg</h3></td></tr>";

        if (count($valeurnettegraph) > 0) {
            echo "<tr><td colspan='8' class='center'>";
            ksort($valeurnettegraph);
            $valeurnettegraphdisplay = array_map('round', $valeurnettegraph);

            foreach ($valeurnettegraph as $key => $val) {
                if (!isset($valeurnettegraphtot[$key])) {
                    $valeurnettegraphtot[$key] = 0;
                }
                $valeurnettegraphtot[$key] += $valeurnettegraph[$key];
            }
            $item = getItemForItemtype($itemtype);
            $stat->displayLineGraph(
                sprintf(
                    __('%1$s account net value'),
                    $item->getTypeName(1)
                ),
                array_keys($valeurnettegraphdisplay),
                [
                  [
                     'data' => $valeurnettegraphdisplay
                  ]
                ],
                $chart_opts
            );
            echo "</td></tr>";
        }

        if (count($valeurgraph) > 0) {
            echo "<tr><td colspan='8' class='center'>";

            ksort($valeurgraph);
            $valeurgraphdisplay = array_map('round', $valeurgraph);

            foreach ($valeurgraph as $key => $val) {
                if (!isset($valeurgraphtot[$key])) {
                    $valeurgraphtot[$key] = 0;
                }
                $valeurgraphtot[$key] += $valeurgraph[$key];
            }

            $stat->displayLineGraph(
                sprintf(
                    __('%1$s value'),
                    $item->getTypeName(1)
                ),
                array_keys($valeurgraphdisplay),
                [
                  [
                     'data' => $valeurgraphdisplay
                  ]
                ],
                $chart_opts
            );
            echo "</td></tr>";
        }
        echo "</table>";
        return true;
    }
    return false;
}

$types = $CFG_GLPI["infocom_types"];

$i = 0;
echo "<table aria-label='Report Data for Each Item Type'><tr><td class='top'>";

while (count($types) > 0) {
    $type = array_shift($types);

    if (display_infocoms_report($type, $_POST["date1"], $_POST["date2"])) {
        echo "</td>";
        $i++;

        if (($i % 2) == 0) {
            echo "</tr><tr>";
        }
        echo "<td class='top'>";
    }
}

if (($i % 2) == 0) {
    echo "&nbsp;</td><td>&nbsp;";
}

echo "</td></tr></table>";


$tmpmsg = sprintf(
    __('Total: Value=%1$s - Account net value=%2$s'),
    Html::formatNumber($valeurtot),
    Html::formatNumber($valeurnettetot)
);
echo "<div class='center'><h3>$tmpmsg</h3></div>";

if (count($valeurnettegraphtot) > 0) {
    $valeurnettegraphtotdisplay = array_map('round', $valeurnettegraphtot);

    $stat->displayLineGraph(
        __('Total account net value'),
        array_keys($valeurnettegraphtotdisplay),
        [
          [
             'data' => $valeurnettegraphtotdisplay
          ]
        ],
        $chart_opts
    );
}
if (count($valeurgraphtot) > 0) {
    $valeurgraphtotdisplay = array_map('round', $valeurgraphtot);

    $stat->displayLineGraph(
        __('Total value'),
        array_keys($valeurgraphtotdisplay),
        [
          [
             'data' => $valeurgraphtotdisplay
          ]
        ],
        $chart_opts
    );
}

Html::footer();
