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
 * Notification_NotificationTemplate Class
 *
 * @since 9.2
 **/
class Notification_NotificationTemplate extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1       = 'Notification';
    public static $items_id_1       = 'notifications_id';
    public static $itemtype_2       = 'NotificationTemplate';
    public static $items_id_2       = 'notificationtemplates_id';
    public static $mustBeAttached_2 = false; // Mandatory to display creation form

    public $no_form_page    = false;
    protected $displaylist  = false;

    public const MODE_MAIL      = 'mailing';
    public const MODE_AJAX      = 'ajax';
    public const MODE_WEBSOCKET = 'websocket';
    public const MODE_SMS       = 'sms';
    public const MODE_XMPP      = 'xmpp';
    public const MODE_IRC       = 'irc';
    public const MODE_CHAT      = 'chat';

    public static function getTypeName($nb = 0)
    {
        return _n('Template', 'Templates', $nb);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate && Notification::canView()) {
            $nb = 0;
            switch ($item->getType()) {
                case Notification::class:
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable(
                            $this->getTable(),
                            ['notifications_id' => $item->getID()]
                        );
                    }
                    return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
                    break;
                case NotificationTemplate::class:
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable(
                            $this->getTable(),
                            ['notificationtemplates_id' => $item->getID()]
                        );
                    }
                    return self::createTabEntry(Notification::getTypeName(Session::getPluralNumber()), $nb);
                    break;
            }
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case Notification::class:
                self::showForNotification($item, $withtemplate);
                break;
            case NotificationTemplate::class:
                self::showForNotificationTemplate($item, $withtemplate);
                break;
        }

        return true;
    }


    /**
     * Print the notification templates
     *
     * @param Notification $notif        Notification object
     * @param boolean      $withtemplate Template or basic item (default '')
     *
     * @return void
     **/
    public static function showForNotification(Notification $notif, $withtemplate = 0)
    {
        global $DB;

        $ID = $notif->getID();

        if (
            !$notif->getFromDB($ID)
            || !$notif->can($ID, READ)
        ) {
            return false;
        }
        $canedit = $notif->canEdit($ID);

        if (
            $canedit
            && !(!empty($withtemplate) && ($withtemplate == 2))
        ) {
            $button = [
                'url'   => self::getFormURL(),
                'title' => __('Add a template'),
            ];
            echo <<<HTML
            <div class='center mb-3'>
                <a class='btn btn-secondary' href='{$button['url']}?notifications_id=$ID&amp;withtemplate=$withtemplate'>
                    {$button['title']}
                </a>
            </div>
        HTML;
        }

        $fields = [
          'id' => __('ID'),
          'name' => static::getTypeName(1),
          'mode' => __('Mode'),
        ];
        $values = [];

        $iterator = $DB->request([
           'FROM'   => self::gettable(),
           'WHERE'  => ['notifications_id' => $ID]
        ]);
        $notiftpl = new self();
        while ($data = $iterator->next()) {
            $notiftpl->getFromDB($data['id']);
            $tpl = new NotificationTemplate();
            $tpl->getFromDB($data['notificationtemplates_id']);

            $tpl_link = $tpl->getLink();
            if (empty($tpl_link)) {
                $tpl_link = "<i class='fa fa-exclamation-triangle red' aria-hidden='true'></i>&nbsp;
                            <a href='" . $notiftpl->getLinkUrl() . "'>" .
                   __("No template selected") .
                   "</a>";
            }
            $mode = self::getMode($data['mode']);
            $mode = $mode === NOT_AVAILABLE ?
                "{$data['mode']} ($mode)" :
                $mode['label'];
            $values[] = [
                'id' => $notiftpl->getID(),
                'name' => $tpl_link,
                'mode' => $mode,
            ];
        }
        renderTwigTemplate('table.twig', [
           'fields' => $fields,
           'values' => $values,
        ]);
    }


    /**
     * Print associated notifications
     *
     * @param NotificationTemplate $template     Notification template object
     * @param boolean              $withtemplate Template or basic item (default '')
     *
     * @return void
     */
    public static function showForNotificationTemplate(NotificationTemplate $template, $withtemplate = 0)
    {
        global $DB;

        $ID = $template->getID();

        if (
            !$template->getFromDB($ID)
            || !$template->can($ID, READ)
        ) {
            return false;
        }

        echo "<div class='center'>";

        $iterator = $DB->request([
           'FROM'   => self::getTable(),
           'WHERE'  => ['notificationtemplates_id' => $ID]
        ]);

        echo "<table class='tab_cadre_fixehov' aria-label='Notification'>";
        $colspan = 2;

        if ($iterator->numrows()) {
            $header = "<tr>";
            $header .= "<th>" . __('ID') . "</th>";
            $header .= "<th>" . _n('Notification', 'Notifications', 1) . "</th>";
            $header .= "<th>" . __('Mode') . "</th>";
            $header .= "</tr>";
            echo $header;

            Session::initNavigateListItems(
                __CLASS__,
                //TRANS : %1$s is the itemtype name,
                //        %2$s is the name of the item (used for headings of a list)
                sprintf(
                    __('%1$s = %2$s'),
                    Notification::getTypeName(1),
                    $template->getName()
                )
            );

            while ($data = $iterator->next()) {
                $notification = new Notification();
                $notification->getFromDB($data['notifications_id']);

                echo "<tr class='tab_bg_2'>";
                echo "<td>" . $data['id'] . "</td>";
                echo "<td>" . $notification->getLink() . "</td>";
                $mode = self::getMode($data['mode']);
                if ($mode === NOT_AVAILABLE) {
                    $mode = "{$data['mode']} ($mode)";
                } else {
                    $mode = $mode['label'];
                }
                echo "<td>$mode</td>";
                echo "</tr>";
                Session::addToNavigateListItems(__CLASS__, $data['id']);
            }
            echo $header;
        } else {
            echo "<tr class='tab_bg_2'><th colspan='$colspan'>" . __('No item found') . "</th></tr>";
        }

        echo "</table>";
        echo "</div>";
    }


    /**
     * Form for Notification on Massive action
     **/
    public static function showFormMassiveAction()
    {

        echo __('Mode') . "<br>";
        self::dropdownMode(['name' => 'mode']);
        echo "<br><br>";

        echo NotificationTemplate::getTypeName(1) . "<br>";
        echo NotificationTemplate::dropdown([
           'name'       => 'notificationtemplates_id',
           'value'     => 0,
           'comment'   => 1,
        ]);
        echo "<br><br>";

        echo Html::submit(_x('button', 'Add'), ['name' => 'massiveaction']);
    }


    public function getName($options = [])
    {
        return $this->getID();
    }


    /**
     * Print the form
     *
     * @param integer $ID      ID of the item
     * @param array   $options array
     *     - target for the Form
     *     - computers_id ID of the computer for add process
     *
     * @return true if displayed  false if item not found or not right to display
     **/
    public function showForm($ID, $options = [])
    {
        if (!Session::haveRight("notification", UPDATE)) {
            return false;
        }

        $notif = new Notification();
        if ($ID > 0) {
            $this->check($ID, READ);
            $notif->getFromDB($this->fields['notifications_id']);
        } else {
            $this->check(-1, CREATE, $options);
            $notif->getFromDB($options['notifications_id']);
        }

        $this->showFormHeader($options);

        if ($this->isNewID($ID)) {
            echo "<input type='hidden' name='notifications_id' value='" . $options['notifications_id'] . "'>";
        }

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . _n('Notification', 'Notifications', 1) . "</td>";
        echo "<td>" . $notif->getLink() . "</td>";
        echo "<td colspan='2'>&nbsp;</td>";
        echo "</tr>\n";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Mode') . "</td>";
        echo "<td>";
        self::dropdownMode(['name' => 'mode', 'value' => $this->getField('mode')]);
        echo "</td>";

        echo "<td>" . NotificationTemplate::getTypeName(1) . "</td>";
        echo "<td><span id='show_templates'>";
        NotificationTemplate::dropdownTemplates(
            'notificationtemplates_id',
            $notif->fields['itemtype'],
            $this->fields['notificationtemplates_id']
        );
        echo "</span></td></tr>";

        $this->showFormButtons($options);

        return true;
    }


    /**
     * Get notification method label
     *
     * @param string $mode the mode to use
     *
     * @return array
     **/
    public static function getMode($mode)
    {
        $tab = self::getModes();
        if (isset($tab[$mode])) {
            return $tab[$mode];
        }
        return NOT_AVAILABLE;
    }

    /**
     * Register a new notification mode (for plugins)
     *
     * @param string $mode  Mode
     * @param string $label Mode's label
     * @param strign $from  Plugin which registers the mode
     *
     * @return void
     */
    public static function registerMode($mode, $label, $from)
    {
        global $CFG_GLPI;

        self::getModes();
        $CFG_GLPI['notifications_modes'][$mode] = [
           'label'  => $label,
           'from'   => $from
        ];
    }

    /**
     * Get notification method label
     *
     * @since 0.84
     *
     * @return the mode's label
     **/
    public static function getModes()
    {
        global $CFG_GLPI;

        $core_modes = [
           self::MODE_MAIL      => [
              'label'  => _n('Email', 'Emails', 1),
              'from'   => 'core'
           ],
           self::MODE_AJAX      => [
              'label'  => __('Browser'),
              'from'   => 'core'
           ],
           self::MODE_CHAT      => [
              'label'  =>  'Chat',
              'from'   =>  'core'
           ]
           /*self::MODE_WEBSOCKET => [
              'label'  => __('Websocket'),
              'from'   => 'core'
           ],
           self::MODE_SMS       => [
              'label'  => __('SMS'),
              'from'   => 'core'
           ]*/
        ];

        if (!isset($CFG_GLPI['notifications_modes']) || !is_array($CFG_GLPI['notifications_modes'])) {
            $CFG_GLPI['notifications_modes'] = $core_modes;
        } else {
            //check that core modes are part of the config
            foreach ($core_modes as $mode => $conf) {
                if (!isset($CFG_GLPI['notifications_modes'][$mode])) {
                    $CFG_GLPI['notifications_modes'][$mode] = $conf;
                }
            }
        }

        return $CFG_GLPI['notifications_modes'];
    }


    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'mode':
                $mode = self::getMode($values[$field]);
                if ($mode === NOT_AVAILABLE) {
                    $mode = "{$values[$field]} ($mode)";
                } else {
                    $mode = $mode['label'];
                }
                return $mode;
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }


    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;

        switch ($field) {
            case 'mode':
                $options['value']    = $values[$field];
                $options['name']     = $name;
                return self::dropdownMode($options);
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }


    /**
     * Display a dropdown with all the available notification modes
     *
     * @param array $options array of options
     *
     * @return void
     */
    public static function dropdownMode($options)
    {
        $p['name']     = 'modes';
        $p['display']  = true;
        $p['value']    = '';

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $p[$key] = $val;
            }
        }

        $modes = self::getModes();
        foreach ($modes as &$mode) {
            $mode = $mode['label'];
        }

        return Dropdown::showFromArray($p['name'], $modes, $p);
    }

    /**
     * Get class name for specified mode
     *
     * @param string $mode      Requested mode
     * @param string $extratype Extra type (either 'event' or 'setting')
     *
     * @return string
     */
    public static function getModeClass($mode, $extratype = '')
    {
        if ($extratype == 'event') {
            $classname = 'NotificationEvent' . ucfirst($mode);
        } elseif ($extratype == 'setting') {
            $classname = 'Notification' . ucfirst($mode) . 'Setting';
        } else {
            if ($extratype != '') {
                trigger_error("Unknown type $extratype", E_USER_ERROR);
            }
            $classname = 'Notification' . ucfirst($mode);
        }
        $conf = self::getMode($mode);
        if ($conf['from'] != 'core') {
            $classname = 'Plugin' . ucfirst($conf['from']) . $classname;
        }
        return $classname;
    }

    /**
     * Check if at least one mode is currently enabled
     *
     * @return boolean
     */
    public static function hasActiveMode()
    {
        global $CFG_GLPI;
        foreach (array_keys(self::getModes()) as $mode) {
            if ($CFG_GLPI['notifications_' . $mode]) {
                return true;
            }
        }
        return false;
    }
}
