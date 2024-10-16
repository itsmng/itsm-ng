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

Session::checkLoginUser();

Html::header(Accessibility::getTypeName(1), $_SERVER['PHP_SELF'], 'accessibility');

echo "<div class='container' mt-4'>";

echo "<h1 class='mb-4 text-center'>Déclaration d'accessibilité ITSM-NG</h1>";
echo "<div class='text-right'>";
echo "<h2 class='mt-4'>Etat de conformité</h2>";
echo "<p class='mt-4 mb-2'>ITSM-NG est en conformité partielle avec le référentiel général d’amélioration de l’accessibilité (RGAA), version 4.1, en raison de non-conformités énumérées dans le rapport d'audit consultable en ligne, effectué avec l'outil ARA :</p>";
echo "<a href='https://ara.numerique.gouv.fr/rapport/1FC31FLg9GmDPvvZiwkhn/resultats' target='_blank' class='d-block mb-4'>Rapport d'audit ITSM-NG.</a>";
echo "<h2 class='mb-4'>Date de réalisation de l'audit</h2>";
echo "<p class='mb-4 mt-4'>Le 22/07/2024.</p>";
echo "<h2 class='mt-4'>Résultats des tests</h2>";
echo "<h4 class='mt-4'>ARA</h4>";
echo "<p class='mb-4'>L'audit de conformité révèle qu'ITSM-NG est conforme au RGAA 4.1 à 84% en moyenne.</p>";
echo "<h4 class='mt-4'>Lighthouse</h4>";
echo "<p class='mb-4'>L'outil lighthouse révèle qu'ITSM-NG possède un taux d'accessibilité de 90% en moyenne.</p>";
echo "<h2 class='mt-4'>Retour d'information</h2>";
echo "<p class='mb-4'>Si vous rencontrez des difficultés pour accéder à une partie quelconque de ITSM-NG, ou si vous avez des suggestions pour améliorer l'accessibilité, nous vous invitons à nous contacter. Votre retour serait précieux pour nous permettre d'identifier et de résoudre les potentiels problèmes d'accessibilité auquel vous feriez face. Vous pourriez nous joindre à l'adresse suivante :</p>";
echo "<p class='mb-4'><strong>Email :</strong> <a href='mailto:contact@itsm-ng.com'>contact@itsm-ng.com</a></p>";
echo "<p class='mb-4'>Merci de nous fournir autant de détails que possible sur le problème rencontré, y compris les pages spécifiques ou les fonctionnalités concernées. Nous ferions de notre mieux pour vous répondre dans les meilleurs délais et apporter les corrections nécessaires.</p>";
echo "</div>";

Html::footer();
