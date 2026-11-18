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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/// Class KnowbaseItem_Revision
/// since version 9.2
class KnowbaseItem_Revision extends CommonDBTM
{
    public static function getTypeName($nb = 0)
    {
        return _n('Revision', 'Revisions', $nb);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$item->canUpdateItem()) {
            return '';
        }

        $nb = 0;
        if ($_SESSION['glpishow_count_on_tabs']) {
            $where = [];
            if ($item->getType() == KnowbaseItem::getType()) {
                $where = [
                   'knowbaseitems_id' => $item->getID(),
                   'language'         => ''
                ];
            } else {
                $where = [
                   'knowbaseitems_id' => $item->fields['knowbaseitems_id'],
                   'language'         => $item->fields['language']
                ];
            }

            $nb = countElementsInTable(
                'glpi_knowbaseitems_revisions',
                $where
            );
        }
        return self::createTabEntry(self::getTypeName($nb), $nb);
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showForItem($item, $withtemplate);
        return true;
    }

    /**
     * Show linked items of a knowbase item
     *
     * @param $item                     CommonDBTM object
     * @param $withtemplate    integer  withtemplate param (default 0)
    **/
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {
        global $DB;

        $where = $item instanceof KnowbaseItem
            ? ['knowbaseitems_id' => $item->getID(), 'language' => '']
            : ['knowbaseitems_id' => $item->fields['knowbaseitems_id'], 'language' => $item->fields['language']];
        $id = 'kbRevisions_' . $item->getType() . '_' . $item->getID();
        $user = new User();
        $user->getFromDB($item->fields['users_id']);
        $values = [0 => [
            'revision' => '(' . __('cur') . ')',
            'selection' => "<input type='radio' name='diff' value='0' data-order='0' aria-label='" . __s('Changed') . "'>",
            'author' => $user->getLink(), 'date' => Html::convDateTime($item->fields['date_mod']), 'actions' => '',
        ]];
        $order = 0;
        $initial = null;
        $canedit = $withtemplate != 2 && $item->can($item->getID(), UPDATE);
        foreach ($DB->request([
            'FROM' => self::getTable(), 'WHERE' => $where, 'ORDER' => 'id DESC',
        ]) as $revision) {
            $order++;
            $revisionID = (int) $revision['id'];
            $initial ??= $revisionID;
            $author = $user->getFromDB($revision['users_id']) ? $user->getLink() : __('Unknown user');
            $actions = "<button type='button' class='btn btn-link p-0 kb-show-revision' data-revid='$revisionID' data-rev='"
                . (int) $revision['revision'] . "'>" . __('show') . '</button>';
            if ($canedit) {
                $actions .= " <a class='kb-restore-revision' href='" . $item::getFormURLWithID($item->getID())
                    . "&amp;to_rev=$revisionID'>" . __('restore') . '</a>';
            }
            $values[$revisionID] = [
                'revision' => (int) $revision['revision'],
                'selection' => "<input type='radio' name='oldid' value='$revisionID' data-order='$order' aria-label='" . __s('Original') . "'> "
                    . "<input type='radio' name='diff' value='$revisionID' data-order='$order' aria-label='" . __s('Changed') . "'>",
                'author' => $author, 'date' => Html::convDateTime($revision['date_creation']), 'actions' => $actions,
            ];
        }
        if ($initial === null) {
            echo "<p class='text-muted'>" . __('No revisions') . '</p>';
            return;
        }
        renderTwigTemplate('knowledgebase/revisions.twig', [
            'id' => $id, 'initial' => $initial, 'kb_id' => $where['knowbaseitems_id'],
            'fields' => ['revision' => _n('Revision', 'Revisions', 1), 'selection' => __('Compare selected revisions'),
                'author' => __('Author'), 'date' => __('Creation date'), 'actions' => __('Actions')],
            'values' => $values, 'pageSize' => max(1, (int) ($_SESSION['glpilist_limit'] ?? 15)),
        ]);
    }

    /**
     * Populate and create a new revision from KnowbaseItem information
     *
     * @param KnowbaseItem $item Knowledge base item
     *
     * @return boolean
     */
    public function createNew(KnowbaseItem $item)
    {
        $this->getEmpty();
        unset($this->fields['id']);
        $this->fields['knowbaseitems_id'] = $item->fields['id'];
        $this->fields['name'] = Toolbox::addslashes_deep($item->fields['name']);
        $this->fields['answer'] = Toolbox::clean_cross_side_scripting_deep(
            Toolbox::addslashes_deep($item->fields['answer'])
        );
        $this->fields['date_creation'] = $item->fields['date_mod'];
        $this->fields['revision'] = $this->getNewRevision();
        $this->fields['users_id'] = $item->fields['users_id'];
        $this->addToDB();
    }

    /**
     * Populate and create a new revision from KnowbaseItem information
     *
     * @param KnowbaseItemTranslation $item Knowledge base item translation
     *
     * @return boolean
     */
    public function createNewTranslated(KnowbaseItemTranslation $item)
    {
        $this->getEmpty();
        unset($this->fields['id']);
        $this->fields['knowbaseitems_id'] = $item->fields['knowbaseitems_id'];
        $this->fields['name'] = Toolbox::addslashes_deep($item->fields['name']);
        $this->fields['answer'] = Toolbox::clean_cross_side_scripting_deep(
            Toolbox::addslashes_deep($item->fields['answer'])
        );
        $this->fields['date_creation'] = $item->fields['date_mod'];
        $this->fields['language'] = $item->fields['language'];
        $this->fields['revision'] = $this->getNewRevision();
        $this->fields['users_id'] = $item->fields['users_id'];
        $this->addToDB();
    }

    /**
     * Get new revision number for item
     *
     * @return integer
     */
    private function getNewRevision()
    {
        global $DB;

        $result = $DB->request([
           'SELECT' => ['MAX' => 'revision AS revision'],
           'FROM'   => 'glpi_knowbaseitems_revisions',
           'WHERE'  => [
              'knowbaseitems_id'   => $this->fields['knowbaseitems_id'],
              'language'           => $this->fields['language']
           ]
        ])->next();

        $rev = $result['revision'];
        if ($rev === null) {
            //no revisions yet
            $rev = 1;
        } else {
            ++$rev;
        }

        return $rev;
    }
}
