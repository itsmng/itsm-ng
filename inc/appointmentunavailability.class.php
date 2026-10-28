<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class AppointmentUnavailability extends CommonDBChild
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
        $unavailability = new self();
        if (!self::canView() || !$unavailability->canAccessTarget(false, $appointmenttargets_id)) {
            return false;
        }
        if (self::canCreate()) {
            $unavailability->showForm(-1, ['appointmenttargets_id' => $appointmenttargets_id]);
        }
        $fields = [__('Start date'), __('End date'), __('Available'), __('Comments')];
        if (self::canPurge()) {
            $fields[] = _n('Action', 'Actions', Session::getPluralNumber());
        }
        $values = [];
        foreach ($DB->request([
            'FROM' => self::getTable(), 'WHERE' => ['appointmenttargets_id' => $appointmenttargets_id], 'ORDER' => 'begin',
        ]) as $row) {
            $value = [Html::convDateTime($row['begin']), Html::convDateTime($row['end']),
                Dropdown::getYesNo($row['is_available']), Html::clean($row['comment'])];
            if (self::canPurge()) {
                $value[] = Html::getSimpleForm(self::getFormURL(), 'purge', __('Delete permanently'),
                    ['id' => $row['id']], 'fa-trash-alt btn btn-outline-danger');
            }
            $values[] = $value;
        }
        $id = 'appointmentUnavailable_' . $appointmenttargets_id;
        renderTwigTemplate('table.twig', [
            'id' => $id, 'state_key' => $id, 'fields' => $fields, 'values' => $values,
            'pageSize' => (int) ($_SESSION['glpilist_limit'] ?? 15),
        ]);
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
                    'class' => $is_new ? 'btn btn-primary' : 'btn btn-secondary',
                    'icon' => $is_new ? 'fas fa-plus' : null
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

    public static function hasBlockingUnavailability($appointmenttargets_id, $begin, $end)
    {
        return self::hasUnavailability($appointmenttargets_id, $begin, $end, 0);
    }

    public static function hasOpeningUnavailability($appointmenttargets_id, $begin, $end)
    {
        return self::hasUnavailability($appointmenttargets_id, $begin, $end, 1);
    }

    public static function hasCoveringOpeningUnavailability($appointmenttargets_id, $begin, $end)
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

    private static function hasUnavailability($appointmenttargets_id, $begin, $end, $is_available)
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
