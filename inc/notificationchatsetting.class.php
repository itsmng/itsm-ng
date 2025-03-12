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
 *  This class manages the chat settings
 */
class NotificationChatSetting extends NotificationSetting
{
    public static function getTypeName($nb = 0)
    {
        return __('Chat followups configuration');
    }


    public function getEnableLabel()
    {
        return __('Enable followups via chat');
    }


    public static function getMode()
    {
        return Notification_NotificationTemplate::MODE_CHAT;
    }


    public function showFormConfig($options = [])
    {
        global $CFG_GLPI, $DB;

        if (!isset($options['display'])) {
            $options['display'] = true;
        }

        $formValues = [];
        $groupsRaw = (new Group())->find();
        $formValues['group'] = [];
        foreach ($groupsRaw as $key => $group) {
            $formValues['group'][$group['id']] = $group['completename'];
        }

        $entitiesRaw = (new Entity())->find();
        $formValues['entity'] = [];
        foreach ($entitiesRaw as $key => $entity) {
            $formValues['entity'][$entity['id']] = $entity['completename'];
        }

        $locationsRaw = (new Location())->find();
        $formValues['location'] = [];
        foreach ($locationsRaw as $key => $location) {
            $formValues['location'][$location['id']] = $location['completename'];
        }

        $categoriesRaw = (new ITILCategory())->find();
        $formValues['category'] = [];
        foreach ($categoriesRaw as $key => $category) {
            $formValues['category'][$category['id']] = $category['completename'];
        }

        $chat_modes = [
            CHAT_ROCKET     => __('Rocket chat'),
            CHAT_SLACK      => __('Slack'),
            CHAT_TEAMS      => __('Teams'),
            CHAT_ZULIP      => __('Zulip')
        ];

        $types = [
            'all'      => __("All"),
            'entity'   => __("Entity"),
            'group'    => __("Group"),
            'location' => __("Location"),
            'category' => __("ITIL category")
        ];

        $form = [
            'action' => Toolbox::getItemTypeFormURL(__CLASS__),
            'buttons' => [
                [
                    'name'   => 'update',
                    'value'  => _sx('button', 'Save'),
                    'class'  => 'btn btn-secondary',
                    'type'   => 'submit'
                ]
            ],
            'content' => [
                __('Chat notifications') => [
                    'visible' => true,
                    'inputs'  => [
                        __('Mode') => [
                            'type'  => 'select',
                            'name'  => 'chat_mode',
                            'values' => $chat_modes,
                            'value' => CHAT_SLACK,
                        ],
                        __('URL') => [
                            'type'  => 'text',
                            'name'  => 'hookurl',
                            'value' => '',
                        ],
                        __('Type') => [
                            'type'  => 'select',
                            'name'  => 'type',
                            'values' => $types,
                            'value' => 'all',
                            'hooks' => [
                                'change' => <<<JS
                                    var _val = $(this).find('option:selected').val();
                                    if (_val == 'entity') {
                                        $('[name=value_entity]').attr('disabled', false);
                                        $('[name=value_group]').attr('disabled', true);
                                        $('[name=value_location]').attr('disabled', true);
                                        $('[name=value_category]').attr('disabled', true);
                                    } else if (_val == 'group') {
                                        $('[name=value_entity]').attr('disabled', true);
                                        $('[name=value_group]').attr('disabled', false);
                                        $('[name=value_location]').attr('disabled', true);
                                        $('[name=value_category]').attr('disabled', true);
                                    } else if (_val == 'location') {
                                        $('[name=value_entity]').attr('disabled', true);
                                        $('[name=value_group]').attr('disabled', true);
                                        $('[name=value_location]').attr('disabled', false);
                                        $('[name=value_category]').attr('disabled', true);
                                    } else if (_val == 'category') {
                                        $('[name=value_entity]').attr('disabled', true);
                                        $('[name=value_group]').attr('disabled', true);
                                        $('[name=value_location]').attr('disabled', true);
                                        $('[name=value_category]').attr('disabled', false);
                                    }
                                JS,
                            ]
                        ],
                        Group::getTypeName(1) => [
                            'type'  => 'select',
                            'name'  => 'value_group',
                            'values' => $formValues['group'],
                            'value' => $formValues['group'][array_key_first($formValues['group'])] ?? '',
                            'disabled' => true,
                        ],
                        Entity::getTypeName(1) => [
                            'type'  => 'select',
                            'name'  => 'value_entity',
                            'values' => $formValues['entity'],
                            'value' => $formValues['entity'][array_key_first($formValues['entity'])] ?? '',
                            'disabled' => true,
                        ],
                        Location::getTypeName(1) => [
                            'type'  => 'select',
                            'name'  => 'value_location',
                            'values' => $formValues['location'],
                            'value' => $formValues['location'][array_key_first($formValues['location'])] ?? '',
                            'disabled' => true,
                        ],
                        ITILCategory::getTypeName(1) => [
                            'type'  => 'select',
                            'name'  => 'value_category',
                            'values' => $formValues['category'],
                            'value' => $formValues['category'][array_key_first($formValues['category'])] ?? '',
                            'disabled' => true,
                        ],
                        [
                            'type'  => 'hidden',
                            'name'  => 'id',
                            'value' => '1',
                        ],
                    ]
                ]
            ],
        ];
        renderTwigForm($form);

        $query = "SELECT * FROM glpi_notificationchatconfigs";
        $iterators = $DB->request($query);

        $result = [];
        foreach ($iterators as $key => $iterator) {
            $res = [];
            $res['hookurl'] = $iterator['hookurl'];
            $res['chat'] = $iterator['chat'];
            $res['type'] = $iterator['type'];
            $res['value'] = $iterator['value'];
            $res['id'] = $iterator['id'];

            $result[] = $res;
        }

        $fields = [
            'chat' => __('Mode'),
            'hookurl' => __('URL'),
            'type' => __('Type'),
            'value' => __('Value'),
            'actions' => __('Actions'),
        ];
        $values = [];

        echo "<th colspan='6'>" . "Liste des configs chats" . "</th>";
        $testLabel = __("Test");
        $deleteLabel = __("Delete");
        foreach ($result as $value) {
            $newValue = [
                'chat' => $chat_modes[$value['chat']],
                'hookurl' => $value['hookurl'],
                'type' => $types[$value['type']],
            ];

            switch ($value['type']) {
                case 'entity':
                    $newValue['value'] = $formValues['entity'][$value['value']];
                    break;
                case 'group':
                    $newValue['value'] = $formValues['group'][$value['value']];
                    break;
                case 'location':
                    $newValue['value'] = $formValues['location'][$value['value']];
                    break;
                case 'category':
                    $newValue['value'] = $formValues['categories'][$value['value']];
                    break;
                default:
                    $newValue['value'] = $value['value'];
                    break;
            }
            $newValue['actions'] = <<<HTML
                <div class="btn-group">
                    <a href="notificationchatsetting.form.php?test={$value['id']}" class="btn btn-sm btn-outline-secondary">{$testLabel}</a>
                    <a href="notificationchatsetting.form.php?delete={$value['id']}" class="btn btn-sm btn-outline-secondary">{$deleteLabel}</a>
                </div>
            HTML;
            $values[] = $newValue;
        }
        renderTwigTemplate('table.twig', [
            'fields' => $fields,
            'values' => $values,
        ]);
    }
}
