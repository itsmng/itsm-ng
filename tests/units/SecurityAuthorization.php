<?php

namespace tests\units;

class SecurityAuthorization extends \DbTestCase
{
    public function getTestedClassName()
    {
        return \User::class;
    }

    protected function createSecurityItem($type, array $input)
    {
        $item = new $type();
        $id = $item->add($input);
        $this->integer((int) $id)->isGreaterThan(0);
        $this->boolean($item->getFromDB($id))->isTrue();
        return $item;
    }

    protected function runSecurityAction($processor, $action, $item, array $ids, array $input = [])
    {
        $results = [];
        $controller = new \atoum\atoum\mock\controller();
        $controller->__construct = function () {};
        $ma = new \mock\MassiveAction([], [], '', false, $controller);
        $ma->getMockController()->getAction = $action;
        $ma->getMockController()->getInput = $input;
        $ma->POST = $input;
        $ma->getMockController()->addMessage = function () {};
        $ma->getMockController()->setRedirect = function () {};
        $ma->getMockController()->itemDone = function ($type, $ids, $result) use (&$results) {
            foreach ((array) $ids as $id) {
                $results[$id] = $result;
            }
        };
        $processor::processMassiveActionsForOneItemtype($ma, $item, $ids);
        return $results;
    }

    protected function restrictSecurityProfile()
    {
        $profile = getItemByTypeName('Profile', 'Technician');
        $this->object($profile)->isInstanceOf(\Profile::class);
        $_SESSION['glpiactiveprofile'] = $profile->fields;
        // Isolate hierarchy checks from the global user-management permission.
        $_SESSION['glpiactiveprofile']['user'] = ALLSTANDARDRIGHT;
        $_SESSION['glpiactiveprofile']['profile'] = 0;
    }

    public function testUserDeletionRespectsProfileHierarchy()
    {
        $this->login();
        $this->setEntity(0, true);
        $high = $this->createSecurityItem(\User::class, [
            'name' => 'security_high', '_profiles_id' => 4, '_entities_id' => 0,
        ]);
        $low = $this->createSecurityItem(\User::class, [
            'name' => 'security_low', '_profiles_id' => 1, '_entities_id' => 0,
        ]);
        $this->restrictSecurityProfile();
        foreach ([DELETE, PURGE] as $right) {
            $this->boolean($high->can($high->getID(), $right))->isFalse();
            $this->boolean($low->can($low->getID(), $right))->isTrue();
        }
        foreach (['delete', 'purge'] as $action) {
            $this->array($this->runSecurityAction(\MassiveAction::class, $action, $high, [$high->getID()]))
                ->isIdenticalTo([$high->getID() => \MassiveAction::ACTION_NORIGHT]);
            $this->boolean($high->getFromDB($high->getID()))->isTrue();
            $this->integer((int) $high->fields['is_deleted'])->isEqualTo(0);
        }
    }

    public function testProfilePurgeRespectsHierarchyAndEntity()
    {
        $this->login();
        $this->setEntity(0, true);
        $source = getItemByTypeName('User', TU_USER);
        $child = (int) getItemByTypeName('Entity', '_test_child_1', true);
        $high = $this->createSecurityItem(\Profile_User::class, [
            'users_id' => $source->getID(), 'profiles_id' => 4, 'entities_id' => $child,
        ]);
        $low = $this->createSecurityItem(\Profile_User::class, [
            'users_id' => $source->getID(), 'profiles_id' => 1, 'entities_id' => $child,
        ]);
        $outside = $this->createSecurityItem(\Profile_User::class, [
            'users_id' => $source->getID(), 'profiles_id' => 1, 'entities_id' => 0,
        ]);
        $this->restrictSecurityProfile();
        $_SESSION['glpiactiveentities'] = [$child];
        $this->boolean($high->can($high->getID(), PURGE))->isFalse();
        $this->boolean($low->can($low->getID(), PURGE))->isTrue();
        $this->boolean($outside->can($outside->getID(), PURGE))->isFalse();
    }

    public function testBulkCommentChecksGlobalUpdateRight()
    {
        $this->login();
        $this->setEntity(0, true);
        $item = getItemByTypeName('Computer', '_test_pc01');
        $comment = $item->fields['comment'];
        $_SESSION['glpiactiveprofile']['computer'] = READ;
        $this->boolean($item->canUpdateItem())->isTrue();
        $this->array($this->runSecurityAction(\MassiveAction::class, 'amend_comment', $item, [$item->getID()], ['amendment' => 'forbidden']))
            ->isIdenticalTo([$item->getID() => \MassiveAction::ACTION_NORIGHT]);
        $item->getFromDB($item->getID());
        $this->variable($item->fields['comment'])->isIdenticalTo($comment);
        $_SESSION['glpiactiveprofile']['computer'] = READ | UPDATE;
        $this->array($this->runSecurityAction(\MassiveAction::class, 'amend_comment', $item, [$item->getID()], ['amendment' => 'allowed']))
            ->isIdenticalTo([$item->getID() => \MassiveAction::ACTION_OK]);
    }

    public function testBulkTransferChecksUpdateRight()
    {
        $this->login();
        $this->setEntity(0, true);
        $item = getItemByTypeName('Computer', '_test_pc01');
        unset($_SESSION['glpitransfer_list']);
        $_SESSION['glpiactiveprofile']['computer'] = READ;
        $this->array($this->runSecurityAction(\MassiveAction::class, 'add_transfer_list', $item, [$item->getID()]))
            ->isIdenticalTo([$item->getID() => \MassiveAction::ACTION_NORIGHT]);
        $this->array($_SESSION['glpitransfer_list']['Computer'])->isEmpty();
        $_SESSION['glpiactiveprofile']['computer'] = READ | UPDATE;
        $this->array($this->runSecurityAction(\MassiveAction::class, 'add_transfer_list', $item, [$item->getID()]))
            ->isIdenticalTo([$item->getID() => \MassiveAction::ACTION_OK]);
        $this->array($_SESSION['glpitransfer_list']['Computer'])->hasKey($item->getID());
    }

    public function testBulkNoteChecksTargetReadRight()
    {
        $this->login();
        $this->setEntity(0, true);
        $item = getItemByTypeName('Computer', '_test_pc01');
        $criteria = ['itemtype' => 'Computer', 'items_id' => $item->getID()];
        $before = countElementsInTable('glpi_notepads', $criteria);
        $_SESSION['glpiactiveprofile']['computer'] = UPDATENOTE;
        $this->array($this->runSecurityAction(\MassiveAction::class, 'add_note', $item, [$item->getID()], ['add_note' => 'security note']))
            ->isIdenticalTo([$item->getID() => \MassiveAction::ACTION_NORIGHT]);
        $this->integer(countElementsInTable('glpi_notepads', $criteria))->isEqualTo($before);
        $_SESSION['glpiactiveprofile']['computer'] = READ | UPDATENOTE;
        $this->array($this->runSecurityAction(\MassiveAction::class, 'add_note', $item, [$item->getID()], ['add_note' => 'security note']))
            ->isIdenticalTo([$item->getID() => \MassiveAction::ACTION_OK]);
        $this->integer(countElementsInTable('glpi_notepads', $criteria))->isEqualTo($before + 1);
    }

    public function testBulkUnlockChecksUnlockRight()
    {
        $this->login();
        $this->setEntity(0, true);
        $item = getItemByTypeName('Computer', '_test_pc01');
        $_SESSION['glpiactiveprofile']['computer'] = READ | UPDATE;
        $this->array($this->runSecurityAction(\ObjectLock::class, 'unlock', $item, [$item->getID()]))
            ->isIdenticalTo([$item->getID() => \MassiveAction::ACTION_NORIGHT]);
    }

    public function testUnlockChecksSavedProfileAndEntityScope()
    {
        $this->login();
        $this->setEntity(0, true);
        $item = getItemByTypeName('Computer', '_test_pc01');
        $_SESSION['glpiactiveprofile']['computer'] = READ | UPDATE | UNLOCK;
        $this->boolean(\ObjectLock::canUnlockItem($item, $item->getID()))->isTrue();
        $_SESSION['glpilocksavedprofile'] = $_SESSION['glpiactiveprofile'];
        $_SESSION['glpiactiveprofile']['computer'] = READ;
        $restricted_profile = $_SESSION['glpiactiveprofile'];
        $this->boolean(\ObjectLock::canUnlockItem($item, $item->getID()))->isTrue();
        $this->array($_SESSION['glpiactiveprofile'])->isIdenticalTo($restricted_profile);
        $_SESSION['glpiactiveentities'] = [0];
        $this->boolean(\ObjectLock::canUnlockItem($item, $item->getID()))->isFalse();
        $this->array($_SESSION['glpiactiveprofile'])->isIdenticalTo($restricted_profile);
        $_SESSION['glpiactiveentities'] = [$item->getEntityID()];
        $_SESSION['glpilocksavedprofile']['computer'] = READ | UNLOCK;
        $this->boolean(\ObjectLock::canUnlockItem($item, $item->getID()))->isFalse();
        $this->array($_SESSION['glpiactiveprofile'])->isIdenticalTo($restricted_profile);
    }

    public function testBulkNotificationRemovalChecksRights()
    {
        $this->login();
        $this->setEntity(0, true);
        $item = $this->createSecurityItem(\Notification::class, [
            'name' => 'security_notification', 'itemtype' => 'Ticket', 'event' => 'new', 'entities_id' => 0,
        ]);
        $link = $this->createSecurityItem(\Notification_NotificationTemplate::class, [
            'notifications_id' => $item->getID(), 'notificationtemplates_id' => 1, 'mode' => 'mail',
        ]);
        $_SESSION['glpiactiveprofile'][\NotificationTemplate::$rightname] = ALLSTANDARDRIGHT;
        $rights = $_SESSION['glpiactiveprofile']['notification'];
        $_SESSION['glpiactiveprofile']['notification'] = 0;
        $this->array($this->runSecurityAction(\Notification::class, 'remove_all_template', $item, [$item->getID()]))
            ->isIdenticalTo([$item->getID() => \MassiveAction::ACTION_KO]);
        $this->boolean($link->getFromDB($link->getID()))->isTrue();
        $_SESSION['glpiactiveprofile']['notification'] = $rights;
        $this->array($this->runSecurityAction(\Notification::class, 'remove_all_template', $item, [$item->getID()]))
            ->isIdenticalTo([$item->getID() => \MassiveAction::ACTION_OK]);
        $this->boolean($link->getFromDB($link->getID()))->isFalse();
    }

    public function testBulkRuleExportChecksReadRight()
    {
        $this->login();
        $this->setEntity(0, true);
        $item = $this->createSecurityItem(\RuleTicket::class, ['name' => 'security_rule']);
        $rights = $_SESSION['glpiactiveprofile'][$item::$rightname];
        $_SESSION['glpiactiveprofile'][$item::$rightname] = 0;
        $this->array($this->runSecurityAction(\Rule::class, 'export', $item, [$item->getID()]))
            ->isIdenticalTo([$item->getID() => \MassiveAction::ACTION_NORIGHT]);
        $this->array($_SESSION['exportitems'])->isEmpty();
        $_SESSION['glpiactiveprofile'][$item::$rightname] = $rights;
        $this->array($this->runSecurityAction(\Rule::class, 'export', $item, [$item->getID()]))
            ->isIdenticalTo([$item->getID() => \MassiveAction::ACTION_OK]);
    }

    public function testBulkAssetLinksCheckBothEntities()
    {
        $this->login();
        $this->setEntity(0, true);
        $computer = getItemByTypeName('Computer', '_test_pc01');
        $outside = getItemByTypeName('Computer', '_test_pc11');
        foreach ([\Certificate::class => \Certificate_Item::class, \Domain::class => \Domain_Item::class] as $type => $relation_type) {
            $source = $this->createSecurityItem($type, [
                'name' => 'security_link_' . $type, 'entities_id' => $computer->getEntityID(),
            ]);
            $action = $type === \Certificate::class ? 'Certificate:install' : 'install';
            $input = ['typeitem' => 'Computer', 'item_item' => $outside->getID()];
            $this->array($this->runSecurityAction($type, $action, $source, [$source->getID()], $input))
                ->isIdenticalTo([$source->getID() => \MassiveAction::ACTION_NORIGHT]);
            $relation = new $relation_type();
            $this->integer(countElementsInTable($relation->getTable(), [$source->getForeignKeyField() => $source->getID()]))->isEqualTo(0);

            $input['item_item'] = $computer->getID();
            $this->array($this->runSecurityAction($type, $action, $source, [$source->getID()], $input))
                ->isIdenticalTo([$source->getID() => \MassiveAction::ACTION_OK]);
            $this->integer(countElementsInTable($relation->getTable(), [$source->getForeignKeyField() => $source->getID()]))->isEqualTo(1);

            $rights = $_SESSION['glpiactiveprofile'];
            $_SESSION['glpiactiveprofile'][$source::$rightname] = 0;
            $_SESSION['glpiactiveprofile']['computer'] = 0;
            $action = $type === \Certificate::class ? 'Certificate:uninstall' : 'uninstall';
            $this->array($this->runSecurityAction($type, $action, $source, [$source->getID()], $input))
                ->isIdenticalTo([$source->getID() => \MassiveAction::ACTION_NORIGHT]);
            $this->integer(countElementsInTable($relation->getTable(), [$source->getForeignKeyField() => $source->getID()]))->isEqualTo(1);
            $_SESSION['glpiactiveprofile'] = $rights;
            $relation->getFromDBByCrit([$source->getForeignKeyField() => $source->getID()]);
            $this->array($this->runSecurityAction($type, $action, $source, [$source->getID()], $input))
                ->isIdenticalTo([$source->getID() => \MassiveAction::ACTION_OK]);
            $this->integer(countElementsInTable($relation->getTable(), [$source->getForeignKeyField() => $source->getID()]))->isEqualTo(0);
        }
    }

    public function testCloneCannotEscalateProfilesOrEntityMemberships()
    {
        $this->login();
        $this->setEntity(0, true);
        $inside = (int) getItemByTypeName('Entity', '_test_root_entity', true);
        $outside = (int) getItemByTypeName('Entity', '_test_child_1', true);
        $source = $this->createSecurityItem(\User::class, [
            'name' => 'security_clone', '_profiles_id' => 4, '_entities_id' => $inside,
        ]);
        foreach ([$inside, $outside] as $entity) {
            $this->createSecurityItem(\Profile_User::class, [
                'users_id' => $source->getID(), 'profiles_id' => 1, 'entities_id' => $entity,
            ]);
        }
        $groups = [];
        foreach ([$inside, $outside] as $entity) {
            $group = $this->createSecurityItem(\Group::class, [
                'name' => 'security_clone_group_' . $entity, 'entities_id' => $entity,
            ]);
            $groups[$entity] = $group->getID();
            $this->createSecurityItem(\Group_User::class, [
                'users_id' => $source->getID(), 'groups_id' => $group->getID(),
            ]);
        }
        $this->restrictSecurityProfile();
        $_SESSION['glpiactiveentities'] = [$inside];
        $_SESSION['glpiactive_entity'] = $inside;
        $clone = $source->clone(['is_template' => 0]);
        $this->integer((int) $clone)->isGreaterThan(0);
        $this->integer(countElementsInTable('glpi_profiles_users', ['users_id' => $clone, 'profiles_id' => 4]))->isEqualTo(0);
        $this->integer(countElementsInTable('glpi_profiles_users', ['users_id' => $clone, 'entities_id' => $outside]))->isEqualTo(0);
        $this->integer(countElementsInTable('glpi_groups_users', ['users_id' => $clone, 'groups_id' => $groups[$outside]]))->isEqualTo(0);
        // Main copies permitted memberships; Stable has no membership-cloning hook.
        $copies_memberships = (new \ReflectionMethod(\User::class, 'post_clone'))->getDeclaringClass()->getName() === \User::class;
        $this->integer(countElementsInTable('glpi_groups_users', ['users_id' => $clone, 'groups_id' => $groups[$inside]]))->isEqualTo($copies_memberships ? 1 : 0);
        if ($copies_memberships) {
            $this->integer(countElementsInTable('glpi_profiles_users', ['users_id' => $clone, 'profiles_id' => 1, 'entities_id' => $inside]))->isEqualTo(1);
        }
    }
}
