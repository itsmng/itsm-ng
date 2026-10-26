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

namespace tests\units;

use DbTestCase;

class TicketChildPropagation extends DbTestCase
{
    private $configBackup;
    private const OPTIONS = [
        'child_ticket_propagate_processing',
        'child_ticket_propagate_solved',
        'child_ticket_propagate_closed',
        'child_ticket_propagate_public_followups',
    ];

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        global $CFG_GLPI;
        $this->configBackup = $CFG_GLPI;
        foreach (self::OPTIONS as $option) {
            $CFG_GLPI[$option] = 0;
        }
        $this->login();
        $_SESSION['glpiset_default_tech'] = 0;
    }

    public function afterTestMethod($method)
    {
        global $CFG_GLPI;
        $CFG_GLPI = $this->configBackup;
        parent::afterTestMethod($method);
    }

    private function enable(string ...$options): void
    {
        global $CFG_GLPI;
        foreach ($options as $option) {
            $CFG_GLPI['child_ticket_propagate_' . $option] = 1;
        }
    }

    private function ticket(int $status = \Ticket::INCOMING, array $extra = []): \Ticket
    {
        $ticket = new \Ticket();
        $this->integer((int)$ticket->add($extra + [
            'name' => $this->getUniqueString(),
            'content' => 'Parent/child propagation',
            'entities_id' => getItemByTypeName('Entity', '_test_root_entity', true),
            'status' => $status,
        ]))->isGreaterThan(0);
        $this->boolean($ticket->getFromDB($ticket->getID()))->isTrue();
        $this->integer((int)$ticket->fields['status'])->isEqualTo($status);
        return $ticket;
    }

    private function link(\Ticket $parent, \Ticket $child, int $type = \Ticket_Ticket::SON_OF): void
    {
        $link = new \Ticket_Ticket();
        $this->integer((int)$link->add([
            'tickets_id_1' => $child->getID(),
            'tickets_id_2' => $parent->getID(),
            'link' => $type,
        ]))->isGreaterThan(0);
    }

    private function status(\Ticket $ticket, int $expected): void
    {
        $this->boolean($ticket->getFromDB($ticket->getID()))->isTrue();
        $this->integer((int)$ticket->fields['status'])->isEqualTo($expected);
    }

    private function start(\Ticket $parent, int $status = \Ticket::ASSIGNED): void
    {
        $this->boolean($parent->update([
            'id' => $parent->getID(),
            'status' => $status,
            '_itil_assign' => ['_type' => 'user', 'users_id' => \Session::getLoginUserID()],
        ]))->isTrue();
        $this->status($parent, $status);
    }

    private function followup(\Ticket $ticket, array $extra = []): \ITILFollowup
    {
        $followup = new \ITILFollowup();
        $this->integer((int)$followup->add(\Toolbox::addslashes_deep($extra + [
            'itemtype' => 'Ticket',
            'items_id' => $ticket->getID(),
            'content' => "Public update with an apostrophe: it's fixed.",
            'is_private' => 0,
        ])))->isGreaterThan(0);
        return $followup;
    }

    private function followups(\Ticket $ticket): array
    {
        return (new \ITILFollowup())->find(['itemtype' => 'Ticket', 'items_id' => $ticket->getID()]);
    }

    public function testDisabledByDefault()
    {
        $parent = $this->ticket();
        $child = $this->ticket();
        $this->link($parent, $child);
        $this->start($parent);
        $this->status($child, \Ticket::INCOMING);
        $this->followup($parent);
        $this->array($this->followups($child))->isEmpty();
        $this->boolean($parent->update(['id' => $parent->getID(), 'status' => \Ticket::SOLVED]))->isTrue();
        $this->status($child, \Ticket::INCOMING);
        $this->boolean($parent->update(['id' => $parent->getID(), 'status' => \Ticket::CLOSED]))->isTrue();
        $this->status($child, \Ticket::INCOMING);
    }

    public function testProcessingOnlyMovesNewDescendants()
    {
        $this->enable('processing');
        $parent = $this->ticket();
        $child = $this->ticket();
        $grandchild = $this->ticket();
        $waiting = $this->ticket(\Ticket::WAITING);
        $solved = $this->ticket(\Ticket::SOLVED);
        $closed = $this->ticket(\Ticket::CLOSED);
        $ordinary = $this->ticket();
        foreach ([$child, $waiting, $solved, $closed] as $ticket) {
            $this->link($parent, $ticket);
        }
        $this->link($child, $grandchild);
        $this->link($parent, $ordinary, \Ticket_Ticket::LINK_TO);
        $this->start($parent);
        $this->status($child, \Ticket::ASSIGNED);
        $this->status($grandchild, \Ticket::ASSIGNED);
        $this->status($waiting, \Ticket::WAITING);
        $this->status($solved, \Ticket::SOLVED);
        $this->status($closed, \Ticket::CLOSED);
        $this->status($ordinary, \Ticket::INCOMING);
        $this->array($child->getUsers(\CommonITILActor::ASSIGN))->isEmpty();
        $this->boolean(\TicketChildPropagation::isUpdatingStatus($child->getID()))->isFalse();
    }

    public function testPlannedProcessingAndUpwardIsolation()
    {
        $this->enable('processing', 'solved', 'closed');
        $ancestor = $this->ticket();
        $parent = $this->ticket();
        $child = $this->ticket();
        $this->link($ancestor, $parent);
        $this->link($parent, $child);
        $this->start($parent, \Ticket::PLANNED);
        $this->status($child, \Ticket::PLANNED);
        $this->status($ancestor, \Ticket::INCOMING);
        $this->boolean($child->update(['id' => $child->getID(), 'status' => \Ticket::CLOSED]))->isTrue();
        $this->status($parent, \Ticket::PLANNED);
        $this->status($ancestor, \Ticket::INCOMING);
    }

    public function testSolutionResolutionAndClosure()
    {
        $this->enable('solved', 'closed');
        $parent = $this->ticket();
        $child = $this->ticket();
        $grandchild = $this->ticket();
        $alreadyClosed = $this->ticket(\Ticket::CLOSED);
        $this->link($parent, $child);
        $this->link($child, $grandchild);
        $this->link($parent, $alreadyClosed);
        $this->start($parent);
        $solution = new \ITILSolution();
        $this->integer((int)$solution->add([
            'itemtype' => 'Ticket',
            'items_id' => $parent->getID(),
            'content' => 'Service restored after restarting the router.',
        ]))->isGreaterThan(0);
        $this->status($parent, \Ticket::SOLVED);
        foreach ([$child, $grandchild] as $ticket) {
            $this->status($ticket, \Ticket::SOLVED);
            $this->variable($ticket->fields['solvedate'])->isNotNull();
            $solutions = (new \ITILSolution())->find(['itemtype' => 'Ticket', 'items_id' => $ticket->getID()]);
            $this->array($solutions)->hasSize(1);
            $this->string(reset($solutions)['content'])->isEqualTo($solution->fields['content']);
        }
        $this->boolean($parent->update(['id' => $parent->getID(), 'status' => \Ticket::CLOSED]))->isTrue();
        foreach ([$child, $grandchild, $alreadyClosed] as $ticket) {
            $this->status($ticket, \Ticket::CLOSED);
            $this->variable($ticket->fields['closedate'])->isNotNull();
        }
    }

    public function testDirectResolutionAndIndependentSwitches()
    {
        $this->enable('solved');
        $parent = $this->ticket();
        $child = $this->ticket();
        $this->link($parent, $child);
        $this->boolean($parent->update(['id' => $parent->getID(), 'status' => \Ticket::SOLVED]))->isTrue();
        $this->status($child, \Ticket::SOLVED);
        $solutions = (new \ITILSolution())->find(['itemtype' => 'Ticket', 'items_id' => $child->getID()]);
        $this->array($solutions)->hasSize(1);
        $this->string(reset($solutions)['content'])->contains((string)$parent->getID());
        $this->boolean($parent->update(['id' => $parent->getID(), 'status' => \Ticket::CLOSED]))->isTrue();
        $this->status($child, \Ticket::SOLVED);
    }

    public function testClosureWithoutResolutionAndNoReopening()
    {
        $this->enable('closed', 'processing');
        $parent = $this->ticket();
        $child = $this->ticket();
        $this->link($parent, $child);
        $this->boolean($parent->update(['id' => $parent->getID(), 'status' => \Ticket::SOLVED]))->isTrue();
        $this->status($child, \Ticket::INCOMING);
        $this->boolean($parent->update(['id' => $parent->getID(), 'status' => \Ticket::CLOSED]))->isTrue();
        $this->status($child, \Ticket::CLOSED);
        $this->start($parent);
        $this->status($child, \Ticket::CLOSED);
    }

    public function testCyclesAndDiamondsDoNotDuplicateFollowups()
    {
        $this->enable('public_followups', 'solved');
        $root = $this->ticket();
        $left = $this->ticket();
        $right = $this->ticket();
        $shared = $this->ticket();
        $this->link($root, $left);
        $this->link($root, $right);
        $this->link($left, $shared);
        $this->link($right, $shared);
        $this->link($shared, $root);
        $source = $this->followup($root);
        foreach ([$root, $left, $right, $shared] as $ticket) {
            $rows = $this->followups($ticket);
            $this->array($rows)->hasSize(1);
            $copy = reset($rows);
            $this->string($copy['content'])->isEqualTo($source->fields['content']);
            $this->integer((int)$copy['is_private'])->isEqualTo(0);
            $this->integer((int)$copy['users_id'])->isEqualTo((int)$source->fields['users_id']);
        }
        $this->boolean($root->update(['id' => $root->getID(), 'status' => \Ticket::SOLVED]))->isTrue();
        foreach ([$root, $left, $right, $shared] as $ticket) {
            $this->status($ticket, \Ticket::SOLVED);
            $solutions = (new \ITILSolution())->find(['itemtype' => 'Ticket', 'items_id' => $ticket->getID()]);
            $this->array($solutions)->hasSize($ticket === $root ? 0 : 1);
        }
    }

    public function testPrivateFollowupsAndUnrelatedTicketsAreExcluded()
    {
        $this->enable('public_followups');
        $parent = $this->ticket();
        $child = $this->ticket();
        $unrelated = $this->ticket();
        $this->link($parent, $child);
        $this->link($parent, $unrelated, \Ticket_Ticket::LINK_TO);
        $this->followup($parent, ['is_private' => 1, 'content' => 'Private diagnosis']);
        $this->array($this->followups($child))->isEmpty();
        $this->followup($parent);
        $this->array($this->followups($child))->hasSize(1);
        $this->array($this->followups($unrelated))->isEmpty();
        $this->followup($child);
        $this->array($this->followups($parent))->hasSize(2);
    }

    public function testChildPermissionsAndLifecycleAreRespected()
    {
        $this->enable('solved', 'public_followups');
        $parent = $this->ticket(\Ticket::ASSIGNED, ['_users_id_assign' => \Session::getLoginUserID()]);
        $hidden = $this->ticket(\Ticket::INCOMING, [
            'entities_id' => getItemByTypeName('Entity', '_test_child_1', true),
        ]);
        $blocked = $this->ticket();
        $this->link($parent, $hidden);
        $this->link($parent, $blocked);
        $this->setEntity('_test_root_entity', false);
        $_SESSION['glpiactiveprofile'][\Ticket::STATUS_MATRIX_FIELD][\Ticket::INCOMING][\Ticket::SOLVED] = 0;
        $this->boolean($parent->update(['id' => $parent->getID(), 'status' => \Ticket::SOLVED]))->isTrue();
        $this->status($parent, \Ticket::SOLVED);
        $this->status($hidden, \Ticket::INCOMING);
        $this->status($blocked, \Ticket::INCOMING);
        $this->hasSessionMessages(WARNING, ['Some child tickets could not be updated. Check their permissions and status transitions.']);
        $this->followup($parent);
        $this->array($this->followups($hidden))->isEmpty();
        $this->array($this->followups($blocked))->hasSize(1);
        $this->hasSessionMessages(WARNING, ['Some child tickets could not receive the public followup. Check your followup permissions.']);
    }

    public function testDeletedChildrenAreSkipped()
    {
        $this->enable('closed', 'public_followups');
        $parent = $this->ticket();
        $child = $this->ticket();
        $this->link($parent, $child);
        $this->boolean($child->delete(['id' => $child->getID()]))->isTrue();
        $this->followup($parent);
        $this->boolean($parent->update(['id' => $parent->getID(), 'status' => \Ticket::CLOSED]))->isTrue();
        $this->status($child, \Ticket::INCOMING);
        $this->array($this->followups($child))->isEmpty();
    }

    public function testConfigurationRenderingAndSaving()
    {
        require_once GLPI_ROOT . '/src/twig/twig.class.php';
        $config = new \Config();
        ob_start();
        $config->showFormHelpdesk();
        $html = ob_get_clean();
        foreach (self::OPTIONS as $name) {
            $this->string($html)->contains('name="' . $name . '"');
        }
        foreach ([1, 0] as $value) {
            $config->update(['id' => 1, 'update' => 1] + array_fill_keys(self::OPTIONS, $value));
            $stored = \Config::getConfigurationValues('core', self::OPTIONS);
            foreach (self::OPTIONS as $name) {
                $this->integer((int)$stored[$name])->isEqualTo($value);
            }
        }
    }

    public function testSystemCronCanCloseDescendants()
    {
        $this->enable('closed');
        $parent = $this->ticket(\Ticket::SOLVED);
        $child = $this->ticket(\Ticket::SOLVED);
        $this->link($parent, $child);
        $_SESSION['glpicronuserrunning'] = 'cron_closeticket';
        $_SESSION['glpiactiveprofile'] = [];
        $this->boolean($parent->update([
            'id' => $parent->getID(),
            'status' => \Ticket::CLOSED,
            '_auto_update' => true,
        ]))->isTrue();
        $this->status($child, \Ticket::CLOSED);
    }

    public function testFollowupDocumentsRemainAccessibleOnChild()
    {
        $this->enable('public_followups');
        $parent = $this->ticket();
        $child = $this->ticket();
        $this->link($parent, $child);
        $name = 'child-propagation-' . uniqid() . '.png';
        copy(__DIR__ . '/../fixtures/uploads/foo.png', GLPI_TMP_DIR . '/' . $name);
        $source = $this->followup($parent, [
            'content' => '#childpropagationimage#',
            '_content' => [$name],
            '_tag_content' => ['childpropagationimage'],
        ]);
        $sourceLinks = (new \Document_Item())->find(['itemtype' => 'ITILFollowup', 'items_id' => $source->getID()]);
        $this->array($sourceLinks)->hasSize(1);
        $copies = $this->followups($child);
        $this->array($copies)->hasSize(1);
        $copy = reset($copies);
        $copyLinks = (new \Document_Item())->find(['itemtype' => 'ITILFollowup', 'items_id' => $copy['id']]);
        $this->array($copyLinks)->hasSize(1);
        $this->integer((int)reset($copyLinks)['documents_id'])->isEqualTo((int)reset($sourceLinks)['documents_id']);
        $this->string($copy['content'])->contains('tickets_id=' . $child->getID());
        $this->string($copy['content'])->notContains('tickets_id=' . $parent->getID());
    }

    public function testMutedResolutionDoesNotQueueChildNotifications()
    {
        global $CFG_GLPI;
        $this->enable('solved');
        $CFG_GLPI['use_notifications'] = 1;
        $CFG_GLPI['notifications_mailing'] = 1;
        $_SESSION['glpinotification_to_myself'] = 1;
        $entity = getItemByTypeName('Entity', '_test_root_entity');
        $this->boolean($entity->update(['id' => $entity->getID(), 'delay_send_emails' => 60]))->isTrue();
        $email = new \UserEmail();
        $this->integer((int)$email->add([
            'users_id' => \Session::getLoginUserID(),
            'email' => 'child-propagation@example.org',
            'is_default' => 1,
        ]))->isGreaterThan(0);
        $requester = ['_users_id_requester' => \Session::getLoginUserID()];
        $parent = $this->ticket(\Ticket::INCOMING, $requester);
        $child = $this->ticket(\Ticket::INCOMING, $requester);
        $this->link($parent, $child);
        $before = (new \QueuedNotification())->find([]);
        $this->boolean($parent->update([
            'id' => $parent->getID(),
            'status' => \Ticket::SOLVED,
            '_disablenotif' => true,
        ]))->isTrue();
        $this->status($child, \Ticket::SOLVED);
        $this->array((new \QueuedNotification())->find([]))->isIdenticalTo($before);

        $ordinary = $this->ticket(\Ticket::INCOMING, $requester);
        $this->boolean($ordinary->update(['id' => $ordinary->getID(), 'status' => \Ticket::SOLVED]))->isTrue();
        $queued = (new \QueuedNotification())->find(['itemtype' => 'Ticket', 'items_id' => $ordinary->getID()]);
        $this->string(implode('\n', array_column($queued, 'name')))->contains('Ticket solved');
    }
}
