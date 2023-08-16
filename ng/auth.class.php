<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 *  Identification class used to login
 */
class Auth2 extends CommonGLPI {

       /**
    * Get authentication methods available
    *
    * @return array
    */
   static function getLoginAuthMethods() {
    global $DB;

    $elements = [
       '_default'  => 'local',
       'local'     => __("GLPI internal database")
    ];

    // Get LDAP
    if (Toolbox::canUseLdap()) {
       $iterator = $DB->request([
          'FROM'   => 'glpi_authldaps',
          'WHERE'  => [
             'is_active' => 1
          ],
          'ORDER'  => ['name']
       ]);
       while ($data = $iterator->next()) {
          $elements['ldap-'.$data['id']] = $data['name'];
          if ($data['is_default'] == 1) {
             $elements['_default'] = 'ldap-'.$data['id'];
          }
       }
    }

    // GET Mail servers
    $iterator = $DB->request([
       'FROM'   => 'glpi_authmails',
       'WHERE'  => [
          'is_active' => 1
       ],
       'ORDER'  => ['name']
    ]);
    while ($data = $iterator->next()) {
       $elements['mail-'.$data['id']] = $data['name'];
    }

    return $elements;
 }

 /**
  * Return raw HTML for the authentication source dropdown for login form
  */
 static function dropdownLogin() {
    $elements = self::getLoginAuthMethods();
    $default = $elements['_default'];
    unset($elements['_default']);
    // show dropdown of login src only when multiple src
    if (count($elements) > 1) {
       return '<p class="login_input" id="login_input_src">' .
       Dropdown::showFromArray('auth', $elements, [
          'rand'      => '1',
          'value'     => $default,
          'width'     => '100%',
          'display'   => false
       ])
       . '</p>';
    } else if (count($elements) == 1) {
       // when one src, don't display it, pass it with hidden input
       return Html::hidden('auth', [
          'value' => key($elements)
       ]);
    }
 }
}