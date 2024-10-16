<?php

namespace itsmng;

/**
 * Time management
 */
class Timezone
{
    public const EMPTY_VALUE = '-----';

    /**
     * GMT
     *
     * @return array
     */
    public static function showGMT(): array
    {

        $elements = [-12, -11, -10, -9, -8, -7, -6, -5, -4, -3.5, -3, -2, -1, 0,
                          '+1', '+2', '+3', '+3.5', '+4', '+4.5', '+5', '+5.5', '+6', '+6.5', '+7',
                          '+8', '+9', '+9.5', '+10', '+11', '+12', '+13'];

        $values = [];
        foreach ($elements as $element) {
            if ($element != 0) {
                $values[$element * HOUR_TIMESTAMP] = sprintf(
                    __('%1$s %2$s'),
                    __('GMT'),
                    sprintf(
                        _n('%s hour', '%s hours', $element),
                        $element
                    )
                );
            } else {
                $values[$element * HOUR_TIMESTAMP] = __('GMT');
            }
        }
        return($values);
    }

    /**
     * Dropdown integers
     *
     * @since 2.0
     *
     * @param array  $options  array of options
     *    - value           : default value
     *    - min             : min value : default 0
     *    - max             : max value : default DAY_TIMESTAMP
     *    - value           : default value
     *    - addfirstminutes : add first minutes before first step (default false)
     *    - toadd           : array of values to add
     *    - inhours         : only show timestamp in hours not in days
     *    - display         : boolean / display or return string
     *    - width           : string / display width of the item
    **/
    public static function GetTimeStamp($options = [])
    {
        global $CFG_GLPI;

        $params['value']               = 0;
        $params['rand']                = mt_rand();
        $params['min']                 = 0;
        $params['max']                 = DAY_TIMESTAMP;
        $params['step']                = $CFG_GLPI["time_step"] * MINUTE_TIMESTAMP;
        $params['emptylabel']          = self::EMPTY_VALUE;
        $params['addfirstminutes']     = false;
        $params['toadd']               = [];
        $params['inhours']             = false;
        $params['display']             = true;
        $params['display_emptychoice'] = true;
        $params['width']               = '80%';

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $params[$key] = $val;
            }
        }

        // Manage min :
        $params['min'] = floor($params['min'] / $params['step']) * $params['step'];

        if ($params['min'] == 0) {
            $params['min'] = $params['step'];
        }

        $params['max'] = max($params['value'], $params['max']);

        // Floor with MINUTE_TIMESTAMP for rounded purpose
        if (empty($params['value'])) {
            $params['value'] = 0;
        }
        if (
            ($params['value'] < max($params['min'], 10 * MINUTE_TIMESTAMP))
            && $params['addfirstminutes']
        ) {
            $params['value'] = floor(($params['value']) / MINUTE_TIMESTAMP) * MINUTE_TIMESTAMP;
        } elseif (!in_array($params['value'], $params['toadd'])) {
            // Round to a valid step except if value is already valid (defined in values to add)
            $params['value'] = floor(($params['value']) / $params['step']) * $params['step'];
        }

        $values = [];

        if ($params['value']) {
            $values[$params['value']] = '';
        }

        if ($params['addfirstminutes']) {
            $max = max($params['min'], 10 * MINUTE_TIMESTAMP);
            for ($i = MINUTE_TIMESTAMP; $i < $max; $i += MINUTE_TIMESTAMP) {
                $values[$i] = '';
            }
        }

        for ($i = $params['min']; $i <= $params['max']; $i += $params['step']) {
            $values[$i] = '';
        }

        if (count($params['toadd'])) {
            foreach ($params['toadd'] as $key) {
                $values[$key] = '';
            }
            ksort($values);
        }

        foreach ($values as $i => $val) {
            if (empty($val)) {
                if ($params['inhours']) {
                    $day  = 0;
                    $hour = floor($i / HOUR_TIMESTAMP);
                } else {
                    $day  = floor($i / DAY_TIMESTAMP);
                    $hour = floor(($i % DAY_TIMESTAMP) / HOUR_TIMESTAMP);
                }
                $minute     = floor(($i % HOUR_TIMESTAMP) / MINUTE_TIMESTAMP);
                if ($minute === '0') {
                    $minute = '00';
                }
                $values[$i] = '';
                if ($day > 0) {
                    if (($hour > 0) || ($minute > 0)) {
                        if ($minute < 10) {
                            $minute = '0' . $minute;
                        }

                        //TRANS: %1$d is the number of days, %2$d the number of hours,
                        //       %3$s the number of minutes : display 1 day 3h15
                        $values[$i] = sprintf(
                            _n('%1$d day %2$dh%3$s', '%1$d days %2$dh%3$s', $day),
                            $day,
                            $hour,
                            $minute
                        );
                    } else {
                        $values[$i] = sprintf(_n('%d day', '%d days', $day), $day);
                    }
                } elseif ($hour > 0 || $minute > 0) {
                    if ($minute < 10) {
                        $minute = '0' . $minute;
                    }

                    //TRANS: %1$d the number of hours, %2$s the number of minutes : display 3h15
                    $values[$i] = sprintf(__('%1$dh%2$s'), $hour, $minute);
                }
            }
        }

        return $values;
    }
}
