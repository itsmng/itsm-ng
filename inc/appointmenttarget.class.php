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
            return Appointment::getTypeName(Session::getPluralNumber());
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

    public function prepareInputForAdd($input)
    {
        return $this->prepareEntityInput($input);
    }

    public function prepareInputForUpdate($input)
    {
        return $this->prepareEntityInput($input);
    }

    private function prepareEntityInput(array $input)
    {
        if (!isset($input['entities_id']) || (int)$input['entities_id'] < 0) {
            $input['entities_id'] = $_SESSION['glpiactive_entity'] ?? 0;
        }

        if (!isset($input['is_recursive'])) {
            $input['is_recursive'] = 0;
        }

        return $input;
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
        global $CFG_GLPI;

        if (!Session::haveRight(self::$rightname, UPDATE)) {
            return false;
        }

        $target = new self();
        $exists = $target->getFromDBByItem($item->getType(), $item->getID());

        echo "<div class='appointment-target-admin'>";
        echo "<div class='appointment-target-admin__header'>";
        echo "<div>";
        echo "<h2>" . Appointment::getTypeName(Session::getPluralNumber()) . "</h2>";
        echo "<p>" . Html::clean($item->getName()) . "</p>";
        echo "</div>";
        echo "<div class='appointment-target-admin__actions'>";

        if ($exists) {
            echo "<div>";
            Html::showSimpleForm(
                self::getFormURL(),
                'update',
                $target->fields['is_active'] ? __('Make unavailable') : __('Make available'),
                [
                  'id'        => $target->fields['id'],
                  'is_active' => $target->fields['is_active'] ? 0 : 1,
                ]
            );
            echo "</div><div>";
            Html::showSimpleForm(
                self::getFormURL(),
                'purge',
                __('Prohibit appointments'),
                ['id' => $target->fields['id']],
                '',
                '',
                [__('Are you sure?'), __('Existing appointments and availability rules will be removed.')]
            );
            echo "</div>";
        } else {
            echo "<div>";
            Html::showSimpleForm(
                self::getFormURL(),
                'add',
                __('Authorize appointments'),
                [
                  'itemtype'     => $item->getType(),
                  'items_id'     => $item->getID(),
                  'entities_id'  => method_exists($item, 'getEntityID') && $item->getEntityID() >= 0
                     ? $item->getEntityID()
                     : $_SESSION['glpiactive_entity'],
                  'is_recursive' => method_exists($item, 'isRecursive') ? (int)$item->isRecursive() : 0,
                  'is_active'    => 1,
                ]
            );
            echo "</div>";
        }

        echo "</div></div>";

        if ($exists) {
            echo Html::css('public/lib/fullcalendar.css', ['media' => '']);
            echo Html::script('public/lib/fullcalendar.js');
            echo Html::script('js/appointment.js');

            echo "<div class='appointment-target-admin__body'>";
            echo "<aside class='appointment-office-hours'>";
            AppointmentAvailability::showForTarget($target->fields['id']);
            echo "</aside>";
            echo "<section class='appointment-target-calendar'>";
            echo "<div class='appointment-target-calendar__heading'>";
            echo "<h3>" . AppointmentAvailabilityException::getTypeName(Session::getPluralNumber()) . "</h3>";
            echo "</div>";
            echo "<div id='appointment-target-calendar'></div>";
            echo "</section>";
            echo "</div>";

            $options = [
               'appointmenttargets_id' => (int)$target->fields['id'],
               'ajax_url'              => $CFG_GLPI['root_doc'] . '/ajax/v2/appointment.php',
               'planning_begin'         => $CFG_GLPI['planning_begin'] ?? '08:00:00',
               'planning_end'           => $CFG_GLPI['planning_end'] ?? '20:00:00',
               'initial_date'           => date('Y-m-d'),
            ];
            echo Html::scriptBlock('$(function() { ITSMAppointmentCalendar.displayTargetManager(' . json_encode($options) . '); });');
        }

        echo "</div>";
    }

    public static function getTargetLabel(array $target)
    {
        $name = self::getTargetName($target);
        if ($name !== NOT_AVAILABLE) {
            return sprintf(__('%1$s - %2$s'), $target['itemtype']::getTypeName(1), $name);
        }
        return NOT_AVAILABLE;
    }

    public static function getTargetName(array $target)
    {
        if ($item = getItemForItemtype($target['itemtype'])) {
            if ($item->getFromDB($target['items_id'])) {
                return $item->getName();
            }
        }
        return NOT_AVAILABLE;
    }

    public static function getTargetIcon(array $target)
    {
        if (isset($target['itemtype']) && is_a($target['itemtype'], CommonGLPI::class, true)) {
            return $target['itemtype']::getIcon();
        }
        return '';
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
