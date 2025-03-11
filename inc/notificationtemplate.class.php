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
 * NotificationTemplate Class
**/
class NotificationTemplate extends CommonDBTM
{
    use Glpi\Features\Clonable;

    // From CommonDBTM
    public $dohistory = true;

    //Signature to add to the template
    public $signature = '';

    //Store templates for each language
    public $templates_by_languages = [];

    public static $rightname = 'config';

    public function getCloneRelations(): array
    {
        return [
           NotificationTemplateTranslation::class
        ];
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Notification template', 'Notification templates', $nb);
    }


    public static function canCreate()
    {
        return static::canUpdate();
    }


    /**
     * @since 0.85
    **/
    public static function canPurge()
    {
        return static::canUpdate();
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('NotificationTemplateTranslation', $ong, $options);
        $this->addStandardTab('Notification_NotificationTemplate', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    /**
     * Reset already computed templates
    **/
    public function resetComputedTemplates()
    {
        $this->templates_by_languages = [];
    }


    public function showForm($ID, $options = [])
    {
        global $CFG_GLPI;

        if (!Config::canUpdate()) {
            return false;
        }

        $types = $CFG_GLPI['notificationtemplates_types'];
        $typeValues = [];
        foreach ($types as $type) {
            if ($item = getItemForItemtype($type)) {
                $typeValues[$type] = $item->getTypeName(1);
            }
        }

        $form = [
          'action' => $this->getFormURL(),
           'buttons' => [
              ($this->canUpdateItem() ? [
                 'type' => 'submit',
                 'name' => $this->isNewID($ID) ? 'add' : 'update',
                 'value' => $this->isNewID($ID) ? __('Add') : __('Update'),
                 'class' => 'btn btn-secondary'
              ] : []),
              (!$this->isNewID($ID) && self::canPurge() ? [
                 'type' => 'submit',
                 'name' => 'purge',
                 'value' => __('Delete permanently'),
                 'class' => 'btn btn-danger'
              ] : []),
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
                      __('Name') => [
                          'type' => 'text',
                          'name' => 'name',
                          'value' => $this->fields['name'],
                          'comment' => 1,
                          'col_lg' => 12,
                          'col_md' => 12,
                      ],
                      __('Type', 'Types', 1) => [
                          'type' => 'select',
                          'name' => 'itemtype',
                          'value' => ($this->fields['itemtype'] ? $this->fields['itemtype'] : 'Ticket'),
                          'values' => $typeValues,
                          'comment' => 1,
                          'col_lg' => 12,
                          'col_md' => 12,
                      ],
                      __('Comments') => [
                          'type' => 'textarea',
                          'name' => 'comment',
                          'value' => $this->fields['comment'],
                          'comment' => 1,
                          'col_lg' => 12,
                          'col_md' => 12,
                      ],
                      __('CSS') => [
                          'type' => 'textarea',
                          'name' => 'css',
                          'value' => $this->fields['css'],
                          'comment' => 1,
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


    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
           'id'                 => 'common',
           'name'               => __('Characteristics')
        ];

        $tab[] = [
           'id'                 => '1',
           'table'              => $this->getTable(),
           'field'              => 'name',
           'name'               => __('Name'),
           'datatype'           => 'itemlink',
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '4',
           'table'              => $this->getTable(),
           'field'              => 'itemtype',
           'name'               => _n('Type', 'Types', 1),
           'datatype'           => 'itemtypename',
           'itemtype_list'      => 'notificationtemplates_types',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '16',
           'table'              => $this->getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text'
        ];

        return $tab;
    }


    /**
     * Display templates available for an itemtype
     *
     * @param $name      the dropdown name
     * @param $itemtype  display templates for this itemtype only
     * @param $value     the dropdown's default value (0 by default)
    **/
    public static function dropdownTemplates($name, $itemtype, $value = 0)
    {
        echo self::dropdown([
           'name'       => $name,
           'value'     => $value,
           'comment'   => 1,
           'condition' => ['itemtype' => addslashes($itemtype)]
        ]);
    }


    /**
     * @param $options
    **/
    public function getAdditionnalProcessOption($options)
    {

        //Additionnal option can be given for template processing
        //For the moment, only option to see private tasks & followups is available
        if (
            !empty($options)
            && isset($options['sendprivate'])
        ) {
            return 1;
        }
        return 0;
    }


    /**
     * @param $target             NotificationTarget object
     * @param $user_infos   array
     * @param $event
     * @param $options      array
     *
     * @return id of the template in templates_by_languages / false if computation failed
    **/
    public function getTemplateByLanguage(
        NotificationTarget $target,
        $user_infos = [],
        $event = '',
        $options = []
    ) {

        $lang     = [];
        $language = $user_infos['language'];

        if (isset($user_infos['additionnaloption'])) {
            $additionnaloption =  $user_infos['additionnaloption'];
        } else {
            $additionnaloption =  [];
        }

        $tid  = $language;
        $tid .= serialize($additionnaloption);

        $tid  = sha1($tid);

        if (!isset($this->templates_by_languages[$tid])) {
            $generated_by = __('Automatically generated by ITSM-NG');

            //Switch to the desired language
            $bak_dropdowntranslations = (isset($_SESSION['glpi_dropdowntranslations']) ? $_SESSION['glpi_dropdowntranslations'] : null);
            $_SESSION['glpi_dropdowntranslations'] = DropdownTranslation::getAvailableTranslations($language);
            Session::loadLanguage($language);
            $bak_language = $_SESSION["glpilanguage"];
            $_SESSION["glpilanguage"] = $language;

            //If event is raised by a plugin, load it in order to get the language file available
            if ($plug = isPluginItemType(get_class($target->obj))) {
                Plugin::loadLang(strtolower($plug['plugin']), $language);
            }

            //Get template's language data for in this language
            $options['additionnaloption'] = $additionnaloption;
            $data = &$target->getForTemplate($event, $options);

            $footer_string = Html::entity_decode_deep(__('Automatically generated by ITSM-NG'));

            $add_header                = Html::entity_decode_deep($target->getContentHeader());
            $add_footer                = Html::entity_decode_deep($target->getContentFooter());

            if ($template_datas = $this->getByLanguage($language)) {
                //Template processing

                // Decode html chars to have clean text
                $template_datas['content_text']
                                           = Html::entity_decode_deep($template_datas['content_text']);
                $data                      = Html::entity_decode_deep($data);

                $template_datas['subject'] = Html::entity_decode_deep($template_datas['subject']);
                $this->signature           = Html::entity_decode_deep($this->signature);

                $lang['subject']           = $target->getSubjectPrefix($event) .
                                              self::process($template_datas['subject'], $data);
                $lang['content_html']      = '';

                //If no html content, then send only in text
                if (!empty($template_datas['content_html'])) {
                    $signature_html = Html::entities_deep($this->signature);
                    $signature_html = Html::nl2br_deep($signature_html);

                    $template_datas['content_html'] = self::process(
                        $template_datas['content_html'],
                        self::getDataForHtml($target, $data)
                    );

                    $lang['content_html'] =
                          "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
                        'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>" .
                          "<html>
                        <head>
                         <META http-equiv='Content-Type' content='text/html; charset=utf-8'>
                         <title>" . Html::entities_deep($lang['subject']) . "</title>
                         <style type='text/css'>
                           " . $this->fields['css'] . "
                         </style>
                        </head>
                        <body>\n" . (!empty($add_header) ? $add_header . "\n<br><br>" : '') .
                             $template_datas['content_html'] .
                          "<br><br>-- \n<br>" . $signature_html .
                          "<br>$footer_string" .
                          "<br><br>\n" . (!empty($add_footer) ? $add_footer . "\n<br><br>" : '') .
                          "\n</body></html>";
                }

                $lang['content_text']
                      = (!empty($add_header) ? $add_header . "\n\n" : '') .                    Html::clean(self::process(
                          $template_datas['content_text'],
                          $data
                      ) . "\n\n-- \n" . $this->signature .
                                          "\n" . Html::entity_decode_deep($generated_by)) . "\n\n" . $add_footer;
                $this->templates_by_languages[$tid] = $lang;
            }

            // Restore default language
            $_SESSION["glpilanguage"] = $bak_language;
            Session::loadLanguage();
            if ($bak_dropdowntranslations !== null) {
                $_SESSION['glpi_dropdowntranslations'] = $bak_dropdowntranslations;
            } else {
                unset($_SESSION['glpi_dropdowntranslations']);
            }
            if ($plug = isPluginItemType(get_class($target->obj))) {
                Plugin::loadLang(strtolower($plug['plugin']));
            }
        }
        if (isset($this->templates_by_languages[$tid])) {
            return $tid;
        }
        return false;
    }


    /**
     * @param $string
     * @param $data
    **/
    public static function process($string, $data)
    {

        $offset = $new_offset = 0;
        //Template processed
        $output = "";

        $cleandata = [];
        // clean data for strtr
        foreach ($data as $field => $value) {
            if (!is_array($value)) {
                $cleandata[$field] = $value;
            }
        }

        //Remove all
        $string = Toolbox::unclean_cross_side_scripting_deep($string);

        //First of all process the FOREACH tag
        if (
            preg_match_all(
                "/##FOREACH[ ]?(FIRST|LAST)?[ ]?([0-9]*)?[ ]?([a-z-0-9\._]*)##/i",
                $string,
                $out
            )
        ) {
            foreach ($out[3] as $id => $tag_infos) {
                $regex = "/" . $out[0][$id] . "(.*)##ENDFOREACH" . $tag_infos . "##/Uis";

                if (
                    preg_match($regex, $string, $tag_out)
                    && isset($data[$tag_infos])
                    && is_array($data[$tag_infos])
                ) {
                    $data_lang_foreach = $cleandata;
                    unset($data_lang_foreach[$tag_infos]);

                    //Manage FIRST & LAST statement
                    $foreachvalues = $data[$tag_infos];
                    if (!empty($foreachvalues)) {
                        if (isset($out[1][$id]) && ($out[1][$id] != '')) {
                            if ($out[1][$id] == 'FIRST') {
                                $foreachvalues = array_reverse($foreachvalues);
                            }

                            if (isset($out[2][$id]) && $out[2][$id]) {
                                $foreachvalues = array_slice($foreachvalues, 0, $out[2][$id]);
                            } else {
                                $foreachvalues = array_slice($foreachvalues, 0, 1);
                            }
                        }
                    }

                    $output_foreach_string = "";
                    foreach ($foreachvalues as $line) {
                        foreach ($line as $field => $value) {
                            if (!is_array($value)) {
                                $data_lang_foreach[$field] = $value;
                            }
                        }
                        $tmp                    = self::processIf($tag_out[1], $data_lang_foreach);
                        $output_foreach_string .= strtr($tmp, $data_lang_foreach);
                    }
                    $string = str_replace($tag_out[0], $output_foreach_string, $string);
                } else {
                    $string = str_replace($tag_out, '', $string);
                }
            }
        }

        //Now process IF statements
        $string = self::processIf($string, $cleandata);
        $string = strtr($string, $cleandata);

        return $string;
    }


    /**
     * @param $string
     * @param $data
    **/
    public static function processIf($string, $data)
    {

        if (preg_match_all("/##IF([a-z-0-9\._]*)[=]?(.*?)##/i", $string, $out)) {
            foreach ($out[1] as $key => $tag_infos) {
                $if_field = $tag_infos;
                //Get the field tag value (if one)
                $regex_if = "/##IF" . $if_field . "[=]?.*##(.*)##ENDIF" . $if_field . "##/Uis";
                //Get the else tag value (if one)
                $regex_else = "/##ELSE" . $if_field . "[=]?.*##(.*)##ENDELSE" . $if_field . "##/Uis";

                $condition_ok = false;

                if (empty($out[2][$key]) && !strlen($out[2][$key])) { // No = : check if ot empty or not null
                    if (
                        isset($data['##' . $if_field . '##'])
                        && $data['##' . $if_field . '##'] != '0'
                        && $data['##' . $if_field . '##'] != ''
                        && $data['##' . $if_field . '##'] != '&nbsp;'
                        && !is_null($data['##' . $if_field . '##'])
                    ) {
                        $condition_ok = true;
                    }
                } else { // check exact match
                    if (isset($data['##' . $if_field . '##'])) {
                        // Data value: the value for the field in the database
                        $data_value = Html::entity_decode_deep($data['##' . $if_field . '##']);

                        // Condition value: the expected value needed to validate the condition
                        $condition_value = Html::entity_decode_deep($out[2][$key]);

                        // Special case for data returned by Dropdown::getYesNo, we
                        // need to use the localized value in the comparison
                        $to_translate = ["Yes", "No"];
                        if (in_array($condition_value, $to_translate)) {
                            $condition_value = __($condition_value);
                        }

                        // Compare data value and condition value
                        $condition_ok = $condition_value == $data_value;
                    }
                }

                // Force only one replacement to permit multiple use of the same condition
                if ($condition_ok) { // Do IF
                    $string = preg_replace($regex_if, "\\1", $string, 1);
                    $string = preg_replace($regex_else, "", $string, 1);
                } else { // Do ELSE
                    $string = preg_replace($regex_if, "", $string, 1);
                    $string = preg_replace($regex_else, "\\1", $string, 1);
                }
            }
        }
        return $string;
    }

    /**
     * Convert notification data to HTML format.
     *
     * @param NotificationTarget $target
     * @param array $data
     * @return array
     */
    private static function getDataForHtml(NotificationTarget $target, array $data)
    {
        $dataForHtml = [];

        foreach ($data as $tag => $value) {
            if (is_array($value)) {
                $dataForHtml[$tag] = self::getDataForHtml($target, $value);
            } elseif (!in_array($tag, $target->html_tags)) {
                $dataForHtml[$tag] = Html::nl2br_deep(Html::entities_deep($value));
            } else {
                $dataForHtml[$tag] = $value;
            }
        }

        return $dataForHtml;
    }


    /**
     * @param $signature
    **/
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }


    /**
     * @param $language
    **/
    public function getByLanguage($language)
    {
        global $DB;

        $iterator = $DB->request([
           'FROM'   => 'glpi_notificationtemplatetranslations',
           'WHERE'  => [
              'notificationtemplates_id' => $this->getField('id'),
              'language'                 => [$language, '']
           ],
           'ORDER'  => 'language DESC',
           'LIMIT'  => 1
        ]);
        if (count($iterator)) {
            return $iterator->next();
        }

        //No template found at all!
        return false;
    }


    /**
     * @param NotificationTarget $target     Target instance
     * @param string             $tid        template computed id
     * @param mixed              $to         Recipient
     * @param array              $user_infos Extra user infos
     * @param array              $options    Options
     *
     * @return array
    **/
    public function getDataToSend(NotificationTarget $target, $tid, $to, array $user_infos, array $options)
    {

        $language   = $user_infos['language'];
        $user_name  = $user_infos['username'];

        $sender     = $target->getSender();
        $replyto    = $target->getReplyTo($options);

        $mailing_options['to']          = $to;
        $mailing_options['toname']      = $user_name;
        $mailing_options['from']        = $sender['email'];
        $mailing_options['fromname']    = $sender['name'];
        $mailing_options['replyto']     = $replyto['email'];
        $mailing_options['replytoname'] = $replyto['name'];
        $mailing_options['messageid']   = $target->getMessageID();

        $template_data    = $this->templates_by_languages[$tid];
        $mailing_options['subject']      = $template_data['subject'];
        $mailing_options['content_html'] = $template_data['content_html'];
        $mailing_options['content_text'] = $template_data['content_text'];
        $mailing_options['items_id']     = method_exists($target->obj, "getField")
           ? $target->obj->getField('id')
           : 0;
        if (isset($target->obj->documents)) {
            $mailing_options['documents'] = $target->obj->documents;
        }

        return $mailing_options;
    }


    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
              Notification_NotificationTemplate::class,
              NotificationTemplateTranslation::class,
            ]
        );

        // QueuedNotification does not extends CommonDBConnexity
        $queued = new QueuedNotification();
        $queued->deleteByCriteria(['notificationtemplates_id' => $this->fields['id']]);

        $queuedChat = new QueuedChat();
        $queuedChat->deleteByCriteria(['notificationtemplates_id' => $this->fields['id']]);
    }

    public function prepareInputForClone($input)
    {
        parent::prepareInputForClone($input);
        $input['name'] = $input['name'] . ' (clone)';
        return $input;
    }
}
