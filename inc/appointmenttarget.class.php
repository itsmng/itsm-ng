<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class AppointmentTarget extends CommonDBTM
{
    public static $rightname = 'appointment';

    public static function getTypeName($nb = 0)
    {
        return _n('Appointment target', 'Appointment targets', $nb);
    }

    public static function getMenuName()
    {
        return Appointment::getTypeName(Session::getPluralNumber());
    }

    public static function getMenuContent()
    {
        global $CFG_GLPI;

        if (!static::canView()) {
            return [];
        }

        return [
           'title' => static::getMenuName(),
           'page'  => $CFG_GLPI['root_doc'] . '/front/appointment.php',
           'icon'  => Appointment::getIcon(),
        ];
    }

    public static function canView()
    {
        return Session::haveRightsOr(self::$rightname, [READ, CREATE, UPDATE]);
    }

    public static function canCreate()
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    public static function getForbiddenActionsForMenu()
    {
        return ['add'];
    }

    public static function getAdditionalMenuLinks()
    {
        global $CFG_GLPI;

        if (!static::canView()) {
            return false;
        }

        return [
           'showall' => $CFG_GLPI['root_doc'] . '/front/appointment.php'
        ];
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (
            !$withtemplate
            && in_array($item->getType(), ['User', 'Group'])
            && Session::haveRight(self::$rightname, UPDATE)
        ) {
            return self::getTypeName(1);
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if (!in_array($item->getType(), ['User', 'Group'])) {
            return false;
        }

        self::showForItem($item);
        return true;
    }

    public function getFromDBByItem($itemtype, $items_id)
    {
        return $this->getFromDBByCrit([
           'itemtype' => $itemtype,
           'items_id' => $items_id,
        ]);
    }

    public function cleanDBonPurge()
    {
        $this->deleteChildrenAndRelationsFromDb([
           Appointment::class,
           AppointmentAvailability::class,
           AppointmentAvailabilityException::class,
        ]);
    }

    public static function showForItem(CommonDBTM $item)
    {
        if (!Session::haveRight(self::$rightname, UPDATE)) {
            return false;
        }

        $target = new self();
        $exists = $target->getFromDBByItem($item->getType(), $item->getID());

        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixe' aria-label='Appointment target'>";
        echo "<tr><th colspan='2'>" . self::getTypeName(1) . "</th></tr>";
        echo "<tr class='tab_bg_1'>";

        if ($exists) {
            echo "<td class='center'>";
            Html::showSimpleForm(
                self::getFormURL(),
                'update',
                $target->fields['is_active'] ? __('Make unavailable') : __('Make available'),
                [
                  'id'        => $target->fields['id'],
                  'is_active' => $target->fields['is_active'] ? 0 : 1,
                ]
            );
            echo "</td><td class='center'>";
            Html::showSimpleForm(
                self::getFormURL(),
                'purge',
                __('Prohibit appointments'),
                ['id' => $target->fields['id']],
                '',
                '',
                [__('Are you sure?'), __('Existing appointments and availability rules will be removed.')]
            );
            echo "</td>";
        } else {
            echo "<td class='center' colspan='2'>";
            Html::showSimpleForm(
                self::getFormURL(),
                'add',
                __('Authorize appointments'),
                [
                  'itemtype'     => $item->getType(),
                  'items_id'     => $item->getID(),
                  'entities_id'  => method_exists($item, 'getEntityID') ? $item->getEntityID() : $_SESSION['glpiactive_entity'],
                  'is_recursive' => method_exists($item, 'isRecursive') ? (int)$item->isRecursive() : 0,
                  'is_active'    => 1,
                ]
            );
            echo "</td>";
        }

        echo "</tr></table></div>";

        if ($exists) {
            AppointmentAvailability::showForTarget($target->fields['id']);
            AppointmentAvailabilityException::showForTarget($target->fields['id']);
        }
    }

    public static function getTargetLabel(array $target)
    {
        if ($item = getItemForItemtype($target['itemtype'])) {
            if ($item->getFromDB($target['items_id'])) {
                return sprintf(__('%1$s - %2$s'), $item->getTypeName(1), $item->getName());
            }
        }
        return NOT_AVAILABLE;
    }

    public function rawSearchOptions()
    {
        $tab = [];
        $tab[] = [
           'id'       => 'common',
           'name'     => __('Characteristics')
        ];
        $tab[] = [
           'id'       => 1,
           'table'    => $this->getTable(),
           'field'    => 'itemtype',
           'name'     => __('Item type'),
           'datatype' => 'string'
        ];
        $tab[] = [
           'id'       => 2,
           'table'    => $this->getTable(),
           'field'    => 'is_active',
           'name'     => __('Active'),
           'datatype' => 'bool'
        ];
        $tab[] = [
           'id'       => 80,
           'table'    => 'glpi_entities',
           'field'    => 'completename',
           'name'     => Entity::getTypeName(1),
           'datatype' => 'dropdown'
        ];
        return $tab;
    }
}
