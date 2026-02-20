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

namespace tests\units;

use DbTestCase;

/* Test for inc/notification_notificationtemplate.class.php */

class Notification_NotificationTemplate extends DbTestCase
{
    public function testGetTypeName()
    {
        $this->string(\Notification_NotificationTemplate::getTypeName(0))->isIdenticalTo('Templates');
        $this->string(\Notification_NotificationTemplate::getTypeName(1))->isIdenticalTo('Template');
        $this->string(\Notification_NotificationTemplate::getTypeName(2))->isIdenticalTo('Templates');
        $this->string(\Notification_NotificationTemplate::getTypeName(10))->isIdenticalTo('Templates');
    }

    public function testGetTabNameForItem()
    {
        $n_nt = new \Notification_NotificationTemplate();
        $this->boolean($n_nt->getFromDB(1))->isTrue();

        $notif = new \Notification();
        $this->boolean($notif->getFromDB($n_nt->getField('notifications_id')))->isTrue();

        $_SESSION['glpishow_count_on_tabs'] = 1;

        //not logged => no ACLs
        $name = $n_nt->getTabNameForItem($notif);
        $this->string($name)->isIdenticalTo('');

        $this->login();
        $name = $n_nt->getTabNameForItem($notif);
        $this->string($name)->isIdenticalTo('Templates <sup class=\'tab_nb\'>1</sup>');

        $_SESSION['glpishow_count_on_tabs'] = 0;
        $name = $n_nt->getTabNameForItem($notif);
        $this->string($name)->isIdenticalTo('Templates');

        $toadd = $n_nt->fields;
        unset($toadd['id']);
        $toadd['mode'] = \Notification_NotificationTemplate::MODE_XMPP;
        $this->integer((int)$n_nt->add($toadd))->isGreaterThan(0);

        $_SESSION['glpishow_count_on_tabs'] = 1;
        $name = $n_nt->getTabNameForItem($notif);
        $this->string($name)->isIdenticalTo('Templates <sup class=\'tab_nb\'>2</sup>');
    }

    public function testShowForNotification()
    {
        $notif = new \Notification();
        $this->boolean($notif->getFromDB(1))->isTrue();

        //not logged, no ACLs
        $this->output(
            function () use ($notif) {
                \Notification_NotificationTemplate::showForNotification($notif);
            }
        )->isEmpty();

        $this->login();

        $this->output(
            function () use ($notif) {
                \Notification_NotificationTemplate::showForNotification($notif);
            }
        )->contains('Alert Tickets not closed')
           ->contains('Template')
           ->contains('Email');
    }

    public function testGetName()
    {
        $n_nt = new \Notification_NotificationTemplate();
        $this->boolean($n_nt->getFromDB(1))->isTrue();
        $this->integer($n_nt->getName())->isIdenticalTo(1);
    }

    public function testShowForFormNotLogged()
    {
        //not logged, no ACLs
        $this->output(
            function () {
                $n_nt = new \Notification_NotificationTemplate();
                $n_nt->showForm(1);
            }
        )->isEmpty();
    }

    public function testShowForForm()
    {
        \ProfileRight::updateProfileRights(
            4,
            [
                'notification'         => READ | UPDATE | CREATE,
                'notificationtemplate' => READ | UPDATE,
            ]
        );

        $this->login();

        $n_nt = new \Notification_NotificationTemplate();
        $notification = new \Notification();
        $this->integer(\Session::haveRight('notification', UPDATE))->isGreaterThan(0);
        $this->integer(\Session::haveRight('notificationtemplate', UPDATE))->isGreaterThan(0);

        $notifications_id = (int)$notification->add([
            'name'       => __FUNCTION__,
            'itemtype'   => 'Ticket',
            'event'      => 'new',
            'entities_id' => $_SESSION['glpiactive_entity'],
        ]);
        $this->integer($notifications_id)->isGreaterThan(0);
        $this->boolean($notification->can($notifications_id, UPDATE))->isTrue();

        $input = ['notifications_id' => $notifications_id];
        $this->boolean($n_nt->can(-1, CREATE, $input))->isTrue();

        $this->output(
            function () use ($n_nt, $notifications_id) {
                $n_nt->showForm(0, ['notifications_id' => $notifications_id]);
            }
        )->contains('<form')
           ->contains('name="mode"')
           ->contains('show_templates');
    }

    public function testGetMode()
    {
        $mode = \Notification_NotificationTemplate::getMode(\Notification_NotificationTemplate::MODE_MAIL);
        $expected = [
           'label'  => 'Email',
           'from'   => 'core'
        ];
        $this->array($mode)->isIdenticalTo($expected);

        $mode = \Notification_NotificationTemplate::getMode('not_a_mode');
        $this->string($mode)->isIdenticalTo(NOT_AVAILABLE);
    }

    public function testGetModes()
    {
        $modes = \Notification_NotificationTemplate::getModes();
        $this->array($modes)
           ->hasKey(\Notification_NotificationTemplate::MODE_MAIL)
           ->hasKey(\Notification_NotificationTemplate::MODE_AJAX);

        //register new mode
        \Notification_NotificationTemplate::registerMode(
            'test_mode',
            'A test label',
            'anyplugin'
        );
        $modes = \Notification_NotificationTemplate::getModes();
        $this->array($modes)->hasKey('test_mode');
    }

    public function testGetSpecificValueToDisplay()
    {
        $n_nt = new \Notification_NotificationTemplate();
        $display = $n_nt->getSpecificValueToDisplay('id', 1);
        $this->string($display)->isEmpty();

        $display = $n_nt->getSpecificValueToDisplay('mode', \Notification_NotificationTemplate::MODE_AJAX);
        $this->string($display)->isIdenticalTo('Browser');

        $display = $n_nt->getSpecificValueToDisplay('mode', 'not_a_mode');
        $this->string($display)->isIdenticalTo('not_a_mode (N/A)');
    }

    public function testGetSpecificValueToSelect()
    {
        $n_nt = new \Notification_NotificationTemplate();
        $select = $n_nt->getSpecificValueToSelect('id', 1);
        $this->string($select)->isEmpty();

        $select = $n_nt->getSpecificValueToSelect('mode', 'a_name', \Notification_NotificationTemplate::MODE_AJAX);
        //FIXME: why @selected?
        /** $this->string($select)->matches(
           "<select name='a_name' id='dropdown_a_name459469776' size='1'><option value='mailing'>Email</option><option value='ajax' selected>Browser</option><option value='chat'>Chat</option></select><script type=\"text/javascript\">
//<![CDATA[

$(function() {
           $('#dropdown_a_name459469776').select2({

              width: '',
              dropdownAutoWidth: true,
              quietMillis: 100,
              minimumResultsForSearch: 10,
              matcher: function(params, data) {
                 // store last search in the global var
                 query = params;

                 // If there are no search terms, return all of the data
                 if ($.trim(params.term) === '') {
                    return data;
                 }

                 var searched_term = getTextWithoutDiacriticalMarks(params.term);
                 var data_text = typeof(data.text) === 'string'
                    ? getTextWithoutDiacriticalMarks(data.text)
                    : '';
                 var select2_fuzzy_opts = {
                    pre: '<span class=\"select2-rendered__match\">',
                    post: '</span>',
                 };

                 if (data_text.indexOf('>') !== -1 || data_text.indexOf('<') !== -1) {
                    // escape text, if it contains chevrons (can already be escaped prior to this point :/)
                    data_text = jQuery.fn.select2.defaults.defaults.escapeMarkup(data_text);
                 }

                 // Skip if there is no 'children' property
                 if (typeof data.children === 'undefined') {
                    var match  = fuzzy.match(searched_term, data_text, select2_fuzzy_opts);
                    if (match == null) {
                       return false;
                    }
                    data.rendered_text = match.rendered_text;
                    data.score = match.score;
                    return data;
                 }

                 // `data.children` contains the actual options that we are matching against
                 // also check in `data.text` (optgroup title)
                 var filteredChildren = [];

                 $.each(data.children, function (idx, child) {
                    var child_text = typeof(child.text) === 'string'
                       ? getTextWithoutDiacriticalMarks(child.text)
                       : '';

                    if (child_text.indexOf('>') !== -1 || child_text.indexOf('<') !== -1) {
                       // escape text, if it contains chevrons (can already be escaped prior to this point :/)
                       child_text = jQuery.fn.select2.defaults.defaults.escapeMarkup(child_text);
                    }

                    var match_child = fuzzy.match(searched_term, child_text, select2_fuzzy_opts);
                    var match_text  = fuzzy.match(searched_term, data_text, select2_fuzzy_opts);
                    if (match_child !== null || match_text !== null) {
                       if (match_text !== null) {
                          data.score         = match_text.score;
                          data.rendered_text = match_text.rendered;
                       }

                       if (match_child !== null) {
                          child.score         = match_child.score;
                          child.rendered_text = match_child.rendered;
                       }
                       filteredChildren.push(child);
                    }
                 });

                 // If we matched any of the group's children, then set the matched children on the group
                 // and return the group object
                 if (filteredChildren.length) {
                    var modifiedData = $.extend({}, data, true);
                    modifiedData.children = filteredChildren;

                    // You can return modified objects from here
                    // This includes matching the `children` how you want in nested data sets
                    return modifiedData;
                 }

                 // Return `null` if the term should not be displayed
                 return null;
              },
              templateResult: templateResult,
              templateSelection: templateSelection,
           })
           .bind('setValue', function(e, value) {
              $('#dropdown_a_name459469776').val(value).trigger('change');
           })
           $('label[for=dropdown_a_name459469776]').on('click', function(){ $('#dropdown_a_name459469776').select2('open'); });
        });

//]]>
</script>"
        );**/
    }

    public function testGetModeClass()
    {
        $class = \Notification_NotificationTemplate::getModeClass(\Notification_NotificationTemplate::MODE_MAIL);
        $this->string($class)->isIdenticalTo('NotificationMailing');

        $class = \Notification_NotificationTemplate::getModeClass(\Notification_NotificationTemplate::MODE_MAIL, 'event');
        $this->string($class)->isIdenticalTo('NotificationEventMailing');

        $class = \Notification_NotificationTemplate::getModeClass(\Notification_NotificationTemplate::MODE_MAIL, 'setting');
        $this->string($class)->isIdenticalTo('NotificationMailingSetting');

        //register new mode
        \Notification_NotificationTemplate::registerMode(
            'testmode',
            'A test label',
            'anyplugin'
        );

        $class = \Notification_NotificationTemplate::getModeClass('testmode');
        $this->string($class)->isIdenticalTo('PluginAnypluginNotificationTestmode');

        $class = \Notification_NotificationTemplate::getModeClass('testmode', 'event');
        $this->string($class)->isIdenticalTo('PluginAnypluginNotificationEventTestmode');

        $class = \Notification_NotificationTemplate::getModeClass('testmode', 'setting');
        $this->string($class)->isIdenticalTo('PluginAnypluginNotificationTestmodeSetting');
    }

    public function testHasActiveMode()
    {
        global $CFG_GLPI;
        $this->boolean(\Notification_NotificationTemplate::hasActiveMode())->isFalse();
        $CFG_GLPI['notifications_ajax'] = 1;
        $this->boolean(\Notification_NotificationTemplate::hasActiveMode())->isTrue();
        $CFG_GLPI['notifications_ajax'] = 0;
    }
}
