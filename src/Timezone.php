<?php

namespace itsmng;


/**
 * Time management
 */
class Timezone {
    /**
     * GMT
     *
     * @return array
     */
    static function showGMT() : array {

      $elements = [-12, -11, -10, -9, -8, -7, -6, -5, -4, -3.5, -3, -2, -1, 0,
                        '+1', '+2', '+3', '+3.5', '+4', '+4.5', '+5', '+5.5', '+6', '+6.5', '+7',
                        '+8', '+9', '+9.5', '+10', '+11', '+12', '+13'];

      $values = [];
      foreach ($elements as $element) {
         if ($element != 0) {
            $values[$element*HOUR_TIMESTAMP] = sprintf(__('%1$s %2$s'), __('GMT'),
                                                       sprintf(_n('%s hour', '%s hours', $element),
                                                               $element));
         } else {
            $values[$element*HOUR_TIMESTAMP] = __('GMT');
         }
      }
      return($values);
   }
}