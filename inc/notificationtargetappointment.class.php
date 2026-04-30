<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class NotificationTargetAppointment extends NotificationTarget
{
    public const APPOINTMENT_TECH = 10240;
    public const APPOINTMENT_GROUP = 10241;

    public function getEvents()
    {
        return [
           'new'    => __('New appointment'),
           'update' => __('Update of an appointment'),
           'delete' => __('Deletion of an appointment'),
        ];
    }

    public function addAdditionalTargets($event = '')
    {
        $this->addTarget(Notification::AUTHOR, _n('Requester', 'Requesters', 1));
        $this->addTarget(self::APPOINTMENT_TECH, __('Appointment technician'));
        $this->addTarget(self::APPOINTMENT_GROUP, __('Appointment group'));
    }

    public function addSpecificTargets($data, $options)
    {
        switch ($data['items_id']) {
            case self::APPOINTMENT_TECH:
                if ($this->obj && $this->obj->getField('users_id_tech') > 0) {
                    $this->addToRecipientsList([
                       'users_id' => $this->obj->getField('users_id_tech'),
                    ]);
                }
                break;

            case self::APPOINTMENT_GROUP:
                if ($this->obj && $this->obj->getField('groups_id_tech') > 0) {
                    foreach (Group_User::getGroupUsers($this->obj->getField('groups_id_tech')) as $user) {
                        $this->addToRecipientsList([
                           'users_id' => $user['id'],
                        ]);
                    }
                }
                break;
        }
    }

    public function addDataForTemplate($event, $options = [])
    {
        $events = $this->getAllEvents();
        $this->data['##appointment.action##'] = $events[$event];
        $this->data['##appointment.title##'] = $this->obj->getField('name');
        $this->data['##appointment.begin##'] = Html::convDateTime($this->obj->getField('begin'));
        $this->data['##appointment.end##'] = Html::convDateTime($this->obj->getField('end'));
        $this->data['##appointment.comment##'] = $this->obj->getField('text');
        $this->data['##appointment.requester##'] = getUserName($this->obj->getField('users_id_requester'));
        $this->data['##appointment.technician##'] = $this->obj->getField('users_id_tech') > 0
            ? getUserName($this->obj->getField('users_id_tech'))
            : '';
        $this->data['##appointment.group##'] = $this->obj->getField('groups_id_tech') > 0
            ? Dropdown::getDropdownName('glpi_groups', $this->obj->getField('groups_id_tech'))
            : '';
        $this->data['##appointment.entity##'] = Dropdown::getDropdownName('glpi_entities', $this->obj->getField('entities_id'));
        $this->data['##appointment.url##'] = $this->formatURL(
            $options['additionnaloption']['usertype'],
            'Appointment_' . $this->obj->getField('id')
        );

        $this->getTags();
        foreach ($this->tag_descriptions[NotificationTarget::TAG_LANGUAGE] as $tag => $values) {
            if (!isset($this->data[$tag])) {
                $this->data[$tag] = $values['label'];
            }
        }
    }

    public function getTags()
    {
        $tags = [
           'appointment.action'     => _n('Event', 'Events', 1),
           'appointment.title'      => __('Title'),
           'appointment.begin'      => __('Start date'),
           'appointment.end'        => __('End date'),
           'appointment.comment'    => __('Comments'),
           'appointment.requester'  => _n('Requester', 'Requesters', 1),
           'appointment.technician' => __('Technician'),
           'appointment.group'      => Group::getTypeName(1),
           'appointment.entity'     => Entity::getTypeName(1),
           'appointment.url'        => __('URL'),
        ];

        foreach ($tags as $tag => $label) {
            $this->addTagToList([
               'tag'   => $tag,
               'label' => $label,
               'value' => true,
            ]);
        }
        asort($this->tag_descriptions);
    }

    public function getObjectItem($event = '')
    {
        if ($this->obj) {
            $this->target_object[] = $this->obj;
        }
    }
}
