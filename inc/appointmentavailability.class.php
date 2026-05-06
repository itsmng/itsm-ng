<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class AppointmentAvailability extends CommonDBChild
{
    public static $itemtype = 'AppointmentTarget';
    public static $items_id = 'appointmenttargets_id';
    public static $rightname = 'appointment';

    public static function getTypeName($nb = 0)
    {
        return _n('Appointment availability', 'Appointment availabilities', $nb);
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

    public static function prepareInputTimes(array $input)
    {
        foreach (['begin', 'end'] as $field) {
            if (isset($input[$field]) && strlen((string)$input[$field]) === 5) {
                $input[$field] .= ':00';
            }
        }
        return $input;
    }

    public function prepareInputForAdd($input)
    {
        return self::prepareInputTimes($input);
    }

    public function prepareInputForUpdate($input)
    {
        return self::prepareInputTimes($input);
    }

    public static function showForTarget($appointmenttargets_id)
    {
        global $DB;

        $appointmenttargets_id = (int)$appointmenttargets_id;
        $days = Toolbox::getDaysOfWeekArray();

        echo "<div class='appointment-office-hours__header'>";
        echo "<h3>" . self::getTypeName(Session::getPluralNumber()) . "</h3>";
        echo "</div>";
        echo "<div class='appointment-office-hours__list'>";

        $iterator = $DB->request([
           'FROM'  => self::getTable(),
           'WHERE' => ['appointmenttargets_id' => $appointmenttargets_id],
           'ORDER' => ['day', 'begin'],
        ]);
        foreach ($iterator as $row) {
            echo "<div class='appointment-office-hours__row'>";
            echo "<div>";
            echo "<strong>" . $days[$row['day']] . "</strong>";
            echo "<span>" . substr((string)$row['begin'], 0, 5) . " - " . substr((string)$row['end'], 0, 5) . "</span>";
            echo "</div>";
            Html::showSimpleForm(self::getFormURL(), 'purge', __('Delete permanently'), ['id' => $row['id']]);
            echo "</div>";
        }
        echo "</div>";

        echo "<div class='appointment-office-hours__form'>";
        echo "<form method='post' action='" . self::getFormURL() . "'>";
        echo Html::hidden('appointmenttargets_id', ['value' => $appointmenttargets_id]);
        echo "<label><span>" . __('Day') . "</span>";
        Dropdown::showFromArray('day', $days, ['display' => true]);
        echo "</label>";
        echo "<label><span>" . __('Start') . "</span><input type='time' name='begin' value='09:00' required></label>";
        echo "<label><span>" . __('End') . "</span><input type='time' name='end' value='17:00' required></label>";
        echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='btn btn-secondary'>";
        Html::closeForm();
        echo "</div>";
    }

    public static function isAvailable($appointmenttargets_id, $begin, $end)
    {
        global $DB;

        $appointmenttargets_id = (int)$appointmenttargets_id;
        $begin_ts = strtotime((string)$begin);
        $end_ts = strtotime((string)$end);
        if ($begin_ts === false || $end_ts === false || $begin_ts >= $end_ts) {
            return false;
        }

        if (date('Y-m-d', $begin_ts) !== date('Y-m-d', $end_ts)) {
            return false;
        }

        if (AppointmentAvailabilityException::hasBlockingException($appointmenttargets_id, $begin, $end)) {
            return false;
        }

        if (AppointmentAvailabilityException::hasCoveringOpeningException($appointmenttargets_id, $begin, $end)) {
            return true;
        }

        $day = (int)date('w', $begin_ts);
        $begin_time = date('H:i:s', $begin_ts);
        $end_time = date('H:i:s', $end_ts);

        $row = $DB->request([
           'COUNT' => 'cpt',
           'FROM'  => self::getTable(),
           'WHERE' => [
              'appointmenttargets_id' => $appointmenttargets_id,
              'day'                   => $day,
              'begin'                 => ['<=', $begin_time],
              'end'                   => ['>=', $end_time],
           ],
        ])->next();

        return (int)$row['cpt'] > 0;
    }
}
