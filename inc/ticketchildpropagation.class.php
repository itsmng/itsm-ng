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

/**
 * Instance-wide, parent-to-descendant ticket propagation.
 */
class TicketChildPropagation
{
    private static $updatingStatus = [];
    private static $propagatingStatus = false;
    private static $propagatingFollowup = false;

    public static function isUpdatingStatus($ticketId): bool
    {
        return isset(self::$updatingStatus[$ticketId]);
    }

    /**
     * Visit descendants once, even with cycles or several paths to the same child.
     */
    private static function getDescendants($parentId): array
    {
        global $DB;

        $seen = [(int)$parentId => true];
        $pending = [(int)$parentId];
        $descendants = [];
        while ($pending) {
            $next = [];
            foreach ($DB->request([
                'SELECT' => ['tickets_id_1'],
                'FROM' => Ticket_Ticket::getTable(),
                'WHERE' => ['tickets_id_2' => $pending, 'link' => Ticket_Ticket::SON_OF],
                'ORDER' => ['id ASC'],
            ]) as $row) {
                $childId = (int)$row['tickets_id_1'];
                if ($childId > 0 && !isset($seen[$childId])) {
                    $seen[$childId] = true;
                    $descendants[] = $childId;
                    $next[] = $childId;
                }
            }
            $pending = $next;
        }
        return $descendants;
    }

    public static function propagateStatus(Ticket $parent): void
    {
        global $CFG_GLPI;

        if (self::$propagatingStatus || $parent->isDeleted()) {
            return;
        }

        $status = (int)$parent->fields['status'];
        $processing = in_array($status, Ticket::getProcessStatusArray());
        $solved = in_array($status, Ticket::getSolvedStatusArray());
        $closed = in_array($status, Ticket::getClosedStatusArray());
        $setting = null;
        if ($processing && in_array($parent->oldvalues['status'] ?? null, Ticket::getNewStatusArray())) {
            $setting = 'child_ticket_propagate_processing';
        } elseif ($solved) {
            $setting = 'child_ticket_propagate_solved';
        } elseif ($closed) {
            $setting = 'child_ticket_propagate_closed';
        }
        if ($setting === null || empty($CFG_GLPI[$setting])) {
            return;
        }

        $solution = null;
        if ($solved) {
            $source = new ITILSolution();
            if ($source->getFromDBByRequest([
                'WHERE' => [
                    'itemtype' => 'Ticket',
                    'items_id' => $parent->getID(),
                    'status' => [CommonITILValidation::WAITING, CommonITILValidation::ACCEPTED],
                ],
                'ORDER' => ['id DESC'],
                'LIMIT' => 1,
            ])) {
                $solution = $source->fields;
            }
        }

        $failed = false;
        self::$propagatingStatus = true;
        try {
            foreach (self::getDescendants($parent->getID()) as $childId) {
                $child = new Ticket();
                if (!$child->getFromDB($childId) || $child->isDeleted()) {
                    continue;
                }
                $current = (int)$child->fields['status'];
                if (
                    in_array($current, Ticket::getClosedStatusArray())
                    || ($processing && !in_array($current, Ticket::getNewStatusArray()))
                    || ($solved && in_array($current, Ticket::getSolvedStatusArray()))
                ) {
                    continue;
                }
                if (
                    (!Session::isCron() && !$child->can($childId, UPDATE))
                    || (!Session::isCron() && !Ticket::isAllowedStatus($current, $status))
                    || ($solved && !Session::isCron() && !$child->canSolve())
                ) {
                    $failed = true;
                    continue;
                }

                self::$updatingStatus[$childId] = true;
                try {
                    if ($solved && !self::addSolution($child, $parent, $solution)) {
                        $failed = true;
                        continue;
                    }
                    $child->getFromDB($childId);
                    if (in_array($child->fields['status'], Ticket::getClosedStatusArray())) {
                        continue;
                    }
                    $input = ['id' => $childId, 'status' => $status];
                    if (isset($parent->input['_disablenotif'])) {
                        $input['_disablenotif'] = true;
                    }
                    if (!$child->update($input) || (int)$child->fields['status'] !== $status) {
                        $failed = true;
                    }
                } finally {
                    unset(self::$updatingStatus[$childId]);
                }
            }
        } finally {
            self::$propagatingStatus = false;
        }
        if ($failed) {
            Session::addMessageAfterRedirect(
                __('Some child tickets could not be updated. Check their permissions and status transitions.'),
                false,
                WARNING
            );
        }
    }

    private static function addSolution(Ticket $child, Ticket $parent, ?array $source): bool
    {
        $input = [
            'itemtype' => 'Ticket',
            'items_id' => $child->getID(),
            'solutiontypes_id' => $source['solutiontypes_id'] ?? 0,
            'content' => self::rebaseDocumentLinks(
                $source['content'] ?? sprintf(__('Solved through parent ticket #%s.'), $parent->getID()),
                $parent->getID(),
                $child->getID()
            ),
        ];
        if (isset($parent->input['_disablenotif'])) {
            $input['_disablenotif'] = true;
        }
        $solution = new ITILSolution();
        if (!$solution->add(Toolbox::addslashes_deep($input))) {
            return false;
        }
        if ($source !== null) {
            $original = new ITILSolution();
            $original->getFromDB($source['id']);
            return self::copyDocuments($original, $solution);
        }
        return true;
    }

    public static function propagateFollowup(ITILFollowup $source): void
    {
        global $CFG_GLPI;

        if (
            empty($CFG_GLPI['child_ticket_propagate_public_followups'])
            || self::$propagatingFollowup
            || $source->fields['itemtype'] !== 'Ticket'
            || !empty($source->fields['is_private'])
        ) {
            return;
        }
        $parent = new Ticket();
        if (!$parent->getFromDB($source->fields['items_id']) || $parent->isDeleted()) {
            return;
        }

        $failed = false;
        self::$propagatingFollowup = true;
        try {
            foreach (self::getDescendants($parent->getID()) as $childId) {
                $child = new Ticket();
                if (!$child->getFromDB($childId) || $child->isDeleted()) {
                    continue;
                }
                $input = [
                    'itemtype' => 'Ticket',
                    'items_id' => $childId,
                    'content' => self::rebaseDocumentLinks($source->fields['content'], $parent->getID(), $childId),
                    'is_private' => 0,
                    'users_id' => $source->fields['users_id'],
                    'requesttypes_id' => $source->fields['requesttypes_id'],
                    'date' => $source->fields['date'],
                ];
                if (isset($source->input['_disablenotif'])) {
                    $input['_disablenotif'] = true;
                }
                $copy = new ITILFollowup();
                if (
                    (!Session::isCron() && !$copy->can(-1, CREATE, $input))
                    || !$copy->add(Toolbox::addslashes_deep($input))
                    || !self::copyDocuments($source, $copy)
                ) {
                    $failed = true;
                }
            }
        } finally {
            self::$propagatingFollowup = false;
        }
        if ($failed) {
            Session::addMessageAfterRedirect(
                __('Some child tickets could not receive the public followup. Check your followup permissions.'),
                false,
                WARNING
            );
        }
    }

    private static function rebaseDocumentLinks(string $content, int $parentId, int $childId): string
    {
        return preg_replace(
            '~(document\.send\.php\?[^\s"\'<>]*?[&;]tickets_id=)' . $parentId . '(?![0-9])~',
            '${1}' . $childId,
            $content
        );
    }

    private static function copyDocuments(CommonDBTM $source, CommonDBTM $copy): bool
    {
        $success = true;
        $relation = new Document_Item();
        foreach ($relation->find(['itemtype' => $source->getType(), 'items_id' => $source->getID()]) as $row) {
            $input = [
                'documents_id' => $row['documents_id'],
                'itemtype' => $copy->getType(),
                'items_id' => $copy->getID(),
                'users_id' => $row['users_id'],
                'date' => $row['date'],
                'timeline_position' => $row['timeline_position'],
                '_disablenotif' => true,
            ];
            $link = new Document_Item();
            if ((!Session::isCron() && !$link->can(-1, CREATE, $input)) || !$link->add($input)) {
                $success = false;
            }
        }
        return $success;
    }
}
