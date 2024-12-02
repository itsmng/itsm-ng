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
 * LevelAgreement base Class for OLA & SLA
 * @since 9.2
**/

abstract class LevelAgreement extends CommonDBChild
{
    // From CommonDBTM
    public $dohistory          = true;
    public static $rightname       = 'slm';

    // From CommonDBChild
    public static $itemtype = 'SLM';
    public static $items_id = 'slms_id';


    /**
     * Display a specific OLA or SLA warning.
     * Called into the above showForm() function
     *
     * @return void
     */
    abstract public function showFormWarning();

    /**
     * Return the text needed for a confirmation of adding level agreement to a ticket
     *
     * @return array of strings
     */
    abstract public function getAddConfirmation();

    /**
     * Get table fields
     *
     * @param integer $subtype of OLA/SLA, can be SLM::TTO or SLM::TTR
     *
     * @return array of 'date' and 'sla' field names
     */
    public static function getFieldNames($subtype)
    {

        $dateField = null;
        $laField  = null;

        switch ($subtype) {
            case SLM::TTO:
                $dateField = static::$prefixticket . 'time_to_own';
                $laField   = static::$prefix . 's_id_tto';
                break;

            case SLM::TTR:
                $dateField = static::$prefixticket . 'time_to_resolve';
                $laField   = static::$prefix . 's_id_ttr';
                break;
        }
        return [$dateField, $laField];
    }

    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab(static::$levelclass, $ong, $options);
        $this->addStandardTab('Rule', $ong, $options);
        $this->addStandardTab('Ticket', $ong, $options);

        return $ong;
    }

    /**
     * Define calendar of the ticket using the SLA/OLA when using this calendar as sla/ola-s calendar
     *
     * @param integer $calendars_id calendars_id of the ticket
    **/
    public function setTicketCalendar($calendars_id)
    {

        if ($this->fields['calendars_id'] == -1) {
            $this->fields['calendars_id'] = $calendars_id;
        }
    }

    public function post_getFromDB()
    {
        // get calendar from slm
        $slm = new SLM();
        if ($slm->getFromDB($this->fields['slms_id'])) {
            $this->fields['calendars_id'] = $slm->fields['calendars_id'];
        }
    }

    public function post_getEmpty()
    {
        $this->fields['number_time'] = 4;
        $this->fields['definition_time'] = 'hour';
    }

    /**
     * Print the form
     *
     * @param $ID        integer  ID of the item
     * @param $options   array    of possible options:
     *     - target filename : where to go when done.
     *     - withtemplate boolean : template or basic item
     *
     *@return boolean item found
    **/
    public function showForm($ID, $options = [])
    {
        // Get SLM object
        $slm = new SLM();
        if (isset($options['parent'])) {
            $slm = $options['parent'];
        } else {
            $slm->getFromDB($this->fields['slms_id']);
        }

        if ($ID > 0) {
            $this->check($ID, READ);
        } else {
            // Create item
            $options[static::$items_id] = $slm->getField('id');

            //force itemtype of parent
            static::$itemtype = get_class($slm);

            $this->check(-1, CREATE, $options);
        }

        ob_start();
        $this->showFormWarning();
        $warnings = ob_get_clean();

        $form = [
          'action' => $options['target'] ?? $this->getFormURL(),
          'itemtype' => $this->getType(),
          'content' => [
             '' => [
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
                   ],
                   __('SLM') => [
                      'content' => $slm->getLink(),
                   ],
                   [
                      'type' => 'hidden',
                      'name' => 'slms_id',
                      'value' => $this->fields['slms_id'],
                   ],
                   __('Last update') => $ID > 0 ? [
                      'content' => $this->fields["date_mod"] ? Html::convDateTime($this->fields["date_mod"])
                      : __('Never'),
                   ] : [],
                   _n('Type', 'Types', 1) => [
                      'type' => 'select',
                      'name' => 'type',
                      'value' => $this->fields['type'],
                      'values' => self::getTypes(),
                   ],

                      __('Maximum time') => [
                         'type' => 'number',
                         'name' => 'number_time',
                         'value' => $this->fields['number_time'],
                      'min' => 0,
                   ],
                   '' => [
                      'type' => 'select',
                      'name' => 'definition_time',
                      'id' => 'dropdown_definition_time',
                      'value' => $this->fields['definition_time'],
                      'values' => [
                         'minute' => _n('Minute', 'Minutes', Session::getPluralNumber()),
                         'hour' => _n('Hour', 'Hours', Session::getPluralNumber()),
                         'day' => _n('Day', 'Days', Session::getPluralNumber()),
                      ],
                      'hooks' => [
                         'change' => <<<JS
                       if ($('#dropdown_definition_time').val() == 'day') {
                          $('#end_of_working_day').removeAttr('disabled');
                       } else {
                          $('#end_of_working_day').attr('disabled', 'disabled');
                       }
                       JS,
                      ]
                   ],
                   __('End of working day') => [
                      'type' => 'checkbox',
                      'id' => 'end_of_working_day',
                      'name' => 'end_of_working_day',
                      'value' => $this->fields['end_of_working_day'],
                      $this->fields['calendars_id'] != 'day' ? 'disabled' : '' => true,
                   ],
                   __('Comments') => [
                      'type' => 'textarea',
                      'name' => 'comment',
                      'value' => $this->fields['comment'],
                      'rows' => 8,
                      'col_lg' => 12,
                      'col_md' => 12,
                   ],
                   ' ' => [
                      'content' => $warnings,
                   ]
                ]
             ]
          ]
        ];
        renderTwigForm($form, '', $this->fields);

        return true;
    }


    /**
     * Show for ticket
     *
     * @param  Ticket         $ticket Ticket item
     * @param  integer        $type
     * @param  ITILTemplate $tt ticket template object
     * @param  bool           $canupdate update right
     */
    public function showForTicket(Ticket $ticket, $type, $tt, $canupdate)
    {
        list($dateField, $laField) = static::getFieldNames($type);
        $rand = mt_rand();
        $pre  = static::$prefix;
        echo "<table width='100%' aria-label ='Ticket'>";
        echo "<tr class='tab_bg_1'>";

        if (!isset($ticket->fields[$dateField]) || $ticket->fields[$dateField] == 'NULL') {
            $ticket->fields[$dateField] = '';
        }

        if ($ticket->fields['id']) {
            if ($this->getDataForTicket($ticket->fields['id'], $type)) {
                echo "<td style='width: 105px'>";
                echo $tt->getBeginHiddenFieldValue($dateField);
                echo Html::convDateTime($ticket->fields[$dateField]);
                echo $tt->getEndHiddenFieldValue($dateField, $ticket);
                echo "</td>";
                echo "<td>";
                echo $tt->getBeginHiddenFieldText($laField);
                echo "<i class='fas fa-stopwatch slt' aria-hidden='true'></i>";
                echo Dropdown::getDropdownName(
                    static::getTable(),
                    $ticket->fields[$laField]
                ) . "&nbsp;";
                echo Html::hidden($laField, ['value' => $ticket->fields[$laField]]);
                $obj = new static();
                $obj->getFromDB($ticket->fields[$laField]);
                $comment = isset($obj->fields['comment']) ? $obj->fields['comment'] : '';
                $level      = new static::$levelclass();
                $nextaction = new static::$levelticketclass();
                if ($nextaction->getFromDBForTicket($ticket->fields["id"], $type)) {
                    $comment .= '<br/><span class="b spaced">' .
                                  sprintf(
                                      __('Next escalation: %s'),
                                      Html::convDateTime($nextaction->fields['date'])
                                  ) .
                                '</span><br>';
                    if ($level->getFromDB($nextaction->fields[$pre . 'levels_id'])) {
                        $comment .= '<span class="b spaced">' .
                                      sprintf(
                                          __('%1$s: %2$s'),
                                          _n('Escalation level', 'Escalation levels', 1),
                                          $level->getName()
                                      ) .
                                    '</span>';
                    }
                }

                $options = [];
                if (Session::haveRight('slm', READ)) {
                    $options['link'] = $this->getLinkURL();
                }
                Html::showToolTip($comment, $options);
                if ($canupdate) {
                    $delete_field = strtolower(get_called_class()) . "_delete";
                    $fields = [$delete_field       => $delete_field,
                               'id'                => $ticket->getID(),
                               'type'              => $type,
                               '_glpi_csrf_token'  => Session::getNewCSRFToken(),
                               '_glpi_simple_form' => 1];
                    $ticket_url = $ticket->getFormURL();
                    echo Html::scriptBlock("
               function delete_date$type$rand(e) {
                  e.preventDefault();

                  if (nativeConfirm('" . addslashes(__('Also delete date?')) . "')) {
                     submitGetLink('$ticket_url',
                                   " . json_encode(array_merge($fields, ['delete_date' => 1])) . ");
                  } else {
                     submitGetLink('$ticket_url',
                                   " . json_encode(array_merge($fields, ['delete_date' => 0])) . ");
                  }
               }");
                    echo "<a class='fa fa-times-circle pointer'
                        onclick='delete_date$type$rand(event)'
                        title='" . _sx('button', 'Delete permanently') . "'>";
                    echo "<span class='sr-only'>" . _x('button', 'Delete permanently') . "</span>";
                    echo "</a>";
                }
                echo $tt->getEndHiddenFieldText($laField);
                echo "</td>";
            } else {
                echo "<td width='200px'>";
                echo $tt->getBeginHiddenFieldValue($dateField);
                echo "<span class='assign_la'>";
                if ($canupdate) {
                    renderTwigTemplate('macros/input.twig', [
                       'type'      => 'datetime-local',
                       'name'      => $dateField,
                       'value'     => $ticket->fields[$dateField],
                    ]);
                } else {
                    echo Html::convDateTime($ticket->fields[$dateField]);
                }
                echo "</span>";
                echo $tt->getEndHiddenFieldValue($dateField, $ticket);
                $data     = $this->find(
                    ['type' => $type] + getEntitiesRestrictCriteria('', '', $ticket->fields['entities_id'], true)
                );
                if (
                    $canupdate
                    && !empty($data)
                ) {
                    echo $tt->getBeginHiddenFieldText($laField);
                    echo "<span id='la_action$type$rand' class='assign_la'>";
                    echo "<a " . Html::addConfirmationOnAction(
                        $this->getAddConfirmation(),
                        "cleanhide('la_action$type$rand');cleandisplay('la_choice$type$rand');"
                    ) .
                         " class='pointer' title='" . static::getTypeName() . "'>
                    <i class='fas fa-stopwatch slt' aria-hidden='true'></i></a>";
                    echo "</span>";
                    echo "<span id='la_choice$type$rand' style='display:none' class='assign_la'>";
                    echo "<i class='fas fa-stopwatch slt' aria-hidden='true'></i>";
                    echo "<span class='b'>" . static::getTypeName() . "</span>&nbsp;";
                    echo static::dropdown([
                       'name'      => $laField,
                       'entity'    => $ticket->fields["entities_id"],
                       'condition' => ['type' => $type]
                    ]);
                    echo "</span>";
                    echo $tt->getEndHiddenFieldText($laField);
                }
                echo "</td>";
            }
        } else { // New Ticket
            echo "<td>";
            echo $tt->getBeginHiddenFieldValue($dateField);
            renderTwigTemplate('macros/input.twig', [
               'type'      => 'datetime-local',
               'name'      => $dateField,
               'value'     => $ticket->fields[$dateField],
               $tt->isMandatoryField($dateField) ? 'required' : '' => true,
               $canupdate ? null : 'disabled' => true,
            ]);
            echo $tt->getEndHiddenFieldValue($dateField, $ticket);
            echo "</td>";
            $data     = $this->find(
                ['type' => $type] + getEntitiesRestrictCriteria('', '', $ticket->fields['entities_id'], true)
            );
            if (
                $canupdate
                && !empty($data)
            ) {
                echo $tt->getBeginHiddenFieldText($laField);
                if (!$tt->isHiddenField($laField) || $tt->isPredefinedField($laField)) {
                    echo "<th>" . sprintf(
                        __('%1$s%2$s'),
                        static::getTypeName(),
                        $tt->getMandatoryMark($laField)
                    ) . "</th>";
                }
                echo $tt->getEndHiddenFieldText($laField);
                echo "<td class='nopadding'>" . $tt->getBeginHiddenFieldValue($laField);
                static::dropdown([
                   'name'      => $laField,
                   'entity'    => $ticket->fields["entities_id"],
                   'value'     => isset($ticket->fields[$laField]) ? $ticket->fields[$laField] : 0,
                   'condition' => ['type' => $type]
                ]);
                echo $tt->getEndHiddenFieldValue($laField, $ticket);
                echo "</td>";
            }
        }

        echo "</tr>";
        echo "</table>";
    }


    /**
     * Print the HTML for a SLM
     *
     * @param SLM $slm Slm item
     */
    public static function showForSLM(SLM $slm)
    {
        global $CFG_GLPI;

        if (!$slm->can($slm->fields['id'], READ)) {
            return false;
        }

        $instID   = $slm->fields['id'];
        $la       = new static();
        $calendar = new Calendar();
        $rand     = mt_rand();
        $canedit  = ($slm->canEdit($instID)
                     && Session::getCurrentInterface() == "central");

        if ($canedit) {
            echo "<div id='showLa$instID$rand'></div>\n";

            echo "<script type='text/javascript' >";
            echo "function viewAddLa$instID$rand() {";
            $params = ['type'                     => $la->getType(),
                       'parenttype'               => $slm->getType(),
                       $slm->getForeignKeyField() => $instID,
                       'id'                       => -1];
            Ajax::updateItemJsCode(
                "showLa$instID$rand",
                $CFG_GLPI["root_doc"] . "/ajax/viewsubitem.php",
                $params
            );
            echo "}";
            echo "</script>";
            echo "<div class='center firstbloc'>" .
                  "<a class='btn btn-secondary' href='javascript:viewAddLa$instID$rand();'>";
            echo __('Add a new item') . "</a></div>\n";
        }

        // list
        $laList = $la->find(['slms_id' => $instID]);
        Session::initNavigateListItems(
            __CLASS__,
            sprintf(
                __('%1$s = %2$s'),
                $slm::getTypeName(1),
                $slm->getName()
            )
        );
        echo "<div class='spaced'>";
        if (count($laList)) {
            if ($canedit) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = ['container' => 'mass' . __CLASS__ . $rand];
                Html::showMassiveActions($massiveactionparams);
            }

            echo "<table class='tab_cadre_fixehov' aria-label='Item Detail'>";
            $header_begin  = "<tr>";
            $header_top    = '';
            $header_bottom = '';
            $header_end    = '';
            if ($canedit) {
                $header_top .= "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                $header_top .= "</th>";
                $header_bottom .= "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                $header_bottom .= "</th>";
            }
            $header_end .= "<th>" . __('Name') . "</th>";
            $header_end .= "<th>" . _n('Type', 'Types', 1) . "</th>";
            $header_end .= "<th>" . __('Maximum time') . "</th>";
            $header_end .= "<th>" . _n('Calendar', 'Calendars', 1) . "</th>";

            echo $header_begin . $header_top . $header_end;
            foreach ($laList as $val) {
                $edit = ($canedit ? "style='cursor:pointer' onClick=\"viewEditLa" .
                            $instID . $val["id"] . "$rand();\""
                            : '');
                echo "<script type='text/javascript' >";
                echo "function viewEditLa" . $instID . $val["id"] . "$rand() {";
                $params = ['type'                     => $la->getType(),
                           'parenttype'               => $slm->getType(),
                           $slm->getForeignKeyField() => $instID,
                           'id'                       => $val["id"]];
                Ajax::updateItemJsCode(
                    "showLa$instID$rand",
                    $CFG_GLPI["root_doc"] . "/ajax/viewsubitem.php",
                    $params
                );
                echo "};";
                echo "</script>\n";

                echo "<tr class='tab_bg_1'>";
                echo "<td width='10' $edit>";
                if ($canedit) {
                    Html::showMassiveActionCheckBox($la->getType(), $val['id']);
                }
                echo "</td>";
                $la->getFromDB($val['id']);
                echo "<td $edit>" . $la->getLink() . "</td>";
                echo "<td $edit>" . $la->getSpecificValueToDisplay('type', $la->fields['type']) . "</td>";
                echo "<td $edit>";
                echo $la->getSpecificValueToDisplay(
                    'number_time',
                    ['number_time'     => $la->fields['number_time'],
                       'definition_time' => $la->fields['definition_time']]
                );
                echo "</td>";
                if (!$slm->fields['calendars_id']) {
                    $link =  __('24/7');
                } elseif ($slm->fields['calendars_id'] == -1) {
                    $link = __('Calendar of the ticket');
                } elseif ($calendar->getFromDB($slm->fields['calendars_id'])) {
                    $link = $calendar->getLink();
                }
                echo "<td $edit>" . $link . "</td>";
                echo "</tr>";
            }
            echo $header_begin . $header_bottom . $header_end;
            echo "</table>";

            if ($canedit) {
                $massiveactionparams['ontop'] = false;
                Html::showMassiveActions($massiveactionparams);
                Html::closeForm();
            }
        } else {
            echo __('No item to display');
        }
        echo "</div>";
    }

    /**
     * Display a list of rule for the current sla/ola
     * @return void
     */
    public function showRulesList()
    {
        global $DB;

        $fk      = static::getFieldNames($this->fields['type'])[1];
        $rule    = new RuleTicket();
        $rand    = mt_rand();
        $canedit = self::canUpdate();

        $rules_id_list = iterator_to_array($DB->request([
           'SELECT'          => 'rules_id',
           'DISTINCT'        => true,
           'FROM'            => 'glpi_ruleactions',
           'WHERE'           => [
              'field' => $fk,
              'value' => $this->getID()]]));
        $nb = count($rules_id_list);

        echo "<div class='spaced'>";
        if (!$nb) {
            echo "<table class='tab_cadre_fixehov' aria-label='No item Found'>";
            echo "<tr><th>" . __('No item found') . "</th>";
            echo "</tr>\n";
            echo "</table>\n";
        } else {
            if ($canedit) {
                Html::openMassiveActionsForm('massRuleTicket' . $rand);
                $massiveactionparams
                   = ['num_displayed'    => min($_SESSION['glpilist_limit'], $nb),
                      'specific_actions' => ['update' => _x('button', 'Update'),
                                             'purge'  => _x('button', 'Delete permanently')]];
                Html::showMassiveActions($massiveactionparams);
            }
            echo "<table class='tab_cadre_fixehov' aria-label='rule List'>";
            $header_begin  = "<tr>";
            $header_top    = '';
            $header_bottom = '';
            $header_end    = '';
            if ($canedit) {
                $header_begin  .= "<th width='10'>";
                $header_top    .= Html::getCheckAllAsCheckbox('massRuleTicket' . $rand);
                $header_bottom .= Html::getCheckAllAsCheckbox('massRuleTicket' . $rand);
                $header_end    .= "</th>";
            }
            $header_end .= "<th>" . RuleTicket::getTypeName($nb) . "</th>";
            $header_end .= "<th>" . __('Active') . "</th>";
            $header_end .= "<th>" . __('Description') . "</th>";
            $header_end .= "</tr>\n";
            echo $header_begin . $header_top . $header_end;

            Session::initNavigateListItems(
                get_class($this),
                sprintf(
                    __('%1$s = %2$s'),
                    $rule->getTypeName(1),
                    $rule->getName()
                )
            );

            foreach ($rules_id_list as $data) {
                $rule->getFromDB($data['rules_id']);
                Session::addToNavigateListItems(get_class($this), $rule->fields["id"]);
                echo "<tr class='tab_bg_1'>";

                if ($canedit) {
                    echo "<td width='10'>";
                    Html::showMassiveActionCheckBox("RuleTicket", $rule->fields["id"]);
                    echo "</td>";
                    $ruleclassname = get_class($rule);
                    echo "<td><a href='" . $ruleclassname::getFormURLWithID($rule->fields["id"])
                            . "&amp;onglet=1'>" . $rule->fields["name"] . "</a></td>";
                } else {
                    echo "<td>" . $rule->fields["name"] . "</td>";
                }

                echo "<td>" . Dropdown::getYesNo($rule->fields["is_active"]) . "</td>";
                echo "<td>" . $rule->fields["description"] . "</td>";
                echo "</tr>\n";
            }
            echo $header_begin . $header_bottom . $header_end;
            echo "</table>\n";

            if ($canedit) {
                $massiveactionparams['ontop'] = false;
                Html::showMassiveActions($massiveactionparams);
                Html::closeForm();
            }
        }
        echo "</div>";
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            $nb = 0;
            switch ($item->getType()) {
                case 'SLM':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable(
                            self::getTable(),
                            ['slms_id' => $item->getField('id')]
                        );
                    }
                    return self::createTabEntry(static::getTypeName($nb), $nb);
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'SLM':
                self::showForSLM($item);
                break;
        }
        return true;
    }


    /**
     * Get data by type and ticket
     *
     * @param $tickets_id
     * @param $type
     */
    public function getDataForTicket($tickets_id, $type)
    {
        global $DB;

        list($dateField, $field) = static::getFieldNames($type);

        $iterator = $DB->request([
           'SELECT'       => [static::getTable() . '.id'],
           'FROM'         => static::getTable(),
           'INNER JOIN'   => [
              'glpi_tickets' => [
                 'FKEY'   => [
                    static::getTable()   => 'id',
                    'glpi_tickets'       => $field
                 ]
              ]
           ],
           'WHERE'        => ['glpi_tickets.id' => $tickets_id],
           'LIMIT'        => 1
        ]);

        if (count($iterator)) {
            return $this->getFromIter($iterator);
        }
        return false;
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
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'massiveaction'      => false,
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => $this->getTable(),
           'field'              => 'number_time',
           'name'               => _x('hour', 'Time'),
           'datatype'           => 'specific',
           'massiveaction'      => false,
           'nosearch'           => true,
           'additionalfields'   => ['definition_time']
        ];

        $tab[] = [
           'id'                 => '6',
           'table'              => $this->getTable(),
           'field'              => 'end_of_working_day',
           'name'               => __('End of working day'),
           'datatype'           => 'bool',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '7',
           'table'              => $this->getTable(),
           'field'              => 'type',
           'name'               => _n('Type', 'Types', 1),
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '8',
           'table'              => 'glpi_slms',
           'field'              => 'name',
           'name'               => __('SLM'),
           'datatype'           => 'dropdown'
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
     * @param $field
     * @param $values
     * @param $options   array
    **/
    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'number_time':
                switch ($values['definition_time']) {
                    case 'minute':
                        return sprintf(_n('%d minute', '%d minutes', $values[$field]), $values[$field]);

                    case 'hour':
                        return sprintf(_n('%d hour', '%d hours', $values[$field]), $values[$field]);

                    case 'day':
                        return sprintf(_n('%d day', '%d days', $values[$field]), $values[$field]);
                }
                break;

            case 'type':
                return self::getOneTypeName($values[$field]);
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }


    /**
     * @param $field
     * @param $name            (default '')
     * @param $values          (default '')
     * @param $options   array
     *
     * @return string
    **/
    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;
        switch ($field) {
            case 'type':
                $options['value'] = $values[$field];
                return self::getTypeDropdown($options);
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }


    /**
     * Get computed resolution time
     *
     * @return integer resolution time (default 0)
    **/
    public function getTime()
    {

        if (isset($this->fields['id'])) {
            if ($this->fields['definition_time'] == "minute") {
                return $this->fields['number_time'] * MINUTE_TIMESTAMP;
            }
            if ($this->fields['definition_time'] == "hour") {
                return $this->fields['number_time'] * HOUR_TIMESTAMP;
            }
            if ($this->fields['definition_time'] == "day") {
                return $this->fields['number_time'] * DAY_TIMESTAMP;
            }
        }
        return 0;
    }


    /**
     * Get active time between to date time for the active calendar
     *
     * @param datetime $start begin
     * @param datetime $end end
     *
     * @return integer timestamp of delay
     **/
    public function getActiveTimeBetween($start, $end)
    {

        if ($end < $start) {
            return 0;
        }

        if (isset($this->fields['id'])) {
            $cal          = new Calendar();
            $work_in_days = ($this->fields['definition_time'] == 'day');

            // Based on a calendar
            if ($this->fields['calendars_id'] > 0) {
                if ($cal->getFromDB($this->fields['calendars_id'])) {
                    return $cal->getActiveTimeBetween($start, $end, $work_in_days);
                }
            } else { // No calendar
                $timestart = strtotime($start);
                $timeend   = strtotime($end);
                return ($timeend - $timestart);
            }
        }
        return 0;
    }


    /**
     * Get date for current agreement
     *
     * @param string  $start_date        datetime start date
     * @param integer $additional_delay  integer  additional delay to add or substract (for waiting time)
     *
     * @return string|null  due date time (NULL if sla/ola not exists)
    **/
    public function computeDate($start_date, $additional_delay = 0)
    {

        if (isset($this->fields['id'])) {
            $delay = $this->getTime();
            // Based on a calendar
            if ($this->fields['calendars_id'] > 0) {
                $cal          = new Calendar();
                $work_in_days = ($this->fields['definition_time'] == 'day');

                if ($cal->getFromDB($this->fields['calendars_id']) && $cal->hasAWorkingDay()) {
                    return $cal->computeEndDate(
                        $start_date,
                        $delay,
                        $additional_delay,
                        $work_in_days,
                        $this->fields['end_of_working_day']
                    );
                }
            }

            // No calendar defined or invalid calendar
            if ($this->fields['number_time'] >= 0) {
                $starttime = strtotime($start_date);
                $endtime   = $starttime + $delay + $additional_delay;
                return date('Y-m-d H:i:s', $endtime);
            }
        }

        return null;
    }


    /**
     * Get execution date of a level
     *
     * @param string  $start_date        start date
     * @param integer $levels_id         sla/ola level id
     * @param integer $additional_delay  additional delay to add or substract (for waiting time)
     *
     * @return string|null  execution date time (NULL if ola/sla not exists)
    **/
    public function computeExecutionDate($start_date, $levels_id, $additional_delay = 0)
    {

        if (isset($this->fields['id'])) {
            $level = new static::$levelclass();
            $fk = getForeignKeyFieldForItemType(get_called_class());

            if ($level->getFromDB($levels_id)) { // level exists
                if ($level->fields[$fk] == $this->fields['id']) { // correct level
                    $work_in_days = ($this->fields['definition_time'] == 'day');
                    $delay        = $this->getTime();

                    // Based on a calendar
                    if ($this->fields['calendars_id'] > 0) {
                        $cal = new Calendar();
                        if ($cal->getFromDB($this->fields['calendars_id']) && $cal->hasAWorkingDay()) {
                            return $cal->computeEndDate(
                                $start_date,
                                $delay,
                                $level->fields['execution_time'] + $additional_delay,
                                $work_in_days
                            );
                        }
                    }
                    // No calendar defined or invalid calendar
                    $delay    += $additional_delay + $level->fields['execution_time'];
                    $starttime = strtotime($start_date);
                    $endtime   = $starttime + $delay;
                    return date('Y-m-d H:i:s', $endtime);
                }
            }
        }
        return null;
    }


    /**
     * Get types
     *
     * @return array array of types
     **/
    public static function getTypes()
    {
        return [SLM::TTO => __('Time to own'),
                SLM::TTR => __('Time to resolve')];
    }


    /**
     * Get types name
     *
     * @param  integer $type
     * @return string  name
     **/
    public static function getOneTypeName($type)
    {

        $types = self::getTypes();
        $name  = null;
        if (isset($types[$type])) {
            $name = $types[$type];
        }
        return $name;
    }


    /**
     * Get SLA types dropdown
     *
     * @param array $options
     *
     * @return string
     */
    public static function getTypeDropdown($options)
    {

        $params = ['name'  => 'type'];

        foreach ($options as $key => $val) {
            $params[$key] = $val;
        }

        return Dropdown::showFromArray($params['name'], self::getTypes(), $options);
    }


    public function prepareInputForAdd($input)
    {

        if ($input['definition_time'] != 'day') {
            $input['end_of_working_day'] = 0;
        }
        return $input;
    }


    public function prepareInputForUpdate($input)
    {

        if (isset($input['definition_time']) && $input['definition_time'] != 'day') {
            $input['end_of_working_day'] = 0;
        }
        return $input;
    }


    /**
     * Add a level to do for a ticket
     *
     * @param Ticket  $ticket Ticket object
     * @param integer $levels_id SlaLevel or OlaLevel ID
     *
     * @return void
     **/
    public function addLevelToDo(Ticket $ticket, $levels_id = 0)
    {

        $pre = static::$prefix;

        if (!$levels_id && isset($ticket->fields[$pre . 'levels_id_ttr'])) {
            $levels_id = $ticket->fields[$pre . "levels_id_ttr"];
        }

        if ($levels_id) {
            $toadd = [];
            $date = $this->computeExecutionDate(
                $ticket->fields['date'],
                $levels_id,
                $ticket->fields[$pre . '_waiting_duration']
            );
            if ($date != null) {
                $toadd['date']           = $date;
                $toadd[$pre . 'levels_id'] = $levels_id;
                $toadd['tickets_id']     = $ticket->fields["id"];
                $levelticket             = new static::$levelticketclass();
                $levelticket->add($toadd);
            }
        }
    }


    /**
     * remove a level to do for a ticket
     *
     * @param $ticket Ticket object
     *
     * @return void
    **/
    public static function deleteLevelsToDo(Ticket $ticket)
    {
        global $DB;

        $ticketfield = static::$prefix . "levels_id_ttr";

        if ($ticket->fields[$ticketfield] > 0) {
            $levelticket = new static::$levelticketclass();
            $iterator = $DB->request([
               'SELECT' => 'id',
               'FROM'   => $levelticket::getTable(),
               'WHERE'  => ['tickets_id' => $ticket->fields['id']]
            ]);

            while ($data = $iterator->next()) {
                $levelticket->delete(['id' => $data['id']]);
            }
        }
    }


    public function cleanDBonPurge()
    {
        global $DB;

        // Clean levels
        $classname = get_called_class();
        $fk        = getForeignKeyFieldForItemType($classname);
        $level     = new static::$levelclass();
        $level->deleteByCriteria([$fk => $this->getID()]);

        // Update tickets : clean SLA/OLA
        list($dateField, $laField) = static::getFieldNames($this->fields['type']);
        $iterator =  $DB->request([
           'SELECT' => 'id',
           'FROM'   => 'glpi_tickets',
           'WHERE'  => [$laField => $this->fields['id']]
        ]);

        if (count($iterator)) {
            $ticket = new Ticket();
            while ($data = $iterator->next()) {
                $ticket->deleteLevelAgreement($classname, $data['id'], $this->fields['type']);
            }
        }

        Rule::cleanForItemAction($this);
    }
}
