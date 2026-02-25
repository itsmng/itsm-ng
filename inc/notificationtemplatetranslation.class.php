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
 * NotificationTemplateTranslation Class
 **/
class NotificationTemplateTranslation extends CommonDBChild
{
    // From CommonDBChild
    public static $itemtype = 'NotificationTemplate';
    public static $items_id = 'notificationtemplates_id';

    public $dohistory = true;


    public static function getTypeName($nb = 0)
    {
        return _n('Template translation', 'Template translations', $nb);
    }


    /**
     * @since 0.84
     **/
    public function getForbiddenStandardMassiveAction()
    {

        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    protected function computeFriendlyName()
    {
        global $CFG_GLPI;

        if ($this->getField('language') != '') {
            return $CFG_GLPI['languages'][$this->getField('language')][0];
        } else {
            return self::getTypeName(1);
        }

        return '';
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    public function showForm($ID, $options)
    {
        global $CFG_GLPI;

        if (!Config::canUpdate()) {
            return false;
        }
        $notificationtemplates_id = -1;
        if (isset($options['notificationtemplates_id'])) {
            $notificationtemplates_id = $options['notificationtemplates_id'];
        }

        if ($this->getFromDB($ID)) {
            $notificationtemplates_id = $this->getField('notificationtemplates_id');
        }

        $this->initForm($ID, $options);
        $template = new NotificationTemplate();
        $template->getFromDB($notificationtemplates_id);

        //Html::initEditorSystem('content_html');

        $rand = mt_rand();
        Ajax::createIframeModalWindow(
            "tags" . $rand,
            $CFG_GLPI['root_doc'] . "/front/notification.tags.php?sub_type=" .
            addslashes((string) $template->getField('itemtype'))
        );

        $form = [
            'action' => $this->getFormURL(),
            'buttons' => [
                [
                    'type' => 'submit',
                    'name' => $this->isNewID($ID) ? 'add' : 'update',
                    'value' => $this->isNewID($ID) ? __('Add') : __('Update'),
                    'class' => 'btn btn-secondary'
                ]
            ],
            'content' => [
                $this->getTypeName() => [
                    'visible' => true,
                    'inputs' => [
                        $this->isNewID($ID) ? [] : [
                            'type' => 'hidden',
                            'name' => 'id',
                            'value' => $ID
                        ],
                        [
                            'type' => 'hidden',
                            'name' => 'notificationtemplates_id',
                            'value' => $notificationtemplates_id
                        ],
                        NotificationTemplate::getTypeName() => [
                            'content' => "<a href='" . Toolbox::getItemTypeFormURL('NotificationTemplate') .
                                "?id=" . $notificationtemplates_id . "'>" . $template->getField('name') . "</a>" .
                                "<a class='btn btn-sm btn-outline-info' href='#' onClick=\"" .
                                Html::jsGetElementbyID("tags" . $rand) . ".dialog('open'); return false;\">" .
                                __('Show list of available tags') . "</a>",
                            'col_lg' => 12,
                            'col_md' => 12,
                        ],
                        __('Language') => [
                            'type' => 'select',
                            'name' => 'language',
                            'value' => $this->fields['language'],
                            'values' => ['' => __('Default translation')] + Language::getLanguages(),
                            'col_lg' => 12,
                            'col_md' => 12,
                        ],
                        __('Subject') => [
                            'type' => 'text',
                            'name' => 'subject',
                            'value' => $this->fields['subject'],
                            'size' => 100,
                            'col_lg' => 12,
                            'col_md' => 12,
                        ],
                        __('Email text body') => [
                            'type' => 'textarea',
                            'name' => 'content_text',
                            'value' => $this->fields['content_text'],
                            'col_lg' => 12,
                            'col_md' => 12,
                        ],
                        __('Email HTML body') => [
                            'type' => 'codeeditor',
                            'name' => 'content_html',
                            'value' => $this->fields['content_html'],
                            'col_lg' => 12,
                            'col_md' => 12,
                        ],
                    ]
                ]
            ]
        ];
        renderTwigForm($form);

        return true;
    }


    /**
     * @param $template        NotificationTemplate object
     * @param $options   array
     **/
    public function showSummary(NotificationTemplate $template, $options = [])
    {
        global $DB, $CFG_GLPI;

        $nID = $template->getField('id');
        $canedit = Config::canUpdate();

        $massiveActionId = 'TableFor' . self::class;
        if ($canedit) {
            $url = Toolbox::getItemTypeFormURL('NotificationTemplateTranslation') .
                "?notificationtemplates_id=" . $nID;
            $title = __('Add a new translation');
            echo <<<HTML
            <div class='center'>
                <a class='btn btn-secondary' href='$url'>$title</a>
            </div>
        HTML;
            $massiveactionparams = [
                'container' => $massiveActionId,
                'display_arrow' => false,
                'is_deleted' => false,
            ];

            Html::showMassiveActions($massiveactionparams);
        }

        $fields = ['language' => __('Language')];
        $values = [];
        $massiveActionValues = [];

        foreach (
            $DB->request(
                'glpi_notificationtemplatetranslations',
                ['notificationtemplates_id' => $nID]
            ) as $data
        ) {
            $link = '';
            if ($this->getFromDB($data['id'])) {
                Session::addToNavigateListItems('NotificationTemplateTranslation', $data['id']);
                $link .= "<a href='" . Toolbox::getItemTypeFormURL('NotificationTemplateTranslation') .
                    "?id=" . $data['id'] . "&amp;notificationtemplates_id=" . $nID . "'>";

                if ($data['language'] != '') {
                    $link .= $CFG_GLPI['languages'][$data['language']][0];
                } else {
                    $link .= __('Default translation');
                }

                $link .= "</a>";
            }
            $values[] = ['language' => $link];
            if ($canedit) {
                $massiveActionValues[] = sprintf('item[%s][%s]', $this::class, $data['id']);
            }
        }
        renderTwigTemplate('table.twig', [
            'id' => $massiveActionId,
            'fields' => $fields,
            'values' => $values,
            'massive_action' => $massiveActionValues,
        ]);
    }


    /**
     * @param $input  array
     */
    public static function cleanContentHtml(array $input)
    {

        $txt = Html::clean(Toolbox::unclean_cross_side_scripting_deep($input['content_html']));
        $txt = trim(html_entity_decode($txt, 0, 'UTF-8'));

        if (!$txt) {
            // No HTML (nothing to display)
            $input['content_html'] = '';
        } elseif (!$input['content_text']) {
            // Use cleaned HTML
            $input['content_text'] = $txt;
        }
        return $input;
    }


    public function prepareInputForAdd($input)
    {
        return parent::prepareInputForAdd(self::cleanContentHtml($input));
    }


    public function prepareInputForUpdate($input)
    {
        return parent::prepareInputForUpdate(self::cleanContentHtml($input));
    }


    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __('Characteristics')
        ];

        $tab[] = [
            'id' => '1',
            'table' => $this->getTable(),
            'field' => 'language',
            'name' => __('Language'),
            'datatype' => 'language',
            'massiveaction' => false
        ];

        $tab[] = [
            'id' => '2',
            'table' => $this->getTable(),
            'field' => 'subject',
            'name' => __('Subject'),
            'massiveaction' => false,
            'datatype' => 'string',
            'autocomplete' => true,
        ];

        $tab[] = [
            'id' => '3',
            'table' => $this->getTable(),
            'field' => 'content_html',
            'name' => __('Email HTML body'),
            'datatype' => 'text',
            'htmltext' => 'true',
            'massiveaction' => false
        ];

        $tab[] = [
            'id' => '4',
            'table' => $this->getTable(),
            'field' => 'content_text',
            'name' => __('Email text body'),
            'datatype' => 'text',
            'massiveaction' => false
        ];

        return $tab;
    }


    /**
     * @param $language_id
     **/
    public static function getAllUsedLanguages($language_id)
    {

        $used_languages = getAllDataFromTable(
            'glpi_notificationtemplatetranslations',
            [
                'notificationtemplates_id' => $language_id
            ]
        );
        $used = [];

        foreach ($used_languages as $used_language) {
            $used[$used_language['language']] = $used_language['language'];
        }

        return $used;
    }


    /**
     * @param $itemtype
     **/
    public static function showAvailableTags($itemtype)
    {
        $target = NotificationTarget::getInstanceByType(stripslashes((string) $itemtype));
        $target->getTags();

        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixe' aria-label='Available Tags'>";
        echo "<tr><th>" . __('Tag') . "</th>
                <th>" . __('Label') . "</th>
                <th>" . _n('Event', 'Events', 1) . "</th>
                <th>" . _n('Type', 'Types', 1) . "</th>
                <th>" . __('Possible values') . "</th>
            </tr>";

        $tags = [];

        foreach ($target->tag_descriptions as $tag_type => $infos) {
            foreach ($infos as $key => $val) {
                $infos[$key]['type'] = $tag_type;
            }
            $tags = array_merge($tags, $infos);
        }
        ksort($tags);
        foreach ($tags as $tag => $values) {
            if ($values['events'] == NotificationTarget::TAG_FOR_ALL_EVENTS) {
                $event = __('All');
            } else {
                $event = implode(', ', $values['events']);
            }

            $action = '';

            if ($values['foreach']) {
                $action = __('List of values');
            } else {
                $action = __('Single value');
            }

            if (!empty($values['allowed_values'])) {
                $allowed_values = implode(',', $values['allowed_values']);
            } else {
                $allowed_values = '';
            }

            echo "<tr class='tab_bg_1'><td>" . $tag . "</td>" .
                "<td>";
            if ($values['type'] == NotificationTarget::TAG_LANGUAGE) {
                printf(__('%1$s: %2$s'), __('Label'), $values['label']);
            } else {
                echo $values['label'];
            }
            echo "</td><td>" . $event . "</td>" .
                "<td>" . $action . "</td>" .
                "<td>" . $allowed_values . "</td>" .
                "</tr>";
        }
        echo "</table></div>";
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            $nb = 0;
            switch ($item->getType()) {
                case 'NotificationTemplate':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable(
                            $this->getTable(),
                            ['notificationtemplates_id' => $item->getID()]
                        );
                    }
                    return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == 'NotificationTemplate') {
            $temp = new self();
            $temp->showSummary($item);
        }
        return true;
    }


    /**
     * Display debug information for current object
     * NotificationTemplateTranslation => translation preview
     *
     * @since 0.84
     **/
    public function showDebug()
    {

        $template = new NotificationTemplate();
        if (!$template->getFromDB($this->fields['notificationtemplates_id'])) {
            return;
        }

        $itemtype = $template->getField('itemtype');
        if (!($item = getItemForItemtype($itemtype))) {
            return;
        }

        echo "<div class='spaced'>";
        echo "<table class='tab_cadre_fixe' aria-label='Debug'>";
        echo "<tr><th colspan='2'>" . __('Preview') . "</th></tr>";

        $oktypes = [
            'CartridgeItem',
            'Change',
            'ConsumableItem',
            'Contract',
            'CronTask',
            'Problem',
            'Project',
            'Ticket',
            'User'
        ];

        if (!in_array($itemtype, $oktypes)) {
            // this itemtype doesn't work, need to be fixed
            echo "<tr class='tab_bg_2 center'><td>" . NOT_AVAILABLE . "</td>";
            echo "</table></div>";
            return;
        }

        // Criteria Form
        $key = getForeignKeyFieldForItemType($item->getType());
        $id = Session::getSavedOption(__CLASS__, $key, 0);
        $event = Session::getSavedOption(__CLASS__, $key . '_event', '');

        echo "<tr class='tab_bg_2'><td>" . $item->getTypeName(1) . "&nbsp;";
        $item->dropdown([
            'value' => $id,
            'on_change' => 'reloadTab("' . $key . '="+this.value)'
        ]);
        echo "</td><td>" . NotificationEvent::getTypeName(1) . "&nbsp;";
        NotificationEvent::dropdownEvents(
            $item->getType(),
            [
                'value' => $event,
                'on_change' => 'reloadTab("' . $key . '_event="+this.value)'
            ]
        );
        echo "</td>";

        // Preview
        if (
            $event
            && $item->getFromDB($id)
        ) {
            $options = ['_debug' => true];

            // TODO Awfull Hack waiting for https://forge.indepnet.net/issues/3439
            $multi = [
                'alert',
                'alertnotclosed',
                'end',
                'notice',
                'periodicity',
                'periodicitynotice'
            ];
            if (in_array($event, $multi)) {
                // Won't work for Cardridge and Consumable
                $options['entities_id'] = $item->getEntityID();
                $options['items'] = [$item->getID() => $item->fields];
            }
            $target = NotificationTarget::getInstance($item, $event, $options);
            $infos = [
                'language' => $_SESSION['glpilanguage'],
                'additionnaloption' => ['usertype' => NotificationTarget::GLPI_USER]
            ];

            $template->resetComputedTemplates();
            $template->setSignature(Notification::getMailingSignature($_SESSION['glpiactive_entity']));
            if ($tid = $template->getTemplateByLanguage($target, $infos, $event, $options)) {
                $data = $template->templates_by_languages[$tid];

                echo "<tr><th colspan='2'>" . __('Subject') . "</th></tr>";
                echo "<tr class='tab_bg_2 b'><td colspan='2'>" . $data['subject'] . "</td></tr>";

                echo "<tr><th>" . __('Email text body') . "</th>";
                echo "<th>" . __('Email HTML body') . "</th></tr>";
                echo "<tr class='tab_bg_2'><td>" . nl2br((string) $data['content_text']) . "</td>";
                echo "<td>" . $data['content_html'] . "</td></tr>";
            }
        }
        echo "</table></div>";
    }
}
