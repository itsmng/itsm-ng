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

include ('../inc/includes.php');

Session::checkCentralAccess();

Html::header(Rule::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "admin", "rule", -1);

RuleCollection::titleBackup();

$links = [];
foreach ($CFG_GLPI["rulecollections_types"] as $rulecollectionclass) {
   $rulecollection = new $rulecollectionclass();
   if ($rulecollection->canList()) {
      if ($plug = isPluginItemType($rulecollectionclass)) {
         $title = sprintf(__('%1$s - %2$s'), Plugin::getInfo($plug['plugin'], 'name'),
                                             $rulecollection->getTitle());
      } else {
         $title = $rulecollection->getTitle();
      }
      $ruleClassName = $rulecollection->getRuleClassName();
      $links[] = ['url'   => $ruleClassName::getSearchURL(),
                  'title' => $title];
   }
}

if (Session::haveRight("transfer", READ)
    && Session::isMultiEntitiesMode()) {
    $links[] = ['url'   => $CFG_GLPI['root_doc']."/front/transfer.php",
                'title' => __('Transfer')];
}

if (Session::haveRight("config", READ)) {
   $links[] = ['url'   => $CFG_GLPI['root_doc']."/front/blacklist.php",
               'title' => _n('Blacklist', 'Blacklists', Session::getPluralNumber())];
}

?>
<div class="container center">
    <h2><?php echo __('Rule type') ?></h2>
    <div class="w-50 mx-auto border rounded p-3">
    <?php foreach ($links as $link) { ?>
        <a class="d-block text-start btn btn-outline-secondary" href="<?php echo $link['url']; ?>">
            <?php echo $link['title']; ?>
        </a><br>
    <?php } ?>
    </div>
</div>
<?php


Html::footer();
