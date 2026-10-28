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
 * SlaLevel class
**/
class SlaLevel extends LevelAgreementLevel
{
    protected $rules_id_field     = 'slalevels_id';
    protected $ruleactionclass    = 'SlaLevelAction';
    protected static $parentclass = 'SLA';
    protected static $fkparent    = 'slas_id';
    // No criteria
    protected $rulecriteriaclass = 'SlaLevelCriteria';


    public static function getTable($classname = null)
    {
        return CommonDBTM::getTable(__CLASS__);
    }


    public function cleanDBonPurge()
    {
        parent::cleanDBonPurge();

        // SlaLevel_Ticket does not extends CommonDBConnexity
        $slt = new SlaLevel_Ticket();
        $slt->deleteByCriteria([$this->rules_id_field => $this->fields['id']]);
    }


    public function showForParent(SLA $sla)
    {
        return $this->showForSLA($sla);
    }


    /**
     * @param $sla SLA object
     *
     * @since 9.1 (before showForSLA)
    **/
    public function showForSLA(SLA $sla)
    {
        return $this->showForAgreement($sla);
    }


    public function getActions()
    {

        $actions = parent::getActions();

        unset($actions['slas_id']);
        $actions['recall']['name']          = __('Automatic reminders of SLA');
        $actions['recall']['type']          = 'yesonly';
        $actions['recall']['force_actions'] = ['send'];

        return $actions;
    }


    /**
     * Show the rule
     *
     * @param $ID              ID of the rule
     * @param $options   array of possible options
     *
     * @return void
    **/
    public function showForm($ID, $options = [])
    {

        $canedit = $this->can('sla', UPDATE);

        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Name') . "</td>";
        echo "<td>";
        Html::autocompletionTextField($this, "name");
        echo "</td>";
        echo "<td>" . __('Active') . "</td>";
        echo "<td>";
        Dropdown::showYesNo("is_active", $this->fields["is_active"]);
        echo"</td></tr>\n";

        $sla = new SLA();
        $sla->getFromDB($this->fields['slas_id']);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . SLA::getTypeName(1) . "</td>";
        echo "<td>" . $sla->getLink() . "</td>";
        echo "<td>" . __('Execution') . "</td>";
        echo "<td>";

        $delay = $sla->getTime();

        self::dropdownExecutionTime(
            'execution_time',
            ['max_time'
                                               => $delay,
                                          'used'
                                               => self::getAlreadyUsedExecutionTime($sla->fields['id']),
                                          'value'
                                               => $this->fields['execution_time'],
                                          'type'
                                               => $sla->fields['type']]
        );
        echo "</td></tr>\n";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Logical operator') . "</td>";
        echo "<td>";
        $this->dropdownRulesMatch(['value' => $this->fields["match"]]);
        echo "</td>";
        echo "<td colspan='2'>&nbsp;</td></tr>";

        $this->showFormButtons($options);
    }

    /**
     * Get first level for a SLA
     *
     * @param $slas_id   integer  id of the SLA
     *
     * @since 9.1 (before getFirst SlaLevel)
     *
     * @return id of the sla level : 0 if not exists
    **/
    public static function getFirstSlaLevel($slas_id)
    {
        global $DB;

        $iterator = $DB->request([
           'SELECT' => 'id',
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'slas_id'   => $slas_id,
              'is_active' => 1
           ],
           'ORDER'  => 'execution_time ASC',
           'LIMIT'  => 1
        ]);

        if ($result = $iterator->next()) {
            return $result['id'];
        }
        return 0;
    }


    /**
     * Get next level for a SLA
     *
     * @param $slas_id         integer id of the SLA
     * @param $slalevels_id    integer id of the current SLA level
     *
     * @return id of the sla level : 0 if not exists
    **/
    public static function getNextSlaLevel($slas_id, $slalevels_id)
    {
        global $DB;

        $iterator = $DB->request([
           'SELECT' => 'execution_time',
           'FROM'   => self::getTable(),
           'WHERE'  => ['id' => $slalevels_id]
        ]);

        if ($result = $iterator->next()) {
            $execution_time = $result['execution_time'];

            $lvl_iterator = $DB->request([
               'SELECT' => 'id',
               'FROM'   => self::getTable(),
               'WHERE'  => [
                  'slas_id'         => $slas_id,
                  'is_active'       => 1,
                  'id'              => ['<>', $slalevels_id],
                  'execution_time'  => ['>', $execution_time]
               ],
               'ORDER'  => 'execution_time ASC',
               'LIMIT'  => 1
            ]);

            if ($result = $lvl_iterator->next()) {
                return $result['id'];
            }
        }
        return 0;
    }
}
