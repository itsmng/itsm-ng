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

if (!$CFG_GLPI["use_anonymous_helpdesk"]) {
    exit();
}

// Send UTF8 Headers
header("Content-Type: text/html; charset=UTF-8");
echo "<!DOCTYPE html>\n";
echo "<html lang=\"{$CFG_GLPI["languages"][$_SESSION['glpilanguage']][3]}\">";
?>
<head>
    <meta charset="utf-8">
    <title>ITSM-NG</title>

<?php
echo Html::scss('css/styles');
if (isset($_SESSION['glpihighcontrast_css']) && $_SESSION['glpihighcontrast_css']) {
    echo Html::scss('css/highcontrast');
}
$theme = isset($_SESSION['glpipalette']) ? $_SESSION['glpipalette'] : 'itsmng';
echo Html::scss('css/palettes/' . $theme);
echo Html::script($CFG_GLPI["root_doc"] . '/script.js');
?>

</head>

<body>
    <script type="text/javascript">
function fillidfield(Type,Id) {

   window.opener.document.forms["helpdeskform"].elements["items_id"].value = Id;
   window.opener.document.forms["helpdeskform"].elements["itemtype"].value = Type;
   window.close();
}
</script>

<?php

echo "<div class='center'>";
echo "<p class='b'>" . __('Search the ID of your hardware') . "</p>";
echo " <form name='form1' aria-label='Hardware ' method='post' action='" . $_SERVER['PHP_SELF'] . "'>";

echo "<table class='tab_cadre_fixe' aria-label='Search form>";
echo "<tr><th height='29'>" . __('Enter the first letters (user, item name, serial or asset number)') .
     "</th></tr>";
echo "<tr><td class='tab_bg_1 center'>";
echo "<input name='NomContact' type='text' id='NomContact' >";
echo "<input type='hidden' name='send' value='1'>"; // bug IE ! La validation par enter ne fonctionne pas sans cette ligne  incroyable mais vrai !
echo "<input type='submit' name='send' value='" . _sx('button', 'Search') . "'>";
echo "</td></tr></table>";
Html::closeForm();
echo "</div>";

if (isset($_POST["send"])) {
    echo "<table class='tab_cadre_fixe' aria-label='Search results table'>";
    echo " <tr class='tab_bg3'>";
    echo " <td class='center b' width='30%'>" . __('Alternate username') . "</td>";
    echo " <td class='center b' width='20%'>" . __('Hardware type') . "</td>";
    echo " <td class='center b' width='30%'>" . _n('Associated element', 'Associated elements', Session::getPluralNumber()) . "</td>";
    echo " <td class='center b' width='5%'>" . __('ID') . "</td>";
    echo " <td class='center b' width='10%'>" . __('Serial number') . "</td>";
    echo " <td class='center b' width='10%'>" . __('Inventory number') . "</td>";
    echo " </tr>";

    $search = filter_input(INPUT_POST, 'NomContact', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
    $types = ['Computer'         => Computer::getTypeName(1),
                   'NetworkEquipment' => NetworkEquipment::getTypeName(1),
                   'Printer'          => Printer::getTypeName(1),
                   'Monitor'          => Monitor::getTypeName(1),
                   'Peripheral'       => Peripheral::getTypeName(1)];

    $em = config::getAdapter()->getEntityManager();
    foreach ($types as $type => $label) {
        $entityClass = 'Itsmng\Domain\Entities\\' . $type; 

    $qb = $em->createQueryBuilder();
    $qb->select('e.name, e.id, e.contact, e.serial, e.otherserial')
       ->from($entityClass, 'e')
       ->where('e.is_template = 0')
       ->andWhere('e.is_deleted = 0')
       ->andWhere(
           $qb->expr()->orX(
               $qb->expr()->like('e.contact', ':search'),
               $qb->expr()->like('e.name', ':search'),
               $qb->expr()->like('e.serial', ':search'),
               $qb->expr()->like('e.otherserial', ':search')
           )
       )
       ->setParameter('search', '%' . $search . '%')
       ->orderBy('e.name', 'ASC');

    $results = $qb->getQuery()->getArrayResult();

    foreach ($results as $ligne) {
            $Comp_num = $ligne['id'];
            $Contact  = $ligne['contact'];
            $Computer = $ligne['name'];
            $s1       = $ligne['serial'];
            $s2       = $ligne['otherserial'];
            echo " <tr class='tab_bg_1' onClick=\"fillidfield(" . $type . "," . $Comp_num . ")\">";
            echo "<td class='center'>&nbsp;$Contact&nbsp;</td>";
            echo "<td class='center'>&nbsp;$label&nbsp;</td>";
            echo "<td class='center b'>&nbsp;$Computer&nbsp;</td>";
            echo "<td class='center'>&nbsp;$Comp_num&nbsp;</td>";
            echo "<td class='center'>&nbsp;$s1&nbsp;</td>";
            echo "<td class='center'>&nbsp;$s2&nbsp;</td>";
            echo "<td class='center'>";
            echo "</td></tr>";
        }
    }

    $qb = $em->createQueryBuilder();
    $qb->select('s.name, s.id')
    ->from('Itsmng\Domain\Entities\Software', 's')
    ->where('s.is_template = 0')
    ->andWhere('s.is_deleted = 0')
    ->andWhere($qb->expr()->like('s.name', ':search'))
    ->setParameter('search', '%' . $search . '%')
    ->orderBy('s.name', 'ASC');

    $results = $qb->getQuery()->getArrayResult();

    foreach ($results as $ligne) {
        $Comp_num = $ligne['id'];
        $Computer = $ligne['name'];
        echo " <tr class='tab_find' onClick=\"fillidfield('Software'," . $Comp_num . ")\">";
        echo "<td class='center'>&nbsp;</td>";
        echo "<td class='center'>&nbsp;" . _n('Software', 'Software', 1) . "&nbsp;</td>";
        echo "<td class='center b'>&nbsp;$Computer&nbsp;</td>";
        echo "<td class='center'>&nbsp;$Comp_num&nbsp;</td>";
        echo "<td class='center'>&nbsp;</td></tr>";
    }

    echo "</table>";
}
echo '</body></html>';
