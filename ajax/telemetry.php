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

if (!($_SESSION['telemetry_from_install'] ?? false)) {
    Session::checkRight("config", READ);
    $hide_sensitive_data = false;
} else {
    $hide_sensitive_data = true;
}

echo Html::css("public/lib/prismjs.css");
echo Html::script("public/lib/prismjs.js");

$infos = Telemetry::getTelemetryInfos($hide_sensitive_data);
echo "<p>" . __("We only collect the following data : plugins usage, performance and responsiveness statistics about user interface features, memory, and hardware configuration.") . "</p>";
echo "<pre><code class='language-json'>";
echo json_encode($infos, JSON_PRETTY_PRINT);
echo "</code></pre>";
