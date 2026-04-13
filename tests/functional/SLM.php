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

class SLM extends DbTestCase
{
    private $method;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        //to handle GLPI barbarian replacements.
        $this->method = str_replace(
            ['\\', 'beforeTestMethod'],
            ['', $method],
            __METHOD__
        );
    }


    /**
     * Create a full SLM with all level filled (slm/sla/ola/levels/action/criterias)
     * And Delete IT to check clean os sons objects
     */
    public function testLifecyle()
    {
        $this->login();

        // ## 1 - test adding sla and sub objects

        // prepare a calendar with limited time ranges [8:00 -> 20:00]
        $cal    = new \Calendar();
        $calseg = new \CalendarSegment();
        $cal_id = $cal->add(['name' => "test calendar"]);
        $this->checkInput($cal, $cal_id);
        for ($day = 1; $day <= 5; $day++) {
            $calseg_id = $calseg->add([
               'calendars_id' => $cal_id,
               'day'          => $day,
               'begin'        => '08:00:00',
               'end'          => '20:00:00'
            ]);
            $this->checkInput($calseg, $calseg_id);
        }

        $slm    = new \SLM();
        $slm_id = $slm->add($slm_in = [
           'name'         => $this->method,
           'comment'      => $this->getUniqueString(),
           'calendars_id' => $cal_id,
        ]);
        $this->checkInput($slm, $slm_id, $slm_in);

        // prepare sla/ola inputs
        $sla1_in = $sla2_in = [
           'slms_id'         => $slm_id,
           'name'            => "SLA TTO",
           'comment'         => $this->getUniqueString(),
           'type'            => \SLM::TTO,
           'number_time'     => 4,
           'definition_time' => 'day',
        ];
        $sla2_in['type'] = \SLM::TTR;
        $sla2_in['name'] = "SLA TTR";

        // add two sla (TTO & TTR)
        $sla    = new \SLA();
        $sla1_id = $sla->add($sla1_in);
        $this->checkInput($sla, $sla1_id, $sla1_in);
        $sla2_id = $sla->add($sla2_in);
        $this->checkInput($sla, $sla2_id, $sla2_in);

        // add two ola (TTO & TTR), we re-use the same inputs as sla
        $ola  = new \OLA();
        $sla1_in['name'] = str_replace("SLA", "OLA", $sla1_in['name']);
        $sla2_in['name'] = str_replace("SLA", "OLA", $sla2_in['name']);
        $ola1_id = $ola->add($sla1_in);
        $this->checkInput($ola, $ola1_id, $sla1_in);
        $ola2_id = $ola->add($sla2_in);
        $this->checkInput($ola, $ola2_id, $sla2_in);

        // prepare levels input for each ola/sla
        $slal1_in = $slal2_in = $olal1_in = $olal2_in = [
           'name'           => $this->method,
           'execution_time' => -DAY_TIMESTAMP,
           'is_active'      => 1,
           'match'          => 'AND',
           'slas_id'        => $sla1_id
        ];
        $slal2_in['slas_id'] = $sla2_id;
        unset($olal1_in['slas_id'], $olal2_in['slas_id']);
        $olal1_in['olas_id'] = $ola1_id;
        $olal2_in['olas_id'] = $ola2_id;

        // add levels
        $slal = new \SlaLevel();
        $slal1_id = $slal->add($slal1_in);
        $this->checkInput($slal, $slal1_id, $slal1_in);
        $slal2_id = $slal->add($slal2_in);
        $this->checkInput($slal, $slal2_id, $slal2_in);

        $olal = new \OlaLevel();
        $olal1_id = $olal->add($olal1_in);
        $this->checkInput($olal, $olal1_id, $olal1_in);
        $olal2_id = $olal->add($olal2_in);
        $this->checkInput($olal, $olal2_id, $olal2_in);

        // add criteria/actions
        $scrit_in = $ocrit_in = [
           'slalevels_id' => $slal1_id,
           'criteria'     => 'status',
           'condition'    => 1,
           'pattern'      => 1
        ];
        unset($ocrit_in['slalevels_id']);
        $ocrit_in['olalevels_id'] = $olal1_id;
        $saction_in = $oaction_in = [
           'slalevels_id' => $slal1_id,
           'action_type'  => 'assign',
           'field'        => 'status',
           'value'        => 4
        ];
        unset($oaction_in['slalevels_id']);
        $oaction_in['olalevels_id'] = $olal1_id;

        $scrit    = new \SlaLevelCriteria();
        $ocrit    = new \OlaLevelCriteria();
        $saction  = new \SlaLevelAction();
        $oaction  = new \OlaLevelAction();

        $scrit_id   = $scrit->add($scrit_in);
        $ocrit_id   = $ocrit->add($ocrit_in);
        $saction_id = $saction->add($saction_in);
        $oaction_id = $oaction->add($oaction_in);
        $this->checkInput($scrit, $scrit_id, $scrit_in);
        $this->checkInput($ocrit, $ocrit_id, $ocrit_in);
        $this->checkInput($saction, $saction_id, $saction_in);
        $this->checkInput($oaction, $oaction_id, $oaction_in);

        // ## 2 - test using sla in tickets

        // add rules for using sla
        $ruleticket = new \RuleTicket();
        $rulecrit   = new \RuleCriteria();
        $ruleaction = new \RuleAction();

        $ruletid = $ruleticket->add($ruleinput = [
           'name'         => $this->method,
           'match'        => 'AND',
           'is_active'    => 1,
           'sub_type'     => 'RuleTicket',
           'condition'    => \RuleTicket::ONADD + \RuleTicket::ONUPDATE,
           'is_recursive' => 1
        ]);
        $this->checkInput($ruleticket, $ruletid, $ruleinput);
        $crit_id = $rulecrit->add($crit_input = [
           'rules_id'  => $ruletid,
           'criteria'  => 'name',
           'condition' => 2,
           'pattern'   => $this->method
        ]);
        $this->checkInput($rulecrit, $crit_id, $crit_input);
        $act_id = $ruleaction->add($act_input = [
           'rules_id'    => $ruletid,
           'action_type' => 'assign',
           'field'       => 'slas_id_tto',
           'value'       => $sla1_id
        ]);
        $act_id = $ruleaction->add($act_input = [
           'rules_id'    => $ruletid,
           'action_type' => 'assign',
           'field'       => 'slas_id_ttr',
           'value'       => $sla2_id
        ]);
        $act_id = $ruleaction->add($act_input = [
           'rules_id'    => $ruletid,
           'action_type' => 'assign',
           'field'       => 'olas_id_tto',
           'value'       => $ola1_id
        ]);
        $act_id = $ruleaction->add($act_input = [
           'rules_id'    => $ruletid,
           'action_type' => 'assign',
           'field'       => 'olas_id_ttr',
           'value'       => $ola2_id
        ]);
        $this->checkInput($ruleaction, $act_id, $act_input);

        // test create ticket
        $ticket = new \Ticket();
        $start_date = date("Y-m-d H:i:s", time() - 4 * DAY_TIMESTAMP);
        $tickets_id = $ticket->add($ticket_input = [
           'date'    => $start_date,
           'name'    => $this->method,
           'content' => $this->method
        ]);
        $this->checkInput($ticket, $tickets_id, $ticket_input);
        $this->integer((int)$ticket->getField('slas_id_tto'))->isEqualTo($sla1_id);
        $this->integer((int)$ticket->getField('slas_id_ttr'))->isEqualTo($sla2_id);
        $this->integer((int)$ticket->getField('olas_id_tto'))->isEqualTo($ola1_id);
        $this->integer((int)$ticket->getField('olas_id_ttr'))->isEqualTo($ola2_id);
        $this->string($ticket->getField('time_to_resolve'))->length->isEqualTo(19);

        // test update ticket
        $ticket = new \Ticket();
        $tickets_id_2 = $ticket->add($ticket_input_2 = [
           'name'    => "to be updated",
           'content' => $this->method
        ]);
        $ticket->update([
           'id'   => $tickets_id_2,
           'name' => $this->method
        ]);
        $ticket_input_2['name'] = $this->method;
        $this->checkInput($ticket, $tickets_id_2, $ticket_input_2);
        $this->integer((int)$ticket->getField('slas_id_tto'))->isEqualTo($sla1_id);
        $this->integer((int)$ticket->getField('slas_id_ttr'))->isEqualTo($sla2_id);
        $this->integer((int)$ticket->getField('olas_id_tto'))->isEqualTo($ola1_id);
        $this->integer((int)$ticket->getField('olas_id_ttr'))->isEqualTo($ola2_id);
        $this->string($ticket->getField('time_to_resolve'))->length->isEqualTo(19);

        // ## 3 - test purge of slm and check if we don't find any sub objects
        $this->boolean($slm->delete(['id' => $slm_id], true))->isTrue();
        //sla
        $this->boolean($sla->getFromDB($sla1_id))->isFalse();
        $this->boolean($sla->getFromDB($sla2_id))->isFalse();
        //ola
        $this->boolean($ola->getFromDB($ola1_id))->isFalse();
        $this->boolean($ola->getFromDB($ola2_id))->isFalse();
        //slalevel
        $this->boolean($slal->getFromDB($slal1_id))->isFalse();
        $this->boolean($slal->getFromDB($slal2_id))->isFalse();
        //olalevel
        $this->boolean($olal->getFromDB($olal1_id))->isFalse();
        $this->boolean($olal->getFromDB($olal2_id))->isFalse();
        //crit
        $this->boolean($scrit->getFromDB($scrit_id))->isFalse();
        $this->boolean($ocrit->getFromDB($ocrit_id))->isFalse();
        //action
        $this->boolean($saction->getFromDB($saction_id))->isFalse();
        $this->boolean($oaction->getFromDB($oaction_id))->isFalse();
    }

    public function testManualSlaOlaNotOverriddenByRule()
    {
        $this->login();

        $slm = new \SLM();
        $slm_id = $slm->add([
           'name'    => $this->method,
           'comment' => $this->getUniqueString(),
        ]);
        $this->integer($slm_id)->isGreaterThan(0);

        $sla = new \SLA();
        $sla_rule_tto_id = $sla->add([
           'slms_id'         => $slm_id,
           'name'            => 'Rule SLA TTO',
           'type'            => \SLM::TTO,
           'number_time'     => 4,
           'definition_time' => 'day',
        ]);
        $this->integer($sla_rule_tto_id)->isGreaterThan(0);
        $sla_rule_ttr_id = $sla->add([
           'slms_id'         => $slm_id,
           'name'            => 'Rule SLA TTR',
           'type'            => \SLM::TTR,
           'number_time'     => 5,
           'definition_time' => 'day',
        ]);
        $this->integer($sla_rule_ttr_id)->isGreaterThan(0);
        $sla_manual_tto_id = $sla->add([
           'slms_id'         => $slm_id,
           'name'            => 'Manual SLA TTO',
           'type'            => \SLM::TTO,
           'number_time'     => 6,
           'definition_time' => 'day',
        ]);
        $this->integer($sla_manual_tto_id)->isGreaterThan(0);
        $sla_manual_ttr_id = $sla->add([
           'slms_id'         => $slm_id,
           'name'            => 'Manual SLA TTR',
           'type'            => \SLM::TTR,
           'number_time'     => 7,
           'definition_time' => 'day',
        ]);
        $this->integer($sla_manual_ttr_id)->isGreaterThan(0);

        $ola = new \OLA();
        $ola_rule_tto_id = $ola->add([
           'slms_id'         => $slm_id,
           'name'            => 'Rule OLA TTO',
           'type'            => \SLM::TTO,
           'number_time'     => 4,
           'definition_time' => 'day',
        ]);
        $this->integer($ola_rule_tto_id)->isGreaterThan(0);
        $ola_rule_ttr_id = $ola->add([
           'slms_id'         => $slm_id,
           'name'            => 'Rule OLA TTR',
           'type'            => \SLM::TTR,
           'number_time'     => 5,
           'definition_time' => 'day',
        ]);
        $this->integer($ola_rule_ttr_id)->isGreaterThan(0);
        $ola_manual_tto_id = $ola->add([
           'slms_id'         => $slm_id,
           'name'            => 'Manual OLA TTO',
           'type'            => \SLM::TTO,
           'number_time'     => 6,
           'definition_time' => 'day',
        ]);
        $this->integer($ola_manual_tto_id)->isGreaterThan(0);
        $ola_manual_ttr_id = $ola->add([
           'slms_id'         => $slm_id,
           'name'            => 'Manual OLA TTR',
           'type'            => \SLM::TTR,
           'number_time'     => 7,
           'definition_time' => 'day',
        ]);
        $this->integer($ola_manual_ttr_id)->isGreaterThan(0);

        $ruleticket = new \RuleTicket();
        $rulecrit   = new \RuleCriteria();
        $ruleaction = new \RuleAction();
        $ruletid = $ruleticket->add([
           'name'         => $this->method,
           'match'        => 'AND',
           'is_active'    => 1,
           'sub_type'     => 'RuleTicket',
           'condition'    => \RuleTicket::ONADD | \RuleTicket::ONUPDATE,
           'is_recursive' => 1,
        ]);
        $this->integer($ruletid)->isGreaterThan(0);
        $crit_id = $rulecrit->add([
           'rules_id'  => $ruletid,
           'criteria'  => 'name',
           'condition' => \Rule::PATTERN_IS,
           'pattern'   => $this->method,
        ]);
        $this->integer($crit_id)->isGreaterThan(0);
        $this->integer(
            $ruleaction->add([
               'rules_id'    => $ruletid,
               'action_type' => 'assign',
               'field'       => 'slas_id_tto',
               'value'       => $sla_rule_tto_id,
            ])
        )->isGreaterThan(0);
        $this->integer(
            $ruleaction->add([
               'rules_id'    => $ruletid,
               'action_type' => 'assign',
               'field'       => 'slas_id_ttr',
               'value'       => $sla_rule_ttr_id,
            ])
        )->isGreaterThan(0);
        $this->integer(
            $ruleaction->add([
               'rules_id'    => $ruletid,
               'action_type' => 'assign',
               'field'       => 'olas_id_tto',
               'value'       => $ola_rule_tto_id,
            ])
        )->isGreaterThan(0);
        $this->integer(
            $ruleaction->add([
               'rules_id'    => $ruletid,
               'action_type' => 'assign',
               'field'       => 'olas_id_ttr',
               'value'       => $ola_rule_ttr_id,
            ])
        )->isGreaterThan(0);

        $ticket = new \Ticket();
        $ticket_id = $ticket->add([
           'name'        => $this->method,
           'content'     => $this->method,
           'slas_id_tto' => $sla_manual_tto_id,
           'slas_id_ttr' => $sla_manual_ttr_id,
           'olas_id_tto' => $ola_manual_tto_id,
           'olas_id_ttr' => $ola_manual_ttr_id,
        ]);
        $this->integer($ticket_id)->isGreaterThan(0);
        $this->boolean($ticket->getFromDB($ticket_id))->isTrue();
        $this->integer((int)$ticket->fields['slas_id_tto'])->isEqualTo($sla_manual_tto_id);
        $this->integer((int)$ticket->fields['slas_id_ttr'])->isEqualTo($sla_manual_ttr_id);
        $this->integer((int)$ticket->fields['olas_id_tto'])->isEqualTo($ola_manual_tto_id);
        $this->integer((int)$ticket->fields['olas_id_ttr'])->isEqualTo($ola_manual_ttr_id);

        $ticket_2 = new \Ticket();
        $ticket_2_id = $ticket_2->add([
           'name'    => 'Ticket without manual SLA/OLA',
           'content' => $this->method,
        ]);
        $this->integer($ticket_2_id)->isGreaterThan(0);
        $this->boolean(
            $ticket_2->update([
               'id'          => $ticket_2_id,
               'name'        => $this->method,
               'slas_id_tto' => $sla_manual_tto_id,
               'slas_id_ttr' => $sla_manual_ttr_id,
               'olas_id_tto' => $ola_manual_tto_id,
               'olas_id_ttr' => $ola_manual_ttr_id,
            ])
        )->isTrue();
        $this->boolean($ticket_2->getFromDB($ticket_2_id))->isTrue();
        $this->integer((int)$ticket_2->fields['slas_id_tto'])->isEqualTo($sla_manual_tto_id);
        $this->integer((int)$ticket_2->fields['slas_id_ttr'])->isEqualTo($sla_manual_ttr_id);
        $this->integer((int)$ticket_2->fields['olas_id_tto'])->isEqualTo($ola_manual_tto_id);
        $this->integer((int)$ticket_2->fields['olas_id_ttr'])->isEqualTo($ola_manual_ttr_id);
    }

    public function testLevelAgreementDateComputationWithoutCalendar()
    {
        $this->login();

        $slm = new \SLM();
        $slm_id = $slm->add([
           'name'    => $this->method,
           'comment' => $this->getUniqueString(),
        ]);
        $this->integer($slm_id)->isGreaterThan(0);

        $ola = new \OLA();
        $ola_id = $ola->add([
           'slms_id'         => $slm_id,
           'name'            => 'No calendar OLA',
           'type'            => \SLM::TTR,
           'number_time'     => 2,
           'definition_time' => 'hour',
        ]);
        $this->integer($ola_id)->isGreaterThan(0);
        $this->boolean($ola->getFromDB($ola_id))->isTrue();

        $olalevel = new \OlaLevel();
        $olalevel_id = $olalevel->add([
           'name'           => $this->method,
           'execution_time' => -HOUR_TIMESTAMP,
           'is_active'      => 1,
           'match'          => 'AND',
           'olas_id'        => $ola_id,
        ]);
        $this->integer($olalevel_id)->isGreaterThan(0);

        $start_date = '2026-02-20 10:00:00';
        $this->string($ola->computeDate($start_date))->isEqualTo('2026-02-20 12:00:00');
        $this->string($ola->computeExecutionDate($start_date, $olalevel_id))->isEqualTo('2026-02-20 11:00:00');
    }

    public function testSlaAndOlaLevelProgressionSchedulesNextLevel()
    {
        $this->login();

        $slm = new \SLM();
        $slm_id = $slm->add([
           'name'    => $this->method,
           'comment' => $this->getUniqueString(),
        ]);
        $this->integer($slm_id)->isGreaterThan(0);

        $sla = new \SLA();
        $sla_id = $sla->add([
           'slms_id'         => $slm_id,
           'name'            => 'Escalation SLA',
           'type'            => \SLM::TTR,
           'number_time'     => 2,
           'definition_time' => 'hour',
        ]);
        $this->integer($sla_id)->isGreaterThan(0);

        $slalevel = new \SlaLevel();
        $slalevel_1_id = $slalevel->add([
           'name'           => 'SLA level 1',
           'execution_time' => -HOUR_TIMESTAMP,
           'is_active'      => 1,
           'match'          => 'AND',
           'slas_id'        => $sla_id,
        ]);
        $this->integer($slalevel_1_id)->isGreaterThan(0);
        $slalevel_2_id = $slalevel->add([
           'name'           => 'SLA level 2',
           'execution_time' => 0,
           'is_active'      => 1,
           'match'          => 'AND',
           'slas_id'        => $sla_id,
        ]);
        $this->integer($slalevel_2_id)->isGreaterThan(0);

        $ola = new \OLA();
        $ola_id = $ola->add([
           'slms_id'         => $slm_id,
           'name'            => 'Escalation OLA',
           'type'            => \SLM::TTR,
           'number_time'     => 2,
           'definition_time' => 'hour',
        ]);
        $this->integer($ola_id)->isGreaterThan(0);

        $olalevel = new \OlaLevel();
        $olalevel_1_id = $olalevel->add([
           'name'           => 'OLA level 1',
           'execution_time' => -HOUR_TIMESTAMP,
           'is_active'      => 1,
           'match'          => 'AND',
           'olas_id'        => $ola_id,
        ]);
        $this->integer($olalevel_1_id)->isGreaterThan(0);
        $olalevel_2_id = $olalevel->add([
           'name'           => 'OLA level 2',
           'execution_time' => 0,
           'is_active'      => 1,
           'match'          => 'AND',
           'olas_id'        => $ola_id,
        ]);
        $this->integer($olalevel_2_id)->isGreaterThan(0);

        $ticket = new \Ticket();
        $ticket_id = $ticket->add([
           'name'        => $this->method,
           'content'     => $this->method,
           'slas_id_ttr' => $sla_id,
           'olas_id_ttr' => $ola_id,
        ]);
        $this->integer($ticket_id)->isGreaterThan(0);
        $this->boolean($ticket->getFromDB($ticket_id))->isTrue();

        $ticket->manageSlaLevel($sla_id);
        $ticket->manageOlaLevel($ola_id);

        $sla_rows = array_values(getAllDataFromTable('glpi_slalevels_tickets', ['tickets_id' => $ticket_id]));
        $ola_rows = array_values(getAllDataFromTable('glpi_olalevels_tickets', ['tickets_id' => $ticket_id]));
        $this->integer(count($sla_rows))->isEqualTo(1);
        $this->integer(count($ola_rows))->isEqualTo(1);
        $this->integer((int)$sla_rows[0]['slalevels_id'])->isEqualTo($slalevel_1_id);
        $this->integer((int)$ola_rows[0]['olalevels_id'])->isEqualTo($olalevel_1_id);

        \SlaLevel_Ticket::doLevelForTicket($sla_rows[0], \SLM::TTR);
        \OlaLevel_Ticket::doLevelForTicket($ola_rows[0], \SLM::TTR);

        $sla_rows = array_values(getAllDataFromTable('glpi_slalevels_tickets', ['tickets_id' => $ticket_id]));
        $ola_rows = array_values(getAllDataFromTable('glpi_olalevels_tickets', ['tickets_id' => $ticket_id]));
        $this->integer(count($sla_rows))->isEqualTo(1);
        $this->integer(count($ola_rows))->isEqualTo(1);
        $this->integer((int)$sla_rows[0]['slalevels_id'])->isEqualTo($slalevel_2_id);
        $this->integer((int)$ola_rows[0]['olalevels_id'])->isEqualTo($olalevel_2_id);
    }

    public function testCronSlaAndOlaTicketProcessOverdueLevels()
    {
        global $DB;

        $this->login();

        $slm = new \SLM();
        $slm_id = $slm->add([
           'name'    => $this->method,
           'comment' => $this->getUniqueString(),
        ]);
        $this->integer($slm_id)->isGreaterThan(0);

        $sla = new \SLA();
        $sla_id = $sla->add([
           'slms_id'         => $slm_id,
           'name'            => 'Cron SLA',
           'type'            => \SLM::TTR,
           'number_time'     => 2,
           'definition_time' => 'hour',
        ]);
        $this->integer($sla_id)->isGreaterThan(0);

        $slalevel = new \SlaLevel();
        $slalevel_id = $slalevel->add([
           'name'           => 'Cron SLA level',
           'execution_time' => 0,
           'is_active'      => 1,
           'match'          => 'AND',
           'slas_id'        => $sla_id,
        ]);
        $this->integer($slalevel_id)->isGreaterThan(0);

        $ola = new \OLA();
        $ola_id = $ola->add([
           'slms_id'         => $slm_id,
           'name'            => 'Cron OLA',
           'type'            => \SLM::TTR,
           'number_time'     => 2,
           'definition_time' => 'hour',
        ]);
        $this->integer($ola_id)->isGreaterThan(0);

        $olalevel = new \OlaLevel();
        $olalevel_id = $olalevel->add([
           'name'           => 'Cron OLA level',
           'execution_time' => 0,
           'is_active'      => 1,
           'match'          => 'AND',
           'olas_id'        => $ola_id,
        ]);
        $this->integer($olalevel_id)->isGreaterThan(0);

        $ticket = new \Ticket();
        $ticket_id = $ticket->add([
           'name'        => $this->method,
           'content'     => $this->method,
           'slas_id_ttr' => $sla_id,
           'olas_id_ttr' => $ola_id,
        ]);
        $this->integer($ticket_id)->isGreaterThan(0);
        $this->boolean($ticket->getFromDB($ticket_id))->isTrue();

        $ticket->manageSlaLevel($sla_id);
        $ticket->manageOlaLevel($ola_id);

        $sla_rows = array_values(getAllDataFromTable('glpi_slalevels_tickets', ['tickets_id' => $ticket_id]));
        $ola_rows = array_values(getAllDataFromTable('glpi_olalevels_tickets', ['tickets_id' => $ticket_id]));
        $this->integer(count($sla_rows))->isEqualTo(1);
        $this->integer(count($ola_rows))->isEqualTo(1);

        $past_date = date('Y-m-d H:i:s', time() - HOUR_TIMESTAMP);
        $DB->update('glpi_slalevels_tickets', ['date' => $past_date], ['id' => $sla_rows[0]['id']]);
        $DB->update('glpi_olalevels_tickets', ['date' => $past_date], ['id' => $ola_rows[0]['id']]);

        $this->integer(\SlaLevel_Ticket::cronSlaTicket(new \CronTask()))->isEqualTo(1);
        $this->integer(\OlaLevel_Ticket::cronOlaTicket(new \CronTask()))->isEqualTo(1);

        $this->integer((int)countElementsInTable('glpi_slalevels_tickets', ['tickets_id' => $ticket_id]))->isEqualTo(0);
        $this->integer((int)countElementsInTable('glpi_olalevels_tickets', ['tickets_id' => $ticket_id]))->isEqualTo(0);
    }

    public function testWaitingTimeImpactsSlaTtrDueDate()
    {
        $this->login();

        $currenttime_bak = $_SESSION['glpi_currenttime'];
        $tomorrow_1pm = date('Y-m-d H:i:s', strtotime('tomorrow 1pm'));
        $tomorrow_2pm = date('Y-m-d H:i:s', strtotime('tomorrow 2pm'));

        $calendar = new \Calendar();
        $segment = new \CalendarSegment();
        $calendars_id = $calendar->add(['name' => 'waiting-sla-' . $this->getUniqueString()]);
        $this->integer($calendars_id)->isGreaterThan(0);

        $segments_id = $segment->add([
            'calendars_id' => $calendars_id,
            'day'          => (int)date('w') === 6 ? 0 : (int)date('w') + 1,
            'begin'        => '09:00:00',
            'end'          => '19:00:00',
        ]);
        $this->integer($segments_id)->isGreaterThan(0);

        $slm = new \SLM();
        $slms_id = $slm->add([
            'name'         => 'waiting-sla-' . $this->getUniqueString(),
            'calendars_id' => $calendars_id,
        ]);
        $this->integer($slms_id)->isGreaterThan(0);

        $sla = new \SLA();
        $slas_id = $sla->add([
            'slms_id'         => $slms_id,
            'name'            => 'waiting-ttr-sla-' . $this->getUniqueString(),
            'type'            => \SLM::TTR,
            'number_time'     => 4,
            'definition_time' => 'hour',
        ]);
        $this->integer($slas_id)->isGreaterThan(0);

        $ticket = new \Ticket();
        $tickets_id = $ticket->add([
            'name'        => 'waiting-sla-ticket-' . $this->getUniqueString(),
            'content'     => 'waiting impact on sla due date',
            'slas_id_ttr' => $slas_id,
        ]);
        $this->integer($tickets_id)->isGreaterThan(0);
        $this->boolean($ticket->getFromDB($tickets_id))->isTrue();
        $this->variable($ticket->fields['time_to_resolve'])->isEqualTo($tomorrow_1pm);

        $this->boolean($ticket->update([
            'id'     => $tickets_id,
            'status' => \CommonITILObject::WAITING,
        ]))->isTrue();

        $_SESSION['glpi_currenttime'] = date('Y-m-d H:i:s', strtotime('tomorrow 10am'));
        $updated = $ticket->update([
            'id'     => $tickets_id,
            'status' => \CommonITILObject::ASSIGNED,
        ]);
        $_SESSION['glpi_currenttime'] = $currenttime_bak;

        $this->boolean($updated)->isTrue();
        $this->boolean($ticket->getFromDB($tickets_id))->isTrue();
        $this->variable($ticket->fields['time_to_resolve'])->isEqualTo($tomorrow_2pm);
    }

    /**
     * Check 'internal_time_to_resolve' computed dates.
     */
    public function testInternalTtrComputation()
    {
        $this->login();

        $currenttime_bak = $_SESSION['glpi_currenttime'];
        $tomorrow_1pm = date('Y-m-d H:i:s', strtotime('tomorrow 1pm'));
        $tomorrow_2pm = date('Y-m-d H:i:s', strtotime('tomorrow 2pm'));

        // Create a calendar having tommorow as working day
        $calendar = new \Calendar();
        $segment  = new \CalendarSegment();
        $calendar_id = $calendar->add(['name' => 'TicketRecurrent testing calendar']);
        $this->integer($calendar_id)->isGreaterThan(0);

        $segment_id = $segment->add(
            [
              'calendars_id' => $calendar_id,
              'day'          => (int)date('w') === 6 ? 0 : (int)date('w') + 1,
              'begin'        => '09:00:00',
              'end'          => '19:00:00'
         ]
        );
        $this->integer($segment_id)->isGreaterThan(0);

        // Create SLM with TTR OLA
        $slm = new \SLM();
        $slm_id = $slm->add(
            [
              'name'         => 'Test SLM',
              'calendars_id' => $calendar_id,
         ]
        );
        $this->integer($slm_id)->isGreaterThan(0);

        $ola = new \OLA();
        $ola_id = $ola->add(
            [
              'slms_id'         => $slm_id,
              'name'            => 'Test TTR OLA',
              'type'            => \SLM::TTR,
              'number_time'     => 4,
              'definition_time' => 'hour',
         ]
        );
        $this->integer($ola_id)->isGreaterThan(0);

        // Create ticket to test computation based on OLA
        $ticket = new \Ticket();
        $ticket_id = $ticket->add(
            [
              'name'    => 'Test Ticket',
              'content' => 'Ticket for TTR OLA test',
         ]
        );
        $this->integer($ticket_id)->isGreaterThan(0);

        $this->boolean($ticket->getFromDB($ticket_id))->isTrue();
        $this->integer((int)$ticket->fields['olas_id_ttr'])->isEqualTo(0);
        $this->variable($ticket->fields['ola_ttr_begin_date'])->isEqualTo(null);
        $this->variable($ticket->fields['internal_time_to_resolve'])->isEqualTo(null);

        //Wait...
        sleep(1);

        // Assign TTR OLA
        $update_time_1 = time();
        $this->boolean($ticket->update(['id' => $ticket_id, 'olas_id_ttr' => $ola_id]))->isTrue();
        $update_time_2 = time();
        $this->boolean($ticket->getFromDB($ticket_id))->isTrue();
        $this->integer((int)$ticket->fields['olas_id_ttr'])->isEqualTo($ola_id);
        $this->integer(strtotime((string) $ticket->fields['ola_ttr_begin_date']))
           ->isGreaterThanOrEqualTo($update_time_1)
           ->isLessThanOrEqualTo($update_time_2);
        $this->variable($ticket->fields['internal_time_to_resolve'])->isEqualTo($tomorrow_1pm);

        // Simulate waiting to first working hour +1
        $this->boolean(
            $ticket->update(
                [
                 'id' => $ticket_id,
                 'status' => \CommonITILObject::WAITING,
            ]
            )
        )->isTrue();
        $_SESSION['glpi_currenttime'] = date('Y-m-d H:i:s', strtotime('tomorrow 10am'));
        $updated = $ticket->update(['id' => $ticket_id, 'status' => \CommonITILObject::ASSIGNED]);
        $_SESSION['glpi_currenttime'] = $currenttime_bak;
        $this->boolean($updated)->isTrue();
        $this->variable($ticket->fields['internal_time_to_resolve'])->isEqualTo($tomorrow_2pm);

        // Create ticket to test computation based on manual date
        $ticket = new \Ticket();
        $ticket_id = $ticket->add(
            [
              'name'    => 'Test Ticket',
              'content' => 'Ticket for TTR manual test',
         ]
        );
        $this->integer($ticket_id)->isGreaterThan(0);

        $this->boolean($ticket->getFromDB($ticket_id))->isTrue();
        $this->integer((int)$ticket->fields['olas_id_ttr'])->isEqualTo(0);
        $this->variable($ticket->fields['ola_ttr_begin_date'])->isEqualTo(null);
        $this->variable($ticket->fields['internal_time_to_resolve'])->isEqualTo(null);

        // Assign manual TTR
        $this->boolean($ticket->update(['id' => $ticket_id, 'internal_time_to_resolve' => $tomorrow_1pm]))->isTrue();
        $this->boolean($ticket->getFromDB($ticket_id))->isTrue();
        $this->integer((int)$ticket->fields['olas_id_ttr'])->isEqualTo(0);
        $this->variable($ticket->fields['ola_ttr_begin_date'])->isEqualTo(null);
        $this->variable($ticket->fields['internal_time_to_resolve'])->isEqualTo($tomorrow_1pm);

        // Simulate 1 hour of waiting time
        $this->boolean(
            $ticket->update(
                [
                 'id' => $ticket_id,
                 'status' => \CommonITILObject::WAITING,
            ]
            )
        )->isTrue();
        $_SESSION['glpi_currenttime'] = date('Y-m-d H:i:s', strtotime('+1 hour', strtotime((string) $currenttime_bak)));
        $updated = $ticket->update(['id' => $ticket_id, 'status' => \CommonITILObject::ASSIGNED]);
        $_SESSION['glpi_currenttime'] = $currenttime_bak;
        $this->boolean($updated)->isTrue();
        $this->variable($ticket->fields['internal_time_to_resolve'])->isEqualTo($tomorrow_2pm);
    }
}
