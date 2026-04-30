<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class AppointmentAvailabilityException extends CommonDBChild
{
    public static $itemtype = 'AppointmentTarget';
    public static $items_id = 'appointmenttargets_id';
    public static $rightname = 'appointment';

    public static function getTypeName($nb = 0)
    {
        return _n('Appointment availability exception', 'Appointment availability exceptions', $nb);
    }

    public static function canCreate()
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    public static function canView()
    {
        return Session::haveRight(self::$rightname, READ)
            || Session::haveRight(self::$rightname, CREATE)
            || Session::haveRight(self::$rightname, UPDATE);
    }

    public function isEntityAssign()
    {
        return false;
    }

    public function prepareInputForAdd($input)
    {
        if (empty($input['plan'])) {
            return false;
        }

        Toolbox::manageBeginAndEndPlanDates($input['plan']);
        $input['begin'] = $input['plan']['begin'];
        $input['end'] = $input['plan']['end'];
        unset($input['plan']);
        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        if (isset($input['plan'])) {
            Toolbox::manageBeginAndEndPlanDates($input['plan']);
            $input['begin'] = $input['plan']['begin'];
            $input['end'] = $input['plan']['end'];
            unset($input['plan']);
        }
        return $input;
    }

    public static function showForTarget($appointmenttargets_id)
    {
        global $DB;

        $appointmenttargets_id = (int)$appointmenttargets_id;
        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixe' aria-label='Appointment availability exceptions'>";
        echo "<tr><th colspan='6'>" . self::getTypeName(Session::getPluralNumber()) . "</th></tr>";
        echo "<tr><th>" . __('Start date') . "</th><th>" . __('End date') . "</th><th>" . __('Available') . "</th><th>" . __('Comments') . "</th><th></th></tr>";

        $iterator = $DB->request([
           'FROM'  => self::getTable(),
           'WHERE' => ['appointmenttargets_id' => $appointmenttargets_id],
           'ORDER' => 'begin',
        ]);
        foreach ($iterator as $row) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . Html::convDateTime($row['begin']) . "</td>";
            echo "<td>" . Html::convDateTime($row['end']) . "</td>";
            echo "<td>" . Dropdown::getYesNo($row['is_available']) . "</td>";
            echo "<td>" . Html::clean($row['comment']) . "</td>";
            echo "<td class='center'>";
            Html::showSimpleForm(self::getFormURL(), 'purge', __('Delete permanently'), ['id' => $row['id']]);
            echo "</td></tr>";
        }

        $begin = date('Y-m-d H:00:00', strtotime('+1 day'));
        $end = date('Y-m-d H:00:00', strtotime('+1 day +1 hour'));
        echo "<tr class='tab_bg_2'><td colspan='6'>";
        echo "<form method='post' action='" . self::getFormURL() . "'>";
        echo Html::hidden('appointmenttargets_id', ['value' => $appointmenttargets_id]);
        echo "<input type='datetime-local' name='plan[begin]' value='" . Html::clean($begin) . "' required>";
        echo "&nbsp;<input type='datetime-local' name='plan[end]' value='" . Html::clean($end) . "' required>";
        echo "&nbsp;";
        Dropdown::showYesNo('is_available', 0);
        echo "&nbsp;<input type='text' name='comment' placeholder=\"" . __s('Comments') . "\">";
        echo "&nbsp;<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='btn btn-secondary'>";
        Html::closeForm();
        echo "</td></tr>";
        echo "</table></div>";
    }

    public static function hasBlockingException($appointmenttargets_id, $begin, $end)
    {
        return self::hasException($appointmenttargets_id, $begin, $end, 0);
    }

    public static function hasOpeningException($appointmenttargets_id, $begin, $end)
    {
        return self::hasException($appointmenttargets_id, $begin, $end, 1);
    }

    private static function hasException($appointmenttargets_id, $begin, $end, $is_available)
    {
        global $DB;

        $row = $DB->request([
           'COUNT' => 'cpt',
           'FROM'  => self::getTable(),
           'WHERE' => [
              'appointmenttargets_id' => (int)$appointmenttargets_id,
              'is_available'          => (int)$is_available,
              'end'                   => ['>', $begin],
              'begin'                 => ['<', $end],
           ],
        ])->next();

        return (int)$row['cpt'] > 0;
    }
}
