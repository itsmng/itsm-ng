<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class AppointmentAvailabilityException extends CommonDBChild
{
    public static $itemtype = 'AppointmentTarget';
    public static $items_id = 'appointmenttargets_id';
    public static $rightname = 'appointment';

    public static function getTypeName($nb = 0)
    {
        return _n('Unavailability', 'Unavailabilities', $nb);
    }

    public static function canCreate()
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    public static function canUpdate()
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    public static function canDelete()
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    public static function canPurge()
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    public static function canView()
    {
        return Session::haveRight(self::$rightname, READ)
            || Session::haveRight(self::$rightname, CREATE)
            || Session::haveRight(self::$rightname, UPDATE);
    }

    public function canUpdateItem()
    {
        return Session::haveRight(self::$rightname, UPDATE)
            && $this->canAccessTarget(false)
            && parent::canUpdateItem();
    }

    public function canDeleteItem()
    {
        return Session::haveRight(self::$rightname, UPDATE)
            && $this->canAccessTarget(false)
            && parent::canDeleteItem();
    }

    public function canPurgeItem()
    {
        return Session::haveRight(self::$rightname, UPDATE)
            && $this->canAccessTarget(false)
            && parent::canPurgeItem();
    }

    public function isEntityAssign()
    {
        return false;
    }

    public function prepareInputForAdd($input)
    {
        $input = parent::prepareInputForAdd($input);
        if ($input === false || !$this->canAccessTargetFromInput($input)) {
            return false;
        }

        if (empty($input['plan'])) {
            return false;
        }

        Toolbox::manageBeginAndEndPlanDates($input['plan']);
        $input['begin'] = $input['plan']['begin'];
        $input['end'] = $input['plan']['end'];
        unset($input['plan']);
        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        $input = parent::prepareInputForUpdate($input);
        if ($input === false || !$this->canAccessTargetFromInput($input)) {
            return false;
        }

        if (isset($input['plan'])) {
            Toolbox::manageBeginAndEndPlanDates($input['plan']);
            $input['begin'] = $input['plan']['begin'];
            $input['end'] = $input['plan']['end'];
            unset($input['plan']);
        }
        return $input;
    }

    private function canAccessTargetFromInput(array $input)
    {
        $appointmenttargets_id = (int)($input[self::$items_id] ?? $this->fields[self::$items_id] ?? 0);
        return $this->canAccessTarget(true, $appointmenttargets_id);
    }

    private function canAccessTarget(bool $add_message, ?int $appointmenttargets_id = null)
    {
        $appointmenttargets_id = $appointmenttargets_id ?? (int)($this->fields[self::$items_id] ?? 0);
        if ($appointmenttargets_id <= 0) {
            return false;
        }

        $target = new AppointmentTarget();
        if (!$target->getFromDB($appointmenttargets_id) || !$target->canAccessEntity()) {
            if ($add_message) {
                Session::addMessageAfterRedirect(__('Appointment target is not available'), false, ERROR);
            }
            return false;
        }

        return true;
    }

    public static function showForTarget($appointmenttargets_id)
    {
        global $DB;

        $appointmenttargets_id = (int) $appointmenttargets_id;
        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixe' aria-label='Unavailabilities'>";
        echo "<tr><th colspan='6'>" . self::getTypeName(Session::getPluralNumber()) . "</th></tr>";
        echo "<tr><th>" . __('Start date') . "</th><th>" . __('End date') . "</th><th>" . __('Available') . "</th><th>" . __('Comments') . "</th><th></th></tr>";

        $iterator = $DB->request([
            'FROM' => self::getTable(),
            'WHERE' => ['appointmenttargets_id' => $appointmenttargets_id],
            'ORDER' => 'begin',
        ]);
        foreach ($iterator as $row) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . Html::convDateTime($row['begin']) . "</td>";
            echo "<td>" . Html::convDateTime($row['end']) . "</td>";
            echo "<td>" . Dropdown::getYesNo($row['is_available']) . "</td>";
            echo "<td>" . Html::clean($row['comment']) . "</td>";
            echo "<td class='center'>";
            Html::showSimpleForm(self::getFormURL(), 'purge', __('Delete permanently'), ['id' => $row['id']]);
            echo "</td></tr>";
        }

        $begin = date('Y-m-d H:00:00', strtotime('+1 day'));
        $end = date('Y-m-d H:00:00', strtotime('+1 day +1 hour'));
        echo "<tr class='tab_bg_2'><td colspan='6'>";
        echo "<form method='post' action='" . self::getFormURL() . "'>";
        echo Html::hidden('appointmenttargets_id', ['value' => $appointmenttargets_id]);
        echo "<input type='datetime-local' name='plan[begin]' value='" . Html::clean($begin) . "' required>";
        echo "&nbsp;<input type='datetime-local' name='plan[end]' value='" . Html::clean($end) . "' required>";
        echo "&nbsp;";
        Dropdown::showYesNo('is_available', 0);
        echo "&nbsp;<input type='text' name='comment' placeholder=\"" . __s('Comments') . "\">";
        echo "&nbsp;<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='btn btn-secondary'>";
        Html::closeForm();
        echo "</td></tr>";
        echo "</table></div>";
    }

    public function showForm($ID, $options = [])
    {
        if (!self::canCreate()) {
            echo "<div class='appointment-calendar-form-error'>" . __("You don't have permission to perform this action.") . "</div>";
            return false;
        }

        $is_new = $this->isNewID($ID);
        if ($is_new) {
            $this->fields['appointmenttargets_id'] = $options['appointmenttargets_id'] ?? 0;
            $this->fields['begin'] = $options['begin'] ?? date('Y-m-d H:00:00', strtotime('+1 day'));
            $this->fields['end'] = $options['end'] ?? date('Y-m-d H:00:00', strtotime('+1 day +1 hour'));
            $this->fields['is_available'] = $options['is_available'] ?? 0;
            $this->fields['comment'] = $options['comment'] ?? '';
        } elseif (!$this->getFromDB($ID)) {
            echo "<div class='appointment-calendar-form-error'>" . __('Item not found') . "</div>";
            return false;
        } elseif (!$this->canUpdateItem()) {
            echo "<div class='appointment-calendar-form-error'>" . __("You don't have permission to perform this action.") . "</div>";
            return false;
        }

        $form = [
            'action' => self::getFormURL(),
            'buttons' => [
                [
                    'name' => $is_new ? 'add' : 'update',
                    'value' => $is_new ? __('Add') : __('Save'),
                    'type' => 'submit',
                    'class' => 'btn btn-secondary'
                ],
                !$is_new && $this->canPurgeItem() ? [
                    'name' => 'purge',
                    'value' => __('Delete permanently'),
                    'type' => 'submit',
                    'class' => 'btn btn-secondary'
                ] : [],
            ],
            'content' => [
                self::getTypeName(1) => [
                    'visible' => true,
                    'inputs' => [
                        !$is_new ? [
                            'type' => 'hidden',
                            'name' => 'id',
                            'value' => $ID,
                        ] : [],
                        [
                            'type' => 'hidden',
                            'name' => 'appointmenttargets_id',
                            'value' => $this->fields['appointmenttargets_id'],
                        ],
                        __('Start date') => [
                            'type' => 'datetime-local',
                            'name' => 'plan[begin]',
                            'value' => $this->fields['begin'],
                        ],
                        __('End date') => [
                            'type' => 'datetime-local',
                            'name' => 'plan[end]',
                            'value' => $this->fields['end'],
                        ],
                        __('Available') => [
                            'type' => 'select',
                            'name' => 'is_available',
                            'values' => [
                                0 => __('No'),
                                1 => __('Yes'),
                            ],
                            'value' => $this->fields['is_available'],
                        ],
                        __('Comments') => [
                            'type' => 'text',
                            'name' => 'comment',
                            'value' => $this->fields['comment'],
                        ],
                    ],
                ],
            ],
        ];

        renderTwigForm($form, '', $this->fields);
    }

    public static function hasBlockingException($appointmenttargets_id, $begin, $end)
    {
        return self::hasException($appointmenttargets_id, $begin, $end, 0);
    }

    public static function hasOpeningException($appointmenttargets_id, $begin, $end)
    {
        return self::hasException($appointmenttargets_id, $begin, $end, 1);
    }

    public static function hasCoveringOpeningException($appointmenttargets_id, $begin, $end)
    {
        global $DB;

        $row = $DB->request([
            'COUNT' => 'cpt',
            'FROM' => self::getTable(),
            'WHERE' => [
                'appointmenttargets_id' => (int) $appointmenttargets_id,
                'is_available' => 1,
                'begin' => ['<=', $begin],
                'end' => ['>=', $end],
            ],
        ])->next();

        return (int) $row['cpt'] > 0;
    }

    private static function hasException($appointmenttargets_id, $begin, $end, $is_available)
    {
        global $DB;

        $row = $DB->request([
            'COUNT' => 'cpt',
            'FROM' => self::getTable(),
            'WHERE' => [
                'appointmenttargets_id' => (int) $appointmenttargets_id,
                'is_available' => (int) $is_available,
                'end' => ['>', $begin],
                'begin' => ['<', $end],
            ],
        ])->next();

        return (int) $row['cpt'] > 0;
    }
}
