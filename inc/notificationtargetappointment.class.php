<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class NotificationTargetAppointment extends NotificationTarget
{
    public const APPOINTMENT_RECEIVER = 10240;

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
        $this->addTarget(self::APPOINTMENT_RECEIVER, __('Appointment receiver'));
    }

    public function addSpecificTargets($data, $options)
    {
        switch ($data['items_id']) {
            case self::APPOINTMENT_RECEIVER:
                if ($this->obj) {
                    foreach ($this->obj->getReceiverUsers() as $users_id) {
                        $this->addToRecipientsList([
                           'users_id'                => $users_id,
                           'is_appointment_receiver' => true,
                        ]);
                    }
                }
                break;
        }
    }

    public function addAdditionnalUserInfo(array $data)
    {
        return [
           'is_appointment_receiver' => !empty($data['is_appointment_receiver']),
        ];
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
        $this->data['##appointment.target##'] = $this->obj->getReceiverLabel();
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
           'appointment.target'     => __('Appointment target'),
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

    public function getGeneratedAttachments($event, array $options, array $user_infos)
    {
        if (empty($user_infos['additionnaloption']['is_appointment_receiver'])) {
            return [];
        }

        return [$this->obj->getIcalAttachment($event)];
    }
}
