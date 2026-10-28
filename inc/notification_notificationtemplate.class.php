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
            (new self())->showForm(-1, ['notifications_id' => $ID, 'inline' => true]);
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
                'id' => $notiftpl->getLink(),
                'name' => $tpl_link,
                'mode' => $mode,
            ];
        }
        renderTwigTemplate('table.twig', [
           'id' => 'notificationTemplates_' . $ID,
           'state_key' => 'notificationTemplates_' . $ID,
           'pageSize' => max(1, (int) ($_SESSION['glpilist_limit'] ?? 15)),
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

        $values = [];
        Session::initNavigateListItems(__CLASS__, sprintf(
            __('%1$s = %2$s'), Notification::getTypeName(1), $template->getName()
        ));
        foreach ($DB->request([
            'FROM' => self::getTable(), 'WHERE' => ['notificationtemplates_id' => $ID],
        ]) as $data) {
            $notification = new Notification();
            $notification->getFromDB($data['notifications_id']);
            $mode = self::getMode($data['mode']);
            $values[$data['id']] = [
                'id' => $data['id'], 'notification' => $notification->getLink(),
                'mode' => $mode === NOT_AVAILABLE
                    ? htmlspecialchars($data['mode'], ENT_QUOTES, 'UTF-8', false) . ' (' . NOT_AVAILABLE . ')'
                    : $mode['label'],
            ];
            Session::addToNavigateListItems(__CLASS__, $data['id']);
        }
        renderTwigTemplate('table.twig', [
            'id' => 'templateNotifications_' . $ID, 'state_key' => 'templateNotifications_' . $ID,
            'fields' => ['id' => __('ID'), 'notification' => _n('Notification', 'Notifications', 1), 'mode' => __('Mode')],
            'values' => $values, 'pageSize' => max(1, (int) ($_SESSION['glpilist_limit'] ?? 15)),
        ]);
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

        ob_start();
        self::dropdownMode(['name' => 'mode', 'value' => $this->getField('mode')]);
        $mode = ob_get_clean();
        ob_start();
        NotificationTemplate::dropdownTemplates(
            'notificationtemplates_id', $notif->fields['itemtype'], $this->fields['notificationtemplates_id']
        );
        $templates = ob_get_clean();
        $buttons = [[
            'type' => 'submit', 'name' => $ID > 0 ? 'update' : 'add',
            'value' => $ID > 0 ? _x('button', 'Update') : _x('button', 'Add'),
            'class' => 'btn btn-primary', 'icon' => $ID > 0 ? 'fas fa-save' : 'fas fa-plus',
        ]];
        if ($ID > 0 && $this->can($ID, PURGE)) {
            $buttons[] = ['type' => 'submit', 'name' => 'purge',
                'value' => _x('button', 'Delete permanently'), 'class' => 'btn btn-danger'];
        }
        renderTwigForm([
            'action' => static::getFormURL(), 'buttons' => $buttons,
            'content' => [($ID > 0 ? static::getTypeName(1) : __('Add a template')) => [
                'visible' => true, 'inputs' => [
                    ['type' => 'hidden', 'name' => '_inline', 'value' => !empty($options['inline']) ? 1 : 0],
                    ['type' => 'hidden', 'name' => 'id', 'value' => $ID],
                    ['type' => 'hidden', 'name' => 'notifications_id', 'value' => $notif->getID()],
                    _n('Notification', 'Notifications', 1) => ['content' => $notif->getLink()],
                    __('Mode') => ['content' => $mode],
                    NotificationTemplate::getTypeName(1) => ['content' => "<span id='show_templates'>$templates</span>"],
                ],
            ]],
        ]);

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
            $classname = 'Plugin' . ucfirst((string) $conf['from']) . $classname;
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
