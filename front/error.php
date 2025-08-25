<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022-2023 ITSM-NG and contributors.
 *
 * https://itsm-ng.org
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

/**
 *
 * This is a page for error page
 *
 */
echo '
<html>
<head>
<link rel="stylesheet" href="css/error.css">

  <script>
  window.console = window.console || function(t) {};
</script>

  
  
</head>

<body translate="no">
<h1>';
//check all messages
foreach ($msg as $v) {
    echo '<span class="word">' . $v . '</span>';
}
echo '</h1><div class="gears">';
$i = 0;
$lettres = array('a','b','c','d','e','f');
while ($i <= 2) {
    echo '<div class="gear ' . $lettres[$i] . '">
  <div class="bar"></div>
  <div class="bar"></div>
  <div class="bar"></div>
  </div>';
    $i++;
}
echo '</div></body></html>';
die();
