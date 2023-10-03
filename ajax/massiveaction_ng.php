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

include('../inc/includes.php');
include( GLPI_ROOT . "/ng/twig.class.php");


header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

try {
   $ma = new MassiveAction($_POST, $_GET, 'initial');
} catch (Exception $e) {
   
   echo "<div class='center'><img src='" . $CFG_GLPI["root_doc"] . "/pics/warning.png' alt='" .
   __s('Warning') . "'><br><br>";
   echo "<span class='b'>" . $e->getMessage() . "</span><br>";
   echo "</div>";
   exit();
}

$params = ['action' => '__VALUE__'];
$input  = $ma->getInput();
foreach ($input as $key => $val) {
   $params[$key] = $val;
}
$actions = $params['actions'];

$POST = $_POST;
$POST['items'] = $POST;
$POST['items'] = $POST['item'];

try {
   $twig = Twig::load(GLPI_ROOT . "/templates", false);
   echo $twig->render('massiveaction.twig', [
      'actions' => $actions,
      'subformBody' => $POST,
   ]);
} catch (Exception $e) {
   echo $e->getMessage();
}
