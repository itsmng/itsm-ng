<?php

use Ramsey\Uuid\Uuid;
use Sabre\VObject\Component\VCalendar;

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

    public function canViewItem()
    {
        if (!parent::canViewItem()) {
            return false;
        }

        return $this->canViewAppointmentDetails();
    }

    public static function canUpdate()
    {
        return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE]);
    }

    public function canUpdateItem()
    {
        return isset($this->fields['users_id_requester'])
            && (int)$this->fields['users_id_requester'] === (int)Session::getLoginUserID()
            && strtotime((string)$this->fields['begin']) > time();
    }

    public function canPurgeItem()
    {
        return isset($this->fields['users_id_requester'])
            && (int)$this->fields['users_id_requester'] === (int)Session::getLoginUserID()
            && strtotime((string)$this->fields['begin']) > time();
    }

    public function canViewAppointmentDetails(?int $users_id = null): bool
    {
        $users_id = $users_id ?? (int)Session::getLoginUserID();
        if ($users_id <= 0) {
            return false;
        }

        return (
            isset($this->fields['users_id_requester'])
            && (int)$this->fields['users_id_requester'] === $users_id
        ) || (
            isset($this->fields['users_id'])
            && (int)$this->fields['users_id'] === $users_id
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

        if (!$this->validateBookingDates($input)) {
            return false;
        }

        $input['_appointment_users_id_provided'] = array_key_exists('users_id', $input);
        $input = $this->completeTargetFields($input);
        if ($input === false || !$this->validateBookingInput($input)) {
            return false;
        }
        unset($input['_appointment_users_id_provided']);

        if (empty($input['name'])) {
            $input['name'] = __('Appointment');
        }
        if (empty($input['uuid'])) {
            $input['uuid'] = Uuid::uuid4()->toString();
        }
        $input['state'] = Planning::INFO;
        $input['date'] = $_SESSION['glpi_currenttime'];

        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        if (!Session::haveRight(self::$rightname, UPDATE)) {
            unset($input['users_id_requester']);
        }

        if (isset($input['plan'])) {
            Toolbox::manageBeginAndEndPlanDates($input['plan']);
            $input['begin'] = $input['plan']['begin'];
            $input['end'] = $input['plan']['end'];
            unset($input['plan']);
        }

        $candidate = array_merge($this->fields, $input);
        $candidate['_appointment_users_id_provided'] = array_key_exists('users_id', $input);
        if (!$this->validateBookingDates($candidate)) {
            return false;
        }

        $candidate = $this->completeTargetFields($candidate);
        if ($candidate === false || !$this->validateBookingInput($candidate)) {
            return false;
        }

        $input['entities_id'] = $candidate['entities_id'];
        $input['is_recursive'] = $candidate['is_recursive'];
        $input['appointmenttargets_id'] = $candidate['appointmenttargets_id'];
        $input['users_id'] = $candidate['users_id'];

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

        if ($target->fields['itemtype'] === 'Group') {
            $target_fields = $this->resolveGroupTargetForBooking($target->fields, $input);
            if ($target_fields === false) {
                return false;
            }
        } else {
            $target_fields = $target->fields;
        }

        if ($target_fields['itemtype'] !== 'User') {
            Session::addMessageAfterRedirect(__('Appointment target is not available'), false, ERROR);
            return false;
        }

        $input['appointmenttargets_id'] = (int)$target_fields['id'];
        $input['users_id'] = (int)$target_fields['items_id'];
        $input['entities_id'] = $target_fields['entities_id'];
        $input['is_recursive'] = $target_fields['is_recursive'];

        return $input;
    }

    private function resolveGroupTargetForBooking(array $group_target, array $input)
    {
        $selected_users_id = !empty($input['_appointment_users_id_provided'])
            ? (int)($input['users_id'] ?? 0)
            : 0;
        $member_targets = AppointmentTarget::getGroupMemberTargetRows($group_target);

        if ($selected_users_id > 0) {
            foreach ($member_targets as $member_target) {
                if ((int)$member_target['items_id'] === $selected_users_id) {
                    return $member_target;
                }
            }

            Session::addMessageAfterRedirect(__('Appointment target is not available'), false, ERROR);
            return false;
        }

        foreach ($member_targets as $member_target) {
            if ($this->isBookableForTarget($member_target, $input)) {
                return $member_target;
            }
        }

        Session::addMessageAfterRedirect(__('No technician is available for the selected timeframe'), false, ERROR);
        return false;
    }

    private function validateBookingDates(array $input)
    {
        if (
            empty($input['begin'])
            || empty($input['end'])
            || strtotime((string)$input['begin']) >= strtotime((string)$input['end'])
        ) {
            Session::addMessageAfterRedirect(__('Error in entering dates. The starting date is later than the ending date'), false, ERROR);
            return false;
        }

        return true;
    }

    private function validateBookingInput(array $input)
    {
        if (!AppointmentAvailability::isAvailable($input['appointmenttargets_id'], $input['begin'], $input['end'])) {
            Session::addMessageAfterRedirect(__('The selected timeframe is outside appointment availability'), false, ERROR);
            return false;
        }

        if ($this->isTargetBooked($input)) {
            Session::addMessageAfterRedirect(__('The selected appointment target is already booked for this timeframe'), false, ERROR);
            return false;
        }

        $target = self::getTargetForAppointmentInput($input);
        if ($target && $target['itemtype'] === 'User' && $this->isUserAlreadyPlanned($target, $input)) {
            return false;
        }

        return true;
    }

    private function isBookableForTarget(array $target, array $input)
    {
        return AppointmentAvailability::isAvailable($target['id'], $input['begin'], $input['end'])
            && !$this->isTargetBooked(array_merge($input, ['appointmenttargets_id' => $target['id']]))
            && !$this->isUserAlreadyPlanned($target, $input);
    }

    private function isUserAlreadyPlanned(array $target, array $input)
    {
        $except = [];
        if (!empty($input['id'])) {
            $except[self::class] = [(int)$input['id']];
        }

        return Planning::checkAlreadyPlanned($target['items_id'], $input['begin'], $input['end'], $except);
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

    private static function getTargetForAppointmentInput(array $input)
    {
        if (empty($input['appointmenttargets_id'])) {
            return false;
        }

        $target = new AppointmentTarget();
        if ($target->getFromDB((int)$input['appointmenttargets_id'])) {
            return $target->fields;
        }

        return false;
    }

    public function getAppointmentTarget()
    {
        return self::getTargetForAppointmentInput($this->fields);
    }

    public function getReceiverUsers()
    {
        $target = $this->getAppointmentTarget();
        if (!$target) {
            return [];
        }

        if ($target['itemtype'] === 'User') {
            return [(int)$target['items_id']];
        }

        if ($target['itemtype'] === 'Group') {
            $users = [];
            foreach (Group_User::getGroupUsers($target['items_id']) as $user) {
                $users[] = (int)$user['id'];
            }
            return $users;
        }

        return [];
    }

    public function getReceiverLabel()
    {
        $target = $this->getAppointmentTarget();
        return $target ? AppointmentTarget::getTargetLabel($target) : '';
    }

    public function getIcalAttachment($event)
    {
        global $CFG_GLPI;

        $method = $event === 'delete' ? 'CANCEL' : 'REQUEST';
        $uid = $this->getField('uuid') ?: 'Appointment-' . $this->getID();
        $description = Html::clean($this->getField('text'));
        $url = $CFG_GLPI['url_base'] . self::getFormURLWithID($this->getID());

        $date_begin = (new DateTime($this->getField('begin')))->format('Ymd\THis');
        $date_end = (new DateTime($this->getField('end')))->format('Ymd\THis');
        $date_stamp = new DateTime($_SESSION['glpi_currenttime'] ?? 'now');
        $date_stamp->setTimezone(new DateTimeZone('UTC'));

        $vevent = [
           'UID'         => $uid,
           'DTSTAMP'     => $date_stamp,
           'DTSTART'     => $date_begin,
           'DTEND'       => $date_end,
           'SUMMARY'     => $this->getField('name'),
           'DESCRIPTION' => $description,
           'URL'         => $url,
        ];
        if ($event === 'delete') {
            $vevent['STATUS'] = 'CANCELLED';
        }

        $vcalendar = new VCalendar();
        $vcalendar->add('METHOD', $method);
        $vcalendar->add('VEVENT', $vevent);

        return [
           'filename' => sprintf('appointment-%s.ics', $this->getID()),
           'content'  => $vcalendar->serialize(),
           'type'     => 'text/calendar; method=' . $method . '; charset=utf-8',
        ];
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

        if (!self::canView()) {
            return false;
        }

        $appointmenttargets_id = (int)$appointmenttargets_id;
        $title = self::getTypeName(Session::getPluralNumber());
        $can_book = false;
        $target = null;
        if ($appointmenttargets_id > 0) {
            $target = new AppointmentTarget();
            if (!$target->getFromDB($appointmenttargets_id)) {
                Html::displayNotFoundError();
                return false;
            }
            if (!$target->canAccessEntity()) {
                Html::displayRightError();
                return false;
            }
            $title = AppointmentTarget::getTargetLabel($target->fields);
            $can_book = self::canCreate();
        }

        $options = [
           'appointmenttargets_id' => $appointmenttargets_id,
           'can_book'              => $can_book,
           'initial_date'          => date('Y-m-d'),
           'title'                 => $title,
           'appointment_title'     => self::getTypeName(1),
           'unavailability_title'  => AppointmentUnavailability::getTypeName(1),
           'ajax_url'              => $CFG_GLPI['root_doc'] . '/ajax/v2/appointment.php',
           'select2_url'           => $CFG_GLPI['root_doc'] . '/node_modules/select2/dist/js/select2.min.js',
           'all_url'               => $CFG_GLPI['root_doc'] . '/front/appointment.php',
           'planning_begin'         => $CFG_GLPI['planning_begin'] ?? '08:00:00',
           'planning_end'           => $CFG_GLPI['planning_end'] ?? '20:00:00',
           'book_with_group_label'  => __('Book with group'),
           'technician_placeholder' => __('Search a technician'),
        ];

        echo Html::css('public/lib/fullcalendar.css', ['media' => '']);
        echo Html::fullCalendarScripts();
        echo Html::script('js/appointment.js', ['version' => ITSM_VERSION . '-appointment-target-search']);

        echo "<div class='appointment-calendar-shell'>";
        echo "<div class='appointment-calendar-header'>";
        echo "<h2>" . Html::clean($title) . "</h2>";
        echo "<div class='appointment-calendar-header__actions'>";
        if ($appointmenttargets_id > 0 && $target !== null && $target->fields['itemtype'] === 'Group') {
            echo "<div class='appointment-calendar-target-switcher'>";
            echo "<label for='appointment-calendar-target-picker'>" . __('Technician') . "</label>";
            echo "<select id='appointment-calendar-target-picker'>";
            echo "<option value='" . (int)$appointmenttargets_id . "'>" . Html::clean(__('Book with group')) . "</option>";
            echo "</select></div>";
        }
        echo "</div>";
        echo "</div>";
        if ($appointmenttargets_id > 0) {
            echo "<div class='appointment-calendar-show-all'>";
            echo "<a href='" . $CFG_GLPI['root_doc'] . "/front/appointment.php'>" . __('Show all') . "</a>";
            echo "</div>";
        }
        echo "<div id='appointment-calendar'></div></div>";
        echo Html::scriptBlock('$(function() { ITSMAppointmentCalendar.display(' . json_encode($options) . '); });');
    }

    public static function showTargetList()
    {
        global $CFG_GLPI;

        if (!self::canView()) {
            return false;
        }

        $fields = [
           'target'  => __('Appointment target'),
           'entity'  => Entity::getTypeName(1),
           'comment' => __('Comments'),
        ];

        echo "<div class='appointment-target-list'>";
        renderTwigTemplate('table.twig', [
           'id'               => 'appointment-target-table',
           'fields'           => $fields,
           'url'              => $CFG_GLPI['root_doc'] . '/ajax/v2/appointment_target.php',
           'search'           => true,
           'search_placeholder' => __('Search'),
           'show_export'      => false,
           'pageSize'         => (int) $_SESSION['glpilist_limit'],
        ]);
        echo "</div>";
    }

    public function showForm($ID, $options = [])
    {
        $is_new = $this->isNewID($ID);
        if ($is_new && !self::canCreate()) {
            return false;
        }

        $this->initForm($ID, $options);
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
           ] + getEntitiesRestrictCriteria(AppointmentTarget::getTable(), 'entities_id', $_SESSION['glpiactiveentities'], true),
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
                  self::getTable() . '.users_id_requester' => (int)$options['who'],
                  self::getTable() . '.users_id' => (int)$options['who'],
               ]
            ];
        } elseif (!empty($options['whogroup'])) {
            $users = [];
            foreach (Group_User::getGroupUsers((int)$options['whogroup']) as $user) {
                $users[] = (int)$user['id'];
            }
            if (count($users) === 0) {
                return $events;
            }
            $where[self::getTable() . '.users_id'] = $users;
        } else {
            return $events;
        }

        $iterator = $DB->request([
           'SELECT' => [
              self::getTable() . '.*',
           ],
           'FROM' => self::getTable(),
           'INNER JOIN' => [
              AppointmentTarget::getTable() => [
                 'ON' => [
                    self::getTable()              => 'appointmenttargets_id',
                    AppointmentTarget::getTable() => 'id',
                 ],
              ],
           ],
           'WHERE' => $where + getEntitiesRestrictCriteria(self::getTable(), 'entities_id', $_SESSION['glpiactiveentities'], true),
           'ORDER' => 'begin',
        ]);

        $item = new self();
        foreach ($iterator as $row) {
            $item->getFromResultSet($row);
            if (!$item->canViewItem()) {
                continue;
            }

            $users_id = (int)($row['users_id'] ?? 0);
            if ($users_id <= 0) {
                $users_id = (int)$row['users_id_requester'];
            }

            $key = $row['begin'] . '$$Appointment$$' . $row['id'] . '$$' . ($options['who'] ?? 0) . '$$' . ($options['whogroup'] ?? 0);
            $events[$key] = [
               'color'            => $options['color'] ?? '',
               'event_type_color' => $options['event_type_color'] ?? '',
               'itemtype'         => self::class,
               'appointments_id'  => $row['id'],
               'id'               => $row['id'],
               'users_id'         => $users_id,
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
