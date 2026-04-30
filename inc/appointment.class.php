<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class Appointment extends CommonDBTM
{
    public static $rightname = 'appointment';
    public $dohistory = true;

    public static function getTypeName($nb = 0)
    {
        return _n('Appointment', 'Appointments', $nb);
    }

    public static function getMenuName()
    {
        return self::getTypeName(Session::getPluralNumber());
    }

    public static function getMenuShorcut()
    {
        return 'a';
    }

    public static function getIcon()
    {
        return 'far fa-calendar-check';
    }

    public static function canView()
    {
        return Session::haveRightsOr(self::$rightname, [READ, CREATE, UPDATE]);
    }

    public static function canCreate()
    {
        return Session::haveRight(self::$rightname, CREATE)
            || Session::haveRight(self::$rightname, UPDATE);
    }

    public static function canUpdate()
    {
        return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE]);
    }

    public function canUpdateItem()
    {
        if (Session::haveRight(self::$rightname, UPDATE)) {
            return parent::canUpdateItem();
        }

        return isset($this->fields['users_id_requester'])
            && (int)$this->fields['users_id_requester'] === (int)Session::getLoginUserID()
            && strtotime((string)$this->fields['begin']) > time();
    }

    public function canPurgeItem()
    {
        return Session::haveRight(self::$rightname, UPDATE)
            || (
                isset($this->fields['users_id_requester'])
                && (int)$this->fields['users_id_requester'] === (int)Session::getLoginUserID()
                && strtotime((string)$this->fields['begin']) > time()
            );
    }

    public function prepareInputForAdd($input)
    {
        if (isset($input['plan'])) {
            Toolbox::manageBeginAndEndPlanDates($input['plan']);
            $input['begin'] = $input['plan']['begin'];
            $input['end'] = $input['plan']['end'];
            unset($input['plan']);
        }

        if (empty($input['users_id_requester'])) {
            $input['users_id_requester'] = Session::getLoginUserID();
        }

        if (!Session::haveRight(self::$rightname, UPDATE)) {
            $input['users_id_requester'] = Session::getLoginUserID();
        }

        $input = $this->completeTargetFields($input);
        if ($input === false || !$this->validateBookingInput($input)) {
            return false;
        }

        if (empty($input['name'])) {
            $input['name'] = __('Appointment');
        }
        if (empty($input['uuid'])) {
            $input['uuid'] = \Ramsey\Uuid\Uuid::uuid4()->toString();
        }
        $input['state'] = Planning::INFO;
        $input['date'] = $_SESSION['glpi_currenttime'];

        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        if (!Session::haveRight(self::$rightname, UPDATE)) {
            unset($input['users_id_requester'], $input['users_id_tech'], $input['groups_id_tech']);
        }

        if (isset($input['plan'])) {
            Toolbox::manageBeginAndEndPlanDates($input['plan']);
            $input['begin'] = $input['plan']['begin'];
            $input['end'] = $input['plan']['end'];
            unset($input['plan']);
        }

        $candidate = array_merge($this->fields, $input);
        $candidate = $this->completeTargetFields($candidate);
        if ($candidate === false || !$this->validateBookingInput($candidate)) {
            return false;
        }

        if (isset($input['appointmenttargets_id']) || isset($input['users_id_tech']) || isset($input['groups_id_tech'])) {
            $input['users_id_tech'] = $candidate['users_id_tech'];
            $input['groups_id_tech'] = $candidate['groups_id_tech'];
        }

        return $input;
    }

    private function completeTargetFields(array $input)
    {
        $target = new AppointmentTarget();
        if (empty($input['appointmenttargets_id']) || !$target->getFromDB((int)$input['appointmenttargets_id'])) {
            Session::addMessageAfterRedirect(__('Appointment target is not available'), false, ERROR);
            return false;
        }
        if (!$target->fields['is_active'] || !empty($target->fields['is_deleted'])) {
            Session::addMessageAfterRedirect(__('Appointment target is not available'), false, ERROR);
            return false;
        }
        if (!Session::haveAccessToEntity($target->fields['entities_id'], $target->fields['is_recursive'])) {
            Session::addMessageAfterRedirect(__('Appointment target is not available'), false, ERROR);
            return false;
        }

        $input['entities_id'] = $target->fields['entities_id'];
        $input['is_recursive'] = $target->fields['is_recursive'];

        if ($target->fields['itemtype'] === 'User') {
            $input['users_id_tech'] = $target->fields['items_id'];
            $input['groups_id_tech'] = 0;
        } else {
            $input['groups_id_tech'] = $target->fields['items_id'];
            $input['users_id_tech'] = isset($input['users_id_tech']) ? (int)$input['users_id_tech'] : 0;
        }

        return $input;
    }

    private function validateBookingInput(array $input)
    {
        if (
            empty($input['begin'])
            || empty($input['end'])
            || strtotime((string)$input['begin']) >= strtotime((string)$input['end'])
        ) {
            Session::addMessageAfterRedirect(__('Error in entering dates. The starting date is later than the ending date'), false, ERROR);
            return false;
        }

        if (!AppointmentAvailability::isAvailable($input['appointmenttargets_id'], $input['begin'], $input['end'])) {
            Session::addMessageAfterRedirect(__('The selected timeframe is outside appointment availability'), false, ERROR);
            return false;
        }

        if ($this->isTargetBooked($input)) {
            Session::addMessageAfterRedirect(__('The selected appointment target is already booked for this timeframe'), false, ERROR);
            return false;
        }

        if (!empty($input['users_id_tech'])) {
            $except = [];
            if (!empty($input['id'])) {
                $except[self::class] = [(int)$input['id']];
            }
            if (Planning::checkAlreadyPlanned($input['users_id_tech'], $input['begin'], $input['end'], $except)) {
                return false;
            }
        }

        return true;
    }

    private function isTargetBooked(array $input)
    {
        global $DB;

        $where = [
           'appointmenttargets_id' => (int)$input['appointmenttargets_id'],
           'end'                   => ['>', $input['begin']],
           'begin'                 => ['<', $input['end']],
           'is_deleted'            => 0,
        ];
        if (!empty($input['id'])) {
            $where['id'] = ['<>', (int)$input['id']];
        }

        $row = $DB->request([
           'COUNT' => 'cpt',
           'FROM'  => self::getTable(),
           'WHERE' => $where,
        ])->next();

        return (int)$row['cpt'] > 0;
    }

    public function post_addItem()
    {
        global $CFG_GLPI;

        if (!isset($this->input['_disablenotif']) && $CFG_GLPI['use_notifications']) {
            NotificationEvent::raiseEvent('new', $this);
        }
        parent::post_addItem();
    }

    public function post_updateItem($history = 1)
    {
        global $CFG_GLPI;

        if (count($this->updates) && !isset($this->input['_disablenotif']) && $CFG_GLPI['use_notifications']) {
            NotificationEvent::raiseEvent('update', $this);
        }
        parent::post_updateItem($history);
    }

    public function pre_deleteItem()
    {
        global $CFG_GLPI;

        if (!isset($this->input['_disablenotif']) && $CFG_GLPI['use_notifications']) {
            NotificationEvent::raiseEvent('delete', $this);
        }
        return true;
    }

    public static function showCalendar($appointmenttargets_id = 0)
    {
        global $CFG_GLPI;

        if (!self::canCreate()) {
            return false;
        }

        $appointmenttargets_id = (int)$appointmenttargets_id;
        $title = self::getTypeName(Session::getPluralNumber());
        $can_book = false;
        if ($appointmenttargets_id > 0) {
            $target = new AppointmentTarget();
            if (!$target->getFromDB($appointmenttargets_id)) {
                Html::displayNotFoundError();
                return false;
            }
            $title = AppointmentTarget::getTargetLabel($target->fields);
            $can_book = true;
        }

        $options = [
           'appointmenttargets_id' => $appointmenttargets_id,
           'can_book'              => $can_book,
           'initial_date'          => date('Y-m-d'),
           'title'                 => $title,
           'ajax_url'              => $CFG_GLPI['root_doc'] . '/ajax/appointment.php',
           'all_url'               => $CFG_GLPI['root_doc'] . '/front/appointment.php',
           'planning_begin'         => $CFG_GLPI['planning_begin'] ?? '08:00:00',
           'planning_end'           => $CFG_GLPI['planning_end'] ?? '20:00:00',
        ];

        echo Html::css('public/lib/fullcalendar.css', ['media' => '']);
        echo Html::script('public/lib/fullcalendar.js');
        echo Html::script('js/appointment.js');

        echo "<div class='appointment-calendar-shell'>";
        echo "<div class='appointment-calendar-header'><h2>" . Html::clean($title) . "</h2>";
        if ($appointmenttargets_id > 0) {
            echo "<a href='" . $CFG_GLPI['root_doc'] . "/front/appointment.php'>" . __('Show all') . "</a>";
        }
        echo "</div><div id='appointment-calendar'></div></div>";
        echo Html::scriptBlock('$(function() { ITSMAppointmentCalendar.display(' . json_encode($options) . '); });');
    }

    public static function showTargetList()
    {
        global $DB, $CFG_GLPI;

        if (!self::canView()) {
            return false;
        }

        $iterator = $DB->request([
           'FROM'  => AppointmentTarget::getTable(),
           'WHERE' => [
              'is_active'  => 1,
              'is_deleted' => 0,
           ] + getEntitiesRestrictCriteria(AppointmentTarget::getTable(), 'entities_id', $_SESSION['glpiactiveentities']),
           'ORDER' => ['itemtype', 'items_id'],
        ]);

        echo "<div class='table-responsive'><table class='table table-striped table-hover' aria-label='Appointments'>";
        echo "<thead class='table-dark'><tr><th>" . __('Appointment target') . "</th><th>" . Entity::getTypeName(1) . "</th><th>" . __('Comments') . "</th></tr></thead><tbody>";
        foreach ($iterator as $row) {
            echo "<tr>";
            echo "<td><a href='" . $CFG_GLPI['root_doc'] . "/front/appointment.php?appointmenttargets_id=" . $row['id'] . "'>" . Html::clean(AppointmentTarget::getTargetLabel($row)) . "</a></td>";
            echo "<td>" . Dropdown::getDropdownName('glpi_entities', $row['entities_id']) . "</td>";
            echo "<td>" . Html::clean($row['comment']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table></div>";
    }

    public function showForm($ID, $options = [])
    {
        if (!self::canCreate()) {
            return false;
        }

        $this->initForm($ID, $options);
        $is_new = $this->isNewID($ID);
        $canedit = $is_new ? self::canCreate() : $this->can($ID, UPDATE);
        if ($is_new) {
            $this->fields['appointmenttargets_id'] = $options['appointmenttargets_id'] ?? 0;
            $this->fields['begin'] = $options['begin'] ?? date('Y-m-d H:00:00');
            $this->fields['end'] = $options['end'] ?? date('Y-m-d H:00:00', strtotime('+1 hour'));
            $this->fields['users_id_requester'] = Session::getLoginUserID();
            $this->fields['name'] = __('Appointment');
        }

        $target_values = self::getTargetOptions();
        $form = [
           'action'  => self::getFormURL(),
           'buttons' => [
              $canedit ? [
                 'name'  => $is_new ? 'add' : 'update',
                 'value' => $is_new ? __('Add') : __('Save'),
                 'type'  => 'submit',
                 'class' => 'btn btn-secondary'
              ] : [],
              !$is_new && $this->canPurgeItem() ? [
                 'name'  => 'purge',
                 'value' => __('Delete permanently'),
                 'type'  => 'submit',
                 'class' => 'btn btn-secondary'
              ] : [],
           ],
           'content' => [
              self::getTypeName(1) => [
                 'visible' => true,
                 'inputs'  => [
                    !$is_new ? [
                       'type'  => 'hidden',
                       'name'  => 'id',
                       'value' => $ID,
                    ] : [],
                    __('Title') => [
                       'type'  => 'text',
                       'name'  => 'name',
                       'value' => $this->fields['name'],
                    ],
                    self::getTypeName(1) => [
                       'type'   => 'select',
                       'name'   => 'appointmenttargets_id',
                       'values' => $target_values,
                       'value'  => $this->fields['appointmenttargets_id'],
                    ],
                    __('Requester') => Session::haveRight(self::$rightname, UPDATE) ? [
                       'type'   => 'select',
                       'name'   => 'users_id_requester',
                       'values' => getOptionsForUsers('all'),
                       'value'  => $this->fields['users_id_requester'],
                    ] : [
                       'content' => getUserName($this->fields['users_id_requester']),
                    ],
                    '' => !Session::haveRight(self::$rightname, UPDATE) ? [
                       'type'  => 'hidden',
                       'name'  => 'users_id_requester',
                       'value' => $this->fields['users_id_requester'],
                    ] : [],
                    __('Technician') => Session::haveRight(self::$rightname, UPDATE) ? [
                       'type'   => 'select',
                       'name'   => 'users_id_tech',
                       'values' => [0 => Dropdown::EMPTY_VALUE] + getOptionsForUsers('all'),
                       'value'  => $this->fields['users_id_tech'],
                    ] : [],
                    __('Start date') => [
                       'type'  => 'datetime-local',
                       'name'  => 'plan[begin]',
                       'value' => $this->fields['begin'],
                    ],
                    __('End date') => [
                       'type'  => 'datetime-local',
                       'name'  => 'plan[end]',
                       'value' => $this->fields['end'],
                    ],
                    __('Description') => [
                       'type'   => 'textarea',
                       'name'   => 'text',
                       'value'  => $this->fields['text'],
                       'col_lg' => 12,
                       'col_md' => 12,
                    ],
                 ],
              ],
           ],
        ];

        renderTwigForm($form, '', $this->fields);
    }

    public static function getTargetOptions()
    {
        global $DB;

        $options = [];
        $iterator = $DB->request([
           'FROM'  => AppointmentTarget::getTable(),
           'WHERE' => [
              'is_active'  => 1,
              'is_deleted' => 0,
           ] + getEntitiesRestrictCriteria(AppointmentTarget::getTable(), 'entities_id', $_SESSION['glpiactiveentities']),
           'ORDER' => ['itemtype', 'items_id'],
        ]);

        foreach ($iterator as $row) {
            $options[$row['id']] = AppointmentTarget::getTargetLabel($row);
        }

        return $options;
    }

    public static function populatePlanning($options = [])
    {
        global $DB, $CFG_GLPI;

        $events = [];
        if (empty($options['begin']) || empty($options['end'])) {
            return $events;
        }

        $where = [
           self::getTable() . '.begin'      => ['<', $options['end']],
           self::getTable() . '.end'        => ['>', $options['begin']],
           self::getTable() . '.is_deleted' => 0,
        ];

        if (!empty($options['who'])) {
            $where[] = [
               'OR' => [
                  self::getTable() . '.users_id_tech'      => (int)$options['who'],
                  self::getTable() . '.users_id_requester' => (int)$options['who'],
               ]
            ];
        } elseif (!empty($options['whogroup'])) {
            $where[self::getTable() . '.groups_id_tech'] = (int)$options['whogroup'];
        } else {
            return $events;
        }

        $iterator = $DB->request([
           'FROM'  => self::getTable(),
           'WHERE' => $where + getEntitiesRestrictCriteria(self::getTable(), 'entities_id', $_SESSION['glpiactiveentities']),
           'ORDER' => 'begin',
        ]);

        $item = new self();
        foreach ($iterator as $row) {
            $item->getFromResultSet($row);
            if (!$item->canViewItem()) {
                continue;
            }

            $key = $row['begin'] . '$$Appointment$$' . $row['id'] . '$$' . ($options['who'] ?? 0) . '$$' . ($options['whogroup'] ?? 0);
            $events[$key] = [
               'color'            => $options['color'] ?? '',
               'event_type_color' => $options['event_type_color'] ?? '',
               'itemtype'         => self::class,
               'appointments_id'  => $row['id'],
               'id'               => $row['id'],
               'users_id'         => $row['users_id_tech'] ?: $row['users_id_requester'],
               'state'            => Planning::INFO,
               'background'       => false,
               'name'             => Html::clean(Html::resume_text($row['name'], $CFG_GLPI['cut'])),
               'text'             => Html::resume_text(Html::clean($row['text']), $CFG_GLPI['cut']),
               'ajaxurl'          => $CFG_GLPI['root_doc'] . '/ajax/planning.php?action=edit_event_form&itemtype=Appointment&id=' . $row['id'] . '&url=' . rawurlencode(self::getFormURLWithID($row['id'])),
               'editable'         => $item->canUpdateItem(),
               'url'              => self::getFormURLWithID($row['id']),
               'begin'            => $row['begin'],
               'end'              => $row['end'],
            ];
        }

        return $events;
    }

    public function getAlreadyPlannedInformation(array $val)
    {
        return sprintf(
            __('%1$s: from %2$s to %3$s:'),
            self::getTypeName(1),
            Html::convDateTime($val['begin']),
            Html::convDateTime($val['end'])
        ) . '<br/><a href="' . self::getFormURLWithID($val['id']) . '">' . Html::resume_text($val['name'], 80) . '</a>';
    }

    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();
        $tab[] = [
           'id'       => 80,
           'table'    => 'glpi_entities',
           'field'    => 'completename',
           'name'     => Entity::getTypeName(1),
           'datatype' => 'dropdown'
        ];
        return $tab;
    }
}
