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

Session::checkRight("link", READ);

$lID = filter_input(INPUT_GET, 'lID', FILTER_VALIDATE_INT);
if (!$lID) {
    exit;
}
$em = config::getAdapter()->getEntityManager();
$qb = $em->createQueryBuilder();
$qb->select('l')
   ->from('Itsmng\Domain\Entities\Link', 'l')
   ->where('l.id = :id')
   ->setParameter('id', $lID);

$linkObj = $qb->getQuery()->getOneOrNullResult();

if ($linkObj) {
    $file = $linkObj->getData();
    $link = $linkObj->getLink();

    $itemtype = filter_input(INPUT_GET, 'itemtype', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $itemid   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);


    if ($itemtype && $itemid && $item = getItemForItemtype($itemtype)) {
        if ($item->getFromDB($itemid)) {
            $content_filename = Link::generateLinkContents($link, $item, false);
            $content_data     = Link::generateLinkContents($file, $item, false);

            $rank = filter_input(INPUT_GET, 'rank', FILTER_VALIDATE_INT);

            if ($rank !== null && isset($content_filename[$rank])) {
                $filename = $content_filename[$rank];
            } else {
                // first one (the same for all IP)
                $filename = reset($content_filename);
            }

            if ($rank !== null && isset($content_data[$rank])) {
                $data = $content_data[$rank];
            } else {
                // first one (probably missing arg)
                $data = reset($content_data);
            }
            header("Content-disposition: filename=\"$filename\"");
            $mime = "application/scriptfile";

            header("Content-type: " . $mime);
            header('Pragma: no-cache');
            header('Expires: 0');

            // May have several values due to network datas : use only first one
            echo $data;
        }
    }
}
